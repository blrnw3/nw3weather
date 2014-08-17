<?php
namespace nw3\app\core;

/**
 * @author Ben
 */
class Alias {
	public $field;
	public $alias;

	function __construct($field, $alias='val') {
		$this->field = $field;
		$this->alias = $alias;
	}

	function __toString() {
		return $this->alias;
	}

	function sql() {
		return "$this->field AS $this->alias";
	}
}
