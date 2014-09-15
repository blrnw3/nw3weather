<?php
use nw3\app\core\Loader;

// Removes DST-based headaches
date_default_timezone_set('UTC');
ini_set('memory_limit', '128M');

//For completeness and brevity
/** * Smallest integer */
const INT_MIN = -92233720000;
/** * Largest integer */
const INT_MAX = 92233720000;

//For simplicty
const MIN =  1;
const MEAN = 2;
const MAX =  3;
const COUNT = 4;
const DAYS = 4;
const SPELL = 5;
const SPELL_INV = 6;
const MINMAX = 13;

spl_autoload_extensions('.php');
spl_autoload_register();

$loader = new Loader();
$loader->load();

?>
