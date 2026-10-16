<?php
/**
 * Webcam sunshine (sun_model/classify.py output) for the live site.
 *
 *   sunlog/YYYYMMDD.csv : one row per daylight minute, UK local: hhmm,value,p,ps,w,flags (value 1 / 0 / blank)
 *   sunlog/latest.json  : today's running total written by the classifier every few minutes
 *
 * PHP 5.4 compatible (prod is 5.4.16): no ??, no scalar type hints.
 */
class SunLog {
	const MAX_AGE_S = 1200;      // "sunny now" only if the newest classified minute is <= 20 min old
	const FULL_COV = 0.9;
	const MIN_COV = 0.5;

	public static function dir() {
		return defined('NW3_SUNLOG_DIR') ? NW3_SUNLOG_DIR : ROOT . 'sunlog/';
	}

	/** Possible sunshine (hours) for a day-of-year, from the nw3 maxsun.csv precedent. */
	public static function sunmax($unix = null) {
		static $mx = null;
		if ($mx === null) {
			$mx = array();
			$f = ROOT . 'maxsun.csv';
			if (file_exists($f)) {
				foreach (file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $l) { $mx[] = (float)$l; }
			}
		}
		if (!$mx) { return null; }
		$doy = (int)date('z', $unix === null ? time() : $unix);
		return $mx[min($doy, count($mx) - 1)];
	}

	/** Per-minute rows for a local day: list of array(t => unix, v => 1|0|null, w => weight, flags => string). */
	public static function minutes($ymd) {
		$path = self::dir() . $ymd . '.csv';
		if (!preg_match('/^\d{8}$/', $ymd) || !file_exists($path)) { return array(); }
		$y = (int)substr($ymd, 0, 4); $m = (int)substr($ymd, 4, 2); $d = (int)substr($ymd, 6, 2);
		$rows = array();
		$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		for ($i = 1; $i < count($lines); $i++) {
			$c = explode(',', $lines[$i]);
			if (count($c) < 6 || strlen($c[0]) !== 4) { continue; }
			$rows[] = array(
				't' => mktime((int)substr($c[0], 0, 2), (int)substr($c[0], 2, 2), 0, $m, $d, $y),
				'v' => ($c[1] === '') ? null : (int)$c[1],
				'w' => (float)$c[4],
				'flags' => $c[5],
			);
		}
		return $rows;
	}

	/** Map unix-minute => 1|0|null, for charts. */
	public static function minuteMap($ymd) {
		$out = array();
		foreach (self::minutes($ymd) as $r) { $out[$r['t']] = $r['v']; }
		return $out;
	}

	/**
	 * Daily total with the coverage rule (>= 90% as is, 50-90% scaled, < 50% none).
	 * $untilUnix limits to minutes already elapsed (today). Returns null or array(hours, coverage, scaled).
	 */
	public static function dayTotal($ymd, $untilUnix = null) {
		$daylight = $classified = $sun = 0.0;
		foreach (self::minutes($ymd) as $r) {
			if ($untilUnix !== null && $r['t'] >= $untilUnix) { continue; }
			$daylight += $r['w'];
			if ($r['v'] !== null) {
				$classified += $r['w'];
				if ($r['v'] === 1) { $sun += $r['w']; }
			}
		}
		if ($daylight <= 0) { return null; }
		$cov = $classified / $daylight;
		if ($cov < self::MIN_COV) { return null; }
		$hrs = $sun / 60;
		$scaled = $cov < self::FULL_COV;
		if ($scaled) { $hrs = $hrs / $cov; }
		return array('hours' => round($hrs, 2), 'coverage' => round($cov, 3), 'scaled' => $scaled);
	}

	/** Spells for the ribbon: list of array(start, end (unix, exclusive), state sun|nosun|unknown). */
	public static function spells($ymd, $untilUnix = null) {
		$out = array();
		foreach (self::minutes($ymd) as $r) {
			if ($untilUnix !== null && $r['t'] >= $untilUnix) { break; }
			$state = ($r['v'] === null) ? 'unknown' : ($r['v'] === 1 ? 'sun' : 'nosun');
			$n = count($out);
			if ($n && $out[$n - 1]['state'] === $state && $out[$n - 1]['end'] === $r['t']) {
				$out[$n - 1]['end'] = $r['t'] + 60;
			} else {
				$out[] = array('start' => $r['t'], 'end' => $r['t'] + 60, 'state' => $state);
			}
		}
		return $out;
	}

	/** latest.json if it is for today; null otherwise. */
	public static function latestRaw() {
		$f = self::dir() . 'latest.json';
		if (!file_exists($f)) { return null; }
		$j = json_decode(file_get_contents($f), true);
		if (!is_array($j) || !isset($j['date']) || $j['date'] !== date('Y-m-d')) { return null; }
		$j['mtime'] = filemtime($f);
		return $j;
	}

	/**
	 * Live block merged into $NOW['sun'] by cron_main and exposed by api_v1:
	 * hours so far, % of sunmax, state now (sun / nosun / unknown / night), freshness.
	 */
	public static function live($now = null) {
		$now = ($now === null) ? time() : $now;
		$ymd = date('Ymd', $now);
		$raw = self::latestRaw();
		$tot = self::dayTotal($ymd, $now);
		$max = self::sunmax($now);
		$state = 'unknown'; $stateTime = null; $age = null;
		$rows = self::minutes($ymd);
		$first = $rows ? $rows[0]['t'] : null;
		$last = $rows ? $rows[count($rows) - 1]['t'] + 60 : null;
		if ($first !== null && ($now < $first || $now >= $last)) {
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
		$hrs = $tot ? $tot['hours'] : null;
		return array(
			'hours' => $hrs,
			'coverage' => $tot ? $tot['coverage'] : null,
			'scaled' => $tot ? $tot['scaled'] : false,
			'pct_of_max' => ($hrs !== null && $max) ? (int)round(100 * min($hrs, $max) / $max) : null,
			'sunmax' => $max,
			'state' => $state,
			'state_time' => $stateTime,
			'age' => $age,
			'model' => $raw && isset($raw['model']) ? $raw['model'] : null,
			'updated' => $raw ? $raw['mtime'] : null,
		);
	}
}

/** Homepage "Sunshine" card: sunny-now badge, hours so far, % of possible, sunrise-sunset minute ribbon. */
function nw3_render_sun_card($now = null) {
	$now = ($now === null) ? time() : $now;
	$ymd = date('Ymd', $now);
	$s = SunLog::live($now);
	$labels = array('sun' => 'Sunny now', 'nosun' => 'Not sunny', 'unknown' => 'No webcam data', 'night' => 'After dark');
	$state = $s['state'];
	$rows = SunLog::minutes($ymd);
	echo '<div class="wx-card wx-card-sunshine">';
	echo '<div class="wx-card-head"><a class="hidden-link" href="wxhistday.php" title="Bright sunshine from webcam imagery">Sunshine</a>';
	echo '<span class="sun-badge sun-' . $state . '">' . $labels[$state] . '</span></div>';
	echo '<div class="wx-card-rows">';
	echo '<div><span class="k">Today so far</span><span class="v"><b>' . ($s['hours'] === null ? '-' : number_format($s['hours'], 1) . ' hrs') . '</b>'
		. ($s['scaled'] ? ' <span title="Some webcam minutes missing; scaled up">*</span>' : '') . '</span></div>';
	echo '<div><span class="k">Of possible</span><span class="v">' . ($s['pct_of_max'] === null ? '-' : $s['pct_of_max'] . '%')
		. ($s['sunmax'] ? ' <span class="sun-max">of ' . number_format($s['sunmax'], 1) . ' hrs</span>' : '') . '</span></div>';
	echo '</div>';
	if ($rows) {
		$t0 = $rows[0]['t']; $t1 = $rows[count($rows) - 1]['t'] + 60; $span = max(1, $t1 - $t0);
		$segs = SunLog::spells($ymd, $now);
		$doneEnd = $t0;
		echo '<div class="sun-ribbon" role="img" aria-label="Sunshine through the day">';
		foreach ($segs as $g) {
			$w = 100 * ($g['end'] - $g['start']) / $span;
			$title = date('H:i', $g['start']) . '-' . date('H:i', $g['end']) . ' ' . ($g['state'] === 'sun' ? 'sunny' : ($g['state'] === 'nosun' ? 'not sunny' : 'no data'));
			echo '<span class="seg seg-' . $g['state'] . '" style="width:' . round($w, 3) . '%" title="' . $title . '"></span>';
			$doneEnd = $g['end'];
		}
		if ($doneEnd < $t1) {
			echo '<span class="seg seg-future" style="width:' . round(100 * ($t1 - $doneEnd) / $span, 3) . '%"></span>';
		}
		echo '</div>';
		echo '<div class="sun-ribbon-axis"><span>' . date('H:i', $t0) . '</span><span>' . date('H:i', $t1) . '</span></div>';
	}
	echo '</div>';
}
