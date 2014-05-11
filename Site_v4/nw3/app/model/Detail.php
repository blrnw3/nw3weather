<?php

use nw3\app\model\Variable;
use nw3\app\util\Date;
use nw3\app\core\Db;

/**
 * Description of Detail
 *
 * @author Ben
 */
class Detail {

	const TODAY = 'today';
	const YESTERDAY = 'yest';
	const NOWMON = 'mon';
	const NOWYR = 'yr';
	const NOWSEAS = 'seas';

	const RECORD = 'rec';
	const RECORD_D = 'rec_d';
	const RECORD_M = 'rec_m';
	const RECORD_Y = 'rec_y';

	const DAY_YR_AGO = 'day_yr_ago';
	const DAY_MON_AGO = 'day_mon_ago';
	const CUM_YR_AGO = 'cum_yr_ago';
	const CUM_MON_AGO = 'cum_mon_ago';

	/** Name of Db table fora daily data */
	const TBL_DAILY = 'daily';

	static $periods = array(7, 31, 365);

	public $num_records;

	protected $yest;
	protected $mon_st;
	protected $yr_st;
	protected $seas_st;
	protected $mon_ago;
	protected $yr_ago;
	protected $mon_ago_st;
	protected $yr_ago_st;

	protected $all_named_periods;
	protected $all_multiday_named_periods;
	protected $all_periods;
	protected $all_multiday_periods;

	protected $period_lengths;

	protected $var;
	private $db;
	private $colname;

	function __construct(Db $db, $varname) {
		$this->db = $db;
		$this->colname = $varname;
		$this->var = Variable::$daily[$varname];

		$this->num_records = $this->db->select(self::TBL_DAILY, '', Db::count($varname));

		# Useful db datetimes
		$this->yest = D_yest;
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

		# Useful period collections
		$this->all_named_periods = array(
			self::CUM_MON_AGO, self::CUM_YR_AGO, self::DAY_MON_AGO, self::DAY_YR_AGO,
			self::YESTERDAY, self::NOWMON, self::NOWSEAS, self::NOWYR, self::RECORD
		);
		$this->all_multiday_periods = array(
			self::CUM_MON_AGO, self::CUM_YR_AGO,
			self::NOWMON, self::NOWSEAS, self::NOWYR, self::RECORD
		);
		$this->all_periods = array_merge($this->all_named_periods, self::$periods);
		$this->all_multiday_periods = array_merge($this->all_multiday_named_periods, self::$periods);

		# Period lengths
		$this->period_lengths = array(
			self::RECORD => $this->num_records,
			self::NOWMON => D_day,
			self::NOWYR => D_doy + 1,
			self::CUM_MON_AGO => D_day,
			self::CUM_YR_AGO => D_doy + 1,
		);
		foreach (self::$periods as $n) {
			$this->period_lengths[$n] = $n;
		}
	}


	protected function select($where='', $func=null) {
		$col = is_null($func) ? array('d', $this->colname) : "$func($this->colname)";
		return $this->db->select(self::TBL_DAILY, $where, $col);
	}
	protected function filter_value($condition, $func=null) {
		$filter = Db::where("$this->colname $condition");
		return $this->select($filter, $func);
	}
	protected function filter_date($condition, $func=null) {
		return $this->select(Db::where($condition), $func);
	}
	protected function filter_dt_val($dt_cond, $val_cond, $func=null) {
		$filter = Db::where(
			Db::and_(array($dt_cond, "$this->colname $val_cond"))
		);
		return $this->select($filter, $func);
	}

	/**
	 * Calculate sum over quantity for given period
	 * @param type $period Must be one of pre-defined set
	 * @return float summation
	 */
	protected function period_sum($period) {
		return $this->db->select(
			self::TBL_DAILY,
			Db::where($this->get_date_filter($period)),
			Db::sum($this->colname)
		);
	}

	protected function period_sum_anom($period) {
		return $this->db->select(self::TBL_DAILY,
			Db::where($this->get_date_filter($period)),
			Db::sum($this->colname) .'/'. Db::sum("$this->colname - a_$this->colname")
		);
	}

	/**
	 * Calculate count of valid values for given period
	 * @param mixed $period Must be one of pre-defined set
	 * @param string $filter [=null] db condition to filter values for count (e.g. '> 1')
	 * @return int count that match $filter within $period
	 */
	protected function period_count($period, $filter=null) {
		$dt_filter = $this->get_date_filter($period);
		$val_filter = ($filter === null) ? null : "$this->colname $filter";
		return $this->db->select(self::TBL_DAILY,
			Db::and_(array($dt_filter, $val_filter)),
			Db::count($this->colname)
		);
	}

	private function DbMkdate($m=false, $d=false, $y=false) {
		return Db::dt(Date::mkdate($m, $d, $y));
	}

	protected function get_date_filter($period) {
		# Numeric period
		if(key_exists($period, self::$periods)) {
			return 'd > '.Date::mkday(D_day - self::$periods[$period]);
		}
		# Named period
		switch($period) {
			case self::RECORD:
				return null;
			case self::YESTERDAY:
				return 'd = '. $this->yest;
			case self::CUM_MON_AGO:
				return 'd '. Db::btwn($this->mon_ago_st, $this->mon_ago);
			case self::CUM_YR_AGO:
				return 'd '. Db::btwn($this->yr_ago_st, $this->yr_ago);
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
		}
		throw new Exception("Invalid period $period specified");
	}

}
