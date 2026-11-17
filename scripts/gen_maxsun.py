#!/usr/bin/env python3
"""Write site/maxsun.csv from the current pw horizon model (2 deg south / 7 deg elsewhere).

Lines 1-365 (z=0..364) are a non-leap year (default 2026) so date('z') matches that
calendar; line 366 (z=365) is 31 Dec of the previous leap year. Daily values are
rounded to 2 decimal hours. Monthly LTA totals in WxDefinition.php need a matching
edit if you regenerate this file.
"""
from __future__ import annotations

import argparse
import sys
from datetime import date, timedelta
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT / "site"))
from sun_model.solar import sun_times_utc  # noqa: E402
from sun_model.classify import horizon  # noqa: E402
from sun_model.rules import daylight_grid  # noqa: E402
from sun_model.localtime import local_day_bounds_utc  # noqa: E402


def pw_hours(d: date) -> float:
    t0, t1 = local_day_bounds_utc(d)
    sr, ss = sun_times_utc(t0, t1)
    grid = daylight_grid(sr, ss)
    return sum(m.w for m in grid if m.el >= horizon(m.az)) / 60.0


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--year", type=int, default=2026, help="Non-leap year for z=0..364")
    ap.add_argument("-o", "--output", type=Path, default=ROOT / "site" / "maxsun.csv")
    args = ap.parse_args()
    y = args.year
    if y % 4 == 0 and (y % 100 != 0 or y % 400 == 0):
        sys.exit("year must be non-leap (date('z') convention)")
    vals = []
    d = date(y, 1, 1)
    while d <= date(y, 12, 31):
        vals.append(round(pw_hours(d) + 1e-12, 2))
        d += timedelta(days=1)
    leap_dec31 = date(y - 1, 12, 31) if (y - 1) % 4 == 0 else date(y - 2, 12, 31)
    # 2026 -> 2024-12-31
    if (leap_dec31.year % 4 != 0) or (leap_dec31.year % 100 == 0 and leap_dec31.year % 400 != 0):
        # find previous leap year
        ly = y - 1
        while not (ly % 4 == 0 and (ly % 100 != 0 or ly % 400 == 0)):
            ly -= 1
        leap_dec31 = date(ly, 12, 31)
    vals.append(round(pw_hours(leap_dec31) + 1e-12, 2))
    args.output.write_text("\n".join("%.2f" % v for v in vals) + "\n")
    print("wrote %s (%d lines); z365 from %s" % (args.output, len(vals), leap_dec31))


if __name__ == "__main__":
    main()
