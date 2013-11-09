<?php require('unit-select.php'); ?>
<?php
@include_once("wxreport-settings.php"); 
// Obtains Server's Self and protect it against XSS injection
$SITE 			= array();
$SITE['self'] = htmlentities( substr($_SERVER['PHP_SELF'], 0, 
	strcspn( $_SERVER['PHP_SELF'] , "\n\r") ), ENT_QUOTES );
############################################################################
$summary = 1;
$temprange_start = array(10, -10); # (Farenheit, Celcius)
$temprange_increment = array(10, 5); # (Farenheit, Celcius)
$increments = 8; 
$set_values_manually = false; 
$manual_values = array(.25, .5, 1, 2, 3,6,12,18,24,36,60,75);  
$loc = $path_dailynoaa; 
$first_year_of_data = $first_year_of_noaadata;
$table_order = array("Low", "High", "LowMax", "HighMin", "LowM", "HighM", "Mean", "Avg Low", "Avg High"); 
$uomTemp = '&deg;'.$unitT;
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
	$file = 50; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title>NW3 Weather - Old(v2) - Historical Dew Point Summary</title>

	<meta name="description" content="Old v2 - Detailed historical monthly/annual Dew Point summary report from NW3 weather. 
	View month-by-month summaries of dew point data; day, month and year/annual extremes for all years on record" />
	
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
		<h1>Dew Point Summary (<?php echo $uomTemp; ?>)</h1>
		
		<?php
			include("wxrepgen.php");
			get_dew_detail($first_year_of_data,$year,$years,$loc, $setcolor, $round); 
		?>
	</div>

	<p><b>Note:</b> Data collection began in Feb 2009. October 2009 values are unreliable as some days in that month are missing data. </p> 
</div><!-- end main-copy -->

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>
 </body>
</html>

<?php
function get_dew_detail ($first_year_of_data,$year, $years, $loc, $setcolor, $round) {
	global $SITE, $tempvalues, $temptype, $table_order, $uomTemp, $colors, $temprounding;
	global $show_today, $maxtemp, $mintemp, $avtempsincemidnight, $mnthname; 

	$places = "%01.1f";
	$places_diff = 1; 
	
	// Collect the data 
		for ( $y = 0; $y < $years ; $y ++ ) {
			$yx = $year - $y;	
			for ( $m = 0; $m < 12 ; $m ++ ) { 
				$filename = date('F', mktime(0,0,0,$m+1,1,$yx)) . $yx . ".htm";
				$raw[$y][1][$m][1] = gethistory($loc . $filename);
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
				$temp = $raw[$yx][1][$mnt][1][2][$day];			// Max
				if ( $temp != 0) { 
					$tempmonth[$yx][$mnt][0] = $tempmonth[$yx][$mnt][0] + $temp;
					$tempmonth[$yx][$mnt][1] = $tempmonth[$yx][$mnt][1] + 1;
					if ($tempmonth[$yx][$mnt][6] < $temp) {	$tempmonth[$yx][$mnt][6] = $temp; }
					if ($temp > $monthmax[$mnt]) { $monthmax[$mnt] = $temp; }
					if ($tempmonth[$yx][$mnt][8] > $temp) {	$tempmonth[$yx][$mnt][8] = $temp; }
					if ($temp < $monthlowmax[$mnt]) { $monthlowmax[$mnt] = $temp; }
				}

				$temp = $raw[$yx][1][$mnt][1][3][$day]; 		// Min
				if ( $temp != 0) { 
					$tempmonth[$yx][$mnt][2] = $tempmonth[$yx][$mnt][2] + $temp;
					$tempmonth[$yx][$mnt][3] = $tempmonth[$yx][$mnt][3] + 1;
					if ($tempmonth[$yx][$mnt][7] > $temp) {	$tempmonth[$yx][$mnt][7] = $temp; }
					if ($temp < $monthmin[$mnt]) { $monthmin[$mnt] = $temp; }
					if ($tempmonth[$yx][$mnt][9] < $temp) {	$tempmonth[$yx][$mnt][9] = $temp; }
					if ($temp > $monthhighmin[$mnt]) { $monthhighmin[$mnt] = $temp; }						
				}

				$temp = $raw[$yx][1][$mnt][1][4][$day]; 		// Mean
				if ( $temp != 0) { 
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
			$max = $tempmonth[$y][$i][6];
			echo '<td class=" ' . ValueColor(conv($max,1,0)).'"' . '>' . conv($max,1,0).' </td>';
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
	$max = $monthmax[$i];
	echo '<td class=" ' . ValueColor(conv($max,1,0)).'"' . '>' . conv($max,1,0) .' </td>'; 
	}
	}
	$max = max($monthmax);
	echo '<td class=" ' . ValueColor3(conv($max,1,0)).'"' . '>' . conv($max,1,0) . ' </td></tr>'; 
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
				$max = $tempmonth[$y][$i][10];
				echo '<td class=" ' . ValueColor(conv($max,1,0)).'"' . '>' . conv($max,1,0).' </td>';
		}
		else
			echo '<td class="reportttl" >' . "---" . '</td>';
	}
	echo '<td class=" ' . ValueColor3(conv(($tempyear[$y][10]),1,0)).'"' . '>' . conv($tempyear[$y][10],1,0).'</td></tr>'; 
}
// Max of like months 
	echo '<tr class="reportttl2" ><td class="reportttl">Max</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) { 
	if ($monthmax[$i]== "" OR $monthmax[$i] == -200) {
		echo '<td class="reportttl" >' . "---" . '</td>';
	} else { 
	$max = $maxmean[$i];
	echo '<td class=" ' . ValueColor(conv($max,1,0)).'"' . '>' . conv($max,1,0) .' </td>'; 
	}
	}
	$max = max($maxmean);
	echo '<td class=" ' . ValueColor3(conv($max,1,0)).'"' . '>' . conv($max,1,0) . ' </td></tr>'; 
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
			$max = $tempmonth[$y][$i][11];
			echo '<td class=" ' . ValueColor(conv($max,1,0)).'"' . '>' . conv($max,1,0).' </td>';
		}
		else
			echo '<td class="reportttl" >' . "---" . '</td>';
	}
	echo '<td class=" ' . ValueColor3(conv(($tempyear[$y][11]),1,0)).'"' . '>' . conv($tempyear[$y][11],1,0).'</td></tr>'; 
}
// Min of like months 
	echo '<tr class="reportttl2" ><td class="reportttl">Min</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) { 
	if ($monthmax[$i]== "" OR $monthmax[$i] == -200) {
		echo '<td class="reportttl" >' . "---" . '</td>';
	} else { 
	$max = $minmean[$i];
	echo '<td class=" ' . ValueColor(conv($max,1,0)).'"' . '>' . conv($max,1,0) .' </td>'; 
	}
	}
	$max = min($minmean);
	echo '<td class=" ' . ValueColor3(conv($max,1,0)).'"' . '>' . conv($max,1,0) . ' </td></tr>'; 
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
			$max = $tempmonth[$y][$i][8];
			echo '<td class=" ' . ValueColor(conv($max,1,0)).'"' . '>' . conv($max,1,0) .' </td>';
		}
		else
			echo '<td class="reportttl" >' . "---" . '</td>';
		}
	echo '<td class=" ' . ValueColor3(conv(($tempyear[$y][8]),1,0)).'"' . '>' . conv($tempyear[$y][8],1,0) .'</td></tr>'; 
}
// Max of like months 
	echo '<tr class="reportttl2" ><td class="reportttl">Min</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) { 
	if ($monthlowmax[$i]== "" OR $monthlowmax[$i] == 100) {
		echo '<td class="reportttl" >' . "---" . '</td>';
	} else { 
	$max = $monthlowmax[$i];
	echo '<td class=" ' . ValueColor(conv($max,1,0)).'"' . '>' . conv($max,1,0) .' </td>'; 
	}
	}
	$max = min($monthlowmax);
	echo '<td class=" ' . ValueColor3(conv($max,1,0)).'"' . '>' . conv($max,1,0) . ' </td></tr>'; 
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
	$year_avg = $year_temp / $year_days; 
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($tempmonth[$y][$i][1] > 0 AND $tempmonth[$y][$i][0] !="" ) {
				$avg = round(($tempmonth[$y][$i][0] / $tempmonth[$y][$i][1]),1);
				$avg_all = round(($temp_month[$i][0] / $temp_month[$i][1]),1);
				echo '<td class=" ' . ValueColor(conv($avg,1,0)).'"' . '>' . conv($avg,1,0). '</td>';
			}
		else
			echo '<td class="reportttl" >' . "---" . '</td>';
		}
		if ($tempyear[$y][1] > 0 AND $tempmonth[$y][1] !="" ) { 
			$avg = round(($tempyear[$y][0] / $tempyear[$y][1]),1);
			$avg_all = round($year_avg,1) ; 
	echo '<td class=" ' . ValueColor3(conv($avg,1,0)).'"' . '>' . conv($avg,1,0). '</td>';  
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
	$avg = $temp_month[$i][0] / $temp_month[$i][1];
	echo '<td class=" ' . ValueColor(conv($avg,1,0)).'"' . '>' . conv($avg,1,0) .' </td>'; 
	}
	}
	echo '<td class=" ' . ValueColor3(conv($year_avg,1,0)).'"' . '>' . conv($year_avg,1,0) . ' </td></tr>'; 
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
	$year_avg = $year_temp / $year_days; 
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($tempmonth[$y][$i][3] > 0 AND $tempmonth[$y][$i][3] !="" ) {
				$avg = round(($tempmonth[$y][$i][2] / $tempmonth[$y][$i][3]),1);
				$avg_all = round(($temp_month[$i][2] / $temp_month[$i][3]),1); 
				echo '<td class=" ' . ValueColor(conv($avg,1,0)).'"' . '>' . conv($avg,1,0). '</td>';
			}
		else
				echo '<td class="reportttl" >' . "---" . '</td>';
	}
		if ($tempyear[$y][3] > 0 AND $tempmonth[$y][3] !="" ) { 
			$avg = round(($tempyear[$y][2] / $tempyear[$y][3]),1);
			$avg_all = round($year_avg,1) ; 
			echo '<td class=" ' . ValueColor3(conv($avg,1,0)).'"' . '>' . conv($avg,1,0). '</td>'; 
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
	$avg = round(($temp_month[$i][2] / $temp_month[$i][3]),1);
	echo '<td class=" ' . ValueColor(conv($avg,1,0)).'"' . '>' . conv($avg,1,0) .' </td>'; 
	}
	}
	echo '<td class=" ' . ValueColor3(conv($year_avg,1,0)).'"' . '>' . conv($year_avg,1,0) . ' </td></tr>'; 
}	
	elseif ($table_order[$table] == "Mean") { 								// Overall Mean
	echo '<tr><td class="separator" colspan="14" >&nbsp;</td></tr>';
	echo '<tr><td class="separator" colspan="14" >&nbsp;</td></tr>'; 
	echo '<tr><td class="title" colspan="14" >Monthly Means</td></tr>';
	echo '<tr><td class="separator" colspan="14" >&nbsp;</td></tr>'; 
	echo '<tr><th class="tableheading" colspan="14">Overall Means</th></tr>' ;
	echo '<tr><th class="labels">Date</th>' ;

		for ( $i = 0 ; $i < 12 ; $i++ ) { 
			echo '<th class="labels">' . substr( $mnthname[$i], 0, 3 ) . '</th>'; 
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
	$year_avg = $year_temp / $year_days; 
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($tempmonth[$y][$i][5] > 0 AND $tempmonth[$y][$i][5] !="" ) {
			$avg = round(($tempmonth[$y][$i][4] / $tempmonth[$y][$i][5]),1);
			$avg_all = round(($temp_month[$i][4] / $temp_month[$i][5]),1);
			echo '<td class=" ' . ValueColor(conv($avg,1,0)).'"' . '>' . conv($avg,1,0). '</td>';
		}	
		else
			echo '<td class="reportttl" >' . "---" . '</td>';
	}
		if ($tempyear[$y][5] > 0 AND $tempmonth[$y][5] !="" ) { 
			$avgt = round(($tempyear[$y][4] / $tempyear[$y][5]),1);
			$avg_all = round($year_avg,1);
			echo '<td class=" ' . ValueColor3(conv($avgt,1,0)).'"' . '>' . conv($avgt,1,0). '</td>';
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
		$minmean2[$i] = min($tvah[$i]);
		echo '<td class=" ' . ValueColor(conv($minmean2[$i],1,0)).'"' . '>' . conv($minmean2[$i],1,0) .'</td>'; 
	}
	$mmmin = min($minmean2);
	echo '<td class=" ' . ValueColor3(conv($mmmin,1,0)).'"' . '>' . conv($mmmin,1,0) . ' </td></tr>';
	
	echo '<tr><td class="reportttl">Max</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		for ( $y = 0 ; $y < $years ; $y++ ) {
			$tvah[$i][$y] = $tempmonth[$y][$i][4] / $tempmonth[$y][$i][5];
			if(floatval($tvah[$i][$y]) == 0) { $tvah[$i][$y] = -200; }
		}
		$maxmean2[$i] = max($tvah[$i]);
		echo '<td class=" ' . ValueColor(conv($maxmean2[$i],1,0)).'"' . '>' . conv($maxmean2[$i],1,0) .'</td>'; 
	}
	$mmmax = max($maxmean2);
	echo '<td class=" ' . ValueColor3(conv($mmmax,1,0)).'"' . '>' . conv($mmmax,1,0) . ' </td></tr>';
	
	echo '<tr><td class="reportttl">Mean</td>';
	$year_days = $year_temp = 0; 
	for ( $i = 0 ; $i < 12 ; $i++ ) { 
		$avg2 = $temp_month[$i][4] / $temp_month[$i][5];
		echo '<td class=" ' . ValueColor(conv($avg2,1,0)).'"' . '>' . conv($avg2,1,0) .'</td>'; 
	}
	echo '<td class=" ' . ValueColor3(conv($year_avg,1,0)).'"' . '>' . conv($year_avg,1,0) . ' </td></tr>';
	}

elseif($table_order[$table] == "Low") {
	echo '<tr><td class="title" colspan="14" >Monthly Extremes</td></tr>';					// Lowest Minima
	echo '<tr><td class="separator" colspan="14" >&nbsp;</td></tr>';
	echo '<tr><th class="tableheading" colspan="14">Lowest Minima</th></tr>' ;
	echo '<tr><th class="labels">Date</th>' ;
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		echo '<th class="labels">' . substr( $mnthname[$i], 0, 3 ) . '</th>'; 
	}
	echo '<th class="labels2"> Year </th></tr>'; 
for ($y = 0; $y < $years; $y++) {
	echo '<tr><td class="reportttl">',  $year-$y,  '</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($tempmonth[$y][$i][1] > 0) {
			$min = $tempmonth[$y][$i][7]; 
			echo '<td class=" ' . ValueColor(conv($min,1,0)).'"' . '>' . conv($min,1,0).' </td>';
		}
		else
			echo '<td class="reportttl" >' . "---" . '</td>';
	}
	echo '<td class=" ' . ValueColor3(conv(($tempyear[$y][7]),1,0)).'"' . '>' . conv($tempyear[$y][7],1,0) .'</td></tr>'; 
}
// Min of like months 
	echo '<tr class="reportttl2" ><td class="reportttl">Min</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) { 
	if ($monthmin[$i]== "" OR $monthmin[$i] == 200) {
		echo '<td class="reportttl" >' . "---" . '</td>';
	} else { 
	$min = $monthmin[$i];
	echo '<td class=" ' . ValueColor(conv($min,1,0)).'"' . '>' . conv($min,1,0) .' </td>'; 
	}
	}
	$min = min($monthmin);
	echo '<td class=" ' . ValueColor3(conv($min,1,0)).'"' . '>' . conv($min,1,0) . ' </td></tr>'; 
}
else { 																				// Highest Minima
	echo '<tr><td class="separator" colspan="14" >&nbsp;</td></tr>'; 
	echo '<tr><th class="tableheading" colspan="14">Highest Minima</th></tr>' ;
	echo '<tr><th class="labels">Date</th>' ;
		for ( $i = 0 ; $i < 12 ; $i++ ) {
			echo '<th class="labels">' . substr( $mnthname[$i], 0, 3 ) . '</th>'; 
		}
	echo '<th class="labels2"> Year </th>'; 
	echo '</tr>'; 
for ($y = 0; $y < $years; $y++) {
	echo '<tr><td class="reportttl">',  $year-$y,  '</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($tempmonth[$y][$i][1] > 0) {
			$min = $tempmonth[$y][$i][9]; 
			echo '<td class=" ' . ValueColor(conv($min,1,0)).'"' . '>' . conv($min,1,0).' </td>';
		}
		else
			echo '<td class="reportttl" >' . "---" . '</td>';
	}

	echo '<td class=" ' . ValueColor3(conv(($tempyear[$y][9]),1,0)).'"' . '>' . conv($tempyear[$y][9],1,0) .'</td></tr>'; 
}
// Min of like months 
	echo '<tr class="reportttl2" ><td class="reportttl">Max</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) { 
	if ($monthhighmin[$i]== "" OR $monthhighmin[$i] == -100) {
		echo '<td class="reportttl" >' . "---" . '</td>';
	} else { 
	$min = $monthhighmin[$i];
	echo '<td class=" ' . ValueColor(conv($min,1,0)).'"' . '>' . conv($min,1,0) .' </td>'; 
	}
	}
	$min = max($monthhighmin);
	echo '<td class=" ' . ValueColor3(conv($min,1,0)).'"' . '>' . conv($min,1,0) . ' </td></tr>'; 
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

function gethistory($file) {
	if(file_exists($file)) {
		$data = file($file);
		$end = 1200;
		for ($i = 1; $i < $end; $i++) {
			if(strpos($data[$i],"remes for the month") > 0) { $end = $i; }
			if(strpos($data[$i],"remes for day") > 0) { $daya = explode(" ", $data[$i]); $a = intval(substr($daya[7],1,2))-1; }
			if(strpos($data[$i],"aximum dew") > 0): $dmaxa = explode(" ", $data[$i]); $dmaxv[$a] = floatval($dmaxa[11])-0.001; endif;
			if(strpos($data[$i],"inimum dew") > 0): $dmina = explode(" ", $data[$i]); $dminv[$a] = floatval($dmina[11])-0.001; endif;
			if(strpos($data[$i],"verage dew") > 0): $davea = explode(" ", $data[$i]); $davev[$a] = floatval($davea[11])-0.001; endif;
		}
		return array(1,1,$dmaxv,$dminv,$davev);
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