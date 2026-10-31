<?php
/**
 * Webcam sunshine presentation for the home page sun card and the daily report: a sunrise-to-sunset strip of
 * sunny / not sunny / no data / not yet, built from SunLog's per-minute files (same layout as rwcweather's ribbon).
 */
class SunTracker {
	const LABELS = ['sun' => 'Sunny', 'nosun' => 'Not sunny', 'unknown' => 'No data', 'future' => 'Not yet'];

	// Same position and sunrise convention as sun_model/solar.py (NOAA, sun centre at -0.833 degrees), so the strip
	// covers exactly the minutes the model classifies.
	const LAT = 51.556;
	const LON = -0.177;
	const RISE_EL = -0.833;
	const CIVIL_EL = -6.0;

	/** Solar elevation (degrees) at a unix time: port of sun_model/solar.py solar_pos. */
	public static function elevation(float $unix): float {
		$jc = ($unix / 86400.0 + 2440587.5 - 2451545.0) / 36525.0;
		$gml = fmod(280.46646 + $jc * (36000.76983 + $jc * 0.0003032), 360);
		$gma = deg2rad(357.52911 + $jc * (35999.05029 - 0.0001537 * $jc));
		$ecc = 0.016708634 - $jc * (0.000042037 + 0.0000001267 * $jc);
		$ceq = sin($gma) * (1.914602 - $jc * (0.004817 + 0.000014 * $jc)) + sin(2 * $gma) * (0.019993 - 0.000101 * $jc) + sin(3 * $gma) * 0.000289;
		$om = deg2rad(125.04 - 1934.136 * $jc);
		$sal = deg2rad($gml + $ceq - 0.00569 - 0.00478 * sin($om));
		$oc = deg2rad(23 + (26 + (21.448 - $jc * (46.815 + $jc * (0.00059 - $jc * 0.001813))) / 60) / 60 + 0.00256 * cos($om));
		$dec = asin(sin($oc) * sin($sal));
		$y = tan($oc / 2) ** 2;
		$l = deg2rad($gml);
		$eot = 4 * rad2deg($y * sin(2 * $l) - 2 * $ecc * sin($gma) + 4 * $ecc * $y * sin($gma) * cos(2 * $l)
			- 0.5 * $y * $y * sin(4 * $l) - 1.25 * $ecc * $ecc * sin(2 * $gma));
		$mins = fmod($unix, 86400) / 60;
		$tst = fmod(fmod($mins + $eot + 4 * self::LON, 1440) + 1440, 1440);
		$ha = deg2rad($tst / 4 < 0 ? $tst / 4 + 180 : $tst / 4 - 180);
		$lat = deg2rad(self::LAT);
		$cz = sin($lat) * sin($dec) + cos($lat) * cos($dec) * cos($ha);
		return 90 - rad2deg(acos(max(-1.0, min(1.0, $cz))));
	}

	/** [rise, set] unix when the sun crosses $el during the local day containing $unix (either may be null). */
	public static function crossings(int $unix, float $el): array {
		$t = mktime(0, 0, 0, (int)date('n', $unix), (int)date('j', $unix), (int)date('Y', $unix));
		$end = mktime(0, 0, 0, (int)date('n', $unix), (int)date('j', $unix) + 1, (int)date('Y', $unix));
		$rise = $set = null;
		$prev = self::elevation($t) > $el;
		while ($t < $end) {
			$n = min($t + 600, $end);
			$cur = self::elevation($n) > $el;
			if ($cur !== $prev) {
				[$a, $b] = [$t, $n];
				for ($i = 0; $i < 12; $i++) {   // to < 1 s
					$m = ($a + $b) / 2;
					if ((self::elevation($m) > $el) === $cur) { $b = $m; } else { $a = $m; }
				}
				if ($cur && $rise === null) { $rise = (int)round(($a + $b) / 2); }
				if (!$cur) { $set = (int)round(($a + $b) / 2); }
			}
			$prev = $cur; $t = $n;
		}
		return [$rise, $set];
	}

	/** Sunrise and sunset (unix) for the local day containing $unix; null if the sun does not rise and set. */
	public static function riseSet(int $unix): ?array {
		static $memo = [];
		$k = date('Ymd', $unix);
		if (!array_key_exists($k, $memo)) {
			[$r, $s] = self::crossings($unix, self::RISE_EL);
			$memo[$k] = ($r !== null && $s !== null && $s > $r) ? [$r, $s] : null;
		}
		return $memo[$k];
	}

	/** 'twilight' (civil, sun 0 to -6 degrees) or 'dark' outside sunrise..sunset; null in daylight. */
	public static function nightPhase(int $now): ?string {
		$rs = self::riseSet($now);
		if ($rs !== null && $now >= $rs[0] && $now < $rs[1]) { return null; }
		$el = self::elevation($now);
		if ($el > self::RISE_EL) { return null; }   // polar day safety; never in London
		return $el > self::CIVIL_EL ? 'twilight' : 'dark';
	}

	/**
	 * Spells from sunrise to sunset for a local day (YYYYMMDD): list of [start, end, state] (unix, state is
	 * sun | nosun | unknown | future). $now (today only) splits elapsed from future time; minutes not yet
	 * classified within SunLog::MAX_AGE_S of now count as future rather than missing. Null if no sunlog for the day.
	 */
	public static function spells(string $ymd, ?int $now = null): ?array {
		$rows = SunLog::minutes($ymd);
		if (!$rows) { return null; }
		$day = mktime(12, 0, 0, (int)substr($ymd, 4, 2), (int)substr($ymd, 6, 2), (int)substr($ymd, 0, 4));
		$rs = self::riseSet($day) ?? [$rows[0]['t'], end($rows)['t'] + 60];
		[$rise, $set] = $rs;
		$map = array_column($rows, 'v', 't');
		$lastSeen = null;
		foreach ($rows as $r) {
			if ($r['v'] !== null && ($now === null || $r['t'] <= $now)) { $lastSeen = $r['t']; }
		}
		$end = $now === null ? $set : min(max($now, $rise), $set);
		$pendingFrom = ($now !== null && $lastSeen !== null && $now - $lastSeen <= SunLog::MAX_AGE_S) ? $lastSeen + 60 : $end;
		$out = [];
		for ($t = $rise - $rise % 60; $t < $set; $t += 60) {
			if ($t >= $end || $t >= $pendingFrom) {
				$state = 'future';
			} else {
				$v = $map[$t] ?? null;
				$state = $v === 1 ? 'sun' : ($v === 0 ? 'nosun' : 'unknown');
			}
			$a = max($t, $rise); $b = min($t + 60, $set);
			$k = count($out) - 1;
			if ($k >= 0 && $out[$k][2] === $state) {
				$out[$k][1] = $b;
			} else {
				$out[] = [$a, $b, $state];
			}
		}
		return ['sunrise' => $rise, 'sunset' => $set, 'spells' => $out, 'last' => $lastSeen,
			'last_v' => $lastSeen !== null ? $map[$lastSeen] : null];
	}

	/** The strip itself, with sunrise / sunset labels underneath. */
	public static function ribbon(array $day, string $height = '16px', bool $labels = true): string {
		$span = max(1, $day['sunset'] - $day['sunrise']);
		$h = '<div class="sun-ribbon"><div class="sun-ribbon-bar" style="height:' . htmlspecialchars($height) . '">';
		foreach ($day['spells'] as [$a, $b, $state]) {
			$h .= '<span class="sun-seg sun-seg-' . $state . '" style="flex-grow:' . round(($b - $a) / $span, 5) . '"'
				. ' title="' . self::LABELS[$state] . ': ' . date('H:i', $a) . ' &ndash; ' . date('H:i', $b) . '"></span>';
		}
		$h .= '</div>';
		if ($labels) {
			$h .= '<div class="sun-ribbon-lab"><span title="Sunrise">&uarr; ' . date('H:i', $day['sunrise']) . '</span>'
				. '<span title="Sunset">' . date('H:i', $day['sunset']) . ' &darr;</span></div>';
		}
		return $h . '</div>';
	}

	public static function legend(): string {
		$h = '<div class="sun-legend">';
		foreach (['sun', 'nosun', 'unknown'] as $s) {
			$h .= '<span><span class="sun-seg sun-seg-' . $s . '"></span>' . self::LABELS[$s] . '</span>';
		}
		return $h . '</div>';
	}

	public static function fmtHours(?float $h, bool $lowerBound = false, string $unit = 'hrs'): string {
		return $h === null ? '&ndash;' : number_format($h, 1) . ($lowerBound ? '+' : '') . ' ' . $unit;
	}

	/** Day length as "9h 46m". */
	public static function fmtLength(int $secs): string {
		$m = intdiv($secs + 30, 60);
		return intdiv($m, 60) . 'h ' . sprintf('%02d', $m % 60) . 'm';
	}

	/** Signed change in day length, e.g. "+1m 05s", "&minus;2m 14s", "+45s". */
	public static function fmtDelta(int $secs): string {
		$sign = $secs > 0 ? '+' : ($secs < 0 ? '&minus;' : '&plusmn;');
		$a = abs($secs); $m = intdiv($a, 60); $s = $a % 60;
		return $sign . ($m ? $m . 'm ' . sprintf('%02d', $s) . 's' : $s . 's');
	}

	/** Sunshine, possible sunshine and the % for a day (so far when $until is given), from the model. */
	public static function total(string $ymd, ?int $until = null): ?array {
		$tot = SunLog::dayTotal($ymd, $until);
		if ($tot === null) { return null; }
		$tot['pct'] = $tot['possible'] > 0 ? (int)round(100 * min($tot['hours'], $tot['possible']) / $tot['possible']) : null;
		return $tot;
	}

	/** Home page sun / moon card: today's webcam sunshine, "sunny now", today's strip, day length and the moon. */
	public static function renderCard(?int $now = null): void {
		$now ??= time();
		if (is_file(Site::$rareTags)) { include Site::$rareTags; } // moon values written by cron
		$today = date('Ymd', $now);
		$rs = self::riseSet($now);
		$night = self::nightPhase($now);
		$beforeRise = $rs !== null && $now < $rs[0];
		$afterSet = $rs !== null && $now >= $rs[1];

		// Main value is always today's total; before sunrise there is none yet (null)
		$ymd = $today;
		$tot = $beforeRise ? null : self::total($ymd, $now);
		$day = $beforeRise ? null : self::spells($ymd, $now);
		$yYmd = date('Ymd', mktime(12, 0, 0, (int)date('n', $now), (int)date('j', $now) - 1, (int)date('Y', $now)));
		$yTot = self::total($yYmd, null);

		// "Sunny now" only from a classified minute at most SunLog::MAX_AGE_S old
		$fresh = $day !== null && $day['last'] !== null && $now - $day['last'] <= SunLog::MAX_AGE_S;
		$badge = '';
		if ($night !== null) {
			$badge = $night === 'twilight' ? ['twilight', 'Twilight'] : ['dark', 'Dark'];
		} elseif ($fresh) {
			$badge = $day['last_v'] === 1 ? ['sun', 'Sunny now'] : ['nosun', 'Not sunny'];
		}
		$hoursTitle = 'Sunshine from the webcam' . ($beforeRise ? ', none yet today' : ($afterSet ? ', today' : ', today so far'));
		if ($tot !== null && $tot['partial']) { $hoursTitle .= '. Webcam data missing for part of the day, so this is a lower bound'; }
		elseif ($tot !== null && $tot['scaled']) { $hoursTitle .= '. Includes estimates for short webcam gaps'; }

		$sub = '';
		if ($tot !== null) {
			$sub = '<b>' . ($tot['pct'] !== null ? $tot['pct'] . '%' : '&ndash;') . '</b> of '
				. self::fmtHours($tot['possible'], false, 'h') . ' possible';
		} elseif ($beforeRise) {
			$sub = 'Sunrise ' . date('H:i', $rs[0]);
		} elseif ($day !== null) {
			$sub = 'Not enough webcam data';
		} else {
			$sub = 'No webcam data yet';
		}
		// The newest classified minute is too old for a "now" (classifier or camera behind)
		if ($night === null && $day !== null && !$fresh) {
			$sub .= '<br /><span class="sun-stale">Webcam data delayed' . ($day['last'] !== null
				? ' (last ' . date('H:i', $day['last']) . ')' : '') . '</span>';
		}

		echo '<div class="wx-card wx-card-sun" id="sun-card">';
		echo '<div class="wx-card-head"><img class="wx-card-icon" src="' . Site::IMG_ROOT . 'clear.png" alt="" width="36" height="36" />';
		$moonName = isset($moonphasename) ? trim($moonphasename) : 'Moon phase';
		$moonImg = self::moonImg($moonage ?? null);
		if ($moonImg !== null) {
			echo '<img class="wx-card-icon wx-card-moon" src="' . Site::IMG_ROOT . $moonImg . '" alt="' . htmlspecialchars($moonName)
				. '" title="' . htmlspecialchars($moonName) . '" width="36" height="36" />';
		}
		echo '<a class="hidden-link" href="wx6.php" title="Sun, moon and astronomy detail">Sun &amp; Moon</a></div>';
		echo '<div class="wx-card-now"><span title="' . $hoursTitle . '">'
			. self::fmtHours($tot['hours'] ?? null, (bool)($tot['partial'] ?? false)) . '</span>'
			. ($badge ? ' <span class="sun-badge sun-badge-' . $badge[0] . '">' . $badge[1] . '</span>' : '') . '</div>';
		echo '<div class="wx-card-sub">' . $sub . '</div>';
		if ($day !== null && !$beforeRise) {
			echo '<div class="sun-card-ribbon">' . self::ribbon($day) . '</div>';
		} elseif ($rs !== null) {
			echo '<div class="sun-card-ribbon"><div class="sun-ribbon-lab sun-ribbon-lab-only"><span title="Sunrise">&uarr; '
				. date('H:i', $rs[0]) . '</span><span title="Sunset">' . date('H:i', $rs[1]) . ' &darr;</span></div></div>';
		}
		$rows = [];
		if ($rs !== null) {
			$y = self::riseSet($now - 86400);
			$delta = $y !== null ? ' <span class="wx-when" title="Change in day length since yesterday">('
				. self::fmtDelta(($rs[1] - $rs[0]) - ($y[1] - $y[0])) . ')</span>' : '';
			$rows[] = ['Daylight', '<b>' . self::fmtLength($rs[1] - $rs[0]) . '</b>' . $delta];
		}
		$rows[] = ['<span title="Sunshine from the webcam, yesterday">Yesterday</span>', '<b>' . self::fmtHours($yTot['hours'] ?? null, (bool)($yTot['partial'] ?? false)) . '</b>'
			. ($yTot !== null && $yTot['pct'] !== null ? ' <span class="wx-when">(' . $yTot['pct'] . '%)</span>' : '')];
		$rows[] = ['<span title="' . htmlspecialchars($moonName) . '">Moon</span>', '<b>' . ($moonphase ?? '&ndash;') . '</b> lit'
			. (($wax = self::moonWaxing($moonage ?? null)) !== null ? ', ' . ($wax ? 'waxing' : 'waning') : '')];
		$rows[] = ['Moonrise/set', '<b>' . ($moonrise ?? '&ndash;') . '</b> / <b>' . ($moonset ?? '&ndash;') . '</b>'];
		echo '<div class="wx-card-rows">';
		foreach ($rows as $r) { echo '<div><span class="k">' . $r[0] . '</span><span class="v">' . $r[1] . '</span></div>'; }
		echo '</div></div>';
	}

	/** Waxing (true) or waning (false) from rareTags' moon age (synodic month 29.53 days); null if unknown. */
	public static function moonWaxing(?string $moonage): ?bool {
		if ($moonage === null || !preg_match('/(\d+(?:\.\d+)?)\s*day/', $moonage, $m)) { return null; }
		$age = (float)$m[1] + (preg_match('/(\d+)\s*hour/', $moonage, $h) ? (int)$h[1] / 24 : 0);
		return $age < 29.53 / 2;
	}

	/** Moon-phase icon from rareTags' "Moon age: 12 days,..." (age-based, as wx6.php). */
	public static function moonImg(?string $moonage): ?string {
		if ($moonage === null || !preg_match('/(\d+(?:\.\d+)?)\s*day/', $moonage, $m)) { return null; }
		$age = (float)$m[1];
		foreach ([2, 4, 6, 9, 11, 13, 16, 18, 21, 23, 26, 28] as $i => $threshold) {
			if ($age < $threshold) { return 'moon' . ($i + 1) . '.jpg'; }
		}
		return 'moon13.jpg';
	}

	/**
	 * Daily report section: the model's total and the day's strip with a legend. Nothing when the day has no sunlog.
	 * $datmHours is the day's recorded sunhr (may be hand-edited), shown when it differs from the model.
	 */
	public static function renderReport(string $ymd, bool $isToday, $datmHours = null, ?int $now = null): void {
		$now = $isToday ? ($now ?? time()) : null;
		$day = self::spells($ymd, $now);
		if ($day === null) { return; }
		$tot = self::total($ymd, $now);
		echo '<h2>Sunshine Tracker</h2><div class="sun-report">';
		echo '<p>';
		if ($tot !== null) {
			echo '<b>' . self::fmtHours($tot['hours'], $tot['partial']) . '</b>' . ($isToday ? ' so far' : '') . ' of '
				. self::fmtHours($tot['possible']) . ' possible' . ($tot['pct'] !== null ? ' (' . $tot['pct'] . '%)' : '');
			$gap = 0;
			foreach ($day['spells'] as [$a, $b, $st]) { if ($st === 'unknown') { $gap += $b - $a; } }
			if ($tot['partial']) { echo ' &middot; lower bound, webcam data missing for part of the day'; }
			elseif ($tot['scaled']) { echo ' &middot; estimated for short gaps'; }
			elseif ($gap >= 600) { echo ' &middot; no webcam data for ' . self::fmtLength($gap); }
			if (nw3_is_numeric($datmHours) && abs((float)$datmHours - $tot['hours']) >= 0.05) {
				echo ' &middot; Sun Hours above: ' . self::fmtHours((float)$datmHours);
			}
		} else {
			echo 'Not enough webcam data to give a total for this day';
		}
		echo '</p>';
		echo self::ribbon($day, '22px');
		echo self::legend();
		echo '<p class="sun-report-note">Classified minute by minute from the webcam, sunrise to sunset. Hover for times.</p>';
		echo '</div>';
	}
}
