<?php
namespace nw3\app\vari;


/**
 * Daily Maximum Relative Humidity
 */
class Hmax extends Max {
	function __construct() {
		$this->days_filter = '> 95';
		parent::__construct('humi');
	}
}

?>
