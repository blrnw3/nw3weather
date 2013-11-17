<?php
namespace nw3\app\controller;

use nw3\app\model as m;

/**
 * Try to get something working with my proposed architecture
 *
 * @author Ben LR
 */
class Test {

	public function __construct() {
		$this->test1();
	}

	function test1() {
		$data = m\Test::get_some_data();
		print_r($data);
	}
}

?>
