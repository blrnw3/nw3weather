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

	protected function values_today_n_24() {
		return [
			self::TODAY => [
				'val' => $this->now->today->frosthrs
			],
			self::HR24 => [
				'val' => $this->now->hr24->frosthrs
			],
		];
	}
}
?>
