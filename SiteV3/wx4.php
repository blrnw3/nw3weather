<?php
$file = 4;
$allDataNeeded = true;
require('unit-select.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Trends, Extremes and Averages</title>

	<meta name="description" content="Trends and Extremes and Averages from NW3 weather station.
	Find out the difference in temperature, wind, rain, pressure, dew point from this time yesterday, one month ago and last year; view max and min records for the site" />

<?php require('chead.php'); ?>
<?php include_once("ggltrack.php"); ?>
</head>

<body>
	<!-- ##### Header ##### -->
	<?php require('header.php'); ?>
	<!-- ##### Left Sidebar ##### -->
	<?php require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->

<div id="main">
	<?php require('site_status.php'); ?>

<h1>Trends, Extremes, and Averages</h1>

<h2>Extremes and Records</h2>
<?php
require ROOT.'TemperatureTags.php';
require ROOT.'RainTags.php';
require ROOT.'Rain2Tags.php';
require ROOT.'HumidityTags.php';
require ROOT.'WindTags.php';
require ROOT.'PressureTags.php';
require ROOT.'FeelTags.php';
require ROOT.'SunTags.php';

$periods = array("Measure", "Today", "Yesterday", "Last 7 days", "Month", "Year", "All Time");
$widths = array(0,15,15,14,14,14,14,14);

$dataNames = array("Min Temperature", "Max Temperature",
				"Min Humidity", "Max Humidity",
				"Min Pressure", "Max Pressure",
				"Max Wind Speed", "Max Wind Gust",
				"Min Dew Point", "Max Dew Point",
				"Wettest Day", "Wettest Hour", "Wettest 10-mins",
				"Min Wind Chill", "Max Humidex",
				"Max Rain Rate",
			);
$dataCat = array(4,4, 0,0, 6,6, 3,3, 0,0, 2,2,2, 4,4, 2);
$dataConv = array(1,1, 5,5, 3,3, 4,4, 1,1, 2,2,2, 1,1, 2.1);
$fulldatNames = array('t', 'h', 'p', 'w', 'd', 'r', 'f');
$order1 = array(0,0, 1,1, 2,2, 3,3, 4,4, 5,5,5, 6,6); //for var name
$order2 = array(0,1, 0,1, 0,1, 1,2, 0,1, 0,1,2, 0,1); //for min/max/mean named key
$order3 = array(0,1, 0,1, 0,1, 1,1, 0,1, 1,1,1, 0,1); //for inner min/max numbered key
$order4 = array(0,1, 0,1, 0,1, 1,1, 0,1, 2,1,2, 0,1); //for today's data min/max/mean named key

$pOrder = array('7', 'm', 'y', 'a');
$values = array();
$dates = array();

for($o = 0; $o < count($order1); $o++) {
	$mType = $dnm[$order2[$o]];
	$values[$o][0] = $NOW[$dnm[$order4[$o]]][$mappingsToDailyDataKey[$fulldatNames[$order1[$o]]]];
	$values[$o][1] = ${$fulldatNames[$order1[$o]] .'datYest'}[0][$mType];
	$dates[$o][0] = $NOW['time'.$mmmr[$order4[$o]]][$mappingsToDailyDataKey[$fulldatNames[$order1[$o]]]];
	$dates[$o][1] = ${$fulldatNames[$order1[$o]] .'datYest'}[1][$order2[$o]];
	foreach($pOrder as $po) {
		$values[$o][] = ${$fulldatNames[$order1[$o]] .'dat'}[$mType][$order3[$o]][$po];
		$dates[$o][] = ${$fulldatNames[$order1[$o]] .'dat'}[$mType][$order3[$o]][$po.'date'];
	}
}

$values[7][0] = $NOW['max']['gust'];
$values[11][0] = $NOW['max']['rnhr'];
$values[12][0] = $NOW['max']['rn10'];
$dates[7][0] = $NOW['timeMax']['gust'];
$dates[11][0] = $NOW['timeMax']['rnhr'];
$dates[12][0] = $NOW['timeMax']['rn10'];

$rnRates = array($NOW['max']['rate'], $maxrainrateyest, $maxRateWeek, $mrecorddailyrate, $yrecorddailyrate, $recorddailyrate);
$rnRatesD = array($NOW['timeMax']['rate'], $maxrainrateyesttime, $maxRateWeek_date, $mrecorddailyratedate, $yrecorddailyratedate, $recorddailyratedate);
$values[$o] = $rnRates;
$dates[$o] = $rnRatesD;

table();
tableHead("Extreme Conditions", 7);
tr();
foreach($periods as $heading) {
	td($heading, null, next($widths));
}
tr_end();

for($r = 0; $r < count($values); $r++) {
	tr("row".colcol($r));
	$tdClass = 'td'. ($dataCat[$r] + 10) .'C';
	td("<b> $dataNames[$r] </b>", $tdClass);
	for($c = 0; $c < count($values[0]); $c++) {
		td( "<b>" . conv($values[$r][$c], $dataConv[$r]) . "</b><br />" . $dates[$r][$c], $tdClass );
	}
	tr_end();
}
table_end();

echo '<p>
	<b>NB:</b> This station defines the start of the meteorological day to be midnight; this is when daily values are reset.<br />
	Station records began on 1st February 2009.
	</p>
	<h2>Means and Totals</h2>
';

//###### means #######//
$dataNames = array("Temperature", "Humidity", "Pressure", "Wind Speed", "Dew Point", "Rain Total", "Sun Total");
$dataCat = array(4,0,6,3,0,2,8);
$widths = array(0,12,10,10,11,11,11,11,12,12);
array_splice($periods, 5, 0, "Last 31d");
array_splice($periods, 7, 0, "Last 365");
array_splice($pOrder, 2, 0, '31');
array_splice($pOrder, 4, 0, '365');

$values = array();
$anoms = array();
$daysof = array();
$dataConv = array(1, 5, 3, 4, 1, 2, 9);
$fulldatNames = array('t', 'h', 'p', 'w', 'd', 'r', 's');

$sdatToday[0][2] = $stotals[0]['today'];
$sdatYest[0]['mean'] = $stotals[0]['yest'];
$rdatToday[0][2] = $rtotals[0]['today'];
$rdatYest[0]['mean'] = $rtotals[0]['yest'];

for($o = 0; $o < count($fulldatNames); $o++) {
	$windFix = ($o === 3); //fix for mean wind being in the 'min' array key
	$values[$o][0] = $NOW['mean'][$mappingsToDailyDataKey[$fulldatNames[$o]]];
	$values[$o][1] = ${$fulldatNames[$o] .'datYest'}[0][$windFix ? 'min' : 'mean'];
	$anoms[$o][0] = ${$fulldatNames[$o] .'datToday'}[2]['mean'];
	$anoms[$o][1] = ${$fulldatNames[$o] .'datYest'}[2]['mean'];
	foreach($pOrder as $po) {
		$values[$o][] = ($o <= 4) ? ${$fulldatNames[$o] .'dat'}[$windFix ? 'min' : 'mean'][2][$po] : ${$fulldatNames[$o] .'totals'}[0][$po];
		$anoms[$o][] = ($o === 0) ? ${$fulldatNames[$o] .'dat'}['mean'][2][$po.'anom'] :
			( ($o > 4) ? ${$fulldatNames[$o] .'totAnoms'}[0][$po] : '' );
		$daysof[$o][] = ($o <= 4) ? '' : ${$fulldatNames[$o] .'daysof'}[0][$po];
	}
}

table();
tableHead("Averages and Totals", 9);
tr();
foreach($periods as $heading) {
	td($heading, null, next($widths));
}
tr_end();

for($r = 0; $r < count($values); $r++) {
	tr("row".colcol($r) .'" style="height: 4.2em;');
	$tdClass = 'td'. ($dataCat[$r] + 10) .'C';
	td("<b> $dataNames[$r] </b>", $tdClass);
	for($c = 0; $c < count($values[0]); $c++) {
		$anomOrNo = isBlank($anoms[$r][$c]) ? '' : ( "<br />".
			( ($r === 0) ? '('. conv($anoms[$r][$c], 1.1, false, true) .')' : $anoms[$r][$c] ) );
		$daysofOrNo = isBlank($daysof[$r][$c-2]) ? '' : "<br />". $daysof[$r][$c-2] .' days';
		td( "<b>" . conv($values[$r][$c], $dataConv[$r]) ."</b>". $anomOrNo . $daysofOrNo, $tdClass );
	}
	tr_end();
}
table_end();
?>
<p>
	Bracketed values refer to departure from the relevant long-term climate average; note well that the month and year figures are adjusted for the current date,
	i.e. compared to the expected conditions for the month/year to-date, rather than the full period.  <br />
	'Days of' require greater than 0.1 of the quantity (rain in mm or sun hrs). <br />
	Sunshine figures are not available for the current day so all periods are upto yesterday, rather than today as for the other variables.
</p>

<hr />

<h2>Trends</h2>

<?php
$periods = array("10 mins", "30 mins", "Hour", "24hrs", "Month", "Year");
$dataNames = array("Temperature / &deg;$unitT", "Humidity / %", "Wind / $unitW",
	"Dew Point / &deg;$unitT", "Pressure / $unitP", "Rainfall / $unitR");
$varPres = array('temp', 'humi', 'wind', 'dewp', 'pres', 'rain'); //index of field position in $HR24
$order = array(0,1,4, 2,3,5);
$dataCat = array(4,0,3, 0,6,2);
$convType = array(1.1,5,4, 1,3,2);

$valuesMon = getvalsDateAgo(date('Ymd', mkdate($dmonth-1)));
$valuesYear = getvalsDateAgo(date('Ymd', mkdate($dmonth,$dday,$dyear-1)));
//special case for rain (need to use cum vals)
$valuesMon['rain'] = $monthrn - $raintodmonthago;
$valuesYear['rain'] = $yearrn - $raintodayearago;

$HR24['changeHr']['rain'] = $HR24['trendRn'][0] - $HR24['trendRn'][1];
$HR24['changeDay']['rain'] = $HR24['trendRn'][0];

table(null, null, 8);
tableHead("Current Trends", 8);

tr();
td("Measure", null, 16, 1, 2);
td("Current<br />$time", "td4", 12, 1, 2);
td("Change Since Last", "td4", 72, 6);
tr_end();

tr();
foreach($periods as $heading) {
	td($heading, 'td4'. (($heading == 'Month') ? ' td4e':''), 12);
}
tr_end();

$valuesDiff = array(10, 30, 60);

for($r = 0; $r < count($dataCat); $r++) {
	tr("row".colcol($r));
	$pos = $order[$r];
	$li = $varPres[$pos];
	$tdClass = 'td'. ($dataCat[$pos] + 10) .'C';
	td("<b> $dataNames[$pos] </b>", $tdClass);
	td( conv( $HR24['trend'][0][$li], round($convType[$pos]), false ), $tdClass );
	foreach($valuesDiff as $diff) {
		//$tdClass2 = ($c == 4) ? $tdClass.'" style="border-left: solid 2px rgb(143,208,246);' : $tdClass;
		td( conv( $HR24['trend'][0][$li] - $HR24['trend'][$diff][$li], $convType[$pos], false, true ), $tdClass );
	}
	td( conv( $HR24['changeDay'][$li], $convType[$pos], false, true ), $tdClass );
	td( conv( $HR24['trend'][0][$li] - $valuesMon[$li], $convType[$pos], false, true ), $tdClass );
	td( conv( $HR24['trend'][0][$li] - $valuesYear[$li], $convType[$pos], false, true ), $tdClass );
	tr_end();
}
table_end();
?>

<p align="center"><b>NB: </b>For the month and year rain trends, values refer to differences from cumulative rainfall;
 i.e. the difference between the rain-to-date for this month/year, and that of the previous month/year.</p>
<?php
$t = time();
$periods = array($t, $t - 3600*24, mkdate($dmonth-1), mkdate($dmonth,$dday,$dyear-1));
$periodNames = array('Today', 'Yesterday', 'Last Month', 'Last Year');
$extremeTypes = array("Min", "Max", "Avg");
$e = array('e','','', 'e','','', 'e','','', 'e','','', '');
$varBase = array(0, 3, 9, 17, 6);

table(null, null, 7);
tableHead("Extremes' Trends", 13);

tr();
td("Measure", null, 16, 1, 2);
foreach($periods as $i => $heading) {
	td(acronym(date('d F Y',$heading), $periodNames[$i], true), 'td4'. (($heading != $t) ? ' td4e':''), 21, 3);
}
tr_end();

tr();
foreach($periods as $x) {
	foreach($extremeTypes as $subheading) {
		td($subheading, 'td4'.(($subheading == 'Min' && $x != $t) ? ' td4e':''), 7);
	}
}
tr_end();

for($r = 0; $r < count($dataCat)-1; $r++) {
	tr("row".colcol($r));
	$pos = $order[$r];
	$tdClass = 'td'. ($dataCat[$pos] + 10) .'C';
	td("<b> $dataNames[$pos] </b>", $tdClass);
	foreach($periods as $tstamp) {
		for($d = 0; $d < count($extremeTypes); $d++) {
			$tdClass2 = ($d == 0 && $tstamp != $t) ? $tdClass.'" style="border-left: solid 2px rgb(143,208,246);' : $tdClass;
			$type = strpos($dataNames[$pos],'ind') ? 11 - $d : $varBase[$pos] + $d;
			$val = ($type == 11) ? '-' : $DATA[$type][date('Y', $tstamp)][date('n', $tstamp)][date('j', $tstamp)];
			td( conv( $val, round($convType[$pos]), false ), $tdClass2 );
		}
	}
	tr_end();
}
//rainfall special case
tr("row".colcol($r));
$tdClass = 'td12C';
td("<b> $dataNames[$r] </b>", $tdClass);
foreach($periods as $tstamp) {
	td( conv( $DATA[13][date('Y', $tstamp)][date('n', $tstamp)][date('j', $tstamp)], 2, false ), $tdClass, null, 3 );
}
tr_end();

table_end();
?>

<p align="center"><b>Description: </b>Today's averages and extremes compared to yesterday's, and those of this day a month ago, and one year ago.</p>

<br />

<?php
$periodsTime = array(5,10, 15,20,30, 45,60,75, 90,120);

table(null, null, 5);
tableHead("Last 2hrs Trends in Detail", 12);

tr();
td("Measure", null, 16, 1, 2);
td("Current<br />$time", "td4", 14, 1, 2);
td("Value x minutes ago", "td4", 70, 10);
tr_end();

tr();
foreach($periodsTime as $heading) {
	td("-".$heading, null, 7);
}
tr_end();

for($r = 0; $r < count($dataCat); $r++) {
	tr("row".colcol($r));
	$pos = $order[$r];
	$li = $varPres[$pos];
	$tdClass = 'td'. ($dataCat[$pos] + 10) .'C';
	td("<b> $dataNames[$pos] </b>", $tdClass);
	td( conv($HR24['trend'][0][$li], $convType[$pos], false), $tdClass );
	foreach($periodsTime as $timeago) {
		td( conv( $HR24['trend'][$timeago][$li], $convType[$pos], false ), $tdClass );
	}
	tr_end();
}
table_end();



function getvalsDateAgo($dateStamp) {
	$trendKeys = array('wind', 'gust', 'wdir', 'temp', 'humi', 'pres', 'dewp');
	$f = file(ROOT.'logfiles/daily/'. $dateStamp .'log.txt');
	$e = explode(',', $f[ 60 * (int)date('H') + (int)date('i') ]);
	return array_combine( $trendKeys, array_slice($e, 3, 7) );
}
?>

</div>

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>

</body>
</html>