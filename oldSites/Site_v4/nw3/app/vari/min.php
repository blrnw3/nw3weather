<?php
namespace nw3\app\vari;

abstract class Min extends \nw3\app\model\Detail {

	function __construct($livename) {
		parent::__construct($livename);
	}

	protected function value_today() {
		return [
			'val' => $this->now->today->min[$this->live_var],
			'dt' => $this->now->today->timeMin[$this->live_var]
		];
	}
	protected function value_hr24() {
		return [
			'val' => $this->now->hr24->min[$this->live_var],
			'dt' => $this->now->hr24->timeMin[$this->live_var]
		];
	}
}

?>
