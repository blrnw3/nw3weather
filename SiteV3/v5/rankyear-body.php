<?php
/**
 * Shared renderer for ranked annual data (RankYear / rankyeardata).
 */

/**
 * @param Report $report
 * @return array meta for AJAX clients
 */
function nw3_rankyear_render(Report $report) {
	$type = $report->type;
	$startY = max((int)$report->startYrReport, (int)$report->startYear);
	$endY = (int)Date::$dyear;

	$yearSecs = 86400 * 365;
	$curYearTs = Date::mkdate(1, 1, (int)Date::$dyear);
	$lastYearTs = Date::mkdate(1, 1, (int)Date::$dyear - 1);

	$explain = [
		Data::SUMMARY_MEAN => 'highest and lowest annual means',
		Data::SUMMARY_SUM => 'highest and lowest annual totals',
		Data::SUMMARY_COUNT => 'most and fewest days with a non-zero value',
		Data::SUMMARY_MIN => 'highest and lowest annual minima',
		Data::SUMMARY_MAX => 'highest and lowest annual maxima',
	];

	foreach ($report->availSummaryTypes as $st) {
		$ranked = [];
		foreach (Data::getAnnualData($type, $st, $startY, $endY) as $y => $v) {
			if (!Util::isBlank($v)) {
				$ranked[] = [(float)$v, Date::mkdate(1, 1, $y)];
			}
		}
		sort($ranked);
		$sortLen = count($ranked);
		$limit = $sortLen;

		$highest = $lowest = $highestDay = $lowestDay = [];
		$fmtYr = function ($ts) use ($yearSecs) {
			$y = (int)date('Y', $ts);
			$s = (string)$y . ($y < 2009 ? '*' : '');
			if ((Date::$dtstamp - $ts) < $yearSecs) { $s = "<b>$s</b>"; }
			return $s;
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
		$isCount = ($st === Data::SUMMARY_COUNT);
		$name = Data::$SUMMARY_NAMES[$st];
		$hide = ($st === $report->summaryType) ? '' : " style='display:none'";
		echo "<div id='rank-$st' class='rank-tab'$hide>";
		echo '<div class="rk-pair">';
		$report->rankTable($highest, $highestDay, $limit, 'Top annual ' . $name, true, true, true, 'annual', $isCount, $sumOff);
		$report->rankTable($lowest, $lowestDay, $limit, 'Bottom annual ' . $name, false, true, true, 'annual', $isCount, $sumOff);
		echo '</div>';
		echo '<p class="rk-blurb">' . $report->description . ' annual extremes: ' . $explain[$st] . '.<br />'
			. 'There are <b>' . $sortLen . '</b> valid years for the chosen period from ' . $startY
			. ' to present in London, NW3. Data for ' . $report->description
			. ' begins in ' . (int)$report->startYear . '.</p>';
		echo '</div>';
	}

	$report->historicalInfo($startY);

	$unitLabel = strip_tags(Wx::getUnits($report->unit));
	return array(
		'type' => $type,
		'startYearRep' => (int)$report->startYrReport,
		'summaryType' => (int)$report->summaryType,
		'summaryTypes' => $report->availSummaryTypes,
		'rankLimit' => (int)$report->rankLimit,
		'title' => $report->description . ' / ' . $unitLabel,
	);
}
