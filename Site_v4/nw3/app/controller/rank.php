<?php
namespace nw3\app\controller;

use nw3\app\core;

/**
 * Data Rankings (tables)
 *
 * @author Ben LR
 */
class Rank extends core\Controller {

	public function __construct() {
		parent::__construct(__CLASS__);
	}

//	public function validate_arg($param) {
//		$this->varname = $param;
//		return true;
//	}
//	public function subpath($path) {
//		$this->build('Data Rankings - daily', null, 'index');
//		$this->render();
//	}

	public function index() {
		$this->redirect('rank/daily');
	}

	public function daily() {
		$this->build('Rankings - all time daily');

		$this->month = isset($_GET['month']) ? (int)$_GET['month'] : 0;
		$this->varname = $_GET['vartype'];
		$this->ranknum = isset($_GET['ranknum']) ? (int)$_GET['ranknum'] : null;

		$this->render();
	}

}

?>
