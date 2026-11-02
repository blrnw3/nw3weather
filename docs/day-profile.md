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

Sunshine: sunlog minute classes (night counts as not sunny) from 23 May 2025; days with under 90% of daylight
classified are left out, and Nov 2025 - 4 Apr 2026 is held pending review.

Use `php warm_day_profile.php day YYYY-MM-DD` to see why a day was used or not.
