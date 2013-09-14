<?php
const smallGraphWidth1 = 533;
const smallGraphWidth2 = 500;
const smallGraphWidth3 = 505;

$t_start = microtime(get_as_float);
include('/home/nwweathe/public_html/basics.php');

//Move clientraw from the wrong directory (stupid WD).
$badCRpath = '/home/nwweathe/clientraw.txt';
if(file_exists($badCRpath)) {
	copy($badCRpath, LIVE_DATA_PATH);
	unlink($badCRpath);
	file_put_contents(ROOT."Logs/stupidWD.txt", date("H:i:s d/m/Y") . "\r\n", FILE_APPEND);
}

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

//Rebuild 24hr and today data logs, plus neaten.
//Do 10-minutely (on top of after downtime) just for extra security, and in case the cron missed an append
$recentWDdowntime = time() - filemtime(ROOT. "Logs/WDuploadReallyBad.txt") < 1200;
if($tstamp != '0000' && (date('i') % 10 == 0 || $recentWDdowntime)) {
	$fsize = filesize(ROOT.'customtextout.txt');
	$fage = time() - filemtime(ROOT.'customtextout.txt');
	if($fsize > 56000 && $fage < 60) { //probably new and valid
		logneatenandrepair();
	} else {
		quick_log("badCustomlogUpload.txt", $fsize.'B '. $fage.'s');
	}
}

//Prepare data for appending logs
$lineVars = array($wind, $gust, $wdir, $temp, $humi, $pres, $dewp, $rain);
$isBadLineData = ($pres == 0);
$newLine = date('H,i,j,');
foreach ($lineVars as $value) {
	$newLine .= round( trim($value), 1) . ',';
}
$newLine = substr($newLine, 0, strlen($newLine)-1) . "\r\n";
########################################################
//NEWLINE IS SOMETIMES 0,0,0,0,0,0,0,0,0 (AFTER DATETIME STAMP).
//MUST FIX (POST DATA MAY WELL FIX PROBLEM)
########################################################

//Midnight procedures
if($tstamp == '0000') {
	require(ROOT.'data.php');

	if(date('j') == 1) {
		exec(EXEC_PATH. 'HourlyLogs.php > hrlogOutput.html');
	}

	$rain = 0; //clientraw hasn't had time to upload and reset this
	file_put_contents($todaylog, $newLine); //reset
} else {
	file_put_contents($todaylog, $newLine, FILE_APPEND);
}

//Append goodlog, deleting the oldest line
if(!$isBadLineData) {
	$oldLines = file($goodlog);
	$len = count($oldLines);
	$filelog = fopen($goodlog, "w");
	for($i = 1; $i < $len; $i++) {
		fwrite($filelog, $oldLines[$i]);
	}
	fwrite($filelog, $newLine);
	fclose($filelog);
} else {
	quick_log("bad_dataline.txt", "");
}

// make date-alias of goodlog (this is needed, even though goodlog never called elsewhere, to keep the 24hr rolling aspect going
copy($goodlog, $stamplog);

//serialise current data
$newNOW = dailyData(); //used by serialiseCSV and datNOW
file_put_contents( ROOT.'serialised_datNow.txt', serialize($newNOW) );
file_put_contents( ROOT.'serialised_datHr24.txt', serialize( dailyData( date('Ymd') ) ) );

//datm append
if($tstamp == '0715') { // Earliest I'd ever reasonably get up, and time enough for sun hrs to be published
	$sunhrs = getSunHrs();
	$wethrs = file_get_contents(ROOT."wethrs.txt");
	$listm = array($sunhrs,$wethrs,'u','','','','','','blr','','','','\n');
	$fildatm = fopen(ROOT."datm" . $yr_yest . ".csv", "a");
	fputcsv($fildatm, $listm);
	fclose($fildatm);
	copy(ROOT.'datm' . $yr_yest . '.csv', ROOT.'backup/datm' . $yr_yest.'-'.$day_yest . '.csv');
}

if($fiveMinutely) {
	//Serialise data
	serialiseCSV('dat');

	//Generate tags
	exec(EXEC_PATH. 'cron_tags.php > /dev/null &');

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
			copy('main'.$i.'.png', 'public_html/mainGraph'.$i.'.png');
		}
	}
}

//More midnight procedures
if($tstamp == '0000') {
	//graph save
	$target_st = ROOT. $yr_yest . '/stitchedmaingraph_';
	$target_en = date('Ymd', time() - 60) .'.png';
	copy(ROOT."stitchedmaingraph.png", $target_st .''. $target_en);
	copy(ROOT."stitchedmaingraph_small.png", $target_st .'small_'. $target_en);

	// WU view count
	$filwu = urlToArray('http://www.wunderground.com/weatherstation/WXDailyHistory.asp?ID=ILONDONL9');
	if($filwu !== false) {
		for ($i = 650; $i < 1950; $i++) {
			if(strpos($filwu[$i],"Viewed") > 0) {
				$wuvul = explode(" ", $filwu[$i]);
				break;
			}
		}
		$wuvu = $wuvul[3];
	} else {
		$wuvu = 'timeout';
	}
	quick_log('WU_counts', $wuvu);
}

//serialise time data when modifications occur
if(time() - filemtime(ROOT.'datt'.$yr_yest.'.csv') < 65) {
	serialiseCSV('datt');
}

//serialise manual data when modifications occur
if(time() - filemtime(ROOT.'datm'.$yr_yest.'.csv') < 65) {
	serialiseCSVm();
	//Now generate the sunTags file
	exec(EXEC_PATH. 'cron_tags.php blr ftw > /dev/null &');
}

 // Ensure that data.php did indeed run at midnight
if($tstamp == '0700') {
	$age = time() - filemtime(ROOT."dat" . $yr_yest . ".csv");
	if($age > 30000) { // 8.3 hrs
		require(ROOT.'data.php');
		mail("alerts@nw3weather.co.uk","Cron fail","Alert! Cron data.php failed on first attempt. Problems may exist", "From: server");
	}
}

 //check for file issues
if(date('i') % 15 == 1) {
	$ageC = time() - filemtime(LIVE_DATA_PATH);
	if($ageC > 500) {
		mail("alerts@nw3weather.co.uk","Old live data","Alert! live data not updating. Act NOW!", "From: server");
		quick_log("WDuploadReallyBad.txt", $ageC);
	}
}

$HR24 = unserialize(file_get_contents(ROOT. 'serialised_datHr24.txt'));
//24hr Rain exceeds 20 mm
$rn24hrs = $HR24['trendRn'][0];
if( $rn24hrs > 20 && $rn24hrs > $rain && ($HR24['trendRn'][0] - $HR24['trendRn']['10m'] > 0) ) {
	quick_log('rain_excess.txt', $rn24hrs);
	if(date('i') % 10 == 0) {
		mail("blr@nw3weather.co.uk","Rain excess","Notice! More than 20 mm of rain (" . $rn24hrs . ") has fallen in the past 24 hrs", "From: server");
	}
}
//record 24hr rain is v. close (54.2 is actual)
if($rn24hrs > 54) {
	mail("alerts@nw3weather.co.uk","Rain record very near","Alert! May need to change wx12 soon - " . $rn24hrs . "mm has fallen in the past 24 hrs", "From: server");
}

// METAR retrieve and parse (updated at 20 and 50 mins past the hour, with delay)
if(date('i') % 30 == 28) {
	$noaaMetar = urlToArray('http://weather.noaa.gov/pub/data/observations/metar/stations/EGLL.TXT');
	if($noaaMetar !== false) file_put_contents(ROOT."METAR.txt", $noaaMetar[1]);
}

//WU forecast retrieve
if($tstamp % 100 == 0) {
	$xml = getXml('http://api.wunderground.com/api/46272bfe75051ab1/forecast/q/UK/EGLL.xml');
	if($xml !== false) { //grab the data if available
		$condition = $xml->forecast->simpleforecast->forecastdays->forecastday->conditions;
		file_put_contents(ROOT."WUforecast.txt", $condition);
//		file_put_contents(ROOT."WUforecastDump.txt", print_r($xml->forecast->simpleforecast->forecastdays, true));
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

///////END OF SCRIPT////////END OF SCRIPT///////////////////////////////////////////////////////////################
$p_time = microtime(get_as_float) - $t_start;
file_put_contents( ROOT."Logs/cronExecuted.txt", myround($p_time) );
/////////END OF SCRIPT//////END OF SCRIPT///////////////////////////////////////////////////////////################


### Functions ###
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

	$filelog = fopen($goodlog, "w");
	$filelog2 = fopen($todaylog,"w");

	$filcust = file(ROOT.'customtextout.txt');
	$len = count($filcust);

	for($i = 0; $i < $len; $i++) {
		$custl[$i] = explode(',', $filcust[$i]);
		$custl[$i][8] = (int)$custl[$i][8];
		$custl[$i][10] = (float)$custl[$i][10];
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
	for($year = 2008; $year <= $dyear; $year++) {
		$yrfil = $siteRoot.$csv.$year.'.csv';
		if(file_exists($yrfil)) {
			$raw = file($yrfil);
			$cntRaw = count($raw);
			for($i = 1; $i < $cntRaw; $i++) {
				$day = date('j', strtotime('Jan 1st '. (string)$year . ' + ' . (string)($i-1) . ' days'));
				$month = date('n', strtotime('Jan 1st '. (string)$year . ' + ' . (string)($i-1) . ' days'));
				$rawa = explode(',', $raw[$i]);
				$cntRawa = count($rawa);
				for($j = 0; $j < $cntRawa; $j++) {
					$data[$j][$year][$month][$day] = $rawa[$j];
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
					' \n'
				);

				for($j = 0; $j < $cntRawa; $j++) {
					$data[$j][$year][$dmonth][$dday] = ($csv == 'dat') ? $list[$j] : $listt[$j];
				}
			}
		}
	}
	file_put_contents( ROOT.'serialised_'.$csv.'.txt', serialize($data) );
}

function serialiseCSVm() {
	$DATA = unserialize(file_get_contents(ROOT . 'serialised_dat.txt'));
	for($year = 2009; $year <= date('Y'); $year++) {
		$yrfil = ROOT.'datm'.$year.'.csv';
		if(file_exists($yrfil)) {
			$raw = file($yrfil);
			$cnt1 = count($raw);
			for($i = 1; $i < $cnt1; $i++) {
				$rawa = explode(',', $raw[$i]);
				for($j = 0; $j < 8; $j++) { //up-to and including fog
					$day = date('j', strtotime('Jan 1st '. (string)$year . ' + ' . (string)($i-1) . ' days'));
					$month = date('n', strtotime('Jan 1st '. (string)$year . ' + ' . (string)($i-1) . ' days'));
					if($j >= 3 && $rawa[$j] == '') {
						$rawa[$j] = '0';
					}
					if($j === 3) { //falling snow
						$rawa[$j] = ($rawa[$j] == 'y') ? $DATA[13][$year][$month][$day] + 0.01 : $rawa[$j];
					}
					$data[$j][$year][$month][$day] = $rawa[$j];
				}
			}
		}
	}
	file_put_contents( ROOT.'serialised_datm.txt', serialize($data) );
}

function getSunHrs() {
	$fileSun = urlToArray("http://www.weatheronline.co.uk/weather/maps/current?CONT=ukuk&TYP=sonne&ART=tabelle", 7);
	if(!$fileSun) return "0";
	$len = count($fileSun);
	$sunHrs = 0;
	for($i = 300; $i < $len; $i++) {
		if(strpos($fileSun[$i], 'Heathrow')) {
			$sunHrs = (int) strip_tags($fileSun[$i+1]);
			break;
		}
	}
	quick_log("sunHrs.txt", $i ." / ". $len);
	return "$sunHrs";
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
?>