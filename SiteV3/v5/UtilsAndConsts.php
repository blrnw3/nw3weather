<?php
const ROOT = '/var/www/html/';
const PHP_INT_MIN = -9223564353720;

class Site {
	const MAIN_ROOT = ROOT;
	const VID_ROOT = '/mnt/nw3-vol1/html/';
	const CAM_ROOT = '/mnt/webcam/html/';
	const IMG_ROOT = '/static-images/';

	const EXEC_PATH = '/usr/bin/php -q /var/www/html/';
	const LIVE_DATA_PATH = '/var/www/html/clientraw.txt';

	const LATITUDE = 51.556;
	const LONGITUDE = -0.154;
	const ZENITH = 90.2;

	const SUN_GRAB_TIME = '0836'; // When to scrape Wonline for EGLL Sun Hrs

	const GRAPH_DIMS_LARGE = ' height="1017" width="850" ';
	const GRAPH_DIMS_SMALL = ' height="619" width="542" ';

	public static $rareTags = '/var/www/html/rareTags.php';

	/**
	* Gets the WD local system time
	* @param bool $getDiff [=true] return as difference from true (server) sys time
	* @return int num seconds
	*/
   public static function sysWDtimes($getDiff = true) {
	   $now = time();
	   $sys = filemtime(self::LIVE_DATA_PATH);
	   $diff = $now - $sys;

	   return $getDiff ? $diff : $sys;
   }
}

class Date {
	public static $months = ['Jan', 'Feb', 'March', 'April', 'May', 'June', 'July', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
	public static $months3 = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
	public static $snames = ['Winter', 'Spring', 'Summer', 'Autumn'];
	public static $snums = [[0,1,11],  [2,3,4], [5,6,7], [8,9,10]];

	public static $monthname;
	public static $date;
	public static $time;
	public static $dst;
	public static $dday;
	public static $dmonth;
	public static $dyear;
	public static $dz;
	public static $dhr;
	public static $dt;
	public static $dtstamp;
	public static $da;
	public static $sunrise;
	public static $sunset;
	public static $dtstamp_yest;
	public static $yr_yest;
	public static $day_yest;
	public static $mon_yest;
	public static $dz_yest;
	public static $firstday;
	public static $too_early;
	public static $season;
	public static $seasonname;
	public static $sc;

	public static function init() {
		self::$monthname = date("F");
		self::$date = date('d M Y');
		self::$time = date('H:i');
		self::$dst = date("I") ? "BST" : "GMT";
		self::$dday = date('j');
		self::$dmonth = date('n');
		self::$dyear = date('Y');
		self::$dz = date('z');
		self::$dhr = date('H');
		self::$dt = date('t');
		self::$dtstamp = mktime(0, 0, 0);
		self::$da = ['j' => self::$dday, 'n' => self::$dmonth, 'Y' => self::$dyear];
		self::$sunrise = date_sunrise(time(), SUNFUNCS_RET_STRING, Site::LATITUDE, Site::LONGITUDE, Site::ZENITH, date('I'));
		self::$sunset = date_sunset(time(), SUNFUNCS_RET_STRING, Site::LATITUDE, Site::LONGITUDE, Site::ZENITH, date('I'));
		self::$dtstamp_yest = mktime(0, 0, 0, self::$dmonth, self::$dday-1, self::$dyear);
		self::$yr_yest = date('Y',self::$dtstamp_yest);
		self::$mon_yest = date('n',self::$dtstamp_yest);
		self::$day_yest = date('j',self::$dtstamp_yest);
		self::$dz_yest = date('z',self::$dtstamp_yest);
		self::$firstday = (date('j') == 1);
		self::$too_early = self::$dday < 15;
		//Season processing
		self::$sc = date('n') % 3 + 1; //Months elapsed during current meteorological season
		for($s2 = 0; $s2 < 4; $s2++) {
			if(in_array(self::$dmonth-1,self::$snums[$s2])) {
				self::$season = $s2+1;
			}
		}
		self::$seasonname = self::$snames[self::$season-1];
	}

	/**
	 * Formats a date. Returns 'Today' if that condition is true. Pass null on y, m, or d to not display that in the output
	 * @param int $year [=false]
	 * @param int $month [=false]
	 * @param int $day [=false]
	 * @param boolean $current [=false] whether to display 'current' or 'Today' as the message if the date is today
	 * @param int $tstamp [=false] use a timestamp instead of d/m/y
	 * @param string $debug [=false] prints some debug information
	 * @param string $format [='js M Y'] date format for full dates
	 * @return string the formatted date or message
	 */
	public static function today($year = false, $month = false, $day = false, $current = false, $tstamp = false, $debug = false, $format = 'jS M Y') {
		if($current) { $message = 'Current'; } else { $message = 'Today'; }
		if($tstamp) {
			$record = $tstamp;
			$year_new = date('Y', $tstamp); $month_new = date('n', $tstamp); $day_new = date('j', $tstamp);
		}
		else {
			if(!$year) { $year_new = self::$dyear; } else { $year_new = $year; }
			if(!$month) { $month_new = self::$dmonth; } else { $month_new = $month; }
			if(!$day) { $day_new = self::$dday; } else { $day_new = $day; }
			$record = mkdate($month_new, $day_new, $year_new);
		}
		if($debug) {
			echo date(' H:i, d m Y ',$record), ' xxx ', date(' H:i, d m Y ',mktime(0,0,0)), '<br />';
			echo '<br />', $year_new, ' ', $month_new, ' ', $day_new, ' ', $message;
		}
		if( $record == self::$dtstamp ) {
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
	 * @param int $month
	 * @param int $day
	 * @param int $year
	 * @return int
	 */
	public static function mkdate($month = false, $day = false, $year = false) {
		if($year === false) { $year = self::$dyear; }
		if($month === false) { $month = self::$dmonth; }
		if($day === false) { $day = self::$dday; }

		$time = mktime(0,0,0, $month, $day, $year);
		if(!$time) {
			error_log('bad mkdate call');
		}
		return $time;
	}
	public static function mkday($day) {
		return mktime(0,0,0, self::$dmonth, $day);
	}
	/**
	 * Returns unix timstamp for a provided z and year
	 * @param int $z day of year, indexed from zero
	 * @param int $y year
	 * @return int timestamp
	 */
	public static function mkz($z, $y = false) {
		if(!$y) { $y = self::$dyear; }
		return strtotime('Jan 1st '. (string)$y . ' + ' . (string)$z . ' days');
	}
	/**
	 * evaluates the date from a given day number in a year
	 * @param int $z the day of year (indexed from 0)
	 * @param int $year
	 * @param string $type the date-format [= n i.e. month]
	 * @return string the date-formatted string
	 */
	public static function true_z($z, $year, $type = 'n') {
		return date($type, strtotime('Jan 1st '. (string)$year . ' + ' . (string)$z . ' days'));
	}
	/**
	 * Converts a daily offset from 1st Jan 2009 to a timestamp
	 * @param int $day offset in days
	 * @return int timestamp
	 */
	public static function daytotime($day) {
		return strtotime('Jan 1st 2009 + '.(string)($day).' days');
	}
	/**
	 * Converts a monthly offset from Jan 2009 to a timestamp
	 * @param int $mon offset in months
	 * @return int timestamp
	 */
	public static function monthtotime($mon) {
		return mkdate(1 + $mon, 1, 2009);
	}
	/**
	 * Converts an offset from 1st [curr_month] 2009 to a timestamp, where the offset is an index of
	 * a zero-based array of all days for [curr_month] in the history.
	 * @param int $days offset in days
	 * @return int timestamp
	 */
	public static function daytotimeCM($days) {
		$days++; // Convert to 1-based
		$y = 2009; $m = self::$dmonth;
		while ($days > 0) {
			$dim = get_days_in_month($m, $y);
			if($days <= $dim) {
				break;
			}
			$days -= $dim;
			$y++;
		}
		return mkdate($m, $days, $y);
	}
	public static function time_av($times) {
		for($i = 0; $i < count($times); $i++) {
			if(!strpos($times[$i], '*')) {
				if(strpos($times[$i], ':') > 0) { $mktimes[$i] = strtotime($times[$i]); }
				elseif($times[$i] < 1 && $times[$i] > 0) { $mktimes[$i] = decimal_timefix($times[$i]); }
			}
		}
		$meanTime = mean($mktimes);
		return ($meanTime === '') ? 'n/a' : date(' H:i ', $meanTime);
	}
	public static function decimal_timefix($dec) {
		$hr = floor($dec*24);
		return zerolead($hr) . ':' . round(($dec*24-$hr)*60);
	}
	public static function timeformat($time) {
		if(strpos($time, ':')) { return date('H:i', strtotime($time)); }
		else { return ''; }
	}

	public static function get_days_in_month($month, $year = 2009) {
		return date('t',mkdate($month,2,$year));
	}
	public static function get_seasondays($sea, $year = 2009) {
		$days = 0;
		for($s2 = 0; $s2 < 3; $s2++) { $days += get_days_in_month(self::$snums[$sea][$s2]+1,$year); }
		return $days;
	}
	public static function get_days_in_year($year) {
		return date("z", mkdate(12,31,$year)) + 1;
	}

	public static function datefull($test) {
		return date('jS', mkdate(1,$test));
	}
	public static function monthfull($mn) {
		return self::$months3[intval($mn)-1];
	}
	/**
	* Converts a number of seconds to secs, mins, hours or days depending on value of $secs
	* @param int $secs the number of seconds to convert
	* @return string nice Human-readable output
	*/
   public static function secsToReadable($secs) {
	   if($secs < 100) {
		   return $secs .' s';
	   } else {
		   $diff = $secs / 60;
	   }

	   if($diff < 100) {
		   $ago = round($diff) .' mins';
	   } elseif($diff < 3000) {
		   $ago = round($diff / 60) .' hours';
	   } else {
		   $ago = round($diff / 60 / 24) .' days';
	   }

	   return $ago;
   }
   /**
	* Convert a YYYYMMDD string to a unix timestamp
	* @param type $stamp
	* @return type
	*/
   public static function datestamp_to_ts($stamp) {
	   return mkdate(substr($stamp,4,2),substr($stamp,6,2),  substr($stamp,0,4));
   }
}
// Initialize computed static fields
Date::init();

class HTML {
	const CHECK = 'checked="checked"';
	const DISABLE = 'disabled="disabled"';
	const SELECT = 'selected="selected"';

	/**
	* Give a tooltip to a string
	* @param type $title the tooltip
	* @param type $value the string to tooltip
	* @param type $show [=false] set true to display dotted underline
	* @return string
	*/
   public static function acronym($title, $value, $show = false) {
	   $type = $show ? 'abbr' : 'span';
	   $tag = '<'. $type .' title="' . $title. '">'. $value. '</'. $type .'>';
	   return $tag;
   }

   /**
	* makes an opening html table tag. Null-pass enabled.
	* @param string $class [= table1]
	* @param string $width [=99%]
	* @param number $cellpadding [=3]
	* @param bool $centre [=false] position table centrally (auto margin)
	* @param number $cellspacing [=0]
	*/
   public static function table( $class = null, $width = null, $cellpadding = null, $centre = false, $cellspacing = 0 ) {
	   if(is_null($class)) { $class = 'table1'; }
	   if(is_null($width)) { $width = '99%'; }
	   if(is_null($cellpadding)) { $cellpadding = 3; }
	   $centrality = $centre ? 'style="margin:auto;"' : '';
	   echo '<table '.$centrality.' class="'.$class.'" width="'.$width.'" cellpadding="'.$cellpadding.'" cellspacing="'.$cellspacing.'">
		   ';
   }
   public static function table_end() {
	   echo '</table>
		   ';
   }

   /**
	* makes a table row with set class. Pass null to give straight &lt;tr&gt;
	* @param type $class [=table-top]
	*/
   public static function tr( $class = 'table-top' ) {
	   $class2 = ( !is_null($class) ) ? ' class="'.$class.'"' : '';
	   echo '<tr'.$class2.'>
	   ';
   }
   public static function tr_end() {
	   echo '</tr>
		   ';
   }

   /**
	* make table data cell
	* @param type $value
	* @param string $class [=null] defaults to td4
	* @param int $width in percent [=false]
	* @param int $colspan [=false]
	* @param int $rowspan [=false]
	*/
   public static function td($value, $class = null, $width = false, $colspan = false, $rowspan = false) {
	   if(is_null($class)) { $class = 'td4'; }
	   if($width) { $wid = ' width="'.$width.'%"'; }
	   if($colspan) { $csp = ' colspan="'.$colspan.'"'; }
	   if($rowspan) { $rsp = ' rowspan="'.$rowspan.'"'; }
	   echo '<td class="'.$class.'"'.$wid.$csp.$rsp.'>'.$value.'</td>
		   ';
   }

  public static function tableHead($text, $colspan = 3) {
	   echo '<tr class="table-head"><td class="td12" style="padding:0.5em" colspan="'.$colspan.'">'.$text.'</td></tr>
			   ';
   }

   /**
	* Produces an html img, with optional surrounding anchor<br />
	* Null-pass enabled.
	* @param string $src required
	* @param string $alt required
	* @param float $margin (in em) required
	* @param string $title [=null]
	* @param int $width [=null]
	* @param int $height [=null]
	* @param string $anchor title of anchor [=null]
	* @param string $extras any other attributes [=null]
	* @return void echoes the well-formed xhtml
	*/
   public static function img($src, $alt, $margin, $title = null, $width = null, $height = null, $anchor = null, $extras = null) {
	   $dims = ($width === null) ? '' : " width='$width' height='$height' ";
	   $tit = ($title === null) ? '' : " title='$title' ";
	   $mores = ($extras === null) ? '' : $extras;
	   $imgString = "<img src='$src' style='margin:{$margin}em;' alt='$alt' $dims $tit $mores />";

	   echo ($anchor === null) ? $imgString : "<a href='$src' title='$anchor'>$imgString</a>";
   }

   /**
	* Light or Dark alternating odd/even
	* @param type $i
	* @return type light or dark
	*/
   public static function colcol($i) {
	   return ($i % 2 == 0) ? 'light' : 'dark';
   }

   /**
	* Produces an html form for inputting year, month and, optionally, day
	* @param int $yproc
	* @param int $mproc
	* @param int $dproc [= 0] leave blank if only month/year selector
	*/
  public static function dateFormMaker($yproc, $mproc, $dproc = 0) {
	   echo '<select name="year">';
	   for($i = 2009; $i <= Date::$dyear; $i++) {
		   $selected = ($i == $yproc) ? 'selected="selected"' : '';
		   echo '<option value="', $i, '" ', $selected, '>', $i, '</option>
			   ';
	   }
	   echo '</select>
		   <select name="month">';
	   for($i = 1; $i <= 12; $i++) {
		   $selected = ($i == $mproc) ? 'selected="selected"' : '';
		   echo '<option value="', $i, '" ', $selected, '>', Date::$months[$i-1], '</option>
			   ';
	   }

	   if($dproc) {
		   echo '</select>
			   <select name="day">';
		   for($i = 1; $i <= 31; $i++) {
			   $selected = ($i == $dproc) ? 'selected="selected"' : '';
			   echo '<option value="', $i, '" ', $selected, '>', zerolead($i), '</option>
				   ';
		   }
	   }
	   echo '</select>';
   }

   /**
	* Makes a button to cycle through a drop down
	* @param bool $dir decrease index (false) or increase (true)
	* @param string $href button link
	* @param string $title link tooltip
	*/
   public static function dropdownCycle($dir, $href, $title, $disabled = false) {
	   $lg = $dir ? 9654 : 9664;
	   echo '<a class="arrow" href="'.PAGE_NAME. '?'. $href .'" title="'. $title .'">
		   &#'. $lg .';
		   </a>
	   ';
   }

	/**
	 * does a PHP <code>print_r</code> with pre-fromat html tags to make the output more readable on a webpage
	 * @param mixed $var the variable to print
	 */
	public static function print_m($var) {
		echo '<pre>';
		print_r($var);
		echo '</pre>';
	}

}

class Util {
	/**
	* Takes mean of an array, discarding blank (0 length and '-' values)
	* @param mixed $array to mean over
	* @return mixed array on success, empty string on receiving non-array input or an array of all blanks
	*/
   public static function mean($array, $cnt = false) {
	   if(!is_array($array)) {
		   return '';
	   }

	   $validCnt = 0;
	   foreach ($array as $value) {
		   if(!self::isBlank($value)) {
			   $validCnt++;
		   }
	   }

	   $finalCnt = $cnt ? $cnt : $validCnt;

	   return ($validCnt > 0) ? array_sum($array)/$finalCnt : '';
   }
   /**
	* Min, max, mean, or count; computed based on $type
	* @param mixed $arr the 1D array
	* @param enum $type 0: min, 1: max, 2: mean, 3: count > 0
	* @return mixed|number
	*/
   public static function mom($arr, $type) {
	   if($type == 0) { return mymin($arr); }
	   elseif($type == 1) { return mymax($arr); }
	   elseif($type == 3) { return sum_cond($arr, true, 0); }
	   else { return mean($arr); }
   }

   /**
	* Variable operator evaluator for less than and greater than
	* For min, evalues var1 &lt; var2. For max, var1 &gt; var 2
	* @param number $var1 first var
	* @param number $var2 second var
	* @param bool $minormax whether to use lt [0] or gt [1]
	* @return bool result
	*/
   public static function opmom($var1, $var2, $minormax) {
	   if($minormax == 0) { return $var1 < $var2; }
	   else { return $var1 > $var2; }
   }

	public static function median($arr) {
		sort($arr);
		return $arr[ floor( count($arr) / 2 ) ];
	}

	public static function mymax($arr, $debug = false) {
		$max = -1 * PHP_INT_MAX;
		if(!is_array($arr)) return null;

		foreach($arr as $val) {
			if(!isBlank($val)) {
				$val = floatval($val);
				if($val > $max) { $max = $val; }
			} else {
				//echo "<br />empty string in array ";
				//$badarr = true;
			}
		}
		if($max == -1 * PHP_INT_MAX) return null;
		return $max;
	}
	public static function mymin($arr, $debug = false) {
		$min = PHP_INT_MAX;
		if(!is_array($arr)) return null;

		foreach($arr as $val) {
			if(!isBlank($val)) {
				$val = floatval($val);
				if($val < $min) { $min = $val; }
			}
		}
		if($min == PHP_INT_MAX) return null;
		return $min;
	}

	public static function sum_cond($arr, $isGreater, $limit, $isMean = false) {
		$cnt = $blanks = 0;

		if($isGreater) {
			foreach($arr as $val) {
				if( !isBlank($val) ) {
					if($val > $limit) {
						$cnt++;
					}
				} else {
					$blanks++;
				}
			}
		} else {
			foreach($arr as $val) {
				if( !isBlank($val) ) {
					if($val < $limit) {
						$cnt++;
					}
				} else {
					$blanks++;
				}
			}
		}

		if($blanks == count($arr))
			return null;

		if($isMean) { return mean($arr, $cnt); }
		return $cnt;
	}

	/**
	 * My idea of how the modulo operator <i>should</i> work, i.e. wraps around on negative nums
	 * @param int $int num
	 * @param int $cnt divisor
	 * @return int remainder
	 */
	public static function mod($int, $cnt) {
		return ($int < 0) ? ($cnt + $int) : ($int % $cnt);
	}


	public static function zerolead($tag) {
		$tag = intval($tag);
		if($tag < 10 && $tag >= 0) { $tag = '0'.$tag; }
		return $tag;
	}
	public static function roundi($val) {
		if($val < 10) { $dp = 1; }
		return round($val,$dp);
	}
	/**
	 * Converts a boolean into a true/false string (good for JS)
	 * @param boolean $bool the boolean
	 * @return string true or false
	 */
	public static function makeBool($bool) {
		return $bool ? 'true' : 'false';
	}
	/**
	 * Rounds to the nearest $size
	 * @param float $val
	 * @param number $size
	 * @param int $dir floor, ceil or round (the default)
	 * @return the rounded value
	 */
	public static function roundbig($val, $size = 5, $dir = false) {
		$size = ($size < 1) ? 1 : $size;
		if($dir === 0) { $new = floor($val / $size); }
		elseif($dir === 1) { $new = ceil($val / $size); }
		else { $new = round($val / $size); }
		return $new * $size;
	}
	/**
	 * Returns a string representation of $val / $div * 100
	 * @param float $val
	 * @param float $div
	 * @param int $dp number of decimal places to show [0]
	 * @param boolean $showp show percent symbol [true]
	 * @param boolean $brackets show enclosing brackets [true]
	 * @return string
	 */
	public static function percent($val, $div, $dp = 0, $showp = true, $brackets = true) {
		$dat = sprintf('%.'.$dp.'f',$val/$div*100);
		if($showp) { $dat .= '%'; }
		if($brackets) { $dat = '(' . $dat . ')'; }
		return $dat;
	}
	public static function find_nearest($val, $arr, $up = false) {
		for($i = 0; $i < count($arr); $i++) {
			$near[$i] = $val - $arr[$i];
			if($up) { if($near[$i] > 0) { $near[$i] = -99; } }
			else { $near[$i] = abs($near[$i]); }
		}
		if($up) { $nearest = array_search( max($near), $near ); }
		else { $nearest = array_search( min($near), $near ); }
		return $arr[$nearest];
	}
	public static function array_dswap($arr) {
		$keyj = array_keys($arr);
		//print_r($keyj); echo '<br /><br />';
		for($i = 0; $i < count($arr[$keyj[0]]); $i++) {
			for($j = 0; $j < count($arr); $j++) { $new[$i][$j+min($keyj)] = $arr[$j+min($keyj)][$i]; }
		}
		//echo count($arr[$keyj[0]]), ' x <br />';
		return $new;
	}
	public static function clearblank($val) {
		return !($val === '' || $val === '-' || $val === null);
	}
	public static function clearblank2($val) {
		$b = floatval($val);
		if($b == 0) { $val = ''; }
		return $b;
	}
	/**
	 * Tests for a blank string/value. NB: '-' also counts as empty.
	 * @param mixed $val
	 * @return bool
	 */
	public static function isBlank($val) {
		return (strlen($val) === 0 || $val === '-');
	}
	/**
	 * Tests for a blank string/value. NB: '-' also counts as empty.
	 * @param mixed $val
	 * @return bool
	 */
	public static function isNotBlank($val) {
		return !isBlank($val);
	}

	public static function monthly_extras($array) {
		return array( sum_cond($array[20],0,0), sum_cond($array[28],1,15), sum_cond($array[13],1,0.1), sum_cond($array[13],1,1),
			conv( sum_cond($array[13],1,0.1,true), 2 )  );
	}

	/**
	 * Rounds a value to a set number of decimal places
	 * @param type $value the value to round
	 * @param string $dp the number of decimal places [= 1]
	 * @return type the rounded string
	 */
	public static function roundToDp($value, $dp = 1) {
		$dp = '.'.$dp.'f';
		return sprintf("%$dp", $value);
	}

	/**
	 * Searches a string for a number of terms, returning true if any are contained within
	 * @param string $str subject
	 * @param mixed $searchTerms array of search terms, or single string
	 * @return boolean true if the string contains of the searched-for terms, false otherwise
	 */
	public static function strContains($str, $searchTerms) {
		if(is_array($searchTerms)) {
			foreach ($searchTerms as $search) {
				if(strpos($str, $search) !== false) {
					return true;
				}
			}
			return false;
		} else {
			return (strpos($str, $searchTerms) !== false);
		}
	}

	/**
	 * Safely accesses a url (using a timeout) and gets the file into an array
	 * @param string $url to parse
	 * @param int $timeout [=5] in seconds
	 * @return array of each line, or false on failure
	 */
	public static function urlToArray($url, $timeout = 5) {
		$ctx = stream_context_create( array( 'http'=> array('timeout' => $timeout) ) );
		return file($url, false, $ctx);
	}

	public static function listdir($dir) {
		return array_diff(scandir($dir), array('.', '..'));
	}
}

class Misc {

	public static function extract_for_timelapse($year, $month = 0, $day = 0, $freq = 1, $twiset = null, $cam = "sky",
		$frame_rate = 24, $crf = 25, $name = null, $scale = "1080x720") {

		$months = $month ? array($month) : range(1, 12);
		$src = CAM_ROOT."camchive/$cam";
		if(is_null($name)) {
			$period = is_null($twiset) ? "all" : "sun$twiset";
			$name = "${cam}cam_test_${year}_m${month}_d${day}_f${freq}_p-${period}_r${frame_rate}";
		}
		$tmpdir = VID_ROOT."tmpdir/$name";
		$outfile = VID_ROOT."timelapse/${name}.mp4";

		echo shell_exec("mkdir $tmpdir");

		foreach($months as $mi) {
			$m = zerolead($mi);
			$mbase = "$src/$year/$m/";
			$days = ($day == 0) ? listdir($mbase) : array(zerolead($day));
			foreach($days as $d) {
				$dbase = $mbase . $d . "/";
				$sproc = mkdate($mi, intval($d), $year);
				if(is_null($twiset)) {
					$sunrise = -1;
					$sunset = 9999;
				} else {
					$sunrise = intval(date_sunrise($sproc, SUNFUNCS_RET_DOUBLE, $lat, $lng, $zenith, date('I', $sproc)) * 60 - $twiset);
					$sunset = intval(date_sunset($sproc, SUNFUNCS_RET_DOUBLE, $lat, $lng, $zenith, date('I', $sproc)) * 60 + $twiset);
				}
				echo "Processing $dbase: sunrise: $sunrise, sunset: $sunset\n";
				foreach(listdir($dbase) as $c){
					$hr = substr($c, 0, 2);
					$min = substr($c, 2, 2);
					$num = intval($hr) * 60 + intval($min);
					if($num % $freq == 0 && $num > $sunrise && $num < $sunset) {
						$trgt = "$tmpdir/$year$m${d}_$c";
						if(file_exists($dbase . $c) && filesize($dbase . $c) > 1024) {
							copy($dbase . $c, $trgt);
						} else {
							echo "$c missing or empty \n";
						}
					}
				}
			}
		}
		$cmd = "/usr/bin/ffmpeg -r $frame_rate -pattern_type glob -y -i \"$tmpdir/*.jpg\" -crf $crf -vf scale=$scale $outfile";
		echo $cmd . "\n";

		echo shell_exec("ls $tmpdir");

		$ffmpeg_res = shell_exec($cmd);
		echo $ffmpeg_res . "\n";

		$res = shell_exec("rm -rf $tmpdir");
		echo $res . "\n";
		return $outfile;
	}

}


?>