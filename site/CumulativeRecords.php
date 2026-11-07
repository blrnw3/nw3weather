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
	const CACHE_VERSION = 3;
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
	 * @return array|null ['lo' => [m][d] => value, 'hi' => [m][d] => value, 'years' => [m][d] => count]
	 *   where m is month (1-12) and d is day of month. Returns null if not summable or no data.
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
	 * For each historic complete year, compute Jan1-to-date cumulative sums aligned by calendar date.
	 * Take min/max across years for each calendar date (month-day).
	 * Uses RAW unconverted values to match DailyRecords behavior (conversion happens in addCumulativeEnvelope via cv()).
	 */
	private static function build($var, $sig = null) {
		$today = date('Y-m-d');
		$thisYear = (int)date('Y');
		$start = Site::BASE_YEAR;

		// Compute cumulative sums for each complete year, aligned by calendar date (m-d)
		// Only include years with valid data for every expected calendar day through Dec 31
		$cumeSums = []; // [year][m][d] => cumulative sum from Jan 1
		foreach (Data::getDailyData($var, $start) as $y => $months) {
			if ((int)$y >= $thisYear) { continue; } // Skip current incomplete year
			if (!is_array($months)) { continue; }

			$cume = 0;
			$yearCume = []; // Temporary storage for this year
			$lastValidMD = null;

			// Iterate chronologically through the year
			$isLeap = (int)date('L', mktime(0, 0, 0, 1, 1, $y));
			$maxDoy = $isLeap ? 366 : 365;
			
			for ($doy = 1; $doy <= $maxDoy; $doy++) {
				$ts = mktime(0, 0, 0, 1, $doy, $y);
				$m = (int)date('n', $ts);
				$d = (int)date('j', $ts);
				
				$v = isset($months[$m][$d]) ? $months[$m][$d] : null;
				
				if ($v === null || $v === '' || !is_numeric($v)) {
					// Missing data: discard entire temporary year upon ANY missing day
					// Don't forward-fill or treat missing as zero
					$yearCume = []; // Discard all accumulated data for this year
					break;
				}

				// Accumulate raw values; conversion to user units happens in addCumulativeEnvelope via cv()
				$cume += (float)$v;
				if (!isset($yearCume[$m])) { $yearCume[$m] = []; }
				$yearCume[$m][$d] = $cume;
				$lastValidMD = [$m, $d];
			}
			
			// Only include this year if last valid date is exactly Dec 31
			// This ensures every expected calendar day was valid (no missing days)
			if ($lastValidMD && $lastValidMD[0] === 12 && $lastValidMD[1] === 31) {
				$cumeSums[$y] = $yearCume;
			}
		}

		if (empty($cumeSums)) {
			return ['v' => self::CACHE_VERSION, 'date' => $today, 'sig' => $sig ?? self::signature(),
				'lo' => [], 'hi' => [], 'years' => []];
		}

		// Compute min/max across years for each calendar date (month-day)
		// Using [m][d] keys for proper calendar alignment (March 1 is always 3-1, not DOY 60 vs 61)
		$lo = [];
		$hi = [];
		$years = [];

		// Iterate through all possible calendar dates (Jan 1 through Dec 31, including Feb 29)
		for ($m = 1; $m <= 12; $m++) {
			$daysInMonth = ($m === 2) ? 29 : (in_array($m, [4, 6, 9, 11]) ? 30 : 31);
			for ($d = 1; $d <= $daysInMonth; $d++) {
				$vals = [];
				foreach ($cumeSums as $y => $ymonths) {
					if (isset($ymonths[$m][$d])) {
						$vals[] = $ymonths[$m][$d];
					}
				}

				if (empty($vals)) { continue; }

				if (!isset($lo[$m])) { $lo[$m] = []; }
				if (!isset($hi[$m])) { $hi[$m] = []; }
				if (!isset($years[$m])) { $years[$m] = []; }
				
				$lo[$m][$d] = min($vals);
				$hi[$m][$d] = max($vals);
				$years[$m][$d] = count($vals);
			}
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
