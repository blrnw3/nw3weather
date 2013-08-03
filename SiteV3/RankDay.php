<?php
	$allDataNeeded = true;
	require('unit-select.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php 
	$file = 41;
	$isDaily = true;
	$showMonth = true;
	$showNum = true;
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
	<!-- ##### Header ##### -->
	<?php require('header.php'); ?>
	<!-- ##### Left Sidebar ##### -->
	<?php require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->
	<div id="main">

<?php
require('wxdatagen.php');

function allDataToDateForMonth($data, $month) {
	$monthData = array();
	foreach ($data as $key => $value) {
		if(date('n', daytotime($key)) == $month) {
			$monthData[$key] = $value;
		}
	}
	return $monthData;
}

$rankSize = $rankLimit;

//Data collection
$datall1 = newData($type, true);
$end = count($datall1);
$datall = ($month > 0) ? allDataToDateForMonth($datall1, $month) : $datall1;
$datallCnt = count($datall);

//Extreme Daily Ranked
$datGood = array_filter($datall, 'isNotBlank');
$data_sort = $datGood;
sort($data_sort, SORT_NUMERIC);
$sortLen = count($data_sort);

for ($i = 1; $i <= $rankSize; $i++) {
	$highest[$i] = $data_sort[$sortLen - $i];
	$dayRecH = array_search($highest[$i], $datGood);
	$highest_day[$i] = today(true, true, true, null, daytotime($dayRecH), false, 'd M Y');
	$datGood[$dayRecH] = -999; //prevent duplicated dates
	$lowest[$i] = $data_sort[$i - 1];
	$dayRecL = array_search($lowest[$i], $datGood);
	$lowest_day[$i] = today(true, true, true, null, daytotime($dayRecL), false, 'd M Y');
	$datGood[$dayRecL] = -999; //prevent duplicated dates

	if($dayRecH === false || $dayRecL === false) { //all values from array put into high or low list, do not continue
		$rankSize = $i-1;
	}
}

$highest['today'] = $lowest['today'] = $datall[$end-1];
$highest['yest'] = $lowest['yest'] = $datall[$end-2];
$lowest_day['today'] = array_search($lowest['today'], $data_sort) + 1;
$lowest_day['yest'] = array_search($lowest['yest'], $data_sort) + 1;
$highest_day['today'] = $sortLen - $lowest_day['today'] + 1;
$highest_day['yest'] = $sortLen - $lowest_day['yest'] + 1;

$footCond = $month === 0 || $month == $dmonth;
rankTable($highest, $highest_day, $typeconvNum, $typevalcolNum, $valcolAbsfix, $rankSize, "Highest", true, $hasToday, $footCond);
rankTable($lowest, $lowest_day, $typeconvNum, $typevalcolNum, $valcolAbsfix, $rankSize, "Lowest", false, $hasToday, $footCond);

if($month === 0) {
	$stMon = 'Jan';
} else {
	$stMon = $months3[$month-1];
	$extraMon = ' in '. $stMon;
}

echo "<p>Ranked daily data from $stMon 2009 to present. For $description, there are<b> $sortLen </b>valid values from a possible $datallCnt$extraMon.</p>";


?>
</div>

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>

</body>
</html>