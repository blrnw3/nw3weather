<?php
	require('unit-select.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php
	$file = 42;
	$showMonth = true;
	$showStartYearSelect = true;
	$linkToOther = 'RankDay';
	$needValcolStyle = true;
	$SHOW_TABS = true;
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

	<title>NW3 Weather - Ranked Monthly Data</title>

	<meta name="description" content="Detailed historical data reports of ranked monthly values - temperature, rainfall, wind speeds and more" />

	<?php require('chead.php'); ?>
	<?php include('ggltrack.php') ?>
</head>

<body>
	<?php require('header.php'); ?>
	<?php require('leftsidebar.php'); ?>
	<div id="main">
<?php
$badCats = array('cloud');
$datgenHeading = 'Historical Ranked Monthly Data';
require('wxdatagen.php');

function getMonthlyRanked($varName, $summary_type, $month, $startY, $endY) {
	$ranked = [];
	foreach (getMonthlyData($varName, $summary_type, $startY, $endY) as $y => $months) {
		foreach ($months as $m => $v) {
			if($month === 0 || $m === $month) {
				$ranked[] = [$v, mkdate($m, 1, $y)];
			}
		}
	}
	sort($ranked);
	return $ranked;
}

$footCond = $month === 0 || $month == $dmonth;
$year_secs = 86400 * 365;

foreach( $AVAIL_SUMMARY_TYPES as $smry_type ) {
	$ranked = getMonthlyRanked($type, $smry_type, $month, $startYrReport, $dyear);
	$sortLen = count($ranked);

	$limit = ($sortLen / 2 < $rankLimit) ? 1 + (int)($sortLen / 2) : $rankLimit;
	for ($i = 1; $i <= $limit; $i++) {
		$highest[$i] = $ranked[$sortLen - $i][0];
		$ts = $ranked[$sortLen - $i][1]; $yr = date('Y', $ts);
		$highest_day[$i] = today($yr, date('n', $ts), false, true) . (($yr < 2009) ? "*" : "");
		if( ($dtstamp - $ts) < $year_secs ) { $highest_day[$i] = "<b>$highest_day[$i]</b>"; }
		$lowest[$i] = $ranked[$i - 1][0];
		$ts = $ranked[$i - 1][1]; $yr = date('Y', $ts);
		$lowest_day[$i] = today($yr, date('n', $ts), false, true) . (($yr < 2009) ? "*" : "");
		if( ($dtstamp - $ts) < $year_secs ) { $lowest_day[$i] = "<b>$lowest_day[$i]</b>"; }
	}

	// Ranking of curr/last month
	foreach( $ranked as $rnk => $val ) {
		if($val[1] === mkdate($dmonth, 1, $dyear)) {
			$highest['today'] = $lowest['today'] = $val[0];
			$lowest_day['today'] = $rnk + 1;
			$highest_day['today'] = $sortLen - $rnk;
		}
		if($val[1] === mkdate($dmonth-1, 1, $dyear)) {
			$highest['yest'] = $lowest['yest'] = $val[0];
			$lowest_day['yest'] = $rnk + 1;
			$highest_day['yest'] = $sortLen - $rnk;
		}
	}
	$sumfix = $smry_type === 1 ? $valcolSumOffset : 1;
	$extraMon = ($month === 0) ? "" : ' for '. $months3[$month-1];
	$hideTab = ($smry_type === $GET_SUMMARY_TYPE) ? "" : "style='display:none'";
	echo "<div id='rank-$smry_type' class='rank-tab' $hideTab>";
	rankTable($highest, $highest_day, $typeconvNum, $typevalcolNum, $valcolAbsfix, $limit, "Top monthly-$SUMMARY_NAMES[$smry_type]", true, true, $footCond, false, $sumfix, $smry_type === 2);
	rankTable($lowest, $lowest_day, $typeconvNum, $typevalcolNum, $valcolAbsfix, $limit, "Bottom monthly-$SUMMARY_NAMES[$smry_type]", false, true, $footCond, false, $sumfix, $smry_type === 2);
	echo "<p>$description monthly extremes: ${SUMMARY_EXPLAIN[$smry_type]}<br />";
	echo "For $description, there are<b> $sortLen </b>valid months for the chosen period from $startYrReport to present$extraMon in London, nw3.</p>";
	echo "</div>";
}
rankLimitForm();

historical_info($startYrReport);

?>
</div>

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>

</body>
</html>