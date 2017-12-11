<?php
$allDataNeeded = true;
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
			if( $arrnum[$i] < count($types) ) {
				if($styr != $endyr) {
					$data[$i] = array_merge($DATA[ $arrnum[$i] ][$styr], $DATA[ $arrnum[$i] ][$endyr]);
				} else {
					$data[$i] = array_merge($DATA[ $arrnum[$i] ][$endyr]);
				}

			}
			elseif( $arrnum[$i] < count($types) + count($types_derived) ) {
				if($styr != $endyr) {
					$data[$i] = array_merge($DATX[ $arrnum[$i] - count($types) ][$styr], $DATX[ $arrnum[$i] - count($types) ][$endyr]);
				} else {
					$data[$i] = array_merge($DATX[ $arrnum[$i] - count($types) ][$endyr]);
				}
			}
			else {
				if($styr != $endyr) {
					$data[$i] = array_merge($DATM[ $arrnum[$i] - count($types_all) + count($types_m) ][$styr],
						$DATM[ $arrnum[$i] - count($types_all) + count($types_m) ][$endyr]);
				} else {
					$data[$i] = array_merge($DATM[ $arrnum[$i] - count($types_all) + count($types_m) ][$endyr]);
				}
				$manual[$i] = true;
				$lhm[2] = 'Total';
				$adj[$i] = 1;
			}
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
	if($extra == 0) {
		$key = $types_all[$type];
		$_GET['vartype'.$key] = $types_alltogether[$key];
	} else {
		$valid = false;
	}
	if($catcher > 50) {
		$valid = false;
	}
}
//print_m($arrnum);
//print_m($dextra);
//print_m($data);
?>

<!-- <form method="get" action="">
<table width="95%" cellpadding="5"><tr><td>
	<b>Graph Type: </b>
	<?php
	/*
	unset($nums);
	$nums = array(1,2,3,4,5,6,8,10,12,18,24);
// 	$types_alltogether = array_merge(array_flip($types_alltogether));
	if($gtype == 'y') { $chosen = ' checked="checked"'; } elseif($gtype == 'y2') { $chosen2 = ' checked="checked"'; } else { $chosen1 = ' checked="checked"'; }
	echo '<input type="radio" name="altg" value="yA"', $chosen1, ' /> Autoscale (choose data types below) &nbsp;
		<input type="radio" name="altg" value="y"', $chosen, ' /> Temp/Hum/Dew/Rain &nbsp;
		<input type="radio" name="altg" value="y2"', $chosen2, ' /> Wind/Gust/Baro
		</td></tr> <tr><td> <b>Autoscale Data Types: </b>';
	for($t = 0; $t < count($types_alltogether); $t++) {
// 		$find = array(' (10-min)', 'Direction', 'Wind Speed'); $repl = array('','Drctn','Speed');
// 		$tvargv = explode('/',$daynames[$types_alltogether[$t-1]]); $datnam = str_replace($find,$repl,$tvargv[0]);
		if(($types_alltogether[$t] == $dextra[$t]
// 				|| (!$more && $t == 1)) && $gtype == 'yA'
				)) {
			$checked = 'checked="checked" ';
		 } else { $checked = ''; }
		echo '<input type="checkbox" name="vartype',$t,'" value="', $types_alltogether[$t], '" ', $checked, '/> ', $types_alltogether[$t], ' &nbsp;
			';
	}
	echo '</td></tr><tr><td> <b>Months to show </b><select name="num">';
	for($n = 0; $n < count($nums); $n++) {
		if($num+1 == $nums[$n]) { $selected = 'selected="selected"'; } else { $selected = ''; }
		echo '<option value="', $nums[$n], '" ', $selected, '>', $nums[$n], '</option>
			';
	}
	echo '</select> &nbsp; &nbsp; <b>End Date </b>
		<select name="endyr">';
	for($y = 2009; $y <= $dyear; $y++) {
		if($y == $endyr) { $selected = 'selected="selected"'; } else { $selected = ''; }
		echo '<option value="', $y, '" ', $selected, '>', $y, '</option>
			';
	}
	echo '</select>
		<select name="endmon">';
	for($m = 1; $m <= 12; $m++) {
		if($m == $en_mon) { $selected = 'selected="selected"'; } else { $selected = ''; }
		echo '<option value="', $m, '" ', $selected, '>', $months[$m-1], '</option>
			';
	}
	echo '</select>
		 &nbsp;';
	 *
	 */
	?>
	<input type="submit" value="Generate Table" /> &nbsp;
	<a href="wxdatadayC.php" title="Reset all parameters to default"> <b>Reset</b> </a>
</td></tr></table>
</form>  -->

<?php

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

// if($varNum > 1) {
// 	tr();
// 	td('Measure', 'td4C');
// 	for($m = $st_mon - 1; $m <= $st_mon - 1 + $num; $m++) {
// 		for($i = 0; $i < $varNum; $i++) {
// 			td($dextra[$i], 'td4C');
// 		}
// 	}
// 	tr_end();
// }

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
			} elseif( $endyr == $dyear && mkdate($m + 1, $day, $styr) > mkdate($dmonth,$dday-$adj[$arrnum[$i]]) ) {
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
echo $description .' for every available day of '. $year . $descripDeets;
if($isAnom) {
	echo '<br />Figures in brackets refer to departure from
		<a href="wxaverages.php" title="Long-term NW3 climate averages">average conditions</a>';
	if($endyr == $dyear) {
		echo " (note that the anomaly for the current month is unadjusted for the month's degree of completeness)";
	}
	echo '.';
}
if($endyr == $dyear) {
	echo "<br />
		Values for recent days are subject to quality control and may be adjusted at any time.";
	if(!$hasToday) {
		echo '<br />Values for the current day normally become available on the following Sunday, when manual observations for the week are input.';
	}
}
echo '</p>';

?>
</div>

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>

</body>
</html>