<?php require('unit-select.php'); ?>
<?php
// Obtains Server's Self and protect it against XSS injection
$SITE = array();
$SITE['self'] = htmlentities( substr($_SERVER['PHP_SELF'], 0, 
	strcspn( $_SERVER['PHP_SELF'] , "\n\r") ), ENT_QUOTES );
@include_once("wxreport-settings.php"); 
# Settings###############################################
$temprange_start = array(10, -10); # (Farenheit, Celcius)
$temprange_increment = array(10, 5); # (Farenheit, Celcius)
$increments = 8;
$set_values_manually = false;
$manual_values = array(.25, .5, 1, 2, 3,6,12,18,24,36,60,75);
$loc = $path_dailynoaa;
$first_year_of_data = $first_year_of_noaadata;
$uomTemp = '&deg;'.$unitT;
$temptype = array("C","F");
$temptype = $temptype[$uomTemp == "&deg;F"];
$temprange_start = $temprange_start[((strtoupper($temptype)) != "F")];
$temprange_increment = $temprange_increment[((strtoupper($temptype)) != "F")];
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

<?php
	include("phptags.php");
	$file = 50;
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - Annual Dew point Reports</title>

	<meta name="description" content="Old v2 - Detailed historical annual dew point report for <?php echo $year; ?> with monthly breakdown from NW3 weather.
	Highest and lowest, extremes and means/averages for dew point. Most and least humid days and months for each year of records." />
	
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
		<h1>Dew Point Reports (&deg;<?php echo $unitT ?>)</h1>
		
		<?php $self = 'wxhist10.php';
			include("wxrepgen.php"); 
			get_dew_detail($year,$loc, $range, $season_start, $round); 
		?>
	</div>
	<?php if($year==2009) { echo '<p><b>Notes for 2009: </b>Station began recording on 1st Feb; Minima for Feb and March are not reliable due to poor hygrometer siting;
		Some October data unavailable due to hardware issues.</p>'; } ?>
</div><!-- end main-copy -->

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>

 </body>
</html>

<?php 
function get_dew_detail ($year, $loc, $range, $season_start, $round) {
	global $SITE, $tempvalues, $temptype, $colors;
	global $show_today, $date, $time, $mnthname; 
	global $maxdew, $mindew; 
	$places = "%01.1f";

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
				echo '<td class=" ' . ValueColor3(conv($mindew,1,0)).'">' . conv($mindew,1,0) .' </td>';
				echo '<td class=" ' . ValueColor2(conv($maxdew,1,0)).'">' . conv($maxdew,1,0) .' </td>';
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
					echo '<td class=" ' . ValueColor3(conv($put,1,0)).'">' . conv($put,1,0) .' </td>';
				elseif ($put != "---" && $mnt == 0):
					echo '<td class=" ' . ValueColor(conv($put,1,0)).'">' . conv($put,1,0) .' </td>';
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
					echo '<td class=" ' . ValueColor2(conv($put,1,0)).'">' . conv($put,1,0) .' </td>';
				elseif ($put != "---" && $mnt == 11):
					echo '<td class=" ' . ValueColor(conv($put,1,0)).'">' . conv($put,1,0) .' </td>';
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
			echo '<td class=" ' . ValueColor(conv(min($raw[$i][0][3]),1,0)).'" colspan="2">' . conv(min($raw[$i][0][3]),1,0) . '</td>'; 
		}	else {
			echo '<td class="reportttl" colspan="2" >' . "---" . '</td>';
			}
	}
	
	echo '</tr><tr><td class="reportttl">Maximum</td>'; // MONTH MAXIMA ECHO 
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($tempmonth[$i][1] > 0) {
			echo '<td class=" ' . ValueColor(conv(max($raw[$i][0][2]),1,0)).'" colspan="2">' . conv(max($raw[$i][0][2]),1,0) .' </td>'; 
		} else {
			echo '<td class="reportttl" colspan="2">' . "---" . '</td>';
			}
	}
	
	echo '</tr><tr class="reportttl2"><td class="reportttl">Mean</td>'; // MONTH MEAN ECHO 
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		$mean = (array_sum($raw[$i][0][2]) + array_sum($raw[$i][0][3])) /(count($raw[$i][0][2]) + count($raw[$i][0][3]));
		if ( $mean != 0 ) {
			echo '<td class=" ' . ValueColor(conv(($mean),1,0)).'" colspan="2">' . conv($mean,1,0). '</td>';
		} else {
			echo '<td class="reportttl" colspan="2" >' . "---" . '</td>';
			}
	} 
	
	echo '</tr><tr><td class="reportttl">Mean Min</td>'; // MONTH MEAN MIN ECHO 
	for ( $i = 0 ; $i < 12 ; $i++ ) { 
		if ($tempmonth[$i][3] > 0) {
			echo '<td class=" ' . ValueColor(conv($tempmonth[$i][2] / $tempmonth[$i][3],1,0)).'" colspan="2">' . conv($tempmonth[$i][2] / $tempmonth[$i][3],1,0) . '</td>'; 
		}	else {
			echo '<td class="reportttl" colspan="2">' . "---" . '</td>';
			}
	}
		
	echo '</tr><tr><td class="reportttl">Mean Max</td>'; // MONTH MEAN MAX ECHO 
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($tempmonth[$i][1] > 0) {
		echo '<td class=" ' . ValueColor(conv($tempmonth[$i][0] / $tempmonth[$i][1],1,0)).'" colspan="2">' . conv($tempmonth[$i][0] / $tempmonth[$i][1],1,0) . '</td>';
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
			echo '<tr><td class="level_1" >&lt;&nbsp;' . conv($tempvalues[$i],0,0) . '</td>';
		} else {
			if (($j == 0) AND ($r > 0)){
			// echo '<tr>';
			}
				echo '<td class="level_'.($band+1).'" > ' . conv($tempvalues[$i-1],0,0) . " - " .conv($tempvalues[$i],0,0) . '</td>';
				if (($j == $colorband_cols-1) AND ($r != $colorband_rows)){
					echo '</tr><tr>';
				} 
		}
		$i = $i+1;
		}
	}
	echo '<td class="level_'.($band+2).'" >&gt;'. conv($tempvalues[$i-1],0,0) . '</td>';
	echo '</tr></table>';
}

function gethistory($file) {
	if(file_exists($file)) {
		$data = file($file);
		$end = 1200;
		for ($i = 1; $i < $end; $i++) {
			if(strpos($data[$i],"remes for the month") > 0) { $end = $i; }
			if(strpos($data[$i],"remes for day") > 0) { $daya = explode(" ", $data[$i]); $a = intval(substr($daya[7],1,2))-1; }
			if(strpos($data[$i],"aximum dew") > 0): $dmaxa = explode(" ", $data[$i]); $dmaxv[$a] = floatval($dmaxa[11])+0.001; endif;
			if(strpos($data[$i],"inimum dew") > 0): $dmina = explode(" ", $data[$i]); $dminv[$a] = floatval($dmina[11])+0.001; endif;
		}
		return array(1,1,$dmaxv,$dminv);
	}
}

function ValueColor($value) {
	global $tempvalues;
	$limit = count($tempvalues);
	if ($value < $tempvalues[0]) {
	return 'level_1';
	} 
	for ($i = 1; $i < $limit; $i++){
		if ($value < $tempvalues[$i]) {
		return 'level_'.($i+1);
		}
	}
	return 'level_'.($limit+1);
}
function ValueColor2($value) {
	global $tempvalues;
	$limit = count($tempvalues);
	if ($value < $tempvalues[0]) {
	return 'level2_1';
	} 
	for ($i = 1; $i < $limit; $i++){
		if ($value < $tempvalues[$i]) {
		return 'level2_'.($i+1);
		}
	}
	return 'level2_'.($limit+1);
}
function ValueColor3($value) {
	global $tempvalues;
	$limit = count($tempvalues);
	if ($value < $tempvalues[0]) {
	return 'level3_1';
	} 
	for ($i = 1; $i < $limit; $i++){
		if ($value < $tempvalues[$i]) {
		return 'level3_'.($i+1);
		}
	}
	return 'level3_'.($limit+1);
}
?>