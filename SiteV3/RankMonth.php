<?php
	$allDataNeeded = true;
	require('unit-select.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php
	$file = 41;
	$showMonth = true;
	$showNum = true;
	$linkToOther = 'RankDay';
	$needValcolStyle = true;
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

	<title>NW3 Weather - Ranked Monthly Data</title>

	<meta name="description" content="Detailed historical data reports of ranked monthly values - temperature, rainfall, wind speeds and more" />

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
$datgenHeading = 'Historical Ranked Monthly-mean Data';
require('wxdatagen.php');

function allDataToDateForMonth($data, $month) {
	$monthData = array();
	foreach ($data as $key => $value) {
		if(date('n', monthtotime($key)) == $month) {
			$monthData[$key] = $value;
		}
	}
	return $monthData;
}

$rankSize = $rankLimit;

$isCountable = $typeNum >= count($sumq_all) - 3;

//Data collection
$datall1 = newData($type, true, ($isCountable) ? 2.3 : 2.2);
$end = count($datall1);
$datall = ($month > 0) ? allDataToDateForMonth($datall1, $month) : $datall1;
$datallCnt = count($datall);

//Extreme Daily Ranked
$datGood = array_filter($datall, 'isNotBlank');
$len = count($datGood);
$data_sort = $datGood;
sort($data_sort, SORT_NUMERIC);
$sortLen = count($data_sort);

for ($i = 1; $i <= $rankSize; $i++) {
	$highest[$i] = $data_sort[$sortLen - $i];
	$dayRecH = array_search($highest[$i], $datGood);
	$stampH = monthtotime($dayRecH);
	$highest_day[$i] = today(date('Y', $stampH), date('n', $stampH), false, true);
	$datGood[$dayRecH] = -999; //prevent duplicated dates
	$lowest[$i] = $data_sort[$i - 1];
	$dayRecL = array_search($lowest[$i], $datGood);
	$stampL = monthtotime($dayRecL);
	$lowest_day[$i] = today(date('Y', $stampL), date('n', $stampL), false, true);
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

$footCond = $month === 0;
$sumfix = $isSum ? $valcolSumOffset : 1;
rankTable($highest, $highest_day, $typeconvNum, $typevalcolNum, $valcolAbsfix, $rankSize, "Highest monthly-$meanOrTotal[$isSum]", true, true, $footCond, false, $sumfix, $isCountable);
rankTable($lowest, $lowest_day, $typeconvNum, $typevalcolNum, $valcolAbsfix, $rankSize, "Lowest monthly-$meanOrTotal[$isSum]", false, true, $footCond, false, $sumfix);

if($month === 0) {
	$stMon = 'Jan';
} else {
	$stMon = $months3[$month-1];
	$extraMon = ' in '. $stMon;
}

echo "<p>Ranked monthly data from $stMon 2009 to present. For $description, there are<b> $sortLen </b>valid months from a possible $datallCnt$extraMon.</p>";


?>
</div>

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>

</body>
</html>