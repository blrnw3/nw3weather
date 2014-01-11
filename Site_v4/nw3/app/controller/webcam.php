<?php
namespace nw3\app\controller;

use nw3\app\core;
use nw3\app\util\Date;

/**
 * Webcam
 *
 * @author Ben LR
 */
class Webcam extends core\Controller {

	public function __construct() {
		parent::__construct(__CLASS__);
		$this->dark = Date::$is_dark;
		$this->skycam_url = 'http://192.168.1.66/jpgwebcam.jpg';
	}

	public function index() {
		$this->dayvid_base = (D_hour > 21) ? D_datestamp : date("Ymd", Date::mkdate(D_month, D_yest_day));
		$this->build('Webcam');
		$this->render();
	}

	public function skycam() {
		$this->build('Skycam - Webcam');
		$this->render();
	}

}

?>
