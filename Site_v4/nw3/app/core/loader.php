<?php
namespace nw3\app\core;

use nw3\app\util\Http;
use nw3\app\util\String;
use nw3\app\core\Session;
use nw3\app\controller\Nomodel;
use nw3\config\Admin;

class Loader {

	private $base_index;
	private $url_parts = null;

	function __construct() {
		$this->base_index = \Config::$install['directory_nesting_level'] + 1;
	}

	function load() {
		//Clear the query string to get the clean URL
		$url = strtok($_SERVER['REQUEST_URI'], '?');

		$this->redirect_inner_multi_slashes($url);

		//Segment the URL
		$this->url_parts = explode('/', $url);
		$url_args = $this->url_args();

		//Get the base path
		$sub_paths = $url_args['args'];
		$base_controller = $this->url_parts[$this->base_index];

		if(String::isBlank($base_controller)) {
			$base_controller = Admin::HOME_ROOT;
		}

		//Attempt to load the controller for the given base path
		$controller_class = 'nw3\app\controller\\'. $base_controller;
		try {
			class_exists($controller_class);
		} catch (\LogicException $e) {
			//Check for existence of static path($base_controller, $sub_paths[0], $this->base_index+1);
			if(Nomodel::path_exists($base_controller)) {
				$static_controller = new Nomodel($base_controller);
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
		$concrete_controller = $controller->newInstance($url_args);

		//Attempt to load method for given sub_path
		$sub_path1 = $sub_paths[0];
		if(String::isBlank($sub_path1)) {
			$concrete_controller->index();
		} elseif(in_array($sub_path1, get_class_methods($controller_class)) //only returns public methods
				&& !in_array($sub_path1, $concrete_controller->invalid_urls)
				&& !String::starts_with($sub_path1, '__')) {
			// Named subpath with corresponding controller method
			$concrete_controller->{$sub_path1}();
		} elseif($concrete_controller->validate_arg($sub_path1)) {
			//Special subpaths (e.g. numeric album nums)
			$concrete_controller->subpath($sub_path1);
		} else {
			$this->no_method($sub_path1, $concrete_controller->controller_name);
		}
	}

	private function no_controller($extra) {
		$this->do_404("No such path '$extra' exists");
	}
	private function no_method($method_name, $controller_name) {
		$this->do_404(
			"No such method or argument '$method_name' exists for this path ($controller_name)"
		);
	}
	private function do_404($msg) {
		Http::response_code(404);
		echo $msg . '<br />Trace:';
		if($this->url_parts !== null) {
			var_dump ($this->url_parts);
		}
		if(Admin::DEBUG) {
			var_dump($_SERVER);
		}
		die();
	}

	private function url_args() {
		$split_point = $this->base_index + 1;
		return array(
			'base' => array_slice($this->url_parts, 0, $split_point),
			'args' => array_slice($this->url_parts, $split_point),
			'qs' => $_SERVER['QUERY_STRING']
		);
	}

	private function redirect_inner_multi_slashes($url) {
		$ideal = preg_replace('%//+%', '/', $url);
		if($ideal !== $url) {
			Http::redirect($ideal);
		}
	}
}

?>
