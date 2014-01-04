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
		parent::__construct(__CLASS__);
		$albnum = String::isBlank($path[0]) ? false : (int)$path[0];

		if($albnum === false) {
			$this->albums = Albums::$data;
			$this->build('photos', 'My Photos');
		} else {
			$this->album = Albums::$data[$albnum];
			$this->album_path = ASSET_PATH .'img/photos/'. $this->album['ref'];
			$this->build('album', $this->album['title'] .' - Photos', true);
		}

		$this->render();
	}
}

?>
