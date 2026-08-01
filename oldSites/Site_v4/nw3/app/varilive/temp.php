<?php
namespace nw3\app\varilive;

use nw3\app\model\Variable;

/**
 * Temperature / C
 */
class Temp extends \nw3\app\model\Varilive {

	protected $trend_thresh_short_val = 0.3;
	protected $trend_thresh_long_val = 0.8;

	function __construct() {
		parent::__construct('temp');
		$this->abs_type = Variable::AbsTemp;
	}

}
?>
