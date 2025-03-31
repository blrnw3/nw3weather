<?php
include('datfuncdef.php');
include_once('climavs.php');

/**
 * Convert from UK units to US or EU, and neaten-up <br />
 * 0: n/a, 1: temp, 2: rain, 3: pressure, 4: wind, 5: humidity, 6: snow, 7: height, 8: days, 9: hrs, 10: abshum/airdensity <br />
 * 1.1: temperature anomaly, 2.1: rain rate, 4.5: degree name
 * Specials: 0.1: hail, 0.2: thunder
 * @param mixed $tag the value to convert
 * @param float $type the coversion type (see description for possibilities)
 * @param boolean $unit display unit [= true]
 * @param boolean $sign display sign +/- [= false]
 * @param int $dpa decimal pt adjustment [= 0]
 * @param boolean $abs take the absolute value [= false]
 * @param boolean $debug [= false]
 * @param boolean $noText [= false] pass true to prevent textual output (e.g. for charting)
 * @return string of the converted value
 */
function conv($tag, $type, $unit = true, $sign = false, $dpa = 0, $abs = false, $debug = false, $noText = false) {
	global $conv_units, $imperial, $metric;

	if($type === false) {
		return $tag;
	} else {
		$dat = floatval($tag);
	}
	if( is_null($tag) ) { return 'null'; } //null takes grater precedence than blank
	if( isBlank($tag) ) { if($debug) { echo 'Bad tag<br />'; } return ''; }

	if($unit) { $uarr = $conv_units; }

	if($type == 4.5) { return $noText ? $dat : degname($dat); }
	elseif($type == 0.1) { return $noText ? $dat : hailname($dat); }
	elseif($type == 0.2) { return $noText ? $dat : thundername($dat); }

	elseif($type == 2.1) { if($unit) { $uarr[2] .= '/h'; } if($dat > 5) { $dpa = -1; } }
	elseif($type == 8 && $dat > 1) { $uarr[8] .= 's'; }

	if($sign) { $pom = '+'; } else { $pom = ''; }

	$div = array(1, 5/9,25.4,33.864,1, 1,2.54,0.3048, 1,1,1.262); //Imperial conversion divisors
	if($imperial) {
		$dat = $dat/$div[intval($type)];
		$dp = array(0, 1,2,2, 1,0, 1,0, 0,0,3);
		if($type == 1) { $dat += 32; }
		elseif($type == 2 && $dat < 0.01 && $dat > 0) { $dp[2] = 3; }
	}
	else { $dp = array(0, 1,1,0, 1,0, 0,0, 0,0,2); } //Default decimal precision for non-Imperial
	if($metric && $type == 4) { $dat *= 1.6093; }
	if(($type == 9 || $type == 6) && $dat < 1 && $dat > 0) { $dpa++; }
	if($abs) { $dat = abs($dat); }

	$dpf = round($dp[intval($type)]+$dpa);
	$strret = $pom.'.'. $dpf.'f';
	return sprintf("%$strret", $dat).$uarr[intval($type)];
}

function hailname($val) {
	$types = array('No', 'Small', 'Med', 'Large');
	return $types[$val];
}
function thundername($val) {
	$types = array('N', 'Y', 'Light', 'Mod', 'Sevr');
	return $types[$val];
}
function degname($winddegree) {
	$windlabels = array ("N","NNE", "NE", "ENE", "E", "ESE", "SE", "SSE", "S", "SSW","SW", "WSW", "W", "WNW", "NW", "NNW","N");
	return $windlabels[ round($winddegree / 22.5, 0) ];
}

/**
 * Convert from wind speeed in mph to a beaufort value and descriptive string
 * @param int $mph raw value
 * @return string bft-descrip + bft-force
 */
function bft($mph) {
	$bftscale = array(1,3,7, 12,17,24, 30,38,46, 54,63,73, 99);
	$bftword = array('Calm', 'Light air', 'Light breeze', 'Gentle breeze', 'Moderate breeze', 'Fresh breeze', 'Strong breeze', 'Near gale', 'Gale',
			'Severe gale', 'Storm', 'Violent storm', 'Hurricane');
	$cnt = 0;
	foreach ($bftscale as $value) {
		if($mph <= $value) {
			return $bftword[$cnt] . " (F$cnt)";
		}
		$cnt++;
	}
	return "Apocalypse";
}

function zerolead($tag) {
	$tag = intval($tag);
	if($tag < 10 && $tag >= 0) { $tag = '0'.$tag; }
	return $tag;
}
function roundi($val) {
	if($val < 10) { $dp = 1; }
	return round($val,$dp);
}

/**
 * Converts a boolean into a true/false string (good for JS)
 * @param boolean $bool the boolean
 * @return string true or false
 */
function makeBool($bool) {
	return $bool ? 'true' : 'false';
}

/**
 * Rounds to the nearest $size
 * @param float $val
 * @param number $size
 * @param int $dir floor, ceil or round (the default)
 * @return the rounded value
 */
function roundbig($val, $size = 5, $dir = false) {
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
function percent($val, $div, $dp = 0, $showp = true, $brackets = true) {
	$dat = sprintf('%.'.$dp.'f',$val/$div*100);
	if($showp) { $dat .= '%'; }
	if($brackets) { $dat = '(' . $dat . ')'; }
	return $dat;
}
function find_nearest($val, $arr, $up = false) {
	for($i = 0; $i < count($arr); $i++) {
		$near[$i] = $val - $arr[$i];
		if($up) { if($near[$i] > 0) { $near[$i] = -99; } }
		else { $near[$i] = abs($near[$i]); }
	}
	if($up) { $nearest = array_search( max($near), $near ); }
	else { $nearest = array_search( min($near), $near ); }
	return $arr[$nearest];
}

function time_av($times) {
	for($i = 0; $i < count($times); $i++) {
		if(!strpos($times[$i], '*')) {
			if(strpos($times[$i], ':') > 0) { $mktimes[$i] = strtotime($times[$i]); }
			elseif($times[$i] < 1 && $times[$i] > 0) { $mktimes[$i] = decimal_timefix($times[$i]); }
		}
	}
	$meanTime = mean($mktimes);
	return ($meanTime === '') ? 'n/a' : date(' H:i ', $meanTime);
}
function decimal_timefix($dec) {
	$hr = floor($dec*24);
	return zerolead($hr) . ':' . round(($dec*24-$hr)*60);
}
function timeformat($time) {
	if(strpos($time, ':')) { return date('H:i', strtotime($time)); }
	else { return ''; }
}

function array_dswap($arr) {
	$keyj = array_keys($arr);
	//print_r($keyj); echo '<br /><br />';
	for($i = 0; $i < count($arr[$keyj[0]]); $i++) {
		for($j = 0; $j < count($arr); $j++) { $new[$i][$j+min($keyj)] = $arr[$j+min($keyj)][$i]; }
	}
	//echo count($arr[$keyj[0]]), ' x <br />';
	return $new;
}

function clearblank($val) {
	return !($val === '' || $val === '-' || $val === null);
}
function clearblank2($val) {
	$b = floatval($val);
	if($b == 0) { $val = ''; }
	return $b;
}

/**
 * Tests for a blank string/value. NB: '-' also counts as empty.
 * @param mixed $val
 * @return bool
 */
function isBlank($val) {
	return (strlen($val) === 0 || $val === '-');
}
/**
 * Tests for a blank string/value. NB: '-' also counts as empty.
 * @param mixed $val
 * @return bool
 */
function isNotBlank($val) {
	return !isBlank($val);
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
function today($year = false, $month = false, $day = false, $current = false, $tstamp = false, $debug = false, $format = 'jS M Y') {
	global $dyear, $dmonth, $dday, $dtstamp;

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
	if( $record == $dtstamp ) {
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
 * @return int
 */
function mkdate($month = false, $day = false, $year = false) {
	if($year === false) { $year = $GLOBALS['dyear']; }
	if($month === false) { $month = $GLOBALS['dmonth']; }
	if($day === false) { $day = $GLOBALS['dday']; }

	$time = mktime(0,0,0, $month, $day, $year);
	if(!$time) {
		error_log('bad mkdate call');
	}
	return $time;
}
function mkday($day) {
	return mktime(0,0,0, $GLOBALS['dmonth'], $day);
}

/**
 * Returns unix timstamp for a provided z and year
 * @param int $z day of year, indexed from zero
 * @param int $y year
 * @return int timestamp
 */
function mkz($z, $y = false) {
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
function true_z($z, $year, $type = 'n') {
	return date($type, strtotime('Jan 1st '. (string)$year . ' + ' . (string)$z . ' days'));
}

/**
 * Converts a daily offset from 1st Jan 2009 to a timestamp
 * @param int $day offset in days
 * @return int timestamp
 */
function daytotime($day) {
	return strtotime('Jan 1st 2009 + '.(string)($day).' days');
}
/**
 * Converts a monthly offset from Jan 2009 to a timestamp
 * @param int $mon offset in months
 * @return int timestamp
 */
function monthtotime($mon) {
	return mkdate(1 + $mon, 1, 2009);
}

/**
 * Converts an offset from 1st [curr_month] 2009 to a timestamp, where the offset is an index of
 * a zero-based array of all days for [curr_month] in the history.
 * @param int $days offset in days
 * @return int timestamp
 */
function daytotimeCM($days) {
	$days++; // Convert to 1-based
	$y = 2009; $m = $GLOBALS["dmonth"];
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

function rate_fix($rate) {
	if(round($rate) > 5) { return round($rate); } else { return $rate; }
}

/**
 * Alias of roundToDp
 */
function myround($val,$dp = 1) {
	return roundToDp($val, $dp);
}

function norain_fix($time, $val, $rate = false) {
	$thresh = 0.1; if($rate) { $thresh = 0.5; }
	if($val < $thresh) { return 'n/a'; }
	else { return $time; }
}

/**
 * Takes mean of an array, discarding blank (0 length and '-' values)
 * @param mixed $array to mean over
 * @return mixed array on success, empty string on receiving non-array input or an array of all blanks
 */
function mean($array, $cnt = false) {
	if(!is_array($array)) {
		return '';
	}

	$validCnt = 0;
	foreach ($array as $value) {
		if(!isBlank($value)) {
			$validCnt++;
		}
	}

	$finalCnt = $cnt ? $cnt : $validCnt;

	return ($validCnt > 0) ? array_sum($array)/$finalCnt : '';
}

/**
 * Give a tooltip to a string
 * @param type $title the tooltip
 * @param type $value the string to tooltip
 * @param type $show [=false] set true to display dotted underline
 * @return string
 */
function acronym($title, $value, $show = false) {
	$type = $show ? 'abbr' : 'span';
	$tag = '<'. $type .' title="' . $title. '">'. $value. '</'. $type .'>';
	return $tag;
}

/**
 * Min, max, mean, or count; computed based on $type
 * @param mixed $arr the 1D array
 * @param enum $type 0: min, 1: max, 2: mean, 3: count > 0
 * @return mixed|number
 */
function mom($arr, $type) {
	if($type == 0) { return mymin($arr); }
	elseif($type == 1) { return mymax($arr); }
	elseif($type == 3) { return sum_cond($arr, true, 0); }
	else { return mean($arr); }
}

// function min2D($arr, $extent) {
// 	foreach($arr as $arrOut) {
// 		$min = min($arrOut);
// 	}
// }

// function mom2D($arr, $type, $extent) {
// 	if($type == 0) { return min($arr); }
// 	elseif($type == 1) { return mymax($arr); }
// 	else { return mean($arr); }
// }

/**
 * Variable operator evaluator for less than and greater than
 * For min, evalues var1 &lt; var2. For max, var1 &gt; var 2
 * @param number $var1 first var
 * @param number $var2 second var
 * @param bool $minormax whether to use lt [0] or gt [1]
 * @return bool result
 */
function opmom($var1, $var2, $minormax) {
	if($minormax == 0) { return $var1 < $var2; }
	else { return $var1 > $var2; }
}

function get_days_in_month($month, $year = 2009) {
	return date('t',mkdate($month,2,$year));
}
function get_seasondays($sea, $year = 2009) {
	$days = 0;
	for($s2 = 0; $s2 < 3; $s2++) { $days += get_days_in_month($snums[$sea][$s2]+1,$year); }
	return $days;
}
function get_days_in_year($year) {
	return date("z", mkdate(12,31,$year)) + 1;
}

function datefull($test) {
	return date('jS', mkdate(1,$test));
}
function monthfull($mn) {
	return $GLOBALS['months3'][intval($mn)-1];
}

/**
 * Pad a string to a specified length. Excess is marked with an elipsis
 * @param string $string
 * @param int $length
 * @return string
 */
function str_subpad($string, $length) {
	$padding = (strlen($string) > $length) ? "...  " : " ";
	return substr( str_pad($string, $length), 0, $length - strlen($padding) ) . $padding;
}

/**
 * Logger for http-based requests. Line includes: date-time, custom content, requestURL, ip, userAgent
 * @param string $txtname name of file in the Logs directory to append
 * @param string $content the custom logging var
 */
function log_events($txtname, $content = "") {
	if(strlen($content) > 0) {
		$content .= "\t";
	}

	$file_1 = fopen(ROOT.'Logs/'.$txtname, "a");
	fwrite( $file_1, date("H:i:s d/m/Y") . "\t" . $content .
		str_subpad( filter_input(INPUT_SERVER, "REQUEST_URI", FILTER_SANITIZE_URL), 100 ) .
		str_subpad(filter_input(INPUT_SERVER, "HTTP_REFERER", FILTER_SANITIZE_URL), 120) .
		str_pad(filter_input(INPUT_SERVER, "REMOTE_ADDR", FILTER_SANITIZE_STRING), 16) .
		substr(str_replace("Mozilla/5.0 (","",filter_input(INPUT_SERVER, "HTTP_USER_AGENT", FILTER_SANITIZE_STRING)), 0, 80) .
		"\r\n" );
	fclose($file_1);
}


function quick_log($txtname, $content, $threshold = false) {
	global $root, $mailBuffer, $mailBufferCount;

	file_put_contents( $root.'Logs/'.$txtname, date("H:i:s d/m/Y") .
		"\t" . $content . "\r\n", FILE_APPEND );

	if($threshold !== false && (int)$content > $threshold) {
		$mailBuffer[$mailBufferCount]['file'] = $txtname;
		$mailBuffer[$mailBufferCount]['content'] = $content;
		$mailBufferCount++;
	}
}
function fileLog($txtname, $content, $isCron = false, $threshold = false) {
	global $cfile, $browser, $root, $ip, $mailBuffer, $mailBufferCount;

	$extras = $isCron ? "" :
		str_pad($cfile[count($cfile)-1], 20) . str_pad("User: ". $ip, 20) . '\t'.$browser;

	file_put_contents( $root.'Logs/'.$txtname, date("H:i:s d/m/Y") ."\t". $content .
		$extras . "\r\n", FILE_APPEND );

	if($threshold && (int)$content > $threshold) {
		$mailBuffer[$mailBufferCount]['file'] = $txtname;
		$mailBuffer[$mailBufferCount]['content'] = $content;
		$mailBufferCount++;
	}
}

function server_mail($txtname, $content) {
	mail("alerts@nw3weather.co.uk","Logging threshold breached by " . $txtname, $content);
}

/**
 * Gets the WD local system time
 * @param bool $getDiff [=true] return as difference from true (server) sys time
 * @return int num seconds
 */
function sysWDtimes($getDiff = true) {
	$now = time();
	$sys = filemtime(LIVE_DATA_PATH);
	$diff = $now - $sys;

	return $getDiff ? $diff : $sys;
}

function median($arr) {
	sort($arr);
	return $arr[ floor( count($arr) / 2 ) ];
}

function mymax($arr, $debug = false) {
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
function mymin($arr, $debug = false) {
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

function sum_cond($arr, $isGreater, $limit, $isMean = false) {
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
function mod($int, $cnt) {
	return ($int < 0) ? ($cnt + $int) : ($int % $cnt);
}

/**
 * makes an opening html table tag. Null-pass enabled.
 * @param string $class [= table1]
 * @param string $width [=99%]
 * @param number $cellpadding [=3]
 * @param bool $centre [=false] position table centrally (auto margin)
 * @param number $cellspacing [=0]
 */
function table( $class = null, $width = null, $cellpadding = null, $centre = false, $cellspacing = 0 ) {
	if(is_null($class)) { $class = 'table1'; }
	if(is_null($width)) { $width = '99%'; }
	if(is_null($cellpadding)) { $cellpadding = 3; }
	$centrality = $centre ? 'style="margin:auto;"' : '';
	echo '<table '.$centrality.' class="'.$class.'" width="'.$width.'" cellpadding="'.$cellpadding.'" cellspacing="'.$cellspacing.'">
		';
}
function table_end() {
	echo '</table>
		';
}

/**
 * makes a table row with set class. Pass null to give straight &lt;tr&gt;
 * @param type $class [=table-top]
 */
function tr( $class = 'table-top' ) {
	$class2 = ( !is_null($class) ) ? ' class="'.$class.'"' : '';
	echo '<tr'.$class2.'>
	';
}
function tr_end() {
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
function td($value, $class = null, $width = false, $colspan = false, $rowspan = false) {
	if(is_null($class)) { $class = 'td4'; }
	if($width) { $wid = ' width="'.$width.'%"'; }
	if($colspan) { $csp = ' colspan="'.$colspan.'"'; }
	if($rowspan) { $rsp = ' rowspan="'.$rowspan.'"'; }
	echo '<td class="'.$class.'"'.$wid.$csp.$rsp.'>'.$value.'</td>
		';
}

function tableHead($text, $colspan = 3) {
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
function img($src, $alt, $margin, $title = null, $width = null, $height = null, $anchor = null, $extras = null) {
	$dims = ($width === null) ? '' : " width='$width' height='$height' ";
	$tit = ($title === null) ? '' : " title='$title' ";
	$mores = ($extras === null) ? '' : $extras;
	$imgString = "<img src='$src' style='margin:{$margin}em;' alt='$alt' $dims $tit $mores />";

	echo ($anchor === null) ? $imgString : "<a href='$src' title='$anchor'>$imgString</a>";
}

/**
 * Light or Dark alternating odd/even
 * @param int $i
 * @return string light or dark
 */
function colcol($i) {
	return ($i % 2 == 0) ? 'light' : 'dark';
}

/**
 * Produces an html form for inputting year, month and, optionally, day
 * @param int $yproc
 * @param int $mproc
 * @param int $dproc [= 0] leave blank if only month/year selector
 */
function dateFormMaker($yproc, $mproc, $dproc = 0) {
	global $dyear, $months;
	echo '<select name="year">';
	for($i = 2009; $i <= $dyear; $i++) {
		$selected = ($i == $yproc) ? 'selected="selected"' : '';
		echo '<option value="', $i, '" ', $selected, '>', $i, '</option>
			';
	}
	echo '</select>
		<select name="month">';
	for($i = 1; $i <= 12; $i++) {
		$selected = ($i == $mproc) ? 'selected="selected"' : '';
		echo '<option value="', $i, '" ', $selected, '>', $months[$i-1], '</option>
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
function dropdownCycle($dir, $href, $title, $disabled = false) {
	$lg = $dir ? 9654 : 9664;
	echo '<a class="arrow" href="'.PAGE_NAME. '?'. $href .'" title="'. $title .'">
		&#'. $lg .';
		</a>
	';
			//<button style="color:#6f6; background-color:#bbd;" title="'. $title .'">&'. $lg .'t;</button>
}

function buildSlug($key, $val) {
	$form_params = ["vartype" => $GLOBALS["type"], "year" => $GLOBALS['year'], "month" => $GLOBALS['month'],
		"summary_type" => $GLOBALS['GET_SUMMARY_TYPE'], "start_year_rep" => $GLOBALS["startYrReport"]];
	$form_params[$key] = $val;
	$slug = "";
	foreach ($form_params as $k => $v) {
		$slug .= "&$k=$v";
	}
	return $slug;
}

/**
 * Converts a number of seconds to secs, mins, hours or days depending on value of $secs
 * @param int $secs the number of seconds to convert
 * @return string nice Human-readable output
 */
function secsToReadable($secs) {
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
 * Computes the appropriate feels-like temperature for given input
 * @param float $t temperature
 * @param float $v wind speed
 * @param float $d dew point
 * @return float the feels-like temp in degC
 */
function feelsLike($t, $v, $d) {
	if($t < 10 && $v > 3) {
		//http://en.wikipedia.org/wiki/Wind_chill#North_American_and_UK_wind_chill_index
		$x = pow($v * 1.61, 0.16);
		$windChill = 13.12 + $t * (0.6215 + 0.3965 * $x) - 11.37 * $x;
		return $windChill;
	} elseif($d > 7) { // humidex is less than T for sub-7 Td
		//http://en.wikipedia.org/wiki/Humidex
		$humidex = $t + 0.5555 * (6.11 * pow(M_E, 5417.753*(0.003660858-1/($d+273.15))) - 10);
		return $humidex;
	}
	return $t;
}

/**
 * Dew point from temp and hum. <br />
 * Found to be within 0.1&deg;C of WD's value across the lifetime range (-12 to +20)
 * @param float $t temp
 * @param int $h hum
 * @return float dew point
 */
function dewPoint($t, $h) {
	//http://en.wikipedia.org/wiki/Dew_point
	$gamma = (17.271*$t) / (237.7+$t) + log($h/100);
	return (237.7*$gamma) / (17.271-$gamma);
}

function monthly_extras($array) {
	return array( sum_cond($array[20],0,0), sum_cond($array[28],1,15), sum_cond($array[13],1,0.1), sum_cond($array[13],1,1),
		conv( sum_cond($array[13],1,0.1,true), 2 )  );
}

/**
 * Rounds a value to a set number of decimal places
 * @param type $value the value to round
 * @param string $dp the number of decimal places [= 1]
 * @return type the rounded string
 */
function roundToDp($value, $dp = 1) {
    $dp = '.'.$dp.'f';
    return sprintf("%$dp", $value);
}

/**
 * Echos a full-width padded divison
 * @param String $message message to show
 * @param bool $isWarning [=true] set false to show the info-style instead of the default red warning-style
 */
function showStatusDiv($message, $isWarning = true) {
	$messageType = $isWarning ? 'warning' : 'info';
	echo "<div class='statusBox $messageType'>$message</div>";
}

/**
 * Searches a string for a number of terms, returning true if any are contained within
 * @param string $str subject
 * @param mixed $searchTerms array of search terms, or single string
 * @return boolean true if the string contains of the searched-for terms, false otherwise
 */
function strContains($str, $searchTerms) {
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
 * does a PHP <code>print_r</code> with pre-fromat html tags to make the output more readable on a webpage
 * @param mixed $var the variable to print
 */
function print_m($var) {
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}

/**
 * Safely accesses a url (using a timeout) and gets the file into an array
 * @param string $url to parse
 * @param int $timeout [=5] in seconds
 * @return array of each line, or false on failure
 */
function urlToArray($url, $timeout = 5) {
	$ctx = stream_context_create( array( 'http'=> array('timeout' => $timeout) ) );
	return file($url, false, $ctx);
}

function listdir($dir) {
	return array_diff(scandir($dir), array('.', '..'));
}

function extract_for_timelapse($year, $month = 0, $day = 0, $freq = 1, $twiset = null, $cam = "sky",
	$frame_rate = 24, $crf = 25, $name = null, $scale = "1080x720") {
	global $lat, $lng, $zenith;

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

/**
 * Convert a YYYYMMDD string to a unix timestamp
 * @param string $stamp
 * @return int
 */
function datestamp_to_ts($stamp) {
	return mkdate(substr($stamp,4,2),substr($stamp,6,2),  substr($stamp,0,4));
}

require_once 'mainData.php';
?>