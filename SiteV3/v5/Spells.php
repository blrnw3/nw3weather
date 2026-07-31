<?php
/**
 * Generic consecutive-day spell finder / ranker for any daily variable.
 *
 * A spell is a run of days matching a threshold rule:
 *   above → value >= threshold (blank days break the spell)
 *   below → value < threshold (blank days break the spell, except rain dry
 *            spells where blanks count as dry to match legacy)
 */
class Spells {

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
	public static function defaultThreshold($varName) {
		if (in_array($varName, ['rain', 'hrmax', '10max', 'ratemax', 'ratemean'], true)) {
			return 0.2;
		}
		if (in_array($varName, ['sunhr', 'wethr'], true)) {
			return 0.1;
		}
		if (in_array($varName, ['gust', 'wmax', 'wmean', 'w10max'], true)) {
			return 10;
		}
		if (in_array($varName, ['hail', 'thunder', 'fog'], true)) {
			return 0.5;
		}
		if (in_array($varName, ['sunhrp', 'wethrp', 'hmin', 'hmax', 'hmean'], true)) {
			return 50;
		}
		return 0;
	}

	/** Preset threshold chips for the ranking selector. */
	public static function thresholdPresets($varName) {
		$unit = isset(Wx::$daily[$varName]['unit']) ? Wx::$daily[$varName]['unit'] : Wx::None;
		if ($unit === Wx::Rain || $unit === Wx::RainRate) {
			$presets = [0.2, 1, 5, 10, 20];
		} elseif ($unit === Wx::Temperature || $unit === Wx::AbsTemp) {
			$presets = [-5, 0, 5, 10, 15, 20, 25, 30];
		} elseif ($unit === Wx::Hours) {
			$presets = [0.1, 1, 3, 6, 10];
		} elseif ($unit === Wx::Wind) {
			$presets = [5, 10, 15, 20, 30];
		} elseif ($unit === Wx::Percentage || $unit === Wx::Humidity) {
			$presets = [25, 50, 75, 90];
		} elseif ($unit === Wx::Pressure) {
			$presets = [990, 1000, 1010, 1020, 1030];
		} elseif ($unit === Wx::Days) {
			$presets = [0.5, 1];
		} else {
			$presets = [0, 1, 5, 10];
		}
		$def = self::defaultThreshold($varName);
		if (!in_array($def, $presets, true)) {
			array_unshift($presets, $def);
		}
		return $presets;
	}

	/**
	 * Whether a daily value is inside a spell for the given rule.
	 * @param mixed $val
	 * @param float $threshold
	 * @param string $direction 'above'|'below'
	 * @param array $opts optional: blankAsBelow (bool)
	 */
	public static function dayMatches($val, $threshold, $direction, $opts = []) {
		$blankAsBelow = !empty($opts['blankAsBelow']);
		if ($direction === 'above') {
			return !Util::isBlank($val) && (float)$val >= (float)$threshold;
		}
		if (Util::isBlank($val)) {
			return $blankAsBelow;
		}
		return (float)$val < (float)$threshold;
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

		$opts = [];
		if ($varName === 'rain' && $direction === 'below') {
			$opts['blankAsBelow'] = true;
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

	/** Human label for direction given a variable. */
	public static function directionLabel($varName, $direction, $threshold) {
		$thresh = is_numeric($threshold) ? (float)$threshold : 0;
		$unit = isset(Wx::$daily[$varName]['unit']) ? Wx::$daily[$varName]['unit'] : Wx::None;
		$threshTxt = Wx::plainText(Wx::conv($thresh, $unit, false));
		if ($varName === 'rain') {
			return ($direction === 'above')
				? 'Wet spells (≥ ' . $threshTxt . ')'
				: 'Dry spells (< ' . $threshTxt . ')';
		}
		return ($direction === 'above')
			? 'Above (≥ ' . $threshTxt . ')'
			: 'Below (< ' . $threshTxt . ')';
	}
}
