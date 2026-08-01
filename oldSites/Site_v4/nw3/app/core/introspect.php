<?php
namespace nw3\app\core;

/**
 * Introspection
 *
 * @author Ben
 */
class Introspect {

	protected $path;

	function __construct(array $paths) {
		$this->path = implode('\\', $paths);
	}

	public static function child_name($obj) {
		$classpath = strtolower(get_class($obj));
		$parts = explode('\\', $classpath);
		return end($parts);
	}

	function is_class() {

	}
}
