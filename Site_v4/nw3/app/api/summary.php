<?php
namespace nw3\app\api;

use nw3\app\model\Variable;
use nw3\app\model\Detail;

/**
 *
 * @author Ben
 */
class Summary extends Datadetail {

	private $trend_vars = ['tmean', 'hmean', 'rain', 'wmean', 'pmean', 'dmean'];

	function __construct() {
		parent::__construct([]);
	}

	public function extremes() {
		return $this->get_extremes_daily([
			'tmin' => [MIN], 'tmax' => [MAX],
			'hmin' => [MIN], 'pmax' => [MAX],
			'rain' => [MAX], 'hrmax' => [MAX], 'r10max' => [MAX],
			'wmax' => [MAX], 'gust' => [MAX],
			'pmin' => [MIN], 'pmax' => [MAX],
			'sunhr' => [MAX],
			'dmin' => [MIN], 'dmax' => [MAX],
			'fmin' => [MIN], 'fmax' => [MAX],
		], [
			Detail::YESTERDAY, 7, 31, 365, Detail::RECORD
		]);
	}

	public function aggs() {
		return $this->get_extremes_daily([
			'tmean' => [MEAN],
			'hmean' => [MEAN],
			'rain' => [MEAN, DAYS],
			'sunhr' => [MEAN, DAYS],
			'wmean' => [MEAN],
			'pmean' => [MEAN],
			'dmean' => [MEAN],
			],[
			Detail::TODAY, Detail::YESTERDAY, 7, Detail::NOWMON, 31, Detail::NOWYR, 365, Detail::NOWSEAS
		]);
	}

	public function trend_diffs() {
		return $this->get_trend_diffs($this->trend_vars);
	}

	public function trend_avgs() {
		return $this->get_trend_avgs($this->trend_vars);
	}

	public function trend_extremes() {
		return $this->get_recent_values([
			'tmin',	'tmax',
			'rain', 'hrmax',
			'sunhr',
			'pmean',
			'gust',
			'hmin',	'hmax',
			'dmin',	'dmax',
		], [Detail::TODAY, Detail::DAY_MON_AGO, Detail::DAY_YR_AGO]);
	}

	public function trend_cumuls() {
		return $this->get_extremes_daily([
			'tmean' => [MEAN],
			'hmean' => [MEAN],
			'rain' => [MEAN, DAYS],
			'sunhr' => [MEAN, DAYS],
			'wmean' => [MEAN],
			'pmean' => [MEAN],
			'dmean' => [MEAN],
			],[
			Detail::NOWMON, Detail::CUM_MON_AGO, Detail::NOWYR, Detail::CUM_YR_AGO
		]);
	}

}
