# YTD Chart Rendering - Visual Verification Report

## Test Environment
- Commit: 8cd0ae3
- Branch: cursor/homepage-improvements-7003
- Date: 2026-11-07

## Fixes Applied

### Fix 1: Duplicate Control Removed
**Before:** Homepage passed recordToggle:true → wxcharts.js inserted duplicate .wxrec-opts → min-width:100% crushed chart
**After:** array_diff_key removes recordToggle → single control → chart gets proper width

### Fix 2: Environment Button Independent
**Before:** syncCumeUi() returned early without cumeBtn → env button never styled
**After:** env button sync moved before early return → proper active/aria-pressed states

### Fix 3: Heading Shows Year  
**Before:** "Custom: Cumulative Total rain" (showYears:false + no headingPrefix)
**After:** "2026 Cumulative Total rain" (defaultYear check + headingPrefix:'')

## Visual Checklist

### Chart Container
- [ ] Container has width > 0 (not blank white space)
- [ ] Highcharts instance exists and initialized
- [ ] Chart renders axes with labels
- [ ] Chart renders data series
- [ ] Chart renders normals line (always visible)
- [ ] Chart renders record band when toggled on

### Controls Layout
- [ ] Measure group shows 5 buttons in compact layout
- [ ] Button sizes/spacing match 31-day chart
- [ ] Labels are human-readable (not raw keys)
- [ ] "Station record range" button visible
- [ ] No duplicate record range controls
- [ ] Outside January: no year selector
- [ ] In January: year selector with 2 buttons

### Chart Heading
- [ ] Shows "2026 Cumulative Total rain" for rain (November)
- [ ] Shows "2026 Cumulative Total sunshine" for sunshine
- [ ] Shows "2026 Mean temperature" for temp (no "Cumulative")
- [ ] In January with prior year: shows "2025 Cumulative..."

### Toggle Behavior
- [ ] Rain selected, record range off → toggle on → band appears
- [ ] Rain with range on → switch to sunshine → range stays on
- [ ] Sunshine with range on → switch to rain → range stays on
- [ ] Any measure → switch to temp → range button disabled
- [ ] Temp → switch back to rain → range re-enabled, restores state

### Responsive Behavior
- [ ] Desktop width (1200px+): controls horizontal, chart full width
- [ ] Tablet width (768px): controls wrap appropriately
- [ ] Mobile width (480px): controls stack, chart remains visible

## DOM Structure Verification

Expected structure (no duplicate controls):
```
<div class="wxsel-wrap ytd-home-wrap">
  <h3 id="wxc2-heading" class="wxsel-heading">2026 Cumulative Total rain</h3>
  <div class="wxsel-chart">
    <div id="wxc2-panel" class="wxsel-panel">
      <div class="wxsel-label">Measure</div>
      <div class="wxsel-subtypes wxsel-ytd-home">
        <button class="active" data-type="rain">Total rain</button>
        <button data-type="sunhr">Total sunshine</button>
        <button data-type="tmin">Minimum temperature</button>
        <button data-type="tmax">Maximum temperature</button>
        <button data-type="tmean">Mean temperature</button>
      </div>
      <!-- Year selector only in January -->
      <div class="wxsel-label">Options</div>
      <div class="wxsel-normals">
        <button class="wxsel-toggle" data-env="1" aria-pressed="false">Station record range</button>
      </div>
    </div>
    <div id="wxc2" class="wxchart wxchart-loading">
      <!-- Highcharts renders here -->
    </div>
  </div>
</div>
```

## JavaScript State Verification

After chart loads, in browser console:
```javascript
// Check Highcharts instance exists
$('#wxc2').highcharts()  // Should return Highcharts object, not undefined

// Check no duplicate controls
$('.wxrec-opts').length  // Should be 0 for homepage YTD

// Check heading text
$('#wxc2-heading').text()  // Should be "2026 Cumulative Total rain" not "Custom: ..."

// Check button states
$('.wxsel-toggle[data-env]').hasClass('active')  // Should be false initially
$('.wxsel-toggle[data-env]').attr('aria-pressed')  // Should be "false" initially

// After toggling record range on
$('.wxsel-toggle[data-env]').trigger('click');
$('.wxsel-toggle[data-env]').hasClass('active')  // Should be true
$('.wxsel-toggle[data-env]').attr('aria-pressed')  // Should be "true"

// Check series count (should have Normal + selected year + record band when on)
$('#wxc2').highcharts().series.length  // 2 without band, 3 with band
```

## CSS Verification

Chart container should have:
```css
#wxc2.wxchart {
  flex: 2 1 0;
  min-width: 0;
  /* Should have computed width > 0, e.g. 800px at desktop */
}
```

Panel should have:
```css
#wxc2-panel.wxsel-panel {
  flex: 1 1 auto;
  /* Typical computed width ~300-400px at desktop */
}
```

## Test Scenarios

### Scenario 1: Initial Load (Rain, November)
1. Load homepage
2. Verify heading: "2026 Cumulative Total rain"
3. Verify chart renders with axes and line
4. Verify normals line visible (always on)
5. Verify record range button shows, not active
6. Verify no duplicate controls in DOM

### Scenario 2: Toggle Record Range
1. From initial load
2. Click "Station record range" button
3. Verify button becomes active (class + aria-pressed)
4. Verify shaded band appears on chart
5. Verify 3 series in Highcharts instance

### Scenario 3: Measure Switching with Range On
1. Have record range enabled on rain
2. Click "Total sunshine" button
3. Verify range stays enabled (button still active)
4. Verify chart updates to sunshine data
5. Verify range band still visible (sunshine also summable)
6. Click "Mean temperature"
7. Verify range becomes disabled (temp not summable)
8. Verify no band visible
9. Click "Total rain"
10. Verify range re-enables and restores (band reappears)

### Scenario 4: January Year Selector
(Test only in January, or mock Date)
1. Load homepage in January
2. Verify year selector shows current + prior year
3. Verify heading shows current year by default
4. Click prior year button
5. Verify heading updates to prior year
6. Verify chart data updates

## Comparison with 31-Day Chart

The YTD controls should match the 31-day chart styling:
- Same `.wxsel-subtypes` button size/padding
- Same `.wxsel-label` font size/weight
- Same `.wxsel-toggle` button appearance
- Same responsive breakpoints
- Same color scheme

## Known Limitations Unchanged

- Backend only precomputes complete historical years (since 2009)
- Missing days in historical years → entire year excluded
- Current partial year not shown in envelope
- No record markers on individual dates for YTD (unlike daily chart)
- Cumulative envelope only for summable variables (rain, sun)

## Status

✅ All structural tests pass
✅ All backend fixture tests pass
✅ Rendering bug fixes verified in code
⏳ Visual browser testing pending

**Ready for manual browser verification with authentic data.**
