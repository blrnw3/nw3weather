<?php require('unit-select.php');
	$record = 1; ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("phptags.php");
	$file = 52; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - Daily Rain Records</title>

	<meta name="description" content="Old v2 - Daily historical rain records - maximum 24hr rainfall" />

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
		<h1>Daily Rain Records (<?php echo $unitR; ?>)</h1>
		
		<?php $self = 'rechist12.php';
			include("wxrepgen.php");
		
$monthshort = array('Measure', 'Jan', 'Feb', 'March', 'April', 'May', 'June', 'July', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
//Script to display records for each day of the year
$manual_values = array(.5,1,2,5,10,15,20,25,50);
$manual_valuesUS = array(.02,.05,.1,.2,.3,.5,.75,1,2);
if($unitR == 'in') { $manual_values = $manual_valuesUS; }
$loc = $path_dailynoaa; # Location of dailynoaareports
$first_year_of_data = $first_year_of_noaadata;
$rain_units = $unitR;
$rainvalues = $manual_values;
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
				if($rawrain[$d][$m][$y] > 0.1) { $raincount[$d][$m] = $raincount[$d][$m] + 1; }
			}
		}
	}
}
$dd = date('j'); $mm = date('n'); $yy = date('Y'); // Now Add today's values to the array
$rawrain[$dd][$mm][$yy] = floatval($dayrn)+0.001;

$hide = 0; if(isset($_GET['year_hide'])) { $hide = $_GET['year_hide']; }
function message_rechist($pos) {
	global $hide;
	$message_rechist = '<a name="'.$pos.'"></a>';
	if($hide == 0) { $message_rechist .= 'Hover over record to reveal year, or <a href=?year_hide=1#' . $pos . '>Display all</a>'; }
	else { $message_rechist .= '<a href=?year_hide=0#' . $pos . '>Hide years</a>'; }
	echo $message_rechist;
}

// Produce the rain table
message_rechist(1);
echo '<h2>Wettest Day (2009-',date('Y'),')</h2><table class="table1" width="95%" cellpadding="3"><tr><th class="labels" rowspan="2">Day</th>';
for($i = 1; $i < 13; $i++) { echo '<th class="labels"><b>', $monthshort[$i], '</b></th>'; } echo '</tr><tr>';
for($i = 0; $i < 12; $i++) { echo '<th class="labels" width="8%"><b>Max</b></th>'; } echo '</tr>';
for($r = 1; $r < 32; $r++) {
	echo '<tr><td class="reportdt">', $r, '</td>';
	for($m = 1; $m <= 12; $m++) {
		if($r <= date('t',mktime(0,0,0,$m,1,2009))) {
			$put[$m][$r] = max($rawrain[$r][$m]);
			$colstyle = ValueColor4(conv($put[$m][$r],2,0),$rainvalues); $cy = ''; if(intval(array_search($put[$m][$r],$rawrain[$r][$m])) == 2009) { $cy = '0'; }
			$na_handle = array_search($put[$m][$r],$rawrain[$r][$m]); if($put[$m][$r] < 0.2) { $na_handle = 'n/a'; }
			echo '<td class="', $colstyle, '">', yr_togg(conv($put[$m][$r],2,0), $na_handle), '</td>';
		}
		else { echo '<td class="noday">&nbsp;</td>'; }
	}
	echo '</tr>';
}
echo '</tr><tr><td colspan="13">&nbsp;</td></tr>';
echo '<tr><th class="labels">Month</th>'; for($i = 1; $i < 13; $i++) { echo '<th class="labels"><b>', $monthshort[$i], '</b></th>'; }
echo '</tr><tr><td class="reportdt">Max</td>';
for($m = 1; $m <= 12; $m++) {
	$put3[$m] = max($put[$m]);
	$colstyle = ValueColor4(conv($put3[$m],2,0), $rainvalues);
	echo '<td class="', $colstyle, '">', yr_togg(conv($put3[$m],2,0), array_search($put3[$m],$put[$m]) . ', ' . array_search($put3[$m],$rawrain[array_search($put3[$m],$put[$m])][$m])), '</td>';
}
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

?>
</div>
</div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>
</body>
</html>