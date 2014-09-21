<?php
namespace nw3\app\varilive;


/**
 * Pressure / hPa
 */
class Pres extends \nw3\app\model\Varilive {

	protected $trend_thresh_short_val = 1;
	protected $trend_thresh_short_time = 60;
	protected $trend_thresh_long_val = 2;
	protected $trend_thresh_long_time = 120;

	function __construct() {
		parent::__construct('pres');
	}

}
?>
