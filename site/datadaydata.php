<?php
/**
 * JSON endpoint for the daily data matrix (wxdataday AJAX updates).
 * Params: vartype, year, agg (same as wxdataday.php).
 */
require __DIR__ . '/Page.php';
require __DIR__ . '/Report.php';
require __DIR__ . '/dataday-body.php';

Page::init([
	'fileNum' => 40,
	'title' => 'Daily Data Tables',
	'description' => 'Daily data table fragment',
]);
Page::requireNw3Ajax();

$report = new Report(['default' => 'rain', 'badCats' => ['cloud']]);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$payload = nw3_dataday_payload($report);
$json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($json === false) {
	http_response_code(500);
	echo '{"error":"encode failed"}';
	exit;
}
echo $json;
