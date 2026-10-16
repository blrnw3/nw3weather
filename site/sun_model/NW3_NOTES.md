# nw3 webcam sunshine (port of rwcweather rwcwx/sun)

Status: M1 scaffold. Models in `models/rwc_*` are rwcweather's (Emerald Hills camera) and are placeholders only;
they must be replaced by `nw3_sunny_lr_v1` / `nw3_skyclear_lr_v1` trained on labelled nw3 frames (M2).

Differences from rwc
- Station 51.556 N, 0.177 W; UK local filenames (`camchive/hik/Y/m/d/HHMMhik.jpg`), DST handled in `localtime.py`.
- Camera faces ~NNE (~100 deg FOV): the sun is in frame only on summer mornings (az ~45-70). Elsewhere the model relies on
  foreground lighting (tree canopy, Heath) and sky colour.
- Winter noon elevation is ~15 deg, so rwc's LOW_EL=8 / OBSTRUCT_EL=16 cover most of a Dec day: re-tune on nw3 labels,
  likely with an azimuth horizon mask.
- Camera eras: stable since ~2021-09-07 (re-aims before that); train on 2021-09+ only.
- Archive thinning (cron_cam.php + ~/cam_cleanup.sh): 1-min for 3 days, 5-min to 200 days, 7.5-min (8/h) to 700 days,
  15-min to 2000 days, 30-min after. Live job reads 1-min frames; backfill uses the coarser cadence (tolerance = spacing/2).
- Output is a logfile per day (`sunlog/YYYYMMDD.csv`) + `sunlog/latest.json`; no DB. datm/sunhr history untouched.
- Possible sunshine for % keeps the nw3 `sunmax` precedent (display side), not astronomical daylight.

Server: py3.6.8, `~/sunenv` (pip 21.3.1, numpy 1.19.5, Pillow 8.4.0); ~105 ms/frame, ~50 MB peak RSS.

## M2 results (2026-10-16)
- Labels: 919 frames 2021-09+ (348 sunny / 386 not / 185 unsure), agent-labelled per rwc guide; Ben rule: clear sky with
  sun behind the house but far trees lit = sunny. Lens-fog/blur frames (Oct 2023, Feb 2026) and frozen exposures = unsure.
- Models: `nw3_sunny_lr_v1` (734 decided frames, C=0.1, class-balanced) and `nw3_skyclear_lr_v1` (313 frames, el<15).
  Day-grouped 5-fold CV x3: balanced accuracy 0.972 (old ResNet on the same frames 0.871); low-sun 0.97 (ResNet 0.83).
- Tuning vs hand-reviewed datm sunhr: decision threshold 0.85 (logit shift), horizon 2 deg for az 110-250 and 7 deg
  elsewhere, fitted on 12 near-100%-of-sunmax winter days + half of the held-out days.
- Frozen-camera gate: the overlay timestamp makes every JPEG unique, so stale = grid features unchanged (< 2e-4).
- Daily hours vs datm (62 held-out days, not in the labelled set): MAE 0.83 h, bias +0.25 h (5-7.5 min cadence 0.69 h,
  15 min 0.96 h). Clear winter days MAE 0.09 h. On the 17 days that also have old-model JSON: new 0.47 h / -0.18 vs old
  0.69 h / -0.22.
