<?php require('unit-select.php'); ?>
<?php
// Obtains Server's Self and protect it against XSS injection
$SITE 			= array();
$SITE['self'] = htmlentities( substr($_SERVER['PHP_SELF'], 0, 
	strcspn( $_SERVER['PHP_SELF'] , "\n\r") ), ENT_QUOTES );
@include_once("wxreport-settings.php"); 
#Settings##############################################
$leading_zeros = 1;
$manual_values = array(.5,1,2,5,10,15,20,25,50);
$manual_valuesUS = array(.02,.05,.1,.2,.3,.5,.75,1,2);
if($unitR == 'in') { $manual_values = $manual_valuesUS; }
$loc = $path_dailynoaa; # Location of dailynoaareports
$first_year_of_data = $first_year_of_noaadata;
$rain_units = $unitR;
$rainformat = '%0' . ($leading_zeros+2+($rain_units == "in")) . '.' . (1+($rain_units == "in")) . 'f';
$rain_increment = $rain_increment[($rain_units != "in")] ; 
$rainvalues = array($rain_increment);

for ( $i = 0; $i < $increments ; $i ++ ) { 
	$rainvalues[$i+1] = $rainvalues[$i] + $rain_increment;
}
$rainvalues = $manual_values;
$increments = (count($manual_values))-1;
$colors = $increments + 1; 
$range = "year";

include('wxrepgen0.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 

<?php include("phptags.php"); 
	$file = 52; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

	<title>NW3 Weather - Old(v2) - Annual Rain Reports</title>

	<meta name="description" content="Old v2 - Detailed historical annaul rain report for <?php echo $year; ?> with monthly breakdown from NW3 weather.
	Find rainfall for each day of every year on record; wettest days / highest totals in each month, dry and wet spells, difference from average rainfall (anomaly),
	number of rain days, and snowfall" />

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
		<div align="center">
			<h1>Rainfall Reports (<?php echo trim($rain_units) ?>)</h1>

			<?php include("wxrepgen.php"); ?> 
		</div>
		
	<?php get_rain_detail($year,$loc, $range, $season_start, $rain_units); ?>
	
	</div>

	<p><b>*Note 1:</b> Values marked with an asterisk correspond to snowfall -
	this is manually recorded and consequently only an estimate, and can only appear in these reports at the end of the given month.
	This may result in discrepancies between figures here and those in the daily reports.<br />
	<b>Note 2:</b> Figures in brackets refer to departure from <a href="wxaverages.php" title="Long-term NW3 climate averages">average conditions</a>
	<?php if($year == date("Y")){ echo " (NB: The anomaly for the current month is unadjusted for the month's degree of completeness)"; } ?>.</p>
	<?php if($year == 2009) { echo'<p><b>Notes for 2009 data:</b> 
	Values for January are from a nearby site as the rain gauge was set up on the 31st Jan 2009;<br />
	Values prior to 8th March are recorded to a precision of '; if($unitR == 'mm') { echo '1 mm, and 0.1 mm after this,
	as the gauge was modified on this day to a resolution of 0.25 mm.</p>'; } else { echo '0.04 in, and 0.01 in after this,
	as the gauge was modified on this day to a resolution of 0.01 in.</p>'; } } ?>
</div>
<!-- end main-copy -->

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>

 </body>
</html>

<?php
function get_rain_detail ($year, $loc, $range, $season_start, $rain_units) {
	global $SITE, $rainvalues, $rainformat, $colors;
	global $show_today, $dayrn, $date, $time, $timeofnextupdate, $mnthname, $unitR;
	global $myavjanrain,$myavfebrain,$myavmarrain,$myavaprrain,$myavmayrain,$myavjunrain,$myavjulrain,$myavaugrain,$myavseprain,$myavoctrain,$myavnovrain,$myavdecrain;
	
	// Raw Data Definitions
	$rawlb = array("day" => 0, "mean" => 1, "high" => 2 , "htime" => 3,
		"low" => 4, "ltime" => 5, "hdg" => 6, "cdg" => 7, "rain" => 8,
		"aws" => 9, "hwind" => 10 , "wtime" => 11, "dir" => 12);
		
		$anom = array($myavjanrain,$myavfebrain,$myavmarrain,$myavaprrain,$myavmayrain,$myavjunrain,
		$myavjulrain,$myavaugrain,$myavseprain,$myavoctrain,$myavnovrain,$myavdecrain); 

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
				$raw[$m][0][0][8] = '0'.floatval($dayrn); 
			} elseif (file_exists($loc . $filename) ) {
				$raw[$m][0] = getnoaafile($loc . $filename); 
			}
				if ($current_month AND $show_today){ 
					$raw[$m][0][date("j")-1][8] = '0'.floatval($dayrn); 
				
			} 
		}

	// Start display of info we got
	// Output Table with information
	echo '<table><tr><th class="labels">Day</th>';
	
		// Cycle through the months for a label
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		$mx = $m + $i;
		$mx = $i + 1;
		$yearx = $year;
		$maxdays[$i] = get_days_in_month($mx, $yearx); // sets number of days per month for selected year
		echo '<th class="labels">' . substr( $mnthname[ $raw[$i][1] ], 0, 3 ) . '</th>';
	}
	echo "</tr>\n"; 
	
	// Setup Rainmonth and year totals
	$rainmonth = array();
	$ytd = 0;
	// Cycle through the possible days 
	for ( $day = 0 ; $day < 31 ; $day++ ) {
		echo '<tr><td width="8%" class="reportdt">' . ($day + 1) . '</td>';
		for ($mnt = 0 ; $mnt < 12 ; $mnt++ ) {
			if ($maxdays[$mnt] < $day + 1 ) {
				echo '<td width="7%" class="noday">&nbsp;</td>'; 
			}
			else {
				if ( $raw[$mnt][0][$day][$rawlb['rain']] == "" ) {
					$put = "---";
				}
				else {
					$put = $raw[$mnt][0][$day][$rawlb['rain']];
					$rainmonth[$mnt][0] = $rainmonth[$mnt][0] + $put;
					if ($put > 0.0) {
						$rainmonth[$mnt][1] = $rainmonth[$mnt][1] + 1;
					}
				}
				$ast = ''; if(strpos($put,'*') != FALSE) { $ast = '*'; }
				$cond = (mktime(0,0,0,$mnt+1,$day+1,$year) < mktime(0,0,0,3,8,2009));
				$cond2 = $unitR == 'mm';
				if ($put > 0) {
					if($cond && $cond2) { $put = conv3($put,2,0); } else { $put = conv($put,2,0); }
					echo '<td width="7%" class=" ' . ValueColor($put,$rainvalues).'"' . '>' . $put.$ast .' </td>';
					} 
				else {
					if ($put == "---"){
						echo '<td width="7%" class="reportday">' . $put . '</td>';
					} 
					else {
						echo '<td width="7%" class="reportday">'; 
						if($cond) { echo conv3(0,2,0); } else { echo conv(0,2,0); } 
						echo '</td>'; 
					}
				}				 
			}
		} 
		echo "</tr>\n"; 	
	}
	// We are done with the daily numbers now lets show the totals
	echo '<tr><td width="7%" class="reportttl">Rain Days</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		echo '<td width="7%" class="reportttl">';
		if ($year == date("Y")) {
			if ($rainmonth[$i][0] > 0 && $i+1 != $date_month): echo sprintf("%d", $rainmonth[$i][1]);
			elseif($date_month == $i+1): echo $dayswithrain;
			else: echo '---'; endif;
		} else {
				echo sprintf("%d", $rainmonth[$i][1]);
			}
			echo '</td>';
	}
	echo '</tr>';
	
	echo '<tr><td width="7%" class="reportttl">Month Total <br /> (anomaly)</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		echo '<td width="7%" class="reportttl">'; 
		if ($year == date("Y")) {
				if ($rainmonth[$i][0] > 0 && $i+1 != $date_month): echo conv($rainmonth[$i][0],2,0). '<br /> (' . round($rainmonth[$i][0]/$anom[$i]*100,0) . '%)'; 
				elseif($date_month == $i+1): echo $monthrn;
				else: echo '---'; endif;
			} else {
					echo conv($rainmonth[$i][0],2,0) .'<br /> (' . round($rainmonth[$i][0]/$anom[$i]*100,0) . '%)'; 
				}
			echo '</td>';
	}
	echo '</tr>';
	
	$ytd=0;
	echo '<tr><td width="7%" class="reportttl">Cumulative <br /> (anomaly)</td>';
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		$ytd = $ytd + $rainmonth[$i][0];
		$anomytd = $anomytd + $anom[$i];
		echo '<td width="7%" class="reportttl">';
		if ($rainmonth[$i][0] > 0): echo conv($ytd,2,0) .'<br /> (' . round($ytd/$anomytd*100,0) . '%)';
		else: echo '---'; endif;
		echo '</td>';
	}
	echo '</tr></table>';

$colorband_rows = ceil($colors/15);
$colorband_cols = ceil(($colors+1)/$colorband_rows);
$dp1 = "%.1f"; $dp = "%.0f"; if(!$cond2) { $dp1 = "%.2f"; $dp = "%.2f"; }
	echo '<table><tr><td width="7%" class="separator" colspan="'.($colorband_cols).'" >&nbsp;</td></tr>'; 
	echo '<tr><td width="7%" class="colorband" colspan="'.($colorband_cols).'">Colour Key</td></tr>';
	$i = 0;
	for ($r = 0; $r < $colorband_rows; $r ++){ 
		for ( $j = 0; (($j < $colorband_cols) AND ($i < $colors)) ; $j ++ ) {
			$band = $i;
			if ($i == 0) { 
				echo '<tr><td width="7%" class="levelb_1" >&lt;&nbsp;' . sprintf($dp1,$rainvalues[$i]) . '</td>';
			}
			elseif ($i == 1) {
				echo '<td width="7%" class="levelb_'.($band+1).'"'.$color_text.' > ' . sprintf($dp1,$rainvalues[$i-1]) . " - " .sprintf($dp,$rainvalues[$i]) . '</td>';
			}
			else {
				echo '<td width="7%" class="levelb_'.($band+1).'"'.$color_text.' > ' . sprintf($dp,$rainvalues[$i-1]) . " - " .sprintf($dp,$rainvalues[$i]) . '</td>';
				if (($j == $colorband_cols-1) AND ($r != $colorband_rows)) {
					echo '</tr><tr>';
				}
			}
			$i = $i+1;
		}
	}
	echo '<td width="7%" class="levelb_'.($band+2).'"'.$color_text.' >'. '&gt;'. sprintf($dp,$rainvalues[$i-1]) . '</td></tr></table>'; 
}

//Calculate colors depending on value
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