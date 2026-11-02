# Daily profile (wxdayprofile.php)

Average weather at each 5 minutes of the (UK clock) day, for all years, one year, one calendar month across all
years, or one year+month. Linked from Climate (wxaverages.php).

## Pieces

| File | Role |
|---|---|
| `site/DayProfile.php` | Quality rules, nightly build, cache readers |
| `site/warm_day_profile.php` | CLI: incremental (default), `full`, `day YYYY-MM-DD`, `audit [FROM [TO]]` |
| `site/cron_main.php` | Runs the incremental build at 03:47 (nice 15, background) |
| `site/wxdayprofile.php`, `site/dayprofile.js` | Page and chart (Highcharts) |
| `site/dayprofiledata.php` | JSON for in-page switching (no log access) |

Cache: `CACHE_ROOT/dayprofile/` (about 13 MB): `state.ser` (per-month sums, counts and source fingerprint),
`p/<var>/<key>.json` (all, yYYYY, mMM, yYYYYmMM) and `meta.json`. Pages only read these files.

Each month is rescanned when its fingerprint changes: code version, any log/sunlog mtime+size, the day's notes, or the
day's checked rain total. A nightly run takes about 1-2 s; a full rebuild about 3 minutes (68 MB). After deploying a
code change run `php warm_day_profile.php full` once (or wait for the next night, which does the same).

## Quality rules (from 2011)

Whole days are left out for a variable when:

- a listed period applies (`DayProfile::$RANGES`: sensor faults, drift, substitute sources, sunshine hold);
- the station notes (datm extra/issues) report a fault, data loss, a substitute source or reconstruction
  (wind/T-H/rain/pressure wording targets that group; anything else applies to all);
- it is a clock-change day;
- 2+ hours look externally filled (wind fields changing in 15 or fewer minutes of the hour);
- fewer than 1,008 clean minutes remain (rain: 1,380, as its total must be complete);
- wind is calm for 80%+ of the day (stalled anemometer).

Minute by minute: out-of-range values; repeated whole rows (5+ while wind moves, 30+ any time); low-cadence hours;
T/H flatlines (120 min by day, 240 at night); pressure flatlines (6 h, 12 h when it logged in coarse steps); spikes.

Rain: each day's minute-log rain is scaled to the checked daily total (so hand corrections carry through). Days whose
log needs a catch-up jump after a gap, a scale over 2 with a gap, or have rain logged as 0 against more than 0.2 mm
are left out; 0.1-0.2 mm trace days count as dry. Wet % uses tip spells (30 min gap, starting 15 min before the
first tip) from Apr 2014, when the gauge resolution changed.

Sunshine: from 23 May 2025 the live sunlog (night counts as not sunny); days with under 90% of the possible sunshine
classified are left out, and Nov 2025 - 4 Apr 2026 is held pending review.

Before that, from 7 Sep 2021 (the camera has been stable since then), sunshine is **model-estimated**: the same sun
model run over the archived webcam frames (`scripts/sun_history.py`, written to `sunlog_model/YYYY.csv`, never to
datm or `sunlog/`). Each day is scaled to its official datm sunhr like rain (over-counts scale the sunny minutes
down; under-counts add sun to minutes the sun could reach, in proportion to the model's probability). A day is left
out when frames cover under 90% of the possible sunshine (e.g. 2022 summer evenings, where the archive has empty
frames) or when |model - official| > max(1.5 h, 50% of the official total). The page marks these years with *.

Archive cadence: 30 min to mid-2020, 15 min 2020-2023, 7.5-10 min 2024-25, 5 min since Apr 2026. On 2018-06..2021-09
frames (camera re-aimed several times) the model under-counts by ~1 h a day and only 35-53% of days pass, so those
years are not used (`DayProfile::SUN_MODEL_FROM`). Thinning the 2025 frames to 30 min changes a monthly profile by
about 1.4 % points RMS.

Rebuild the timeline (about 75 s from cached frame scores; about 25 s a day when frames must be downloaded):

    ~/sunenv/bin/python scripts/sun_history.py --from 2018-06-20 --to 2026-04-04 --out /var/www/html/sunlog_model \
        --frames '' --cache-dir ~/sun_history_cache

Use `php warm_day_profile.php day YYYY-MM-DD` to see why a day was used or not.
