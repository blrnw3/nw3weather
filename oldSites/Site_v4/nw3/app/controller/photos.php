<?php
namespace nw3\app\controller;

use nw3\app\core;
use nw3\app\util\String;
use nw3\data\Albums;

/**
 * Webcam main page
 *
 * @author Ben LR
 */
class Photos extends core\Controller {

	public function __construct($path) {
		parent::__construct(__CLASS__, $path);
	}

	public function subpath($albnum) {
		$this->album = Albums::$data[$albnum];
		$this->album_path = ASSET_PATH .'img/photos/'. $this->album['ref'];
		$this->build($this->album['title'] .' - Photos', null, 'album');
		$this->render();
	}

	public function validate_arg($arg) {
		$albnum = is_numeric($arg) ? (int)$arg : -1;
		return array_key_exists($albnum, Albums::$data);
	}

	public function index() {
		$this->albums = Albums::$data;
		$this->build('My Photos');
		$this->render();
	}
}

?>
