<?php
namespace nw3\app\vari;

/**
 * Maximum Hourly Rainfall
 */
class Hrmax extends Max {
	function __construct() {
//		$this->days_filter = '> 25';
		parent::__construct('rnhr');
	}

	protected function assign_descriptions() {
//		$this->descrip_max = 'Max Wind Speed';
	}
}

?>
