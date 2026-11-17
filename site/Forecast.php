<?php
/**
 * Local forecast helpers for the homepage strip and wx5 page.
 * Data is cached by cron_main.php under V5_CACHE_ROOT (Yr.no location 2-2647553).
 */
class Forecast {

	private static $data = null;

	/** Load (and memoize) the cached forecast JSON. */
	public static function load() {
		if (self::$data !== null) { return self::$data; }
		$path = V5_CACHE_ROOT . 'forecast_v5.json';
		if (!is_file($path)) {
			self::$data = array();
			return self::$data;
		}
		$json = json_decode(file_get_contents($path), true);
		self::$data = is_array($json) ? $json : array();
		return self::$data;
	}

	/** First N daily summaries (homepage uses 2). */
	public static function days($limit = null) {
		$data = self::load();
		$days = isset($data['days']) && is_array($data['days']) ? $data['days'] : array();
		if ($limit !== null) { $days = array_slice($days, 0, (int)$limit); }
		return $days;
	}

	public static function hourly() {
		$data = self::load();
		return isset($data['hourly']) && is_array($data['hourly']) ? $data['hourly'] : array();
	}

	public static function periods() {
		$data = self::load();
		return isset($data['periods']) && is_array($data['periods']) ? $data['periods'] : array();
	}

	public static function updated() {
		$data = self::load();
		return isset($data['updated']) ? (int)$data['updated'] : 0;
	}

	/**
	 * Prefer Yr.no (same source as the wx5 tables) so hourly temps match the
	 * daily / 6-hour views. Yr shortIntervals cover ~2.5 days; later hours are
	 * stitched from 6-hour longIntervals (no POP). Fall back to the Open-Meteo
	 * 10-day hourly cache if Yr is unavailable.
	 */
	private static function meteogramHours() {
		$hourly = self::hourly();
		if ($hourly) {
			$hours = $hourly;
			$lastT = 0;
			foreach ($hourly as $iv) {
				if (!empty($iv['t']) && (int)$iv['t'] > $lastT) {
					$lastT = (int)$iv['t'];
				}
			}
			foreach (self::periods() as $p) {
				if (empty($p['t']) || (int)$p['t'] <= $lastT) { continue; }
				$start = (int)$p['t'];
				$range = (!empty($p['end']) && (int)$p['end'] > $start)
					? ((int)$p['end'] - $start)
					: (6 * 3600 * 1000);
				$temp = isset($p['temp']) ? $p['temp'] : null;
				$hours[] = array(
					't' => $start,
					'temp' => $temp,
					'dew' => isset($p['dew']) ? $p['dew'] : null,
					'precip' => isset($p['precip']) ? $p['precip'] : null,
					'precipRange' => $range,
					'cloud' => isset($p['cloud']) ? $p['cloud'] : null,
					'wind' => isset($p['wind']) ? $p['wind'] : null,
					'gust' => isset($p['gust']) ? $p['gust'] : null,
				);
			}
			// Densify the 6h tail to hourly with monotone interpolation so the
			// line stays smooth (no min/max sawtooth). Daily table extremes
			// are shown separately as a range band.
			$hours = self::interpolateSixHourTail($hours, $lastT);
			$hours = self::mergeOpenMeteoGaps($hours);
			return array($hours, self::updated(), 'yr', self::meteogramIcons(self::periods()));
		}
		$path = V5_CACHE_ROOT . 'forecast_meteogram.json';
		if (is_file($path)) {
			$json = json_decode(file_get_contents($path), true);
			if (is_array($json) && !empty($json['hourly']) && is_array($json['hourly'])) {
				$omHours = $json['hourly'];
				return array(
					$omHours,
					isset($json['updated']) ? (int)$json['updated'] : 0,
					'open-meteo',
					self::meteogramIcons(array(), $omHours)
				);
			}
		}
		return array(array(), 0, 'yr', array());
	}

	/** 6-hour weather icons from Yr longIntervals (same set as the period table). */
	private static function iconsFromPeriods($periods) {
		$out = array();
		$img = Site::IMG_ROOT;
		$step = 6 * 3600 * 1000;
		foreach ($periods as $p) {
			if (empty($p['t'])) { continue; }
			$start = (int)$p['t'];
			$end = !empty($p['end']) ? (int)$p['end'] : ($start + $step);
			$icon = isset($p['icon']) ? $p['icon'] : 'cloudy';
			$slot = self::londonSlotStart((int)(($start + $end) / 2), 6);
			$t = $slot + (int)($step / 2);
			// One chip per 6h clock slot so a leftover first period cannot sit
			// 5h from the next mid-point and overlap its neighbour.
			$out[$slot] = array(
				't' => $t,
				'start' => $start,
				'end' => $end,
				'icon' => $img . $icon . '_lg.png',
				'desc' => isset($p['desc']) ? $p['desc'] : '',
			);
		}
		ksort($out);
		return array_values($out);
	}

	/**
	 * 6-hour icons from hourly points that already carry an icon (Open-Meteo
	 * fallback, once cron has stored weather-code mappings).
	 */
	private static function iconsFromHours($hours) {
		$buckets = array();
		$img = Site::IMG_ROOT;
		$step = 6 * 3600 * 1000;
		foreach ($hours as $iv) {
			if (empty($iv['t']) || empty($iv['icon'])) { continue; }
			$t = (int)$iv['t'];
			$key = (int)(floor($t / $step) * $step);
			if (!isset($buckets[$key])) {
				$buckets[$key] = array('icon' => $iv['icon'], 'desc' => isset($iv['desc']) ? $iv['desc'] : '', 'n' => 1);
			} else {
				$buckets[$key]['n']++;
				// Prefer a non-generic icon if the mid-slot one was cloudy/clear.
			}
		}
		if (!$buckets) { return array(); }
		$out = array();
		foreach ($buckets as $start => $b) {
			$out[] = array(
				't' => $start + (int)($step / 2),
				'start' => $start,
				'end' => $start + $step,
				'icon' => $img . $b['icon'] . '_lg.png',
				'desc' => $b['desc'],
			);
		}
		return $out;
	}

	/** Prefer more significant weather when collapsing hours into a period icon. */
	private static function iconRank($icon) {
		$order = array(
			'tstorms_showers' => 90,
			'tstorms' => 85,
			'snow_showers' => 80,
			'snow' => 75,
			'rain_showers' => 70,
			'rain' => 65,
			'fog' => 40,
			'partlycloudy' => 30,
			'cloudy' => 20,
			'clear' => 10,
		);
		return isset($order[$icon]) ? $order[$icon] : 0;
	}

	/** London-local 3-hour slot start (ms) for a UTC epoch-ms timestamp. */
	private static function londonSlotStart($tMs, $hours = 3) {
		$dt = new DateTime('@' . (int)($tMs / 1000));
		$dt->setTimezone(new DateTimeZone('Europe/London'));
		$hour = (int)$dt->format('G');
		$step = max(1, (int)$hours);
		$dt->setTime($hour - ($hour % $step), 0, 0);
		return $dt->getTimestamp() * 1000;
	}

	/** Open-Meteo hourly cache, keyed by timestamp (ms). Units already mph / °C. */
	private static function openMeteoHoursByT() {
		static $map = null;
		if ($map !== null) { return $map; }
		$map = array();
		$path = V5_CACHE_ROOT . 'forecast_meteogram.json';
		if (!is_file($path)) { return $map; }
		$json = json_decode(file_get_contents($path), true);
		if (!is_array($json) || empty($json['hourly']) || !is_array($json['hourly'])) {
			return $map;
		}
		foreach ($json['hourly'] as $iv) {
			if (!empty($iv['t'])) { $map[(int)$iv['t']] = $iv; }
		}
		return $map;
	}

	/**
	 * Fill missing POP / gust from Open-Meteo where Yr (or interpolation) has none.
	 * Yr's public location forecast currently has no probability or gust fields.
	 */
	private static function mergeOpenMeteoGaps($hours) {
		$om = self::openMeteoHoursByT();
		if (!$om) { return $hours; }
		foreach ($hours as &$iv) {
			if (empty($iv['t'])) { continue; }
			$t = (int)$iv['t'];
			$src = isset($om[$t]) ? $om[$t] : null;
			if (!$src) { continue; }
			if ((!isset($iv['pop']) || $iv['pop'] === null) && isset($src['pop']) && $src['pop'] !== null) {
				$iv['pop'] = $src['pop'];
			}
			if ((!isset($iv['gust']) || $iv['gust'] === null) && isset($src['gust']) && $src['gust'] !== null) {
				$iv['gust'] = $src['gust'];
			}
			if ((!isset($iv['windDir']) || $iv['windDir'] === null) && isset($src['windDir']) && $src['windDir'] !== null) {
				$iv['windDir'] = $src['windDir'];
			}
		}
		unset($iv);
		return $hours;
	}

	/**
	 * 3-hour periods from Yr shortIntervals, from the current slot up to $untilMs
	 * (default: +36 hours). Used by the period table and near-term meteogram icons.
	 */
	public static function threeHourPeriods($untilMs = null) {
		$hourly = self::hourly();
		if (!$hourly) { return array(); }
		$now = (int) floor(microtime(true) * 1000);
		if ($untilMs === null) { $untilMs = $now + 36 * 3600 * 1000; }
		$buckets = array();
		foreach ($hourly as $iv) {
			if (empty($iv['t'])) { continue; }
			$t = (int)$iv['t'];
			if ($t >= $untilMs) { continue; }
			$start = self::londonSlotStart($t, 3);
			$end = $start + 3 * 3600 * 1000;
			if ($end <= $now) { continue; }
			if (!isset($buckets[$start])) {
				$buckets[$start] = array('t' => $start, 'end' => $end, 'hours' => array());
			}
			$buckets[$start]['hours'][] = $iv;
		}
		ksort($buckets);
		$out = array();
		foreach ($buckets as $b) {
			$out[] = self::summariseHourBucket($b);
		}
		return $out;
	}

	private static function summariseHourBucket($b) {
		$hours = $b['hours'];
		$temp = $tmin = $tmax = $precip = null;
		$windSum = 0.0;
		$windN = 0;
		$dirX = 0.0;
		$dirY = 0.0;
		$dirN = 0;
		$bestIcon = 'cloudy';
		$bestDesc = '';
		$bestRank = -1;
		foreach ($hours as $iv) {
			if (isset($iv['temp']) && $iv['temp'] !== null) {
				$v = (float)$iv['temp'];
				$temp = $v;
				$tmin = ($tmin === null) ? $v : min($tmin, $v);
				$tmax = ($tmax === null) ? $v : max($tmax, $v);
			}
			if (isset($iv['precip']) && $iv['precip'] !== null) {
				$precip = ($precip === null ? 0.0 : $precip) + (float)$iv['precip'];
			}
			if (isset($iv['wind']) && $iv['wind'] !== null) {
				$windSum += (float)$iv['wind'];
				$windN++;
			}
			if (isset($iv['windDir']) && $iv['windDir'] !== null) {
				$rad = deg2rad((float)$iv['windDir']);
				$dirX += cos($rad);
				$dirY += sin($rad);
				$dirN++;
			}
			$icon = isset($iv['icon']) ? $iv['icon'] : 'cloudy';
			$rank = self::iconRank($icon);
			if ($rank > $bestRank) {
				$bestRank = $rank;
				$bestIcon = $icon;
				$bestDesc = isset($iv['desc']) ? $iv['desc'] : '';
			}
		}
		$windDir = null;
		if ($dirN && ($dirX != 0.0 || $dirY != 0.0)) {
			$windDir = (int)nw3_round(fmod(rad2deg(atan2($dirY, $dirX)) + 360.0, 360.0));
		}
		$tz = new DateTimeZone('Europe/London');
		$labelDt = new DateTime('@' . (int)($b['t'] / 1000));
		$labelDt->setTimezone($tz);
		return array(
			't' => $b['t'],
			'end' => $b['end'],
			'label' => $labelDt->format('D H:i'),
			'temp' => $temp,
			'tmin' => $tmin,
			'tmax' => $tmax,
			'precip' => ($precip !== null) ? nw3_round($precip, 2) : null,
			'wind' => $windN ? nw3_round($windSum / $windN, 1) : null,
			'windDir' => $windDir,
			'icon' => $bestIcon,
			'desc' => $bestDesc,
		);
	}

	/**
	 * 6-hour weather icons along the meteogram. Near-term 3-hour detail is in
	 * the period table — 3h chips overlap on a shared 10-day axis.
	 */
	private static function meteogramIcons($periods, $omHours = null) {
		$img = Site::IMG_ROOT;
		$now = (int) floor(microtime(true) * 1000);
		$out = array();
		$step = 6 * 3600 * 1000;
		if ($periods) {
			foreach ($periods as $p) {
				if (empty($p['t'])) { continue; }
				$start = (int)$p['t'];
				$end = !empty($p['end']) ? (int)$p['end'] : ($start + $step);
				if ($end <= $now) { continue; }
				$icon = isset($p['icon']) ? $p['icon'] : 'cloudy';
				// Place every chip on a 6h London clock mid-point (03/09/15/21)
				// so a current leftover period (e.g. 02–06) is not 5h from the
				// next 6h icon and the chips do not overlap.
				$slot = self::londonSlotStart((int)(($start + $end) / 2), 6);
				$t = $slot + (int)($step / 2);
				$out[$slot] = array(
					't' => $t,
					'start' => $start,
					'end' => $end,
					'icon' => $img . $icon . '_lg.png',
					'desc' => isset($p['desc']) ? $p['desc'] : '',
					'step' => 6,
				);
			}
			ksort($out);
			$out = array_values($out);
		} elseif ($omHours) {
			foreach (self::hoursToPeriodBuckets($omHours, 6, $now, null) as $p) {
				$out[] = array(
					't' => (int)(($p['t'] + $p['end']) / 2),
					'start' => $p['t'],
					'end' => $p['end'],
					'icon' => $img . $p['icon'] . '_lg.png',
					'desc' => isset($p['desc']) ? $p['desc'] : '',
					'step' => 6,
				);
			}
		}
		return $out;
	}

	private static function hoursToPeriodBuckets($hours, $stepHours, $fromMs, $untilMs) {
		$step = $stepHours * 3600 * 1000;
		$buckets = array();
		foreach ($hours as $iv) {
			if (empty($iv['t']) || empty($iv['icon'])) { continue; }
			$t = (int)$iv['t'];
			if ($untilMs !== null && $t >= $untilMs) { continue; }
			$start = self::londonSlotStart($t, $stepHours);
			$end = $start + $step;
			if ($end <= $fromMs) { continue; }
			if ($untilMs !== null && $start >= $untilMs) { continue; }
			if (!isset($buckets[$start])) {
				$buckets[$start] = array('t' => $start, 'end' => $end, 'hours' => array());
			}
			$buckets[$start]['hours'][] = $iv;
		}
		ksort($buckets);
		$out = array();
		foreach ($buckets as $b) {
			$out[] = self::summariseHourBucket($b);
		}
		return $out;
	}

	/**
	 * Per-day hi/lo, description, rain and wind for the meteogram header/footer,
	 * using the same Yr dayIntervals values as the (removed) daily table.
	 */
	private static function dailySummaries() {
		$om = self::openMeteoHoursByT();
		$samples = array();
		foreach (array_merge(self::hourly(), self::periods()) as $iv) {
			if (!empty($iv['t'])) { $samples[(int)$iv['t']] = $iv; }
		}
		foreach ($om as $t => $iv) {
			if (!isset($samples[$t])) {
				$samples[$t] = $iv;
			} else {
				if ((!isset($samples[$t]['gust']) || $samples[$t]['gust'] === null)
					&& isset($iv['gust']) && $iv['gust'] !== null) {
					$samples[$t]['gust'] = $iv['gust'];
				}
				if ((!isset($samples[$t]['windDir']) || $samples[$t]['windDir'] === null)
					&& isset($iv['windDir']) && $iv['windDir'] !== null) {
					$samples[$t]['windDir'] = $iv['windDir'];
				}
			}
		}

		$out = array();
		foreach (self::days() as $d) {
			if (empty($d['date'])) { continue; }
			$startTs = strtotime($d['date'] . ' 00:00:00');
			if ($startTs === false) { continue; }
			$start = $startTs * 1000;
			$end = $start + 24 * 3600 * 1000;
			$mid = $start + 12 * 3600 * 1000;

			$dirX = 0.0;
			$dirY = 0.0;
			$dirN = 0;
			$gustMax = null;
			$windSum = 0.0;
			$windN = 0;
			foreach ($samples as $t => $iv) {
				if ($t < $start || $t >= $end) { continue; }
				if (isset($iv['wind']) && $iv['wind'] !== null) {
					$windSum += (float)$iv['wind'];
					$windN++;
				}
				if (isset($iv['windDir']) && $iv['windDir'] !== null) {
					$rad = deg2rad((float)$iv['windDir']);
					$dirX += cos($rad);
					$dirY += sin($rad);
					$dirN++;
				}
				if (isset($iv['gust']) && $iv['gust'] !== null) {
					$g = (float)$iv['gust'];
					$gustMax = ($gustMax === null) ? $g : max($gustMax, $g);
				}
			}

			$mean = null;
			if (isset($d['windMin'], $d['windMax']) && $d['windMin'] !== null && $d['windMax'] !== null) {
				$mean = ((float)$d['windMin'] + (float)$d['windMax']) / 2.0;
			} elseif (isset($d['windMax']) && $d['windMax'] !== null) {
				$mean = (float)$d['windMax'];
			} elseif ($windN) {
				$mean = $windSum / $windN;
			}
			$dirTxt = '';
			if ($dirN && ($dirX != 0.0 || $dirY != 0.0)) {
				$deg = fmod(rad2deg(atan2($dirY, $dirX)) + 360.0, 360.0);
				$dirTxt = Wx::degname($deg);
			}
			$windTxt = '—';
			$gustTxt = '';
			if ($mean !== null) {
				$windTxt = trim($dirTxt . ' ' . Wx::conv($mean, Wx::Wind, true, false, -1));
				if ($gustMax !== null) {
					$gustTxt = 'gust ' . Wx::conv($gustMax, Wx::Wind, true, false, -1);
				}
			} elseif ($dirTxt !== '') {
				$windTxt = $dirTxt;
			}

			$precip = isset($d['precip']) ? $d['precip'] : null;
			$precipTxt = ($precip !== null && (float)$precip > 0)
				? Wx::conv($precip, Wx::Rain, true)
				: '—';
			$hi = (isset($d['tmax']) && $d['tmax'] !== null)
				? Wx::conv($d['tmax'], Wx::Temperature, false, false, -1) : '—';
			$lo = (isset($d['tmin']) && $d['tmin'] !== null)
				? Wx::conv($d['tmin'], Wx::Temperature, false, false, -1) : '—';
			$deg = '°';

			$out[] = array(
				't' => $mid,
				'start' => $start,
				'end' => $end,
				'label' => isset($d['label']) ? $d['label'] : '',
				'desc' => isset($d['desc']) ? htmlspecialchars($d['desc']) : '',
				'hi' => $hi,
				'lo' => $lo,
				'hiLo' => '<span class="fc-mg-hi">' . htmlspecialchars($hi) . $deg
					. '</span><span class="fc-mg-lo">' . htmlspecialchars($lo) . $deg . '</span>',
				'precip' => $precipTxt,
				'wind' => $windTxt,
				'gust' => $gustTxt,
			);
		}
		return $out;
	}

	/**
	 * Fill the 6-hour tail with hourly points via Fritsch–Carlson monotone
	 * cubic interpolation of temp/dew/wind/cloud. Does not invent new local
	 * extrema between the 6h instantaneous values. Precip stays as 6h totals.
	 */
	private static function interpolateSixHourTail($hours, $lastT) {
		if ($lastT <= 0 || !$hours) { return $hours; }
		$head = array();
		$tail = array();
		$anchor = null;
		foreach ($hours as $iv) {
			if (empty($iv['t'])) { continue; }
			$t = (int)$iv['t'];
			if ($t < $lastT) {
				$head[] = $iv;
			} elseif ($t === $lastT) {
				$anchor = $iv;
				$head[] = $iv;
			} else {
				$tail[] = $iv;
			}
		}
		if (!$anchor || !$tail) { return $hours; }
		$step = 3600 * 1000;
		$fields = array('temp', 'dew', 'wind', 'cloud');
		$knots = array();
		foreach (array_merge(array($anchor), $tail) as $iv) {
			$knots[] = $iv;
		}
		$byT = array();
		foreach ($tail as $iv) {
			$byT[(int)$iv['t']] = $iv;
		}
		$end = (int)$tail[count($tail) - 1]['t'];
		$out = $head;
		for ($t = $lastT + $step; $t <= $end; $t += $step) {
			$row = array('t' => $t);
			foreach ($fields as $f) {
				$pts = array();
				foreach ($knots as $iv) {
					if (isset($iv[$f]) && $iv[$f] !== null) {
						$pts[] = array((int)$iv['t'], (float)$iv[$f]);
					}
				}
				$val = self::monotoneAt($pts, $t);
				if ($val !== null) { $row[$f] = nw3_round($val, ($f === 'cloud') ? 0 : 1); }
			}
			if (isset($byT[$t])) {
				$src = $byT[$t];
				foreach (array('precip', 'precipRange', 'gust', 'icon', 'desc') as $k) {
					if (isset($src[$k])) { $row[$k] = $src[$k]; }
				}
			}
			$out[] = $row;
		}
		return $out;
	}

	/**
	 * Evaluate a Fritsch–Carlson monotone cubic at $x. $pts is [[x,y], ...]
	 * sorted by x. Returns null if there are fewer than 2 points.
	 */
	private static function monotoneAt($pts, $x) {
		$n = count($pts);
		if ($n === 0) { return null; }
		if ($n === 1) { return $pts[0][1]; }
		if ($x <= $pts[0][0]) { return $pts[0][1]; }
		if ($x >= $pts[$n - 1][0]) { return $pts[$n - 1][1]; }
		$h = $delta = $m = array();
		for ($i = 0; $i < $n - 1; $i++) {
			$h[$i] = $pts[$i + 1][0] - $pts[$i][0];
			if ($h[$i] == 0) { $delta[$i] = 0.0; }
			else { $delta[$i] = ($pts[$i + 1][1] - $pts[$i][1]) / $h[$i]; }
		}
		$m[0] = $delta[0];
		$m[$n - 1] = $delta[$n - 2];
		for ($i = 1; $i < $n - 1; $i++) {
			if ($delta[$i - 1] * $delta[$i] <= 0) {
				$m[$i] = 0.0;
			} else {
				$w1 = 2 * $h[$i] + $h[$i - 1];
				$w2 = $h[$i] + 2 * $h[$i - 1];
				$m[$i] = ($w1 + $w2) / ($w1 / $delta[$i - 1] + $w2 / $delta[$i]);
			}
		}
		$i = 0;
		while ($i < $n - 2 && $x > $pts[$i + 1][0]) { $i++; }
		$hi = $h[$i];
		if ($hi == 0) { return $pts[$i][1]; }
		$t = ($x - $pts[$i][0]) / $hi;
		$t2 = $t * $t;
		$t3 = $t2 * $t;
		$h00 = 2 * $t3 - 3 * $t2 + 1;
		$h10 = $t3 - 2 * $t2 + $t;
		$h01 = -2 * $t3 + 3 * $t2;
		$h11 = $t3 - $t2;
		return $h00 * $pts[$i][1] + $h10 * $hi * $m[$i] + $h01 * $pts[$i + 1][1] + $h11 * $hi * $m[$i + 1];
	}

	/**
	 * Daily tmin/tmax band from Yr dayIntervals for every day in the meteogram
	 * (including days that also have hourly points).
	 */
	private static function dailyTempRange() {
		$out = array();
		foreach (self::days() as $d) {
			if (empty($d['date']) || !isset($d['tmin']) || !isset($d['tmax'])
				|| $d['tmin'] === null || $d['tmax'] === null) {
				continue;
			}
			$start = strtotime($d['date'] . ' 00:00:00');
			if ($start === false) { continue; }
			$startMs = $start * 1000;
			$endMs = $startMs + 24 * 3600 * 1000 - 1;
			$lo = Wx::convNum($d['tmin'], Wx::Temperature, 1);
			$hi = Wx::convNum($d['tmax'], Wx::Temperature, 1);
			if ($out) { $out[] = null; }
			$out[] = array($startMs, $lo, $hi);
			$out[] = array($endMs, $lo, $hi);
		}
		return $out;
	}

	/**
	 * meteogram can shade elapsed time and draw a now-line with real context.
	 * The current (incomplete) hour is omitted so it does not collide with the
	 * first forecast step.
	 */
	private static function todayObservedHours() {
		$path = ROOT . 'logfiles/daily/todaylog.txt';
		if (!is_file($path)) {
			$path = ROOT . 'logfiles/daily/' . date('Ymd') . 'log.txt';
		}
		if (!is_file($path)) { return array(); }
		$today = (int)date('j');
		$buckets = array();
		$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		if ($lines === false) { return array(); }
		foreach ($lines as $line) {
			$p = explode(',', $line);
			if (count($p) < 10) { continue; }
			if ((int)$p[2] !== $today) { continue; }
			if (!is_numeric($p[6])) { continue; }
			$h = (int)$p[0];
			if (!isset($buckets[$h])) {
				$buckets[$h] = array('t' => 0.0, 'd' => 0.0, 'w' => 0.0, 'n' => 0, 'nd' => 0, 'nw' => 0);
			}
			$buckets[$h]['t'] += (float)$p[6];
			$buckets[$h]['n']++;
			if (is_numeric($p[9])) {
				$buckets[$h]['d'] += (float)$p[9];
				$buckets[$h]['nd']++;
			}
			if (is_numeric($p[3])) {
				$buckets[$h]['w'] += (float)$p[3];
				$buckets[$h]['nw']++;
			}
		}
		$currentHour = (int)date('G');
		$dayStamp = date('Y-m-d');
		$out = array();
		ksort($buckets);
		foreach ($buckets as $h => $b) {
			if ((int)$h === $currentHour) { continue; }
			if ($b['n'] < 10) { continue; }
			$ts = strtotime($dayStamp . sprintf(' %02d:00:00', $h));
			if ($ts === false) { continue; }
			$iv = array('t' => $ts * 1000, 'temp' => nw3_round($b['t'] / $b['n'], 1));
			if ($b['nd'] > 0) {
				$iv['dew'] = nw3_round($b['d'] / $b['nd'], 1);
			}
			if ($b['nw'] > 0) {
				$iv['wind'] = nw3_round($b['w'] / $b['nw'], 1);
			}
			$out[] = $iv;
		}
		return $out;
	}

	/** Prepend today's observed hours that fall before the first forecast step. */
	private static function prependObserved($hours) {
		$obs = self::todayObservedHours();
		if (!$obs) { return $hours; }
		$first = null;
		foreach ($hours as $iv) {
			if (!empty($iv['t'])) { $first = (int)$iv['t']; break; }
		}
		if ($first === null) { return $obs; }
		$head = array();
		foreach ($obs as $iv) {
			if ((int)$iv['t'] < $first) { $head[] = $iv; }
		}
		return $head ? array_merge($head, $hours) : $hours;
	}

	/**
	 * Unit-converted series for the stacked 10-day meteogram.
	 * Yr is the primary source (aligned with the daily / period views). Open-Meteo
	 * fills precipitation probability and gusts when Yr does not provide them, and
	 * is the fallback if the Yr cache is missing.
	 */
	public static function meteogramSeries() {
		$tempU = Wx::getUnitsText(Wx::Temperature);
		$rainU = Wx::getUnitsText(Wx::Rain);
		$windU = Wx::getUnitsText(Wx::Wind);
		list($hours, $updated, $src, $icons) = self::meteogramHours();
		$hours = self::prependObserved($hours);

		$temp = $dew = $precip = $precip6h = $pop = $cloud = $wind = $gust = array();
		foreach ($hours as $iv) {
			if (empty($iv['t'])) { continue; }
			$t = (int)$iv['t'];
			if (isset($iv['temp']) && $iv['temp'] !== null) {
				$temp[] = array($t, Wx::convNum($iv['temp'], Wx::Temperature, 1));
			}
			if (isset($iv['dew']) && $iv['dew'] !== null) {
				$dew[] = array($t, Wx::convNum($iv['dew'], Wx::Temperature, 1));
			}
			if (isset($iv['precip']) && $iv['precip'] !== null) {
				$val = Wx::convNum($iv['precip'], Wx::Rain, 2);
				if ($val === null || (float)$val <= 0) { /* skip dry hours: zero columns render as hairlines */ }
				elseif (!empty($iv['precipRange']) && (int)$iv['precipRange'] >= 2 * 3600 * 1000) {
					$range = (int)$iv['precipRange'];
					$precip6h[] = array($t + (int)($range / 2), $val);
				} else {
					$precip[] = array($t + 30 * 60 * 1000, $val);
				}
			}
			if (isset($iv['pop']) && $iv['pop'] !== null) {
				$pop[] = array($t, (float)$iv['pop']);
			}
			if (isset($iv['cloud']) && $iv['cloud'] !== null) {
				$cloud[] = array($t, (float)$iv['cloud']);
			}
			if (isset($iv['wind']) && $iv['wind'] !== null) {
				$wind[] = array($t, Wx::convNum($iv['wind'], Wx::Wind, 1));
			}
			if (isset($iv['gust']) && $iv['gust'] !== null) {
				$gust[] = array($t, Wx::convNum($iv['gust'], Wx::Wind, 1));
			}
		}
		return array(
			'updated' => $updated,
			'source' => $src,
			'units' => array('temp' => $tempU, 'precip' => $rainU, 'wind' => $windU, 'pop' => '%', 'cloud' => '%'),
			// Match site chart palette (wxcharts.js INTRA_TABS).
			'colors' => array(
				'temp' => '#d34f00',
				'dew' => '#0b8043',
				'precip' => '#2554c7',
				'pop' => 'rgba(37,84,199,0.28)',
				'cloud' => '#5a7a9a',
				'wind' => '#c01717',
				'gust' => '#c23b86',
			),
			'temp' => $temp,
			'dew' => $dew,
			'tempRange' => ($src === 'yr') ? self::dailyTempRange() : array(),
			'precip' => $precip,
			'precip6h' => $precip6h,
			'pop' => $pop,
			'cloud' => $cloud,
			'wind' => $wind,
			'gust' => $gust,
			'icons' => $icons,
			'daily' => ($src === 'yr') ? self::dailySummaries() : array(),
			'popSource' => ($src === 'yr' && $pop) ? 'open-meteo' : $src,
			'gustSource' => ($src === 'yr' && $gust) ? 'open-meteo' : $src,
		);
	}

	/**
	 * Parse the Met Office London & South East regional forecast page.
	 * The headline is taken only from the regional "Headline" section inside
	 * #fiveDayText — never from #breaking-news-headline (a national article title).
	 * Returns headline, issued, and sections, or null if the regional block is missing.
	 */
	public static function parseRegionalHtml($raw) {
		if (!is_string($raw) || $raw === '') { return null; }
		$sections = array();
		$block = '';
		if (preg_match('/id="fiveDayText"[^>]*>(.*?)<\/div>\s*<\/div>\s*<\/section>/s', $raw, $mBlock)) {
			$block = $mBlock[1];
			if (preg_match_all('/<div class="forecast-text-section">\s*<h4>(.*?)<\/h4>\s*<p>(.*?)<\/p>\s*<\/div>/s', $block, $mm, PREG_SET_ORDER)) {
				foreach ($mm as $row) {
					$title = trim(html_entity_decode(strip_tags($row[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
					$text = trim(html_entity_decode(strip_tags($row[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
					$text = preg_replace('/\s+/u', ' ', $text);
					if ($title === '' && $text === '') { continue; }
					$sections[] = array('title' => $title, 'text' => $text);
				}
			}
		}
		$headline = '';
		foreach ($sections as $sec) {
			$label = rtrim($sec['title'], ':');
			if (strcasecmp($label, 'Headline') === 0) {
				$headline = $sec['text'];
				break;
			}
		}
		// Fallback if the section wrapper markup changed but Headline is still in the regional block.
		if ($headline === '' && $block !== '' && preg_match('/<h4>[^<]*Headline:?[^<]*<\/h4>\s*<p>(.*?)<\/p>/is', $block, $mh)) {
			$headline = trim(html_entity_decode(strip_tags($mh[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
			$headline = preg_replace('/\s+/u', ' ', $headline);
		}
		$issued = '';
		if (preg_match('/id="fiveDayText"[\s\S]{0,2500}?<time datetime="([^"]+)"/', $raw, $mt)) {
			$issued = $mt[1];
		}
		if ($headline === '' && !$sections) {
			return null;
		}
		return array(
			'headline' => $headline,
			'issued' => $issued,
			'sections' => $sections,
		);
	}

	/** Cached Met Office London & SE regional text (OGL / Crown copyright). */
	public static function discussion() {
		$path = V5_CACHE_ROOT . 'forecast_discussion.json';
		if (!is_file($path)) { return array(); }
		$json = json_decode(file_get_contents($path), true);
		return is_array($json) ? $json : array();
	}

	/** Cached Met Office surface-pressure analysis pointer. */
	public static function pressureChart() {
		$path = V5_CACHE_ROOT . 'forecast_pressure.json';
		if (!is_file($path)) { return array(); }
		$json = json_decode(file_get_contents($path), true);
		return is_array($json) ? $json : array();
	}

	/** OGL credit line on the wx5 discussion (full attribution). */
	private static function discussionCreditHtml($d, $srcUrl) {
		$when = '';
		if (!empty($d['issued'])) {
			$fmt = self::fmtUk($d['issued']);
			$when = 'Issued ' . htmlspecialchars($fmt !== '' ? $fmt : $d['issued']);
		} elseif (!empty($d['updated'])) {
			$fmt = self::fmtUk((int)$d['updated']);
			$when = 'Cached ' . htmlspecialchars($fmt !== '' ? $fmt : date('H:i D j M', (int)$d['updated']));
		}
		return '<p class="fc-credit">Content supplied by the Met Office'
			. ($when !== '' ? (' · ' . $when) : '')
			. ' · <a href="' . htmlspecialchars($srcUrl) . '" rel="noopener" target="_blank">'
			. 'Full London &amp; South East forecast</a>'
			. ' · <a href="https://www.nationalarchives.gov.uk/doc/open-government-licence/version/3/" rel="noopener" target="_blank">OGL v3</a>'
			. '</p>';
	}

	/** Met Office regional headline for the homepage forecast card. */
	public static function renderHomeHeadline() {
		$d = self::discussion();
		if (empty($d['headline'])) { return; }
		echo '<p class="fcast-headline">' . htmlspecialchars($d['headline']) . '</p>';
	}

	/** Short Met Office credit, same placement/styling as the card's Yr.no credit. */
	public static function renderHomeSrc() {
		$d = self::discussion();
		if (empty($d['headline'])) { return; }
		$srcUrl = 'https://weather.metoffice.gov.uk/forecast/regional/se';
		echo '<span class="fcast-src"> · data <a href="' . htmlspecialchars($srcUrl) . '" rel="noopener">Met Office</a></span>';
	}

	/** Expert-style regional discussion (Met Office London & South East). */
	public static function renderDiscussion() {
		$d = self::discussion();
		$srcUrl = 'https://weather.metoffice.gov.uk/forecast/regional/se';
		echo '<div class="fc-discuss">';
		echo '<h2>London &amp; South East forecast discussion</h2>';
		echo '<p class="fc-discuss-intro">Met Office regional guidance for London area. Full text and maps on Met Office site.</p>';
		if (!empty($d['sections']) && is_array($d['sections'])) {
			if (!empty($d['headline'])) {
				echo '<p class="fc-discuss-headline">' . htmlspecialchars($d['headline']) . '</p>';
			}
			echo '<div class="fc-discuss-body">';
			foreach ($d['sections'] as $sec) {
				$title = isset($sec['title']) ? $sec['title'] : '';
				$text = isset($sec['text']) ? $sec['text'] : '';
				if ($title === '' && $text === '') { continue; }
				// Headline is already shown unlabelled above; skip the stored "Headline:" section.
				if (strcasecmp(rtrim($title, ':'), 'Headline') === 0) { continue; }
				echo '<div class="fc-discuss-sec">';
				if ($title !== '') {
					echo '<h3>' . htmlspecialchars($title) . '</h3>';
				}
				echo '<p>' . htmlspecialchars($text) . '</p>';
				echo '</div>';
			}
			echo '</div>';
			echo self::discussionCreditHtml($d, $srcUrl);
		} else {
			echo '<p class="fc-discuss-fallback">Regional discussion text is temporarily unavailable. '
				. 'Read the latest on the '
				. '<a href="' . htmlspecialchars($srcUrl) . '" rel="noopener" target="_blank">Met Office London &amp; South East forecast</a>.</p>';
		}
		echo '</div>';
	}

	/** Format a timestamp (ISO string or unix time) as UK local e.g. "16:00 Mon 9 Nov". */
	private static function fmtUk($time) {
		if ($time === null || $time === '') { return ''; }
		try {
			if (is_int($time) || (is_string($time) && ctype_digit($time))) {
				$dt = new DateTime('@' . (string)((int)$time));
			} else {
				$dt = new DateTime((string)$time);
			}
			$dt->setTimezone(new DateTimeZone('Europe/London'));
			return $dt->format('H:i D j M');
		} catch (Exception $e) {
			return is_string($time) ? (string)$time : '';
		}
	}

	/** Current UK surface pressure: analysis plus T+24 and T+48 forecast charts. */
	public static function renderPressureChart() {
		$p = self::pressureChart();
		$charts = (isset($p['charts']) && is_array($p['charts'])) ? $p['charts'] : array();
		if (!$charts && (!empty($p['local']) || !empty($p['uri']) || is_file(V5_GENERATED_ROOT . 'surface_pressure.gif'))) {
			$charts[] = array(
				'lead' => 0,
				'label' => 'Analysis',
				'file' => 'surface_pressure.gif',
				'valid' => isset($p['valid']) ? $p['valid'] : '',
				'uri' => isset($p['uri']) ? $p['uri'] : '',
				'local' => !empty($p['local']) || is_file(V5_GENERATED_ROOT . 'surface_pressure.gif'),
			);
		}
		$page = 'https://weather.metoffice.gov.uk/maps-and-charts/surface-pressure';
		$bust = !empty($p['issued']) ? ('?t=' . rawurlencode(preg_replace('/\W+/', '', $p['issued']))) : ('?t=' . (int)(isset($p['updated']) ? $p['updated'] : time()));

		echo '<div class="fc-pressure">';
		echo '<h2 id="fc-pressure-heading">UK surface pressure</h2>';
		if (!$charts) {
			echo '<p>Surface pressure charts temporarily unavailable. See the '
				. '<a href="' . $page . '" rel="noopener" target="_blank">Met Office surface pressure charts</a>.</p>';
			echo '</div>';
			return;
		}

		$items = array();
		foreach ($charts as $c) {
			$lead = isset($c['lead']) ? (int)$c['lead'] : 0;
			$label = isset($c['label']) ? $c['label'] : ($lead === 0 ? 'Analysis' : ('T+' . $lead . ' forecast'));
			$file = isset($c['file']) ? $c['file'] : '';
			$src = '';
			if (!empty($c['local']) && $file !== '' && is_file(V5_GENERATED_ROOT . $file)) {
				$src = '/' . $file;
			} elseif ($file !== '' && is_file(V5_GENERATED_ROOT . $file)) {
				$src = '/' . $file;
			} elseif (!empty($c['uri'])) {
				$src = $c['uri'];
			}
			if ($src === '') { continue; }
			$valid = self::fmtUk(isset($c['valid']) ? $c['valid'] : '');
			$cap = '<b>' . htmlspecialchars($label) . '</b>';
			if ($valid !== '') { $cap .= ' · valid ' . htmlspecialchars($valid); }
			$items[] = array(
				'lead' => $lead,
				'label' => $label,
				'src' => $src . $bust,
				'cap' => $cap,
				'alt' => 'Met Office UK surface pressure ' . $label . ($valid !== '' ? (' valid ' . $valid) : ''),
			);
		}
		if (!$items) {
			echo '<p>Surface pressure charts temporarily unavailable. See the '
				. '<a href="' . $page . '" rel="noopener" target="_blank">Met Office surface pressure charts</a>.</p>';
			echo '</div>';
			return;
		}

		echo '<div class="fc-pressure-tabs" role="tablist" aria-labelledby="fc-pressure-heading">';
		foreach ($items as $i => $it) {
			$sel = ($i === 0) ? 'true' : 'false';
			$tabIndex = ($i === 0) ? '0' : '-1';
			$active = ($i === 0) ? ' active' : '';
			echo '<button type="button" class="fc-pressure-tab' . $active . '" role="tab"'
				. ' id="fc-p-tab-' . $i . '" aria-controls="fc-p-panel-' . $i . '"'
				. ' aria-selected="' . $sel . '" tabindex="' . $tabIndex . '" data-fc-p-index="' . $i . '">'
				. $it['cap'] . '</button>';
		}
		echo '</div>';
		echo '<div class="fc-pressure-panels">';
		foreach ($items as $i => $it) {
			$lead = $it['lead'];
			$cls = 'fc-pressure-item' . ($lead === 0 ? ' fc-pressure-item-analysis' : ' fc-pressure-item-forecast');
			$hidden = ($i === 0) ? '' : ' hidden';
			echo '<div class="' . $cls . '" role="tabpanel" id="fc-p-panel-' . $i . '"'
				. ' aria-labelledby="fc-p-tab-' . $i . '" data-fc-p-index="' . $i . '"' . $hidden . '>';
			echo '<figure>';
			echo '<a href="' . htmlspecialchars($it['src']) . '" rel="noopener" target="_blank" title="Open full-size ' . htmlspecialchars($it['label']) . '">';
			$dims = ' width="800" height="540"';
			if ($i === 0) {
				echo '<img class="fc-pressure-img" src="' . htmlspecialchars($it['src']) . '" alt="' . htmlspecialchars($it['alt']) . '"' . $dims . ' />';
			} else {
				echo '<img class="fc-pressure-img" data-src="' . htmlspecialchars($it['src']) . '" alt="' . htmlspecialchars($it['alt']) . '"' . $dims . ' />';
			}
			echo '</a>';
			echo '<figcaption class="fc-pressure-cap">' . $it['cap'] . '</figcaption></figure>';
			echo '</div>';
		}
		echo '</div>';
		echo '<script>(function(){var r=document.currentScript&&document.currentScript.parentElement;if(!r)return;'
			. 'var tabs=[].slice.call(r.querySelectorAll("[role=tab]")),panels=[].slice.call(r.querySelectorAll("[role=tabpanel]"));'
			. 'if(!tabs.length||!panels.length)return;'
			. 'function showImg(p){var img=p.querySelector("img.fc-pressure-img");if(img&&img.getAttribute("data-src")&&!img.getAttribute("src")){img.src=img.getAttribute("data-src");}}'
			. 'function go(i){i=Math.max(0,Math.min(i,tabs.length-1));'
			. 'tabs.forEach(function(t,n){var on=n===i;t.setAttribute("aria-selected",on?"true":"false");t.tabIndex=on?0:-1;t.classList.toggle("active",on);});'
			. 'panels.forEach(function(p,n){var on=n===i;p.hidden=!on;if(on)showImg(p);});}'
			. 'tabs.forEach(function(t,i){t.addEventListener("click",function(){go(i);t.focus();});'
			. 't.addEventListener("keydown",function(e){var n=i;if(e.key==="ArrowRight"||e.key==="ArrowDown")n=i+1;else if(e.key==="ArrowLeft"||e.key==="ArrowUp")n=i-1;else if(e.key==="Home")n=0;else if(e.key==="End")n=tabs.length-1;else return;e.preventDefault();go(n);tabs[n].focus();});});'
			. 'window.NW3=window.NW3||{};window.NW3.selectPressureTab=function(i){go(i);};'
			. 'function preload(){panels.forEach(showImg);}'
			. 'if(document.readyState==="complete")preload();else window.addEventListener("load",preload);'
			. 'go(0);})();</script>';
		$issued = self::fmtUk(isset($p['issued']) ? $p['issued'] : '');
		echo '<p class="fc-credit">Met Office surface pressure charts'
			. ($issued !== '' ? (' · issued ' . htmlspecialchars($issued)) : '')
			. ' · <a href="' . $page . '" rel="noopener" target="_blank">all charts</a></p>';
		echo '</div>';
	}

	/** Ben's favourite external forecast pages for Hampstead / NW3. */
	public static function renderFavouriteLinks() {
		$links = array(
			array(
				'label' => 'Netweather 7-day forecast — Hampstead',
				'url' => 'https://www.netweather.tv/weather-forecasts/uk/7-day/13501~Hampstead',
				'note' => 'Local model blend',
			),
			array(
				'label' => 'Met Office forecast — Hampstead',
				'url' => 'https://weather.metoffice.gov.uk/forecast/gcpv7fnqu',
				'note' => 'Official forecast for Hampstead (Greater London)',
			),
			array(
				'label' => 'Met Office — London &amp; South East regional',
				'url' => 'https://weather.metoffice.gov.uk/forecast/regional/se',
				'note' => 'Written regional discussion used above',
			),
		);
		echo '<div class="fc-favs">';
		echo '<h2>Favourite forecast pages</h2>';
		echo '<ul class="fc-fav-list">';
		foreach ($links as $L) {
			echo '<li><a href="' . htmlspecialchars($L['url']) . '" rel="noopener" target="_blank">'
				. $L['label'] . '</a>';
			if (!empty($L['note'])) {
				echo ' <span class="fc-fav-note">— ' . $L['note'] . '</span>';
			}
			echo '</li>';
		}
		echo '</ul></div>';
	}

	/** Daily forecast table (replaces the Yr table iframe). */
	public static function renderDailyTable() {
		$days = self::days();
		if (!count($days)) {
			echo '<p>Forecast data is temporarily unavailable.</p>';
			return;
		}

		echo '<div class="fc-table-wrap">';
		echo '<table class="table1 fc-table" width="100%" cellpadding="3" cellspacing="0">';
		echo '<tr class="table-head"><td class="td12" style="padding:0.5em" colspan="6">'
			. 'Daily forecast for Hampstead (NW3)</td></tr>';
		echo '<tr class="table-top">';
		echo '<td class="td4">Day</td><td class="td4" colspan="2">Conditions</td>'
			. '<td class="td4">High / Low</td><td class="td4">Rain</td><td class="td4">Wind</td>';
		echo '</tr>';

		foreach ($days as $i => $day) {
			HTML::tr(HTML::colcol($i));
			$icon = isset($day['icon']) ? $day['icon'] : 'cloudy';
			$desc = isset($day['desc']) ? $day['desc'] : '';
			$label = isset($day['label']) ? $day['label'] : '';
			$img = Site::IMG_ROOT . htmlspecialchars($icon) . '_lg.png';

			HTML::td(htmlspecialchars($label), 'td4');
			echo '<td class="td4 fc-icon"><img src="' . $img . '" width="40" height="40" alt="'
				. htmlspecialchars($desc) . '" title="' . htmlspecialchars($desc) . '" /></td>';
			HTML::td(htmlspecialchars($desc), 'td4');
			HTML::td(
				'<b>' . Wx::conv($day['tmax'], Wx::Temperature, true, false, -1) . '</b> / '
				. Wx::conv($day['tmin'], Wx::Temperature, true, false, -1),
				'td14C'
			);
			$precip = isset($day['precip']) ? $day['precip'] : null;
			HTML::td(
				($precip !== null && (float)$precip > 0) ? Wx::conv($precip, Wx::Rain, true) : '—',
				'td12C'
			);
			$wMin = isset($day['windMin']) ? $day['windMin'] : null;
			$wMax = isset($day['windMax']) ? $day['windMax'] : null;
			if ($wMin !== null && $wMax !== null) {
				$windTxt = Wx::conv($wMin, Wx::Wind, false, false, -1) . '–'
					. Wx::conv($wMax, Wx::Wind, true, false, -1);
			} elseif ($wMax !== null) {
				$windTxt = Wx::conv($wMax, Wx::Wind, true, false, -1);
			} else {
				$windTxt = '—';
			}
			HTML::td($windTxt, 'td13C');
			HTML::tr_end();
		}
		echo '</table></div>';

		$upd = self::updated();
		echo '<p class="fc-credit">Forecast data from '
			. '<a href="https://www.yr.no/en/forecast/daily-table/2-2647553/" rel="noopener">Yr.no / MET Norway</a>'
			. ($upd ? (' · updated ' . date('H:i', $upd)) : '')
			. '.</p>';
	}

	/** Near-term 3-hour period table (next 36 hours from shortIntervals). */
	public static function renderPeriodTable($limit = null) {
		$periods = self::threeHourPeriods();
		if (!count($periods)) {
			$fallback = self::periods();
			if ($limit !== null) { $fallback = array_slice($fallback, 0, (int)$limit); }
			$periods = $fallback;
		}
		if (!count($periods)) { return; }

		echo '<div class="fc-table-wrap">';
		echo '<table class="table1 fc-table" width="100%" cellpadding="3" cellspacing="0">';
		echo '<tr class="table-head"><td class="td12" style="padding:0.5em" colspan="5">'
			. 'Next 36 hours (3-hourly)</td></tr>';
		echo '<tr class="table-top">';
		echo '<td class="td4">Period</td><td class="td4" colspan="2">Conditions</td>'
			. '<td class="td4">Temp</td><td class="td4">Rain</td>';
		echo '</tr>';

		foreach ($periods as $i => $p) {
			HTML::tr(HTML::colcol($i));
			$icon = isset($p['icon']) ? $p['icon'] : 'cloudy';
			$desc = isset($p['desc']) ? $p['desc'] : '';
			$img = Site::IMG_ROOT . htmlspecialchars($icon) . '_lg.png';
			HTML::td(htmlspecialchars(isset($p['label']) ? $p['label'] : ''), 'td4');
			echo '<td class="td4 fc-icon"><img src="' . $img . '" width="36" height="36" alt="'
				. htmlspecialchars($desc) . '" title="' . htmlspecialchars($desc) . '" /></td>';
			HTML::td(htmlspecialchars($desc), 'td4');
			$tmax = isset($p['tmax']) ? $p['tmax'] : $p['temp'];
			$tmin = isset($p['tmin']) ? $p['tmin'] : $p['temp'];
			if ($tmax !== null && $tmin !== null && (float)$tmax !== (float)$tmin) {
				$tempTxt = Wx::conv($tmax, Wx::Temperature, true, false, -1) . ' / '
					. Wx::conv($tmin, Wx::Temperature, true, false, -1);
			} else {
				$tempTxt = Wx::conv($p['temp'], Wx::Temperature, true, false, -1);
			}
			HTML::td($tempTxt, 'td14C');
			$precip = isset($p['precip']) ? $p['precip'] : null;
			HTML::td(
				($precip !== null && (float)$precip > 0) ? Wx::conv($precip, Wx::Rain, true) : '—',
				'td12C'
			);
			HTML::tr_end();
		}
		echo '</table></div>';
	}
}
