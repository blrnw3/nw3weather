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

	/** Short chip labels for the two spell directions, keyed above|below. */
	public static function directionChipLabels($varName) {
		return ($varName === 'rain')
			? ['above' => 'Wet', 'below' => 'Dry']
			: ['above' => 'Above', 'below' => 'Below'];
	}

	/** Comparison symbol shown for the rule, e.g. '≥' or '>' at a zero threshold. */
	public static function ruleSymbol($varName, $direction, $threshold) {
		$strict = self::strictAbove($varName, $threshold);
		if ($direction === 'above') { return $strict ? '>' : '≥'; }
		return $strict ? '≤' : '<';
	}

	/** Human label for direction given a variable (unit sits on the threshold). */
	public static function directionLabel($varName, $direction, $threshold) {
		$thresh = is_numeric($threshold) ? (float)$threshold : 0;
		$unit = isset(Wx::$daily[$varName]['unit']) ? Wx::$daily[$varName]['unit'] : Wx::None;
		$threshTxt = Wx::plainText(Wx::conv($thresh, $unit, true));
		$sym = self::ruleSymbol($varName, $direction, $thresh);
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
