<?php require('unit-select.php');
	$average = 1; ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("phptags.php");
	$file = 54; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - Daily Temperature Averages</title>

	<meta name="description" content="Old v2 - Daily historical temperature averages - min, max, mean, diurnal range and more" />

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
		<h1>Temperature Averages (&deg;<?php echo $unitT; ?>)</h1>
		
		<?php $self = 'avhist14.php';
			include("wxrepgen.php");
		
$monthshort = array('Measure', 'Jan', 'Feb', 'March', 'April', 'May', 'June', 'July', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Year');

$temprange_start = -5; //Set up colour scheme
$temprange_increment = 5;
if($unitT == 'F') { $temprange_start = 20; $temprange_increment = 10; }
$increments = 8;
for ( $i = 0; $i < $increments; $i++ ) {
	$tempvalues[$i] = $temprange_start + $temprange_increment*$i;
}

for($y = 2009; $y <= $date_year; $y++) { //Add past data to the array
	for($m = 1; $m <= 12; $m++) {
		if(date('n') == $m && date('Y') == $y && date('j') != 1) { $filename = "dailynoaareport".".htm"; $daysvalid = date('j')-1; }
		elseif(date('n') == $m && date('Y') == $y && date('j') == 1) { $filename = 'nothing'; }
		else { $filename = "dailynoaareport".date("nY", mktime(0,0,0,$m,1,$y)).".htm"; $daysvalid = date('t',mktime(0,0,0,$m,1,$y)); }
		if(file_exists($filename)) {
			$raw[$m][$y] = getnoaafile($filename);
			for($d = 1; $d <= $daysvalid; $d++) {
				$rawtmin[$d][$m][$y] = $raw[$m][$y][$d-1][4]+0.001;
				$rawtmax[$d][$m][$y] = $raw[$m][$y][$d-1][2]+0.001;
				$rawtmean[$d][$m][$y] = $raw[$m][$y][$d-1][1]+0.001;
				$rawtrange[$d][$m][$y] = $rawtmax[$d][$m][$y] - $rawtmin[$d][$m][$y];
				if($rawtmin[$d][$m][$y] < 0) { $days_lt_0[$d][$m][$y] = 1; } else { $days_lt_0[$d][$m][$y] = 0; }
				if($rawtmax[$d][$m][$y] > 25) { $days_gt_25[$d][$m][$y]= 1; } else { $days_gt_25[$d][$m][$y]= 0; }
			}
		}
	}
}
$dd = date('j'); $mm = date('n'); $yy = date('Y'); // Now Add today's values to the array
$rawtmin[$dd][$mm][$yy] = floatval($mintemp)+0.001;
$rawtmax[$dd][$mm][$yy] = floatval($maxtemp)+0.001;
$rawtmean[$dd][$mm][$yy] = floatval($avtempsincemidnight)+0.001;
if(intval(date('H')) > 15) { $rawtrange[$dd][$mm][$yy] = floatval($maxtemp-$mintemp+0.001); }
if(floatval($mintemp) < 0) { $days_lt_0[$dd][$mm][$yy]= 1; } else { $days_lt_0[$dd][$mm][$yy] = 0; }
if(floatval($maxtemp) > 25) { $days_gt_25[$dd][$mm][$yy] = 1; } else { $days_gt_25[$dd][$mm][$yy] = 0; }

$oldt = file($absRoot.'extraTdata.csv'); // Special addition of available 2008 data
for($l = 0; $l < count($oldt); $l++) {
	$oldtl = explode(',', $oldt[$l]);
	$d = intval($oldtl[1]); $m = intval($oldtl[0]); $y = intval($oldtl[2008]);
	if($m != 1) {
		$rawtmin[$d][$m][$y] = floatval($oldtl[2]); $rawtmax[$d][$m][$y] = floatval($oldtl[3]);
		$rawtmean[$d][$m][$y] = floatval($oldtl[4]); $rawtrange[$d][$m][$y] = floatval($oldtl[3] - $oldtl[2]);
		if(floatval($oldtl[2]) < 0) { $days_lt_0[$d][$m][$y] = 1; } else { $days_lt_0[$d][$m][$y] = 0; }
		if(floatval($oldtl[3]) > 25) { $days_gt_25[$d][$m][$y] = 1; } else { $days_gt_25[$d][$m][$y] = 0; }
	}
}

echo '<b>NB: </b> These are averages over the operation lifetime of this weather station, not <a href="wxaverages.php">long-term climate averages</a>.<br />';

// Produce the tables
echo 'Total number of records = <b>', round((mktime()-mktime(1,1,1,3,20,2008))/86400), ' </b>&nbsp;(20 Mar 2008 - ',date('d M Y',mktime()),')
	<h2>Daily Minima and Maxima temperature averages (from 00-00)</h2>
	<table class="table1" width="95%" cellpadding="2"><tr><th class="labels" rowspan="2">Day</th>';
for($i = 1; $i < 13; $i++) { echo '<th class="labels" colspan="2"><b>', $monthshort[$i], '</b></th>
	'; } echo '</tr><tr>';
for($i = 0; $i < 12; $i++) { echo '<th class="labels" width="4%"><b>Min</b></th><th class="labels" width="4%"><b>Max</b></th>
	'; } echo '</tr>';
for($r = 1; $r < 32; $r++) {
	echo '<tr><td class="reportdt">', $r, '</td>';
	for($m = 1; $m <= 12; $m++) {
		if($r <= date('t',mktime(0,0,0,$m,1,2009))) {
			$put = mean($rawtmin[$r][$m]);
			$newput2[$m] = $newput2[$m] + array_sum($rawtmin[$r][$m]); $newput3[$m] = $newput3[$m] + count($rawtmin[$r][$m]); //for monthly data
			$colstyle = ValueColor3(conv($put,1,0));
			echo '<td class="', $colstyle, '">', conv($put,1,0), '</td>
				';
			$put = mean($rawtmax[$r][$m]);
			$newput4[$m] = $newput4[$m] + array_sum($rawtmax[$r][$m]); $newput5[$m] = $newput5[$m] + count($rawtmax[$r][$m]); //for monthly data
			$colstyle = ValueColor(conv($put,1,0));
			echo '<td class="', $colstyle, '">', conv($put,1,0), '</td>
				';
		}
		else { echo '<td style="border-right:2px solid black; border-left:2px solid black;" class="noday" colspan="2">&nbsp;</td>
			'; }
	}
	echo '</tr>';
}
echo '<tr><td style="height:60px" rowspan="2" class="reportdt">Month<br />Mean</td>';
for($m = 1; $m <= 12; $m++) {
	$put = $newput2[$m]/$newput3[$m]; $colstyle = ValueColor(conv($put,1,0));
	echo '<td style="border-top:3px solid black; height:40px; font-size:120%; border-left:2px solid black" class="', $colstyle, '">', conv($put,1,0), '</td>
		';
	$put = $newput4[$m]/$newput5[$m]; $colstyle = ValueColor(conv($put,1,0));
	echo '<td style="border-top:3px solid black; height:40px; font-size:120%" class="', $colstyle, '">', conv($put,1,0), '</td>
		';
}
echo '</tr><tr>'; for($i = 1; $i < 13; $i++) { echo '<td class="labels" colspan="2"><b>', $monthshort[$i], '</b></td>
	'; }
echo '</tr></table><br />

	<h2>Daily Mean temperature averages (from 00-00)</h2><table class="table1" cellpadding="3"><tr><th width="4%" class="labels">Day</th>';
for($i = 1; $i < 13; $i++) { echo '<th width="8%" class="labels"><b>', $monthshort[$i], '</b></th>
	'; } echo '</tr>';
for($r = 1; $r < 32; $r++) {
	echo '<tr><td width="4%" class="reportdt">', $r, '</td>';
	for($m = 1; $m <= 12; $m++) {
		if($r <= date('t',mktime(0,0,0,$m,1,2009))) {
			$put = mean($rawtmean[$r][$m]);
			$put6[$m] = $put6[$m] + array_sum($rawtmean[$r][$m]); $put7[$m] = $put7[$m] + count($rawtmean[$r][$m]); //for monthly data
			$colstyle = ValueColor(conv($put,1,0));
			echo '<td width="8%" class="', $colstyle, '">', conv($put,1,0), '</td>
				';
		}
		else { echo '<td width="8%" class="noday">&nbsp;</td>'; }
	}
	echo '</tr>';
}
echo '<tr><td rowspan="2" width="4%" style="height:60px" class="reportdt">Month<br />Mean</td>';
for($m = 1; $m <= 12; $m++) {
	$put = $put6[$m]/$put7[$m]; $colstyle = ValueColor(conv($put,1,0));
	echo '<td width="8%" style="border-top:3px solid black; height:40px; font-size:120%" class="', $colstyle, '">', conv($put,1,0), '</td>
		';
}
echo '</tr><tr>'; for($i = 1; $i < 13; $i++) { echo '<td width="8%" class="labels"><b>', $monthshort[$i], '</b></td>
	'; }
echo '</tr></table><br />

	<h2>Daily Diurnal temperature range averages</h2><table class="table1" cellpadding="3"><tr><th width="4%" class="labels">Day</th>';
for($i = 1; $i < 13; $i++) { echo '<th width="8%" class="labels"><b>', $monthshort[$i], '</b></th>
	'; } echo '</tr>';
for($r = 1; $r < 32; $r++) {
	echo '<tr><td width="4%" class="reportdt">', $r, '</td>';
	for($m = 1; $m <= 12; $m++) {
		if($r <= date('t',mktime(0,0,0,$m,1,2009))) {
			$put = mean($rawtrange[$r][$m]);
			$put8[$m] += array_sum($rawtrange[$r][$m]); $put9[$m] += count($rawtrange[$r][$m]); //for monthly data
			$colstyle = ValueColor(conv($put,1,0));
			echo '<td width="8%" class="', $colstyle, '">', conv($put,1,0), '</td>
				';
		}
		else { echo '<td width="8%" class="noday">&nbsp;</td>'; }
	}
	echo '</tr>';
}
echo '<tr><td rowspan="2" width="4%" style="height:60px" class="reportdt">Month<br />Mean</td>';
for($m = 1; $m <= 12; $m++) {
	$put = $put8[$m]/$put9[$m]; $colstyle = ValueColor(conv($put,1,0));
	echo '<td width="8%" style="border-top:3px solid black; height:40px; font-size:120%" class="', $colstyle, '">', conv($put,1,0), '</td>
		';
}
echo '</tr><tr>'; for($i = 1; $i < 13; $i++) { echo '<td width="8%" class="labels"><b>', $monthshort[$i], '</b></td>
	'; }
echo '</tr></table><br />

	<h2>Average air frosts (Minimum &lt; 0) and heat days (Maximum &gt; 25)</h2>
	<table class="table1" width="95%" cellpadding="2"><tr><th class="labels" rowspan="2">Day</th>';
for($i = 1; $i < 13; $i++) { echo '<th class="labels" colspan="2"><b>', $monthshort[$i], '</b></th>
	'; } echo '</tr><tr>';
for($i = 0; $i < 12; $i++) { echo '<th class="labels" width="4%"><b>AF</b></th><th class="labels" width="4%"><b>HD</b></th>
	'; } echo '</tr>';
for($r = 1; $r < 32; $r++) {
	echo '<tr><td class="reportdt">', $r, '</td>';
	for($m = 1; $m <= 12; $m++) {
		if($r <= date('t',mktime(0,0,0,$m,1,2009))) {
			$put = mean($days_lt_0[$r][$m]);
			$monput1[$m] += $put; //for monthly data
			if($put < 0.1) { $colstyle = 'reportday'; } else { $colstyle = ValueColor($put*10); }
			echo '<td style="border-left:2px solid black;" class="', $colstyle, '">', $put, '</td>
				';
			$put = mean($days_gt_25[$r][$m]);
			$monput2[$m] += $put; //for monthly data
			if($put < 0.1) { $colstyle = 'reportday'; } else { $colstyle = ValueColor($put*10); }
			echo '<td class="', $colstyle, '">', $put, '</td>
				';
		}
		else { echo '<td style="border-left:2px solid black;" class="noday" colspan="2">&nbsp;</td>
			'; }
	}
	echo '</tr>';
}
echo '<tr><td style="height:60px" rowspan="2" class="reportdt">Month<br />Mean</td>';
for($m = 1; $m <= 12; $m++) {
	$put = $monput1[$m]; if($put < 0.1) { $colstyle = 'reportday'; } else { $colstyle = ValueColor($put); }
	echo '<td style="border-top:3px solid black; height:40px; font-size:120%; border-left:2px solid black;" class="', $colstyle, '">', conv3($put,0,0), '</td>
		';
	$put = $monput2[$m]; if($put < 0.1) { $colstyle = 'reportday'; } else { $colstyle = ValueColor($put); }
	echo '<td style="border-top:3px solid black; height:40px; font-size:120%" class="', $colstyle, '">', conv3($put,0,0), '</td>
		';
}
echo '</tr><tr>'; for($i = 1; $i < 13; $i++) { echo '<td class="labels" colspan="2"><b>', $monthshort[$i], '</b></td>
	'; }
echo '</tr></table>';

//Calculate colours depending on value
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
?>
</div>
</div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>
</body>
</html>