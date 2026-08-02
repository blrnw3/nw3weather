<?php

class ViewDetailedData {

	private $group;
	private $groupName;
	private $conv;
	private $getAnom;

	private $letter;
	private $varMin;
	private $varMax;
	private $varMean;
	private $intradayVar;
	private $label;
	private $cssClass;
	private $type;
	private $superlativeLow;
	private $superlativeHigh;
	private $measureLabels;
	private $measureConvs;

	// summarize() outputs (associative arrays, not objects) — loaded via
	// DataSummarizer::summarizeCached() so detail pages avoid recomputing
	// ranks/spells/rolling windows on every request.
	private $minSum;
	private $maxSum;
	private $meanSum;
	private $extraSum;
	private $meanStartYear;

	// Legacy-shaped adapter arrays, built from the summaries above (replaces crontag globals)
	private $dat;
	private $datMM;
	private $datMMCurr; // current (incomplete) month summary, same shape as one month of datMM
	private $datSS;
	private $datSSanom;
	private $ranks;
	private $datToday;
	private $datYest;

	public static $periods = array('latest_7d','curr_month','latest_31d','curr_year','latest_365d','alltime','all_this_month','all_this_date');
	public static $measuresGeneric = array('Lowest Min','Highest Max','Highest Min','Lowest Max','Lowest Mean','Highest Mean','Averages','Mean','Avg Low','Avg High');

	public $periods_all;
	public $startYrReport;
	public static $periodCnt;

	// LTA daily-anomaly available for these (tmean resolved dynamically via LTA)
	private static $ltaDailyTypes = array('tmin', 'tmax', 'tmean', 'rain', 'sunhr');


	/**
	 * Detail pages always summarise from the station record (Site::BASE_YEAR).
	 *
	 * @param string $groupName temp|baro|wind|rain|hum|dew|sun
	 */
	function __construct($groupName) {
		$groups = [
			"temp" => [
				"name" => "Temperature",
				"unit" => Wx::Temperature,
				"var_min" => "tmin",
				"var_max" => "tmax",
				"var_mean" => "tmean",
				"superlativeLo" => "Coldest",
				"superlativeHi" => "Warmest",
				"letter" => "t",
				"class" => 14,
				"anomaly" => true,
				"chartGroup" => "temp",
				"measures" => ['Lowest Min', 'Highest Max', 'Highest Min', 'Lowest Max', 'Coldest Day', 'Warmest Day', 'Averages', 'Mean', 'Mean Minimum', 'Mean Maximum'],
			],
			"baro" => [
				"name" => "Pressure",
				"unit" => Wx::Pressure,
				"var_min" => "pmin",
				"var_max" => "pmax",
				"var_mean" => "pmean",
				"superlativeLo" => "Lowest",
				"superlativeHi" => "Highest",
				"letter" => "p",
				"class" => 16,
				"chartGroup" => "pres",
			],
			"wind" => [
				"name" => "Wind",
				"unit" => Wx::Wind,
				"var_min" => "gust",
				"var_max" => "wmax",
				"var_mean" => "wmean",
				"var_extra" => "w10max",
				"superlativeLo" => "Calmest",
				"superlativeHi" => "Windiest",
				"letter" => "w",
				"class" => 13,
				"chartGroup" => "wind",
				"measures" => ['Max Gust', 'Max Speed', 'Max 10-min Speed', 'Calmest Day', 'Windiest Day', 'Mean Speed'],
				// var_min/max/mean are gust/speed/mean here, not a low-high pair.
				"latestLabels" => ['min' => 'Max Gust', 'max' => 'Max Speed', 'mean' => 'Mean Speed'],
				"trendTypes" => ['wmean', 'gust', 'wmax', 'w10max'],
				"seasonCols" => [
					['label' => 'Mean Speed', 'stat' => 'mean'],
					['label' => 'Mean Gust', 'stat' => 'min'],
				],
				"rankDailyCols" => [
					['label' => 'Mean Speed', 'j' => 2],
					['label' => 'Max Gust', 'j' => 0, 'dpa' => -1],
					['label' => 'Max Speed', 'j' => 1],
				],
				"rankMonthlyCols" => [
					['label' => 'Mean Speed', 'j' => 2],
					['label' => 'Mean Gust', 'j' => 0],
				],
			],
			"rain" => [
				"name" => "Rain",
				"unit" => Wx::Rain,
				"var_min" => "10max",
				"var_max" => "hrmax",
				"var_mean" => "rain",
				"var_extra" => "wethr",
				"superlativeLo" => "Driest",
				"superlativeHi" => "Wettest",
				"letter" => "r",
				"class" => 12,
				"anomaly" => true,
				"anomPct" => true,
				"chartGroup" => "rain",
				"measures" => ['Total', 'Wettest Day', 'Rain Days', 'Max Hourly', 'Max 10-min', 'Wet Hours'],
				"measureConvs" => [Wx::Rain, Wx::Rain, Wx::Days, Wx::Rain, Wx::Rain, Wx::Hours],
				"rankDailyCols" => [
					['label' => 'Total', 'j' => 2, 'unit' => Wx::Rain],
					['label' => 'Wet Hours', 'j' => 3, 'unit' => Wx::Hours],
					['label' => 'Max Hourly', 'j' => 1, 'unit' => Wx::Rain],
					['label' => 'Max 10-min', 'j' => 0, 'unit' => Wx::Rain],
				],
				"rankMonthlyCols" => [
					['label' => 'Total', 'j' => 2, 'unit' => Wx::Rain],
					['label' => 'Wet Hours', 'j' => 3, 'unit' => Wx::Hours],
					['label' => 'Rain Days', 'j' => 4, 'unit' => Wx::Days],
				],
				"rankDailyHiOnly" => true,
				"seasonCols" => [
					['label' => 'Total', 'stat' => 'mean', 'unit' => Wx::Rain],
					['label' => 'Wet Hours', 'stat' => 'extra', 'unit' => Wx::Hours],
					['label' => 'Rain Days', 'stat' => 'rdays', 'unit' => Wx::Days],
				],
			],
			"sun" => [
				"name" => "Sunshine",
				"unit" => Wx::Hours,
				"var_min" => "sunhr",
				"var_max" => "sunhr",
				"var_mean" => "sunhr",
				"var_extra" => "sunhrp",
				"superlativeLo" => "Dullest",
				"superlativeHi" => "Sunniest",
				"letter" => "s",
				"class" => 11,
				"anomaly" => true,
				"anomPct" => true,
				"chartGroup" => "sun",
				"measures" => ['Total', 'Sunniest Day', 'Sun Days', 'Dullest Day', '% of Max'],
				"measureConvs" => [Wx::Hours, Wx::Hours, Wx::Days, Wx::Hours, Wx::Percentage],
				"rankDailyCols" => [
					['label' => 'Total', 'j' => 2, 'unit' => Wx::Hours],
					['label' => '% of Max', 'j' => 3, 'unit' => Wx::Percentage],
				],
				"rankMonthlyCols" => [
					['label' => 'Total', 'j' => 2, 'unit' => Wx::Hours],
					['label' => 'Sun Days', 'j' => 4, 'unit' => Wx::Days],
					['label' => '% of Max', 'j' => 3, 'unit' => Wx::Percentage],
				],
				"rankDailyHiOnly" => true,
				"seasonCols" => [
					['label' => 'Total', 'stat' => 'mean', 'unit' => Wx::Hours],
					['label' => 'Sun Days', 'stat' => 'sdays', 'unit' => Wx::Days],
					['label' => '% of Max', 'stat' => 'extra', 'unit' => Wx::Percentage],
				],
			],
			"hum" => [
				"name" => "Humidity",
				"unit" => Wx::Humidity,
				"var_min" => "hmin",
				"var_max" => "hmax",
				"var_mean" => "hmean",
				"superlativeLo" => "Least Humid",
				"superlativeHi" => "Most Humid",
				"letter" => "h",
				"class" => 10,
				"chartGroup" => "humi",
			],
			"dew" => [
				"name" => "Dew Point",
				"unit" => Wx::Temperature,
				"var_min" => "dmin",
				"var_max" => "dmax",
				"var_mean" => "dmean",
				"superlativeLo" => "Least Humid",
				"superlativeHi" => "Most Humid",
				"letter" => "d",
				"class" => 10,
				"chartGroup" => "dewp",
			],
		];
		$this->groupName = $groupName;
		$this->group = $groups[$groupName];
		$this->conv = $this->group["unit"];
		$this->getAnom = array_key_exists("anomaly", $this->group);
		$this->measureLabels = isset($this->group['measures']) ? $this->group['measures'] : self::$measuresGeneric;
		$this->measureConvs = isset($this->group['measureConvs']) ? $this->group['measureConvs'] : null;

		// Properties the render methods rely on
		$this->letter = $this->group["letter"];
		$this->varMin = $this->group["var_min"];
		$this->varMax = $this->group["var_max"];
		$this->varMean = $this->group["var_mean"];
		$this->intradayVar = isset(Wx::$mappingsToDailyDataKey[$this->letter])
			? Wx::$mappingsToDailyDataKey[$this->letter] : null;
		$this->label = $this->group["name"];
		$this->cssClass = "td12";
		$this->type = $groupName;
		$this->superlativeLow = $this->group["superlativeLo"];
		$this->superlativeHigh = $this->group["superlativeHi"];
		$this->startYrReport = Site::BASE_YEAR;

		// Load per-variable summarize() payloads from daily serialised cache
		// (filled by first request or cron warm). Deduplicate by variable name.
		$sumByVar = array();
		foreach (array($this->group['var_mean'], $this->group['var_min'], $this->group['var_max']) as $v) {
			if (!isset($sumByVar[$v])) {
				$sumByVar[$v] = DataSummarizer::summarizeCached($v, $this->startYrReport);
			}
		}
		if (!empty($this->group['var_extra']) && !isset($sumByVar[$this->group['var_extra']])) {
			$sumByVar[$this->group['var_extra']] = DataSummarizer::summarizeCached(
				$this->group['var_extra'], $this->startYrReport
			);
		}
		$this->meanSum = $sumByVar[$this->group['var_mean']];
		$this->minSum = $sumByVar[$this->group['var_min']];
		$this->maxSum = $sumByVar[$this->group['var_max']];
		$this->extraSum = !empty($this->group['var_extra'])
			? $sumByVar[$this->group['var_extra']] : null;
		$this->meanStartYear = DataSummarizer::resolveStartYear(
			$this->group['var_mean'], $this->startYrReport
		);

		$monthLabel = Date::$months[Date::$dmonth-1];
		$this->periods_all = [
			"today" => "Today",
			"yest" => "Yesterday",
			"latest_7d" => "7-day",
			"curr_month" => $monthLabel,
			"latest_31d" => "31-day",
			"curr_year" => "Year",
			"latest_365d" => "365-day",
			"alltime" => "Overall",
			"all_this_month" => $monthLabel . " (all)",
			"all_this_date" => Date::datefull(Date::$dday) . ' ' . Date::monthfull(Date::$dmonth),
			// record-period columns
			"7cum" => "7-day",
			"Ma" => "Monthly",
			"Mmr" => $monthLabel,
			"31cum" => "31-day",
			"Ya" => "Annual",
			"365cum" => "365-day",
		];
		self::$periodCnt = count(self::$periods);

		$this->buildAdapters();
	}

	/** Effective start year after flooring to the variable's own start_year. */
	public function effectiveStartYear() {
		return $this->meanStartYear;
	}

	/** Chart selector group(s) for this detail page's variable family. */
	private function chartGroups() {
		$want = isset($this->group['chartGroup']) ? $this->group['chartGroup'] : null;
		if ($want === null) { return Charts::selectableGroups(); }
		foreach (Charts::selectableGroups() as $g) {
			if ($g['id'] === $want) {
				// Wind daily/monthly trend selectors: mean speed + max gust only.
				if ($this->groupName === 'wind' && !empty($this->group['trendTypes'])) {
					$opts = [];
					foreach ($this->group['trendTypes'] as $t) {
						if (isset($g['options'][$t])) { $opts[$t] = $g['options'][$t]; }
					}
					if (count($opts)) {
						$g = $g;
						$g['options'] = $opts;
					}
				}
				return [$g];
			}
		}
		return Charts::selectableGroups();
	}

	/** Table header label; keep "% of Max" on one line, otherwise stack words. */
	private function measureHeader($label) {
		if (strpos($label, '% of Max') !== false) {
			return $label;
		}
		return str_ireplace(' ', '<br />', $label);
	}

	/** Rain/sun-style summable detail pages (totals, day counts, % anomalies). */
	private function isSummableDetail() {
		return $this->groupName === 'rain' || $this->groupName === 'sun';
	}

	/** Adapter key for wet/sun day counts, or null. */
	private function dayCountKey() {
		if ($this->groupName === 'rain') { return 'rdays'; }
		if ($this->groupName === 'sun') { return 'sdays'; }
		return null;
	}

	/**
	 * Row labels + value sources for Recent / Station Lifetime (and past-year monthly).
	 * Each values entry is a $dat[stat][rank]-shaped array (period keys, or month index keys).
	 */
	private function periodMeasureRows($dat) {
		$measures = $this->measureLabels;
		if ($this->groupName === 'wind') {
			return [
				$measures,
				[
					$dat['min'][1],   // Max Gust
					$dat['max'][1],   // Max Speed
					isset($dat['extra'][1]) ? $dat['extra'][1] : [], // Max 10-min
					$dat['mean'][0],  // Calmest Day
					$dat['mean'][1],  // Windiest Day
					$dat['mean'][2],  // Mean Speed
				],
			];
		}
		if ($this->groupName === 'rain') {
			return [
				$measures,
				[
					$dat['mean'][2],  // Total
					$dat['mean'][1],  // Wettest Day
					isset($dat['rdays'][2]) ? $dat['rdays'][2] : [], // Rain Days
					$dat['max'][1],   // Max Hourly
					$dat['min'][1],   // Max 10-min
					isset($dat['extra'][2]) ? $dat['extra'][2] : [], // Wet Hours
				],
			];
		}
		if ($this->groupName === 'sun') {
			return [
				$measures,
				[
					$dat['mean'][2],  // Total
					$dat['mean'][1],  // Sunniest Day
					isset($dat['sdays'][2]) ? $dat['sdays'][2] : [], // Sun Days
					$dat['mean'][0],  // Dullest Day
					isset($dat['extra'][2]) ? $dat['extra'][2] : [], // % of Max
				],
			];
		}
		return [
			$measures,
			[
				$dat['min'][0], $dat['max'][1], $dat['min'][1], $dat['max'][0],
				$dat['mean'][0], $dat['mean'][1], '---',
				$dat['mean'][2], $dat['min'][2], $dat['max'][2],
			],
		];
	}

	private function isSeparatorRow($label) {
		return ($label === '---' || $label === 'Averages');
	}

	/**
	 * Current-month cell values / day stamps / anomalies aligned with periodMeasureRows().
	 * @return array [values[], days[], anoms[]]
	 */
	private function periodMeasureCurrMonth($curr) {
		if ($this->groupName === 'wind') {
			$extra = isset($curr['extra']) ? $curr['extra'] : ['days' => [], 'anoms' => []];
			return [
				[
					isset($curr['min'][1]) ? $curr['min'][1] : null,
					isset($curr['max'][1]) ? $curr['max'][1] : null,
					isset($extra[1]) ? $extra[1] : null,
					isset($curr['mean'][0]) ? $curr['mean'][0] : null,
					isset($curr['mean'][1]) ? $curr['mean'][1] : null,
					isset($curr['mean'][2]) ? $curr['mean'][2] : null,
				],
				[
					isset($curr['min']['days'][1]) ? $curr['min']['days'][1] : null,
					isset($curr['max']['days'][1]) ? $curr['max']['days'][1] : null,
					isset($extra['days'][1]) ? $extra['days'][1] : null,
					isset($curr['mean']['days'][0]) ? $curr['mean']['days'][0] : null,
					isset($curr['mean']['days'][1]) ? $curr['mean']['days'][1] : null,
					null,
				],
				[
					isset($curr['min']['anoms'][1]) ? $curr['min']['anoms'][1] : null,
					isset($curr['max']['anoms'][1]) ? $curr['max']['anoms'][1] : null,
					isset($extra['anoms'][1]) ? $extra['anoms'][1] : null,
					isset($curr['mean']['anoms'][0]) ? $curr['mean']['anoms'][0] : null,
					isset($curr['mean']['anoms'][1]) ? $curr['mean']['anoms'][1] : null,
					isset($curr['mean']['anoms'][2]) ? $curr['mean']['anoms'][2] : null,
				],
			];
		}
		if ($this->groupName === 'rain') {
			$extra = isset($curr['extra']) ? $curr['extra'] : ['days' => [], 'anoms' => []];
			$rdays = isset($curr['rdays']) ? $curr['rdays'] : ['anoms' => []];
			return [
				[
					isset($curr['mean'][2]) ? $curr['mean'][2] : null,
					isset($curr['mean'][1]) ? $curr['mean'][1] : null,
					isset($rdays[2]) ? $rdays[2] : null,
					isset($curr['max'][1]) ? $curr['max'][1] : null,
					isset($curr['min'][1]) ? $curr['min'][1] : null,
					isset($extra[2]) ? $extra[2] : null,
				],
				[
					null,
					isset($curr['mean']['days'][1]) ? $curr['mean']['days'][1] : null,
					null,
					isset($curr['max']['days'][1]) ? $curr['max']['days'][1] : null,
					isset($curr['min']['days'][1]) ? $curr['min']['days'][1] : null,
					null,
				],
				[
					isset($curr['mean']['anoms'][2]) ? $curr['mean']['anoms'][2] : null,
					isset($curr['mean']['anoms'][1]) ? $curr['mean']['anoms'][1] : null,
					isset($rdays['anoms'][2]) ? $rdays['anoms'][2] : null,
					isset($curr['max']['anoms'][1]) ? $curr['max']['anoms'][1] : null,
					isset($curr['min']['anoms'][1]) ? $curr['min']['anoms'][1] : null,
					isset($extra['anoms'][2]) ? $extra['anoms'][2] : null,
				],
			];
		}
		if ($this->groupName === 'sun') {
			$extra = isset($curr['extra']) ? $curr['extra'] : ['days' => [], 'anoms' => []];
			$sdays = isset($curr['sdays']) ? $curr['sdays'] : ['anoms' => []];
			return [
				[
					isset($curr['mean'][2]) ? $curr['mean'][2] : null,
					isset($curr['mean'][1]) ? $curr['mean'][1] : null,
					isset($sdays[2]) ? $sdays[2] : null,
					isset($curr['mean'][0]) ? $curr['mean'][0] : null,
					isset($extra[2]) ? $extra[2] : null,
				],
				[
					null,
					isset($curr['mean']['days'][1]) ? $curr['mean']['days'][1] : null,
					null,
					isset($curr['mean']['days'][0]) ? $curr['mean']['days'][0] : null,
					null,
				],
				[
					isset($curr['mean']['anoms'][2]) ? $curr['mean']['anoms'][2] : null,
					null, // single-day total — no anomaly
					null,
					null,
					isset($extra['anoms'][2]) ? $extra['anoms'][2] : null,
				],
			];
		}
		return [
			[
				$curr['min'][0], $curr['max'][1], $curr['min'][1], $curr['max'][0],
				$curr['mean'][0], $curr['mean'][1], '---',
				$curr['mean'][2], $curr['min'][2], $curr['max'][2],
			],
			[
				$curr['min']['days'][0], $curr['max']['days'][1], $curr['min']['days'][1], $curr['max']['days'][0],
				$curr['mean']['days'][0], $curr['mean']['days'][1], null,
				null, null, null,
			],
			[
				$curr['min']['anoms'][0], $curr['max']['anoms'][1], $curr['min']['anoms'][1], $curr['max']['anoms'][0],
				$curr['mean']['anoms'][0], $curr['mean']['anoms'][1], null,
				$curr['mean']['anoms'][2], $curr['min']['anoms'][2], $curr['max']['anoms'][2],
			],
		];
	}

	// ---- Adapter construction: legacy-shaped arrays from summarize() output ----

	private function buildAdapters() {
		$statSums = ['min' => $this->minSum, 'max' => $this->maxSum, 'mean' => $this->meanSum];
		if ($this->extraSum !== null) {
			$statSums['extra'] = $this->extraSum;
		}

		// $this->dat[stat][rank][periodKey] (+ 'date'/'anom' suffix keys)
		$this->dat = ['min' => [[], [], []], 'max' => [[], [], []], 'mean' => [[], [], []]];
		if (isset($statSums['extra'])) {
			$this->dat['extra'] = [[], [], []];
		}
		$ndayMap = ['7cum' => 7, '31cum' => 31, '365cum' => 365];
		$recMap = ['Ma' => 'month_mean_alltime', 'Mmr' => 'month_mean_all_this_month', 'Ya' => 'year_mean_alltime'];

		foreach ($statSums as $stat => $sum) {
			$ps = $sum['period_summaries'];
			$varForStat = ($stat === 'min') ? $this->varMin
				: (($stat === 'max') ? $this->varMax
				: (($stat === 'extra') ? $this->group['var_extra'] : $this->varMean));
			$aggKey = $this->isSummableVar($varForStat) ? 'sum' : 'mean';
			foreach (self::$periods as $pk) {
				$s = isset($ps[$pk]) ? $ps[$pk] : null;
				$minVal = $this->cleanExtreme($s, 'min');
				$maxVal = $this->cleanExtreme($s, 'max');
				$minDate = $this->sval($s, 'minDate');
				$maxDate = $this->sval($s, 'maxDate');
				$this->dat[$stat][0][$pk] = $minVal;
				$this->dat[$stat][0][$pk . 'date'] = $this->fmtPeriodDate($minDate, $pk);
				$this->dat[$stat][1][$pk] = $maxVal;
				$this->dat[$stat][1][$pk . 'date'] = $this->fmtPeriodDate($maxDate, $pk);
				$this->dat[$stat][2][$pk] = $this->sval($s, $aggKey);
				if ($aggKey === 'sum') {
					// Keep daily mean too (Station Lifetime shows means for rain totals / wet hours).
					$meanVal = $this->sval($s, 'mean');
					$this->dat[$stat]['avg'][$pk] = $meanVal;
					if ($this->getAnom) {
						if ($stat === 'mean' && $this->isSummableDetail()) {
							$this->dat[$stat]['avg'][$pk . 'anom'] = $this->summableMeanPeriodAnom($pk, $meanVal, $this->varMean);
						} elseif ($stat === 'extra' && $this->groupName === 'rain') {
							$this->dat[$stat]['avg'][$pk . 'anom'] = $this->wetHoursMeanPeriodAnom($pk, $meanVal);
						}
					}
				}
				if ($this->getAnom) {
					// Rain/sun: no anomalies on single-day totals (wettest/sunniest day).
					// Sun: also no anomaly on dullest day.
					$skipMinAnom = ($this->groupName === 'sun' && $stat === 'mean');
					$skipMaxAnom = ($this->isSummableDetail() && $stat === 'mean');
					if (!$skipMinAnom) {
						$this->dat[$stat][0][$pk . 'anom'] = $this->eventDayAnom($varForStat, $minVal, $minDate);
					}
					if (!$skipMaxAnom) {
						$this->dat[$stat][1][$pk . 'anom'] = $this->eventDayAnom($varForStat, $maxVal, $maxDate);
					}
					$this->dat[$stat][2][$pk . 'anom'] = $this->periodAnom($s);
				}
			}
			foreach ($ndayMap as $rk => $d) {
				$lo = isset($ps[$d . 'd_lo_mean_alltime']) ? $ps[$d . 'd_lo_mean_alltime'] : null;
				$hi = isset($ps[$d . 'd_hi_mean_alltime']) ? $ps[$d . 'd_hi_mean_alltime'] : null;
				$this->dat[$stat][0][$rk] = $this->sval($lo, $aggKey);
				$this->dat[$stat][0][$rk . 'date'] = $this->sval($lo, 'endDateFmt');
				$this->dat[$stat][1][$rk] = $this->sval($hi, $aggKey);
				$this->dat[$stat][1][$rk . 'date'] = $this->sval($hi, 'endDateFmt');
			}
			foreach ($recMap as $rk => $psk) {
				$s = isset($ps[$psk]) ? $ps[$psk] : null;
				$this->dat[$stat][0][$rk] = $this->cleanExtreme($s, 'min');
				$this->dat[$stat][0][$rk . 'date'] = $this->fmtRecDate($this->sval($s, 'minDate'), $rk);
				$this->dat[$stat][1][$rk] = $this->cleanExtreme($s, 'max');
				$this->dat[$stat][1][$rk . 'date'] = $this->fmtRecDate($this->sval($s, 'maxDate'), $rk);
			}
		}

		$dayKey = $this->dayCountKey();
		if ($dayKey !== null) {
			$this->dat[$dayKey] = [2 => [], 'avg' => []];
			$ps = $this->meanSum['period_summaries'];
			foreach (self::$periods as $pk) {
				$s = isset($ps[$pk]) ? $ps[$pk] : null;
				$cnz = $this->sval($s, 'count_nonzero');
				$this->dat[$dayKey][2][$pk] = $cnz;
				$meanDays = $this->dayCountMeanForPeriod($pk, $s);
				$this->dat[$dayKey]['avg'][$pk] = $meanDays;
				if ($this->getAnom && $this->groupName === 'rain') {
					$this->dat[$dayKey][2][$pk . 'anom'] = $this->rainDaysPeriodAnom($pk, $cnz);
					$this->dat[$dayKey]['avg'][$pk . 'anom'] = $this->rainDaysMeanPeriodAnom($pk, $meanDays);
				}
			}
		}

		$this->buildTodayYest();
		$this->buildMonthly($statSums);
		$this->buildSeasonal($statSums);
		$this->buildRanks();
	}

	private function buildTodayYest() {
		$todMin = $this->minSum['period_summaries']['today'];
		$todMax = $this->maxSum['period_summaries']['today'];
		$todMean = $this->meanSum['period_summaries']['today'];
		$yMin = $this->minSum['period_summaries']['yest'];
		$yMax = $this->maxSum['period_summaries']['yest'];
		$yMean = $this->meanSum['period_summaries']['yest'];

		$todMinVal = $this->sval($todMin, 'val');
		$todMaxVal = $this->sval($todMax, 'val');
		$todMeanVal = $this->sval($todMean, 'val');
		$todMinTime = $this->sval($todMin, 'time');
		$todMaxTime = $this->sval($todMax, 'time');

		// Prefer live TODAY snapshot (matches legacy recentAvgsExtrms / $NOW)
		$liveKey = isset(Wx::$mappingsToDailyDataKey[$this->letter])
			? Wx::$mappingsToDailyDataKey[$this->letter] : null;
		if ($liveKey && is_array(Live::$NOW)) {
			if (isset(Live::$NOW['min'][$liveKey]) && Util::isNotBlank(Live::$NOW['min'][$liveKey])) {
				$todMinVal = Live::$NOW['min'][$liveKey];
			}
			if (isset(Live::$NOW['max'][$liveKey]) && Util::isNotBlank(Live::$NOW['max'][$liveKey])) {
				$todMaxVal = Live::$NOW['max'][$liveKey];
			}
			if (isset(Live::$NOW['mean'][$liveKey]) && Util::isNotBlank(Live::$NOW['mean'][$liveKey])) {
				$todMeanVal = Live::$NOW['mean'][$liveKey];
			}
			if (!empty(Live::$NOW['timeMin'][$liveKey])) {
				$todMinTime = Live::$NOW['timeMin'][$liveKey];
			}
			if (!empty(Live::$NOW['timeMax'][$liveKey])) {
				$todMaxTime = Live::$NOW['timeMax'][$liveKey];
			}
		}

		$this->datToday = [
			0 => [0 => $todMinVal, 1 => $todMaxVal, 2 => $todMeanVal],
			1 => [0 => $todMinTime, 1 => $todMaxTime, 2 => null],
			2 => [
				'min' => $this->dailyAnom($this->group['var_min'], $todMinVal, false),
				'max' => $this->dailyAnom($this->group['var_max'], $todMaxVal, false),
				'mean' => $this->dailyAnom($this->group['var_mean'], $todMeanVal, false),
			],
		];
		$this->datYest = [
			0 => ['min' => $this->sval($yMin, 'val'), 'max' => $this->sval($yMax, 'val'), 'mean' => $this->sval($yMean, 'val')],
			1 => [0 => $this->sval($yMin, 'time'), 1 => $this->sval($yMax, 'time'), 2 => null],
			2 => [
				'min' => $this->dailyAnom($this->group['var_min'], $this->sval($yMin, 'val'), true),
				'max' => $this->dailyAnom($this->group['var_max'], $this->sval($yMax, 'val'), true),
				'mean' => $this->dailyAnom($this->group['var_mean'], $this->sval($yMean, 'val'), true),
			],
		];
	}

	private function buildMonthly($statSums) {
		$this->datMM = ['min' => [], 'max' => [], 'mean' => []];
		$this->datMMCurr = ['min' => [], 'max' => [], 'mean' => []];
		if (isset($statSums['extra'])) {
			$this->datMM['extra'] = [];
			$this->datMMCurr['extra'] = [];
		}
		if ($this->groupName === 'rain') {
			$this->datMM['rdays'] = [];
			$this->datMMCurr['rdays'] = [];
		} elseif ($this->groupName === 'sun') {
			$this->datMM['sdays'] = [];
			$this->datMMCurr['sdays'] = [];
		}
		foreach ($statSums as $stat => $sum) {
			$ms = $sum['month_summaries'];
			$varForStat = ($stat === 'min') ? $this->varMin
				: (($stat === 'max') ? $this->varMax
				: (($stat === 'extra') ? $this->group['var_extra'] : $this->varMean));
			$aggKey = $this->isSummableVar($varForStat) ? 'sum' : 'mean';
			$collect = [0 => [], 1 => [], 2 => []];
			$days = [0 => [], 1 => []];
			$anoms = [0 => [], 1 => [], 2 => []];
			$rdaysCollect = [];
			$rdaysAnoms = [];
			// Last 12 complete months (exclude current incomplete month) — matches
			// pastYearAvgsExtrms display: dmonth-12 .. dmonth-1 (e.g. Jul25–Jun26).
			for ($offset = 1; $offset <= 12; $offset++) {
				$ts = Date::mkdate(Date::$dmonth - $offset, 15, Date::$dyear);
				$y = date('Y', $ts);
				$mZ = date('m', $ts);
				$mt = intval(date('n', $ts)) - 1;
				$s = isset($ms[$y][$mZ]) ? $ms[$y][$mZ] : null;
				$minVal = $this->cleanExtreme($s, 'min');
				$maxVal = $this->cleanExtreme($s, 'max');
				$minDate = $this->sval($s, 'minDate');
				$maxDate = $this->sval($s, 'maxDate');
				$collect[0][$mt] = $minVal;
				$collect[1][$mt] = $maxVal;
				$collect[2][$mt] = $this->sval($s, $aggKey);
				$days[0][$mt] = $this->dayOf($minDate);
				$days[1][$mt] = $this->dayOf($maxDate);
				if ($this->getAnom) {
					// Rain/sun: no anomalies on single-day totals (wettest/sunniest day of month).
					// Sun: also no anomaly on dullest day.
					$skipMinAnom = ($this->groupName === 'sun' && $stat === 'mean');
					$skipMaxAnom = ($this->isSummableDetail() && $stat === 'mean');
					if (!$skipMinAnom) {
						$anoms[0][$mt] = $this->eventDayAnom($varForStat, $minVal, $minDate);
					}
					if (!$skipMaxAnom) {
						$anoms[1][$mt] = $this->eventDayAnom($varForStat, $maxVal, $maxDate);
					}
					$anoms[2][$mt] = $this->periodAnom($s);
				}
				if ($stat === 'mean' && $this->isSummableDetail()) {
					$dayKey = $this->dayCountKey();
					$rdaysCollect[$mt] = $this->sval($s, 'count_nonzero');
					if ($this->getAnom && $this->groupName === 'rain') {
						$rdaysAnoms[$mt] = $this->rainDaysMonthAnom((int)date('n', $ts), $rdaysCollect[$mt]);
					}
				}
			}
			for ($rank = 0; $rank < 3; $rank++) {
				$numeric = array_filter($collect[$rank], 'is_numeric');
				$entry = [
					'extr' => [$numeric ? min($numeric) : null, $numeric ? max($numeric) : null],
					0 => $collect[$rank],
					2 => $anoms[$rank],
				];
				if ($rank < 2) {
					$entry[1] = $days[$rank];
				}
				$this->datMM[$stat][$rank] = $entry;
			}
			if ($stat === 'mean' && $this->isSummableDetail()) {
				$dayKey = $this->dayCountKey();
				$numeric = array_filter($rdaysCollect, 'is_numeric');
				$this->datMM[$dayKey][2] = [
					'extr' => [$numeric ? min($numeric) : null, $numeric ? max($numeric) : null],
					0 => $rdaysCollect,
					2 => $rdaysAnoms,
				];
			}

			// Current incomplete month (for trailing row in past-year table)
			$cy = (string)Date::$dyear;
			$cmZ = Util::zerolead(Date::$dmonth);
			$cs = isset($ms[$cy][$cmZ]) ? $ms[$cy][$cmZ] : null;
			$cMin = $this->cleanExtreme($cs, 'min');
			$cMax = $this->cleanExtreme($cs, 'max');
			$cMinDate = $this->sval($cs, 'minDate');
			$cMaxDate = $this->sval($cs, 'maxDate');
			$this->datMMCurr[$stat] = [
				0 => $cMin,
				1 => $cMax,
				2 => $this->sval($cs, $aggKey),
				'days' => [0 => $this->dayOf($cMinDate), 1 => $this->dayOf($cMaxDate)],
				'anoms' => $this->getAnom ? [
					0 => $this->eventDayAnom($varForStat, $cMin, $cMinDate),
					1 => ($this->isSummableDetail() && $stat === 'mean')
						? null : $this->eventDayAnom($varForStat, $cMax, $cMaxDate),
					2 => $this->periodAnom($cs),
				] : [0 => null, 1 => null, 2 => null],
			];
			if ($stat === 'mean' && $this->isSummableDetail()) {
				$cRdays = $this->sval($cs, 'count_nonzero');
				$this->datMMCurr[$this->dayCountKey()] = [
					2 => $cRdays,
					'anoms' => [2 => ($this->getAnom && $this->groupName === 'rain')
						? $this->rainDaysMonthAnom((int)Date::$dmonth, $cRdays) : null],
				];
			}
		}
	}

	private function buildSeasonal($statSums) {
		$this->datSS = ['min' => [], 'max' => [], 'mean' => []];
		$this->datSSanom = ['min' => [], 'max' => [], 'mean' => []];
		if (isset($statSums['extra'])) {
			$this->datSS['extra'] = [];
			$this->datSSanom['extra'] = [];
		}
		if ($this->groupName === 'rain') {
			$this->datSS['rdays'] = [];
			$this->datSSanom['rdays'] = [];
		} elseif ($this->groupName === 'sun') {
			$this->datSS['sdays'] = [];
			$this->datSSanom['sdays'] = [];
		}
		foreach ($statSums as $stat => $sum) {
			$ss = $sum['season_summaries'];
			$varForStat = ($stat === 'min') ? $this->varMin
				: (($stat === 'max') ? $this->varMax
				: (($stat === 'extra') ? $this->group['var_extra'] : $this->varMean));
			$aggKey = $this->isSummableVar($varForStat) ? 'sum' : 'mean';
			for ($i = 0; $i < 4; $i++) {
				$y = ($i + 1 < Date::$season || Date::$dmonth == 12) ? Date::$dyear : Date::$dyear - 1;
				$key = Date::$snames[$i] . '_' . $y;
				$s = isset($ss[$key]) ? $ss[$key] : null;
				$this->datSS[$stat][$i] = $this->sval($s, $aggKey);
				$this->datSSanom[$stat][$i] = ($this->getAnom) ? $this->periodAnom($s) : null;
				if ($stat === 'mean' && $this->isSummableDetail()) {
					$dayKey = $this->dayCountKey();
					$rdays = $this->sval($s, 'count_nonzero');
					$this->datSS[$dayKey][$i] = $rdays;
					$this->datSSanom[$dayKey][$i] = ($this->getAnom && $this->groupName === 'rain')
						? $this->anomFromNorm($rdays, LTA::getSeasonAnom('rdays', $i)) : null;
				}
			}
		}
	}

	private function buildRanks() {
		$this->ranks = [];
		$rankSums = [0 => $this->minSum, 1 => $this->maxSum, 2 => $this->meanSum];
		if ($this->extraSum !== null) {
			$rankSums[3] = $this->extraSum;
		}
		$typeMap = [
			'daily' => 'daily_alltime',
			'monthly' => 'month_mean_alltime',
			'dailyCM' => 'daily_all_this_month',
			'monthlyCM' => 'month_mean_all_this_month',
			'annual' => 'year_mean_alltime',
		];
		foreach ($rankSums as $j => $sum) {
			$r = $sum['ranks'];
			foreach ($typeMap as $typeOut => $typeIn) {
				$src = isset($r[$typeIn]) ? $r[$typeIn] : ['lo' => [], 'hi' => []];
				foreach ([0 => 'lo', 1 => 'hi'] as $hilo => $hiloName) {
					$list = isset($src[$hiloName]) ? $src[$hiloName] : [];
					$vals = [];
					$dates = [];
					for ($i = 1; $i <= count($list); $i++) {
						$e = $list[$i - 1];
						$vals[$i] = isset($e['val']) ? $e['val'] : null;
						$dates[$i] = $this->fmtRankDate(isset($e['dt']) ? $e['dt'] : null, $typeOut);
					}
					$this->ranks[$j][$typeOut][$hilo] = [0 => $vals, 1 => $dates];
				}
			}
		}
		// Day counts = monthly/annual count of wet/sunny days (j=4).
		if ($this->isSummableDetail() && isset($this->meanSum['ranks'])) {
			$r = $this->meanSum['ranks'];
			$countMap = [
				'monthly' => 'month_count_alltime',
				'monthlyCM' => 'month_count_all_this_month',
				'annual' => 'year_count_alltime',
			];
			foreach ($countMap as $typeOut => $typeIn) {
				$src = isset($r[$typeIn]) ? $r[$typeIn] : ['lo' => [], 'hi' => []];
				foreach ([0 => 'lo', 1 => 'hi'] as $hilo => $hiloName) {
					$list = isset($src[$hiloName]) ? $src[$hiloName] : [];
					$vals = [];
					$dates = [];
					for ($i = 1; $i <= count($list); $i++) {
						$e = $list[$i - 1];
						$vals[$i] = isset($e['val']) ? $e['val'] : null;
						$dates[$i] = $this->fmtRankDate(isset($e['dt']) ? $e['dt'] : null, $typeOut);
					}
					$this->ranks[4][$typeOut][$hilo] = [0 => $vals, 1 => $dates];
				}
			}
		}
	}

	// ---- small helpers ----

	private function sval($arr, $key) {
		return (is_array($arr) && isset($arr[$key])) ? $arr[$key] : null;
	}

	private function cleanExtreme($s, $key) {
		$v = $this->sval($s, $key);
		if ($v === null) return null;
		if ($v === PHP_INT_MAX || $v === -1 * PHP_INT_MAX) return null;
		return $v;
	}

	private function dayOf($dateStr) {
		if (!$dateStr) return null;
		if (preg_match('/^\d{4}-\d{2}-(\d{2})$/', $dateStr, $m)) {
			return intval($m[1]);
		}
		return null;
	}

	private function fmtDate($raw) {
		if ($raw === null || $raw === '') return '';
		$ts = $this->parseDateTs($raw);
		if ($ts === null) return (string) $raw;
		// Full date with Today/Yesterday (ranks, fallbacks)
		return Date::today(true, true, true, false, $ts);
	}

	/**
	 * Rank-table date labels (legacy TagGen):
	 *   daily     → full date / Today / Yesterday
	 *   monthly   → "Jul 2018", or red "Current" for this month
	 *   dailyCM   → "Day N, YYYY" / Today / Yesterday
	 *   monthlyCM → same as monthly
	 *   annual    → year, or red "Current" for this year
	 */
	private function fmtRankDate($raw, $type) {
		if ($raw === null || $raw === '') return '';
		if ($type === 'annual') {
			if (preg_match('/^(\d{4})/', (string)$raw, $m)) {
				return Date::today((int)$m[1], false, false, true);
			}
			return (string)$raw;
		}
		$ts = $this->parseDateTs($raw);
		if ($ts === null) return (string) $raw;
		if ($type === 'monthly' || $type === 'monthlyCM') {
			// Pass y/m (not tstamp) so "Current" matches any day in this month
			// (Date::today fills day-of-month from today when $day is false).
			return Date::today((int)date('Y', $ts), (int)date('n', $ts), false, true);
		}
		if ($type === 'dailyCM') {
			return Date::today(true, false, true, false, $ts);
		}
		return Date::today(true, true, true, false, $ts);
	}

	/**
	 * Period-aware date label matching legacy TagGen today() usage:
	 *   7d / 31d / curr month → day only ("20th"), or Today/Yesterday
	 *   year / 365d           → day + month ("6th Jan")
	 *   alltime               → full ("11th Feb 2012")
	 *   all this month        → "Day N, YYYY"
	 *   all this date         → year only
	 */
	private function fmtPeriodDate($raw, $pk) {
		if ($raw === null || $raw === '') return '';
		$ts = $this->parseDateTs($raw);
		if ($ts === null) return (string) $raw;
		switch ($pk) {
			case 'latest_7d':
			case 'latest_31d':
			case 'curr_month':
				return Date::today(false, false, true, false, $ts);
			case 'curr_year':
			case 'latest_365d':
				return Date::today(false, true, true, false, $ts);
			case 'all_this_month':
				return Date::today(true, false, true, false, $ts);
			case 'all_this_date':
				return Date::today(true, false, false, false, $ts);
			case 'alltime':
			default:
				return Date::today(true, true, true, false, $ts);
		}
	}

	private function parseDateTs($raw) {
		if ($raw === null || $raw === '') return null;
		if (is_numeric($raw) && (int)$raw > 100000) {
			return (int)$raw; // unix timestamp
		}
		if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
			return Date::dtStrToTs($raw);
		}
		$ts = strtotime($raw);
		return $ts ? $ts : null;
	}

	private function fmtRecDate($raw, $rk) {
		if (!$raw) return '';
		if ($rk === 'Ya') return (string) $raw;
		if (preg_match('/^(\d{4})-(\d{2})/', $raw, $m)) {
			$y = (int)$m[1];
			$mo = (int)$m[2];
			// Month/year means: "Jul 2018", or red "Current" for this month
			return Date::today($y, $mo, false, true);
		}
		return (string) $raw;
	}

	/** Wrap a date label for display under a value (italic, easier to scan). */
	private function dateHtml($label) {
		if ($label === null || $label === '') return '';
		return '<br /><span class="wx-date">' . $label . '</span>';
	}

	private function isSummableVar($varName) {
		return !empty(Wx::$daily[$varName]['summable']);
	}

	/** Period-summary anomaly: percent when group requests it, else absolute. */
	private function periodAnom($summary) {
		if (!$this->getAnom || !is_array($summary)) return null;
		if (!empty($this->group['anomPct']) && isset($summary['anom_pct']) && is_numeric($summary['anom_pct'])) {
			return $summary['anom_pct'];
		}
		return isset($summary['anom']) ? $summary['anom'] : null;
	}

	private function anomFromNorm($val, $norm) {
		if (!is_numeric($val) || !is_numeric($norm)) return null;
		if (!empty($this->group['anomPct'])) {
			return ($norm != 0) ? (($val - $norm) / $norm * 100) : null;
		}
		return $val - $norm;
	}

	private function rainDaysMonthAnom($month, $count) {
		if (!$this->getAnom || !is_numeric($count)) return null;
		$norm = LTA::getMonthlyAnom('rdays', $month);
		return $this->anomFromNorm($count, $norm);
	}

	/** Rough rain-day anomaly for calendar periods that map to a month/year. */
	private function rainDaysPeriodAnom($pk, $count) {
		if (!$this->getAnom || !is_numeric($count)) return null;
		if ($pk === 'curr_month') {
			$norm = LTA::getMonthlyAnom('rdays', (int)Date::$dmonth);
			if (is_numeric($norm)) {
				$dim = (int)date('t', Date::mkdate(Date::$dmonth, 15, Date::$dyear));
				$norm = $norm * ((int)Date::$dday / max(1, $dim));
			}
			return $this->anomFromNorm($count, $norm);
		}
		if ($pk === 'curr_year') {
			$yearNorm = LTA::getYearlyAnom('rdays');
			if (!is_numeric($yearNorm)) return null;
			$doy = (int)date('z', Date::mkdate(Date::$dmonth, Date::$dday, Date::$dyear)) + 1;
			return $this->anomFromNorm($count, $yearNorm * ($doy / 365.0));
		}
		return null;
	}

	/** % anomaly for a period mean of a summable var vs climate normal. */
	private function summableMeanPeriodAnom($pk, $meanVal, $ltaVar) {
		if (!$this->getAnom || !is_numeric($meanVal)) return null;
		if ($pk === 'alltime' || $pk === 'curr_year' || $pk === 'latest_365d') {
			$yearSum = LTA::getYearlyAnom($ltaVar);
			if (!is_numeric($yearSum)) return null;
			return $this->anomFromNorm($meanVal, $yearSum / 365.0);
		}
		if ($pk === 'all_this_month' || $pk === 'curr_month' || $pk === 'latest_31d') {
			$mon = LTA::getMonthlyAnom($ltaVar, (int)Date::$dmonth);
			$dim = (int)date('t', Date::mkdate(Date::$dmonth, 15, Date::$dyear));
			if (!is_numeric($mon)) return null;
			return $this->anomFromNorm($meanVal, $mon / max(1, $dim));
		}
		if ($pk === 'all_this_date') {
			$norm = LTA::getDailyAnom($ltaVar, (int)Date::$dmonth, (int)Date::$dday);
			return $this->anomFromNorm($meanVal, $norm);
		}
		if ($pk === 'latest_7d') {
			$norm = LTA::getRecentPeriodMeanAnom($ltaVar, 7);
			if (!is_numeric($norm)) return null;
			return $this->anomFromNorm($meanVal, $norm / 7.0);
		}
		return null;
	}

	/** Percentage of observed days with rain/sun in a period. */
	private function dayCountMeanForPeriod($pk, $summary) {
		$count = $this->sval($summary, 'count');
		$cnz = $this->sval($summary, 'count_nonzero');
		if (!is_numeric($count) || $count <= 0 || !is_numeric($cnz)) { return null; }
		return $cnz / $count * 100;
	}

	/** @deprecated use summableMeanPeriodAnom */
	private function rainMeanPeriodAnom($pk, $meanVal) {
		return $this->summableMeanPeriodAnom($pk, $meanVal, 'rain');
	}

	/** @deprecated use dayCountMeanForPeriod */
	private function rainDaysMeanForPeriod($pk, $summary) {
		return $this->dayCountMeanForPeriod($pk, $summary);
	}

	/** % anomaly for mean wet hours vs climate normal. */
	private function wetHoursMeanPeriodAnom($pk, $meanVal) {
		if (!$this->getAnom || !is_numeric($meanVal)) return null;
		if ($pk === 'alltime' || $pk === 'curr_year' || $pk === 'latest_365d') {
			$yearSum = LTA::getYearlyAnom('wethr');
			if (!is_numeric($yearSum)) return null;
			return $this->anomFromNorm($meanVal, $yearSum / 365.0);
		}
		if ($pk === 'all_this_month' || $pk === 'curr_month' || $pk === 'latest_31d') {
			$mon = LTA::getMonthlyAnom('wethr', (int)Date::$dmonth);
			$dim = (int)date('t', Date::mkdate(Date::$dmonth, 15, Date::$dyear));
			if (!is_numeric($mon)) return null;
			return $this->anomFromNorm($meanVal, $mon / max(1, $dim));
		}
		if ($pk === 'all_this_date') {
			$mon = LTA::getMonthlyAnom('wethr', (int)Date::$dmonth);
			$dim = (int)date('t', Date::mkdate(Date::$dmonth, 15, Date::$dyear));
			if (!is_numeric($mon)) return null;
			return $this->anomFromNorm($meanVal, $mon / max(1, $dim));
		}
		return null;
	}

	/** % anomaly for mean rain-days vs climate normal. */
	private function rainDaysMeanPeriodAnom($pk, $meanVal) {
		if (!$this->getAnom || !is_numeric($meanVal)) return null;
		if ($pk === 'alltime') {
			$yearNorm = LTA::getYearlyAnom('rdays');
			return $this->anomFromNorm($meanVal, $yearNorm);
		}
		if ($pk === 'all_this_month') {
			$norm = LTA::getMonthlyAnom('rdays', (int)Date::$dmonth);
			return $this->anomFromNorm($meanVal, $norm);
		}
		if ($pk === 'all_this_date') {
			$norm = LTA::getMonthlyAnom('rdays', (int)Date::$dmonth);
			$dim = (int)date('t', Date::mkdate(Date::$dmonth, 15, Date::$dyear));
			if (!is_numeric($norm)) return null;
			return $this->anomFromNorm($meanVal, $norm / max(1, $dim));
		}
		return null;
	}

	private function dailyAnom($varName, $val, $yest) {
		if (!$this->getAnom || $val === null || !is_numeric($val)) return null;
		if (!in_array($varName, self::$ltaDailyTypes)) return null;
		$m = $yest ? Date::$mon_yest : Date::$dmonth;
		$d = $yest ? Date::$day_yest : Date::$dday;
		$y = $yest ? Date::$yr_yest : Date::$dyear;
		$norm = LTA::getDailyAnom($varName, $m, $d, $y);
		return $this->anomFromNorm($val, $norm);
	}

	/** Anomaly of an extreme value vs daily LTA on the event date (Y-m-d or day-of-month). */
	private function eventDayAnom($varName, $val, $dateRaw) {
		if (!$this->getAnom || $val === null || !is_numeric($val) || !$dateRaw) return null;
		if (!in_array($varName, self::$ltaDailyTypes)) return null;
		$m = null; $d = null; $y = null;
		if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $dateRaw, $mm)) {
			$y = (int)$mm[1]; $m = (int)$mm[2]; $d = (int)$mm[3];
		} elseif (is_numeric($dateRaw)) {
			$m = (int)Date::$dmonth;
			$d = (int)$dateRaw;
			$y = (int)Date::$dyear;
		} else {
			$ts = strtotime($dateRaw);
			if ($ts) {
				$y = (int)date('Y', $ts); $m = (int)date('n', $ts); $d = (int)date('j', $ts);
			}
		}
		if (!$m || !$d) return null;
		$norm = LTA::getDailyAnom($varName, $m, $d, $y ?: Date::$dyear);
		return $this->anomFromNorm($val, $norm);
	}

	/** Display a value, or '-' when null. */
	private function disp($val, $conv = null, $dpa = 0) {
		if ($val === null) return '-';
		return Wx::conv($val, $conv === null ? $this->conv : $conv, true, false, $dpa);
	}

	private function measureConv($r) {
		if ($this->measureConvs !== null && isset($this->measureConvs[$r])) {
			return $this->measureConvs[$r];
		}
		return $this->conv;
	}

	/**
	 * Extra decimal adjustment for Wx::conv. Daily max gusts are whole mph;
	 * monthly+ means of gust stay at the Wind unit's 1 dp.
	 */
	private function measureDpa($label) {
		if ($this->groupName === 'wind' && $label === 'Max Gust') {
			return -1;
		}
		return 0;
	}

	/** Render an anomaly hint like " (+1.2)" / " (+12%)" or '' when unavailable. */
	private function anomHint($val) {
		if (!$this->getAnom || $val === null || !is_numeric($val)) return '';
		if (!empty($this->group['anomPct'])) {
			$sign = ($val > 0) ? '+' : '';
			return '<br />(' . $sign . round($val) . '%)';
		}
		return '<br />(' . Wx::conv($val, Wx::AbsTemp, 0, 1) . ')';
	}

	// ---- rendering ----

	/**
	 * Makes a "current/latest" table
	 * @param array $measures names of vars
	 * @param array $values vars
	 * @param array $convs conv types
	 */
	public function currentLatest($measures, $values, $convs) {
		$cnt = count($measures);
		echo "<h2>Current/Latest conditions</h2>";

		echo '<div class="detail-grid current-latest-grid">';
		echo ' <div class="kv-table">';
		for ($r = 0; $r < $cnt; $r++) {
			echo '<div class="' . Html::colcol($r) . '">';
			echo '<div>' . $measures[$r] . '</div>';
			echo '<div>' . Wx::conv($values[$r], $convs[$r]) . '</div>';
			echo '</div>';
		}
		echo ' </div>';
		echo '<div class="detail-graph">';
		if ($this->intradayVar !== null) {
			Charts::intraday(['num' => 1, 'ts' => 12], $this->intradayVar, ['height' => 300]);
		} elseif ($this->groupName !== 'sun') {
			Charts::dailySelectable(
				['mode' => 'daily', 'length' => 31, 'lta' => 1],
				[
					'height' => 260,
					'showHeading' => false,
					'groups' => $this->chartGroups(),
					'hideGroups' => true,
					'selectorBelow' => true,
				],
				null,
				$this->varMean
			);
		}
		echo '</div>';
		echo '</div>';

		$statLabels = !empty($this->group['latestLabels'])
			? $this->group['latestLabels']
			: ['min' => 'Low', 'max' => 'High', 'mean' => 'Mean'];
		$statDpa = [
			'min' => $this->measureDpa($statLabels['min']),
			'max' => $this->measureDpa($statLabels['max']),
			'mean' => $this->measureDpa($statLabels['mean']),
		];
		$data = [
			[
				'label' => "Today's " . $statLabels['min'],
				'value' => $this->disp($this->datToday[0][0], null, $statDpa['min']),
				'time' => $this->datToday[1][0],
				'anomaly' => $this->datToday[2]['min'],
			],
			[
				'label' => "Today's " . $statLabels['max'],
				'value' => $this->disp($this->datToday[0][1], null, $statDpa['max']),
				'time' => $this->datToday[1][1],
				'anomaly' => $this->datToday[2]['max'],
			],
			[
				'label' => "Today's " . $statLabels['mean'],
				'value' => $this->disp($this->datToday[0][2], null, $statDpa['mean']),
				'time' => null,
				'anomaly' => $this->datToday[2]['mean'],
			],
			[
				'label' => "Yesterday's " . $statLabels['min'],
				'value' => $this->disp($this->datYest[0]['min'], null, $statDpa['min']),
				'time' => $this->datYest[1][0],
				'anomaly' => $this->datYest[2]['min'],
			],
			[
				'label' => "Yesterday's " . $statLabels['max'],
				'value' => $this->disp($this->datYest[0]['max'], null, $statDpa['max']),
				'time' => $this->datYest[1][1],
				'anomaly' => $this->datYest[2]['max'],
			],
			[
				'label' => "Yesterday's " . $statLabels['mean'],
				'value' => $this->disp($this->datYest[0]['mean'], null, $statDpa['mean']),
				'time' => null,
				'anomaly' => $this->datYest[2]['mean'],
			],
		];
		if ($this->groupName === 'rain') {
			$seasIdx = (int)Date::$season - 1;
			$seasName = isset(Date::$snames[$seasIdx]) ? Date::$snames[$seasIdx] : 'Season';
			$yestRain = isset($this->datYest[0]['mean']) ? $this->datYest[0]['mean'] : null;
			$yestWet = Data::get('wethr', Date::$yr_yest, Date::$mon_yest, Date::$day_yest);
			if (Util::isBlank($yestWet)) {
				// Dry days are often untagged for wet hours — treat as 0 when rain was 0.
				$yestWet = (is_numeric($yestRain) && (float)$yestRain == 0.0) ? 0 : null;
			}
			$seasTot = isset($this->datSS['mean'][$seasIdx]) ? $this->datSS['mean'][$seasIdx] : null;
			$seasAnom = isset($this->datSSanom['mean'][$seasIdx]) ? $this->datSSanom['mean'][$seasIdx] : null;
			$monthTot = isset($this->dat['mean'][2]['curr_month']) ? $this->dat['mean'][2]['curr_month'] : null;
			$monthAnom = isset($this->dat['mean'][2]['curr_monthanom']) ? $this->dat['mean'][2]['curr_monthanom'] : null;
			$yearTot = isset($this->dat['mean'][2]['curr_year']) ? $this->dat['mean'][2]['curr_year'] : null;
			$yearAnom = isset($this->dat['mean'][2]['curr_yearanom']) ? $this->dat['mean'][2]['curr_yearanom'] : null;
			$data = [
				[
					'label' => "Yesterday's Rain",
					'value' => $this->disp($yestRain, Wx::Rain),
					'time' => null,
					'anomaly' => null, // single-day total — no anomaly
				],
				[
					'label' => "Yesterday's Wet Hours",
					'value' => $this->disp($yestWet, Wx::Hours),
					'time' => null,
					'anomaly' => null,
				],
				[
					'label' => 'Monthly Rain',
					'value' => $this->disp($monthTot, Wx::Rain),
					'time' => null,
					'anomaly' => $monthAnom,
				],
				[
					'label' => 'Annual Rain',
					'value' => $this->disp($yearTot, Wx::Rain),
					'time' => null,
					'anomaly' => $yearAnom,
				],
				[
					'label' => $seasName . ' Total',
					'value' => $this->disp($seasTot, Wx::Rain),
					'time' => null,
					'anomaly' => $seasAnom,
				],
				[
					'label' => 'Current Spell',
					'value' => $this->currentSpellText(),
					'time' => null,
					'anomaly' => null,
				],
			];
		} elseif ($this->groupName === 'sun') {
			$seasIdx = (int)Date::$season - 1;
			$seasName = isset(Date::$snames[$seasIdx]) ? Date::$snames[$seasIdx] : 'Season';
			$yestSun = isset($this->datYest[0]['mean']) ? $this->datYest[0]['mean'] : null;
			$yestPct = Data::get('sunhrp', Date::$yr_yest, Date::$mon_yest, Date::$day_yest);
			$seasTot = isset($this->datSS['mean'][$seasIdx]) ? $this->datSS['mean'][$seasIdx] : null;
			$seasAnom = isset($this->datSSanom['mean'][$seasIdx]) ? $this->datSSanom['mean'][$seasIdx] : null;
			$monthTot = isset($this->dat['mean'][2]['curr_month']) ? $this->dat['mean'][2]['curr_month'] : null;
			$monthAnom = isset($this->dat['mean'][2]['curr_monthanom']) ? $this->dat['mean'][2]['curr_monthanom'] : null;
			$yearTot = isset($this->dat['mean'][2]['curr_year']) ? $this->dat['mean'][2]['curr_year'] : null;
			$yearAnom = isset($this->dat['mean'][2]['curr_yearanom']) ? $this->dat['mean'][2]['curr_yearanom'] : null;
			$data = [
				[
					'label' => "Yesterday's Sun",
					'value' => $this->disp($yestSun, Wx::Hours),
					'time' => null,
					'anomaly' => null,
				],
				[
					'label' => "Yesterday's % of Max",
					'value' => $this->disp($yestPct, Wx::Percentage),
					'time' => null,
					'anomaly' => null,
				],
				[
					'label' => 'Monthly Sun',
					'value' => $this->disp($monthTot, Wx::Hours),
					'time' => null,
					'anomaly' => $monthAnom,
				],
				[
					'label' => 'Annual Sun',
					'value' => $this->disp($yearTot, Wx::Hours),
					'time' => null,
					'anomaly' => $yearAnom,
				],
				[
					'label' => $seasName . ' Total',
					'value' => $this->disp($seasTot, Wx::Hours),
					'time' => null,
					'anomaly' => $seasAnom,
				],
			];
		}
		echo '<div class="detail-grid current-latest-grid">';
		echo ' <div class="kv-table">';
		foreach ($data as $r => $row) {
			echo '<div class="' . Html::colcol($r) . '">';
			echo '<div>' . $row['label'] . '</div>';
			$val = $row['value'];
			if ($row['time']) {
				$val .= ' @ ' . $row['time'];
			}
			if ($row['anomaly'] !== null && is_numeric($row['anomaly'])) {
				if (!empty($this->group['anomPct'])) {
					$av = $row['anomaly'];
					$val .= '&nbsp; (' . (($av > 0) ? '+' : '') . round($av) . '%)';
				} else {
					$val .= '&nbsp; (' . Wx::conv($row['anomaly'], Wx::AbsTemp, 0, 1) . ')';
				}
			}
			echo '<div>' . $val . '</div>';
			echo '</div>';
		}
		echo ' </div>';

		echo ' <div class="detail-graph">';
		if ($this->intradayVar !== null) {
			Charts::dailySelectable(
				['mode' => 'daily', 'length' => 31],
				[
					'height' => 350,
					'showHeading' => false,
					'groups' => $this->chartGroups(),
					'hideGroups' => true,
					'selectorBelow' => true,
				],
				null,
				$this->varMean
			);
		} elseif ($this->groupName === 'sun') {
			Charts::daily(
				['type' => 'sunhr', 'mode' => 'daily', 'length' => 31],
				['height' => 350]
			);
		}
		echo ' </div>';
		echo '</div>';

		if ($this->isSummableDetail()) {
			Charts::dailySelectable(
				['mode' => 'monthly', 'length' => 12, 'lta' => 1],
				[
					'height' => 350,
					'headingPrefix' => 'Last 12 months: ',
					'groups' => $this->chartGroups(),
					'hideGroups' => true,
				],
				null,
				$this->varMean
			);
		}
	}

	/** Plain-text current wet/dry spell for the live conditions table. */
	private function currentSpellText() {
		$spell = (isset($this->meanSum['spells']['current']) && is_array($this->meanSum['spells']['current']))
			? $this->meanSum['spells']['current'] : null;
		if (!$spell || empty($spell['length'])) { return '-'; }
		$n = (int)$spell['length'];
		$type = strtolower($spell['type']);
		return $n . ' ' . $type . ' day' . ($n === 1 ? '' : 's');
	}

	/**
	 * Rain-only wet/dry spell summary.
	 */
	public function rainSpells($wid = 85) {
		if ($this->groupName !== 'rain' || !isset($this->meanSum['spells'])) { return; }
		$spells = $this->meanSum['spells'];
		$lifetimeLabel = 'Station lifetime';

		echo '<h2>Wet and Dry Spells</h2>';

		Html::table(null, $wid . '%" align="center', 6);
		Html::tr();
		Html::td('Period', $this->cssClass, '24%');
		Html::td('Longest Wet Spell', $this->cssClass, '38%');
		Html::td('Longest Dry Spell', $this->cssClass, '38%');
		Html::tr_end();

		$rows = [
			['Current month', 'current_month'],
			['Current year', 'current_year'],
			[$lifetimeLabel, 'alltime'],
			[Date::$monthname . ' (all years)', 'alltime_current_month'],
		];
		foreach ($rows as $i => $row) {
			$scope = isset($spells[$row[1]]) ? $spells[$row[1]] : [];
			Html::tr(Html::colcol($i));
			Html::td($row[0], $this->cssClass);
			Html::td($this->spellCell(isset($scope['wet']) ? $scope['wet'] : null), $this->cssClass);
			Html::td($this->spellCell(isset($scope['dry']) ? $scope['dry'] : null), $this->cssClass);
			Html::tr_end();
		}
		Html::table_end();

		$this->rainSpellTop10();
	}

	/** Top-10 longest wet and dry spells of all time. */
	private function rainSpellTop10() {
		$spells = $this->meanSum['spells'];
		$topWet = isset($spells['top']['wet']) ? $spells['top']['wet'] : [];
		$topDry = isset($spells['top']['dry']) ? $spells['top']['dry'] : [];
		if (!count($topWet) && !count($topDry)) { return; }

		echo '<h3>Longest Wet and Dry Spells</h3>';
		echo "<div class='detail-grid'>";

		echo '<div>';
		echo '<h4>Longest Wet Spells</h4>';
		Html::table('table1', '99%');
		Html::tr();
		Html::td('Rank', $this->cssClass);
		Html::td('Length', $this->cssClass);
		Html::td('Period', $this->cssClass);
		Html::tr_end();
		foreach ($topWet as $i => $spell) {
			Html::tr(Html::colcol($i));
			Html::td($i + 1, $this->cssClass);
			Html::td((int)$spell['length'] . ' day' . ((int)$spell['length'] === 1 ? '' : 's'), $this->cssClass);
			$end = !empty($spell['ongoing']) ? 'Current' : $this->spellDate($spell['endDate']);
			Html::td($this->spellDate($spell['startDate']) . ' - ' . $end, $this->cssClass);
			Html::tr_end();
		}
		Html::table_end();
		echo '</div>';

		echo '<div>';
		echo '<h4>Longest Dry Spells</h4>';
		Html::table('table1', '99%');
		Html::tr();
		Html::td('Rank', $this->cssClass);
		Html::td('Length', $this->cssClass);
		Html::td('Period', $this->cssClass);
		Html::tr_end();
		foreach ($topDry as $i => $spell) {
			Html::tr(Html::colcol($i));
			Html::td($i + 1, $this->cssClass);
			Html::td((int)$spell['length'] . ' day' . ((int)$spell['length'] === 1 ? '' : 's'), $this->cssClass);
			$end = !empty($spell['ongoing']) ? 'Current' : $this->spellDate($spell['endDate']);
			Html::td($this->spellDate($spell['startDate']) . ' - ' . $end, $this->cssClass);
			Html::tr_end();
		}
		Html::table_end();
		echo '</div>';

		echo '</div>';
	}

	private function spellCell($spell) {
		if (!is_array($spell) || empty($spell['length'])) { return '-'; }
		$end = !empty($spell['ongoing']) ? 'Current' : $this->spellDate($spell['endDate']);
		return '<b>' . (int)$spell['length'] . ' day'
			. ((int)$spell['length'] === 1 ? '' : 's') . '</b>'
			. $this->dateHtml($this->spellDate($spell['startDate']) . ' - ' . $end);
	}

	private function spellDate($raw) {
		$ts = strtotime($raw);
		return $ts ? date('jS M Y', $ts) : $raw;
	}

	function avgsExtrmsRecs($measures = null, $wid = 99) {
		echo "<h2>Averages, Extremes, and Records</h2>";
		$this->renderAvgsExtrmsRecsBody($measures, $wid);
	}

	/**
	 * Body of the avg/extremes/records section (Recent + Station Lifetime +
	 * record periods + rain spells).
	 */
	function renderAvgsExtrmsRecsBody($measures = null, $wid = 99) {
		$dat = $this->dat;
		// Wind/rain/sun (and any group with custom measures) always uses periodMeasureRows().
		// Other callers may still pass explicit labels with the classic value mapping.
		if ($measures === null || $this->groupName === 'wind' || $this->isSummableDetail()) {
			list($measures, $values) = $this->periodMeasureRows($dat);
		} else {
			$values = array(
				$dat['min'][0], $dat['max'][1], $dat['min'][1], $dat['max'][0],
				$dat['mean'][0], $dat['mean'][1], '---',
				$dat['mean'][2], $dat['min'][2], $dat['max'][2],
			);
		}
		$effStart = $this->effectiveStartYear();

		$splitOne = self::$periodCnt - 3;
		echo "<div class='detail-grid'>";

		echo "<div>";
		echo "<h3>Recent</h3>";

		echo '<div class="detail-table-scroll">';
		Html::table(null, $wid . '%" align="center', 6);
		Html::tr();
		Html::td($this->label, $this->cssClass, "10%");
		for ($h = 0; $h < $splitOne; $h++) {
			Html::td($this->periods_all[self::$periods[$h]], $this->cssClass, '18%');
		}
		Html::tr_end();

		for ($r = 0; $r < count($measures); $r++) {
			$sep = $this->isSeparatorRow($measures[$r]);
			Html::tr($sep ? 'table-top' : Html::colcol($r));
			Html::td($this->measureHeader($measures[$r]), $this->cssClass);

			for ($c = 0; $c < $splitOne; $c++) {
				if (!$sep) {
					$pk = self::$periods[$c];
					$anom = $this->anomHint(isset($values[$r][$pk . 'anom']) ? $values[$r][$pk . 'anom'] : null);
					$date = $this->dateHtml(isset($values[$r][$pk . 'date']) ? $values[$r][$pk . 'date'] : '');
					Html::td('<b>' . $this->disp(isset($values[$r][$pk]) ? $values[$r][$pk] : null, $this->measureConv($r), $this->measureDpa($measures[$r])) . '</b>' . $anom . $date, $this->cssClass);
				} else {
					Html::td('&nbsp;', $this->cssClass);
				}
			}
			Html::tr_end();
		}
		Html::table_end();
		echo '</div>';
		echo "</div>";

		echo "<div>";
		echo "<h3>Station Lifetime (" . $effStart . "-" . Date::$dyear . ")</h3>";

		echo '<div class="detail-table-scroll">';
		Html::table(null, $wid . '%" align="center', 6);
		Html::tr();
		Html::td($this->label, $this->cssClass, "10%");
		for ($h = $splitOne; $h < self::$periodCnt; $h++) {
			Html::td($this->periods_all[self::$periods[$h]], $this->cssClass, '18%');
		}
		Html::tr_end();

		for ($r = 0; $r < count($measures); $r++) {
			$sep = $this->isSeparatorRow($measures[$r]);
			Html::tr($sep ? 'table-top' : Html::colcol($r));
			// Rain/sun lifetime: show means instead of period totals.
			$rowLabel = $measures[$r];
			if ($this->isSummableDetail() && $measures[$r] === 'Total') {
				$rowLabel = 'Mean';
			}
			Html::td($this->measureHeader($rowLabel), $this->cssClass);

			for ($c = $splitOne; $c < self::$periodCnt; $c++) {
				$dpa = $this->measureDpa($measures[$r]);
				if (!$sep) {
					$pk = self::$periods[$c];
					$cell = isset($values[$r][$pk]) ? $values[$r][$pk] : null;
					$anomVal = isset($values[$r][$pk . 'anom']) ? $values[$r][$pk . 'anom'] : null;
					if ($this->groupName === 'rain') {
						if ($measures[$r] === 'Total' && isset($dat['mean']['avg'][$pk])) {
							$cell = $dat['mean']['avg'][$pk];
							$anomVal = isset($dat['mean']['avg'][$pk . 'anom'])
								? $dat['mean']['avg'][$pk . 'anom'] : $anomVal;
						} elseif ($measures[$r] === 'Wet Hours' && isset($dat['extra']['avg'][$pk])) {
							$cell = $dat['extra']['avg'][$pk];
							$anomVal = isset($dat['extra']['avg'][$pk . 'anom'])
								? $dat['extra']['avg'][$pk . 'anom'] : null;
							$dpa = 1;
						} elseif ($measures[$r] === 'Rain Days' && isset($dat['rdays']['avg'][$pk])) {
							$cell = $dat['rdays']['avg'][$pk];
							$anomVal = null;
						}
					} elseif ($this->groupName === 'sun') {
						if ($measures[$r] === 'Total' && isset($dat['mean']['avg'][$pk])) {
							$cell = $dat['mean']['avg'][$pk];
							$anomVal = isset($dat['mean']['avg'][$pk . 'anom'])
								? $dat['mean']['avg'][$pk . 'anom'] : $anomVal;
						} elseif ($measures[$r] === 'Sun Days' && isset($dat['sdays']['avg'][$pk])) {
							$cell = $dat['sdays']['avg'][$pk];
							$anomVal = null;
						}
					}
					$anom = $this->anomHint($anomVal);
					$date = $this->dateHtml(isset($values[$r][$pk . 'date']) ? $values[$r][$pk . 'date'] : '');
					$cellConv = $this->measureConv($r);
					if (($this->groupName === 'rain' && $measures[$r] === 'Rain Days')
						|| ($this->groupName === 'sun' && $measures[$r] === 'Sun Days')) {
						$cellConv = Wx::Percentage;
					}
					Html::td('<b>' . $this->disp($cell, $cellConv, $dpa) . '</b>' . $anom . $date, $this->cssClass);
				} else {
					Html::td('&nbsp;', $this->cssClass);
				}
			}
			Html::tr_end();
		}
		Html::table_end();
		echo '</div>';
		echo "</div>";
		echo "</div>";

		$this->recordPeriodAvgs();
		$this->rainSpells();
	}

	function pastYearAvgsExtrms($measures = null, $wid = 99) {
		// Daily trends: Highcharts via histdata.php (same stack as wx3), replacing
		// the legacy graph_daily_trend.php jpgraph images.
		$yr = (int)Date::$dyear;
		if ($this->groupName === 'rain') {
			echo '<h3>Cumulative rainfall vs recent extreme years (2014, 2011) and last year</h3>';
			echo '<div class="charts">';
			Charts::daily([
				'type' => 'rain',
				'mode' => 'daily',
				'year' => $yr,
				'month' => 0,
				'cume' => 1,
				'multiyr' => 'last,2014,2011',
				'lta' => 1,
			], ['height' => 470]);
			echo '</div>';
		} elseif ($this->groupName === 'sun') {
			echo '<h3>Cumulative sunshine vs last year and normal</h3>';
			echo '<div class="charts">';
			Charts::daily([
				'type' => 'sunhr',
				'mode' => 'daily',
				'year' => $yr,
				'month' => 0,
				'cume' => 1,
				'multiyr' => 'last',
				'lta' => 1,
			], ['height' => 470]);
			echo '</div>';
		} else {
			echo '<h3>Current year vs last year daily trends for ' . $this->label . '</h3>';
			echo '<div class="charts">';
			Charts::daily([
				'type' => $this->varMean,
				'mode' => 'daily',
				'year' => $yr,
				'month' => 0,
				'multiyr' => 'last',
				'lta' => 1,
			], ['height' => 420]);
			echo '</div>';
		}

		if ($this->groupName === 'wind') {
			// Wind: mean speed (above) + max gust only — no min/max speed pair.
			echo '<h3>This year daily max gust for ' . $this->label . '</h3>';
			echo '<div class="charts">';
			Charts::daily([
				'type' => $this->varMin, // gust
				'mode' => 'daily',
				'year' => $yr,
				'month' => 0,
				'lta' => 1,
			], ['height' => 350]);
			echo '</div>';
		} elseif (!$this->isSummableDetail()) {
			echo '<h3>This year min/max daily trends in detail for ' . $this->label . '</h3>';
			echo '<div class="detail-grid charts detail-chart-pair">';
			echo '<div>';
			Charts::daily([
				'type' => $this->varMin,
				'mode' => 'daily',
				'year' => $yr,
				'month' => 0,
				'lta' => 1,
			], ['height' => 350]);
			echo '</div><div>';
			Charts::daily([
				'type' => $this->varMax,
				'mode' => 'daily',
				'year' => $yr,
				'month' => 0,
				'lta' => 1,
			], ['height' => 350]);
			echo '</div></div>';
		}

		$dat = $this->datMM;
		if ($measures === null || $this->groupName === 'wind' || $this->isSummableDetail()) {
			list($measures, $values) = $this->periodMeasureRows($dat);
		} else {
			$values = array($dat['min'][0], $dat['max'][1], $dat['min'][1], $dat['max'][0], $dat['mean'][0], $dat['mean'][1], '---', $dat['mean'][2], $dat['min'][2], $dat['max'][2]);
		}

		$pastStart = Date::mkdate(Date::$dmonth - 12, 15, Date::$dyear);
		$currTs = Date::mkdate(Date::$dmonth, 15, Date::$dyear);
		echo "<h2>Past Year Monthly Averages and Extremes ("
			. date('F Y', $pastStart) . ' - ' . date('F Y', $currTs) . ")</h2>";
		echo '<div class="detail-table-scroll detail-table-wide">';
		Html::table(null, $wid . '%" align="center', 6);
		Html::tr();
		Html::td("Month", $this->cssClass);
		for ($r = 0; $r < count($measures); $r++) {
			if (!$this->isSeparatorRow($measures[$r])) {
				Html::td($this->measureHeader($measures[$r]), $this->cssClass);
			}
		}
		Html::tr_end();

		for ($m = 11; $m >= 0; $m--) {
			$ts = Date::mkdate(Date::$dmonth - $m - 1, 15, Date::$dyear);
			$mt = (int)date('n', $ts) - 1;
			Html::tr(Html::colcol($m));
			Html::td(Date::$months3[$mt] . ' ' . date('Y', $ts), $this->cssClass);

			for ($r = 0; $r < count($measures); $r++) {
				if ($this->isSeparatorRow($measures[$r])) { continue; }
				$cell = isset($values[$r][0][$mt]) ? $values[$r][0][$mt] : null;
				if ($cell !== null && isset($values[$r]['extr'][1]) && $cell == $values[$r]['extr'][1]) {
					$colour = ($this->isSummableDetail())
						? '" style="color:#0101DF' : '" style="color:#DF7401';
				} elseif ($cell !== null && isset($values[$r]['extr'][0]) && $cell == $values[$r]['extr'][0]) {
					$colour = ($this->isSummableDetail())
						? '" style="color:#DF7401' : '" style="color:#0101DF';
				} else {
					$colour = '';
				}
				$anom = $this->anomHint(isset($values[$r][2][$mt]) ? $values[$r][2][$mt] : null);
				if (array_key_exists(1, $values[$r]) && array_key_exists($mt, $values[$r][1]) && $values[$r][1][$mt] !== null) {
					$date = $this->dateHtml(Date::datefull($values[$r][1][$mt]));
				} else {
					$date = '';
				}
				Html::td('<b>' . $this->disp($cell, $this->measureConv($r), $this->measureDpa($measures[$r])) . '</b>' . $anom . $date, $this->cssClass . $colour);
			}
			Html::tr_end();
		}

		// Current (incomplete) month — distinct row style
		$curr = $this->datMMCurr;
		list($currVals, $currDays, $currAnoms) = $this->periodMeasureCurrMonth($curr);
		Html::tr('rowcurrent');
		Html::td(Date::$months3[Date::$dmonth - 1] . ' ' . Date::$dyear, $this->cssClass);
		for ($r = 0; $r < count($measures); $r++) {
			if ($this->isSeparatorRow($measures[$r])) { continue; }
			$cell = isset($currVals[$r]) ? $currVals[$r] : null;
			$anom = $this->anomHint(isset($currAnoms[$r]) ? $currAnoms[$r] : null);
			$date = (isset($currDays[$r]) && $currDays[$r] !== null)
				? $this->dateHtml(Date::datefull($currDays[$r])) : '';
			Html::td('<b>' . $this->disp($cell, $this->measureConv($r), $this->measureDpa($measures[$r])) . '</b>' . $anom . $date, $this->cssClass);
		}
		Html::tr_end();

		Html::table_end();
		echo '</div>';

		if ($this->groupName === 'sun') {
			echo '<p>View daily tables of
				<a href="/wxdataday.php?vartype=sunhr">sunshine hours</a>
				 / <a href="/wxdataday.php?vartype=sunhrp">% of max</a>
				 for the past year <br />View monthly tables of
				<a href="/TablesDataMonth.php?vartype=sunhr">sunshine hours</a>
				 / <a href="/TablesDataMonth.php?vartype=sunhrp">% of max</a>
				 for all months in the station history.
				</p>';
		} else {
			echo '<p>View daily tables of
				<a href="/wxdataday.php?vartype=' . $this->letter . 'min">min</a> /
				<a href="/wxdataday.php?vartype=' . $this->letter . 'max">max</a> /
				<a href="/wxdataday.php?vartype=' . $this->letter . 'mean">mean</a>
				 ' . $this->label . ' data for the past year <br />View monthly tables of
				<a href="/TablesDataMonth.php?vartype=' . $this->letter . 'min">min</a> /
				<a href="/TablesDataMonth.php?vartype=' . $this->letter . 'max">max</a> /
				<a href="/TablesDataMonth.php?vartype=' . $this->letter . 'mean">mean</a>
				 ' . $this->label . ' data for all months in the station history.
				</p>';
		}

		$this->seasonalAvgs();

		// Rain/sun already have a selectable 12-month chart near the top; other
		// detail pages keep the 24hr + 12-month pair here.
		if ($this->isSummableDetail()) {
			$recentLabel = ($this->intradayVar !== null) ? 'Past 24hrs' : 'Past 31 days';
			echo '<h3>' . $recentLabel . ' and current year cumulative trends for ' . $this->label . '</h3>';
			echo '<div class="detail-grid charts">';
			echo '<div>';
			if ($this->intradayVar !== null) {
				Charts::intraday(['num' => 1], $this->intradayVar, ['height' => 330]);
			} else {
				Charts::daily(
					['type' => $this->varMean, 'mode' => 'daily', 'length' => 31, 'lta' => 1],
					['height' => 330]
				);
			}
			echo '</div><div>';
			Charts::daily([
				'type' => $this->varMean,
				'mode' => 'daily',
				'year' => Date::$yr_yest,
				'month' => 0,
				'cume' => 1,
				'lta' => 1,
			], ['height' => 330]);
			echo '</div></div>';
		} else {
			echo '<h3>Past 24hrs and past 12 months trends for ' . $this->label . '</h3>';
			echo '<div class="detail-grid">';
			echo '<div>';
			if ($this->intradayVar !== null) {
				Charts::intraday(['num' => 1], $this->intradayVar, ['height' => 330]);
			} else {
				Charts::daily(
					['type' => $this->varMean, 'mode' => 'daily', 'length' => 31, 'lta' => 1],
					['height' => 330]
				);
			}
			echo '</div><div>';
			Charts::dailySelectable(
				['mode' => 'monthly', 'length' => 12, 'lta' => 1],
				[
					'height' => 330,
					'headingPrefix' => 'Last 12 months: ',
					'groups' => $this->chartGroups(),
					'hideGroups' => true,
				],
				null,
				$this->varMean
			);
			echo '</div></div>';
		}
		echo '<p><a href="/charts.php">View more ' . $this->label . ' charts</a></p>';
	}

	private function seasonalAvgs($wid = 75) {
		$dat = $this->datSS;
		$datAnom = $this->getAnom ? $this->datSSanom : [];

		$cols = !empty($this->group['seasonCols'])
			? $this->group['seasonCols']
			: [
				['label' => 'Daily Min', 'stat' => 'min'],
				['label' => 'Daily Max', 'stat' => 'max'],
				['label' => 'Mean', 'stat' => 'mean'],
			];

		echo "<h2>Past Year Seasonal Averages</h2>";
		Html::table(null, $wid . '%" align="center', 6, true);

		Html::tr();
		Html::td("Season", $this->cssClass, "22%");
		$colW = (int)(78 / max(1, count($cols))) . '%';
		foreach ($cols as $col) {
			Html::td($col['label'], $this->cssClass, $colW);
		}
		Html::tr_end();

		for ($i = 0; $i < 4; $i++) {
			$dfo1 = Date::$dyear - 2001;
			$dfo2 = Date::$dyear - 2000;
			$dfo3 = Date::$dyear - 2002;
			$nwint = ($i + 1 < Date::$season || Date::$dmonth == 12) ? Date::$dyear : Date::$dyear - 1;
			$wint = (Date::$dmonth > 2) ? $dfo1 . '/' . $dfo2 : $dfo3 . '/' . $dfo1;
			$yr3 = array($wint, $nwint, $nwint, $nwint);
			$hlite = ($i + 1 == Date::$season - 1) ? 'border-bottom:3px solid #8181F7' : '';

			Html::tr(Html::colcol($i));
			Html::td(Date::$snames[$i] . ' ' . $yr3[$i], $this->cssClass . '" style="' . $hlite);

			foreach ($cols as $col) {
				$sk = $col['stat'];
				$unit = isset($col['unit']) ? $col['unit'] : $this->conv;
				$anom = '';
				if ($this->getAnom && isset($datAnom[$sk][$i]) && is_numeric($datAnom[$sk][$i])) {
					if (!empty($this->group['anomPct'])) {
						$av = $datAnom[$sk][$i];
						$anom = ' (' . (($av > 0) ? '+' : '') . round($av) . '%)';
					} else {
						$anom = ' (' . Wx::conv($datAnom[$sk][$i], Wx::AbsTemp, 1, 1) . ')';
					}
				}
				$v = isset($dat[$sk][$i]) ? $dat[$sk][$i] : null;
				Html::td($this->disp($v, $unit) . $anom . '<br />', $this->cssClass . '" style="' . $hlite);
			}
			Html::tr_end();
		}

		Html::table_end();
	}

	private function recordPeriodAvgs($wid = 98) {
		$dat = $this->dat;
		$periods = array('7cum', 'Ma', 'Mmr', '31cum', 'Ya', '365cum');
		$rowConvs = null;

		if ($this->groupName === 'wind') {
			// Calmest / Windiest (mean speed) + highest period-mean of daily gust.
			$measures = array($this->superlativeLow, $this->superlativeHigh, 'Mean Gust');
			$values = array($dat['mean'][0], $dat['mean'][1], $dat['min'][1]);
		} elseif ($this->groupName === 'rain') {
			$measures = array('Wettest', 'Driest', 'Most Wet Hours', 'Fewest Wet Hours');
			$values = array(
				$dat['mean'][1],
				$dat['mean'][0],
				isset($dat['extra'][1]) ? $dat['extra'][1] : [],
				isset($dat['extra'][0]) ? $dat['extra'][0] : [],
			);
			$rowConvs = array(Wx::Rain, Wx::Rain, Wx::Hours, Wx::Hours);
		} elseif ($this->groupName === 'sun') {
			$measures = array('Sunniest', 'Dullest', 'Highest % of Max', 'Lowest % of Max');
			$values = array(
				$dat['mean'][1],
				$dat['mean'][0],
				isset($dat['extra'][1]) ? $dat['extra'][1] : [],
				isset($dat['extra'][0]) ? $dat['extra'][0] : [],
			);
			$rowConvs = array(Wx::Hours, Wx::Hours, Wx::Percentage, Wx::Percentage);
		} else {
			$measures = array($this->superlativeLow, $this->superlativeHigh, 'Lowest Mean Daily-Min', 'Highest Mean Daily-Min', 'Lowest Mean Daily-Max', 'Highest Mean Daily-Max');
			$values = array($dat['mean'][0], $dat['mean'][1], $dat['min'][0], $dat['min'][1], $dat['max'][0], $dat['max'][1]);
		}

		echo "<h2>Record Period Averages</h2>";
		echo '<div class="detail-table-scroll detail-table-wide">';
		Html::table(null, $wid . '%" style="margin-bottom:28px;', 6);

		Html::tr();
		Html::td("Measure", $this->cssClass, "8%");
		for ($h = 0; $h < count($periods); $h++) {
			Html::td($this->periods_all[$periods[$h]], $this->cssClass, '15%');
		}
		Html::tr_end();

		for ($r = 0; $r < count($measures); $r++) {
			Html::tr(Html::colcol($r));
			Html::td($this->measureHeader($measures[$r]), $this->cssClass);
			$conv = ($rowConvs !== null && isset($rowConvs[$r])) ? $rowConvs[$r] : $this->conv;

			for ($c = 0; $c < count($periods); $c++) {
				$pk = $periods[$c];
				$date = $this->dateHtml(isset($values[$r][$pk . 'date']) ? $values[$r][$pk . 'date'] : '');
				Html::td('<b>' . $this->disp(isset($values[$r][$pk]) ? $values[$r][$pk] : null, $conv) . '</b>' . $date, $this->cssClass);
			}
			Html::tr_end();
		}

		Html::table_end();
		echo '</div>';
	}

	private function rankTablePair($rankArray, $rankNum, $type, $title, $label, $moreHref = null, $cols = null, $hiOnly = false) {
		if ($cols === null) {
			$meanLabel = $label === "Mean" ? "" : " Mean";
			$cols = [
				['label' => $label . ' Low', 'j' => 0],
				['label' => $label . ' High', 'j' => 1],
				['label' => $label . ' '. $meanLabel, 'j' => 2],
			];
		}

		echo "<div class='detail-grid'>";

		echo "<div>";
		echo "<h3>" . $this->superlativeHigh . " " . $title . "</h3>";
		echo '<div class="detail-table-scroll">';
		Html::table("table1", '99%');
		Html::tr();
		Html::td("Rank", $this->cssClass);
		foreach ($cols as $col) {
			Html::td($col['label'], $this->cssClass);
		}
		Html::tr_end();

		for ($i = 1; $i <= $rankNum; $i++) {
			Html::tr(Html::colcol($i));
			Html::td($i, $this->cssClass);
			foreach ($cols as $col) {
				$j = $col['j'];
				$unit = isset($col['unit']) ? $col['unit'] : $this->conv;
				$dpa = isset($col['dpa']) ? (int)$col['dpa'] : 0;
				$v = isset($rankArray[$j][$type][1][0][$i]) ? $rankArray[$j][$type][1][0][$i] : null;
				$d = isset($rankArray[$j][$type][1][1][$i]) ? $rankArray[$j][$type][1][1][$i] : '';
				Html::td($this->disp($v, $unit, $dpa) . $this->dateHtml($d), $this->cssClass);
			}
			Html::tr_end();
		}
		Html::table_end();
		echo '</div>';
		echo "</div>";

		if (!$hiOnly) {
			echo "<div>";
			echo "<h3>" . $this->superlativeLow . " " . $title . "</h3>";
			echo '<div class="detail-table-scroll">';
			Html::table("table1", '99%');
			Html::tr();
			Html::td("Rank", $this->cssClass);
			foreach ($cols as $col) {
				Html::td($col['label'], $this->cssClass);
			}
			Html::tr_end();

			for ($i = 1; $i <= $rankNum; $i++) {
				Html::tr(Html::colcol($i));
				Html::td($i, $this->cssClass);
				foreach ($cols as $col) {
					$j = $col['j'];
					$unit = isset($col['unit']) ? $col['unit'] : $this->conv;
					$dpa = isset($col['dpa']) ? (int)$col['dpa'] : 0;
					$v = isset($rankArray[$j][$type][0][0][$i]) ? $rankArray[$j][$type][0][0][$i] : null;
					$d = isset($rankArray[$j][$type][0][1][$i]) ? $rankArray[$j][$type][0][1][$i] : '';
					Html::td($this->disp($v, $unit, $dpa) . $this->dateHtml($d), $this->cssClass);
				}
				Html::tr_end();
			}
			Html::table_end();
			echo '</div>';
			echo "</div>";
		}

		echo "</div>";
		if ($moreHref) {
			echo "<p><a href=\"" . htmlspecialchars($moreHref) . "\">View more "
				. strtolower($label) . " rankings</a></p>";
		}
	}

	function rankTables($rankNum = 10, $rankNumM = 10, $rankNumCM = 5, $rankNumY = 5) {
		echo '<h2>Ranked Historical ' . $this->label . ' Data</h2>';
		$this->renderRankTablesBody($rankNum, $rankNumM, $rankNumCM, $rankNumY);
	}

	/**
	 * Body of the rankings section.
	 */
	function renderRankTablesBody($rankNum = 10, $rankNumM = 10, $rankNumCM = 5, $rankNumY = 5) {
		$vt = $this->varMean;
		$mon = (int)Date::$dmonth;
		$monName = Date::$monthname;
		$monPlural = $monName . 's';

		$dailyCols = !empty($this->group['rankDailyCols']) ? $this->group['rankDailyCols'] : null;
		$monthlyCols = !empty($this->group['rankMonthlyCols']) ? $this->group['rankMonthlyCols'] : null;
		$annualCols = !empty($this->group['rankAnnualCols'])
			? $this->group['rankAnnualCols']
			: $monthlyCols;
		$dailyHiOnly = !empty($this->group['rankDailyHiOnly']);

		$this->rankTablePair($this->ranks, $rankNum, 'daily', "Days", "Daily",
			'/RankDay.php?vartype=' . rawurlencode($vt), $dailyCols, $dailyHiOnly);
		$this->rankTablePair($this->ranks, $rankNumM, 'monthly', "Months", "Mean",
			'/RankMonth.php?vartype=' . rawurlencode($vt), $monthlyCols);
		$this->rankTablePair($this->ranks, $rankNumY, 'annual', "Years", "Mean",
			'/RankYear.php?vartype=' . rawurlencode($vt), $annualCols);
		$this->rankTablePair($this->ranks, $rankNumCM, 'dailyCM', "Days in " . $monName, "Daily",
			null, $dailyCols, $dailyHiOnly);
		$this->rankTablePair($this->ranks, $rankNumCM, 'monthlyCM', $monPlural, "Mean",
			null, $monthlyCols);
	}
}
?>
