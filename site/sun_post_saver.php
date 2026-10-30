<?php
/**
 * External sun-hours webhook (the old classifier's nightly POST). Always saves yesterday's sun JSON.
 * With NW3_SUN_AUTO_DATM on (default) the webcam model owns datm sunhr, so the posted value is only recorded in
 * NW3_SUN_COMPARE_CSV next to the model's; with it off, the posted value appends the datm row as before.
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
	$sunhrs = strval(nw3_round(intval($data['sun_mins']) / 60, 1));

	if(file_put_contents($targetFile, nw3_json_encode($data))) {
		echo "Data saved to $targetFile";
	} else {
		http_response_code(500);
		echo "Failed to write data.";
	}
	if(NW3_SUN_AUTO_DATM) {
		SunLog::recordCompare($dtStr, ['old_h' => $sunhrs, 'old_received' => date('c')]);
		return;
	}
	if(!DatmWriter::write($sunhrs)) {
		mail('alerts@nw3weather.co.uk', 'Failed to write sunhrs!', 'Data already written for this day apparently');
	}
}

save_sun();
echo "DONE". date('r');
