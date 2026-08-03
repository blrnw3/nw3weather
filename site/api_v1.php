<?php
/**
 * Lightweight JSON live-data API. Uses the v5 Live stack (no legacy includes).
 */
$GLOBALS['NW3_ALLOW_WEB_BOOTSTRAP'] = true;
require_once('/var/www/html/cron/bootstrap.php');
Live::init();

header('Content-Type: application/json');

$NOW = Live::$NOW;
$HR24 = Live::$HR24;
unset($NOW['trend']);
unset($NOW['trendRn']);
unset($NOW['changeHr']);
unset($NOW['changeDay']);
unset($NOW['misc']['rnlast']);
unset($NOW['misc']['prevRn']);
unset($NOW['misc']['prevRnOld']);

$ret = array(
	'time' => array(
		'server' => time(),
		'last_measurement' => Live::$unix,
		'age' => Live::$diff,
		'outage' => Live::$outage,
	),
	'current' => array(
		'temperature' => (float)Live::$temp,
		'humidity' => (int)Live::$humi,
		'dew_point' => (float)Live::$dewp,
		'pressure' => (float)Live::$pres,
		'feels_like' => (float)Live::$feel,
		'wind_speed' => (float)Live::$wind,
		'wind_gust' => (float)Live::$gustRaw,
		'wind_10min' => (float)Live::$w10m,
		'wind_gust_1min' => (float)Live::$gust,
		'wind_degree' => (float)Live::$wdir,
		'wind_direction' => Wx::degname(Live::$wdir),
		'rain_today' => (float)Live::$rain,
	),
	'today' => $NOW,
	'24hr' => $HR24,
);

echo json_encode($ret);
