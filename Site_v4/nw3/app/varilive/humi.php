<?php
namespace nw3\app\varilive;


/**
 * Relative Humidity / %
 */
class Humi extends \nw3\app\model\Varilive {

	protected $trend_thresh_short_val = 2;
	protected $trend_thresh_long_val = 8;
	protected $trend_thresh_long_time = 45;

	function __construct() {
		parent::__construct('humi');
	}

}
?>
