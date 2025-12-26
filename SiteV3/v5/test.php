<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
date_default_timezone_set('Europe/London');

// NB: see end of file for other inclusions and init
require("UtilsAndConsts.php");
require("WxDefinition.php");
require("WxFn.php");

Live::init();

$var = "tmin";
if(isset($_GET['var'])) {
    $var = substr($_GET['var'], 0, 9);
}

echo "<pre>";
$s = new DataSummarizer($var);
print_r($s->summarize());
// var_dump(Live::$NOW);
echo "</pre>";

?>