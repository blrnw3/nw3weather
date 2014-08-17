<?php
namespace nw3\app\api;

use nw3\app\model\Detail;
use nw3\app\model\Store;
use nw3\app\model\Climate;
use nw3\app\model\Variable as Vari;
use nw3\app\util\Date;

/**
 * All temperature stats n stuff
 */
class Temperature extends \nw3\app\core\Api {

	private $tmax, $tmin, $tmean;

	function __construct() {
		parent::__construct();
		$this->tmean = new Detail('tmean');
		$this->tmin = new Detail('tmin');
		$this->tmax = new Detail('tmax');
	}

	function current_latest() {
		$now = Store::g();

		$data = [
			'now' => [
				'descrip' => 'Temperature',
				'val' => $now->temp,
			],
			'trend10m' => [
				'descrip' => 'Temperature Trend / 10m',
				'val' => $now->change('temp', 10),
				'type' => Vari::AbsTemp,
				'sign' => true
			],
			'trend1' => [
				'descrip' => 'Temperature Trend / hr',
				'val' => $now->change('temp', '1h'),
				'type' => Vari::AbsTemp,
				'sign' => true
			],
			'trend3' => [
				'descrip' => 'Temperature Trend / 3hr',
				'val' => $now->change('temp', '3h'),
				'type' => Vari::AbsTemp,
				'sign' => true
			],
			'trend6' => [
				'descrip' => 'Temperature Trend / 6hr',
				'val' => $now->change('temp', '6h'),
				'type' => Vari::AbsTemp,
				'sign' => true
			],
			'trend24' => [
				'descrip' => 'Temperature Trend / 24hr',
				'val' => $now->change('temp', '24h'),
				'type' => Vari::AbsTemp,
				'sign' => true
			],
			'feels' => [
				'descrip' => 'Feels Like',
				'val' => $now->feel,
			],
			'day_min' => [
				'descrip' => 'Daily Minimum (00-00)',
				'val' => $now->today->min['temp'],
				'dt' => $now->today->timeMin['temp']
			],
			'day_max' => [
				'descrip' => 'Daily Maximum (00-00)',
				'val' => $now->today->max['temp'],
				'dt' => $now->today->timeMax['temp']
			],
			'tmean_24hr' => [
				'descrip' => 'Last 24hrs Mean Temperature',
				'val' => $now->hr24->mean['temp'],
			],
			'afhrs' => [
				'descrip' => 'Daily Air Frost Duration',
				'val' => $now->today->frosthrs,
				'type' => Vari::Hours
			],
			'afhrs_24' => [
				'descrip' => '24hr Air Frost Duration',
				'val' => $now->hr24->frosthrs,
				'type' => Vari::Hours
			],
		];
		//Default type
		foreach ($data as &$dat) {
			if(!key_exists('type', $dat)) {
				$dat['type'] = Vari::Temperature;
			}
		}
		return $data;
	}

	function recent_values() {
		$now = Store::g();
		Climate::g()->load();
		$ltas = Climate::g()->daily;
		$data = [
			'tmean' => [Detail::TODAY => [
				'val' => $now->today->mean['temp']
			]] + $this->tmean->values(),
			'tmin' => [Detail::TODAY => [
				'val' => $now->today->min['night'],
				'dt' => $now->today->timeMin['night'],
				'anom' => $now->today->min['night'] - $ltas['tmin'][D_doy]
			]] + $this->tmin->values(),
			'tmax' => [Detail::TODAY => [
				'val' => $now->today->max['day'],
				'dt' => $now->today->timeMax['day'],
				'anom' => $now->today->max['day'] - $ltas['tmax'][D_doy]
			]] + $this->tmax->values(),
		];
		$data['tmean'][Detail::TODAY]['anom'] =
			($data['tmin'][Detail::TODAY]['anom'] + $data['tmax'][Detail::TODAY]['anom']) / 2.0;
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
		$means = $this->tmean->extremes();
		$maxs = $this->tmax->extremes();
		$mins = $this->tmin->extremes();
		$data = [
			'Coldest Night' => ['data' => $mins['min']],
			'Warmest Day' => ['data' => $maxs['max']],
			'Warmest Night' => ['data' => $mins['max']],
			'Coldest Day' => ['data' => $maxs['min']],
			'Coldest 00-00' => ['data' => $means['min']],
			'Warmest 00-00' => ['data' => $means['max']],
			'Overall Mean' => ['data' => $this->tmean->means()],
			'Mean Night-min' => ['data' => $this->tmin->means()],
			'Mean Day-max' => ['data' => $this->tmax->means()],
		];
		foreach($data as $k => $dat) {
			if(!key_exists('type', $dat)) {
				$data[$k]['type'] = Vari::Temperature;
			}
			$data[$k]['descrip'] = $k;
		}
		return $data;
	}

	function extremes_month() {
		$means = $this->tmean->extremes_month();
		$maxs = $this->tmax->extremes_month();
		$mins = $this->tmin->extremes_month();
		$data = [
			'coldest' => ['data' => $means['min'], 'descrip' => 'Coldest Overall'],
			'warmest' => ['data' => $means['max'], 'descrip' => 'Warmest Overall'],
			'coldest_min' => ['data' => $mins['min'], 'descrip' => 'Coldest by Night'],
			'warmest_min' => ['data' => $mins['max'], 'descrip' => 'Warmest by Night'],
			'coldest_max' => ['data' => $maxs['min'], 'descrip' => 'Coldest by Day'],
			'warmest_max' => ['data' => $maxs['max'], 'descrip' => 'Warmest by Day'],
		];
		foreach($data as $k => &$dat) {
			$dat['rec_type'] = Detail::MONTHLY;
			if(!$dat['type']) {
				$dat['type'] = Vari::Temperature;
			}
		}
		return $data;
	}
	function extremes_year() {
		$means = $this->tmean->extremes_year();
		$maxs = $this->tmax->extremes_year();
		$mins = $this->tmin->extremes_year();
		$data = [
			'coldest' => ['data' => $means['min'], 'descrip' => 'Coldest Overall'],
			'warmest' => ['data' => $means['max'], 'descrip' => 'Warmest Overall'],
			'coldest_min' => ['data' => $mins['min'], 'descrip' => 'Coldest by Night'],
			'warmest_min' => ['data' => $mins['max'], 'descrip' => 'Warmest by Night'],
			'coldest_max' => ['data' => $maxs['min'], 'descrip' => 'Coldest by Day'],
			'warmest_max' => ['data' => $maxs['max'], 'descrip' => 'Warmest by Day'],
		];
		foreach($data as $k => &$dat) {
			$dat['rec_type'] = Detail::YEARLY;
			if(!$dat['type']) {
				$dat['type'] = Vari::Temperature;
			}
		}
		return $data;
	}
	function extremes_nday() {
		$means = $this->tmean->extremes_ndays();
		$maxs = $this->tmax->extremes_ndays();
		$mins = $this->tmin->extremes_ndays();
		$data = [
			'coldest' => ['data' => $means['min'], 'descrip' => 'Coldest 00-00'],
			'warmest' => ['data' => $means['max'], 'descrip' => 'Warmest 00-00'],
			'coldest_min' => ['data' => $mins['min'], 'descrip' => 'Coldest by Night'],
			'warmest_min' => ['data' => $mins['max'], 'descrip' => 'Warmest by Night'],
			'coldest_max' => ['data' => $maxs['min'], 'descrip' => 'Coldest by Day'],
			'warmest_max' => ['data' => $maxs['max'], 'descrip' => 'Warmest by Day'],
		];
		foreach($data as &$dat) {
			$dat['rec_type'] = '\T\o jS M Y';
			$dat['type'] = Vari::Temperature;
		}
		return $data;
	}

	function ranks_day() {
		$means = $this->tmean->rankings(Detail::DAILY, Detail::RECORD);
		$maxs = $this->tmax->rankings(Detail::DAILY, Detail::RECORD);
		$mins = $this->tmin->rankings(Detail::DAILY, Detail::RECORD);
		$data = [
			'coldest' => ['data' => $means['min'], 'descrip' => 'Coldest 00-00'],
			'coldest_min' => ['data' => $mins['min'], 'descrip' => 'Coldest by Night'],
			'coldest_max' => ['data' => $maxs['min'], 'descrip' => 'Coldest by Day'],
			'warmest' => ['data' => $means['max'], 'descrip' => 'Warmest 00-00'],
			'warmest_min' => ['data' => $mins['max'], 'descrip' => 'Warmest by Night'],
			'warmest_max' => ['data' => $maxs['max'], 'descrip' => 'Warmest by Day'],
		];
		foreach($data as &$dat) {
			$dat['type'] = Vari::Temperature;
			$dat['period'] = Detail::RECORD;
		}
		return $data;
	}

	function ranks_month() {
		$means = $this->tmean->rankings(Detail::MONTHLY, Detail::RECORD);
		$maxs = $this->tmax->rankings(Detail::MONTHLY, Detail::RECORD);
		$mins = $this->tmin->rankings(Detail::MONTHLY, Detail::RECORD);
		$data = [
			'coldest' => ['data' => $means['min'], 'descrip' => 'Coldest Overall'],
			'coldest_min' => ['data' => $mins['min'], 'descrip' => 'Coldest by Night'],
			'coldest_max' => ['data' => $maxs['min'], 'descrip' => 'Coldest by Day'],
			'warmest' => ['data' => $means['max'], 'descrip' => 'Warmest Overall'],
			'warmest_min' => ['data' => $mins['max'], 'descrip' => 'Warmest by Night'],
			'warmest_max' => ['data' => $maxs['max'], 'descrip' => 'Warmest by Day'],
		];
		foreach($data as &$dat) {
			$dat['type'] = Vari::Temperature;
			$dat['period'] = Detail::RECORD;
			$dat['rec_type'] = Detail::MONTHLY;
		}
		return $data;
	}

	function ranks_day_curr_month() {
		$means = $this->tmean->rankings(Detail::DAILY, Detail::RECORD_M);
		$maxs = $this->tmax->rankings(Detail::DAILY, Detail::RECORD_M);
		$mins = $this->tmin->rankings(Detail::DAILY, Detail::RECORD_M);
		$data = [
			'coldest' => ['data' => $means['min'], 'descrip' => 'Coldest Overall'],
			'coldest_min' => ['data' => $mins['min'], 'descrip' => 'Coldest by Night'],
			'coldest_max' => ['data' => $maxs['min'], 'descrip' => 'Coldest by Day'],
			'warmest' => ['data' => $means['max'], 'descrip' => 'Warmest Overall'],
			'warmest_min' => ['data' => $mins['max'], 'descrip' => 'Warmest by Night'],
			'warmest_max' => ['data' => $maxs['max'], 'descrip' => 'Warmest by Day'],
		];
		foreach($data as &$dat) {
			$dat['type'] = Vari::Temperature;
			$dat['period'] = Detail::RECORD_M;
		}
		return $data;
	}

	function past_yr_month_avg_extremes() {
		$data = [
			'mean' => $this->tmean->past_year_monthly_aggs() + ['descrip' => 'Overall Mean'],
			'min' => $this->tmin->past_year_monthly_aggs() + ['descrip' => 'Mean Night-min'],
			'max' => $this->tmax->past_year_monthly_aggs() + ['descrip' => 'Mean Day-max'],
		];
		foreach($data as &$dat) {
			$dat['type'] = Vari::Temperature;
			$dat['agg'] = true;
		}
		return $data;
	}
	function past_yr_season_tots() {
		$tots = $this->tmean->past_year_seasonal_aggs();
		foreach ($tots['periods'] as &$tot) {
			$tot['d'] = Date::$seasons[$tot['season']] .' '. substr($tot['d'], 0, 4);
		}
		return $tots;
	}

	function record_24hrs() {
		return [
			'wettest' => ['data' => $this->tmean->record_24hr()['max'], 'descrip' => 'Wettest 24hrs', 'type' => Vari::Temperature, 'rec_type' => 'H:i jS M Y']
		];
	}
}
?>
