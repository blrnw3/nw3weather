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

	function is_class() {

	}
}
