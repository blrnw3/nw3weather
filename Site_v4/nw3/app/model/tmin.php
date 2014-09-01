<?php
namespace nw3\app\model;

use nw3\app\model\Variable;
/**
 * All rain stats n stuff
 */
class Tmin extends Detail {
	function __construct() {
		$this->days_filter = '< 0';
		parent::__construct('tmin');
		$this->abs_type = Variable::AbsTemp;
	}
}

?>
