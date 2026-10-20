<?php
/**
 * Webcam sunshine (sun_model/classify.py output) for the live site.
 *
 *   sunlog/YYYYMMDD.csv : one row per daylight minute, UK local: hhmm,value,p,ps,w,flags,pw (value 1 / 0 / blank; pw = possible-sun weight)
 *   sunlog/latest.json  : today's running total written by the classifier every few minutes
 *
 * PHP 5.4 compatible (prod is 5.4.16): no ??, no scalar type hints.
 */
class SunLog {
	const MAX_AGE_S = 1200;      // "sunny now" only if the newest classified minute is <= 20 min old
	const FULL_COV = 0.9;
	const MIN_COV = 0.5;
	const EDGE_UNKNOWN_MIN = 60.0;   // possible-sun minutes: longer start/end-of-day gaps stay unknown
	const LOCAL_WIN_MIN = 90;        // minutes either side of an interior gap used for its sunny fraction

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
				'pw' => (count($c) >= 7 && $c[6] !== '') ? (float)$c[6] : (float)$c[4],   // pre-v4 logs: pw = w
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
	 * Daily total with the partial-day rule (mirrors sun_model/rules.py partial_total; see NW3_NOTES.md):
	 * weights are possible-sun minutes (pw: daylight weight while the sun is above the local horizon cutoff);
	 * coverage = classified pw / total pw. >= 90%: measured as is. < 50%: null. Between: interior gaps (and edge gaps
	 * shorter than EDGE_UNKNOWN_MIN) take the sunny fraction of classified minutes within LOCAL_WIN_MIN either side;
	 * a longer missing block at the start or end of the day is not extrapolated (unknown_h, partial = true, so the
	 * total is a lower bound). $untilUnix limits to minutes already elapsed (today).
	 * Returns null or array(hours, measured, filled, unknown, possible, coverage, scaled, partial).
	 */
	public static function dayTotal($ymd, $untilUnix = null) {
		$rows = array();
		foreach (self::minutes($ymd) as $r) {
			if ($untilUnix !== null && $r['t'] >= $untilUnix) { continue; }
			$rows[] = $r;
		}
		return self::partialTotal($rows);
	}

	/** $rows: time-ordered array(w, pw, v). Pure function, unit-testable. */
	public static function partialTotal($rows) {
		$n = count($rows);
		if (!$n) { return null; }
		$possible = 0.0;
		foreach ($rows as $r) { $possible += $r['pw']; }
		if ($possible <= 0) {
			foreach ($rows as $k => $r) { $rows[$k]['pw'] = $r['w']; $possible += $r['w']; }
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
		return array('hours' => round(($sun + $fill) / 60, 2), 'measured' => round($sun / 60, 2),
			'filled' => round($fill / 60, 2), 'unknown' => round($unknown / 60, 2), 'possible' => round($possible / 60, 2),
			'coverage' => round($cov, 3), 'scaled' => $fill > 0, 'partial' => $unknown > 0);
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
			'partial' => $tot ? $tot['partial'] : false,
			'unknown_h' => $tot ? $tot['unknown'] : null,
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
		. ($s['partial'] ? ' <span title="Webcam data missing for part of the day (' . number_format($s['unknown_h'], 1) . ' hrs possible sun unknown); at least this much">+</span>'
			: ($s['scaled'] ? ' <span title="Some webcam minutes missing; short gaps estimated from neighbouring minutes">*</span>' : '')) . '</span></div>';
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
