<?php
/**
 * Shared renderer for ranked spell lengths (RankSpells / rankspellsdata).
 */

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
	$report->spellRankTable($spells, 'Longest ' . $dirLabel);

	if (is_array($current) && !empty($current['length'])) {
		$n = (int)$current['length'];
		$since = strtotime($current['startDate']);
		$sinceLbl = $since ? date('j M Y', $since) : $current['startDate'];
		echo '<p class="rk-blurb"><b>Current matching spell:</b> '
			. $n . ' day' . ($n === 1 ? '' : 's')
			. ' (since ' . htmlspecialchars($sinceLbl) . ')</p>';
	}

	$extraMon = ($month === 0) ? '' : ' with midpoint in ' . Date::$months3[$month - 1];
	$rule = Spells::ruleSymbol($type, $dir, $threshold);
	$threshTxt = Wx::plainText(Wx::conv($threshold, $report->unit, true));

	echo '<p class="rk-blurb">Longest consecutive-day spells from ' . $effStart
		. ' to present for London, NW3. Rule: ' . htmlspecialchars($report->description)
		. ' ' . $rule . ' ' . htmlspecialchars($threshTxt)
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
		'threshold' => $threshold,
		'thresholds' => $report->spellThresholds,
		'title' => $report->description . ' · ' . $dirLabel,
	);
}
