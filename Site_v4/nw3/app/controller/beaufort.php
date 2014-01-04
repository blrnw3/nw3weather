<?php
namespace nw3\app\controller;

use nw3\app\core;
use nw3\app\model as m;

/**
 * Beaufort Scale page
 *
 * @author Ben LR
 */
class Beaufort extends core\Controller {

	public function __construct() {
		parent::__construct(__CLASS__);
		$this->build('beaufort', 'Beaufort Scale');

		$this->bft = new m\Beaufort();
		$this->render();
	}

}

?>
