<?php
namespace nw3\app\controller;

use nw3\app\core;
use nw3\app\model;

/**
 * Climate
 *
 * @author Ben LR
 */
class Climate extends core\Controller {

	public function __construct() {
		parent::__construct(__CLASS__);
	}

	public function index() {
		$this->build('Climate');
		$this->render();
	}

	public function daily() {
		$this->build('Daily LTAs - Climate');
		$this->render();
	}

	public function graph() {
		$climate = new model\Climate();
		$types = explode(',', $_GET['types']);
		$this->data = $climate->monthly_graph($types);

		$this->jpgraph('graph');
	}
	public function graphyear() {
		$climate = new model\Climate();
		$types = explode(',', $_GET['types']);
		$this->data = $climate->annual_graph($types);

		$this->jpgraph('graphyear');
	}
}

?>
