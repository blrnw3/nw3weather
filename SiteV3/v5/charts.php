<?php
require("Page.php");
Page::init([
	"fileNum" => 32,
	"title" => "Chart Viewer",
	"description" => "Latest and historical 31-day, monthly and annual interactive charts of weather variables for Hampstead, London."
]);
Page::Start();

// Build the variable picker from the data definitions, skipping non-numeric and
// currently unsupported (percentage/anomaly) series.
$skip = ['comms', 'extra', 'issues', 'away', 'cloud', 'spare', 'sunhrp', 'wethrp'];
$varOptions = '';
foreach (Wx::$daily as $key => $meta) {
	if (in_array($key, $skip, true)) { continue; }
	$sel = ($key === 'tmean') ? ' selected="selected"' : '';
	$varOptions .= '<option value="' . $key . '"' . $sel . '>' . htmlspecialchars($meta['description']) . '</option>';
}
?>

<h1 id="chart-heading">Data Charts &ndash; Mean Temperature</h1>

<div class="chart-viewer-controls">
	<label>Variable
		<select id="cv-var"><?php echo $varOptions; ?></select>
	</label>
	<label>Chart
		<select id="cv-type">
			<option value="d" selected="selected">Daily</option>
			<option value="m0">Monthly &ndash; mean</option>
			<option value="m1">Monthly &ndash; total</option>
			<option value="m3">Monthly &ndash; lowest</option>
			<option value="m4">Monthly &ndash; highest</option>
			<option value="m2">Monthly &ndash; count</option>
			<option value="a0">Annual &ndash; mean</option>
			<option value="a1">Annual &ndash; total</option>
			<option value="a3">Annual &ndash; lowest</option>
			<option value="a4">Annual &ndash; highest</option>
			<option value="a2">Annual &ndash; count</option>
		</select>
	</label>
	<label id="cv-year-wrap">Period
		<select id="cv-year">
			<option value="0" selected="selected">Current / recent</option>
			<?php for ($i = (int)Date::$yr_yest; $i >= 1871; $i--) { echo '<option value="' . $i . '">' . $i . '</option>'; } ?>
		</select>
	</label>
	<label id="cv-month-wrap" style="display:none">Month
		<select id="cv-month">
			<option value="0" selected="selected">Whole year</option>
			<?php for ($i = 1; $i <= 12; $i++) { echo '<option value="' . $i . '">' . Date::$months3[$i - 1] . '</option>'; } ?>
		</select>
	</label>
	<label id="cv-lengthD-wrap">Length
		<select id="cv-lengthD">
			<option value="31" selected="selected">31 days</option>
			<option value="60">60 days</option>
			<option value="90">90 days</option>
			<option value="180">180 days</option>
			<option value="365">1 year</option>
			<option value="730">2 years</option>
			<option value="1095">3 years</option>
			<option value="1826">5 years</option>
			<option value="3653">10 years</option>
		</select>
	</label>
	<label id="cv-lengthM-wrap" style="display:none">Length
		<select id="cv-lengthM">
			<option value="12" selected="selected">12 months</option>
			<option value="24">2 years</option>
			<option value="36">3 years</option>
			<option value="60">5 years</option>
			<option value="120">10 years</option>
			<option value="240">20 years</option>
		</select>
	</label>
	<label id="cv-start-wrap" style="display:none">Start
		<select id="cv-start">
			<?php foreach ([1871, 1910, 1950, 1980, 1990, 2000, 2009, 2015] as $y) { echo '<option value="' . $y . '"' . ($y === 2009 ? ' selected="selected"' : '') . '>' . $y . '</option>'; } ?>
		</select>
	</label>
	<label id="cv-end-wrap" style="display:none">End
		<select id="cv-end">
			<?php for ($i = (int)Date::$dyear; $i >= 1950; $i -= ($i > 2000 ? 1 : 10)) { echo '<option value="' . $i . '"' . ($i === (int)Date::$dyear ? ' selected="selected"' : '') . '>' . $i . '</option>'; } ?>
		</select>
	</label>
	<label id="cv-lta-wrap">
		<input type="checkbox" id="cv-lta" /> Show normals
	</label>
	<label id="cv-cume-wrap" style="display:none">
		<input type="checkbox" id="cv-cume" /> Cumulative
	</label>
</div>

<div id="cv-chart" class="wxchart" style="min-height:460px"></div>

<p id="cv-disclaimer" style="display:none" class="note">
	Data from before 2009 are mostly from the historical site at Whitestone Pond, Hampstead; where missing, nearby sites
	(St James's Park, Heathrow, Kew) were used, adjusted for site differences. Source: Met Office
	<a href="https://data.ceda.ac.uk/badc/ukmo-midas-open/">MIDAS Open</a>.
</p>

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/highcharts-more.js"></script>
<script src="/v5/wxcharts.js"></script>
<script>
//<![CDATA[
(function () {
	function startYearFor(v) {
		if (v === 'rain') { return 1871; }
		if (['tmin','tmax','tmean','trange','nightmin','daymax'].indexOf(v) >= 0) { return 1881; }
		if (v === 'sunhr' || v === 'sunhrp') { return 1910; }
		if (['wmean','lysnw','thunder'].indexOf(v) >= 0) { return 1949; }
		if (['snow','gust'].indexOf(v) >= 0) { return 1959; }
		return 2009;
	}

	function update() {
		var v = $('#cv-var').val();
		var t = $('#cv-type').val();
		var yr = parseInt($('#cv-year').val(), 10);
		var mon = $('#cv-month').val();
		var startY = startYearFor(v);

		// Reset conditional controls
		$('#cv-month-wrap, #cv-start-wrap, #cv-end-wrap, #cv-cume-wrap').hide();
		$('#cv-year-wrap, #cv-lta-wrap').show();
		$('#cv-disclaimer').hide();

		var params = { type: v };
		var kind = t.charAt(0);

		if (kind === 'd') {
			$('#cv-lengthD-wrap').show(); $('#cv-lengthM-wrap').hide();
			params.mode = 'daily';
			if (yr > 0) {
				$('#cv-month-wrap').show();
				params.year = yr; params.month = mon;
				if (mon === '0') { $('#cv-cume-wrap').show(); if ($('#cv-cume').is(':checked')) { params.cume = 1; } if ($('#cv-lta').is(':checked')) { params.lta = 1; } }
				if (yr < 2009) { $('#cv-disclaimer').show(); }
			} else {
				params.length = $('#cv-lengthD').val();
			}
		} else if (kind === 'm') {
			$('#cv-lengthD-wrap').hide(); $('#cv-lengthM-wrap').show();
			params.mode = 'monthly';
			params.summary_type = t.slice(1);
			if ($('#cv-lta').is(':checked')) { params.lta = 1; }
			if (yr > 0) { params.year = yr; if (yr < 2009) { $('#cv-disclaimer').show(); } }
			else { params.length = $('#cv-lengthM').val(); }
		} else { // annual
			$('#cv-lengthD-wrap, #cv-lengthM-wrap, #cv-year-wrap').hide();
			$('#cv-start-wrap, #cv-end-wrap').show();
			params.mode = 'annual';
			params.summary_type = t.slice(1);
			params.start = $('#cv-start').val();
			params.end = $('#cv-end').val();
			if (parseInt(params.start, 10) < 2009) { $('#cv-disclaimer').show(); }
		}

		$('#cv-heading');
		$('#chart-heading').text('Data Charts \u2013 ' + $('#cv-var option:selected').text());
		NW3.histChart('cv-chart', '/v5/histdata.php?' + $.param(params));
	}

	$(function () {
		$('.chart-viewer-controls').on('change', 'select, input', update);
		update();
	});
})();
//]]>
</script>

<?php Page::End(); ?>
