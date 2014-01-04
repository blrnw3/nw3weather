<?php
namespace nw3\app\controller;

use nw3\app\core;

/**
 * Routes for pages with no model or special controller interaction required (essentially static pages)
 *
 * @author Ben LR
 */
class Nomodel extends core\Controller {

	private static $paths = array(
		'external' => 'External',
		'about' => 'About',
		'forecast' => 'Forecast',
		'blog' => 'Blog | News',
	);

	public function __construct($path) {
		parent::__construct($path);
		$this->build($path, self::$paths[$path]);
		$this->render();
	}

	static function path_exists($path) {
		return array_key_exists($path, self::$paths);
	}
}

?>
