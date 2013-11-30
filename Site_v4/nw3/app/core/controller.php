<?php

namespace nw3\app\core;

use nw3\app\util as u;
use nw3\app\core\Session;

class Controller {

	private $view;
	private $title;
	private $timer;
	private $vars = array();

	/**
	 *
	 * @param string $view path to the view file to load
	 * @param string $title html title
	 */
	public function __construct($view, $title) {
		$this->view = __DIR__ . '/../view/' . $view . '.php';
		$this->title = $title;

		$this->timer = new u\ScriptTimer();
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

		$script_load_time = $this->timer->executionTime();
		$session_page_count = Session::page_count();

		require __DIR__ . '/../view/base.php';
	}

}

?>
