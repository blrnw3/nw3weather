<?php
namespace nw3\app\controller;

use nw3\app\core;
use nw3\app\model\Detail;

/**
 * Graphs
 *
 * @author Ben LR
 */
class Graph extends core\Controller {

	public function __construct($path) {
		parent::__construct(__CLASS__, $path);
	}

	public function index() {
		die('Unimplemented');
	}

	public function monthly() {
		$type = $this->sub_path(1);
		$data = Detail::get_instance($type);
		$this->data = $data->monthly();
//		var_dump($this->data);
		$this->build();
		$this->jpgraph();
	}

	public function daily() {
		$type = $this->sub_path(1);
		$data = Detail::get_instance($type);
		$this->data = $data->daily();
		$this->build();
		$this->jpgraph();
	}
}

?>
