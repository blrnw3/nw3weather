<?php
namespace nw3\app\util;

/**
 *
 * @author Ben LR
 */
class Date {

	static $months = array('Jan', 'Feb', 'March', 'April', 'May', 'June', 'July', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
	static $months3 = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');

	static $seasons = array('Winter', 'Spring', 'Summer', 'Autumn');
	static $season_month_nums = array(array(0,1,11), array(2,3,4), array(5,6,7), array(8,9,10));

	 //number of leap years since 2009
	const num_leap_yrs = 1;

	/**
	 * Loads up the global define with some useful constants
	 */
	public static function initialise() {
		//When testing, it could be useful to change this
		$now = time();
		define('D_now', $now);

		//Define (globally) some of the most useful date-based pseudo-constants
		define('D_monthname', date('F', $now));
		define('D_date', date('d M Y', $now));
		define('D_time', date('H:i', $now));
		define('D_dst', date("I", $now) ? "BST" : "GMT");

		define('D_hour', date('H', $now));
		define('D_day', date('j', $now));
		define('D_month', date('n', $now));
		define('D_year', date('Y', $now));
		define('D_doy', date('z', $now));
		define('D_dim', date('t', $now));

		$lat = \nw3\config\Location::LAT;
		$lng = \nw3\config\Location::LNG;
		$zenith = \nw3\config\Location::ZENITH;
		define('D_sunrise', date_sunrise($now, SUNFUNCS_RET_STRING, $lat, $lng, $zenith));
		define('D_sunset', date_sunset($now, SUNFUNCS_RET_STRING, $lat, $lng, $zenith));

		$yest = self::mkdate(false, D_day-1);
		define( 'D_yest_day', date('j', $yest) );
		define( 'D_yest_month', date('n', $yest) );
		define( 'D_yest_year', date('Y', $yest) );
		define( 'D_yest_doy', date('z', $yest) );


		//Determine current season
		for($s = 0; $s < 4; $s++) {
			if(in_array(D_month-1,  self::$season_month_nums[$s])) {
				define('D_season', $s+1);
				break;
			}
		}
		define('D_seasonname', self::$seasons[D_season-1]);
	}

	/**
	 * Returns true if it's the first day of the month, else false
	 * @return bool
	 */
	static function is_first_of_month() {
		return D_day == 1;
	}

	/**
	 * Months elapsed during the current meteorological season
	 * @return int
	 */
	static function months_this_season() {
		return D_month % 3 + 1;
	}

	/**
	 * Formats a date. Returns 'Today' if that condition is true. Pass null on y, m, or d to not display that in the output
	 * @param int $year [=false]
	 * @param int $month [=false]
	 * @param int $day [=false]
	 * @param boolean $current [=false] whether to display 'current' or 'Today' as the message if the date is today
	 * @param long $tstamp [=false] use a timestamp instead of d/m/y
	 * @param string $debug [=false] prints some debug information
	 * @param string $format [='js M Y'] date format for full dates
	 * @return string the formatted date or message
	 */
	static function today($year = false, $month = false, $day = false, $current = false, $tstamp = false, $debug = false, $format = 'jS M Y') {
		global $dyear, $dmonth, $dday;

		if($current) { $message = 'Current'; } else { $message = 'Today'; }
		if($tstamp) {
			$record = $tstamp;
			$year_new = date('Y', $tstamp); $month_new = date('n', $tstamp); $day_new = date('j', $tstamp);
		}
		else {
			if(!$year) { $year_new = $dyear; } else { $year_new = $year; }
			if(!$month) { $month_new = $dmonth; } else { $month_new = $month; }
			if(!$day) { $day_new = $dday; } else { $day_new = $day; }
			$record = mkdate($month_new, $day_new, $year_new);
		}
		if($debug) {
			echo date(' H:i, d m Y ',$record), ' xxx ', date(' H:i, d m Y ',mktime(0,0,0)), '<br />';
			echo '<br />', $year_new, ' ', $month_new, ' ', $day_new, ' ', $message;
		}
		if( $record == mktime(0,0,0) ) {
			return '<span style="color:red">'.$message.'</span>';
		}
		else {
			//echo ' post-fail ';
			if(!$month && !$day) { return $year_new; }
			elseif(!$month && !$year) { return datefull($day_new); }
			elseif(!$year && !$day) { return monthfull($month_new); }
			elseif(!$day) { return monthfull($month) . ' ' . $year_new; }
			elseif(!$month) { return 'Day ' . $day_new . ', ' . $year_new; }
			elseif(!$year) { return datefull($day_new) . ' ' . monthfull($month_new); }
			else { return date($format, mkdate($month_new, $day_new,$year_new)); }
		}
	}
	/**
	 * mktime(0,0,0, m, d, y) shortcut
	 * @param type $month
	 * @param type $day
	 * @param type $year
	 * @return type
	 */
	static function mkdate($month = false, $day = false, $year = false) {
		if($year === false) { $year = $GLOBALS['dyear']; }
		if($month === false) { $month = $GLOBALS['dmonth']; }
		if($day === false) { $day = $GLOBALS['dday']; }

		$time = mktime(0,0,0, $month, $day, $year);
		if(!$time) {
			error_log('bad mkdate call');
		}
		return $time;
	}
	static function mkday($day) {
		return mktime(0,0,0, $GLOBALS['dmonth'], $day);
	}

	/**
	 * Returns unix timstamp for a provided z and year
	 * @param int $z day of year, indexed from zero
	 * @param int $y year
	 * @return int timestamp
	 */
	static function mkz($z, $y = false) {
		if(!$y) { $y = $GLOBALS['dyear']; }
		return strtotime('Jan 1st '. (string)$y . ' + ' . (string)$z . ' days');
	}

	/**
	 * evaluates the date from a given day number in a year
	 * @param int $z the day of year (indexed from 0)
	 * @param int $year
	 * @param string $type the date-format [= n i.e. month]
	 * @return string the date-formatted string
	 */
	static function true_z($z, $year, $type = 'n') {
		return date($type, strtotime('Jan 1st '. (string)$year . ' + ' . (string)$z . ' days'));
	}

	/**
	 * Converts a daily offset from 1st Jan 2009 to a timestamp
	 * @param int $day offset in days
	 * @return int timestamp
	 */
	static function daytotime($day) {
		return strtotime('Jan 1st 2009 + '.(string)($day).' days');
	}
	/**
	 * Converts a monthly offset from Jan 2009 to a timestamp
	 * @param int $mon offset in months
	 * @return int timestamp
	 */
	static function monthtotime($mon) {
		return mkdate(1 + $mon, 1, 2009);
	}

	/**
	 * Converts an offset from 1st [curr_month] 2009 to a timestamp, where the offset is an index of
	 * an array of all days for [curr_month] in the history.
	 * @param int $day offset in days
	 * @return int timestamp
	 */
	static function daytotimeCM($day) {
		global $dmonth, $dyear, $lyNum;
		$nly = ($dmonth == 2 && $day > 111) ? $lyNum : 0; //leap-yr fix (111 is num days from 01 feb 2009 to 28 feb 2012)
		$dim = get_days_in_month($dmonth); //non-leap year
		$year = 2009 + floor(($day - $nly) / $dim); //$day starts from 0 so no offset needed
		$trueDay = $day - ($dim * ($year - 2009)) - $nly + 1; //offset now needed
	//	$stuff = array($dmonth, $dyear, $lyNum, $nly, $dim, $year, $trueDay);
	//	print_m($stuff);
		return mkdate($dmonth, $trueDay, $year);
	}

	static function get_days_in_month($month, $year = 2009) {
		return date('t',mkdate($month,2,$year));
	}
	static function get_seasondays($sea, $year = 2009) {
		for($s2 = 0; $s2 < 3; $s2++) { $days += get_days_in_month($snums[$sea][$s2]+1,$year); }
		return $days;
	}
	static function get_days_in_year($year) {
		return date("z", mkdate(12,31,$year)) + 1;
	}

	static function datefull($test) {
		return date('jS', mkdate(1,$test));
	}
	static function monthfull($mn) {
		return $GLOBALS['months3'][intval($mn)-1];
	}
}

?>