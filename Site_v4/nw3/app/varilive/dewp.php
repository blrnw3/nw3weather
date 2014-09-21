<?php
namespace nw3\app\varilive;

use nw3\app\model\Variable;

/**
 * Dew Point / C
 */
class Dewp extends \nw3\app\model\Varilive {

	protected $trend_thresh_short_val = 0.4;
	protected $trend_thresh_long_val = 0.9;

	function __construct() {
		parent::__construct('dewp');
		$this->abs_type = Variable::AbsTemp;
		$this->type = Variable::Temperature;
	}

}
?>
