<?php
/**
 * Daily data grid: payload builder + HTML renderer (wxdataday / datadaydata).
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

/** Static MIDAS / Whitestone footnote (also templated in reporthydrate.js). */
function nw3_dataday_hist_note_html() {
	return DataSummarizer::historicalNoteHtml(1871);
}

/**
 * Build display-ready JSON for the daily matrix (formatted text + CSS classes).
 *
 * @param Report $report
 * @return array
 */
function nw3_dataday_payload(Report $report) {
	$t0 = microtime(true);
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

	$months = [];
	for ($m = 1; $m <= 12; $m++) {
		$canLink = !$isAgg && (
			($year < (int)Date::$yr_yest) || ($year == (int)Date::$yr_yest && $m <= (int)Date::$mon_yest)
		);
		$months[] = array(
			'name' => Date::$months3[$m - 1],
			'link' => $canLink,
			'future' => dd_month_future($m, $isCurrentYear, $curMonth),
		);
	}

	$rows = [];
	for ($day = 1; $day <= 31; $day++) {
		$cells = [];
		for ($m = 1; $m <= 12; $m++) {
			$val = isset($data[$m][$day]) ? $data[$m][$day] : null;
			if ($maxdays[$m] < $day) {
				$cells[] = array('c' => 'noday');
			} elseif ($isCurrentYear && Date::mkdate($m, $day, $year) > $today) {
				$cells[] = array('c' => 'dd-future');
			} elseif (Util::isBlank($val)) {
				$cells[] = array('c' => 'invalid', 't' => "\xC2\xA0");
			} else {
				$cumuls[$m] += $val;
				$cumcnts[$m]++;
				$num = $report->valcolConvert ? Wx::convNum($val, $unit) : (float)$val;
				$cells[] = array(
					't' => Wx::conv($val, $unit, false),
					'c' => $report->valcolr($num),
				);
			}
		}
		$rows[] = array('day' => $day, 'cells' => $cells);
	}

	$sums = [];
	$labels = [0 => 'Lowest', 1 => 'Highest', 2 => ($report->isSum ? 'Total' : 'Mean')];
	$sumOffset = $report->valcolSumOffset();
	if (!$report->isNotSummarisable) {
		for ($mm = (int)$report->isSum; $mm < 3; $mm++) {
			$sumCells = [];
			for ($m = 1; $m <= 12; $m++) {
				if (dd_month_future($m, $isCurrentYear, $curMonth)) {
					$sumCells[] = array('c' => 'dd-future');
					continue;
				}
				$vals = isset($data[$m]) && is_array($data[$m]) ? array_filter($data[$m], ['Util', 'clearblank']) : [];
				if (count($vals) === 0) {
					$sumCells[] = array('t' => '---', 'c' => 'reportday');
					continue;
				}
				$put = Report::aggregate($vals, $mm);
				$sumfix = 1;
				if ($report->isSum && $mm == 2) {
					$put *= count($vals);
					$sumfix = $sumOffset;
				}
				$num = $report->valcolConvert ? Wx::convNum($put, $unit) : (float)$put;
				$cell = array(
					't' => Wx::conv($put, $unit, false),
					'c' => $report->valcolr($num / $sumfix),
				);
				if (!$isAgg && $report->isAnom && $mm == 2) {
					$anom = $report->anomMonth($put, $m);
					if ($anom !== '' && $anom !== null) { $cell['a'] = $anom; }
				}
				$sumCells[] = $cell;
			}
			$sums[] = array('label' => $labels[$mm], 'cells' => $sumCells);
		}

		if ($report->isSum) {
			$sumCells = [];
			for ($m = 1; $m <= 12; $m++) {
				if (dd_month_future($m, $isCurrentYear, $curMonth)) {
					$sumCells[] = array('c' => 'dd-future');
					continue;
				}
				$vals = isset($data[$m]) && is_array($data[$m]) ? array_filter($data[$m], ['Util', 'clearblank']) : [];
				if (count($vals) === 0) {
					$sumCells[] = array('t' => '---', 'c' => 'reportday');
					continue;
				}
				$cnt = Util::cond_count($vals, true, 0);
				$sumCells[] = array('t' => (string)$cnt, 'c' => $report->valcolr($cnt, true));
			}
			$sums[] = array('label' => 'Count', 'cells' => $sumCells);
		}

		$sumCells = [];
		for ($m = 1; $m <= 12; $m++) {
			if (dd_month_future($m, $isCurrentYear, $curMonth)) {
				$sumCells[] = array('c' => 'dd-future');
				continue;
			}
			$put = 0; $putCnt = 0;
			for ($c = 1; $c <= $m; $c++) { $put += $cumuls[$c]; $putCnt += $cumcnts[$c]; }
			if ($putCnt == 0) {
				$sumCells[] = array('t' => '---', 'c' => 'reportday');
				continue;
			}
			$sumfix = 1;
			if (!$report->isSum) {
				$put /= $putCnt;
			} else {
				$sumfix = $sumOffset * $m;
			}
			$num = $report->valcolConvert ? Wx::convNum($put, $unit) : (float)$put;
			$cell = array(
				't' => Wx::conv($put, $unit, false),
				'c' => $report->valcolr($num / $sumfix),
			);
			if (!$isAgg && $report->isAnom) {
				$anom = $report->anomMonthCum($put, $m - 1);
				if ($anom !== '' && $anom !== null) { $cell['a'] = $anom; }
			}
			$sumCells[] = $cell;
		}
		$cumLabel = (!$isAgg && $report->isAnom)
			? array('label' => 'Cumulative', 'labelBr' => true, 'cells' => $sumCells)
			: array('label' => 'Cumul', 'cells' => $sumCells);
		$sums[] = $cumLabel;
	}

	$summaryText = $report->isNotSummarisable ? '.' : ' along with monthly summary: lowest, highest, '
		. ($report->isSum ? 'total, count (days > 0)' : 'mean') . ', and the cumulative value for the year to the month\'s end.';
	if ($isAgg) {
		$blurb = $report->description
			. ' in London, NW3 — calendar-day ' . $aggLabels[$dayAgg]
			. ' across all years from ' . $aggFromYear . ' onwards'
			. $summaryText;
		$histWindow = $aggFromYear;
	} else {
		$blurb = $report->description . ' in London, NW3, for every available day of ' . $year . $summaryText
			. ' Data for ' . $report->description . ' begins in ' . (int)$report->startYear . '.';
		$histWindow = $year;
	}

	$histWindow = max((int)$histWindow, (int)$report->startYear);
	$showHist = ((int)$report->startYear < Site::BASE_YEAR && $histWindow < Site::BASE_YEAR);

	$unitLabel = Wx::getUnitsText($unit);
	$title = $report->description . ' / ' . $unitLabel;
	if ($isAgg) {
		$title .= ' · ' . $aggShort[$dayAgg] . ' (all years)';
	}

	$yearDefaulted = !$isAgg && !empty($report->yearDefaulted);
	$warn = $yearDefaulted
		? ('No data for ' . $report->description . ' in the selected year; '
			. 'defaulted to ' . (int)$year . ' (earliest available).')
		: '';

	$report->logRankRuntime($t0, 'dataday');

	return array(
		'mode' => 'daily',
		'meta' => array(
			'type' => $type,
			'year' => $year,
			'agg' => $dayAgg,
			'startYear' => (int)$report->startYear,
			'startYearRep' => (int)$report->startYrReport,
			'startYearOptions' => array_map('intval', $report->startYearOptions),
			'yearDefaulted' => $yearDefaulted,
			'yearWarn' => $warn,
			'description' => $report->description,
			'unit' => $unitLabel,
			'title' => $title,
		),
		'grid' => array(
			'year' => $year,
			'dayLinks' => !$isAgg,
			'months' => $months,
			'rows' => $rows,
			'sums' => $sums,
		),
		'footer' => array(
			'blurb' => $blurb,
			'anomNote' => !$isAgg && $report->isAnom,
			'qcNote' => !$isAgg && $year == (int)Date::$dyear,
			'histNote' => $showHist,
			'about' => Wx::measureAbout($type),
		),
	);
}

/**
 * Emit one daily-grid value cell from a payload cell object.
 */
function nw3_dataday_echo_cell($cell, $year, $day, $month, $dayLinks, $extraClass) {
	$class = isset($cell['c']) ? $cell['c'] : 'reportday';
	$text = isset($cell['t']) ? $cell['t'] : '';
	$linkable = $dayLinks && $class !== 'noday' && $class !== 'dd-future';
	$html = $text;
	if ($linkable) {
		$lnk = "/wxhistday.php?year=$year&month=$month&day=$day";
		$html = '<a class="hidden-link" href="' . $lnk . '" title="View detailed report for day">' . $text . '</a>';
	}
	if (isset($cell['a']) && $cell['a'] !== '') {
		$html .= '<br />(' . $cell['a'] . ')';
	}
	dd_cell($html, $class, $extraClass);
}

/**
 * Render payload as the existing CSS-grid HTML (first paint / no-JS).
 */
function nw3_dataday_echo(array $payload) {
	$meta = $payload['meta'];
	$grid = $payload['grid'];
	$year = (int)$grid['year'];
	$dayLinks = !empty($grid['dayLinks']);
	$months = $grid['months'];

	echo '<div class="dd-scroll"><div class="dd-grid">';

	echo '<div class="dd-row dd-head">';
	dd_lab('Day');
	foreach ($months as $i => $mon) {
		$m = $i + 1;
		$label = $mon['name'];
		if (!empty($mon['link'])) {
			$lnk = "/wxhistmonth.php?year=$year&month=$m";
			$label = '<a class="hidden-link" href="' . $lnk . '" title="View detailed report for month">' . $label . '</a>';
		}
		dd_lab($label, !empty($mon['future']) ? 'dd-future-lab' : '');
	}
	echo '</div>';

	foreach ($grid['rows'] as $row) {
		$day = (int)$row['day'];
		echo '<div class="dd-row">';
		echo '<div class="dd-day">' . $day . '</div>';
		foreach ($row['cells'] as $i => $cell) {
			nw3_dataday_echo_cell($cell, $year, $day, $i + 1, $dayLinks, '');
		}
		echo '</div>';
	}

	echo '<div class="dd-row dd-head dd-sep">';
	dd_lab('');
	foreach ($months as $mon) {
		dd_lab($mon['name'], !empty($mon['future']) ? 'dd-future-lab' : '');
	}
	echo '</div>';

	foreach ($grid['sums'] as $sum) {
		echo '<div class="dd-row dd-sum">';
		$lab = !empty($sum['labelBr']) ? 'Cumu-<br />lative' : $sum['label'];
		dd_lab($lab, 'dd-sum-lab');
		foreach ($sum['cells'] as $i => $cell) {
			nw3_dataday_echo_cell($cell, $year, 0, $i + 1, false, 'dd-sum-cell');
		}
		echo '</div>';
	}

	echo '</div></div>';

	$f = $payload['footer'];
	echo '<p>' . $f['blurb'];
	if (!empty($f['anomNote'])) {
		echo '<br />Figures in brackets refer to departure from <strong>recent</strong> '
			. '<a href="/wxaverages.php" title="Long-term NW3 climate averages">average conditions</a>.';
	}
	if (!empty($f['qcNote'])) {
		echo '<br />Values for recent days are subject to quality control and may be adjusted at any time.';
	}
	echo '</p>';
	if (!empty($f['histNote'])) {
		echo nw3_dataday_hist_note_html();
	}
	if (!empty($f['about'])) {
		echo '<p class="report-var-about" id="report-var-about">'
			. htmlspecialchars($f['about']) . '</p>';
	}
}

/**
 * Render the daily matrix, summary rows, and descriptive footer for $report.
 *
 * @param Report $report
 * @return array meta for AJAX clients
 */
function nw3_dataday_render(Report $report) {
	$payload = nw3_dataday_payload($report);
	nw3_dataday_echo($payload);
	return $payload['meta'];
}
