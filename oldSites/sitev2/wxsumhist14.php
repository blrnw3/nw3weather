<?php require('unit-select.php'); ?>
<?php
@include_once("wxreport-settings.php");
// Obtains Server's Self and protect it against XSS injection
$SITE 			= array();
$SITE['self'] = htmlentities( substr($_SERVER['PHP_SELF'], 0,
	strcspn( $_SERVER['PHP_SELF'] , "\n\r") ), ENT_QUOTES );
################################################################
$summary = 1;
$temprange_start = array(20, -5); # (Farenheit, Celcius)
$temprange_increment = array(10, 5); # (Farenheit, Celcius)
$increments = 8;
$set_values_manually = false;
$manual_values = array(.25, .5, 1, 2, 3,6,12,18,24,36,60,75);
$loc = $path_dailynoaa;
$first_year_of_data = $first_year_of_noaadata;
$uomTemp = '&deg;'.$unitT;
$table_order = array("Low", "High", "LowMax", "HighMin", "LowM", "HighM", "Mean", "Avg Low", "Avg High");
$temptype = array("C","F") ;
$temptype = $temptype[$uomTemp == "&deg;F"];
$temprange_start = $temprange_start[((strtoupper($temptype)) != "F")] ;
$temprange_increment = $temprange_increment[((strtoupper($temptype)) != "F")] ;
$tempvalues = array($temprange_start);
for ( $i = 0; $i < $increments ; $i ++ ) {
	$tempvalues[$i+1] = $tempvalues[$i] + $temprange_increment;
}
$temprounding = "%01.1f";
if ($set_values_manually == true){
	$tempvalues = $manual_values;
	$increments = (count($manual_values))-1;
}
$colors = $increments + 1;

// If first day of first month of the year, use previous year
$year = date("Y");
if ($show_today != true){
	if (( date("n") == 1) AND date("j") == 1) {
	$year = $year -1;
	}
}
$setcolor = "background-color:";
$years = 1 + ($year - $first_year_of_data);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("phptags.php");
	$file = 54; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title>NW3 Weather - Old(v2) - Historical Temperature Summary</title>

	<meta name="description" content="Old v2 - Detailed historical monthly/annual temperature summary report from NW3 weather; on a month by month basis with annual summary/extremes.
	Find warmest and coldest days of every month and each year on record, as well as mean temperatures with anomalies (departure from climate average);
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
	<div id="report">
		<h1>Temperature Summary (<?php echo $uomTemp; ?>)</h1>
		
		<?php
			$self = $tempsummaryfile_name;
			include("wxrepgen.php");
			get_temp_detail($first_year_of_data,$year,$years,$loc, $setcolor, $round);
		?>
	</div>

	<p><b>Note 1:</b> Figures in brackets refer to departure from <a href="wxaverages.php" title="Long-term NW3 climate averages">average conditions</a><br />
	<b>Note 2:</b> Mean values are calculated from thousands of 1-minute-spaced values, so may differ from (mean max + mean min)/2.</p>
</div><!-- end main-copy -->

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>
 </body>
</html>

<?php
function get_temp_detail ($first_year_of_data,$year, $years, $loc, $setcolor, $round) {
	global $SITE, $tempvalues, $temptype, $table_order, $uomTemp, $colors, $temprounding;
	global $show_today, $maxtemp, $mintemp, $avtempsincemidnight, $mnthname, $monthtodateavtemp;

	$places = "%01.1f";
	$places_diff = 1;
	$anom = array(4.7, 4.9, 7, 8.9, 12.5, 15.65, 17.9, 17.7, 14.9, 11.7, 7.6, 5.5);
	$anoml = array(2.4,2.2,3.6,5.0,8.3,11.1,13.4,13.2,11.1,8.8,5.0,3.2);
	$anomh = array(7.0,7.6,10.4,12.8,16.7,20.2,22.4,22.2,18.7,14.6,10.2,7.8);
	
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
				$raw[$y][1][$m][1][0][1] = strip_units($avtempsincemidnight);
				$raw[$y][1][$m][1][0][2] = strip_units($maxtemp);
				$raw[$y][1][$m][1][0][4] = strip_units($mintemp);
			} elseif (file_exists($loc . $filename) ) {
				$raw[$y][1][$m][1] = getnoaafile($loc . $filename);
			}
			if ($current_month AND $show_today){
				$raw[$y][1][$m][1][date("j")-1][1] = strip_units($avtempsincemidnight);
				$raw[$y][1][$m][1][date("j")-1][2] = strip_units($maxtemp);
				$raw[$y][1][$m][1][date("j")-1][4] = strip_units($mintemp);
					
				}
			}
		}

	// Output Table with information
	echo '<table>';
	$tempmonth = array();
	$tempyear = array();
	$monthmin = array(200,200,200,200,200,200,200,200,200,200,200,200);
	$minmean = array(200,200,200,200,200,200,200,200,200,200,200,200);
	$maxmean = array(-200,-200,-200,-200,-200,-200,-200,-200,-200,-200,-200,-200);
	$monthmax = array(-200,-200,-200,-200,-200,-200,-200,-200,-200,-200,-200,-200);
	$monthhighmin = array (-100, -100, -100, -100, -100, -100, -100, -100, -100, -100, -100, -100);
	$monthlowmax = array (100, 100, 100, 100, 100, 100, 100, 100, 100, 100, 100, 100);
	$ytd = 0;

	for ($yx = 0 ; $yx < $years ; $yx++ ) {
		// Display each years values for that month
			$tempyear[$yx][6] = -100; // Initial High max setting
			$tempyear[$yx][7] = 100; // Initial Low min setting
			$tempyear[$yx][8] = 100; // Initial low max setting
			$tempyear[$yx][9] = -100; // Initial high min setting
			$tempyear[$yx][10] = -100; // Initial low max setting
			$tempyear[$yx][11] = 100; // Initial high min setting
		for ($mnt = 0 ; $mnt < 12 ; $mnt++ ) {
			$tempmonth[$yx][$mnt][6] = -100; // Initial High Max setting
			$tempmonth[$yx][$mnt][7] = 100; // Initial Low Min setting
			$tempmonth[$yx][$mnt][8] = 100; // Initial Lowest Max setting
			$tempmonth[$yx][$mnt][9] = -100; // Initial Highest Min setting
			$tempmonth[$yx][$mnt][10] = -100; // Initial Highest Mean setting
			$tempmonth[$yx][$mnt][11] = 100; // Initial Lowest Mean setting
			
			for ($day = 0 ; $day < 31 ; $day++ ){
				$temp = $raw[$yx][1][$mnt][1][$day][2];			// Highest Max
				if ( $temp != "" AND $temp != "-----" AND $temp != "---" AND $temp != "X") {
					$tempmonth[$yx][$mnt][0] = $tempmonth[$yx][$mnt][0] + $temp;
					$tempmonth[$yx][$mnt][1] = $tempmonth[$yx][$mnt][1] + 1;
					if ($tempmonth[$yx][$mnt][6] < $temp) {	$tempmonth[$yx][$mnt][6] = $temp; }
					if ($temp > $monthmax[$mnt]) { $monthmax[$mnt] = $temp; }
				}

				$temp = $raw[$yx][1][$mnt][1][$day][4]; 		// Lowest Min
				if ( $temp != "" AND $temp != "-----" AND $temp != "---" AND $temp != "X") {
					$tempmonth[$yx][$mnt][2] = $tempmonth[$yx][$mnt][2] + $temp;
					$tempmonth[$yx][$mnt][3] = $tempmonth[$yx][$mnt][3] + 1;
						if ($tempmonth[$yx][$mnt][7] > $temp) {	$tempmonth[$yx][$mnt][7] = $temp; }
						if ($temp < $monthmin[$mnt]) { $monthmin[$mnt] = $temp; }				
				}

				$temp = $raw[$yx][1][$mnt][1][$day][2];			// Lowest Max
				if ( $temp != "" AND $temp != "-----" AND $temp != "---" AND $temp != "X") {
					$tempmonth[$yx][$mnt][0] = $tempmonth[$yx][$mnt][0] + $temp;
					$tempmonth[$yx][$mnt][1] = $tempmonth[$yx][$mnt][1] + 1;
					if ($tempmonth[$yx][$mnt][8] > $temp) {	$tempmonth[$yx][$mnt][8] = $temp; }
						if ($temp < $monthlowmax[$mnt]) { $monthlowmax[$mnt] = $temp; }
				}

				$temp = $raw[$yx][1][$mnt][1][$day][4]; 		// Highest Min
				if ( $temp != "" AND $temp != "-----" AND $temp != "---" AND $temp != "X") {
					$tempmonth[$yx][$mnt][2] = $tempmonth[$yx][$mnt][2] + $temp;
					$tempmonth[$yx][$mnt][3] = $tempmonth[$yx][$mnt][3] + 1;
						if ($tempmonth[$yx][$mnt][9] < $temp) {	$tempmonth[$yx][$mnt][9] = $temp; }
						if ($temp > $monthhighmin[$mnt]) { $monthhighmin[$mnt] = $temp; }				
				}

				$temp = $raw[$yx][1][$mnt][1][$day][1]; 		// All Mean Data
				if ( $temp != "" AND $temp != "-----" AND $temp != "---" AND $temp != "X") {
					$tempmonth[$yx][$mnt][4] = $tempmonth[$yx][$mnt][4] + $temp;
					$tempmonth[$yx][$mnt][5] = $tempmonth[$yx][$mnt][5] + 1;
					if ($tempmonth[$yx][$mnt][10] < $temp) { $tempmonth[$yx][$mnt][10] = $temp;	}
					if ($tempmonth[$yx][$mnt][11] > $temp) { $tempmonth[$yx][$mnt][11] = $temp;	}
					if ($temp > $maxmean[$mnt]) { $maxmean[$mnt] = $temp; }
					if ($temp < $minmean[$mnt]) { $minmean[$mnt] = $temp; }
				}
			}

			for ($i = 0 ; $i < 6 ; $i++ ){ 	$tempyear[$yx][$i] = $tempyear[$yx][$i] + $tempmonth[$yx][$mnt][$i]; }
			if ($tempyear[$yx][6] < $tempmonth[$yx][$mnt][6]) {	$tempyear[$yx][6] = $tempmonth[$yx][$mnt][6]; }
			if ($tempyear[$yx][7] > $tempmonth[$yx][$mnt][7]) {	$tempyear[$yx][7] = $tempmonth[$yx][$mnt][7]; }
			if ($tempyear[$yx][8] > $tempmonth[$yx][$mnt][8]) {	$tempyear[$yx][8] = $tempmonth[$yx][$mnt][8]; }
			if ($tempyear[$yx][9] < $tempmonth[$yx][$mnt][9]) {	$tempyear[$yx][9] = $tempmonth[$yx][$mnt][9]; }
			if ($tempyear[$yx][10] < $tempmonth[$yx][$mnt][10]) { $tempyear[$yx][10] = $tempmonth[$yx][$mnt][10]; }
			if ($tempyear[$yx][11] > $tempmonth[$yx][$mnt][11]) { $tempyear[$yx][11] = $tempmonth[$yx][$mnt][11]; }
			$temp_month[$mnt][0] = $temp_month[$mnt][0] + $tempmonth[$yx][$mnt][0]; // High Temp
			$temp_month[$mnt][1] = $temp_month[$mnt][1] + $tempmonth[$yx][$mnt][1]; // Hight Temp days
			$temp_month[$mnt][2] = $temp_month[$mnt][2] + $tempmonth[$yx][$mnt][2]; // Low Temp
			$temp_month[$mnt][3] = $temp_month[$mnt][3] + $tempmonth[$yx][$mnt][3]; // Low Temp days
			$temp_month[$mnt][4] = $temp_month[$mnt][4] + $tempmonth[$yx][$mnt][4]; // Mean Temp
			$temp_month[$mnt][5] = $temp_month[$mnt][5] + $tempmonth[$yx][$mnt][5]; // Mean Temp days
		}
	}

for ($table = 0; $table < 9; $table++) {
	if ($table_order[$table] == "High") {							// Highest Maxima
	echo '<tr><td class="separator" colspan="14" >&nbsp;</td></tr>';
	echo '<tr><th class="tableheading" colspan="14">Highest Maxima</th></tr>';
	echo '<tr><th class="labels" width="7%">Date</th>';
		for ( $i = 0 ; $i < 12 ; $i++ ) {
			echo '<th class="labels" width="7%">' . substr( $mnthname[$i], 0, 3 ) . '</th>';
		}
	echo '<th class="labels2" width="7%">Year</th></tr>';
for ($y = 0; $y < $years; $y++) {
	echo '<tr><td class="reportttl">',  $year-$y,  '</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($tempmonth[$y][$i][1] > 0) {
			$max = conv($tempmonth[$y][$i][6],1,0);
			echo '<td class=" ' . ValueColor($max).'"' . '>' . sprintf($places,($max)).' </td>';
		}
		else
			echo '<td class="reportttl" >' . "---" . '</td>';
	}
	echo '<td class=" ' . ValueColor3(conv($tempyear[$y][6],1,0)).'"' . '>' . conv($tempyear[$y][6],1,0).'</td></tr>';
}
// Now display the max of like months
	echo '<tr class="reportttl2" ><td class="reportttl">Max</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
	if ($monthmax[$i]== "" OR $monthmax[$i] == -200) {
		echo '<td class="reportttl" >' . "---" . '</td>';
	} else {
	$max = conv($monthmax[$i],1,0);
	echo '<td class=" ' . ValueColor($max).'"' . '>' . sprintf($places,($max)) .' </td>';
	}
	}
	$max = conv(max($monthmax),1,0);
	echo '<td class=" ' . ValueColor3($max).'"' . '>' . sprintf($places,($max)) . ' </td></tr>';
	}
	
	elseif ($table_order[$table] == "HighM") {							// Highest Means
	echo '<tr><td class="separator" colspan="14" >&nbsp;</td></tr>';
	echo '<tr><th class="tableheading" colspan="14">Highest Means</th></tr>';
	echo '<tr><th class="labels" width="7%">Date</th>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		echo '<th class="labels" width="7%">' . substr( $mnthname[$i], 0, 3 ) . '</th>';
	}
	echo '<th class="labels2" width="7%">Year</th></tr>';
for ($y = 0; $y < $years; $y++) {
	echo '<tr><td class="reportttl">', $year-$y, '</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($tempmonth[$y][$i][1] > 0) {
				$max = conv($tempmonth[$y][$i][10],1,0);
				echo '<td class=" ' . ValueColor($max).'"' . '>' . sprintf($places,($max)).' </td>';
		}
		else
			echo '<td class="reportttl" >' . "---" . '</td>';
	}
	echo '<td class=" ' . ValueColor3(conv($tempyear[$y][10],1,0)).'"' . '>' . conv($tempyear[$y][10],1,0).'</td></tr>';
}
// Max of like months
	echo '<tr class="reportttl2" ><td class="reportttl">Max</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
	if ($monthmax[$i]== "" OR $monthmax[$i] == -200) {
		echo '<td class="reportttl" >' . "---" . '</td>';
	} else {
	$max = conv($maxmean[$i],1,0);
	echo '<td class=" ' . ValueColor($max).'"' . '>' . sprintf($places,($max)) .' </td>';
	}
	}
	$max = conv(max($maxmean),1,0);
	echo '<td class=" ' . ValueColor3($max).'"' . '>' . sprintf($places,($max)) . ' </td></tr>';
	}
	
	elseif ($table_order[$table] == "LowM") {							// Lowest Means
	echo '<tr><td class="separator" colspan="14" >&nbsp;</td></tr>';
	echo '<tr><th class="tableheading" colspan="14">Lowest Means</th></tr>';
	echo '<tr><th class="labels" width="7%">Date</th>';
		for ( $i = 0 ; $i < 12 ; $i++ ) {
			echo '<th class="labels" width="7%">' . substr( $mnthname[$i], 0, 3 ) . '</th>';
		}
	echo '<th class="labels2" width="7%">Year</th>';
	echo '</tr>';
for ($y = 0; $y < $years; $y++) {
	echo '<tr><td class="reportttl">', $year-$y, '</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($tempmonth[$y][$i][1] > 0) {
			$max = conv($tempmonth[$y][$i][11],1,0);
			echo '<td class=" ' . ValueColor($max).'"' . '>' . sprintf($places,($max)).' </td>';
		}
		else
			echo '<td class="reportttl" >' . "---" . '</td>';
	}
	echo '<td class=" ' . ValueColor3(conv($tempyear[$y][11],1,0)).'"' . '>' . conv($tempyear[$y][11],1,0).'</td></tr>';
}
// Min of like months
	echo '<tr class="reportttl2" ><td class="reportttl">Min</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
	if ($monthmax[$i]== "" OR $monthmax[$i] == -200) {
		echo '<td class="reportttl" >' . "---" . '</td>';
	} else {
	$max = conv($minmean[$i],1,0);
	echo '<td class=" ' . ValueColor($max).'"' . '>' . sprintf($places,($max)) .' </td>';
	}
	}
	$max = conv(min($minmean),1,0);
	echo '<td class=" ' . ValueColor3($max).'"' . '>' . sprintf($places,($max)) . ' </td></tr>';
	}
	
	elseif ($table_order[$table] == "LowMax") {							// Lowest Maxima
	echo '<tr><td class="separator" colspan="14" >&nbsp;</td></tr>';
	echo '<tr><th class="tableheading" colspan="14">Lowest Maxima</th></tr>' ;
	echo '<tr><th class="labels" width="7%">Date</th>' ;
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		echo '<th class="labels" width="7%">' . substr( $mnthname[$i], 0, 3 ) . '</th>';
	}
	echo '<th class="labels2" width="7%">Year</th></tr>';
for ($y = 0; $y < $years; $y++) {
	echo '<tr><td class="reportttl">', $year-$y, '</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($tempmonth[$y][$i][1] > 0) {
			$max = conv($tempmonth[$y][$i][8],1,0);
			echo '<td class=" ' . ValueColor($max).'"' . '>' . sprintf($places,($max)) .' </td>';
		}
		else
			echo '<td class="reportttl" >' . "---" . '</td>';
		}
	echo '<td class=" ' . ValueColor3(conv($tempyear[$y][8],1,0)).'"' . '>' . conv($tempyear[$y][8],1,0) .'</td></tr>';
}
// Max of like months
	echo '<tr class="reportttl2" ><td class="reportttl">Min</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
	if ($monthlowmax[$i]== "" OR $monthlowmax[$i] == 100) {
		echo '<td class="reportttl" >' . "---" . '</td>';
	} else {
	$max = conv($monthlowmax[$i],1,0);
	echo '<td class=" ' . ValueColor($max).'"' . '>' . sprintf($places,($max)) .' </td>';
	}
	}
	$max = conv(min($monthlowmax),1,0);
	echo '<td class=" ' . ValueColor3($max).'"' . '>' . sprintf($places,($max)) . ' </td></tr>';
	}	
	
	elseif ($table_order[$table] == "Avg High") { 							// Mean Maxima
	echo '<tr><td class="separator" colspan="14" >&nbsp;</td></tr>';
	echo '<tr><th class="tableheading" colspan="14">Mean Maxima</th></tr>' ;
	echo '<tr><th class="labels">Date</th>' ;

		for ( $i = 0 ; $i < 12 ; $i++ )
		{
			echo '<th class="labels">' . substr( $mnthname[$i], 0, 3 ) . '</th>';
		}
	echo '<th class="labels2"> Year </th>';
	echo '</tr>';
for ($y = 0; $y < $years; $y++) {
			$days_of_data = $tempyear[$y][1];
			if ($days_of_data > 0){ // if no data for year, skip to next year
	echo '<tr><td class="reportttl">',  $year-$y,  '</td>';
	$year_days = $year_temp = 0;
	for ( $i = 0 ; $i < 12 ; $i++ ) {
	$year_temp = $year_temp + $temp_month[$i][0];
	$year_days = $year_days + $temp_month[$i][1];
	}
	$year_avg = conv($year_temp / $year_days,1,0);
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($tempmonth[$y][$i][1] > 0 AND $tempmonth[$y][$i][0] !="" ) {
				$avg = conv(($tempmonth[$y][$i][0] / $tempmonth[$y][$i][1]),1,0);
				$avg_all = round(($temp_month[$i][0] / $temp_month[$i][1]),1);
				echo '<td class=" ' . ValueColor($avg).'"' . '>' . sprintf($places,($avg)). '<br />('. sprintf('%+.1f',$avg-conv($anomh[$i],1,0)). ')</td>';
			}
		else
			echo '<td class="reportttl" >' . "---" . '</td>';
		}
		if ($tempyear[$y][1] > 0 AND $tempmonth[$y][1] !="" ) {
			$avg = conv(($tempyear[$y][0] / $tempyear[$y][1]),1,0);
			$avg_all = round($year_avg,1) ;
	echo '<td class=" ' . ValueColor3($avg).'"' . '>' . sprintf($places,$avg). '<br /> ('. sprintf('%+.1f',($avg - conv(array_sum($anomh)/12,1,0))). ')</td>';
		}
		else {
			echo '<td class="reportttl" >' . "---" . '</td>';
		}
	echo '</tr>';
}
}
// Average high temp
	echo '<tr class="reportttl2"><td class="reportttl">Mean</td>';
	$year_days = $year_temp = 0;
	for ( $i = 0 ; $i < 12 ; $i++ ) {
	if ($temp_month[$i][1]==0) {
		echo '<td class="reportttl" >' . "---" . '</td>';
	} else {
	$avg = conv($temp_month[$i][0] / $temp_month[$i][1],1,0);
	echo '<td class=" ' . ValueColor($avg).'"' . '>' . sprintf($places,($avg)) .' </td>';
	}
	}
	echo '<td class=" ' . ValueColor3($year_avg).'"' . '>' . sprintf($places,($year_avg)) . ' </td></tr>';
	}

	elseif ($table_order[$table] == "Avg Low") { 									// Mean Minima
for ($y = 0; $y < $years; $y++) {
echo '<tr><td class="separator" colspan="14" >&nbsp;</td></tr>';
	echo '<tr><th class="tableheading" colspan="14">Mean Minima</th></tr>' ;
	echo '<tr><th class="labels">Date</th>' ;
		for ( $i = 0 ; $i < 12 ; $i++ ) {
			echo '<th class="labels">' . substr( $mnthname[$i], 0, 3 ) . '</th>';
		}
	echo '<th class="labels2"> Year </th></tr>';
for ($y = 0; $y < $years; $y++) {
	$days_of_data = $tempyear[$y][3];
	if ($days_of_data > 0){ // if no data for year, skip to next year
	echo '<tr><td class="reportttl">',  $year-$y,  '</td>';
	$year_days = $year_temp = 0;
	for ( $i = 0 ; $i < 12 ; $i++ ) {
	$year_temp = $year_temp + $temp_month[$i][2];
	$year_days = $year_days + $temp_month[$i][3];
	}
	$year_avg = conv($year_temp / $year_days,1,0);
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($tempmonth[$y][$i][3] > 0 AND $tempmonth[$y][$i][3] !="" ) {
				$avg = conv(($tempmonth[$y][$i][2] / $tempmonth[$y][$i][3]),1,0);
				$avg_all = round(($temp_month[$i][2] / $temp_month[$i][3]),1);
				echo '<td class=" ' . ValueColor($avg).'"' . '>' . sprintf($places,($avg)). '<br />('. sprintf('%+.1f',$avg-conv($anoml[$i],1,0)). ')</td>';
			}
		else
				echo '<td class="reportttl" >' . "---" . '</td>';
	}
		if ($tempyear[$y][3] > 0 AND $tempmonth[$y][3] !="" ) {
			$avg = conv(($tempyear[$y][2] / $tempyear[$y][3]),1,0);
			$avg_all = round($year_avg,1) ;
			echo '<td class=" ' . ValueColor3($avg).'"' . '>' . sprintf($places,$avg). '<br /> ('. sprintf('%+.1f',($avg - conv(array_sum($anoml)/12,1,0))). ')</td>';
		}
		else {
			echo '<td class="reportttl" >' . "---" . '</td>';
		}
	echo '</tr>';
}
}
}
//Average low temp
	echo '<tr class="reportttl2" ><td class="reportttl">Mean</td>';
	$year_days = $year_temp = 0;
	for ( $i = 0 ; $i < 12 ; $i++ ) {
	if ($temp_month[$i][3]==0) {
		echo '<td class="reportttl" >' . "---" . '</td>';
	} else {
	$avg = conv(($temp_month[$i][2] / $temp_month[$i][3]),1,0);
	echo '<td class=" ' . ValueColor($avg).'"' . '>' . sprintf($places,($avg)) .' </td>';
	}
	}
	echo '<td class=" ' . ValueColor3($year_avg).'"' . '>' . sprintf($places,($year_avg)) . ' </td></tr>';
}	
	elseif ($table_order[$table] == "Mean") { 								// Mean Temperature
	echo '<tr><td class="separator" colspan="14" ><a name="means">&nbsp;</a></td></tr>';
	echo '<tr><td class="separator" colspan="14" >&nbsp;</td></tr>';
	echo '<tr><td class="title" colspan="14" >Monthly Means</td></tr>';
	echo '<tr><td class="separator" colspan="14" >&nbsp;</td></tr>';
	echo '<tr><th class="tableheading" colspan="14">Overall Means</th></tr>' ;
	echo '<tr><th class="labels">Date</th>' ;

		for ( $i = 0 ; $i < 12 ; $i++ ) {
			echo '<th class="labels">' . substr( $mnthname[$i], 0, 3 ) . '</th>
				';
		}
	echo '<th class="labels2"> Year </th></tr>';
for ($y = 0; $y < $years; $y++) {
	$days_of_data = $tempyear[$y][5];
	if ($days_of_data > 0){ // if no data for year, skip to next year
	echo '<tr><td class="reportttl">',  $year-$y,  '</td>';
	$year_days = $year_temp = 0;
	for ( $i = 0 ; $i < 12 ; $i++ ) {
	$year_temp = $year_temp + $temp_month[$i][4];
	$year_days = $year_days + $temp_month[$i][5];
	}
	$year_avg = conv($year_temp / $year_days,1,0);
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($tempmonth[$y][$i][5] > 0 AND $tempmonth[$y][$i][5] !="" ) {
			$avg = ($tempmonth[$y][$i][4] / $tempmonth[$y][$i][5]); if($i+1 == date('n') && $y == 0) { $avg = $monthtodateavtemp; }
			echo '<td class=" ' . ValueColor(conv($avg,1,0)).'"' . '>' . conv($avg,1,0). '<br /> ('. conv2($avg-$anom[$i],1,0). ')</td>
				';
		}	
		else
			echo '<td class="reportttl" >' . "---" . '</td>
				';
	}
		if ($tempyear[$y][5] > 0 AND $tempmonth[$y][5] !="" ) {
			$avgt = ($tempyear[$y][4] / $tempyear[$y][5]);
			echo '<td class=" ' . ValueColor3(conv($avgt,1,0)).'"' . '>' . conv($avgt,1,0). '<br /> ('. conv2($avgt-array_sum($anom)/12,1,0). ')</td>
				';
		}
		else {
				echo '<td class="reportttl" >' . "---" . '</td>';
		}
	echo '</tr>';
}
}
//Summary/Extremes
	echo '<tr class="reportttl2" ><td class="reportttl">Min</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		for ( $y = 0 ; $y < $years ; $y++ ) {
			$tvah[$i][$y] = $tempmonth[$y][$i][4] / $tempmonth[$y][$i][5];
			if(floatval($tvah[$i][$y]) == 0) { $tvah[$i][$y] = 200; }
		}
		$minmean2[$i] = conv(min($tvah[$i]),1,0);
		echo '<td class=" ' . ValueColor($minmean2[$i]).'"' . '>' . sprintf($places,$minmean2[$i]) .'</td>
			';
	}
	$mmmin = min($minmean2);
	echo '<td class=" ' . ValueColor3($mmmin).'"' . '>' . sprintf($places,$mmmin) . ' </td></tr>'; //F screw-up
	
	echo '<tr><td class="reportttl">Max</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		for ( $y = 0 ; $y < $years ; $y++ ) {
			$tvah[$i][$y] = $tempmonth[$y][$i][4] / $tempmonth[$y][$i][5];
			if(floatval($tvah[$i][$y]) == 0) { $tvah[$i][$y] = -200; }
		}
		$maxmean2[$i] = conv(max($tvah[$i]),1,0);
		echo '<td class=" ' . ValueColor($maxmean2[$i]).'"' . '>' . sprintf($places,$maxmean2[$i]) .'</td>
			';
	}
	$mmmax = max($maxmean2);
	echo '<td class=" ' . ValueColor3($mmmax).'"' . '>' . sprintf($places,$mmmax) . ' </td></tr>
		';
	
	echo '<tr><td class="reportttl">Mean</td>';
	$year_days = $year_temp = 0;
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		$avg2 = conv($temp_month[$i][4] / $temp_month[$i][5],1,0);
		echo '<td class=" ' . ValueColor($avg2).'"' . '>' . sprintf($places,($avg2)) .'</td>
			';
	}
	echo '<td class=" ' . ValueColor3($year_avg).'"' . '>' . sprintf($places,($year_avg)) . ' </td></tr>
		';
	}

elseif($table_order[$table] == "Low") {
	echo '<tr><td class="title" colspan="14" >Monthly Extremes</td></tr>';					// Lowest Minima
	echo '<tr><td class="separator" colspan="14" >&nbsp;</td></tr>';
	echo '<tr><th class="tableheading" colspan="14">Lowest Minima</th></tr>' ;
	echo '<tr><th class="labels">Date</th>' ;
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		echo '<th class="labels">' . substr( $mnthname[$i], 0, 3 ) . '</th>
			';
	}
	echo '<th class="labels2"> Year </th></tr>';
for ($y = 0; $y < $years; $y++) {
	echo '<tr><td class="reportttl">',  $year-$y,  '</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($tempmonth[$y][$i][1] > 0) {
			$min = conv($tempmonth[$y][$i][7],1,0);
			echo '<td class=" ' . ValueColor($min).'"' . '>' . sprintf($places,($min)).' </td>
				';
		}
		else
			echo '<td class="reportttl" >' . "---" . '</td>';
	}
	echo '<td class=" ' . ValueColor3(conv($tempyear[$y][7],1,0)).'"' . '>' . conv($tempyear[$y][7],1,0) .'</td></tr>
		';
}
// Min of like months
	echo '<tr class="reportttl2" ><td class="reportttl">Min</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
	if ($monthmin[$i]== "" OR $monthmin[$i] == 200) {
		echo '<td class="reportttl" >' . "---" . '</td>';
	} else {
	$min = conv($monthmin[$i],1,0);
	echo '<td class=" ' . ValueColor($min).'"' . '>' . sprintf($places,($min)) .' </td>
		';
	}
	}
	$min = conv(min($monthmin),1,0);
	echo '<td class=" ' . ValueColor3($min).'"' . '>' . sprintf($places,($min)) . ' </td></tr>
		';
}
else { 																				// Highest Minima
	echo '<tr><td class="separator" colspan="14" >&nbsp;</td></tr>';
	echo '<tr><th class="tableheading" colspan="14">Highest Minima</th></tr>' ;
	echo '<tr><th class="labels">Date</th>' ;
		for ( $i = 0 ; $i < 12 ; $i++ ) {
			echo '<th class="labels">' . substr( $mnthname[$i], 0, 3 ) . '</th>
				';
		}
	echo '<th class="labels2"> Year </th>';
	echo '</tr>';
for ($y = 0; $y < $years; $y++) {
	echo '<tr><td class="reportttl">',  $year-$y,  '</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($tempmonth[$y][$i][1] > 0) {
			$min = conv($tempmonth[$y][$i][9],1,0);
			echo '<td class=" ' . ValueColor($min).'"' . '>' . sprintf($places,($min)).' </td>';
		}
		else
			echo '<td class="reportttl" >' . "---" . '</td>';
	}

	echo '<td class=" ' . ValueColor3(conv($tempyear[$y][9],1,0)).'"' . '>' . conv($tempyear[$y][9],1,0) .'</td></tr>';
}
// Min of like months
	echo '<tr class="reportttl2" ><td class="reportttl">Max</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
	if ($monthhighmin[$i]== "" OR $monthhighmin[$i] == -100) {
		echo '<td class="reportttl" >' . "---" . '</td>';
	} else {
	$min = conv($monthhighmin[$i],1,0);
	echo '<td class=" ' . ValueColor($min).'"' . '>' . sprintf($places,($min)) .' </td>';
	}
	}
	$min = conv(max($monthhighmin),1,0);
	echo '<td class=" ' . ValueColor3($min).'"' . '>' . sprintf($places,($min)) . ' </td></tr>';
}
}

$colorband_rows = ceil($colors/15);
$colorband_cols = ceil(($colors+1)/$colorband_rows);
	echo '</table><table><tr><td class="separator" colspan="'.($colorband_cols).'" >&nbsp;</td></tr>';
	echo '<tr><td class="colorband" colspan="'.($colorband_cols).'">Colour Key</td></tr>';
	$i = 0;
	for ($r = 0; $r < $colorband_rows; $r ++){
		for ( $j = 0; (($j < $colorband_cols) AND ($i < $colors)) ; $j ++ ){
			$band = $i;
			if ($i == 0){
				echo '<tr><td class="level_1" >&lt;&nbsp;' . sprintf("%01.0f",$tempvalues[$i]) . '</td>';
			}
			else {
				echo '<td class="level_'.($band+1).'" > ' . sprintf("%01.0f",$tempvalues[$i-1]) . " - " .sprintf("%01.0f",$tempvalues[$i]) . '</td>';
				if (($j == $colorband_cols-1) AND ($r != $colorband_rows)){
					echo '</tr><tr>';
				}
			}
			$i = $i+1;
		}
	}
	echo '<td class="level_'.($band+2).'" >&gt;'. sprintf("%01.0f",$tempvalues[$i-1]) . '</td></tr></table>';
}

function ValueColor($value) {
	global $tempvalues;
	$limit = count($tempvalues);
	if ($value < $tempvalues[0]) {
	return 'level_1';
	}
	for ($i = 1; $i < $limit; $i++){
		if ($value <= $tempvalues[$i]) {
		return 'level_'.($i+1);
		}
	}
	return 'level_'.($limit+1);
}
function ValueColor3($value) {
	global $tempvalues;
	$limit = count($tempvalues);
	if ($value < $tempvalues[0]) {
	return 'level3_1';
	}
	for ($i = 1; $i < $limit; $i++){
		if ($value <= $tempvalues[$i]) {
		return 'level3_'.($i+1);
		}
	}
	return 'level3_'.($limit+1);
}
?>