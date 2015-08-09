<?php

if($mainDataCritical) {
	$crsize1 = filesize(LIVE_DATA_PATH);
	$crmoddiff1 = time() - filemtime(LIVE_DATA_PATH);
	if($crmoddiff1 <= 1 && $crsize1 == 0) { // stalled/mid upload
		sleep(1); //should fix things; not too critical anyway
		$scriptbeg -= 1.0;
		clearstatcache(); //has resolved issue?
		$slept = true;
	}
}
$crsizeFinal = filesize(LIVE_DATA_PATH);

//Select appropriate file to use
if($crsizeFinal === 0) {
	$usePath = ROOT.'clientrawBackup.txt';
	$badCRdata = true;
} else {
	$usePath = LIVE_DATA_PATH;
}

######## TO BE COMPLETED  ###########
//pseudeo code for when I get this sorted
//if($oldData) {
//	$usePath = 'ucl data';
//}
######################################

$client = file($usePath);
$mainData = explode(" ", $client[0]);

if($badCRdata || $slept) {
	log_events('clientrawBad.txt', $crsizeFinal ."B ". makeBool($slept));
}


$kntsToMph = 1.152;
// Main current weather variables
$temp = $mainData[4];
$humi = $mainData[5];

if(false && $temp == 15.4) {
	$ext_dat_file = urlToArray("http://weather.stevenjamesgray.com/realtime.txt");
	$ext_dat = $ext_dat_file[0];
	$dat_fields = explode(" ", $ext_dat);
	$temp = $dat_fields[2];
	$humi = $dat_fields[3];
}
$pres = $mainData[6];
$rain = $mainData[7];
$wind = $mainData[1] * $kntsToMph;
$gust = $mainData[140] * $kntsToMph; //actually the max 1-min gust
$gustRaw = $mainData[2] * $kntsToMph; //true 14s gust
$w10m = $mainData[158] * $kntsToMph;
$wdir = $mainData[3];

// Time variables
$unix = filemtime(LIVE_DATA_PATH);

// Derived current weather variables
$dewp = dewPoint($temp, $humi);
$feel = feelsLike($temp, $gust, $dewp);

// Other multi-use weather vars
$maxgsthr = $HR24['misc']['maxhrgst'];
$maxgstToday = $NOW['max']['gust'];
$maxavgToday = $maxavgspd;
?>