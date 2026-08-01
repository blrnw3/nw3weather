<?php
namespace nw3\app\varilive;


/**
 * Wind Direction / degrees
 */
class Wdir extends \nw3\app\model\Varilive {

	function __construct() {
		parent::__construct('wdir');
	}

	public function trend_ternary() {
		return null;
	}

}
?>
