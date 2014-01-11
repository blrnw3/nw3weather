<?php
namespace nw3\app\controller;

use nw3\app\core;
use nw3\app\model as m;

/**
 * Beaufort Scale page
 *
 * @author Ben LR
 */
class Contact extends core\Controller {

	public function __construct($path) {
		parent::__construct(__CLASS__, $path);
	}

	public function index() {
		$this->build('Contact');
		$this->render();
	}

	public function submit() {
		$this->json(m\Contact::validate($_POST));
	}

}

?>
