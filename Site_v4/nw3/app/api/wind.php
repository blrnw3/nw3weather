<?php
namespace nw3\app\api;

use nw3\app\util\Date;
use nw3\app\model\Detail;

/**
 * All Wind stats n stuff
 */
class Wind extends \nw3\app\core\Api {

	function __construct() {
		parent::__construct([
			'wmean',
			'wmax',
			'gust',
			'wdir',
			'w10max'
		]);
	}

	function extremes_daily() {
		return $this->get_extremes_daily([
			'wmean' => [MIN, MAX, MEAN],
			'wmax' => [MAX],
			'gust' => [MAX],
			'w10max' => [MAX]
		]);
	}

	function extremes_monthly() {
		return $this->get_extremes_monthly([
			'wmean' => [MIN, MAX],
		]);
	}

	function extremes_yearly() {
		return $this->get_extremes_yearly([
			'wmean' => [MIN, MAX],
		]);
	}

	function extremes_nday() {
		return $this->get_extremes_nday([
			'wmean' => [MIN, MAX],
		]);
	}

	function ranks_daily() {
		return $this->get_ranks([
			'wmean' => [
				[Detail::DAILY, Detail::RECORD, MINMAX],
			],
			'wmax' => [[Detail::DAILY, Detail::RECORD, MAX]],
			'gust' => [[Detail::DAILY, Detail::RECORD, MAX]],
			'w10max' => [[Detail::DAILY, Detail::RECORD, MAX]]
		]);
	}
	function ranks_monthly() {
		return $this->get_ranks([
			'wmean' => [
				[Detail::MONTHLY, Detail::RECORD, MINMAX]
			]
		]);
	}

	public function ranks_daily_past_year() {
		return $this->get_ranks([
			'wmean' => [[Detail::DAILY, 365, MINMAX]],
			'wmax' => [[Detail::DAILY, 365, MAX]],
			'gust' => [[Detail::DAILY, 365, MAX]],
		]);
	}

	function past_yr_monthly_aggs() {
		return array_merge($this->get_past_yr_month_aggs([
			'wmean' => [MEAN]
		]), $this->get_past_yr_month_extremes([
			'wmean' => [MIN, MAX],
			'wmax' => [MAX],
			'gust' => [MAX]
		]));
	}

	public function past_yr_monthly_extremes() {
		return null;
	}

	function past_yr_season_aggs() {
		return $this->get_past_yr_season_aggs([
			'wmean' => [MEAN]
		]);
	}

	function monthly_windrose_path() {
		$prefix = Date::is_first_of_month() ? date('Yn', D_yest) : '';
		return "{$prefix}windrose.gif";
	}
	function annual_windrose_path() {
		$suffix = (D_month > 1) ? 'year' : '';
		return "windrose$suffix.gif";
	}
}
?>
