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
	$span = $report->span;
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
			if ($span !== 'full' && Data::dateIsFuture($y, ($span === 'mtd') ? $endM : 1, ($span === 'mtd') ? 1 : $endD)) {
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
		$fmtYr = function ($ts) use ($yearSecs, $type, $span, $endM, $endD) {
			$y = (int)date('Y', $ts);
			$label = (string)$y . ($y < Site::BASE_YEAR ? '*' : '');
			if ($span === 'mtd' || $span === 'ytd') {
				$label .= ' (' . Data::spanRangeText($span, ($span === 'mtd') ? $endM : $endM, $endD, $y) . ')';
			}
			if ((Date::$dtstamp - $ts) < $yearSecs) { $label = '<b>' . $label . '</b>'; }
			$href = '/wxdataday.php?vartype=' . rawurlencode($type) . '&year=' . $y;
			return '<a class="hidden-link" href="' . htmlspecialchars($href)
				. '" title="View full data for year">' . $label . '</a>';
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

		// Current year is partial (so far); last year is complete.
		$keys = array('today' => $yrKey($curYearTs), 'yest' => $yrKey($lastYearTs));
		$cur = Report::currentRanks($ranked, $keys, $yrKey);
		$samePeriod = ($span === 'mtd' || $span === 'ytd');
		$labels = array(
			'todayLabel' => 'Current year (so far)',
			'yestLabel' => $samePeriod ? 'Last year (same period)' : 'Last year',
		);
		$ctxHi = Report::rankCtx($cur, $hiKeys, $keys, true, $highest, $labels);
		$ctxLo = Report::rankCtx($cur, $loKeys, $keys, false, $lowest, $labels);

		// Annual totals are ~12× monthly; scale colour divisor for MTD/YTD length.
		if ($st !== Data::SUMMARY_SUM) {
			$sumOff = 1;
		} elseif ($span === 'mtd') {
			$sumOff = $report->valcolSumOffset();
		} elseif ($span === 'ytd') {
			$doy = (int)date('z', Date::mkdate($endM, $endD, 2001)) + 1;
			$sumOff = $report->valcolSumOffset() * 12 * $doy / 365.0;
		} else {
			$sumOff = $report->valcolSumOffset() * 12;
		}
		$isCount = Data::isCountSummary($st);
		$displayUnit = $report->summaryDisplayUnit($st);
		$name = $report->summaryCaptionName($st);
		$topKind = ($span === 'ytd') ? 'year-to-date ' : (($span === 'mtd') ? 'month-to-date ' : 'annual ');
		echo "<div id='rank-$st' class='rank-tab'>";
		foreach (array(
			'today' => date('Y', $curYearTs) . ' (so far)',
			'yest' => date('Y', $lastYearTs) . ($samePeriod ? ' (same period)' : ''),
		) as $k => $lbl) {
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
