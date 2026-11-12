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
	$cls = trim($class . ($extraClass ? ' ' . $extraClass : '') . (strpos($html, 'class="aqv"') !== false ? ' aqc' : ''));
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
		return (string)nw3_round($val, 1);
	}
	$unit = $report->summaryDisplayUnit($st);
	$out = Wx::conv($val, $unit, false, !empty($report->meta['signed']));
	if ($unit === Wx::Pm25 && in_array($st, [Data::SUMMARY_MEAN, Data::SUMMARY_MIN, Data::SUMMARY_MAX], true)) {
		$out = Wx::aqSpan($val, $out, Wx::aqAvg($report->type));
	}
	return $out;
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
function dm_span_layout($span) {
	if ($span === 'seasonal') {
		$cols = [];
		foreach (Data::$seasonSpans as $s) {
			$si = Data::seasonIndex($s);
			$cols[] = array(
				'season' => $s,
				'lab' => Date::$snames[$si],
				'prevYear' => ($s === 'winter'),
			);
		}
		return array(
			'cols' => $cols,
			'totalLab' => 'Year',
			'rowClass' => 'dm-cols-6',
			'gridClass' => 'dm-grid-seasonal',
		);
	}
	$cols = [];
	for ($m = 1; $m <= 12; $m++) {
		$cols[] = array('m' => $m, 'lab' => Date::$months3[$m - 1], 'prevYear' => false);
	}
	return array(
		'cols' => $cols,
		'totalLab' => 'Year',
		'rowClass' => '',
		'gridClass' => '',
	);
}

/** Year-column anomaly for MTD: full months + current month days 1..D.
 *  $includeAfter is false for the current year (later months are blank). */
function dm_mtd_year_anom($report, $yrVal, $year, $curMonth, $endDay, $includeAfter) {
	$year = (int)$year;
	$curMonth = (int)$curMonth;
	$endDay = Data::clampDay($year, $curMonth, $endDay);
	if (!$includeAfter || $curMonth === 12) {
		return $report->anomSpan($yrVal, 1, 1, $curMonth, $endDay, $year);
	}
	$ranges = [];
	if ($curMonth > 1) {
		$ranges[] = array(1, 1, $curMonth - 1, (int)Date::get_days_in_month($curMonth - 1, $year));
	}
	$ranges[] = array($curMonth, 1, $curMonth, $endDay);
	if ($curMonth < 12) {
		$ranges[] = array($curMonth + 1, 1, 12, (int)Date::get_days_in_month(12, $year));
	}
	$summable = isset(Wx::$daily[$report->type]['summable']) && Wx::$daily[$report->type]['summable'];
	$norm = 0.0;
	$n = 0.0;
	$any = false;
	foreach ($ranges as $r) {
		$a = LTA::getSpanAnom($report->type, $r[0], $r[1], $r[2], $r[3], $year);
		if ($a === null) { continue; }
		$any = true;
		$days = Data::boundsDayCount(array(
			'sy' => $year, 'sm' => $r[0], 'sd' => $r[1],
			'ey' => $year, 'em' => $r[2], 'ed' => $r[3],
		));
		if ($summable) { $norm += (float)$a; }
		else { $norm += (float)$a * $days; $n += $days; }
	}
	if (!$any) { return ''; }
	if (!$summable) {
		if ($n <= 0) { return ''; }
		$norm = $norm / $n;
	}
	if ($report->isSum) { return Util::percent($yrVal, $norm, 0, true, false); }
	return Wx::conv($yrVal - $norm, Wx::AbsTemp, 0, 1);
}

/** Meteorological-year bounds covering the seasons present in $present (keys winter..autumn). */
function dm_seasonal_year_bounds($year, $present = null) {
	$start = null;
	$end = null;
	foreach (Data::$seasonSpans as $s) {
		if (is_array($present) && !isset($present[$s])) { continue; }
		$b = Data::seasonBounds($s, $year, true);
		if ($b === null) { continue; }
		if ($start === null) { $start = $b; }
		$end = $b;
	}
	if ($start === null) { return null; }
	return array(
		'sy' => $start['sy'], 'sm' => $start['sm'], 'sd' => $start['sd'],
		'ey' => $end['ey'], 'em' => $end['em'], 'ed' => $end['ed'],
	);
}

function tdmMakeTable($report, $DAT, $st, $heading, $startYr, $threshold = null) {
	$unit = $report->summaryDisplayUnit($st);
	$isCount = Data::isCountSummary($st);
	$isAnom = $report->isAnom && ($st === Data::SUMMARY_MEAN || $st === Data::SUMMARY_SUM);
	$sumOff = ($st === Data::SUMMARY_SUM) ? $report->valcolSumOffset() : 1;
	$yrYest = (int)Date::$yr_yest;
	$monYest = (int)Date::$mon_yest;
	$span = $report->span;
	list($endM, $endD) = Data::spanEndMd();
	$cy = (int)Date::$dyear;
	$cm = (int)Date::$dmonth;
	$layout = dm_span_layout($span);
	$cols = $layout['cols'];
	$totalLab = $layout['totalLab'];
	$rowClass = $layout['rowClass'] ? (' ' . $layout['rowClass']) : '';
	$gridClass = $layout['gridClass'] ? (' ' . $layout['gridClass']) : '';
	$fromYr = max((int)$startYr, (int)$report->startYear);
	if ($span === 'full') {
		$lastYr = $yrYest;
	} elseif ($span === 'seasonal') {
		$labelY = Data::seasonLabelYear($cy, $cm);
		$lastYr = $labelY;
	} else {
		$lastYr = max($yrYest, $cy);
	}

	echo '<div class="dm-scroll"><div class="dm-grid' . $gridClass . '">';
	$captionExtra = $report->spanTitleBit();
	$caption = $heading . ' ' . $report->description . ($captionExtra !== '' ? ' · ' . $captionExtra : '');
	echo '<div class="dm-caption">' . htmlspecialchars($caption) . '</div>';

	$head = function () use ($cols, $totalLab, $rowClass) {
		echo '<div class="dm-row dm-head' . $rowClass . '">';
		dm_lab('');
		foreach ($cols as $col) {
			$title = !empty($col['prevYear']) ? ' title="December of the previous year"' : '';
			$lab = $col['lab'] . (!empty($col['prevYear']) ? '*' : '');
			echo '<div class="dm-lab"' . $title . '>' . $lab . '</div>';
		}
		if ($totalLab !== null) { dm_lab($totalLab, 'dm-yr'); }
		echo '</div>';
	};
	$head();

	$extreme = [];   // [year][colKey] and [colKey][year]
	$yrAgg = [];

	for ($y = $lastYr; $y >= $fromYr; $y--) {
		echo '<div class="dm-row' . $rowClass . '">';
		$yrLabel = $y . ($y < Site::BASE_YEAR ? '*' : '');
		echo '<div class="dm-year"><a href="/wxdataday.php?vartype='
			. htmlspecialchars($report->type) . '&year=' . $y
			. '" title="View full data for year">' . $yrLabel . '</a></div>';

		foreach ($cols as $ci => $col) {
			if (isset($col['season'])) {
				$ss = $col['season'];
				$colKey = $ss;
				$b = Data::seasonBounds($ss, $y, true);
				if ($b === null) {
					dm_cell('', 'dm-future');
					continue;
				}
				if ($ss === 'winter' && !Data::seasonStartDataExists($DAT, $ss, $y)) {
					dm_cell('-', 'reportday');
					continue;
				}
				$days = Data::MDtoZ(Data::mergeYearMonths(Data::sliceSeasonDays($DAT, $ss, $y)));
				$val = Data::summarize($days, $st, $threshold, $report->type);
				$partial = !Data::seasonIsComplete($ss, $y);
				$anom = ''; $value = '-'; $class = 'reportday';
				if ($val !== null && !Util::isBlank($val)) {
					$extreme[$y][$colKey] = $val;
					$extreme[$colKey][$y] = $val;
					$value = dm_fmt($report, $val, $st);
					$num = $report->valcolConvert ? Wx::convNum($val, $unit) : (float)$val;
					$class = $isCount
						? Report::dayCountClass($val)
						: $report->valcolr(($num === null ? 0 : $num) / $sumOff);
					if ($isAnom) {
						$anom = $report->anomCell($report->anomBounds($val, $b));
					}
				} elseif (Util::mycount($days) === 0) {
					$value = '-';
				} else {
					$value = ''; $class = 'invalid';
				}
				$mark = $partial ? ' <span class="rkf-prov" title="so far">so far</span>' : '';
				dm_cell($value . $anom . $mark, $class);
				continue;
			}

			$m = (int)$col['m'];
			$srcY = !empty($col['prevYear']) ? ($y - 1) : $y;
			$yearDays = isset($DAT[$srcY]) && is_array($DAT[$srcY]) ? $DAT[$srcY] : [];

			if ($span === 'ytd') {
				$isFuture = ($m > $endM) || Data::dateIsFuture($srcY, $m, 1);
			} elseif ($span === 'mtd') {
				$isFuture = Data::dateIsFuture($srcY, $m, 1);
			} else {
				$isFuture = ($srcY === $yrYest && $m > $monYest) || Data::dateIsFuture($srcY, $m, 1);
			}
			if ($isFuture) {
				dm_cell('', 'dm-future');
				continue;
			}

			$anom = ''; $value = '-'; $class = 'reportday';
			$anomStartM = $m; $anomStartD = 1; $anomEndM = $m; $anomEndD = 0;
			$mtdThis = ($span === 'mtd' && $m === $cm);
			if ($mtdThis) {
				$last = Data::clampDay($srcY, $m, $endD);
				$src = isset($yearDays[$m]) && is_array($yearDays[$m]) ? $yearDays[$m] : [];
				$days = [];
				foreach ($src as $d => $v) {
					if ((int)$d >= 1 && (int)$d <= $last) { $days[$d] = $v; }
				}
				$anomEndD = $last;
			} elseif ($span === 'ytd') {
				$fullDays = isset($DAT[$y]) && is_array($DAT[$y]) ? $DAT[$y] : [];
				$anomStartM = 1; $anomStartD = 1;
				if ($m < $endM) {
					$anomEndM = $m;
					$anomEndD = (int)Date::get_days_in_month($m, $y);
				} else {
					$anomEndM = $m;
					$anomEndD = Data::clampDay($y, $m, $endD);
				}
				$sliced = Data::sliceYearDays($fullDays, 'ytd', $y, $anomEndM, $anomEndD);
				$days = Data::MDtoZ($sliced);
			} else {
				$days = isset($yearDays[$m]) && is_array($yearDays[$m]) ? $yearDays[$m] : [];
			}
			$val = Data::summarize($days, $st, $threshold, $report->type);

			$colKey = (!empty($col['prevYear']) ? 'prev-' : '') . $m;
			if ($val !== null && !Util::isBlank($val)) {
				$extreme[$y][$colKey] = $val;
				$extreme[$colKey][$y] = $val;
				$value = dm_fmt($report, $val, $st);
				$num = $report->valcolConvert ? Wx::convNum($val, $unit) : (float)$val;
				$class = $isCount
					? Report::dayCountClass($val)
					: $report->valcolr(($num === null ? 0 : $num) / $sumOff);
				if ($isAnom) {
					if ($mtdThis) {
						$anom = $report->anomCell($report->anomSpan($val, $anomStartM, $anomStartD, $anomEndM, $anomEndD, $srcY));
					} elseif ($span === 'ytd') {
						$anom = $report->anomCell($report->anomSpan($val, $anomStartM, $anomStartD, $anomEndM, $anomEndD, $srcY));
					} else {
						$anom = $report->anomCell($report->anomMonth($val, $m));
					}
				}
			} elseif (Util::mycount($days) === 0) {
				$value = '-';
			} else {
				$value = ''; $class = 'invalid';
			}
			$lnk = "/wxhistmonth.php?year=$srcY&month=$m";
			$linked = ($srcY >= 2009) ?
				'<a class="hidden-link" href="' . $lnk . '" title="View detailed report for month">' . $value . '</a>' : $value;
			dm_cell($linked . $anom, $class);
		}

		if ($totalLab === null) {
			echo '</div>';
			continue;
		}

		if ($span === 'ytd') {
			$yearDays = isset($DAT[$y]) && is_array($DAT[$y]) ? $DAT[$y] : [];
			if (Data::dateIsFuture($y, 1, 1)) {
				$yrVal = null;
			} else {
				$sliced = Data::sliceYearDays($yearDays, 'ytd', $y, $endM, $endD);
				$yrVal = Data::summarize(Data::MDtoZ($sliced), $st, $threshold, $report->type);
			}
			if ($yrVal === null || Util::isBlank($yrVal)) {
				dm_cell('-', 'reportday', 'dm-yr');
			} else {
				$yrAgg[$y] = $yrVal;
				$anom = $isAnom ? $report->anomCell($report->anomSpan($yrVal, 1, 1, $endM, $endD, $y)) : '';
				$valyr = dm_fmt($report, $yrVal, $st);
				$class = $isCount
					? Report::dayCountClass($yrVal)
					: $report->valcolr($yrVal / $sumOff / (($st === Data::SUMMARY_SUM) ? max(1, $endM) : 1));
				dm_cell($valyr . $anom, $class, 'dm-yr');
			}
		} else {
			$months = isset($extreme[$y]) ? $extreme[$y] : [];
			if (count($months) === 0) {
				dm_cell('-', 'reportday', 'dm-yr');
			} else {
				if (Data::isThresholdSummary($st)) {
					$yrVal = nw3_array_sum($months);
				} elseif ($st === Data::SUMMARY_COUNT) {
					$yrVal = Util::mean($months);
				} elseif ($st === Data::SUMMARY_SUM) {
					$yrVal = nw3_array_sum($months);
				} elseif ($st === Data::SUMMARY_RANGE) {
					$yrVal = Util::mean($months);
				} else {
					$yrVal = Data::summarize($months, $st);
				}
				$yrAgg[$y] = $yrVal;
				if (!$isAnom) {
					$anom = '';
				} elseif ($span === 'mtd') {
					$includeAfter = !Data::dateIsFuture($y, 12, 1);
					$anom = $report->anomCell(dm_mtd_year_anom($report, $yrVal, $y, $cm, $endD, $includeAfter));
				} elseif ($span === 'seasonal') {
					$b = dm_seasonal_year_bounds($y, $months);
					$anom = ($b === null) ? '' : $report->anomCell($report->anomBounds($yrVal, $b));
				} else {
					$anom = $report->anomCell($report->anomYear($yrVal));
				}
				$valyr = dm_fmt($report, $yrVal, $st);
				$cntM = ($st === Data::SUMMARY_SUM) ? max(1, count($months)) : 1;
				$class = $isCount
					? Report::dayCountClass($yrVal)
					: $report->valcolr($yrVal / $sumOff / $cntM);
				dm_cell($valyr . $anom, $class, 'dm-yr');
			}
		}
		echo '</div>';
	}

	echo '<div class="dm-row dm-head dm-sep' . $rowClass . '">';
	dm_lab('');
	foreach ($cols as $col) {
		$lab = $col['lab'] . (!empty($col['prevYear']) ? '*' : '');
		dm_lab($lab);
	}
	if ($totalLab !== null) { dm_lab($totalLab, 'dm-yr'); }
	echo '</div>';

	$labels = ['Lowest', 'Highest', 'Mean'];
	for ($mm = 0; $mm < 3; $mm++) {
		echo '<div class="dm-row dm-sum' . $rowClass . '">';
		dm_lab($labels[$mm], 'dm-sum-lab');
		foreach ($cols as $col) {
			if (isset($col['season'])) {
				$colKey = $col['season'];
				$c = isset($extreme[$colKey]) ? $extreme[$colKey] : [];
				if (count($c) === 0) { dm_cell('-', 'reportday', 'dm-sum-cell'); continue; }
				$v = Report::aggregate($c, $mm);
				$value = dm_fmt($report, $v, $st);
				if (!$isAnom) {
					$anom = '';
				} else {
					$dummyY = 2001;
					$b = Data::seasonBounds($col['season'], $dummyY, false);
					$curLabel = Data::seasonLabelYear($cy, $cm);
					if ($b && !Data::seasonIsComplete($col['season'], $curLabel)) {
						$bNow = Data::seasonBounds($col['season'], $curLabel, true);
						if ($bNow) {
							$b['em'] = $bNow['em'];
							$b['ed'] = $bNow['ed'];
						}
					}
					$anom = ($b === null) ? '' : $report->anomCell($report->anomBounds($v, $b));
				}
				$class = $isCount
					? Report::dayCountClass($v)
					: $report->valcolr($v / $sumOff);
				dm_cell($value . $anom, $class, 'dm-sum-cell');
				continue;
			}
			$colKey = (!empty($col['prevYear']) ? 'prev-' : '') . $col['m'];
			$m = (int)$col['m'];
			$c = isset($extreme[$colKey]) ? $extreme[$colKey] : [];
			if (count($c) === 0) { dm_cell('-', 'reportday', 'dm-sum-cell'); continue; }
			$v = Report::aggregate($c, $mm);
			$value = dm_fmt($report, $v, $st);
			if (!$isAnom) {
				$anom = '';
			} elseif ($span === 'mtd' && $m === $cm) {
				$anom = $report->anomCell($report->anomSpan($v, $m, 1, $m, Data::clampDay(2001, $m, $endD), 2001));
			} elseif ($span === 'ytd') {
				$em = $m;
				$ed = ($m < $endM) ? (int)Date::get_days_in_month($m, 2001) : Data::clampDay(2001, $m, $endD);
				$anom = $report->anomCell($report->anomSpan($v, 1, 1, $em, $ed, 2001));
			} else {
				$anom = $report->anomCell($report->anomMonth($v, $m));
			}
			$class = $isCount
				? Report::dayCountClass($v)
				: $report->valcolr($v / $sumOff);
			dm_cell($value . $anom, $class, 'dm-sum-cell');
		}
		if ($totalLab !== null) {
			if (count($yrAgg) === 0) { dm_cell('-', 'reportday', 'dm-sum-cell dm-yr'); }
			else {
				$yv = Report::aggregate($yrAgg, $mm);
				$valall = dm_fmt($report, $yv, $st);
				if (!$isAnom) {
					$anom = '';
				} elseif ($span === 'ytd') {
					$anom = $report->anomCell($report->anomSpan($yv, 1, 1, $endM, $endD, 2001));
				} elseif ($span === 'mtd') {
					$anom = $report->anomCell(dm_mtd_year_anom($report, $yv, 2001, $cm, $endD, true));
				} elseif ($span === 'seasonal') {
					$dummyY = 2001;
					$b = dm_seasonal_year_bounds($dummyY);
					$curLabel = Data::seasonLabelYear($cy, $cm);
					$bNow = dm_seasonal_year_bounds($curLabel);
					if ($b && $bNow) {
						$b['em'] = $bNow['em'];
						$b['ed'] = $bNow['ed'];
					}
					$anom = ($b === null) ? '' : $report->anomCell($report->anomBounds($yv, $b));
				} else {
					$anom = $report->anomCell($report->anomYear($yv));
				}
				$class = $isCount
					? Report::dayCountClass($yv)
					: $report->valcolr($yv / $sumOff);
				dm_cell($valall . $anom, $class, 'dm-sum-cell dm-yr');
			}
		}
		echo '</div>';
	}

	if ($span === 'seasonal') {
		echo '<p class="rk-blurb">* December is from the previous year (Winter YYYY = Dec of YYYY−1 + Jan–Feb of YYYY).</p>';
	}

	echo '</div></div>'; // .dm-grid + .dm-scroll
}

/**
 * Render all monthly summary tabs + footer blurb for $report.
 * @return array meta for AJAX clients
 */
function nw3_datamonth_render(Report $report) {
	$t0 = microtime(true);
	$report->normalizeSpanFor('monthly');
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
			'span' => $report->span,
		);
	}

	$loadFrom = ($report->span === 'seasonal')
		? max(1, (int)$report->startYrReport - 1)
		: (int)$report->startYrReport;
	$DAT = Data::getDailyData($report->type, $loadFrom);
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
		$adj = ($report->span === 'full')
			? "(the anomaly for the current month is unadjusted for the month's degree of completeness)."
			: '(anomalies are against the 1991-2020 normal for the same date span).';
		echo '<p>Figures in brackets refer to departure from <strong>recent</strong> '
			. '<a href="/wxaverages.php" title="Long-term NW3 climate averages">average conditions</a> '
			. $adj . '</p>';
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
		'span' => $report->span,
	);
}
