# Local development for SiteV3

Run the production weather site locally against **real prod data**, with no
source changes. The container's docroot is `/var/www/html` — exactly what
`basics.php` / `v5/UtilsAndConsts.php` hardcode — so `ROOT` just works.

```
  your editor ──▶ ./SiteV3 ──bind mount──▶ container:/var/www/html ──▶ Apache :8080
                       ▲
  prod (SSH) ──rsync pull (one-way)──┘   clientraw.txt, dat*.csv, serialised_*, *Tags.php, graphs
```

## Prerequisites

- **Colima** as the Docker engine (preferred over Docker Desktop on macOS),
  plus the Docker CLI and Compose plugin
- SSH key access to the prod server (same box you push to `/var/www`)

Install via Homebrew if needed:

```bash
brew install colima docker docker-compose
```

Colima does not auto-start after reboot. Either run `colima start` each
session, or `brew services start colima` for persistence.

## One-time setup

1. **Start the Docker engine:**

   ```bash
   colima start
   ```

   The `web` service is pinned to `platform: linux/amd64` because
   `php:5.4-apache` has no arm64 build. Colima's QEMU emulation handles that
   on Apple Silicon. If pulls of the old image fail with a schema-v1 /
   containerd error, disable the containerd snapshotter in
   `~/.colima/default/colima.yaml` and restart:

   ```yaml
   docker:
     features:
       containerd-snapshotter: false
   ```

   ```bash
   colima restart
   ```

2. **SSH target is preconfigured** in `scripts/sync-prod-data.sh`
   (`ben@188.166.156.109` on port `8294`). Override with `PROD_SSH` /
   `PROD_SSH_PORT` env vars if needed. Make sure your key gets you in:

   ```bash
   ssh -p 8294 ben@188.166.156.109 true && echo ok
   ```

3. **PHP version** is pinned to `5.4` in `docker-compose.yml` to match prod
   (5.4.16). The image is Debian jessie (archived) — the Dockerfile handles that.

4. **Pull prod data** (one-shot). `--full` also grabs the JPGraph library and
   `static-images/`, needed for the graph pages:

   ```bash
   ./scripts/sync-prod-data.sh --full
   ```

## Run the site

```bash
colima start          # if the engine isn't already up
docker compose up --build
```

- Site:    http://localhost:8080
- Mailpit: http://localhost:8025  (catches any cron `mail()` — nothing is actually emailed)

Edit files in `./SiteV3` and refresh — changes are live (bind mount). The v5
refactor is at http://localhost:8080/v5/wx3.php etc.

## Keeping data fresh

The site reads `clientraw.txt` for live conditions. To mimic the FTP feed while
you work, run the live loop in a second terminal:

```bash
./scripts/sync-prod-data.sh --loop 20    # re-pull clientraw.txt every 20s
```

Re-run `./scripts/sync-prod-data.sh` any time to refresh the generated files
(daily CSVs, serialised caches, `*Tags.php`, graphs).

## Notes & limitations

- **Sync is one-way (prod → local). It never writes to prod.** Your existing
  deploy workflow (pushing to `/var/www`) is unchanged.
- Synced data files are gitignored (`SiteV3/.gitignore`) so they won't be committed.
- **Crons are not run locally** — for page work the synced generated files are
  enough. If you later need to test cron logic, run a script by hand inside the
  container, e.g. `docker compose exec -w /var/www/html web php cron_main.php`,
  ideally guarded by the `NW3_LOCAL_DEV=1` env var to skip email/external side
  effects.
- Webcam/video archives (`/mnt/...`) are empty stubs locally; cam/timelapse
  pages will show placeholders unless you sync samples.
- `ffmpeg` isn't available on the jessie-based PHP 5.4 image, so
  `cron_cam.php` video generation can't run locally — not needed for pages.
- `wxapp/` (the only MySQL part) is optional: start its DB with
  `docker compose --profile wxapp up`, import `SiteV3/wxapp/EuroWeather.sql`, and
  create `SiteV3/wxapp/config.php`.
