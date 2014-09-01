<?php
namespace nw3\app\api;

use nw3\app\util\Date;
use nw3\app\model\Detail;
use nw3\app\model\Store;
use nw3\app\model\Variable as Vari;

/**
 * All temperature stats n stuff
 */
class Wind extends \nw3\app\core\Api {

	private $wmean, $gust, $wmax, $wdir, $w10m;
	protected $vars;

	function __construct() {
		parent::__construct(Vari::Wind, 'wind');

		$this->wmean = new Detail('wmean', 'wind');
		$this->wmax = new Detail('wmax', 'wind');
		$this->gust = new Detail('gust', 'gust');
		$this->wdir = new Detail('wdir', 'wdir');
		$this->w10m = new Detail('w10max', 'w10m');
//		$this->vars = [$this->wmax, $this->gust, $this->wmean];
	}

	function current_latest() {
		$now = Store::g();
		$data = [
			'gust' => [
				'descrip' => 'Gust',
				'val' => $now->gust,
			],
			'speed' => [
				'descrip' => 'Speed',
				'val' => $now->wind,
			],
			'dir' => [
				'descrip' => 'Direction',
				'val' => $now->wdir,
				'type' => 'wdir'
			],
			'bft' => [
				'descrip' => 'Beaufort Speed',
				'val' => Vari::bft($now->wind),
				'type' => Vari::None
			],
			'maxhr_gst' => [
				'descrip' => 'Max Gust Last Hour',
				'val' => $now->hr24->maxhrgst,
			],
			'10m' => [
				'descrip' => '10-min Speed',
				'val' => $now->w10m,
			],
		];
		$this->assign_default_type($data);
		return $data;
	}

	function recent_values() {
		$data = [
			'gust' => $this->gust->values(),
			'wmax' => $this->wmax->values(),
			'w10max' => $this->w10m->values(),
			'wdir' => $this->wdir->values(),
			'wmean' => $this->wmean->values(),
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

	function trend_avgs() {
		$data = [
			'wmean' => $this->trend_avgs_single(),
			'wdir' => Store::g()->trend_avg_wdir(self::$trend_avg_periods)
		];
		foreach($data as $k => &$dat) {
			$dat = [
				'data' => $dat,
				'type' => $k,
				'descrip' => Vari::$daily[$k]['description']
			];
		}
		return ['data' => $data, 'periods' => self::$trend_avg_periods];
	}

	function extremes() {
		$means = $this->wmean->extremes();
		$maxs = $this->gust->extremes();
		$mins = $this->wmax->extremes();
		$w10ms = $this->w10m->extremes();
		$data = [
			'Gust Max' => ['data' => $maxs['max']],
			'Speed Max' => ['data' => $mins['max']],
			'10m Max' => ['data' => $w10ms['max']],
			'Calmest' => ['data' => $means['min']],
			'Windiest' => ['data' => $means['max']],
			'Mean Speed' => ['data' => $this->wmean->means()],
		];
		foreach($data as $k => $dat) {
			if(!key_exists('type', $dat)) {
				$data[$k]['type'] = $this->default_type;
			}
			$data[$k]['descrip'] = $k;
		}
		return $data;
	}

	function extremes_month() {
		$means = $this->wmean->extremes_month();
		$data = [
			'coldest' => ['data' => $means['min'], 'descrip' => 'Calmest Mean'],
			'warmest' => ['data' => $means['max'], 'descrip' => 'Highest  Mean'],
		];
		foreach($data as $k => &$dat) {
			$dat['rec_type'] = Detail::MONTHLY;
			$dat['type'] = $this->default_type;
		}
		return $data;
	}
	function extremes_year() {
		$means = $this->wmean->extremes_year();
		$data = [
			'coldest' => ['data' => $means['min'], 'descrip' => 'Calmest Mean'],
			'warmest' => ['data' => $means['max'], 'descrip' => 'Highest  Mean'],
		];
		foreach($data as $k => &$dat) {
			$dat['rec_type'] = Detail::YEARLY;
			$dat['type'] = $this->default_type;
		}
		return $data;
	}
	function extremes_nday() {
		$means = $this->wmean->extremes_ndays();
		$data = [
			'coldest' => ['data' => $means['min'], 'descrip' => 'Calmest'],
			'warmest' => ['data' => $means['max'], 'descrip' => 'Windiest'],
		];
		foreach($data as &$dat) {
			$dat['rec_type'] = '\T\o jS M Y';
			$dat['type'] = $this->default_type;
		}
		return $data;
	}

	function ranks_day() {
		$means = $this->wmean->rankings(Detail::DAILY, Detail::RECORD);
		$maxs = $this->gust->rankings(Detail::DAILY, Detail::RECORD);
		$mins = $this->wmax->rankings(Detail::DAILY, Detail::RECORD);
		$w10m = $this->w10m->rankings(Detail::DAILY, Detail::RECORD);
		$means_curr = $this->wmean->rankings(Detail::DAILY, Detail::$periodsn[2]);
		$data = [
			'1' => ['data' => $means['min'], 'descrip' => 'Calmest'],
			'2' => ['data' => $means['max'], 'descrip' => 'Windiest'],
			'3' => ['data' => $mins['max'], 'descrip' => 'Highest Speed'],
			'4' => ['data' => $maxs['max'], 'descrip' => 'Highest Gust'],
			'4.5' => ['data' => $w10m['max'], 'descrip' => 'Max 10m'],
			'5' => ['data' => $means_curr['min'], 'descrip' => 'Yr Calmest', 'period' => Detail::NOWYR],
			'6' => ['data' => $means_curr['max'], 'descrip' => 'Yr Windiest', 'period' => Detail::NOWYR],
		];
		foreach($data as &$dat) {
			$dat['type'] = $this->default_type;
			if(!$dat['period']) {
				$dat['period'] = Detail::RECORD;
			}
		}
		return $data;
	}
	function ranks_month() {
		$means_monthly = $this->wmean->rankings(Detail::MONTHLY, Detail::RECORD);
		$data = [
			'1' => ['data' => $means_monthly['min'], 'descrip' => 'Calmest Month', 'rec_type' => Detail::MONTHLY],
			'2' => ['data' => $means_monthly['max'], 'descrip' => 'Windiest Month', 'rec_type' => Detail::MONTHLY],
		];
		foreach($data as &$dat) {
			$dat['type'] = $this->default_type;
			if(!$dat['period']) {
				$dat['period'] = Detail::RECORD;
			}
		}
		return $data;
	}

	function past_yr_month_avgs_extrms() {
		$means = $this->wmean->past_year_monthly_extremes();
		$maxs = $this->gust->past_year_monthly_extremes();
		$mins = $this->wmax->past_year_monthly_extremes();
		$data = [
			'mean' => $this->wmean->past_year_monthly_aggs() + ['descrip' => 'Mean Speed'],
			'coldest' => ['periods' => $means['min'], 'descrip' => 'Calmest Day'],
			'warmest' => ['periods' => $means['max'], 'descrip' => 'Windiest Day'],
			'warmest_min' => ['periods' => $mins['max'], 'descrip' => 'Highest Speed'],
			'warmest_max' => ['periods' => $maxs['max'], 'descrip' => 'Highest Gust'],
		];
		foreach($data as &$dat) {
			$dat['type'] = $this->default_type;
			$dat['agg'] = true; // Not interested in date for now
		}
		return $data;
	}
	function past_yr_season_means() {
		$data = [
			'mean' => $this->wmean->past_year_seasonal_aggs() + ['descrip' => 'Mean Speed'],
		];
		foreach($data as &$dat) {
			$dat['type'] = $this->default_type;
			$dat['agg'] = true; // Not interested in date for now
		}
		return $data;
	}

	function record_24hrs() {
		throw new \BadMethodCallException('Unimplemented');
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
