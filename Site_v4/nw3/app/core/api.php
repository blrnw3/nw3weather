<?php
namespace nw3\app\core;

use nw3\app\model\Store;
use nw3\app\model\Variable;
use nw3\app\model\Detail;

/**
 * Description of api
 *
 * @author Ben
 */
abstract class Api {

	const ALL = 'all_vars';

	protected $vars;

	public static $trend_avg_periods = [10, 30, 60, 180];

	function __construct($vars) {
		foreach ($vars as $var) {
			$cls = new \ReflectionClass("nw3\app\vari\\$var");
			$this->vars[$var] = $cls->newInstance();
		}
	}

	public abstract function current_latest();
	public abstract function trend_diffs();
	public abstract function recent_values();
	public abstract function extremes_daily();
	public abstract function extremes_monthly();
	public abstract function extremes_yearly();
	public abstract function extremes_nday();
	public abstract function past_yr_month_aggs();
	public abstract function past_yr_month_extremes();
	public abstract function past_yr_season_aggs();

	protected function get_current_latest($vars) {
		$data = [];
		foreach($vars as $var) {
			$data += $var->live();
		}
		return $data;
	}

	protected function get_recent_values($vars) {
		$data = [];
		foreach($vars as $var) {
			$data[] = [
				'data' => $var->values(),
				'type' => $var->type,
				'descrip' => $var->var['description']
			];
		}
		return $data;
	}

	protected function get_extremes_daily($opts=null) {
		return $this->get_extremes_for_type($opts, Detail::DAILY);
	}
	protected function get_extremes_monthly($opts=null) {
		return $this->get_extremes_for_type($opts, Detail::MONTHLY);
	}
	protected function get_extremes_yearly($opts=null) {
		return $this->get_extremes_for_type($opts, Detail::YEARLY);
	}

	private function get_extremes_for_type($opts, $type) {
		$data = [];
		$opts = $this->default_opts($opts);
		foreach($opts as $varname => $optset) {
			$var = $this->vars[$varname];
			//Some sensible defaults
			if($optset === null) {
				//TODO - smart choice of these
				$optset = [MIN, MAX, MEAN];
			}
			foreach($optset as $mmm) {
				$data[] = [
					'data' => $var->extremes_agg_or_days($mmm, $type),
					'descrip' => $var->get_descrip($mmm, $type),
					'type' => ($mmm === COUNT) ? Variable::Days : $var->type,
					'rec_type' => $type
				];
			}
		}
		return $data;
	}

	protected function get_extremes_nday($vars) {
		$data = [];
		foreach($vars as $var) {
			$dat = $var->extremes_ndays();
			$data[] = [
				'data' => $dat[MIN],
				'type' => $var->type,
				'descrip' => $var->var['description'],
				'rec_type' => '\T\o jS M Y'
			];
			$data[] = [
				'data' => $dat[MAX],
				'type' => $var->type,
				'descrip' => $var->var['description'],
				'rec_type' => '\T\o jS M Y'
			];
		}
		return $data;
	}

	protected function get_ranks($opts) {
		$data = [];
		if(array_keys($opts)[0] === self::ALL) {
			$req = $opts[0];
			$opts = [];
			foreach($this->vars as $k => $v) {
				$opts[$k] = $req;
			}
		}
		foreach($opts as $varname => $optset) {
			$var = $this->vars[$varname];
			foreach($optset as $req) {
				$rec_type = $req[0];
				$period = $req[1];
				$mmm_type = $req[2];
				$mmms = ($mmm_type === MINMAX) ? [MIN, MAX] : [$mmm_type];
				foreach($mmms as $mmm) {
					$data[] = [
						'data' => $var->rankings($rec_type, $period, $mmm),
						'descrip' => $var->get_descrip($mmm, $rec_type),
						'type' => ($mmm === COUNT) ? Variable::Days : $var->type,
						'rec_type' => $rec_type
					];
				}
			}
		}
		return $data;
	}

	protected function get_past_yr_month_avgs($opts=null) {
		$data = [];
		$opts = $this->default_opts($opts);
		foreach($opts as $varname => $optset) {
			$var = $this->vars[$varname];
			//Some sensible defaults
			if($optset === null) {
				//TODO - smart choice of these
				$optset = [MEAN];
			}
			foreach($optset as $mmm) {
				$data[] = [
					'data' => $var->past_year_monthly_mmm($mmm),
					'descrip' => $var->get_descrip($mmm, Detail::DAILY),
					'type' => ($mmm === COUNT) ? Variable::Days : $var->type,
					'agg' => true
				];
			}
		}
		return $data;
	}

	protected function get_past_yr_month_extremes($opts=null) {
		$data = [];
		$opts = $this->default_opts($opts);
		foreach($opts as $varname => $optset) {
			$var = $this->vars[$varname];
			//Some sensible defaults
			if($optset === null) {
				//TODO - smart choice of these
				$optset = [MIN, MAX];
			}
			foreach($optset as $mmm) {
				$data[] = [
					'data' => $var->past_year_monthly_mmm($mmm),
					'descrip' => $var->get_descrip($mmm, Detail::DAILY),
					'type' => $var->type,
					'agg' => true
				];
			}
		}
		return $data;
	}

	protected function get_past_yr_season_aggs($opts=null) {
		$data = [];
		$opts = $this->default_opts($opts);
		foreach($opts as $varname => $optset) {
			$var = $this->vars[$varname];
			//Some sensible defaults
			if($optset === null) {
				//TODO - smart choice of these
				$optset = [MEAN];
			}
			foreach($optset as $mmm) {
				$data[] = [
					'data' => $var->past_year_seasonal_mmm($mmm),
					'descrip' => $var->get_descrip($mmm, Detail::DAILY),
					'type' => ($mmm === COUNT) ? Variable::Days : $var->type,
					'agg' => true
				];
			}
		}
		return $data;
	}

	protected function get_trend_diffs($vars) {
		$data = [];
		foreach($vars as $var) {
			$diffs = $var->trend_diffs();
			foreach($diffs as $p => $diff) {
				$data[] = [
					'val' => $diff,
					'descrip' => "Trend / $p",
					'sign' => true,
					'type' => $var->abs_type
				];
			}
		}
		return $data;
	}

	protected function assign_type(&$data, $type) {
		foreach ($data as &$dat) {
			if(!key_exists('type', $dat)) {
				$dat['type'] = $type;
			}
		}
	}

	protected function filtered_vars($vars=null, $excludes=null) {
		$ret_vars = [];
		if($vars === null) {
			$ret_vars = $this->vars;
			if($excludes) {
				foreach ($excludes as $ex) {
					unset($ret_vars[$ex]);
				}
			}
		} else {
			foreach ($vars as $v) {
				$ret_vars[$v] = $this->vars[$v];
			}
		}
		return $ret_vars;
	}

	private function default_opts($opts) {
		if($opts === null) {
			$opts = [];
			foreach($this->vars as $k => $v) {
				$opts[$k] = null;
			}
		}
		return $opts;
	}
}
