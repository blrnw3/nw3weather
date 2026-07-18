<?php
require "Page.php";
require "Report.php";
Page::init([
	"fileNum" => 43,
	"title" => "Ranked Monthly Data",
	"description" => "Historical ranked monthly weather values for Hampstead, London (NW3) - temperature, rainfall, wind and more.",
	"needValcolStyle" => true,
]);
Page::Start();

$report = new Report(["default" => "rain", "badCats" => ["cloud"]]);
$report->controls([
	"heading" => "Historical Ranked Monthly Data",
	"showYear" => false,
	"showMonth" => true,
	"showStartYear" => true,
	"isDaily" => false,
	"linkToOther" => "RankDay",
	"showTabs" => true,
]);

$type = $report->type;
$month = $report->month;
$startY = $report->startYrReport;
$endY = (int)Date::$dyear;

$footCond = ($month === 0 || $month == (int)Date::$dmonth);
$yearSecs = 86400 * 365;
$curMonthTs = Date::mkdate((int)Date::$dmonth, 1, (int)Date::$dyear);
$lastMonthTs = Date::mkdate((int)Date::$dmonth - 1, 1, (int)Date::$dyear);

$explain = [
	Data::SUMMARY_MEAN => 'highest and lowest monthly means',
	Data::SUMMARY_SUM => 'highest and lowest monthly totals',
	Data::SUMMARY_COUNT => 'most and fewest days with a non-zero value',
	Data::SUMMARY_MIN => 'highest and lowest monthly minima',
	Data::SUMMARY_MAX => 'highest and lowest monthly maxima',
];

foreach ($report->availSummaryTypes as $st) {
	$ranked = [];
	foreach (Data::getMonthlySummary($type, $st, $startY, $endY) as $y => $months) {
		foreach ($months as $m => $v) {
			if (($month === 0 || $m === $month) && !Util::isBlank($v)) {
				$ranked[] = [(float)$v, Date::mkdate($m, 1, $y)];
			}
		}
	}
	sort($ranked);
	$sortLen = count($ranked);
	$limit = ($sortLen / 2 < $report->rankLimit) ? 1 + (int)($sortLen / 2) : $report->rankLimit;

	$highest = $lowest = $highestDay = $lowestDay = [];
	$fmtMon = function ($ts) use ($yearSecs) {
		$s = date('M Y', $ts) . (date('Y', $ts) < 2009 ? '*' : '');
		if ((Date::$dtstamp - $ts) < $yearSecs) { $s = "<b>$s</b>"; }
		return $s;
	};
	for ($i = 1; $i <= $limit; $i++) {
		$highest[$i] = $ranked[$sortLen - $i][0];
		$highestDay[$i] = $fmtMon($ranked[$sortLen - $i][1]);
		$lowest[$i] = $ranked[$i - 1][0];
		$lowestDay[$i] = $fmtMon($ranked[$i - 1][1]);
	}

	foreach ($ranked as $rnk => $val) {
		if ($val[1] === $curMonthTs) {
			$highest['today'] = $lowest['today'] = $val[0];
			$lowestDay['today'] = $rnk + 1;
			$highestDay['today'] = $sortLen - $rnk;
		}
		if ($val[1] === $lastMonthTs) {
			$highest['yest'] = $lowest['yest'] = $val[0];
			$lowestDay['yest'] = $rnk + 1;
			$highestDay['yest'] = $sortLen - $rnk;
		}
	}

	$sumfix = ($st === Data::SUMMARY_SUM) ? $report->valcolSumOffset() : 1;
	$isCount = ($st === Data::SUMMARY_COUNT);
	$name = Data::$SUMMARY_NAMES[$st];
	$hide = ($st === $report->summaryType) ? '' : "style='display:none'";
	echo "<div id='rank-$st' class='rank-tab' $hide>";
	$report->rankTable($highest, $highestDay, $limit, "Top monthly $name", true, true, $footCond, false, $isCount, $sumfix);
	$report->rankTable($lowest, $lowestDay, $limit, "Bottom monthly $name", false, true, $footCond, false, $isCount, $sumfix);
	$extraMon = ($month === 0) ? '' : ' for ' . Date::$months3[$month - 1];
	echo "<p style='clear:both'>" . $report->description . " monthly extremes: " . $explain[$st] . ".<br />"
		. "There are <b>$sortLen</b> valid months for the chosen period from $startY to present$extraMon in London, NW3.</p>";
	echo "</div>";
}

$report->rankLimitForm();
$report->historicalInfo();
?>
<script>//<![CDATA[
function changeTab(t){
	var tabs=document.getElementsByClassName('rank-tab');
	for(var i=0;i<tabs.length;i++){ tabs[i].style.display='none'; }
	var el=document.getElementById('rank-'+t); if(el){ el.style.display=''; }
	var btns=document.getElementsByClassName('rank-tab-button');
	for(var j=0;j<btns.length;j++){ btns[j].disabled=false; }
	var btn=document.getElementById('rank-btn-'+t); if(btn){ btn.disabled=true; }
	var hid=document.getElementById('summary-type-input'); if(hid){ hid.value=t; }
}
//]]></script>
<?php
Page::End();
