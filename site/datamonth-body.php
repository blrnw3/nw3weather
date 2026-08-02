<?php
/**
 * Shared renderer for monthly data tables (TablesDataMonth / datamonthdata).
 */

/** Emit a month / year / summary label cell. */
function dm_lab($html, $extraClass = '') {
	echo '<div class="dm-lab' . ($extraClass ? ' ' . $extraClass : '') . '">' . $html . '</div>';
}

/** Emit a value cell (keeps valcol colour classes). */
function dm_cell($html, $class = 'reportday', $extraClass = '') {
	$cls = trim($class . ($extraClass ? ' ' . $extraClass : ''));
	echo '<div class="dm-cell ' . $cls . '">' . $html . '</div>';
}

/**
 * Format a monthly-summary cell value for display.
 * @param Report $report
 * @param float|int $val raw UK-unit value
 * @param int $st Data::SUMMARY_*
 * @return string
 */
function dm_fmt($report, $val, $st) {
	if (Data::isCountSummary($st)) {
		return (string)round($val, 1);
	}
	return Wx::conv($val, $report->summaryDisplayUnit($st), false);
}

/**
 * Renders one year-by-month grid for a given summary type.
 * @param Report $report
 * @param array $DAT [year][month][day] => val
 * @param int $st Data::SUMMARY_*
 * @param string $heading
 * @param int $startYr
 * @param float|null $threshold
 */
function tdmMakeTable($report, $DAT, $st, $heading, $startYr, $threshold = null) {
	$unit = $report->summaryDisplayUnit($st);
	$isCount = Data::isCountSummary($st);
	$isAnom = $report->isAnom && ($st === Data::SUMMARY_MEAN || $st === Data::SUMMARY_SUM);
	$sumOff = ($st === Data::SUMMARY_SUM) ? $report->valcolSumOffset() : 1;
	$yrYest = (int)Date::$yr_yest;
	$monYest = (int)Date::$mon_yest;
	// Never show years before this variable has data, even if an earlier Start year is selected.
	$fromYr = max((int)$startYr, (int)$report->startYear);

	echo '<div class="dm-scroll"><div class="dm-grid">';
	echo '<div class="dm-caption">' . htmlspecialchars($heading . ' ' . $report->description) . '</div>';

	echo '<div class="dm-row dm-head">';
	dm_lab('');
	for ($m = 1; $m <= 12; $m++) { dm_lab(Date::$months3[$m - 1]); }
	dm_lab('Year', 'dm-yr');
	echo '</div>';

	$extreme = [];   // [year][month] and [month][year]
	$yrAgg = [];     // [year] => annual aggregate

	for ($y = $yrYest; $y >= $fromYr; $y--) {
		echo '<div class="dm-row">';
		$yrLabel = $y . ($y < Site::BASE_YEAR ? '*' : '');
		echo '<div class="dm-year"><a href="/wxdataday.php?vartype='
			. htmlspecialchars($report->type) . '&year=' . $y
			. '" title="View full data for year">' . $yrLabel . '</a></div>';

		for ($m = 1; $m <= 12; $m++) {
			$isFuture = ($y === $yrYest && $m > $monYest);
			if ($isFuture) {
				dm_cell('', 'dm-future');
				continue;
			}

			$days = isset($DAT[$y][$m]) && is_array($DAT[$y][$m]) ? $DAT[$y][$m] : [];
			$anom = ''; $value = '-'; $class = 'reportday';
			$val = Data::summarize($days, $st, $threshold, $report->type);

			if ($val !== null && !Util::isBlank($val)) {
				$extreme[$y][$m] = $val;
				$extreme[$m][$y] = $val;
				$value = dm_fmt($report, $val, $st);
				$num = $report->valcolConvert ? Wx::convNum($val, $unit) : (float)$val;
				$class = $isCount
					? Report::dayCountClass($val)
					: $report->valcolr(($num === null ? 0 : $num) / $sumOff);
				$anom = $isAnom ? $report->anomCell($report->anomMonth($val, $m)) : '';
			} elseif (Util::mycount($days) === 0) {
				$value = '-';
			} else {
				$value = ''; $class = 'invalid';
			}
			$lnk = "/wxhistmonth.php?year=$y&month=$m";
			$linked = ($y >= 2009) ?
				'<a class="hidden-link" href="' . $lnk . '" title="View detailed report for month">' . $value . '</a>' : $value;
			dm_cell($linked . $anom, $class);
		}

		// Annual summary cell
		$months = isset($extreme[$y]) ? $extreme[$y] : [];
		if (count($months) === 0) {
			dm_cell('-', 'reportday', 'dm-yr');
		} else {
			if (Data::isThresholdSummary($st)) {
				$yrVal = array_sum($months);
			} elseif ($st === Data::SUMMARY_COUNT) {
				$yrVal = Util::mean($months); // legacy: mean days/month
			} elseif ($st === Data::SUMMARY_SUM) {
				$yrVal = array_sum($months);
			} elseif ($st === Data::SUMMARY_RANGE) {
				$yrVal = Util::mean($months);
			} else {
				$yrVal = Data::summarize($months, $st);
			}
			$yrAgg[$y] = $yrVal;
			$anom = $isAnom ? $report->anomCell($report->anomYear($yrVal)) : '';
			$valyr = dm_fmt($report, $yrVal, $st);
			$cntM = ($st === Data::SUMMARY_SUM) ? max(1, count($months)) : 1;
			$class = $isCount
				? Report::dayCountClass($yrVal)
				: $report->valcolr($yrVal / $sumOff / $cntM);
			dm_cell($valyr . $anom, $class, 'dm-yr');
		}
		echo '</div>';
	}

	// Repeat month header
	echo '<div class="dm-row dm-head dm-sep">';
	dm_lab('');
	for ($m = 1; $m <= 12; $m++) { dm_lab(Date::$months3[$m - 1]); }
	dm_lab('Year', 'dm-yr');
	echo '</div>';

	// All-time summary rows across years: Lowest / Highest / Mean
	$labels = ['Lowest', 'Highest', 'Mean'];
	for ($mm = 0; $mm < 3; $mm++) {
		echo '<div class="dm-row dm-sum">';
		dm_lab($labels[$mm], 'dm-sum-lab');
		for ($m = 1; $m <= 12; $m++) {
			$col = isset($extreme[$m]) ? $extreme[$m] : [];
			if (count($col) === 0) { dm_cell('-', 'reportday', 'dm-sum-cell'); continue; }
			$v = Report::aggregate($col, $mm);
			$value = dm_fmt($report, $v, $st);
			$anom = $isAnom ? $report->anomCell($report->anomMonth($v, $m)) : '';
			$class = $isCount
				? Report::dayCountClass($v)
				: $report->valcolr($v / $sumOff);
			dm_cell($value . $anom, $class, 'dm-sum-cell');
		}
		if (count($yrAgg) === 0) { dm_cell('-', 'reportday', 'dm-sum-cell dm-yr'); }
		else {
			$yv = Report::aggregate($yrAgg, $mm);
			$valall = dm_fmt($report, $yv, $st);
			$anom = $isAnom ? $report->anomCell($report->anomYear($yv)) : '';
			$class = $isCount
				? Report::dayCountClass($yv)
				: $report->valcolr($yv / $sumOff);
			dm_cell($valall . $anom, $class, 'dm-sum-cell dm-yr');
		}
		echo '</div>';
	}

	echo '</div></div>'; // .dm-grid + .dm-scroll
}

/**
 * Render all monthly summary tabs + footer blurb for $report.
 * @return array meta for AJAX clients
 */
function nw3_datamonth_render(Report $report) {
	$t0 = microtime(true);
	if ($report->isNotSummarisable) {
		echo '<p>' . htmlspecialchars($report->description) . ' has no meaningful monthly summary.</p>';
		$report->echoVarAbout();
		$report->logRankRuntime($t0, 'datamonth');
		return array(
			'type' => $report->type,
			'startYearRep' => (int)$report->startYrReport,
			'startYearOptions' => array_map('intval', $report->startYearOptions),
			'summaryType' => (int)$report->summaryType,
			'summaryTypes' => $report->availSummaryTypes,
			'threshold' => $report->spellThreshold,
			'thresholds' => $report->spellThresholds,
			'thresholdLabels' => $report->thresholdLabels(),
			'description' => $report->description,
			'title' => $report->summaryTitleSuffix(),
		);
	}

	$DAT = Data::getDailyData($report->type, $report->startYrReport);
	$threshold = $report->spellThreshold;

	$explain = [
		Data::SUMMARY_MEAN => 'monthly means',
		Data::SUMMARY_SUM => 'monthly totals',
		Data::SUMMARY_COUNT => 'count of days with a non-zero value',
		Data::SUMMARY_MIN => 'lowest daily value each month',
		Data::SUMMARY_MAX => 'highest daily value each month',
		Data::SUMMARY_RANGE => 'range of daily values each month (max − min)',
		Data::SUMMARY_COUNT_ABOVE => 'count of days at or above the chosen threshold',
		Data::SUMMARY_COUNT_BELOW => 'count of days below the chosen threshold',
	];

	$fromYr = max((int)$report->startYrReport, (int)$report->startYear);

	foreach ($report->availSummaryTypes as $st) {
		// Render the active summary only — AJAX reloads when the chip changes.
		if ($st !== $report->summaryType) {
			continue;
		}
		$th = Data::isThresholdSummary($st) ? $threshold : null;
		echo "<div id='rank-$st' class='rank-tab scroll'>";
		tdmMakeTable(
			$report, $DAT, $st,
			'Monthly ' . ucfirst($report->summaryCaptionName($st)),
			$report->startYrReport,
			$th
		);
		$extra = '';
		if (Data::isThresholdSummary($st)) {
			$extra = ' (' . $report->summaryThresholdPhrase($st) . ')';
		}
		echo '<p>' . $report->description . ': ' . $explain[$st] . $extra . ' for all months since '
			. $fromYr . ' in London, NW3. Data for ' . $report->description
			. ' begins in ' . (int)$report->startYear . '.</p>';
		echo '</div>';
	}

	if ($report->isAnom) {
		echo '<p>Figures in brackets refer to departure from <strong>recent</strong> '
			. '<a href="/wxaverages.php" title="Long-term NW3 climate averages">average conditions</a> '
			. "(the anomaly for the current month is unadjusted for the month's degree of completeness).</p>";
	}

	$report->historicalInfo($fromYr);
	$report->echoVarAbout();
	$report->logRankRuntime($t0, 'datamonth');

	return array(
		'type' => $report->type,
		'startYearRep' => (int)$report->startYrReport,
		'startYearOptions' => array_map('intval', $report->startYearOptions),
		'summaryType' => (int)$report->summaryType,
		'summaryTypes' => $report->availSummaryTypes,
		'threshold' => $report->spellThreshold,
		'thresholds' => $report->spellThresholds,
		'thresholdLabels' => $report->thresholdLabels(),
		'description' => $report->description,
		'title' => $report->summaryTitleSuffix(),
	);
}
