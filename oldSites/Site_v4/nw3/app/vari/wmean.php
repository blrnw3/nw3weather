<?php
namespace nw3\app\vari;

use nw3\app\model\Variable;

/**
 * Daily Mean Wind Speed
 */
class Wmean extends Live {
	function __construct() {
//		$this->days_filter = '> 10';
		parent::__construct('wind');
	}

	public function live() {
		return [
			$this->main_live(), [
				'val' => Variable::bft($this->now->wind),
				'descrip' => 'Beaufort Speed',
				'type' => Variable::None
			]
		];
	}
}

?>
