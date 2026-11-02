<?php
/**
 * RecapPack - the facts a recap writer needs for one period, as one JSON object (served by api_recap_pack.php).
 *
 * Kinds and periods are the same as Recap (day YYYY-MM-DD, month / month_to_date YYYY-MM, year / year_to_date
 * YYYY). The *_to_date kinds cover the 1st of the period up to yesterday (London), or the period end if earlier.
 *
 * Everything is in metric units (°C, mm, hours, mph, hPa, %, µg/m³). Comparisons:
 *   - normals: the site's 1971–2000 long-term averages (LTA), pro-rated by day for part months;
 *   - station: the same calendar span in every station year since 2009 (ranks, record, previous record,
 *     "highest since" years and the other years' mean);
 *   - long_term: the same span in the Hampstead archive (historical.csv, rain from 1871, temperature from 1881,
 *     sunshine from 1910, up to 2008) joined to the station years.
 *
 * Results are cached in V5_CACHE_ROOT/recap_pack/, keyed by the mtimes and sizes of the data files, so they are
 * rebuilt automatically whenever the data changes (and warmed each morning by warm_recap_pack.php).
 */
class RecapPack {
	const VERSION = 1;
	const FIRST_YEAR = 2009;
	const MIN_YEARS = 5;          // fewest years with data before a rank / record is reported
	const MEAN_COVER = 0.8;       // share of days needed for a mean
	const SUM_COVER = 0.95;       // share of days needed for a total or a count

	const DAT_VARS = ['tmin', 'tmax', 'tmean', 'hmin', 'hmax', 'hmean', 'pmin', 'pmax', 'pmean', 'wmean', 'wmax', 'gust',
		'rain', 'hrmax', '10max', 'ratemax', 'dmin', 'dmax', 'dmean', 'nightmin', 'daymax', 'fmin', 'fmax', 'fmean',
		'afhrs', 'aqmin', 'aqmax', 'aqmean'];
	const DATM_VARS = ['sunhr', 'wethr', 'snow', 'lysnw', 'hail', 'thunder', 'fog'];
	const FLAG_VARS = ['snow', 'lysnw', 'hail', 'thunder', 'fog']; // blank = none observed
	const HIST_VARS = ['rain', 'sunhr', 'lysnw', 'tmin', 'tmax'];

	const UNITS = ['temperature' => '°C', 'rain' => 'mm', 'sunshine' => 'hours', 'wind' => 'mph', 'pressure' => 'hPa',
		'humidity' => '%', 'pm25' => 'µg/m³', 'snow_depth' => 'cm'];

	/** Daily variables: label, unit, record directions, and whether it is a headline variable. */
	const DAILY = [
		'tmax' => ['Max temperature (00–00)', '°C', ['high', 'low'], true],
		'tmin' => ['Min temperature (00–00)', '°C', ['high', 'low'], true],
		'tmean' => ['Mean temperature', '°C', ['high', 'low'], true],
		'daymax' => ['Day max (09–21)', '°C', ['high', 'low'], true],
		'nightmin' => ['Night min (21–09)', '°C', ['high', 'low'], true],
		'rain' => ['Rainfall', 'mm', ['high'], true],
		'sunhr' => ['Sunshine', 'h', ['high'], true],
		'hrmax' => ['Max hourly rain', 'mm', ['high'], false],
		'10max' => ['Max 10-min rain', 'mm', ['high'], false],
		'ratemax' => ['Max rain rate', 'mm/h', ['high'], false],
		'gust' => ['Max gust', 'mph', ['high'], false],
		'wmax' => ['Max wind speed', 'mph', ['high'], false],
		'wmean' => ['Mean wind speed', 'mph', ['high'], false],
		'pmin' => ['Min pressure', 'hPa', ['low'], false],
		'pmax' => ['Max pressure', 'hPa', ['high'], false],
		'hmin' => ['Min humidity', '%', ['low'], false],
		'dmax' => ['Max dew point', '°C', ['high'], false],
		'dmin' => ['Min dew point', '°C', ['low'], false],
		'fmin' => ['Min feels-like', '°C', ['low'], false],
		'fmax' => ['Max feels-like', '°C', ['high'], false],
		'aqmean' => ['Mean PM2.5', 'µg/m³', ['high'], false],
		'aqmax' => ['Max PM2.5', 'µg/m³', ['high'], false],
	];

	/** Period metrics: label, unit, how (mean|sum|max|min|count), source var, test for counts, headline? */
	private static function metrics() {
		return [
			'tmean' => ['Mean temperature', '°C', 'mean', 'tmean', null, true],
			'tmax' => ['Mean daily max', '°C', 'mean', 'tmax', null, true],
			'tmin' => ['Mean daily min', '°C', 'mean', 'tmin', null, true],
			'rain' => ['Rainfall', 'mm', 'sum', 'rain', null, true],
			'sunhr' => ['Sunshine', 'h', 'sum', 'sunhr', null, true],
			'rain_days' => ['Rain days (≥0.2 mm)', 'days', 'count', 'rain', function ($v) { return $v >= 0.2; }, true],
			'rain_days_1mm' => ['Days with ≥1 mm', 'days', 'count', 'rain', function ($v) { return $v >= 1; }, true],
			'rain_days_10mm' => ['Days with ≥10 mm', 'days', 'count', 'rain', function ($v) { return $v >= 10; }, true],
			'air_frosts' => ['Air frosts (min <0 °C)', 'days', 'count', 'tmin', function ($v) { return $v < 0; }, true],
			'ice_days' => ['Ice days (max <0 °C)', 'days', 'count', 'tmax', function ($v) { return $v < 0; }, true],
			'summer_days' => ['Days with max ≥25 °C', 'days', 'count', 'tmax', function ($v) { return $v >= 25; }, true],
			'hot_days' => ['Days with max ≥30 °C', 'days', 'count', 'tmax', function ($v) { return $v >= 30; }, true],
			'sunless_days' => ['Sunless days', 'days', 'count', 'sunhr', function ($v) { return $v <= 0; }, true],
			'fog_days' => ['Fog days (fog at 09)', 'days', 'count', 'fog', function ($v) { return $v > 0; }, true],
			'snowfall_days' => ['Days with falling snow', 'days', 'count', 'snow', function ($v) { return $v > 0; }, true],
			'lying_snow_days' => ['Days with lying snow at 09', 'days', 'count', 'lysnw', function ($v) { return $v > 0; }, true],
			'thunder_days' => ['Thunder days', 'days', 'count', 'thunder', function ($v) { return $v > 0; }, true],
			'hail_days' => ['Hail days', 'days', 'count', 'hail', function ($v) { return $v > 0; }, false],
			'tmax_max' => ['Highest max', '°C', 'max', 'tmax', null, true],
			'tmin_min' => ['Lowest min', '°C', 'min', 'tmin', null, true],
			'tmax_min' => ['Lowest max', '°C', 'min', 'tmax', null, true],
			'tmin_max' => ['Highest min', '°C', 'max', 'tmin', null, true],
			'rain_max' => ['Wettest day', 'mm', 'max', 'rain', null, true],
			'sunhr_max' => ['Sunniest day', 'h', 'max', 'sunhr', null, true],
			'snow_depth_max' => ['Deepest lying snow', 'cm', 'max', 'lysnw', null, true],
			'gust_max' => ['Max gust', 'mph', 'max', 'gust', null, false],
			'hrmax_max' => ['Max hourly rain', 'mm', 'max', 'hrmax', null, false],
			'wmean' => ['Mean wind speed', 'mph', 'mean', 'wmean', null, false],
			'pmean' => ['Mean pressure', 'hPa', 'mean', 'pmean', null, false],
			'pmin_min' => ['Lowest pressure', 'hPa', 'min', 'pmin', null, false],
			'pmax_max' => ['Highest pressure', 'hPa', 'max', 'pmax', null, false],
			'hmean' => ['Mean humidity', '%', 'mean', 'hmean', null, false],
			'aqmean' => ['Mean PM2.5', 'µg/m³', 'mean', 'aqmean', null, false],
		];
	}

	/** Which metrics are compared with the long-term (pre-2009) archive, and the source var there. */
	const LONG_TERM = ['tmax' => 'tmax', 'tmin' => 'tmin', 'tminmax' => null, 'rain' => 'rain', 'sunhr' => 'sunhr',
		'rain_days' => 'rain', 'air_frosts' => 'tmin', 'lying_snow_days' => 'lysnw', 'tmax_max' => 'tmax', 'tmin_min' => 'tmin'];

	/** Which way a metric is "extraordinary" (for the other-variables flags). */
	const OTHER_DIRS = ['gust_max' => ['high'], 'hrmax_max' => ['high'], 'wmean' => ['high', 'low'], 'pmean' => ['high', 'low'],
		'pmin_min' => ['low'], 'pmax_max' => ['high'], 'hmean' => ['high', 'low'], 'aqmean' => ['high', 'low'], 'hail_days' => ['high']];

	/** Station series [year][z] => [var => float|null], long-term [year][z], and day-of-year normals. */
	private static $S = null;
	private static $H = null;
	private static $N = null;
	private static $lastYear = null;
	private static $lastZ = null;   // last day with data in the last year

	// ------------------------------------------------------------------ data loading

	private static function num($v) {
		$v = trim((string)$v);
		return ($v !== '' && is_numeric($v)) ? (float)$v : null;
	}

	/** Files whose change invalidates every pack. */
	static function sourceFiles() {
		$files = [];
		for ($y = self::FIRST_YEAR; $y <= (int)date('Y'); $y++) {
			$files[] = ROOT . "dat$y.csv";
			$files[] = ROOT . "datm$y.csv";
		}
		$files[] = ROOT . 'historical.csv';
		$files[] = ROOT . 'tminmaxav.csv';
		$files[] = ROOT . 'maxsun.csv';
		return $files;
	}

	static function signature() {
		$s = 'v' . self::VERSION . '|' . @filemtime(__FILE__) . '|' . Recap::today();
		foreach (self::sourceFiles() as $f) {
			$st = @stat($f);
			$s .= '|' . basename($f) . ':' . ($st ? $st['mtime'] . '.' . $st['size'] : '-');
		}
		return md5($s);
	}

	private static function readCsv($file) {
		$lines = is_file($file) ? file($file, FILE_IGNORE_NEW_LINES) : [];
		if (!$lines) { return [[], []]; }
		$head = array_map('trim', explode(',', $lines[0]));
		return [$head, array_slice($lines, 1)];
	}

	private static function load() {
		if (self::$S !== null) { return; }
		self::$S = [];
		$thisYear = (int)date('Y');
		for ($y = self::FIRST_YEAR; $y <= $thisYear; $y++) {
			list($dh, $dl) = self::readCsv(ROOT . "dat$y.csv");
			list($mh, $ml) = self::readCsv(ROOT . "datm$y.csv");
			$di = array_flip($dh);
			$mi = array_flip($mh);
			$n = max(count($dl), count($ml));
			for ($z = 0; $z < $n; $z++) {
				$r = isset($dl[$z]) ? explode(',', $dl[$z]) : [];
				$m = isset($ml[$z]) ? explode(',', $ml[$z]) : [];
				$row = [];
				foreach (self::DAT_VARS as $v) {
					$row[$v] = isset($di[$v], $r[$di[$v]]) ? self::num($r[$di[$v]]) : null;
				}
				$haveM = count($m) > 1;
				foreach (self::DATM_VARS as $v) {
					$raw = isset($mi[$v], $m[$mi[$v]]) ? trim($m[$mi[$v]]) : '';
					$val = self::num($raw);
					if ($val === null && in_array($v, self::FLAG_VARS, true)) {
						// Blank: none observed. A non-numeric mark (e.g. "y") still means it happened.
						$val = $haveM ? (($raw === '') ? 0.0 : 1.0) : null;
					}
					$row[$v] = $val;
				}
				// Rain-intensity maxima are blank on dry days.
				foreach (['hrmax', '10max', 'ratemax'] as $v) {
					if ($row[$v] === null && $row['rain'] !== null && $row['rain'] < 0.2) { $row[$v] = 0.0; }
				}
				$row['cloud'] = isset($mi['cloud'], $m[$mi['cloud']]) ? trim($m[$mi['cloud']]) : '';
				$row['tminmax'] = ($row['tmin'] !== null && $row['tmax'] !== null) ? ($row['tmin'] + $row['tmax']) / 2 : null;
				if ($row['tmax'] !== null || $row['rain'] !== null || $row['sunhr'] !== null) {
					self::$S[$y][$z] = $row;
				}
			}
		}
		self::$lastYear = $thisYear;
		while (self::$lastYear > self::FIRST_YEAR && empty(self::$S[self::$lastYear])) { self::$lastYear--; }
		self::$lastZ = empty(self::$S[self::$lastYear]) ? -1 : max(array_keys(self::$S[self::$lastYear]));

		// Long-term archive (pre-station years only).
		self::$H = [];
		list($hh, $hl) = self::readCsv(ROOT . 'historical.csv');
		$hi = array_flip($hh);
		foreach ($hl as $line) {
			$r = explode(',', $line);
			if (!isset($r[0]) || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $r[0], $m) || (int)$m[1] >= self::FIRST_YEAR) { continue; }
			$row = [];
			foreach (self::HIST_VARS as $v) {
				$row[$v] = isset($hi[$v], $r[$hi[$v]]) ? self::num($r[$hi[$v]]) : null;
			}
			$row['tminmax'] = ($row['tmin'] !== null && $row['tmax'] !== null) ? ($row['tmin'] + $row['tmax']) / 2 : null;
			self::$H[(int)$m[1]][self::z((int)$m[1], (int)$m[2], (int)$m[3])] = $row;
		}

		// Daily normals by day of a leap year (z 0..365) from LTA.
		LTA::init();
		self::$N = [];
		for ($z = 0; $z <= 365; $z++) {
			list($mm, $dd) = self::md(2024, $z);
			$tn = LTA::$vars['tmin']['daily'][$z] ?? null;
			$tx = LTA::$vars['tmax']['daily'][$z] ?? null;
			$dim = (int)date('t', mktime(12, 0, 0, $mm, 1, 2023));
			self::$N[$z] = [
				'tmin' => $tn, 'tmax' => $tx, 'tmean' => ($tn !== null && $tx !== null) ? ($tn + $tx) / 2 : null,
				'rain' => LTA::$vars['rain']['monthly'][$mm - 1] / $dim,
				'sunhr' => LTA::$vars['sunhr']['daily'][$z] ?? null,
				'maxsun' => LTA::$vars['maxsun']['daily'][$z] ?? null,
				'month' => $mm,
			];
		}
	}

	/** Day of year (0-based) of a date. */
	static function z($y, $m, $d) {
		return (int)gmdate('z', gmmktime(12, 0, 0, $m, $d, $y));
	}

	/** [month, day] of day-of-year z in year y. */
	static function md($y, $z) {
		$t = gmmktime(12, 0, 0, 1, 1 + $z, $y);
		return [(int)gmdate('n', $t), (int)gmdate('j', $t)];
	}

	private static function ymd($y, $z) {
		return gmdate('Y-m-d', gmmktime(12, 0, 0, 1, 1 + $z, $y));
	}

	/** Normal-calendar index (leap-year z) for a date, so Feb 29 has its own slot and Mar 1 lines up. */
	private static function nz($m, $d) {
		return self::z(2024, $m, $d);
	}

	private static function row($y, $z) {
		return self::$S[$y][$z] ?? null;
	}

	private static function val($y, $z, $v, $longTerm = false) {
		if ($y >= self::FIRST_YEAR) {
			return self::$S[$y][$z][$v] ?? null;
		}
		return $longTerm ? (self::$H[$y][$z][$v] ?? null) : null;
	}

	/** Day indexes in year $y for the span (m1,d1)..(m2,d2); Feb 29 is skipped in non-leap years. */
	private static function span($y, $m1, $d1, $m2, $d2) {
		$leap = (bool)date('L', mktime(12, 0, 0, 1, 1, $y));
		if (!$leap && $m2 === 2 && $d2 === 29) { $d2 = 28; }
		if (!$leap && $m1 === 2 && $d1 === 29) { $m1 = 3; $d1 = 1; }
		$a = self::z($y, $m1, $d1);
		$b = self::z($y, $m2, $d2);
		return ($b >= $a) ? range($a, $b) : [];
	}

	// ------------------------------------------------------------------ helpers

	/** Whether year y has data up to (m, d): other years are only compared over spans they fully cover. */
	private static function covered($y, $m, $d) {
		if (!checkdate($m, $d, $y)) { $d = 28; }
		return sprintf('%04d-%02d-%02d', $y, $m, $d) <= self::lastDate();
	}

	private static function r($v, $dp = 1) {
		return ($v === null) ? null : round($v, $dp);
	}

	/** Aggregate one metric over day indexes of year $y. Returns [value, days with data, days]. */
	private static function agg($y, $zs, $metric, $longTerm = false) {
		$def = self::metrics()[$metric] ?? null;
		if ($metric === 'tminmax') { $def = ['', '°C', 'mean', 'tminmax', null, false]; }
		list(, , $how, $var, $test) = $def;
		if ($longTerm && $y < self::FIRST_YEAR && isset(self::LONG_TERM[$metric])) {
			$var = self::LONG_TERM[$metric] ?? $var;
			if ($metric === 'tminmax') { $var = 'tminmax'; }
		}
		$vals = [];
		foreach ($zs as $z) {
			$v = self::val($y, $z, $var, $longTerm);
			if ($v !== null) { $vals[] = $v; }
		}
		$n = count($vals);
		$total = count($zs);
		if ($n === 0 || $total === 0) { return [null, $n, $total]; }
		$need = ($how === 'mean') ? self::MEAN_COVER : self::SUM_COVER;
		if (in_array($how, ['max', 'min'], true)) { $need = self::MEAN_COVER; }
		if ($n / $total < $need) { return [null, $n, $total]; }
		switch ($how) {
			case 'mean': return [array_sum($vals) / $n, $n, $total];
			case 'sum': return [array_sum($vals), $n, $total];
			case 'max': return [max($vals), $n, $total];
			case 'min': return [min($vals), $n, $total];
			case 'count':
				$c = 0;
				foreach ($vals as $v) { if ($test($v)) { $c++; } }
				return [$c, $n, $total];
		}
		return [null, $n, $total];
	}

	/** Date (Y-m-d list) of the extreme of a metric in a span. */
	private static function extremeDates($y, $zs, $var, $how) {
		$best = null; $dates = [];
		foreach ($zs as $z) {
			$v = self::val($y, $z, $var);
			if ($v === null) { continue; }
			if ($best === null || ($how === 'max' ? $v > $best : $v < $best)) { $best = $v; $dates = [self::ymd($y, $z)]; }
			elseif ($v == $best) { $dates[] = self::ymd($y, $z); }
		}
		return [$best, $dates];
	}

	/**
	 * Rank $target's value among years. $byYear: year => value (nulls dropped). Values are compared at $dp places.
	 */
	private static function rank($byYear, $target, $dp = 1, $dirs = ['high', 'low']) {
		$byYear = array_filter($byYear, function ($v) { return $v !== null; });
		if (!isset($byYear[$target])) { return null; }
		foreach ($byYear as $y => $v) { $byYear[$y] = round($v, $dp); }
		$v = $byYear[$target];
		$n = count($byYear);
		$others = $byYear;
		unset($others[$target]);
		$out = ['value' => $v, 'years' => $n, 'first_year' => min(array_keys($byYear))];
		if (count($others)) {
			$out['others_mean'] = round(array_sum($others) / count($others), $dp + 1);
		}
		$enough = $n >= self::MIN_YEARS;
		foreach ($dirs as $dir) {
			$hi = ($dir === 'high');
			$beat = 0; $tie = 0;
			foreach ($others as $ov) {
				if ($hi ? $ov > $v : $ov < $v) { $beat++; }
				elseif ($ov == $v) { $tie++; }
			}
			$o = ['rank' => $beat + 1, 'ties' => $tie];
			$then = 0;
			foreach ($others as $oy => $ov) { if ($oy < $target && ($hi ? $ov > $v : $ov < $v)) { $then++; } }
			$o['rank_among_years_up_to_then'] = $then + 1;
			if ($others) {
				$rec = $hi ? max($others) : min($others);
				$o['previous_record'] = ['value' => $rec, 'years' => array_values(array_map('intval', array_keys($others, $rec)))];
			}
			// Most recent earlier year at least as extreme ("highest since 2018").
			$since = null;
			foreach ($others as $oy => $ov) {
				if ($oy < $target && ($hi ? $ov >= $v : $ov <= $v) && ($since === null || $oy > $since)) { $since = $oy; }
			}
			$o['last_as_' . ($hi ? 'high' : 'low')] = $since;
			// A record needs enough years and is not reported when it is shared with more than two other years
			// (e.g. no snow in most Octobers).
			if ($enough && $beat === 0 && $tie <= 2) {
				$o['record'] = ($tie > 0) ? 'tied' : 'new';
			}
			$out[$hi ? 'highest' : 'lowest'] = $o;
		}
		return $out;
	}

	private static function lastDate() {
		return self::ymd(self::$lastYear, self::$lastZ);
	}

	/** Normals over day indexes of year y: mean temps, rain and sun totals, possible sun. */
	private static function normals($y, $zs) {
		if (!$zs) { return null; }
		$n = ['tmin' => 0, 'tmax' => 0, 'tmean' => 0, 'rain' => 0, 'sunhr' => 0, 'maxsun' => 0];
		$byMonth = [];
		foreach ($zs as $z) {
			list($m, $d) = self::md($y, $z);
			$byMonth[$m][] = $d;
		}
		$days = count($zs);
		$tmin = 0; $tmax = 0;
		$counts = ['rain_days_1mm' => 'rdays', 'air_frosts' => 'afdays', 'snowfall_days' => 'fsdays', 'lying_snow_days' => 'lsdays'];
		$cnt = array_fill_keys(array_keys($counts), 0.0);
		foreach ($byMonth as $m => $ds) {
			$dim = (int)date('t', mktime(12, 0, 0, $m, 1, $y));
			$whole = count($ds) >= $dim;
			$frac = min(1, count($ds) / $dim);
			foreach ($counts as $k => $lta) {
				if (isset(LTA::$vars[$lta]['monthly'][$m - 1])) { $cnt[$k] += LTA::$vars[$lta]['monthly'][$m - 1] * $frac; }
			}
			if ($whole) {
				$tmin += LTA::$vars['tmin']['monthly'][$m - 1] * count($ds);
				$tmax += LTA::$vars['tmax']['monthly'][$m - 1] * count($ds);
				$n['rain'] += LTA::$vars['rain']['monthly'][$m - 1];
				$n['sunhr'] += LTA::$vars['sunhr']['monthly'][$m - 1];
			} else {
				foreach ($ds as $d) {
					$nz = self::nz($m, $d);
					$tmin += self::$N[$nz]['tmin'];
					$tmax += self::$N[$nz]['tmax'];
					$n['rain'] += self::$N[$nz]['rain'];
					$n['sunhr'] += self::$N[$nz]['sunhr'];
				}
			}
			foreach ($ds as $d) { $n['maxsun'] += self::$N[self::nz($m, $d)]['maxsun']; }
		}
		$n['tmin'] = $tmin / $days;
		$n['tmax'] = $tmax / $days;
		$n['tmean'] = ($n['tmin'] + $n['tmax']) / 2;
		$out = [];
		foreach ($n as $k => $v) { $out[$k] = self::r($v, ($k === 'rain' || $k === 'sunhr' || $k === 'maxsun') ? 1 : 2); }
		foreach ($cnt as $k => $v) { $out[$k] = self::r($v, 1); }
		return $out;
	}

	private static function anomalies($vals, $norm) {
		if (!$norm) { return []; }
		$a = [];
		foreach (['tmin', 'tmax', 'tmean'] as $k) {
			if (isset($vals[$k]) && $vals[$k] !== null) { $a[$k] = round($vals[$k] - $norm[$k], 1); }
		}
		foreach (['rain', 'sunhr'] as $k) {
			if (isset($vals[$k]) && $vals[$k] !== null && $norm[$k] > 0) { $a[$k . '_pct_of_normal'] = (int)round(100 * $vals[$k] / $norm[$k]); }
		}
		if (isset($vals['sunhr']) && $vals['sunhr'] !== null && $norm['maxsun'] > 0) {
			$a['sunhr_pct_of_possible'] = (int)round(100 * $vals['sunhr'] / $norm['maxsun']);
		}
		return $a;
	}

	// ------------------------------------------------------------------ streaks

	private static function streakTests() {
		$self = __CLASS__;
		return [
			'dry' => ['Dry days (<0.2 mm)', function ($r) { return $r['rain'] === null ? null : $r['rain'] < 0.2; }],
			'wet' => ['Rain days (≥0.2 mm)', function ($r) { return $r['rain'] === null ? null : $r['rain'] >= 0.2; }],
			'air_frost' => ['Air frosts (min <0 °C)', function ($r) { return $r['tmin'] === null ? null : $r['tmin'] < 0; }],
			'sunless' => ['Sunless days', function ($r) { return $r['sunhr'] === null ? null : $r['sunhr'] <= 0; }],
			'max_above_normal' => ['Max above normal', function ($r, $n) { return $r['tmax'] === null ? null : $r['tmax'] > $n['tmax']; }],
			'max_below_normal' => ['Max below normal', function ($r, $n) { return $r['tmax'] === null ? null : $r['tmax'] < $n['tmax']; }],
			'summer_days' => ['Max ≥25 °C', function ($r) { return $r['tmax'] === null ? null : $r['tmax'] >= 25; }],
			'fog' => ['Fog at 09', function ($r) { return $r['fog'] === null ? null : $r['fog'] > 0; }],
		];
	}

	/** All runs of each streak type over the station record: type => [[start, end, len], ...] (chronological). */
	private static function allStreaks() {
		static $cache = null;
		if ($cache !== null) { return $cache; }
		$tests = self::streakTests();
		$runs = array_fill_keys(array_keys($tests), []);
		$cur = array_fill_keys(array_keys($tests), null);
		for ($y = self::FIRST_YEAR; $y <= self::$lastYear; $y++) {
			$days = (int)date('L', mktime(12, 0, 0, 1, 1, $y)) ? 366 : 365;
			for ($z = 0; $z < $days; $z++) {
				if ($y === self::$lastYear && $z > self::$lastZ) { break; }
				$r = self::row($y, $z);
				list($m, $d) = self::md($y, $z);
				$n = self::$N[self::nz($m, $d)];
				$date = self::ymd($y, $z);
				foreach ($tests as $k => $t) {
					$ok = $r ? $t[1]($r, $n) : null;
					if ($ok) {
						if ($cur[$k] === null) { $cur[$k] = [$date, $date, 0]; }
						$cur[$k][1] = $date;
						$cur[$k][2]++;
					} elseif ($cur[$k] !== null) {
						$runs[$k][] = $cur[$k];
						$cur[$k] = null;
					}
				}
			}
		}
		foreach ($cur as $k => $c) { if ($c !== null) { $runs[$k][] = $c; } }
		return $cache = $runs;
	}

	private static function runInfo($k, $run, $runs) {
		$longest = null; $since = null;
		foreach ($runs as $o) {
			if ($longest === null || $o[2] > $longest[2]) { $longest = $o; }
			if ($o[1] < $run[0] && $o[2] >= $run[2] && ($since === null || $o[1] > $since[1])) { $since = $o; }
		}
		$out = ['type' => $k, 'label' => self::streakTests()[$k][0], 'days' => $run[2], 'start' => $run[0], 'end' => $run[1],
			'longest_on_record' => ['days' => $longest[2], 'start' => $longest[0], 'end' => $longest[1]]];
		$out['last_as_long'] = $since ? ['days' => $since[2], 'start' => $since[0], 'end' => $since[1]] : null;
		return $out;
	}

	/** Streaks that include the date: current run per type (length >= 2). */
	private static function streaksAt($date) {
		$out = [];
		foreach (self::allStreaks() as $k => $runs) {
			foreach ($runs as $run) {
				if ($run[0] <= $date && $run[1] >= $date && $run[2] >= 2) {
					$info = self::runInfo($k, [$run[0], $date, self::daysBetween($run[0], $date) + 1], $runs);
					$info['continues'] = $run[1] > $date;
					$out[] = $info;
				}
			}
		}
		return $out;
	}

	/** Longest run of each type that overlaps [from, to] (length >= 3, or >=2 for frost / fog). */
	private static function streaksIn($from, $to) {
		$out = [];
		foreach (self::allStreaks() as $k => $runs) {
			$best = null; $bestIn = 0;
			foreach ($runs as $run) {
				if ($run[1] < $from || $run[0] > $to) { continue; }
				$in = self::daysBetween(max($run[0], $from), min($run[1], $to)) + 1;
				if ($in > $bestIn) { $best = $run; $bestIn = $in; }
			}
			$min = in_array($k, ['air_frost', 'fog', 'summer_days'], true) ? 2 : 3;
			if ($best && $bestIn >= $min) {
				$info = self::runInfo($k, $best, $runs);
				$info['days_in_period'] = $bestIn;
				$info['began_before_period'] = $best[0] < $from;
				$info['continues_after'] = $best[1] > $to;
				$out[] = $info;
			}
		}
		return $out;
	}

	private static function daysBetween($a, $b) {
		return (int)round((strtotime($b . ' 12:00 UTC') - strtotime($a . ' 12:00 UTC')) / 86400);
	}

	// ------------------------------------------------------------------ day records

	/** Values of a daily var for (m, d) in every station year: year => value. */
	private static function sameDate($m, $d, $var, $longTerm = false) {
		$out = [];
		$y0 = $longTerm ? 1871 : self::FIRST_YEAR;
		for ($y = $y0; $y <= self::$lastYear; $y++) {
			if (!checkdate($m, $d, $y)) { continue; }
			$v = self::val($y, self::z($y, $m, $d), $var, $longTerm);
			if ($v !== null) { $out[$y] = $v; }
		}
		return $out;
	}

	/** Rank a day's value among all station days of a set (calendar month, all, or this year so far). */
	private static function rankAmongDays($y, $z, $var, $dirs, $filter) {
		$v = self::val($y, $z, $var);
		if ($v === null) { return null; }
		$v = round($v, 1);
		$n = 0; $out = [];
		$beat = array_fill_keys($dirs, 0); $tie = array_fill_keys($dirs, 0);
		$prev = array_fill_keys($dirs, null);
		for ($yy = self::FIRST_YEAR; $yy <= self::$lastYear; $yy++) {
			if (empty(self::$S[$yy])) { continue; }
			foreach (self::$S[$yy] as $zz => $r) {
				if (($yy === $y && $zz === $z) || $r[$var] === null || !$filter($yy, $zz)) { continue; }
				$n++;
				$ov = round($r[$var], 1);
				foreach ($dirs as $dir) {
					$hi = $dir === 'high';
					if ($hi ? $ov > $v : $ov < $v) { $beat[$dir]++; }
					elseif ($ov == $v) { $tie[$dir]++; }
					if ($prev[$dir] === null || ($hi ? $ov > $prev[$dir][0] : $ov < $prev[$dir][0])) {
						$prev[$dir] = [$ov, [self::ymd($yy, $zz)]];
					} elseif ($ov == $prev[$dir][0]) {
						$prev[$dir][1][] = self::ymd($yy, $zz);
					}
				}
			}
		}
		if ($n === 0) { return null; }
		$out = ['value' => $v, 'days_compared' => $n + 1];
		foreach ($dirs as $dir) {
			$o = ['rank' => $beat[$dir] + 1, 'ties' => $tie[$dir]];
			if ($prev[$dir]) {
				$o['previous_record'] = ['value' => $prev[$dir][0], 'dates' => array_slice(array_reverse($prev[$dir][1]), 0, 3)];
			}
			if ($beat[$dir] === 0 && $n + 1 >= 30) { $o['record'] = $tie[$dir] ? 'tied' : 'new'; }
			$out[$dir === 'high' ? 'highest' : 'lowest'] = $o;
		}
		return $out;
	}

	/** Top/bottom 4 values (1 dp) of a daily var per calendar month and over all station days. */
	private static function extremeIndex($var) {
		static $cache = [];
		if (isset($cache[$var])) { return $cache[$var]; }
		$idx = [];
		for ($y = self::FIRST_YEAR; $y <= self::$lastYear; $y++) {
			if (empty(self::$S[$y])) { continue; }
			foreach (self::$S[$y] as $z => $r) {
				if ($r[$var] === null) { continue; }
				$e = [round($r[$var], 1), self::ymd($y, $z)];
				foreach ([self::md($y, $z)[0], 'all'] as $key) {
					if (!isset($idx[$key])) { $idx[$key] = ['n' => 0, 'high' => [], 'low' => []]; }
					$idx[$key]['n']++;
					$idx[$key]['high'][] = $e;
					$idx[$key]['low'][] = $e;
					if (count($idx[$key]['high']) > 64) { self::trimIdx($idx[$key]); }
				}
			}
		}
		foreach ($idx as &$i) { self::trimIdx($i); }
		unset($i);
		return $cache[$var] = $idx;
	}

	private static function trimIdx(&$i) {
		usort($i['high'], function ($a, $b) { return ($b[0] <=> $a[0]) ?: strcmp($b[1], $a[1]); });
		usort($i['low'], function ($a, $b) { return ($a[0] <=> $b[0]) ?: strcmp($b[1], $a[1]); });
		// Keep every entry tied with the 4th so ties are reported in full.
		foreach (['high', 'low'] as $d) {
			$cut = min(4, count($i[$d]));
			while ($cut < count($i[$d]) && $i[$d][$cut][0] == $i[$d][$cut - 1][0] && $cut < 40) { $cut++; }
			$i[$d] = array_slice($i[$d], 0, $cut);
		}
	}

	/** Date, month and all-time record flags for one day: list of {var, label, value, scope, dir, record, previous}. */
	private static function dayRecords($y, $z, $vars = null, $withMonthAll = true) {
		list($m, $d) = self::md($y, $z);
		$out = [];
		foreach (self::DAILY as $var => $def) {
			if ($vars !== null && !in_array($var, $vars, true)) { continue; }
			$v = self::val($y, $z, $var);
			if ($v === null) { continue; }
			$rk = self::rank(self::sameDate($m, $d, $var), $y, 1, $def[2]);
			if ($rk) {
				foreach ($def[2] as $dir) {
					$o = $rk[$dir === 'high' ? 'highest' : 'lowest'];
					if (!empty($o['record'])) {
						$out[] = ['var' => $var, 'label' => $def[0], 'value' => $rk['value'], 'unit' => $def[1], 'scope' => 'date',
							'dir' => $dir, 'record' => $o['record'], 'previous' => $o['previous_record'] ?? null, 'years' => $rk['years']];
					}
				}
			}
			if (!$withMonthAll) { continue; }
			$date = self::ymd($y, $z);
			$v1 = round($v, 1);
			foreach (['month' => $m, 'all_time' => 'all'] as $scope => $key) {
				$idx = self::extremeIndex($var);
				if (!isset($idx[$key]) || $idx[$key]['n'] < 30) { continue; }
				foreach ($def[2] as $dir) {
					$best = null;
					foreach ($idx[$key][$dir] as $e) { if ($e[1] !== $date) { $best = $e; break; } }
					if ($best === null) { continue; }
					$hi = $dir === 'high';
					if ($hi ? $v1 > $best[0] : $v1 < $best[0]) { $rec = 'new'; }
					elseif ($v1 == $best[0]) { $rec = 'tied'; }
					else { continue; }
					$prevDates = [];
					foreach ($idx[$key][$dir] as $e) { if ($e[1] !== $date && $e[0] == $best[0]) { $prevDates[] = $e[1]; } }
					if ($rec === 'tied' && count($prevDates) > 2) { continue; }
					$out[] = ['var' => $var, 'label' => $def[0], 'value' => $v1, 'unit' => $def[1], 'scope' => $scope, 'dir' => $dir,
						'record' => $rec, 'previous' => ['value' => $best[0], 'dates' => $prevDates], 'days_compared' => $idx[$key]['n']];
				}
			}
		}
		return $out;
	}

	// ------------------------------------------------------------------ packs

	/** Build (or read from cache) the pack. Throws RecapError on a bad request. */
	static function get($kind, $period) {
		if (!Recap::isKind($kind)) {
			throw new RecapError(400, 'kind must be one of ' . implode(', ', Recap::KINDS));
		}
		$p = Recap::parsePeriod($kind, $period);
		$today = Recap::today();
		$err = Recap::periodError($kind, $p, $today);
		if ($err !== null) {
			throw new RecapError(400, $err);
		}
		$key = Recap::periodKey($kind, $p);
		$sig = self::signature();
		$dir = V5_CACHE_ROOT . 'recap_pack/';
		$file = $dir . $kind . '_' . $key . '.json';
		if (is_file($file)) {
			$cached = json_decode((string)file_get_contents($file), true);
			if (is_array($cached) && ($cached['meta']['signature'] ?? null) === $sig) {
				return $cached;
			}
		}
		// One build at a time (a build reads every data file): wait up to ~10 s for another to finish.
		if (!is_dir($dir)) { @mkdir($dir, 02775, true); }
		$lock = @fopen($dir . '.build.lock', 'c');
		$got = false;
		for ($i = 0; $lock && $i < 20 && !($got = flock($lock, LOCK_EX | LOCK_NB)); $i++) { usleep(500000); }
		if (!$got) {
			throw new RecapError(503, 'busy building another pack; retry shortly');
		}
		try {
			if (is_file($file)) {
				$cached = json_decode((string)file_get_contents($file), true);
				if (is_array($cached) && ($cached['meta']['signature'] ?? null) === $sig) {
					return $cached;
				}
			}
			$pack = self::build($kind, $p);
			$pack['meta']['signature'] = $sig;
			$pack['meta']['built_at'] = gmdate('Y-m-d\TH:i:s\Z');
			if (function_exists('nw3_atomic_write')) {
				nw3_atomic_write($file, json_encode($pack, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION));
			}
			return $pack;
		} finally {
			flock($lock, LOCK_UN);
			fclose($lock);
		}
	}

	static function build($kind, $p) {
		self::load();
		if ($kind === 'day') {
			return self::buildDay($p[0], $p[1], $p[2]);
		}
		// Span of the period, cut at the last day with data.
		$y = $p[0];
		if ($kind === 'month' || $kind === 'month_to_date') {
			$m1 = $p[1]; $d1 = 1; $m2 = $p[1]; $d2 = (int)date('t', mktime(12, 0, 0, $p[1], 1, $y));
		} else {
			$m1 = 1; $d1 = 1; $m2 = 12; $d2 = 31;
		}
		$last = self::lastDate();
		if (sprintf('%04d-%02d-%02d', $y, $m1, $d1) > $last) {
			throw new RecapError(404, 'no data yet for this period');
		}
		if (sprintf('%04d-%02d-%02d', $y, $m2, $d2) > $last) {
			$m2 = (int)substr($last, 5, 2);
			$d2 = (int)substr($last, 8, 2);
		}
		return self::buildPeriod($kind, $y, $m1, $d1, $m2, $d2);
	}

	private static function meta($kind, $period, $from, $to, $extra = []) {
		return array_merge([
			'kind' => $kind,
			'period' => $period,
			'from' => $from,
			'to' => $to,
			'station' => 'NW3 Weather, Hampstead, London (since 2009)',
			'normals' => '1971–2000 long-term averages (LTA)',
			'units' => self::UNITS,
			'notes' => [
				'Station ranks compare the same calendar span in each year since 2009 (rank 1 = highest/lowest; ties share).',
				'long_term ranks add the Hampstead archive (historical.csv, to 2008) to the station years.',
				'Fog, snow, hail and thunder are manual observations; a blank day counts as none, so counts are lower bounds.',
				'Day max (09–21) and night min (21–09) are the site record conventions for temperature; tmax/tmin are midnight to midnight.',
			],
		], $extra);
	}

	private static function buildDay($y, $m, $d) {
		$z = self::z($y, $m, $d);
		$date = sprintf('%04d-%02d-%02d', $y, $m, $d);
		$row = self::row($y, $z);
		if ($row === null) {
			throw new RecapError(404, 'no data yet for this day');
		}
		$norm = self::$N[self::nz($m, $d)];
		$vals = [];
		foreach (self::DAILY as $v => $def) {
			if ($row[$v] !== null) { $vals[$v] = $row[$v]; }
		}
		foreach (['wethr', 'snow', 'lysnw', 'hail', 'thunder', 'fog'] as $v) { $vals[$v] = $row[$v]; }
		$vals['cloud_code'] = $row['cloud'];
		$missing = [];
		foreach (['tmax', 'tmin', 'rain', 'sunhr', 'daymax', 'nightmin'] as $v) { if ($row[$v] === null) { $missing[] = $v; } }

		$normals = ['tmin' => self::r($norm['tmin'], 1), 'tmax' => self::r($norm['tmax'], 1), 'tmean' => self::r($norm['tmean'], 1),
			'rain' => self::r($norm['rain'], 1), 'sunhr' => self::r($norm['sunhr'], 1), 'maxsun' => self::r($norm['maxsun'], 1)];
		$anoms = self::anomalies($vals, $normals);

		// Same calendar date in other years (station), and in the long-term archive for the headline vars.
		$dateRanks = [];
		foreach (self::DAILY as $v => $def) {
			if (!$def[3] || !isset($vals[$v])) { continue; }
			$dateRanks[$v] = self::rank(self::sameDate($m, $d, $v), $y, 1, $def[2]);
		}
		$ltRanks = [];
		foreach (['tmax', 'tmin', 'rain', 'sunhr'] as $v) {
			if (!isset($vals[$v])) { continue; }
			$ltRanks[$v] = self::rank(self::sameDate($m, $d, $v, true), $y, 1, self::DAILY[$v][2]);
		}

		// Among days: this calendar month in every year, this month so far, this year so far, all time.
		$among = [];
		foreach (['tmax', 'tmin', 'tmean', 'daymax', 'nightmin', 'rain', 'sunhr'] as $v) {
			if (!isset($vals[$v])) { continue; }
			$dirs = self::DAILY[$v][2];
			$among[$v] = [
				'this_month_so_far' => self::rankAmongDays($y, $z, $v, $dirs, function ($yy, $zz) use ($y, $m, $z) { return $yy === $y && $zz < $z && self::md($yy, $zz)[0] === $m; }),
				'this_year_so_far' => self::rankAmongDays($y, $z, $v, $dirs, function ($yy, $zz) use ($y, $z) { return $yy === $y && $zz < $z; }),
				'calendar_month_all_years' => self::rankAmongDays($y, $z, $v, $dirs, function ($yy, $zz) use ($m) { return self::md($yy, $zz)[0] === $m; }),
				'all_days' => self::rankAmongDays($y, $z, $v, $dirs, function () { return true; }),
			];
		}

		$records = self::dayRecords($y, $z);
		$extra = self::dayExtraordinary($y, $z, $m);

		$prev = [];
		for ($i = 1; $i <= 3; $i++) {
			$t = strtotime("$date 12:00 UTC -$i days");
			$py = (int)gmdate('Y', $t);
			$pr = self::row($py, (int)gmdate('z', $t));
			if ($pr) {
				$prev[] = ['date' => gmdate('Y-m-d', $t), 'tmax' => $pr['tmax'], 'tmin' => $pr['tmin'], 'rain' => $pr['rain'], 'sunhr' => $pr['sunhr'], 'fog' => $pr['fog']];
			}
		}

		$mtdFrom = sprintf('%04d-%02d-01', $y, $m);
		$mtd = self::periodValues($y, self::span($y, $m, 1, $m, $d));
		$mtdNorm = self::normals($y, self::span($y, $m, 1, $m, $d));

		return [
			'meta' => self::meta('day', $date, $date, $date, [
				'weekday' => gmdate('l', gmmktime(12, 0, 0, $m, $d, $y)),
				'data_ready' => !$missing,
				'missing' => $missing,
				'links' => ['daily_report' => "https://nw3weather.co.uk/wxhistday.php?year=$y&month=$m&day=$d"],
			]),
			'values' => $vals,
			'normals' => $normals,
			'anomalies' => $anoms,
			'same_date' => ['station' => $dateRanks, 'long_term' => $ltRanks],
			'rank_among_days' => $among,
			'records' => $records,
			'streaks' => self::streaksAt($date),
			'extraordinary' => $extra,
			'previous_days' => $prev,
			'month_to_date' => ['from' => $mtdFrom, 'to' => $date, 'values' => $mtd, 'normals' => $mtdNorm, 'anomalies' => self::anomalies($mtd, $mtdNorm)],
		];
	}

	/** Other variables on a day that are top 3 for the calendar month or top 10 of all days. */
	private static function dayExtraordinary($y, $z, $m) {
		$out = [];
		foreach (self::DAILY as $v => $def) {
			if ($def[3] || self::val($y, $z, $v) === null) { continue; }
			$mo = self::rankAmongDays($y, $z, $v, $def[2], function ($yy, $zz) use ($m) { return self::md($yy, $zz)[0] === $m; });
			$al = self::rankAmongDays($y, $z, $v, $def[2], function () { return true; });
			foreach ($def[2] as $dir) {
				$k = $dir === 'high' ? 'highest' : 'lowest';
				$mr = $mo[$k]['rank'] ?? 99999; $ar = $al[$k]['rank'] ?? 99999;
				if (($mo && $mo['days_compared'] >= 100 && $mr <= 3) || ($al && $al['days_compared'] >= 365 && $ar <= 10)) {
					$out[] = ['var' => $v, 'label' => $def[0], 'unit' => $def[1], 'value' => $mo['value'] ?? $al['value'], 'dir' => $dir,
						'rank_calendar_month' => $mo ? ['rank' => $mr, 'of' => $mo['days_compared']] : null,
						'rank_all_days' => $al ? ['rank' => $ar, 'of' => $al['days_compared']] : null];
				}
			}
		}
		return $out;
	}

	/** Every metric over a span of year y (rounded), with nulls for gaps. */
	private static function periodValues($y, $zs) {
		$out = [];
		foreach (self::metrics() as $k => $def) {
			$a = self::agg($y, $zs, $k);
			$dp = ($def[2] === 'mean') ? 2 : 1;
			if ($def[2] === 'count') { $dp = 0; }
			$out[$k] = ($a[0] === null) ? null : (($def[2] === 'count') ? (int)$a[0] : round($a[0], $dp));
		}
		return $out;
	}

	private static function buildPeriod($kind, $y, $m1, $d1, $m2, $d2) {
		$zs = self::span($y, $m1, $d1, $m2, $d2);
		$from = sprintf('%04d-%02d-%02d', $y, $m1, $d1);
		$to = sprintf('%04d-%02d-%02d', $y, $m2, $d2);
		$period = ($kind === 'month' || $kind === 'month_to_date') ? sprintf('%04d-%02d', $y, $m1) : (string)$y;
		$metrics = self::metrics();

		$vals = []; $coverage = [];
		foreach ($metrics as $k => $def) {
			$a = self::agg($y, $zs, $k);
			$dp = ($def[2] === 'mean') ? 2 : (($def[2] === 'count') ? 0 : 1);
			$vals[$k] = ($a[0] === null) ? null : (($def[2] === 'count') ? (int)$a[0] : round($a[0], $dp));
			if ($a[1] < $a[2]) { $coverage[$def[3]] = ['days_with_data' => $a[1], 'days' => $a[2]]; }
		}
		$norm = self::normals($y, $zs);
		$anoms = self::anomalies($vals, $norm);
		foreach (['rain_days_1mm', 'air_frosts', 'snowfall_days', 'lying_snow_days'] as $k) {
			if ($vals[$k] !== null && isset($norm[$k])) { $anoms[$k . '_vs_normal'] = round($vals[$k] - $norm[$k], 1); }
		}

		// Dates of the extremes.
		$ext = [];
		foreach (['tmax_max' => ['tmax', 'max'], 'tmin_min' => ['tmin', 'min'], 'tmax_min' => ['tmax', 'min'], 'tmin_max' => ['tmin', 'max'],
			'rain_max' => ['rain', 'max'], 'sunhr_max' => ['sunhr', 'max'], 'gust_max' => ['gust', 'max'], 'hrmax_max' => ['hrmax', 'max'],
			'snow_depth_max' => ['lysnw', 'max'], 'daymax_max' => ['daymax', 'max'], 'nightmin_min' => ['nightmin', 'min'],
			'daymax_min' => ['daymax', 'min'], 'nightmin_max' => ['nightmin', 'max']] as $k => $vh) {
			list($best, $dates) = self::extremeDates($y, $zs, $vh[0], $vh[1]);
			if ($best !== null && !($k === 'snow_depth_max' && $best <= 0)) { $ext[$k] = ['value' => round($best, 1), 'dates' => $dates]; }
		}

		// Ranks among station years over the same span.
		$station = []; $other = [];
		foreach ($metrics as $k => $def) {
			$by = [];
			for ($yy = self::FIRST_YEAR; $yy <= self::$lastYear; $yy++) {
				if (!self::covered($yy, $m2, $d2)) { continue; }
				$a = self::agg($yy, self::span($yy, $m1, $d1, $m2, $d2), $k);
				if ($a[0] !== null) { $by[$yy] = $a[0]; }
			}
			$dp = ($def[2] === 'mean') ? 2 : 1;
			$rk = self::rank($by, $y, $dp);
			if ($rk === null) { continue; }
			if ($def[5]) {
				$station[$k] = $rk;
			} elseif (isset(self::OTHER_DIRS[$k])) {
				foreach (self::OTHER_DIRS[$k] as $dir) {
					$o = $rk[$dir === 'high' ? 'highest' : 'lowest'];
					// Not for values shared with several other years, nor "most" of something that did not happen.
					if ($o['ties'] > 2 || ($dir === 'high' && $rk['value'] <= 0)) { continue; }
					if ($rk['years'] >= self::MIN_YEARS && $o['rank'] <= 2) {
						$other[] = ['metric' => $k, 'label' => $def[0], 'unit' => $def[1], 'value' => $rk['value'], 'dir' => $dir,
							'rank' => $o['rank'], 'of_years' => $rk['years'], 'record' => $o['record'] ?? null,
							'previous_record' => $o['previous_record'] ?? null];
					}
				}
			}
		}
		// The whole table for a month, so the writer can compare years directly.
		$yearsTable = [];
		for ($yy = self::FIRST_YEAR; $yy <= self::$lastYear; $yy++) {
			if (!self::covered($yy, $m2, $d2)) { continue; }
			$sp = self::span($yy, $m1, $d1, $m2, $d2);
			$row = ['year' => $yy];
			foreach (['tmean', 'tmax', 'tmin', 'rain', 'sunhr', 'rain_days', 'air_frosts', 'fog_days', 'lying_snow_days'] as $k) {
				$a = self::agg($yy, $sp, $k);
				$row[$k] = ($a[0] === null) ? null : round($a[0], $k === 'tmean' || $k === 'tmax' || $k === 'tmin' ? 1 : 1);
			}
			if (count(array_filter($row, function ($v) { return $v !== null; })) > 1) { $yearsTable[] = $row; }
		}

		// Long-term: the archive years plus the station years.
		$longTerm = [];
		foreach (self::LONG_TERM as $k => $src) {
			$by = [];
			$first = ($k === 'rain' || $k === 'rain_days') ? 1871 : (($k === 'sunhr') ? 1910 : 1881);
			for ($yy = $first; $yy <= self::$lastYear; $yy++) {
				if (($yy < self::FIRST_YEAR && empty(self::$H[$yy])) || !self::covered($yy, $m2, $d2)) { continue; }
				$a = self::agg($yy, self::span($yy, $m1, $d1, $m2, $d2), $k, true);
				if ($a[0] !== null) { $by[$yy] = $a[0]; }
			}
			$rk = self::rank($by, $y, ($k === 'tmax' || $k === 'tmin' || $k === 'tminmax') ? 2 : 1);
			if ($rk) { $longTerm[$k === 'tminmax' ? 'tmean_of_max_and_min' : $k] = $rk; }
		}

		// Notable days: records set or tied, period extremes, and observed fog / snow / thunder / hail.
		$notable = [];
		$tag = function ($date, $t) use (&$notable) { $notable[$date][] = $t; };
		foreach ($zs as $z) {
			$date = self::ymd($y, $z);
			$r = self::row($y, $z);
			if (!$r) { continue; }
			$isYear = ($kind === 'year' || $kind === 'year_to_date');
			foreach (self::dayRecords($y, $z, ['tmax', 'tmin', 'daymax', 'nightmin', 'tmean', 'rain', 'sunhr', 'gust', 'hrmax']) as $rec) {
				// Over a whole year only month and all-time records are worth a mention.
				if ($isYear && $rec['scope'] === 'date') { continue; }
				$tag($date, ['type' => 'record'] + $rec);
			}
			foreach (['fog' => 'Fog at 09', 'snow' => 'Falling snow', 'lysnw' => 'Lying snow at 09', 'thunder' => 'Thunder', 'hail' => 'Hail'] as $f => $lab) {
				if (($r[$f] ?? 0) > 0) { $tag($date, ['type' => 'observed', 'what' => $lab, 'value' => $r[$f]]); }
			}
		}
		foreach ($ext as $k => $e) {
			foreach ($e['dates'] as $dt) { $tag($dt, ['type' => 'period_extreme', 'what' => $k, 'value' => $e['value']]); }
		}
		ksort($notable);
		$nd = [];
		foreach ($notable as $date => $tags) {
			$zz = self::z((int)substr($date, 0, 4), (int)substr($date, 5, 2), (int)substr($date, 8, 2));
			$r = self::row($y, $zz);
			$nd[] = ['date' => $date, 'tmax' => $r['tmax'] ?? null, 'tmin' => $r['tmin'] ?? null, 'rain' => $r['rain'] ?? null,
				'sunhr' => $r['sunhr'] ?? null, 'tags' => $tags];
		}

		$out = [
			'meta' => self::meta($kind, $period, $from, $to, [
				'days' => count($zs),
				'complete' => ($kind === 'month' || $kind === 'year') && $to === sprintf('%04d-%02d-%02d', $y, $kind === 'year' ? 12 : $m1,
					$kind === 'year' ? 31 : (int)date('t', mktime(12, 0, 0, $m1, 1, $y))),
				'coverage_gaps' => $coverage,
				'links' => ($kind === 'month' || $kind === 'month_to_date')
					? ['monthly_report' => "https://nw3weather.co.uk/wxhistmonth.php?year=$y&month=$m1"]
					: ['annual_report' => "https://nw3weather.co.uk/wxhistyear.php?year=$y"],
			]),
			'values' => $vals,
			'extreme_dates' => $ext,
			'normals' => $norm,
			'anomalies' => $anoms,
			'station_ranks' => $station,
			'long_term_ranks' => $longTerm,
			'years_table' => $yearsTable,
			'notable_days' => $nd,
			'streaks' => self::streaksIn($from, $to),
			'extraordinary' => $other,
		];
		if ($kind === 'year' || $kind === 'year_to_date') {
			$out['months'] = self::monthBreakdown($y, $m2, $d2);
		}
		return $out;
	}

	/** Each month of the year so far: headline values, anomalies and station ranks. */
	private static function monthBreakdown($y, $mLast, $dLast) {
		$rows = [];
		for ($m = 1; $m <= $mLast; $m++) {
			$dEnd = ($m === $mLast) ? $dLast : (int)date('t', mktime(12, 0, 0, $m, 1, $y));
			$zs = self::span($y, $m, 1, $m, $dEnd);
			$v = self::periodValues($y, $zs);
			$n = self::normals($y, $zs);
			$row = ['month' => sprintf('%04d-%02d', $y, $m), 'complete' => $dEnd === (int)date('t', mktime(12, 0, 0, $m, 1, $y)),
				'tmean' => $v['tmean'], 'rain' => $v['rain'], 'sunhr' => $v['sunhr'], 'anomalies' => self::anomalies($v, $n)];
			foreach (['tmean', 'rain', 'sunhr'] as $k) {
				$by = [];
				for ($yy = self::FIRST_YEAR; $yy <= self::$lastYear; $yy++) {
					if (!self::covered($yy, $m, $dEnd)) { continue; }
					$a = self::agg($yy, self::span($yy, $m, 1, $m, $dEnd), $k);
					if ($a[0] !== null) { $by[$yy] = $a[0]; }
				}
				$rk = self::rank($by, $y, $k === 'tmean' ? 2 : 1);
				if ($rk) {
					$row['rank_' . $k] = ['highest' => $rk['highest']['rank'], 'lowest' => $rk['lowest']['rank'], 'of_years' => $rk['years']];
				}
			}
			$rows[] = $row;
		}
		return $rows;
	}

	/** Periods worth precomputing on a London date: yesterday's day, month and year so far, and on the 1st last month (and on 1 Jan last year). */
	static function warmList($today = null) {
		$today = $today ?: Recap::today();
		$yest = date('Y-m-d', strtotime($today . ' 12:00 -1 day'));
		$list = [['day', $yest], ['month_to_date', substr($yest, 0, 7)], ['year_to_date', substr($yest, 0, 4)]];
		if (substr($today, 8, 2) === '01') {
			$list = [['day', $yest], ['month', substr($yest, 0, 7)]];
			if (substr($today, 5, 2) !== '01') {
				$list[] = ['year_to_date', substr($yest, 0, 4)];
			} else {
				$list[] = ['year', substr($yest, 0, 4)];
			}
		}
		return $list;
	}
}
