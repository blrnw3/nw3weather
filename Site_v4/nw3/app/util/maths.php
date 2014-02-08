<?php
namespace nw3\app\util;

/**
 *
 * @author Ben LR
 */
class Maths {

	/**
	 * Rounds a value to a <em>fixed</em> number of dp
	 * @param number $val val to round
	 * @param int $dp[=1] precision
	 * @return string
	 */
	static function round($val, $dp = 1) {
		$dp = '.'.$dp.'f';
		return sprintf("%$dp", $val);
	}

	/**
	 * Rounds a value to the nearest integer if above 10, else gives 1dp.
	 * @param number $val
	 * @return string rounded val
	 */
	static function round_i($val) {
		if($val < 10) { $dp = 1; }
		return round($val,$dp);
	}

	/**
	 * Rounds to the nearest $size
	 * @param float $val
	 * @param number $size
	 * @param int $dir floor, ceil or round (the default)
	 * @return the rounded value
	 */
	static function round_big($val, $size = 5, $dir = false) {
		$size = ($size < 1) ? 1 : $size;
		if($dir === 0) { $new = floor($val / $size); }
		elseif($dir === 1) { $new = ceil($val / $size); }
		else { $new = round($val / $size); }
		return $new * $size;
	}

	/**
	 * Converts a boolean into a true/false string (good for JS)
	 * @param boolean $bool the boolean
	 * @return string true or false
	 */
	static function make_bool($bool) {
		return $bool ? 'true' : 'false';
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
	static function percent($val, $div, $dp = 0, $showp = true, $brackets = true) {
		$dat = sprintf('%.'.$dp.'f',$val/$div*100);
		if($showp) { $dat .= '%'; }
		if($brackets) { $dat = '(' . $dat . ')'; }
		return $dat;
	}


	/**
	 * Takes mean of an array, discarding blank (0 length and '-' values)
	 * @param mixed $array to mean over
	 * @return mixed array on success, empty string on receiving non-array input or an array of all blanks
	 */
	static function mean($array, $cnt = false) {
		if(!is_array($array))
			return null;

		$validCnt = 0;
		foreach ($array as $value) {
			if($value !== null)
				$validCnt++;
		}

		$finalCnt = $cnt ? $cnt : $validCnt;

		return ($validCnt > 0) ? array_sum($array)/$finalCnt : null;
	}

	/**
	 * Counts non-null values
	 * @param type $array
	 * @return null
	 */
	static function count($array) {
		if(!is_array($array))
			return null;

		$validCnt = 0;
		foreach ($array as $value) {
			if($value !== null)
				$validCnt++;
		}
		return ($validCnt > 0) ? $validCnt : null;
	}


	/**
	 * Min, max, mean, or count; computed based on $type
	 * @param mixed $arr the 1D array
	 * @param enum $type 0: min, 1: max, 2: mean, 3: count > 0
	 * @return mixed|number
	 */
	static function mom($arr, $type) {
		if($type == 0) { return min($arr); }
		elseif($type == 1) { return max($arr); }
		elseif($type == 3) { return sum_cond($arr, true, 0); }
		else { return mean($arr); }
	}

	// static function min2D($arr, $extent) {
	// 	foreach($arr as $arrOut) {
	// 		$min = min($arrOut);
	// 	}
	// }

	// static function mom2D($arr, $type, $extent) {
	// 	if($type == 0) { return min($arr); }
	// 	elseif($type == 1) { return max($arr); }
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
	static function opmom($var1, $var2, $minormax) {
		if($minormax == 0) { return $var1 < $var2; }
		else { return $var1 > $var2; }
	}

	static function median($arr) {
		sort($arr);
		return $arr[ floor( count($arr) / 2 ) ];
	}

	static function max($arr) {
		$max = INT_MIN;
		if(!is_array($arr))
			return null;

		foreach($arr as $val) {
			if($val !== null) {
				$val = (float)$val;
				if($val > $max)
					$max = $val;
			}
		}
		if($max === INT_MIN)
			return null;
		return $max;
	}
	static function min($arr) {
		$min = INT_MAX;
		if(!is_array($arr))
			return null;

		foreach($arr as $val) {
			if($val !== null) {
				$val = (float)$val;
				if($val < $min)
					$min = $val;
			}
		}
		if($min === INT_MAX)
			return null;
		return $min;
	}

	static function count_cond($arr, $isGreater, $limit, $isMean = false) {
		$cnt = $blanks = 0;

		if($isGreater) {
			foreach($arr as $val) {
				if( $val !== null ) {
					if($val > $limit) {
						$cnt++;
					}
				} else {
					$blanks++;
				}
			}
		} else {
			foreach($arr as $val) {
				if( $val !== null ) {
					if($val < $limit) {
						$cnt++;
					}
				} else {
					$blanks++;
				}
			}
		}

		if($blanks === count($arr))
			return null;

		if($isMean) { return mean($arr, $cnt); }
		return $cnt;
	}

	/**
	 * My idea of how the modulo operator <em>should</em> work, i.e. wraps around on negative nums
	 * @param int $int num
	 * @param int $cnt divisor
	 * @return int remainder
	 */
	static function mod($int, $cnt) {
		return ($int < 0) ? ($cnt + $int) : ($int % $cnt);
	}

	static function find_nearest($val, $arr, $up = false) {
		for($i = 0; $i < count($arr); $i++) {
			$near[$i] = $val - $arr[$i];
			if($up) { if($near[$i] > 0) { $near[$i] = -99; } }
			else { $near[$i] = abs($near[$i]); }
		}
		if($up) { $nearest = array_search( max($near), $near ); }
		else { $nearest = array_search( min($near), $near ); }
		return $arr[$nearest];
	}


}

?>
