<?php
namespace nw3\app\util;

/**
 *
 * @author Ben LR
 */
class Time {

	static function stamp($unix) {
		return date('H:i', $unix);
	}

	static function time_av($times) {
		for($i = 0; $i < count($times); $i++) {
			if(!strpos($times[$i], '*')) {
				if(strpos($times[$i], ':') > 0) { $mktimes[$i] = strtotime($times[$i]); }
				elseif($times[$i] < 1 && $times[$i] > 0) { $mktimes[$i] = decimal_timefix($times[$i]); }
			}
		}
		$meanTime = mean($mktimes);
		return ($meanTime === '') ? 'n/a' : date(' H:i ', $meanTime);
	}


	/**
	 * Converts a number of seconds to secs, mins, hours or days depending on value of $secs
	 * @param int $secs the number of seconds to convert
	 * @return string nice Human-readable output
	 */
	static function secsToReadable($secs) {
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

	static function pretty_duration($secs, $show_sign = null) {

		$sign = '';
		if($secs < 0) {
			$sign = '-';
			$secs = -$secs;
		} elseif($show_sign) {
			$sign = '+';
		}

		if($secs < 60) {
			return $sign. $secs .'s';
		}

		$mins = floor($secs / 60);
		$seconds = $secs % 60;
		if($mins < 60) {
			return "$sign{$mins}m {$seconds}s";
		}

		$hrs = floor($mins / 60);
		$minutes = $mins % 60;
		if($hrs < 48) {
			return "$sign{$hrs}h {$minutes}m";
		}

		$dys = floor($hrs / 24);
		$hours = $hrs % 24;
		if($dys < 100) {
			return "$sign{$dys}d {$hours}h";
		}

		return $sign.'bloody ages';
	}
}

?>
