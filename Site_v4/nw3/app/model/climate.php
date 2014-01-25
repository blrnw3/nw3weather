<?php
namespace nw3\app\model;

use nw3\data;
use nw3\app\model\Variable;
use nw3\app\util\Date;
use nw3\app\util\Maths;

/*
 * For the specific climate pages, as well as site-wide access for anomaly generation
 */
class Climate {

	public $daily = array();
	public $monthly = array();
	public $seasonal = array();
	public $annual = array();

	public static $order = array('tmin', 'tmax', 'tmean', 'trange', 'rain', 'rdays', 'wmean',
			'days_frost', 'days_storm', 'days_snow', 'days_snowfall', 'wethr', 'sunhr', 'sunmax');

	public static $order_daily = array('tmin', 'tmax', 'tmean', 'trange', 'sunmax');

	public function __construct() {
		$daily = data\Climate::$LTA_daily;
		$monthly = data\Climate::$LTA;

		foreach($monthly as $var_name => $var) {
			//Seasonal
			$this->seasonal[$var_name] = array();
			foreach(Date::$seasons as $snum => $season) {
				$this->seasonal[$var_name][$season] = 0;
				foreach(Date::$season_month_nums[$snum] as $month) {
					$this->seasonal[$var_name][$season] += $var[$month];
				}
				if(!Variable::$daily[$var_name]['summable']) {
					$this->seasonal[$var_name][$season] /= 3;
				}
			}
			//Annual
			$this->annual[$var_name] = array(
				'sum' => array_sum($var),
				'range' => max($var) - min($var)
			);
			$this->annual[$var_name]['mean'] = round($this->annual[$var_name]['sum'] / 12, 1);

			//Better keys
			$this->monthly[$var_name] = array_combine(Date::$months3, $var);
		}
		$this->daily = $daily;
	}

	public function daily_ltas() {
		$daily = array();
		foreach ($this->daily as $var => $data) {
			$var_info = Variable::$daily[$var];
			$daily[$var] = array(
				'values' => $data,
				'group' => $var_info['group'],
				'dpa' => (($var === 'sunmax') ? 1 : 0)
			);
		}
		return $daily;
	}

	public function summary() {
		$data = array();

		foreach (self::$order as $var) {
			$var_info = Variable::$daily[$var];
			$data[$var] = array(
				'monthly' => $this->monthly[$var],
				'seasonal' => $this->seasonal[$var],
				'annual' => $this->annual[$var],
				'group' => $var_info['group'],
				'dpa' => (($var === 'rain') ? -1 : 0)
			);
		}
		return $data;
	}

	public function sun_with_maxsun_comparison($period, $key) {
		$sun_val = round($this->{$period}['sunhr'][$key]);
		$maxsun_val = $this->{$period}['sunmax'][$key];
		$maxsun_percent = Maths::percent($sun_val, $maxsun_val, 0, true, false);

		return "$sun_val (<abbr title=\"Max possible: $maxsun_val \">$maxsun_percent</abbr>)";
	}

	public function monthly_graph($types) {
		$data = array();
		foreach ($types as $type) {
			if(key_exists($type, $this->monthly)) {
				$data[] = array(
					'values' => Variable::clean_data($this->monthly[$type], $type),
					'group' => Variable::$daily[$type]
				);
			}
		}
		return $data;
	}
	public function annual_graph($types) {
		$data = array();
		foreach ($types as $type) {
			if(key_exists($type, $this->daily)) {
				$data[] = array(
					'values' => Variable::clean_data($this->daily[$type], $type),
					'group' => Variable::$daily[$type]
				);
			}
		}
		return $data;
	}

	public static function get_timestamps_annual() {
		$timestamps = array();
		for($i = 0; $i < 366; $i++) {
			$timestamps[] = mktime(0,0,0, 1,1+$i,2008); //leap year needed
		}
		return $timestamps;
	}
}
?>
