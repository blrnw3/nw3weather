<?php
namespace nw3\app\model;

use nw3\app\model\Variable;

/**
 * All rain stats n stuff
 */
class Tmax extends Detail {
	function __construct() {
		$this->days_filter = '> 25';
		parent::__construct('tmax');
		$this->abs_type = Variable::AbsTemp;
	}
}

?>
