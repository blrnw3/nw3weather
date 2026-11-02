<?php
/**
 * JSON for the daily profile page (wxdayprofile.php): reads the precomputed DayProfile cache only.
 *   var   temp | dewp | humi | pres | wind | gust | rain | wet | sun
 *   year  e.g. 2025 (optional), month 1-12 (optional)
 */
require __DIR__ . '/Page.php';
require __DIR__ . '/DayProfile.php';
ini_set('display_errors', '0');

$unitPref = isset($_GET['unit']) ? $_GET['unit'] : (isset($_COOKIE['SetUnits']) ? $_COOKIE['SetUnits'] : 'UK');
Page::$units = ($unitPref === 'US') ? UNIT_US : (($unitPref === 'EU') ? UNIT_EU : UNIT_UK);

header('Content-Type: application/json; charset=utf-8');
$var = isset($_GET['var']) ? (string)$_GET['var'] : '';
if (!isset(DayProfile::$VARS[$var])) {
	http_response_code(400);
	echo nw3_json_encode(['error' => 'unknown var']);
	exit;
}
$year = isset($_GET['year']) ? (int)$_GET['year'] : 0;
$month = isset($_GET['month']) ? (int)$_GET['month'] : 0;
if (($year && ($year < DayProfile::FIRST_YEAR || $year > (int)date('Y'))) || $month < 0 || $month > 12) {
	http_response_code(400);
	echo nw3_json_encode(['error' => 'bad period']);
	exit;
}
header('Cache-Control: private, max-age=900');
echo nw3_json_encode(DayProfile::payload($var, $year, $month));
