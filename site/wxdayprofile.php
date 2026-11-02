<?php
require "Page.php";
require_once "DayProfile.php";
Page::init([
	"fileNum" => 20,
	"isSubFile" => true,
	"title" => "Daily profile",
	"description" => "Average weather through the day in Hampstead, London NW3: temperature, humidity, pressure, wind, rain and sunshine at every time of day, by month and year, from quality-checked station data since 2011.",
]);
Page::Start();

$meta = DayProfile::meta();
$var = isset($_GET['var']) && isset(DayProfile::$VARS[$_GET['var']]) ? $_GET['var'] : 'temp';
$years = $meta ? DayProfile::years($meta, $var) : [];
$year = isset($_GET['year']) ? (int)$_GET['year'] : 0;
if ($year && !in_array($year, $years, true)) { $year = 0; }
$month = isset($_GET['month']) ? (int)$_GET['month'] : 0;
if ($month < 0 || $month > 12) { $month = 0; }
$payload = $meta ? DayProfile::payload($var, $year, $month) : null;

$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$link = function ($v, $y, $m) {
	$q = ['var' => $v];
	if ($y) { $q['year'] = $y; }
	if ($m) { $q['month'] = $m; }
	return 'wxdayprofile.php?' . http_build_query($q);
};
$h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
$allYears = [];
$varYears = [];
foreach (DayProfile::$VARS as $v => $c) {
	$varYears[$v] = $meta ? DayProfile::years($meta, $v) : [];
	$allYears = array_merge($allYears, $varYears[$v]);
}
$allYears = array_values(array_unique($allYears));
// years whose profile is (partly) model-estimated: sunshine before the live webcam log
$estYears = ['sun' => []];
foreach (($meta['vars']['sun']['estYears'] ?? []) as $y => $n) { $estYears['sun'][(int)$y] = (int)$n; }
rsort($allYears);
?>
<style>
.report-sel .wxsel-chip.dp-off { opacity: 0.35; pointer-events: none; }
.report-sel .wxsel-chip.est::after { content: '*'; }
#main .report-sel .wxsel-chip[hidden] { display: none; }
#dp-chart { min-height: 440px; margin-top: 4px; }
.dp-summary { font-size: 0.9em; margin: 4px 0; }
.dp-note, .dp-foot { font-size: 0.85em; color: #555; margin: 3px 0; }
table.dp-q { border-collapse: collapse; width: 100%; font-size: 0.85em; margin: 6px 0; }
table.dp-q th, table.dp-q td { border-bottom: 1px solid #ddd; padding: 3px 5px; text-align: left; vertical-align: top; }
table.dp-q td.num, table.dp-q th.num { text-align: right; white-space: nowrap; }
details.dp-rules summary { cursor: pointer; font-size: 0.9em; margin: 6px 0; }
details.dp-rules li { font-size: 0.85em; margin: 2px 0; }
@media (max-width: 600px) {
	#dp-chart { min-height: 340px; }
	table.dp-q .dp-why { display: none; }
}
</style>

<h1>Daily profile</h1>
<p>
	How the weather typically changes through the day: the average at every time of day, from each clean minute of
	station data since 2011. Pick a variable, then a year, a month (that month in every year), or both. The dashed line
	compares a year or month with the long-term profile. Drag across the chart to zoom in.
</p>

<?php if (!$payload) { ?>
	<p><b>The daily profiles are being prepared. Please check back later.</b></p>
<?php Page::End(); exit; } ?>

<?php
// Selectors in the data tables' style (Report button selectors): variable groups, then their measures, then period.
$groupOrder = ['temp' => ['temp'], 'rain' => ['rain', 'wet'], 'pres' => ['pres'], 'wind' => ['wind', 'gust'],
	'humi' => ['humi'], 'dewp' => ['dewp'], 'sun' => ['sun']];
$chipLabel = ['temp' => 'Temperature', 'dewp' => 'Dew point', 'humi' => 'Humidity', 'pres' => 'Pressure',
	'wind' => 'Mean speed', 'gust' => 'Gust', 'rain' => 'Rain rate', 'wet' => 'Wet % of time', 'sun' => 'Sunshine % of time'];
$groupMeta = [];
foreach (Charts::selectableGroups() as $g) { $groupMeta[$g['id']] = $g; }
$activeGroup = 'temp';
foreach ($groupOrder as $gid => $vs) { if (in_array($var, $vs, true)) { $activeGroup = $gid; } }
$recentYears = array_slice($allYears, 0, 5);
$olderYears = array_slice($allYears, 5);
$yearChip = function ($y) use ($h, $link, $var, $year, $month, $years, $estYears) {
	$cls = ($y === $year ? ' active' : '') . (in_array($y, $years, true) ? '' : ' dp-off') . (isset($estYears[$var][$y]) ? ' est' : '');
	return '<a rel="nofollow" class="wxsel-chip' . $cls . '" data-v="' . $y . '" href="' . $h($link($var, $y, $month)) . '">' . $y . '</a>';
};
?>
<div class="report-sel" id="dp-controls">
	<div class="report-sel-row"><div class="wxsel-groups" role="tablist">
	<?php foreach ($groupOrder as $gid => $vs) {
		$g = $groupMeta[$gid] ?? ['label' => DayProfile::$VARS[$vs[0]]['short']];
		$icon = isset($g['icon']) ? '<img src="' . $h($g['icon']) . '" alt="" width="16" height="16" />' : '';
		echo '<button type="button" class="' . ($gid === $activeGroup ? 'active' : '') . '" data-group="' . $h($gid)
			. '" data-default-type="' . $h($vs[0]) . '" title="' . $h($g['label']) . '">' . $icon . '<span>' . $h($g['label']) . '</span></button>';
	} ?>
	</div></div>
	<div class="report-sel-row"><div class="wxsel-subtypes" role="tablist" data-dp="var">
	<?php foreach ($groupOrder as $gid => $vs) {
		foreach ($vs as $v) {
			echo '<a class="wxsel-chip' . ($v === $var ? ' active' : '') . '" data-v="' . $h($v) . '" data-group="' . $h($gid) . '"'
				. ($gid === $activeGroup ? '' : ' hidden') . ' href="' . $h($link($v, $year, $month)) . '">' . $h($chipLabel[$v]) . '</a>';
		}
	} ?>
	</div></div>
	<div class="report-sel-row report-sel-labelled"><div class="wxsel-label">Year</div>
	<div class="wxsel-scale wxsel-years" role="tablist" data-dp="year">
	<?php
	echo '<a rel="nofollow" class="wxsel-chip' . (!$year ? ' active' : '') . '" data-v="0" href="' . $h($link($var, 0, $month)) . '">All</a>';
	foreach ($recentYears as $y) { echo $yearChip($y); }
	if ($olderYears) {
		$inOlder = in_array($year, $olderYears, true);
		echo '<details class="wxsel-overflow"><summary class="wxsel-chip' . ($inOlder ? ' active' : '') . '">' . ($inOlder ? $year : 'Older') . '</summary>';
		echo '<div class="wxsel-overflow-menu">';
		foreach ($olderYears as $y) { echo $yearChip($y); }
		echo '</div></details>';
	} ?>
	</div></div>
	<div class="report-sel-row report-sel-labelled"><div class="wxsel-label">Month</div>
	<div class="wxsel-scale wxsel-months" role="tablist" data-dp="month">
	<?php
	echo '<a rel="nofollow" class="wxsel-chip' . (!$month ? ' active' : '') . '" data-v="0" href="' . $h($link($var, $year, 0)) . '">All</a>';
	foreach ($months as $i => $mn) {
		echo '<a rel="nofollow" class="wxsel-chip' . ($month === $i + 1 ? ' active' : '') . '" data-v="' . ($i + 1) . '" href="' . $h($link($var, $year, $i + 1)) . '">' . $mn . '</a>';
	} ?>
	</div></div>
	<div class="report-sel-row report-sel-labelled"><div class="wxsel-label">Average over</div>
	<div class="wxsel-scale" role="tablist" id="dp-bins">
		<button type="button" class="wxsel-chip" data-bin="5">5 min</button>
		<button type="button" class="wxsel-chip" data-bin="30">30 min</button>
		<button type="button" class="wxsel-chip" data-bin="60">1 hour</button>
	</div></div>
	<div class="report-sel-row report-sel-labelled" id="dp-cmp-wrap"><div class="wxsel-label">Compare with</div>
	<div class="wxsel-scale">
		<button type="button" class="wxsel-chip active" id="dp-cmp" aria-pressed="true">Long-term profile</button>
	</div></div>
</div>

<div id="dp-chart" class="wxchart"></div>
<p class="dp-summary" id="dp-summary"></p>
<p class="dp-note" id="dp-note"></p>
<p class="dp-foot" id="dp-foot"></p>

<?php
$notes = [
	'temp' => 'Air temperature from the screen at the station.',
	'dewp' => 'Dew point, from temperature and humidity; it leaves out the humidity sensor\'s drift periods.',
	'humi' => 'Relative humidity. The sensor never reads above 98-99%, so foggy or saturated nights read slightly low.',
	'pres' => 'Sea-level pressure. The curve shows the twice-daily atmospheric tide; levels before and after the 2017 recalibration differ by about 1 hPa.',
	'wind' => 'Mean wind speed (1-minute). The current sensor (since Dec 2024) reads a little higher than the old one.',
	'gust' => 'Highest gust in each minute.',
	'rain' => 'Average rain per hour at each time of day, counting dry days. Each day\'s minute-log rain is scaled to that day\'s checked daily total, so hand corrections (false or reassigned tips) carry through.',
	'wet' => 'Share of the time it was raining, from rain-gauge tips: tips up to 30 minutes apart count as one spell, starting 15 minutes before the first tip. From Apr 2014 (finer gauge).',
	'sun' => 'Share of the time it was sunny (night counts as not sunny), from the webcam sunshine model. From late May 2025 this is the live per-minute webcam log. Sep 2021 to May 2025 (years marked *) is model-estimated: the same model run over the archived webcam frames (every 7.5-15 minutes), each day scaled to its official sunshine total, so it gives the shape of the day rather than exact minutes. Nov 2025 to early Apr 2026 is left out until it has been reviewed.',
];
$js = [
	'payload' => $payload,
	'months' => $months,
	'notes' => $notes,
	'varYears' => $varYears,
	'estYears' => $estYears,
	'vars' => array_map(function ($c) { return ['name' => $c['name'], 'short' => $c['short']]; }, DayProfile::$VARS),
	'built' => isset($meta['stats']['built']) ? date('j M Y H:i', $meta['stats']['built']) : null,
];
Charts::assets();
echo '<script src="/dayprofile.js?20261102j"></script>' . "\n";
Charts::run('NW3DayProfile.init(' . nw3_json_encode($js) . ');');
?>

<h2>Data quality</h2>
<p>
	Only the station's own, well-performing data is used. Whole days are left out for a variable when its sensor was
	faulty, drifting or stalled, when another station's data was filled in, on clock-change days, and when the
	station notes report a fault. Within each day, repeated or slow (externally filled) feed rows, flatlines, spikes
	and out-of-range readings are removed minute by minute, and days with too little clean data left are dropped.
</p>
<table class="dp-q">
	<tr><th>Variable</th><th>From</th><th class="num">Days used</th><th class="num">Coverage</th><th class="dp-why">Main reasons days were left out</th></tr>
	<?php foreach (DayProfile::$VARS as $v => $c) {
		$m = $meta['vars'][$v] ?? null;
		if (!$m) { continue; }
		$why = [];
		foreach (array_slice($m['excluded'], 0, 3, true) as $r => $cnt) {
			$why[] = $h(DayProfile::$REASONS[$r] ?? $r) . ' (' . number_format($cnt) . ')';
		}
		echo '<tr><td>' . $h($c['name']) . '</td><td>' . $h(date('M Y', strtotime($m['from']))) . '</td>'
			. '<td class="num">' . number_format($m['used']) . ' of ' . number_format($m['possible']) . '</td>'
			. '<td class="num">' . $h($m['pct']) . '%</td><td class="dp-why">' . implode('; ', $why)
			. (!empty($m['estimated']) ? '<br />Of the days used, ' . number_format($m['estimated']) . ' are model-estimated from webcam ('
				. $h(implode(', ', array_keys($m['estYears']))) . ')' : '') . '</td></tr>';
	} ?>
</table>
<details class="dp-rules">
	<summary>Periods left out</summary>
	<ul>
	<?php
	$gname = ['temp' => 'temperature', 'hum' => 'humidity', 'pres' => 'pressure', 'wind' => 'wind', 'rain' => 'rain', 'sun' => 'sunshine'];
	foreach (DayProfile::$RANGES as $r) {
		$gs = count($r[2]) >= count(DayProfile::ALL) ? 'all station data' : implode(', ', array_map(function ($g) use ($gname) { return $gname[$g]; }, $r[2]));
		$span = $r[0] === $r[1] ? date('j M Y', strtotime($r[0])) : date('j M Y', strtotime($r[0])) . ' – ' . date('j M Y', strtotime($r[1]));
		echo '<li>' . $h($span) . ' (' . $h($gs) . '): ' . $h($r[3]) . '</li>';
	} ?>
	</ul>
</details>
<p class="dp-foot">
	Times are UK clock time (GMT in winter, BST in summer). Profiles are rebuilt nightly<?php
	echo $js['built'] ? ', last on ' . $h($js['built']) : ''; ?>.
	See also the <a href="wxaverages.php">climate averages</a>.
</p>
<?php
Page::End();
