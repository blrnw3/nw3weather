<?php
/**
 * DayProfile - the average of each weather variable at every time of day (5-minute bins, UK clock time), from the
 * station's minute logs since 2011, using only data that passes per-variable quality rules.
 *
 * Built offline (warm_day_profile.php, nightly from cron_main) and served from CACHE_ROOT/dayprofile/. Pages and the
 * JSON endpoint only read the precomputed files; nothing scans the logs at request time.
 *
 *   build state    dayprofile/state.ser       per calendar month: source fingerprint, per-variable bin sums and
 *                                              counts (packed), days used and exclusion tallies
 *   profiles       dayprofile/p/<var>/<key>.json   key = all | y2015 | m07 | 2015-07 (station units)
 *   summary        dayprofile/meta.json       coverage and exclusions per variable, build stats
 *
 * Incremental: a month is rescanned only when its fingerprint changes (its log and sunlog files' mtime/size, its
 * datm issue notes and dat rain totals, or this file), so a nightly run normally rescans only the current month.
 * Pass full to rescan everything. One build at a time (flock on dayprofile/build.lock).
 *
 * Quality rules (see RANGES, textRules() and scanDay()): whole days are dropped per variable group for known
 * sensor faults, substitute-source periods (another station's data written into our logs), sensor drift and
 * outages, from both the list below and the datm issues notes; within a day, stale/repeated feed rows, low-cadence
 * (externally filled) hours, flatlines, spikes and out-of-range values are masked minute by minute, and days with
 * too little clean data are dropped. Rain is scaled to each day's (hand-corrected) daily total.
 */
class DayProfile {
	const VERSION = 1;
	const BIN = 5;                 // minutes per bin
	const NBIN = 288;
	const FIRST_YEAR = 2011;
	const MIN_N = 20;              // minutes of data needed for a bin to be shown
	const DIR = 'dayprofile/';

	// Variable groups that masks act on. dewp needs both temp and hum; wind and gust share 'wind'; rain and wet 'rain'.
	const G_TEMP = 'temp';
	const G_HUM = 'hum';
	const G_PRES = 'pres';
	const G_WIND = 'wind';
	const G_RAIN = 'rain';
	const G_SUN = 'sun';

	/** var => label, Wx unit type, chart colour, groups it depends on, first date, area chart, note */
	public static $VARS = [
		'temp' => ['name' => 'Temperature', 'short' => 'Temp', 'type' => Wx::Temperature, 'colour' => '#e8590c',
			'groups' => ['temp'], 'dp' => 1],
		'dewp' => ['name' => 'Dew point', 'short' => 'Dew pt', 'type' => Wx::Temperature, 'colour' => '#0c8599',
			'groups' => ['temp', 'hum'], 'dp' => 1],
		'humi' => ['name' => 'Humidity', 'short' => 'Humidity', 'type' => Wx::Humidity, 'colour' => '#2b8a3e',
			'groups' => ['hum'], 'dp' => 1],
		'pres' => ['name' => 'Pressure', 'short' => 'Pressure', 'type' => Wx::Pressure, 'colour' => '#6741d9',
			'groups' => ['pres'], 'dp' => 2],
		'wind' => ['name' => 'Wind speed', 'short' => 'Wind', 'type' => Wx::Wind, 'colour' => '#495057',
			'groups' => ['wind'], 'dp' => 1],
		'gust' => ['name' => 'Gust speed', 'short' => 'Gust', 'type' => Wx::Wind, 'colour' => '#a61e4d',
			'groups' => ['wind'], 'dp' => 1],
		'rain' => ['name' => 'Rain rate', 'short' => 'Rain', 'type' => Wx::RainRate, 'colour' => '#1c7ed6',
			'groups' => ['rain'], 'dp' => 3, 'area' => true, 'bin' => 30],
		'wet' => ['name' => 'Raining (% of time)', 'short' => 'Wet', 'type' => Wx::Percentage, 'colour' => '#4263eb',
			'groups' => ['rain'], 'dp' => 1, 'area' => true, 'from' => '2014-04-01', 'bin' => 30],
		'sun' => ['name' => 'Sunshine (% of time)', 'short' => 'Sun', 'type' => Wx::Percentage, 'colour' => '#f59f00',
			'groups' => ['sun'], 'dp' => 1, 'area' => true, 'from' => '2025-05-23'],
	];

	/** Reason codes => text (shown on the page's data-quality table). */
	public static $REASONS = [
		'range' => 'Known sensor fault, drift or substitute-source period',
		'notes' => 'Station notes for the day report a fault, data loss or substitute data',
		'dst' => 'Clock-change day (the 01:00 hour is missing or doubled)',
		'feed' => 'Stale or externally filled feed for 2+ hours',
		'sparse' => 'Under 70% of the day left after minute checks',
		'stall' => 'Anemometer stalled (calm 80%+ of the day)',
		'rainlog' => 'Rain not in the minute log, or timing unknown (catch-up jump)',
		'rainscale' => 'Minute-log rain far below the corrected daily total',
		'raingap' => 'Minute log incomplete (rain needs a near-complete day)',
		'nolog' => 'No minute log',
		'nosun' => 'No webcam sunshine log (no frames that day)',
		'suncov' => 'Webcam sunshine classified for under 90% of daylight',
	];

	const ALL = ['temp', 'hum', 'pres', 'wind', 'rain'];

	/**
	 * Date ranges excluded per group (inclusive). From the data audit (Nov 2026), the station notes and comparisons
	 * of the minute logs with Heathrow (humidity) and Heathrow/ERA5 (wind).
	 */
	public static $RANGES = [
		['2011-08-22', '2011-08-29', ['wind'], 'Logger download, not live feed: gusts unreliable'],
		['2011-12-15', '2011-12-21', ['wind'], 'Wind sensor failure; rebuilt from nearby sites'],
		['2015-05-06', '2015-05-11', ['wind'], 'Wind sensor battery flat; reconstructed'],
		['2016-02-10', '2016-05-12', ['wind'], 'Wind sensor out; Harpenden and Tottenham wind substituted'],
		['2016-09-04', '2016-09-17', ['temp'], 'T/H sensor failed; casa and St Albans data substituted'],
		['2016-09-04', '2017-09-30', ['hum'], 'Humidity read 5-10% low vs Heathrow (failed sensor, then replacement)'],
		['2017-03-12', '2017-09-01', ['rain'], 'Rain gauge broken; casa, N16 and Brixton rain substituted'],
		['2018-09-22', '2018-09-25', self::ALL, 'Weather PC died; E2 and N16 data substituted'],
		['2019-09-23', '2019-09-24', ['rain'], 'Rain gauge broken'],
		['2020-08-16', '2020-08-19', ['rain'], 'Rain gauge blocked; St James backfill'],
		['2020-09-01', '2021-03-16', ['wind'], 'Wind sensor under-reading, then Harpenden wind substituted'],
		['2021-07-12', '2021-07-14', ['rain'], 'Rain gauge blocked; tips delayed'],
		['2022-04-01', '2025-02-23', ['hum'], 'Humidity drifted 5-7% high vs Heathrow until the sensor died (Feb 2025)'],
		['2022-09-15', '2022-09-18', self::ALL, 'Power cut; logger stuck, then EGLC data'],
		['2024-04-15', '2024-05-02', ['rain'], 'Rain gauge blocked, then broken; Islington rain substituted'],
		['2024-06-28', '2024-11-30', ['wind'], 'Wind sensor dead, then weak; Harpenden wind substituted'],
		['2025-02-04', '2025-02-23', ['temp'], 'T/H sensor died; Islington and Potters Bar data substituted'],
		['2025-11-01', '2026-04-04', ['sun'], 'Webcam sunshine on hold (not yet reviewed)'],
		['2026-06-26', '2026-06-28', self::ALL, 'Station outage; Islington CWOP data filled in'],
	];

	/** Single days with a stalled anemometer (data audit F051-F064); most are also caught by the stall check. */
	public static $STALL_DAYS = ['2016-10-29', '2016-12-05', '2017-01-18', '2017-01-23', '2020-08-31', '2020-11-05',
		'2020-11-26', '2020-12-07', '2021-10-09', '2021-11-15', '2021-12-16', '2022-01-14', '2022-01-23', '2022-01-24'];

	// ---------------------------------------------------------------- reading (page side)

	public static function dir() { return CACHE_ROOT . self::DIR; }

	public static function keyFor($year, $month) {
		$year = (int)$year; $month = (int)$month;
		if ($year && $month) { return sprintf('%04d-%02d', $year, $month); }
		if ($year) { return 'y' . $year; }
		if ($month) { return sprintf('m%02d', $month); }
		return 'all';
	}

	/** Baseline to compare a period against: the same month(s) across all years, or all time. */
	public static function baseKeyFor($year, $month) {
		if ((int)$year && (int)$month) { return sprintf('m%02d', (int)$month); }
		return ((int)$year || (int)$month) ? 'all' : null;
	}

	/** Precomputed profile in station units, or null. */
	public static function read($var, $key) {
		if (!isset(self::$VARS[$var]) || !preg_match('/^(all|y\d{4}|m\d{2}|\d{4}-\d{2})$/', $key)) { return null; }
		$f = self::dir() . "p/$var/$key.json";
		if (!is_file($f)) { return null; }
		$j = json_decode((string)file_get_contents($f), true);
		return is_array($j) ? $j : null;
	}

	public static function meta() {
		$f = self::dir() . 'meta.json';
		$j = is_file($f) ? json_decode((string)file_get_contents($f), true) : null;
		return is_array($j) ? $j : null;
	}

	/** Years that have any profile data for $var (from the summary), newest first. */
	public static function years($meta, $var) {
		$ys = isset($meta['vars'][$var]['years']) ? array_keys($meta['vars'][$var]['years']) : [];
		$ys = array_map('intval', $ys);
		rsort($ys);
		return $ys;
	}

	/** Chart payload in the visitor's units: main series plus optional baseline. */
	public static function payload($var, $year, $month, $withBase = true) {
		$cfg = self::$VARS[$var];
		$type = $cfg['type'];
		$conv = function ($p) use ($type, $cfg) {
			if (!$p) { return null; }
			$dp = $cfg['dp'] + (($type === Wx::Pressure || $type === Wx::RainRate) && Page::$units === UNIT_US ? 2 : 0);
			$out = [];
			foreach ($p['avg'] as $v) { $out[] = ($v === null) ? null : Wx::convNum($v, $type, $dp); }
			return ['avg' => $out, 'n' => $p['n'], 'days' => $p['days'], 'from' => $p['from'], 'to' => $p['to']];
		};
		$key = self::keyFor($year, $month);
		$bk = $withBase ? self::baseKeyFor($year, $month) : null;
		$unit = Wx::getUnitsText($type);
		return [
			'var' => $var, 'key' => $key, 'name' => $cfg['name'], 'unit' => $unit,
			'colour' => $cfg['colour'], 'area' => !empty($cfg['area']), 'bin' => self::BIN,
			'defaultBin' => isset($cfg['bin']) ? $cfg['bin'] : self::BIN,
			'dp' => $cfg['dp'] + (($type === Wx::Pressure || $type === Wx::RainRate) && Page::$units === UNIT_US ? 2 : 0),
			'yMin' => in_array($type, [Wx::RainRate, Wx::Percentage], true) ? 0 : null,
			'main' => $conv(self::read($var, $key)),
			'baseKey' => $bk,
			'base' => $bk ? $conv(self::read($var, $bk)) : null,
		];
	}

	// ---------------------------------------------------------------- building (cron side)

	/**
	 * Build or update the cache. $full rescans every month. Returns stats, or null if another build holds the lock.
	 * $until: last day to include (default yesterday; today's log is incomplete).
	 */
	public static function build($full = false, $until = null, $log = null) {
		$t0 = microtime(true);
		$dir = self::dir();
		if (!is_dir($dir) && !@mkdir($dir, 02775, true) && !is_dir($dir)) { return null; }
		@chmod($dir, 02775);
		$lockFile = $dir . 'build.lock';
		$lock = @fopen($lockFile, 'c');
		if (!$lock) { return null; }
		@chmod($lockFile, 0664);
		if (!flock($lock, LOCK_EX | LOCK_NB)) { fclose($lock); return null; }
		try {
			$until = $until ?: date('Y-m-d', strtotime('yesterday'));
			$state = $full ? null : self::readState();
			if (!$state || ($state['v'] ?? null) !== self::VERSION) { $state = ['v' => self::VERSION, 'months' => []]; }
			$codeSig = md5_file(__FILE__);
			$notes = self::loadNotes();
			$scanned = 0; $days = 0;
			$lastY = (int)substr($until, 0, 4); $lastM = (int)substr($until, 5, 2);
			$want = [];
			for ($y = self::FIRST_YEAR; $y <= $lastY; $y++) {
				for ($m = 1; $m <= 12; $m++) {
					if ($y === $lastY && $m > $lastM) { break; }
					$ym = sprintf('%04d-%02d', $y, $m);
					$want[$ym] = true;
					$fp = self::fingerprint($y, $m, $until, $notes, $codeSig);
					if (isset($state['months'][$ym]) && $state['months'][$ym]['fp'] === $fp) { continue; }
					$state['months'][$ym] = self::scanMonth($y, $m, $until, $notes);
					$state['months'][$ym]['fp'] = $fp;
					$scanned++; $days += $state['months'][$ym]['ndays'];
					if ($log) { call_user_func($log, "scanned $ym"); }
				}
			}
			foreach (array_keys($state['months']) as $ym) { if (!isset($want[$ym])) { unset($state['months'][$ym]); } }
			ksort($state['months']);
			nw3_atomic_write($dir . 'state.ser', serialize($state));
			$written = self::writeProfiles($state, $until);
			$stats = ['built' => time(), 'until' => $until, 'full' => (bool)$full, 'monthsScanned' => $scanned,
				'daysScanned' => $days, 'filesWritten' => $written, 'secs' => round(microtime(true) - $t0, 1)];
			self::writeMeta($state, $until, $stats);
			return $stats;
		} finally {
			flock($lock, LOCK_UN);
			fclose($lock);
		}
	}

	private static function readState() {
		$f = self::dir() . 'state.ser';
		if (!is_file($f)) { return null; }
		$s = @unserialize((string)file_get_contents($f), ['allowed_classes' => false]);
		return is_array($s) ? $s : null;
	}

	/** datm issues/extra notes and dat rain totals by date, for every year in range. */
	public static function loadNotes() {
		$out = ['notes' => [], 'rain' => []];
		for ($y = self::FIRST_YEAR; $y <= (int)date('Y'); $y++) {
			foreach (['datm' => ['extra', 'issues'], 'dat' => ['rain']] as $csv => $cols) {
				$f = ROOT . $csv . $y . '.csv';
				if (!is_file($f) || !($fh = fopen($f, 'r'))) { continue; }
				$head = fgetcsv($fh, 0, ',', '"', '');
				if (!$head) { fclose($fh); continue; }
				$idx = [];
				foreach ($cols as $c) { $k = array_search($c, $head, true); if ($k !== false) { $idx[$c] = $k; } }
				$doy = 0;
				while (($r = fgetcsv($fh, 0, ',', '"', '')) !== false) {
					if ($r === [null]) { $doy++; continue; }
					$d = date('Y-m-d', mktime(12, 0, 0, 1, 1 + $doy, $y));
					$doy++;
					if ((int)substr($d, 0, 4) !== $y) { break; }
					if ($csv === 'datm') {
						$t = [];
						foreach (['extra', 'issues'] as $c) {
							if (isset($idx[$c]) && isset($r[$idx[$c]]) && trim($r[$idx[$c]]) !== '') { $t[] = trim($r[$idx[$c]]); }
						}
						if ($t) { $out['notes'][$d] = implode(' || ', $t); }
					} elseif (isset($idx['rain'])) {
						$v = isset($r[$idx['rain']]) ? trim($r[$idx['rain']]) : '';
						$out['rain'][$d] = is_numeric($v) ? (float)$v : null;
					}
				}
				fclose($fh);
			}
		}
		return $out;
	}

	private static function fingerprint($y, $m, $until, $notes, $codeSig) {
		$parts = [self::VERSION, $codeSig];
		$n = (int)date('t', mktime(12, 0, 0, $m, 1, $y));
		for ($d = 1; $d <= $n; $d++) {
			$ymd = sprintf('%04d-%02d-%02d', $y, $m, $d);
			if ($ymd > $until) { break; }
			$c = str_replace('-', '', $ymd);
			$lf = ROOT . "logfiles/daily/{$c}log.txt";
			$sf = NW3_SUNLOG_DIR . "$c.csv";
			$parts[] = $ymd . ':' . (is_file($lf) ? filemtime($lf) . '/' . filesize($lf) : '-')
				. ':' . (is_file($sf) ? filemtime($sf) . '/' . filesize($sf) : '-')
				. ':' . ($notes['notes'][$ymd] ?? '') . ':' . var_export($notes['rain'][$ymd] ?? null, true);
		}
		return md5(implode("\n", $parts));
	}

	/** Groups excluded for a date by the fixed ranges: group => reason text. */
	public static function rangeRules($ymd) {
		$out = [];
		foreach (self::$RANGES as $r) {
			if ($ymd >= $r[0] && $ymd <= $r[1]) {
				foreach ($r[2] as $g) { if (!isset($out[$g])) { $out[$g] = $r[3]; } }
			}
		}
		return $out;
	}

	/**
	 * Groups the station's free-text notes for a day mark as faulty: group => clause. A clause counts when it reports
	 * a fault, data loss, reconstruction or a substitute source; it applies to the groups it names, or to all
	 * station groups when it names none. Webcam, sightings and sun-hours notes are ignored.
	 */
	public static function textRules($txt) {
		$out = [];
		if ($txt === null || trim($txt) === '') { return $out; }
		$bad = '/\bfrom\b|loss|lossy|reconstruct|corrupt|glitch|spike|fail|broke|\bboke\b|bust|block|died|\bdead\b|batter'
			. '|not reporting|outage|power ?cut|no (\\w+ )?data|crash|substitut|subbed|patchy|froze|frozen|unreliable|server|network'
			. '|stuck|dubious|massag|relocat|cleaning|maintenance|delayed|ghost|\bdown\b|missed|estimate|wpc|new pc|ups failed'
			. '|downtime|win update|wxpc|\bcasa\b|\bcaa\b|\bucl\b|harpenden|tottenham|islington|potters|n16|\be2\b|eglc'
			. '|st ?albans|pinner|brixton|st james|not properly|computer|pc reset|software/';
		$groups = static function ($c) {
			$g = [];
			if (preg_match('/wind|gust|anemom|harpenden|tottenham|\bwmax|wmean/', $c)) { $g[] = self::G_WIND; }
			if (preg_match('/t\/h|\btemp|\bhum|tmin|tmax|hmin|hmax|radiation shield|\brh\b|\bdew/', $c)) { $g[] = self::G_TEMP; $g[] = self::G_HUM; }
			if (preg_match('/\brn\b|rain|gauge|\btips?\b|\bsn\b|snow/', $c)) { $g[] = self::G_RAIN; }
			if (preg_match('/pres|baro/', $c)) { $g[] = self::G_PRES; }
			return $g;
		};
		// "||" separates issues; within one issue, sentences share context ("Wind data loss. Not reconstructed.").
		foreach (explode('||', strtolower($txt)) as $issue) {
			$issue = trim($issue);
			if ($issue === '' || preg_match('/recovered from (the )?log|no loss|no data loss/', $issue)) { continue; }
			$hit = null;
			$g = [];
			foreach (preg_split('/;|\.\s+|\.$/', $issue) as $c) {
				$c = trim($c);
				if ($c === '' || !preg_match($bad, $c)) { continue; }
				if (preg_match('/webcam|\bcam\b|video|images?\b|sun hours/', $c)) { continue; }
				$hit = $hit ?? $c;
				$g = array_merge($g, $groups($c));
			}
			if ($hit === null) { continue; }
			if (!$g) { $g = $groups($issue); }
			if (!$g) { $g = self::ALL; }
			foreach (array_unique($g) as $x) { if (!isset($out[$x])) { $out[$x] = $hit; } }
		}
		return $out;
	}

	/** Clock-change day: local midnight and 23:30 differ in DST. */
	public static function isDstDay($ymd) {
		list($y, $m, $d) = array_map('intval', explode('-', $ymd));
		return date('I', mktime(0, 30, 0, $m, $d, $y)) !== date('I', mktime(23, 30, 0, $m, $d, $y));
	}

	private static function emptyAcc() {
		$a = [];
		foreach (self::$VARS as $v => $c) { $a[$v] = [array_fill(0, self::NBIN, 0.0), array_fill(0, self::NBIN, 0)]; }
		return $a;
	}

	/** Scan every day of a month. */
	public static function scanMonth($y, $m, $until, $notes) {
		$acc = self::emptyAcc();
		$used = array_fill_keys(array_keys(self::$VARS), 0);
		$excl = [];
		$scaled = 0;
		$n = (int)date('t', mktime(12, 0, 0, $m, 1, $y));
		$ndays = 0;
		for ($d = 1; $d <= $n; $d++) {
			$ymd = sprintf('%04d-%02d-%02d', $y, $m, $d);
			if ($ymd > $until) { break; }
			$ndays++;
			$r = self::scanDay($ymd, $notes, $acc);
			foreach ($r['used'] as $v => $ok) {
				if ($ok) { $used[$v]++; } elseif (isset($r['why'][$v])) {
					$excl[$v][$r['why'][$v]] = ($excl[$v][$r['why'][$v]] ?? 0) + 1;
				}
			}
			if (!empty($r['scaled'])) { $scaled++; }
		}
		$packed = [];
		foreach ($acc as $v => $sn) { $packed[$v] = [pack('e*', ...$sn[0]), pack('V*', ...$sn[1])]; }
		return ['acc' => $packed, 'used' => $used, 'excl' => $excl, 'ndays' => $ndays, 'scaled' => $scaled];
	}

	/**
	 * One day: apply the day-level rules, mask bad minutes, and add the clean minutes into $acc (var => [sums, counts]).
	 * Returns ['used' => var => bool, 'why' => var => reason code, 'scaled' => bool, 'debug' => [...]].
	 */
	public static function scanDay($ymd, $notes, &$acc) {
		$vars = array_keys(self::$VARS);
		$used = array_fill_keys($vars, false);
		$why = [];
		$dbg = [];
		// ---- day-level group exclusions
		$gx = [];                                       // group => reason code
		foreach (self::rangeRules($ymd) as $g => $t) { $gx[$g] = 'range'; }
		foreach (self::textRules($notes['notes'][$ymd] ?? null) as $g => $t) { if (!isset($gx[$g])) { $gx[$g] = 'notes'; } }
		if (in_array($ymd, self::$STALL_DAYS, true) && !isset($gx['wind'])) { $gx['wind'] = 'stall'; }
		if (self::isDstDay($ymd)) { foreach (self::ALL as $g) { $gx[$g] = 'dst'; } }
		$windRangeOnly = isset($gx['wind']) && !isset($gx['temp']);

		// ---- sunshine (webcam classifier) is independent of the station feed
		self::scanSun($ymd, $gx, $acc, $used, $why);

		$c = str_replace('-', '', $ymd);
		$lf = ROOT . "logfiles/daily/{$c}log.txt";
		$lines = is_file($lf) ? file($lf, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : false;
		if (!$lines) {
			foreach ($vars as $v) { if ($v !== 'sun') { $why[$v] = 'nolog'; } }
			return ['used' => $used, 'why' => $why, 'scaled' => false];
		}
		// ---- parse: first row per minute wins. Fields: wind, gust, wdir, temp, humi, pres, dewp, rain.
		$W = $G = $D = $T = $H = $P = $DP = $R = $K = [];
		$num = function ($x) { $x = trim($x); return is_numeric($x) ? (float)$x : null; };
		foreach ($lines as $line) {
			$f = explode(',', $line);
			if (count($f) < 11) { continue; }
			$hh = (int)$f[0]; $mi = (int)$f[1];
			if ($hh < 0 || $hh > 23 || $mi < 0 || $mi > 59 || !is_numeric($f[0]) || !is_numeric($f[1])) { continue; }
			$k = $hh * 60 + $mi;
			if (isset($K[$k])) { continue; }
			$K[$k] = implode(',', array_slice($f, 3, 8));
			$W[$k] = $num($f[3]); $G[$k] = $num($f[4]); $D[$k] = $num($f[5]); $T[$k] = $num($f[6]);
			$H[$k] = $num($f[7]); $P[$k] = $num($f[8]); $DP[$k] = $num($f[9]); $R[$k] = $num($f[10]);
		}
		ksort($K);
		$keys = array_keys($K);
		$present = count($keys);
		// ---- range checks (null = invalid)
		foreach ($keys as $k) {
			if ($T[$k] !== null && ($T[$k] < -25 || $T[$k] > 40)) { $T[$k] = null; }
			if ($H[$k] !== null && ($H[$k] < 1 || $H[$k] > 100)) { $H[$k] = null; }
			if ($P[$k] !== null && ($P[$k] < 940 || $P[$k] > 1060)) { $P[$k] = null; }
			if ($W[$k] !== null && ($W[$k] < 0 || $W[$k] > 100)) { $W[$k] = null; }
			if ($G[$k] !== null && ($G[$k] < 0 || $G[$k] > 150)) { $G[$k] = null; }
			if ($DP[$k] !== null && ($DP[$k] < -35 || $DP[$k] > 35)) { $DP[$k] = null; }
			if ($R[$k] !== null && $R[$k] < 0) { $R[$k] = null; }
		}
		$bad = ['all' => [], 'temp' => [], 'hum' => [], 'pres' => [], 'wind' => []];   // minute => true

		// ---- stale/repeated feed: 5+ identical rows while the wind is moving, or 30+ at any time
		$run = [];
		// (on days whose wind came from another station at a slow cadence, only the wind is masked)
		$flush = function () use (&$run, &$bad, &$W, &$G, $windRangeOnly) {
			$n = count($run);
			if ($n >= 5) {
				$k0 = $run[0];
				$moving = ($W[$k0] ?? 0) > 0 || ($G[$k0] ?? 0) > 0;
				$tgt = $windRangeOnly && $n < 30 ? 'wind' : 'all';
				if ($moving || $n >= 30) { for ($i = 1; $i < $n; $i++) { $bad[$tgt][$run[$i]] = true; } }
			}
			$run = [];
		};
		$prev = null;
		foreach ($keys as $k) {
			if ($prev !== null && $k === $prev + 1 && $K[$k] === $K[$prev]) {
				if (!$run) { $run[] = $prev; }
				$run[] = $k;
			} else { $flush(); }
			$prev = $k;
		}
		$flush();
		$dbg['repeat'] = count($bad['all']);

		// ---- low-cadence hours: wind fields change in <= 15 minutes of an hour with wind over 1 mph -> the feed was
		// a 5-10 minute external source (CWOP backfill, substitute station) or stale
		$lowHours = 0;
		for ($h = 0; $h < 24; $h++) {
			$cnt = 0; $chg = 0; $sumW = 0; $last = null;
			for ($k = $h * 60; $k < $h * 60 + 60; $k++) {
				if (!isset($K[$k])) { continue; }
				$sig = $W[$k] . '|' . $G[$k] . '|' . $D[$k];
				if ($last === null || $sig !== $last) { $chg++; }
				$last = $sig; $cnt++; $sumW += (float)$W[$k];
			}
			if ($cnt >= 30 && $sumW / $cnt > 1.0 && $chg <= 15) {
				$lowHours++;
				for ($k = $h * 60; $k < $h * 60 + 60; $k++) {
					if ($windRangeOnly) { $bad['wind'][$k] = true; } else { $bad['all'][$k] = true; }
				}
			}
		}
		$dbg['lowHours'] = $lowHours;
		if ($lowHours >= 2 && !$windRangeOnly) {
			foreach (self::ALL as $g) { if (!isset($gx[$g])) { $gx[$g] = 'feed'; } }
		}

		// ---- flatlines
		self::flat($keys, [$T, $H], function ($k0, $k1, $n) use (&$bad) {
			$mid = ($k0 + $k1) / 2;
			$day = $mid >= 480 && $mid < 1200;
			if ($n >= ($day ? 120 : 240)) { for ($k = $k0; $k <= $k1; $k++) { $bad['temp'][$k] = true; $bad['hum'][$k] = true; } }
		});
		// pressure resolution: whole hPa (to 2017, and in steps of ~1 hPa into 2018) or 0.1 hPa
		$fine = 0; $pp = null;
		foreach ($keys as $k) {
			if ($P[$k] === null) { continue; }
			if ($pp !== null) { $dl = abs($P[$k] - $pp); if ($dl > 0.05 && $dl < 0.5) { $fine++; } }
			$pp = $P[$k];
		}
		$decimalP = $fine >= 5;
		self::flat($keys, [$P], function ($k0, $k1, $n) use (&$bad, $decimalP) {
			if ($n >= ($decimalP ? 360 : 720)) { for ($k = $k0; $k <= $k1; $k++) { $bad['pres'][$k] = true; } }
		});

		// ---- spikes: a single-minute excursion beyond thr that comes straight back, or a jump beyond big that does
		// not settle back within 30 minutes of the pre-jump level
		self::spikes($keys, $T, 1.5, 3.0, $bad['temp']);
		self::spikes($keys, $H, 8, 15, $bad['hum']);
		self::spikes($keys, $P, $decimalP ? 1.0 : 1.5, 2.5, $bad['pres']);
		self::spikes($keys, $DP, 2.0, 4.0, $bad['hum']);

		// ---- per-group clean minutes
		$ok = function ($g, $k) use (&$bad) { return !isset($bad['all'][$k]) && !isset($bad[$g][$k]); };
		$series = [
			'temp' => [$T, ['temp']], 'dewp' => [$DP, ['temp', 'hum']], 'humi' => [$H, ['hum']],
			'pres' => [$P, ['pres']], 'wind' => [$W, ['wind']], 'gust' => [$G, ['wind']],
		];
		// anemometer stall
		if (!isset($gx['wind'])) {
			$calm = 0; $nw = 0;
			foreach ($keys as $k) { if ($W[$k] !== null && $ok('wind', $k)) { $nw++; if ($W[$k] == 0 && (float)$G[$k] == 0) { $calm++; } } }
			if ($nw && $calm / $nw >= 0.8) { $gx['wind'] = 'stall'; }
		}
		foreach ($series as $v => $sg) {
			list($S, $groups) = $sg;
			$skip = null;
			foreach ($groups as $g) { if (isset($gx[$g])) { $skip = $gx[$g]; break; } }
			if ($skip) { $why[$v] = $skip; continue; }
			$list = [];
			foreach ($keys as $k) {
				if ($S[$k] === null) { continue; }
				$good = true;
				foreach ($groups as $g) { if (!$ok($g, $k)) { $good = false; break; } }
				if ($v === 'dewp' && ($T[$k] === null || $H[$k] === null)) { $good = false; }
				if ($good) { $list[$k] = $S[$k]; }
			}
			if (count($list) < 1008) { $why[$v] = 'sparse'; continue; }   // 70% of 1440
			$bs = &$acc[$v][0]; $bn = &$acc[$v][1];
			foreach ($list as $k => $x) { $b = intdiv($k, self::BIN); $bs[$b] += $x; $bn[$b]++; }
			unset($bs, $bn);
			$used[$v] = true;
		}

		// ---- rain (rate) and wet (% of time)
		$scaled = false;
		if (isset($gx['rain'])) { $why['rain'] = $why['wet'] = $gx['rain']; }
		else {
			$cleanK = [];
			foreach ($keys as $k) { if ($R[$k] !== null && $ok('rain', $k)) { $cleanK[] = $k; } }
			if (count($cleanK) < 1380) { $why['rain'] = $why['wet'] = 'raingap'; }
			else {
				// increments of the running daily total (a drop is the midnight reset)
				$inc = []; $sum = 0.0; $prevK = null; $bad1 = false; $big = [];
				foreach ($cleanK as $k) {
					if ($prevK !== null) {
						$dlt = round($R[$k] - $R[$prevK], 2);
						if ($dlt > 0) {
							if ($k - $prevK > 10 && $dlt > 1.0) { $bad1 = true; }      // catch-up after a gap: timing unknown
							if ($dlt > 2.5) { $big[$k] = $dlt; }
							$inc[$k] = $dlt; $sum += $dlt;
						}
					}
					$prevK = $k;
				}
				$daily = $notes['rain'][$ymd] ?? null;
				// a 2.5 mm+ one-minute jump is a glitch when the day's total only adds up without it
				if ($big && $daily !== null) {
					$without = $sum - array_sum($big);
					if (abs($without - $daily) <= 0.35) { foreach ($big as $k => $x) { unset($inc[$k]); } $sum = $without; }
					elseif (abs($sum - $daily) > 0.35) { $bad1 = true; }
				}
				$f = null;
				if ($bad1) { $why['rain'] = $why['wet'] = 'rainlog'; }
				elseif ($daily === null) { $why['rain'] = $why['wet'] = 'rainlog'; }
				elseif ($sum <= 0) {
					// no tips: dry, or a trace (0.1-0.2 mm) entered by hand; more than that came from elsewhere
					if ($daily > 0.2) { $why['rain'] = $why['wet'] = 'rainlog'; } else { $f = 0.0; }
				} else {
					$f = $daily / $sum;
					if ($f > 2 && $daily - $sum > 1.0) { $why['rain'] = $why['wet'] = 'rainscale'; $f = null; }
				}
				if ($f !== null) {
					$scaled = abs($f - 1) > 0.001 && $sum > 0;
					$bs = &$acc['rain'][0]; $bn = &$acc['rain'][1];
					foreach ($cleanK as $k) {
						$b = intdiv($k, self::BIN);
						$bn[$b]++;
						if (isset($inc[$k])) { $bs[$b] += $inc[$k] * $f * 60; }
					}
					unset($bs, $bn);
					$used['rain'] = true;
					if ($ymd < self::$VARS['wet']['from']) { $why['wet'] = 'before'; }
					else {
						// wet minutes: tips no more than 30 min apart form a spell, which starts 15 min before its first tip
						$wet = [];
						if ($f > 0) {
							$tips = array_keys($inc);
							$i = 0; $nt = count($tips);
							while ($i < $nt) {
								$j = $i;
								while ($j + 1 < $nt && $tips[$j + 1] - $tips[$j] <= 30) { $j++; }
								for ($k = max(0, $tips[$i] - 15); $k <= $tips[$j]; $k++) { $wet[$k] = true; }
								$i = $j + 1;
							}
						}
						$bs = &$acc['wet'][0]; $bn = &$acc['wet'][1];
						foreach ($cleanK as $k) { $b = intdiv($k, self::BIN); $bn[$b]++; if (isset($wet[$k])) { $bs[$b] += 100; } }
						unset($bs, $bn);
						$used['wet'] = true;
					}
				}
				$dbg['rainLog'] = $sum; $dbg['rainDay'] = $daily;
			}
		}
		return ['used' => $used, 'why' => $why, 'scaled' => $scaled, 'debug' => $dbg, 'gx' => $gx];
	}

	/** Runs where every series in $S is unchanged between consecutive minutes; calls $fn(k0, k1, rows). */
	private static function flat($keys, $S, $fn) {
		$start = null; $prev = null;
		foreach ($keys as $k) {
			$same = $prev !== null && $k === $prev + 1;
			if ($same) {
				foreach ($S as $s) { if ($s[$k] === null || $s[$k] !== $s[$prev]) { $same = false; break; } }
			}
			if ($same) { if ($start === null) { $start = $prev; } }
			elseif ($start !== null) { $fn($start, $prev, $prev - $start + 1); $start = null; }
			$prev = $k;
		}
		if ($start !== null) { $fn($start, $prev, $prev - $start + 1); }
	}

	private static function spikes($keys, $S, $thr, $big, &$mask) {
		$v = []; foreach ($keys as $k) { if ($S[$k] !== null) { $v[] = [$k, $S[$k]]; } }
		$n = count($v);
		for ($i = 1; $i < $n - 1; $i++) {
			$a = $v[$i - 1][1]; $x = $v[$i][1]; $b = $v[$i + 1][1];
			if (abs($x - $a) > $thr && abs($x - $b) > $thr && (($x - $a) > 0) === (($x - $b) > 0)) { $mask[$v[$i][0]] = true; }
		}
		for ($i = 1; $i < $n; $i++) {
			$base = $v[$i - 1][1];
			if (abs($v[$i][1] - $base) <= $big || $v[$i][0] - $v[$i - 1][0] > 10) { continue; }
			$j = $i;
			while ($j < $n && $v[$j][0] - $v[$i][0] <= 30 && abs($v[$j][1] - $base) > $big / 2) { $j++; }
			if ($j < $n && $v[$j][0] - $v[$i][0] <= 30) {        // came back: the excursion was a glitch
				for ($q = $i; $q < $j; $q++) { $mask[$v[$q][0]] = true; }
				$i = $j;
			}
		}
	}

	/** Webcam sunshine: % of the time sunny, counting night as not sunny. */
	private static function scanSun($ymd, $gx, &$acc, &$used, &$why) {
		if ($ymd < self::$VARS['sun']['from']) { $why['sun'] = 'before'; return; }
		if (isset($gx['sun'])) { $why['sun'] = $gx['sun']; return; }
		$f = NW3_SUNLOG_DIR . str_replace('-', '', $ymd) . '.csv';
		if (!is_file($f) || !($fh = fopen($f, 'r'))) { $why['sun'] = 'nosun'; return; }
		$rows = [];
		$possible = 0.0; $classified = 0.0;
		while (($r = fgetcsv($fh, 0, ',', '"', '')) !== false) {
			if (!isset($r[4]) || !preg_match('/^\d{4}$/', $r[0])) { continue; }
			$k = (int)substr($r[0], 0, 2) * 60 + (int)substr($r[0], 2, 2);
			if ($k < 0 || $k >= 1440 || isset($rows[$k])) { continue; }
			$w = (float)$r[4];
			$pw = (isset($r[6]) && $r[6] !== '') ? (float)$r[6] : $w;
			$v = $r[1] === '' ? null : (int)$r[1];
			$rows[$k] = [$v, $w];
			$possible += $pw;
			if ($v !== null) { $classified += $pw; }
		}
		fclose($fh);
		if (!$rows) { $why['sun'] = 'nosun'; return; }
		if ($possible > 0 && $classified / $possible < 0.9) { $why['sun'] = 'suncov'; return; }
		$bs = &$acc['sun'][0]; $bn = &$acc['sun'][1];
		for ($k = 0; $k < 1440; $k++) {
			if (isset($rows[$k]) && $rows[$k][0] === null) { continue; }
			$b = intdiv($k, self::BIN);
			$bn[$b]++;
			if (isset($rows[$k]) && $rows[$k][0] === 1) { $bs[$b] += 100 * min(1.0, $rows[$k][1]); }
		}
		unset($bs, $bn);
		$used['sun'] = true;
	}

	/** Sum months into every profile key and write the files that changed. */
	private static function writeProfiles($state, $until) {
		$keysFor = function ($ym) {
			$y = substr($ym, 0, 4); $m = substr($ym, 5, 2);
			return ['all', "y$y", "m$m", $ym];
		};
		$sum = [];      // var => key => [s, n, days, from, to]
		foreach ($state['months'] as $ym => $mo) {
			foreach (self::$VARS as $v => $cfg) {
				if (empty($mo['used'][$v])) { continue; }
				$s = array_values(unpack('e*', $mo['acc'][$v][0]));
				$n = array_values(unpack('V*', $mo['acc'][$v][1]));
				foreach ($keysFor($ym) as $key) {
					if (!isset($sum[$v][$key])) { $sum[$v][$key] = [array_fill(0, self::NBIN, 0.0), array_fill(0, self::NBIN, 0), 0, $ym, $ym]; }
					$t = &$sum[$v][$key];
					for ($i = 0; $i < self::NBIN; $i++) { $t[0][$i] += $s[$i]; $t[1][$i] += $n[$i]; }
					$t[2] += $mo['used'][$v];
					if ($ym < $t[3]) { $t[3] = $ym; }
					if ($ym > $t[4]) { $t[4] = $ym; }
					unset($t);
				}
			}
		}
		$written = 0;
		$base = self::dir() . 'p/';
		foreach ($sum as $v => $keys) {
			$vdir = $base . $v . '/';
			if (!is_dir($vdir)) { @mkdir($vdir, 02775, true); }
			$keep = [];
			foreach ($keys as $key => $t) {
				$avg = [];
				for ($i = 0; $i < self::NBIN; $i++) { $avg[] = $t[1][$i] >= self::MIN_N ? round($t[0][$i] / $t[1][$i], 3) : null; }
				$json = nw3_json_encode(['var' => $v, 'key' => $key, 'bin' => self::BIN, 'days' => $t[2],
					'from' => $t[3], 'to' => $t[4], 'avg' => $avg, 'n' => $t[1]]);
				$f = $vdir . $key . '.json';
				$keep[$key . '.json'] = true;
				if (!is_file($f) || md5_file($f) !== md5($json)) { nw3_atomic_write($f, $json); $written++; }
			}
			foreach (glob($vdir . '*.json') ?: [] as $f) { if (!isset($keep[basename($f)])) { @unlink($f); } }
		}
		return $written;
	}

	private static function writeMeta($state, $until, $stats) {
		$vars = [];
		foreach (self::$VARS as $v => $cfg) {
			$from = isset($cfg['from']) ? $cfg['from'] : self::FIRST_YEAR . '-01-01';
			$possible = 0; $used = 0; $ex = []; $years = [];
			foreach ($state['months'] as $ym => $mo) {
				$y = substr($ym, 0, 4);
				$used += $mo['used'][$v] ?? 0;
				if (!empty($mo['used'][$v])) { $years[$y] = ($years[$y] ?? 0) + $mo['used'][$v]; }
				foreach ($mo['excl'][$v] ?? [] as $r => $c) { $ex[$r] = ($ex[$r] ?? 0) + $c; }
			}
			$possible = (int)round((strtotime($until) - strtotime($from)) / 86400) + 1;
			// days before the variable starts are not exclusions
			if ($v === 'wet') { $ex = self::wetRecount($state, $from); }
			unset($ex['before']);
			arsort($ex);
			$vars[$v] = ['from' => $from, 'possible' => max(0, $possible), 'used' => $used,
				'pct' => $possible > 0 ? round(100 * $used / $possible, 1) : 0, 'excluded' => $ex, 'years' => $years];
		}
		$scaled = 0; foreach ($state['months'] as $mo) { $scaled += $mo['scaled'] ?? 0; }
		$meta = ['v' => self::VERSION, 'stats' => $stats, 'rainScaledDays' => $scaled, 'vars' => $vars];
		nw3_atomic_write(self::dir() . 'meta.json', nw3_json_encode($meta));
	}

	/** wet shares rain's exclusions, minus the days before it starts. */
	private static function wetRecount($state, $from) {
		$ex = [];
		foreach ($state['months'] as $ym => $mo) {
			if ($ym . '-31' < $from) { continue; }
			foreach ($mo['excl']['wet'] ?? [] as $r => $c) { $ex[$r] = ($ex[$r] ?? 0) + $c; }
		}
		return $ex;
	}
}
