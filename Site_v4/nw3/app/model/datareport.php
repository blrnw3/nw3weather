<?php
namespace nw3\app\model;

use nw3\app\model\Variable;
use nw3\app\model\Climate;
use nw3\app\model\Report;
use nw3\app\util\Date;
use nw3\app\util\Maths;
use nw3\app\core\Db;
use nw3\config\Admin;

/*
 * Data Reports
 */
class Datareport extends Report {
	const DEFAULT_VARNAME = 'rain';

	public $var;
	public $year;
	public $rolling12;
	public $months;
	public $dims;
	public $data = array();

	private $class_prefix;
	private $class_bands = array();

	public function __construct($var_name, $year, $rolling12) {
		parent::__construct();

		$this->rolling12 = $rolling12;
		$this->year = $this->sanitise_year($year);
		$this->var = $this->sanitise_varname($var_name);

		$this->get_months();
		$this->get_data(new Db());

		$this->class_prefix = $this->var['name'] .'_level_';
		$this->class_bands['day'] = array('count' => count($this->var['thresholds_day']),
			'bands' => $this->var['thresholds_day']);
		if($this->var['thresholds_month']) {
			$this->class_bands['month'] = array('count' => count($this->var['thresholds_month']),
				'bands' => $this->var['thresholds_month']);
		}
		if($this->var['summable']) {
			$day_thresholds = Variable::$_[Variable::Days]['thresholds_month'];
			$this->class_bands['count'] = array('count' => count($day_thresholds),
				'bands' => $day_thresholds);
		}

	}

	function get_months() {
		$dims = array(); //Days in month
		if(!$this->rolling12) {
			$this->months = Date::$monthsn;
			for($m = 1; $m <= 12; $m++) {
				$dims[$m] = Date::get_days_in_month($m, $this->year);
			}
		} else {
			//get the last rolling 12 months
			$months = array();
			for($m = 11; $m >= 0; $m--) {
				$dt = Date::mkdate(D_month - $m, 1);
				$month = (int)date('n', $dt);
				$months[] = $month;
				$dims[$month] = (int)date('t', $dt);
			}
			$this->months = $months;
		}
		$this->dims = $dims;
	}

	function get_data($db) {
		if($this->rolling12) {
			$d1 = date(Db::DATE_FORMAT, Date::mkdate(D_month-11, 1));
			$d2 = date(Db::DATE_FORMAT, D_now);
		} else {
			$d1 = "{$this->year}-01-01";
			$d2 = "{$this->year}-12-31";
		}

		$select = "DAYOFMONTH(d) as d, MONTH(d) as m, {$this->var['id']} as var";
		$where = "WHERE d BETWEEN '$d1' AND '$d2'";
		$data = $db->select('daily', $where, $select);

		$daily = array();
		foreach ($data as $val) {
			$daily[$val['m']][$val['d']] = $val['var'];
		}
		$this->data['daily'] = $daily;

		if($this->var['nosummary']) {
			return;
		}
		$this->data['monthly_min'] = $this->data['monthly_max'] =
			$this->data['monthly_mean'] = $this->data['monthly_count'] = array();
		$cumul = $cumul_count = 0;

		foreach ($daily as $m => $value) {
			$sum = array_sum($value);
			$valid_count = Maths::count($value);
			$cumul += $sum;
			$cumul_count += $valid_count;

			if($valid_count > 0) {
				$this->data['monthly_mean'][$m] = $this->var['summable'] ? $sum : $sum / $valid_count;
			}
			$this->data['monthly_max'][$m] = Maths::max($value);

			if($this->var['summable']) {
				$this->data['monthly_count'][$m] = Maths::count_cond($value, true, 0);
			} else {
				$this->data['monthly_min'][$m] = Maths::min($value);
			}

			$show_cumul = ($cumul_count > 0) && !$this->rolling12;
			if($show_cumul) {
				$this->data['monthly_cumul'][$m] = $this->var['summable'] ? $cumul : $cumul / $cumul_count;
			}

			if($this->var['anomable']) {
				$this->data['monthly_anom'][$m] = Climate::anom_monthly($this->data['monthly_mean'][$m], $this->var, $m);

				if($show_cumul) {
					$this->data['monthly_cumul_anom'][$m] =
						Climate::anom_monthly($this->data['monthly_cumul'][$m], $this->var, $m, true);
				}
			}
		}
	}

	function finalise($val) {
		return Variable::conv($val, $this->var['id'], false);
	}

	function get_class_day($value, $day, $month) {
		if($day > $this->dims[$month]) {
			return 'noday';
		}
		if(Date::mkdate($month, $day, $this->year) > D_now || ($this->rolling12 && $month === D_month && $day > D_day)) {
			return 'notyetday';
		}
		return $this->class_level($value, $this->class_bands['day']);
	}

	function get_class_month($value, $month, $banding_type=0) {
		if(Date::mkdate($month, 1, $this->year) > D_now) {
			return 'notyetday';
		}
		if($banding_type === self::BANDING_MONTHLY && $this->class_bands['month']) {
			return $this->class_level($value, $this->class_bands['month']);
		}
		if($banding_type === self::BANDING_COUNTS) {
			return $this->class_level($value, $this->class_bands['count']);
		}
		if($banding_type === self::BANDING_CUMULATIVE && $this->class_bands['month'] && $value !== null) {
			return $this->class_level($value/$month, $this->class_bands['month']);
		}

		return $this->class_level($value, $this->class_bands['day']);
	}

	private function class_level($value, $bands) {
		if($value === null) {
			return 'null';
		}
		for($i = 0; $i < $bands['count']; $i++){
			if($value < $bands['bands'][$i]) {
				return $this->class_prefix.$i;
			}
		}
		return $this->class_prefix.$i;
	}

	private function sanitise_year($yr) {
		if(is_null($yr) || $yr > D_year) {
			return D_yest_year;
		}
		if($yr !== 0 && $yr < Admin::FIRST_YEAR_REPORTS) {
			return Admin::FIRST_YEAR_REPORTS;
		}
		return $yr;
	}

	private function sanitise_varname($varname) {
		if(is_null($varname) || !Variable::is_valid($varname)) {
			return Variable::$daily[self::DEFAULT_VARNAME];
		}
		return Variable::$daily[$varname];
	}
}
?>
