<?php
require('unit-select.php');
// Obtains Server's Self and protect it against XSS injection
$SITE = array();
$SITE['self'] = htmlentities( substr($_SERVER['PHP_SELF'], 0, 
	strcspn( $_SERVER['PHP_SELF'] , "\n\r") ), ENT_QUOTES );
@include_once("wxreport-settings.php");
# Settings###################################################
$temprange_start = array(0, 10); # (Farenheit, Celcius) 
$temprange_increment = array(10, 10); # (Farenheit, Celcius)
$increments = 8;
$set_values_manually = true;
$manual_values = array(30,40,50,60,70,80,90,98);
$loc = $path_dailynoaa;
$first_year_of_data = $first_year_of_noaadata;
$uomTemp = '&deg;C';
$temptype = array("C","F") ;
$temptype = $temptype[$uomTemp == "&deg;F"];
$temprange_start = $temprange_start[((strtoupper($temptype)) != "F")] ;
$temprange_increment = $temprange_increment[((strtoupper($temptype)) != "F")] ;
$tempvalues = array($temprange_start);
for ( $i = 0; $i < $increments ; $i ++ ) {
	$tempvalues[$i+1] = $tempvalues[$i] + $temprange_increment;
}
if ($set_values_manually == true){
	$tempvalues = $manual_values;
	$increments = (count($manual_values))-1;
}
$colors = $increments + 1; 
$range = "year";
	
include('wxrepgen0.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("phptags.php");
	$file = 50.5; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - Annual Relative Humidity Reports</title>

	<meta name="description" content="Old v2 - Detailed historical annual relative humidity report for <?php echo $year; ?> with monthly breakdown from NW3 weather.
	Find most and least humid days and months, Daily detail for lowest and highest values, and monthly summary data." />
	
	<?php require('chead.php'); ?>
	<link rel="stylesheet" type="text/css" href="wxreports.css" media="screen" title="screen" />
	<?php include_once("ggltrack.php") ?> 
</head> 

<body>
	<!-- For non-visual user agents: -->
	<div id="top"><a href="#main-copy" class="doNotDisplay doNotPrint">Skip to main content.</a></div>

	<!-- ##### Header ##### -->	
	<?php require('header.php'); ?>
	
	<!-- ##### Left Sidebar ##### -->
	<?php require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->
<div id="main-copy">
	<div id="report">
		<h1>Relative Humidity Reports (%)</h1>

<?php $self = 'wxhist10-5.php';
	include("wxrepgen.php"); 

get_rh_detail($year,$loc, $range, $season_start, $round); ?>

	</div>
		<p><b>Note:</b> 98% is the physical limit of the hygrometer, in practice representing complete saturation of the air.</p>
		<?php if($year==2009) { echo '<p><b>Notes for 2009: </b>Station began recording on 1st Feb; Minima for Feb and March are not reliable due to poor hygrometer siting;
		Some October data unavailable due to hardware issues.</p>'; } ?>
</div><!-- end main-copy -->

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>

 </body>
</html>

<?php 
function get_rh_detail ($year, $loc, $range, $season_start, $round) {
	global $SITE, $tempvalues, $temptype, $colors;
	global $show_today, $date, $time, $mnthname; 
	global $highhum, $lowhum; 
	$places = "%01.0f";

	// First Collect the data for the year
		for ( $m = 0; $m < 12 ; $m ++ ) {
			// Save Month in the raw array for use later
			$raw[$m][1] = $m; 
			// Check for current year and current month
			if ($year == date("Y") && $m == ( date("n") - 1) && ((date("j") != 1 ) OR $show_today)) {
				$current_month = 1; 
			} else {
				$current_month = 0; 
			}
			$filename = date('F', mktime(0,0,0,$m+1,1,$year)) . $year . ".htm";
			$raw[$m][0] = gethistory($loc . $filename);
		}
	echo '<table><tr><th rowspan="2" class="labels" width="8%">Day</th>';
 
	// Cycle through the months for a label
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		$mx = $m + $i;
		$mx = $i + 1;
		$yearx = $year;
		$maxdays[$i] = get_days_in_month($mx, $yearx); // sets number of days per month for selected year
		echo '<th colspan="2" class="labels" width="7%" >' . substr( $mnthname[ $raw[$i][1] ], 0, 3 ) . '</th>';
	}
	echo "</tr>\n";

echo '<tr>';
for ($i = 0 ; $i < 12 ; $i++ ) {
	echo '<th class="labels" width="3%">Low</th>';
	echo '<th class="labels" width="3%" >High</th>';
}
echo "</tr>\n"; 
	
	// Setup month and year totals
	$tempmonth = array();
	$monthmean = array();
	$ytd = 0;
	
	// Cycle through the possible days 
	for ( $day = 0 ; $day < 31 ; $day++ ) {
		echo '<tr><td class="reportdt">' . ($day + 1) . '</td>';
		// Display each months values for that day
		for ($mnt = 0 ; $mnt < 12 ; $mnt++ ) {
			if($year == date("Y") && $mnt == date("m")-1 && $day == date("j")-1) {
				echo '<td class=" ' . ValueColor3($lowhum).'">' . sprintf($places,$lowhum) .' </td>';
				echo '<td class=" ' . ValueColor2($highhum).'">' . sprintf($places,$highhum) .' </td>';
			} 
			elseif ($maxdays[$mnt] < $day + 1 ) {
				echo '<td class="noday" colspan="2">&nbsp;</td>';
			} else {
				if ( $raw[$mnt][0][3][$day] == "" ) { //MINIMA DATA ECHO
				$put = "---";
				} else {
				$put = $raw[$mnt][0][3][$day];
				$tempmonth[$mnt][2] = $tempmonth[$mnt][2] + $put;
				$tempmonth[$mnt][3] = $tempmonth[$mnt][3] + 1;
				}
				if ($put != "---" && $mnt != 0):
					echo '<td class=" ' . ValueColor3($put).'">' . sprintf($places,$put) .' </td>';
				elseif ($put != "---" && $mnt == 0):
					echo '<td class=" ' . ValueColor($put).'">' . sprintf($places,$put) .' </td>';
					else: echo '<td class="reportday" >' . "---" . '</td>';
				endif;
				
				if ( $raw[$mnt][0][2][$day] == "" ) { //MAXIMA DATA ECHO
					$put = "---";
				} else {
					$put = $raw[$mnt][0][2][$day];
					$tempmonth[$mnt][0] = $tempmonth[$mnt][0] + $put;
					$tempmonth[$mnt][1] = $tempmonth[$mnt][1] + 1;
				}
			if ($put != "---" && $mnt != 11):
					echo '<td class=" ' . ValueColor2($put).'">' . sprintf($places,$put) .' </td>';
				elseif ($put != "---" && $mnt == 11):
					echo '<td class=" ' . ValueColor($put).'">' . sprintf($places,$put) .' </td>';
					else: echo '<td class="reportday" >' . "---" . '</td>';
			endif;
			}
		}
		echo "</tr>\n";
	}	
echo '<tr><td class="separator" colspan="25" >&nbsp;</td></tr>'; 
echo '<tr><th class="labels">&nbsp;</th>' ;

		for ( $i = 0 ; $i < 12 ; $i++ ) {
			echo '<th colspan="2" class="labels">' . substr( $mnthname[ $raw[$i][1] ], 0, 3 ) . '</th>'; 
		}

	echo '</tr><tr><td class="reportttl">Minimum</td>'; // MONTH MINIMUM ECHO 
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($tempmonth[$i][3] > 0) {
			echo '<td class=" ' . ValueColor(min($raw[$i][0][3])).'" colspan="2">' . sprintf($places,min($raw[$i][0][3])) . '</td>'; 
		}	else {
			echo '<td class="reportttl" colspan="2" >' . "---" . '</td>';
			}
	}
	
	echo '</tr><tr><td class="reportttl">Maximum</td>'; // MONTH MAXIMA ECHO 
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($tempmonth[$i][1] > 0) {
			echo '<td class=" ' . ValueColor(max($raw[$i][0][2])).'" colspan="2">' . sprintf($places,max($raw[$i][0][2])) .' </td>'; 
		} else {
			echo '<td class="reportttl" colspan="2">' . "---" . '</td>';
			}
	}
	
	echo '</tr><tr class="reportttl2"><td class="reportttl">Mean</td>'; // MONTH MEAN ECHO 
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		$mean = (array_sum($raw[$i][0][2]) + array_sum($raw[$i][0][3])) /(count($raw[$i][0][2]) + count($raw[$i][0][3]));
		if ( $mean != 0 ) {
			echo '<td class=" ' . ValueColor($mean).'" colspan="2">' . sprintf($places,$mean). '</td>';
		} else {
			echo '<td class="reportttl" colspan="2" >' . "---" . '</td>';
			}
	} 
	
	echo '</tr><tr><td class="reportttl">Mean Min</td>'; // MONTH MEAN MIN ECHO 
	for ( $i = 0 ; $i < 12 ; $i++ ) { 
		if ($tempmonth[$i][3] > 0) {
			echo '<td class=" ' . ValueColor(($tempmonth[$i][2] / $tempmonth[$i][3] )).'" colspan="2">' . sprintf($places,($tempmonth[$i][2] / $tempmonth[$i][3] )) . '</td>'; 
		}	else {
			echo '<td class="reportttl" colspan="2">' . "---" . '</td>';
			}
	}
		
	echo '</tr><tr><td class="reportttl">Mean Max</td>'; // MONTH MEAN MAX ECHO 
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($tempmonth[$i][1] > 0) {
		echo '<td class=" ' . ValueColor(($tempmonth[$i][0] / $tempmonth[$i][1] )).'" colspan="2">' . sprintf($places,($tempmonth[$i][0] / $tempmonth[$i][1] )) . '</td>';
		} else {
			echo '<td class="reportttl" colspan="2" >' . "---" . '</td>';
			}
	}
	
$colorband_rows = ceil($colors/15);
$colorband_cols = ceil(($colors+1)/$colorband_rows); 
	echo '</tr></table><table><tr><td class="separator" colspan="'.($colorband_cols).'" >&nbsp;</td></tr>'; 
	echo '<tr><td class="colorband" colspan="'.($colorband_cols).'">Colour Key</td></tr>';
	$i = 0;
	for ($r = 0; $r < $colorband_rows; $r ++){ 
		for ( $j = 0; (($j < $colorband_cols) AND ($i < $colors)) ; $j ++ ){
		$band = $i;

		if ($i == 0){ 
			echo '<tr><td class="levelc_1" >&lt;&nbsp;' . sprintf("%01.0f",$tempvalues[$i]) . '</td>';
		} else {
			echo '<td class="levelc_'.($band+1).'" > ' . sprintf("%01.0f",$tempvalues[$i-1]) . " - " .sprintf("%01.0f",$tempvalues[$i]-1) . '</td>';
			if (($j == $colorband_cols-1) AND ($r != $colorband_rows)){
				echo '</tr><tr>';
				} 
		}
		$i = $i+1;
		}
	}
	echo '<td class="levelc_'.($band+2).'" >'. sprintf("%01.0f",$tempvalues[$i-1]) . '</td>';
	echo '</tr></table>';
}

function gethistory($file) {
	if(file_exists($file)) {
		$data = file($file);
		$end = 1200;
		for ($i = 1; $i < $end; $i++) {
			if(strpos($data[$i],"remes for the month") > 0) { $end = $i; }
			if(strpos($data[$i],"remes for day") > 0) { $daya = explode(" ", $data[$i]); $a = intval(substr($daya[7],1,2))-1; }
			if(strpos($data[$i],"aximum hum") > 0): $hmaxa = explode(" ", $data[$i]); $hmaxv[$a] = intval($hmaxa[12]); endif;
			if(strpos($data[$i],"inimum hum") > 0): $hmina = explode(" ", $data[$i]); $hminv[$a] = intval($hmina[11]); endif;
		}
		return array(1,1,$hmaxv,$hminv);
	}
}

//Calculate colors depending on value
function ValueColor($value) {
	global $tempvalues;
	$limit = count($tempvalues);
	if ($value < $tempvalues[0]) {
	return 'levelc_1';
	} 
	for ($i = 1; $i < $limit; $i++){
		if ($value < $tempvalues[$i]) {
		return 'levelc_'.($i+1);
		}
	}
	return 'levelc_'.($limit+1);
}
function ValueColor2($value) {
	global $tempvalues;
	$limit = count($tempvalues);
	if ($value < $tempvalues[0]) {
	return 'levelc2_1';
	} 
	for ($i = 1; $i < $limit; $i++){
		if ($value < $tempvalues[$i]) {
		return 'levelc2_'.($i+1);
		}
	}
	return 'levelc2_'.($limit+1);
}
function ValueColor3($value) {
	global $tempvalues;
	$limit = count($tempvalues);
	if ($value < $tempvalues[0]) {
	return 'levelc3_1';
	} 
	for ($i = 1; $i < $limit; $i++){
		if ($value < $tempvalues[$i]) {
		return 'levelc3_'.($i+1);
		}
	}
	return 'levelc3_'.($limit+1);
}
?>