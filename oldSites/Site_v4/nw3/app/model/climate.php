<?php
namespace nw3\app\model;

use nw3\data;
use nw3\app\model\Variable;
use nw3\app\util\Date;
use nw3\app\util\Maths;

/*
 * For the specific climate pages, as well as site-wide access for anomaly generation
 */
class Climate extends \nw3\app\core\Singleton {

	public $daily = [];
	public $monthly = [];
	public $seasonal = [];
	public $annual = [];

	public static $order = ['tmin', 'tmax', 'tmean', 'trange', 'rain', 'rdays', 'wmean',
			'days_frost', 'days_storm', 'days_snow', 'days_snowfall', 'wethr', 'sunhr', 'sunmax'];

	public static $order_daily = ['tmin', 'tmax', 'tmean', 'trange', 'sunmax'];

	public function __construct($load=false) {
		if($load) {
			$this->load();
		}
	}

	function load() {
		//Lazy load
		if($this->daily) {
			return;
		}

		$daily = data\Climate::$LTA_daily;
		$monthly = data\Climate::$LTA;

		foreach($monthly as $var_name => $var) {
			//Seasonal
			$this->seasonal[$var_name] = [];
			foreach(Date::$seasons as $snum => $season) {
				$this->seasonal[$var_name][$season] = 0;
				foreach(Date::$season_month_nums[$snum] as $month) {
					$this->seasonal[$var_name][$season] += $var[$month];
				}
				if(!Variable::$daily[$var_name]['summable']) {
					$this->seasonal[$var_name][$season] /= 3.0;
				}
			}
			//Annual
			$this->annual[$var_name] = [
				'sum' => array_sum($var),
				'range' => max($var) - min($var)
			];
			$this->annual[$var_name]['mean'] = round($this->annual[$var_name]['sum'] / 12.0, 1);

			//Better keys
			$this->monthly[$var_name] = array_combine(Date::$months3, $var);
		}
		$this->daily = $daily;
	}

	public function daily_ltas() {
		$daily = [];
		foreach ($this->daily as $var => $data) {
			if($var === 'wethr' || $var === 'rain' || $var === 'wmean') {
				continue;
			}
			$var_info = Variable::$daily[$var];
			$daily[$var] = [
				'values' => $data,
				'id' => $var_info['id'],
				'dpa' => (($var === 'sunmax' || $var === 'sunhr') ? 1 : 0)
			];
		}
		return $daily;
	}

	public function summary() {
		$data = [];

		foreach (self::$order as $var) {
			$var_info = Variable::$daily[$var];
			$data[$var] = [
				'monthly' => $this->monthly[$var],
				'seasonal' => $this->seasonal[$var],
				'annual' => $this->annual[$var],
				'id' => $var_info['id'],
				'dpa' => (($var === 'rain') ? -1 : 0)
			];
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
		$data = [];
		foreach ($types as $type) {
			if(key_exists($type, $this->monthly)) {
				$data[] = [
					'values' => Variable::clean_data($this->monthly[$type], $type),
					'group' => Variable::$daily[$type]
				];
			}
		}
		return $data;
	}
	public function annual_graph($types) {
		$data = [];
		foreach ($types as $type) {
			if(key_exists($type, $this->daily)) {
				$data[] = [
					'values' => Variable::clean_data($this->daily[$type], $type),
					'group' => Variable::$daily[$type]
				];
			}
		}
		return $data;
	}

	public static function get_timestamps_annual() {
		$timestamps = [];
		for($i = 0; $i < 365; $i++) {
			$timestamps[] = mktime(0,0,0, 1,1+$i,2009); //leap year avoid
		}
		return $timestamps;
	}

	public static function anom_monthly($value, $var, $month, $cumul=false) {
		if($value === null) {
			return null;
		}
		if($cumul) {
			$lta = array_sum(array_slice(data\Climate::$LTA[$var['id']], 0, $month));
			if(!$var['summable']) {
				$lta /= $month;
			}
		} else {
			$lta = data\Climate::$LTA[$var['id']][$month-1];
		}
		if($var['summable']) {
			return round($value / $lta * 100) .'%';
		} else {
			if($var['group'] === Variable::Temperature) {
				$var['id'] = 'trange'; //Hack to get proper Farenheit conversions
			}
			return Variable::conv($value-$lta, $var['id'], false, true);
		}
	}
}
?>
