#!/usr/bin/env python3
"""Rough per-minute sunshine timeline for past days, from archived webcam frames, for the daily profile only.

Runs the current sun model (site/sun_model, the one that writes sunlog/) over archived frames of each day and writes
one line per day to <out>/YYYY.csv. It never writes datm, sunlog/ or anything else: the official daily totals stay
as they are, and DayProfile scales each day's timeline to the official datm sunhr at build time (like rain).

  ~/sunenv/bin/python scripts/sun_history.py --from 2021-09-07 --to 2025-10-31 --out /tmp/sunlog_model
  ~/sunenv/bin/python scripts/sun_history.py --cache-dir ~/sun_audit/sunlog/cache ...   # reuse per-frame scores

Frames: the local camchive when it has the day, else the Spaces cam archive (downloaded to a temp dir and deleted
after each day; at most one day of frames on disk). Archive cadence: 30 min to mid-2020, 15 min 2020-2023,
7.5-10 min 2024-25, 5 min since Apr 2026. Per-frame scores are cached (--cache-dir), so a rerun does not refetch.

Line format (one per day, sorted):
  YYYYMMDD,first_hhmm,w_first,w_last,cadence_s,coverage,model_h,TOKENS
TOKENS: 4 characters per daylight minute from first_hhmm (UK clock time): state ('1' sunny, '0' not, '-' unknown),
model probability x99 as two digits ('--' if none), then '.' if the sun is above the local horizon cut-off (could
shine) or 'h' if not. Every minute's daylight weight is 1 except the first and last (w_first, w_last).
"""
import argparse
import http.client
import json
import os
import shutil
import sys
import tempfile
import urllib.error
import urllib.request
from concurrent.futures import ThreadPoolExecutor
from datetime import date, datetime, timedelta

HERE = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, os.path.join(HERE, "..", "site"))
os.environ.setdefault("OPENBLAS_NUM_THREADS", "1")

from sun_model import classify                                         # noqa: E402
from sun_model.localtime import local_day_bounds_utc, utc_to_local     # noqa: E402
from sun_model.solar import sun_times_utc                              # noqa: E402

SPACES = "https://ben-nw3-rwc.lon1.digitaloceanspaces.com/camchive/hik"


def fetch_day(d, root, spaces, threads):
    """Download a day's daylight frames (every 5 min tried; missing ones are skipped) into root/Y/m/d."""
    t0, t1 = local_day_bounds_utc(d)
    sr, ss = sun_times_utc(t0, t1)
    day_dir = os.path.join(root, d.strftime("%Y/%m/%d"))
    os.makedirs(day_dir, exist_ok=True)
    if not sr or not ss:
        return
    a, b = utc_to_local(sr - timedelta(minutes=20)), utc_to_local(ss + timedelta(minutes=20))
    t = a.replace(minute=a.minute - a.minute % 5, second=0, microsecond=0)
    names = []
    while t <= b:
        if t.date() == d:
            names.append(t.strftime("%H%M") + "hik.jpg")
        t += timedelta(minutes=5)

    def get(n):
        url = "%s/%s/%s" % (spaces, d.strftime("%Y/%m/%d"), n)
        for attempt in range(3):
            try:
                with urllib.request.urlopen(url, timeout=30) as r:
                    data = r.read()
                break
            except urllib.error.HTTPError:
                return
            except (OSError, http.client.HTTPException):
                if attempt == 2:
                    return
        if not data:                                    # the archive has some 0-byte frames
            return
        with open(os.path.join(day_dir, n), "wb") as fh:
            fh.write(data)

    with ThreadPoolExecutor(threads) as ex:
        list(ex.map(get, names))


def encode(d, grid, info):
    rows = [m for m in grid if utc_to_local(m.t).date() == d]
    if not rows:
        return None
    toks = []
    for m in rows:
        st = "-" if m.value is None else str(int(m.value))
        p = "--" if m.p is None else "%02d" % max(0, min(99, int(round(m.p * 99))))
        toks.append(st + p + ("." if classify.possible_w(m) > 0 else "h"))
    first = utc_to_local(rows[0].t).strftime("%H%M")
    return "%s,%s,%.3f,%.3f,%d,%.3f,%.2f,%s" % (d.strftime("%Y%m%d"), first, rows[0].w, rows[-1].w,
                                               int(info["cadence_s"]), info["coverage"], info["measured_h"], "".join(toks))


def model_day(d, frames_local, cache_dir, spaces, threads):
    key = d.strftime("%Y%m%d")
    work = tempfile.mkdtemp(prefix="sunhist_")
    try:
        out = os.path.join(work, "out")
        os.makedirs(os.path.join(out, "cache"))
        cached = os.path.join(cache_dir, key + ".json") if cache_dir else None
        if cached and os.path.exists(cached):
            shutil.copyfile(cached, os.path.join(out, "cache", key + ".json"))
            frames = os.path.join(work, "frames")              # empty: classify reuses the cached scores
            os.makedirs(frames)
        elif frames_local and os.path.isdir(os.path.join(frames_local, d.strftime("%Y/%m/%d"))):
            frames = frames_local
        else:
            frames = os.path.join(work, "frames")
            fetch_day(d, frames, spaces, threads)
        info, grid = classify.run(d, frames, out, dry_run=False)
        # classify.run only lists frames on disk; with a cache and no frames it still has every scored frame
        if cache_dir:
            os.makedirs(cache_dir, exist_ok=True)
            shutil.copyfile(os.path.join(out, "cache", key + ".json"), os.path.join(cache_dir, key + ".json"))
        return encode(d, grid, info)
    finally:
        shutil.rmtree(work, ignore_errors=True)


def main():
    ap = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    ap.add_argument("--from", dest="start", required=True)
    ap.add_argument("--to", dest="end", required=True)
    ap.add_argument("--out", required=True, help="output dir for YYYY.csv (not the docroot sunlog/)")
    ap.add_argument("--frames", default=classify.FRAMES_DIR, help="local camchive/hik ('' for Spaces only)")
    ap.add_argument("--cache-dir", default=None, help="per-frame score cache (reused and extended)")
    ap.add_argument("--spaces", default=SPACES)
    ap.add_argument("--threads", type=int, default=6)
    a = ap.parse_args()
    if os.path.realpath(a.out).rstrip("/").endswith("/sunlog"):
        sys.exit("refusing to write into an official sunlog dir")
    start, end = date.fromisoformat(a.start), date.fromisoformat(a.end)
    os.makedirs(a.out, exist_ok=True)
    lines = {}
    for y in range(start.year, end.year + 1):
        f = os.path.join(a.out, "%d.csv" % y)
        if os.path.exists(f):
            for ln in open(f):
                if ln[:8].isdigit():
                    lines[ln[:8]] = ln.rstrip("\n")
    d = start
    while d <= end:
        try:
            ln = model_day(d, a.frames, a.cache_dir, a.spaces, a.threads)
        except Exception as e:                          # one bad day must not stop the run
            print("%s failed: %r" % (d, e), file=sys.stderr)
            ln = None
        if ln:
            lines[d.strftime("%Y%m%d")] = ln
        print(d, "ok" if ln else "no data", file=sys.stderr, flush=True)
        d += timedelta(days=1)
    for y in range(start.year, end.year + 1):
        keys = sorted(k for k in lines if k.startswith(str(y)))
        if not keys:
            continue
        f = os.path.join(a.out, "%d.csv" % y)
        with open(f + ".tmp", "w") as fh:
            fh.write("# model-estimated webcam sunshine timeline, profile use only; see scripts/sun_history.py\n")
            for k in keys:
                fh.write(lines[k] + "\n")
        os.rename(f + ".tmp", f)


if __name__ == "__main__":
    main()
