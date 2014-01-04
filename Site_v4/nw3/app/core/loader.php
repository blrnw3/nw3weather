<?php
namespace nw3\app\core;

use nw3\app\util\Date;
use nw3\app\util\Http;
use nw3\app\core\Session;
use nw3\app\core\Units;
use nw3\app\controller\Nomodel;

class Loader {

	private $url_parts = null;

	function load() {

		Date::initialise();
		Session::initialise();
		Units::initialise();

		define('ASSET_PATH', \Config::HTML_ROOT .'static/');

		$url = strtok($_SERVER['REQUEST_URI'], '?');

		$empty_controller = 'test';

		$this->url_parts = explode('/', $url);

		$base_index = \Config::$install['directory_nesting_level'] + 1;

		$base_controller = $this->url_parts[$base_index];

		if($base_controller === '') {
			$base_controller = $empty_controller;
		}

		$controller_class = 'nw3\app\controller\\'. $base_controller;

		try {
			class_exists($controller_class);
		} catch (\LogicException $e) {
			$this->check_static_path($base_controller);
			$this->no_controller('Bad class name '. $controller_class);
		}
		$controller = new \ReflectionClass($controller_class);
		if ($controller->isAbstract()) {
			$this->no_controller('Not a concrete class');
		}
		Session::increment($base_controller);
		$controller->newInstance(array_slice($this->url_parts, $base_index+1));

	}

	private function check_static_path($path) {
		if(Nomodel::path_exists($path)) {
			new Nomodel($path);
			Session::increment($path);
			die();
		}
	}

	private function no_controller($extra) {
		Http::response_code(404);
		echo "No controller mapped: $extra <br />";
		if($this->url_parts !== null)
			var_dump ($this->url_parts);
		var_dump($_SERVER);
		die();
	}
}

?>
