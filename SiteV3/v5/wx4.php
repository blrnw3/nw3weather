<?php
require("Page.php");
Page::init([
	"fileNum" => 4,
	"allDataNeeded" => true,
	"title" => "Trends, Extremes and Averages",
	"description" => 'Trends and Extremes and Averages from NW3 weather station.
	Find out the difference in temperature, wind, rain, pressure, dew point from this time yesterday, one month ago and last year; view max and min records for the site.'
]);
Page::Start();

require ROOT.'TemperatureTags.php';
require ROOT.'RainTags.php';
require ROOT.'Rain2Tags.php';
require ROOT.'HumidityTags.php';
require ROOT.'WindTags.php';
require ROOT.'PressureTags.php';
require ROOT.'FeelTags.php';
require ROOT.'SunTags.php';

?>

<h1>Trends, Extremes, and Averages</h1>

<h2>Extremes and Records</h2>
<?php
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
$dataConv = array(Wx::Temperature,Wx::Temperature, Wx::Humidity,Wx::Humidity, Wx::Pressure,Wx::Pressure, Wx::Wind,Wx::Wind,
				  Wx::Temperature,Wx::Temperature, Wx::Rain,Wx::Rain,Wx::Rain, Wx::Temperature,Wx::Temperature, Wx::RainRate);
$fulldatNames = array('t', 'h', 'p', 'w', 'd', 'r', 'f');
$order1 = array(0,0, 1,1, 2,2, 3,3, 4,4, 5,5,5, 6,6); //for var name
$order2 = array(0,1, 0,1, 0,1, 1,2, 0,1, 0,1,2, 0,1); //for min/max/mean named key
$order3 = array(0,1, 0,1, 0,1, 1,1, 0,1, 1,1,1, 0,1); //for inner min/max numbered key
$order4 = array(0,1, 0,1, 0,1, 1,1, 0,1, 2,1,2, 0,1); //for today's data min/max/mean named key

$pOrder = array('7', 'm', 'y', 'a');
$values = array();
$dates = array();

for($o = 0; $o < count($order1); $o++) {
	$mType = Wx::$mmm[$order2[$o]];
	$dkey = isset(Wx::$mappingsToDailyDataKey[$fulldatNames[$order1[$o]]]) ? Wx::$mappingsToDailyDataKey[$fulldatNames[$order1[$o]]] : null;
	$mmKey = Wx::$mmm[$order4[$o]];
	$timeKey = 'time' . Wx::$mmmr[$order4[$o]];
	$values[$o][0] = ($dkey !== null && isset(Live::$NOW[$mmKey][$dkey])) ? Live::$NOW[$mmKey][$dkey] : null;
	$values[$o][1] = ${$fulldatNames[$order1[$o]] .'datYest'}[0][$mType];
	$dates[$o][0] = ($dkey !== null && isset(Live::$NOW[$timeKey][$dkey])) ? Live::$NOW[$timeKey][$dkey] : '';
	$dates[$o][1] = ${$fulldatNames[$order1[$o]] .'datYest'}[1][$order2[$o]];
	foreach($pOrder as $po) {
		$values[$o][] = ${$fulldatNames[$order1[$o]] .'dat'}[$mType][$order3[$o]][$po];
		$dates[$o][] = ${$fulldatNames[$order1[$o]] .'dat'}[$mType][$order3[$o]][$po.'date'];
	}
}

$values[7][0] = Live::$NOW['max']['gust'];
$values[11][0] = isset(Live::$NOW['max']['rnhr']) ? Live::$NOW['max']['rnhr'] : null;
$values[12][0] = isset(Live::$NOW['max']['rn10']) ? Live::$NOW['max']['rn10'] : null;
$dates[7][0] = Live::$NOW['timeMax']['gust'];
$dates[11][0] = isset(Live::$NOW['timeMax']['rnhr']) ? Live::$NOW['timeMax']['rnhr'] : '';
$dates[12][0] = isset(Live::$NOW['timeMax']['rn10']) ? Live::$NOW['timeMax']['rn10'] : '';

$rnRates = array(isset(Live::$NOW['max']['rate']) ? Live::$NOW['max']['rate'] : null, $maxrainrateyest, $maxRateWeek, $mrecorddailyrate, $yrecorddailyrate, $recorddailyrate);
$rnRatesD = array(isset(Live::$NOW['timeMax']['rate']) ? Live::$NOW['timeMax']['rate'] : '', $maxrainrateyesttime, $maxRateWeek_date, $mrecorddailyratedate, $yrecorddailyratedate, $recorddailyratedate);
$values[$o] = $rnRates;
$dates[$o] = $rnRatesD;

Html::table();
Html::tableHead("Extreme Conditions", 7);
Html::tr();
foreach($periods as $heading) {
	Html::td($heading, null, next($widths));
}
Html::tr_end();

for($r = 0; $r < count($values); $r++) {
	Html::tr(Html::colcol($r));
	$tdClass = 'td'. ($dataCat[$r] + 10) .'C';
	Html::td("<b> $dataNames[$r] </b>", $tdClass);
	for($c = 0; $c < count($values[0]); $c++) {
		Html::td( "<b>" . Wx::conv($values[$r][$c], $dataConv[$r]) . "</b><br />" . $dates[$r][$c], $tdClass );
	}
	Html::tr_end();
}
Html::table_end();

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
$dataConv = array(Wx::Temperature, Wx::Humidity, Wx::Pressure, Wx::Wind, Wx::Temperature, Wx::Rain, Wx::Hours);
$fulldatNames = array('t', 'h', 'p', 'w', 'd', 'r', 's');

$sdatToday[0][2] = $stotals[0]['today'];
$sdatYest[0]['mean'] = $stotals[0]['yest'];
$rdatToday[0][2] = $rtotals[0]['today'];
$rdatYest[0]['mean'] = $rtotals[0]['yest'];

for($o = 0; $o < count($fulldatNames); $o++) {
	$windFix = ($o === 3); //fix for mean wind being in the 'min' array key
	$dk = isset(Wx::$mappingsToDailyDataKey[$fulldatNames[$o]]) ? Wx::$mappingsToDailyDataKey[$fulldatNames[$o]] : null;
	$values[$o][0] = ($dk !== null && isset(Live::$NOW['mean'][$dk])) ? Live::$NOW['mean'][$dk]
		: (isset(${$fulldatNames[$o] .'datToday'}[0][2]) ? ${$fulldatNames[$o] .'datToday'}[0][2] : null);
	$values[$o][1] = ${$fulldatNames[$o] .'datYest'}[0][$windFix ? 'min' : 'mean'];
	$anoms[$o][0] = isset(${$fulldatNames[$o] .'datToday'}[2]['mean']) ? ${$fulldatNames[$o] .'datToday'}[2]['mean'] : '';
	$anoms[$o][1] = isset(${$fulldatNames[$o] .'datYest'}[2]['mean']) ? ${$fulldatNames[$o] .'datYest'}[2]['mean'] : '';
	foreach($pOrder as $po) {
		$values[$o][] = ($o <= 4) ? ${$fulldatNames[$o] .'dat'}[$windFix ? 'min' : 'mean'][2][$po] : ${$fulldatNames[$o] .'totals'}[0][$po];
		$anoms[$o][] = ($o === 0) ? ${$fulldatNames[$o] .'dat'}['mean'][2][$po.'anom'] :
			( ($o > 4) ? ${$fulldatNames[$o] .'totAnoms'}[0][$po] : '' );
		$daysof[$o][] = ($o <= 4) ? '' : ${$fulldatNames[$o] .'daysof'}[0][$po];
	}
}

Html::table();
Html::tableHead("Averages and Totals", 9);
Html::tr();
foreach($periods as $heading) {
	Html::td($heading, null, next($widths));
}
Html::tr_end();

for($r = 0; $r < count($values); $r++) {
	Html::tr(Html::colcol($r) .'" style="height: 4.2em;');
	$tdClass = 'td'. ($dataCat[$r] + 10) .'C';
	Html::td("<b> $dataNames[$r] </b>", $tdClass);
	for($c = 0; $c < count($values[0]); $c++) {
		$anomVal = isset($anoms[$r][$c]) ? $anoms[$r][$c] : '';
		$anomOrNo = Util::isBlank($anomVal) ? '' : ( "<br />".
			( ($r === 0) ? '('. Wx::conv($anomVal, Wx::AbsTemp, false, true) .')' : $anomVal ) );
		$daysofVal = isset($daysof[$r][$c-2]) ? $daysof[$r][$c-2] : '';
		$daysofOrNo = Util::isBlank($daysofVal) ? '' : "<br />". $daysofVal .' days';
		Html::td( "<b>" . Wx::conv($values[$r][$c], $dataConv[$r]) ."</b>". $anomOrNo . $daysofOrNo, $tdClass );
	}
	Html::tr_end();
}
Html::table_end();
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
$unitT = Wx::getUnits(Wx::Temperature);
$unitR = Wx::getUnits(Wx::Rain);
$unitW = Wx::getUnits(Wx::Wind);
$unitP = Wx::getUnits(Wx::Pressure);
$periods = array("10 mins", "30 mins", "Hour", "24hrs", "Month", "Year");
$dataNames = array("Temperature / $unitT", "Humidity / %", "Wind / $unitW",
	"Dew Point / $unitT", "Pressure / $unitP", "Rainfall / $unitR");
$varPres = array('temp', 'humi', 'wind', 'dewp', 'pres', 'rain'); //index of field position in Live::$HR24
$order = array(0,1,4, 2,3,5);
$dataCat = array(4,0,3, 0,6,2);
$convType = array(Wx::AbsTemp,Wx::Humidity,Wx::Wind, Wx::Temperature,Wx::Pressure,Wx::Rain);

$valuesMon = getvalsDateAgo(date('Ymd', Date::mkdate(Date::$dmonth-1)));
$valuesYear = getvalsDateAgo(date('Ymd', Date::mkdate(Date::$dmonth,Date::$dday,Date::$dyear-1)));
//special case for rain (need to use cum vals)
$valuesMon['rain'] = $monthrn - $raintodmonthago;
$valuesYear['rain'] = $yearrn - $raintodayearago;

Live::$HR24['changeHr']['rain'] = Live::$HR24['trendRn'][0] - Live::$HR24['trendRn'][1];
Live::$HR24['changeDay']['rain'] = Live::$HR24['trendRn'][0];

Html::table(null, null, 8);
Html::tableHead("Current Trends", 8);

Html::tr();
Html::td("Measure", null, 16, 1, 2);
Html::td("Current<br />". Date::$time, "td4", 12, 1, 2);
Html::td("Change Since Last", "td4", 72, 6);
Html::tr_end();

Html::tr();
foreach($periods as $heading) {
	Html::td($heading, 'td4'. (($heading == 'Month') ? ' td4e':''), 12);
}
Html::tr_end();

$valuesDiff = array(10, 30, 60);

for($r = 0; $r < count($dataCat); $r++) {
	Html::tr(Html::colcol($r));
	$pos = $order[$r];
	$li = $varPres[$pos];
	$tdClass = 'td'. ($dataCat[$pos] + 10) .'C';
	Html::td("<b> $dataNames[$pos] </b>", $tdClass);
	Html::td( Wx::conv( Live::$HR24['trend'][0][$li], $convType[$pos], false ), $tdClass );
	foreach($valuesDiff as $diff) {
		//$tdClass2 = ($c == 4) ? $tdClass.'" style="border-left: solid 2px rgb(143,208,246);' : $tdClass;
		Html::td( Wx::conv( Live::$HR24['trend'][0][$li] - Live::$HR24['trend'][$diff][$li], $convType[$pos], false, true ), $tdClass );
	}
	Html::td( Wx::conv( Live::$HR24['changeDay'][$li], $convType[$pos], false, true ), $tdClass );
	Html::td( Wx::conv( Live::$HR24['trend'][0][$li] - $valuesMon[$li], $convType[$pos], false, true ), $tdClass );
	Html::td( Wx::conv( Live::$HR24['trend'][0][$li] - $valuesYear[$li], $convType[$pos], false, true ), $tdClass );
	Html::tr_end();
}
Html::table_end();
?>

<p align="center"><b>NB: </b>For the month and year rain trends, values refer to differences from cumulative rainfall;
 i.e. the difference between the rain-to-date for this month/year, and that of the previous month/year.</p>
<?php
$t = time();
$periods = array($t, $t - 3600*24, Date::mkdate(Date::$dmonth-1), Date::mkdate(Date::$dmonth,Date::$dday,Date::$dyear-1));
$periodNames = array('Today', 'Yesterday', 'Last Month', 'Last Year');
$extremeTypes = array("Min", "Max", "Avg");
$e = array('e','','', 'e','','', 'e','','', 'e','','', '');
$varBase = array('t', 'h', 'w', 'd', 'p');
$subnames = ['min', 'max', 'mean'];

Html::table(null, null, 7);
Html::tableHead("Extremes' Trends", 13);

Html::tr();
Html::td("Measure", null, 16, 1, 2);
foreach($periods as $i => $heading) {
	Html::td(HTML::acronym(date('d F Y',$heading), $periodNames[$i], true), 'td4'. (($heading != $t) ? ' td4e':''), 21, 3);
}
Html::tr_end();

Html::tr();
foreach($periods as $x) {
	foreach($extremeTypes as $subheading) {
		Html::td($subheading, 'td4'.(($subheading == 'Min' && $x != $t) ? ' td4e':''), 7);
	}
}
Html::tr_end();

for($r = 0; $r < count($dataCat)-1; $r++) {
	Html::tr(Html::colcol($r));
	$pos = $order[$r];
	$tdClass = 'td'. ($dataCat[$pos] + 10) .'C';
	Html::td("<b> $dataNames[$pos] </b>", $tdClass);
	foreach($periods as $tstamp) {
		for($d = 0; $d < count($extremeTypes); $d++) {
			$tdClass2 = ($d == 0 && $tstamp != $t) ? $tdClass.'" style="border-left: solid 2px rgb(143,208,246);' : $tdClass;
			$name = $varBase[$pos] . $subnames[$d];
			$val = ($name == 'wmin') ? '-' : Data::get($name, date('Y', $tstamp), date('n', $tstamp), date('j', $tstamp));
			Html::td( Wx::conv( $val, $convType[$pos], false ), $tdClass2 );
		}
	}
	Html::tr_end();
}
//rainfall special case
Html::tr(Html::colcol($r));
$tdClass = 'td12C';
Html::td("<b> $dataNames[$r] </b>", $tdClass);
foreach($periods as $tstamp) {
	Html::td( Wx::conv(Data::get("rain", date('Y', $tstamp), date('n', $tstamp), date('j', $tstamp)), Wx::Rain, false ), $tdClass, null, 3 );
}
Html::tr_end();

Html::table_end();
?>

<p align="center"><b>Description: </b>Today's averages and extremes compared to yesterday's, and those of this day a month ago, and one year ago.</p>

<br />

<?php
$periodsTime = array(5,10, 15,20,30, 45,60,75, 90,120);

Html::table(null, null, 5);
Html::tableHead("Last 2hrs Trends in Detail", 12);

Html::tr();
Html::td("Measure", null, 16, 1, 2);
Html::td("Current<br />". Date::$time, "td4", 14, 1, 2);
Html::td("Value x minutes ago", "td4", 70, 10);
Html::tr_end();

Html::tr();
foreach($periodsTime as $heading) {
	Html::td("-".$heading, null, 7);
}
Html::tr_end();

for($r = 0; $r < count($dataCat); $r++) {
	Html::tr(Html::colcol($r));
	$pos = $order[$r];
	$li = $varPres[$pos];
	$tdClass = 'td'. ($dataCat[$pos] + 10) .'C';
	Html::td("<b> $dataNames[$pos] </b>", $tdClass);
	Html::td( Wx::conv(Live::$HR24['trend'][0][$li], $convType[$pos], false), $tdClass );
	foreach($periodsTime as $timeago) {
		Html::td( Wx::conv( Live::$HR24['trend'][$timeago][$li], $convType[$pos], false ), $tdClass );
	}
	Html::tr_end();
}
Html::table_end();



function getvalsDateAgo($dateStamp) {
	$trendKeys = array('wind', 'gust', 'wdir', 'temp', 'humi', 'pres', 'dewp');
	$f = file(ROOT.'logfiles/daily/'. $dateStamp .'log.txt');
	$e = explode(',', $f[ 60 * (int)date('H') + (int)date('i') ]);
	return array_combine( $trendKeys, array_slice($e, 3, 7) );
}
?>

<?php Page::End(); ?>