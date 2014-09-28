<?php
namespace nw3\app\model;

use nw3\app\model\Variable;
use nw3\config\Admin;

/**
 * Helper for generation of historical reports for a partiular variable
 *
 * @author Ben LR
 */
class Report {

	const BANDING_MONTHLY = 1;
	const BANDING_COUNTS = 2;
	const BANDING_CUMULATIVE = 3;

	const DEFAULT_VARNAME = 'rain';
	const DEFAULT_RANKNUM = 20;

	public static $ranknumOptions = [5,10,15, 20,25,50, 100,250,500];

	public $var;
	public $categories = [];

	protected $class_prefix;
	public $class_bands = [];


	function __construct($varname) {
		$this->var = $this->sanitise_varname($varname);
		$this->generate_category_groups();
		$this->set_colour_bandings();
	}

	public function class_level($value, $bands) {
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

	protected function sanitise_year($yr) {
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

	public function sanitise_ranknum($num) {
		if(is_null($num) || !in_array($num, self::$ranknumOptions)) {
			return self::DEFAULT_RANKNUM;
		}
		return $num;
	}


	private function generate_category_groups() {
		foreach (Variable::$daily as $varname => $var) {
			$cat = $var['category'];
			if($cat) {
				if(key_exists($cat, $this->categories)) {
					$this->categories[$cat][] = $varname;
				} else {
					$this->categories[$cat] = [$varname];
				}
			}
		}
	}

	private function set_colour_bandings() {
		$this->class_prefix = $this->var['name'] .'_level_';
		$this->class_bands['day'] = [
			'count' => count($this->var['thresholds_day']),
			'bands' => $this->var['thresholds_day']
		];
		if($this->var['thresholds_month']) {
			$this->class_bands['month'] = [
				'count' => count($this->var['thresholds_month']),
				'bands' => $this->var['thresholds_month']
			];
		}
		if($this->var['summable']) {
			$day_thresholds = Variable::$_[Variable::Days]['thresholds_month'];
			$this->class_bands['count'] = [
				'count' => count($day_thresholds),
				'bands' => $day_thresholds
			];
		}
	}
}

?>
