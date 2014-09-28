<?php
namespace nw3\app\util;

use nw3\config\Station;

/**
 *
 * @author Ben LR
 */
class Date {

	/** Number of seconds in a single day */
	const secs_DAY = 86400;

	static $months = ['Jan', 'Feb', 'March', 'April', 'May', 'June', 'July', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
	static $months3 = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
	static $monthsn = [1,2,3,4,5,6,7,8,9,10,11,12];

	static $seasons = ['Winter', 'Spring', 'Summer', 'Autumn'];
	static $season_month_nums = [[1,2,12], [3,4,5], [6,7,8], [9,10,11]];
	static $month_season_map = [];

	 //number of leap years since 2009
	const num_leap_yrs = 1;

	public static $is_dark;

	/**
	 * Loads up the global define with some useful constants
	 */
	public static function initialise() {
//		$debug_offset = self::secs_DAY * 218; //When testing, it could be useful to change this
//		$now = time() - $debug_offset;
		$debug_date = new \DateTime();
		$debug_date->setDate(2013, 11, 9);
//		$debug_date->setTime(6, 0, 0);
		$now = $debug_date->getTimestamp();
		define('D_now', $now);

		//Define (globally) some of the most useful date-based pseudo-constants
		define('D_monthname', date('F', $now));
		define('D_monthshort', date('M', $now));
		define('D_date', date('D jS M Y', $now));
		define('D_time', date('H:i', $now));
		define('D_dst', date('i', $now) ? 'BST' : 'GMT');
		define('D_is_dst', date('i', $now));

		define('D_hour', date('H', $now));
		define('D_day', (int)date('j', $now));
		define('D_month', (int)date('n', $now));
		define('D_year', (int)date('Y', $now));
		define('D_doy', (int)date('z', $now));
		define('D_dim', (int)date('t', $now));

		define('D_datestamp', date('Ymd', $now));
		define('D_timestamp', date('Hi', $now));

		list($sunrise, $sunset) = self::get_rise_set();
		define('D_sunrise', $sunrise);
		define('D_sunset', $sunset);
		self::$is_dark = ($now < $sunrise || $now > $sunset);

		$yest = $now - self::secs_DAY;
		define('D_yest', $yest);
		define( 'D_yest_day', (int)date('j', $yest) );
		define( 'D_yest_month', (int)date('n', $yest) );
		define( 'D_yest_year', (int)date('Y', $yest) );
		define( 'D_yest_doy', (int)date('z', $yest) );

		//Determine current season and fill-in the month map
		foreach(self::$season_month_nums as $s => $months) {
			foreach($months as $m) {
				self::$month_season_map[$m] = $s;
			}
		}
		define('D_season', self::$month_season_map[D_month]);
		define('D_seasonname', self::$seasons[D_season]);
	}

	static function short_mon($month_index_natural) {
		return self::$months3[$month_index_natural-1];
	}
	static function mon($month_index_natural) {
		return date('F', self::mkdate($month_index_natural, 1));
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
	 * mktime(0,0,0, m, d, y) shortcut
	 * @param type $month
	 * @param type $day
	 * @param type $year
	 * @return type
	 */
	static function mkdate($month = false, $day = false, $year = false) {
		if($year === false) { $year = D_year; }
		if($month === false) { $month = D_month; }
		if($day === false) { $day = D_day; }

		$time = mktime(0,0,0, $month, $day, $year);
		return $time;
	}
	static function mkday($day) {
		return mktime(0,0,0, D_month, $day, D_year);
	}

	/**
	 * Returns unix timstamp for a provided z and year
	 * @param int $z day of year, indexed from zero
	 * @param int $y year
	 * @return int timestamp
	 */
	static function mkz($z, $y = false) {
		$y = $y || D_year;
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
		return self::mkdate(1 + $mon, 1, 2009);
	}


	/**
	 * Get day in year (aka z) for given dmy
	 * @param type $day
	 * @param type $month
	 * @param type $year
	 * @return type
	 */
	static function get_z($day, $month, $year = 2009) {
		return date('z', self::mkdate($month, $day, $year));
	}

	static function get_days_in_month($month, $year = 2009) {
		return (int)date('t',self::mkdate($month,2,$year));
	}
	static function get_days_in_month_from_date_string($dt_string) {
		$dt = new \DateTime($dt_string);
		return (int) $dt->format('t');
	}
	static function get_days_in_year($year) {
		return date('z', self::mkdate(12,31,$year)) + 1;
	}

	static function datefull($test) {
		return date('jS', self::mkdate(1,$test));
	}

	/**
	 * Sunrise and set times for a specfied offset from the config zenith
	 * @param float $zenith_offset [=0]
	 * @param int $datetime [=null] calculation point
	 * @return array 0=>sunrise, 1=>sunset
	 */
	static function get_rise_set($zenith_offset = 0, $datetime = null) {
		$lat = Station::LAT;
		$lng = Station::LNG;
		$zenith = Station::ZENITH + $zenith_offset;
		$datetime = ($datetime === null) ? D_now : $datetime;
		return [
			date_sunrise($datetime, SUNFUNCS_RET_TIMESTAMP, $lat, $lng, $zenith, D_is_dst),
			date_sunset($datetime, SUNFUNCS_RET_TIMESTAMP, $lat, $lng, $zenith, D_is_dst)
		];
	}

	/** Returns month number (1-12) of the current season's starting month (Dec, Mar, Jun, or Sep) */
	static function get_current_season_start_month() {
		return self::$season_month_nums[D_season][0];
	}

	/** TODO - great candidate for unit testing */
	static function get_current_season_days_elapsed() {
		return ceil((D_now - self::mkdate(self::get_current_season_start_month(), 1)) / 86400.0);
	}

	static function season_name_from_month($m) {
		return self::$seasons[self::$month_season_map[$m]];
	}
}

?>
