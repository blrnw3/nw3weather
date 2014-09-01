<?php
namespace nw3\app\vari;


/**
 * Daily Maximum Relative Humidity
 */
class Hmax extends \nw3\app\model\Detail {
	function __construct() {
		$this->days_filter = '> 95';
		parent::__construct('hmax', 'humi');
	}
}

?>
