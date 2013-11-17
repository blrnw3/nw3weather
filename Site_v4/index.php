<?php
spl_autoload_extensions(".php");
spl_autoload_register();

use nw3\app\controller as c;

$class = 'Test';

$lol = new c\Test();

?>
