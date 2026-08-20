<?php
// PurpleAir sensor index to pull air-quality (PM2.5) from. Set after picking the
// nearest outdoor sensor to the station; 0 = unconfigured (fetch skipped).
// The API key lives in secrets.php (PURPLEAIR_KEY), included below.
const PURPLEAIR_SENSOR = 197637;

if(PHP_SAPI !== 'cli') {
	http_response_code(403);
	die("CLI only.\n");
}

$t_start = microtime(true);
require_once('/var/www/html/cron/bootstrap.php');
require_once(ROOT . 'cron/DatmWriter.php');
require_once(ROOT . 'cron/CacheSerialiser.php');
require_once(ROOT . 'cron/MidnightData.php');
require_once(ROOT . 'cron/MonthlyReport.php');

// API keys (git-excluded; this cron is the only includer - keys never reach page code).
if(file_exists(ROOT.'secrets.php')) include(ROOT.'secrets.php');

echo "START: ". date('r'). "\n";

Live::init();
Cron::bindDateGlobals();
Cron::bindLiveGlobals();

$fiveMinutely = date('i') % 5 == 0;
$tstamp = date('Hi');
$datmCheckTime = Cron::DATM_CHECK_TIME;
$yr_yest = Date::$yr_yest;
$mon_yest = Date::$mon_yest;
$day_yest = Date::$day_yest;
$dtstamp_yest = Date::$dtstamp_yest;
$firstday = Date::$firstday;
$dyear = Date::$dyear;
$dmonth = Date::$dmonth;
$dday = Date::$dday;
$siteRoot = ROOT;

//Create clientraw backup for use in mainData when trying to access it mid-upload
if(!$badCRdata) {
	copy(LIVE_DATA_PATH, ROOT.'clientrawBackup.txt');
}

$stamplog = ROOT.'logfiles/daily/'.date('Ymd').'log.txt';
$goodlog = ROOT.'goodlog.txt';
$todaylog = ROOT.'logfiles/daily/todaylog.txt';
$goodlog_backup = ROOT.'logfiles/backup/goodlog_'. $tstamp .'.txt';

//Rebuild 24hr and today data logs, plus neaten.
//Do 10-minutely (on top of after downtime) just for extra security, and in case the cron missed an append
$wdDowntimeLog = ROOT . "Logs/WDuploadReallyBad.txt";
$recentWDdowntime = file_exists($wdDowntimeLog) && time() - filemtime($wdDowntimeLog) < 1200;
if(false && $tstamp != '0000' && (date('i') % 10 == 1 || $recentWDdowntime)) {
	$fsize = filesize(ROOT.'customtextout.txt');
	$fage = time() - filemtime(ROOT.'customtextout.txt');
	if($fsize > 61000 && $fsize < 75000) { //probably valid
		if($fage < 500) { // probably new
		   # TODO: check data integrity: are all hours in the file
			copy($goodlog, $goodlog_backup);
			logneatenandrepair();
		} else {
			Page::quick_log("badCustomlogUpload.txt", $fsize.'B '. $fage.'s');
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
$pm25 = file_exists(V5_CACHE_ROOT.'pm25_latest.txt') ? trim(file_get_contents(V5_CACHE_ROOT.'pm25_latest.txt')) : '';
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
	MidnightData::run();

	exec(escapeshellarg(PHP_BIN) . ' -q ' . ROOT . 'HourlyLogs.php > ' . escapeshellarg(ROOT . 'html/Log/hrlogOutput.html'));

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

// 'API'
file_put_contents(ROOT.'api_latest.txt', $newLine);

//datm append
if($tstamp == $datmCheckTime) {
	DatmWriter::checkWritten();
}

// v5 live and rolling caches.
nw3_ensure_runtime_dirs();
$newNOW = Data::dailyData();
$GLOBALS['newNOW'] = $newNOW;
nw3_atomic_write(V5_CACHE_ROOT . 'serialised_datNow.txt', serialize($newNOW));
nw3_atomic_write(V5_CACHE_ROOT . 'serialised_datHr24.txt', serialize(Data::dailyData(date('Ymd'))));

$yestFile = V5_CACHE_ROOT . 'serialised_datYest.txt';
if(!file_exists($yestFile) || date('Ymd', filemtime($yestFile)) !== date('Ymd')) {
	$yestYmd = date('Ymd', $dtstamp_yest);
	if(file_exists(ROOT . "logfiles/daily/{$yestYmd}log.txt")) {
		nw3_atomic_write($yestFile, serialize(Data::dailyData($yestYmd)));
	}
}

if($fiveMinutely) {
	CacheSerialiser::serialiseCSV('dat');
	exec(escapeshellarg(PHP_BIN) . ' -q ' . ROOT . 'warm_detail_summaries.php', $warmOut, $warmRc);
	if($warmRc !== 0) {
		Page::quick_log('warm_detail_summaries_bad.txt', 'rc=' . $warmRc . ' ' . substr(implode(' ', $warmOut), 0, 200));
	}
}

// Historical detail windows: too expensive for every 5 minutes. Refresh once
// a day (after midnight data is in) and merge with the station cache on request.
if($tstamp === '0121') {
	exec(escapeshellarg(PHP_BIN) . ' -q ' . ROOT . 'warm_detail_summaries.php hist', $warmHOut, $warmHRc);
	if($warmHRc !== 0) {
		Page::quick_log('warm_detail_summaries_bad.txt', 'hist rc=' . $warmHRc . ' ' . substr(implode(' ', $warmHOut), 0, 200));
	}
}

if(!file_exists(V5_CACHE_ROOT . 'serialised_datt.txt')
		|| (file_exists(ROOT . 'datt' . $yr_yest . '.csv') && time() - filemtime(ROOT . 'datt' . $yr_yest . '.csv') < 65)) {
	CacheSerialiser::serialiseCSV('datt');
}
if(!file_exists(V5_CACHE_ROOT . 'serialised_datm.txt')
		|| (file_exists(ROOT . 'datm' . $yr_yest . '.csv') && time() - filemtime(ROOT . 'datm' . $yr_yest . '.csv') < 65)) {
	CacheSerialiser::serialiseCSVm();
	exec(escapeshellarg(PHP_BIN) . ' -q ' . ROOT . 'warm_detail_summaries.php datm', $warmMOut, $warmMRc);
	if($warmMRc !== 0) {
		Page::quick_log('warm_detail_summaries_bad.txt', 'datm rc=' . $warmMRc . ' ' . substr(implode(' ', $warmMOut), 0, 200));
	}
}
if(!file_exists(V5_CACHE_ROOT . 'serialised_historical_tmax.txt')
		|| (file_exists(ROOT . 'historical.csv') && time() - filemtime(ROOT . 'historical.csv') < 60)) {
	CacheSerialiser::serializeHistoricalData();
	exec(escapeshellarg(PHP_BIN) . ' -q ' . ROOT . 'warm_detail_summaries.php hist', $warmHOut, $warmHRc);
	if($warmHRc !== 0) {
		Page::quick_log('warm_detail_summaries_bad.txt', 'histcsv rc=' . $warmHRc . ' ' . substr(implode(' ', $warmHOut), 0, 200));
	}
}

// Monthly report
if($firstday && $fiveMinutely && time() - filemtime(ROOT.'datm'.$yr_yest.'.csv') < 303) {
	$rep = MonthlyReport::generate((int)$mon_yest, (int)$yr_yest);
	mail("blr@nw3weather.co.uk","Monthly report $mon_yest $yr_yest", $rep);
}

 // Ensure that data.php did indeed run at midnight
if($tstamp == '0700') {
	$age = time() - filemtime(ROOT."dat" . $yr_yest . ".csv");
	if($age > 30000) { // 8.3 hrs
		MidnightData::run();
		mail("alerts@nw3weather.co.uk","Cron fail","Alert! Cron data.php failed on first attempt. Problems may exist");
	}
}

 //check for file issues
if(date('i') % 15 == 1) {
	if($OUTAGE) {
		Page::quick_log("outage.txt", "Outage: $OUTAGE ($diff s)");
		if($diff < 5000) {
			mail("alerts@nw3weather.co.uk","Old live data","Alert! live data not updating. Act NOW!");
		}
	}
}

$HR24 = unserialize(file_get_contents(CACHE_ROOT . 'serialised_datHr24.txt'));
//24hr Rain exceeds 20 mm
$rn24hrs = $HR24['trendRn'][0];
if( $rn24hrs > 20 && $rn24hrs > $rain && ($HR24['trendRn'][0] - $HR24['trendRn']['10m'] > 0) ) {
	Page::quick_log('rain_excess.txt', $rn24hrs);
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
	$noaaMetar = Util::urlToArray('http://tgftp.nws.noaa.gov/data/observations/metar/stations/EGLL.TXT');
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

// Local forecast from Yr.no (same location as wx5: 2-2647553 Hampstead).
// Full payload cached for homepage strip + wx5 table/meteogram.
if(date('i') % 30 == 12) {
	$fcUrl = 'https://www.yr.no/api/v0/locations/2-2647553/forecast';
	$fcCtx = stream_context_create(array('http' => array(
		'header' => "Accept: application/json\r\n"
			. "User-Agent: nw3weather.co.uk/v5 (https://nw3weather.co.uk)\r\n",
		'timeout' => 8,
	)));
	$fcRaw = @file_get_contents($fcUrl, false, $fcCtx);
	$fcJson = $fcRaw ? json_decode($fcRaw, true) : null;
	if($fcJson && !empty($fcJson['dayIntervals']) && is_array($fcJson['dayIntervals'])) {
		$todayYmd = date('Y-m-d');
		$tomorrowYmd = date('Y-m-d', strtotime($todayYmd . ' +1 day'));

		$fcDays = array();
		foreach($fcJson['dayIntervals'] as $i => $day) {
			$date = isset($day['start']) ? substr($day['start'], 0, 10) : '';
			$sym = isset($day['twentyFourHourSymbol']) ? $day['twentyFourHourSymbol'] : '';
			list($fcIcon, $fcDesc) = yrForecastIcon($sym);
			if($date === $todayYmd) { $label = 'Today'; }
			elseif($date === $tomorrowYmd) { $label = 'Tomorrow'; }
			else { $label = $date ? date('D j M', strtotime($date)) : 'Day ' . ($i + 1); }
			$fcDays[] = array(
				'label' => $label,
				'date' => $date,
				'icon' => $fcIcon,
				'desc' => $fcDesc,
				'symbol' => $sym,
				'tmax' => isset($day['temperature']['max']) ? round((float)$day['temperature']['max'], 1) : null,
				'tmin' => isset($day['temperature']['min']) ? round((float)$day['temperature']['min'], 1) : null,
				'precip' => isset($day['precipitation']['value']) ? round((float)$day['precipitation']['value'], 1) : null,
				'windMin' => isset($day['wind']['min']) ? yrMsToMph($day['wind']['min']) : null,
				'windMax' => isset($day['wind']['max']) ? yrMsToMph($day['wind']['max']) : null,
			);
		}

		$fcHourly = array();
		if(!empty($fcJson['shortIntervals']) && is_array($fcJson['shortIntervals'])) {
			foreach($fcJson['shortIntervals'] as $iv) {
				$sym = isset($iv['symbolCode']['next1Hour']) ? $iv['symbolCode']['next1Hour']
					: (isset($iv['symbolCode']['next6Hours']) ? $iv['symbolCode']['next6Hours'] : '');
				list($fcIcon, $fcDesc) = yrForecastIcon($sym);
				$fcHourly[] = array(
					't' => isset($iv['start']) ? (strtotime($iv['start']) * 1000) : null,
					'temp' => isset($iv['temperature']['value']) ? round((float)$iv['temperature']['value'], 1) : null,
					'precip' => isset($iv['precipitation']['value']) ? round((float)$iv['precipitation']['value'], 2) : null,
					'wind' => isset($iv['wind']['speed']) ? yrMsToMph($iv['wind']['speed']) : null,
					'windDir' => isset($iv['wind']['direction']) ? (int)round((float)$iv['wind']['direction']) : null,
					'pressure' => isset($iv['pressure']['value']) ? round((float)$iv['pressure']['value'], 1) : null,
					'icon' => $fcIcon,
					'desc' => $fcDesc,
					'symbol' => $sym,
				);
			}
		}

		$fcPeriods = array();
		if(!empty($fcJson['longIntervals']) && is_array($fcJson['longIntervals'])) {
			foreach($fcJson['longIntervals'] as $iv) {
				$sym = isset($iv['symbolCode']['next6Hours']) ? $iv['symbolCode']['next6Hours']
					: (isset($iv['symbolCode']['next1Hour']) ? $iv['symbolCode']['next1Hour'] : '');
				list($fcIcon, $fcDesc) = yrForecastIcon($sym);
				$startTs = isset($iv['start']) ? strtotime($iv['start']) : false;
				$fcPeriods[] = array(
					't' => $startTs ? ($startTs * 1000) : null,
					'end' => isset($iv['end']) ? (strtotime($iv['end']) * 1000) : null,
					'label' => $startTs ? date('D H:i', $startTs) : '',
					'temp' => isset($iv['temperature']['value']) ? round((float)$iv['temperature']['value'], 1) : null,
					'tmax' => isset($iv['temperature']['max']) ? round((float)$iv['temperature']['max'], 1) : null,
					'tmin' => isset($iv['temperature']['min']) ? round((float)$iv['temperature']['min'], 1) : null,
					'precip' => isset($iv['precipitation']['value']) ? round((float)$iv['precipitation']['value'], 2) : null,
					'wind' => isset($iv['wind']['speed']) ? yrMsToMph($iv['wind']['speed']) : null,
					'windDir' => isset($iv['wind']['direction']) ? (int)round((float)$iv['wind']['direction']) : null,
					'icon' => $fcIcon,
					'desc' => $fcDesc,
					'symbol' => $sym,
				);
			}
		}

		if(count($fcDays)) {
			nw3_atomic_write(V5_CACHE_ROOT.'forecast_v5.json', json_encode(array(
				'updated' => time(),
				'source' => 'yr',
				'location' => '2-2647553',
				'days' => $fcDays,
				'hourly' => $fcHourly,
				'periods' => $fcPeriods,
			)));
		}
	} else {
		Page::quick_log('forecast_bad.txt', substr((string)$fcRaw, 0, 200));
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
		nw3_atomic_write(V5_CACHE_ROOT.'pm25_latest.txt', (string)round((float)$pm25Now, 1));
	} else {
		Page::quick_log('purpleair_bad.txt', substr((string)$paRaw, 0, 200));
	}
}

// Windy.app map widget (wx5): their windy_map_async.js loader fetches manifest.json
// cross-origin, but that response carries no CORS header, so the browser blocks it and
// the widget never loads. Resolve the hashed bundle name here instead and cache it for
// the page to script-src directly (classic scripts aren't CORS-checked).
if(date('i') % 30 == 13) {
	$wmRaw = @file_get_contents('https://windy.app/widget3/manifest.json', false,
		stream_context_create(array('http' => array('timeout' => 6))));
	$wmJson = $wmRaw ? json_decode($wmRaw, true) : null;
	$wmFile = isset($wmJson['js'][0]) ? $wmJson['js'][0] : null;
	if($wmFile !== null && preg_match('/^windy_map\.[a-z0-9]+\.js$/i', $wmFile)) {
		nw3_atomic_write(V5_CACHE_ROOT.'windy_widget.txt', $wmFile);
	} else {
		Page::quick_log('windy_widget_bad.txt', substr((string)$wmRaw, 0, 200));
	}
}

// External clientraw grab and save
if(false) {
	$path = 'http://www.harpendenweather.co.uk/live/clientraw.txt';
	$harpendenData = Util::urlToArray($path);
	if($harpendenData[0] && count($harpendenData) === 1) {
		file_put_contents(ROOT.'EXT_harpenden.txt', $harpendenData[0]);
	} else {
		Page::quick_log("HarpendenBadData.txt", $harpendenData[0]);
	}
}
if(true && (date('i') % 5 == 4)) {
	// St James
//	$pathJames = "https://api.synopticdata.com/v2/stations/latest?token=790b537f5b0248bc94ec8bbeae0bcba7&stid=SYN03770";
//	$dataJames = Util::urlToArray($pathJames);
//	if($dataJames[0]) {
//		file_put_contents(ROOT.'EXT_james.json', $dataJames[0]);
//	} else {
//		Page::quick_log("james_bad_data.txt", $dataJames[0]);
//	}
	// Nearby CWOP (Islington)
	 $aprsKey = defined('APRSFI_KEY') ? APRSFI_KEY : '';
	 $pathIslington = "https://api.aprs.fi/api/get?name=2E0RGX-13&what=wx&apikey=$aprsKey&format=json";
	 $dataIslington = Util::urlToArray($pathIslington);
	 if($dataIslington[0]) {
	 	file_put_contents(ROOT.'EXT_islington.json', $dataIslington[0]);
	 } else {
	 	Page::quick_log("islington_bad_data.txt", $dataIslington[0]);
	 }
	$pathPotters = "https://api.aprs.fi/api/get?name=G6LTT&what=wx&apikey=$aprsKey&format=json";
	$dataPotters = Util::urlToArray($pathPotters);
	if($dataPotters[0]) {
		file_put_contents(ROOT.'EXT_potters.json', $dataPotters[0]);
	} else {
		Page::quick_log("potters_bad_data.txt", $dataPotters[0]);
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
	$dayTag = date('d');
	foreach(array('siteV5Access.txt', 'rank_runtime.txt') as $logName) {
		$path = ROOT.'Logs/'.$logName;
		if(file_exists($path)) {
			$base = pathinfo($logName, PATHINFO_FILENAME);
			$path_new = ROOT.'Logs/old/'.$base.'_day_of_month_'.$dayTag.'.txt';
			copy($path, $path_new);
			unlink($path);
		}
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
$p_time = microtime(true) - $t_start;
file_put_contents( ROOT."Logs/cronExecuted.txt", Cron::myround($p_time) );
/////////END OF SCRIPT//////END OF SCRIPT///////////////////////////////////////////////////////////################
echo "END: ". date('r'). "\n";

### Functions ###

/**
 * Maps a Yr.no / MET Norway symbol_code to a [icon, description] pair.
 * Icons match the *_lg.png set in static-images/.
 */
function yrForecastIcon($symbol) {
	$base = preg_replace('/_(day|night|polartwilight)$/', '', (string)$symbol);
	$map = array(
		'clearsky' => array('clear', 'Clear sky'),
		'fair' => array('clear', 'Fair'),
		'partlycloudy' => array('partlycloudy', 'Partly cloudy'),
		'cloudy' => array('cloudy', 'Cloudy'),
		'fog' => array('fog', 'Fog'),
		'lightrain' => array('rain', 'Light rain'),
		'rain' => array('rain', 'Rain'),
		'heavyrain' => array('rain', 'Heavy rain'),
		'lightrainshowers' => array('rain_showers', 'Light showers'),
		'rainshowers' => array('rain_showers', 'Showers'),
		'heavyrainshowers' => array('rain_showers', 'Heavy showers'),
		'lightrainandthunder' => array('tstorms', 'Light rain and thunder'),
		'rainandthunder' => array('tstorms', 'Rain and thunder'),
		'heavyrainandthunder' => array('tstorms', 'Heavy rain and thunder'),
		'lightrainshowersandthunder' => array('tstorms_showers', 'Light showers and thunder'),
		'rainshowersandthunder' => array('tstorms_showers', 'Showers and thunder'),
		'heavyrainshowersandthunder' => array('tstorms_showers', 'Heavy showers and thunder'),
		'lightsnow' => array('snow', 'Light snow'),
		'snow' => array('snow', 'Snow'),
		'heavysnow' => array('snow', 'Heavy snow'),
		'lightsnowshowers' => array('snow_showers', 'Light snow showers'),
		'snowshowers' => array('snow_showers', 'Snow showers'),
		'heavysnowshowers' => array('snow_showers', 'Heavy snow showers'),
		'lightsleet' => array('rain', 'Light sleet'),
		'sleet' => array('rain', 'Sleet'),
		'heavysleet' => array('rain', 'Heavy sleet'),
		'lightsleetshowers' => array('rain_showers', 'Light sleet showers'),
		'sleetshowers' => array('rain_showers', 'Sleet showers'),
		'heavysleetshowers' => array('rain_showers', 'Heavy sleet showers'),
	);
	return isset($map[$base]) ? $map[$base] : array('cloudy', 'Cloudy');
}

/** Yr wind is m/s; site UK base unit for Wind is mph. */
function yrMsToMph($ms) {
	if($ms === null || $ms === '') { return null; }
	return round((float)$ms * 2.236936, 1);
}

/**
 * Maps a WMO weather code (from Open-Meteo) to a [icon, description] pair.
 * Icons match the *_lg.png set in static-images/. Kept for reference / fallback.
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
