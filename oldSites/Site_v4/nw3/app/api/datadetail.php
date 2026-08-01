<?php

namespace nw3\app\api;

use nw3\app\model\Variable;
use nw3\app\model\Detail;

/**
 * Main API for specific variable
 * TODO - make the keys more useful for the public API, i.e. use an assoc array
 *
 * @author Ben
 */
abstract class Datadetail {

	const ALL = 'all_vars';

	public static $mmm_pretty_map = [null, 'min', 'mean', 'max', 'count', 'spell', 'spell_inv'];
	public $trend_avg_periods = [10, 30, 60, 120, 180, 360, 720];
	public $trend_diff_periods = [10, 30, '1h', '3h', '6h', '12h', '24h'];

	protected $default_vars;
	private $vars = [];

	function __construct($default_vars) {
		$this->default_vars = $default_vars;
	}

	protected function vari($name) {
		if (!key_exists($name, $this->vars)) {
			$this->vars[$name] = Detail::get_instance($name);
		}
		return $this->vars[$name];
	}

	public function trend_diffs() {
		return $this->get_trend_diffs();
	}

	public function trend_avgs() {
		return $this->get_trend_avgs();
	}

	/*
	 * Default functions - override for more control
	 */

	public function current_latest() {
		return $this->get_current_latest();
	}

	public function recent_values() {
		return $this->get_recent_values();
	}

	public function extremes_daily() {
		return $this->get_extremes_daily();
	}

	public function extremes_monthly() {
		return $this->get_extremes_monthly();
	}

	public function extremes_yearly() {
		return $this->get_extremes_yearly();
	}

	public function extremes_nday() {
		return $this->get_extremes_nday();
	}

	public function ranks_daily() {
		return $this->get_ranks([
				self::ALL => [
					[Detail::DAILY, Detail::RECORD, MINMAX],
				]
		]);
	}

	public function ranks_monthly() {
		return $this->get_ranks([
				self::ALL => [
					[Detail::MONTHLY, Detail::RECORD, MINMAX],
				]
		]);
	}

	public function ranks_daily_curr_month() {
		return null;
	}

	public function ranks_daily_past_year() {
		return null;
	}

	public function ranks_custom() {
		// TODO - allow setting of descrips
		return null;
	}

	public function past_yr_monthly_aggs() {
		return $this->get_past_yr_month_aggs();
	}

	public function past_yr_monthly_extremes() {
		return $this->get_past_yr_month_extremes();
	}

	public function past_yr_season_aggs() {
		return $this->get_past_yr_season_aggs();
	}

	public function record_24hrs() {
		return null; //TODO
	}

	protected function get_current_latest($varnames=null) {
		$data = [];
		if(is_null($varnames)) $varnames = $this->default_vars;
		foreach ($varnames as $varname) {
			$data += $this->vari($varname)->live();
		}
		return $data;
	}

	protected function get_recent_values($varnames=null, $periods=null) {
		$data = [];
		if(is_null($varnames)) $varnames = $this->default_vars;
		foreach ($varnames as $varname) {
			$var = $this->vari($varname);
			$data[] = [
				'data' => $var->values($periods),
				'type' => $var->type,
				'descrip' => $var->var['description']
			];
		}
		return $data;
	}

	protected function get_extremes_daily($opts=null, $periods=null) {
		return $this->get_extremes_for_type($opts, $periods, Detail::DAILY);
	}

	protected function get_extremes_monthly($opts=null, $periods=null) {
		return $this->get_extremes_for_type($opts, $periods, Detail::MONTHLY);
	}

	protected function get_extremes_yearly($opts=null, $periods=null) {
		return $this->get_extremes_for_type($opts, $periods, Detail::YEARLY);
	}

	private function get_extremes_for_type($opts, $periods, $type) {
		$data = [];
		$optset_default = [MIN, MAX];
		if ($type === Detail::DAILY) {
			$optset_default[] = MEAN;
		}
		$opts = $this->default_opts($opts, $optset_default);
		foreach ($opts as $varname => $optset) {
			$var = $this->vari($varname);
			foreach ($optset as $mmm) {
				$data[$this->nice_key($varname, $type, self::$mmm_pretty_map[$mmm])] = [
					'data' => $var->extremes_agg_or_days($mmm, $type, $periods),
					'descrip' => $var->get_descrip($mmm, $type),
					'type' => ($mmm >= COUNT) ? Variable::Days : $var->type,
					'rec_type' => $type
				];
			}
		}
		return $data;
	}

	protected function get_extremes_nday($opts = null) {
		$optset_default = [MIN, MAX];
		$opts = $this->default_opts($opts, $optset_default);
		foreach ($opts as $varname => $optset) {
			$var = $this->vari($varname);
			$dat = $var->extremes_ndays();
			if (in_array(MIN, $optset)) {
				$data[] = [
					'data' => $dat[MIN],
					'type' => $var->type,
					'descrip' => $var->descrip_period_min,
					'rec_type' => '\T\o jS M Y'
				];
			}
			if (in_array(MAX, $optset)) {
				$data[] = [
					'data' => $dat[MAX],
					'type' => $var->type,
					'descrip' => $var->descrip_period_max,
					'rec_type' => '\T\o jS M Y'
				];
			}
		}
		return $data;
	}

	protected function get_ranks($opts) {
		$data = [];
		if (array_keys($opts)[0] === self::ALL) {
			$req = $opts[self::ALL];
			$opts = $this->default_opts(null, $req);
		}
		foreach ($opts as $varname => $optset) {
			$var = $this->vari($varname);
			foreach ($optset as $req) {
				$rec_type = $req[0];
				$period = $req[1];
				$mmm_type = $req[2];
				$mmms = ($mmm_type === MINMAX) ? [MIN, MAX] : [$mmm_type];
				foreach ($mmms as $mmm) {
					$data[] = [
						'data' => $var->rankings($rec_type, $period, $mmm),
						'descrip' => $var->get_descrip($mmm, $rec_type),
						'type' => ($mmm === COUNT) ? Variable::Days : $var->type,
						'rec_type' => $rec_type,
						'period' => $period
					];
				}
			}
		}
		return $data;
	}

	protected function get_past_yr_month_aggs($opts = null) {
		$data = [];
		$opts = $this->default_opts($opts, [MEAN]);
		foreach ($opts as $varname => $optset) {
			$var = $this->vari($varname);
			foreach ($optset as $mmm) {
				$data[] = [
					'descrip' => $var->get_descrip($mmm, Detail::DAILY),
					'type' => ($mmm === COUNT) ? Variable::Days : $var->type,
					'agg' => true,
					'rec_type' => Detail::MONTHLY,
					'period' => 365
					] + $var->past_year_monthly_mmm($mmm);
			}
		}
		return $data;
	}

	protected function get_past_yr_month_extremes($opts = null) {
		$data = [];
		$opts = $this->default_opts($opts, [MIN, MAX]);
		foreach ($opts as $varname => $optset) {
			$var = $this->vari($varname);
			foreach ($optset as $mmm) {
				$data[] = [
					'periods' => $var->past_year_monthly_mmm($mmm),
					'descrip' => $var->get_descrip($mmm, Detail::DAILY),
					'type' => $var->type,
					'agg' => true,
					'rec_type' => Detail::MONTHLY,
					'period' => 365,
					'no_anom' => $var->no_daily_anom
				];
			}
		}
		return $data;
	}

	protected function get_past_yr_season_aggs($opts = null) {
		$data = [];
		$opts = $this->default_opts($opts, [MEAN]);
		foreach ($opts as $varname => $optset) {
			$var = $this->vari($varname);
			foreach ($optset as $mmm) {
				$data[] = [
					'descrip' => $var->get_descrip($mmm, Detail::DAILY),
					'type' => ($mmm === COUNT) ? Variable::Days : $var->type,
					'agg' => true,
					'rec_type' => Detail::SEASONAL,
					'period' => 365
					] + $var->past_year_seasonal_mmm($mmm);
			}
		}
		return $data;
	}

	protected function get_trend_diffs($varnames=null) {
		$data = [];
		if(is_null($varnames)) $varnames = $this->default_vars;
		foreach ($varnames as $varname) {
			$var = $this->vari($varname);
			$diffs = $var->trend_diffs($this->trend_diff_periods);
			if(!$diffs) {
				continue;
			}
			$data[$varname] = [
				'data' => [],
				'now' => $var->current(),
				'is_abs' => true,
				'type' => $var->abs_type,
				'descrip' => $var->live['name']
			];
			foreach ($diffs as $p => $diff) {
				$data[$varname]['data'][self::pretty_trend_period($p)] = $diff;
			}
		}
		return $data;
	}

	protected function get_trend_avgs($varnames=null) {
		$data = [];
		if(is_null($varnames)) $varnames = $this->default_vars;
		foreach ($varnames as $varname) {
			$var = $this->vari($varname);
			$diffs = $var->trend_avgs($this->trend_avg_periods);
			if(!$diffs) {
				continue;
			}
			$data[$varname] = [
				'data' => [],
				'now' => $var->current(),
				'type' => $var->type,
				'descrip' => $var->live['name']
			];
			foreach ($diffs as $p => $diff) {
				$data[$varname]['data'][self::pretty_trend_period($p)] = $diff;
			}
		}
		return $data;
	}

	protected function nice_key() {
		return implode('_', func_get_args());
	}

	private function default_opts($opts, $default = null) {
		if ($opts === null) {
			$opts = [];
			foreach ($this->default_vars as $v) {
				$opts[$v] = $default;
			}
		}
		return $opts;
	}

	public static function pretty_trend_period($period) {
		if (is_int($period)) {
			if ($period % 60 === 0) {
				return ($period === 60) ? 'hour' : ($period / 60) . ' hrs';
			} else {
				return "$period mins";
			}
		} else {
			return ($period === '1h') ? 'hour' : "{$period}rs";
		}
	}

}
