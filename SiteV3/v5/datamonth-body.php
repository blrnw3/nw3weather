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
 * Renders one year-by-month grid for a given aggregation.
 * @param Report $report
 * @param array $DAT [year][month][day] => val
 * @param int $t aggregation: 0=min 1=max 2=mean/total 3=count
 * @param string $heading
 * @param int $startYr
 */
function tdmMakeTable($report, $DAT, $t, $heading, $startYr) {
	$unit = $report->unit;
	$isCount = ($t === 3);
	$isAnom = $report->isAnom;
	$sumfix = ($report->isSum && $t === 2) ? $report->valcolSumOffset() : 1;
	$yrYest = (int)Date::$yr_yest;
	$monYest = (int)Date::$mon_yest;
	// Never show years before this variable has data, even if an earlier Start year is selected.
	$fromYr = max((int)$startYr, (int)$report->startYear);

	echo '<div class="dm-grid">';
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
			$linked = ($y >= 2009) ?
				'<a class="hidden-link" href="' . $lnk . '" title="View detailed report for month">' . $value . '</a>' : $value;
			dm_cell($linked . $anom, $class);
		}

		// Annual summary cell
		$months = isset($extreme[$y]) ? $extreme[$y] : [];
		if (count($months) === 0) {
			dm_cell('-', 'reportday', 'dm-yr');
		} else {
			$yrVal = $isCount ? Util::mean($months) : Report::aggregate($months, $t);
			$cntM = 1;
			if ($report->isSum && $t >= 2) { $cntM = count($months); $yrVal *= $cntM; }
			$yrAgg[$y] = $yrVal;
			$anom = ($isAnom && $t === 2) ? '<br />(' . $report->anomYear($yrVal) . ')' : '';
			$valyr = $isCount ? round($yrVal, 1) : Wx::conv($yrVal, $unit, false);
			$class = $report->valcolr($yrVal / $sumfix / $cntM, $isCount);
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
			$value = $isCount ? round($v, 1) : Wx::conv($v, $unit, false);
			$anom = ($isAnom && $t === 2) ? '<br />(' . $report->anomMonth($v, $m) . ')' : '';
			$class = $report->valcolr($v / $sumfix, $isCount);
			dm_cell($value . $anom, $class, 'dm-sum-cell');
		}
		if (count($yrAgg) === 0) { dm_cell('-', 'reportday', 'dm-sum-cell dm-yr'); }
		else {
			$yv = Report::aggregate($yrAgg, $mm);
			$valall = $isCount ? round($yv, 1) : Wx::conv($yv, $unit, false);
			$anom = ($isAnom && $t === 2) ? '<br />(' . $report->anomYear($yv) . ')' : '';
			dm_cell($valall . $anom, $report->valcolr($yv / $sumfix, $isCount), 'dm-sum-cell dm-yr');
		}
		echo '</div>';
	}

	echo '</div>'; // .dm-grid
}

/**
 * Render all monthly summary tabs + footer blurb for $report.
 * @return array meta for AJAX clients
 */
function nw3_datamonth_render(Report $report) {
	if ($report->isNotSummarisable) {
		echo '<p>' . htmlspecialchars($report->description) . ' has no meaningful monthly summary.</p>';
		$unitLabel = strip_tags(Wx::getUnits($report->unit));
		return array(
			'type' => $report->type,
			'startYearRep' => (int)$report->startYrReport,
			'summaryType' => (int)$report->summaryType,
			'summaryTypes' => $report->availSummaryTypes,
			'description' => $report->description,
			'title' => $report->description . ' / ' . $unitLabel,
		);
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

	$fromYr = max((int)$report->startYrReport, (int)$report->startYear);

	foreach ($report->availSummaryTypes as $st) {
		$hide = ($st === $report->summaryType) ? '' : " style='display:none'";
		echo "<div id='rank-$st' class='rank-tab scroll'$hide>";
		tdmMakeTable($report, $DAT, $newToOld[$st], 'Monthly ' . ucfirst(Data::$SUMMARY_NAMES[$st]), $report->startYrReport);
		echo '<p>' . $report->description . ': ' . $explain[$st] . ' for all months since '
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

	$unitLabel = strip_tags(Wx::getUnits($report->unit));
	return array(
		'type' => $report->type,
		'startYearRep' => (int)$report->startYrReport,
		'summaryType' => (int)$report->summaryType,
		'summaryTypes' => $report->availSummaryTypes,
		'description' => $report->description,
		'title' => $report->description . ' / ' . $unitLabel,
	);
}
