<?php
$root = '/var/www/html/';
include($root.'basics.php');
include($fullpath.'functions.php');

echo "START: ". date('r'). "<br />";

function save_sun() {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if ($data === null || !isset($data['sun_mins'])) {
        http_response_code(400);
        echo "Invalid input: missing or malformed JSON or 'sun_mins' field.";
        exit;
    }

    $dtStr = date('Ymd', $GLOBALS["dtstamp_yest"]);
    if($dtStr !== $data["dir"]) {
        http_response_code(400);
        echo "Unexpected dir/dtstr. Expected $dtStr";
        exit;
    }

    $base_dir = ROOT . 'sun/';
    $targetFile = $base_dir . $dtStr . ".json";
    $sunhrs = strval(round(intval($data["sun_mins"]) / 60, 1));

    // Save the full JSON data to a file
    if (file_put_contents($targetFile, json_encode($data))) {
        echo "Data saved to $targetFile";
    } else {
        http_response_code(500);
        echo "Failed to write data.";
    }
    // Write datm with sunhrs
    if(!write_datm($sunhrs)) {
        mail("alerts@nw3weather.co.uk", "Failed to write sunhrs!", "Data already written for this day apparently");
    }
}

save_sun();
echo "DONE". date('r');
?>
