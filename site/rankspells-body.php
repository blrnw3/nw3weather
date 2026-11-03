<?php
/**
 * Shared renderer for ranked spell lengths (RankSpells / rankspellsdata).
 */

/**
 * Status line for heatwave / cold snap rankings: in progress or not.
 * @param Report $report
 * @param string $dir heatwave|coldsnap
 * @param float $x anomaly threshold (°C)
 * @param array|null $current ongoing spell from Spells::rankLongest
 * @return string HTML
 */
function nw3_spell_anom_status(Report $report, $dir, $x, $current) {
	$hot = Spells::isHotDir($dir);
	$noun = $hot ? 'Heatwave' : 'Cold snap';
	$rule = htmlspecialchars(Spells::anomalyRule($report->type, $dir, $x));
	$cls = 'rk-status ' . ($hot ? 'rk-status-hot' : 'rk-status-cold');
	if (is_array($current) && !empty($current['length'])) {
		$n = (int)$current['length'];
		$since = strtotime($current['startDate']);
		$sinceLbl = $since ? date('d M', $since) : $current['startDate'];
		$extra = !empty($current['endsYesterday']) ? '; today not yet qualifying' : '';
		return '<p class="' . $cls . ' rk-status-on"><b>' . $noun . ' in progress:</b> '
			. $n . ' day' . ($n === 1 ? '' : 's') . ' so far, since ' . htmlspecialchars($sinceLbl)
			. ' (mean ' . htmlspecialchars(Spells::anomText($current['meanAnom']))
			. ', peak ' . htmlspecialchars(Spells::anomText($current['peakAnom'])) . $extra . '). '
			. 'Rule: ' . $rule . '.</p>';
	}
	return '<p class="rk-status rk-status-off">No ' . strtolower($noun) . ' in progress (' . $rule . ').</p>';
}

/**
 * @param Report $report
 * @return array meta for AJAX clients
 */
function nw3_rankspells_render(Report $report) {
	$t0 = microtime(true);
	$type = $report->type;
	$month = $report->month;
	$startY = max((int)$report->startYrReport, (int)$report->startYear);
	$dir = $report->spellDir;
	$threshold = $report->spellThreshold;
	$limit = (int)$report->rankLimit;

	$result = Spells::rankLongest($type, $threshold, $dir, $startY, $limit, $month);
	$spells = $result['spells'];
	$total = (int)$result['total'];
	$current = $result['current'];
	$effStart = (int)$result['startYear'];

	$dirLabel = Spells::directionLabel($type, $dir, $threshold);
	$isAnom = Spells::isAnomalyDir($dir);
	if ($isAnom) {
		echo nw3_spell_anom_status($report, $dir, $threshold, $current);
	}
	$report->spellRankTable($spells, 'Longest ' . $dirLabel, $isAnom);

	if (!$isAnom && is_array($current) && !empty($current['length'])) {
		$n = (int)$current['length'];
		$since = strtotime($current['startDate']);
		$sinceLbl = $since ? date('d M Y', $since) : $current['startDate'];
		echo '<p class="rk-blurb"><b>Current matching spell:</b> '
			. $n . ' day' . ($n === 1 ? '' : 's')
			. ' (since ' . htmlspecialchars($sinceLbl) . ')</p>';
	}

	$extraMon = ($month === 0) ? '' : ' with midpoint in ' . Date::$months3[$month - 1];
	if ($isAnom) {
		$ruleTxt = htmlspecialchars(Spells::anomalyRule($type, $dir, $threshold))
			. ' on every day, where normal is that calendar day\'s 1991-2020 average. '
			. 'Anomaly column: mean (and peak) difference from normal over the spell'
			. (Spells::isBothDir($dir) ? ', using the smaller of each day\'s max and min anomalies' : '');
	} else {
		$rule = Spells::ruleSymbol($type, $dir, $threshold);
		$threshTxt = Wx::plainText(Wx::conv($threshold, $report->unit, true));
		$ruleTxt = htmlspecialchars($report->description) . ' ' . $rule . ' ' . htmlspecialchars($threshTxt);
	}

	echo '<p class="rk-blurb">Longest consecutive-day spells from ' . $effStart
		. ' to present for London, NW3. Rule: ' . $ruleTxt
		. '.<br />Found <b>' . $total . '</b> matching spell'
		. ($total === 1 ? '' : 's') . $extraMon
		. '. Showing top ' . min($limit, $total)
		. '. Data for ' . htmlspecialchars($report->description)
		. ' begins in ' . (int)$report->startYear . '.</p>';

	$report->historicalInfo($effStart);
	$report->echoVarAbout();
	$report->logRankRuntime($t0, 'spells');

	return array(
		'type' => $type,
		'month' => (int)$month,
		'startYearRep' => (int)$report->startYrReport,
		'startYearOptions' => array_map('intval', $report->startYearOptions),
		'rankLimit' => (int)$report->rankLimit,
		'spellDir' => $dir,
		'spellDirLabels' => Spells::directionChipLabels($type),
		'current' => $current,
		'threshold' => $threshold,
		'thresholds' => $report->spellThresholds,
		'thresholdLabels' => $report->thresholdLabels(),
		'title' => $report->description . ' · ' . $dirLabel,
	);
}
