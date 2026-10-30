#!/usr/bin/env python3
"""Backfill datm sunhr with the webcam model where the existing value was automated. Dry run unless --apply.

A day is replaced only if all of these hold:
  * its datm row is automated, never hand-edited:
      auto      comms field is 'blr' (never saved in datamod) and sunhr equals the old classifier's posted value
                (sun/YYYYMMDD.json sun_mins, as sun_post_saver.php rounded it);
      default0  comms is 'blr', no old-classifier post for the day and sunhr is 0 (the 06:03 "never arrived" default);
      reviewed  (only with --include-reviewed) comms was changed in datamod but sunhr still equals the posted value;
    any other row (sunhr typed in datamod, or no matching automated source) is never touched;
  * webcam frames every 5 min or better for the day (local camchive, else the Spaces cam archive), and the model's
    possible-sun coverage is >= 90% (no partial / scaled total).
Only the sunhr field changes; the rest of each line is kept byte for byte. --apply re-reads each file under an
exclusive lock, re-checks every line it is about to change, and writes a backup copy first.

  ~/sunenv/bin/python scripts/sun_backfill.py                       # dry run, 2026-04-05 .. yesterday
  ~/sunenv/bin/python scripts/sun_backfill.py --from 2026-07-01 --apply
"""
import argparse
import csv
import fcntl
import http.client
import json
import os
import shutil
import sys
import urllib.error
import urllib.request
from concurrent.futures import ProcessPoolExecutor, ThreadPoolExecutor
from datetime import date, datetime, timedelta, timezone
from decimal import ROUND_HALF_UP, Decimal

HERE = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, os.path.join(HERE, "..", "site"))
os.environ.setdefault("OPENBLAS_NUM_THREADS", "1")

from sun_model import classify                                         # noqa: E402
from sun_model.localtime import local_day_bounds_utc, utc_to_local     # noqa: E402
from sun_model.solar import sun_times_utc                              # noqa: E402

SPACES = "https://ben-nw3-rwc.lon1.digitaloceanspaces.com/camchive/hik"
MAX_CADENCE_S = 300
MIN_COVERAGE = 0.9


def half_up(x, places=1):
    return float(Decimal(repr(x)).quantize(Decimal(1).scaleb(-places), rounding=ROUND_HALF_UP))


def num(s):
    try:
        return float(s)
    except ValueError:
        return None


def load_datm(root, year):
    path = os.path.join(root, "datm%d.csv" % year)
    with open(path, newline="") as fh:
        lines = fh.read().splitlines(keepends=True)
    return path, lines


def old_post(root, d):
    f = os.path.join(root, "sun", d.strftime("%Y%m%d") + ".json")
    if not os.path.exists(f):
        return None
    with open(f) as fh:
        j = json.load(fh)
    return int(float(j["sun_mins"])) / 60.0          # sun_post_saver.php: round(intval(sun_mins) / 60, 1)


def classify_row(fields, old):
    """('auto' | 'default0' | 'reviewed' | 'manual', reason)."""
    if len(fields) < 9:
        return "manual", "short row"
    sunhr, comms = num(fields[0].strip('"')), fields[8].strip('"')
    if sunhr is None:
        return "manual", "sunhr blank / not numeric"
    matches = old is not None and abs(sunhr - old) <= 0.05 + 1e-9
    if comms == "blr":
        if matches:
            return "auto", "old classifier value, not reviewed"
        if old is None and sunhr == 0:
            return "default0", "no old post; 06:03 default 0"
        return "manual", "differs from old post"
    if matches:
        return "reviewed", "reviewed in datamod, sunhr left at the old value"
    return "manual", "hand-edited"


def fetch_day(d, work, spaces, threads):
    """Download the 5-min daylight frames of one local day from the cam archive. Returns the frames root."""
    t0, t1 = local_day_bounds_utc(d)
    sr, ss = sun_times_utc(t0, t1)
    root = os.path.join(work, "frames")
    day_dir = os.path.join(root, d.strftime("%Y/%m/%d"))
    os.makedirs(day_dir, exist_ok=True)
    if not sr or not ss:
        return root
    a, b = utc_to_local(sr - timedelta(minutes=20)), utc_to_local(ss + timedelta(minutes=20))
    t = a.replace(minute=a.minute - a.minute % 5, second=0, microsecond=0)
    names = []
    while t <= b:
        if t.date() == d:
            names.append(t.strftime("%H%M") + "hik.jpg")
        t += timedelta(minutes=5)

    def get(n):
        dst = os.path.join(day_dir, n)
        if os.path.exists(dst):
            return
        url = "%s/%s/%s" % (spaces, d.strftime("%Y/%m/%d"), n)
        for attempt in range(3):
            try:
                with urllib.request.urlopen(url, timeout=30) as r:
                    data = r.read()
                break
            except urllib.error.HTTPError:
                return                               # not in the archive: the model treats the minute as missing
            except (OSError, http.client.HTTPException):
                if attempt == 2:
                    print("  download failed: " + url, file=sys.stderr)
                    return
        with open(dst + ".part", "wb") as fh:
            fh.write(data)
        os.rename(dst + ".part", dst)

    with ThreadPoolExecutor(threads) as ex:
        list(ex.map(get, names))
    return root


def model_day(args_tuple):
    d, frames_local, work, spaces, threads, keep = args_tuple
    local_day = os.path.join(frames_local, d.strftime("%Y/%m/%d")) if frames_local else None
    if local_day and os.path.isdir(local_day) and len(os.listdir(local_day)) >= 100:
        frames, src = frames_local, "local"
    else:
        frames, src = fetch_day(d, work, spaces, threads), "spaces"
    info, _ = classify.run(d, frames, os.path.join(work, "sunlog"))
    if src == "spaces" and not keep:
        shutil.rmtree(os.path.join(frames, d.strftime("%Y/%m/%d")), ignore_errors=True)
    return d, src, info


def main():
    ap = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    ap.add_argument("--root", default="/var/www/html", help="docroot with datmYYYY.csv and sun/*.json")
    ap.add_argument("--frames", default=classify.FRAMES_DIR, help="local camchive/hik ('' to always use Spaces)")
    ap.add_argument("--spaces", default=SPACES)
    ap.add_argument("--from", dest="start", default="2026-04-05", help="first day with 5-min frames in the archive")
    ap.add_argument("--to", dest="end", default=None, help="last day (default: yesterday)")
    ap.add_argument("--include-reviewed", action="store_true", help="also replace reviewed-but-unchanged days")
    ap.add_argument("--work", default=os.path.expanduser("~/sun_backfill"), help="scratch dir (sunlog cache, frames)")
    ap.add_argument("--jobs", type=int, default=1)
    ap.add_argument("--threads", type=int, default=8, help="parallel downloads per day")
    ap.add_argument("--keep-frames", action="store_true")
    ap.add_argument("--apply", action="store_true")
    ap.add_argument("--backup-dir", default=os.path.expanduser("~/nw3-datm-backup"))
    a = ap.parse_args()

    start = date.fromisoformat(a.start)
    today = utc_to_local(datetime.now(timezone.utc).replace(tzinfo=None)).date()
    end = date.fromisoformat(a.end) if a.end else today - timedelta(days=1)
    end = min(end, today - timedelta(days=1))
    if a.apply:
        hm = utc_to_local(datetime.now(timezone.utc).replace(tzinfo=None)).strftime("%H%M")
        if hm < "0100" or "0555" <= hm <= "0615":
            sys.exit("refusing --apply during the nightly datm writes (00:00-01:00, 05:55-06:15 London)")

    files, plan = {}, []
    for year in range(start.year, end.year + 1):
        files[year] = load_datm(a.root, year)
    for i in range((end - start).days + 1):
        d = start + timedelta(days=i)
        path, lines = files[d.year]
        idx = d.timetuple().tm_yday                  # line 0 is the header
        if idx >= len(lines):
            continue
        fields = lines[idx].rstrip("\r\n").split(",")
        old = old_post(a.root, d)
        kind, why = classify_row(fields, old)
        plan.append(dict(d=d, idx=idx, line=lines[idx], cur=fields[0], old=old, kind=kind, why=why))

    wanted = {"auto", "default0"} | ({"reviewed"} if a.include_reviewed else set())
    todo = [p for p in plan if p["kind"] in wanted]
    counts = {}
    for p in plan:
        counts[p["kind"]] = counts.get(p["kind"], 0) + 1
    print("days %s .. %s: %s; candidates %d" % (start, end, counts, len(todo)), flush=True)

    os.makedirs(a.work, exist_ok=True)
    jobs = [(p["d"], a.frames, a.work, a.spaces, a.threads, a.keep_frames) for p in todo]
    res = {}
    with ProcessPoolExecutor(max(1, a.jobs)) as ex:
        for d, src, info in ex.map(model_day, jobs):
            res[d] = (src, info)
            print("  modelled %s (%s, %d frames)" % (d, src, info["frames"]), file=sys.stderr, flush=True)

    rows, changes = [], []
    print("date,kind,datm_now,old_post,new,diff,frames,cadence_s,coverage,src,action")
    for p in todo:
        src, info = res[p["d"]]
        new = info.get("sun_h")
        ok = (new is not None and info.get("cadence_s", 1e9) <= MAX_CADENCE_S
              and info.get("coverage", 0) >= MIN_COVERAGE and not info.get("partial") and not info.get("scaled"))
        newr = half_up(new, 1) if new is not None else None
        cur = num(p["cur"].strip('"'))
        action = "replace" if ok and newr is not None and abs(newr - cur) > 1e-9 else ("same" if ok else "skip")
        if action == "skip":
            why = "cadence %ss" % int(info.get("cadence_s", 0)) if info.get("cadence_s", 0) > MAX_CADENCE_S \
                else "coverage %.2f" % info.get("coverage", 0)
            action = "skip(%s)" % why
        diff = "" if newr is None or cur is None else "%+.1f" % (newr - cur)
        r = [p["d"].isoformat(), p["kind"], p["cur"], "" if p["old"] is None else "%.2f" % p["old"],
             "" if newr is None else "%.1f" % newr, diff, info["frames"], int(info.get("cadence_s", 0)),
             info.get("coverage"), src, action]
        rows.append(r)
        print(",".join(map(str, r)))
        if action == "replace":
            changes.append((p, newr))

    print("replace %d of %d candidates" % (len(changes), len(todo)), file=sys.stderr)
    report = os.path.join(a.work, "backfill_%s.csv" % datetime.now().strftime("%Y%m%d-%H%M%S"))
    with open(report, "w", newline="") as fh:
        w = csv.writer(fh)
        w.writerow(["date", "kind", "datm_now", "old_post", "new", "diff", "frames", "cadence_s", "coverage", "src",
                    "action"])
        w.writerows(rows)
    print("report: " + report, file=sys.stderr)
    if not a.apply:
        print("dry run; add --apply to write", file=sys.stderr)
        return
    for year in sorted({p["d"].year for p, _ in changes}):
        apply_year(files[year][0], [(p, v) for p, v in changes if p["d"].year == year], a.backup_dir)


def apply_year(path, changes, backup_dir):
    os.makedirs(backup_dir, mode=0o700, exist_ok=True)
    with open(path, "r+", newline="") as fh:
        fcntl.flock(fh, fcntl.LOCK_EX)
        raw = fh.read()
        backup = os.path.join(backup_dir, os.path.basename(path) + "." + datetime.now().strftime("%Y%m%d-%H%M%S"))
        with open(backup, "w", newline="") as b:
            b.write(raw)
        lines = raw.splitlines(keepends=True)
        n = 0
        for p, v in changes:
            line = lines[p["idx"]]
            if line != p["line"]:
                print("  %s changed since the plan; left as is" % p["d"], file=sys.stderr)
                continue
            first = line.split(",", 1)[0]
            lines[p["idx"]] = ("%.1f" % v).rstrip("0").rstrip(".") if v != int(v) else str(int(v))
            lines[p["idx"]] += line[len(first):]
            n += 1
        fh.seek(0)
        fh.truncate()
        fh.write("".join(lines))
        fh.flush()
        fcntl.flock(fh, fcntl.LOCK_UN)
    print("%s: %d rows written; backup %s" % (path, n, backup), file=sys.stderr)


if __name__ == "__main__":
    main()
