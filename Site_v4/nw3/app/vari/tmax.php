<?php
namespace nw3\app\vari;

use nw3\app\model\Variable;

/**
 * Maximum Temperature
 */
class Tmax extends Max {
	function __construct() {
		$this->days_filter = '> 25';
		parent::__construct('temp');
		$this->abs_type = Variable::AbsTemp;
	}

	protected function value_today() {
		$ltas = \nw3\app\model\Climate::g()->daily;
		return [
			'val' => $this->now->today->max['day'],
			'dt' => $this->now->today->timeMax['day'],
			'anom' => $this->now->today->max['day'] - $ltas['tmax'][D_doy]
		];
	}
	protected function value_hr24() {
		return [
			'val' => $this->now->today->max['temp'],
			'dt' => $this->now->today->timeMax['temp']
		];
	}

	protected function assign_descriptions() {
		$this->descrip_days_of = 'Days of Max > 25C';
	}
}

?>
