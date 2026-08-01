<?php
namespace nw3\app\varilive;

use nw3\app\model\Variable;

/**
 * Feels-like Temperature / C
 */
class Feel extends \nw3\app\model\Varilive {

	function __construct() {
		parent::__construct('feel');
		$this->abs_type = Variable::AbsTemp;
		$this->type = Variable::Temperature;
	}

	public function trend_ternary() {
		return null;
	}

}
?>
