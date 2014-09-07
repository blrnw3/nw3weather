<?php
namespace nw3\app\api;

use nw3\app\model\Detail;
use nw3\app\model\Variable as Vari;

/**
 * All rain stats n stuff
 */
class Rain extends \nw3\app\core\Api {

	function __construct() {
		parent::__construct([
			'rain',
			'hrmax',
			'r10max',
			'ratemax'
		]);
	}

	public function extremes_daily() {
		return $this->get_extremes_daily([
			'rain' => [MEAN, MAX, DAYS, SPELL, SPELL_INV],
			'hrmax' => [MAX],
			'r10max' => [MAX],
			'ratemax' => [MAX],
		]);
	}

	function extremes_monthly() {
		return $this->get_extremes_monthly([
			'rain' => [MIN, MAX, DAYS],
		]);
	}

	function extremes_yearly() {
		return $this->get_extremes_yearly([
			'rain' => [MIN, MAX, DAYS],
		]);
	}
	function extremes_nday() {
		return $this->get_extremes_nday([
			'rain' => [MIN, MAX, DAYS],
		]);
	}

	function ranks_daily() {
		return $this->get_ranks([
			'rain' => [[Detail::DAILY, Detail::RECORD, MAX]],
			'hrmax' => [[Detail::DAILY, Detail::RECORD, MAX]],
		]);
	}

	function ranks_monthly() {
		return $this->get_ranks([
			'rain' => [[Detail::MONTHLY, Detail::RECORD, MINMAX]],
		]);
	}

	public function ranks_daily_past_year() {
		return $this->get_ranks([
			'rain' => [[Detail::DAILY, 365, MAX]],
			'hrmax' => [[Detail::DAILY, 365, MAX]],
		]);
	}

	function past_yr_monthly_aggs() {
		return $this->get_past_yr_month_aggs(['rain' => [MEAN, COUNT]]);
	}

	public function past_yr_monthly_extremes() {
		return $this->get_past_yr_month_extremes(['rain' => [MAX]]);
	}

	function past_yr_season_aggs() {
		return $this->get_past_yr_season_aggs(['rain' => [MEAN]]);
	}

	function record_24hrs() {
		return [
			'wettest' => ['data' => $this->vars['rain']->record_24hr()['max'], 'descrip' => 'Wettest 24hrs', 'type' => Vari::Rain, 'rec_type' => 'H:i jS M Y']
		];
	}
}
?>
