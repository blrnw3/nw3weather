<?php
namespace nw3\app\model;

use nw3\app\model\Variable;
use nw3\app\util\Date;
use nw3\app\core\Db;
use nw3\app\core\Query;
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

	protected $period_lengths;

	protected $var;
	protected $db;
	private $climate;
	private $colname;
	private $aggtype; //Sum or mean
	private $summable;

	public static $periods = [
		self::TODAY => [
			'multi' => false,
			'descrip' => 'Today'
		],
		self::YESTERDAY => [
			'multi' => false,
			'descrip' => 'Yesterday'
		],
		self::NOWMON => [
			'multi' => true,
			'descrip' => 'Month'
		],
		self::NOWYR => [
			'multi' => true,
			's' => true,
			'descrip' => 'Year'
		],
		self::NOWSEAS=> [
			'multi' => true,
			'descrip' => 'Season'
		],
		self::RECORD => [
			'multi' => true,
			'record' => true,
			's' => true,
			'descrip' => 'Overall'
		],
		self::RECORD_D => [
			'multi' => true,
			'record' => true,
			'descrip' => 'This Date (day)'
		],
		self::RECORD_M => [
			'multi' => true,
			'record' => true,
			's' => true,
			'descrip' => 'This Date (month)'
		],
		self::CUM_MON_AGO => [
			'multi' => true,
			'descrip' => 'Cum Mon Ago'
		],
		self::CUM_YR_AGO => [
			'multi' => true,
			'descrip' => 'Cum Yr Ago'
		],
		self::DAY_MON_AGO => [
			'multi' => false,
			'descrip' => 'Day Mon Ago'
		],
		self::DAY_YR_AGO => [
			'multi' => false,
			'descrip' => 'Day Yr Ago'
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
				'descrip' => "$n days"
			];
		}
		self::$periods[self::RECORD_D]['descrip'] = date('jS M', D_now);
		self::$periods[self::RECORD_M]['descrip'] = D_monthname;
//		self::$periods[self::RECORD_S]['descrip'] = D_seasonname;
	}

	/**
	 * @param type $varname as in the db and Variable Class
	 */
	function __construct($varname) {
		$this->db = Db::g();
		$this->climate = Climate::g();
		$this->colname = $varname;
		$this->var = Variable::$daily[$varname];
		$this->summable = $this->var['summable'];
		$this->aggtype = $this->var['summable'] ? Db::SUM : Db::AVG;

		$this->num_records = $this->db->query($varname)->count();

		# Useful db datetimes
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

	public function values() {
		$data = [];
		foreach (self::get_periods_single() as $period) {
			$val = $this->period_extreme($period);
			$data[$period] = [
				'val' => $val['val'],
				'dt' => $val['t']
			];
		}
		return $data;
	}

	public function extremes() {
		$data = ['max' => [], 'min' => []];
		foreach (self::get_periods_multi() as $period) {
			$max = $this->period_extreme($period, Db::MAX);
			$data['max'][$period] = [
				'val' => $max['val'],
				'dt' => $max['d']
			];
			if($this->var['minmax']) {
				$min = $this->period_extreme($period, Db::MIN);
				$data['min'][$period] = [
					'val' => $min['val'],
					'dt' => $min['d']
				];
			}
		}
		return $data;
	}

	public function extremes_month() {
		$data = ['max' => [], 'min' => []];
		foreach (self::get_periods_month_rec() as $period) {
			$max = $this->period_extreme_month($period, Db::MAX);
			$data['max'][$period] = [
				'val' => $max['val'],
				'dt' => $max['d']
			];
			if($this->var['minmax']) {
				$min = $this->period_extreme_month($period, Db::MIN);
				$data['min'][$period] = [
					'val' => $min['val'],
					'dt' => $min['d']
				];
			}
		}
		return $data;
	}

	/**
	 * Calculate sum/avg over quantity for given period
	 * @param type $period Must be one of pre-defined set
	 * @return float summation
	 */
	protected function period_agg($period) {
		return $this->db->query(Db::agg($this->colname, $this->aggtype))
			->filter($this->get_date_filter($period))
			->scalar();
	}

	protected function period_extreme($period, $extrm_type=null) {
		$fields = [Db::as_($this->colname, 'val')];
		$q = $this->db->query();
		if(!$this->var['spread']) {
			$fields[] = Db::time_field($this->colname);
		}
		if(self::$periods[$period]['multi']) {
			$fields[] = 'd';
			$q = $q->extreme($extrm_type);
		}
		$q = $q->fields($fields)->filter($this->get_date_filter($period));
		return $q->one();
	}

	protected function period_sum_anom($period) {
		return $this->db->query(Db::sum($this->colname) .'/'. Db::sum("$this->colname - a_$this->colname"))
			->filter($this->get_date_filter($period))
			->scalar()
		;
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

	/**
	 * Min or Max monthly mean/sum for all months but the current one
	 * @param boolean $is_high set true to get max, false to get min
	 * @return [int,int,float] year, month, val
	 */
	protected function period_extreme_month($period, $extrm_type=null) {
		$q = $this->db->query(Db::as_('YEAR(d)', 'y'), Db::as_('MONTH(d)', 'm'),
				Db::as_(Db::agg($this->colname, $this->aggtype), 'val'), 'd')
			->filter($this->get_date_filter_month($period))
			->group('y', 'm')
			->extreme($extrm_type)
			->one();
		return $q;
	}

	/**
	 * Min or Max annual mean/sum, excluding current year
	 * @param boolean $is_high set true to get max, false to get min
	 * @return [int,float] year, val
	 */
	protected function extreme_year_agg($is_high) {
		$dir = $is_high ? 'DESC' : 'ASC';
		return $this->db->select(self::TBL_DAILY,
			"YEAR(d) as y, ". Db::agg($this->colname, $this->aggtype) ." as val",
			"  WHERE d < $this->yr_st"
			." GROUP BY y"
			." ORDER BY val $dir"
			." LIMIT 1",
			Db::SINGLE
		);
	}
	/**
	 * Min or Max annual count for values passing the given filter, excluding current year
	 * @param boolean $is_high set true to get max, false to get min
	 * @param string $filter count only values passing this filter
	 * @return [int,float] year, val
	 */
	protected function extreme_year_count($is_high, $filter) {
		$dir = $is_high ? 'DESC' : 'ASC';
		return $this->db->select(self::TBL_DAILY,
			"YEAR(d) as y, COUNT($this->colname) as val",
			"  WHERE d < $this->yr_st AND $filter"
			." GROUP BY y"
			." ORDER BY val $dir"
			." LIMIT 1",
			Db::SINGLE
		);
	}
	protected function records($is_high, $num=1) {
		$dir = $is_high ? 'MAX' : 'MIN';
		return $this->db->select(self::TBL_DAILY,
			"d, $dir($this->colname) AS val",
			"WHERE d <= $this->yest"
			." ORDER BY val $dir"
			." LIMIT $num",
			($num === 1) ? Db::SINGLE : null
		);
	}
	protected function extremes_nowyr($is_high, $num=1) {
		$dir = $is_high ? 'MAX' : 'MIN';
		return $this->db->select(self::TBL_DAILY,
			"d, $dir($this->colname) AS val",
			Db::where(Db::btwn($this->yr_st, $this->yest, 'd'))
			." ORDER BY val $dir"
			." LIMIT $num"
		);
	}
	protected function extremes_nowmon($is_high, $num=1) {
		$dir = $is_high ? 'MAX' : 'MIN';
		return $this->db->select(self::TBL_DAILY,
			"d, $dir($this->colname) AS val",
			Db::where(Db::btwn($this->mon_st, $this->yest, 'd'))
			." ORDER BY val $dir"
			." LIMIT $num"
		);
	}
//	protected function records_mon($is_high, $num=1) {
//		$dir = $is_high ? 'DESC' : 'ASC';
//		return $this->db->select(self::TBL_DAILY,
//			"d, $dir($this->colname) AS val",
//			Db::where(Db::and_(["d <= $this->yest", "MONTH(d) = ".D_month))]
//			." ORDER BY val $dir"
//			." LIMIT $num"
//		);
//	}
//	protected function records_day($is_high, $num=1) {
//		$dir = $is_high ? 'DESC' : 'ASC';
//		return $this->db->select(self::TBL_DAILY,
//			"d, $dir($this->colname) AS val",
//			Db::where(["d <= $this->yest", "MONTH(d) = ".D_month, "DAY(d) = ".D_day)]
//			." ORDER BY val $dir"
//			." LIMIT $num"
//		);
//	}

	protected function day_extremes($is_high, $num=1) {
		$dir = $is_high ? 'DESC' : 'ASC';
		return $this->db->select(self::TBL_DAILY,
			"d, $dir($this->colname) AS val",
			''# TODO date_filter to genericise above 'extremes' functions
			." ORDER BY val $dir"
			." LIMIT $num"
		);
	}

	protected function get_period_end_anom(&$data, $period) {
		$this->climate->load();
		if($period === self::NOWMON) {
			$lta = $this->climate->monthly[$this->colname][D_monthshort];
		} elseif($period === self::NOWYR) {
			$lta = $this->climate->annual[$this->colname]['sum'];
		} elseif($period === self::NOWSEAS) {
			$lta = $this->climate->seasonal[$this->colname][D_seasonname];
		} else {
			throw new Exception("Invalid period $period specified");
		}
		return $this->summable ? $data[$period]['val'] / $lta : $data[$period]['val'] - $lta;
	}

	private function DbMkdate($m=false, $d=false, $y=false) {
		return Db::dt(Date::mkdate($m, $d, $y));
	}

	public static function get_periods($filter, $keys_only=true) {
		if(is_string($filter)) {
			$cond = ($filter[0] !== '!');
			$func = function($p) use(&$filter, &$cond) {return $p[$filter] === $cond;};
		} else {
			$func = $filter;
		}
		$periods = array_filter(self::$periods, $func);
		return $keys_only ? array_keys($periods) : $periods;
	}

	public static function get_periods_multi() {
		return self::get_periods('multi');
	}
	public static function get_periods_single($include_today=false) {
		$periods = self::get_periods('!multi');
		if(!$include_today) {
			unset($periods[self::TODAY]);
		}
		return $periods;
	}
	public static function get_periods_record() {
		return array_filter(self::$periods, function($p) {return $p['record'];});
	}
	public static function get_periods_recent_multi() {
		return array_filter(self::$periods, function($p) {return $p['multi'] && !$p['record'];});
	}
	public static function get_periods_month_rec() {
		return array_filter(self::$periods, function($p) {return $p['month_recs'];});
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
		throw new \Exception("Invalid period $period specified");
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
				$cond = null;
			case self::NOWYR:
				$cond = "d >= $this->yr_st";
			case self::RECORD_M:
				$cond = "MONTH(d) = ".D_month;
		}
		if($cond) {
			return Db::and_($cond, $base_cond);
		}
		throw new \Exception("Invalid period $period specified");
	}

}
