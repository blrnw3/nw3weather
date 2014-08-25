<?php
namespace nw3\app\model;

use nw3\app\core\Db;
use nw3\app\util\Date;
use nw3\app\model\Store;

/**
 * All rain stats n stuff
 */
class Tmax extends Detail {
	function __construct() {
		$this->days_filter = '> 25';
		parent::__construct('tmax');
	}
}

?>
