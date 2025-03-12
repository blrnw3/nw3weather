<?php
require('unit-select.php');
require('functions.php');

header('Content-Type: application/json');

unset($NOW["trend"]);
unset($NOW["trendRn"]);
unset($NOW["changeHr"]);
unset($NOW["changeDay"]);
unset($NOW["misc"]["rnlast"]);
unset($NOW["misc"]["prevRn"]);
unset($NOW["misc"]["prevRnOld"]);

$ret = [
	"time" => [
		"server" => time(),
		"last_measurement" => $unix,
		"age" => $diff,
		"outage" => $OUTAGE,
	],
	"current" => [
		"temperature" => (float)$temp,
		"humidity" => (int)$humi,
		"dew_point" => (float)$dewp,
		"pressure" => (float)$pres,
		"feels_like" => (float)$feel,
		"wind_speed" => (float)$wind,
		"wind_gust" => (float)$gustRaw,
		"wind_10min" => (float)$w10m,
		"wind_gust_1min" => (float)$gust,
		"wind_degree" => (float)$wdir,
		"wind_direction" => degname($wdir),
		"rain_today" => (float)$rain,
	],
	"today" => $NOW,
	"24hr" => $HR24
];

echo json_encode($ret);

?>
