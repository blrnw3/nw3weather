"""Per-minute sunshine rules for nw3 (port of rwcweather rwcwx/sun/rules.py, py3.6, no dataclasses).

nw3 differences (see NW3_NOTES.md): London latitude (winter noon sun only ~15 deg, so LOW_EL / OBSTRUCT_EL must be
re-tuned on labelled nw3 days; values below are rwc's starting points), camera faces ~NNE so the sun is only in frame
on summer mornings, and the camchive cadence is 1 min for 3 days, 5 min to 200 days, then coarser (thinning).
Turn per-frame probabilities into per-minute sunshine for one local day.

Pipeline (PLAN 3.1/3.2), identical to the research evaluation (eval/sunday.py, rule R3):
  1. grid: every UTC minute overlapping [sunrise, sunset]; weight w = overlap fraction (so a cloudless day sums to
     exactly the astronomical daylight length, rwcwx.astronomy convention: sun centre at -0.833 deg).
  2. gates: missing (no frame within +-5 min), stale (frame identical to the previous one), ir (night/IR mode,
     Smean < 0.03 while el > 3), glitch (encoder smear over the foreground: pink_bot > 0.05, or a smear episode,
     see mark_glitch_episodes).
     Gated minutes are unclassified (value NULL).
  3. main model state = p >= 0.5.
  4. low sun (el < 8 deg): carry the nearest confident main-model state (el >= 8, p < .25 or > .75) on the same side
     of solar noon (morning: next one, evening: previous one), vetoed to 0 if sky-clarity prob < 0.3. Flag 'lowsun'.
     Elevations here are the frame's, not the minute's: with 30-min archive snapshots a minute just above 8 deg can
     map to a frame taken below it, whose low-sun "not sunny" must not become the confident state carried to dawn.
     Obstructed sun: a frame with el < OBSTRUCT_EL, p < 0.5 and sky-clarity >= CLEAR_PS is a clear sky with the sun
     behind the trees or eaves (in Feb-Apr the sun rises and sets behind them up to ~15 deg). It is treated as low
     sun (carried, flag 'lowsun') and is never itself a carry source. Fog, marine layer and overcast frames score
     sky-clarity < 0.3 and are unaffected.
  5. median-5 over the classified minutes.
  6. short gaps: a run of unclassified minutes (missing/stale/ir/glitch) of at most MAX_GAP_MIN, with classified
     minutes on both sides, is filled: with that state if both sides agree, else split at the midpoint (earlier
     half takes the left state). Filled minutes keep their gate flag and get 'interp'. Runs touching sunrise or
     sunset (one side only) and longer runs stay unknown. Runs that are entirely glitch, with the same state on both
     sides, may be up to MAX_GLITCH_GAP_MIN: a smear episode lasts until the camera's next keyframe (~13-30 min).
"""
from datetime import datetime, timedelta
from typing import Dict, List, Optional, Set

from sun_model.solar import solar_pos

LOW_EL = 8.0
CARRY_CONF = 0.25
VETO = 0.3
TOLERANCE_S = 300
IR_SMEAN = 0.03
OBSTRUCT_EL = 16.0
CLEAR_PS = 0.85
IR_MIN_EL = 3.0
GLITCH_PINK = 0.05
# Smear episodes (camera H.264 error propagation, typically at dawn): pink_bot ramps up from ~0.005 over 10-30
# consecutive frames, then clears at the next keyframe. Frames well under GLITCH_PINK are already visibly smeared
# and drag the sky-clarity model below the low-sun veto. A run of consecutive frames with pink >= GLITCH_EDGE that
# contains one >= GLITCH_SEED is a glitch episode. On 4.9k research frames every frame >= 0.01 was a real smear.
GLITCH_SEED = 0.01
GLITCH_EDGE = 0.004
EPISODE_STEP_S = 150
MAX_GLITCH_GAP_MIN = 40
# 20 min: longer than the glitch / stale bursts seen in practice (e.g. 13 min on 2026-10-12, 08:00-08:13 PT), but
# shorter than one lost 30-min archive snapshot, so backfill never invents a whole snapshot period, and well under
# the typical length of real sunny/cloudy spells here (median spell ~45 min in the 2026-10 per-minute data).
MAX_GAP_MIN = 20


class Frame(object):
    __slots__ = ("t", "p", "ps", "smean", "stale", "glitch", "pink")

    def __init__(self, t, p, ps, smean, stale=False, glitch=False, pink=None):
        self.t, self.p, self.ps, self.smean = t, p, ps, smean      # t: naive UTC; p: P(sunny); ps: P(clear sky)
        self.stale, self.glitch, self.pink = stale, glitch, pink


class Minute(object):
    __slots__ = ("t", "w", "el", "az", "p", "ps", "res_s", "fel", "flags", "value")

    def __init__(self, t, w, el, az=None):
        self.t, self.w, self.el, self.az = t, w, el, az            # t: naive UTC minute start; w: daylight weight 0..1
        self.p = self.ps = self.res_s = self.fel = self.value = None
        self.flags = set()

    @property
    def ok(self):
        return not (self.flags & {"missing", "stale", "ir", "glitch"})


def daylight_grid(sunrise: datetime, sunset: datetime) -> List[Minute]:
    out = []
    t = sunrise.replace(second=0, microsecond=0)
    while t < sunset:
        a = min(max((t + timedelta(minutes=1) - sunrise).total_seconds(), 0), 60)
        b = min(max((sunset - t).total_seconds(), 0), 60)
        w = min(a, b) / 60.0
        if w > 0:
            el, az = solar_pos(t + timedelta(seconds=30))
            out.append(Minute(t, w, el, az))
        t += timedelta(minutes=1)
    return out


def _nearest(frames: List[Frame], t: datetime, tolerance_s: float = TOLERANCE_S) -> Optional[Frame]:
    # frames sorted by t; small lists (<= ~800), bisect would be overkill but cheap anyway
    import bisect
    keys = [f.t for f in frames]
    i = bisect.bisect_left(keys, t)
    best = None
    for j in (i - 1, i):
        if 0 <= j < len(frames):
            d = abs((frames[j].t - t).total_seconds())
            if d <= tolerance_s and (best is None or d < abs((best.t - t).total_seconds())):
                best = frames[j]
    return best


def _median5(vals: List[int]) -> List[int]:
    n = len(vals)
    out = []
    for i in range(n):
        win = [vals[min(max(k, 0), n - 1)] for k in range(i - 2, i + 3)]
        out.append(sorted(win)[2])
    return out


def mark_glitch_episodes(frames: List[Frame]) -> int:
    """Set glitch on every frame of a smear episode (frames sorted by t); returns the number newly marked."""
    marked, i, n = 0, 0, len(frames)
    while i < n:
        if frames[i].pink is None or frames[i].pink < GLITCH_EDGE:
            i += 1
            continue
        j = i + 1
        while j < n and frames[j].pink is not None and frames[j].pink >= GLITCH_EDGE \
                and (frames[j].t - frames[j - 1].t).total_seconds() <= EPISODE_STEP_S:
            j += 1
        if any(f.pink >= GLITCH_SEED for f in frames[i:j]):
            for f in frames[i:j]:
                if not f.glitch:
                    f.glitch = True
                    marked += 1
        i = j
    return marked


def fill_short_gaps(grid: List[Minute], max_gap_min: int = MAX_GAP_MIN,
                    max_glitch_gap_min: int = MAX_GLITCH_GAP_MIN) -> int:
    """Step 6 in place; returns the number of minutes filled."""
    filled = 0
    i, n = 0, len(grid)
    while i < n:
        if grid[i].value is not None:
            i += 1
            continue
        j = i
        while j < n and grid[j].value is None:
            j += 1
        # gap is grid[i:j]; needs a classified minute on both sides and contiguous minutes
        all_glitch = all("glitch" in grid[k].flags for k in range(i, j))
        if 0 < i and j < n and grid[j].t - grid[i - 1].t == timedelta(minutes=j - i + 1) and (
                (j - i) <= max_gap_min
                or (all_glitch and (j - i) <= max_glitch_gap_min and grid[i - 1].value == grid[j].value)):
            left, right = grid[i - 1].value, grid[j].value
            half = (j - i + 1) // 2
            for k in range(i, j):
                grid[k].value = left if (left == right or k - i < half) else right
                grid[k].flags.add("interp")
                filled += 1
        i = j
    return filled


def classify_day(frames: List[Frame], sunrise: datetime, sunset: datetime,
                 tolerance_s: float = TOLERANCE_S, max_gap_min: int = MAX_GAP_MIN) -> List[Minute]:
    """tolerance_s: how far a minute may be from its nearest frame (5 min live; 15 min for the 30-min archive)."""
    frames = sorted(frames, key=lambda f: f.t)
    mark_glitch_episodes(frames)
    grid = daylight_grid(sunrise, sunset)
    fel: Dict[datetime, float] = {}
    for m in grid:
        f = _nearest(frames, m.t, tolerance_s)
        if f is None:
            m.flags.add("missing")
            continue
        m.p, m.ps, m.res_s = f.p, f.ps, abs((m.t - f.t).total_seconds())
        if f.t not in fel:
            fel[f.t] = solar_pos(f.t)[0]
        m.fel = fel[f.t]
        if f.stale:
            m.flags.add("stale")
        elif f.glitch:
            m.flags.add("glitch")
        elif f.smean < IR_SMEAN and m.el > IR_MIN_EL:
            m.flags.add("ir")
    if not grid:
        return grid

    main: List[Optional[int]] = [int(m.p >= 0.5) if m.ok else None for m in grid]
    low = [m.ok and (m.fel < LOW_EL or (m.fel < OBSTRUCT_EL and m.p < 0.5 and m.ps >= CLEAR_PS)) for m in grid]
    conf = [main[i] if (m.ok and not low[i] and (m.p < CARRY_CONF or m.p > 1 - CARRY_CONF)) else None
            for i, m in enumerate(grid)]
    noon = max(range(len(grid)), key=lambda i: grid[i].el)
    carry: List[Optional[int]] = [None] * len(grid)
    nxt = None
    for i in range(noon, -1, -1):          # morning: next confident state
        if conf[i] is not None:
            nxt = conf[i]
        carry[i] = nxt
    prev = None
    for i in range(noon, len(grid)):       # evening: previous confident state (noon minute itself as morning)
        if conf[i] is not None:
            prev = conf[i]
        if i > noon:
            carry[i] = prev

    raw: Dict[int, int] = {}
    for i, m in enumerate(grid):
        if not m.ok:
            continue
        if low[i]:
            m.flags.add("lowsun")
            v = carry[i] or 0
            raw[i] = v if m.ps >= VETO else 0
        else:
            raw[i] = main[i]
    idx = sorted(raw)
    for i, v in zip(idx, _median5([raw[i] for i in idx])):
        grid[i].value = v
    if max_gap_min:
        # Longer glitch fills only for per-minute frames: never invent a whole 30-min archive snapshot (backfill).
        fill_short_gaps(grid, max_gap_min, MAX_GLITCH_GAP_MIN if tolerance_s <= TOLERANCE_S else max_gap_min)
    return grid


def summary(grid: List[Minute]) -> dict:
    daylight = sum(m.w for m in grid)
    classified = sum(m.w for m in grid if m.value is not None)
    sun = sum(m.w for m in grid if m.value == 1)
    return dict(daylight_min=round(daylight, 2), sun_min=round(sun, 2), classified_min=round(classified, 2),
                coverage=round(classified / daylight, 4) if daylight else 0.0)
