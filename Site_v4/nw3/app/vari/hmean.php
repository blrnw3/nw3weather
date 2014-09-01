<?php
namespace nw3\app\vari;

use nw3\app\model\Variable;
use nw3\app\model\Store;

/**
 * Daily Mean Relative Humidity
 */
class Hmean extends Live {
	function __construct() {
//		$this->days_filter = '= 98';
		parent::__construct('hmean', 'humi');
	}

	public function live() {
		$now = Store::g();
		return [
			$this->main_live(), [
				'val' => Variable::wetbulb($now->pres, $now->temp, $now->dewp),
				'descrip' => 'Wet-Bulb Temperature',
				'type' => Variable::Temperature
			], [
				'val' => Variable::absolute_humidity($now->pres, $now->temp, $now->dewp),
				'descrip' => 'Absolute Humidity',
				'type' => Variable::Area
			], [
				'val' => Variable::air_density($now->pres, $now->temp, $now->dewp),
				'descrip' => 'Air Density',
				'type' => Variable::Area
			]
		];
	}
}

?>
