<?php
namespace nw3\app\controller;

use nw3\app\core;
use nw3\app\util\Date;

/**
 * Webcam main page
 *
 * @author Ben LR
 */
class Webcam extends core\Controller {

	public function __construct() {
		parent::__construct(__CLASS__);
		$this->build('webcam', 'Webcam');

		$this->dayvid_base = (D_hour > 21) ? D_datestamp : date("Ymd", Date::mkdate(D_month, D_yest_day));
		$this->dark = Date::$is_dark;

		$this->render();
	}

}

?>
