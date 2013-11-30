<?php
use nw3\app\core;

### ini settings ###
date_default_timezone_set('Europe/London');

### autoload ###
spl_autoload_extensions(".php");
spl_autoload_register();

$loader = new core\Loader();
$loader->load();

?>
