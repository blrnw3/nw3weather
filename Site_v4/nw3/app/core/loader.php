<?php
namespace nw3\app\core;

use nw3\app\util\Date;
use nw3\app\util\Http;
use nw3\app\util\String;
use nw3\app\core\Session;
use nw3\app\core\Units;
use nw3\app\controller\Nomodel;
use nw3\config\Admin;

class Loader {

	private $url_parts = null;

	function load() {

		Date::initialise();
		Session::initialise();
		Units::initialise();

		define('ASSET_PATH', \Config::HTML_ROOT .'static/');

		//Clear the query string to get the clean URL
		$url = strtok($_SERVER['REQUEST_URI'], '?');

		//Segment the URL
		$this->url_parts = explode('/', $url);

		//Get the base path
		$base_index = \Config::$install['directory_nesting_level'] + 1;
		$sub_paths = array_slice($this->url_parts, $base_index+1);
		$base_controller = $this->url_parts[$base_index];

		if(String::isBlank($base_controller)) {
			$base_controller = Admin::HOME_ROOT;
		}

		//Attempt to load the controller for the given base path
		$controller_class = 'nw3\app\controller\\'. $base_controller;
		try {
			class_exists($controller_class);
		} catch (\LogicException $e) {
			//Check for existence of static path($base_controller, $sub_paths[0], $base_index+1);
			if(Nomodel::path_exists($base_controller)) {
				$static_controller = new Nomodel($base_controller);
				$this->clean_url($url, $this->get_good_url($base_index));
				$static_controller->load();
				Session::increment($base_controller);
				die();
			}
			$this->no_controller($base_controller);
		}
		if($base_controller === 'nomodel') {
			$this->no_controller($base_controller);
		}
		$controller = new \ReflectionClass($controller_class);
		if ($controller->isAbstract()) {
			$this->no_controller('Abstract');
		}
		Session::increment($base_controller);
		//Instantiate the valid concrete controller
		$concrete_controller = $controller->newInstance($sub_paths);

		//Attempt to load method for given sub_path
		$sub_path1 = $sub_paths[0];
		if($sub_path1 === null) {
			//No trailing slash on controller; redirect 301
		} elseif(String::isBlank($sub_path1)) {
			$this->clean_url($url, $this->get_good_url($base_index));
			$concrete_controller->index();
		} elseif(in_array($sub_path1, get_class_methods($controller_class)) //only returns public methods
				&& !in_array($sub_path1, $concrete_controller->invalid_urls)
				&& !String::starts_with($sub_path1, '__')) {
			$this->clean_url($url, $this->get_good_url($base_index, 2, false));
			$concrete_controller->{$sub_path1}();
		} elseif($concrete_controller->validate_arg($sub_path1)) {
			$this->clean_url($url, $this->get_good_url($base_index, 2, false));
			$concrete_controller->subpath($sub_path1);
		} else {
			$this->no_method($sub_path1, $concrete_controller->controller_name);
		}
	}

	private function get_good_url($base_index, $nesting = 1, $trailing_slash = true) {
		$trail = $trailing_slash ? '/' : '';
		return implode('/', array_slice($this->url_parts, 0, $base_index + $nesting)) . $trail;
	}

	private function clean_url($url, $ideal_url) {
		if($url !== $ideal_url) { //probably multiple trailing slashes (at least)
			Http::redirect($ideal_url);
			die();
		}
	}

	private function no_controller($extra) {
		Http::response_code(404);
		echo "No such path '$extra' exists<br />";
		if($this->url_parts !== null) {
			var_dump ($this->url_parts);
		}
		if(Admin::DEBUG) {
			var_dump($_SERVER);
		}
		die();
	}

	private function no_method($method_name, $controller_name) {
		Http::response_code(404);
		echo "No such method or argument '$method_name' exists for this path ($controller_name)<br />Trace:";
		var_dump ($this->url_parts);
		die();
	}
}

?>
