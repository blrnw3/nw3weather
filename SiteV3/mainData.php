<?php
$crsizeFinal = filesize(LIVE_DATA_PATH);

//Select appropriate file to use
if($crsizeFinal === 0) {
	$usePath = ROOT.'clientrawBackup.txt';
	$badCRdata = true;
} else {
	$usePath = LIVE_DATA_PATH;
}

$client = file($usePath);
$mainData = explode(" ", $client[0]);

if($badCRdata || $slept) {
	log_events('clientrawBad.txt', $crsizeFinal ."B ". makeBool($slept));
}

$kntsToMph = 1.152;
// Main current weather variables
$temp = $mainData[4];
$humi = $mainData[5];

$pres = $mainData[6];
$rain = $mainData[7];
$wind = $mainData[1] * $kntsToMph;
$gust = $mainData[140] * $kntsToMph; //actually the max 1-min gust
$gustRaw = $mainData[2] * $kntsToMph; //true 14s gust
$w10m = $mainData[158] * $kntsToMph;
$wdir = $mainData[3];

// Time variables
$unix = mktime(intval($mainData[29]), intval($mainData[30]), intval($mainData[31]),
		intval($mainData[36]), intval($mainData[35]), intval($mainData[141]));

$diff = time() - $unix;
$OUTAGE = $diff > 3600;
$ALT_OUTAGE = false;
$alt_ready = false;
if($OUTAGE) {
	$alt_age = time() - filemtime(ROOT.'EXTclientraw.txt');
	$alt_ready = $alt_age < 300;
}

// Other multi-use weather vars
$maxgsthr = $HR24['misc']['maxhrgst'];
$maxgstToday = $NOW['max']['gust'];
$maxavgToday = $maxavgspd;

// No wind data - use Harpenden wind data from their clientraw (cached by cron_main)
if($wind < 10) { // wind issue Nov 2020
	$extClient = file(ROOT.'EXTclientraw.txt');
	$extOffset = 0.99; // 0.91; //1.3 - tott;
	$extData = explode(" ", $extClient[0]);
	$wind = $extData[1] * $kntsToMph * $extOffset;
	$gust = $extData[140] * $kntsToMph * $extOffset; //actually the max 1-min gust
	$gustRaw = $extData[2] * $kntsToMph * $extOffset; //true 14s gust
	$w10m = $extData[158] * $kntsToMph * $extOffset;
	$wdir = $extData[3];
}
if($OUTAGE && $alt_ready) {
	$extClient = file(ROOT.'EXTclientraw.txt');
	$extOffset = 0.99; // 0.91; //1.3 - tott;
	$extData = explode(" ", $extClient[0]);
	$wind = $extData[1] * $kntsToMph * $extOffset;
	$gust = $extData[140] * $kntsToMph * $extOffset; //actually the max 1-min gust
	$gustRaw = $extData[2] * $kntsToMph * $extOffset; //true 14s gust
	$w10m = $extData[158] * $kntsToMph * $extOffset;
	$wdir = $extData[3];

	$temp = $extData[4];
	$humi = $extData[5];

	$pres = $extData[6];
	$rain = $extData[7];

	$unix = mktime(intval($extData[29]), intval($extData[30]), intval($extData[31]),
		intval($extData[36]), intval($extData[35]), intval($extData[141])) - 50;

	$feel = feelsLike($temp, $gust, $dewp);
	$maxavgToday = $NOW['max']['wind'];
}
if(false && $extData[3] === "101") { // CASA rules whilst Harpenden is down ;(
	$extClient = file(ROOT.'EXTclientraw2.txt');
	$extOffset = 0.95; // 0.91; //1.3 - tott;
	$extData = explode(" ", $extClient[0]);
	$wind = $extData[5] * $extOffset;
	$gust = $extData[6] * $extOffset;
	$gustRaw = $extData[6] * $extOffset;
	$w10m = $extData[5] * $extOffset;
	$wdir = $extData[7];

	$maxavgToday = $NOW['max']['wind'];
}
if(false && $temp == 16.9) {
	// Casa
	$extClient2 = file(ROOT.'EXTclientraw2.txt');
	$extData2 = explode(" ", $extClient2[0]);
	$temp = $extData2[2] - 0.7;
	$humi = $extData2[3] + 1;
}
if(false && $temp == 16.9) {
	// StAlbans
	$extClient2 = file(ROOT.'EXTclientraw2.txt');
	$extData2 = explode(" ", $extClient2[0]);
	$temp = $extData2[4] + 0.3;
	$humi = $extData2[5] - 1;
}

if(false && date("Hi") > "0009") {
	// Casa rain
	$extClient2 = file(ROOT.'EXTclientraw2.txt');
	$extData2 = explode(" ", $extClient2[0]);
	$rain = $extData2[7];
}

if(false && $OUTAGE && $alt_ready) {
	$extClient2 = file(LIVE_DATA_PATH_ALT);
	$extData2 = explode(" ", $extClient2[0]);

	$unix = mktime(intval($extData[29]), intval($extData[30]), intval($extData[31]),
		intval($extData[36]), intval($extData[35]), intval($extData[141])) - 50;

	$alt_true_age = time() - $unix;

	if($alt_true_age < 1200) {
		// Bencook temp/humi/rain/pres
		$rain = (date('Ymd') === "20180923") ? 11.0 : $extData2[7];
		$temp = $extData2[4] + 0.0;
		$humi = $extData2[5] + 0;
		$pres = $extData[6] + 0.0;
		if(date("Hi") <= "0009") {
			$rain = 0;
		}
	} else {
		$ALT_OUTAGE = true;
	}
}

// Derived current weather variables
$dewp = dewPoint($temp, $humi);
$feel = feelsLike($temp, $gust, $dewp);

?>
