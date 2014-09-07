<?php
namespace nw3\app\vari;

/**
 * Daily maximum 10-min wind speed
 */
class W10max extends Max {
	function __construct() {
//		$this->days_filter = '> 30';
		parent::__construct('w10m');
	}
}

?>
