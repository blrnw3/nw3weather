<?php
namespace nw3\app\vari;

use nw3\app\model\Variable;

/**
 * Maximum Rainfall Rate
 */
class Ratemax extends Max {
	function __construct() {
//		$this->days_filter = '> 25';
		parent::__construct('rate');
	}

	public function live() {
		return [[
			'descrip' => 'Rain Rate',
			'val' => $this->now->hr24->rnrate,
			'type' => Variable::RainRate
		]];
	}

	protected function assign_descriptions() {
//		$this->descrip_max = 'Max Wind Speed';
	}
}

?>
