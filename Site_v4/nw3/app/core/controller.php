<?php

namespace nw3\app\core;

use nw3\app\util as u;
use nw3\app\util\Date;
use nw3\app\util\Maths;
use nw3\app\util\String;
use nw3\app\helper as h;
use nw3\app\core\Units;
use nw3\app\core\Session;
use nw3\app\core\Logger;
use nw3\config\Admin;
use nw3\app\model\Variable;
use nw3\app\core\Db;

abstract class Controller {

	public $controller_name;
	public $services;
	protected $timer;
	private $page;
	private $view_base;
	private $view;
	private $title;
	private $vars = [];
	private $errors = [];

	protected $qs = null;
	protected $url_args = null;
	protected $base_url_parts = null;

	//Custom concrete public methods
	public $invalid_urls = ['subpath', 'validate_args'];

	/**
	 * Default page
	 */
	public abstract function index();

	public function subpath($param) {}

	public function validate_arg($path) {
		return $this->invalid_subpath($path,
			'Warning! No arg validation implemented by subclass');
	}
	protected function invalid_subpath($path, $err) {
		echo "Bad path $path.<br /> $err <br />";
		return false;
	}

	/**
	 * Intial set-up. Call before any logic/routing/data processing.
	 */
	public function __construct($class_name, $url_args=null) {
		$this->controller_name = str_replace('nw3\app\controller\\', '', strtolower($class_name));
		$this->timer = new u\ScriptTimer();
		if($url_args) {
			$this->qs = $url_args['qs'];
			$this->url_args = $url_args['args'];
			$this->base_url_parts = $url_args['base'];
		}

//		$this->services = new Servicecontainer();
//		$this->services->get('logger')->test();

		Session::initialise();
		Date::initialise();
		Units::initialise();
		Variable::initialise();

		define('HTML_ROOT', \Config::HTML_ROOT);
		define('ASSET_PATH', \Config::HTML_ROOT .'static/');

//		set_error_handler([$this, 'SecretErrorHandler']);
	}

	public function __set($key, $val) {
		$this->vars[$key] = $val;
	}

	public function __get($var) {
		if (array_key_exists($var, $this->vars)) {
			return $this->vars[$var];
		}

		$trace = debug_backtrace();
		trigger_error(
			'Undefined property via __get(): ' . $var .
			' in ' . $trace[0]['file'] .
			' on line ' . $trace[0]['line'], E_USER_NOTICE);
		return null;
	}

	/**
	 *
	 * @param string $title html title
	 * @param string $_view [=null] folder of the view. Defaults to controller name
	 * @param string $_subview [=null] filename of the view file to load. Defaults to method called
	 */
	protected function build($title=null, $_view = null, $_subview = null) {
		$view = ($_view === null) ? $this->controller_name : $_view;
		$subview = ($_subview === null) ? $this->get_name_of_calling_method() : $_subview;
		$this->page = $subview;
		$this->view_base = __DIR__ ."/../view/$view/";
		$this->view = $this->view_base . "$subview.php";
		$this->title = $title;
	}

	/**
	 * Outputs a full HTML response based on the main template
	 */
	protected function render() {
		$include_analytics = false;
		$show_sneaky_nw3_header = true;
		$sidebar = new h\Sidebar($this->controller_name, $this->page !== 'index');

		require __DIR__ . '/../view/base.php';
		$this->flush_logs(true);
	}

	/**
	 * Outputs raw output from a view
	 */
	protected function raw($file) {
		u\Http::text();
		require __DIR__ . "/../view/$this->controller_name/$file.php";
	}

	/**
	 * Outputs a json response
	 * @param array $data data to encode and sent as JSON
	 */
	protected function json($data) {
		u\Http::json();
		$ret = [
			'response' => $data,
			'exec_stats' => $this->get_stats(),
			'err' => $this->errors
		];
		echo json_encode($ret);
		$this->flush_logs();
	}

	protected function redirect($path) {
		$url = HTML_ROOT . $path;
		u\Http::redirect($url);
	}

	/**
	 * Loads a jpgraph view
	 * @param string $file name of view file
	 */
	protected function jpgraph() {
		$this->jpgraph_root = __DIR__ .'/../../lib/jpgraph/';
		require $this->jpgraph_root.'jpgraph.php';
		require $this->jpgraph_root.'jpgraph_bar.php';
		require $this->view;
		$this->flush_logs();
	}

	/**
	 * Get the portion of the URL path at the specified index
	 * @param type $index Sub path index (0 is the first <em>sub</em> path portion)
	 */
	protected function sub_path($index=0, $validate=true) {
		if($validate) {
			$this->check_correct_subpath_length($index + 1);
		}
		return u\String::isBlank($this->url_args[$index]) ? false : $this->url_args[$index];
	}

	/**
	 * Loads a mini view (shared across other views), aka 'partial'
	 * @param type $name file_name of the viewette (excluding leading underscore and extension)
	 */
	protected function viewette($_name, $_this=null) {
		foreach($_this as $_k => $_val) {
			${$_k} = $_val;
		}
		if(String::contains($_name, '/')) {
			require  __DIR__ ."/../view/$_name.php";
		} else {
			require "{$this->view_base}_$_name.php";
		}
	}

	protected function check_correct_subpath_length($subpaths_allowed=1) {
		$args_num = count($this->url_args);
		if($args_num <= $subpaths_allowed) {
			return;
		}
		$good_url = implode('/', array_merge($this->base_url_parts, array_slice($this->url_args, 0, $subpaths_allowed)))
			.'?'. $this->qs;
		u\Http::response_code(404);
		echo "Too many url arguments specified (expected $subpaths_allowed, got $args_num)<br />"
			. "Perhaps you meant <a href='$good_url'>$good_url</a>?"
			. "<br />Provided subpaths:";
		var_dump ($this->url_args);
		if(Admin::DEBUG) {
			var_dump($_SERVER);
		}
		die();
	}

	protected function get_stats() {
		$this->timer->stop();
		$exec_time = $this->timer->executionTimeMs();
		if(Db::is_set()) {
			$db = Db::g();
			$query_time = round($db->query_time * 1000);
			$query_count = $db->query_count;
			$avg = ($query_count > 0) ? Maths::round($query_time / $query_count) : 0;
//			return "$db->query_count executed in $query_time ms ($frac%)";
		} else {
			$query_time = $query_count = $avg = 0;
		}
		$frac = round($query_time / $exec_time * 100);
		$db_stats = [
			'count' => $query_count,
			'time' => $query_time,
			'prop' => $frac,
			'avg' => $avg
		];
		$mem_usage = Maths::round(memory_get_usage() / 1024 / 1024, 1);
		$mem_peak = Maths::round(memory_get_peak_usage() / 1024 / 1024, 1);
		return [
			'db' => $db_stats,
			'mem' => [
				'current' => $mem_usage,
				'peak' => $mem_peak
			],
			'cpu' => [
				'time' => $exec_time
			]
		];
	}

	private function flush_logs($is_http=true) {
		Logger::g()->flush($is_http);
	}

	private function get_name_of_calling_method() {
		$callers = debug_backtrace();
		return $callers[2]['function'];
	}

	public function SecretErrorHandler($errno, $errstr, $errfile, $errline){
		switch ($errno) {
			case E_USER_ERROR:
				$this->errors[] = "ERROR: Fatal error on line $errline in file $errfile [$errno] $errstr";
				exit(1);
				break;

			case E_USER_WARNING:
				$this->errors['warn'] = "WARNING: [$errno] $errstr";
				break;

			case E_USER_NOTICE:
				break;

			default:
				$this->errors['unknown'] = "Unknown error type: [$errno] $errstr";
				break;
		}

		/* Don't execute PHP internal error handler */
		return false;
	}

}
?>
