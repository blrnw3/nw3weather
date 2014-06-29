<?php
namespace nw3\app\util;

/**
 * Array util. Deliberately mispelt due to reserved word conflict
 * @author Ben LR
 */
class arraay {

	/**
	 * Inverts the nesting structure of a 2D array, i.e. from <code>$arr[x][y]</code> to <code>$arr[y][x]</code>
	 * @param type $arr
	 * @return type
	 */
	static function dswap($arr) {
		$new = [];
		$keyj = array_keys($arr);
		//print_r($keyj); echo '<br /><br />';
		for($i = 0; $i < count($arr[$keyj[0]]); $i++) {
			for($j = 0; $j < count($arr); $j++) { $new[$i][$j+min($keyj)] = $arr[$j+min($keyj)][$i]; }
		}
		//echo count($arr[$keyj[0]]), ' x <br />';
		return $new;
	}

	/**
	 * Gets the nearest value to the given  one, from an array of given options
	 * @param number $val search value
	 * @param arr $arr options
	 * @param bool $up set true to round up
	 * @return number the nearest value found in <code>options</code>
	 */
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

	/**
	 * Callback used for removal of blanks from an array
	 * @param type $val
	 * @return boolean
	 */
	static function clearblank($val) {
		if($val == '' || $val == '-') { $b = false; } else { $b = true; }
		return $b;
	}
	/**
	 * Alternative callback for blank clearance
	 * @param string $val
	 * @return
	 */
	static function clearblank2($val) {
		$b = floatval($val);
		if($b == 0) { $val = ''; }
		return $b;
	}

}

?>
