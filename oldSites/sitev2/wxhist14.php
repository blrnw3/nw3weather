<?php require('unit-select.php'); ?>
<?php
// Obtains Server's Self and protect it against XSS injection
$SITE = array();
$SITE['self'] = htmlentities( substr($_SERVER['PHP_SELF'], 0,
	strcspn( $_SERVER['PHP_SELF'] , "\n\r") ), ENT_QUOTES );
@include_once("wxreport-settings.php");
# Settings###################################################
$temprange_start = array(20, -5); # (Farenheit, Celcius)
$temprange_increment = array(10, 5); # (Farenheit, Celcius)
$increments = 8;
$set_values_manually = false;
$manual_values = array(.25, .5, 1, 2, 3,6,12,18,24,36,60,75);
$loc = $path_dailynoaa;
$first_year_of_data = $first_year_of_noaadata;
$uomTemp = '&deg;'.$unitT;
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
	$file = 54; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - Annual Temperature Reports</title>

	<meta name="description" content="Old v2 - Detailed historical annual temperature report for <?php echo $year; ?> with monthly breakdown from NW3 weather.
	Find warmest and coldest days in every month for each year on record, as well as mean temperatures with anomalies;
	highest and lowest temperatures recorded; also: highest night time minima/lows and lowest day time maxima/highs; mean low and mean high temperatures." />

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
	<div align="center" id="report">
		<h1>Temperature Reports (<?php echo trim($uomTemp) ?>)</h1>
		
		<?php $self = 'wxhist14.php';
			include("wxrepgen.php");
			get_temp_detail($year,$loc, $range, $season_start, $round);
		?>
	</div>
	
	<p><b>Note 1:</b> Figures in brackets refer to departure from <a href="wxaverages.php" title="Long-term NW3 climate averages">average conditions</a><br />
	<b>Note 2:</b> Mean values are calculated from thousands of 1-minute-spaced values, so may differ slightly from (mean max + mean min)/2.</p>
</div><!-- end main-copy -->

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>

 </body>
</html>

<?php
function get_temp_detail ($year, $loc, $range, $season_start, $round) {
	global $SITE, $tempvalues, $temptype, $colors;
	global $show_today, $maxtempyest, $mintempyest, $maxtemp, $mintemp, $date, $time, $mnthname;
	global $avtempsincemidnight, $yesterdayavtemp, $monthtodateavtemp;
	$places = "%01.1f";
		
	// Raw Data Definitions
	$rawlb = array("day" => 0, "mean" => 1, "high" => 2 , "htime" => 3,
		"low" => 4, "ltime" => 5, "hdg" => 6, "cdg" => 7, "rain" => 8,
		"aws" => 9, "hwind" => 10 , "wtime" => 11, "dir" => 12);
		
	$anom = array(4.7, 4.9, 7, 8.9, 12.5, 15.65, 17.9, 17.7, 14.9, 11.7, 7.6, 5.5);
	$anoml = array(2.4,2.2,3.6,5.0,8.3,11.1,13.4,13.2,11.1,8.8,5.0,3.2);
	$anomh = array(7.0,7.6,10.4,12.8,16.7,20.2,22.4,22.2,18.7,14.6,10.2,7.8);

	// First Collect the data for the year
		for ( $m = 0; $m < 12 ; $m ++ ) {
			// Save Month in the raw array for use later
			$raw[$m][1] = $m;
			// Check for current year and current month
			if ($year == date("Y") && $m == ( date("n") - 1) && ((date("j") != 1 ) OR $show_today)) {
				$filename = $absRoot."dailynoaareport.htm";
				$current_month = 1;
			} else {
				$filename = "dailynoaareport" . ( $m + 1 ) . $year . ".htm";
				$current_month = 0;
			}
			if ($current_month AND $show_today AND date("j")==1) {
				$raw[$m][0][0][1] = strip_units($avtempsincemidnight);
				$raw[$m][0][0][2] = strip_units($maxtemp);
				$raw[$m][0][0][4] = strip_units($mintemp);
			} elseif (file_exists($loc . $filename) ) {
				$raw[$m][0] = getnoaafile($loc . $filename);
			}
			if ($current_month AND $show_today){
				$raw[$m][0][date("j")-1][1] = strip_units($avtempsincemidnight);
				$raw[$m][0][date("j")-1][2] = strip_units($maxtemp);
				$raw[$m][0][date("j")-1][4] = strip_units($mintemp);
			}
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
	echo '<th class="labels" width="3%">Min</th>';
	echo '<th class="labels" width="3%" >Max</th>';
}
echo "</tr>\n";
	
	// Setup month and year totals
	$tempmonth = array();
	$monthmax = array (-100, -100, -100, -100, -100, -100, -100, -100, -100, -100, -100, -100) ; // Initial value for highest temp for month
	$monthmin = array (100, 100, 100, 100, 100, 100, 100, 100, 100, 100, 100, 100) ; // Initial value for lowest temp for month
	$monthhighmin = array (-100, -100, -100, -100, -100, -100, -100, -100, -100, -100, -100, -100) ; // Initial value for highest min for month
	$monthlowmax = array (100, 100, 100, 100, 100, 100, 100, 100, 100, 100, 100, 100) ; // Initial value for lowest max temp for month
	$monthmean = array();
	$ytd = 0;
	
	// Cycle through the possible days
	for ( $day = 0 ; $day < 31 ; $day++ ) {
		echo '<tr><td class="reportdt">' . ($day + 1) . '</td>';
		// Display each months values for that day
		for ($mnt = 0 ; $mnt < 12 ; $mnt++ ) {
			if ($maxdays[$mnt] < $day + 1 ) {
				echo '<td class="noday" colspan="2">&nbsp;</td>';
			} else {
		// Get mean temperature data
				$xyz = $raw[$mnt][0][$day][$rawlb['mean']] ;
				if ( $raw[$mnt][0][$day][$rawlb['mean']] != "" AND $raw[$mnt][0][$day][$rawlb['mean']] != "-----" AND $raw[$mnt][0][$day][$rawlb['mean']] != "X" ) {
				$tempmonth[$mnt][4] = $tempmonth[$mnt][4] + $xyz;
				$tempmonth[$mnt][5] = $tempmonth[$mnt][5] + 1;
				}
				
				if ( $raw[$mnt][0][$day][$rawlb['low']] == "" ) { //MINIMA DATA ECHO
				$put = "---";
				} else {
				$put = $raw[$mnt][0][$day][$rawlb['low']];
				$tempmonth[$mnt][2] = $tempmonth[$mnt][2] + $put;
				$tempmonth[$mnt][3] = $tempmonth[$mnt][3] + 1;
				if ($put < $monthmin[$mnt]) {
					$monthmin[$mnt] = $put;
				}
				if ($put > $monthhighmin[$mnt]) {
					$monthhighmin[$mnt] = $put;
				}
				}
			if ($put != "---" && $mnt != 0):
					echo '<td class=" ' . ValueColor3(conv($put,1,0)).'">' . conv($put,1,0) .' </td>';
				elseif ($put != "---" && $mnt == 0):
					echo '<td class=" ' . ValueColor(conv($put,1,0)).'">' . conv($put,1,0) .' </td>';
					else: echo '<td class="reportday" >' . "---" . '</td>';
			endif;
				
				if ( $raw[$mnt][0][$day][$rawlb['high']] == "" ) { //MAXIMA DATA ECHO
					$put = "---";
				} else {
					$put = $raw[$mnt][0][$day][$rawlb['high']];
					$tempmonth[$mnt][0] = $tempmonth[$mnt][0] + $put;
					$tempmonth[$mnt][1] = $tempmonth[$mnt][1] + 1;
					if ($put > $monthmax[$mnt]) {
						$monthmax[$mnt] = $put;
						}
					if ($put < $monthlowmax[$mnt]) {
						$monthlowmax[$mnt] = $put;
					}
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

	echo '</tr><tr><td class="reportttl">Lowest Min</td>'; // MONTH MINIMUM
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($tempmonth[$i][3] > 0) {
			echo '<td class=" ' . ValueColor(conv($monthmin[$i],1,0)).'" colspan="2">' . conv($monthmin[$i],1,0) . '</td>';
		}	else {
			echo '<td class="reportttl" colspan="2" >' . "---" . '</td>';
			}
	}
	
	echo '</tr><tr><td class="reportttl">Highest Max</td>'; // MONTH MAXIMUM
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($tempmonth[$i][1] > 0) {
			echo '<td class=" ' . ValueColor(conv($monthmax[$i],1,0)).'" colspan="2">' . conv($monthmax[$i],1,0) .' </td>';
		} else {
			echo '<td class="reportttl" colspan="2">' . "---" . '</td>';
			}
	}
	
	echo '</tr><tr><td class="reportttl">Highest Min</td>'; // MONTH HIGH MINIMUM
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($tempmonth[$i][3] > 0) {
			echo '<td class=" ' . ValueColor(conv($monthhighmin[$i],1,0)).'" colspan="2">' . conv($monthhighmin[$i],1,0) . '</td>';
		}	else {
			echo '<td class="reportttl" colspan="2" >' . "---" . '</td>';
			}
	}
	
	echo '</tr><tr><td class="reportttl">Lowest Max</td>'; // MONTH LOW MAXIMUM
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($tempmonth[$i][1] > 0) {
			echo '<td class=" ' . ValueColor(conv($monthlowmax[$i],1,0)).'" colspan="2">' . conv($monthlowmax[$i],1,0) .' </td>';
		} else {
			echo '<td class="reportttl" colspan="2">' . "---" . '</td>';
			}
	}
	
	echo '</tr><tr class="reportttl2"><td class="reportttl">Mean</td>'; // MONTH MEAN
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($tempmonth[$i][5] > 0 AND $tempmonth[$i][5] !="" ) {
			$mmean = ($tempmonth[$i][4] / $tempmonth[$i][5] ); if($i+1 == date('n') && $year == date('Y')) { $mmean = $monthtodateavtemp; }
			echo '<td class=" ' . ValueColor(conv($mmean,1,0)).'" colspan="2"><b>' . conv($mmean,1,0) .
			'<br /> (' . conv2($mmean - $anom[$i],1,0) . ')</b></td>';
		} else {
			echo '<td class="reportttl" colspan="2" >' . "---" . '</td>';
			}
	}
	
	echo '</tr><tr><td class="reportttl">Mean Min</td>'; // MONTH MEAN MIN ECHO
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($tempmonth[$i][3] > 0) {
			echo '<td class=" ' . ValueColor(conv(($tempmonth[$i][2] / $tempmonth[$i][3] ),1,0)).'" colspan="2">' . conv($tempmonth[$i][2] / $tempmonth[$i][3],1,0) .
			'<br /> (' . conv2(($tempmonth[$i][2] / $tempmonth[$i][3]) - $anoml[$i],1,0) . ')</b></td>';
		}	else {
			echo '<td class="reportttl" colspan="2">' . "---" . '</td>';
			}
	}
		
	echo '</tr><tr><td class="reportttl">Mean Max</td>'; // MONTH MEAN MAX ECHO
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($tempmonth[$i][1] > 0) {
		echo '<td class=" ' . ValueColor(conv(($tempmonth[$i][0] / $tempmonth[$i][1] ),1,0)).'" colspan="2">' . conv($tempmonth[$i][0] / $tempmonth[$i][1],1,0) .
		'<br /> (' . conv2(($tempmonth[$i][0] / $tempmonth[$i][1]) - $anomh[$i],1,0) . ')</b></td>';
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
			echo '<tr><td class="level_1" >&lt;&nbsp;' . sprintf("%01.0f",$tempvalues[$i]) . '</td>';
		} else {
			if (($j == 0) AND ($r > 0)){
			// echo '<tr>';
			}
				echo '<td class="level_'.($band+1).'" > ' . sprintf("%01.0f",$tempvalues[$i-1]) . " - " .sprintf("%01.0f",$tempvalues[$i]) . '</td>';
				if (($j == $colorband_cols-1) AND ($r != $colorband_rows)){
					echo '</tr><tr>';
				}
		}
		$i = $i+1;
		}
	}
	echo '<td class="level_'.($band+2).'" >&gt;'. sprintf("%01.0f",$tempvalues[$i-1]) . '</td>';
	echo '</tr></table>';
}

//Calculate colors depending on value
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