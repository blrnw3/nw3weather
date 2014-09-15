<?php
namespace nw3\app\api;

use nw3\app\model\Live;

class Latest {

	private $live;

	public function __construct() {
		$this->live = new Live();
	}

	public function weather_report() {
		return [
			'forecast' => $this->live->forecast(),
			'conditions' => $this->live->condition(),
			'graph' => '',
			'station_stats' => $this->live->station_stats()
		];
	}
}

?>

