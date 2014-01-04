<?php
use nw3\app\core\Loader;

date_default_timezone_set('Europe/London');

spl_autoload_extensions(".php");
spl_autoload_register();

$loader = new Loader();
$loader->load();

?>
