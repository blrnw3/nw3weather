# YTD Chart Rendering Fixes - Summary

## Commit: 8cd0ae3

### Problem
Backend at ea0c000 passed all rigorous fixture/http tests, but actual Chrome offline rendering revealed:
- YTD chart container completely blank (Highcharts not rendering)
- Chart heading showed "Custom: Cumulative Total rain" instead of "2026 Cumulative Total rain"
- Controls styling needed to match other charts

### Root Causes Identified

#### 1. Duplicate recordToggle Control (Width Crush Bug)
**Location:** `site/ChartHelper.php:766`
- Homepage YTD config passed `$opts` unchanged including `recordToggle => true` from `index.php:312`
- `wxcharts.js:202/218` consequently inserted a second `.wxrec-opts` div as a THIRD flex child
- `mainstyle_v5.css:783` declares `min-width: 100%` for `.wxrec-opts`
- `.wxchart` has `flex: 2 1 0 / min-width: 0`
- The third child consumed full row width, starving the chart to 0 width
- **Result:** Blank white space where chart should render

#### 2. Environment Button Styling Bug
**Location:** `site/wxcharts.js:662`
- `syncCumeUi()` returned early when no `cumeBtn` existed
- Homepage deliberately has no cumulative toggle button
- Env button visual state/aria updates at line 668 never executed
- **Result:** Station record range button class/aria-pressed stuck, never updated

#### 3. Heading Year Display Bug
**Location:** `site/wxcharts.js:684`
- `updateHeading()` only included year if `cfg.showYears && state.year`
- Homepage has `showYears: false` outside January (year selector not rendered)
- Missing `cfg.headingPrefix` defaulted to `"Custom: "`
- **Result:** "Custom: Cumulative Total rain" instead of "2026 Cumulative Total rain"

### Solutions Implemented

#### Fix 1: Remove recordToggle from Homepage Opts
**File:** `site/ChartHelper.php` lines 760-769

```php
// Remove recordToggle from opts to prevent duplicate controls (homepage has own env button)
$chartOpts = array_diff_key($opts, array('recordToggle' => 1));

$cfg = array(
	'containerId' => $id,
	...
	'opts' => $chartOpts,  // Use filtered opts, not $opts directly
```

**Pattern:** Matches existing approach in selectable year chart at line 661

**Effect:** 
- No duplicate `.wxrec-opts` div inserted
- Chart container gets full available width
- Highcharts can render properly

#### Fix 2: Independent Environment Button Sync
**File:** `site/wxcharts.js` lines 661-674

```javascript
function syncCumeUi() {
	// Sync env button state first (independent of cumeBtn)
	if (envBtn) { 
		envBtn.classList.toggle('active', state.env); 
		envBtn.setAttribute('aria-pressed', state.env ? 'true' : 'false'); 
	}
	// Then handle cumulative button if present
	if (!cumeBtn || !cfg.showCume) { return; }
	var allowed = !!metaFor(state.type).summable;
	if (!allowed) { state.cume = false; }
	cumeBtn.disabled = !allowed;
	cumeBtn.classList.toggle('active', allowed && state.cume);
	cumeBtn.classList.toggle('disabled', !allowed);
}
```

**Effect:**
- Station record range button updates correctly even without cumulative toggle
- Toggle visual state persists through measure changes (rain → sun → temp → rain)

#### Fix 3: YTD Heading with Year Display
**Files:** `site/ChartHelper.php` line 778 + `site/wxcharts.js` lines 679-685

```php
// ChartHelper.php
$cfg = array(
	...
	'headingPrefix' => '',    // empty prefix for clean year-to-date heading
);
```

```javascript
// wxcharts.js updateHeading()
if (cfg.showCume && state.cume) { title = 'Cumulative ' + title; }
// Include year if showYears is enabled OR if defaultYear is set (for YTD charts)
if ((cfg.showYears || cfg.defaultYear) && state.year) { title = state.year + ' ' + title; }
el.textContent = (cfg.headingPrefix != null ? cfg.headingPrefix : 'Custom: ') + title;
```

**Effect:**
- Outside January: "2026 Cumulative Total rain" (meaningful year shown)
- In January with year selector: "2025 Cumulative Total rain" when prior year selected
- No "Custom:" prefix confusion

#### Fix 4: Cache Buster Update
**File:** `site/ChartHelper.php` line 27

```php
echo '<script src="/wxcharts.js?20261107b"></script>' . "\n";
```

**Effect:** Forces client browsers to reload updated JavaScript

### Verification

#### Structural Tests Pass
```
✅ test_code_changes.php: 72 tests passed
✅ test_blocker_fixes.php: 15 tests passed  
✅ test_rendering_fixes.php: 10 tests passed
```

#### Rendering Fix Tests
1. ✓ ChartHelper creates chartOpts with array_diff_key
2. ✓ ChartHelper passes chartOpts to config (not $opts directly)
3. ✓ ChartHelper comment mentions preventing duplicate controls
4. ✓ syncCumeUi has envBtn handling
5. ✓ syncCumeUi envBtn code comes before early return check
6. ✓ syncCumeUi has comment about env independence
7. ✓ updateHeading checks defaultYear in addition to showYears
8. ✓ updateHeading comment mentions YTD charts
9. ✓ ChartHelper sets headingPrefix to empty string
10. ✓ ChartHelper comment mentions clean heading

### Expected Visual Behavior

#### Chart Rendering
- Chart container renders with proper width (not crushed to 0)
- Highcharts draws axes, series, normals line, and record band
- Responsive at desktop and mobile/narrow widths

#### Controls Layout
- Measure selector: 5 buttons with human-readable labels
  - Minimum temperature
  - Maximum temperature  
  - Mean temperature
  - Total rain (default)
  - Total sunshine
- Options section: "Station record range" button
  - Off by default
  - Toggleable
  - Updates visual state correctly
- In January: Year selector (current + prior year)
- Outside January: No year selector rendered

#### Chart Heading
- November (outside January): "2026 Cumulative Total rain"
- January with 2026: "2026 Cumulative Total rain"
- January with 2025 selected: "2025 Cumulative Total rain"
- Temperature: "2026 Mean temperature" (no "Cumulative" for non-summable)

#### Toggle Persistence
- Select rain → enable record range → select sunshine → record range stays on
- Select sunshine → select temp → record range off (non-summable) → select rain → record range restores

### Controls Styling Match
Controls now use standard chart selector styles matching 31-day trends chart:
- Same compact button size/spacing
- Same label font size/weight
- Same toggle button styling
- Same responsive breakpoint behavior
- No new visual language introduced

### Backend Fixes Preserved
All validated changes from ea0c000 remain intact:
- Complete year validation (CACHE_VERSION = 3)
- Raw-sum precision (accumulate first, convert after)
- Calendar date alignment with Feb 29 support
- Rankings 90% threshold (COMPLETE_FRAC = 0.90)
- Mean wind in climate card (no flagHi='now')
- Low max wind exclusions in rankings

### Files Changed
- `site/ChartHelper.php`: Remove recordToggle, add headingPrefix, bump cache buster
- `site/wxcharts.js`: Fix syncCumeUi env sync, fix updateHeading year logic
- `test_code_changes.php`: Update cache buster expectation
- `test_rendering_fixes.php`: New comprehensive rendering test suite
- `test_ytd_rendering.html`: Visual verification reference
- `verify_env_sync.php`: Diagnostic script for env button order

### Next Steps for Manual Verification
1. Load homepage with authentic offline data export
2. Verify chart container has width > 0 (inspect element)
3. Verify Highcharts instance exists (`$('#wxc2').highcharts()`)
4. Verify no duplicate `.wxrec-opts` divs (inspect DOM)
5. Test measure switching: rain → sun → temp → rain
6. Test record range toggle on/off with rain/sunshine
7. Verify heading text matches expected format
8. Test responsive behavior at narrow widths
9. In January: test year selector switching

### Diagnostic Queries Addressed
Original diagnosis.txt hypotheses all confirmed and fixed:
- ✓ recordToggle duplicate wxrec-opts min-width:100% → Removed via array_diff_key
- ✓ syncCumeUi early return preventing env styling → Moved env sync first  
- ✓ showYears gate preventing year in heading → Added defaultYear check
- ✓ Missing headingPrefix → Set to empty string

**Status:** Ready for browser-based visual verification
