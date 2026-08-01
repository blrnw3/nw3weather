<?php
namespace nw3\app\vari;

/**
 * Daily maximum Wind speed
 */
class Wmax extends Max {
	function __construct() {
//		$this->days_filter = '> 25';
		parent::__construct('wind');
	}

	protected function assign_descriptions() {
		$this->descrip_max = 'Max Wind Speed';
	}
}

?>
