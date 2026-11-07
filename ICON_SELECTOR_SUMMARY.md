# Icon-Based Selector Implementation Summary

## Commit: Implements Ben's UI requirements

### Changes Made

#### 1. YTD Chart Icon-Based Grouped Selector

**Previous (Wrong):** Flat 5-button selector (Min temp, Max temp, Mean temp, Total rain, Total sunshine)

**Current (Correct):** Icon-based grouped selector matching 31-day chart style:
- **Temp group** (thermometer icon): Min / Mean / Max sub-selector
- **Rain group** (rain icon): Total
- **Sun group** (sun icon): Total

**Files:**
- `site/ChartHelper.php` lines 678-774: ytdHomeChart() function
  - Creates $groups array with temp/rain/sun
  - Calls emitGroupButtons($groups, $defaultGroup)
  - Uses selectableVarMeta($groups) for metadata
  - Passes $groups to JS config (not flat varOptions)

**Implementation:**
```php
$groups = array(
	array(
		'id' => 'temp', 'label' => 'Temp', 'icon' => $img . 'thermom8_small.png',
		'options' => array('tmin' => 'Min', 'tmean' => 'Mean', 'tmax' => 'Max'),
	),
	array(
		'id' => 'rain', 'label' => 'Rain', 'icon' => $img . 'icon-rain.svg',
		'options' => array('rain' => 'Total'),
	),
	array(
		'id' => 'sun', 'label' => 'Sun', 'icon' => $img . 'icon-sun.svg',
		'options' => array('sunhr' => 'Total'),
	),
);
self::emitGroupButtons($groups, $defaultGroup);
```

#### 2. Controls Above Chart (Full-Width Chart Below)

**Previous (Wrong):** Sidebar panel + chart side-by-side (chart constrained)

**Current (Correct):** Controls above, chart spans full width below

**HTML Structure:**
```html
<div class="wxsel-wrap ytd-home-wrap">
  <h3 class="wxsel-heading">2026 Cumulative Total rain</h3>
  <!-- Panel above -->
  <div class="wxsel-panel wxsel-panel-above">
    <div class="wxsel-groups">...</div>  <!-- Temp/Rain/Sun icons -->
    <div class="wxsel-subtypes">...</div> <!-- Min/Mean/Max when Temp selected -->
    <div class="wxsel-normals">...</div>  <!-- Station record range button -->
  </div>
  <!-- Chart below, full width -->
  <div class="wxchart">...</div>
</div>
```

**CSS (`site/mainstyle_v5.css` lines 706-723):**
```css
.ytd-home-wrap {
	display: flex;
	flex-direction: column;
}
.ytd-home-wrap .wxsel-panel-above {
	width: 100%;
	max-width: none;
	order: 0;
}
.ytd-home-wrap .wxchart {
	width: 100%;
	flex: 1 1 auto;
	order: 1;
	min-width: 0;
}
```

**Benefits:**
- Chart uses full content width (no sidebar constraint)
- Controls compact horizontal layout above
- No duplicate flex children crushing chart width
- Responsive: controls wrap at narrow widths

#### 3. Climate Card Mean Wind: 1dp + LOW Records

**File:** `site/ClimateCard.php` line 61

**Previous:**
```php
'wmean' => array('label' => 'Mean wind', 'unit' => Wx::Wind, 'dp' => 0, 'norm' => 'wmean', 'lo' => false, 'hi' => true),
```

**Current:**
```php
'wmean' => array('label' => 'Mean wind', 'unit' => Wx::Wind, 'dp' => 1, 'norm' => 'wmean', 'lo' => true, 'hi' => true),
```

**Changes:**
- `'dp' => 1`: Format to 1 decimal place (was 0)
- `'lo' => true`: Populate LOW mean wind records (was false)

**Effect:**
- Today's value: e.g., "0.3 mph" instead of "0 mph"
- Normal: e.g., "5.1 mph" instead of "5 mph"
- LOW records: Shows actual daily mean wind minima with dates/years
  - e.g., "0.2 mph (2015)" instead of "–"
- HIGH records: Already shown, now with 1dp consistency

**Note:** "Low for each date" interpreted as card's existing date-specific/month/all-time low record columns showing actual daily mean wind minima (NOT low gust or low max speed).

### Preserved Features

All previous rendering fixes and backend features intact:
- ✅ No duplicate recordToggle control (array_diff_key)
- ✅ envBtn styling independent (syncCumeUi fix)
- ✅ Heading shows year (defaultYear check)
- ✅ Cache buster 20261107b
- ✅ Complete year validation (CACHE_VERSION = 3)
- ✅ Raw-sum precision (accumulate first, convert after)
- ✅ Calendar date alignment with Feb 29
- ✅ Rankings 90% threshold
- ✅ Low max wind exclusions (wmax, gust, w10max)

### Testing Results

```
✅ test_code_changes.php:      72 tests passed
✅ test_ytd_icon_selector.php: 17 tests passed
✅ test_climate_wind.php:       7 tests passed
─────────────────────────────────────────
   Total:                      96 tests passed
```

**Icon Selector Tests:**
- Icon-based groups with correct icons
- emitGroupButtons called
- Controls above chart layout
- Panel before chart container
- CSS full-width rules
- No flat button structure

**Climate Wind Tests:**
- wmean dp => 1
- wmean lo => true
- wmean hi => true
- No flagHi (not instantaneous)
- Unit Wx::Wind
- gust row removed

### Visual Behavior

**Icon Group Selection:**
1. Click Temp icon → Min/Mean/Max buttons appear
2. Click Rain icon → Total button (single option)
3. Click Sun icon → Total button (single option)
4. Selected group highlighted, sub-options update

**Layout:**
- Desktop: Icon buttons horizontal, sub-selector compact, chart full width below
- Tablet: Controls wrap appropriately
- Mobile: Stacks vertically, chart remains visible full width

**Chart Behavior:**
- Temp selections (Min/Mean/Max): Daily values, no cumulative
- Rain/Sun: Cumulative by default (summable)
- Station record range: Off default, toggleable (works for all measures)
- Normals: Always on (no toggle button)
- January: Year selector (2026/2025)
- Outside January: No year selector, but sends year=2026

**Climate Card:**
- Mean wind row shows 1 decimal place throughout
- LOW column populated with actual daily minima (not dashes)
- HIGH column shows maxima (already worked, now 1dp)

### Files Changed

Application Code (3 files):
- `site/ChartHelper.php`: Icon-based groups, above-chart layout
- `site/ClimateCard.php`: Mean wind 1dp + LOW records
- `site/mainstyle_v5.css`: YTD full-width layout CSS

Tests (3 files):
- `test_code_changes.php`: Updated for icon-based approach
- `test_ytd_icon_selector.php`: New 17-test suite
- `test_climate_wind.php`: New 7-test suite

### Implementation Notes

**Why Icon-Based Groups:**
- Matches existing 31-day chart selector semantics
- Reuses shared emitGroupButtons function
- Consistent visual language across homepage
- Supports sub-selectors (Temp: Min/Mean/Max)
- Single-option groups (Rain/Sun) handled naturally

**Why Controls Above Chart:**
- Chart spans full content width (desktop ~1200px vs ~800px sidebar layout)
- More data visible without horizontal scrolling
- Ben explicitly requested: "have the selectors above chart so it's full width"
- Matches common chart UI pattern (controls top, plot below)

**Why 1dp for Mean Wind:**
- Ben: "For mean wind you can show to 1dp"
- Reveals precision: 0.3 mph vs 0 mph (more informative)
- Consistent with other wind measures on card
- Unit-aware: Wx::Wind handles mph/km/h/kts conversion before rounding

**Why LOW Records:**
- Ben: "show the low for each date"
- Interpreted consistently with card's existing structure:
  - "07 Nov records" column: date-specific records
  - "November records" column: month records
  - "All-time records" column: all-time records
- Shows actual daily mean wind minima (not gust/max speed lows)
- Handles ties/missing using existing card conventions

### No Production Changes

- ❌ No merge performed
- ❌ No deploy performed
- ✅ All changes on PR #52 branch
- ✅ Ready for Ben's visual verification

---

**Commit:** Icon-based grouped selector with controls above full-width chart  
**Branch:** `cursor/homepage-improvements-7003`  
**PR:** #52  
**Tests:** 96/96 passing (100%)
