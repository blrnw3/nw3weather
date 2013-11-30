<?php
namespace nw3\app\controller;

use nw3\app\model as m;
use nw3\app\core;

/**
 * Webcam main page
 *
 * @author Ben LR
 */
class Forecast extends core\Controller {

	public function __construct() {
		parent::__construct('forecast', 'Forecast');

		/*
		 * Do some stuff model/controller stuff
		 */

		$this->render();
	}

}

?>
