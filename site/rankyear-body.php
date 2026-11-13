<?php

/**
 * First air frost annual rankings (earliest / latest date of season).
 */
function nw3_rankyear_render_first_frost(Report $report, $t0) {
	$type = $report->type;
	$startY = max((int)$report->startYrReport, (int)$report->startYear);
	$endY = (int)Date::$dyear;
	$rows = Data::getFirstAirFrost($startY, $endY);

	$ranked = [];
	foreach ($rows as $y => $row) {
		$ranked[] = [(int)$row['offset'], Date::mkdate(1, 1, $y), $row];
	}
	sort($ranked);
	$sortLen = count($ranked);
	$limit = $sortLen;

	$highest = $lowest = $highestDay = $lowestDay = $hiValHtml = $loValHtml = [];
	$hiKeys = $loKeys = [];
	$yrKey = function ($ts) { return date('Y', $ts); };
	for ($i = 1; $i <= $limit; $i++) {
		if ($sortLen < $i) { break; }
		// Earliest = smallest offset; latest = largest (none / none yet last).
		$highest[$i] = $ranked[$i - 1][0];
		$highestDay[$i] = Report::yearDateHtml($ranked[$i - 1][1], 'nightmin');
		$hiValHtml[$i] = Report::firstFrostValueHtml($ranked[$i - 1][2]);
		$hiKeys[$i] = $yrKey($ranked[$i - 1][1]);
		$lowest[$i] = $ranked[$sortLen - $i][0];
		$lowestDay[$i] = Report::yearDateHtml($ranked[$sortLen - $i][1], 'nightmin');
		$loValHtml[$i] = Report::firstFrostValueHtml($ranked[$sortLen - $i][2]);
		$loKeys[$i] = $yrKey($ranked[$sortLen - $i][1]);
	}

	$curYearTs = Date::mkdate(1, 1, (int)Date::$dyear);
	$lastYearTs = Date::mkdate(1, 1, (int)Date::$dyear - 1);
	$keys = array('today' => $yrKey($curYearTs), 'yest' => $yrKey($lastYearTs));
	$labels = array(
		'todayLabel' => 'Current year (so far)',
		'yestLabel' => 'Last year',
	);
	$cur = Report::currentRanks($ranked, $keys, $yrKey);
	// Earliest = smaller offset is better (fromHigh false); latest = larger offset.
	$ctxHi = Report::rankCtx($cur, $hiKeys, $keys, false, $highest, $labels);
	$ctxLo = Report::rankCtx($cur, $loKeys, $keys, true, $lowest, $labels);
	if (isset($ctxHi['today'])) { $hiValHtml['today'] = Report::firstFrostValueHtml($rows[(int)$keys['today']]); }
	if (isset($ctxHi['yest'])) { $hiValHtml['yest'] = Report::firstFrostValueHtml($rows[(int)$keys['yest']]); }
	if (isset($ctxLo['today'])) { $loValHtml['today'] = $hiValHtml['today']; }
	if (isset($ctxLo['yest'])) { $loValHtml['yest'] = $hiValHtml['yest']; }

	$st = (int)$report->summaryType;
	echo "<div id='rank-$st' class='rank-tab'>";
	$curLines = array(
		'today' => date('Y', $curYearTs) . ' (so far)',
		'yest' => date('Y', $lastYearTs),
	);
	foreach ($curLines as $k => $lbl) {
		if (isset($cur[$k]) && isset($rows[(int)$keys[$k]])) {
			echo Report::currentRankLine(
				$lbl,
				$cur[$k]['hi'],
				$cur[$k]['lo'],
				Report::firstFrostValueChip($rows[(int)$keys[$k]]),
				'latest',
				'earliest'
			);
		}
	}
	echo '<div class="rk-pair">';
	$report->rankTable($highest, $highestDay, $limit, 'Earliest first air frost', true, true, true, 'annual', false, 1, Wx::None, $ctxHi, $hiValHtml);
	$report->rankTable($lowest, $lowestDay, $limit, 'Latest first air frost', false, true, true, 'annual', false, 1, Wx::None, $ctxLo, $loValHtml);
	echo '</div>';
	echo '<p class="rk-blurb">' . $report->description
		. ' of the season (1 September to 31 March): earliest and latest dates, ranked by day of the season. '
		. 'An air frost is a night (21:00–09:00) when the overnight minimum is below 0 °C. '
		. 'Years with no air frost in that window are ranked latest as none. The current season is provisional until a frost occurs or 31 March passes.<br />'
		. 'There are <b>' . $sortLen . '</b> valid years from ' . $startY
		. ' to present in London, NW3. Data for Night Minimum begins in ' . (int)$report->startYear . '.</p>';
	echo '</div>';

	$report->historicalInfo($startY);
	$report->echoVarAbout();
	$report->logRankRuntime($t0, 'year');

	return array(
		'type' => $type,
		'startYearRep' => (int)$report->startYrReport,
		'startYearOptions' => array_map('intval', $report->startYearOptions),
		'summaryType' => (int)$report->summaryType,
		'summaryTypes' => $report->availSummaryTypes,
		'rankLimit' => (int)$report->rankLimit,
		'threshold' => $report->spellThreshold,
		'thresholds' => $report->spellThresholds,
		'thresholdLabels' => $report->thresholdLabels(),
		'title' => $report->summaryTitleSuffix(),
		'span' => 'full',
	);
}

/**
 * Shared renderer for ranked annual data (RankYear / rankyeardata).
 */

/**
 * @param Report $report
 * @return array meta for AJAX clients
 */
function nw3_rankyear_render(Report $report) {
	$t0 = microtime(true);
	$type = $report->type;
	$report->normalizeSpanFor('rank-annual');
	$span = $report->span;
	if ($report->isAnnualOnly() && $type === 'affirst') {
		return nw3_rankyear_render_first_frost($report, $t0);
	}
	$startY = max((int)$report->startYrReport, (int)$report->startYear);
	$endY = (int)Date::$dyear;
	$threshold = $report->spellThreshold;
	list($endM, $endD) = Data::spanEndMd();

	$yearSecs = 86400 * 365;
	$curYearTs = Date::mkdate(1, 1, (int)Date::$dyear);
	$lastYearTs = Date::mkdate(1, 1, (int)Date::$dyear - 1);

	$explain = [
		Data::SUMMARY_MEAN => 'highest and lowest annual means',
		Data::SUMMARY_SUM => 'highest and lowest annual totals',
		Data::SUMMARY_COUNT => 'most and fewest days with a non-zero value',
		Data::SUMMARY_MIN => 'highest and lowest annual minima',
		Data::SUMMARY_MAX => 'highest and lowest annual maxima',
		Data::SUMMARY_RANGE => 'highest and lowest annual ranges (daily max − min)',
		Data::SUMMARY_COUNT_ABOVE => 'most and fewest days at or above the chosen threshold',
		Data::SUMMARY_COUNT_BELOW => 'most and fewest days below the chosen threshold',
	];

	foreach ($report->availSummaryTypes as $st) {
		// Render the active summary only — AJAX reloads when the chip changes.
		if ($st !== $report->summaryType) {
			continue;
		}
		$th = Data::isThresholdSummary($st) ? $threshold : null;
		$ranked = [];
		foreach (Data::getAnnualData($type, $st, $startY, $endY, $th, $span) as $y => $v) {
			if ($span === 'ytd' && Data::dateIsFuture($y, 1, $endD)) {
				continue;
			}
			if (!Util::isBlank($v)) {
				$ranked[] = [(float)$v, Date::mkdate(1, 1, $y)];
			}
		}
		sort($ranked);
		$sortLen = count($ranked);
		$limit = $sortLen;

		$highest = $lowest = $highestDay = $lowestDay = [];
		$fmtYr = function ($ts) use ($type, $span, $endM, $endD) {
			return Report::yearSpanDateHtml($ts, $span, $endM, $endD, $type);
		};
		$yrKey = function ($ts) { return date('Y', $ts); };
		$hiKeys = $loKeys = [];
		for ($i = 1; $i <= $limit; $i++) {
			if ($sortLen < $i) { break; }
			$highest[$i] = $ranked[$sortLen - $i][0];
			$highestDay[$i] = $fmtYr($ranked[$sortLen - $i][1]);
			$hiKeys[$i] = $yrKey($ranked[$sortLen - $i][1]);
			$lowest[$i] = $ranked[$i - 1][0];
			$lowestDay[$i] = $fmtYr($ranked[$i - 1][1]);
			$loKeys[$i] = $yrKey($ranked[$i - 1][1]);
		}

		$isSeason = Data::isSeasonSpan($span);
		$samePeriod = ($span === 'ytd');
		$seasonInProgress = $isSeason
			&& (Data::seasonIndex($span) === ((int)Date::$season - 1));
		if ($isSeason) {
			$labelY = Data::seasonLabelYear((int)Date::$dyear, (int)Date::$dmonth);
			if ($seasonInProgress) {
				$keys = array('today' => (string)$labelY, 'yest' => (string)($labelY - 1));
			} else {
				$finishedThisCycle = Data::seasonBounds($span, $labelY, false);
				$latest = ($finishedThisCycle !== null
					&& !Data::dateIsFuture($finishedThisCycle['ey'], $finishedThisCycle['em'], $finishedThisCycle['ed']))
					? $labelY : ($labelY - 1);
				$keys = array('today' => (string)$latest, 'yest' => (string)($latest - 1));
			}
		} else {
			$keys = array('today' => $yrKey($curYearTs), 'yest' => $yrKey($lastYearTs));
		}
		$cur = Report::currentRanks($ranked, $keys, $yrKey);
		if ($isSeason) {
			$labels = array(
				'todayLabel' => $seasonInProgress ? 'Current season (so far)' : 'Current season',
				'yestLabel' => 'Last year',
			);
		} else {
			$labels = array(
				'todayLabel' => 'Current year (so far)',
				'yestLabel' => $samePeriod ? 'Last year (same period)' : 'Last year',
			);
		}
		$ctxHi = Report::rankCtx($cur, $hiKeys, $keys, true, $highest, $labels);
		$ctxLo = Report::rankCtx($cur, $loKeys, $keys, false, $lowest, $labels);

		// Annual totals are ~12× monthly; scale colour divisor for MTD/YTD length.
		if ($st !== Data::SUMMARY_SUM) {
			$sumOff = 1;
		} elseif ($span === 'ytd') {
			$doy = (int)date('z', Date::mkdate($endM, $endD, 2001)) + 1;
			$sumOff = $report->valcolSumOffset() * 12 * $doy / 365.0;
		} elseif (Data::isSeasonSpan($span)) {
			$sumOff = $report->valcolSumOffset() * 3;
		} else {
			$sumOff = $report->valcolSumOffset() * 12;
		}
		$isCount = Data::isCountSummary($st);
		$displayUnit = $report->summaryDisplayUnit($st);
		$name = $report->summaryCaptionName($st);
		if ($span === 'ytd') { $topKind = 'year-to-date '; }
		elseif ($isSeason) { $topKind = Date::$snames[Data::seasonIndex($span)] . ' '; }
		else { $topKind = 'annual '; }
		echo "<div id='rank-$st' class='rank-tab'>";
		if ($isSeason) {
			$sName = Date::$snames[Data::seasonIndex($span)];
			$curLines = array(
				'today' => $sName . ' ' . $keys['today'] . ($seasonInProgress ? ' (so far)' : ''),
				'yest' => $sName . ' ' . $keys['yest'],
			);
			if (!$seasonInProgress) {
				# Completed current season already appears in the table — no banner.
				unset($curLines['today'], $cur['today']);
			}
		} else {
			$curLines = array(
				'today' => date('Y', $curYearTs) . ' (so far)',
				'yest' => date('Y', $lastYearTs) . ($samePeriod ? ' (same period)' : ''),
			);
		}
		foreach ($curLines as $k => $lbl) {
			if (isset($cur[$k])) {
				echo Report::currentRankLine($lbl, $cur[$k]['hi'], $cur[$k]['lo'],
					$report->rankValueText($cur[$k]['v'], $isCount, $displayUnit), 'highest', 'lowest');
			}
		}
		echo '<div class="rk-pair">';
		$report->rankTable($highest, $highestDay, $limit, 'Top ' . $topKind . $name, true, true, true, 'annual', $isCount, $sumOff, $displayUnit, $ctxHi);
		$report->rankTable($lowest, $lowestDay, $limit, 'Bottom ' . $topKind . $name, false, true, true, 'annual', $isCount, $sumOff, $displayUnit, $ctxLo);
		echo '</div>';
		$extraTh = Data::isThresholdSummary($st) ? (' (' . $report->summaryThresholdPhrase($st) . ')') : '';
		$spanNote = $report->spanTitleBit();
		$spanNote = ($spanNote === '') ? '' : (' ' . $spanNote);
		echo '<p class="rk-blurb">' . $report->description . ' annual extremes: ' . $explain[$st] . $extraTh . $spanNote . '.<br />'
			. 'There are <b>' . $sortLen . '</b> valid years for the chosen period from ' . $startY
			. ' to present in London, NW3. Data for ' . $report->description
			. ' begins in ' . (int)$report->startYear . '.</p>';
		echo '</div>';
	}

	$report->historicalInfo($startY);
	$report->echoVarAbout();
	$report->logRankRuntime($t0, 'year');

	return array(
		'type' => $type,
		'startYearRep' => (int)$report->startYrReport,
		'startYearOptions' => array_map('intval', $report->startYearOptions),
		'summaryType' => (int)$report->summaryType,
		'summaryTypes' => $report->availSummaryTypes,
		'rankLimit' => (int)$report->rankLimit,
		'threshold' => $report->spellThreshold,
		'thresholds' => $report->spellThresholds,
		'thresholdLabels' => $report->thresholdLabels(),
		'title' => $report->summaryTitleSuffix(),
		'span' => $report->span,
	);
}
