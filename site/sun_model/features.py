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
