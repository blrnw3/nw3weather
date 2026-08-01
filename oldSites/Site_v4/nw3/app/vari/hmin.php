<?php
namespace nw3\app\vari;


/**
 * Daily Maximum Relative Humidity
 */
class Hmin extends Min {
	function __construct() {
		$this->days_filter = '< 35';
		parent::__construct('humi');
	}
}

?>
