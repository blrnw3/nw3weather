<?php
namespace nw3\app\vari;

/**
 * All Sun hrs stats n stuff
 */
class Sunhr extends Live {
	function __construct() {
		$this->days_filter = '> 0.1';
		parent::__construct(null);
	}

	public function trend_diffs($p) {
		return [];
	}

	public function trend_avgs($p) {
		return [];
	}

	protected function value_today() {
		return [
			'val' => null,
		];
	}
	protected function value_hr24() {
		return [
			'val' => null,
		];
	}
}
?>
