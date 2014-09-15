<?php
namespace nw3\app\controller;

use nw3\app\core;

/**
 * Data Summary (main rain, temp, sun etc. stats)
 *
 * @author Ben LR
 */
class Datasummary extends core\Controller {

	public function __construct($path) {
		parent::__construct(__CLASS__, $path);
	}

	public function index() {
		$this->build('Recent and Record Extremes, Trends and Averages');
		$this->render();
	}

}

?>
