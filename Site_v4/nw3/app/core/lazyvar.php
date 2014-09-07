<?php
namespace nw3\app\core;

use nw3\app\util\File;

/**
 * Loads serialised data from the FS lazily, i.e. on first access
 *
 * @author Ben
 */
class Lazyvar {

	private $data;
	private $path;

	function __construct($path) {
		$this->path = $path;
	}

	function __get($name) {
		// Lazy load
		if(!isset($this->data)) {
			$this->load();
		}
		// Ensure the key is valid
		if (array_key_exists($name, $this->data)) {
			return $this->data[$name];
		}
		// Except
		throw new \Exception("$name is not a valid key for $this->path");
	}

	public function json() {
		$this->load();
		return $this->data;
	}

	private function load() {
		$this->data = unserialize(File::live_data($this->path));
	}

}
