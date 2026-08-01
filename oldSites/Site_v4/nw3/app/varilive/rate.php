<?php
namespace nw3\app\varilive;

use nw3\app\model\Variable;
/**
 * Rainfall Rate / mm/h
 */
class Rate extends \nw3\app\model\Varilive {

	public $type = Variable::RainRate;

	function __construct() {
		parent::__construct('rate');
	}

	public function current() {
		return $this->hr24->rnrate;
	}

	public function trend_ternary() {
		return null;
	}

}
?>
