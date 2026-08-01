<?php
namespace nw3\app\varilive;


/**
 * Gust Speed / mph
 */
class Gust extends \nw3\app\model\Varilive {

	function __construct() {
		parent::__construct('gust');
	}

	public function trend_ternary() {
		return null;
	}

	public function functionName() {
		return $this->now->gust_raw;
	}

}
?>
