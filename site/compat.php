<?php
/**
 * PHP 5.4 <-> 8.x compatibility shims.
 *
 * Prod runs PHP 5.4; the replacement server will run PHP 8.3/8.4. Every helper here keeps the
 * PHP 5.4 result (so output and cached numbers are identical on both), uses only 5.4 syntax,
 * and is a thin pass-through where the versions already agree. Required from
 * UtilsAndConsts.php (pages + current crons) and oldSites/sitev3/basics.php (legacy crons).
 */
if (defined('NW3_COMPAT_LOADED')) {
	return;
}
define('NW3_COMPAT_LOADED', true);

// PHP 7+ defines PHP_INT_MIN natively; 5.4 does not.
if (!defined('PHP_INT_MIN')) {
	define('PHP_INT_MIN', -PHP_INT_MAX - 1);
}
// date_sunrise() return formats (SUNFUNCS_RET_* are deprecated in 8.4); see nw3_sunrise().
define('NW3_SUN_TIMESTAMP', 0);
define('NW3_SUN_STRING', 1);
define('NW3_SUN_DOUBLE', 2);

/** Lenient numeric coercion as PHP 5.4 did for math functions (null/bool/'' /'12abc'); 8.x throws for some. */
function nw3_to_number($value) {
	if (is_int($value) || is_float($value)) {
		return $value;
	}
	return is_string($value) ? nw3_str_to_num($value) : (int)$value;
}

/**
 * round() with PHP 5.4 results. PHP 8.4 rewrote round() (no more "pre-rounding"), which moves
 * computed values such as 6.5499999999999945 (5.4: 6.6, 8.4: 6.5). On 8.4 this runs a userland
 * port of PHP 5.4's _php_math_round() (same IEEE double operations, so bit-identical results).
 */
function nw3_round($value, $precision = 0) {
	$value = nw3_to_number($value);
	if (PHP_VERSION_ID < 80400) {
		return round($value, $precision);
	}
	$precision = (int)$precision;
	if (is_int($value)) {
		if ($precision >= 0) {
			return (float)$value;
		}
		$value = (float)$value;
	}
	return nw3_php54_math_round($value, $precision);
}

/** Port of PHP 5.4 ext/standard/math.c _php_math_round() (PHP_ROUND_HALF_UP). */
function nw3_php54_math_round($value, $places) {
	if (is_nan($value) || is_infinite($value)) {
		return $value;
	}
	if ($value == 0.0) {
		return $value * 1.0;
	}
	$precisionPlaces = 14 - nw3_php54_intlog10abs($value);
	$f1 = nw3_php54_intpow10(abs($places));
	if ($precisionPlaces > $places && $precisionPlaces - $places < 15) {
		$f2 = nw3_php54_intpow10(abs($precisionPlaces));
		$tmp = ($precisionPlaces >= 0) ? $value * $f2 : $value / $f2;
		$tmp = nw3_php54_round_helper($tmp);
		$f2 = nw3_php54_intpow10(abs($places - $precisionPlaces));
		$tmp = $tmp / $f2;
	} else {
		$tmp = ($places >= 0) ? $value * $f1 : $value / $f1;
		if (abs($tmp) >= 1e15) {
			return $value;
		}
	}
	$tmp = nw3_php54_round_helper($tmp);
	if (abs($places) < 23) {
		$tmp = ($places > 0) ? $tmp / $f1 : $tmp * $f1;
	} else {
		$tmp = (float)sprintf('%15fe%d', $tmp, -$places);
		if (is_nan($tmp) || is_infinite($tmp)) {
			return $value;
		}
	}
	return $tmp;
}
function nw3_php54_round_helper($v) {
	return ($v >= 0.0) ? floor($v + 0.5) : ceil($v - 0.5);
}
function nw3_php54_intpow10($power) {
	if ($power < 0 || $power > 22) {
		return pow(10.0, $power);
	}
	return (float)('1e' . $power); // exact for 0..22, like 5.4's static table
}
function nw3_php54_intlog10abs($value) {
	$value = abs($value);
	if ($value < 1e-8 || $value > 1e22) {
		return (int)floor(log10($value));
	}
	// 5.4 does a table binary search (exact at powers of ten); same result, exactly.
	$r = (int)floor(log10($value));
	if ($r >= -8 && $r <= 22 && (float)('1e' . $r) > $value) {
		$r--;
	} elseif ($r + 1 >= -8 && $r + 1 <= 22 && (float)('1e' . ($r + 1)) <= $value) {
		$r++;
	}
	return $r;
}

/** number_format() with PHP 5.4 results, including 5.4's "-0" for small negatives. */
function nw3_number_format($num, $decimals = 0, $decPoint = '.', $thousandsSep = ',') {
	if (is_array($num) || is_object($num) || (is_string($num) && !preg_match('/^[ \t\n\r\v\f]*[+-]?(\d|\.\d)/', $num))) {
		return null; // 5.4: "expects parameter 1 to be double" warning + NULL (e.g. for ''); 8.x throws
	}
	$num = (float)nw3_to_number($num);
	$decimals = max(0, (int)$decimals);
	if (PHP_VERSION_ID < 80000) {
		return number_format($num, $decimals, $decPoint, $thousandsSep);
	}
	$num = nw3_php54_math_round($num, $decimals); // 8.x's own re-rounding of this is a no-op
	$out = number_format($num, $decimals, $decPoint, $thousandsSep);
	if ($num == 0.0 && $out[0] !== '-' && fdiv(1.0, $num) < 0) {
		$out = '-' . $out; // 5.4 printed a rounded negative zero as "-0" / "-0.0"; 8.0+ drops the sign
	}
	return $out;
}

/**
 * PHP 5.4 numeric-string rules: leading whitespace allowed, trailing whitespace NOT
 * (8.0 accepts "12 " and "0\n"; old datm/dat lines can carry a trailing newline).
 */
function nw3_is_numeric($v) {
	if (!is_numeric($v)) {
		return false;
	}
	if (is_string($v) && $v !== '') {
		$last = substr($v, -1);
		if ($last === ' ' || $last === "\t" || $last === "\n" || $last === "\r" || $last === "\v" || $last === "\f") {
			return false;
		}
	}
	return true;
}

/** Number from a string the way PHP 5.4 arithmetic read it ('' -> 0, '12abc' -> 12, '1.5x' -> 1.5). */
function nw3_str_to_num($s) {
	if (is_numeric($s)) {
		return $s + 0;
	}
	if (preg_match('/^[ \t\n\r\v\f]*[+-]?(\d+(\.\d*)?|\.\d+)([eE][+-]?\d+)?/', $s, $m)) {
		return $m[0] + 0;
	}
	return 0;
}

/** array_sum() as in 5.4: non-numeric strings count as their leading number (8.3+ warns). */
function nw3_array_sum($arr) {
	$sum = 0;
	foreach ($arr as $v) {
		if (is_int($v) || is_float($v)) {
			$sum += $v;
		} elseif (is_string($v)) {
			$sum += nw3_str_to_num($v);
		} elseif (is_bool($v) || $v === null) {
			$sum += (int)$v;
		}
	}
	return $sum;
}

/**
 * json_encode() with 5.4's float output. 7.1+ prints floats with serialize_precision (-1 = shortest
 * round-trip, e.g. 6.6815999999999995); 5.4 used `precision` (14 significant digits, 6.6816).
 */
function nw3_json_encode($value, $options = 0) {
	if (PHP_VERSION_ID < 70100) {
		return json_encode($value, $options);
	}
	$old = ini_get('serialize_precision');
	ini_set('serialize_precision', ini_get('precision'));
	$out = json_encode($value, $options);
	ini_set('serialize_precision', $old);
	return $out;
}

/** asort()/arsort() that keep equal values in input order (PHP 8 behaviour) on 5.4 too. */
function nw3_asort(&$arr, $reverse = false) {
	if (PHP_VERSION_ID >= 80000) {
		return $reverse ? arsort($arr) : asort($arr);
	}
	$rows = array();
	$i = 0;
	foreach ($arr as $k => $v) {
		$rows[] = array($v, $i++, $k);
	}
	usort($rows, function ($a, $b) use ($reverse) {
		if ($a[0] == $b[0]) {
			return $a[1] - $b[1];
		}
		$lt = $a[0] < $b[0];
		return ($reverse ? !$lt : $lt) ? -1 : 1;
	});
	$arr = array();
	foreach ($rows as $r) {
		$arr[$r[2]] = $r[0];
	}
	return true;
}
function nw3_arsort(&$arr) {
	return nw3_asort($arr, true);
}

/** fputcsv(): pass PHP's long-standing default escape explicitly on 7.4+ (8.4 deprecates omitting it); 5.4 has no 5th arg. */
function nw3_fputcsv($handle, $fields, $delimiter = ',', $enclosure = '"') {
	if (PHP_VERSION_ID >= 70400) {
		return fputcsv($handle, $fields, $delimiter, $enclosure, '\\');
	}
	return fputcsv($handle, $fields, $delimiter, $enclosure);
}

/**
 * What filter_var($v, FILTER_SANITIZE_STRING) returned (deprecated in 8.1): quotes encoded, tags
 * stripped. null stays null (filter_input: missing), non-scalars give false.
 */
function nw3_sanitize_string($v) {
	if ($v === null) {
		return null;
	}
	if (!is_scalar($v)) {
		return false;
	}
	$v = str_replace(array("'", '"'), array('&#39;', '&#34;'), (string)$v);
	return strip_tags(str_replace("\0", '', $v));
}
/** filter_input(INPUT_SERVER, $name, FILTER_SANITIZE_STRING) without filter_input (unreliable under php-fpm/CLI). */
function nw3_server_str($name) {
	return nw3_sanitize_string(isset($_SERVER[$name]) ? $_SERVER[$name] : null);
}

/*
 * Sunrise / sunset. date_sunrise()/date_sunset() are deprecated since 8.1, and timelib's
 * algorithm changed between 5.4 and 8.x (times move by ~1 minute). This is a straight port of
 * PHP 5.4's ext/date timelib astro.c + php_do_date_sunrise_sunset(), so both versions agree
 * with what prod has always shown. Signature as date_sunrise(); format: SUNFUNCS_RET_* value
 * (0 = timestamp, 1 = 'HH:MM' string, 2 = float hours).
 */
function nw3_sunrise($timestamp, $format, $latitude, $longitude, $zenith, $gmtOffset) {
	return nw3_sun_rise_set(false, $timestamp, $format, $latitude, $longitude, $zenith, $gmtOffset);
}
function nw3_sunset($timestamp, $format, $latitude, $longitude, $zenith, $gmtOffset) {
	return nw3_sun_rise_set(true, $timestamp, $format, $latitude, $longitude, $zenith, $gmtOffset);
}
function nw3_sun_rise_set($calcSunset, $timestamp, $format, $latitude, $longitude, $zenith, $gmtOffset) {
	$timestamp = (int)$timestamp;
	$altitude = 90 - $zenith;
	// t_loc: local date of $timestamp at 12:00:00 local time; t_utc: that date at 00:00 UTC.
	$y = (int)date('Y', $timestamp); $m = (int)date('n', $timestamp); $d = (int)date('j', $timestamp);
	$locNoon = mktime(12, 0, 0, $m, $d, $y);
	$utcMidnight = gmmktime(0, 0, 0, $m, $d, $y);
	// Days since 2000 Jan 0.0 of 12h local mean solar time.
	$dd = ($locNoon / 86400 + 2440587.5 - 2451543) - $longitude / 360.0;
	$sidtime = nw3_astro_rev(nw3_astro_gmst0($dd) + 180.0 + $longitude);
	list($sRA, $sdec, $sr) = nw3_astro_sun_ra_dec($dd);
	$tsouth = 12.0 - nw3_astro_rev180($sidtime - $sRA) / 15.0;
	$sradius = 0.2666 / $sr;
	$altitude -= $sradius; // upper limb
	$rad = M_PI / 180.0;
	$cost = (sin($altitude * $rad) - sin($latitude * $rad) * sin($sdec * $rad)) / (cos($latitude * $rad) * cos($sdec * $rad));
	if ($cost >= 1.0 || $cost <= -1.0) {
		return false; // sun never reaches the altitude (5.4 returns false for both cases)
	}
	$t = (180.0 / M_PI) * acos($cost) / 15.0;
	if ($format == 0) { // SUNFUNCS_RET_TIMESTAMP
		return (int)(($calcSunset ? $tsouth + $t : $tsouth - $t) * 3600 + $utcMidnight);
	}
	$n = ($calcSunset ? $tsouth + $t : $tsouth - $t) + $gmtOffset;
	if ($n > 24 || $n < 0) {
		$n -= floor($n / 24) * 24;
	}
	if ($format == 1) { // SUNFUNCS_RET_STRING
		return sprintf('%02d:%02d', (int)$n, (int)(60 * ($n - (int)$n)));
	}
	return $n;
}
function nw3_astro_rev($x) { return $x - 360.0 * floor($x * (1.0 / 360.0)); }
function nw3_astro_rev180($x) { return $x - 360.0 * floor($x * (1.0 / 360.0) + 0.5); }
function nw3_astro_gmst0($d) { return nw3_astro_rev((180.0 + 356.0470 + 282.9404) + (0.9856002585 + 4.70935E-5) * $d); }
function nw3_astro_sun_ra_dec($d) {
	$rad = M_PI / 180.0; $deg = 180.0 / M_PI;
	$M = nw3_astro_rev(356.0470 + 0.9856002585 * $d);
	$w = 282.9404 + 4.70935E-5 * $d;
	$e = 0.016709 - 1.151E-9 * $d;
	$E = $M + $e * $deg * sin($M * $rad) * (1.0 + $e * cos($M * $rad));
	$x = cos($E * $rad) - $e;
	$y = sqrt(1.0 - $e * $e) * sin($E * $rad);
	$r = sqrt($x * $x + $y * $y);
	$lon = $deg * atan2($y, $x) + $w;
	if ($lon >= 360.0) {
		$lon -= 360.0;
	}
	$x = $r * cos($lon * $rad);
	$y = $r * sin($lon * $rad);
	$obl = 23.4393 - 3.563E-7 * $d;
	$z = $y * sin($obl * $rad);
	$y = $y * cos($obl * $rad);
	return array($deg * atan2($y, $x), $deg * atan2($z, sqrt($x * $x + $y * $y)), $r);
}
