<?php require('unit-select.php');
		include("phptags.php");
		$file = 15;
	?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - System/Site</title>

	<meta name="description" content="Old v2 - System Administration - site location and system (local weather server) information and latest data." />

	<? require('chead.php'); ?>
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
<? require('site_status.php'); ?>

<h1>Detailed System and Site Information</h1>

<table width="50%" align="left" cellpadding="5" cellspacing="0" border="1">
<tr><td colspan="2" align="center"><h3>System &amp; Site</h3></td></tr>
<tr class="table-top">
<td align="center" colspan="1">Measure</td><td align="center" colspan="1">Value</td>
</tr>
<tr class="column-light">
<td align="center" width="44%"><acronym title="Weather Display - the data collection software used">WD</acronym> Version &amp; Build #</td>
<td align="center" width="56%"><b><?php echo $wdversion; ?> - <?php echo $wdbuild; ?></b></td></tr>
<tr class="column-dark">
<td align="center" width="44%">WD Start Time</td>
<td align="center" width="56%"><b><?php echo $startimedate; ?></b></td></tr>
<tr class="column-light">
<td align="center" width="44%">WD Data Count</td>
<td align="center" width="56%"><b><?php echo $datareceivedcount; ?></b></td></tr>
<tr class="column-dark">
<td align="center" width="44%">WD Memory Use</td>
<td align="center" width="56%"><b><?php echo $memoryused; ?>B</b></td></tr>
<tr class="column-light">
<td align="center" width="44%">---</td>
<td align="center" width="56%">---</td></tr>
<tr class="column-dark">
<td align="center" width="44%">Windows Uptime</td>
<td align="center" width="56%"><b><?php echo $windowsuptime; ?></b></td></tr>
<tr class="column-light">
<td align="center" width="44%">Free System Memory</td>
<td align="center" width="56%"><b><?php if($freememory<0): echo round(4000+$freememory,0); else: echo round($freememory,0); endif; ?> MB</b> (Max: 4GB)</td></tr>
<tr class="column-dark">
<td align="center" width="44%">---</td>
<td align="center" width="56%">---</td></tr>
<tr class="column-light">
<td align="center" width="44%">Station Altitude</td>
<td align="center" width="56%"><b><?php if($unitT == 'F') { echo '187 ft'; } else { echo '57 m'; } ?></b></td></tr>
<tr class="column-dark">
<td align="center" width="44%">Station Latitude</td>
<td align="center" width="56%"><b>0<?php echo $stationlatitude; ?> N</b></td></tr>
<tr class="column-light">
<td align="center" width="44%">Station Longitude</td>
<td align="center" width="56%"><b><?php echo $stationlongitude; ?> W</b></td></tr>
</table>

<table width="40%" align="center" cellpadding="5" cellspacing="0" border="1">
<tr><td colspan="2" align="center"><h3>System Location Conditions (Indoors)</h3></td></tr>
<tr class="table-top">
<td align="center" colspan="1">Measure</td><td align="center" colspan="1">Value</td>
</tr>
<tr class="column-light">
<td align="center" width="44%">Temperature</td>
<td align="center" width="56%"><b><?php echo conv($indoortemp,1,1); ?></b></td></tr>
<tr class="column-dark">
<td align="center" width="44%">Temperature Trend</td>
<td align="center" width="56%"><b><?php echo conv2($intempchangelasthour,1,1); ?> /hr</b></td></tr>
<tr class="column-light">
<td align="center" width="44%">Relative Humidity</td>
<td align="center" width="56%"><b><?php echo $indoorhum; ?>%</b></td></tr>
<tr class="column-dark">
<td align="center" width="44%">Dew Point</td>
<td align="center" width="56%"><b><?php echo conv($indoordewcelsius,1,1); ?></b></td></tr>
<tr class="column-light">
<td align="center" width="44%">---</td>
<td align="center" width="56%">---</td></tr>
<tr class="column-dark">
<td align="center" width="44%">Tmin Today</td>
<td align="center" width="56%"><b><?php echo conv($minindoortemp,1,1); ?></b> at <?php echo $minindoortempt; ?></td></tr>
<tr class="column-light">
<td align="center" width="44%">Tmax Today</td>
<td align="center" width="56%"><b><?php echo conv($maxindoortemp,1,1); ?></b> at <?php echo $maxindoortempt; ?></td></tr>
<tr class="column-dark">
<td align="center" width="44%">Hmin Today</td>
<td align="center" width="56%"><b><?php echo $minindoorhum; ?>%</b> at <?php echo $dailylowindoorhumtime; ?></td></tr>
<tr class="column-light">
<td align="center" width="44%">Hmax Today</td>
<td align="center" width="56%"><b><?php echo $maxindoorhum; ?>%</b> at <?php echo $dailyhighindoorhumtime; ?></td></tr>
<tr class="column-dark">
<td align="center" width="44%">Tmin Yesterday</td>
<td align="center" width="56%"><b><?php echo conv($minindoortempyest,1,1); ?></b> at <?php echo $minindoortempyestt; ?></td></tr>
<tr class="column-light">
<td align="center" width="44%">Tmax Yesterday</td>
<td align="center" width="56%"><b><?php echo conv($maxindoortempyest,1,1); ?></b> at <?php echo $maxindoortempyestt; ?></td></tr>
</table>

<br />
Site administrator: Ben Lee-Rodgers
<hr />

<div align="center">
<h3>Last 10hrs Temp/Hum for Secondary Sensor and Indoors</h3>
<img src="/extrarealtimegraph.gif" title="Extra T/H Sensor and Indoor Conditions" alt="Extra Sensors/Indoor" />
<br />
Live Secondary Temp: <?php echo $extratemp1; ?>&deg;C --- Min: <?php echo $minsoiltemp; ?>; Max: <?php echo $maxsoiltemp; ?>
<br />
Live Secondary Humidity: <?php echo $generalextrahum1; ?>%
<br />
<hr />

<h1>Other</h1>

<p><b>METAR <br />
<?php echo $extrametarlabel; ?> <br />
<img src="/metar.gif" title="Latest EGLL METAR" alt="loading" />
<br /> <br />

WD Screenshot <br />
<img src="/hidden.gif" title="Latest Screenshot of Weather Display Program" alt="not available" />
</b>
<br />
<br />
Home pages from old site versions: <br />
<a href="hidden.htm" title="old home page 2">Version 0</a> -
<a href="wx16.html" title="old home page 3">Version 1</a> -
<a href="wxold.html" title="old home page 3">Version 1.1</a> 
</p>

<h1>Test Area</h1>

<table>
<tr> <td align="center"><h4>Europe humidities</h4></td></tr>
<tr> <td align="center"><img src="http://icons-pe.wunderground.com/data/640x480/2xeu_rh.gif" alt="hum" height="300" title="Humidities across Europe" /></td></tr>
<tr> <td align="center"><i>Source: Wunderground</i></td></tr>
</table>
<?php $report = date("F", mktime(0,0,0,$date_month,$date_day-1)).date("Y", mktime(0,0,0,$date_month,$date_day-1,$date_year)).'.htm';
echo $currentalarmflashing, '<br />wugrab last accessed: ', date("H:i, d M",fileatime('wugrab.html')), '<br />Data.csv last modified: ', date("H:i, d M",filemtime('data.csv')), '<br />','Latest history data source file modified: ',
date("H:i, d M",filemtime($report)); ?>
<!-- <a href="phptest.php" title="PHP testing page and data download">(PHP test)</a> -->
<br />
Latest timelapse available for download 
<a href="/
<?php 
if($time_hour > 21) {
	echo $date_year,$date_month,$date_day;
}
else {
	echo date("Ymd", mktime(0,0,0,$date_month,$date_day-1,$date_year));
} ?>dayvideo.wmv" title="<? echo $tl_day; ?>'s full-day extended HQ timelapse">here</a> 

<br /><br />
<?php $icon[0] = 'day_clear';	// imagesunny.visible
$icon[1] = 'night_clear';	// imageclearnight.visible
$icon[2] = 'day_partly_cloudy';	// imagecloudy.visible
$icon[3] = 'day_partly_cloudy';	// imagecloudy2.visible
$icon[4] = 'night_partly_cloudy'; // imagecloudynight.visible
$icon[5] = 'day_partly_cloudy';	// imagedry.visible
$icon[6] = 'fog';		// imagefog.visible
$icon[7] = 'haze';		// imagehaze.visible
$icon[8] = 'day_heavy_rain';	// imageheavyrain.visible
$icon[9] = 'day_mostly_sunny';	// imagemainlyfine.visible
$icon[10] = 'mist';		// imagemist.visible
$icon[11] = 'fog';		// imagenightfog.visible
$icon[12] = 'night_heavy_rain';	// imagenightheavyrain.visible
$icon[13] = 'night_cloudy';	// imagenightovercast.visible
$icon[14] = 'night_rain';	// imagenightrain.visible
$icon[15] = 'night_light_rain';	// imagenightshowers.visible
$icon[16] = 'night_snow';	// imagenightsnow.visible
$icon[17] = 'night_tstorm';	// imagenightthunder.visible
$icon[18] = 'day_cloudy';	// imageovercast.visible
$icon[19] = 'day_partly_cloudy';	// imagepartlycloudy.visible
$icon[20] = 'day_rain';	// imagerain.visible
$icon[21] = 'day_rain';	// imagerain2.visible
$icon[22] = 'day_light_rain';	// imageshowers2.visible
$icon[23] = 'sleet';		// imagesleet.visible
$icon[24] = 'sleet';		// imagesleetshowers.visible
$icon[25] = 'snow';		// imagesnow.visible
$icon[26] = 'snow';		// imagesnowmelt.visible
$icon[27] = 'snow';		// imagesnowshowers2.visible
$icon[28] = 'day_clear';	// imagesunny.visible
$icon[29] = 'day_tstorm';	// imagethundershowers.visible
$icon[30] = 'day_tstorm';	// imagethundershowers2.visible
$icon[31] = 'day_tstorm';	// imagethunderstorms.visible
$icon[32] = 'tornado';		// imagetornado.visible
$icon[33] = 'windy';		// imagewindy.visible
$icon[34] = 'day_partly_cloudy';	// stopped raining
$icon[35] = 'windyrain';	// Wind+rain
?>
<img src="/static-images/<?php echo $icon[$iconnumber]; ?>.gif" alt="Current weather icon" title="Current Weather: <?php echo str_ireplace('_',' ',$icon[$iconnumber]); ?>" />

</div>
</div>

<!-- ##### Footer ##### -->
	<? require('footer.php'); ?>
	
</body>
</html>