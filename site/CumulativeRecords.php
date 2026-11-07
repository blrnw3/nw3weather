<?php
/**
 * Station cumulative record envelope for YTD charts: the record low and high
 * Jan 1-to-date cumulative total for summable variables (rain, sunshine).
 *
 * For each historic year since Site::BASE_YEAR, compute the cumulative sum from
 * Jan 1 to each calendar date. Then take the min/max across years for each date.
 * Handles leap years properly (29 Feb included, other dates aligned), skips
 * incomplete years beyond observed data, doesn't treat missing data as zero.
 *
 * Cached per variable into CACHE_ROOT/serialised_cumedrec_<var>.txt and
 * rebuilt when date or data files change (same pattern as DailyRecords).
 */
class CumulativeRecords {
	const CACHE_VERSION = 1;
	const EPS = 1e-6;

	/** Cheap fingerprint of daily data files to invalidate cache on edits. */
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
		return CACHE_ROOT . 'serialised_cumedrec_' . $var . '.txt';
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
	 * Get cumulative records for a summable variable.
	 * @return array|null ['lo' => [doy] => value, 'hi' => [doy] => value, 'years' => [doy] => count]
	 *   where doy is 1-based day of year (1..366). Returns null if not summable or no data.
	 */
	public static function get($var) {
		if (!isset(Wx::$daily[$var])) { return null; }
		$meta = Wx::$daily[$var];
		if (empty($meta['summable'])) { return null; } // Only for summable variables

		$ymd = date('Y-m-d');
		$sig = self::signature();
		$c = self::read($var);
		if (self::fresh($c, $ymd, $sig)) { return $c; }

		// Same-day cache but possibly stale data: lock and check again
		$sameDay = (is_array($c) && ($c['date'] ?? null) === $ymd);
		$lockFile = CACHE_ROOT . 'serialised_cumedrec_' . $var . '.lock';
		$lock = @fopen($lockFile, 'c');
		if ($lock) { @chmod($lockFile, 0664); }
		if ($lock && !flock($lock, LOCK_EX | LOCK_NB)) {
			if ($sameDay) {
				fclose($lock);
				return $c;
			}
			flock($lock, LOCK_EX);
		}
		$c2 = self::read($var);
		if (self::fresh($c2, $ymd, $sig)) {
			$out = $c2;
		} else {
			$out = self::build($var, $sig);
			nw3_atomic_write(self::path($var), serialize($out));
		}
		if ($lock) { flock($lock, LOCK_UN); fclose($lock); }
		return $out;
	}

	/**
	 * Build cumulative record envelope for a variable.
	 * For each historic complete year, compute Jan1-to-date cumulative sums.
	 * Take min/max across years for each day of year.
	 */
	private static function build($var, $sig = null) {
		$today = date('Y-m-d');
		$thisYear = (int)date('Y');
		$thisDoy = (int)date('z') + 1;
		$start = Site::BASE_YEAR;

		// Compute cumulative sums for each complete year
		$cumeSums = []; // [year][doy] => cumulative sum from Jan 1
		foreach (Data::getDailyData($var, $start) as $y => $months) {
			if ((int)$y >= $thisYear) { continue; } // Skip current incomplete year
			if (!is_array($months)) { continue; }

			$isLeap = (int)date('L', mktime(0, 0, 0, 1, 1, $y));
			$maxDoy = $isLeap ? 366 : 365;
			$cume = 0;
			$hasMissing = false;
			$lastDoy = 0;

			foreach ($months as $m => $days) {
				if (!is_array($days)) { continue; }
				foreach ($days as $d => $v) {
					$doy = (int)date('z', Date::mkdate($m, $d, $y)) + 1;
					$lastDoy = max($lastDoy, $doy);

					if ($v === null || $v === '' || !is_numeric($v)) {
						$hasMissing = true;
						continue; // Skip missing data, don't treat as zero
					}

					$cume += (float)$v;
					$cumeSums[$y][$doy] = $cume;
				}
			}

			// Forward-fill for valid days (only within observed range)
			if (!$hasMissing && $lastDoy < $maxDoy) {
				// If year data ends early (incomplete historical year), forward-fill to year end
				for ($doy = $lastDoy + 1; $doy <= $maxDoy; $doy++) {
					$cumeSums[$y][$doy] = $cume;
				}
			}
		}

		if (empty($cumeSums)) {
			return ['v' => self::CACHE_VERSION, 'date' => $today, 'sig' => $sig ?? self::signature(),
				'lo' => [], 'hi' => [], 'years' => []];
		}

		// Compute min/max across years for each day of year
		$lo = [];
		$hi = [];
		$years = [];

		for ($doy = 1; $doy <= 366; $doy++) {
			$vals = [];
			foreach ($cumeSums as $y => $days) {
				if (isset($days[$doy])) {
					$vals[] = $days[$doy];
				}
			}

			if (empty($vals)) { continue; }

			$lo[$doy] = min($vals);
			$hi[$doy] = max($vals);
			$years[$doy] = count($vals);
		}

		return [
			'v' => self::CACHE_VERSION,
			'date' => $today,
			'sig' => $sig ?? self::signature(),
			'lo' => $lo,
			'hi' => $hi,
			'years' => $years,
		];
	}

	/** Warm the cache for a variable (called by cron). */
	public static function warm($var) {
		return self::get($var);
	}
}
