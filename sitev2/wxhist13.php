<?php require('unit-select.php'); ?>
<?php
// Obtains Server's Self and protect it against XSS injection
$SITE 			= array();
$SITE['self'] = htmlentities( substr($_SERVER['PHP_SELF'], 0,
	strcspn( $_SERVER['PHP_SELF'] , "\n\r") ), ENT_QUOTES );
@include_once("wxreport-settings.php");
############################################################################
$start_year = "2009";
$wind_unit = $unitW;
$css_file = "wxreports.css" ;
$manual_valuesUK = array(1,5,10,15,20,25,30,35,40);
$manual_valuesEU = array(2,5,10,20,30,40,50,60,70);
$loc = $path_dailynoaa;
$first_year_of_data = $first_year_of_noaadata;
$first_year_of_data = max($first_year_of_data,$start_year);
$windvalues = $manual_valuesUK;
if($unitW == 'km/h') { $windvalues = $manual_valuesEU; }
$increments = (count($manual_valuesUK))-1;
$colors = $increments + 1;
$dirs = array("N","NNE", "NE", "ENE", "E", "ESE", "SE", "SSE", "S", "SSW","SW", "WSW", "W", "WNW", "NW", "NNW");

$season_start = 1;
$range = "year";
include('wxrepgen0.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("phptags.php");
		$file = 53; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - Annual Wind Reports</title>

	<meta name="description" content="Old v2 - Detailed historical annual wind speed, gust and direction report for <?php echo $year; ?> with monthly breakdown from NW3 weather.
	Find windiest and calmest days of each month for each year on record." />

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
		<h1>Wind Reports (<?php echo trim($wind_unit) ?>)</h1>
		
		<?php $self = 'wxhist13.php';
			include("wxrepgen.php");
			get_wind_detail($year,$loc, $range, $season_start, $hot, $cold, $round); 
		?>
	</div>
</div> <!-- end main copy -->

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>

 </body>
</html>

<?php
function get_wind_detail ($year, $loc, $range, $season_start, $hot, $cold, $round) {
	global $SITE, $windvalues, $colors, $dirs;
	global $wind_unit, $show_today, $maxgst, $avgspeedsincereset, $date, $time, $mnthname;
	$places = "%01.1f";
  	
	// Raw Data Definitions
	$rawlb = array("day" => 0, "mean" => 1, "high" => 2 , "htime" => 3,
		"low" => 4, "ltime" => 5, "hdg" => 6, "cdg" => 7, "rain" => 8,
		"aws" => 9, "hwind" => 10 , "wtime" => 11, "dir" => 12);
		
	$anom = array(5.2,5.1,5.2,4.9,4.7,4.4,4.3,4.0,3.9,4.1,4.6,5.1);

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
			
			if ($current_month AND $show_today AND date("j")==1){
				$raw[$m][0][0][9] = strip_units($avgspeedsincereset);
				$raw[$m][0][0][10] = strip_units($maxgst);
			} elseif (file_exists($loc . $filename) ) {
				$raw[$m][0] = getnoaafile($loc . $filename);
			}
				if ($current_month AND $show_today){
					$raw[$m][0][date("j")-1][9] = strip_units($avgspeedsincereset);
					$raw[$m][0][date("j")-1][10] = strip_units($maxgst);
				}
		}
		
	// Output Table with information
	echo '<table><tr><th rowspan="2" class="labels" width="8%">Day</th>';

	for ( $i = 0 ; $i < 12 ; $i++ ) {
		$mx = $m + $i;
		$mx = $i + 1;
		$yearx = $year;
		$maxdays[$i] = get_days_in_month($mx, $yearx);  // sets number of days per month for selected year
		echo '<th  colspan="2" class="labels" width="7%" >' . substr( $mnthname[ $raw[$i][1] ], 0, 3 ) . '</th>';
	}
	echo "</tr>\n";

echo '<tr>';
for ($i = 0 ; $i < 12 ; $i++ ) {
		echo '<th class="labels" width="3%">Avg</th>';
		echo '<th class="labels" width="3%">Max</th>';
		$monthmin[$i] = 9;
	}
echo "</tr>\n";
	
	$windmonth = array();
	$ytd = 0;
	for ( $day = 0 ; $day < 31 ; $day++ ) {
		echo '<tr><td class="reportdt" rowspan="1">' . ($day + 1) . '</td>';
		for ($mnt = 0 ; $mnt < 12 ; $mnt++ ) {
			$condd = date("z", mktime(0,0,0,$mnt+1,$day+1,$year));
			$cond = (($year == 2009 && $condd < 214)  || ($year == 2010 && $condd > 105 && $condd < 208));
			if ($maxdays[$mnt] < $day + 1 ) {
				echo '<td class="noday" colspan="2" >&nbsp;</td>';
			}	else {
				if ( $raw[$mnt][0][$day][$rawlb['aws']] == "" || $raw[$mnt][0][$day][$rawlb['aws']] == "---" || $cond) {	// Average Wind speed
					$put = "---";
					echo '<td class="reportday">' . $put . '</td>';
				} else {
					 $put = $raw[$mnt][0][$day][$rawlb['aws']];
					 $windmonth[$mnt][0] = $windmonth[$mnt][0] + $put;
					 $windmonth[$mnt][1] = $windmonth[$mnt][1] + 1;
					 if ($put > $monthmax[$mnt]) { $monthmax[$mnt] = $put; }
					 if ($put < $monthmin[$mnt]) { $monthmin[$mnt] = $put; }
					 echo '<td class="'. ValueColor2(conv($put,4,0),$windvalues) .'">'. conv($put,4,0) . '</td>';
					}

			if ( $raw[$mnt][0][$day][$rawlb['hwind']] == "" || $raw[$mnt][0][$day][$rawlb['hwind']] == "---" || $cond) {	// Gust
				 $put = "---";
				 echo '<td class="reportday">' . $put . '</td>';
			} else {
					$put = $raw[$mnt][0][$day][$rawlb['hwind']];
					$windmonth[$mnt][2] = $windmonth[$mnt][2] + $put;
					$windmonth[$mnt][3] = $windmonth[$mnt][3] + 1;
					if ($put > $monthmaxgust[$mnt]) { $monthmaxgust[$mnt] = $put; }
					echo '<td class="'. ValueColor3(conv($put,4,0),$windvalues) .'">'. conv3($put,4,0) . '</td>';
				}
				}
		}
			echo "</tr>\n";
	}
	// We are done with the daily numbers now lets show the summary
	
echo '<tr><td colspan="25" class="separator">&nbsp;</td></tr>';
// Put month headings
echo '<tr><th class="labels">&nbsp;</th>'  ;
		for ( $i = 0 ; $i < 12 ; $i++ ) {
			echo '<th colspan="2" class="labels">' . substr( $mnthname[ $raw[$i][1] ], 0, 3 ) . '</th>';
		}
		
	echo '</tr><tr><td class="reportttl">Month Mean<br />(anomaly)</td>';	//Month Mean
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($windmonth[$i][1] > 0 && !($i == 6 && $year == 2010)) {
			$windspeed =  conv(($windmonth[$i][0] / $windmonth[$i][1] ),4,0);
			echo '<td colspan="2" class="'. ValueColor2($windspeed,$windvalues) .'">'. sprintf($places, $windspeed). '<br />('. sprintf('%+.1f',$windspeed - conv($anom[$i],4,0)) . ')</td>';
		} else {
			 echo '<td colspan="2" class="reportttl" >' . "---"  . '</td>';
			}
	}
	
	echo '</tr><tr><td class="reportttl">Highest Avg</td>';					//Highest Avg
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($windmonth[$i][1] > 0 && !($i == 6 && $year == 2010)) {
			$windspeed =  conv($monthmax[$i],4,0);
			echo '<td colspan="2" class="'. ValueColor2($windspeed,$windvalues) .'">'. sprintf($places, $windspeed) . '</td>';
		} else {
			 echo '<td colspan="2" class="reportttl">' . "---"  . '</td>';
			}
	}
	
	echo '</tr><tr><td class="reportttl">Lowest Avg</td>';					//Lowest Avg
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($windmonth[$i][1] > 0 && !($i == 6 && $year == 2010)) {
			$windspeed =  conv($monthmin[$i],4,0);
			echo '<td colspan="2" class="'. ValueColor2($windspeed,$windvalues) .'">'. sprintf($places, $windspeed) . '</td>';
		} else {
			 echo '<td colspan="2" class="reportttl">' . "---"  . '</td>';
			}
	}
	
	echo '</tr><tr><td class="reportttl">Max Gust</td>';					//Max Gust
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($windmonth[$i][3] > 0 && !($i == 6 && $year == 2010)) {
			$windspeed = conv($monthmaxgust[$i],4,0);
			echo '<td colspan="2" class="'. ValueColor2($windspeed,$windvalues) .'">'. sprintf("%01.0f", $windspeed) . '</td>';
		} else {
			echo '<td colspan="2" class="reportttl">---</td>';
		}
	}
	echo '</tr></table>';
	
$colorband_rows = ceil($colors/15);
$colorband_cols = ceil(($colors+1)/$colorband_rows);
	echo '<table><tr><td width="7%" class="separator" colspan="'.($colorband_cols).'" >&nbsp;</td></tr>';
	echo '<tr><td width="7%" class="colorband" colspan="'.($colorband_cols).'">Colour Key</td></tr>';
	$i = 0;
	for ($r = 0; $r < 1; $r ++){
		for ( $j = 0; (($j < $colorband_cols) AND ($i < $colors)) ; $j ++ ){
		$band = $i;

		if ($i == 0){
			echo '<tr><td width="7%" class="beaufort1" >&lt;&nbsp;' . $windvalues[$i] . '</td>';
		} else {
			if (($j == 0) AND ($r > 0)){
			}
				echo '<td width="7%" class="beaufort'.($band+1).'"'.$color_text.' > ' . $windvalues[$i-1] . " - " . $windvalues[$i] . '</td>';
				if (($j == $colorband_cols-1) AND ($r != $colorband_rows)){
					echo '</tr><tr>';
					
				}
		}
		$i = $i+1;
		}
	}
	echo '<td width="7%" class="beaufort'.($band+2).'"'.$color_text.' >'. '&gt;'. $windvalues[$i-1] . '</td>';
	echo '</tr></table>';
	
	echo "<p align='left' class='large'><b>Note 1:</b> Figures in brackets refer to departure from <a href='wxaverages.php' title='Long-term NW3 climate averages'>average conditions</a><br />
	<b>Note 2:</b> 'Max' values refer to the highest gust speed (wind speed sampled over 14s) achieved on a given day; 'Avg' refers to the day's overall mean wind speed<br />
	<b>Note 3:</b> Gust values are extracted from log files, which record the data in knots rounded to the nearest integer. Consequently, these values can only take
	certain discreet values seperated by 1 knot (&asymp;", conv(1.152,4,1), "), and may differ slightly from values quoted elsewhere on this site. </p>";
	if($year == 2009) { echo '<p class="large"><b>NB:</b> Valid wind records began on 3rd August</p>'; }
	if($year == 2010) { echo '<p class="large"><b>NB:</b> Valid wind records not available for the blanked-out period due to sensor relocation</p>'; }

	echo '<br /><h2>Wind Direction</h2><table><tr><th class="labels" width="8%">Day</th>';		//Wind direction table
	
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		$mx = $m + $i;
		$mx = $i + 1;
		$yearx = $year;
		$maxdays[$i] = get_days_in_month($mx, $yearx); 
		echo '<th class="labels" width="7%" >' . substr( $mnthname[ $raw[$i][1] ], 0, 3 ) . '</th>';
	}
	echo "</tr>\n";

	for ( $day = 0 ; $day < 31 ; $day++ ) {
		echo '<tr><td class="reportdt">' . ($day + 1) . '</td>';
		
		for ($mnt = 0 ; $mnt < 12 ; $mnt++ ) {
			$condd = date("z", mktime(0,0,0,$mnt+1,$day+1,$year));
			$cond = (($year == 2009 && $condd < 214) || ($year == 2010 && $condd > 105 && $condd < 208));
			if ($maxdays[$mnt] < $day + 1 ) {
				echo '<td class="noday">&nbsp;</td>';
			}	else {
			if ( $raw[$mnt][0][$day][$rawlb['dir']] == "" || $raw[$mnt][0][$day][$rawlb['dir']] == "---" || $cond ) {
				 $put = "---";
				 echo '<td class="reportday">' . $put . '</td>';
			} else {
					$put = $raw[$mnt][0][$day][$rawlb['dir']];
					//echo '<td class="'. vcdir($put) .'">'. $put . '</td>';
					echo '<td class="'. vcdir($put) .'">'. $put . '</td>';
				}
				}
		}
		echo "</tr>\n";
	}
	echo '</table>';
	
	echo '<table><tr><td width="7%" class="separator" colspan="16" >&nbsp;</td></tr>';
	echo '<tr><td width="7%" class="colorband" colspan="16">Colour Key</td></tr><tr>';
	for ($r = 0; $r < 16; $r ++) {
		echo '<td width="6%" class="dir'. ($r+1) .'" >'. $dirs[$r] .'</td>';
	}
 echo '</tr></table>';
}

//Calculate colors depending on value
function ValueColor($value,$values) {
	$limit = count($values);
	if ($value < $values[0]) {
	return 'beaufort1';
	}
	for ($i = 1; $i < $limit ; $i++){
		if ($value <= $values[$i]) {
		return 'beaufort'.($i+1);
		}
	}
	return 'beaufort'.($limit+1);
}
function ValueColor2($value,$values) {
	$limit = count($values);
	if ($value < $values[0]) {
	return 'beaufortb1';
	}
	for ($i = 1; $i < $limit ; $i++){
		if ($value <= $values[$i]) {
		return 'beaufortb'.($i+1);
		}
	}
	return 'beaufortb'.($limit+1);
}
function ValueColor3($value,$values) {
	$limit = count($values);
	if ($value < $values[0]) {
	return 'beaufortc1';
	}
	for ($i = 1; $i < $limit ; $i++){
		if ($value <= $values[$i]) {
		return 'beaufortc'.($i+1);
		}
	}
	return 'beaufortc'.($limit+1);
}
function vcdir($dir) {
	global $dirs;
	for ($i = 0; $i < 16 ; $i++){
		if ($dir == $dirs[$i]) {
		return 'dir'.($i+1);
		}
	}
}
?>