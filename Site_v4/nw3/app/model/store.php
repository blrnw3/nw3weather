<?php
namespace nw3\app\model;

use nw3\app\core\Logger;
use nw3\app\core\Lazyvar;
use nw3\app\model\Variable;
use nw3\app\util\File;

/**
 * Data store:
 * one shop access to non-db data, like serialised 24hr/day data, and live/current data
 * Uses lazy loading so data is only pulled from the fs if needed, though live data is always needed
 * so is pulled at construct time.
 *
 *  Holder of call current data for the core 'live' variables
 *  Rips them out of clientraw for now, but in future would be good
 *	to get POSTing the data, serialising it, then deserialising here
 */
class Store extends \nw3\app\core\Singleton {

	const DAT24_NAME = 'dat24.sphp';
	const DATDAY_NAME = 'datday.sphp';

	// Live Data
	public $temp;
	public $humi;
	public $pres;
	public $rain;
	public $wind;
	public $gust;
	public $gust_raw;
	public $w10m;
	public $wdir;
	public $dewp;
	public $feel;

	public $today;
	public $hr24;

	function __construct() {
		$this->load_live();

		$this->today = new Lazyvar(self::DATDAY_NAME);
		$this->hr24 = new Lazyvar(self::DAT24_NAME);
	}

	function change($var, $since) {
		return $this->hr24->trend[0][$var] - $this->hr24->trend[$since][$var];
	}

	private function load_live() {
		$backup_path = File::live_path('wd_latest_stable.txt');
		$live_path = File::live_path('wd_latest.txt');

		$size = filesize($live_path);
//		$time_diff = D_now - filemtime($live_path);

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
		$this->temp = (float)$data[4];
		$this->humi = (int)$data[5];
		$this->pres = (int)$data[6];
		$this->rain = (float)$data[7];
		$this->wind = (float)($data[1] * $kntsToMph);
		$this->gust = (float)($data[140] * $kntsToMph); //actually the max 1-min gust
		$this->gust_raw = (float)($data[2] * $kntsToMph); //true 14s gust
		$this->w10m = (float)($data[158] * $kntsToMph);
		$this->wdir = (int)$data[3];
		// Calculated
		$this->dewp = Variable::dewPoint($this->temp, $this->humi);
		$this->feel = Variable::feelsLike($this->temp, $this->wind, $this->dewp);
	}
}
?>
