<?php require('unit-select.php');
	$ranking = 1; ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("phptags.php");
	$file = 52; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - Ranking test</title>

	<meta name="description" content="Old v2 - PHP script ranking testing for NW3 weather" />

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
				$rawrain[$d.$m.$y] = $raw[$m][$y][$d-1][8] + mktime(1,1,1,$m,$d,$y)/time()/1000;
				$rawrain2[date('jS F Y',mktime(1,1,1,$m,$d,$y))] = $raw[$m][$y][$d-1][8] + mktime(1,1,1,$m,$d,$y)/time()/1000;
			}
		}
	}
}
$length = count($rawrain);
sort($rawrain);
if(isset($_GET['length'])) { $cust_length = $length-1; } else { $cust_length = 100; }
echo '<h2>Wettest days</h2><table align="center" class="table1" width="500" cellpadding="4">
		<tr><th width="20%" class="labels">Rank</th><th width="40%" class="labels">24hr Total</th><th width="40%" class="labels">Date</th></tr>';
for($i = 1; $i < $cust_length; $i++) {
	if($rawrain[$length-$i] > 0.1) {
		if($i % 2 == 0) { $style = 'column-light'; } else { $style = 'column-dark'; }
		echo '<tr class="', $style, '"><td>', $i; if($i == 99) { echo '<a name="99"></a>'; } echo '</td>
			<td class="', ValueColor4($rawrain[$length-$i], $rainvalues), '">', conv($rawrain[$length-$i],2,0), '</td>
			<td>', array_search($rawrain[$length-$i], $rawrain2), '</td></tr>';
	}
}
echo '</table>';
if(!isset($_GET['length'])) { echo '<a href="?length#99">View All</a>'; }
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
?>
</div></div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>
</body>
</html>