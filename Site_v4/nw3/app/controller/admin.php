<?php
namespace nw3\app\controller;

use nw3\app\core;
use nw3\app\model as m;

/**
 * Admin tasks
 *
 * @author Ben LR
 */
class Admin extends core\Controller {

	public function __construct($path) {
		parent::__construct(__CLASS__, $path);
	}

	public function index() {
		die('Unimplemented');
	}

	public function css() {
		$this->raw('css');
	}

}

?>
