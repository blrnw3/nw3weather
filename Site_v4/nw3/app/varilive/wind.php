<?php
namespace nw3\app\varilive;


/**
 * Wind Speed / mph
 */
class Wind extends \nw3\app\model\Varilive {

	function __construct() {
		parent::__construct('wind');
	}

	public function trend_ternary() {
		return null;
	}

}
?>
