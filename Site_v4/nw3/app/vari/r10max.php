<?php
namespace nw3\app\vari;

/**
 * Maximum 10-minute Rainfall
 */
class R10max extends Max {
	function __construct() {
//		$this->days_filter = '> 25';
		parent::__construct('rn10');
	}

	protected function assign_descriptions() {
//		$this->descrip_max = 'Max Wind Speed';
	}
}

?>
