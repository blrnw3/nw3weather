<?php
namespace nw3\app\varilive;


/**
 * Rain / mm
 */
class Rain extends \nw3\app\model\Varilive {

	protected $trend_thresh_short_val = 0.1;
	protected $trend_thresh_short_time = 30;
	protected $trend_thresh_long_val = 1;
	protected $trend_thresh_long_time = 45;

	function __construct() {
		parent::__construct('rain');
	}

}
?>
