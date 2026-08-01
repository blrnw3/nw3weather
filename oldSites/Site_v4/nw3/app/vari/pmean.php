<?php
namespace nw3\app\vari;

use nw3\app\model\Variable;

/**
 * Daily Mean Pressure
 */
class Pmean extends Live {
	function __construct() {
		parent::__construct('pres');
	}

//	public function live() {
//		$now = $this->now;
//		return [
//			$this->main_live(), [
//				'val' => Variable::wetbulb($now->pres, $now->temp, $now->dewp),
//				'descrip' => 'Wet-Bulb Temperature',
//				'type' => Variable::Temperature
//			], [
//			]
//		];
//	}
}

?>
