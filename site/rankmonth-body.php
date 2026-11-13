<?php
/**
 * Shared renderer for ranked monthly data (RankMonth / rankmonthdata).
 */

/**
 * @param Report $report
 * @return array meta for AJAX clients
 */
function nw3_rankmonth_render(Report $report) {
	$t0 = microtime(true);
	$type = $report->type;
	$report->normalizeSpanFor('rank-monthly');
	$month = $report->month;
	$span = $report->span;
	$startY = max((int)$report->startYrReport, (int)$report->startYear);
	$endY = (int)Date::$dyear;
	$threshold = $report->spellThreshold;
	list($endM, $endD) = Data::spanEndMd();

	$isHalf = Data::isHalfSpan($span);
	$footCond = ($month === 0 || $month == (int)Date::$dmonth);
	$yearSecs = 86400 * 365;
	$curMonthTs = Date::mkdate((int)Date::$dmonth, 1, (int)Date::$dyear);
	$lastMonthTs = Date::mkdate((int)Date::$dmonth - 1, 1, (int)Date::$dyear);
	$curYearTs = Date::mkdate(1, 1, (int)Date::$dyear);
	$lastYearTs = Date::mkdate(1, 1, (int)Date::$dyear - 1);

	$explain = [
		Data::SUMMARY_MEAN => 'highest and lowest monthly means',
		Data::SUMMARY_SUM => 'highest and lowest monthly totals',
		Data::SUMMARY_COUNT => 'most and fewest days with a non-zero value',
		Data::SUMMARY_MIN => 'highest and lowest monthly minima',
		Data::SUMMARY_MAX => 'highest and lowest monthly maxima',
		Data::SUMMARY_RANGE => 'highest and lowest monthly ranges (daily max − min)',
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
		$sumSpan = ($span === 'mtd' || $span === 'h1' || $span === 'h2') ? $span : 'full';
		foreach (Data::getMonthlySummary($type, $st, $startY, $endY, $th, $sumSpan) as $y => $months) {
			foreach ($months as $m => $v) {
				if (($month === 0 || $m === $month) && !Util::isBlank($v)) {
					if ($span === 'mtd' && Data::dateIsFuture($y, $m, 1)) { continue; }
					if ($isHalf && Data::halfBounds($span, $y, $m, true) === null) { continue; }
					$ranked[] = [(float)$v, Date::mkdate($m, 1, $y)];
				}
			}
		}
		sort($ranked);
		$sortLen = count($ranked);
		$limit = ($sortLen / 2 < $report->rankLimit) ? 1 + (int)($sortLen / 2) : $report->rankLimit;

		$highest = $lowest = $highestDay = $lowestDay = [];
		$fmtMon = function ($ts) use ($span, $endD, $type) {
			return Report::monthSpanDateHtml($ts, $span, (int)date('n', $ts), $endD, $type);
		};
		$keyFn = function ($ts) { return date('Y-m', $ts); };
		$hiKeys = $loKeys = [];
		for ($i = 1; $i <= $limit; $i++) {
			if ($sortLen < $i) { break; }
			$highest[$i] = $ranked[$sortLen - $i][0];
			$highestDay[$i] = $fmtMon($ranked[$sortLen - $i][1]);
			$hiKeys[$i] = $keyFn($ranked[$sortLen - $i][1]);
			$lowest[$i] = $ranked[$i - 1][0];
			$lowestDay[$i] = $fmtMon($ranked[$i - 1][1]);
			$loKeys[$i] = $keyFn($ranked[$i - 1][1]);
		}

		$keys = array('today' => date('Y-m', $curMonthTs), 'yest' => date('Y-m', $lastMonthTs));
		if ($span === 'mtd') {
			$labels = array('todayLabel' => 'Current month (so far)', 'yestLabel' => 'Last month (same period)');
			$curLine = array(
				'today' => date('M Y', $curMonthTs) . ' (so far)',
				'yest' => date('M Y', $lastMonthTs) . ' (same period)',
			);
		} elseif ($span === 'h1' || $span === 'h2') {
			$halfName = ($span === 'h1') ? '1st half' : '2nd half';
			$curHalfStarted = Data::halfBounds($span, (int)Date::$dyear, (int)Date::$dmonth, true) !== null;
			$labels = array(
				'todayLabel' => $curHalfStarted ? ('Current ' . $halfName . ' (so far)') : ('Current ' . $halfName),
				'yestLabel' => 'Last month (' . $halfName . ')',
			);
			$curLine = array();
			if ($curHalfStarted && ($month === 0 || $month == (int)Date::$dmonth)) {
				$curLine['today'] = date('M Y', $curMonthTs) . ' ' . $halfName . ' (so far)';
			}
			$curLine['yest'] = date('M Y', $lastMonthTs) . ' ' . $halfName;
		} else {
			$labels = array('todayLabel' => 'Current month (so far)', 'yestLabel' => 'Last month');
			$curLine = array(
				'today' => date('M Y', $curMonthTs) . ' (so far)',
				'yest' => date('M Y', $lastMonthTs),
			);
		}
		$cur = Report::currentRanks($ranked, $keys, $keyFn);
		$ctxHi = Report::rankCtx($cur, $hiKeys, $keys, true, $highest, $labels);
		$ctxLo = Report::rankCtx($cur, $loKeys, $keys, false, $lowest, $labels);

		$sumOff = ($st === Data::SUMMARY_SUM) ? $report->valcolSumOffset() : 1;
		$isCount = Data::isCountSummary($st);
		$displayUnit = $report->summaryDisplayUnit($st);
		$name = $report->summaryCaptionName($st);
		echo "<div id='rank-$st' class='rank-tab'>";
		if ($footCond) {
			foreach ($curLine as $k => $lbl) {
				if (isset($cur[$k])) {
					echo Report::currentRankLine($lbl, $cur[$k]['hi'], $cur[$k]['lo'],
						$report->rankValueText($cur[$k]['v'], $isCount, $displayUnit), 'highest', 'lowest');
				}
			}
		}
		if ($span === 'mtd') { $topKind = 'month-to-date '; }
		elseif ($span === 'h1') { $topKind = '1st-half '; }
		elseif ($span === 'h2') { $topKind = '2nd-half '; }
		else { $topKind = 'monthly '; }
		echo '<div class="rk-pair">';
		$report->rankTable($highest, $highestDay, $limit, 'Top ' . $topKind . $name, true, true, $footCond, false, $isCount, $sumOff, $displayUnit, $ctxHi);
		$report->rankTable($lowest, $lowestDay, $limit, 'Bottom ' . $topKind . $name, false, true, $footCond, false, $isCount, $sumOff, $displayUnit, $ctxLo);
		echo '</div>';
		$extraMon = ($month === 0) ? '' : ' for ' . Date::$months3[$month - 1];
		$extraTh = Data::isThresholdSummary($st) ? (' (' . $report->summaryThresholdPhrase($st) . ')') : '';
		$spanNote = $report->spanTitleBit();
		$spanNote = ($spanNote === '') ? '' : (' ' . $spanNote);
		$itemWord = 'months';
		echo '<p class="rk-blurb">' . $report->description . ' monthly extremes: ' . $explain[$st] . $extraTh . $spanNote . '.<br />'
			. 'There are <b>' . $sortLen . '</b> valid ' . $itemWord . ' for the chosen period from ' . $startY
			. ' to present' . $extraMon . ' in London, NW3. Data for ' . $report->description
			. ' begins in ' . (int)$report->startYear . '.</p>';
		echo '</div>';
	}

	$report->historicalInfo($startY);
	$report->echoVarAbout();
	$report->logRankRuntime($t0, 'month');

	return array(
		'type' => $type,
		'month' => (int)$month,
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
