<?php
require "Page.php";
require "Report.php";
Page::init([
	"fileNum" => 42,
	"title" => "Ranked Daily Data",
	"description" => "Historical ranked daily weather values for Hampstead, London (NW3) - temperature, rainfall, wind and more.",
	"needValcolStyle" => true,
]);
Page::Start();

$report = new Report(["default" => "rain", "badCats" => ["cloud"]]);
$report->controls([
	"heading" => "Historical Ranked Daily Data",
	"showYear" => false,
	"showMonth" => true,
	"showStartYear" => true,
	"isDaily" => true,
	"linkToOther" => "RankMonth",
]);

$type = $report->type;
$month = $report->month;
$startY = $report->startYrReport;

// Build the ranked list of [value, timestamp] for the chosen month(s).
$ranked = [];
foreach (Data::getDailyData($type, $startY) as $y => $months) {
	foreach ($months as $m => $daily) {
		if ($month == 0 || $m == $month) {
			foreach ($daily as $d => $v) {
				if (!Util::isBlank($v)) {
					$ranked[] = [(float)$v, Date::mkdate($m, $d, $y)];
				}
			}
		}
	}
}
sort($ranked);
$sortLen = count($ranked);

$fmat = isset($_GET['withdayofweek']) ? 'D d M Y' : 'd M Y';
$yearSecs = 86400 * 365;

$fmtDay = function ($ts) use ($fmat, $yearSecs) {
	$s = date($fmat, $ts) . (date('Y', $ts) < 2009 ? '*' : '');
	if ((Date::$dtstamp - $ts) < $yearSecs) { $s = "<b>$s</b>"; }
	return $s;
};

$highest = $lowest = $highestDay = $lowestDay = [];
$limit = min($sortLen, $report->rankLimit);
for ($i = 1; $i <= $limit; $i++) {
	$highest[$i] = $ranked[$sortLen - $i][0];
	$highestDay[$i] = $fmtDay($ranked[$sortLen - $i][1]);
	$lowest[$i] = $ranked[$i - 1][0];
	$lowestDay[$i] = $fmtDay($ranked[$i - 1][1]);
}

// Locate today / yesterday within the ranking.
foreach ($ranked as $rnk => $val) {
	if ($val[1] === Date::$dtstamp) {
		$highest['today'] = $lowest['today'] = $val[0];
		$lowestDay['today'] = $rnk + 1;
		$highestDay['today'] = $sortLen - $rnk;
	}
	if ($val[1] === Date::$dtstamp_yest) {
		$highest['yest'] = $lowest['yest'] = $val[0];
		$lowestDay['yest'] = $rnk + 1;
		$highestDay['yest'] = $sortLen - $rnk;
	}
}

$footCond = ($month === 0 || $month == (int)Date::$dmonth);
$report->rankTable($highest, $highestDay, $limit, "Highest", true, $report->hasToday, $footCond, true);
$report->rankTable($lowest, $lowestDay, $limit, "Lowest", false, $report->hasToday, $footCond, true);

$report->rankLimitForm();

$extraMon = ($month === 0) ? '' : ' in ' . Date::$months3[$month - 1];
$extraCount = '';
if ($report->isSum) {
	$c = 0;
	foreach ($ranked as $v) { if ($v[0] > 0) { $c++; } }
	$extraCount = ", and $c values > 0";
}

echo "<p style='clear:both'>Ranked daily data from $startY to present for London, NW3. For "
	. $report->description . ", there are <b>$sortLen</b> valid values$extraMon$extraCount.</p>";

$report->historicalInfo();

Page::End();
