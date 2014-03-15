<?php
namespace nw3\app\model;

use nw3\app\model\Variable;
use nw3\app\core\Logger;

/**
 * Holder of call current data for the core 'live' variables
 * Rips them out of clientraw for now, but in future would be good
 *	to get POSTing the data, serialising it, then deserialising here
 *
 * @author Ben LR
 */
class Current {

	public $temp;
	public $humi;
	public $pres;
	public $rain;
	public $wind;
	public $gust;
	public $gust_raw;
	public $w10m;
	public $wdir;

	function __construct() {
		$backup_path = __DIR__.'/../../data/live/wd_latest_stable.txt';
		$live_path = \Config::LIVE_DATA_PATH;

		$size = filesize($live_path);
		$time_diff = D_now - filemtime($live_path);

		if($size === 0) {
			$use_path = $backup_path;
			Logger::g()->queue('live_path_bad', $size.'B');
		} else {
			Logger::g()->queue('lol', $size.'B');
			$use_path = $live_path;
		}

		$datastream = file_get_contents($use_path);
		$data = explode(' ', $datastream);

		$kntsToMph = 1.152;
		// Main current weather variables
		$this->temp = $data[4];
		$this->humi = $data[5];
		$this->pres = $data[6];
		$this->rain = $data[7];
		$this->wind = $data[1] * $kntsToMph;
		$this->gust = $data[140] * $kntsToMph; //actually the max 1-min gust
		$this->gust_raw = $data[2] * $kntsToMph; //true 14s gust
		$this->w10m = $data[158] * $kntsToMph;
		$this->wdir = $data[3];
	}

}

?>
