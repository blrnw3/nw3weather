<?php
namespace nw3\app\core;
/**
 *
 * @author http://stackoverflow.com/questions/203336/creating-the-singleton-design-pattern-in-php5
 */
class Singleton {
	protected static $instance = null;
	protected function __construct() {
	}
	protected function __clone() {
	}

	/**
	 * Returns the one-and-only instance
	 * @return type
	 */
	public static function g() {
		if (!isset(static::$instance)) {
			static::$instance = new static;
		}
		return static::$instance;
	}
}

?>
