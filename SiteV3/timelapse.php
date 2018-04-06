<?php
$root = '/var/www/html/';
$t_start = microtime(get_as_float);

include_once($root.'basics.php');
include_once($fullpath.'functions.php');

echo "<pre>START: ". date('r'). "\n";

$year = intval($_GET["y"]);
$month = isset($_GET["m"]) ? intval($_GET["m"]) : 0;
$day = isset($_GET["d"]) ? intval($_GET["d"]) : 0;
$freq = isset($_GET["f"]) ? intval($_GET["f"]) : 180;
$twisetT = isset($_GET["t"]) ? ($_GET["t"]) : 0;  // Twilight offset mins. Null means use night images too
$camT = ($_GET["c"]);
$rate = isset($_GET["r"]) ? intval($_GET["r"]) : 24;
$qual = isset($_GET["q"]) ? intval($_GET["q"]) : 25;

$twiset = ($twisetT === "n") ? null : intval($twisetT);
$cam = ($camT === "gnd") ? "gnd" : "sky";

if($year > intval($dyear) or $year < 2009 or $month < 0 or $month > 12 or $rate == 0 or $qual < 20 or $freq == 0) {
	die("BAD PARAMS $year $month $rate $qual $freq");
}

$filf = extract_for_timelapse($year, $month, $day, $freq, $twiset, $cam, $rate, $qual);

$fil = substr($filf, 13);
echo "</pre>";
echo "<a href='$fil'>$fil</a>"
?>
