<?php
namespace nw3\app\core;

use nw3\app\model\Store;
use nw3\app\model\Variable;

/**
 * Description of api
 *
 * @author Ben
 */
class Api {

	public $default_type;
	public $live_var;

	public static $trend_avg_periods = [10, 30, 60, 180];

	function __construct($default_type, $live_var) {
		$this->default_type = $default_type;
		$this->live_var = $live_var;
//		$recs = Store::g()->record_24hr($live_var);
//		var_dump($recs);
	}

	function current_latest() {
		throw new \BadMethodCallException('Not implemented');
	}
	function recent_values() {
		throw new \BadMethodCallException('Not implemented');
	}

	protected function trend_avgs_single() {
		$data = [];
		foreach(self::$trend_avg_periods as $period) {
			$val = Store::g()->trend_avg($this->live_var, $period);
			$data[$period] = $val;
		}
		return $data;
	}

	protected function assign_default_type(&$data) {
		foreach ($data as &$dat) {
			if(!key_exists('type', $dat)) {
				$dat['type'] = $this->default_type;
			}
		}
	}
}
