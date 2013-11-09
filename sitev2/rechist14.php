<?php require('unit-select.php');
	$record = 1; ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("phptags.php");
	$file = 54; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - Daily Temperature Records</title>

	<meta name="description" content="Old v2 - Daily historical temperature records - min/max/mean" />

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
		<h1>Daily Temperature Records (<?php echo '&deg;'.$unitT; ?>)</h1>
		
		<?php $self = 'rechist14.php';
			include("wxrepgen.php");
		
$monthshort = array('Measure', 'Jan', 'Feb', 'March', 'April', 'May', 'June', 'July', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
//Script to display records for each day of the year
$temprange_start = array(20, -5); # (Farenheit, Celcius)
$temprange_increment = array(10, 5); # (Farenheit, Celcius)
$increments = 8;
$uomTemp = '&deg;'.$unitT;
$temptype = array("C","F") ;
$temptype = $temptype[$uomTemp == "&deg;F"];
$temprange_start = $temprange_start[((strtoupper($temptype)) != "F")] ;
$temprange_increment = $temprange_increment[((strtoupper($temptype)) != "F")] ;
$tempvalues = array($temprange_start);
for ( $i = 0; $i < $increments ; $i ++ ) {
	$tempvalues[$i+1] = $tempvalues[$i] + $temprange_increment;
}
$colors = $increments + 1;
$loc = $path_dailynoaa; # Location of dailynoaareports
$first_year_of_data = $first_year_of_noaadata;

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
			}
		}
	}
}
$dd = date('j'); $mm = date('n'); $yy = date('Y'); // Now Add today's values to the array
$rawtmin[$dd][$mm][$yy] = floatval($mintemp)+0.001;
$rawtmax[$dd][$mm][$yy] = floatval($maxtemp)+0.001;
$rawtmean[$dd][$mm][$yy] = floatval($avtempsincemidnight)+0.001;
if(intval(date('H')) > 15) { $rawtrange[$dd][$mm][$yy] = floatval($maxtemp-$mintemp+0.001); }

$oldt = file($absRoot.'extraTdata.csv'); // Special addition of available 2008 temperature data
for($l = 0; $l < count($oldt); $l++) {
	$oldtl = explode(',', $oldt[$l]);
	if(intval($oldtl[0]) != 1) {
		$rawtmin[$oldtl[1]][$oldtl[0]][2008] = floatval($oldtl[2]); $rawtmax[$oldtl[1]][$oldtl[0]][2008] = floatval($oldtl[3]);
		$rawtmean[$oldtl[1]][$oldtl[0]][2008] = floatval($oldtl[4]); $rawtrange[$oldtl[1]][$oldtl[0]][2008] = floatval($oldtl[3] - $oldtl[2]);
	}
}

$hide = 0; if(isset($_GET['year_hide'])) { $hide = $_GET['year_hide']; }
function message_rechist($pos) {
	global $hide;
	$message_rechist = '<a name="'.$pos.'"></a>';
	if($hide == 0) { $message_rechist .= 'Hover over record to reveal year, or <a href=?year_hide=1#' . $pos . '>Display all</a>'; }
	else { $message_rechist .= '<a href=?year_hide=0#' . $pos . '>Hide years</a>'; }
	echo $message_rechist;
}

// Produce the min/max temperature table
message_rechist(1);
echo '<h2>Coldest / Hottest (2008-',date('Y'),')</h2><table class="table1" width="95%" cellpadding="3"><tr><th class="labels" rowspan="2">Day</th>';
for($i = 1; $i < 13; $i++) { echo '<th class="labels" colspan="2"><b>', $monthshort[$i], '</b></th>'; } echo '</tr><tr>';
for($i = 0; $i < 12; $i++) { echo '<th class="labels" width="4%"><b>Min</b></th><th class="labels" width="4%"><b>Max</b></th>'; } echo '</tr>';
for($r = 1; $r < 32; $r++) {
	echo '<tr><td class="reportdt">', $r, '</td>';
	for($m = 1; $m <= 12; $m++) {
		if($r <= date('t',mktime(0,0,0,$m,1,2009))) {
			$put[$m][$r] = min($rawtmin[$r][$m]);
			if($m == 1) { $colstyle = ValueColor(conv($put[$m][$r],1,0)); } else { $colstyle = ValueColor3(conv($put[$m][$r],1,0)); }
			echo '<td class="', $colstyle, '">', yr_togg(conv($put[$m][$r],1,0), array_search($put[$m][$r],$rawtmin[$r][$m])), '</td>';
			$put2[$m][$r] = max($rawtmax[$r][$m]);
			if($m == 12) { $colstyle = ValueColor(conv($put2[$m][$r],1,0)); } else { $colstyle = ValueColor2(conv($put2[$m][$r],1,0)); }
			echo '<td class="', $colstyle, '">', yr_togg(conv($put2[$m][$r],1,0), array_search($put2[$m][$r],$rawtmax[$r][$m])), '</td>';
		}
		else { echo '<td class="noday" colspan="2">&nbsp;</td>'; }
	}
	echo '</tr>';
}
//echo '<tr><th class="labels">Day</th>'; for($i = 1; $i < 13; $i++) { echo '<th class="labels" colspan="2"><b>', $monthshort[$i], '</b></th>'; }
echo '</tr><tr><td colspan="25">&nbsp;</td></tr>';
echo '<tr><th class="labels">Month</th>'; for($i = 1; $i < 13; $i++) { echo '<th class="labels" colspan="2"><b>', $monthshort[$i], '</b></th>'; }
echo '</tr><tr><td class="reportdt">Min</td>';
for($m = 1; $m <= 12; $m++) {
	$put3[$m] = min($put[$m]);
	if($m == 1) { $colstyle = ValueColor(conv($put3[$m],1,0)); } else { $colstyle = ValueColor3(conv($put3[$m],1,0)); }
	echo '<td class="', $colstyle, '" colspan="2">', yr_togg(conv($put3[$m],1,0), array_search($put3[$m],$put[$m]) . ', ' . array_search($put3[$m],$rawtmin[array_search($put3[$m],$put[$m])][$m])), '</td>';
}
echo '</tr><tr><td class="reportdt">Max</td>';
for($m = 1; $m <= 12; $m++) {
	$put4[$m] = max($put2[$m]);
	if($m == 1) { $colstyle = ValueColor(conv($put4[$m],1,0)); } else { $colstyle = ValueColor3(conv($put4[$m],1,0)); }
	echo '<td class="', $colstyle, '" colspan="2">', yr_togg(conv($put4[$m],1,0), array_search($put4[$m],$put2[$m]) . ', ' . array_search($put4[$m],$rawtmax[array_search($put4[$m],$put2[$m])][$m])), '</td>';
}
echo '</tr><tr><td class="reportdt" style="border-top:5px solid gray">Range</td>';
for($m = 1; $m <= 12; $m++) {
	$put5 = $put4[$m] - $put3[$m];
	if($m == 1) { $colstyle = ValueColor(conv($put5,1,0)); } else { $colstyle = ValueColor3(conv($put5,1,0)); }
	echo '<td class="', $colstyle, '" colspan="2" style="border-top:5px solid gray">', conv($put5,1,0);
}
echo '</td></tr></table>';

// Produce the min/max(night/day) temperature table
message_rechist(2);
echo '<h2>Warmest night / Coldest day</h2><table class="table1" width="95%" cellpadding="3"><tr><th class="labels" rowspan="2">Day</th>';
for($i = 1; $i < 13; $i++) { echo '<th class="labels" colspan="2"><b>', $monthshort[$i], '</b></th>'; } echo '</tr><tr>';
for($i = 0; $i < 12; $i++) { echo '<th class="labels" width="4%"><b>Max</b></th><th class="labels" width="4%"><b>Min</b></th>'; } echo '</tr>';
for($r = 1; $r < 32; $r++) {
	echo '<tr><td class="reportdt">', $r, '</td>';
	for($m = 1; $m <= 12; $m++) {
		if($r <= date('t',mktime(0,0,0,$m,1,2009))) {
			$put[$m][$r] = max($rawtmin[$r][$m]);
			if($m == 1) { $colstyle = ValueColor(conv($put[$m][$r],1,0)); } else { $colstyle = ValueColor3(conv($put[$m][$r],1,0)); }
			echo '<td class="', $colstyle, '">', yr_togg(conv($put[$m][$r],1,0), array_search($put[$m][$r],$rawtmin[$r][$m])), '</td>';
			$put2[$m][$r] = min($rawtmax[$r][$m]);
			if($m == 12) { $colstyle = ValueColor(conv($put2[$m][$r],1,0)); } else { $colstyle = ValueColor2(conv($put2[$m][$r],1,0)); }
			echo '<td class="', $colstyle, '">', yr_togg(conv($put2[$m][$r],1,0), array_search($put2[$m][$r],$rawtmax[$r][$m])), '</td>';
		}
		else { echo '<td class="noday" colspan="2">&nbsp;</td>'; }
	}
	echo '</tr>';
}
echo '</tr><tr><td colspan="25">&nbsp;</td></tr>';
echo '<tr><th class="labels">Month</th>'; for($i = 1; $i < 13; $i++) { echo '<th class="labels" colspan="2"><b>', $monthshort[$i], '</b></th>'; }
echo '</tr><tr><td class="reportdt">Max</td>';
for($m = 1; $m <= 12; $m++) {
	$put3[$m] = max($put[$m]);
	if($m == 1) { $colstyle = ValueColor(conv($put3[$m],1,0)); } else { $colstyle = ValueColor3(conv($put3[$m],1,0)); }
	echo '<td class="', $colstyle, '" colspan="2">', yr_togg(conv($put3[$m],1,0), array_search($put3[$m],$put[$m]) . ', ' . array_search($put3[$m],$rawtmin[array_search($put3[$m],$put[$m])][$m])), '</td>';
}
echo '</tr><tr><td class="reportdt">Min</td>';
for($m = 1; $m <= 12; $m++) {
	$put4[$m] = min($put2[$m]);
	if($m == 1) { $colstyle = ValueColor(conv($put4[$m],1,0)); } else { $colstyle = ValueColor3(conv($put4[$m],1,0)); }
	echo '<td class="', $colstyle, '" colspan="2">', yr_togg(conv($put4[$m],1,0), array_search($put4[$m],$put2[$m]) . ', ' . array_search($put4[$m],$rawtmax[array_search($put4[$m],$put2[$m])][$m])), '</td>';
}
echo '</tr><tr><td class="reportdt" style="border-top:5px solid gray">Range</td>';
for($m = 1; $m <= 12; $m++) {
	$put5 = $put4[$m] - $put3[$m];
	if($m == 1) { $colstyle = ValueColor(conv($put5,1,0)); } else { $colstyle = ValueColor3(conv($put5,1,0)); }
	echo '<td class="', $colstyle, '" colspan="2" style="border-top:5px solid gray">', conv($put5,1,0);
}
echo '</td></tr></table>';

// Produce the min/max(mean) temperature table
message_rechist(3);
echo '<h2>Extreme means</h2><table class="table1" width="95%" cellpadding="3"><tr><th class="labels" rowspan="2">Day</th>';
for($i = 1; $i < 13; $i++) { echo '<th class="labels" colspan="2"><b>', $monthshort[$i], '</b></th>'; } echo '</tr><tr>';
for($i = 0; $i < 12; $i++) { echo '<th class="labels" width="4%"><b>Min</b></th><th class="labels" width="4%"><b>Max</b></th>'; } echo '</tr>';
for($r = 1; $r < 32; $r++) {
	echo '<tr><td class="reportdt">', $r, '</td>';
	for($m = 1; $m <= 12; $m++) {
		if($r <= date('t',mktime(0,0,0,$m,1,2009))) {
			$put[$m][$r] = min($rawtmean[$r][$m]);
			if($m == 1) { $colstyle = ValueColor(conv($put[$m][$r],1,0)); } else { $colstyle = ValueColor3(conv($put[$m][$r],1,0)); }
			echo '<td class="', $colstyle, '">', yr_togg(conv($put[$m][$r],1,0), array_search($put[$m][$r],$rawtmean[$r][$m])), '</td>';
			$put2[$m][$r] = max($rawtmean[$r][$m]);
			if($m == 12) { $colstyle = ValueColor(conv($put2[$m][$r],1,0)); } else { $colstyle = ValueColor2(conv($put2[$m][$r],1,0)); }
			echo '<td class="', $colstyle, '">', yr_togg(conv($put2[$m][$r],1,0), array_search($put2[$m][$r],$rawtmean[$r][$m])), '</td>';
		}
		else { echo '<td class="noday" colspan="2">&nbsp;</td>'; }
	}
	echo '</tr>';
}
echo '</tr><tr><td colspan="25">&nbsp;</td></tr>';
echo '<tr><th class="labels">Month</th>'; for($i = 1; $i < 13; $i++) { echo '<th class="labels" colspan="2"><b>', $monthshort[$i], '</b></th>'; }
echo '</tr><tr><td class="reportdt">Min</td>';
for($m = 1; $m <= 12; $m++) {
	$put3[$m] = min($put[$m]);
	if($m == 1) { $colstyle = ValueColor(conv($put3[$m],1,0)); } else { $colstyle = ValueColor3(conv($put3[$m],1,0)); }
	echo '<td class="', $colstyle, '" colspan="2">', yr_togg(conv($put3[$m],1,0), array_search($put3[$m],$put[$m]) . ', ' . array_search($put3[$m],$rawtmean[array_search($put3[$m],$put[$m])][$m])), '</td>';
}
echo '</tr><tr><td class="reportdt">Max</td>';
for($m = 1; $m <= 12; $m++) {
	$put4[$m] = max($put2[$m]);
	if($m == 1) { $colstyle = ValueColor(conv($put4[$m],1,0)); } else { $colstyle = ValueColor3(conv($put4[$m],1,0)); }
	echo '<td class="', $colstyle, '" colspan="2">', yr_togg(conv($put4[$m],1,0), array_search($put4[$m],$put2[$m]) . ', ' . array_search($put4[$m],$rawtmean[array_search($put4[$m],$put2[$m])][$m])), '</td>';
}
echo '</tr><tr><td class="reportdt" style="border-top:5px solid gray">Range</td>';
for($m = 1; $m <= 12; $m++) {
	$put5 = $put4[$m] - $put3[$m];
	if($m == 1) { $colstyle = ValueColor(conv($put5,1,0)); } else { $colstyle = ValueColor3(conv($put5,1,0)); }
	echo '<td class="', $colstyle, '" colspan="2" style="border-top:5px solid gray">', conv($put5,1,0);
}
echo '</td></tr></table>';

// Produce the min/max(range) temperature table
message_rechist(4);
echo '<h2>Extreme Diurnal Ranges</h2><table class="table1" width="95%" cellpadding="3"><tr><th class="labels" rowspan="2">Day</th>';
for($i = 1; $i < 13; $i++) { echo '<th class="labels" colspan="2"><b>', $monthshort[$i], '</b></th>'; } echo '</tr><tr>';
for($i = 0; $i < 12; $i++) { echo '<th class="labels" width="4%"><b>Min</b></th><th class="labels" width="4%"><b>Max</b></th>'; } echo '</tr>';
for($r = 1; $r < 32; $r++) {
	echo '<tr><td class="reportdt">', $r, '</td>';
	for($m = 1; $m <= 12; $m++) {
		if($r <= date('t',mktime(0,0,0,$m,1,2009))) {
			$put[$m][$r] = min($rawtrange[$r][$m]);
			if($m == 1) { $colstyle = ValueColor(conv($put[$m][$r],1,0)); } else { $colstyle = ValueColor3(conv($put[$m][$r],1,0)); }
			echo '<td class="', $colstyle, '">', yr_togg(conv($put[$m][$r],1,0), array_search($put[$m][$r],$rawtrange[$r][$m])), '</td>';
			$put2[$m][$r] = max($rawtrange[$r][$m]);
			if($m == 12) { $colstyle = ValueColor(conv($put2[$m][$r],1,0)); } else { $colstyle = ValueColor2(conv($put2[$m][$r],1,0)); }
			echo '<td class="', $colstyle, '">', yr_togg(conv($put2[$m][$r],1,0), array_search($put2[$m][$r],$rawtrange[$r][$m])), '</td>';
		}
		else { echo '<td class="noday" colspan="2">&nbsp;</td>'; }
	}
	echo '</tr>';
}
echo '</tr><tr><td colspan="25">&nbsp;</td></tr>';
echo '<tr><th class="labels">Month</th>'; for($i = 1; $i < 13; $i++) { echo '<th class="labels" colspan="2"><b>', $monthshort[$i], '</b></th>'; }
echo '</tr><tr><td class="reportdt">Min</td>';
for($m = 1; $m <= 12; $m++) {
	$put3[$m] = min($put[$m]);
	if($m == 1) { $colstyle = ValueColor(conv($put3[$m],1,0)); } else { $colstyle = ValueColor3(conv($put3[$m],1,0)); }
	echo '<td class="', $colstyle, '" colspan="2">', yr_togg(conv($put3[$m],1,0), array_search($put3[$m],$put[$m]) . ', ' . array_search($put3[$m],$rawtrange[array_search($put3[$m],$put[$m])][$m])), '</td>';
}
echo '</tr><tr><td class="reportdt">Max</td>';
for($m = 1; $m <= 12; $m++) {
	$put4[$m] = max($put2[$m]);
	if($m == 1) { $colstyle = ValueColor(conv($put4[$m],1,0)); } else { $colstyle = ValueColor3(conv($put4[$m],1,0)); }
	echo '<td class="', $colstyle, '" colspan="2">', yr_togg(conv($put4[$m],1,0), array_search($put4[$m],$put2[$m]) . ', ' . array_search($put4[$m],$rawtrange[array_search($put4[$m],$put2[$m])][$m])), '</td>';
}
echo '</tr><tr><td class="reportdt" style="border-top:5px solid gray">Range</td>';
for($m = 1; $m <= 12; $m++) {
	$put5 = $put4[$m] - $put3[$m];
	if($m == 1) { $colstyle = ValueColor(conv($put5,1,0)); } else { $colstyle = ValueColor3(conv($put5,1,0)); }
	echo '<td class="', $colstyle, '" colspan="2" style="border-top:5px solid gray">', conv($put5,1,0);
}
echo '</td></tr></table>';

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
</div>
</div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>
</body>
</html>