<?php
namespace nw3\app\vari;

use nw3\app\model\Variable;

/**
 * Minimum Temperature
 */
class Tmin extends Min {
	function __construct() {
		$this->days_filter = '< 0';
		parent::__construct('temp');
		$this->abs_type = Variable::AbsTemp;
	}

	protected function values_today_n_24() {
		$ltas = \nw3\app\model\Climate::g()->daily;
		return [
			self::TODAY => [
				'val' => $this->now->today->min['night'],
				'dt' => $this->now->today->timeMin['night'],
				'anom' => $this->now->today->min['night'] - $ltas['tmin'][D_doy]
			],
			self::HR24 => [
				'val' => $this->now->today->min['temp'],
				'dt' => $this->now->today->timeMin['temp']
			],
		];
	}

	protected function assign_descriptions() {
		$this->descrip_days_of = 'Days of Air Frost';
	}
}

?>
