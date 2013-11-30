<?php
namespace nw3\app\util;

/**
 *
 * @author Ben LR
 */
class Maths {

	const PHP_INT_MIN = -92233720;

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

	static function max($arr, $debug = false) {
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
	//	if($badarr && $debug) {
	//		print_r($arr);
	//		debug_print_backtrace();
	//	}
		if($max == -1 * PHP_INT_MAX) return null;
		return $max;
	}
	static function min($arr, $debug = false) {
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

	static function sum_cond($arr, $isGreater, $limit, $isMean = false) {
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
	 * My idea of how the modulo operator <em>should</em> work, i.e. wraps around on negative nums
	 * @param int $int num
	 * @param int $cnt divisor
	 * @return int remainder
	 */
	static function mod($int, $cnt) {
		return ($int < 0) ? ($cnt + $int) : ($int % $cnt);
	}



}

?>
