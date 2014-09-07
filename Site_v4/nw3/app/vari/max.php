<?php
namespace nw3\app\vari;

abstract class Max extends \nw3\app\model\Detail {

	function __construct($livename) {
		parent::__construct($livename);
	}

	protected function values_today_n_24() {
		return [
			self::TODAY => [
				'val' => $this->now->today->max[$this->live_var],
				'dt' => $this->now->today->timeMax[$this->live_var]
			],
			self::HR24 => [
				'val' => $this->now->hr24->max[$this->live_var],
				'dt' => $this->now->hr24->timeMax[$this->live_var]
			],
		];
	}
}

?>
