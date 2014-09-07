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

	protected function values_today_n_24() {
		$ltas = \nw3\app\model\Climate::g()->daily;
		return [
			self::TODAY => [
				'val' => $this->now->today->max['day'],
				'dt' => $this->now->today->timeMax['day'],
				'anom' => $this->now->today->max['day'] - $ltas['tmax'][D_doy]
			],
			self::HR24 => [
				'val' => $this->now->today->max['temp'],
				'dt' => $this->now->today->timeMax['temp']
			],
		];
	}

	protected function assign_descriptions() {
		$this->descrip_days_of = 'Days of Max > 25C';
	}
}

?>
