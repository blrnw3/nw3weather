<?php
require 'Page.php';
Page::init([
	"fileNum" => 15,
	"title" => "System/Site",
	"description" => 'System Administration - site location and system (local weather server) information and latest data.'
]);
Page::Start();

// Live WD system/console stats (written by cron)
include Site::$rareTags;
?>

<h1>Detailed System and Site Information</h1>

<?php
$format = 'H:i:s, jS F Y';
$labels = array('Web Server Live', 'Last live-data upload from local system (nw3)',
	'Last upload of secondary data', 'Latest Webcam upload', 'Last upload of 24hr data log', 'Last full data process',
	'Last data downtime (&gt;60 mins)');
$outageFile = ROOT . 'Logs/outage.txt';
$timestamps = array(time(), filemtime(Site::LIVE_DATA_PATH),
	filemtime(Site::$rareTags), filemtime(ROOT . 'jpgwebcam.jpg'), filemtime(ROOT . 'customtextout.txt'), filemtime(ROOT . 'RainTags.php'),
	file_exists($outageFile) ? filemtime($outageFile) : null);
$freqs = array(1, 60, 3600, 60, 300, 300);
$limit = array(2, 200, 10000, 200, 750, 750);

Html::table(null, '92%" style="margin-bottom:15px; margin-left:25px;', 6);
Html::tableHead("System Data Health", 4);

Html::tr();
Html::td("Measure", null, "40%");
Html::td("Timestamp", null, "28%");
Html::td("Ago", null, "17%");
Html::td("Health", null, "15%");
Html::tr_end();

for ($r = 0; $r < count($labels); $r++) {
	Html::tr("row" . Html::colcol($r));
	Html::td($labels[$r]);
	Html::td($timestamps[$r] !== null ? date($format, $timestamps[$r]) : '&ndash;');
	if ($timestamps[$r] !== null) {
		$ago = $timestamps[0] - $timestamps[$r];
		Html::td(Date::secsToReadable($ago));
		if (isset($freqs[$r])) {
			$ledColour = ($ago <= $freqs[$r]) ? 'Green' : (($ago < $limit[$r]) ? 'Amber' : 'Red');
			$led = '<img src="' . Site::IMG_ROOT . 'LED_' . $ledColour
				. '.png" alt="health" title="Expected Frequency: ' . Date::secsToReadable($freqs[$r]) . '" />';
		} else {
			$led = '';
		}
		Html::td($led);
	} else {
		Html::td('&ndash;');
		Html::td('');
	}
	Html::tr_end();
}

Html::table_end();


$measures = array('<acronym title="Weather Display - the data collection software used">WD</acronym> Version &amp; Build #',
	'WD Start Time', 'WD Data Count', 'WD Memory Use', '---', 'Windows Uptime', 'Free System Memory', '---',
	'<acronym title="Davis Vantage Pro2 - the weather station model">VP2</acronym> console battery',
	'<acronym title="packets received, packets missed, resynchs, best packet run, CRC errors">VP2 reception</acronym>', 'VP2 transmitter status');
$values = array($wdversion . ' - ' . $wdbuild,
	$startimedate, $datareceivedcount, $memoryused, '---', $windowsuptime, $freememory . ' (Max: 4GB)', '---',
	$vpconsolebattery, $vpreception2 . ' (' . $vpreception . ')', $vpissstatus);

Html::table(null, '53%" align="left', 5);
Html::tableHead("Local System and Site Information", 2);

Html::tr();
Html::td("Measure", null, "42%");
Html::td("Value", null, "58%");
Html::tr_end();

for ($r = 0; $r < count($measures); $r++) {
	Html::tr("row" . Html::colcol($r));
	Html::td($measures[$r]);
	Html::td($values[$r]);
	Html::tr_end();
}

Html::table_end();


$measures2 = array('Temperature', 'Temperature Trend', 'Relative Humidity', 'Dew Point', '---',
	'Tmin Today', 'Tmax Today', 'Hmin Today', 'Hmax Today', 'Tmin Yesterday', 'Tmax Yesterday');
$values2 = array($indoortemp, Wx::conv($intempchangelasthour, Wx::AbsTemp, 1, 1) . ' /hr', $indoorhum, $indoordewcelsius, '---',
	$minindoortemp, $maxindoortemp, $minindoorhum, $maxindoorhum, $minindoortempyest, $maxindoortempyest);
$convs2 = array(Wx::Temperature, Wx::None, Wx::Humidity, Wx::Temperature, Wx::None,
	Wx::Temperature, Wx::Temperature, Wx::Humidity, Wx::Humidity, Wx::Temperature, Wx::Temperature);
$times2 = array('', '', '', '', '',
	$minindoortempt, $maxindoortempt, $dailylowindoorhumtime, $dailyhighindoorhumtime, $minindoortempyestt, $maxindoortempyestt);

Html::table(null, '42%" align="center', 5);
Html::tableHead("Machine Room Conditions", 2);

Html::tr();
Html::td("Measure", null, "45%");
Html::td("Value", null, "55%");
Html::tr_end();

for ($r = 0; $r < count($measures2); $r++) {
	$time = !Util::isBlank($times2[$r]) ? ' at ' . $times2[$r] : '';
	Html::tr("row" . Html::colcol($r));
	Html::td($measures2[$r]);
	Html::td(Wx::conv($values2[$r], $convs2[$r]) . $time);
	Html::tr_end();
}

Html::table_end();
?>

<br />
Site owner and administrator: Ben Lee-Rodgers (2010 - 2015), Ben Masschelein-Rodgers (2015-)
<hr />

<div align="center">
<h2>Last 10hrs Temp/Hum for Secondary Sensor and Indoors</h2>
<img src="/extrarealtimegraph.gif" title="Extra T/H Sensor and Indoor Conditions" alt="Extra Sensors/Indoor" />
<hr />

<h1>Other</h1>

<h2>Raw METAR from EGLL (Heathrow)</h2>
<?php echo file_exists(ROOT . 'METAR.txt') ? file_get_contents(ROOT . 'METAR.txt') : ''; ?> <br />
<a href="http://aviationweather.gov/data/metar/?ids=EGLL">Source</a>
<br /><br />

<h2>WD Screenshot</h2>
<img src="/hidden.gif" title="Latest Screenshot of Weather Display Program" alt="not available" />

</div>
<p>
	Storm Rain: <?php echo $vpstormrain; ?> (<?php echo $vpstormrainstart; ?>)
</p>

<?php Page::End(); ?>
