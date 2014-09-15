<?php
namespace nw3\app\model;

use nw3\app\model\Variable;
use nw3\app\model\Store;
use nw3\app\model\Climate;
use nw3\app\util\Date;
use nw3\app\core\Db;
use nw3\app\core\Alias;
use nw3\app\core\Introspect;

/**
 * Description of Detail
 *
 * @author Ben
 */
abstract class Detail {

	const LIVE = 'live';
	const HR24 = 'hr24';
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
	const SEASONAL = 'sesonal';

	const TBL_DAILY = 'daily';

	static $ranknum = 5;

	static $periodsn = [7, 31, 365];

	private static $init = false;

	public $var;
	public $type;
	public $abs_type;

	public $descrip;
	public $descrip_min;
	public $descrip_max;
	public $descrip_mean;
	public $descrip_period_min;
	public $descrip_period_max;
	public $descrip_period_mean;
	public $descrip_days_of;

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
	protected $query_date;
	protected $query_month;
	protected $query_year;
	protected $query_val;
	protected $query_anom;
	protected $count_filter;

	/* Lazy loaded to minimise db_calls */
	private $period_lengths;

	protected $db;
	protected $now;
	protected $colname;
	protected $anom_colname;
	protected $varname;
	protected $climate;
	protected $aggtype; //Sum or mean
	protected $summable;
	protected $anomable;

	public $no_daily_anom = false;

	protected $days_filter;
	protected $live_var;

	public static $periods = [
		self::TODAY => [
			'multi' => false,
			'descrip' => 'Today',
			'today' => true
		],
		self::HR24 => [
			'multi' => false,
			'descrip' => 'Past 24 Hrs',
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
		],
		self::RECORD_D => [
			'multi' => true,
			'record' => true,
			'descrip' => 'This Date (day)',
			'format' => 'Y'
		],
		self::RECORD_M => [
			'multi' => true,
			'record' => true,
			'month_recs' => true,
			'descrip' => 'This Date (month)',
			'format' => '\D\a\y j Y',
			'mon_format' => 'Y',
			'has_spell' => true,
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

	public static function get_instance($varname) {
		$cls = new \ReflectionClass("nw3\\app\\vari\\$varname");
		return $cls->newInstance();
	}

	public static function initialise() {
		self::$init = true;
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
	 * @param type $livename any live-assoc
	 */
	function __construct($livename, $graph=false) {
		$varname = Introspect::child_name($this);
		$this->db = Db::g();
		$this->climate = Climate::g();
		$this->varname = $varname;
		$this->var = Variable::$daily[$varname];
		$this->colname = $this->var['db_field'] ? $this->var['db_field'] : $varname;
		$this->anom_colname = $this->var['db_field_anom'] ? $this->var['db_field_anom'] : "a_$this->colname";
		$this->summable = $this->var['summable'];
		$this->anomable = $this->var['anomable'];
		$this->aggtype = $this->var['summable'] ? Db::SUM : Db::AVG;

		$this->live_var = $livename;
		$this->live = Variable::$live[$this->live_var];
		$this->now = Store::g();

		$this->type = $this->var['group'];
		$this->abs_type = $this->type;

		if(!$this->var) {
			throw new \Exception("$varname is not a valid variable");
		}

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
		$this->query_date = new Alias('d', 'dt');
		$this->query_month = new Alias('MONTH(d)', 'm');
		$this->query_year = new Alias('YEAR(d)', 'y');
		$this->query_val = new Alias($this->colname, 'val');
		$this->query_anom = new Alias($this->anom_colname, 'anom');
		$this->count_filter = "$this->colname $this->days_filter";

		$query_agg = new Alias(Db::agg($this->colname, $this->aggtype));
		$query_anom_sum = Db::sum($this->colname) .'/'. Db::sum("$this->colname - $this->anom_colname");
		$query_anom_avg = Db::avg($this->anom_colname);
		$this->query_agg = [$query_agg];
		if($this->anomable && !$graph) {
			$this->query_agg[] = new Alias($this->summable ? $query_anom_sum : $query_anom_avg, 'anom');
		}

		$this->assign_default_descriptions();
		$this->assign_descriptions();

		if(!self::$init) {
			self::initialise();
		}
	}

	protected function period_lengths() {
		if(!$this->period_lengths) {
			$this->period_lengths = [
				self::RECORD => $this->db->query($this->colname)->count(),
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
		return $this->period_lengths;
	}

	protected function assign_default_descriptions() {
		$descrip = $this->var['descrip'] ? $this->var['descrip'] : $this->var['description'];
		$this->descrip = $descrip;
		$this->descrip_min = "Lowest $descrip";
		$this->descrip_max = "Highest $descrip";
		$this->descrip_mean = $this->var['spread'] ? "Overall $descrip" : "Mean $descrip";
		$this->descrip_period_min = "Lowest $descrip";
		$this->descrip_period_max = "Highest $descrip";
		$this->descrip_period_mean = "Mean $descrip";
		$this->descrip_days_of = "Days of $descrip $this->days_filter";
	}
	protected function assign_descriptions() {
		//Override if a better descrip exists
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
		foreach ($query as $record) {
			$data['values'][] = $record['val'];
			$labs[] = new \DateTime($record['d']);
		}
		return [
			'data' => [$data],
			'labels' => $labs
		];
	}


	### Data-y stuff ###

	public function num_records() {
		return $this->period_lengths()[self::RECORD];
	}

	public function live() {
		return [];
	}
	public function trend_diffs($diff_periods) {
		return [];
	}
	public function trend_avgs($avg_periods) {
		return [];
	}
	protected function value_today() {
		return [];
	}
	protected function value_hr24() {
		return [];
	}

	public function values($periods=null) {
		if(is_null($periods)) {
			$periods = self::get_periods_single(true);
		}
		foreach ($periods as $period) {
			$val = $this->period_extreme($period);
			$data[$period] = $val;
		}
		return $data;
	}

	/**
	 * Convenience wrapper around extremes and means and counts/days
	 * @param type $mmm MIN, MAX, MEAN, or COUNT
	 * @param type $extreme_type
	 * @return type
	 */
	public function extremes_agg_or_days($mmm, $extreme_type=null, $periods=null) {
		switch($mmm) {
			case MEAN:
				return $this->means($periods);
			case COUNT:
				return $this->days($periods);
			case SPELL:
			case SPELL_INV:
				return $this->spells($mmm);
			case MIN:
			case MAX:
				return $this->extremes($extreme_type, $mmm, $periods);
		}
		throw new \Exception("Bad mmm '$mmm' specified");
	}
	public function get_descrip($mmm, $extreme_type) {
		$is_daily = ($extreme_type === self::DAILY);
		switch($mmm) {
			case MIN:
				return $is_daily ? $this->descrip_min : $this->descrip_period_min;
			case MAX:
				return $is_daily ? $this->descrip_max : $this->descrip_period_max;
			case MEAN:
				return $is_daily ? $this->descrip_mean : $this->descrip_period_mean;
			case COUNT:
				return $this->descrip_days_of;
			case SPELL:
				return "Spell $this->descrip_days_of";
			case SPELL_INV:
				return "Spell not $this->descrip_days_of";
		}
		throw new \Exception("Bad mmm '$mmm' specified");
	}

	public function mins_daily() {
		return $this->extremes(self::DAILY, MIN);
	}
	public function maxs_daily() {
		return $this->extremes(self::DAILY, MAX);
	}
	public function mins_monthly() {
		return $this->extremes(self::MONTHLY, MIN);
	}
	public function maxs_monthly() {
		return $this->extremes(self::MONTHLY, MAX);
	}
	public function mins_yearly() {
		return $this->extremes(self::YEARLY, MIN);
	}
	public function maxs_yearly() {
		return $this->extremes(self::YEARLY, MAX);
	}

	public function extremes($type, $mmm, $periods=null) {
		$data = [];
		$fn = $this->get_extreme_fn($type);
		if(is_null($periods)) $periods = self::get_periods($type);
		foreach ($periods as $period) {
			$data[$period] = $this->$fn($period, $mmm);
		}
		return $data;
	}

	public function means($periods=null) {
		$data = [];
		if(is_null($periods)) $periods = self::get_periods('multi');
		foreach ($periods as $period) {
			$data[$period] = $this->period_agg($period);
		}
		return $data;
	}

	public function days($periods=null) {
		$data = [];
		if(is_null($periods)) $periods = self::get_periods('multi');
		foreach ($periods as $period) {
			$cnt = $this->period_count($period, $this->days_filter);
			$data[$period] = [
				'val' => $cnt,
			];
			$len = $this->period_lengths()[$period];
			if($len > 1) {
				$data[$period]['prop'] = $cnt / $len;
			}
		}
		return $data;
	}

	public function spells($mmm) {
		throw new \Exception("Spells not implemented for $this->colname");
	}

	public function rankings_min($type, $period, $num=null) {
		return $this->rankings($type, $period, MIN, $num);
	}
	public function rankings_max($type, $period, $num=null) {
		return $this->rankings($type, $period, MAX, $num);
	}

	public function rankings($type, $period, $mmm, $num=null) {
		$num = is_null($num) ? self::$ranknum : $num;
		if(!self::$periods[$period]) {
			throw new \Exception("'$period' is not a valid period");
		}
		$fn = $this->get_extreme_fn($type);
		return $this->$fn($period, $mmm, $num);
	}

	/**
	 * Convenience wrapper
	 * @param type $mmm
	 */
	public function past_year_monthly_mmm($mmm) {
		switch($mmm) {
			case MEAN:
				return $this->past_year_monthly_aggs();
			case COUNT:
				return $this->past_year_monthly_counts();
			case MIN:
			case MAX:
				return $this->past_year_monthly_extremes($mmm);
		}
		throw new \Exception("Bad mmm $mmm passed for past year monthly");
	}
	public function past_year_seasonal_mmm($mmm) {
		switch($mmm) {
			case MEAN:
				return $this->past_year_seasonal_aggs();
			case COUNT:
				throw new \Exception("Past yr seasonal counts not yet implemented");
		}
		throw new \Exception("Bad mmm $mmm passed for past year seasonal");
	}

	public function past_year_monthly_aggs() {
		$q = $this->db->query('d', $this->query_agg)
			->filter("d < $this->mon_st", "d >= $this->yr_ago_mon_st")
		;
		$data = [
			'annual' => $q->one(),
			'periods' => $q->group('MONTH(d)')->order(Db::ASC)->all()
		];
		$this->mark_extreme($data['periods']);
		return $data;
	}
	public function past_year_monthly_counts() {
		$q = $this->db->query('d', new Alias('MONTH(d)', 'm'), $this->query_count)
			->filter("d < $this->mon_st", "d >= $this->yr_ago_mon_st", $this->count_filter)
		;
		$annual = $q->one();
		$monthly = $q->group('m')->order(Db::ASC)->all();

		$diy = 365;
		foreach($monthly as &$db_val) {
			$dim = Date::get_days_in_month_from_date_string($db_val['d']);
			$db_val['prop'] = $db_val['val'] / $dim;
			if($dim === 29) ++$diy;
		}
		$annual['prop'] = $annual['val'] / $diy;

		if(count($monthly) < 12) {
			// Really annoying. SQL doesn't return counts of 0 on the group_by if all rows are filtered out!
			$mons_missing = [];
			foreach(Date::$monthsn as $i => $m) {
				$mons_missing[date('Y-m', Date::mkdate(D_month + $i, 1, D_year-1))] = true;
			}
			foreach($monthly as &$db_m) {
				$mons_missing[substr($db_m['d'], 0, 7)] = false;
			}
			foreach($mons_missing as $d => $missing) {
				if(!$missing) continue;
				$monthly[] = [
					'd' => "$d-01",
					'val' => 0,
					'prop' => 0
				];
			}
			usort($monthly, function($a, $b) {return ($a['d'] > $b['d']) ? 1 : -1;});
		}
		$this->mark_extreme($monthly);
		return [
			'annual' => $annual,
			'periods' => $monthly
		];
	}

	public function past_year_monthly_extremes($mmm) {
		$data = [];
		foreach(Date::$monthsn as $i => $m) {
			$st = $this->DbMkdate(D_month + $i, 1, D_year-1);
			$en = $this->DbMkdate(D_month + $m, 1, D_year-1);
			$q = $this->db->query($this->query_val, 'd')
				->filter("d < $en", "d >= $st")
			;
			if($this->anomable) {
				$q = $q->fields([$this->query_anom]);
			}
			$data[] = $q->extreme($mmm)->one();
		}
		$this->mark_extreme($data);
		return $data;
	}

	public function past_year_seasonal_aggs() {
		$q = $this->db->query('d', $this->query_agg, Db::SEASON_COL)
			->filter("d < $this->seas_st", "d >= $this->yr_ago_seas_st")
		;
		$data = [
			'annual' => $q->one(),
			'periods' => $q->group('season')->order(Db::ASC)->all()
		];
		$this->mark_extreme($data['periods']);
		return $data;
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
		$data = [MAX => [], MIN => []];
		$all = $this->db->query('d', $this->colname)
			->filter(Db::not_null($this->colname))
			->order(Db::ASC)
		->all();
		foreach(self::$periodsn as $spell_len) {
			$spells = $this->nday_avg_extremes($spell_len, $all);
			$data[MIN][$spell_len] = $spells['min'];
			$data[MAX][$spell_len] = $spells['max'];
		}
		return $data;
	}

	protected function mark_extreme(&$db_vals) {
		$min = INT_MAX;
		$max = INT_MIN;
		foreach($db_vals as $i => $db_val) {
			if($db_val['val'] > $max) {
				$max = $db_val['val'];
				$max_n = $i;
			} elseif($db_val['val'] < $min) {
				$min = $db_val['val'];
				$min_n = $i;
			}
		}
		$db_vals[$min_n]['is_min'] = true;
		$db_vals[$max_n]['is_max'] = true;
	}

	/**
	 * Calculate sum/avg over quantity for given period
	 * @param type $period Must be one of pre-defined set
	 * @return float summation
	 */
	protected function period_agg($period) {
		if($period == self::TODAY) {
			return $this->value_today();
		}
		if($period == self::HR24) {
			return $this->value_hr24();
		}
		
		return $this->db->query($this->query_agg)
			->filter($this->get_date_filter($period))
			->one();
	}

	protected function period_extreme($period, $extrm_type=null, $num=1) {
		if($period == self::TODAY) {
			return $this->value_today();
		}
		if($period == self::HR24) {
			return $this->value_hr24();
		}

		$fields = [$this->query_val];
		$q = $this->db->query();
		if(!$this->var['spread'] && $num === 1) {
			$fields[] = Db::time_field($this->colname, 'dt');
		}
		if($this->anomable && !$this->summable && !$this->var['anom_day_ignore']) {
			$fields[] = $this->query_anom;
		}
		if(self::$periods[$period]['multi']) {
			$fields[] = $this->query_date;
			if($num === 1) {
				$q = $q->extreme($extrm_type);
			}
		}
		$q = $q->fields($fields)->filter($this->get_date_filter($period));
		if($extrm_type === MIN) {
			$q = $q->no_nulls();
		}
		if($num === 1) {
			return $q->one();
		}
		return $q->limit($num)->order($extrm_type)->all();
	}

	/**
	 * Calculate count of valid values for given period
	 * @param mixed $period Must be one of pre-defined set
	 * @param string $filter [=null] db condition to filter values for count (e.g. '> 1')
	 * @return int count that match $filter within $period
	 */
	protected function period_count($period, $filter=null) {
		if($period == self::TODAY || $period == self::HR24) {
			return null;
		}

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
		$q = $this->db->query($this->query_agg, $this->query_year,
				$this->query_month, $this->query_date)
			->filter($this->get_date_filter_month($period))
			->group('y', 'm')
		;
		if($extrm_type === MIN) {
			$q = $q->filter(Db::not_null($this->colname));
		}
		if($num === 1) {
			return $q->extreme($extrm_type)->one();
		}
		return $q->order($extrm_type)->limit($num)->all();
	}

	/**
	 * Min or Max monthly mean/sum for all years but the current one
	 * @param boolean $is_high set true to get max, false to get min
	 * @return [int,float] year, val
	 */
	protected function period_extreme_year($period, $extrm_type=null, $num=1) {
		$q = $this->db->query($this->query_agg, $this->query_year, $this->query_date)
			->filter('d < '. $this->yr_st)
			->group('y')
		;
		if($num === 1) {
			return $q->extreme($extrm_type)->one();
		}
		return $q->order($extrm_type)->limit($num)->all();
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

	private function get_extreme_fn($type) {
		switch($type) {
			case self::DAILY:
				return 'period_extreme';
			case self::MONTHLY:
				return 'period_extreme_month';
			case self::YEARLY:
				return 'period_extreme_year';
			case self::SEASONAL:
				throw new \Exception('Seasonal periods extremes not yet implemented');
		}
		throw new \Exception("'$type' is not a valid extreme type. Must be D, M, or Y");
	}

	public static function get_periods($filter, $keys_only=true) {
		if($filter === self::DAILY) {
			$filter = 'multi';
		}
		if($filter === self::MONTHLY) {
			$filter = 'month_recs';
		}
		if($filter === self::YEARLY) {
			return [self::RECORD];
		}
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
