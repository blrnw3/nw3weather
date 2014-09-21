<?php
namespace nw3\app\controller;

use nw3\app\core;

/**
 * Webcam main page
 *
 * @author Ben LR
 */
class Home extends core\Controller {

	public function __construct() {
		parent::__construct(__CLASS__);
	}

	public function index() {
		$this->build('Live Weather');
		$this->render();
	}

}

?>
