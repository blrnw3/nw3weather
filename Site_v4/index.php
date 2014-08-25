<?php
use nw3\app\core\Loader;

// Removes DST-based headaches
date_default_timezone_set('UTC');
ini_set("memory_limit","1024M");

//For completeness and brevity
/** * Smallest integer */
const INT_MIN = -92233720000;
/** * Largest integer */
const INT_MAX = 92233720000;

spl_autoload_extensions(".php");
spl_autoload_register();

$loader = new Loader();
$loader->load();

?>
