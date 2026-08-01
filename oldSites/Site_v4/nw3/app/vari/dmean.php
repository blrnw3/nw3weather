<?php
namespace nw3\app\vari;

use nw3\app\model\Variable;

/**
 * Daily Mean Dew Point
 */
class Dmean extends Live {


	function __construct() {
		parent::__construct('dewp');
		$this->live_var_sql = '(237.7*((17.271*temp)/(237.7+temp)+LOG(humi/100)))/(17.271-((17.271*temp)/(237.7+temp)+LOG(humi/100)))';
	}

	public function live() {
		$now = $this->now;
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
