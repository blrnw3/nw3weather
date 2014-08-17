<?php
namespace nw3\app\api;

use nw3\app\model\Rain as Rn;
use nw3\app\model\Detail;
use nw3\app\model\Store;
use nw3\app\model\Variable as Vari;
use nw3\app\util\Date;

/**
 * All rain stats n stuff
 */
class Rain extends \nw3\app\core\Api {

	private $rain;
	private $ratemax, $r10max, $hrmax;

	function __construct() {
		parent::__construct();
		$this->rain = new Rn();
		$this->ratemax = new Detail('ratemax');
		$this->r10max = new Detail('r10max');
		$this->hrmax = new Detail('hrmax');
	}

	function current_latest() {
		$now = Store::g();

		$data = [
			'rn' => [
				'descrip' => 'Daily Rain (00-00)',
				'val' => $now->rain,
			],
			'rn10' => [
				'descrip' => 'Rain Last 10 mins',
				'val' => $now->change('rain', 10)
			],
			'rnhr' => [
				'descrip' => 'Rain Last Hour',
				'val' => $now->change('rain', '1h')
			],
			'rn3h' => [
				'descrip' => 'Rain Last 3 hrs',
				'val' => $now->change('rain', '3h')
			],
			'rn6h' => [
				'descrip' => 'Rain Last 6 hrs',
				'val' => $now->change('rain', '6h'),
			],
			'rn24h' => [
				'descrip' => 'Rain Last 24 hrs',
				'val' => $now->change('rain', '24h'),
			],
			'rndur' => [
				'descrip' => 'Rain Duration',
				'val' => $now->hr24->rnduration,
				'type' => Vari::Hours
			],
			'rnrate' => [
				'descrip' => 'Rain Rate',
				'val' => $now->hr24->rnrate,
				'type' => Vari::RainRate
			],
			'rndur24' => [
				'descrip' => 'Past 24hrs Duration',
				'val' => $now->hr24->wethrs,
				'type' => Vari::Hours
			],
			'rnlast' => [
				'descrip' => 'Most Recent Rain',
				'val' => $now->hr24->rnlast,
				'type' => Vari::Laststamp
			],
		];
		// Spell
		$currspell = $this->rain->curr_spell($now->rain > 0);
		$currspell_type = ($now->rain > 0) ? 'Wet' : 'Dry';
		$data += [
			'currspell' => [
				'descrip' => "Consecutive $currspell_type Days",
				'val' => $currspell,
				'type' => Vari::Days
			]
		];
		//Default type
		foreach ($data as &$dat) {
			if(!key_exists('type', $dat)) {
				$dat['type'] = Vari::Rain;
			}
		}
		return $data;
	}

	function recent_values() {
		$now = Store::g();
		$data = [
			'rain' => [Rn::TODAY => [
				'val' => $now->rain
			]] + $this->rain->values(),
			'ratemax' => [Rn::TODAY => [
				'val' => $now->today->max['rate'],
				'dt' => $now->today->timeMax['rate']
			]] + $this->ratemax->values(),
			'hrmax' => [Rn::TODAY => [
				'val' => $now->today->max['rnhr'],
				'dt' => $now->today->timeMax['rnhr']
			]] + $this->hrmax->values(),
			'r10max' => [Rn::TODAY => [
				'val' => $now->today->max['rn10'],
				'dt' => $now->today->timeMax['rn10']
			]] + $this->r10max->values()
		];
		foreach($data as $k => &$dat) {
			$dat = [
				'data' => $dat,
				'type' => $k,
				'descrip' => Vari::$daily[$k]['description']
			];
		}
		return $data;
	}


	function extremes() {
		$spells = $this->rain->spells();
		$data = [
			'rain' => ['data' => $this->rain->totals()],
			'rdays' => ['data' => $this->rain->days()],
			'wettest' => ['data' => $this->rain->extremes()['max'], 'type' => Vari::Rain, 'descrip' => 'Wettest Day'],
			'ratemax' => ['data' => $this->ratemax->extremes()['max']],
			'hrmax' => ['data' => $this->hrmax->extremes()['max']],
			'r10max' => ['data' => $this->r10max->extremes()['max']],
			'wet_spell' =>['data' => $spells['wet'], 'type' => Vari::Days, 'descrip' => 'Longest Wet Spell'],
			'dry_spell' =>['data' => $spells['dry'], 'type' => Vari::Days, 'descrip' => 'Longest Dry Spell'],
		];
		foreach($data as $k => $dat) {
			if(!key_exists('type', $dat)) {
				$data[$k]['type'] = $k;
			}
			if(!key_exists('descrip', $dat)) {
				$data[$k]['descrip'] = Vari::$daily[$k]['description'];
			}
		}
		return $data;
	}

	function extremes_month() {
		$rn = $this->rain->extremes_month();
		$rndays = $this->rain->extreme_days_monthly();
		$data = [
			'wettest' => ['data' => $rn['max'], 'type' => Vari::Rain, 'descrip' => 'Wettest'],
			'driest' => ['data' => $rn['min'], 'type' => Vari::Rain, 'descrip' => 'Driest'],
			'rdays_max' => ['data' => $rndays['max'], 'type' => Vari::Days, 'descrip' => 'Most Rn Days'],
			'rdays_min' => ['data' => $rndays['min'], 'type' => Vari::Days, 'descrip' => 'Fewest Rn Days'],
//			'ratemax' => ['data' => $this->ratemax->extremes_month()['max']],
//			'hrmax' => ['data' => $this->hrmax->extremes()['max']],
//			'r10max' => ['data' => $this->r10max->extremes_month()['max']],
		];
		foreach($data as &$dat) {
			$dat['rec_type'] = Rn::MONTHLY;
		}
		return $data;
	}
	function extremes_year() {
		$rn = $this->rain->extremes_year();
		$rndays = $this->rain->extreme_days_yearly();
		$data = [
			'wettest' => ['data' => $rn['max'], 'type' => Vari::Rain, 'descrip' => 'Wettest'],
			'driest' => ['data' => $rn['min'], 'type' => Vari::Rain, 'descrip' => 'Driest'],
			'rdays_max' => ['data' => $rndays['max'], 'type' => Vari::Days, 'descrip' => 'Most Rn Days'],
			'rdays_min' => ['data' => $rndays['min'], 'type' => Vari::Days, 'descrip' => 'Fewest Rn Days'],
		];
		foreach($data as &$dat) {
			$dat['rec_type'] = Rn::YEARLY;
		}
		return $data;
	}
	function extremes_nday() {
		$rn = $this->rain->extremes_ndays();
//		$rn2 = $this->rain->extremes_ndays_db();
		$data = [
			'wettest' => ['data' => $rn['max'], 'type' => Vari::Rain, 'descrip' => 'Wettest'],
			'driest' => ['data' => $rn['min'], 'type' => Vari::Rain, 'descrip' => 'Driest'],
//			'wettest2' => ['data' => $rn2['max'], 'type' => Vari::Rain, 'descrip' => 'Wettest DB'],
//			'driest2' => ['data' => $rn2['min'], 'type' => Vari::Rain, 'descrip' => 'Driest DB'],
			'rdays_max' => ['data' => $rn['max_days'], 'type' => Vari::Days, 'descrip' => 'Most Rn Days'],
			'rdays_min' => ['data' => $rn['min_days'], 'type' => Vari::Days, 'descrip' => 'Fewest Rn Days'],
		];
		foreach($data as &$dat) {
			$dat['rec_type'] = '\T\o jS M Y';
		}
		return $data;
	}

	function ranks() {
		$rnm = $this->rain->rankings(Detail::MONTHLY, Detail::RECORD);
		$data = [
			'rain' => ['data' => $this->rain->rankings(Detail::DAILY, Detail::RECORD)['max'], 'descrip' => 'Wettest', 'type' => Vari::Rain],
			'hrmax' => ['data' => $this->hrmax->rankings(Detail::DAILY, Detail::RECORD)['max'], 'descrip' => 'Max in 1hr', 'type' => Vari::Rain],
			'rain_m_max' => ['data' => $rnm['max'], 'descrip' => 'Wettest Month', 'type' => Vari::Rain, 'rec_type' => Detail::MONTHLY],
			'rain_m_min' => ['data' => $rnm['min'], 'descrip' => 'Driest Month', 'type' => Vari::Rain, 'rec_type' => Detail::MONTHLY],
			'rain_daily_curr_yr' => ['data' => $this->rain->rankings(Detail::DAILY, Detail::NOWYR)['max'], 'descrip' => 'Wettest [CURR_YR]', 'type' => Vari::Rain, 'period' => Detail::NOWYR],
		];
		foreach($data as &$dat) {
			if(!$dat['period']) {
				$dat['period'] = Detail::RECORD;
			}
		}
		return $data;
	}

	function past_yr_month_tots() {
		return [
			'rn_tot' => $this->rain->past_year_monthly_aggs() + [
				'type' => Vari::Rain,
				'descrip' => 'Total Rain',
				'agg' => true
			],
			'rn_max' => [
				'periods' => $this->rain->past_year_monthly_extremes()['max'],
				'type' => Vari::Rain,
				'descrip' => 'Max Rain',
				'agg' => false,
				'no_anom' => true,
				'rec_type' => 'jS'
			],
		];
	}
	function past_yr_season_tots() {
		$tots = $this->rain->past_year_seasonal_aggs();
		foreach ($tots['periods'] as &$tot) {
			$tot['d'] = Date::$seasons[$tot['season']] .' '. substr($tot['d'], 0, 4);
		}
		return $tots;
	}

	function record_24hrs() {
		return [
			'wettest' => ['data' => $this->rain->record_24hr()['max'], 'descrip' => 'Wettest 24hrs', 'type' => Vari::Rain, 'rec_type' => 'H:i jS M Y']
		];
	}
}
?>
