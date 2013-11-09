<?php require('unit-select.php');
	$ranking = 1; ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("phptags.php");
	$file = 52; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - Rain Rankings</title>

	<meta name="description" content="Old v2 - Wettest days on record ranked in order, with percentiles" />

	<?php require('chead.php'); ?>
	<link rel="stylesheet" type="text/css" href="wxreports2.css" media="screen" title="screen" />
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
		<h1>Daily Ranked Rain Totals (<?php echo $unitR; ?>)</h1>
	
		<?php $self = 'rankhist12.php';
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
				if($raw[$m][$y][$d-1][8] > 0.1) {
					$dd = fullify($d); $mm = fullify($m,2);
					$dmyID = mktime(1,1,1,$m,$d,$y)/mktime()/1000;
					$rawrain[$dd.$mm.$y] = $rawrain2[date('d M Y',mktime(1,1,1,$m,$d,$y))] = $raw[$m][$y][$d-1][8] + $dmyID;
					$rawrainM[$m][$dd.$mm.$y] = $rawrainM2[$m][date('d M Y',mktime(1,1,1,$m,$d,$y))] = $rawrain[$dd.$mm.$y];
				}
			}
		}
	}
}
if(floatval($dayrn) > 0.1) {
	$dd = date('d'); $mm = date('m'); $yy = date('Y'); $m = date('n'); //Add today's values to the array
	$t_rn = floatval($dayrn) + mktime(1,1,1,$mm,$dd,$yy)/mktime()/1000;
	$rawrain[$dd.$mm.$yy] = $rawrain2[date('d M Y',mktime(1,1,1,$mm,$dd,$yy))] = $t_rn;
	$rawrainM[$m][$dd.$mm.$yy] = $rawrainM2[$m][date('d M Y',mktime(1,1,1,$mm,$dd,$yy))] = $t_rn;
}

$length = count($rawrain);
sort($rawrain);
for($m = 1; $m <= 12; $m++) { $lengthM[$m] = count($rawrainM[$m]); sort($rawrainM[$m]); }

$all_ranks = 'View All';
$rank_lengths = array(10,25,50,100,250,500,$all_ranks); //Customise number of records to display
if(isset($_GET['length'])) { $cust_length = $_GET['length']; } elseif(isset($_COOKIE['length'])) { $cust_length = $_COOKIE['length']; } else { $cust_length = $rank_lengths[1]; }
$rank_lengthsM = array(5,10,20,30,40,50,$all_ranks);
if(isset($_GET['lengthM'])) { $cust_lengthM = $_GET['lengthM']; } elseif(isset($_COOKIE['lengthM'])) { $cust_lengthM = $_COOKIE['lengthM']; } else { $cust_lengthM = $rank_lengthsM[1]; }

$disable = array('', 'disabled="disabled"');
$rank_type = array('Unsplit', 'Split by Month'); $d_a2 = 0; $monthly = 0;  //Customise - allow monthly split
if(isset($_GET['rank_type'])) { if($_GET['rank_type'] == $rank_type[1]) { $monthly = 1; $d_a2 = 1; } }
elseif(isset($_COOKIE['rank_type'])) { if($_COOKIE['rank_type'] == $rank_type[1]) { $monthly = 1; $d_a2 = 1; } }
echo '<table align="center" cellpadding="3"><tr><td align="center" class="rep">Settings</td></tr><tr><td align="center">
	<form method="get" action="">';
	for($r = 0; $r < 2; $r++) { echo '<input name="rank_type" type="submit" value="', $rank_type[$r],'" ', $disable[($d_a2+1+$r)%2], ' /> '; }
echo '</form></td></tr>';

echo '<tr><td align="center"><form method="get" action="">';

if($monthly == 1) { //Rank by month
	if($cust_lengthM == $lengthM[12]) { $cust_lengthM = $all_ranks; }
	for($l = 0; $l < count($rank_lengthsM); $l++) {
		if($cust_lengthM == $rank_lengthsM[$l]) { $disable2 = 'disabled="disabled"'; } else { $disable2 = ''; }
		echo '<input name="lengthM" type="submit" value="', $rank_lengthsM[$l],'" ', $disable2, ' /> ';
	}
	echo '</form></td></tr><tr><td><b>Jump to: &nbsp; </b>';
	for($m = 1; $m <= 12; $m++) { echo ' <a href="#', $monthshort[$m], '">', $monthshort[$m], '</a> &nbsp; '; }
	echo '</td></tr></table><br />
		<b>NB:</b> Hover over a value for its rank within all the records';
	for($m = 1; $m <= 12; $m++) {
		if($cust_lengthM == $all_ranks || $cust_lengthM > $rank_lengthsM[count($rank_lengthsM)-1]) { $cust_lengthM = $lengthM[$m]; }
		echo '<h2>Records for ', $monthshort[$m],
			'</h2>Total number of records = <b>', $lengthM[$m], '</b> with measurable precipitation, from a possible ', possraindaysM($m), 
			' &nbsp; <a name="', $monthshort[$m], '" href="#top">Jump to top</a>',
			'<table align="center" class="table1" width="500" cellpadding="4">
			<tr><th width="15%" class="labels">Rank</th><th width="35%" class="labels">24hr Total</th><th width="35%" class="labels">Date</th>
			<th width="15%" class="labels">Percentile</th></tr>';
		for($i = 1; $i <= $cust_lengthM; $i++) {
			$order = $lengthM[$m]-$i;
			if($i % 2 == 0) { $style = 'column-light'; } else { $style = 'column-dark'; }
			if($rawrainM[$m][$order] == $t_rn) { $style .= 'B'; }
			echo '<tr class="', $style, '"><td>', $i, '</td>
				<td class="', ValueColor4(conv($rawrainM[$m][$order],2,0), $rainvalues), '">',
				acronym($length-array_search($rawrainM[$m][$order], $rawrain),conv($rawrainM[$m][$order],2,0)), '</td>
				<td>', array_search($rawrainM[$m][$order], $rawrainM2[$m]), '</td>
				<td style="border-left: 2px solid #2A0A1B">', conv3(1-($i/$lengthM[$m]),6,0), '</td></tr>';
		}
		echo '</table>';
	}
}

else {
	for($l = 0; $l < count($rank_lengths); $l++) {
		if($cust_length == $rank_lengths[$l]) { $disable2 = 'disabled="disabled"'; } else { $disable2 = ''; }
		echo '<input name="length" type="submit" value="', $rank_lengths[$l],'" ', $disable2, ' /> ';
	}
	echo '</td></tr></table><br /><b>NB:</b> Percentile is interpreted as the proportion of rain days that were drier';
	if($cust_length == $all_ranks) { $cust_length = $length; }
	echo '<h2>Wettest days</h2>Total number of records = <b>', $length, '</b> with measurable precipitation, from a possible ',
	round((mktime()-mktime(1,1,1,1,1,2009))/86400), ' (01 Jan 2009 - ', date('d M Y',mktime()),')
			<table align="center" class="table1" width="500" cellpadding="4">
			<tr><th width="15%" class="labels">Rank</th><th width="35%" class="labels">24hr Total</th><th width="35%" class="labels">Date</th>
			<th width="15%" class="labels">Percentile</th></tr>';
	for($i = 1; $i <= $cust_length; $i++) {
		if($i % 2 == 0) { $style = 'column-light'; } else { $style = 'column-dark'; }
		if($rawrain[$length-$i] == $t_rn) { $style .= 'B'; }
		echo '<tr class="', $style, '"><td>', $i, '</td>
			<td class="', ValueColor4($rawrain[$length-$i], $rainvalues), '">', conv($rawrain[$length-$i],2,0), '</td>
			<td>', array_search($rawrain[$length-$i], $rawrain2), '</td>
			<td style="border-left: 2px solid #2A0A1B">', conv3(1-($i/$length),6,0), '</td></tr>';
	}
	echo '</table>';
}
//print_r($rawrain);
//print_r($rawrain2);

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

function possraindaysM($month) {
	$d = date('j'); $m = date('n'); $y = date('Y');
	$numyears = $y - 2009; $add = 0; $dim = date('t',mktime(1,1,1,$month,1,2009));
	if($month > $m) { $val = $numyears*$dim; }
	elseif($month < $m) { $val = ($numyears+1)*$dim; $add = 1; }
	else { $val = $numyears*$dim + $d; }
	if($month == 2) { $val += floor(($numyears+$add)/4); }
	return $val;
}
?>
</div></div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>
</body>
</html>