<?php

namespace nw3\app\core;

use nw3\app\util as u;
use nw3\app\util\Date;
use nw3\app\helper as h;
use nw3\app\core\Units;
use nw3\app\core\Session;
use nw3\app\model\Variable;

abstract class Controller {

	public $controller_name;
	private $path;
	private $page;
	private $view_base;
	private $view;
	private $title;
	private $timer;
	private $vars = array();

	//Custom concrete public methods
	public $invalid_urls = array('subpath', 'validate_args');

	/**
	 * Default page
	 */
	public abstract function index();

	public function subpath() {}

	public function validate_arg($arg) {
		return false;
	}

	/**
	 * Intial set-up. Call before any logic/routing/data processing.
	 */
	public function __construct($class_name, $path = null) {
		$this->controller_name = str_replace('nw3\app\controller\\', '', strtolower($class_name));
		$this->timer = new u\ScriptTimer();
		$this->path = $path;

		Session::initialise();
		Date::initialise();
		Units::initialise();
		Variable::initialise();

		define('ASSET_PATH', \Config::HTML_ROOT .'static/');
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
	 * @param string $view [=null] folder of the view. Defaults to controller name
	 * @param string $subview [=null] filename of the view file to load. Defaults to method called
	 */
	protected function build($title, $_view = null, $_subview = null) {
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
		echo json_encode($data);
	}

	/**
	 * Loads a jpgraph view
	 * @param string $file name of view file
	 */
	protected function jpgraph($file) {
		$this->jpgraph_root = __DIR__ .'/../../lib/jpgraph/';
		require __DIR__ . "/../view/$this->controller_name/$file.php";
	}

	/**
	 * Get the portion of the URL path at the specified index
	 * @param type $index Sub path index (0 is the first <em>sub</em> path portion)
	 */
	protected function sub_path($index = 0) {
		return u\String::isBlank($this->path[$index]) ? false : $this->path[$index];
	}

	/**
	 * Loads a mini view (shared across other views), aka 'partial'
	 * @param type $name file_name of the viewette (excluding leading underscore and extension)
	 */
	protected function viewette($name) {
		require $this->view_base . "_$name.php";
	}


	private function get_name_of_calling_method() {
		$callers = debug_backtrace();
		return $callers[2]['function'];
	}
}
?>
