<?php require('unit-select.php');
	$average = 1; ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("phptags.php");
	$file = 52; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - Daily Rain Averages</title>

	<meta name="description" content="Old v2 - Daily historical rain averages - rain and days of" />

	<?php require('chead.php'); ?>
	<link rel="stylesheet" type="text/css" href="wxreports.css" media="screen" title="screen" />
<?php include_once("ggltrack.php") ?>
</head>

<body>
	<!-- For non-visual user agents: -->
	<div id="top"><a href="#main-copy" class="doNotDisplay doNotPrint">Skip to main content.</a></div>

	<!-- ##### Header ##### -->
	<? require('header.php'); ?>

	<!-- ##### Left Sidebar ##### -->
	<? require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->

<div id="main-copy">

	<div align="center" id="report">
		<h1>Daily Rain Averages/Totals (<?php echo $unitR; ?>)</h1>
		
		<?php $self = 'avhist12.php';
			include("wxrepgen.php");
		
$monthshort = array('Measure', 'Jan', 'Feb', 'March', 'April', 'May', 'June', 'July', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Year');
//Script to display records for each day of the year
$manual_values = array(.5,1,2,5,10,15,20,25,50);
$manual_valuesUS = array(.02,.05,.1,.2,.3,.5,.75,1,2);
if($unitR == 'in') { $manual_values = $manual_valuesUS; }
$loc = $path_dailynoaa; # Location of dailynoaareports
$first_year_of_data = $first_year_of_noaadata;
$rain_units = $unitR;
$rainvalues = $manual_values;
$rndaysvalues = array(10,20,30,40,50,60,70,80,99);
$increments = (count($manual_values))-1;
$colors = $increments + 1;

for($y = 2009; $y <= $date_year; $y++) { //Add past data to the array
	for($m = 1; $m <= 12; $m++) {
		if(date('n') == $m && date('Y') == $y && date('j') != 1) { $filename = "dailynoaareport".".htm"; $daysvalid = date('j')-1; }
		elseif(date('n') == $m && date('Y') == $y && date('j') == 1) { $filename = 'nothing'; }
		else { $filename = "dailynoaareport".date("nY", mktime(0,0,0,$m,1,$y)).".htm"; $daysvalid = date('t',mktime(0,0,0,$m,1,$y)); }
		if(file_exists($filename)) {
			$raw[$m][$y] = getnoaafile($filename);
			for($d = 1; $d <= $daysvalid; $d++) {
				$rawrain[$d][$m][$y] = $raw[$m][$y][$d-1][8]+0.001;
				if($rawrain[$d][$m][$y] > 0) { $raincount0[$d][$m][$y] = 1; } else { $raincount0[$d][$m][$y] = 0; }
				if($rawrain[$d][$m][$y] > 0.1) { $raincount[$d][$m][$y] = 1; } else { $raincount[$d][$m][$y] = 0; }
				if($rawrain[$d][$m][$y] > 1) { $raincount1[$d][$m][$y] = 1; } else { $raincount1[$d][$m][$y] = 0; }
				if($rawrain[$d][$m][$y] > 10) { $raincount10[$d][$m][$y] = 1; } else { $raincount10[$d][$m][$y] = 0; }
				if(isset($_GET['rnday_custom'])) {
					$cust_rnday = round($_GET['rnday_custom'],1);
					if($rawrain[$d][$m][$y] > $cust_rnday) { $raincountc[$d][$m][$y] = 1; } else { $raincountc[$d][$m][$y] = 0; }
				}
			}
		}
	}
}
$dd = date('j'); $mm = date('n'); $yy = date('Y'); // Now Add today's values to the array
$t_rn = floatval($dayrn)+0.001;
$rawrain[$dd][$mm][$yy] = $t_rn;
if($t_rn > 0) { $raincount0[$dd][$mm][$yy] = 1; } else { $raincount0[$dd][$mm][$yy] = 0; }
if($t_rn > 0.1) { $raincount[$dd][$mm][$yy] = 1; } else { $raincount[$dd][$mm][$yy] = 0; }
if($t_rn > 1) { $raincount1[$dd][$mm][$yy] = 1; } else { $raincount1[$dd][$mm][$yy] = 0; }
if($t_rn > 10) { $raincount10[$dd][$mm][$yy] = 1; } else { $raincount10[$dd][$mm][$yy] = 0; }
if(isset($_GET['rnday_custom'])) { if($t_rn > $cust_rnday) { $raincountc[$dd][$mm][$yy] = 1; } else { $raincountc[$dd][$mm][$yy] = 0; } }

echo '<b>NB: </b> These are averages over the operation lifetime of this weather station, not <a href="wxaverages.php">long-term climate averages</a>.';
// Produce the rainfall table
echo '<h2>Average Rainfall (2009-',date('Y'),')</h2><table class="table1" width="98%" cellpadding="2"><tr><th class="labels">Day</th>';
for($i = 1; $i < 14; $i++) { echo '<th class="labels" width="7%"><b>', $monthshort[$i], '</b></th>'; } echo '</tr>';
for($r = 1; $r < 32; $r++) {
	echo '<tr><td class="reportdt">', $r, '</td>';
	for($m = 1; $m <= 12; $m++) {
		if($r <= date('t',mktime(0,0,0,$m,1,2009))) {
			$put[$m][$r] = mean($rawrain[$r][$m]); $puty[$r][$m] = mean($rawrain[$r][$m]);
			$colstyle = ValueColor4(conv($put[$m][$r],2,0),$rainvalues); if($m == 12) { $colstyle = ValueColor5(conv($put[$m][$r],2,0),$rainvalues); }
			echo '<td class="', $colstyle, '">', conv($put[$m][$r],2,0), '</td>';
		}
		else { echo '<td class="noday" >&nbsp;</td>'; }
	}
	$puty2[$r] = mean($puty[$r]);
	$colstyle = ValueColor4(conv($puty2[$r],2,0),$rainvalues);
	echo '<td class="', $colstyle, '">', conv($puty2[$r],2,0), '</td>';
	echo '</tr>';
}
echo '</tr><tr><td colspan="14">&nbsp;</td></tr>';
echo '<tr><th class="labels">Month</th>'; for($i = 1; $i < 14; $i++) { echo '<th class="labels" ><b>', $monthshort[$i], '</b></th>'; }
echo '</tr><tr><td class="reportdt">Total</td>';
for($m = 1; $m <= 12; $m++) {
	$put4[$m] = average($put[$m]);
	$colstyle = ValueColor4(conv($put4[$m],1,0), $rainvalues);
	echo '<td class="', $colstyle, '" >', conv($put4[$m],1,0), '</td>';
}
echo '<td class="yeartotals">', $stbfix, '<acronym title="Month mean: ', conv(mean($put4),2,0), '">', conv(average($put4),2,0),	'</acronym>', $enbfix, '</td>';
echo '</tr></table>';

//Produce rain day table
echo '<h2>Average Raindays (2009-',date('Y'),')</h2><table class="table1" width="98%" cellpadding="2"><tr><th class="labels">Day</th>';
for($i = 1; $i < 14; $i++) { echo '<th class="labels" width="7%"><b>', $monthshort[$i], '</b></th>'; } echo '</tr>';
for($r = 1; $r < 32; $r++) {
	echo '<tr><td class="reportdt">', $r, '</td>';
	for($m = 1; $m <= 12; $m++) {
		if($r <= date('t',mktime(0,0,0,$m,1,2009))) {
			$put3[$m][$r] = mean($raincount[$r][$m]); $puty[$r][$m] = mean($raincount[$r][$m]);
			$colstyle = ValueColor4($put3[$m][$r]*100,$rndaysvalues); if($m == 12) { $colstyle = ValueColor5($put3[$m][$r]*100,$rndaysvalues); }
			echo '<td class="', $colstyle, '">', conv4($put3[$m][$r],2,0), '</td>';
		}
		else { echo '<td class="noday" >&nbsp;</td>'; }
	}
	$puty2[$r] = mean($puty[$r]);
	$colstyle = ValueColor4($puty2[$r]*100,$rndaysvalues);
	echo '<td class="', $colstyle, '">', conv4($puty2[$r],2,0), '</td>';
	echo '</tr>';
}
echo '</tr><tr><td colspan="14">&nbsp;</td></tr>';
echo '<tr><th class="labels">Month</th>'; for($i = 1; $i < 14; $i++) { echo '<th class="labels" ><b>', $monthshort[$i], '</b></th>'; }
echo '</tr><tr><td class="reportdt">Days</td>';
for($m = 1; $m <= 12; $m++) {
	$put5[$m] = average($put3[$m]);
	$colstyle = ValueColor4($put5[$m], $rainvalues);
	echo '<td class="', $colstyle, '" >', round($put5[$m]), '</td>';
}
echo '<td class="yeartotals">', $stbfix, '<acronym title="Month mean: ', round(mean($put5)), '">', round(average($put5)),	'</acronym>', $enbfix, '</td>';
echo '</tr></table>';

//Produce rain day >1mm table
echo '<h2>Average Raindays >1mm (2009-',date('Y'),')</h2><table class="table1" width="98%" cellpadding="2"><tr><th class="labels">Day</th>';
for($i = 1; $i < 14; $i++) { echo '<th class="labels" width="7%"><b>', $monthshort[$i], '</b></th>'; } echo '</tr>';
for($r = 1; $r < 32; $r++) {
	echo '<tr><td class="reportdt">', $r, '</td>';
	for($m = 1; $m <= 12; $m++) {
		if($r <= date('t',mktime(0,0,0,$m,1,2009))) {
			$put3[$m][$r] = mean($raincount1[$r][$m]); $puty[$r][$m] = mean($raincount1[$r][$m]);
			$colstyle = ValueColor4($put3[$m][$r]*100,$rndaysvalues); if($m == 12) { $colstyle = ValueColor5($put3[$m][$r]*100,$rndaysvalues); }
			echo '<td class="', $colstyle, '">', conv4($put3[$m][$r],2,0), '</td>';
		}
		else { echo '<td class="noday" >&nbsp;</td>'; }
	}
	$puty2[$r] = mean($puty[$r]);
	$colstyle = ValueColor4($puty2[$r]*100,$rndaysvalues);
	echo '<td class="', $colstyle, '">', conv4($puty2[$r],2,0), '</td>';
	echo '</tr>';
}
echo '</tr><tr><td colspan="14">&nbsp;</td></tr>';
echo '<tr><th class="labels">Month</th>'; for($i = 1; $i < 14; $i++) { echo '<th class="labels" ><b>', $monthshort[$i], '</b></th>'; }
echo '</tr><tr><td class="reportdt">Days</td>';
for($m = 1; $m <= 12; $m++) {
	$put5[$m] = average($put3[$m]);
	$colstyle = ValueColor4($put5[$m], $rainvalues);
	echo '<td class="', $colstyle, '" >', round($put5[$m]), '</td>';
}
echo '<td class="yeartotals">', $stbfix, '<acronym title="Month mean: ', round(mean($put5)), '">', round(average($put5)),	'</acronym>', $enbfix, '</td>';
echo '</tr></table>';

//Produce rain day >10mm table
echo '<h2>Average Raindays >10mm (2009-',date('Y'),')</h2><table class="table1" width="98%" cellpadding="2"><tr><th class="labels">Day</th>';
for($i = 1; $i < 14; $i++) { echo '<th class="labels" width="7%"><b>', $monthshort[$i], '</b></th>'; } echo '</tr>';
for($r = 1; $r < 32; $r++) {
	echo '<tr><td class="reportdt">', $r, '</td>';
	for($m = 1; $m <= 12; $m++) {
		if($r <= date('t',mktime(0,0,0,$m,1,2009))) {
			$put3[$m][$r] = mean($raincount10[$r][$m]); $puty[$r][$m] = mean($raincount10[$r][$m]);
			$colstyle = ValueColor4($put3[$m][$r]*100,$rndaysvalues); if($m == 12) { $colstyle = ValueColor5($put3[$m][$r]*100,$rndaysvalues); }
			echo '<td class="', $colstyle, '">', conv4($put3[$m][$r],2,0), '</td>';
		}
		else { echo '<td class="noday" >&nbsp;</td>'; }
	}
	$puty2[$r] = mean($puty[$r]);
	$colstyle = ValueColor4($puty2[$r]*100,$rndaysvalues);
	echo '<td class="', $colstyle, '">', conv4($puty2[$r],2,0), '</td>';
	echo '</tr>';
}
echo '</tr><tr><td colspan="14">&nbsp;</td></tr>';
echo '<tr><th class="labels">Month</th>'; for($i = 1; $i < 14; $i++) { echo '<th class="labels" ><b>', $monthshort[$i], '</b></th>'; }
echo '</tr><tr><td class="reportdt">Days</td>';
for($m = 1; $m <= 12; $m++) {
	$put5[$m] = average($put3[$m]);
	$colstyle = ValueColor4($put5[$m], $rainvalues);
	echo '<td class="', $colstyle, '" >', round($put5[$m],1), '</td>';
}
echo '<td class="yeartotals">', $stbfix, '<acronym title="Month mean: ', round(mean($put5),1), '">', round(average($put5)),	'</acronym>', $enbfix, '</td>';
echo '</tr></table>';

//Produce rain day >custom table
echo '<br /><h2>Average Raindays >'; if(isset($_GET['rnday_custom'])) { echo $cust_rnday, ' mm</h2>'; } else { echo 'NOT SET</h2>'; }
echo '<form action="" method="get">Enter amount <input type="text" size="4" maxlength="4" name="rnday_custom" /><input type="submit" value="Go" /></form>';
echo '<table class="table1" width="98%" cellpadding="2"><tr><th class="labels">Day</th>';
for($i = 1; $i < 14; $i++) { echo '<th class="labels" width="7%"><b>', $monthshort[$i], '</b></th>'; } echo '</tr>';
for($r = 1; $r < 32; $r++) {
	echo '<tr><td class="reportdt">', $r, '</td>';
	for($m = 1; $m <= 12; $m++) {
		if($r <= date('t',mktime(0,0,0,$m,1,2009))) {
			$put3[$m][$r] = mean($raincountc[$r][$m]); $puty[$r][$m] = mean($raincountc[$r][$m]);
			$colstyle = ValueColor4($put3[$m][$r]*100,$rndaysvalues); if($m == 12) { $colstyle = ValueColor5($put3[$m][$r]*100,$rndaysvalues); }
			echo '<td class="', $colstyle, '">', conv4($put3[$m][$r],2,0), '</td>';
		}
		else { echo '<td class="noday" >&nbsp;</td>'; }
	}
	$puty2[$r] = mean($puty[$r]);
	$colstyle = ValueColor4($puty2[$r]*100,$rndaysvalues);
	echo '<td class="', $colstyle, '">', conv4($puty2[$r],2,0), '</td>';
	echo '</tr>';
}
echo '</tr><tr><td colspan="14">&nbsp;</td></tr>';
echo '<tr><th class="labels">Month</th>'; for($i = 1; $i < 14; $i++) { echo '<th class="labels" ><b>', $monthshort[$i], '</b></th>'; }
echo '</tr><tr><td class="reportdt">Days</td>';
for($m = 1; $m <= 12; $m++) {
	$put5[$m] = average($put3[$m]);
	$colstyle = ValueColor4($put5[$m], $rainvalues);
	echo '<td class="', $colstyle, '" >', round($put5[$m],1), '</td>';
}
echo '<td class="yeartotals">', $stbfix, '<acronym title="Month mean: ', round(mean($put5),1), '">', round(average($put5)),	'</acronym>', $enbfix, '</td>';
echo '</tr></table>';

//Calculate colors depending on value
function ValueColor4($value,$values) {
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

function ValueColor5($value,$values) {
	$limit = count($values);
	if ($value == 0){
		return 'reportday2';
	}
	if ($value < $values[0]) {
	return 'levelb2_1';
	} 
	for ($i = 1; $i < $limit ; $i++){
		if ($value <= $values[$i]) {
		return 'levelb2_'.($i+1);
		}
	}
	return 'levelb2_'.($limit+1);
}

?>
</div>
</div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>
</body>
</html>