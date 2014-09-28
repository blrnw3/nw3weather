<?php
namespace nw3\app\controller;

use nw3\app\core;

/**
 * Graph-based pages
 *
 * @author Ben LR
 */
class Graphs extends core\Controller {

	public function __construct() {
		parent::__construct(__CLASS__);
	}

	public function index() {
		$this->build('Latest Graphs');
		$this->render();
	}

	public function charts() {
		$this->build('Chart Viewer');
		$this->render();
	}

}

?>
