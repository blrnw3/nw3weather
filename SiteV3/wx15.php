<?php require('unit-select.php');
		include $rareTags;
		$file = 15;
	?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - System/Site</title>

	<meta name="description" content="System Administration - site location and system (local weather server) information and latest data." />

	<?php require('chead.php'); ?>
	<?php include_once("ggltrack.php") ?>
</head>

<body>
	<!-- ##### Header ##### -->
	<?php require('header.php'); ?>
	<!-- ##### Left Sidebar ##### -->
	<?php require('leftsidebar.php'); ?>
	<!-- ##### Main Copy ##### -->
	<div id="main">

<?php require('site_status.php'); ?>

<h1>Detailed System and Site Information</h1>

<?php
	$format = 'H:i:s, jS F Y';
	$labels = array('Web Server Live', 'Last live-data upload from local system (nw3)',
		'Last upload of secondary data', 'Latest Webcam upload', 'Last upload of 24hr data log', 'Last full data process',
		'Last data downtime (&gt;15 mins)');
	$timestamps = array(time(), filemtime(LIVE_DATA_PATH),
		 filemtime($rareTags), filemtime(ROOT. 'jpgwebcam.jpg'), filemtime(ROOT. 'customtextout.txt'), filemtime(ROOT. 'RainTags.php'),
		 filemtime(ROOT. "Logs/WDuploadReallyBad.txt"));
	$freqs = array(1, 60, 3600, 60, 300, 300);
	$limit = array(2, 200, 10000, 200, 750, 750);

	table(null, '92%" style="margin-bottom:15px; margin-left:25px;', 6);
	tableHead("System Data Health", 4);

	tr();
	td("Measure", null, "40%");
	td("Timestamp", null, "28%");
	td("Ago", null, "17%");
	td("Health", null, "15%");
	tr_end();

	for ($r = 0; $r < count($labels); $r++) {
		tr("row" . colcol($r));
		td($labels[$r]);
		td(date($format, $timestamps[$r]));
		$ago = $timestamps[0] - $timestamps[$r];
		td(secsToReadable($ago));
		$ledColour = ($ago <= $freqs[$r]) ? 'Green' : ( ($ago < $limit[$r]) ? 'Amber' : 'Red' );
		$led = (isset($freqs[$r])) ? '<img src="'. IMG_ROOT .'LED_'. $ledColour
			.'.png" alt="health" title="Expected Frequency: '. secsToReadable($freqs[$r]) .'" />' : '';
		td($led);
		tr_end();
	}

	table_end();


	$freemem = ($freememory < 0) ? (4000 + $freememory) : $freememory;
	$measures = array('<acronym title="Weather Display - the data collection software used">WD</acronym> Version &amp; Build #',
		'WD Start Time', 'WD Data Count', 'WD Memory Use', '---', 'Windows Uptime', 'Free System Memory', '---', 'Station Altitude',
		'Station Latitude', 'Station Longitude');
	$values = array($wdversion .' - '. $wdbuild,
		$startimedate, $datareceivedcount, $memoryused, '---', $windowsuptime, str_replace("MB", " MB", $freemem) .' (Max: 4GB)', '---', '57 m (187 ft)',
		$lat, $lng);

	table(null, '53%" align="left', 5);
	tableHead("Local System and Site Information", 2);

	tr();
	td("Measure", null, "42%");
	td("Value", null, "58%");
	tr_end();

	for ($r = 0; $r < count($measures); $r++) {
		tr("row" . colcol($r));
		td($measures[$r]);
		td($values[$r]);
		tr_end();
	}

	table_end();


	$measures2 = array('Temperature', 'Temperature Trend', 'Relative Humidity', 'Dew Point', '---',
		'Tmin Today', 'Tmax Today', 'Hmin Today', 'Hmax Today', 'Tmin Yesterday', 'Tmax Yesterday');
	$values2 = array($indoortemp, conv($intempchangelasthour, 1.1, 1, 1) .' /hr', $indoorhum, $indoordewcelsius, '---',
		$minindoortemp, $maxindoortemp, $minindoorhum, $maxindoorhum, $minindoortempyest, $maxindoortempyest);
	$convs2 = array(1,false,5,1,false,		1,1,5,5,1,1);
	$times2 = array('', '', '', '', '',
		$minindoortempt, $maxindoortempt, $dailylowindoorhumtime, $dailyhighindoorhumtime, $minindoortempyestt, $maxindoortempyestt);

	table(null, '42%" align="center', 5);
	tableHead("Machine Room Conditions", 2);

	tr();
	td("Measure", null, "45%");
	td("Value", null, "55%");
	tr_end();

	for ($r = 0; $r < count($measures2); $r++) {
		$time = !isBlank($times2[$r]) ? ' at ' . $times2[$r] : '';
		tr("row" . colcol($r));
		td($measures2[$r]);
		td(conv($values2[$r], $convs2[$r]) . $time);
		tr_end();
	}

	table_end();
?>

<br />
Site owner and administrator: Ben Lee-Rodgers (2010 - present)
<hr />

<div align="center">
<h2>Last 10hrs Temp/Hum for Secondary Sensor and Indoors</h2>
<img src="/extrarealtimegraph.gif" title="Extra T/H Sensor and Indoor Conditions" alt="Extra Sensors/Indoor" />
<hr />

<h1>Other</h1>

<h2>Raw METAR from EGLL (Heathrow) </h2>
<?php echo file_get_contents('METAR.txt'); ?> <br />
<a href="http://www.wunderground.com/metarFAQ.asp">Decode Instructions</a> <br />
<a href="http://aviationweather.gov/adds/metars/?station_ids=EGLL&chk_metars=on&hoursStr=most+recent+only&chk_tafs=on">Source</a>
<br /> <br />

<h2>WD Screenshot</h2>
<img src="/hidden.gif" title="Latest Screenshot of Weather Display Program" alt="not available" />

</div>
</div>

<!-- ##### Footer ##### -->
	<?php require('footer.php'); ?>

</body>
</html>