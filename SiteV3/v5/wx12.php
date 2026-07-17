<?php
require "Page.php";
require "ViewDetailedData.php";
Page::init([
	"fileNum" => 12,
	"title" => "Rain Detail",
	"description" => 'Detailed latest rainfall data, graphs and records from NW3 weather station.'
]);
Page::Start();
?>

<h1>Detailed Rainfall Data</h1>

<?php
$mainTables = new ViewDetailedData("rain");

$rn24 = isset(Live::$HR24['trendRn'][0]) ? Live::$HR24['trendRn'][0] : null;
$measures = ['Rainfall Today', 'Rain Rate', 'Rain Last Hour', 'Rain Last 24 hrs', 'Wet Hours Today', 'Rain Duration', 'Most Recent Rain'];
$values = [
	Live::$rain,
	Live::$NOW['misc']['rnrate'],
	(isset($rn24, Live::$HR24['trendRn'][1])) ? $rn24 - Live::$HR24['trendRn'][1] : null,
	$rn24,
	Live::$NOW['misc']['wethrs'],
	Live::$NOW['misc']['rnduration'],
	Live::$HR24['misc']['rnlast'],
];
$conv = [Wx::Rain, Wx::RainRate, Wx::Rain, Wx::Rain, Wx::Hours, Wx::Hours, Wx::None];

$mainTables->currentLatest($measures, $values, $conv);

$measures2 = array('Lowest 10-min Rate', 'Highest Hourly Rate', 'Highest 10-min Rate', 'Lowest Hourly Rate',
	'Driest Day', 'Wettest Day', 'Averages', 'Mean Daily Total', 'Mean Daily 10-min', 'Mean Daily Hourly');
$mainTables->avgsExtrmsRecs($measures2);
$mainTables->pastYearAvgsExtrms($measures2);
$mainTables->rankTables();
?>

<?php
Charts::daily(['type' => 'rain', 'mode' => 'monthly', 'length' => 12, 'summary_type' => Data::SUMMARY_SUM, 'lta' => 1], ['height' => 400]);
Charts::daily(['type' => 'rain', 'mode' => 'daily', 'year' => Date::$yr_yest, 'month' => 0, 'cume' => 1, 'lta' => 1], ['height' => 460]);
?>

<p><a href="/charts.php?dtype=rain">View more rain charts</a></p>

<h2>Notes</h2>
<ul>
	<li>Rain records began in February 2009</li>
	<li>The minimum recordable rain (the rain gauge resolution) is 0.2 mm</li>
	<li>Figures in brackets refer to departure from
		<a href="/wxaverages.php" title="Long-term NW3 climate averages">average conditions</a>
	</li>
	<li>Rain rate records are manually checked, and changed if necessary, due to occasional lag in data
		transmission. Initial high readings may well be corrected at a later date.</li>
</ul>

<p><a href="/wxdataday.php?vartype=rain" title="<?php echo Date::$dyear; ?> daily rain totals"><b>View daily totals for the past year</b></a></p>

<?php Page::End(); ?>
