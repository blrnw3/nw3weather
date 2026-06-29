<?php
require("Page.php");
Page::init([
	"fileNum" => 31,
	"isSubFile" => true,
	"title" => "Daily Graph Archive",
	"description" => "Browse the archive of daily 24-hour weather graphs for Hampstead, London, back to February 2009."
]);
Page::Start();

$vars = ['temp' => 'Temperature', 'dewp' => 'Dew point', 'humi' => 'Humidity', 'pres' => 'Pressure',
	'wind' => 'Wind speed', 'gust' => 'Gust', 'wdir' => 'Wind direction', 'rain' => 'Rainfall', 'pm25' => 'Air quality'];
?>

<h1>Daily Graph Archive</h1>
<p>Per-minute weather for any single day since 1 February 2009.</p>

<div class="chart-viewer-controls">
	<button type="button" id="ga-prev" title="Previous day">&#9664; Prev</button>
	<label>Date
		<select id="ga-year">
			<?php for ($y = (int)Date::$dyear; $y >= 2009; $y--) { echo '<option value="' . $y . '">' . $y . '</option>'; } ?>
		</select>
		<select id="ga-month">
			<?php for ($m = 1; $m <= 12; $m++) { echo '<option value="' . $m . '"' . ($m === (int)Date::$dmonth ? ' selected="selected"' : '') . '>' . Date::$months3[$m - 1] . '</option>'; } ?>
		</select>
		<select id="ga-day">
			<?php for ($d = 1; $d <= 31; $d++) { echo '<option value="' . $d . '"' . ($d === (int)Date::$dday ? ' selected="selected"' : '') . '>' . Util::zerolead($d) . '</option>'; } ?>
		</select>
	</label>
	<button type="button" id="ga-next" title="Next day">Next &#9654;</button>
	<div class="wxchart-vars" id="ga-vars">
		<?php $first = true; foreach ($vars as $k => $label) {
			echo '<button type="button" data-var="' . $k . '"' . ($first ? ' class="active"' : '') . '>' . $label . '</button>';
			$first = false;
		} ?>
	</div>
</div>

<h2 id="ga-title"></h2>
<div id="ga-chart" class="wxchart" style="min-height:440px"></div>

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="/v5/wxcharts.js"></script>
<script>
//<![CDATA[
(function () {
	var current = 'temp';
	var MONTHS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
	function pad(n) { return (n < 10 ? '0' : '') + n; }
	function currentDate() {
		return new Date(parseInt($('#ga-year').val(), 10), parseInt($('#ga-month').val(), 10) - 1, parseInt($('#ga-day').val(), 10));
	}
	function setDate(dt) {
		$('#ga-year').val(dt.getFullYear());
		$('#ga-month').val(dt.getMonth() + 1);
		$('#ga-day').val(dt.getDate());
	}
	function load() {
		var y = $('#ga-year').val(), m = pad(parseInt($('#ga-month').val(), 10)), d = pad(parseInt($('#ga-day').val(), 10));
		$('#ga-title').text(parseInt(d, 10) + ' ' + MONTHS[parseInt(m, 10) - 1] + ' ' + y);
		NW3.intradayPanel('ga-chart', '/v5/intradaydata.php?date=' + y + m + d + '&num=1', current, '#ga-vars button');
	}
	function shift(days) {
		var dt = currentDate(); dt.setDate(dt.getDate() + days);
		var min = new Date(2009, 1, 1), max = new Date();
		if (dt < min) { dt = min; } if (dt > max) { dt = max; }
		setDate(dt); load();
	}
	$(function () {
		$('#ga-vars').on('click', 'button', function () { current = $(this).data('var'); });
		$('#ga-prev').on('click', function () { shift(-1); });
		$('#ga-next').on('click', function () { shift(1); });
		$('#ga-year, #ga-month, #ga-day').on('change', load);
		load();
	});
})();
//]]>
</script>

<?php Page::End(); ?>
