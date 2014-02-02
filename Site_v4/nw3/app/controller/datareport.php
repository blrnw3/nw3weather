<?php
namespace nw3\app\controller;

use nw3\app\core;
use nw3\app\model;

/**
 * Data Reports (tables)
 *
 * @author Ben LR
 */
class Datareport extends core\Controller {

	public function __construct() {
		parent::__construct(__CLASS__);
	}

	public function index() {
		$this->build('Data reports - daily');

		$year = isset($_GET['year']) ? (int)$_GET['year'] : D_year;
		$var_name = $_GET['vartype'];
		$rolling12 = $year === 0;

		$report = new model\Datareport($var_name, $year, $rolling12);
		$this->report = $report;

		$this->render();
	}

}

?>
