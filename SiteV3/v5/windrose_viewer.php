<?php
require("Page.php");
Page::init([
	"fileNum" => 31,
	"isSubFile" => true,
	"title" => "Wind Roses",
	"description" => "Interactive wind roses for Hampstead, London: last 24 hours, any month, any year, and all-time."
]);
Page::Start();
?>

<h1>Wind Roses</h1>
<p>Distribution of wind direction and speed. Choose a period below.</p>

<div class="chart-viewer-controls">
	<label>View
		<select id="wr-view">
			<option value="24hrs">Last 24 hours</option>
			<option value="month" selected="selected">A month</option>
			<option value="year">A year</option>
			<option value="now">All-time (since 2009)</option>
		</select>
	</label>
	<label id="wr-month-wrap">Month
		<select id="wr-month">
			<?php for ($m = 1; $m <= 12; $m++) { echo '<option value="' . $m . '"' . ($m === (int)Date::$mon_yest ? ' selected="selected"' : '') . '>' . Date::$months3[$m - 1] . '</option>'; } ?>
		</select>
	</label>
	<label id="wr-year-wrap">Year
		<select id="wr-year">
			<?php for ($y = (int)Date::$dyear; $y >= 2009; $y--) { echo '<option value="' . $y . '">' . $y . '</option>'; } ?>
		</select>
	</label>
</div>

<div id="wr-chart" class="wxchart" style="min-height:520px;max-width:640px;margin:auto"></div>

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/highcharts-more.js"></script>
<script src="/v5/wxcharts.js"></script>
<script>
//<![CDATA[
(function () {
	function pad(n) { return (n < 10 ? '0' : '') + n; }
	function load() {
		var view = $('#wr-view').val();
		var year = $('#wr-year').val();
		var mon = pad(parseInt($('#wr-month').val(), 10));
		$('#wr-month-wrap').toggle(view === 'month');
		$('#wr-year-wrap').toggle(view === 'month' || view === 'year');

		var params = { en: view };
		if (view === 'month') { params.st = year + mon + '01'; }
		else if (view === 'year') { params.st = year + '0101'; }
		NW3.windRose('wr-chart', '/v5/rosedata.php?' + $.param(params));
	}
	$(function () {
		$('#wr-view, #wr-month, #wr-year').on('change', load);
		load();
	});
})();
//]]>
</script>

<?php Page::End(); ?>
