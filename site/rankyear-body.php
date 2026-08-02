<?php
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
	$startY = max((int)$report->startYrReport, (int)$report->startYear);
	$endY = (int)Date::$dyear;
	$threshold = $report->spellThreshold;

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
		foreach (Data::getAnnualData($type, $st, $startY, $endY, $th) as $y => $v) {
			if (!Util::isBlank($v)) {
				$ranked[] = [(float)$v, Date::mkdate(1, 1, $y)];
			}
		}
		sort($ranked);
		$sortLen = count($ranked);
		$limit = $sortLen;

		$highest = $lowest = $highestDay = $lowestDay = [];
		$fmtYr = function ($ts) use ($yearSecs, $type) {
			$y = (int)date('Y', $ts);
			$label = (string)$y . ($y < Site::BASE_YEAR ? '*' : '');
			if ((Date::$dtstamp - $ts) < $yearSecs) { $label = '<b>' . $label . '</b>'; }
			$href = '/wxdataday.php?vartype=' . rawurlencode($type) . '&year=' . $y;
			return '<a class="hidden-link" href="' . htmlspecialchars($href)
				. '" title="View full data for year">' . $label . '</a>';
		};
		for ($i = 1; $i <= $limit; $i++) {
			if ($sortLen < $i) { break; }
			$highest[$i] = $ranked[$sortLen - $i][0];
			$highestDay[$i] = $fmtYr($ranked[$sortLen - $i][1]);
			$lowest[$i] = $ranked[$i - 1][0];
			$lowestDay[$i] = $fmtYr($ranked[$i - 1][1]);
		}

		foreach ($ranked as $rnk => $val) {
			if ($val[1] === $curYearTs) {
				$highest['today'] = $lowest['today'] = $val[0];
				$lowestDay['today'] = $rnk + 1;
				$highestDay['today'] = $sortLen - $rnk;
			}
			if ($val[1] === $lastYearTs) {
				$highest['yest'] = $lowest['yest'] = $val[0];
				$lowestDay['yest'] = $rnk + 1;
				$highestDay['yest'] = $sortLen - $rnk;
			}
		}

		// Annual totals are ~12× monthly; scale colour divisor accordingly.
		$sumOff = ($st === Data::SUMMARY_SUM) ? $report->valcolSumOffset() * 12 : 1;
		$isCount = Data::isCountSummary($st);
		$displayUnit = $report->summaryDisplayUnit($st);
		$name = $report->summaryCaptionName($st);
		echo "<div id='rank-$st' class='rank-tab'>";
		echo '<div class="rk-pair">';
		$report->rankTable($highest, $highestDay, $limit, 'Top annual ' . $name, true, true, true, 'annual', $isCount, $sumOff, $displayUnit);
		$report->rankTable($lowest, $lowestDay, $limit, 'Bottom annual ' . $name, false, true, true, 'annual', $isCount, $sumOff, $displayUnit);
		echo '</div>';
		$extraTh = Data::isThresholdSummary($st) ? (' (' . $report->summaryThresholdPhrase($st) . ')') : '';
		echo '<p class="rk-blurb">' . $report->description . ' annual extremes: ' . $explain[$st] . $extraTh . '.<br />'
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
	);
}
