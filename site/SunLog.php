<?php
/**
 * Webcam sunshine (sun_model/classify.py output) for the site and the nightly datm sunhr.
 *
 *   sunlog/YYYYMMDD.csv : one row per daylight minute, UK local: hhmm,value,p,ps,w,flags,pw
 *                         (value 1 / 0 / blank; pw = possible-sun weight)
 *   sunlog/latest.json  : today's running total written by the classifier every few minutes
 *   sun/sun_old_posts.csv : old classifier (sun_post_saver.php) vs this model, one row per day, for comparison
 */
class SunLog {
	const MAX_AGE_S = 1200;          // "sunny now" only if the newest classified minute is <= 20 min old
	const FULL_COV = 0.9;
	const MIN_COV = 0.5;
	const EDGE_UNKNOWN_MIN = 60.0;   // possible-sun minutes: longer start/end-of-day gaps stay unknown
	const LOCAL_WIN_MIN = 90;        // minutes either side of an interior gap used for its sunny fraction
	const COMPARE_COLS = ['date', 'old_h', 'old_received', 'new_h', 'new_coverage', 'new_partial', 'new_recorded', 'datm_written'];

	public static function dir(): string {
		return defined('NW3_SUNLOG_DIR') ? NW3_SUNLOG_DIR : ROOT . 'sunlog/';
	}

	/** Possible sunshine (hours) for a day-of-year, from the nw3 maxsun.csv precedent. */
	public static function sunmax(?int $unix = null): ?float {
		static $mx = null;
		if ($mx === null) {
			$mx = [];
			$f = ROOT . 'maxsun.csv';
			if (is_file($f)) {
				foreach (file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $l) { $mx[] = (float)$l; }
			}
		}
		if (!$mx) { return null; }
		$doy = (int)date('z', $unix ?? time());
		return $mx[min($doy, count($mx) - 1)];
	}

	/** Per-minute rows for a local day: list of [t => unix, v => 1|0|null, w, pw, flags]. */
	public static function minutes(string $ymd): array {
		$path = self::dir() . $ymd . '.csv';
		if (!preg_match('/^\d{8}$/', $ymd) || !is_file($path)) { return []; }
		[$y, $m, $d] = [(int)substr($ymd, 0, 4), (int)substr($ymd, 4, 2), (int)substr($ymd, 6, 2)];
		$rows = [];
		$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		foreach (array_slice($lines, 1) as $line) {
			$c = explode(',', $line);
			if (count($c) < 6 || strlen($c[0]) !== 4) { continue; }
			$rows[] = [
				't' => mktime((int)substr($c[0], 0, 2), (int)substr($c[0], 2, 2), 0, $m, $d, $y),
				'v' => $c[1] === '' ? null : (int)$c[1],
				'w' => (float)$c[4],
				'pw' => ($c[6] ?? '') !== '' ? (float)$c[6] : (float)$c[4],   // 6-column logs: pw = w
				'flags' => $c[5],
			];
		}
		return $rows;
	}

	/** Map unix-minute => 1|0|null, for charts. */
	public static function minuteMap(string $ymd): array {
		return array_column(self::minutes($ymd), 'v', 't');
	}

	/**
	 * Daily total with the partial-day rule (mirrors sun_model/rules.py partial_total; see NW3_NOTES.md):
	 * weights are possible-sun minutes (pw: daylight weight while the sun is above the local horizon cutoff);
	 * coverage = classified pw / total pw. >= 90%: measured as is. < 50%: null. Between: interior gaps (and edge gaps
	 * shorter than EDGE_UNKNOWN_MIN) take the sunny fraction of classified minutes within LOCAL_WIN_MIN either side;
	 * a longer missing block at the start or end of the day is not extrapolated (unknown, partial = true, so the
	 * total is a lower bound). $untilUnix limits to minutes already elapsed (today).
	 */
	public static function dayTotal(string $ymd, ?int $untilUnix = null): ?array {
		$rows = array_values(array_filter(self::minutes($ymd), fn($r) => $untilUnix === null || $r['t'] < $untilUnix));
		return self::partialTotal($rows);
	}

	/** $rows: time-ordered [w, pw, v]. Pure function, unit-testable. */
	public static function partialTotal(array $rows): ?array {
		$n = count($rows);
		if (!$n) { return null; }
		$possible = array_sum(array_column($rows, 'pw'));
		if ($possible <= 0) {
			foreach ($rows as $k => $r) { $rows[$k]['pw'] = $r['w']; }
			$possible = array_sum(array_column($rows, 'w'));
		}
		if ($possible <= 0) { return null; }
		$cls = $sun = 0.0;
		foreach ($rows as $r) {
			if ($r['v'] !== null) { $cls += $r['pw']; }
			if ($r['v'] === 1) { $sun += $r['w']; }
		}
		$cov = $cls / $possible;
		if ($cov < self::MIN_COV) { return null; }
		$fill = $unknown = 0.0;
		if ($cov < self::FULL_COV) {
			$dayFrac = $cls > 0 ? $sun / $cls : 0.0;
			$i = 0;
			while ($i < $n) {
				if ($rows[$i]['v'] !== null) { $i++; continue; }
				$j = $i; $gap = 0.0;
				while ($j < $n && $rows[$j]['v'] === null) { $gap += $rows[$j]['pw']; $j++; }
				if (($i === 0 || $j === $n) && $gap >= self::EDGE_UNKNOWN_MIN) {
					$unknown += $gap;
				} elseif ($gap > 0) {
					$c = $s = 0.0;
					for ($k = max(0, $i - self::LOCAL_WIN_MIN); $k < min($n, $j + self::LOCAL_WIN_MIN); $k++) {
						if (($k >= $i && $k < $j) || $rows[$k]['v'] === null) { continue; }
						$c += $rows[$k]['pw'];
						if ($rows[$k]['v'] === 1) { $s += $rows[$k]['w']; }
					}
					$frac = $c > 0 ? $s / $c : $dayFrac;
					$fill += $gap * min(1.0, $frac);
				}
				$i = $j;
			}
		}
		return ['hours' => round(($sun + $fill) / 60, 2), 'measured' => round($sun / 60, 2),
			'filled' => round($fill / 60, 2), 'unknown' => round($unknown / 60, 2), 'possible' => round($possible / 60, 2),
			'coverage' => round($cov, 3), 'scaled' => $fill > 0, 'partial' => $unknown > 0];
	}

	/** latest.json if it is for today; null otherwise. */
	public static function latestRaw(): ?array {
		$f = self::dir() . 'latest.json';
		if (!is_file($f)) { return null; }
		$j = json_decode(file_get_contents($f), true);
		if (!is_array($j) || ($j['date'] ?? null) !== date('Y-m-d')) { return null; }
		$j['mtime'] = filemtime($f);
		return $j;
	}

	/**
	 * Live block merged into $NOW['sun'] by cron_main and exposed by api_v1:
	 * hours so far, % of sunmax, state now (sun / nosun / unknown / night), freshness.
	 */
	public static function live(?int $now = null): array {
		$now ??= time();
		$ymd = date('Ymd', $now);
		$raw = self::latestRaw();
		$tot = self::dayTotal($ymd, $now);
		$max = self::sunmax($now);
		$state = 'unknown'; $stateTime = null; $age = null;
		$rows = self::minutes($ymd);
		if ($rows && ($now < $rows[0]['t'] || $now >= end($rows)['t'] + 60)) {
			$state = 'night';
		}
		for ($i = count($rows) - 1; $i >= 0; $i--) {
			if ($rows[$i]['t'] > $now || $rows[$i]['v'] === null) { continue; }
			$age = $now - $rows[$i]['t'];
			if ($state !== 'night' && $age <= self::MAX_AGE_S) {
				$state = $rows[$i]['v'] === 1 ? 'sun' : 'nosun';
				$stateTime = $rows[$i]['t'];
			}
			break;
		}
		$hrs = $tot['hours'] ?? null;
		return [
			'hours' => $hrs,
			'coverage' => $tot['coverage'] ?? null,
			'scaled' => $tot['scaled'] ?? false,
			'partial' => $tot['partial'] ?? false,
			'unknown_h' => $tot['unknown'] ?? null,
			'pct_of_max' => ($hrs !== null && $max) ? (int)round(100 * min($hrs, $max) / $max) : null,
			'sunmax' => $max,
			'state' => $state,
			'state_time' => $stateTime,
			'age' => $age,
			'model' => $raw['model'] ?? null,
			'updated' => $raw['mtime'] ?? null,
		];
	}

	/**
	 * Nightly datm sunhr from the model, for yesterday (London). DatmWriter::write only ever appends the new day's
	 * row, so an existing row (hand-entered or already written) is never touched. Returns true if a row was written.
	 * No total (under 50% webcam coverage) writes nothing: DatmWriter::checkWritten then applies its usual fallback.
	 */
	public static function writeDatm(int $yestUnix): bool {
		$ymd = date('Ymd', $yestUnix);
		$tot = self::dayTotal($ymd);
		if ($tot === null) { return false; }
		$hrs = (string)round($tot['hours'], 1);
		$written = DatmWriter::write($hrs);
		self::recordCompare($ymd, [
			'new_h' => $hrs, 'new_coverage' => (string)$tot['coverage'], 'new_partial' => $tot['partial'] ? '1' : '0',
			'new_recorded' => date('c'),
		] + ($written ? ['datm_written' => date('c')] : []));
		if ($written && $tot['partial']) {
			mail('alerts@nw3weather.co.uk', 'Sunhr for ' . $ymd . ' is a lower bound',
				"Webcam data was missing for part of the day ({$tot['unknown']} h of possible sun). Wrote {$hrs} h; please review in datamod.");
		}
		return $written;
	}

	/** Upsert one day's fields into the old-vs-new comparison CSV (atomic rewrite under a lock; tiny file). */
	public static function recordCompare(string $ymd, array $fields): bool {
		$path = defined('NW3_SUN_COMPARE_CSV') ? NW3_SUN_COMPARE_CSV : ROOT . 'sun/sun_old_posts.csv';
		// www-data (sun_post_saver) and the cron user share group nw3web; a read-only handle is enough for flock
		$lock = @fopen($path . '.lock', 'c') ?: @fopen($path . '.lock', 'r');
		if ($lock === false) { return false; }
		@chmod($path . '.lock', 0664);
		flock($lock, LOCK_EX);
		$rows = [];
		if (is_file($path)) {
			foreach (array_slice(file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES), 1) as $line) {
				$c = explode(',', $line);
				$rows[$c[0]] = array_combine(self::COMPARE_COLS, array_pad(array_slice($c, 0, count(self::COMPARE_COLS)), count(self::COMPARE_COLS), ''));
			}
		}
		$row = $rows[$ymd] ?? array_fill_keys(self::COMPARE_COLS, '');
		foreach ($fields as $k => $v) {
			if (in_array($k, self::COMPARE_COLS, true)) { $row[$k] = str_replace([',', "\r", "\n"], ' ', (string)$v); }
		}
		$row['date'] = $ymd;
		$rows[$ymd] = $row;
		ksort($rows);
		$out = implode(',', self::COMPARE_COLS) . "\n";
		foreach ($rows as $r) { $out .= implode(',', $r) . "\n"; }
		$ok = nw3_atomic_write($path, $out) !== false;
		flock($lock, LOCK_UN);
		fclose($lock);
		return $ok;
	}
}
