<?php
namespace nw3\app\api;

use nw3\app\model\Detail;

/**
 * All Pressure stats n stuff
 */
class Pressure extends Datadetail {

	function __construct() {
		parent::__construct([
			'pmean',
			'pmin',
			'pmax',
		]);
	}

	function extremes_daily() {
		return $this->get_extremes_daily([
			'pmin' => [MIN],
			'pmax' => [MAX],
			'pmean' => [MEAN],
		]);
	}

	function extremes_monthly() {
		return $this->get_extremes_monthly([
			'pmean' => [MIN, MAX],
		]);
	}

	function extremes_yearly() {
		return $this->get_extremes_yearly([
			'pmean' => [MIN, MAX],
		]);
	}

	function extremes_nday() {
		return $this->get_extremes_nday([
			'pmean' => [MIN, MAX],
		]);
	}

	function ranks_daily() {
		return $this->get_ranks([
			'pmin' => [[Detail::DAILY, Detail::RECORD, MIN]],
			'pmax' => [[Detail::DAILY, Detail::RECORD, MAX]],
		]);
	}
	function ranks_monthly() {
		return $this->get_ranks([
			'pmean' => [
				[Detail::MONTHLY, Detail::RECORD, MINMAX]
			]
		]);
	}

	public function ranks_daily_past_year() {
		return $this->get_ranks([
			'pmin' => [[Detail::DAILY, 365, MIN]],
			'pmax' => [[Detail::DAILY, 365, MAX]],
		]);
	}

	public function past_yr_monthly_extremes() {
		return $this->get_past_yr_month_extremes([
			'pmin' => [MIN],
			'pmax' => [MAX],
		]);
	}
//
//	function past_yr_season_aggs() {
//		return $this->get_past_yr_season_aggs([
//			'wmean' => [MEAN]
//		]);
//	}

}
?>
