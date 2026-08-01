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

	public function __construct($url_args) {
		parent::__construct(__CLASS__, $url_args);
		$this->dark = Date::$is_dark;
		$this->skycam_url = 'http://192.168.1.68/jpgwebcam.jpg';
	}

	public function index() {
		$vid_extension = 'dayvideo.wmv';
		$vid_today = D_datestamp. $vid_extension;
		$vid_yest = date("Ymd", Date::mkdate(D_month,D_day-1,D_year)). $vid_extension;
		if(file_exists($vid_today)) {
			$this->dayvid = $vid_today;
		} elseif(file_exists($vid_yest)) {
			$this->dayvid = $vid_yest;
		}
		$this->build('Webcam');
		$this->render();
	}

	public function skycam() {
		$this->check_correct_subpath_length();
		$this->build('Skycam - Webcam');
		$this->render();
	}

}

?>
