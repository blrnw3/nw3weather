"""Classify one UK-local day of nw3 camchive frames into per-minute sunshine, written as a plain logfile.

  ~/sunenv/bin/python -m sun_model.classify                      # today (UK local)
  ~/sunenv/bin/python -m sun_model.classify -d 2026-10-15 --out /tmp/sunlog --dry-run

Inputs : <frames>/YYYY/MM/DD/HHMMhik.jpg  (UK local time, as written by cron_cam.php)
Outputs: <out>/YYYYMMDD.csv   one row per daylight minute: hhmm_local,value,p,ps,w,flags  (value 1/0/blank)
         <out>/latest.json    today's running total, coverage and the current state, for cron_main/live pages
         <out>/cache/YYYYMMDD.json  per-frame probabilities, so each run only classifies new frames
Runs every 5-15 min under flock/nice; one frame in memory at a time (~50 MB RSS, ~105 ms/frame on the server).
Nothing here touches datm or the historical sunhr values.
"""
import argparse
import gc
import hashlib
import json
import os
import sys
from datetime import date, datetime, timedelta

HERE = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, os.path.dirname(HERE))

from sun_model.features import feats, load_small                      # noqa: E402
from sun_model.localtime import local_day_bounds_utc, local_to_utc, utc_to_local   # noqa: E402
from sun_model.model import LrModel                                    # noqa: E402
from sun_model.rules import GLITCH_PINK, Frame, classify_day, summary  # noqa: E402
from sun_model.solar import solar_pos, sun_times_utc                              # noqa: E402

FRAMES_DIR = "/mnt/webcam/html/camchive/hik"
OUT_DIR = "/var/www/html/sunlog"
CACHE_SCHEMA = 1
FULL_COV, MIN_COV = 0.9, 0.5
MAIN_MODEL = os.environ.get("NW3_SUN_MODEL", "rwc_sunny_lr_v1")       # placeholder until nw3_sunny_lr_v1 is trained
SKY_MODEL = os.environ.get("NW3_SKY_MODEL", "rwc_skyclear_lr_v1")


def list_frames(frames_dir, d):
    """{naive UTC datetime: path} for one local day."""
    day_dir = os.path.join(frames_dir, d.strftime("%Y/%m/%d"))
    out = {}
    try:
        names = os.listdir(day_dir)
    except OSError:
        return out
    for n in names:
        if not n.endswith("hik.jpg") or len(n) != 11:
            continue
        try:
            lt = datetime.strptime(d.strftime("%Y%m%d") + n[:4], "%Y%m%d%H%M")
        except ValueError:
            continue
        out[local_to_utc(lt)] = os.path.join(day_dir, n)
    return out


def classify_frames(frames, cache, main, sky):
    prev_sig = None
    for t in sorted(frames):
        key = t.strftime("%Y%m%d%H%M")
        path = frames[t]
        if key in cache and cache[key].get("v") == main.version:
            prev_sig = cache[key].get("sig")
            continue
        try:
            with open(path, "rb") as fh:
                sig = hashlib.md5(fh.read()).hexdigest()
            f = feats(load_small(path))
        except Exception as e:          # truncated / corrupt file: leave it out (minute becomes 'missing')
            print("bad frame %s: %s" % (path, e))
            continue
        f["el"], f["az"] = solar_pos(t)
        cache[key] = dict(v=main.version, p=round(main.prob(f), 4), ps=round(sky.prob(f), 4),
                          smean=round(f["Smean"], 4), pink=round(f["pink_bot"], 4), sig=sig, stale=(sig == prev_sig))
        prev_sig = sig
        gc.collect()
    return cache


def to_frames(cache):
    out = []
    for key, c in cache.items():
        t = datetime.strptime(key, "%Y%m%d%H%M")
        out.append(Frame(t, c["p"], c["ps"], c["smean"], stale=c.get("stale", False),
                         glitch=c.get("pink", 0) > GLITCH_PINK, pink=c.get("pink")))
    return out


def rollup(grid, now_utc):
    g = [m for m in grid if now_utc is None or m.t < now_utc]
    s = summary(g)
    cov = s["coverage"]
    hrs = s["sun_min"] / 60.0
    if s["daylight_min"] <= 0 or cov < MIN_COV:
        return dict(s, sun_h=None, scaled=False)
    scaled = cov < FULL_COV
    return dict(s, sun_h=round(hrs / cov if scaled else hrs, 2), scaled=scaled)


def write_log(path, grid):
    tmp = path + ".tmp"
    with open(tmp, "w") as fh:
        fh.write("hhmm,value,p,ps,w,flags\n")
        for m in grid:
            lt = utc_to_local(m.t)
            fh.write("%s,%s,%s,%s,%.3f,%s\n" % (lt.strftime("%H%M"), "" if m.value is None else m.value,
                                                "" if m.p is None else "%.3f" % m.p, "" if m.ps is None else "%.3f" % m.ps,
                                                m.w, "|".join(sorted(m.flags))))
    os.rename(tmp, path)


def atomic_json(path, obj):
    tmp = path + ".tmp"
    with open(tmp, "w") as fh:
        json.dump(obj, fh, sort_keys=True)
    os.rename(tmp, path)


def run(d, frames_dir, out_dir, dry_run=False, now_utc=None):
    t0, t1 = local_day_bounds_utc(d)
    sr, ss = sun_times_utc(t0, t1)
    frames = list_frames(frames_dir, d)
    cache_path = os.path.join(out_dir, "cache", d.strftime("%Y%m%d") + ".json")
    cache = {}
    if os.path.exists(cache_path):
        with open(cache_path) as fh:
            c = json.load(fh)
        if c.get("schema") == CACHE_SCHEMA:
            cache = c["frames"]
    main, sky = LrModel.load(MAIN_MODEL), LrModel.load(SKY_MODEL)
    cache = classify_frames(frames, cache, main, sky)
    # cadence decides the nearest-frame tolerance: 1-min live days 5 min, thinned archive days half the spacing
    ts = sorted(frames)
    gaps = sorted((b - a).total_seconds() for a, b in zip(ts, ts[1:]))
    cadence = gaps[len(gaps) // 2] if gaps else 60
    tol = max(300, cadence / 2)
    grid = classify_day(to_frames(cache), sr, ss, tolerance_s=tol) if sr and ss else []
    tot = rollup(grid, now_utc)
    latest = None
    done = [m for m in grid if m.value is not None and (now_utc is None or m.t <= now_utc)]
    if done:
        m = done[-1]
        latest = dict(hhmm=utc_to_local(m.t).strftime("%H%M"), value=m.value, flags=sorted(m.flags))
    info = dict(date=d.isoformat(), model=main.version, sky_model=sky.version, cadence_s=cadence, frames=len(frames),
                sunrise=utc_to_local(sr).strftime("%H:%M") if sr else None,
                sunset=utc_to_local(ss).strftime("%H:%M") if ss else None, latest=latest, **tot)
    if not dry_run:
        os.makedirs(os.path.join(out_dir, "cache"), exist_ok=True)
        atomic_json(cache_path, dict(schema=CACHE_SCHEMA, frames=cache))
        write_log(os.path.join(out_dir, d.strftime("%Y%m%d") + ".csv"), grid)
        if now_utc is not None:
            atomic_json(os.path.join(out_dir, "latest.json"), info)
    return info, grid


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("-d", "--date", help="UK local date YYYY-MM-DD (default: today)")
    ap.add_argument("--frames", default=FRAMES_DIR)
    ap.add_argument("--out", default=OUT_DIR)
    ap.add_argument("--dry-run", action="store_true")
    a = ap.parse_args()
    now = datetime.utcnow()
    d = datetime.strptime(a.date, "%Y-%m-%d").date() if a.date else utc_to_local(now).date()
    is_today = d == utc_to_local(now).date()
    info, _ = run(d, a.frames, a.out, a.dry_run, now if is_today else None)
    print(json.dumps(info, sort_keys=True))


if __name__ == "__main__":
    main()
