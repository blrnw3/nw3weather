<?php
/**
 * "Best webcam shots" for wx2: automatic picks from the daily webcam summary sheets, with hand pins on top.
 *
 * Scoring source: the archived daily summary sheet (Y/YmddailywebcamXX.jpg, written by cron_cam.php at 23:54),
 * which holds all 48 half-hourly frames of a day as small cells. One small JPEG per day covers the whole
 * Hikvision archive (from HIK_START), so a rebuild never touches the frame archive itself. Each daylight cell
 * gets an image score (warm sky near sunrise/sunset, cloud texture, colourfulness, contrast; a plain blue sky is
 * marked down) plus weather bonuses from the daily data (lying/falling snow, air frost, fog). Dark, blank,
 * washed-out, missing or repeated (frozen camera) frames are skipped.
 *
 * Files (cron_webcam_best.php writes, wx2 reads):
 *   CACHE_ROOT webcam_best_days.json  best cell of every scored day
 *   CACHE_ROOT webcam_best.json       current selection (12 calendar months, past month, past year)
 *   CAM_ROOT camchive/thumbs/best/    600x400 thumbnails of the selected frames (served by /camchive/)
 * Hand overrides: config/webcam_best_pins.php (pins win over the automatic choice; excludes are never picked).
 * Until the cron has run, config/webcam_best_seed.php (a saved selection, cron --write-seed) is shown.
 */
class WebcamBest {
	const HIK_START = '2018-06-21';  // first summary sheet built from the Hikvision camera
	const COLS = 6;
	const ROWS = 8;
	const GAP_X = 4;
	const GAP_Y = 18;               // caption strip under each row of cells
	const THUMB_W = 600;
	const THUMB_H = 400;
	const ARCHIVE_HOST = 'https://cam.nw3weather.co.uk/';
	const PAST_MONTH_DAYS = 30;
	const PAST_YEAR_DAYS = 365;

	public static function daysFile() { return CACHE_ROOT . 'webcam_best_days.json'; }
	public static function selectionFile() { return CACHE_ROOT . 'webcam_best.json'; }
	public static function seedFile() { return ROOT . 'config/webcam_best_seed.php'; }
	public static function thumbDir() { return Site::CAM_ROOT . 'camchive/thumbs/best/'; }

	/** Summary sheet path for a day (relative to ROOT), or null. */
	public static function sheetRel($dayTs) {
		$rel = date('Y/Ymd', $dayTs) . 'dailywebcam.jpg';
		return is_file(ROOT . $rel) ? $rel : null;
	}

	/** Public URL path of an archived frame. */
	public static function frameUrl($ymd, $hm, $cam = 'hik') {
		return '/camchive/' . $cam . '/' . str_replace('-', '/', $ymd) . '/' . $hm . $cam . '.jpg';
	}

	public static function thumbName($ymd, $hm, $cam = 'hik') {
		return str_replace('-', '', $ymd) . '_' . $hm . $cam . '.jpg';
	}

	// ---------------------------------------------------------------- daily data signals

	private static $signals = null;
	/** Optional stream for a per-cell feature dump (cron --dump=file), for tuning the scoring offline. */
	public static $dump = null;

	/** Y-m-d => [snow, lysnw, tmin, fog, sunhr] from the daily data arrays. */
	public static function daySignals() {
		if (self::$signals !== null) { return self::$signals; }
		$out = array();
		foreach (array('snow', 'lysnw', 'tmin', 'fog', 'sunhr') as $var) {
			$arr = Data::varToDatArray($var);
			if (!is_array($arr)) { continue; }
			foreach ($arr as $y => $months) {
				if ((int)$y < 2018) { continue; }
				foreach ($months as $m => $days) {
					foreach ($days as $d => $v) {
						if ($v === '' || $v === null || $v === '-') { continue; }
						$out[sprintf('%04d-%02d-%02d', $y, $m, $d)][$var] = (float)$v;
					}
				}
			}
		}
		return self::$signals = $out;
	}

	// ---------------------------------------------------------------- image scoring

	/** Features of one sheet cell, or null if the cell is a missing frame (sheet background). */
	public static function cellFeatures($im, $x0, $y0, $w, $h) {
		$step = 2;
		$n = 0; $sumY = 0; $sumY2 = 0; $bg = 0;
		$sumRg = 0; $sumRg2 = 0; $sumYb = 0; $sumYb2 = 0;
		$skyN = 0; $warm = 0; $blue = 0; $sat = 0;
		$skyY = 0; $skyY2 = 0; $grad = 0; $gradN = 0;
		$skyRows = (int)($h * 0.6);    // the skyline sits at about 80% of the frame height
		for ($y = 0; $y < $h; $y += $step) {
			$lastY = null;
			for ($x = 0; $x < $w; $x += $step) {
				$c = imagecolorat($im, $x0 + $x, $y0 + $y);
				$r = ($c >> 16) & 255; $g = ($c >> 8) & 255; $b = $c & 255;
				if (abs($r - 223) < 6 && abs($g - 255) < 6 && abs($b - 185) < 6) { $bg++; }
				$Y = 0.299 * $r + 0.587 * $g + 0.114 * $b;
				$rg = $r - $g; $yb = 0.5 * ($r + $g) - $b;
				$n++; $sumY += $Y; $sumY2 += $Y * $Y;
				$sumRg += $rg; $sumRg2 += $rg * $rg; $sumYb += $yb; $sumYb2 += $yb * $yb;
				$mx = max($r, $g, $b); $mn = min($r, $g, $b);
				$s = $mx > 0 ? ($mx - $mn) / $mx : 0;
				$sat += $s;
				if ($y < $skyRows) {
					$skyN++; $skyY += $Y; $skyY2 += $Y * $Y;
					if ($lastY !== null) { $grad += abs($Y - $lastY); $gradN++; }
					$lastY = $Y;
					if ($r > $b + 18 && $r >= $g && $s > 0.18 && $mx > 70) { $warm++; }
					if ($b > $r + 25 && $s > 0.25 && $mx > 90) { $blue++; }
				}
			}
		}
		if ($n === 0 || $bg > $n * 0.4) { return null; }
		$mY = $sumY / $n;
		$sdY = sqrt(max(0, $sumY2 / $n - $mY * $mY));
		$mRg = $sumRg / $n; $mYb = $sumYb / $n;
		$sdRg = sqrt(max(0, $sumRg2 / $n - $mRg * $mRg));
		$sdYb = sqrt(max(0, $sumYb2 / $n - $mYb * $mYb));
		$colour = sqrt($sdRg * $sdRg + $sdYb * $sdYb) + 0.3 * sqrt($mRg * $mRg + $mYb * $mYb);
		$mSky = $skyN ? $skyY / $skyN : 0;
		$sdSky = $skyN ? sqrt(max(0, $skyY2 / $skyN - $mSky * $mSky)) : 0;
		return array(
			'lum' => round($mY, 1), 'con' => round($sdY, 1), 'col' => round($colour, 1),
			'warm' => round($skyN ? $warm / $skyN : 0, 3), 'blue' => round($skyN ? $blue / $skyN : 0, 3),
			'sat' => round($sat / $n, 3),
			'sky' => round($sdSky, 1), 'grad' => round($gradN ? $grad / $gradN : 0, 2),
		);
	}

	/** Sunrise and sunset timestamps for a day. */
	private static function sunTimes($dayTs) {
		$noon = $dayTs + 12 * 3600;
		$rise = nw3_sunrise($noon, 0, Site::LATITUDE, Site::LONGITUDE, Site::ZENITH, date('I', $noon));
		$set = nw3_sunset($noon, 0, Site::LATITUDE, Site::LONGITUDE, Site::ZENITH, date('I', $noon));
		return array($rise, $set);
	}

	/**
	 * Score one cell. Returns [score, tags] or null when the frame is unusable.
	 * $sig: that day's daily data signals.
	 */
	public static function scoreCell($f, $ts, $rise, $set, $sig) {
		if ($ts < $rise - 40 * 60 || $ts > $set + 40 * 60) { return null; }     // night
		if ($f['lum'] < 38 || $f['lum'] > 238 || $f['con'] < 5) { return null; } // dark, washed out or blank
		$fromSun = min(abs($ts - $rise), abs($ts - $set)) / 60;
		$golden = $fromSun <= 50 ? 1.0 : max(0.0, 1 - ($fromSun - 50) / 60);
		// Sky interest: warm light near sunrise/sunset, then cloud texture and contrast; a plain blue sky is
		// colourful but dull, so blue only scores when the sky also has structure.
		$texture = min($f['sky'], 40) / 40 * 0.5 + min($f['grad'], 10) / 10 * 0.5;
		$score = 45 * min(1, $f['warm'] * 2.5) * (0.35 + 0.65 * $golden);
		$score += 20 * $texture;
		$score += 10 * min($f['col'], 60) / 60;
		$score += 6 * min($f['con'], 55) / 55;
		$score += 6 * $golden;
		$score -= 18 * max(0, $f['blue'] - 0.45) * (1 - $texture);
		if ($f['lum'] < 70) { $score -= (70 - $f['lum']) * 0.5; }
		$tags = array();
		$hr = (int)date('G', $ts);
		$lying = isset($sig['lysnw']) ? $sig['lysnw'] : 0;
		$falling = isset($sig['snow']) ? $sig['snow'] : 0;
		// lying snow is the 09:00 depth, so it counts in full until early afternoon, then half (it may have melted)
		if ($lying >= 1 && $f['lum'] >= 80) {
			$score += (14 + min($lying, 10)) * ($hr < 13 ? 1 : 0.5);
			$tags[] = 'lying';
		} elseif ($falling > 0 && $f['lum'] >= 70) {
			$score += 8;
			$tags[] = 'falling';
		}
		if (isset($sig['tmin']) && $sig['tmin'] <= -1 && $hr < 11) { $score += 6; $tags[] = 'frost'; }
		if (!empty($sig['fog']) && $hr < 12 && $f['lum'] > 70) { $score += 6; $tags[] = 'fog'; }
		if ($golden >= 1) { $tags[] = ($ts < $rise + 3 * 3600) ? 'sunrise' : 'sunset'; }
		return array(round($score, 2), $tags);
	}

	/**
	 * Score a day's sheet: best usable cell (with its runners-up in 'alts'), or null. Hand excludes are applied
	 * when selecting, not here, so editing them never needs a rescore.
	 */
	public static function scoreDay($dayTs, $sig = array()) {
		$rel = self::sheetRel($dayTs);
		if ($rel === null) { return null; }
		$data = @file_get_contents(ROOT . $rel);
		$im = $data === false ? false : @imagecreatefromstring($data);
		if (!$im) { return null; }
		$W = imagesx($im); $H = imagesy($im);
		$cw = ($W - (self::COLS - 1) * self::GAP_X) / self::COLS;
		$ch = ($H - self::ROWS * self::GAP_Y) / self::ROWS;
		if ($cw < 60 || $ch < 40) { imagedestroy($im); return null; }
		list($rise, $set) = self::sunTimes($dayTs);
		$ymd = date('Y-m-d', $dayTs);
		$cands = array(); $prev = null;
		for ($i = 0; $i < self::COLS * self::ROWS; $i++) {
			$hm = sprintf('%02d%02d', intdiv($i, 2), ($i % 2) * 30);
			$ts = mktime(intdiv($i, 2), ($i % 2) * 30, 0, (int)date('n', $dayTs), (int)date('j', $dayTs), (int)date('Y', $dayTs));
			$x0 = (int)round(($i % self::COLS) * ($cw + self::GAP_X)) + 2;
			$y0 = (int)round(intdiv($i, self::COLS) * ($ch + self::GAP_Y)) + 2;
			$f = self::cellFeatures($im, $x0, $y0, (int)$cw - 4, (int)$ch - 4);
			if ($f === null) { $prev = null; continue; }
			// a frozen camera repeats the same frame: skip exact repeats
			if ($prev !== null && $prev['lum'] == $f['lum'] && $prev['col'] == $f['col'] && $prev['con'] == $f['con']) { continue; }
			$prev = $f;
			if (self::$dump) { fwrite(self::$dump, json_encode(array($ymd, $hm, $ts - $rise, $set - $ts, $f, $sig)) . "\n"); }
			$s = self::scoreCell($f, $ts, $rise, $set, $sig);
			if ($s === null) { continue; }
			$cands[] = array('date' => $ymd, 'hm' => $hm, 'score' => $s[0], 'tags' => $s[1], 'f' => $f);
		}
		imagedestroy($im);
		if (!$cands) { return null; }
		usort($cands, function($a, $b) { return $b['score'] < $a['score'] ? -1 : ($b['score'] > $a['score'] ? 1 : 0); });
		$best = $cands[0];
		// runners-up, so a hand-excluded frame falls back to the next best of that day without a rescore
		$best['alts'] = array();
		foreach (array_slice($cands, 1, 3) as $c) { $best['alts'][] = array('hm' => $c['hm'], 'score' => $c['score'], 'tags' => $c['tags']); }
		return $best;
	}

	// ---------------------------------------------------------------- pins, captions, selection

	public static function pins() {
		$file = ROOT . 'config/webcam_best_pins.php';
		$p = is_file($file) ? include $file : array();
		$p = is_array($p) ? $p : array();
		$p += array('months' => array(), 'past_month' => null, 'past_year' => null, 'exclude' => array());
		$ex = array('frames' => array(), 'ranges' => array());
		foreach ((array)$p['exclude'] as $e) {
			$e = str_replace(':', '', trim($e));
			if (strpos($e, '..') !== false) {
				list($a, $b) = array_map('trim', explode('..', $e, 2));
				$ex['ranges'][] = array($a, $b);
			} else {
				$ex['frames'][$e] = true;
			}
		}
		$p['exclude'] = $ex;
		return $p;
	}

	/** True if a frame / day is excluded: "Y-m-d HHMM", "Y-m-d", or a day range "Y-m-d..Y-m-d". */
	public static function isExcluded($ex, $ymd, $hm) {
		if (empty($ex)) { return false; }
		if (isset($ex['frames'][$ymd]) || isset($ex['frames']["$ymd $hm"])) { return true; }
		foreach ($ex['ranges'] as $r) {
			if ($ymd >= $r[0] && $ymd <= $r[1]) { return true; }
		}
		return false;
	}

	/** Parse a pin: ['2026-11-14 07:30', 'caption', 'cam'] => entry, or null. */
	public static function pinEntry($pin) {
		if (!is_array($pin) || empty($pin[0])) { return null; }
		if (!preg_match('/^(\d{4}-\d\d-\d\d)[ T](\d\d):?(\d\d)$/', trim($pin[0]), $m)) { return null; }
		return array(
			'date' => $m[1], 'hm' => $m[2] . $m[3], 'cam' => isset($pin[2]) ? $pin[2] : 'hik',
			'caption' => isset($pin[1]) ? $pin[1] : '', 'pinned' => true,
		);
	}

	/** Short automatic caption from the scoring tags and daily data. */
	public static function autoCaption($day, $sig) {
		$tags = isset($day['tags']) ? $day['tags'] : array();
		$when = '';
		if (in_array('sunrise', $tags)) { $when = 'sunrise'; }
		elseif (in_array('sunset', $tags)) { $when = 'sunset'; }
		$deg = "\xC2\xB0C";
		if (in_array('lying', $tags)) {
			$cm = isset($sig['lysnw']) ? $sig['lysnw'] : 0;
			$t = $when ? 'Snowy ' . $when : 'Snow';
			return $t . ', ' . Util::roundToDp($cm, 0) . ' cm lying';
		}
		if (in_array('falling', $tags)) { return $when ? 'Falling snow at ' . $when : 'Falling snow'; }
		if (in_array('fog', $tags)) { return $when ? 'Foggy ' . $when : 'Fog'; }
		if (in_array('frost', $tags)) {
			return ($when ? 'Frosty ' . $when : 'Frosty morning') . ', ' . str_replace('-', "\xE2\x88\x92", Util::roundToDp($sig['tmin'], 1)) . ' ' . $deg;
		}
		$f = isset($day['f']) ? $day['f'] : array();
		if ($when) { return ucfirst($when); }
		if (!empty($f['blue']) && $f['blue'] > 0.35) { return 'Blue sky'; }
		return 'Dramatic sky';
	}

	/**
	 * Build the selection from scored days. $days: Y-m-d => day best. $today: Y-m-d (exclusive window end).
	 * The past month / past year slots keep a short ranked list (one frame per day), so the page can skip a frame
	 * that is already on show as a calendar-month pick. Pins are applied at display time (gallery()), so a pin
	 * edit shows without re-running the cron.
	 */
	public static function select($days, $today, $sig, $ex) {
		$todayTs = strtotime($today . ' 00:00');
		$winM = date('Y-m-d', strtotime('-' . self::PAST_MONTH_DAYS . ' days', $todayTs));
		$winY = date('Y-m-d', strtotime('-' . self::PAST_YEAR_DAYS . ' days', $todayTs));
		$months = array(); $pm = array(); $py = array();
		foreach ($days as $ymd => $d) {
			if (!$d || $ymd >= $today) { continue; }
			if (self::isExcluded($ex, $ymd, $d['hm'])) {
				$alt = null;
				foreach (isset($d['alts']) ? $d['alts'] : array() as $a) {
					if (!self::isExcluded($ex, $ymd, $a['hm'])) { $alt = $a; break; }
				}
				if (!$alt) { continue; }
				$d = array('date' => $ymd, 'hm' => $alt['hm'], 'score' => $alt['score'], 'tags' => $alt['tags']);
			}
			$mo = (int)substr($ymd, 5, 2);
			if (!isset($months[$mo]) || $d['score'] > $months[$mo]['score']) { $months[$mo] = $d; }
			if ($ymd >= $winM) { $pm[] = $d; }
			if ($ymd >= $winY) { $py[] = $d; }
		}
		$fmt = function($d) use ($sig) {
			$s = isset($sig[$d['date']]) ? $sig[$d['date']] : array();
			return array('date' => $d['date'], 'hm' => $d['hm'], 'cam' => 'hik', 'score' => $d['score'],
				'caption' => self::autoCaption($d, $s), 'pinned' => false);
		};
		$top = function($list) use ($fmt) {
			usort($list, function($a, $b) { return $b['score'] < $a['score'] ? -1 : ($b['score'] > $a['score'] ? 1 : 0); });
			return array_map($fmt, array_slice($list, 0, 15));
		};
		ksort($months);
		$out = array('built' => date('c'), 'today' => $today, 'months' => array(),
			'past_month' => $top($pm), 'past_year' => $top($py));
		foreach ($months as $mo => $d) { $out['months'][$mo] = $fmt($d); }
		return $out;
	}

	/** Selection for display: cron output (or the seed), then pins on top. Keys past_month, past_year, 1..12. */
	public static function gallery() {
		$sel = is_file(self::selectionFile()) ? json_decode(file_get_contents(self::selectionFile()), true) : null;
		if (!is_array($sel) && is_file(self::seedFile())) { $sel = include self::seedFile(); }
		if (!is_array($sel)) { $sel = array(); }
		$sel += array('months' => array(), 'past_month' => array(), 'past_year' => array());
		$pins = self::pins();
		$ex = $pins['exclude'];
		$today = date('Y-m-d');
		$todayTs = strtotime($today . ' 00:00');
		$from = array(
			'past_month' => date('Y-m-d', strtotime('-' . self::PAST_MONTH_DAYS . ' days', $todayTs)),
			'past_year' => date('Y-m-d', strtotime('-' . self::PAST_YEAR_DAYS . ' days', $todayTs)),
		);

		$months = array(); $shown = array();
		for ($mo = 1; $mo <= 12; $mo++) {
			$e = isset($sel['months'][$mo]) ? $sel['months'][$mo] : null;
			$p = isset($pins['months'][$mo]) ? self::pinEntry($pins['months'][$mo]) : null;
			if ($p && (int)substr($p['date'], 5, 2) === $mo) { $e = $p; }
			elseif ($e && self::isExcluded($ex, $e['date'], $e['hm'])) { $e = null; }
			if ($e) { $shown[$e['date'] . ' ' . $e['hm']] = true; }
			$months[$mo] = $e;
		}
		$slots = array();
		foreach ($from as $k => $start) {
			$e = null;
			$p = self::pinEntry($pins[$k]);
			if ($p && $p['date'] >= $start && $p['date'] < $today) {
				$e = $p;   // a pin lapses once its frame leaves the window
			} else {
				$list = $sel[$k];
				if (isset($list['date'])) { $list = array($list); }   // single entry
				foreach ((array)$list as $c) {
					if (!$c || $c['date'] < $start || $c['date'] >= $today) { continue; }
					if (self::isExcluded($ex, $c['date'], $c['hm']) || isset($shown[$c['date'] . ' ' . $c['hm']])) { continue; }
					$e = $c; break;
				}
			}
			if ($e) { $shown[$e['date'] . ' ' . $e['hm']] = true; }
			$slots[$k] = $e;
		}
		$all = $slots + $months;
		foreach ($all as $k => $e) {
			if (!$e) { continue; }
			$cam = isset($e['cam']) ? $e['cam'] : 'hik';
			$e['src'] = self::frameUrl($e['date'], $e['hm'], $cam);
			$tn = self::thumbName($e['date'], $e['hm'], $cam);
			$e['thumb'] = is_file(self::thumbDir() . $tn) ? '/camchive/thumbs/best/' . $tn : $e['src'];
			$e['ts'] = strtotime($e['date'] . ' ' . substr($e['hm'], 0, 2) . ':' . substr($e['hm'], 2));
			$all[$k] = $e;
		}
		return $all;
	}

	/** Make (or keep) the 600x400 thumbnail for a frame. Local archive first, else the public archive host. */
	public static function makeThumb($ymd, $hm, $cam = 'hik') {
		$dir = self::thumbDir();
		if (!is_dir($dir) && !@mkdir($dir, 0775, true)) { return false; }
		$dst = $dir . self::thumbName($ymd, $hm, $cam);
		if (is_file($dst) && filesize($dst) > 1024) { return true; }
		$rel = ltrim(self::frameUrl($ymd, $hm, $cam), '/');
		$local = Site::CAM_ROOT . $rel;
		$data = is_file($local) ? @file_get_contents($local) : false;
		if ($data === false) {
			$ctx = stream_context_create(array('http' => array('timeout' => 20, 'user_agent' => 'nw3weather-cron')));
			$data = @file_get_contents(self::ARCHIVE_HOST . $rel, false, $ctx);
		}
		$im = ($data === false || strlen($data) < 1024) ? false : @imagecreatefromstring($data);
		if (!$im) { return false; }
		$sw = imagesx($im); $sh = imagesy($im);
		$th = (int)round(self::THUMB_W * $sh / $sw);
		$t = imagecreatetruecolor(self::THUMB_W, $th);
		imagecopyresampled($t, $im, 0, 0, 0, 0, self::THUMB_W, $th, $sw, $sh);
		$tmp = $dst . '.temp';
		$ok = imagejpeg($t, $tmp, 78) && rename($tmp, $dst);
		imagedestroy($im); imagedestroy($t);
		if ($ok) { @chmod($dst, 0664); }
		return $ok;
	}
}
