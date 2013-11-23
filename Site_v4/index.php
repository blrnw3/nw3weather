<?php
spl_autoload_extensions(".php");
spl_autoload_register();

$url = $_SERVER['REQUEST_URI'];
$qs = $_SERVER['QUERY_STRING'];


$url_parts = explode('/', $url);
print_r ($url_parts);

$controller_class = 'nw3\app\controller\\'. $url_parts[2];

try {
	class_exists($controller_class);
} catch (LogicException $e) {
	no_controller('bad class name '. $controller_class);
}
$reflection = new ReflectionClass($controller_class);
if ($reflection->isAbstract()) {
	no_controller('not a concrete class');
}
$reflection->newInstance();

function no_controller($extra) {
	echo 'No controller mapped: '. $extra;
	die();
}

?>
