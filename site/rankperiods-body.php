<?php
/**
 * Shared renderer for ranked N-day periods (RankPeriods / rankperiodsdata).
 */

/**
 * Format a period start–end label for ranking tables.
 * @param int $startTs
 * @param int $endTs
 * @return string
 */
function nw3_period_date_label($startTs, $endTs) {
	$startY = (int)date('Y', $startTs);
	$endY = (int)date('Y', $endTs);
	$star = ($startY < Site::BASE_YEAR) ? '*' : '';
	if ($startY === $endY && date('n', $startTs) === date('n', $endTs)) {
		// Same month: "1–5 Jul 2024"
		$s = date('d', $startTs) . '–' . date('d M Y', $endTs) . $star;
	} elseif ($startY === $endY) {
		$s = date('d M', $startTs) . ' – ' . date('d M Y', $endTs) . $star;
	} else {
		$s = date('d M Y', $startTs) . ' – ' . date('d M Y', $endTs) . $star;
	}
	$yearSecs = 86400 * 365;
	if ((Date::$dtstamp - $endTs) < $yearSecs) {
		$s = '<b>' . $s . '</b>';
	}
	return $s;
}

/**
 * Greedily pick up to $limit periods from a value-sorted list, optionally
 * skipping any that overlap an already-chosen window.
 * @param array $ranked list of [value, startTs, endTs], sorted ascending by value
 * @param int $limit
 * @param bool $fromHigh true = take from the high end first
 * @param bool $noOverlap
 * @return array picked rows in rank order (best first)
 */
function nw3_period_pick($ranked, $limit, $fromHigh, $noOverlap) {
	$n = count($ranked);
	$picked = [];
	if ($n === 0 || $limit < 1) { return $picked; }

	$indices = $fromHigh
		? range($n - 1, 0, -1)
		: range(0, $n - 1);

	foreach ($indices as $idx) {
		if (count($picked) >= $limit) { break; }
		$cand = $ranked[$idx];
		if ($noOverlap) {
			$ok = true;
			foreach ($picked as $p) {
				if ($cand[1] <= $p[2] && $p[1] <= $cand[2]) {
					$ok = false;
					break;
				}
			}
			if (!$ok) { continue; }
		}
		$picked[] = $cand;
	}
	return $picked;
}

/**
 * Status line for the anomaly summary: is the latest complete N-day window a
 * heatwave (every day > normal + X) or cold snap (every day < normal − X)?
 * @param array|null $row [meanAnom, startTs, endTs, allHot, allCold]
 * @return string HTML
 */
function nw3_period_anom_status(Report $report, $length, $x, $row, $startTs, $endTs) {
	$xTxt = Wx::plainText(Wx::conv(abs((float)$x), Wx::AbsTemp, true));
	$when = 'Latest ' . (int)$length . '-day period (' . strip_tags(nw3_period_date_label($startTs, $endTs)) . ')';
	if ($row === null) {
		return '<p class="rk-status rk-status-off">' . htmlspecialchars($when) . ': incomplete data.</p>';
	}
	$mean = htmlspecialchars(Spells::anomText($row[0]));
	if (!empty($row[3])) {
		return '<p class="rk-status rk-status-hot rk-status-on"><b>Heatwave:</b> every day of the '
			. htmlspecialchars(lcfirst($when)) . ' was more than ' . htmlspecialchars($xTxt)
			. ' above normal (mean ' . $mean . ').</p>';
	}
	if (!empty($row[4])) {
		return '<p class="rk-status rk-status-cold rk-status-on"><b>Cold snap:</b> every day of the '
			. htmlspecialchars(lcfirst($when)) . ' was more than ' . htmlspecialchars($xTxt)
			. ' below normal (mean ' . $mean . ').</p>';
	}
	return '<p class="rk-status rk-status-off">' . htmlspecialchars($when) . ': mean ' . $mean
		. ' vs normal; not a heatwave or cold snap (needs every day beyond normal ± ' . htmlspecialchars($xTxt) . ').</p>';
}

/**
 * @param Report $report
 * @return array meta for AJAX clients
 */
function nw3_rankperiods_render(Report $report) {
	$t0 = microtime(true);
	$type = $report->type;
	$month = $report->month;
	$startY = max((int)$report->startYrReport, (int)$report->startYear);
	$threshold = $report->spellThreshold;
	$length = (int)$report->periodLength;
	$noOverlap = !empty($report->periodNoOverlap);

	$explain = [
		Data::SUMMARY_MEAN => 'highest and lowest period means',
		Data::SUMMARY_SUM => 'highest and lowest period totals',
		Data::SUMMARY_COUNT => 'most and fewest non-zero days in the period',
		Data::SUMMARY_MIN => 'highest and lowest period minima',
		Data::SUMMARY_MAX => 'highest and lowest period maxima',
		Data::SUMMARY_RANGE => 'highest and lowest period ranges (daily max − min)',
		Data::SUMMARY_COUNT_ABOVE => 'most and fewest days at or above the chosen threshold',
		Data::SUMMARY_COUNT_BELOW => 'most and fewest days below the chosen threshold',
		Data::SUMMARY_ANOM => 'warmest and coldest periods relative to the daily 1991-2020 normal (mean of each day\'s anomaly)',
	];

	// Latest complete window ends on yesterday (most recent finished day).
	$latestEnd = Date::mkdate((int)Date::$mon_yest, (int)Date::$day_yest, (int)Date::$yr_yest);
	$prevEnd = $latestEnd - 86400;
	$latestStart = $latestEnd - ($length - 1) * 86400;
	$prevStart = $prevEnd - ($length - 1) * 86400;

	foreach ($report->availSummaryTypes as $st) {
		// Period rankings are expensive over long histories — render the active
		// summary only (AJAX reloads when the aggregate chip changes).
		if ($st !== $report->summaryType) {
			continue;
		}
		$isAnom = ($st === Data::SUMMARY_ANOM);
		$th = Data::isThresholdSummary($st) ? $threshold : null;
		$ranked = $isAnom
			? Spells::periodAnomWindows($type, $length, $startY, $month, (float)$threshold)
			: Data::getPeriodWindows($type, $length, $st, $startY, $month, $th);
		sort($ranked);
		$sortLen = count($ranked);
		$limit = ($sortLen / 2 < $report->rankLimit) ? 1 + (int)($sortLen / 2) : $report->rankLimit;

		$topRows = nw3_period_pick($ranked, $limit, true, $noOverlap);
		$botRows = nw3_period_pick($ranked, $limit, false, $noOverlap);
		$limitTop = count($topRows);
		$limitBot = count($botRows);
		$limitShow = max($limitTop, $limitBot);

		$xTxt = $isAnom ? Wx::plainText(Wx::conv(abs((float)$threshold), Wx::AbsTemp, true)) : '';
		$hotTag = $isAnom ? ' <span class="rk-flag rk-flag-hot" title="Heatwave: every day &gt; normal + ' . htmlspecialchars($xTxt) . '">heatwave</span>' : '';
		$coldTag = $isAnom ? ' <span class="rk-flag rk-flag-cold" title="Cold snap: every day &lt; normal − ' . htmlspecialchars($xTxt) . '">cold snap</span>' : '';
		$highest = $lowest = $highestDay = $lowestDay = [];
		for ($i = 1; $i <= $limitTop; $i++) {
			$highest[$i] = $topRows[$i - 1][0];
			$highestDay[$i] = nw3_period_date_label($topRows[$i - 1][1], $topRows[$i - 1][2])
				. (($isAnom && !empty($topRows[$i - 1][3])) ? $hotTag : '');
		}
		for ($i = 1; $i <= $limitBot; $i++) {
			$lowest[$i] = $botRows[$i - 1][0];
			$lowestDay[$i] = nw3_period_date_label($botRows[$i - 1][1], $botRows[$i - 1][2])
				. (($isAnom && !empty($botRows[$i - 1][4])) ? $coldTag : '');
		}
		// Window timestamps are noon-based; match the latest window by date.
		$latestRow = null;
		$latestKey = date('Y-m-d', $latestStart);
		foreach ($ranked as $val) {
			if (date('Y-m-d', $val[1]) === $latestKey) { $latestRow = $val; break; }
		}

		$showFoot = ($month === 0 || (int)date('n', $latestStart) === $month);
		foreach ($ranked as $rnk => $val) {
			if ($val[1] === $latestStart && $val[2] === $latestEnd) {
				$highest['today'] = $lowest['today'] = $val[0];
				$lowestDay['today'] = $rnk + 1;
				$highestDay['today'] = $sortLen - $rnk;
			}
			if ($val[1] === $prevStart && $val[2] === $prevEnd) {
				$highest['yest'] = $lowest['yest'] = $val[0];
				$lowestDay['yest'] = $rnk + 1;
				$highestDay['yest'] = $sortLen - $rnk;
			}
		}

		$sumOff = ($st === Data::SUMMARY_SUM) ? $report->valcolSumOffset() * max(1, $length / 30) : 1;
		$isCount = Data::isCountSummary($st);
		$displayUnit = $report->summaryDisplayUnit($st);
		$name = $length . '-day ' . $report->summaryCaptionName($st);
		echo "<div id='rank-$st' class='rank-tab'>";
		if ($isAnom) {
			echo nw3_period_anom_status($report, $length, $threshold, $latestRow, $latestStart, $latestEnd);
			$report->valcolIdxOverride = 15;
		}
		echo '<div class="rk-pair">';
		$report->rankTable($highest, $highestDay, $limitShow, $isAnom ? ('Warmest ' . $length . '-day periods vs normal') : ('Top ' . $name), true, true, $showFoot, 'period', $isCount, $sumOff, $displayUnit);
		$report->rankTable($lowest, $lowestDay, $limitShow, $isAnom ? ('Coldest ' . $length . '-day periods vs normal') : ('Bottom ' . $name), false, true, $showFoot, 'period', $isCount, $sumOff, $displayUnit);
		echo '</div>';
		$report->valcolIdxOverride = null;
		$extraMon = ($month === 0) ? '' : ' starting in ' . Date::$months3[$month - 1];
		$extraTh = Data::isThresholdSummary($st) ? (' (' . $report->summaryThresholdPhrase($st) . ')') : '';
		if ($isAnom) {
			$extraTh = '. Tagged periods had every day beyond normal ± ' . htmlspecialchars($xTxt)
				. ' (heatwave above, cold snap below)';
		}
		$overlapNote = $noOverlap ? ', hiding overlapping windows' : '';
		echo '<p class="rk-blurb">' . $report->description . ' ' . $length . '-day extremes: '
			. $explain[$st] . $extraTh . '.<br />'
			. 'There are <b>' . $sortLen . '</b> valid ' . $length . '-day periods from ' . $startY
			. ' to present' . $extraMon . ' in London, NW3 (complete windows only'
			. $overlapNote . '). Data for '
			. $report->description . ' begins in ' . (int)$report->startYear . '.</p>';
		echo '</div>';
	}

	$report->historicalInfo($startY);
	$report->echoVarAbout();
	$report->logRankRuntime($t0, 'periods');

	return array(
		'type' => $type,
		'month' => (int)$month,
		'startYearRep' => (int)$report->startYrReport,
		'startYearOptions' => array_map('intval', $report->startYearOptions),
		'summaryType' => (int)$report->summaryType,
		'summaryTypes' => $report->availSummaryTypes,
		'rankLimit' => (int)$report->rankLimit,
		'periodLength' => $length,
		'periodNoOverlap' => $noOverlap ? 1 : 0,
		'threshold' => $report->spellThreshold,
		'thresholds' => $report->spellThresholds,
		'thresholdLabels' => $report->thresholdLabels(),
		'title' => $report->periodTitleSuffix(),
	);
}
