<?php require('unit-select.php'); ?>
<?php
@include_once("wxreport-settings.php");
// Obtains Server's Self and protect it against XSS injection
$SITE 			= array();
$SITE['self'] = htmlentities( substr($_SERVER['PHP_SELF'], 0,
strcspn( $_SERVER['PHP_SELF'] , "\n\r") ), ENT_QUOTES );
######SETTINGS#################################################
$summary = 1;
$season_start = 1;
$manual_values = array(5,10,20,30,50,75,100,150,200);
$manual_valuesUS = array(.2,.5,1,1.5,2,3,4,6,8);
if($unitR == 'in') { $manual_values = $manual_valuesUS; }
$loc = $path_dailynoaa; 
$first_year_of_data = $first_year_of_noaadata;
$raintype = $unitR;
$rain_increment = $rain_increment[((strtoupper($raintype)) != "IN")] ;
$raintype = " " . $raintype;
$rainvalues = array($rain_increment);
$range = "year";
for ( $i = 0; $i < $increments ; $i ++ ) {
	$rainvalues[$i+1] = $rainvalues[$i] + $rain_increment;
}
$rainvalues = $manual_values;
$increments = (count($manual_values))-1;
$colors = $increments + 1;
	
// If first day of year/season, default to the previous year
	if (( date("n") == $season_start) AND (date("j") == 1) AND ($show_today != true)) {
		$year = date("Y")-1;
	}
	else {
		$year = date("Y");
	}
$years = 1 + ($year - $first_year_of_data);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("phptags.php");
		$file = 52; ?>
		
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - Historical Rain Summary</title>

	<meta name="description" content="Old v2 - Detailed historical monthly/annual rain summary report from NW3 weather.
	Find rainfall for each month of every year on record; wettest days / highest totals in each month and year, difference from average rainfall (anomaly) by month and year,
	number of rain days, rain days > 10 mm, and months with snowfall" />

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
		<div align="center"><h1>Rainfall Summary (<?php echo $unitR; ?>)</h1>

			<?php include("wxrepgen.php"); ?>
		</div>
		
		<?php get_rain_detail($first_year_of_data,$year,$years,$loc, $season_start, $range, $round); ?>
	</div>
	
	<p><b>Note 1:</b> Figures in brackets refer to departure from <a href="wxaverages.php" title="Long-term NW3 climate averages">average conditions</a>
	(anomalies are absolute, i.e. unadjusted to reflect incompleteness of the current month/year).<br />
	<b>Note 2:</b> Monthly totals marked with an asterisk refer to months in which significant snow fell (>10% of the total).<br />
	<b>Note 3:</b> The Min and Mean for year totals exclude the current year.</p>
</div><!-- end main-copy -->

<!-- ##### Footer ##### -->

<? require('footer.php'); ?>
 </body>
</html>

<?php
function get_rain_detail ($first_year_of_data,$year,$years,$loc, $season_start, $range, $round) {
	global $SITE, $rainvalues, $raintype, $table_order, $show_today, $dayrn, $colors, $mnthname, $unitR;
	global $myavjanrain,$myavfebrain,$myavmarrain,$myavaprrain,$myavmayrain,$myavjunrain,$myavjulrain,$myavaugrain,$myavseprain,$myavoctrain,$myavnovrain,$myavdecrain;
	$places = "%01.1f"; if($unitR == 'in') { $places = "%.2f"; }
	$anom = array($myavjanrain,$myavfebrain,$myavmarrain,$myavaprrain,$myavmayrain,$myavjunrain,$myavjulrain,$myavaugrain,$myavseprain,$myavoctrain,$myavnovrain,$myavdecrain);
	
	// Collect the data
		for ( $y = 0; $y < $years ; $y ++ ) {
			$yx = $year - $y;
		for ( $mx = 0; $mx < 12 ; $mx ++ ) {
			$m = $season_start+$mx-1;
			if ($m > 11){
				$m = $m - 12;
				$yx = ($year-$y)+1;
			}
			
			if ((($yx == $first_year_of_data) AND ($m >= $start_month-1)) OR ($yx > $first_year_of_data)) {
			// Check for current year and current month
			
		if ($yx == date("Y") && $m == ( date("n") - 1) &&((date("j") != 1 ) OR $show_today)){
				$filename = $absRoot."dailynoaareport.htm";
				$current_month = 1;
			} else {
				$filename = "dailynoaareport" . ( $m + 1 ) . $yx . ".htm";
				$current_month = 0;
			}

			if ($current_month AND $show_today AND date("j")==1){
				$raw[$y][1][$mx][1][0][8] = strip_units($dayrn);
			} elseif (file_exists($loc . $filename) ) {
				$raw[$y][1][$mx][1] = getnoaafile($loc . $filename);
			}
			if ($current_month AND $show_today){
				$raw[$y][1][$mx][1][date("j")-1][8] = strip_units($dayrn);
					
				}
			}
		}
		}
		
	// Output Table with information
	echo '<table>';
	$rainmonth = array();
	$rainyear = array();
	$ytd = 0;
	$monthly_maxes = array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1);
	$monthly_mins = array(900,900,900,900,900,900,900,900,900,900,900,900);
	$yearly_max = -1;
	$yearly_min = 5000;
	$moredata = file('mrep.csv');
	
		for ($yx = 0 ; $yx < $years ; $yx++ ) {
		// Display each years values for that month
		for ($mnt = 0 ; $mnt < 12 ; $mnt++ ) {
		for ($day = 0 ; $day < 31 ; $day++ ){
											
		// Rain amount
		$rain = $raw[$yx][1][$mnt][1][$day][8];
		if(strpos($rain,'*') > 0) { $ast[$yx][$mnt] = $ast[$yx][$mnt] + floatval($rain); }
		$b4_start = ($yx == $years-1) && ($mnt == $start_month-1) && ($day < $start_day-1) ;
				if (( $rain != "") AND ( $rain != "-----") AND ($b4_start == false)) {
					$rainmonth[$yx][$mnt][0] = $rainmonth[$yx][$mnt][0] + $rain;
					$rainmonth[$yx][$mnt][1] = $rainmonth[$yx][$mnt][1] + 1;
					if ($rain > 0) { $raindays[$yx][$mnt] = $raindays[$yx][$mnt] + 1; }
					if ($rain > 1) { $raindays1[$yx][$mnt] = $raindays1[$yx][$mnt] + 1; }
					if ($rain > 10) { $raindays10[$yx][$mnt] = $raindays10[$yx][$mnt] + 1; }
					if (conv($rain,2,0) > 0.5) { $raindays05[$yx][$mnt] = $raindays05[$yx][$mnt] + 1; }
					if ($rain > $maxdaily[$yx][$mnt]) { $maxdaily[$yx][$mnt] = $rain; }
				}
		}

		$yea = $year - $yx;
		$mon = $mnt+1;
		$st = intval((mktime(12,0,0,$mon,1,$yea)-mktime(0,0,0,2,1,2009))/(24*3600))+1; $en = $st + date('t', mktime(0,0,0,$mon,1,$yea));
		for($l = $st; $l < $en; $l++) {
			$mhrndata[$l] = explode(',',$moredata[$l]);
			$rhmaxdaily[$yx][$mnt][$l] = floatval($mhrndata[$l][5]);
			$rrmaxdaily[$yx][$mnt][$l] = floatval($mhrndata[$l][6]);
		}	// end day loop
		
		$rhmax[$yx][$mnt] = max($rhmaxdaily[$yx][$mnt]);
		$rrmax[$yx][$mnt] = max($rrmaxdaily[$yx][$mnt]);
		
		$rainyear[$yx][0] = $rainyear[$yx][0] + $rainmonth[$yx][$mnt][0];
		$rainyear[$yx][1] = $rainyear[$yx][1] + $rainmonth[$yx][$mnt][1];
		$raindaysyear[$yx] = $raindaysyear[$yx] + $raindays[$yx][$mnt];
		$raindaysyear1[$yx] = $raindaysyear1[$yx] + $raindays1[$yx][$mnt];
		$raindaysyear10[$yx] = $raindaysyear10[$yx] + $raindays10[$yx][$mnt];
		$raindaysyear05[$yx] = $raindaysyear05[$yx] + $raindays05[$yx][$mnt];
		if ($maxdaily[$yx][$mnt] > $maxdailyyear[$yx]) { $maxdailyyear[$yx] = $maxdaily[$yx][$mnt]; }
		if ($rainmonth[$yx][$mnt][1] > 0){
			if ($rainmonth[$yx][$mnt][0]>$monthly_maxes[$mnt]){
				$monthly_maxes[$mnt] = $rainmonth[$yx][$mnt][0];
				}
				if ($rainmonth[$yx][$mnt][0]<$monthly_mins[$mnt]){
				$monthly_mins[$mnt] = $rainmonth[$yx][$mnt][0];
				}
		}

		} 
		if ($rainyear[$yx][0] > $yearly_max){
			$yearly_max = conv($rainyear[$yx][0],2,0);
		}
		if ($rainyear[$yx][0] < $yearly_min && $yx != 0) {
			$yearly_min = conv($rainyear[$yx][0],2,0);
		}
		}

	 
		
	echo '<tr><th class="tableheading" colspan="14">Rainfall</th></tr>' ;				// Rainfall
	echo '<tr><th class="labels" width="7%">Date</th>' ;
		for ( $i = 0 ; $i < 12 ; $i++ ) {
			echo '<th class="labels" width="7%">' . substr( $mnthname[$i], 0, 3 ) . '</th>';
		}
	echo '<th class="labels" width="7%">Year</th>';
	echo '</tr>';
for ($y = 0; $y < $years; $y++) {
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($rainmonth[$y][$i][1] > 0) {
			$rain_months[$i][0] = $rain_months[$i][0] + $rainmonth[$y][$i][0];
			$rain_months[$i][1] = $rain_months[$i][1] + 1;
		}
	}
}
	for ($y = 1; $y < $years; $y++) { //exclude current year from mean of year-totals
		for ( $i = 0 ; $i < 12 ; $i++ ) {
			$rmnty[$i][1] = $rmnty[$i][1] + 1;
			$rmnty[$i][0] = $rmnty[$i][0] + $rainmonth[$y][$i][0];
		}
	}
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if (0 == $rmnty[$i][1])
			$avg = 0;
		else
			$avg = round(($rmnty[$i][0] / $rmnty[$i][1]),2);
		$year_avg = $year_avg + $avg;
	}
	
for ($y = 0; $y < $years; $y++) {
	echo '<tr><td class="reportttl">', $year-$y, '</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($rainmonth[$y][$i][1] > 0) {
				$avg_all = round(($rain_months[$i][0] / $rain_months[$i][1]),2);
				$cur_rain = $rainmonth[$y][$i][0];
				if($ast[$y][$i]*10 > $cur_rain) { $astr = '*'; } else { $astr = ''; }
			echo '<td class="' . ValueColor(conv($cur_rain,2,0),$rainvalues).'"' . '>' . conv($cur_rain,2,0). $astr . '<br /> ('. round($cur_rain/$anom[$i]*100,0). '%)</td>';
		}
		else
			echo '<td class="reportttl" >' . "---" . '</td>';
	}
	echo '<td class="yeartotals">' . conv($rainyear[$y][0],2,0) . '<br /> ('. round($rainyear[$y][0]/array_sum($anom)*100,0). '%)</td></tr>';
}

	echo '<tr><td class="separator" colspan="14" >&nbsp;</td></tr>';
	
	// minima
	echo '<tr><td class="reportttl">Min</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($rain_months[$i][1]==0) {
			echo '<td class="reportttl" >' . "---" . '</td>';
		} else {
			$min = conv($monthly_mins[$i],2,0);
			echo '<td class=" ' . ValueColor($min,$rainvalues).'"' . '>' . sprintf($places,($min)). '<br /> ('. round($min/conv($anom[$i],2,0)*100,0) .'%) </td>';
		}
	}
	echo '<td class="yeartotals">' . sprintf($places,($yearly_min)) .  '<br /> ('. round($yearly_min/conv(array_sum($anom),2,0)*100,0) .'%) </td></tr>';
	
	// maxima
	echo '<tr><td class="reportttl">Max</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($rain_months[$i][1]==0) {
			echo '<td class="reportttl" >' . "---" . '</td>';
		} else {
			$max = conv($monthly_maxes[$i],2,0);
			echo '<td class=" ' . ValueColor($max,$rainvalues).'"' . '>' . sprintf($places,($max)). '<br /> ('. round($max/conv($anom[$i],2,0)*100,0) .'%) </td>';
		}
	}
	echo '<td class="yeartotals">' . sprintf($places,($yearly_max)) .  '<br /> ('. round($yearly_max/conv(array_sum($anom),2,0)*100,0) .'%) </td></tr>';

	// averages
	echo '<tr><td class="reportttl">Mean</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		if ($rain_months[$i][1]==0) {
			echo '<td class="reportttl" >' . "---" . '</td>';
		} else {
			$avg = conv(($rain_months[$i][0] / $rain_months[$i][1]),2,0);
			echo '<td class=" ' . ValueColor($avg,$rainvalues).'"' . '>' . sprintf($places,($avg)) .' </td>';
		}
	}
	echo '<td class="yeartotals">' . conv($year_avg,2,0) . ' </td>';
	
	echo '</tr><tr><td class="separator" colspan="14" >&nbsp;</td></tr>';

	echo '<tr><th class="tableheading" colspan="14">Rain Days</th></tr>';			// Rain Days
	echo '<tr><th class="labels" width="7%">Date</th>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		echo '<th class="labels" width="7%">' . substr( $mnthname[$i], 0, 3 ) . '</th>';
	}
	echo '<th class="labels" width="7%">Year</th></tr>';
	for ($y = 0; $y < $years; $y++) {
		echo '<tr><td class="reportttl">', $year-$y, '</td>';
		for ( $i = 0 ; $i < 12 ; $i++ ) {
			if ($rainmonth[$y][$i][1] > 0) {
				echo '<td class="' . ValueColor($raindays[$y][$i],$rainvalues).'"' . '>' . $raindays[$y][$i] .'</td>';
			}
			else {
				echo '<td class="reportttl" >' . "---" . '</td>';
			}
		}
		echo '<td class="yeartotals">' . $raindaysyear[$y] .'</td></tr>';
	}
	//Min
	echo '<tr class="reportttl2"><td class="reportttl">Min</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		for ( $y = 0 ; $y < $years ; $y++ ) {
			$tvah[$i][$y] = $raindays[$y][$i];
			if(floatval($tvah[$i][$y]) == 0) { $tvah[$i][$y] = 200; }
		}
		$minrd[$i] = min($tvah[$i]);
		echo '<td class="' . ValueColor($minrd[$i],$rainvalues).'"' . '>' . $minrd[$i] .'</td>';
	}
	for ( $y = 1 ; $y < $years ; $y++ ) { $minyt[$y] = $raindaysyear[$y]; }
	echo '<td class="yeartotals">' . min($minyt) .'</td></tr>';
	//Max
	echo '<tr><td class="reportttl">Max</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		for ( $y = 0 ; $y < $years ; $y++ ) {
			$tvah[$i][$y] = $raindays[$y][$i];
		}
		$maxrd[$i] = max($tvah[$i]);
		echo '<td class="' . ValueColor($maxrd[$i],$rainvalues).'"' . '>' . $maxrd[$i] .'</td>';
	}
	echo '<td class="yeartotals">' . max($raindaysyear) .'</td></tr>';
	//Mean
	echo '<tr><td class="reportttl">Mean</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		for ( $y = 0 ; $y < $years ; $y++ ) {
			if($tvah[$i][$y] > 0) {	$rdsum[$i][$y] = $rdsum[$i][$y] + $tvah[$i][$y]; }
		}
		$meanrd[$i] = (array_sum($rdsum[$i]) / count($rdsum[$i]));
		echo '<td class="' . ValueColor($meanrd[$i],$rainvalues).'"' . '>' . round($meanrd[$i],0) .'</td>';
	}
	for ( $y = 1 ; $y < $years ; $y++ ) { $meanyt[$y] = $raindaysyear[$y]; }
	echo '<td class="yeartotals">' . round(array_sum($meanyt) / count($meanyt),0) .'</td></tr>';
	
		echo '</tr><tr><td class="separator" colspan="14" >&nbsp;</td></tr>';
	
	$rdm = '&gt;1 mm'; if($unitR == 'in') { $rdm = '&gt;0.025 in'; }
	echo '<tr><th class="tableheading" colspan="14">Days with ', $rdm, '</th></tr>';			// Rain Days >1mm
	echo '<tr><th class="labels" width="7%">Date</th>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		echo '<th class="labels" width="7%">' . substr( $mnthname[$i], 0, 3 ) . '</th>';
	}
	echo '<th class="labels" width="7%">Year</th></tr>';
	for ($y = 0; $y < $years; $y++) {
		echo '<tr><td class="reportttl">', $year-$y, '</td>';
		for ( $i = 0 ; $i < 12 ; $i++ ) {
			if ($rainmonth[$y][$i][1] > 0) {
				if (intval($raindays1[$y][$i]) == 0) {
					echo '<td class="reportttl">0</td>';
				}
				else {
					echo '<td class="' . ValueColor($raindays1[$y][$i],$rainvalues).'"' . '>' . $raindays1[$y][$i] .'</td>';
				}
			}
			else {
				echo '<td class="reportttl" >' . "---" . '</td>';
			}
		}
		echo '<td class="yeartotals">' . $raindaysyear1[$y] .'</td></tr>'; 
	}
	
	echo '</tr><tr><td class="separator" colspan="14" >&nbsp;</td></tr>';
	
	$rdm = '>10 mm'; if($unitR == 'in') { $rdm = '>0.5 in'; }
	echo '<tr><th class="tableheading" colspan="14">Days with ', $rdm, '</th></tr>';			// Rain Days >10mm (0.5in)
	echo '<tr><th class="labels" width="7%">Date</th>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		echo '<th class="labels" width="7%">' . substr( $mnthname[$i], 0, 3 ) . '</th>';
	}
	echo '<th class="labels" width="7%">Year</th></tr>';
	for ($y = 0; $y < $years; $y++) {
		echo '<tr><td class="reportttl">', $year-$y, '</td>';
		for ( $i = 0 ; $i < 12 ; $i++ ) {
			if ($rainmonth[$y][$i][1] > 0) {
				if($unitR == 'mm') {
					if (intval($raindays10[$y][$i]) == 0) {
						echo '<td class="reportttl">0</td>';
					}
					else {
						echo '<td class="' . ValueColor($raindays10[$y][$i],$rainvalues).'"' . '>' . $raindays10[$y][$i] .'</td>';
					}
				}
				else {
				if (intval($raindays05[$y][$i]) == 0) {
						echo '<td class="reportttl">0</td>';
					}
					else {
						echo '<td class="' . ValueColor($raindays05[$y][$i],$rainvalues).'"' . '>' . $raindays05[$y][$i] .'</td>';
					}
				}
			}
			else {
				echo '<td class="reportttl" >' . "---" . '</td>';
			}
		}
		if($unitR == 'mm') { echo '<td class="yeartotals">' . $raindaysyear10[$y] .'</td></tr>'; }
		else { echo '<td class="yeartotals">' . $raindaysyear05[$y] .'</td></tr>'; }
	}
	
	echo '</tr><tr><td class="separator" colspan="14" >&nbsp;</td></tr>';
	
	echo '<tr><th class="tableheading" colspan="14">Highest Daily Total</th></tr>';	// Highest Daily Total
	echo '<tr><th class="labels" width="7%">Date</th>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		echo '<th class="labels" width="7%">' . substr( $mnthname[$i], 0, 3 ) . '</th>';
	}
	echo '<th class="labels" width="7%">Year</th></tr>';
	for ($y = 0; $y < $years; $y++) {
		echo '<tr><td class="reportttl">', $year-$y, '</td>';
		for ( $i = 0 ; $i < 12 ; $i++ ) {
			if ($rainmonth[$y][$i][1] > 0) {
					echo '<td class="' . ValueColor(conv($maxdaily[$y][$i],2,0),$rainvalues).'"' . '>' . conv($maxdaily[$y][$i],2,0) .'</td>';
			}
			else {
				echo '<td class="reportttl" >' . "---" . '</td>';
			}
		}
		echo '<td style="border-left: 2px solid black; font-weight: bold;" class="' . ValueColor(conv($maxdailyyear[$y],2,0),$rainvalues).'"' . '>' . conv($maxdailyyear[$y],2,0) .'</td></tr>';
	}
	//Min
	echo '<tr class="reportttl2"><td class="reportttl">Min</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		for ( $y = 0 ; $y < $years ; $y++ ) {
			$tvah[$i][$y] = $maxdaily[$y][$i];
			if(floatval($tvah[$i][$y]) == 0) { $tvah[$i][$y] = 200; }
		}
		$minrd[$i] = min($tvah[$i]);
		echo '<td class="' . ValueColor(conv($minrd[$i],2,0),$rainvalues).'"' . '>' . conv($minrd[$i],2,0) .'</td>';
	}
	echo '<td style="border-left: 2px solid black; font-weight: bold;" class="' . ValueColor(conv(min($maxdailyyear),2,0),$rainvalues) .'">' . conv(min($maxdailyyear),2,0) .'</td></tr>';
	//Max
	echo '<tr><td class="reportttl">Max</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		for ( $y = 0 ; $y < $years ; $y++ ) {
			$tvah[$i][$y] = $maxdaily[$y][$i];
		}
		$maxrd[$i] = max($tvah[$i]);
		echo '<td class="' . ValueColor(conv($maxrd[$i],2,0),$rainvalues).'"' . '>' . conv($maxrd[$i],2,0) .'</td>';
	}
	echo '<td style="border-left: 2px solid black; font-weight: bold;" class="' . ValueColor(conv(max($maxdailyyear),2,0),$rainvalues) .'">' . conv(max($maxdailyyear),2,0) .'</td></tr>';
	//Mean
	echo '<tr><td class="reportttl">Mean</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		for ( $y = 0 ; $y < $years ; $y++ ) {
			$tvah[$i][$y] = $maxdaily[$y][$i];
			if($tvah[$i][$y] > 0) {	$mrsum[$i][$y] = $mrsum[$i][$y] + $tvah[$i][$y]; }
		}
		$meanrd[$i] = (array_sum($mrsum[$i]) / count($mrsum[$i]));
		echo '<td class="' . ValueColor(conv($meanrd[$i],2,0),$rainvalues).'"' . '>' . conv($meanrd[$i],2,0) .'</td>';
	}
	$mdymn = (array_sum($maxdailyyear) / count($maxdailyyear));
	echo '<td style="border-left: 2px solid black; font-weight: bold;" class="' . ValueColor(conv($mdymn,2,0),$rainvalues) .'">' . conv($mdymn,2,0) .'</td></tr>';

	echo '</tr><tr><td class="separator" colspan="14" >&nbsp;</td></tr>';
	
	echo '<tr><th class="tableheading" colspan="14">Highest Hourly Total</th></tr>';	// Highest Hourly Total
	echo '<tr><th class="labels" width="7%">Date</th>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		echo '<th class="labels" width="7%">' . substr( $mnthname[$i], 0, 3 ) . '</th>';
	}
	echo '<th class="labels" width="7%">Year</th></tr>';
	for ($y = 0; $y < $years; $y++) {
		echo '<tr><td class="reportttl">', $year-$y, '</td>';
		for ( $i = 0 ; $i < 12 ; $i++ ) {
			if ($rainmonth[$y][$i][1] > 0 && !($y == $years - 1 && $i == 0)) {
					echo '<td class="' . ValueColor(conv($rhmax[$y][$i],2,0),$rainvalues).'"' . '>' . conv($rhmax[$y][$i],2,0) .'</td>';
			}
			else {
				echo '<td class="reportttl" >' . "---" . '</td>';
			}
		}
		echo '<td style="border-left: 2px solid black; font-weight: bold;" class="' . ValueColor(conv(max($rhmax[$y]),2,0),$rainvalues).'"' . '>' . conv(max($rhmax[$y]),2,0) .'</td></tr>';
	}
	//Max
	echo '<tr class="reportttl2"><td class="reportttl">Max</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		for ( $y = 0 ; $y < $years ; $y++ ) {
			$tvah[$i][$y] = $rhmax[$y][$i];
		}
		$maxrd[$i] = max($tvah[$i]);
		echo '<td class="' . ValueColor(conv($maxrd[$i],2,0),$rainvalues).'"' . '>' . conv($maxrd[$i],2,0) .'</td>';
	}
	echo '<td style="border-left: 2px solid black; font-weight: bold;" class="' . ValueColor(conv(max($maxrd),2,0),$rainvalues) .'">' . conv(max($maxrd),2,0) .'</td></tr>';
	//Mean
/*	echo '<tr><td class="reportttl">Mean</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		for ( $y = 0 ; $y < $years ; $y++ ) {
			$tvah[$i][$y] = $maxdaily[$y][$i];
			if($tvah[$i][$y] > 0) {	$mrsum[$i][$y] = $mrsum[$i][$y] + $tvah[$i][$y]; }
		}
		$meanrd[$i] = (array_sum($mrsum[$i]) / count($mrsum[$i]));
		echo '<td class="' . ValueColor(conv($meanrd[$i],2,0),$rainvalues).'"' . '>' . conv($meanrd[$i],2,0) .'</td>';
	}
	$mdymn = (array_sum($maxdailyyear) / count($maxdailyyear));
	echo '<td style="border-left: 2px solid black; font-weight: bold;" class="' . ValueColor(conv($mdymn,2,0),$rainvalues) .'">' . conv($mdymn,2,0) .'</td></tr>';
	*/
	
	echo '</tr><tr><td class="separator" colspan="14" >&nbsp;</td></tr>';
	
	echo '<tr><th class="tableheading" colspan="14">Highest Rain Rate</th></tr>';	// Highest Rain rate
	echo '<tr><th class="labels" width="7%">Date</th>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		echo '<th class="labels" width="7%">' . substr( $mnthname[$i], 0, 3 ) . '</th>';
	}
	echo '<th class="labels" width="7%">Year</th></tr>';
	for ($y = 0; $y < $years; $y++) {
		echo '<tr><td class="reportttl">', $year-$y, '</td>';
		for ( $i = 0 ; $i < 12 ; $i++ ) {
			if ($rainmonth[$y][$i][1] > 0 && $rrmax[$y][$i] > 0) {
					echo '<td class="' . ValueColor(conv($rrmax[$y][$i],2,0),$rainvalues).'"' . '>' . conv3($rrmax[$y][$i],2,0) .'</td>';
			}
			else {
				echo '<td class="reportttl" >' . "---" . '</td>';
			}
		}
		echo '<td style="border-left: 2px solid black; font-weight: bold;" class="' . ValueColor(conv(max($rrmax[$y]),2,0),$rainvalues).'"' . '>' . conv3(max($rrmax[$y]),2,0) .'</td></tr>';
	}
	//Max
	echo '<tr class="reportttl2"><td class="reportttl">Max</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		for ( $y = 0 ; $y < $years ; $y++ ) {
			$tvah[$i][$y] = $rrmax[$y][$i];
		}
		$maxrd[$i] = max($tvah[$i]);
		echo '<td class="' . ValueColor(conv($maxrd[$i],2,0),$rainvalues).'"' . '>' . conv3($maxrd[$i],2,0) .'</td>';
	}
	echo '<td style="border-left: 2px solid black; font-weight: bold;" class="' . ValueColor(conv(max($maxrd),2,0),$rainvalues) .'">' . conv3(max($maxrd),2,0) .'</td></tr>';
	//Mean
/*	echo '<tr><td class="reportttl">Mean</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		for ( $y = 0 ; $y < $years ; $y++ ) {
			$tvah[$i][$y] = $maxdaily[$y][$i];
			if($tvah[$i][$y] > 0) {	$mrsum[$i][$y] = $mrsum[$i][$y] + $tvah[$i][$y]; }
		}
		$meanrd[$i] = (array_sum($mrsum[$i]) / count($mrsum[$i]));
		echo '<td class="' . ValueColor(conv($meanrd[$i],2,0),$rainvalues).'"' . '>' . conv($meanrd[$i],2,0) .'</td>';
	}
	$mdymn = (array_sum($maxdailyyear) / count($maxdailyyear));
	echo '<td style="border-left: 2px solid black; font-weight: bold;" class="' . ValueColor(conv($mdymn,2,0),$rainvalues) .'">' . conv($mdymn,2,0) .'</td></tr>';
	*/	
	
	$dp = "%01.0f"; if($unitR == 'in') { $dp ='%.1f'; }
	$colorband_rows = ceil($colors/15);
	$colorband_cols = ceil(($colors+1)/$colorband_rows);
	echo '</table><table><tr><td class="separator" colspan="'.($colorband_cols).'" >&nbsp;</td></tr>';
	echo '<tr><td class="colorband" colspan="'.($colorband_cols).'">Colour Key</td></tr>';
	$i = 0;
	for ($r = 0; $r < $colorband_rows; $r ++){
		for ( $j = 0; (($j < $colorband_cols) AND ($i < $colors)) ; $j ++ ){
			$band = $i;
			if ($i == 0) {
				echo '<tr><td class="levelb_1" >&lt;&nbsp;' . sprintf($dp,$rainvalues[$i]) . '</td>';
			} else {
				echo '<td class="levelb_'.($band+1).'"'.$color_text.' > ' . sprintf($dp,$rainvalues[$i-1]) . " - " .sprintf($dp,$rainvalues[$i]) . '</td>';
				if (($j == $colorband_cols-1) AND ($r != $colorband_rows)){
					echo '</tr><tr>';	
				}
			}
			$i = $i+1;
		}
	}
	echo '<td class="levelb_'.($band+2).'"'.$color_text.' > &gt;'. sprintf($dp,$rainvalues[$i-1]) . '</td></tr></table>';
}

function ValueColor($value,$values) {
	$limit = count($values);
	if ($value == 0){
		return 'reportday';
	}
	if ($value < $values[0]) {
	return 'levelb_1';
	}
	for ($i = 1; $i < $limit ; $i++){
		if ($value <= $values[$i]) {
		return 'levelb_'.($i+1);
		}
	}
	return 'levelb_'.($limit+1);
}
?>