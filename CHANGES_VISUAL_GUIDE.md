# Visual Guide: Homepage Improvements

## Change 1: Notable Rankings - Exclude LOW Maximum Wind Speeds

### Before
```
Notable in the rankings
────────────────────────────────────
• Daily maximum wind speed, today: 2.3 mph · =4th lowest of 2,156 days...
• Maximum gust speed, today: 5.1 mph · 9th lowest of 2,234 days...
• Max 10m wind speed, today: 3.8 mph · 6th lowest of 1,986 days...
```
❌ Low maximum wind speeds incorrectly shown as notable

### After
```
Notable in the rankings
────────────────────────────────────
• Mean wind speed, today: 1.2 mph · 3rd lowest of 2,156 days...
• Rainfall, October total: 165.8 mm · 2nd highest of 18 years...
```
✅ Low maximum wind speeds excluded; low mean wind still eligible

---

## Change 2: Climate Card - Mean Wind & Avg Temp Label

### Before
```
Climate & extremes for 07 Nov
─────────────────────────────────────────────────────────
                Today    Normal    Records (Low | High)
─────────────────────────────────────────────────────────
Day max         18.2°C   12.4°C    7.8°C  |  19.4°C
Night min        8.1°C    4.8°C    -3.2°C |  12.1°C
Mean            12.6°C    8.6°C    3.2°C  |  15.8°C  ← Wrong label
Rain             5.2 mm   3.1 mm   0.0 mm |  32.4 mm
Sunshine         4.2 hrs  2.8 hrs  0.0 hrs|   8.3 hrs
Max gust        24 mph    -        3 mph  |  52 mph  ← Wrong variable
```

### After
```
Climate & extremes for 07 Nov
─────────────────────────────────────────────────────────
                Today    Normal    Records (Low | High)
─────────────────────────────────────────────────────────
Day max         18.2°C   12.4°C    7.8°C  |  19.4°C
Night min        8.1°C    4.8°C    -3.2°C |  12.1°C
Avg temp        12.6°C    8.6°C    3.2°C  |  15.8°C  ← New label ✓
Rain             5.2 mm   3.1 mm   0.0 mm |  32.4 mm
Sunshine         4.2 hrs  2.8 hrs  0.0 hrs|   8.3 hrs
Mean wind        8 mph    7 mph    2 mph  |  18 mph  ← New variable ✓
```

**Changes:**
- ✅ "Mean" → "Avg temp" (clearer labeling)
- ✅ "Max gust" → "Mean wind" (with normals/records)

---

## Change 3: New Year-to-Date Chart

### New Chart Section (placed between 31-day trends and climate card)

```
┌─────────────────────────────────────────────────────────────┐
│ Year-to-date                                                 │
│                                                              │
│ 2026 Cumulative Rainfall                     ←─── Heading   │
│ ┌─────────────────────────────────────────────────────────┐│
│ │ Measure                                    Options       ││
│ │ ┌─────────────────────┐                   ┌───────────┐ ││
│ │ │ Minimum temperature │                   │ Normals ✓ │ ││ ← Always ON
│ │ │ Maximum temperature │                   │ (disabled)│ ││
│ │ │ Mean temperature    │                   └───────────┘ ││
│ │ │ Rainfall           ◄│◄─ Only 5          ┌───────────┐ ││
│ │ │ Sunshine            │   variables       │ Station   │ ││ ← User toggle
│ │ └─────────────────────┘                   │ record    │ ││   (off by default)
│ │                                            │ range     │ ││
│ │                                            └───────────┘ ││
│ ├─────────────────────────────────────────────────────────┤│
│ │           ┌─── Chart shows cumulative total ───┐        ││
│ │  200mm ┤  /                                              ││
│ │        │ /    Normals line (always visible)             ││
│ │  150mm ┤/─ ─ ─                                          ││
│ │        /      ╲                                          ││
│ │  100mm ┤       ╲                                         ││
│ │       /         ╲___                                     ││
│ │   50mm ┤            ╲                                    ││
│ │        │             ╲__                                 ││
│ │     0mm└──────────────────────────────────────────────── ││
│ │        Jan    Mar    May    Jul    Sep    Nov           ││
│ └─────────────────────────────────────────────────────────┘│
│ More YTD options →                                          │
└─────────────────────────────────────────────────────────────┘
```

### January-Only Year Selector

When viewed in **January**:
```
┌──────────────────────────────────────────┐
│ Measure                                   │
│ [Min temp][Max temp][Mean temp]...        │
│                                           │
│ Year                      ←─── Only in   │
│ [  2026  ][ 2025 ]            January!   │
│                                           │
│ Options                                   │
│ [ Normals ✓ (disabled) ]                 │
│ [ Station record range  ]                │
└──────────────────────────────────────────┘
```

When viewed in **any other month**:
```
┌──────────────────────────────────────────┐
│ Measure                                   │
│ [Min temp][Max temp][Mean temp]...        │
│                                           │
│ (Year selector not shown)                │
│                                           │
│ Options                                   │
│ [ Normals ✓ (disabled) ]                 │
│ [ Station record range  ]                │
└──────────────────────────────────────────┘
```

### Variable Behavior

**Temperatures (tmin, tmax, tmean):**
- Show daily values (not cumulative)
- Normals line shows 1991-2020 daily normal
- Line connects daily readings

**Rain & Sunshine:**
- Show cumulative totals (running sum)
- Start from 0 on Jan 1
- Increase throughout the year
- Normals line shows cumulative normal

**Station Record Range (toggle):**
- Off by default
- When enabled:
  - Shaded area shows record low to record high for each date
  - Markers show where this year set records
  - Works for both daily and cumulative modes

---

## Page Layout Changes

### Before (partial homepage layout)
```
┌────────────────────────────────────┐
│ Live weather cards                 │
└────────────────────────────────────┘
┌────────────────────────────────────┐
│ 31-day trends                      │
│ (selectable chart)                 │
└────────────────────────────────────┘
┌────────────────────────────────────┐
│ Climate & extremes card            │
│ (with Max gust)                    │
└────────────────────────────────────┘
┌────────────────────────────────────┐
│ Forecast                           │
└────────────────────────────────────┘
```

### After (partial homepage layout)
```
┌────────────────────────────────────┐
│ Live weather cards                 │
└────────────────────────────────────┘
┌────────────────────────────────────┐
│ 31-day trends                      │
│ (selectable chart)                 │
└────────────────────────────────────┘
┌────────────────────────────────────┐  ← NEW!
│ Year-to-date                       │
│ (restricted 5-variable chart)      │
└────────────────────────────────────┘
┌────────────────────────────────────┐
│ Climate & extremes card            │
│ (with Mean wind)                   │  ← Modified
└────────────────────────────────────┘
┌────────────────────────────────────┐
│ Forecast                           │
└────────────────────────────────────┘
```

**Note:** Climate card stays in its original position, not moved down unnecessarily.

---

## Responsive Behavior

### Desktop (1200px+)
- YTD chart: Full width with side-by-side controls and chart
- Variable buttons: Inline horizontal layout
- All toggles visible

### Tablet (768-1199px)
- YTD chart: Slightly narrower
- Controls: May wrap but stay accessible
- Chart height: 360px (same)

### Mobile (< 768px)
- YTD chart: Full width
- Variable buttons: May stack or scroll horizontally
- Controls: Stacked vertically
- Chart height: 360px or adjusted for readability
- Touch-friendly button sizes

---

## Browser Compatibility

All changes use existing chart infrastructure, so compatibility matches current site:
- ✅ Chrome/Edge (Chromium)
- ✅ Firefox
- ✅ Safari (macOS/iOS)
- ✅ Mobile browsers

No new JavaScript libraries or CSS features required.

---

## Technical Details

### Variable Mappings

| Display Name          | Variable ID | Type         | Cumulative? | Normals? |
|-----------------------|-------------|--------------|-------------|----------|
| Minimum temperature   | `tmin`      | Daily min    | No          | Yes      |
| Maximum temperature   | `tmax`      | Daily max    | No          | Yes      |
| Mean temperature      | `tmean`     | Daily mean   | No          | Yes      |
| Rainfall              | `rain`      | Daily total  | Yes         | Yes      |
| Sunshine              | `sunhr`     | Daily total  | Yes         | Yes      |

### Climate Card Variables

| Label         | Variable ID | Unit    | Normals? | Records?  |
|---------------|-------------|---------|----------|-----------|
| Day max       | `daymax`    | Temp    | Yes      | Low, High |
| Night min     | `nightmin`  | Temp    | Yes      | Low, High |
| **Avg temp**  | `tmean`     | Temp    | Yes      | Low, High |
| Rain          | `rain`      | mm      | Yes      | High only |
| Sunshine      | `sunhr`     | hours   | Yes      | High only |
| **Mean wind** | `wmean`     | mph/kph | Yes      | High only |

### Notable Rankings Variables (LOW excluded)

| Variable     | Description                | LOW Notable? |
|--------------|----------------------------|--------------|
| `wmean`      | Mean Wind Speed            | ✅ Yes       |
| `wmax`       | Maximum Wind Speed (1-min) | ❌ No        |
| `gust`       | Maximum Gust Speed         | ❌ No        |
| `w10max`     | Max 10m Wind Speed         | ❌ No        |

---

## User Experience Improvements

### 1. Notable Rankings
**Problem:** Calm days (low max wind) were shown as "notable" but aren't interesting  
**Solution:** Only show genuinely interesting low winds (sustained mean, not peaks)

### 2. Climate Card
**Problem:** "Mean" label ambiguous; gust lacks normals/context  
**Solution:** "Avg temp" clearer; mean wind has normals for comparison

### 3. YTD Chart
**Problem:** No quick YTD view on homepage; full chart page has too many options  
**Solution:** Simplified 5-variable chart for core year-to-date tracking

---

## Data Sources

All three changes use **existing data infrastructure**:
- RankFlags: Uses `Data::getDailyData()` for all variables
- ClimateCard: Uses `CACHE_ROOT/serialised_dat_new_wmean.txt`
- YTD Chart: Uses `histdata.php?mode=daily` endpoint

**No new data collection required.**

---

## Cache Impact

### Auto-Rebuilding Caches
- `serialised_climrec.txt` - rebuilds on first access (ClimateCard)
- `rank_flags.json` - rebuilds every 5 minutes (cron)

### Build Time
- ClimateCard rebuild: ~2-5 seconds (daily data for 6 variables)
- RankFlags rebuild: ~30-60 seconds (full ranking analysis)

**Impact:** Minimal, handled in background.

---

## Accessibility

All changes maintain or improve accessibility:

### YTD Chart
- ✅ ARIA labels on controls (`role="tablist"`, `role="group"`)
- ✅ Button text clearly describes function
- ✅ Disabled normals toggle has visual and programmatic state
- ✅ Keyboard navigation works (tab through buttons)

### Climate Card
- ✅ Table structure semantic and screen-reader friendly
- ✅ Row headers clearly labeled
- ✅ Tooltips provide additional context
- ✅ Record markers have descriptive titles

### Notable Rankings
- ✅ List structure semantic
- ✅ Links clearly describe destination
- ✅ Rankings expressed in plain language

---

## Performance

### Page Load Impact
- **JavaScript:** +0 KB (reuses existing wxcharts.js)
- **CSS:** +0 KB (reuses existing chart styles)
- **HTML:** +~2 KB (new YTD chart section)
- **HTTP Requests:** +1 (YTD chart data fetch)

### Rendering Performance
- YTD chart uses existing Highcharts rendering
- Climate card rebuild is background/cached
- RankFlags rebuild is cron/background
- No impact on time-to-interactive

**Overall impact:** Negligible

---

## Future Enhancements (Out of Scope)

Possible future improvements not in this PR:
- YTD chart: Add more weather variables (wind, pressure, etc.)
- YTD chart: Multi-year comparison view
- Climate card: Add more rows (wind direction, pressure, etc.)
- Notable rankings: Configurable thresholds
- Mobile: Collapsible chart controls

---

**Documentation last updated:** 2026-11-07
