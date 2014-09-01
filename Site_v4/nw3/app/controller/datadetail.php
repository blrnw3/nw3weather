<?php
namespace nw3\app\controller;

use nw3\app\core;
use nw3\app\model\Detail;

/**
 * Data Detail (rain, temp, hum etc.)
 *
 * @author Ben LR
 */
class Datadetail extends core\Controller {

	public function __construct($path) {
		parent::__construct(__CLASS__, $path);
		Detail::initialise();
	}

	public function index() {
		$this->rain();
	}

	public function rain() {
		$this->build('Rain Detail');
		$this->render();
	}

	public function temperature() {
		$this->build('Temperature Detail');
		$this->render();
	}

	public function wind() {
		$this->build('Wind Detail');
		$this->render();
	}

	public function humidity() {
		$this->build('Humidity Detail');
		$this->render();
	}

	public function generic() {
		$var = $this->sub_path(1);
		$this->build("$var Detail");
		$this->render();
	}

}

?>
