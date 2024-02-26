<?php
require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php
	$file = 40;
	$showYear = true;
	$isDaily = true;
	$linkToOther = 'TablesDataMonth';
	$needValcolStyle = true;
	$datgenHeading = 'Daily Data Tables';
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

	<title>NW3 Weather - Annual Data Reports</title>

	<meta name="description" content="Detailed historical annual data reports with monthly summary." />

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
$badCats = array('cloud','raina','sunhra','wethra');
require('wxdatagen.php');

$en_mon = isset($_GET['endmon']) ? $_GET['endmon'] : 12;
$endyr = $year;
$num = isset($_GET['num']) ? $_GET['num'] - 1 : 11;
$st_mon = 1; //date('n', mkdate($en_mon - $num, 1, $endyr));
$styr = $endyr;

$valid = true;
$key = $types_all[$type];
$_GET['vartype'.$key] = $types_alltogether[$key];
//echo 'Key: '. $key;
$data = array();
while($valid) {
	for($i = 0; $i < count($types_all); $i++) {
		if(isset($_GET['vartype'.$i])) {
			$dextra[$i] = $_GET['vartype'.$i];
			$arrnum[$i] = $types_all[$dextra[$i]];

			//Collect data
			$allDat = getDailyDataForYear($dextra[$i], $year);
			$data[$i] = array_merge($allDat);
			if( $dextra[$i] == 'rain') {
				$lhm[2] = 'Total';
			}
			if( in_array( $dextra[$i], array('tc10min','tchrmin','hchrmin') ) ) {
				$absfix[$i] = true;
			}
			$extra++;
		}
		$catcher++;
	}
	if($extra == 0) { // Non-multi mode
		$key = $types_all[$type];
		$_GET['vartype'.$key] = $types_alltogether[$key];
	} else {
		$valid = false;
	}
	if($catcher > 5) {
		$valid = false;
	}
}

//if($me) {
//	var_dump($allDat);
//	var_dump($data);
//}

$dextra = array_merge($dextra);
$arrnum = array_merge($arrnum);
$varNum = count($dextra);

table();
tr();
td('Day', 'td4C', 4, 1, 1 + (int)($extra > 1));
for($m = $st_mon - 1; $m <= $st_mon - 1 + $num; $m++) {
	$m_true = $m + 1;
	$monthsTbl[$m] = date('n', mkdate($m_true, 1, $styr));
	$maxdays[$m] = get_days_in_month($monthsTbl[$m], $endyr);
	$mon = $months3[$monthsTbl[$m]-1];
	$lnk = "/wxhistmonth.php?year=$year&month=$m_true";
	$linked_mon = ($year < $yr_yest || $m_true <= $mon_yest) ? '<a class="hidden-link" href="'. $lnk .'" title="View detailed report for month">'. $mon .'</a>' : $mon;
	td( $linked_mon, 'td4C', 8, $varNum );
}
tr_end();

if($extra > 1) {
	tr();
	// td('Vrbl', 'td4C', 4);
	for($m = $st_mon - 1; $m <= $st_mon - 1 + $num; $m++) {
		for($i = 0; $i < $varNum; $i++) {
			td( acronym( $dextra[$i], 'V'.($i+1), true ), 'td4C', floor(96/($num+1)/$varNum) );
		}
	}
	tr_end();
}

$cumuls = $cumcnts = array();
for($day = 1; $day <= 31; $day++) {
	tr(null);
	td( $day, 'row'.colcol($day).'" style="text-align:center' );
	for($m = $st_mon - 1; $m <= $st_mon - 1 + $num; $m++) {
		$m_true = $m + 1;
		$lnk = "/wxhistday.php?year=$year&month=$m_true&day=$day";
		$show_link = false;
		for($i = 0; $i < $varNum; $i++) {
			//defaults
			$rconv = false;
			$class = 'reportday';
			$finalVal = '-';
			if( $maxdays[$m] < $day) { //no such day (e.g. 30th Feb)
				$class = 'noday';
				$finalVal = '&nbsp;';
			} elseif( $endyr == $dyear && mkdate($m_true, $day, $styr) > mkdate($dmonth,$dday, $dyear) ) {
				//default for day not arrived (e.g. 7th April 2045)
			} elseif( isBlank($data[ $arrnum[$i] ][$m][$day]) ) {
//				echo $data[ $arrnum[$i] ][$m][$day], ',';
				$class = 'invalid';
				$finalVal = '&nbsp;';
				$show_link = true;
			}
			else {
				$rconv = $typeconvs_all[$arrnum[$i]];
				$put = $data[ $arrnum[$i] ][$m][$day];

				$cumuls[$m] += $put;
				$cumcnts[$m]++;

				$finalVal = conv( $put, $rconv, false );
				$valcolval = $valcolConvert ? $finalVal : $put;
				$class = valcolr( $valcolval, $wxtablecols_all[$arrnum[$i]] );
				$show_link = true;
			}
			$finalValLinked = $show_link ? '<a class="hidden-link" href="'. $lnk .'" title="View detailed report for day">'. $finalVal .'</a>' : $finalVal;
			td($finalValLinked, $class );
		}
	}
	tr_end();
}

tr();
td('', 'td4C" style="border-top:10px solid white', 4, 1, 1 + (int)($extra > 1));
for($m = $st_mon - 1; $m <= $st_mon - 1 + $num; $m++) {
	$monthsTbl[$m] = date('n', mkdate($m + 1, 1, $styr));
	td( $months3[$monthsTbl[$m]-1], 'td4C" style="border-top:10px solid white', 8, $varNum );
}
tr_end();

$limitForSummaryLoop = $isNotSummarisable ? 0 : 3;
for($mm = (int)$isSum; $mm < $limitForSummaryLoop; $mm++) {
	tr(null);
	td( $lhm[$mm], 'reportttl" style="padding:4px' );

	for($m = $st_mon - 1; $m <= $st_mon - 1 + $num; $m++) {
		for($i = 0; $i < $varNum; $i++) {
			if($mm == 0 && is_array($data[ $arrnum[$i] ][$m])) {
				$data[ $arrnum[$i] ][$m] = array_filter($data[ $arrnum[$i] ][$m], 'clearblank');
			}
			if(count($data[ $arrnum[$i] ][$m]) == 0) {
				$putConv = '---';
				$rconv = false;
				$class = 'reportday';
				$anom = '-';
			}
			else {
				$put = mom($data[ $arrnum[$i] ][$m], $mm);
				$rconv = $typeconvs_all[$arrnum[$i]];

				if($sumq_all[$arrnum[$i]] && $mm == 2) { //convert mean to sum and amend valuecolour setting
					$put *= count($data[ $arrnum[$i] ][$m]);
					$sumfix = $valcolSumOffset;
				} else {
					$sumfix = 1;
				}

				$putConv = conv($put, $rconv, false );
				$class = valcolr( $putConv / $sumfix, $wxtablecols_all[$arrnum[$i]] );

				$anom = ($anomq_all[$arrnum[$i]] && $mm == 2) ?
					'<br />' . '('. anomMonth($put, $arrnum[$i], $m) .')' : "";

			}
			td( $putConv . $anom, $class . '" style="padding:4px;font-weight:bold' );
		}
	}
	tr_end();
}
if($isSum) { //show count of values above zero
	tr(null);
	td( 'Count', 'reportttl" style="padding:4px' );
	for($m = $st_mon - 1; $m <= $st_mon - 1 + $num; $m++) {
		for($i = 0; $i < $varNum; $i++) {
			if(count($data[ $arrnum[$i] ][$m]) === 0) {
				$put = '---';
				$class = 'reportday';
			}
			else {
				$put = sum_cond($data[ $arrnum[$i] ][$m], true, 0);
				$class = valcolr( $put, $wxtablecols_all[$arrnum[$i]], true );
			}
			td( $put, $class . '" style="padding:4px;font-weight:bold' );
		}
	}
	tr_end();

	$meanTotalCount = ', total, count (values > 0)';
} else {
	$meanTotalCount = ', mean';
}
if(!$isNotSummarisable) {
	$cumExt = $isAnom ? '-<br />lative' : 'l';
	tr(null);
	td( 'Cumu'.$cumExt, 'reportttl" style="padding:4px' );
	for($m = $st_mon - 1; $m <= $st_mon - 1 + $num; $m++) {
		for($i = 0; $i < $varNum; $i++) {
			if($cumcnts[$m] == 0) {
				$putConv = '---';
				$rconv = false;
				$class = 'reportday';
				$anom = '';
			}
			else {
				$put = $putCnt = 0;
				for ($c = 0; $c <= $m; $c++) {
					$put += $cumuls[$c];
					$putCnt += $cumcnts[$c];
				}
				if(!$isSum) {
					$put /= $putCnt;
					$sumfix = 1;
				} else {
					$sumfix = $valcolSumOffset * ($m+1);
				}
				$rconv = $typeconvs_all[$arrnum[$i]];
				$putConv = conv($put, $rconv, false );
				$class = valcolr( $putConv / $sumfix, $wxtablecols_all[$arrnum[$i]] );
				$anom = ($isAnom) ? '<br />('. anomMonthCum($put, $arrnum[$i], $m) .')' : '';
			}

			td( $putConv . $anom, $class . '" style="padding:4px;font-weight:bold' );
		}
	}
	tr_end();
}

table_end();

function anomMonth($value, $type, $month) {
	global $sumq_all, $vars, $typeconvs_all, $maptoClimavs;

	if($sumq_all[$type]) {
		return percent( $value, $vars[ $maptoClimavs[$type] ][$month], 0, true, false );
	}
	$ctype = ($typeconvs_all[$type] == 1) ? 1.1 : $typeconvs_all[$type];
	return conv($value - $vars[ $maptoClimavs[$type] ][$month], $ctype, false, true);
}
function anomMonthCum($value, $type, $month) {
	global $sumq_all, $vars, $typeconvs_all, $maptoClimavs;

	$anomCum = 0;
	for ($i = 0; $i <= $month; $i++) {
		$anomCum += $vars[ $maptoClimavs[$type] ][$i];
	}

	if($sumq_all[$type]) {
		return percent( $value, $anomCum, 0, true, false );
	}

	$anomCum /= ($month+1);
	$ctype = ($typeconvs_all[$type] == 1) ? 1.1 : $typeconvs_all[$type];
	return conv($value - $anomCum, $ctype, false, true);
}

$descripDeets = $isNotSummarisable ? '.' : ' along with monthly summary:
	lowest, highest'.$meanTotalCount.', and the cumulative value for the year to the month\'s end.';
echo '<p>';
echo $description .' in London, nw3, for every available day of '. $year . $descripDeets;
if($isAnom) {
	echo '<br />Figures in brackets refer to departure from <strong>recent</strong> 
		<a href="wxaverages.php" title="Long-term NW3 climate averages">average conditions</a>';
	if($endyr == $dyear) {
		echo " (note that the anomaly for the current month is unadjusted for the month's degree of completeness)";
	}
	echo '.';
}
if($endyr == $dyear) {
	echo "<br />
		Values for recent days are subject to quality control and may be adjusted at any time.";
}
echo '</p>';

historical_info($year);

?>
</div>

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>

</body>
</html>