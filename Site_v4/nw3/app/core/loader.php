<?php
namespace nw3\app\core;

use nw3\app\util\Date;
use nw3\app\util\Http;
use nw3\app\core\Session;
use nw3\app\core\Units;

class Loader {

	private $url_parts = null;

	function load() {

		Date::initialise();
		Session::initialise();
		Units::initialise();

		$url = $_SERVER['REQUEST_URI'];

		$empty_controller = 'test';

		$this->url_parts = explode('/', $url);

		$base_controller = $this->url_parts[\Config::$install['directory_nesting_level'] + 1];

		if($base_controller === '') {
			$base_controller = $empty_controller;
		}

		$controller_class = 'nw3\app\controller\\'. $base_controller;

		try {
			class_exists($controller_class);
		} catch (\LogicException $e) {
			$this->no_controller('Bad class name '. $controller_class);
		}
		$reflection = new \ReflectionClass($controller_class);
		if ($reflection->isAbstract()) {
			$this->no_controller('Not a concrete class');
		}
		Session::increment($base_controller);
		$reflection->newInstance();

	}

	private function no_controller($extra) {
		Http::response_code(404);
		echo "No controller mapped: $extra <br />";
		if($this->url_parts !== null)
			var_dump ($this->url_parts);
		die();
	}
}

?>
