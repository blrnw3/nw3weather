<?php

namespace nw3\app\core;

use nw3\app\util as u;
use nw3\app\core\Session;
use nw3\app\helper as h;

class Controller {


	private $controller_name;
	private $page;
	private $view;
	private $title;
	private $timer;
	private $vars = array();

	/**
	 * Intial set-up. Call before any logic/routing/data processing.
	 */
	public function __construct($class_name) {
		$this->controller_name = str_replace('nw3\app\controller\\', '', strtolower($class_name));
		$this->timer = new u\ScriptTimer();
	}
	/**
	 *
	 * @param string $view path to the view file to load
	 * @param string $title html title
	 */
	public function build($view, $title, $subfile = false) {
		$this->page = $view;
		$this->view = __DIR__ . '/../view/' . $view . '.php';
		$this->title = $title;
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

	public function render() {
		$include_analytics = false;
		$show_sneaky_nw3_header = true;

		$nw3_time = D_date .', '. D_time .' '. D_dst;
		$current_year = D_year;

		$this->timer->stop();

		$script_load_time = $this->timer->executionTimeMs();
		$session_page_count = Session::page_count();

		$sidebar = new h\Sidebar($this->controller_name, $this->page !== $this->controller_name);

		require __DIR__ . '/../view/base.php';
	}

}
?>
