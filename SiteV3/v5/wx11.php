<?php
require "Page.php";
require "ViewDetailedData.php";
Page::init([
	"fileNum" => 11,
	"title" => "Sunshine Detail",
	"description" => 'Detailed sunshine hours data, graphs and records from NW3 weather station.'
]);
Page::Start();
?>

<h1>Detailed Sunshine Data</h1>

<?php
$mainTables = new ViewDetailedData("sun");

$yestSun = Data::get('sunhr', Date::$yr_yest, Date::$mon_yest, Date::$day_yest);
$yestPct = Data::get('sunhrp', Date::$yr_yest, Date::$mon_yest, Date::$day_yest);
$maxPoss = LTA::getDailyAnom('maxsun', (int)Date::$dmonth, (int)Date::$dday, (int)Date::$dyear);

$measures = [
	"Yesterday's Sunshine",
	"Yesterday's % of Max",
	'Max Possible Today',
];
$values = [$yestSun, $yestPct, $maxPoss];
$conv = [Wx::Hours, Wx::Percentage, Wx::Hours];

$mainTables->currentLatest($measures, $values, $conv);

$mainTables->avgsExtrmsRecs();
$mainTables->pastYearAvgsExtrms();
$mainTables->rankTables();
?>

<?php
Charts::daily([
	'type' => 'sunhr',
	'mode' => 'daily',
	'year' => Date::$yr_yest,
	'month' => 0,
	'cume' => 1,
	'lta' => 1,
], ['height' => 460]);
?>

<p><a href="/charts.php?dtype=sunhr">View more sunshine charts</a></p>

<h2>Notes</h2>
<ul>
	<li>Sunshine totals are only available for prior days — never the current day</li>
	<li>Sunshine records at NW3 began in 2009; longer-term climate normals use data from 1910</li>
	<li>Figures in brackets refer to departure from
		<a href="/wxaverages.php" title="Long-term NW3 climate averages">average conditions</a>
		(as a percentage for sunshine)
	</li>
	<li>Sun days are days with more than 0 hours of sunshine recorded</li>
	<li>% of max is sunshine hours as a percentage of the maximum possible for that date</li>
</ul>

<p><a href="/wxdataday.php?vartype=sunhr" title="<?php echo Date::$dyear; ?> daily sun totals"><b>View daily totals for the past year</b></a></p>


<?php Page::End(); ?>
