<?php
namespace nw3\app\vari;

/**
 * All AF hrs stats n stuff
 */
class Afhrs extends Live {
	function __construct() {
		parent::__construct('frosthrs');
	}

	public function trend_diffs($p) {
		return [];
	}

	public function trend_avgs($p) {
		return [];
	}

	protected function value_today() {
		return ['val' => $this->now->today->frosthrs];
	}
	protected function value_hr24() {
		return ['val' => $this->now->hr24->frosthrs];
	}
}
?>
