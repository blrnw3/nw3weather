<?php
const smallGraphWidth1 = 533;
const smallGraphWidth2 = 500;
const smallGraphWidth3 = 505;

// PurpleAir sensor index to pull air-quality (PM2.5) from. Set after picking the
// nearest outdoor sensor to the station; 0 = unconfigured (fetch skipped).
// The API key lives in secrets.php (PURPLEAIR_KEY), included below.
const PURPLEAIR_SENSOR = 197637;

$t_start = microtime(get_as_float);
include('/var/www/html/basics.php');

// API keys (git-excluded; this cron is the only includer - keys never reach page code).
if(file_exists(ROOT.'secrets.php')) include(ROOT.'secrets.php');

echo "START: ". date('r'). "\n";

//Now call this, which generates the mainData
include(ROOT.'functions.php');

//Create clientraw backup for use in mainData when trying to access it mid-upload
if(!$badCRdata) {
	copy(LIVE_DATA_PATH, ROOT.'clientrawBackup.txt');
}

$fiveMinutely = date('i') % 5 == 0;
$tstamp = date('Hi');
$stamplog = ROOT.'logfiles/daily/'.date('Ymd').'log.txt';
$goodlog = ROOT.'goodlog.txt';
$todaylog = ROOT.'logfiles/daily/todaylog.txt';
$goodlog_backup = ROOT.'logfiles/backup/goodlog_'. $tstamp .'.txt';

//Rebuild 24hr and today data logs, plus neaten.
//Do 10-minutely (on top of after downtime) just for extra security, and in case the cron missed an append
$recentWDdowntime = time() - filemtime(ROOT. "Logs/WDuploadReallyBad.txt") < 1200;
if(false && $tstamp != '0000' && (date('i') % 10 == 1 || $recentWDdowntime)) {
	$fsize = filesize(ROOT.'customtextout.txt');
	$fage = time() - filemtime(ROOT.'customtextout.txt');
	if($fsize > 61000 && $fsize < 75000) { //probably valid
		if($fage < 500) { // probably new
		   # TODO: check data integrity: are all hours in the file
			copy($goodlog, $goodlog_backup);
			logneatenandrepair();
		} else {
			quick_log("badCustomlogUpload.txt", $fsize.'B '. $fage.'s');
		}
	} else {
		mail("alerts@nw3weather.co.uk","Datalog corrupt","Alert! customtextout.txt is corrupt. Size: ". $fsize);
	}
}

//Prepare data for appending logs
// Fix weird bug
if($tstamp == '0107') {
	$gust = $wind;
}
// Air quality: carry forward the latest PM2.5 reading (polled every 5 min below).
$pm25 = file_exists(ROOT.'pm25_latest.txt') ? trim(file_get_contents(ROOT.'pm25_latest.txt')) : '';
$lineVars = array($wind, $gust, $wdir, $temp, $humi, $pres, $dewp, $rain, $pm25);
$isBadLineData = ($pres == 0);
$newLine = date('H,i,d,');
foreach ($lineVars as $value) {
	$newLine .= (($value === '') ? '' : round( trim($value), 1)) . ',';
}
$newLine = substr($newLine, 0, strlen($newLine)-1) . "\r\n";
########################################################
//NEWLINE IS SOMETIMES 0,0,0,0,0,0,0,0,0 (AFTER DATETIME STAMP).
//MUST FIX (POST DATA MAY WELL FIX PROBLEM)
########################################################

//Midnight procedures
if($tstamp == '0000') {
	require(ROOT.'data.php');

	exec(EXEC_PATH. 'HourlyLogs.php > html/Log/hrlogOutput.html');

	$rain = 0; //clientraw hasn't had time to upload and reset this
	file_put_contents($todaylog, $newLine); //reset
} else {
	file_put_contents($todaylog, $newLine, FILE_APPEND);
}

//Append goodlog, deleting the oldest line
$oldLines = file($goodlog);
$len = count($oldLines);
$filelog = fopen($goodlog, "w");
for($i = 1; $i < $len; $i++) {
	fwrite($filelog, $oldLines[$i]);
}
if($isBadLineData) {
	// Grab newline from the most recent one, replacing its datetime with the current
	$oldNewLine = $newLine;
	$newLine = date('H,i,d') . substr($oldLines[$len-1], 8);
	mail("alerts@nw3weather.co.uk", "Bad Dataline - corrupt!", "Offender: $oldNewLine . Replaced by $newLine");
}
fwrite($filelog, $newLine);
fclose($filelog);

// make date-alias of goodlog (this is needed, even though goodlog never called elsewhere, to keep the 24hr rolling aspect going
copy($goodlog, $stamplog);

//serialise current data
$newNOW = dailyData(); //used by serialiseCSV and datNOW
file_put_contents( ROOT.'serialised_datNow.txt', serialize($newNOW) );
file_put_contents( ROOT.'serialised_datHr24.txt', serialize( dailyData( date('Ymd') ) ) );

// 'API'
file_put_contents(ROOT.'api_latest.txt', $newLine);

// Cache yesterday's full daily summary once per day (self-healing via mtime check).
// The v5 home page loads this single small file instead of unserialising the large
// per-variable daily files just to read a few of yesterday's values.
$yestFile = ROOT.'serialised_datYest.txt';
if(!file_exists($yestFile) || date('Ymd', filemtime($yestFile)) !== date('Ymd')) {
	$yestYmd = date('Ymd', $dtstamp_yest);
	if(file_exists(ROOT."logfiles/daily/{$yestYmd}log.txt")) {
		file_put_contents($yestFile, serialize(dailyData($yestYmd)));
	}
}

//datm append
if($tstamp == $datmCheckTime) {
	checkDatmWritten();
}

if($fiveMinutely) {
	//Serialise data
	serialiseCSV('dat');

	//pre-run 24hr graphs
	exec(EXEC_PATH. 'graphday.php 1.png');
	exec(EXEC_PATH. 'graphday2.php 2.png');
	exec(EXEC_PATH. 'graphdayA.php 3.png wdir');
	exec(EXEC_PATH. 'graphday.php 1s.png s 260 '. smallGraphWidth1);
	exec(EXEC_PATH. 'graphday2.php 2s.png s 260 '. smallGraphWidth2);
	exec(EXEC_PATH. 'graphdayA.php 3s.png wdir s '. smallGraphWidth3);
	graph_stitch();
	//pre-run main-page graphs
	if(date('i') != 0) { //weird glitch for graphs using 2hr scale, only on the hour mark
		$vars = array('temp', 'rain', 'hum', 'dew', 'wind', 'baro', 'wdir', 'gust');
		$margs = array(7, 12, 23, 21);
		for($i = 1; $i <= 4; $i++) {
			$arg1 = $vars[$i*2 -2];
			$arg2 = $vars[$i*2 -1];
			$arg3 = (int)($i % 2 === 1);
			$arg4 = $margs[$i-1];
			exec(EXEC_PATH. "graphdayA.php main$i.png $arg1 $arg2 $arg3 $arg4 miniMain");
			copy('main'.$i.'.png', 'html/mainGraph'.$i.'.png');
		}
	}
	// windroses - 24hr, month and year
	$rose_types = ["24hrs", "month", "year"];
	foreach($rose_types as $roset) {
		exec(EXEC_PATH. "windrose.php $roset html/rose_$roset.png");
	}
}

//More midnight procedures
if($tstamp == '0000') {
	//graph save
	$target_st = ROOT. $yr_yest . '/stitchedmaingraph_';
	$target_en = date('Ymd', time() - 60) .'.png';
	copy(ROOT."stitchedmaingraph.png", $target_st .''. $target_en);
	copy(ROOT."stitchedmaingraph_small.png", $target_st .'small_'. $target_en);

}

// All-time windrose
if($tstamp == '1656') {
	exec(EXEC_PATH. "windrose.php now html/rose_all.png");
}

//serialise time data when modifications occur
if(time() - filemtime(ROOT.'datt'.$yr_yest.'.csv') < 65) {
	serialiseCSV('datt');
}

//serialise manual data when modifications occur
if(time() - filemtime(ROOT.'datm'.$yr_yest.'.csv') < 65) {
	serialiseCSVm();
	//Now generate the sunTags file
	exec(EXEC_PATH. 'cron_tags.php blr ftw > log/cronsuntaglog.txt &');
}

//serialise historical data when modifications occur
if(time() - filemtime(ROOT.'historical.csv') < 60) {
	serializeHistoricalData();
}

// Monthly report
if($firstday && $fiveMinutely && time() - filemtime(ROOT.'datm'.$yr_yest.'.csv') < 303) {
	require ('monthrepgen.php');
	$rep = monthlyReport((int)$mon_yest, (int)$yr_yest);
	mail("blr@nw3weather.co.uk","Monthly report $mon_yest $yr_yest", $rep);
}

 // Ensure that data.php did indeed run at midnight
if($tstamp == '0700') {
	$age = time() - filemtime(ROOT."dat" . $yr_yest . ".csv");
	if($age > 30000) { // 8.3 hrs
		require(ROOT.'data.php');
		mail("alerts@nw3weather.co.uk","Cron fail","Alert! Cron data.php failed on first attempt. Problems may exist");
	}
}

 //check for file issues
if(date('i') % 15 == 1) {
	if($OUTAGE || $ALT_OUTAGE) {
		quick_log("outage.txt", "Outage: $OUTAGE ($diff s), alt-outage: $ALT_OUTAGE ($alt_true_age s)");
		if($diff < 5000) {
			mail("alerts@nw3weather.co.uk","Old live data","Alert! live data not updating. Act NOW!");
		}
	}
}

$HR24 = unserialize(file_get_contents(ROOT. 'serialised_datHr24.txt'));
//24hr Rain exceeds 20 mm
$rn24hrs = $HR24['trendRn'][0];
if( $rn24hrs > 20 && $rn24hrs > $rain && ($HR24['trendRn'][0] - $HR24['trendRn']['10m'] > 0) ) {
	quick_log('rain_excess.txt', $rn24hrs);
//	if(date('i') % 10 == 0) {
//		mail("blr@nw3weather.co.uk","Rain excess","Notice! More than 20 mm of rain (" . $rn24hrs . ") has fallen in the past 24 hrs");
//	}
}
//record 24hr rain is v. close (54.2 is actual)
if($rn24hrs > 54) {
	mail("alerts@nw3weather.co.uk","Rain record very near","Alert! May need to change wx12 soon - " . $rn24hrs . "mm has fallen in the past 24 hrs");
}

// METAR retrieve and parse (updated at 20 and 50 mins past the hour, with delay)
if(date('i') % 30 == 28) {
	$noaaMetar = urlToArray('http://tgftp.nws.noaa.gov/data/observations/metar/stations/EGLL.TXT');
	if($noaaMetar !== false) file_put_contents(ROOT."METAR.txt", $noaaMetar[1]);
}

//WU forecast retrieve
// API key is dead
// New URL: https://api.weather.com/v3/wx/forecast/daily/5day?postalKey=NW3%202:GB&units=h&language=en-US&format=json&apiKey=d5fedde5c6ae4eabbedde5c6aeaeab51
// it's json so need to parse it
if(false && $tstamp % 100 == 0) {
	$xml = getXml('http://api.wunderground.com/api/46272bfe75051ab1/forecast/q/UK/EGLL.xml');
	if($xml !== false) { //grab the data if available
		$condition = $xml->forecast->simpleforecast->forecastdays->forecastday->conditions;
		file_put_contents(ROOT."WUforecast.txt", $condition);
//		file_put_contents(ROOT."WUforecastDump.txt", print_r($xml->forecast->simpleforecast->forecastdays, true));
	}
}

// Local forecast from Open-Meteo (free, no API key). Today + tomorrow.
if(date('i') % 30 == 7) {
	$fcUrl = "https://api.open-meteo.com/v1/forecast?latitude=$lat&longitude=$lng"
		. "&daily=weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max"
		. "&timezone=Europe%2FLondon&forecast_days=2";
	$fcRaw = @file_get_contents($fcUrl, false, stream_context_create(array('http' => array('timeout' => 6))));
	$fcJson = $fcRaw ? json_decode($fcRaw, true) : null;
	if($fcJson && isset($fcJson['daily']['time'])) {
		$d = $fcJson['daily'];
		$labels = array('Today', 'Tomorrow');
		$fcDays = array();
		foreach($d['time'] as $i => $date) {
			list($fcIcon, $fcDesc) = forecastIcon((int)$d['weather_code'][$i]);
			$fcDays[] = array(
				'label' => isset($labels[$i]) ? $labels[$i] : date('D', strtotime($date)),
				'date' => $date,
				'icon' => $fcIcon,
				'desc' => $fcDesc,
				'tmax' => $d['temperature_2m_max'][$i],
				'tmin' => $d['temperature_2m_min'][$i],
				'pop'  => $d['precipitation_probability_max'][$i],
			);
		}
		file_put_contents(ROOT.'forecast_v5.json', json_encode(array('updated' => time(), 'days' => $fcDays)));
	} else {
		quick_log('forecast_bad.txt', substr((string)$fcRaw, 0, 200));
	}
}

// Air quality (PM2.5) from PurpleAir. Polled every 5 min; the latest value is
// carried forward into the per-minute log (see $lineVars above) so dailyData()
// produces min/max/mean for free. Raw PM2.5 is stored; the v5 site bands it as UK DAQI.
if(PURPLEAIR_SENSOR && (date('i') % 5 == 2) && defined('PURPLEAIR_KEY') && PURPLEAIR_KEY !== '') {
	$paUrl = 'https://api.purpleair.com/v1/sensors/' . PURPLEAIR_SENSOR . '?fields=pm2.5_10minute';
	$paCtx = stream_context_create(array('http' => array(
		'header' => "X-API-Key: " . PURPLEAIR_KEY . "\r\n",
		'timeout' => 6,
	)));
	$paRaw = @file_get_contents($paUrl, false, $paCtx);
	$paJson = $paRaw ? json_decode($paRaw, true) : null;
	$pm25Now = null;
	if(isset($paJson['sensor']['pm2.5_10minute'])) {
		$pm25Now = $paJson['sensor']['pm2.5_10minute'];
	} elseif(isset($paJson['sensor']['stats']['pm2.5_10minute'])) {
		$pm25Now = $paJson['sensor']['stats']['pm2.5_10minute'];
	}
	if($pm25Now !== null && is_numeric($pm25Now)) {
		file_put_contents(ROOT.'pm25_latest.txt', round((float)$pm25Now, 1));
	} else {
		quick_log('purpleair_bad.txt', substr((string)$paRaw, 0, 200));
	}
}

// External clientraw grab and save
if(false) {
	$path = 'http://www.harpendenweather.co.uk/live/clientraw.txt';
	$harpendenData = urlToArray($path);
	if($harpendenData[0] && count($harpendenData) === 1) {
		file_put_contents(ROOT.'EXT_harpenden.txt', $harpendenData[0]);
	} else {
		quick_log("HarpendenBadData.txt", $harpendenData[0]);
	}
}
if(true && (date('i') % 5 == 4)) {
	// St James
//	$pathJames = "https://api.synopticdata.com/v2/stations/latest?token=790b537f5b0248bc94ec8bbeae0bcba7&stid=SYN03770";
//	$dataJames = urlToArray($pathJames);
//	if($dataJames[0]) {
//		file_put_contents(ROOT.'EXT_james.json', $dataJames[0]);
//	} else {
//		quick_log("james_bad_data.txt", $dataJames[0]);
//	}
	// Nearby CWOP (Islington)
	 $aprsKey = defined('APRSFI_KEY') ? APRSFI_KEY : '';
	 $pathIslington = "https://api.aprs.fi/api/get?name=2E0RGX-13&what=wx&apikey=$aprsKey&format=json";
	 $dataIslington = urlToArray($pathIslington);
	 if($dataIslington[0]) {
	 	file_put_contents(ROOT.'EXT_islington.json', $dataIslington[0]);
	 } else {
	 	quick_log("islington_bad_data.txt", $dataIslington[0]);
	 }
	$pathPotters = "https://api.aprs.fi/api/get?name=G6LTT&what=wx&apikey=$aprsKey&format=json";
	$dataPotters = urlToArray($pathPotters);
	if($dataPotters[0]) {
		file_put_contents(ROOT.'EXT_potters.json', $dataPotters[0]);
	} else {
		quick_log("potters_bad_data.txt", $dataPotters[0]);
	}
}

//########### OLD WD file crap ##########//
if(date('i') % 5 == 1) {
	$repStamp = mktime(0,0,0,$dmonth,$dday-1);
	$report = date("FY", $repStamp).'.htm';
	$graphDaily = date("Ymd", $repStamp).'.gif';
	$repStamp2 = mktime(0,0,0,$dmonth-1,$dday);
	$report2 = 'dailynoaareport'. date("n", $repStamp2).date("Y", $repStamp2).'.htm';
	$oldWDfiles = array("wx18.html", "wx19.html", "dailydatalog.txt", "dailyreport.htm", "dailynoaareport.htm",
		"curr72hourgraph.gif", "curr24hourgraph.gif", "energy.gif", "windtempraintrendyear.gif", "windtempraintrend.gif",
		"weekgra.gif", "phptags.php", "main_tags.php", "dailywebcam.gif", "dailywebcam2.gif", $report, $report2);
	$sitev2 = ROOT.'oldSites/sitev2/';

	foreach ($oldWDfiles as $oldFile) {
		if(file_exists(ROOT.$oldFile)) {
			copy(ROOT. $oldFile, $sitev2.$oldFile);
			unlink(ROOT.$oldFile);
			if($tstamp == '0016') log_events("WDfilesMoved.txt", $oldFile);
		}
	}

}
if($tstamp == '0721') {
	//Some files are not here because they are frequently uploaded, so it is best to use 0444 perms to block them instead
	copy(ROOT. $graphDaily, ROOT.date("Y", $repStamp).'/'.$graphDaily);
	$daystamp = date("Ymd", $repStamp);
	$WDfilesToDelete_daily = array($daystamp.'.gif', 'windtempraintrend'.$dmonth.'.gif', 'windtempraintrend3d'.$dmonth.'.gif',
		'yesterdaygraph.gif', date("Ymd", $repStamp).'dailywebcam.gif', date("Ymd", $repStamp).'dailywebcam2.gif',
		'windtempraintrend3d.gif', 'climatedataout.html', 'monthtodate.gif', 'raindetail.gif', 'weekrep.htm'
	);
	foreach ($WDfilesToDelete_daily as $fileToDelete) {
		if(file_exists(ROOT.$fileToDelete)) {
			copy(ROOT.$fileToDelete, ROOT.'oldSites/'.$fileToDelete); // rather than delete, move here
			unlink(ROOT.$fileToDelete);
			log_events("WDfilesDeleted.txt", $fileToDelete);
		}
	}
}
if($tstamp == '0621' && $dday == 1) {
	$WDfilesToDelete_monthly = array($yr_yest.$mon_yest.'windrose.gif', $mon_yest.'monthtodate.gif', $mon_yest.$yr_yest.'monthtodate.gif',
		'windtempraintrend'.$mon_yest.'.gif', 'windtempraintrend3d'.$mon_yest.'.gif', $report, $report2,
		'climatedataoutyear.html', 'noaareportyear.htm'
	);
	foreach ($WDfilesToDelete_monthly as $fileToDelete) {
		if(file_exists(ROOT.$fileToDelete)) {
			copy(ROOT.$fileToDelete, ROOT.'oldSites/'.$fileToDelete); // rather than delete, move here
			unlink(ROOT.$fileToDelete);
			log_events("WDfilesDeleted.txt", $fileToDelete);
		}
	}
}

if($tstamp == '2359') {
	$path = ROOT.'Logs/siteV3Access.txt';
	if(file_exists($path)) {
		$path_new = ROOT.'Logs/old/siteV3Access_day_of_month_'. date('d') .'.txt';
		copy($path, $path_new);
		unlink($path);
	}
}

// CHECK for data flat-lining
$same = true;
foreach($HR24['trend'] as $ti => $trend) {
	if($trend['temp'] != $temp || $trend['humi'] != $humi) {
		$same = false;
		break;
	}
}
if($same && (date('i') % 10 == 1)) {
	mail("alerts@nw3weather.co.uk","Data is flat-lining","Alert! Temperature/Hum is stuck at $temp / $humi");
}

if($tstamp == '2336') {
	pullAndSavePondData();
}

///////END OF SCRIPT////////END OF SCRIPT///////////////////////////////////////////////////////////################
$p_time = microtime(get_as_float) - $t_start;
file_put_contents( ROOT."Logs/cronExecuted.txt", myround($p_time) );
/////////END OF SCRIPT//////END OF SCRIPT///////////////////////////////////////////////////////////################
echo "END: ". date('r'). "\n";

### Functions ###

/**
 * Maps a WMO weather code (from Open-Meteo) to a [icon, description] pair.
 * Icons match the *_lg.png set in static-images/.
 */
function forecastIcon($code) {
	$map = array(
		0  => array('clear', 'Clear sky'),
		1  => array('clear', 'Mainly clear'),
		2  => array('partlycloudy', 'Partly cloudy'),
		3  => array('cloudy', 'Overcast'),
		45 => array('fog', 'Fog'),
		48 => array('fog', 'Freezing fog'),
		51 => array('rain', 'Light drizzle'),
		53 => array('rain', 'Drizzle'),
		55 => array('rain', 'Heavy drizzle'),
		56 => array('rain', 'Freezing drizzle'),
		57 => array('rain', 'Freezing drizzle'),
		61 => array('rain', 'Light rain'),
		63 => array('rain', 'Rain'),
		65 => array('rain', 'Heavy rain'),
		66 => array('rain', 'Freezing rain'),
		67 => array('rain', 'Freezing rain'),
		71 => array('snow', 'Light snow'),
		73 => array('snow', 'Snow'),
		75 => array('snow', 'Heavy snow'),
		77 => array('snow', 'Snow grains'),
		80 => array('rain_showers', 'Light showers'),
		81 => array('rain_showers', 'Showers'),
		82 => array('rain_showers', 'Heavy showers'),
		85 => array('snow_showers', 'Snow showers'),
		86 => array('snow_showers', 'Snow showers'),
		95 => array('tstorms', 'Thunderstorm'),
		96 => array('tstorms_showers', 'Thunderstorm with hail'),
		99 => array('tstorms_showers', 'Thunderstorm with hail'),
	);
	return isset($map[$code]) ? $map[$code] : array('cloudy', 'Cloudy');
}

function graph_stitch() {
	$im1 = imagecreatefrompng('1.png');
	$im2 = imagecreatefrompng('2.png');
	$im3 = imagecreatefrompng('3.png');
	if($im1) {
		$h1 = 407;	$h2 = 390;	$h3 = 220;
		$dimx = 850;
		$dimy = $h1+$h2+$h3;

		//full-size version
		$im_stitch = imagecreatetruecolor($dimx, $dimy);
		imagecopyresampled($im_stitch, $im1, 0, 0,   0, 0,  $dimx, $h1, $dimx, $h1);
		imagecopyresampled($im_stitch, $im2, 0, $h1, 0, 17, $dimx, $h2, $dimx, $h2);
		imagecopyresampled($im_stitch, $im3, 0, $h1+$h2, 0, 0,  $dimx, $h3, $dimx, $h3);
		imagepng($im_stitch, ROOT.'stitchedmaingraph.png', 9);

		imagedestroy($im1);
		imagedestroy($im2);
		imagedestroy($im3);
		imagedestroy($im_stitch);
	}
	else {
		error_log('bad image when trying to stitch');
	}

//	if(date('i') == '00') {
		$im1 = imagecreatefrompng('1s.png');
		$im2 = imagecreatefrompng('2s.png');
		$im3 = imagecreatefrompng('3s.png');
		if($im1) {
			//mini-version
			$h1 = 245;	$h2 = 225;	$h3 = 149;
			$dimy = $h1 + $h2 + $h3;
			$fix1 = 9;

			$im_stitch = imagecreatetruecolor(smallGraphWidth1 + $fix1, $dimy);
			imagefill( $im_stitch, 0, 0, imagecolorallocate($im_stitch, 255, 255, 255) );
			imagecopyresampled($im_stitch, $im1, $fix1, 0,   0, 0,  smallGraphWidth1, $h1, smallGraphWidth1, $h1);
			imagecopyresampled($im_stitch, $im2, 0, $h1, 0, 17, smallGraphWidth2, $h2, smallGraphWidth2, $h2);
			imagecopyresampled($im_stitch, $im3, 0, $h1+$h2, 0, 0,  smallGraphWidth3, $h3, smallGraphWidth3, $h3);
			imagepng($im_stitch, ROOT.'stitchedmaingraph_small.png', 9);

			imagedestroy($im1);
			imagedestroy($im2);
			imagedestroy($im3);
			imagedestroy($im_stitch);
		}
		else {
			error_log('bad image when trying to stitch smalls');
		}
//	}
}


/**
 * Neaten up the WD-uploaded custom log by padding missing lines and cleaning values
 * Copies output to goodlog.txt, and todaylog.txt using lines for today only
 */
function logneatenandrepair() {
	global $goodlog, $todaylog;

	// e.g. 02,10,24,1.3,4.8,214,12.5,74,1020,8.0,0
	// wind: 3, 4, 5
	// T/H/Dew: 6, 7, 9
	// Baro: 8
	// Rain: 10
	// PM2.5: 11
	$FIELDS_TO_PRESERVE = [3, 4, 5, 6, 7, 8, 9, 10, 11];

	if($FIELDS_TO_PRESERVE) {
		$live_data = file($goodlog);
		$len = count($live_data);
		$livel = array();
		for($i = 0; $i < $len; $i++) {
			$livel[substr($live_data[$i], 0, 8)] = explode(',', trim($live_data[$i]));
		}
	}

	$filelog = fopen($goodlog, "w");
	$filelog2 = fopen($todaylog,"w");

	$filcust = file(ROOT.'customtextout.txt');
	$len = count($filcust);

	for($i = 0; $i < $len; $i++) {
		$custl[$i] = explode(',', $filcust[$i]);
		if($custl[$i][8] == "1007.1" && $i > 0) {
			$custl[$i][8] = $custl[$i-1][8];
		}
		$custl[$i][8] = (int)$custl[$i][8];
		$custl[$i][10] = (float)$custl[$i][10];
		if($FIELDS_TO_PRESERVE) { // Preserve the goodlog values
			$ts = substr($filcust[$i], 0, 8);
			if($livel[$ts]) {
				foreach($FIELDS_TO_PRESERVE as $j) {
					$custl[$i][$j] = $livel[$ts][$j];
				}
			}
		}
	}

	$linewrite[0] = implode(',', $custl[0]);

	$cnt = 0;
	for($i = 1; $i < $len; $i++) {
		$diff = ( mktime($custl[$i][0], $custl[$i][1], 0) - mktime($custl[$i-1][0], $custl[$i-1][1], 0) ) / 60;
		if( $diff > 1 && $diff < 10 ) {
			for($j = 1; $j < $diff; $j++) {
				$linewrite[$i+$j-1+$cnt] = $linewrite[$i+$j-2+$cnt];
			}
			$cnt += $j - 1;
		}
		$linewrite[$i+$cnt] = implode(',', $custl[$i]);
		$lineday[$i+$cnt] = (int)$custl[$i][2];
	}

	$len2 = count($linewrite);
	for($i = $cnt + 1; $i < $len2; $i++) {
		fwrite($filelog, $linewrite[$i]."\r\n");
		if( $lineday[$i] == date('j') ) {
			fwrite($filelog2, $linewrite[$i]."\r\n");
		}
	}

	fclose($filelog);
	fclose($filelog2);
}

/**
 * serialises csv files for all years on record
 * @param string $csv can be dat, datt or datm
 * @param boolean [$today = true]
 */
function serialiseCSV($csv, $today = true) {
	global $dyear, $dmonth, $dday, $siteRoot, $newNOW;

	$data = array();
	$dataNew = [];

	for($year = 2009; $year <= $dyear; $year++) {
		$yrfil = $siteRoot.$csv.$year.'.csv';
		if(file_exists($yrfil)) {
			$raw = file($yrfil);
			$header = explode(',', trim($raw[0]));
			$cntRaw = count($raw);
			for($i = 1; $i < $cntRaw; $i++) {
				$day = date('j', strtotime('Jan 1st '. (string)$year . ' + ' . (string)($i-1) . ' days'));
				$month = date('n', strtotime('Jan 1st '. (string)$year . ' + ' . (string)($i-1) . ' days'));
				$rawa = explode(',', $raw[$i]);
				$cntRawa = count($rawa);
				for($j = 0; $j < $cntRawa; $j++) {
					$data[$j][$year][$month][$day] = $rawa[$j];
					$dataNew[$header[$j]][$year][$month][$day] = $rawa[$j];
				}
			}
			if( $year == $dyear && $today && $csv != 'datm' ) {
				$list = array(
					$newNOW['min']['temp'], $newNOW['max']['temp'], $newNOW['mean']['temp'],
					$newNOW['min']['humi'], $newNOW['max']['humi'], $newNOW['mean']['humi'],
					$newNOW['min']['pres'], $newNOW['max']['pres'], $newNOW['mean']['pres'],
					$newNOW['mean']['wind'], $newNOW['max']['wind'], $newNOW['max']['gust'], $newNOW['mean']['wdir'],
					$newNOW['mean']['rain'], $newNOW['max']['rnhr'], $newNOW['max']['rn10'], $newNOW['max']['rate'],
					$newNOW['min']['dewp'], $newNOW['max']['dewp'], $newNOW['mean']['dewp'],
					$newNOW['min']['night'], $newNOW['max']['day'],
					$newNOW['max']['tchange10'], $newNOW['max']['tchangehr'], $newNOW['max']['hchangehr'],
					$newNOW['min']['tchange10'], $newNOW['min']['tchangehr'], $newNOW['min']['hchangehr'],
					$newNOW['max']['w10m'],
					$newNOW['min']['feel'], $newNOW['max']['feel'], $newNOW['mean']['feel'],
					$newNOW['misc']['frosthrs'],
					isset($newNOW['min']['pm25']) ? $newNOW['min']['pm25'] : '',
					isset($newNOW['max']['pm25']) ? $newNOW['max']['pm25'] : '',
					isset($newNOW['mean']['pm25']) ? $newNOW['mean']['pm25'] : '',
					' \n'
				);

				$listt = array(
					$newNOW['timeMin']['temp'], $newNOW['timeMax']['temp'], '',
					$newNOW['timeMin']['humi'], $newNOW['timeMax']['humi'], '',
					$newNOW['timeMin']['pres'], $newNOW['timeMax']['pres'], '',
					'', $newNOW['timeMax']['wind'], $newNOW['timeMax']['gust'], '',
					'', $newNOW['timeMax']['rnhr'], $newNOW['timeMax']['rn10'], $newNOW['timeMax']['rate'],
					$newNOW['timeMin']['dewp'], $newNOW['timeMax']['dewp'], '',
					$newNOW['timeMin']['night'], $newNOW['timeMax']['day'],
					$newNOW['timeMax']['tchange10'], $newNOW['timeMax']['tchangehr'], $newNOW['timeMax']['hchangehr'],
					$newNOW['timeMin']['tchange10'], $newNOW['timeMin']['tchangehr'], $newNOW['timeMin']['hchangehr'],
					$newNOW['timeMax']['w10m'],
					$newNOW['timeMin']['feel'], $newNOW['timeMax']['feel'], '',
					'',
					isset($newNOW['timeMin']['pm25']) ? $newNOW['timeMin']['pm25'] : '',
					isset($newNOW['timeMax']['pm25']) ? $newNOW['timeMax']['pm25'] : '',
					'',
					' \n'
				);

				for($j = 0; $j < $cntRawa; $j++) {
					$data[$j][$year][$dmonth][$dday] = ($csv == 'dat') ? $list[$j] : $listt[$j];
					$dataNew[$header[$j]][$year][$dmonth][$dday] = ($csv == 'dat') ? $list[$j] : $listt[$j];
				}
			}
		}
	}
	file_put_contents( ROOT.'serialised_'.$csv.'.txt', serialize($data) );
	file_put_contents( ROOT.'serialised_'.$csv.'_new.txt', serialize($dataNew) );
	// For perf, serialize each var too
	if($csv === "dat") {
		foreach ($data as $j => $dat) {
			file_put_contents( ROOT."serialised_dat_$j.txt", serialize($dat) );
		}
		foreach ($dataNew as $j => $dat) {
			file_put_contents( ROOT."serialised_dat_new_$j.txt", serialize($dat) );
		}
	}
}

function serialiseCSVm() {
	$data = array();
	$dataNew = [];

	$DATA = unserialize(file_get_contents(ROOT . 'serialised_dat.txt'));

	for($year = 2009; $year <= date('Y'); $year++) {
		$yrfil = ROOT.'datm'.$year.'.csv';
		if(file_exists($yrfil)) {
			$raw = file($yrfil);
			$header = explode(',', trim($raw[0]));
			$cnt1 = count($raw);
			for($i = 1; $i < $cnt1; $i++) {
				$rawa = explode(',', $raw[$i]);
				for($j = 0; $j <= 12; $j++) { //up-to and including fog, plus pond
					if($j >= 8 && $j < 12) {
						continue;
					}
					$day = date('j', strtotime('Jan 1st '. (string)$year . ' + ' . (string)($i-1) . ' days'));
					$month = date('n', strtotime('Jan 1st '. (string)$year . ' + ' . (string)($i-1) . ' days'));
					if($j >= 3 && $j !== 12 && $rawa[$j] == '') {
						$rawa[$j] = '0';
					}
					if($j === 3) { //falling snow
						$rawa[$j] = ($rawa[$j] == 'y') ? $DATA[13][$year][$month][$day] + 0.01 : $rawa[$j];
					}
					if($j === 12 && $year < 2019) { // pond temp from 2019
						$rawa[$j] = '';
					}
					$data[$j][$year][$month][$day] = $rawa[$j];
					$dataNew[$header[$j]][$year][$month][$day] = $rawa[$j];
				}
			}
		}
	}
	file_put_contents( ROOT.'serialised_datm.txt', serialize($data) );
	file_put_contents( ROOT.'serialised_datm_new.txt', serialize($dataNew) );
	// For perf, serialize each var too
	foreach ($data as $j => $dat) {
		file_put_contents( ROOT."serialised_datm_$j.txt", serialize($dat) );
	}
	foreach ($dataNew as $j => $dat) {
		file_put_contents( ROOT."serialised_dat_new_$j.txt", serialize($dat) );
	}
}

function checkDatmWritten() {
	if(write_datm("0")) {
		mail("alerts@nw3weather.co.uk", "Failed to receive sunhrs!", "Data not written for this day so defaulted to zero sun");
	}
}

function get_wuvu_cnt() {
	$filwu = urlToArray('http://www.wunderground.com/weatherstation/WXDailyHistory.asp?ID=ILONDONL9');
	if($filwu !== false) {
		$limit = count($filwu);
		for ($i = 8000; $i < $limit; $i++) {
			if(strpos($filwu[$i], "view_count") > 0) {
				$wuvul = $filwu[$i];
				break;
			}
		}
		if($wuvul) {
			return (int)preg_replace('/\D/', '', $wuvul);
		} else {
			return 'not found';
		}
	}
	return 'timeout';
}

/**
Get an XML document over http with a 3s timeout
 * @param string $url
 * @return boolean true on success, false otherwise
 * @author http://stackoverflow.com/questions/4867086/timing-out-a-script-portion-and-allowing-the-rest-to-continue
*/
function getXml($url){
	$ch = curl_init($url);

	curl_setopt_array($ch, array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 3
	));

	if($xml = curl_exec($ch)){
		return simplexml_load_string($xml);
	}
	else {
		return false;
	}
}

function serializeHistoricalData() {
	$raw = file(ROOT."historical.csv");
	$header = explode(',', trim($raw[0]));
	$col_count = count($header);
	$cnt = count($raw);
	$data = array();
	for($i = 1; $i < $cnt; $i++) {
		$rawa = explode(',', trim($raw[$i]));
		$dp = explode("-", $rawa[0]);
		$year = (int)($dp[0]);
		$month = (int)($dp[1]);
		$day = (int)($dp[2]);
		for($j = 1; $j < $col_count; $j++) {
			if($rawa[$j] !== "") {
				$data[$header[$j]][$year][$month][$day] = (float)$rawa[$j];
			}
		}
		if($rawa[10] !== "" && $rawa[11] !== "") {
			$data["tmean"][$year][$month][$day] = ($rawa[10] + $rawa[11]) / 2;
			$data["trange"][$year][$month][$day] = $rawa[11] - $rawa[10];
		}
	}
	// Split by var for perf
	foreach ($data as $var => $dat) {
		file_put_contents( ROOT."serialised_historical_$var.txt", serialize($dat) );
	}
}

function pullAndSavePondData($url = 'https://ponds.nsupdate.info/pond-temps.html', $outputFile = 'pond_temp.txt') {
    try {
        // Get the HTML content
        $html = @file_get_contents($url);
        if (!$html) {
            throw new Exception("Failed to fetch the web page.");
        }
		file_put_contents(ROOT."pond_raw.html", $html);

        // Load HTML into DOMDocument
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

		$table = $dom->getElementsByTagName('table')->item(0);
        if (!$table) {
            throw new Exception("No table found.");
        }

        $output = null;
        foreach ($table->getElementsByTagName('tr') as $row) {
            if ($row->parentNode->nodeName == 'thead') continue;

            $cols = $row->getElementsByTagName('td');
            if ($cols->length < 2) continue;

            $deepTemp = trim($cols->item(1)->nodeValue);

            $output = $deepTemp;
			break;
        }
        if (!is_null($output)) {
            file_put_contents(ROOT.$outputFile, $output);
			return $output;
        } else {
            throw new Exception("No data found to write.");
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        return null;
    }
}

?>
