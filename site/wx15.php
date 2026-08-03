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
$now = time();
$yr = date('Y');
$outageFile = ROOT . 'Logs/outage.txt';
$rainSummaryFile = DataSummarizer::summaryCachePath('rain', Site::BASE_YEAR);
$rainDataFile = V5_CACHE_ROOT . 'serialised_dat_new_rain.txt';
$fullDataStamp = is_file($rainSummaryFile) ? filemtime($rainSummaryFile)
	: (is_file($rainDataFile) ? filemtime($rainDataFile) : null);

// Critical live signals (LED health).
$healthRows = array(
	array('Web Server Live', $now, 1, 2),
	array('Live data upload (clientraw.txt)', wx15_mtime(Site::LIVE_DATA_PATH), 60, 200),
	array('Secondary WD tags (rareTags.php)', wx15_mtime(Site::$rareTags), 3600, 10000),
	array('Skycam (generated/v5/skycam.jpg)', wx15_mtime(V5_GENERATED_ROOT . 'skycam.jpg'), 60, 200),
	array('24hr data log (customtextout.txt)', wx15_mtime(ROOT . 'customtextout.txt'), 300, 750),
	array('Full data process (rain summary)', $fullDataStamp, 300, 750),
	array('Last data downtime (&gt;60 mins)', wx15_mtime($outageFile), null, null),
);

Html::table(null, '92%" style="margin-bottom:15px; margin-left:25px;', 6);
Html::tableHead("System Data Health", 4);
Html::tr();
Html::td("Measure", null, "40%");
Html::td("Timestamp", null, "28%");
Html::td("Ago", null, "17%");
Html::td("Health", null, "15%");
Html::tr_end();
wx15_render_health_rows($healthRows, $now, $format);
Html::table_end();

// Uploaded / FTP drop files that feed the site.
$uploadedFiles = array(
	array('Live WD clientraw', 'clientraw.txt', Site::LIVE_DATA_PATH, 60, 200),
	array('WD rareTags (console/system)', 'rareTags.php', Site::$rareTags, 3600, 10000),
	array('WD 24hr custom log', 'customtextout.txt', ROOT . 'customtextout.txt', 300, 750),
	array('Rolling goodlog', 'goodlog.txt', ROOT . 'goodlog.txt', 60, 200),
	array('Daily CSV (current year)', "dat{$yr}.csv", ROOT . "dat{$yr}.csv", 90000, 200000),
	array('Trend CSV (current year)', "datt{$yr}.csv", ROOT . "datt{$yr}.csv", 90000, 200000),
	array('Monthly CSV (current year)', "datm{$yr}.csv", ROOT . "datm{$yr}.csv", 90000, 200000),
	array('Legacy WD webcam still', 'jpgwebcam.jpg', ROOT . 'jpgwebcam.jpg', 3600, 10000),
);

Html::table(null, '92%" style="margin-bottom:15px; margin-left:25px;', 6);
Html::tableHead("Uploaded Files (FTP / Weather Display)", 5);
Html::tr();
Html::td("File", null, "28%");
Html::td("Path", null, "27%");
Html::td("Timestamp", null, "22%");
Html::td("Ago", null, "13%");
Html::td("Health", null, "10%");
Html::tr_end();
wx15_render_file_rows($uploadedFiles, $now, $format);
Html::table_end();

// Rebuildable v5 caches and presentation assets.
$generatedFiles = array(
	array('Live summary cache', 'serialised_datNow.txt', V5_CACHE_ROOT . 'serialised_datNow.txt', 60, 200),
	array('24hr summary cache', 'serialised_datHr24.txt', V5_CACHE_ROOT . 'serialised_datHr24.txt', 60, 200),
	array('Daily serialisations', 'serialised_dat.txt', V5_CACHE_ROOT . 'serialised_dat.txt', 300, 900),
	array('Detail summary (rain)', basename($rainSummaryFile), $rainSummaryFile, 300, 900),
	array('Yr.no forecast cache', 'forecast_v5.json', V5_CACHE_ROOT . 'forecast_v5.json', 1800, 7200),
	array('PurpleAir PM2.5', 'pm25_latest.txt', V5_CACHE_ROOT . 'pm25_latest.txt', 300, 900),
	array('Windy widget id', 'windy_widget.txt', V5_CACHE_ROOT . 'windy_widget.txt', 86400, 259200),
	array('METAR (EGLL)', 'METAR.txt', ROOT . 'METAR.txt', 1800, 7200),
	array('Skycam HD', 'skycam.jpg', V5_GENERATED_ROOT . 'skycam.jpg', 60, 200),
	array('Skycam home', 'skycam_home.jpg', V5_GENERATED_ROOT . 'skycam_home.jpg', 60, 200),
	array('Skycam wx2', 'skycam_wx2.jpg', V5_GENERATED_ROOT . 'skycam_wx2.jpg', 60, 200),
);

Html::table(null, '92%" style="margin-bottom:15px; margin-left:25px;', 6);
Html::tableHead("Generated Files (v5 cache / assets)", 5);
Html::tr();
Html::td("File", null, "28%");
Html::td("Path", null, "27%");
Html::td("Timestamp", null, "22%");
Html::td("Ago", null, "13%");
Html::td("Health", null, "10%");
Html::tr_end();
wx15_render_file_rows($generatedFiles, $now, $format);
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
	Html::tr(Html::colcol($r));
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
	Html::tr(Html::colcol($r));
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

<?php Page::End();

function wx15_mtime($path) {
	return (is_string($path) && is_file($path)) ? filemtime($path) : null;
}

function wx15_rel_path($path) {
	if(!is_string($path) || $path === '') {
		return '&ndash;';
	}
	if(strpos($path, ROOT) === 0) {
		return substr($path, strlen(ROOT));
	}
	return $path;
}

function wx15_led($ago, $freq, $limit) {
	if($freq === null) {
		return '';
	}
	$ledColour = ($ago <= $freq) ? 'Green' : (($ago < $limit) ? 'Amber' : 'Red');
	return '<img src="' . Site::IMG_ROOT . 'LED_' . $ledColour
		. '.png" alt="health" title="Expected Frequency: ' . Date::secsToReadable($freq) . '" />';
}

function wx15_render_health_rows($rows, $now, $format) {
	for($r = 0; $r < count($rows); $r++) {
		$label = $rows[$r][0];
		$stamp = $rows[$r][1];
		$freq = $rows[$r][2];
		$limit = $rows[$r][3];
		Html::tr(Html::colcol($r));
		Html::td($label);
		Html::td($stamp !== null ? date($format, $stamp) : '&ndash;');
		if($stamp !== null) {
			$ago = $now - $stamp;
			Html::td(Date::secsToReadable($ago));
			Html::td(wx15_led($ago, $freq, $limit));
		} else {
			Html::td('&ndash;');
			Html::td('');
		}
		Html::tr_end();
	}
}

function wx15_render_file_rows($rows, $now, $format) {
	for($r = 0; $r < count($rows); $r++) {
		$label = $rows[$r][0];
		$name = $rows[$r][1];
		$path = $rows[$r][2];
		$freq = $rows[$r][3];
		$limit = $rows[$r][4];
		$stamp = wx15_mtime($path);
		Html::tr(Html::colcol($r));
		Html::td($label . '<br /><span style="color:#666">' . htmlspecialchars($name) . '</span>');
		Html::td('<code>' . htmlspecialchars(wx15_rel_path($path)) . '</code>');
		Html::td($stamp !== null ? date($format, $stamp) : 'missing');
		if($stamp !== null) {
			$ago = $now - $stamp;
			Html::td(Date::secsToReadable($ago));
			Html::td(wx15_led($ago, $freq, $limit));
		} else {
			Html::td('&ndash;');
			Html::td('<img src="' . Site::IMG_ROOT . 'LED_Red.png" alt="missing" title="File missing" />');
		}
		Html::tr_end();
	}
}
?>
