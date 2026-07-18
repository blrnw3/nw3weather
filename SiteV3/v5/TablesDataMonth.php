<?php
require "Page.php";
require "Report.php";
Page::init([
	"fileNum" => 41,
	"title" => "Monthly Data Tables",
	"description" => "Detailed historical monthly summary data tables with all-time summaries for Hampstead, London (NW3).",
	"needValcolStyle" => true,
]);
Page::Start();

$report = new Report(["default" => "rain", "badCats" => ["cloud"]]);
$report->controls([
	"heading" => "Monthly Data Tables",
	"showYear" => false,
	"showStartYear" => true,
	"isDaily" => false,
	"linkToOther" => "wxdataday",
	"showTabs" => true,
]);

if ($report->isNotSummarisable) {
	echo "<p>" . $report->description . " has no meaningful monthly summary.</p>";
	Page::End();
	return;
}

/**
 * Renders one year-by-month grid for a given aggregation.
 * @param Report $report
 * @param array $DAT [year][month][day] => val
 * @param int $t aggregation: 0=min 1=max 2=mean/total 3=count
 * @param string $heading
 * @param int $startYr
 */
function tdmMakeTable($report, $DAT, $t, $heading, $startYr) {
	$edge = 'border-left: 5px solid #565';
	$unit = $report->unit;
	$isCount = ($t === 3);
	$isAnom = $report->isAnom;
	$sumfix = ($report->isSum && $t === 2) ? $report->valcolSumOffset() : 1;
	$yrYest = (int)Date::$yr_yest;
	$monYest = (int)Date::$mon_yest;

	HTML::table('table1" style="margin-bottom: 15px;', null, 6);
	HTML::tableHead($heading . ' ' . $report->description, 14);

	HTML::tr();
	HTML::td('', 'td4C sticky-head', '7%');
	for ($m = 1; $m <= 12; $m++) { HTML::td(Date::$months3[$m - 1], 'td4C sticky-head', '7%'); }
	HTML::td('Year', 'td4 sticky-head" style="' . $edge, '9%');
	HTML::tr_end();

	$extreme = [];   // [year][month] and [month][year]
	$yrAgg = [];     // [year] => annual aggregate

	for ($y = $yrYest; $y >= $startYr; $y--) {
		HTML::tr(null);
		HTML::td("<a href='/wxdataday.php?vartype=" . $report->type . "&year=$y' title='View full data for year'>$y</a>",
			'row' . HTML::colcol($y) . '" style="text-align:center; font-weight:bold');

		for ($m = 1; $m <= 12; $m++) {
			$days = isset($DAT[$y][$m]) && is_array($DAT[$y][$m]) ? array_filter($DAT[$y][$m], ['Util', 'clearblank']) : [];
			$cnt = count($days);
			$anom = ''; $value = '-'; $class = 'reportday';

			if ($cnt >= 1) {
				$val = Report::aggregate($days, $t);
				if (Util::isBlank($val)) {
					$value = ''; $class = 'invalid';
				} else {
					if ($report->isSum && $t === 2) { $val *= $cnt; }
					$extreme[$y][$m] = $val;
					$extreme[$m][$y] = $val;
					$value = $isCount ? round($val, 1) : Wx::conv($val, $unit, false);
					$num = $report->valcolConvert ? Wx::convNum($val, $unit) : (float)$val;
					$class = $report->valcolr(($num === null ? 0 : $num) / $sumfix, $isCount);
					$anom = ($isAnom && $t === 2) ? '<br />(' . $report->anomMonth($val, $m) . ')' : '';
				}
			}
			$lnk = "/wxhistmonth.php?year=$y&month=$m";
			$linked = ($y >= 2009 && ($y < $yrYest || $m <= $monYest)) ?
				'<a class="hidden-link" href="' . $lnk . '" title="View detailed report for month">' . $value . '</a>' : $value;
			HTML::td($linked . $anom, $class);
		}

		// Annual summary cell
		$months = isset($extreme[$y]) ? $extreme[$y] : [];
		if (count($months) === 0) {
			HTML::td('-', 'reportday" style="' . $edge);
		} else {
			$yrVal = $isCount ? Util::mean($months) : Report::aggregate($months, $t);
			$cntM = 1;
			if ($report->isSum && $t >= 2) { $cntM = count($months); $yrVal *= $cntM; }
			$yrAgg[$y] = $yrVal;
			$anom = ($isAnom && $t === 2) ? '<br />(' . $report->anomYear($yrVal) . ')' : '';
			$valyr = $isCount ? round($yrVal, 1) : Wx::conv($yrVal, $unit, false);
			$class = $report->valcolr($yrVal / $sumfix / $cntM, $isCount);
			HTML::td($valyr . $anom, $class . '" style="' . $edge);
		}
		HTML::tr_end();
	}

	// Repeat month header
	HTML::tr();
	HTML::td('', 'td4C', '7%');
	for ($m = 1; $m <= 12; $m++) { HTML::td(Date::$months3[$m - 1], 'td4C', '7%'); }
	HTML::td('Year', 'td4" style="' . $edge, '9%');
	HTML::tr_end();

	// All-time summary rows across years: Lowest / Highest / Mean
	$labels = ['Lowest', 'Highest', 'Mean'];
	for ($mm = 0; $mm < 3; $mm++) {
		$extra = ($mm === 0) ? '" style="border-top:10px solid #cdc' : '';
		HTML::tr(null);
		HTML::td($labels[$mm], 'reportttl' . $extra);
		for ($m = 1; $m <= 12; $m++) {
			$col = isset($extreme[$m]) ? $extreme[$m] : [];
			if (count($col) === 0) { HTML::td('-', 'reportday' . $extra); continue; }
			$v = Report::aggregate($col, $mm);
			$value = $isCount ? round($v, 1) : Wx::conv($v, $unit, false);
			$anom = ($isAnom && $t === 2) ? '<br />(' . $report->anomMonth($v, $m) . ')' : '';
			$class = $report->valcolr($v / $sumfix, $isCount);
			HTML::td($value . $anom, $class . $extra);
		}
		if (count($yrAgg) === 0) { HTML::td('-', 'reportday' . $extra); }
		else {
			$yv = Report::aggregate($yrAgg, $mm);
			$valall = $isCount ? round($yv, 1) : Wx::conv($yv, $unit, false);
			$anom = ($isAnom && $t === 2) ? '<br />(' . $report->anomYear($yv) . ')' : '';
			HTML::td($valall . $anom, $report->valcolr($yv / $sumfix, $isCount) . $extra . '" style="' . $edge);
		}
		HTML::tr_end();
	}

	HTML::table_end();
}

$DAT = Data::getDailyData($report->type, $report->startYrReport);

$newToOld = [0 => 2, 1 => 2, 2 => 3, 3 => 0, 4 => 1];
$explain = [
	Data::SUMMARY_MEAN => 'monthly means',
	Data::SUMMARY_SUM => 'monthly totals',
	Data::SUMMARY_COUNT => 'count of days with a non-zero value',
	Data::SUMMARY_MIN => 'lowest daily value each month',
	Data::SUMMARY_MAX => 'highest daily value each month',
];

foreach ($report->availSummaryTypes as $st) {
	$hide = ($st === $report->summaryType) ? '' : "style='display:none'";
	echo "<div id='rank-$st' class='rank-tab scroll' $hide>";
	tdmMakeTable($report, $DAT, $newToOld[$st], 'Monthly ' . ucfirst(Data::$SUMMARY_NAMES[$st]), $report->startYrReport);
	echo "<p>" . $report->description . ": " . $explain[$st] . " for all months since " . $report->startYrReport . " in London, NW3.</p>";
	echo "</div>";
}

if ($report->isAnom) {
	echo '<p>Figures in brackets refer to departure from <strong>recent</strong> '
		. '<a href="/wxaverages.php" title="Long-term NW3 climate averages">average conditions</a> '
		. "(the anomaly for the current month is unadjusted for the month's degree of completeness).</p>";
}

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
