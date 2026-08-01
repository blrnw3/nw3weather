<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
date_default_timezone_set('Europe/London');

// NB: see end of file for other inclusions and init
require("UtilsAndConsts.php");
require("WxDefinition.php");
require("WxFn.php");
require("Spells.php");

Live::init();

$var = "tmin";
if(isset($_GET['var'])) {
    $var = substr($_GET['var'], 0, 9);
}
if (!isset(Wx::$daily[$var])) {
	http_response_code(400);
	die('Unknown daily variable');
}

echo "<pre>";
print_r(DataSummarizer::summarizeCached($var, Site::BASE_YEAR));
// var_dump(Live::$NOW);
echo "</pre>";

?>