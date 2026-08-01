<?php
namespace nw3\app\model;

use nw3\app\core\Db;

/**
 * Description of Test
 *
 * @author Ben LR
 */
class Test {
	private static $table = 'daily';

	private $lil;

	function __construct() {
		$this->lil = 5;
	}

	function lolo($a) {
		echo "Well donw, Ben!";
		echo ($a + $this->lil) * 10;
	}

	static function get_some_data() {
		$db = new Db();
		return $db->select(self::$table, "WHERE `day` = '2013-10-09'");
	}

}

?>
