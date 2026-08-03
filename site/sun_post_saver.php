<?php
/**
 * External sun-hours webhook. Writes yesterday's sun JSON and appends datm.
 */
$GLOBALS['NW3_ALLOW_WEB_BOOTSTRAP'] = true;
require_once('/var/www/html/cron/bootstrap.php');
require_once(ROOT . 'cron/DatmWriter.php');
Cron::bindDateGlobals();

echo "START: ". date('r'). "<br />";

function save_sun() {
	$rawInput = file_get_contents('php://input');
	$data = json_decode($rawInput, true);

	if($data === null || !isset($data['sun_mins'])) {
		http_response_code(400);
		echo "Invalid input: missing or malformed JSON or 'sun_mins' field.";
		exit;
	}

	$dtStr = date('Ymd', Date::$dtstamp_yest);
	if($dtStr !== $data['dir']) {
		http_response_code(400);
		echo "Unexpected dir/dtstr. Expected $dtStr";
		exit;
	}

	$base_dir = ROOT . 'sun/';
	$targetFile = $base_dir . $dtStr . '.json';
	$sunhrs = strval(round(intval($data['sun_mins']) / 60, 1));

	if(file_put_contents($targetFile, json_encode($data))) {
		echo "Data saved to $targetFile";
	} else {
		http_response_code(500);
		echo "Failed to write data.";
	}
	if(!DatmWriter::write($sunhrs)) {
		mail('alerts@nw3weather.co.uk', 'Failed to write sunhrs!', 'Data already written for this day apparently');
	}
}

save_sun();
echo "DONE". date('r');
