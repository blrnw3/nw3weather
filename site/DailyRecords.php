<?php
/**
 * Station record low and high of a daily series for each calendar date (1 Jan .. 31 Dec, 29 Feb on its own),
 * for the Chart Viewer's "Station record range" band.
 *
 * Same basis as the home climate card's records (ClimateCard): the station's daily data from
 * Site::BASE_YEAR (2009) on, as served by Data (audit-corrected serialised files), days before today only.
 * Each series is precomputed into CACHE_ROOT/serialised_dayrec_<var>.txt and rebuilt only when the date or
 * the daily data files change; cron warms every series after the data refresh (warm_detail_summaries.php).
 */
class DailyRecords {
	const CACHE_VERSION = 2;

	/** Values this close are the same reading (ties). */
	const EPS = 1e-6;

	/** Fewest years on a date before a day can count as a record day (short series would flag nearly every day). */
	const MIN_YEARS = 5;

	/** Cheap fingerprint of every daily data file (derived series read several), so data edits invalidate the cache. */
	private static function signature() {
		static $sig = null;
		if ($sig !== null) { return $sig; }
		clearstatcache();
		$files = glob(CACHE_ROOT . 'serialised_dat_new_*.txt');
		sort($files);
		$parts = array();
		foreach ($files ?: array() as $f) {
			$parts[] = basename($f) . ':' . filemtime($f) . ':' . filesize($f);
		}
		return $sig = md5(implode('|', $parts));
	}

	private static function path($var) {
		return CACHE_ROOT . 'serialised_dayrec_' . $var . '.txt';
	}

	private static function fresh($c, $ymd, $sig) {
		return is_array($c) && ($c['v'] ?? null) === self::CACHE_VERSION
			&& ($c['date'] ?? null) === $ymd && ($c['sig'] ?? null) === $sig;
	}

	private static function read($var) {
		$p = self::path($var);
		return is_file($p) ? @unserialize(file_get_contents($p)) : null;
	}

	/**
	 * ['lo' => [m][d] => value, 'hi' => [m][d] => value, 'nlo'/'nhi' => [m][d] => years holding it (ties),
	 * 'years' => [m][d] => count] in native units,
	 * or null for an unknown series. Served from the cache; one process rebuilds a stale cache at a time
	 * while the others keep using a same-day copy.
	 */
	public static function get($var, $now = null) {
		if (!isset(Wx::$daily[$var])) { return null; }
		$now = ($now === null) ? time() : (int)$now;
		$ymd = date('Y-m-d', $now);
		$sig = self::signature();
		$c = self::read($var);
		if (self::fresh($c, $ymd, $sig)) { return $c; }
		$sameDay = is_array($c) && ($c['v'] ?? null) === self::CACHE_VERSION && ($c['date'] ?? null) === $ymd;
		$lockFile = CACHE_ROOT . 'serialised_dayrec.lock';
		$lock = @fopen($lockFile, 'c');
		if ($lock) { @chmod($lockFile, 0664); } // cron and web users share it
		if ($lock && !flock($lock, LOCK_EX | LOCK_NB)) {
			if ($sameDay) { fclose($lock); return $c; }
			flock($lock, LOCK_EX);
		}
		$c2 = self::read($var);
		if (self::fresh($c2, $ymd, $sig)) {
			$out = $c2;
		} else {
			$out = self::build($var, $ymd, $sig);
			nw3_atomic_write(self::path($var), serialize($out));
		}
		if ($lock) { flock($lock, LOCK_UN); fclose($lock); }
		return $out;
	}

	private static function build($var, $today, $sig) {
		$out = array('v' => self::CACHE_VERSION, 'date' => $today, 'sig' => $sig, 'built' => time(),
			'lo' => array(), 'hi' => array(), 'nlo' => array(), 'nhi' => array(), 'years' => array());
		foreach (Data::varToDatArray($var, false) as $y => $months) {
			if ((int)$y < Site::BASE_YEAR || !is_array($months)) { continue; }
			foreach ($months as $m => $days) {
				if (!is_array($days)) { continue; }
				foreach ($days as $d => $val) {
					if ($val === null || $val === '' || $val === '-' || !is_numeric($val)) { continue; }
					if (sprintf('%04d-%02d-%02d', $y, $m, $d) >= $today) { continue; }
					$v = (float)$val;
					$m = (int)$m; $d = (int)$d;
					if (!isset($out['lo'][$m][$d]) || $v < $out['lo'][$m][$d] - self::EPS) { $out['lo'][$m][$d] = $v; $out['nlo'][$m][$d] = 1; }
					elseif (abs($v - $out['lo'][$m][$d]) <= self::EPS) { $out['nlo'][$m][$d]++; }
					if (!isset($out['hi'][$m][$d]) || $v > $out['hi'][$m][$d] + self::EPS) { $out['hi'][$m][$d] = $v; $out['nhi'][$m][$d] = 1; }
					elseif (abs($v - $out['hi'][$m][$d]) <= self::EPS) { $out['nhi'][$m][$d]++; }
					$out['years'][$m][$d] = ($out['years'][$m][$d] ?? 0) + 1;
				}
			}
		}
		return $out;
	}

	/**
	 * Days of one year (axis order) whose value is the station record for that date: ['hi' => [i => ties], 'lo' => ...].
	 * $days is [i => [month, day, raw value]]. The records include the year itself (every day before today), so a
	 * record day equals the record; 'ties' is how many other years hold the same value. Today is left out (still
	 * running). $highOnly skips lows (totals: the low is nearly always 0); $nonNeg skips a record low of 0 for
	 * series that cannot go below it (calm, dry or sunless days are not records).
	 */
	public static function recordDays($rec, $days, $yr, $highOnly, $nonNeg, $now = null, $type = '') {
		$now = ($now === null) ? time() : (int)$now;
		$today = date('Y-m-d', $now);
		$run = self::runningDir($type, $highOnly, $now);
		$out = array('hi' => array(), 'lo' => array());
		if ((int)$yr < Site::BASE_YEAR) { return $out; } // records are 2009+ only
		foreach ($days as $i => $x) {
			list($m, $d, $v) = $x;
			if ($v === null || $v === '' || $v === '-' || !is_numeric($v)) { continue; }
			$date = sprintf('%04d-%02d-%02d', $yr, $m, $d);
			if ($date > $today) { continue; }
			if (($rec['years'][$m][$d] ?? 0) < self::MIN_YEARS) { continue; }
			$v = (float)$v;
			if ($date === $today) {
				// Today is still running: only a value already past the record is certain (a running max or total
				// can only rise, a running min only fall). Ties and means are left out.
				if ($run === 'hi' && isset($rec['hi'][$m][$d]) && $v > $rec['hi'][$m][$d] + self::EPS && !($highOnly && $v == 0)) {
					$out['hi'][$i] = 'today';
				} elseif ($run === 'lo' && isset($rec['lo'][$m][$d]) && $v < $rec['lo'][$m][$d] - self::EPS && $v < 99) {
					$out['lo'][$i] = 'today';
				}
				continue;
			}
			if (isset($rec['hi'][$m][$d]) && abs($v - $rec['hi'][$m][$d]) <= self::EPS && !($highOnly && $v == 0)) {
				$out['hi'][$i] = max(0, ($rec['nhi'][$m][$d] ?? 1) - 1);
			} elseif (!$highOnly && isset($rec['lo'][$m][$d]) && abs($v - $rec['lo'][$m][$d]) <= self::EPS && !($nonNeg && $v == 0)) {
				$out['lo'][$i] = max(0, ($rec['nlo'][$m][$d] ?? 1) - 1);
			}
		}
		return $out;
	}

	/**
	 * Which way today's running value of a series can only move: 'hi' (maxima and totals), 'lo' (minima) or null
	 * (means and anything else, which can go either way). Day max (09-21) counts only from 09:00.
	 */
	public static function runningDir($type, $summable, $now) {
		$t = strtolower((string)$type);
		if ($t === 'daymax' && (int)date('G', $now) < 9) { return null; }
		if ($summable) { return 'hi'; }
		if (strpos($t, 'mean') !== false || strpos($t, 'avg') !== false) { return null; }
		if (strpos($t, 'max') !== false || $t === 'gust') { return 'hi'; }
		if (strpos($t, 'min') !== false) { return 'lo'; }
		return null;
	}

	/** Rebuild every stale series (cron, after the data refresh). Returns the number of series checked. */
	public static function warm($now = null) {
		$n = 0;
		foreach (array_keys(Wx::$daily) as $var) {
			self::get($var, $now);
			$n++;
		}
		return $n;
	}
}
