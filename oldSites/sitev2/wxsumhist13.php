<?php require('unit-select.php'); ?>
<?php
// Obtains Server's Self and protect it against XSS injection
$SITE['self'] = htmlentities( substr($_SERVER['PHP_SELF'], 0,
	strcspn( $_SERVER['PHP_SELF'] , "\n\r") ), ENT_QUOTES );
include_once("wxreport-settings.php");
#####################################################
$summary = 1;
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
$round = false;
$increments = (count($manual_valuesUK))-1;
$colors = $increments + 1;

// If first day of first month of the year, use previous year
$year = date("Y");
if ($show_today != true){
	if (( date("n") == 1) AND date("j") == 1) {
	$year = $year -1;
	}
}
$years = 1 + ($year - $first_year_of_data);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("phptags.php");
	$file = 53; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title>NW3 Weather - Old(v2) - Historical Wind Summary</title>

	<meta name="description" content="Old v2 - Detailed historical monthly/annual wind speed, gust and direction summary report from NW3 weather;
	find record windiest month, and the windiest and calmest days of each month and year on record; highest gusts and average windspeeds too." />

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
		<h1>Wind Summary (mph)</h1>

		<?php include("wxrepgen.php");
			get_wind_detail($first_year_of_data,$year,$years,$loc, $round);
		?>
	</div>

	<p><b>Note 1:</b> Figures in brackets refer to departure from <a href="wxaverages.php" title="Long-term NW3 climate averages">average conditions</a><br />
	<b>Note 2:</b> Data for the periods Jan-Jul 2009 and Apr-Jul 2010 are invlaid due to poor siting <a href="wx8.php#History">(see explanation)</a>, and have therefore been excluded. </p>
</div><!-- end main-copy -->

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>
 </body>
</html>

<?php
function get_wind_detail ($first_year_of_data,$year, $years, $loc, $round) {
	global $SITE, $windvalues, $colors;
	global $windvalues;
	global $wind_unit, $show_today, $maxgst, $avgspeedsincereset, $mnthname;
	
	$anom = array(5.2,5.1,5.2,4.9,4.7,4.4,4.3,4.0,3.9,4.1,5.6,5.1);	
	$places = "%01.1f";
	
	// Collect the data
		for ( $y = 0; $y < $years ; $y ++ ) {
			$yx = $year - $y;
			
		for ( $m = 0; $m < 12 ; $m ++ ) {
			
			// Check for current year and current month
		if ($yx == date("Y") && $m == ( date("n") - 1) &&((date("j") != 1 ) OR $show_today)){
				$filename = $absRoot."dailynoaareport.htm";
				$current_month = 1;
			} else {
				$filename = "dailynoaareport" . ( $m + 1 ) . $yx . ".htm";
				$current_month = 0;
			}
					
			if ($current_month AND $show_today AND date("j")==1){
				$raw[$y][1][$m][1][0][9] = strip_units($avgspeedsincereset);
				$raw[$y][1][$m][1][0][10] = strip_units($maxgst);
			} elseif (file_exists($loc . $filename) ) {
				$raw[$y][1][$m][1] = getnoaafile($loc . $filename);
			}
			if ($current_month AND $show_today){
				$raw[$y][1][$m][1][date("j")-1][9] = strip_units($avgspeedsincereset);
				$raw[$y][1][$m][1][date("j")-1][10] = strip_units($maxgst);
					
				}
		}
		}
		
	// Output Table with information
	echo '<table>';
	$windmonth = array();
	$windyear = array();
	$ytd = 0;
	for ($mnt = 0 ; $mnt < 12 ; $mnt++ ) {
		$monthavmin[$mnt] = 9;
	}
	for ($yx = 0 ; $yx < $years ; $yx++ )  {
			$yeardmin[$yx] = 9; //initialise
		for ($mnt = 0 ; $mnt < 12 ; $mnt++ ) {
			$windmonth[$yx][$mnt][7] = 9;
		for ($day = 0 ; $day < 31 ; $day++ ) {
			$cond = (!($yx == date("Y")-2010 && $mnt > 2 && $mnt < 7) && !($yx == date("Y")-2009 && $mnt < 7));
				$wind = $raw[$yx][1][$mnt][1][$day][9];   // Average Wind Speed
				if ( $wind != "" AND $wind != "-----") {
					$windmonth[$yx][$mnt][0] = $windmonth[$yx][$mnt][0] + $wind;
					$windmonth[$yx][$mnt][1] = $windmonth[$yx][$mnt][1] + 1;
					if($cond) { 
						$like_months[$mnt][0] = $like_months[$mnt][0] + $wind;
						$like_months[$mnt][1] = $like_months[$mnt][1] + 1; 
					}
					if ($wind > $windmonth[$yx][$mnt][6]) {	$windmonth[$yx][$mnt][6] = $wind; }
					if ($wind > $monthavmax[$mnt]  && $cond) { $monthavmax[$mnt] = $wind; }
					if ($wind < $windmonth[$yx][$mnt][7]) {	$windmonth[$yx][$mnt][7] = $wind; }
					if ($wind < $monthavmin[$mnt] && $cond) { $monthavmin[$mnt] = $wind; }
				}
				$wind = $raw[$yx][1][$mnt][1][$day][10];   // Max Gust
				if ( $wind != "" AND $wind != "-----") {
					$windmonth[$yx][$mnt][2] = $windmonth[$yx][$mnt][2] + $wind;
					$windmonth[$yx][$mnt][3] = $windmonth[$yx][$mnt][3] + 1;
					if ($wind > $maxgust[$yx][$mnt] && $cond) { $maxgust[$yx][$mnt] = $wind; }
					if ($wind > $monthmax[$mnt] && $cond) {	$monthmax[$mnt] = $wind; }
					$like_months[$mnt][2] = $like_months[$mnt][2] + $wind;
					$like_months[$mnt][3] = $like_months[$mnt][3] + 1;
				}
		}    // end day loop
		if ($windmonth[$yx][$mnt][6] > $yeardmax[$yx] && $cond) { $yeardmax[$yx] = $windmonth[$yx][$mnt][6]; }
		if ($windmonth[$yx][$mnt][7] < $yeardmin[$yx] && $cond) { $yeardmin[$yx] = $windmonth[$yx][$mnt][7]; }
		if($cond) {
		$windyear[$yx][0] = $windyear[$yx][0] + $windmonth[$yx][$mnt][0];  // Average Wind
		$windyear[$yx][1] = $windyear[$yx][1] + $windmonth[$yx][$mnt][1];  // Average Wind days
		$windyear[$yx][2] = $windyear[$yx][2] + $windmonth[$yx][$mnt][2];  // Max wind
		$windyear[$yx][3] = $windyear[$yx][3] + $windmonth[$yx][$mnt][3];  // Max wind days
		$allyears[0] = $allyears[0] + $windmonth[$yx][$mnt][0];
		$allyears[1] = $allyears[1] + $windmonth[$yx][$mnt][1];
		$allyears[2] = $allyears[2] + $windmonth[$yx][$mnt][2];
		$allyears[3] = $allyears[3] + $windmonth[$yx][$mnt][3];
		}
		}  // end month loop
	}   // end year loop
	
// We have all the info, now display it
	echo '<tr><th class="tableheading" colspan="14">Maximum Gust Speed</th></tr>';			//  Maximum Gust
	echo '<tr><th class="labels" width="7%">Date</th>';
		for ( $i = 0 ; $i < 12 ; $i++ ) {
			echo '<th class="labels" width="7%">' . substr( $mnthname[$i], 0, 3 ) . '</th>';
		}
	echo '<th class="labels" width="7%">Year</th>';
	echo '</tr>';
for ($y = 0; $y < $years; $y++)  {
	echo '<tr><td class="reportttl">', $year-$y, '</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		 if ($windmonth[$y][$i][3] > 0 && !(($y == $years-1 && $i < 7)) && !($y == $years-2 && ($i < 7 && $i > 2))) {
				echo '<td class=" ' . ValueColor(conv($maxgust[$y][$i],4,0),$windvalues).'"' . '>' . sprintf("%01.0f",conv($maxgust[$y][$i],4,0)) .' </td>';
		}
		 else { echo '<td class="reportttl"  >' . "---"  . '</td>'; }
		 if ($windmonth[$y][$i][3] > 0) { $wind_months[$i][3] = $wind_months[$i][3] + $days_of_data; }
		}
				$days_of_data = $windyear[$y][3];
				if ($days_of_data > 0){
					$max = conv(max($maxgust[$y]),4,0);
					echo '<td class=" ' . ValueColor2($max,$windvalues).'"' . '>' . sprintf("%01.0f",($max)) .'</td>';
				}
				else {
					echo '<td class="reportttl"  >' . "---"  . '</td>';
					}
	echo '</tr>';
}
// Now display the max of like months
	echo '<tr class="reportttl2"><td class="reportttl">Max</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
	if ($wind_months[$i][3]==0) {
		echo '<td class="reportttl"  >' . "---"  . '</td>';
	} else {
	$max = conv($monthmax[$i],4,0);
	echo '<td class=" ' . ValueColor($max,$windvalues).'"' . '>' . sprintf("%01.0f",($max)) .' </td>';
	}
	}
	$max = conv(max($monthmax),4,0);
	echo '<td class=" ' . ValueColor2($max,$windvalues).'"' . '>' . sprintf("%01.0f",($max)) . ' </td></tr>';
	
	echo '<tr><td class="separator" colspan="14" >&nbsp;</td></tr>';
	
	echo '<tr><th class="tableheading" colspan="14">Highest Daily Mean Speed (Windiest Day)</th></tr>';		//  Highest Daily Wind Mean
	echo '<tr><th class="labels" width="7%">Date</th>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		echo '<th class="labels" width="7%">' . substr( $mnthname[$i], 0, 3 ) . '</th>';
	}
	echo '<th class="labels" width="7%">Year</th>';
	echo '</tr>';
for ($y = 0; $y < $years; $y++)  {
	echo '<tr><td class="reportttl">', $year-$y, '</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		 if ($windmonth[$y][$i][3] > 0 && !(($y == $years-1 && $i < 7)) && !($y == $years-2 && ($i < 7 && $i > 2))) {
				echo '<td class=" ' . ValueColor(conv($windmonth[$y][$i][6],4,0),$windvalues).'"' . '>' . sprintf($places,conv($windmonth[$y][$i][6],4,0)) .' </td>';
			}
		 else
			echo '<td class="reportttl"  >' . "---"  . '</td>';
	}
	if ($windyear[$y][3] > 0){
		$max = conv($yeardmax[$y],4,0);
		echo '<td class=" ' . ValueColor2($max,$windvalues).'"' . '>' . sprintf($places,$max) .'</td>';
	}
	else {
		echo '<td class="reportttl"  >' . "---"  . '</td>';
	}
	echo '</tr>';
}
// Max of like months
	echo '<tr class="reportttl2"><td class="reportttl">Max</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		$max = conv($monthavmax[$i],4,0);	echo '<td class=" ' . ValueColor($max,$windvalues).'"' . '>' . sprintf($places,($max)) .' </td>';
	}
	$max = conv(max($monthavmax),4,0); echo '<td class=" ' . ValueColor2($max,$windvalues).'"' . '>' . sprintf($places,($max)) . ' </td></tr>';

	echo '<tr><td class="separator" colspan="14" >&nbsp;</td></tr>';
	
	echo '<tr><th class="tableheading" colspan="14">Lowest Daily Mean Speed (Calmest Day)</th></tr>';		//  Lowest Daily Wind Mean
	echo '<tr><th class="labels" width="7%">Date</th>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		echo '<th class="labels" width="7%">' . substr( $mnthname[$i], 0, 3 ) . '</th>';
	}
	echo '<th class="labels" width="7%">Year</th>';
	echo '</tr>';
for ($y = 0; $y < $years; $y++)  {
	echo '<tr><td class="reportttl">', $year-$y, '</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		 if ($windmonth[$y][$i][3] > 0 && !(($y == $years-1 && $i < 7)) && !($y == $years-2 && ($i < 7 && $i > 2))) {
				echo '<td class=" ' . ValueColor(conv($windmonth[$y][$i][7],4,0),$windvalues).'"' . '>' . sprintf($places,conv($windmonth[$y][$i][7],4,0)) .' </td>';
			}
		 else
			echo '<td class="reportttl"  >' . "---"  . '</td>';
	}
	if ($windyear[$y][3] > 0){
		$max = conv($yeardmin[$y],4,0); echo '<td class=" ' . ValueColor2($max,$windvalues).'"' . '>' . sprintf($places,$max) .'</td>';
	}
	else {
		echo '<td class="reportttl"  >' . "---"  . '</td>';
	}
	echo '</tr>';
}
// Min of like months
	echo '<tr class="reportttl2"><td class="reportttl">Min</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		$min = conv($monthavmin[$i],4,0);	echo '<td class=" ' . ValueColor($min,$windvalues).'"' . '>' . sprintf($places,($min)) .' </td>';
	}
	$min = conv(min($monthavmin),4,0); echo '<td class=" ' . ValueColor2($min,$windvalues).'"' . '>' . sprintf($places,($min)) . ' </td></tr>';

	echo '<tr><td class="separator" colspan="14" >&nbsp;</td></tr>';					// Average Wind
	echo '<tr><th class="tableheading" colspan="14">Average Wind Speed</th></tr>';
	echo '<tr><th class="labels" width="7%">Date</th>';
		for ( $i = 0 ; $i < 12 ; $i++ ) {
			echo '<th class="labels" width="7%">' . substr( $mnthname[$i], 0, 3 ) . '</th>';
		}
	echo '<th class="labels" width="7%">Year</th></tr>';
	
for ($y = 0; $y < $years; $y++)  {
	echo '<tr><td class="reportttl">', $year-$y, '</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		 if ($windmonth[$y][$i][1] > 0 && !(($y == $years-1 && $i < 7)) && !($y == $years-2 && ($i < 7 && $i > 2))) {
				$cur_wind = conv($windmonth[$y][$i][0] / $windmonth[$y][$i][1],4,0);
				echo '<td class=" ' . ValueColor($cur_wind,$windvalues).'">'.sprintf($places,$cur_wind). '<br />('. sprintf('%+.1f',$cur_wind - conv($anom[$i],4,0)).')</td>';
			}
		 else
				echo '<td class="reportttl"  >' . "---"  . '</td>';
		}
				$days_of_data = $windyear[$y][1];
				if ($days_of_data > 0 && $y != $years-1 && $y != $years-2) {
					$cur_wind = conv($windyear[$y][0] / $windyear[$y][1],4,0);
					echo '<td class=" ' . ValueColor2($cur_wind,$windvalues).'">'.sprintf($places,$cur_wind).'<br />('. sprintf('%+.1f',$cur_wind - conv(array_sum($anom)/12,4,0)).')</td>';
				}
				else {
					echo '<td class="reportttl"  >' . "N/A"  . '</td>';
					}
	echo '</tr>';
}
// Monthly averages
	echo '<tr class="windttl2"><td class="reportttl">Avg</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
	if ($like_months[$i][1]==0) {
		echo '<td class="reportttl"  >' . "---"  . '</td>';
	} else {
	$avg = conv($like_months[$i][0] / $like_months[$i][1],4,0);
	echo '<td class=" ' . ValueColor($avg,$windvalues).'"' . '>' . sprintf($places,($avg)) .' </td>';
	}
	}
	$all_year_avg = conv($allyears[0] / $allyears[1],4,0);
	echo '<td class=" ' . ValueColor2($all_year_avg,$windvalues).'"' . '>' . sprintf($places,($all_year_avg)) . ' </td>';
	echo '</tr>';

$colorband_rows = ceil($colors/15);
$colorband_cols = ceil(($colors+1)/$colorband_rows);

	echo '</table><table><tr><td width="7%" class="separator" colspan="'.($colorband_cols).'" >&nbsp;</td></tr>'; 	//Colour Key
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
	echo '<td width="7%" class="beaufort'.($band+2).'"'.$color_text.' >'. '&gt;'. $windvalues[$i-1] . '</td></tr></table>';
}

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
?>