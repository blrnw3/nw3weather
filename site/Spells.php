<?php
/**
 * Generic consecutive-day spell finder / ranker for any daily variable.
 *
 * A spell is a run of days matching a threshold rule:
 *   above → value >= threshold (blank days break the spell)
 *   below → value < threshold (blank days break the spell, except rain dry
 *            spells where blanks count as dry to match legacy)
 *   heatwave  → value > that calendar day's 1991-2020 normal + X (temperature only)
 *   coldsnap  → value < that calendar day's 1991-2020 normal − X (temperature only)
 */
class Spells {

	/** Variables with daily 1991-2020 normals, so heatwave / cold snap modes apply. */
	public static $anomVars = ['tmin', 'tmax', 'tmean'];

	/** Anomaly threshold presets (°C above / below the daily normal). */
	public static $anomPresets = [2, 3, 4, 5, 6, 8];
	const ANOM_DEFAULT = 5;

	private static $normCache = [];

	/** True when heatwave / cold snap modes are offered for a variable. */
	public static function supportsAnomaly($varName) {
		return in_array($varName, self::$anomVars, true);
	}

	/** True for the anomaly-relative spell directions. */
	public static function isAnomalyDir($direction) {
		return $direction === 'heatwave' || $direction === 'coldsnap';
	}

	/**
	 * Daily 1991-2020 normal for a Y-m-d date (memoised by leap flag + day of year).
	 * @return float|null
	 */
	public static function dailyNormal($varName, $ymd) {
		$y = (int)substr($ymd, 0, 4);
		$m = (int)substr($ymd, 5, 2);
		$d = (int)substr($ymd, 8, 2);
		$leap = ($y % 4 === 0 && ($y % 100 !== 0 || $y % 400 === 0)) ? 1 : 0;
		$key = $varName . '|' . $leap . '|' . $m . '|' . $d;
		if (!array_key_exists($key, self::$normCache)) {
			LTA::init();
			$n = LTA::getDailyAnom($varName, $m, $d, $leap ? 2024 : 2023);
			self::$normCache[$key] = nw3_is_numeric($n) ? (float)$n : null;
		}
		return self::$normCache[$key];
	}

	/**
	 * Y-m-d => anomaly (value − daily normal), null where the value or normal is blank.
	 * @param array $vals Y-m-d => value
	 * @return array
	 */
	public static function anomalySeries($varName, $vals) {
		$out = [];
		foreach ($vals as $ymd => $v) {
			if (Util::isBlank($v)) { $out[$ymd] = null; continue; }
			$n = self::dailyNormal($varName, $ymd);
			$out[$ymd] = ($n === null) ? null : round((float)$v - $n, 2);
		}
		return $out;
	}

	/**
	 * Load a flat Y-m-d => value series from the requested start year.
	 * @return array
	 */
	public static function loadDailySeries($varName, $startYear) {
		$requested = (int)$startYear;
		$varStart = isset(Wx::$daily[$varName]['start_year'])
			? (int)Wx::$daily[$varName]['start_year'] : Site::BASE_YEAR;
		$start = max($requested, $varStart);
		$vals = [];
		foreach (Data::varToDatArray($varName, $start) as $year => $arr1) {
			if ((int)$year < $start) { continue; }
			foreach ($arr1 as $month => $arr2) {
				$monthIdx = Util::zerolead($month);
				foreach ($arr2 as $day => $val) {
					$dayIdx = Util::zerolead($day);
					$vals["$year-$monthIdx-$dayIdx"] = $val;
				}
			}
		}
		ksort($vals);
		return $vals;
	}

	/** Sensible default threshold for a variable. */
	public static function defaultThreshold($varName, $unit) {
		return Wx::defaultThreshold($varName, $unit);
	}

	/** Preset threshold chips for the ranking selector. */
	public static function thresholdPresets($varName) {
		return Wx::thresholdPresets($varName);
	}

	/**
	 * Accumulations and event counts (rain, sun/wet/frost hours, hail...) sit at
	 * zero on a "nothing happened" day, so "above 0" has to mean strictly more
	 * than zero or every day would qualify.
	 * @return bool
	 */
	public static function strictAbove($varName, $threshold) {
		return Wx::strictAbove($varName, $threshold);
	}

	/**
	 * Whether a daily value is inside a spell for the given rule.
	 * @param mixed $val
	 * @param float $threshold
	 * @param string $direction 'above'|'below'
	 * @param array $opts optional: blankAsBelow (bool), strict (bool)
	 */
	public static function dayMatches($val, $threshold, $direction, $opts = []) {
		$blankAsBelow = !empty($opts['blankAsBelow']);
		if ($direction === 'above') {
			if (Util::isBlank($val)) { return false; }
			return !empty($opts['strict'])
				? (float)$val > (float)$threshold
				: (float)$val >= (float)$threshold;
		}
		if (Util::isBlank($val)) {
			return $blankAsBelow;
		}
		return !empty($opts['strict'])
			? (float)$val <= (float)$threshold
			: (float)$val < (float)$threshold;
	}

	/**
	 * Consecutive matching runs for one direction.
	 * @return array list of spell records
	 */
	public static function findRuns($vals, $threshold, $direction, $opts = []) {
		ksort($vals);
		$runs = [];
		$inSpell = false;
		$runDates = [];
		$keys = array_keys($vals);
		$n = count($keys);
		for ($i = 0; $i < $n; $i++) {
			$dt = $keys[$i];
			$match = self::dayMatches($vals[$dt], $threshold, $direction, $opts);
			if ($match) {
				if (!$inSpell) {
					$inSpell = true;
					$runDates = [];
				}
				$runDates[] = $dt;
			} elseif ($inSpell) {
				$runs[] = self::makeSpell($direction, $runDates, false);
				$inSpell = false;
				$runDates = [];
			}
		}
		if ($inSpell && count($runDates)) {
			$runs[] = self::makeSpell($direction, $runDates, true);
		}
		return $runs;
	}

	/**
	 * Rain-style wet/dry classification (blank days count as dry).
	 * @return array spells with type 'wet'|'dry'
	 */
	public static function findWetDryRuns($vals, $threshold = 0.2) {
		ksort($vals);
		$runs = [];
		$runType = null;
		$runDates = [];
		foreach ($vals as $dt => $val) {
			$type = (!Util::isBlank($val) && (float)$val >= (float)$threshold) ? 'wet' : 'dry';
			if ($runType !== null && $type !== $runType) {
				$runs[] = self::makeSpell($runType, $runDates, false);
				$runDates = [];
			}
			$runType = $type;
			$runDates[] = $dt;
		}
		if ($runType !== null && count($runDates)) {
			$runs[] = self::makeSpell($runType, $runDates, true);
		}
		return $runs;
	}

	public static function makeSpell($type, $dates, $ongoing) {
		$length = count($dates);
		$midpointIdx = $length - 1 - (int)floor($length / 2);
		return [
			'type' => $type,
			'length' => $length,
			'startDate' => $dates[0],
			'endDate' => $dates[$length - 1],
			'midpointDate' => $dates[$midpointIdx],
			'ongoing' => (bool)$ongoing,
		];
	}

	public static function keepLonger(&$record, $candidate) {
		if ($record === null || $candidate['length'] > $record['length']) {
			$record = $candidate;
		}
	}

	public static function keepTop(&$list, $candidate, $n) {
		$list[] = $candidate;
		usort($list, function ($a, $b) {
			if ($a['length'] !== $b['length']) {
				return ($a['length'] > $b['length']) ? -1 : 1;
			}
			return strcmp($a['startDate'], $b['startDate']);
		});
		if (count($list) > $n) {
			$list = array_slice($list, 0, $n);
		}
	}

	/**
	 * Universal logarithmic colour level for spell length (0–9).
	 * Log scaling keeps short spells distinguishable without letting very long
	 * spells immediately saturate the shared green gradient.
	 */
	public static function lengthColourLevel($days) {
		$days = max(1, (int)$days);
		$ratio = min(1, log($days) / log(365));
		return min(9, max(0, (int)floor($ratio * 10)));
	}

	/**
	 * Top-N longest spells for a variable / threshold / direction.
	 *
	 * @param string $varName
	 * @param float $threshold
	 * @param string $direction 'above'|'below'
	 * @param int $startYear
	 * @param int $limit
	 * @param int $month 0 = all; 1–12 = midpoint month filter
	 * @return array spells, total, current, startYear
	 */
	public static function rankLongest($varName, $threshold, $direction, $startYear, $limit, $month = 0) {
		$vals = self::loadDailySeries($varName, $startYear);
		$varStart = isset(Wx::$daily[$varName]['start_year'])
			? (int)Wx::$daily[$varName]['start_year'] : Site::BASE_YEAR;
		$effStart = max((int)$startYear, $varStart);

		if (self::isAnomalyDir($direction)) {
			return self::rankAnomaly($varName, $vals, (float)$threshold, $direction, $effStart, $limit, $month);
		}

		$opts = [];
		if ($varName === 'rain' && $direction === 'below') {
			$opts['blankAsBelow'] = true;
		}
		if (self::strictAbove($varName, $threshold)) {
			$opts['strict'] = true;
		}

		if ($varName === 'rain') {
			$all = self::findWetDryRuns($vals, $threshold);
			$want = ($direction === 'above') ? 'wet' : 'dry';
			$runs = [];
			foreach ($all as $spell) {
				if ($spell['type'] === $want) {
					$spell['type'] = $direction;
					$runs[] = $spell;
				}
			}
		} else {
			$runs = self::findRuns($vals, $threshold, $direction, $opts);
		}

		$month = (int)$month;
		if ($month >= 1 && $month <= 12) {
			$filtered = [];
			foreach ($runs as $spell) {
				if ((int)substr($spell['midpointDate'], 5, 2) === $month) {
					$filtered[] = $spell;
				}
			}
			$runs = $filtered;
		}

		$top = [];
		foreach ($runs as $spell) {
			self::keepTop($top, $spell, (int)$limit);
		}

		$current = null;
		if (count($vals)) {
			$allForCurrent = ($varName === 'rain')
				? self::findWetDryRuns($vals, $threshold)
				: self::findRuns($vals, $threshold, $direction, $opts);
			if (count($allForCurrent)) {
				$last = $allForCurrent[count($allForCurrent) - 1];
				$wantType = ($varName === 'rain')
					? (($direction === 'above') ? 'wet' : 'dry')
					: $direction;
				if ($last['type'] === $wantType && !empty($last['ongoing'])) {
					$last['type'] = $direction;
					$current = $last;
				}
			}
		}

		return [
			'spells' => $top,
			'total' => count($runs),
			'current' => $current,
			'startYear' => $effStart,
		];
	}

	/**
	 * Heatwave / cold snap ranking: runs of days each beyond normal ± X.
	 * Blank days (or days without a normal) break a spell. Each spell also
	 * carries its mean and peak anomaly, its mean value, and its peak value (highest in a
 * heatwave, lowest in a cold snap) and that day's date.
	 * @return array same shape as rankLongest
	 */
	private static function rankAnomaly($varName, $vals, $x, $direction, $effStart, $limit, $month) {
		$anom = self::anomalySeries($varName, $vals);
		$hot = ($direction === 'heatwave');
		$runs = [];
		$dates = [];
		$sum = 0.0;
		$peak = null;
		$pkVal = null;
		$pkDate = null;
		$vsum = 0.0;
		$flush = function ($ongoing) use (&$runs, &$dates, &$sum, &$peak, &$pkVal, &$pkDate, &$vsum, $direction) {
			if (!count($dates)) { return; }
			$sp = self::makeSpell($direction, $dates, $ongoing);
			$sp['meanAnom'] = $sum / count($dates);
			$sp['peakAnom'] = $peak;
			$sp['meanVal'] = $vsum / count($dates);
			$sp['peakVal'] = $pkVal;
			$sp['peakDate'] = $pkDate;
			$runs[] = $sp;
			$dates = [];
			$sum = 0.0;
			$peak = null;
			$pkVal = null;
			$pkDate = null;
			$vsum = 0.0;
		};
		foreach ($anom as $ymd => $a) {
			$match = ($a !== null) && ($hot ? ($a > $x) : ($a < -$x));
			if ($match) {
				$dates[] = $ymd;
				$sum += $a;
				if ($peak === null || ($hot ? $a > $peak : $a < $peak)) { $peak = $a; }
				// Highest value in a heatwave, lowest in a cold snap (first day on ties).
				$v = (float)$vals[$ymd];
				$vsum += $v;
				if ($pkVal === null || ($hot ? $v > $pkVal : $v < $pkVal)) { $pkVal = $v; $pkDate = $ymd; }
			} else {
				$flush(false);
			}
		}
		$flush(true);

		// Ongoing = the run reaches the latest day in the series, or ends
		// yesterday while today has no qualifying value yet (still in progress).
		$keys = array_keys($anom);
		$lastKey = count($keys) ? $keys[count($keys) - 1] : null;
		$yest = date('Y-m-d', Date::mkdate((int)Date::$mon_yest, (int)Date::$day_yest, (int)Date::$yr_yest));
		$current = null;
		if (count($runs)) {
			$last = $runs[count($runs) - 1];
			$isCur = !empty($last['ongoing']) || $last['endDate'] === $yest;
			if ($isCur) {
				$last['ongoing'] = true;
				$last['endsYesterday'] = ($last['endDate'] === $yest && $lastKey !== $yest);
				$runs[count($runs) - 1] = $last;
				$current = $last;
			}
		}

		$month = (int)$month;
		if ($month >= 1 && $month <= 12) {
			$runs = array_values(array_filter($runs, function ($sp) use ($month) {
				return (int)substr($sp['midpointDate'], 5, 2) === $month;
			}));
		}
		// Longest first; ties broken by stronger mean anomaly, then earlier start.
		$sorted = $runs;
		usort($sorted, function ($a, $b) use ($hot) {
			if ($a['length'] !== $b['length']) { return ($a['length'] > $b['length']) ? -1 : 1; }
			$ma = $hot ? -$a['meanAnom'] : $a['meanAnom'];
			$mb = $hot ? -$b['meanAnom'] : $b['meanAnom'];
			if (abs($ma - $mb) > 1e-9) { return ($ma < $mb) ? -1 : 1; }
			return strcmp($a['startDate'], $b['startDate']);
		});
		return [
			'spells' => array_slice($sorted, 0, (int)$limit),
			'total' => count($runs),
			'current' => $current,
			'startYear' => $effStart,
			'all' => $runs,
		];
	}

	/**
	 * Sliding N-day windows of mean daily anomaly (value − normal), for the
	 * Ranked Periods "anomaly" summary. Same quality rule as
	 * Data::getPeriodWindows: every day in a window needs a value.
	 * @param float $x heatwave / cold snap threshold (°C from normal)
	 * @return array list of [meanAnom, startTs, endTs, allHot(0|1), allCold(0|1)]
	 */
	public static function periodAnomWindows($varName, $length, $startYear, $monthStart, $x) {
		$length = max(1, (int)$length);
		$monthStart = (int)$monthStart;
		$anom = self::anomalySeries($varName, self::loadDailySeries($varName, $startYear));
		$dates = array_keys($anom);
		$a = array_values($anom);
		$n = count($a);
		$out = [];
		if ($n < $length) { return $out; }
		$span = ($length - 1) * 86400;
		$blank = 0; $sum = 0.0; $hot = 0; $cold = 0;
		for ($i = 0; $i < $n; $i++) {
			$v = $a[$i];
			if ($v === null) { $blank++; } else {
				$sum += $v;
				if ($v > $x) { $hot++; }
				if ($v < -$x) { $cold++; }
			}
			$left = $i - $length + 1;
			if ($left > 0) {
				$o = $a[$left - 1];
				if ($o === null) { $blank--; } else {
					$sum -= $o;
					if ($o > $x) { $hot--; }
					if ($o < -$x) { $cold--; }
				}
			}
			if ($left < 0 || $blank !== 0) { continue; }
			$startTs = strtotime($dates[$left] . ' 12:00:00');
			if ($startTs === false) { continue; }
			$endTs = $startTs + $span;
			if ($dates[$i] !== date('Y-m-d', $endTs)) { continue; }
			if ($monthStart > 0 && (int)date('n', $startTs) !== $monthStart) { continue; }
			$out[] = [round($sum / $length, 4), $startTs, $endTs, ($hot === $length) ? 1 : 0, ($cold === $length) ? 1 : 0];
		}
		return $out;
	}

	/** Short chip labels for the spell directions, keyed above|below[|heatwave|coldsnap]. */
	public static function directionChipLabels($varName) {
		if ($varName === 'rain') { return ['above' => 'Wet', 'below' => 'Dry']; }
		$labels = ['above' => 'Above', 'below' => 'Below'];
		if (self::supportsAnomaly($varName)) {
			$labels['heatwave'] = 'Heatwave';
			$labels['coldsnap'] = 'Cold snap';
		}
		return $labels;
	}

	/** "normal + 5.0 °C" / "normal − 5.0 °C" phrase for anomaly rules. */
	public static function anomalyPhrase($direction, $x) {
		$txt = Wx::plainText(Wx::conv(abs((float)$x), Wx::AbsTemp, true));
		return ($direction === 'heatwave') ? ('> normal + ' . $txt) : ('< normal − ' . $txt);
	}

	/** Signed anomaly text, e.g. "+6.3 °C". */
	public static function anomText($a) {
		$a = (float)$a;
		$txt = Wx::plainText(Wx::conv(abs($a), Wx::AbsTemp, true));
		return (($a >= 0) ? '+' : '−') . $txt;
	}

	/** Comparison symbol shown for the rule, e.g. '≥' or '>' at a zero threshold. */
	public static function ruleSymbol($varName, $direction, $threshold) {
		if ($direction === 'heatwave') { return '>'; }
		if ($direction === 'coldsnap') { return '<'; }
		$strict = self::strictAbove($varName, $threshold);
		if ($direction === 'above') { return $strict ? '>' : '≥'; }
		return $strict ? '≤' : '<';
	}

	/** Human label for direction given a variable (unit sits on the threshold). */
	public static function directionLabel($varName, $direction, $threshold) {
		$thresh = nw3_is_numeric($threshold) ? (float)$threshold : 0;
		$unit = isset(Wx::$daily[$varName]['unit']) ? Wx::$daily[$varName]['unit'] : Wx::None;
		$threshTxt = Wx::plainText(Wx::conv($thresh, $unit, true));
		$sym = self::ruleSymbol($varName, $direction, $thresh);
		if (self::isAnomalyDir($direction)) {
			return (($direction === 'heatwave') ? 'Heatwaves' : 'Cold snaps')
				. ' (' . self::anomalyPhrase($direction, $thresh) . ')';
		}
		if ($varName === 'rain') {
			return ($direction === 'above')
				? 'Wet spells (' . $sym . ' ' . $threshTxt . ')'
				: 'Dry spells (' . $sym . ' ' . $threshTxt . ')';
		}
		return ($direction === 'above')
			? 'Above ' . $threshTxt
			: 'Below ' . $threshTxt;
	}
}
