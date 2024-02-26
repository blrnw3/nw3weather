<?php
	require('unit-select.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php
	$file = 41;
	$isDaily = true;
	$showMonth = true;
	$showStartYearSelect = true;
	$linkToOther = 'RankMonth';
	$needValcolStyle = true;
	$datgenHeading = 'Historical Ranked Daily Data';
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

	<title>NW3 Weather - Ranked Daily Data</title>

	<meta name="description" content="Detailed historical data reports of ranked daily values - temperature, rainfall, wind speeds and more" />

	<?php require('chead.php'); ?>
	<?php include('ggltrack.php') ?>
</head>

<body>
	<?php require('header.php'); ?>
	<?php require('leftsidebar.php'); ?>

	<div id="main">

<?php
$badCats = array('cloud','raina','sunhra','wethra');
require('wxdatagen.php');

function getDailyRanked($varName, $month, $startY) {
	$ranked = [];
	foreach (getDailyData($varName, $startY) as $y => $months) {
		foreach ($months as $m => $daily) {
			if($month == 0 || $m == $month) {
				foreach ($daily as $d => $v) {
					if(!isBlank($v)) {
						$ranked[] = [$v, mkdate($m, $d, $y)];
					}
				}
			}
		}
	}
	sort($ranked);
	return $ranked;
}

$ranked = getDailyRanked($type, $month, $startYrReport);
$sortLen = count($ranked);

$fmat = isset($_GET['withdayofweek']) ? 'D d M Y' : 'd M Y';
$year_secs = 86400 * 365;

$limit = ($sortLen < $rankLimit) ? $sortLen : $rankLimit;
for ($i = 1; $i <= $limit; $i++) {
	$highest[$i] = $ranked[$sortLen - $i][0];
	$ts = $ranked[$sortLen - $i][1];
	$highest_day[$i] = today(true, true, true, null, $ts, false, $fmat) . ((date("Y", $ts) < 2009) ? "*" : "");
	if( ($dtstamp - $ts) < $year_secs ) { $highest_day[$i] = "<b>$highest_day[$i]</b>"; }
	$lowest[$i] = $ranked[$i - 1][0];
	$ts = $ranked[$i - 1][1];
	$lowest_day[$i] = today(true, true, true, null, $ts, false, $fmat) . ((date("Y", $ts) < 2009) ? "*" : "");
	if( ($dtstamp - $ts) < $year_secs ) { $lowest_day[$i] = "<b>$lowest_day[$i]</b>"; }
}


// Ranking of today/yest
foreach( $ranked as $rnk => $val ) {
	if($val[1] === $dtstamp) {
		$highest['today'] = $lowest['today'] = $val[0];
		$lowest_day['today'] = $rnk + 1;
		$highest_day['today'] = $sortLen - $rnk;
	}
	if($val[1] === $dtstamp_yest) {
		$highest['yest'] = $lowest['yest'] = $val[0];
		$lowest_day['yest'] = $rnk + 1;
		$highest_day['yest'] = $sortLen - $rnk;
	}
}

$footCond = $month === 0 || $month == $dmonth;
rankTable($highest, $highest_day, $typeconvNum, $typevalcolNum, $valcolAbsfix, $limit, "Highest", true, $hasToday, $footCond);
rankTable($lowest, $lowest_day, $typeconvNum, $typevalcolNum, $valcolAbsfix, $limit, "Lowest", false, $hasToday, $footCond);

rankLimitForm();

if($month === 0) {
} else {
	$extraMon = ' in '. $months3[$month-1];
}
if($isSum) {
	$c = 0;
	foreach($ranked as $v) {
		if($v[0] > 0) { $c++; }
	}
	$extraCount = ", and $c values > 0";
}

echo "<p>Ranked daily data from $startYrReport to present for London, nw3. For $description, there are<b> $sortLen </b>valid values$extraMon$extraCount</p>";

historical_info($startYrReport);
?>
</div>

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>

</body>
</html>