<?php
/**
 * Shared renderer for ranked daily data (RankDay / rankdaydata).
 */

/**
 * @param Report $report
 * @return array meta for AJAX clients
 */
function nw3_rankday_render(Report $report) {
	$t0 = microtime(true);
	$type = $report->type;
	$month = $report->month;
	$startY = max((int)$report->startYrReport, (int)$report->startYear);

	$ranked = [];
	foreach (Data::getDailyData($type, $startY) as $y => $months) {
		foreach ($months as $m => $daily) {
			if ($month == 0 || $m == $month) {
				foreach ($daily as $d => $v) {
					if (!Util::isBlank($v)) {
						$ranked[] = [(float)$v, Date::mkdate($m, $d, $y)];
					}
				}
			}
		}
	}
	sort($ranked);
	$sortLen = count($ranked);

	$fmat = isset($_GET['withdayofweek']) ? 'D d M Y' : 'd M Y';
	$yearSecs = 86400 * 365;

	$fmtDay = function ($ts) use ($fmat, $yearSecs) {
		$s = date($fmat, $ts) . (date('Y', $ts) < 2009 ? '*' : '');
		if ((Date::$dtstamp - $ts) < $yearSecs) { $s = "<b>$s</b>"; }
		return $s;
	};

	$highest = $lowest = $highestDay = $lowestDay = [];
	$hiKeys = $loKeys = [];
	$dayKey = function ($ts) { return date('Y-m-d', $ts); };
	$limit = min($sortLen, $report->rankLimit);
	for ($i = 1; $i <= $limit; $i++) {
		$highest[$i] = $ranked[$sortLen - $i][0];
		$highestDay[$i] = $fmtDay($ranked[$sortLen - $i][1]);
		$hiKeys[$i] = $dayKey($ranked[$sortLen - $i][1]);
		$lowest[$i] = $ranked[$i - 1][0];
		$lowestDay[$i] = $fmtDay($ranked[$i - 1][1]);
		$loKeys[$i] = $dayKey($ranked[$i - 1][1]);
	}

	// Current-day rank (today is partial: labelled "so far"). Manual variables
	// have no live today value, so only yesterday is ranked for them.
	$keys = array(
		'today' => $report->hasToday ? $dayKey(Date::$dtstamp) : null,
		'yest' => $dayKey(Date::$dtstamp_yest),
	);
	$cur = Report::currentRanks($ranked, $keys, $dayKey);
	$labels = array('todayLabel' => 'Today (so far)', 'yestLabel' => 'Yesterday');
	$ctxHi = Report::rankCtx($cur, $hiKeys, $keys, true, $highest, $labels);
	$ctxLo = Report::rankCtx($cur, $loKeys, $keys, false, $lowest, $labels);

	$footCond = ($month === 0 || $month == (int)Date::$dmonth);
	if ($footCond) {
		foreach (array('today' => 'Today (so far)', 'yest' => 'Yesterday') as $k => $lbl) {
			if (isset($cur[$k])) {
				echo Report::currentRankLine($lbl, $cur[$k]['hi'], $cur[$k]['lo'], $report->rankValueText($cur[$k]['v']));
			}
		}
	}
	echo '<div class="rk-pair">';
	$report->rankTable($highest, $highestDay, $limit, 'Highest', true, $report->hasToday, $footCond, true, false, 1, null, $ctxHi);
	$report->rankTable($lowest, $lowestDay, $limit, 'Lowest', false, $report->hasToday, $footCond, true, false, 1, null, $ctxLo);
	echo '</div>';

	$extraMon = ($month === 0) ? '' : ' in ' . Date::$months3[$month - 1];
	$extraCount = '';
	if ($report->isSum) {
		$c = 0;
		foreach ($ranked as $v) { if ($v[0] > 0) { $c++; } }
		$extraCount = ", and $c values > 0";
	}

	echo '<p class="rk-blurb">Ranked daily data from ' . $startY . ' to present for London, NW3. For '
		. $report->description . ', there are <b>' . $sortLen . '</b> valid values' . $extraMon . $extraCount
		. '. Data for ' . $report->description . ' begins in ' . (int)$report->startYear . '.</p>';

	$report->historicalInfo($startY);
	$report->echoVarAbout();
	$report->logRankRuntime($t0, 'day');

	$unitLabel = Wx::getUnitsText($report->unit);
	return array(
		'type' => $type,
		'month' => (int)$month,
		'startYearRep' => (int)$report->startYrReport,
		'startYearOptions' => array_map('intval', $report->startYearOptions),
		'rankLimit' => (int)$report->rankLimit,
		'title' => $report->description . ' / ' . $unitLabel,
	);
}
