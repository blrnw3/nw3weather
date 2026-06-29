<?php
require "Page.php";
require "Report.php";
Page::init([
	"fileNum" => 40,
	"title" => "Daily Data Tables",
	"description" => "Detailed historical daily data tables with monthly summaries for Hampstead, London (NW3).",
	"needValcolStyle" => true,
]);
Page::Start();

$report = new Report(["default" => "rain", "badCats" => ["cloud"]]);
$report->controls([
	"heading" => "Daily Data Tables",
	"showYear" => true,
	"isDaily" => true,
	"linkToOther" => "TablesDataMonth",
]);

$type = $report->type;
$year = $report->year;
$unit = $report->unit;
$data = Data::getDailyDataForYear($type, $year);

$today = Date::mkdate(Date::$dmonth, Date::$dday, Date::$dyear);

HTML::table();

// Month header row
HTML::tr();
HTML::td('Day', 'td4C', 4);
for ($m = 1; $m <= 12; $m++) {
	$mon = Date::$months3[$m - 1];
	$canLink = ($year < (int)Date::$yr_yest) || ($year == (int)Date::$yr_yest && $m <= (int)Date::$mon_yest);
	$lnk = "/wxhistmonth.php?year=$year&month=$m";
	$label = $canLink ? '<a class="hidden-link" href="' . $lnk . '" title="View detailed report for month">' . $mon . '</a>' : $mon;
	HTML::td($label, 'td4C', 8);
}
HTML::tr_end();

$maxdays = [];
for ($m = 1; $m <= 12; $m++) { $maxdays[$m] = Date::get_days_in_month($m, $year); }

$cumuls = array_fill(1, 12, 0);
$cumcnts = array_fill(1, 12, 0);

for ($day = 1; $day <= 31; $day++) {
	HTML::tr(null);
	HTML::td($day, 'row' . HTML::colcol($day) . '" style="text-align:center');
	for ($m = 1; $m <= 12; $m++) {
		$class = 'reportday';
		$finalVal = '-';
		$showLink = false;
		$val = isset($data[$m][$day]) ? $data[$m][$day] : null;
		if ($maxdays[$m] < $day) {
			$class = 'noday';
			$finalVal = '&nbsp;';
		} elseif ($year == (int)Date::$dyear && Date::mkdate($m, $day, $year) > $today) {
			// day not yet arrived
		} elseif (Util::isBlank($val)) {
			$class = 'invalid';
			$finalVal = '&nbsp;';
			$showLink = true;
		} else {
			$cumuls[$m] += $val;
			$cumcnts[$m]++;
			$finalVal = Wx::conv($val, $unit, false);
			$num = $report->valcolConvert ? Wx::convNum($val, $unit) : (float)$val;
			$class = $report->valcolr($num);
			$showLink = true;
		}
		$lnk = "/wxhistday.php?year=$year&month=$m&day=$day";
		$cell = $showLink ? '<a class="hidden-link" href="' . $lnk . '" title="View detailed report for day">' . $finalVal . '</a>' : $finalVal;
		HTML::td($cell, $class);
	}
	HTML::tr_end();
}

// Spacer + month names again
HTML::tr();
HTML::td('', 'td4C" style="border-top:10px solid white', 4);
for ($m = 1; $m <= 12; $m++) {
	HTML::td(Date::$months3[$m - 1], 'td4C" style="border-top:10px solid white', 8);
}
HTML::tr_end();

// Monthly summary rows: Lowest / Highest / Mean(or Total)
$labels = [0 => 'Lowest', 1 => 'Highest', 2 => ($report->isSum ? 'Total' : 'Mean')];
$sumOffset = $report->valcolSumOffset();
if (!$report->isNotSummarisable) {
	for ($mm = (int)$report->isSum; $mm < 3; $mm++) {
		HTML::tr(null);
		HTML::td($labels[$mm], 'reportttl" style="padding:4px');
		for ($m = 1; $m <= 12; $m++) {
			$vals = isset($data[$m]) && is_array($data[$m]) ? array_filter($data[$m], ['Util', 'clearblank']) : [];
			if (count($vals) === 0) {
				HTML::td('---', 'reportday" style="padding:4px;font-weight:bold');
				continue;
			}
			$put = Report::aggregate($vals, $mm);
			$sumfix = 1;
			if ($report->isSum && $mm == 2) {
				$put *= count($vals);
				$sumfix = $sumOffset;
			}
			$putConv = Wx::conv($put, $unit, false);
			$num = $report->valcolConvert ? Wx::convNum($put, $unit) : (float)$put;
			$class = $report->valcolr($num / $sumfix);
			$anom = ($report->isAnom && $mm == 2) ? '<br />(' . $report->anomMonth($put, $m) . ')' : '';
			HTML::td($putConv . $anom, $class . '" style="padding:4px;font-weight:bold');
		}
		HTML::tr_end();
	}

	// Count row (summable only): number of days with a value > 0
	if ($report->isSum) {
		HTML::tr(null);
		HTML::td('Count', 'reportttl" style="padding:4px');
		for ($m = 1; $m <= 12; $m++) {
			$vals = isset($data[$m]) && is_array($data[$m]) ? array_filter($data[$m], ['Util', 'clearblank']) : [];
			if (count($vals) === 0) {
				HTML::td('---', 'reportday" style="padding:4px;font-weight:bold');
				continue;
			}
			$cnt = Util::cond_count($vals, true, 0);
			$class = $report->valcolr($cnt, true);
			HTML::td($cnt, $class . '" style="padding:4px;font-weight:bold');
		}
		HTML::tr_end();
	}

	// Cumulative row
	HTML::tr(null);
	HTML::td($report->isAnom ? 'Cumu-<br />lative' : 'Cumul', 'reportttl" style="padding:4px');
	for ($m = 1; $m <= 12; $m++) {
		$put = 0; $putCnt = 0;
		for ($c = 1; $c <= $m; $c++) { $put += $cumuls[$c]; $putCnt += $cumcnts[$c]; }
		if ($putCnt == 0) {
			HTML::td('---', 'reportday" style="padding:4px;font-weight:bold');
			continue;
		}
		$sumfix = 1;
		if (!$report->isSum) {
			$put /= $putCnt;
		} else {
			$sumfix = $sumOffset * $m;
		}
		$putConv = Wx::conv($put, $unit, false);
		$num = $report->valcolConvert ? Wx::convNum($put, $unit) : (float)$put;
		$class = $report->valcolr($num / $sumfix);
		$anom = $report->isAnom ? '<br />(' . $report->anomMonthCum($put, $m - 1) . ')' : '';
		HTML::td($putConv . $anom, $class . '" style="padding:4px;font-weight:bold');
	}
	HTML::tr_end();
}

HTML::table_end();

$summaryText = $report->isNotSummarisable ? '.' : ' along with monthly summary: lowest, highest, '
	. ($report->isSum ? 'total, count (days > 0)' : 'mean') . ', and the cumulative value for the year to the month\'s end.';
echo '<p>' . $report->description . ' in London, NW3, for every available day of ' . $year . $summaryText;
if ($report->isAnom) {
	echo '<br />Figures in brackets refer to departure from <strong>recent</strong> '
		. '<a href="/wxaverages.php" title="Long-term NW3 climate averages">average conditions</a>.';
}
if ($year == (int)Date::$dyear) {
	echo '<br />Values for recent days are subject to quality control and may be adjusted at any time.';
}
echo '</p>';

$report->historicalInfo();

Page::End();
