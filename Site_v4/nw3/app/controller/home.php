<?php
namespace nw3\app\controller;

use nw3\app\model\Live;
use nw3\app\core;
use nw3\app\util as u;

/**
 * Webcam main page
 *
 * @author Ben LR
 */
class Home extends core\Controller {

	public function __construct() {
		parent::__construct(__CLASS__);
		$this->build('home', 'Live Weather');

		$live = new Live();

		$this->m = $live;

		$this->render();
	}

}

?>
