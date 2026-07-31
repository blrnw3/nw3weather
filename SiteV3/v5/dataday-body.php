<?php
/**
 * Shared renderer for the daily data grid + footer blurb (wxdataday / datadaydata).
 */

/** Emit a day / month / summary label cell. */
function dd_lab($html, $extraClass = '') {
	echo '<div class="dd-lab' . ($extraClass ? ' ' . $extraClass : '') . '">' . $html . '</div>';
}

/** Emit a value cell (keeps valcol colour classes). */
function dd_cell($html, $class = 'reportday', $extraClass = '') {
	$cls = trim($class . ($extraClass ? ' ' . $extraClass : ''));
	echo '<div class="dd-cell ' . $cls . '">' . $html . '</div>';
}

/** True when the whole month is still in the future (current year only). */
function dd_month_future($m, $isCurrentYear, $curMonth) {
	return $isCurrentYear && $m > $curMonth;
}

/**
 * Render the daily matrix, summary rows, and descriptive footer for $report.
 *
 * @param Report $report
 * @return array meta for AJAX clients: type, year, title, startYear, description, unit
 */
function nw3_dataday_render(Report $report) {
	$type = $report->type;
	$year = $report->year;
	$unit = $report->unit;
	$dayAgg = $report->dayAgg;
	$isAgg = ($dayAgg !== '');

	$aggFromYear = max((int)$report->startYrReport, (int)$report->startYear);
	if ($isAgg) {
		$data = Data::getDailyCalendarAgg($type, $dayAgg, $aggFromYear);
	} else {
		$data = Data::getDailyDataForYear($type, $year);
	}

	$today = Date::mkdate(Date::$dmonth, Date::$dday, Date::$dyear);
	$isCurrentYear = (!$isAgg && $year == (int)Date::$dyear);
	$curMonth = (int)Date::$dmonth;

	$maxdays = [];
	$refYear = $isAgg ? 2000 : $year; // leap year so Feb 29 can appear in agg mode
	for ($m = 1; $m <= 12; $m++) { $maxdays[$m] = Date::get_days_in_month($m, $refYear); }

	$cumuls = array_fill(1, 12, 0);
	$cumcnts = array_fill(1, 12, 0);

	$aggLabels = array('min' => 'minimum', 'max' => 'maximum', 'mean' => 'mean');
	$aggShort = array('min' => 'Min', 'max' => 'Max', 'mean' => 'Mean');

	echo '<div class="dd-scroll"><div class="dd-grid">';

	// Month header row
	echo '<div class="dd-row dd-head">';
	dd_lab('Day');
	for ($m = 1; $m <= 12; $m++) {
		$mon = Date::$months3[$m - 1];
		if ($isAgg) {
			$label = $mon;
		} else {
			$canLink = ($year < (int)Date::$yr_yest) || ($year == (int)Date::$yr_yest && $m <= (int)Date::$mon_yest);
			$lnk = "/wxhistmonth.php?year=$year&month=$m";
			$label = $canLink ? '<a class="hidden-link" href="' . $lnk . '" title="View detailed report for month">' . $mon . '</a>' : $mon;
		}
		dd_lab($label, dd_month_future($m, $isCurrentYear, $curMonth) ? 'dd-future-lab' : '');
	}
	echo '</div>';

	for ($day = 1; $day <= 31; $day++) {
		echo '<div class="dd-row">';
		echo '<div class="dd-day">' . $day . '</div>';
		for ($m = 1; $m <= 12; $m++) {
			$class = 'reportday';
			$finalVal = '-';
			$showLink = false;
			$val = isset($data[$m][$day]) ? $data[$m][$day] : null;
			if ($maxdays[$m] < $day) {
				$class = 'noday';
				$finalVal = '';
			} elseif ($isCurrentYear && Date::mkdate($m, $day, $year) > $today) {
				$class = 'dd-future';
				$finalVal = '';
			} elseif (Util::isBlank($val)) {
				$class = 'invalid';
				$finalVal = '&nbsp;';
				$showLink = !$isAgg;
			} else {
				$cumuls[$m] += $val;
				$cumcnts[$m]++;
				$finalVal = Wx::conv($val, $unit, false);
				$num = $report->valcolConvert ? Wx::convNum($val, $unit) : (float)$val;
				$class = $report->valcolr($num);
				$showLink = !$isAgg;
			}
			if ($showLink) {
				$lnk = "/wxhistday.php?year=$year&month=$m&day=$day";
				$cell = '<a class="hidden-link" href="' . $lnk . '" title="View detailed report for day">' . $finalVal . '</a>';
			} else {
				$cell = $finalVal;
			}
			dd_cell($cell, $class);
		}
		echo '</div>';
	}

	// Month names again before summary block
	echo '<div class="dd-row dd-head dd-sep">';
	dd_lab('');
	for ($m = 1; $m <= 12; $m++) {
		dd_lab(Date::$months3[$m - 1], dd_month_future($m, $isCurrentYear, $curMonth) ? 'dd-future-lab' : '');
	}
	echo '</div>';

	// Monthly summary rows: Lowest / Highest / Mean(or Total)
	$labels = [0 => 'Lowest', 1 => 'Highest', 2 => ($report->isSum ? 'Total' : 'Mean')];
	$sumOffset = $report->valcolSumOffset();
	if (!$report->isNotSummarisable) {
		for ($mm = (int)$report->isSum; $mm < 3; $mm++) {
			echo '<div class="dd-row dd-sum">';
			dd_lab($labels[$mm], 'dd-sum-lab');
			for ($m = 1; $m <= 12; $m++) {
				if (dd_month_future($m, $isCurrentYear, $curMonth)) {
					dd_cell('', 'dd-future', 'dd-sum-cell');
					continue;
				}
				$vals = isset($data[$m]) && is_array($data[$m]) ? array_filter($data[$m], ['Util', 'clearblank']) : [];
				if (count($vals) === 0) {
					dd_cell('---', 'reportday', 'dd-sum-cell');
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
				$anom = (!$isAgg && $report->isAnom && $mm == 2) ? '<br />(' . $report->anomMonth($put, $m) . ')' : '';
				dd_cell($putConv . $anom, $class, 'dd-sum-cell');
			}
			echo '</div>';
		}

		// Count row (summable only): number of days with a value > 0
		if ($report->isSum) {
			echo '<div class="dd-row dd-sum">';
			dd_lab('Count', 'dd-sum-lab');
			for ($m = 1; $m <= 12; $m++) {
				if (dd_month_future($m, $isCurrentYear, $curMonth)) {
					dd_cell('', 'dd-future', 'dd-sum-cell');
					continue;
				}
				$vals = isset($data[$m]) && is_array($data[$m]) ? array_filter($data[$m], ['Util', 'clearblank']) : [];
				if (count($vals) === 0) {
					dd_cell('---', 'reportday', 'dd-sum-cell');
					continue;
				}
				$cnt = Util::cond_count($vals, true, 0);
				$class = $report->valcolr($cnt, true);
				dd_cell($cnt, $class, 'dd-sum-cell');
			}
			echo '</div>';
		}

		// Cumulative row
		echo '<div class="dd-row dd-sum">';
		dd_lab((!$isAgg && $report->isAnom) ? 'Cumu-<br />lative' : 'Cumul', 'dd-sum-lab');
		for ($m = 1; $m <= 12; $m++) {
			if (dd_month_future($m, $isCurrentYear, $curMonth)) {
				dd_cell('', 'dd-future', 'dd-sum-cell');
				continue;
			}
			$put = 0; $putCnt = 0;
			for ($c = 1; $c <= $m; $c++) { $put += $cumuls[$c]; $putCnt += $cumcnts[$c]; }
			if ($putCnt == 0) {
				dd_cell('---', 'reportday', 'dd-sum-cell');
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
			$anom = (!$isAgg && $report->isAnom) ? '<br />(' . $report->anomMonthCum($put, $m - 1) . ')' : '';
			dd_cell($putConv . $anom, $class, 'dd-sum-cell');
		}
		echo '</div>';
	}

	echo '</div></div>'; // .dd-grid + .dd-scroll

	$summaryText = $report->isNotSummarisable ? '.' : ' along with monthly summary: lowest, highest, '
		. ($report->isSum ? 'total, count (days > 0)' : 'mean') . ', and the cumulative value for the year to the month\'s end.';
	if ($isAgg) {
		echo '<p>' . $report->description
			. ' in London, NW3 — calendar-day ' . $aggLabels[$dayAgg]
			. ' across all years from ' . $aggFromYear . ' onwards'
			. $summaryText . '</p>';
		$report->historicalInfo($aggFromYear);
	} else {
		echo '<p>' . $report->description . ' in London, NW3, for every available day of ' . $year . $summaryText
			. ' Data for ' . $report->description . ' begins in ' . (int)$report->startYear . '.';
		if ($report->isAnom) {
			echo '<br />Figures in brackets refer to departure from <strong>recent</strong> '
				. '<a href="/wxaverages.php" title="Long-term NW3 climate averages">average conditions</a>.';
		}
		if ($year == (int)Date::$dyear) {
			echo '<br />Values for recent days are subject to quality control and may be adjusted at any time.';
		}
		echo '</p>';
		$report->historicalInfo($year);
	}

	$unitLabel = Wx::getUnitsText($unit);
	$title = $report->description . ' / ' . $unitLabel;
	if ($isAgg) {
		$title .= ' · ' . $aggShort[$dayAgg] . ' (all years)';
	}
	return array(
		'type' => $type,
		'year' => $year,
		'agg' => $dayAgg,
		'startYear' => (int)$report->startYear,
		'startYearRep' => (int)$report->startYrReport,
		'startYearOptions' => array_map('intval', $report->startYearOptions),
		'yearDefaulted' => !$isAgg && !empty($report->yearDefaulted),
		'description' => $report->description,
		'unit' => $unitLabel,
		'title' => $title,
	);
}
