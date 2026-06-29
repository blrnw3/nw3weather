<?php
require("Page.php");
Page::init([
	"fileNum" => 31,
	"title" => "Custom Graphs",
	"description" => "View customisable per-minute graphs of temperature, wind, rain, pressure and more over a chosen day or range of days."
]);
Page::Start();

$vars = ['temp' => 'Temperature', 'dewp' => 'Dew point', 'humi' => 'Humidity', 'pres' => 'Pressure',
	'wind' => 'Wind speed', 'gust' => 'Gust', 'wdir' => 'Wind direction', 'rain' => 'Rainfall', 'pm25' => 'Air quality'];
$nums = [1, 2, 3, 5, 7, 10, 14, 21, 31, 62, 92];
?>

<h1>Custom Graph Viewer</h1>
<p>Per-minute data for a chosen end date and number of days. Use the buttons to switch variable.</p>

<div class="chart-viewer-controls">
	<div class="wxchart-vars" id="gv-vars">
		<?php $first = true; foreach ($vars as $k => $label) {
			echo '<button type="button" data-var="' . $k . '"' . ($first ? ' class="active"' : '') . '>' . $label . '</button>';
			$first = false;
		} ?>
	</div>
	<label>Days
		<select id="gv-num">
			<?php foreach ($nums as $n) { echo '<option value="' . $n . '"' . ($n === 3 ? ' selected="selected"' : '') . '>' . $n . '</option>'; } ?>
		</select>
	</label>
	<label>End date
		<select id="gv-year">
			<?php for ($y = (int)Date::$dyear; $y >= 2009; $y--) { echo '<option value="' . $y . '">' . $y . '</option>'; } ?>
		</select>
		<select id="gv-month">
			<?php for ($m = 1; $m <= 12; $m++) { echo '<option value="' . $m . '"' . ($m === (int)Date::$dmonth ? ' selected="selected"' : '') . '>' . Date::$months3[$m - 1] . '</option>'; } ?>
		</select>
		<select id="gv-day">
			<?php for ($d = 1; $d <= 31; $d++) { echo '<option value="' . $d . '"' . ($d === (int)Date::$dday ? ' selected="selected"' : '') . '>' . Util::zerolead($d) . '</option>'; } ?>
		</select>
	</label>
	<button type="button" id="gv-go">Update</button>
</div>

<div id="gv-chart" class="wxchart" style="min-height:440px"></div>

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="/v5/wxcharts.js"></script>
<script>
//<![CDATA[
(function () {
	var current = 'temp';
	function pad(n) { return (n < 10 ? '0' : '') + n; }
	function load() {
		var y = $('#gv-year').val();
		var m = pad(parseInt($('#gv-month').val(), 10));
		var d = pad(parseInt($('#gv-day').val(), 10));
		var url = '/v5/intradaydata.php?date=' + y + m + d + '&num=' + $('#gv-num').val();
		NW3.intradayPanel('gv-chart', url, current, '#gv-vars button');
	}
	$(function () {
		$('#gv-vars').on('click', 'button', function () { current = $(this).data('var'); });
		$('#gv-go').on('click', load);
		$('#gv-num, #gv-year, #gv-month, #gv-day').on('change', load);
		load();
	});
})();
//]]>
</script>

<?php Page::End(); ?>
