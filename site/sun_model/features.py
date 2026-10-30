"""Hand-crafted per-frame features. Pure numpy + Pillow so it runs in ~40 MB on the server.

Must stay numerically identical to the research version the models were trained on (feats v2)."""
from typing import Dict

import numpy as np
from PIL import Image, ImageFile

ImageFile.LOAD_TRUNCATED_IMAGES = True
W, H = 192, 108


def load_small(path: str) -> Image.Image:
    """Decode a frame and downsample it. Full decode (no JPEG draft mode): draft decoding shifts probabilities by
    up to 0.03 vs. the training features. A 1560 px frame is ~4 MB decoded, freed straight after."""
    with Image.open(path) as im:
        return im.convert("RGB").resize((W, H), Image.BILINEAR)


def feats(img: Image.Image) -> Dict[str, float]:
    im = img.convert("RGB")
    if im.size != (W, H):
        im = im.resize((W, H), Image.BILINEAR)
    a = np.asarray(im, np.float32) / 255.
    hsv = np.asarray(im.convert("HSV"), np.float32) / 255.
    R, G, B = a[..., 0], a[..., 1], a[..., 2]
    V, S = hsv[..., 2], hsv[..., 1]
    L = 0.299 * R + 0.587 * G + 0.114 * B
    warm = R + G - 2 * B
    f = {}
    gy = gx = 4
    for i in range(gy):
        for j in range(gx):
            sl = (slice(i * H // gy, (i + 1) * H // gy), slice(j * W // gx, (j + 1) * W // gx))
            f[f"L{i}{j}"] = L[sl].mean()
            f[f"S{i}{j}"] = S[sl].mean()
            f[f"Ls{i}{j}"] = L[sl].std()
            f[f"w{i}{j}"] = warm[sl].mean()
    for q in (5, 25, 50, 75, 95, 99):
        f[f"Lp{q}"] = np.percentile(L, q)
    f["clip"] = (V > 0.98).mean()
    f["dark"] = (V < 0.08).mean()
    f["Smean"] = S.mean()
    f["Lstd"] = L.std()
    top, bot = L[:H // 2], L[H // 2:]
    f["tb_ratio"] = top.mean() / (bot.mean() + 1e-3)
    f["bot_p90"] = np.percentile(bot, 90)
    f["bot_std"] = bot.std()
    f["bot_warm_p90"] = np.percentile(warm[H // 2:], 90)
    f["sky_blue"] = (B - R)[:H // 2].mean()
    f["edge"] = np.abs(np.diff(L, axis=1)).mean() + np.abs(np.diff(L, axis=0)).mean()
    f["bot_edge"] = np.abs(np.diff(bot, axis=1)).mean()
    rg = R - G
    yb = 0.5 * (R + G) - B
    f["colourful"] = np.sqrt(rg.std() ** 2 + yb.std() ** 2) + 0.3 * np.sqrt(rg.mean() ** 2 + yb.mean() ** 2)
    f["magenta"] = ((R > 0.6) & (B > 0.6) & (G < 0.45)).mean()
    up = V[:int(H * 0.6)]
    cm = up > 0.97
    f["top_clip"] = cm.mean()
    if cm.sum() > 3:
        ys, xs = np.nonzero(cm)
        f["clip_spread"] = (ys.std() + xs.std()) / (W / 4)
        cy, cx = int(ys.mean()), int(xs.mean())
        y0, y1, x0, x1 = max(cy - 8, 0), cy + 8, max(cx - 8, 0), cx + 8
        f["glow"] = L[y0:y1, x0:x1].mean() - np.median(L[:int(H * 0.6)])
    else:
        f["clip_spread"] = 0.
        f["glow"] = 0.
    f["fg_dark_skybright"] = np.percentile(top, 90) - np.percentile(bot, 50)
    # glitch cue (not a model input): pastel magenta smear the camera's encoder leaves over the foreground
    Rb, Gb, Bb = R[H // 2:], G[H // 2:], B[H // 2:]
    f["pink_bot"] = ((Rb - Gb > 0.08) & (Bb - Gb > 0.08) & (np.minimum(Rb, Bb) > 0.45)).mean()
    return {k: float(v) for k, v in f.items()}


# ---- v3: near-vegetation features (camera era from 2021-09-07) ------------------------------------------------
# Masks (192x108, white = near/far vegetation) were built from ~2,100 daytime frames: pixels that are almost never
# sky-like (blue or bright).  Summer = leaf months May-Oct, winter = Nov-Apr; the bottom overlay bar is excluded.
import os as _os
_MASKS = {}


def veg_mask(month):
    season = "summer" if 5 <= month <= 10 else "winter"
    if season not in _MASKS:
        p = _os.path.join(_os.path.dirname(_os.path.abspath(__file__)), "models", "veg_mask_%s.png" % season)
        with Image.open(p) as m:
            _MASKS[season] = np.asarray(m.convert("L")) > 127
    return _MASKS[season]


def veg_feats(img, month):
    """Lit-vs-shadow cues on the near trees, and the same relative to the sky, so bright sky over dark foliage
    (sun hidden behind a cloud, or only distant cloud lit) reads as not sunny."""
    im = img.convert("RGB")
    if im.size != (W, H):
        im = im.resize((W, H), Image.BILINEAR)
    a = np.asarray(im, np.float32) / 255.
    R, G, B = a[..., 0], a[..., 1], a[..., 2]
    L = 0.299 * R + 0.587 * G + 0.114 * B
    mx, mn = a.max(axis=2), a.min(axis=2)
    S = (mx - mn) / (mx + 1e-3)
    M = veg_mask(month)
    rows = np.nonzero(M.any(axis=1))[0]
    top = rows.min() if len(rows) else H // 2
    sky = np.zeros_like(M)
    sky[:max(top - 2, 1)] = True
    vL, sL = L[M], L[sky]
    sm = float(np.median(sL)) + 1e-3
    f = {}
    f["vL_mean"] = vL.mean()
    for q in (10, 50, 90):
        f["vL_p%d" % q] = np.percentile(vL, q)
    f["vL_std"] = vL.std()
    f["v_warm"] = (R + G - 2 * B)[M].mean()
    f["v_green"] = (G - 0.5 * (R + B))[M].mean()
    f["v_S"] = S[M].mean()
    f["v_hi"] = (vL > 0.45).mean()
    f["v_hi_rel"] = (vL > 0.5 * sm).mean()
    dx = np.abs(np.diff(L, axis=1))[M[:, 1:] & M[:, :-1]]
    f["v_edge"] = dx.mean() if dx.size else 0.
    xs = np.arange(W)[None, :].repeat(H, 0)
    ys = np.arange(H)[:, None].repeat(W, 1)
    for nm, sel in (("left", xs < W * 0.25), ("mid", (xs >= W * 0.25) & (xs < W * 0.6)), ("right", xs >= W * 0.6),
                    ("crown", ys < top + (H - top) * 0.4)):
        r = L[M & sel]
        f["vL_" + nm] = r.mean() if r.size else f["vL_mean"]
    f["sL_med"] = sm
    f["sL_p95"] = np.percentile(sL, 95)
    f["s_blue"] = (B - R)[sky].mean()
    f["s_clip"] = (sL > 0.97).mean()
    f["r_veg_sky"] = f["vL_mean"] / sm
    f["r_vp90_sky95"] = f["vL_p90"] / (f["sL_p95"] + 1e-3)
    f["r_crown_sky"] = f["vL_crown"] / sm
    f["r_log"] = float(np.log((f["vL_mean"] + 0.02) / (sm + 0.02)))
    return {k: float(v) for k, v in f.items()}


def add_interactions(f):
    """Sun-height interactions (needs f['el'] set): vegetation cues count for more as the sun climbs."""
    h = min(max(f["el"], 0.0), 40.0) / 40.0
    f["x_rveg_h"] = f["r_veg_sky"] * h
    f["x_hirel_h"] = f["v_hi_rel"] * h
    f["x_crown_h"] = f["r_crown_sky"] * h
    return f
