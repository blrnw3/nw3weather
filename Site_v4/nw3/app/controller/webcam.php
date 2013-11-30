<?php
namespace nw3\app\controller;

use nw3\app\model as m;
use nw3\app\core;
use nw3\app\util as u;

/**
 * Webcam main page
 *
 * @author Ben LR
 */
class Webcam extends core\Controller {

	public function __construct() {
		parent::__construct('webcam', 'Webcam');

		/*
		 * Do some stuff model/controller stuff
		 */

		$this->test_passed_var = 'Dude, it worked!';
		$this->dayvid_base = (D_hour > 21) ? date('Ymd') : date("Ymd", u\Date::mkdate(D_month, D_yest_day));

		$this->render();
	}

}

?>
