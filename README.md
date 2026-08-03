# nw3weather

A web-based system for the aggregation, management and public visualisation of
data from my weather station in Hampstead NW3, North London.

The back end is entirely written in PHP.

| Path | Role |
| --- | --- |
| `site/` | Current production site |
| `oldSites/` | Archived earlier versions (`sitev0`…`sitev3`, plus `Site_v4`) |
| `LOCALDEV.md` | How to run the site locally against prod data |
| `CUTOVER.md` | Hard-cutover runbook for promoting the current site on the server |

## Runtime layout

Production remains a flat PHP/data docroot for compatibility with Weather
Display FTP uploads and historical code. Only rebuildable outputs are split:

- `cache/v5/` — v5 serializations, summary caches, forecast and AQ state
- `cache/legacy/` — the v3 serialization snapshot and generated `*Tags.php`
- `generated/v5/` — v5 camera/graph assets
- `generated/legacy/` — v3 graphs, roses and camera composites

The public root URLs for generated images are preserved by `.htaccess`.
`cron_main.php`, `cron_cam.php`, and `cron_hikcam.php` handle shared/current-site
work via `cron/bootstrap.php` (v5 stack). Schedule `cron_legacy.php` once per
minute for v3-only caches, graphs, and `oldSites/sitev3/cron_tags.php`. Do not
schedule a separate root `cron_tags.php` entry. Use
`scripts/migrate-runtime-layout.php` (dry-run by default) for the one-time
output move.

Deploy current code with `scripts/sync-code-to-prod.sh --go`; deploy maintained
v3 archive code separately with `scripts/sync-legacy-to-prod.sh --go`.
