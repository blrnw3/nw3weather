<?php
namespace nw3\app\model;

use nw3\app\model\Variable;
use nw3\app\util\Date;
use nw3\app\core\Db;
use nw3\app\core\Alias;
use nw3\app\model\Climate;

/**
 * Description of Detail
 *
 * @author Ben
 */
class Detail {

	const LIVE = 'live';
	const TODAY = 'today';
	const YESTERDAY = 'yest';
	const NOWMON = 'mon';
	const NOWYR = 'yr';
	const NOWSEAS = 'seas';

	const RECORD = 'rec';
	const RECORD_D = 'rec_d';
	const RECORD_M = 'rec_m';
	const RECORD_S = 'rec_s'; # TODO - period for all days from current season

	const DAY_YR_AGO = 'day_yr_ago';
	const DAY_MON_AGO = 'day_mon_ago';
	const CUM_YR_AGO = 'cum_yr_ago';
	const CUM_MON_AGO = 'cum_mon_ago';

	/** Record types */
	const DAILY = 'daily';
	const MONTHLY = 'monthly';
	const YEARLY = 'yearly';

	const TBL_DAILY = 'daily';

	static $periodsn = [7, 31, 365];

	public $num_records;

	protected $yest;
	protected $mon_st;
	protected $yr_st;
	protected $seas_st;
	protected $mon_ago;
	protected $yr_ago;
	protected $mon_ago_st;
	protected $yr_ago_st;
	protected $yr_ago_mon_st;
	protected $yr_ago_seas_st;

	protected $query_count;
	protected $query_agg;
	protected $query_month;
	protected $query_year;
	protected $query_val;
	protected $query_anom;

	protected $period_lengths;

	protected $var;
	protected $db;
	protected $colname;
	protected $anom_colname;
	protected $varname;
	protected $climate;
	protected $aggtype; //Sum or mean
	protected $summable;
	protected $anomable;

	public static $periods = [
		self::TODAY => [
			'multi' => false,
			'descrip' => 'Today',
			'today' => true
		],
		self::YESTERDAY => [
			'multi' => false,
			'descrip' => 'Yesterday'
		],
		self::NOWMON => [
			'multi' => true,
			'descrip' => 'Month',
			'format' => 'jS',
			'has_spell' => true,
		],
		self::NOWYR => [
			'multi' => true,
			'month_recs' => true,
			'descrip' => 'Year',
			'format' => 'jS M',
			'mon_format' => 'M',
			'has_spell' => true,
		],
		self::NOWSEAS=> [
			'multi' => true,
			'descrip' => 'Season',
			'format' => 'jS M',
			'has_spell' => true
		],
		self::RECORD => [
			'multi' => true,
			'record' => true,
			'month_recs' => true,
			'descrip' => 'Overall',
			'has_spell' => true,
			'ranknum' => 10,
		],
		self::RECORD_D => [
			'multi' => true,
			'record' => true,
			'descrip' => 'This Date (day)',
			'format' => 'Y',
			'ranknum' => 5,
		],
		self::RECORD_M => [
			'multi' => true,
			'record' => true,
			'month_recs' => true,
			'descrip' => 'This Date (month)',
			'format' => '\D\a\y j Y',
			'mon_format' => 'Y',
			'has_spell' => true,
			'ranknum' => 5,
		],
		self::CUM_MON_AGO => [
			'multi' => true,
			'descrip' => 'Cum Mon Ago',
			'format' => '\D\a\y j'
		],
		self::CUM_YR_AGO => [
			'multi' => true,
			'descrip' => 'Cum Yr Ago',
		],
		self::DAY_MON_AGO => [
			'multi' => false,
			'descrip' => 'Day Mon Ago',
			'format' => 'jS M'
		],
		self::DAY_YR_AGO => [
			'multi' => false,
			'descrip' => 'Day Yr Ago',
		],
	];

	const VAL = 'val'; # e.g. Tmin today, Rain yesterday
	const HIGH = 'high'; # e.g. Highest Tmin this month, highest Rain this year
	const LOW = 'low';
	const AGG = 'agg'; # e.g. Mean Tmax this month, total Sun this year, Rain days last 31 days
	const HIGH_M = 'high_m'; # e.g. Lowest Monthly_Mean(Tmin) this year, highest Month_sum(Rain) ever
	const LOW_M = 'low_m';
	public static $record_types = [

	];

	public static function initialise() {
		foreach (self::$periodsn as $n) {
			self::$periods[$n] = [
				'multi' => true,
				'month_recs' => $n > 99,
				'descrip' => "$n days",
				'format' => ($n > 30) ? 'jS M' : 'jS',
				'has_spell' => $n > 30,
			];
		}
		self::$periods[self::RECORD_D]['descrip'] = date('jS M', D_now);
		self::$periods[self::RECORD_M]['descrip'] = D_monthname;
//		self::$periods[self::RECORD_S]['descrip'] = D_seasonname;
	}

	/**
	 * @param type $varname as in the db and Variable Class
	 */
	function __construct($varname, $graph=false) {
		$this->db = Db::g();
		$this->climate = Climate::g();
		$this->varname = $varname;
		$this->var = Variable::$daily[$varname];
		$this->colname = $this->var['db_field'] ? $this->var['db_field'] : $varname;
		$this->anom_colname = $this->var['db_field_anom'] ? $this->var['db_field_anom'] : "a_$this->colname";
		$this->summable = $this->var['summable'];
		$this->anomable = $this->var['anomable'];
		$this->aggtype = $this->var['summable'] ? Db::SUM : Db::AVG;


		if(!$this->var) {
			throw new \Exception("$varname is not a valid variable");
		}

		$this->num_records = $this->db->query($this->colname)->count();

		# Useful db datetimes
		$this->rec_st = "'2009-01-01'";
		$this->yest = Db::dt(D_yest);
		$this->mon_st = $this->DbMkdate(false, 1);
		$this->yr_st = $this->DbMkdate(1, 1);
		$this->seas_st = $this->DbMkdate(Date::get_current_season_start_month(), 1);
		/**
		 * !!! AMBIGUITY WARNING !!!
		 * Using this may produce undesired effects. Check useage EVERY time, and consider the alternative.
		 * The behaviour of 'a month ago' is technically undefined for multiple dates, due to unequal month lengths
		 * e.g. if it's the 31st march, this will produce either 2nd or 3rd March, NOT 28/29 Feb as might be expected
		 * To produce the latter result, consider "strtotime('last day of -1 month', [timestamp])"
		 */
		$this->mon_ago = $this->DbMkdate(D_month-1);
		/**
		 * !!! AMBIGUITY WARNING !!!
		 * Using this may produce undesired effects. Check useage EVERY time, and consider the alternative.
		 * The behaviour of 'a year ago' is technically undefined if it's 29th Feb, due to leap years
		 * The following useage results in 1st March for such a scenario, NOT 28th Feb as might be expected
		 * To produce the latter result, consider "D_now - 86400 * [len_of_curr_yr]"
		 */
		$this->yr_ago = $this->DbMkdate(D_month, D_day, D_year-1);

		$this->mon_ago_st = $this->DbMkdate(D_month-1, 1);
		$this->yr_ago_st = $this->DbMkdate(1, 1, D_year-1);
		$this->yr_ago_mon_st = $this->DbMkdate(D_month, 1, D_year-1);
		$this->yr_ago_seas_st = $this->DbMkdate(Date::get_current_season_start_month(), 1, D_year-1);

		# Useful Query fragments
		$this->query_count = new Alias(Db::count($this->colname));
		$this->query_month = new Alias('MONTH(d)', 'm');
		$this->query_year = new Alias('YEAR(d)', 'y');
		$this->query_val = new Alias($this->colname, 'val');
		$this->query_anom = new Alias($this->anom_colname, 'anom');

		$query_agg = new Alias(Db::agg($this->colname, $this->aggtype));
		$query_anom_sum = Db::sum($this->colname) .'/'. Db::sum("$this->colname - $this->anom_colname");
		$query_anom_avg = Db::avg($this->anom_colname);
		$this->query_agg = [$query_agg];
		if($this->anomable && !$graph) {
			$this->query_agg[] = new Alias($this->summable ? $query_anom_sum : $query_anom_avg, 'anom');
		}
		if($graph) return;
		# Period lengths
		$this->period_lengths = [
			self::RECORD => $this->num_records,
			self::NOWMON => D_day,
			self::NOWSEAS => Date::get_current_season_days_elapsed(),
			self::NOWYR => D_doy + 1,
			self::CUM_MON_AGO => D_day,
			self::CUM_YR_AGO => D_doy + 1,
			self::RECORD_D => $this->db->query($this->colname)->filter($this->get_date_filter(self::RECORD_D))->count(),
			self::RECORD_M => $this->db->query($this->colname)->filter($this->get_date_filter(self::RECORD_M))->count()
		];
		foreach (self::$periodsn as $n) {
			$this->period_lengths[$n] = $n;
		}
	}

	### Graphy stuff ###

	public function monthly($param=false) {
		$st = $this->DbMkdate(D_month+1, 1, D_year-1);
		$q = $this->db->query('d', $this->query_agg)
			->filter("d >= $st")
			->group('YEAR(d), MONTH(d)')
			->order(Db::ASC)
		;
		return $this->graph_output($q);
	}

	public function daily($param=false) {
		$st = $this->DbMkdate(D_month, D_day-31, D_year);
		$q = $this->db->query('d', $this->query_val)
			->filter("d >= $st")
		;
		return $this->graph_output($q);
	}

	protected function graph_output($query) {
		$data = [
			'values' => [],
			'group' => $this->var
		];
		$labs = [];
		foreach ($query->all() as $record) {
			$data['values'][] = $record['val'];
			$labs[] = new \DateTime($record['d']);
		}
		return [
			'data' => [$data],
			'labels' => $labs
		];
	}


	### Data-y stuff ###

	public function values() {
		$data = [];
		foreach (self::get_periods_single() as $period) {
			$val = $this->period_extreme($period);
			$data[$period] = [
				'val' => $val['val'],
				'dt' => $val['t']
			];
			if($val['anom']) {
				$data[$period]['anom'] = $val['anom'];
			}
		}
		return $data;
	}

	public function extremes() {
		$data = ['max' => [], 'min' => []];
		foreach (self::get_periods('multi') as $period) {
			$max = $this->period_extreme($period, Db::MAX);
			$data['max'][$period] = [
				'val' => $max['val'],
				'dt' => $max['d']
			];
			if($max['anom']) {
				$data['max'][$period]['anom'] = $max['anom'];
			}
			if($this->var['minmax']) {
				$min = $this->period_extreme($period, Db::MIN);
				$data['min'][$period] = [
					'val' => $min['val'],
					'dt' => $min['d']
				];
				if($min['anom']) {
					$data['min'][$period]['anom'] = $min['anom'];
				}
			}
		}
		return $data;
	}
	public function extremes_month() {
		$data = ['max' => [], 'min' => []];
		foreach (self::get_periods('month_recs') as $period) {
			$max = $this->period_extreme_month($period, Db::MAX);
			$data['max'][$period] = [
				'val' => $max['val'],
				'dt' => $max['d']
			];
			$min = $this->period_extreme_month($period, Db::MIN);
			$data['min'][$period] = [
				'val' => $min['val'],
				'dt' => $min['d']
			];
		}
		return $data;
	}
	public function extremes_year() {
		$data = ['max' => [], 'min' => []];
		$period = self::RECORD;
		$max = $this->period_extreme_year(Db::MAX);
		$data['max'][$period] = [
			'val' => $max['val'],
			'dt' => $max['y']
		];
		$min = $this->period_extreme_year(Db::MIN);
		$data['min'][$period] = [
			'val' => $min['val'],
			'dt' => $min['y']
		];
		return $data;
	}

	public function means() {
		$data = [];
		foreach (self::get_periods('multi') as $period) {
			$data[$period] = $this->period_agg($period);
		}
		return $data;
	}

	public function rankings($type, $period) {
		if(!self::$periods[$period]) {
			throw new \Exception("'$period' is not a valid period");
		}
		switch($type) {
			case self::DAILY:
				$fn = 'period_extreme';
				break;
			case self::MONTHLY:
				$fn = 'period_extreme_month';
				break;
			default:
				throw new \Exception("'$type' is not a valid rank type. Must be D, M, or Y");
		}

		$data = ['max' => [], 'min' => []];
		$rank_num = self::$periods[$period]['ranknum'];

		$max = $this->$fn($period, Db::MAX, $rank_num);
		foreach ($max as $val) {
			$data['max'][] = [
				'val' => $val['val'],
				'dt' => $val['d']
			];
		}
		if($this->var['minmax'] || $type !== self::DAILY) {
			$min = $this->$fn($period, Db::MIN, $rank_num);
			foreach ($min as $val) {
				$data['min'][] = [
					'val' => $val['val'],
					'dt' => $val['d']
				];
			}
		}
		return $data;
	}

	public function past_year_monthly_aggs() {
		$q = $this->db->query('d', $this->query_agg)
			->filter("d < $this->mon_st", "d >= $this->yr_ago_mon_st")
		;
		return [
			'annual' => $q->one(),
			'periods' => $q->group('MONTH(d)')->order(Db::ASC)->all()
		];
	}

	public function past_year_monthly_extremes() {
		$data = ['max' => [], 'min' => []];
		foreach(Date::$monthsn as $i => $m) {
			$st = $this->DbMkdate(D_month + $i, 1, D_year-1);
			$en = $this->DbMkdate(D_month + $m, 1, D_year-1);
			$q = $this->db->query($this->query_val, 'd', $this->query_anom)
				->filter("d < $en", "d >= $st")
			;
			$data['max'][] = $q->extreme(Db::MAX)->one();
			if($this->var['minmax']) {
				$data['min'][] = $q->extreme(Db::MIN)->one();
			}
		}
		return $data;
	}

	public function past_year_seasonal_aggs() {
		$q = $this->db->query('d', $this->query_agg, Db::SEASON_COL)
			->filter("d < $this->seas_st", "d >= $this->yr_ago_seas_st")
			->group('season')
			->order(Db::ASC)
		;
		$all = $q->all();
		$tot = array_reduce($all, function($all, $v){return $v['val'] + $all;}, 0);
		$tot_anom = $tot / $this->climate->annual[$this->varname]['sum'];
		return [
			'periods' => $all,
			'annual' => ['val' => $tot, 'anom' => $tot_anom]
		];
	}

	public function extremes_ndays_db() {
		$data = ['max' => [], 'min' => []];
		foreach (self::$periodsn as $period) {
			$data['max'][$period] = $this->extreme_nday_agg($period, Db::MAX);
			$data['min'][$period] = $this->extreme_nday_agg($period, Db::MIN);
		}
		return $data;
	}

	public function record_24hr() {
		return null;
	}

	public function extremes_ndays() {
		$data = ['max' => [], 'min' => []];
		$all = $this->db->query('d', $this->colname)->order(Db::ASC)->all();
		foreach(self::$periodsn as $spell_len) {
			$spells = $this->nday_avg_extremes($spell_len, $all);
			$data['min'][$spell_len] = $spells['min'];
			$data['max'][$spell_len] = $spells['max'];
		}
		return $data;
	}

	/**
	 * Calculate sum/avg over quantity for given period
	 * @param type $period Must be one of pre-defined set
	 * @return float summation
	 */
	protected function period_agg($period) {
		return $this->db->query($this->query_agg)
			->filter($this->get_date_filter($period))
			->one();
	}

	protected function period_extreme($period, $extrm_type=null, $num=1) {
		$fields = [$this->query_val];
		$q = $this->db->query();
		if(!$this->var['spread'] && $num === 1) {
			$fields[] = Db::time_field($this->colname);
		}
		if($this->anomable && !$this->summable) {
			$fields[] = $this->query_anom;
		}
		if(self::$periods[$period]['multi']) {
			$fields[] = 'd';
			if($num === 1) {
				$q = $q->extreme($extrm_type);
			}
		}
		$q = $q->fields($fields)->filter($this->get_date_filter($period));
		if($num === 1) {
			return $q->one();
		}
		return $q->limit($num)->order(($extrm_type === Db::MAX) ? Db::DESC : Db::ASC)->all();
	}

	/**
	 * Calculate count of valid values for given period
	 * @param mixed $period Must be one of pre-defined set
	 * @param string $filter [=null] db condition to filter values for count (e.g. '> 1')
	 * @return int count that match $filter within $period
	 */
	protected function period_count($period, $filter=null) {
		$val_filter = ($filter === null) ? null : "$this->colname $filter";
		return $this->db->query($this->colname)
			->filter($this->get_date_filter($period), $val_filter)
			->count()
		;
	}

	protected function period_count_month_extreme($period, $extrm_type, $filter=null) {
		$val_filter = ($filter === null) ? null : "$this->colname $filter";
		$q = $this->db->query($this->query_count, $this->query_year, $this->query_month, 'd')
			->filter($this->get_date_filter_month($period), $val_filter)
			->group('y', 'm')
			->extreme($extrm_type)
			->one();
		return $q;
	}
	protected function period_count_year_extreme($extrm_type, $filter=null) {
		$val_filter = ($filter === null) ? null : "$this->colname $filter";
		$q = $this->db->query($this->query_count, $this->query_year)
			->filter('d < '.$this->yr_st, $val_filter)
			->group('y')
			->extreme($extrm_type)
		;
		return $q->one();
	}

	/**
	 * Min or Max monthly mean/sum for all months but the current one
	 * @param boolean $is_high set true to get max, false to get min
	 * @return [int,int,float] year, month, val
	 */
	protected function period_extreme_month($period, $extrm_type=null, $num=1) {
		$q = $this->db->query($this->query_agg, $this->query_year, $this->query_month, 'd')
			->filter($this->get_date_filter_month($period))
			->group('y', 'm')
		;
		if($num === 1) {
			return $q->extreme($extrm_type)->one();
		}
		return $q->order(($extrm_type === Db::MAX) ? Db::DESC : Db::ASC)->limit($num)->all();
	}

	/**
	 * Min or Max monthly mean/sum for all years but the current one
	 * @param boolean $is_high set true to get max, false to get min
	 * @return [int,float] year, val
	 */
	protected function period_extreme_year($extrm_type=null, $num=1) {
		$q = $this->db->query($this->query_agg, $this->query_year)
			->filter('d < '. $this->yr_st)
			->group('y')
		;
		if($num === 1) {
			return $q->extreme($extrm_type)->one();
		}
		return $q->order(($extrm_type === Db::MAX) ? Db::DESC : Db::ASC)->limit($num)->all();
	}

	protected function extreme_nday_agg($n, $extrm_type=null) {
		/*
		 * SUPER expensive compared to evaluating in code (~200ms vs. ~5ms) but fine for debugging
		 */
		$n -= 1;
		$q = $this->db->query(new Alias(Db::agg('b.'. $this->colname, $this->aggtype), 'val'), new Alias('daily.d', 'dt'))
			->join('daily b', "datediff(daily.d, b.d) BETWEEN 0 AND $n")
			->filter("daily.d > $this->rec_st + INTERVAL $n DAY")
			->group('dt')
			->extreme($extrm_type)
		;
		return $q->one();
	}

	protected function get_period_end_anom(&$data, $period) {
		$this->climate->load();
		if($period === self::NOWMON) {
			$lta = $this->climate->monthly[$this->varname][D_monthshort];
		} elseif($period === self::NOWYR) {
			$lta = $this->climate->annual[$this->varname]['sum'];
		} elseif($period === self::NOWSEAS) {
			$lta = $this->climate->seasonal[$this->varname][D_seasonname];
		} else {
			throw new Exception("Invalid period $period specified");
		}
		return $this->summable ? $data[$period]['val'] / $lta : $data[$period]['val'] - $lta;
	}

	private function nday_avg_extremes($n, &$all_vals) {
		# Totals
		$lowest = INT_MAX;
		$highest = INT_MIN;
		$cum = 0.0;

		foreach ($all_vals as $i => $val) {
			$dt = $val['d'];
			$cum += $val[$this->colname];
			if ($i >= $n) {
				$cum -= $all_vals[$i - $n][$this->colname];
				if ($cum < $lowest) {
					$lowest = $cum;
					$lowest_end = $dt;
				}
				if ($cum > $highest) {
					$highest = $cum;
					$highest_end = $dt;
				}
			}
		}
		return [
			'min' => ['val' => $lowest / $n, 'dt' => $lowest_end],
			'max' => ['val' => $highest / $n, 'dt' => $highest_end],
		];
	}

	private function DbMkdate($m=false, $d=false, $y=false) {
		return Db::dt(Date::mkdate($m, $d, $y));
	}

	public static function get_periods($filter, $keys_only=true) {
		if(is_string($filter)) {
			$cond = true;
			if($filter[0] === '!') {
				$cond = false;
				$filter = substr($filter, 1);
			}
			$func = function($p) use(&$filter, &$cond) {return $p[$filter] === $cond;};
		} else {
			$func = $filter;
		}
		$periods = array_filter(self::$periods, $func);
		return $keys_only ? array_keys($periods) : $periods;
	}

	public static function get_periods_single($include_today=false) {
		return self::get_periods(function($p) use(&$include_today) {return !$p['multi'] && $p['today'] == $include_today;});
	}

	protected function get_date_filter($period) {
		# Numeric period
		if(in_array($period, self::$periodsn)) {
			return 'd > '. $this->DbMkdate(false, D_day - $period);
		}
		# Named period
		switch($period) {
			case self::RECORD:
				return null;
			case self::YESTERDAY:
				return 'd = '. $this->yest;
			case self::CUM_MON_AGO:
				return Db::btwn($this->mon_ago_st, $this->mon_ago, 'd');
			case self::CUM_YR_AGO:
				return Db::btwn($this->yr_ago_st, $this->yr_ago, 'd');
			case self::DAY_MON_AGO:
				return 'd = '.$this->mon_ago;
			case self::DAY_YR_AGO:
				return 'd = '.$this->yr_ago;
			case self::NOWMON:
				return 'd >= '.$this->mon_st;
			case self::NOWYR:
				return 'd >= '.$this->yr_st;
			case self::NOWSEAS:
				return 'd >= '.$this->seas_st;
			case self::RECORD_D:
				return "MONTH(d) = ".D_month ." AND DAY(d) = ".D_day;
			case self::RECORD_M:
				return "MONTH(d) = ".D_month;
		}
		throw new \Exception("Invalid period '$period' specified");
	}

	protected function get_date_filter_month($period) {
		$base_cond = 'd < '.$this->mon_st;
		# Numeric period
		if(in_array($period, self::$periodsn) && self::$periods[$period]['month_recs']) {
			$dt = Date::mkdate(false, D_day - $period);
			$cond = 'd >= '. $this->DbMkdate((int)date('n', $dt), 1, (int)date('Y', $dt));
		}
		# Named period
		switch($period) {
			case self::RECORD:
				return $base_cond;
			case self::NOWYR:
				$cond = "d >= $this->yr_st";
				break;
			case self::RECORD_M:
				$cond = "Month(d) = ".D_month;
				break;
		}
		if($cond) {
			return Db::and_($cond, $base_cond);
		}
		throw new \Exception("Invalid period '$period' specified");
	}

}
