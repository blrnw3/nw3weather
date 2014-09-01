<?php
namespace nw3\app\api;

use nw3\app\model\Detail;

/**
 * All Relative Humidity stats n stuff
 */
class Humidity extends \nw3\app\core\Api {

	function __construct() {
		parent::__construct([
			'Hmin',
			'Hmax',
			'Hmean'
		]);
	}

	function trend_diffs() {
		$this->get_trend_diffs($this->vars);
	}

	public function current_latest() {
		return $this->get_current_latest($this->vars);
	}

	function recent_values() {
		return $this->get_recent_values($this->vars);
	}

	function extremes_daily() {
		return $this->get_extremes_daily();
	}

	function extremes_monthly() {
		return $this->get_extremes_monthly();
	}

	function extremes_yearly() {
		return $this->get_extremes_yearly();
	}

	function extremes_nday() {
		return $this->get_extremes_nday($this->vars);
	}

	function ranks_day() {
		return $this->get_ranks([
			self::ALL => [Detail::DAILY, Detail::RECORD, MINMAX]
		]);
	}

	function ranks_month() {
		return $this->get_ranks([
			self::ALL => [Detail::MONTHLY, Detail::RECORD, MINMAX]
		]);
	}

	function ranks_day_curr_month() {
		return $this->get_ranks([
			self::ALL => [Detail::DAILY, Detail::RECORD_M, MINMAX]
		]);
	}

	function past_yr_month_aggs() {
		$this->get_past_yr_month_aggs();
	}

	function past_yr_month_extremes() {
		$this->get_past_yr_month_extremes();
	}

	function past_yr_season_aggs() {
		$this->get_past_yr_season_aggs();
	}

	function record_24hrs() {
		throw new \BadMethodCallException('Unimplemented');
	}
}
?>
