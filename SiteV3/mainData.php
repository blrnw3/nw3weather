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

// Other multi-use weather vars
$maxgsthr = $HR24['misc']['maxhrgst'];
$maxgstToday = $NOW['max']['gust'];
$maxavgToday = $maxavgspd;

// Harpenden data
// July 1st 2024: wind outage
if(false) {
	$extClient = file(ROOT.'EXT_harpenden.txt');
	$extData = explode(" ", $extClient[0]);
	$unix =  mktime(intval($extData[29]), intval($extData[30]), intval($extData[31]),
		intval($extData[36]), intval($extData[35]), intval($extData[141]));
	$harpenden_age = time() - $unix;
	if($harpenden_age < 600) {
		$extOffset = 0.9; // 0.91; //1.3 - tott;
		$wind = $extData[1] * $kntsToMph * $extOffset;
		$gust = $extData[140] * $kntsToMph * $extOffset; //actually the max 1-min gust
		$gustRaw = $extData[2] * $kntsToMph * $extOffset; //true 14s gust
		$w10m = $extData[158] * $kntsToMph * $extOffset;
		$wdir = $extData[3];
//		$pres = $extData[6];
//		$temp = $extData[4] + 1;
//		$humi = $extData[5];
	//	$rain = $extData[7];
		$maxavgToday = $NOW['max']['wind'];
	}
}

// Synoptic data from James park
if($OUTAGE && false) {
	$mod_james = filemtime(ROOT.'EXT_james.json');
	$alt_age = time() - $mod_james;
	if($alt_age < 600) {
		$unix = $mod_james;
		$james_data = json_decode(file_get_contents(ROOT."EXT_james.json"), true);
		$temp = $james_data["STATION"][0]["OBSERVATIONS"]["air_temp_value_1"]["value"] - 0.5;
		$dewp = $james_data["STATION"][0]["OBSERVATIONS"]["dew_point_temperature_value_1"]["value"] - 0.5;
		if($dday == 5) {
			$rain = 0.5;
		}
//		$rain = $james_data["STATION"][0]["OBSERVATIONS"]["precip_accum_12_hour_value_1"]["value"];
		// https://www.omnicalculator.com/physics/relative-humidity
		$humi = intval(100 * exp((17.625 * $dewp) / (243.04 + $dewp)) / exp((17.625 * $temp) / (243.04 + $temp)));
//		$humi = (int)(100 - ($temp - $dewp) * 5);  // TODO better
	}
}

$DOWN = ($temp < -15);

// CWOP Potters
if(false && $OUTAGE) {
	$pot_data = json_decode(file_get_contents(ROOT."EXT_potters.json"), true);
	$pot_unix = intval($pot_data["weather"]["timestamp"] / 1000);
	if((time() - $pot_unix) < 20000) {
//		$unix = $isl_unix;
		$temp = (float)$pot_data["weather"]["wx"]["temp"];
		$humi = $pot_data["weather"]["wx"]["humidity"];
//		$rain = (float)$isl_data["weather"]["wx"]["rain_midnight"];
//		$pres = (float)$isl_data["weather"]["wx"]["pressure"];
	}
}

// CWOP Islington data
if($DOWN  || $OUTAGE) {
	$isl_data = json_decode(file_get_contents(ROOT."EXT_islington.json"), true);
	$isl_unix = intval($isl_data["weather"]["timestamp"] / 1000);
	if((time() - $isl_unix) < 3600) {
		$temp = (float)$isl_data["weather"]["wx"]["temp"];
		$humi = $isl_data["weather"]["wx"]["humidity"];
		if($OUTAGE) {
			$unix = $isl_unix;
			$rain = (float)$isl_data["weather"]["wx"]["rain_midnight"];
			$pres = (float)$isl_data["weather"]["wx"]["pressure"];
		}
	}
}


$feel = feelsLike($temp, $gust, $dewp);
// Derived current weather variables
$dewp = dewPoint($temp, $humi);
?>
