<?php
namespace nw3\app\core;

use nw3\app\core\Db;

class ModelFactory {

	protected $db;

	public function __construct($db) {
		$this->db = $db;
	}

}

?>
