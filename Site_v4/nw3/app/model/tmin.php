<?php
namespace nw3\app\model;

/**
 * All rain stats n stuff
 */
class Tmin extends Detail {
	function __construct() {
		$this->days_filter = '< 0';
		parent::__construct('tmin');
	}
}

?>
