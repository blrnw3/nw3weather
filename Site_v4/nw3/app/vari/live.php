<?php
namespace nw3\app\vari;

use nw3\app\core\Db;
use nw3\app\util\Date;
use nw3\app\model\Store;
use nw3\app\model\Variable;

abstract class Live extends \nw3\app\model\Detail {

	public static $trend_avg_periods = [10, 30, 60, 180];
	public static $trend_diff_periods = [10, '1hr', '3hr', '6hr', '24hr'];

	protected $live;

	function __construct($name, $livename) {
		parent::__construct($name, $livename);
	}

	public function live() {
		return [$this->main_live()];
	}

	public function trend_avgs() {
		$data = [];
		foreach(self::$trend_avg_periods as $period) {
			$val = Store::g()->trend_avg($this->live_var, $period);
			$data[$period] = $val;
		}
		return $data;
	}

	public function trend_diffs() {
		$data = [];
		foreach(self::$trend_diff_periods as $period) {
			$val = Store::g()->change($this->live_var, $period);
			$data[$period] = $val;
		}
		return $data;
	}

	protected function main_live() {
		return [
			'val' => Store::g()->{$this->live_var},
			'descrip' => $this->live['name'],
			'type' => $this->live['group']
		];
	}
}

?>
