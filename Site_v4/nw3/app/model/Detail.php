<?php

use nw3\app\model\Variable;
use nw3\app\model\Store;
use nw3\app\util\Date;
use nw3\app\core\Db;
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

	protected $period_lengths;
	protected $all_named_periods;
	protected $all_multiday_named_periods;
	protected $all_periods;
	protected $all_multiday_periods;

	protected $var;
	protected $db;
	private $climate;
	private $colname;
	private $aggtype; //Sum or mean
	private $summable;

	public $live;

	function __construct($varname) {
		$this->db = Db::g();
		$this->climate = Climate::g();
		$this->colname = $varname;
		$this->var = Variable::$daily[$varname];
		$this->summable = $this->var['summable'];
		$this->aggtype = $this->var['summable'] ? Db::SUM : Db::AVG;

		$this->num_records = $this->db->select(self::TBL_DAILY, Db::count($varname), '', Db::SCALAR);

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

		# Useful period collections
		$this->all_named_periods = array(
			self::CUM_MON_AGO, self::CUM_YR_AGO, self::DAY_MON_AGO, self::DAY_YR_AGO,
			self::YESTERDAY, self::NOWMON, self::NOWSEAS, self::NOWYR, self::RECORD
		);
		$this->all_multiday_named_periods = array(
			self::CUM_MON_AGO, self::CUM_YR_AGO,
			self::NOWMON, self::NOWSEAS, self::NOWYR, self::RECORD
		);
		$this->all_periods = array_merge($this->all_named_periods, self::$periods);
		$this->all_multiday_periods = array_merge($this->all_multiday_named_periods, self::$periods);

		# Period lengths
		$this->period_lengths = array(
			self::RECORD => $this->num_records,
			self::NOWMON => D_day,
			self::NOWSEAS => Date::get_current_season_days_elapsed(),
			self::NOWYR => D_doy + 1,
			self::CUM_MON_AGO => D_day,
			self::CUM_YR_AGO => D_doy + 1,
		);
		foreach (self::$periods as $n) {
			$this->period_lengths[$n] = $n;
		}
		$this->live = Store::g();
	}


	protected function select($where='', $func=null) {
		$col = is_null($func) ? array('d', $this->colname) : "$func($this->colname)";
		return $this->db->select(self::TBL_DAILY, $col, $where);
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
	 * Calculate sum/avg over quantity for given period
	 * @param type $period Must be one of pre-defined set
	 * @return float summation
	 */
	protected function period_agg($period) {
		return $this->db->select(self::TBL_DAILY,
			Db::agg($this->colname, $this->aggtype),
			Db::where($this->get_date_filter($period)),
			Db::SCALAR
		);
	}

	protected function period_sum_anom($period) {
		return $this->db->select(self::TBL_DAILY,
			Db::sum($this->colname) .'/'. Db::sum("$this->colname - a_$this->colname"),
			Db::where($this->get_date_filter($period)),
			Db::SCALAR
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
			Db::count($this->colname),
			Db::where( Db::and_(array($dt_filter, $val_filter)) ),
			Db::SCALAR
		);
	}

	/**
	 * Min or Max annual mean/sum, excluding current year
	 * @param boolean $is_high set true to get max, false to get min
	 * @return array(int,float) year, val
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
	 * @return array(int,float) year, val
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

	/**
	 * Min or Max monthly mean/sum for all months but the current one
	 * @param boolean $is_high set true to get max, false to get min
	 * @return array(int,int,float) year, month, val
	 */
	protected function extreme_month_agg($is_high) {
		$dir = $is_high ? 'DESC' : 'ASC';
		return $this->db->select(self::TBL_DAILY,
			"YEAR(d) as y, MONTH(d) as m, ". Db::agg($this->colname, $this->aggtype) ." as val",
			"  WHERE d < $this->mon_st"
			." GROUP BY y, m"
			." ORDER BY val $dir"
			." LIMIT 1",
			Db::SINGLE
		);
	}
	/**
	 * Min or Max monthly count for values passing the given filter, excluding current month
	 * @param boolean $is_high set true to get max, false to get min
	 * @param string $filter count only values passing this filter
	 * @return array(int,float) year, val
	 */
	protected function extreme_month_count($is_high, $filter) {
		$dir = $is_high ? 'DESC' : 'ASC';
		return $this->db->select(self::TBL_DAILY,
			"YEAR(d) as y, MONTH(d) as m, COUNT($this->colname) as val",
			"  WHERE d < $this->mon_st AND $filter"
			." GROUP BY y, m"
			." ORDER BY val $dir"
			." LIMIT 1",
			Db::SINGLE
		);
	}

	/**
	 * Min or Max monthly mean/sum for all months this year, excluding the current one
	 * @param boolean $is_high set true to get max, false to get min
	 * @return array(int,float) month, val
	 */
	protected function extreme_nowyr_agg($is_high) {
		$dir = $is_high ? 'DESC' : 'ASC';
		return $this->db->select(self::TBL_DAILY,
			"MONTH(d) as m, ". Db::agg($this->colname, $this->aggtype) ." as val",
			"  WHERE d >= $this->yr_st AND d < $this->mon_st"
			." GROUP BY m"
			." ORDER BY val $dir"
			." LIMIT 1",
			Db::SINGLE
		);
	}
	/**
	 * Min or Max monthly count for all months this year, excluding the current one
	 * @param boolean $is_high set true to get max, false to get min
	 * @param string $filter count only values passing this filter
	 * @return array(int,float) month, val
	 */
	protected function extreme_nowyr_count($is_high, $filter) {
		$dir = $is_high ? 'DESC' : 'ASC';
		return $this->db->select(self::TBL_DAILY,
			"MONTH(d) AS m, COUNT($this->colname) as val",
			"  WHERE d >= $this->yr_st AND d < $this->mon_st AND $filter"
			." GROUP BY m"
			." ORDER BY val $dir"
			." LIMIT 1",
			Db::SINGLE
		);
	}

	protected function extreme_currmonth_agg($is_high) {
		$dir = $is_high ? 'DESC' : 'ASC';
		return $this->db->select(self::TBL_DAILY,
			"YEAR(d) AS y, MONTH(d) as m, ". Db::agg($this->colname, $this->aggtype) ." AS val",
			"  WHERE d < $this->mon_st"
			." GROUP BY y, m"
			." HAVING m = ". D_month
			." ORDER BY val $dir"
			." LIMIT 1",
			Db::SINGLE
		);
	}
	# TODO - same for count

	protected function extremes($is_high, $num=1) {
		$dir = $is_high ? 'MAX' : 'MIN';
		return $this->db->select(self::TBL_DAILY,
			"d, $dir($this->colname) AS val",
			"WHERE d <= $this->yest"
			." ORDER BY val $dir"
			." LIMIT $num"
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
	protected function extremes_currmon($is_high, $num=1) {
		$dir = $is_high ? 'DESC' : 'ASC';
		return $this->db->select(self::TBL_DAILY,
			"d, $dir($this->colname) AS val",
			Db::where(Db::and_(array("d <= $this->yest", "MONTH(d) = ".D_month)))
			." ORDER BY val $dir"
			." LIMIT $num"
		);
	}
	protected function extremes_currday($is_high, $num=1) {
		$dir = $is_high ? 'DESC' : 'ASC';
		return $this->db->select(self::TBL_DAILY,
			"d, $dir($this->colname) AS val",
			Db::where(array("d <= $this->yest", "MONTH(d) = ".D_month, "DAY(d) = ".D_day))
			." ORDER BY val $dir"
			." LIMIT $num"
		);
	}

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

	protected function get_date_filter($period) {
		# Numeric period
		if(in_array($period, self::$periods)) {
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
		}
		throw new Exception("Invalid period $period specified");
	}

}
