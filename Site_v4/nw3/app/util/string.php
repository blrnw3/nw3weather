<?php
namespace nw3\app\util;

/**
 *
 * @author Ben LR
 */
class String {

	/**
	 * Searches a string for a substring
	 * @param string $search
	 * @param string $find
	 * @return bool true on success
	 */
	static function contains($search, $find) {
		return strpos($search, $find) !== false;
	}

	static function starts_with($search, $find) {
		return strpos($search, $find) === 0;
	}

	/**
	 * Pads an integer value with a leading 0 if it is between 0 and 9 (inclusive)
	 * @param number $tag
	 * @return string potentially-padded val
	 */
	static function zerolead($tag) {
		$tag = (int)$tag;
		if($tag < 10 && $tag >= 0) { $tag = '0'.$tag; }
		return $tag;
	}


	/**
	 * Tests for a blank string/value.
	 * @param mixed $val
	 * @return bool
	 */
	static function isBlank($val) {
		return strlen($val) === 0;
	}
	/**
	 * Tests for a blank string/value.
	 * @param mixed $val
	 * @return bool
	 */
	static function isNotBlank($val) {
		return strlen($val) > 0;
	}

	/**
	 * Pad a string to a specified length. Excess is marked with an elipsis
	 * @param string $string
	 * @param int $length
	 * @return string
	 */
	static function str_subpad($string, $length) {
		$padding = (strlen($string) > $length) ? "...  " : " ";
		return substr( str_pad($string, $length), 0, $length - strlen($padding) ) . $padding;
	}

	/**
	 * Searches a string for a number of terms, returning true if any are contained within
	 * @param string $str subject
	 * @param mixed $searchTerms array of search terms, or single string
	 * @return boolean true if the string contains of the searched-for terms, false otherwise
	 */
	static function strContains($str, $searchTerms) {
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
	static function print_m($var) {
		echo '<pre>';
		print_r($var);
		echo '</pre>';
	}

}

?>
