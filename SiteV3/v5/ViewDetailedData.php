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

	/** @var DataSummarizer */
	private $datMins;
	private $datMaxs;
	private $datMeans;

	// summarize() outputs (associative arrays, not objects)
	private $minSum;
	private $maxSum;
	private $meanSum;

	// Legacy-shaped adapter arrays, built from the summaries above (replaces crontag globals)
	private $dat;
	private $datMM;
	private $datSS;
	private $datSSanom;
	private $ranks;
	private $datToday;
	private $datYest;

	public static $periods = array('latest_7d','curr_month','latest_31d','curr_year','latest_365d','alltime','all_this_month','all_this_date');
	public static $measuresGeneric = array('Lowest Min','Highest Max','Highest Min','Lowest Max','Lowest Mean','Highest Mean','Averages','Mean','Avg Low','Avg High');

	public $periods_all;
	public static $periodCnt;

	// LTA daily-anomaly available for these (tmean resolved dynamically via LTA)
	private static $ltaDailyTypes = array('tmin', 'tmax', 'tmean', 'rain');


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
				"anomaly" => true
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
				"class" => 16
			],
			"wind" => [
				"name" => "Wind",
				"unit" => Wx::Wind,
				"var_min" => "gust",
				"var_max" => "wmax",
				"var_mean" => "wmean",
				"superlativeLo" => "Calmest",
				"superlativeHi" => "Windiest",
				"letter" => "w",
				"class" => 13
			],
			"rain" => [
				"name" => "Rain",
				"unit" => Wx::Rain,
				"var_min" => "10max",
				"var_max" => "hrmax",
				"var_mean" => "rain",
				"superlativeLo" => "Driest",
				"superlativeHi" => "Wettest",
				"letter" => "r",
				"class" => 12,
				"anomaly" => true
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
				"class" => 10
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
				"class" => 10
			],
		];
		$this->groupName = $groupName;
		$this->group = $groups[$groupName];
		$this->conv = $this->group["unit"];
		$this->getAnom = array_key_exists("anomaly", $this->group);

		// Properties the render methods rely on
		$this->letter = $this->group["letter"];
		$this->varMin = $this->group["var_min"];
		$this->varMax = $this->group["var_max"];
		$this->varMean = $this->group["var_mean"];
		$this->intradayVar = isset(Wx::$mappingsToDailyDataKey[$this->letter]) ? Wx::$mappingsToDailyDataKey[$this->letter] : 'temp';
		$this->label = $this->group["name"];
		$this->cssClass = "td12";
		$this->type = $groupName;
		$this->superlativeLow = $this->group["superlativeLo"];
		$this->superlativeHigh = $this->group["superlativeHi"];

		$this->datMins = new DataSummarizer($this->group["var_min"]);
		$this->datMaxs = new DataSummarizer($this->group["var_max"]);
		$this->datMeans = new DataSummarizer($this->group["var_mean"]); // was incorrectly var_max

		$this->minSum = $this->datMins->summarize();
		$this->maxSum = $this->datMaxs->summarize();
		$this->meanSum = $this->datMeans->summarize();

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

	// ---- Adapter construction: legacy-shaped arrays from summarize() output ----

	private function buildAdapters() {
		$statSums = ['min' => $this->minSum, 'max' => $this->maxSum, 'mean' => $this->meanSum];

		// $this->dat[stat][rank][periodKey] (+ 'date'/'anom' suffix keys)
		$this->dat = ['min' => [[], [], []], 'max' => [[], [], []], 'mean' => [[], [], []]];
		$ndayMap = ['7cum' => 7, '31cum' => 31, '365cum' => 365];
		$recMap = ['Ma' => 'month_mean_alltime', 'Mmr' => 'month_mean_all_this_month', 'Ya' => 'year_mean_alltime'];

		foreach ($statSums as $stat => $sum) {
			$ps = $sum['period_summaries'];
			$varForStat = ($stat === 'min') ? $this->varMin : (($stat === 'max') ? $this->varMax : $this->varMean);
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
				$this->dat[$stat][2][$pk] = $this->sval($s, 'mean');
				if ($this->getAnom) {
					// Extremes: anom vs daily LTA on the event date (TagGen behaviour)
					$this->dat[$stat][0][$pk . 'anom'] = $this->eventDayAnom($varForStat, $minVal, $minDate);
					$this->dat[$stat][1][$pk . 'anom'] = $this->eventDayAnom($varForStat, $maxVal, $maxDate);
					// Means: period-mean anom from DataSummarizer
					$this->dat[$stat][2][$pk . 'anom'] = $this->sval($s, 'anom');
				}
			}
			// Record N-day means
			foreach ($ndayMap as $rk => $d) {
				$lo = isset($ps[$d . 'd_lo_mean_alltime']) ? $ps[$d . 'd_lo_mean_alltime'] : null;
				$hi = isset($ps[$d . 'd_hi_mean_alltime']) ? $ps[$d . 'd_hi_mean_alltime'] : null;
				$this->dat[$stat][0][$rk] = $this->sval($lo, 'mean');
				$this->dat[$stat][0][$rk . 'date'] = $this->sval($lo, 'endDateFmt');
				$this->dat[$stat][1][$rk] = $this->sval($hi, 'mean');
				$this->dat[$stat][1][$rk . 'date'] = $this->sval($hi, 'endDateFmt');
			}
			// Record month/year means
			foreach ($recMap as $rk => $psk) {
				$s = isset($ps[$psk]) ? $ps[$psk] : null;
				$this->dat[$stat][0][$rk] = $this->cleanExtreme($s, 'min');
				$this->dat[$stat][0][$rk . 'date'] = $this->fmtRecDate($this->sval($s, 'minDate'), $rk);
				$this->dat[$stat][1][$rk] = $this->cleanExtreme($s, 'max');
				$this->dat[$stat][1][$rk . 'date'] = $this->fmtRecDate($this->sval($s, 'maxDate'), $rk);
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
		foreach ($statSums as $stat => $sum) {
			$ms = $sum['month_summaries'];
			$varForStat = ($stat === 'min') ? $this->varMin : (($stat === 'max') ? $this->varMax : $this->varMean);
			$collect = [0 => [], 1 => [], 2 => []];
			$days = [0 => [], 1 => []];
			$anoms = [0 => [], 1 => [], 2 => []];
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
				$collect[2][$mt] = $this->sval($s, 'mean');
				$days[0][$mt] = $this->dayOf($minDate);
				$days[1][$mt] = $this->dayOf($maxDate);
				if ($this->getAnom) {
					$anoms[0][$mt] = $this->eventDayAnom($varForStat, $minVal, $minDate);
					$anoms[1][$mt] = $this->eventDayAnom($varForStat, $maxVal, $maxDate);
					$anoms[2][$mt] = $this->sval($s, 'anom');
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
		}
	}

	private function buildSeasonal($statSums) {
		$this->datSS = ['min' => [], 'max' => [], 'mean' => []];
		$this->datSSanom = ['min' => [], 'max' => [], 'mean' => []];
		foreach ($statSums as $stat => $sum) {
			$ss = $sum['season_summaries'];
			for ($i = 0; $i < 4; $i++) {
				$y = ($i + 1 < Date::$season || Date::$dmonth == 12) ? Date::$dyear : Date::$dyear - 1;
				$key = Date::$snames[$i] . '_' . $y;
				$s = isset($ss[$key]) ? $ss[$key] : null;
				$this->datSS[$stat][$i] = $this->sval($s, 'mean');
				$this->datSSanom[$stat][$i] = ($this->getAnom) ? $this->sval($s, 'anom') : null;
			}
		}
	}

	private function buildRanks() {
		$this->ranks = [];
		$rankSums = [0 => $this->minSum, 1 => $this->maxSum, 2 => $this->meanSum];
		$typeMap = ['daily' => 'daily_alltime', 'monthly' => 'month_mean_alltime', 'dailyCM' => 'daily_all_this_month'];
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
	 *   daily    → full date / Today / Yesterday
	 *   monthly  → "Jul 2018", or red "Current" for this month
	 *   dailyCM  → "Day N, YYYY" / Today / Yesterday
	 */
	private function fmtRankDate($raw, $type) {
		if ($raw === null || $raw === '') return '';
		$ts = $this->parseDateTs($raw);
		if ($ts === null) return (string) $raw;
		if ($type === 'monthly') {
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

	private function dailyAnom($varName, $val, $yest) {
		if (!$this->getAnom || $val === null || !is_numeric($val)) return null;
		if (!in_array($varName, self::$ltaDailyTypes)) return null;
		$m = $yest ? Date::$mon_yest : Date::$dmonth;
		$d = $yest ? Date::$day_yest : Date::$dday;
		$y = $yest ? Date::$yr_yest : Date::$dyear;
		$norm = LTA::getDailyAnom($varName, $m, $d, $y);
		if (!is_numeric($norm)) return null;
		return $val - $norm;
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
		if (!is_numeric($norm)) return null;
		return $val - $norm;
	}

	/** Display a value, or '-' when null. */
	private function disp($val, $conv = null) {
		if ($val === null) return '-';
		return Wx::conv($val, $conv === null ? $this->conv : $conv);
	}

	/** Render an anomaly hint like " (+1.2)" or '' when unavailable. */
	private function anomHint($val) {
		if (!$this->getAnom || $val === null || !is_numeric($val)) return '';
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

		echo '<div class="detail-grid">';
		echo ' <div class="kv-table">';
		for ($r = 0; $r < $cnt; $r++) {
			echo '<div class="' . Html::colcol($r) . '">';
			echo '<div>' . $measures[$r] . '</div>';
			echo '<div>' . Wx::conv($values[$r], $convs[$r]) . '</div>';
			echo '</div>';
		}
		echo ' </div>';
		echo '<div class="detail-graph">';
		Charts::intraday(['num' => 1, 'ts' => 12], $this->intradayVar, ['height' => 300]);
		echo '</div>';
		echo '</div>';

		$data = [
			[
				'label' => "Today's Low",
				'value' => $this->disp($this->datToday[0][0]),
				'time' => $this->datToday[1][0],
				'anomaly' => $this->datToday[2]['min'],
			],
			[
				'label' => "Today's High",
				'value' => $this->disp($this->datToday[0][1]),
				'time' => $this->datToday[1][1],
				'anomaly' => $this->datToday[2]['max'],
			],
			[
				'label' => "Today's Mean",
				'value' => $this->disp($this->datToday[0][2]),
				'time' => null,
				'anomaly' => $this->datToday[2]['mean'],
			],
			[
				'label' => "Yesterday's Low",
				'value' => $this->disp($this->datYest[0]['min']),
				'time' => $this->datYest[1][0],
				'anomaly' => $this->datYest[2]['min'],
			],
			[
				'label' => "Yesterday's High",
				'value' => $this->disp($this->datYest[0]['max']),
				'time' => $this->datYest[1][1],
				'anomaly' => $this->datYest[2]['max'],
			],
			[
				'label' => "Yesterday's Mean",
				'value' => $this->disp($this->datYest[0]['mean']),
				'time' => null,
				'anomaly' => $this->datYest[2]['mean'],
			],
		];
		echo '<div class="detail-grid">';
		echo ' <div class="kv-table">';
		foreach ($data as $r => $row) {
			echo '<div class="' . Html::colcol($r) . '">';
			echo '<div>' . $row['label'] . '</div>';
			$val = $row['value'];
			if ($row['time']) {
				$val .= ' @ ' . $row['time'];
			}
			if ($row['anomaly'] !== null && is_numeric($row['anomaly'])) {
				$val .= '&nbsp; (' . Wx::conv($row['anomaly'], Wx::AbsTemp, 0, 1) . ')';
			}
			echo '<div>' . $val . '</div>';
			echo '</div>';
		}
		echo ' </div>';

		echo ' <div class="detail-graph">';
		Charts::daily(['type' => $this->varMean, 'mode' => 'daily', 'length' => 31], ['height' => 300]);
		echo ' </div>';
		echo '</div>';

		// 31-day min and max charts
		echo '<div class="detail-grid">';
		echo '<div class="detail-graph">';
		Charts::daily(['type' => $this->varMin, 'mode' => 'daily', 'length' => 31], ['height' => 350]);
		echo '</div>';
		echo '<div class="detail-graph">';
		Charts::daily(['type' => $this->varMax, 'mode' => 'daily', 'length' => 31], ['height' => 350]);
		echo '</div>';
		echo '</div>';
	}

	function avgsExtrmsRecs($measures = null, $wid = 99) {
		$dat = $this->dat;
		$measures = is_null($measures) ? self::$measuresGeneric : $measures;
		$values = array($dat['min'][0], $dat['max'][1], $dat['min'][1], $dat['max'][0], $dat['mean'][0], $dat['mean'][1], '---', $dat['mean'][2], $dat['min'][2], $dat['max'][2]);

		$splitOne = self::$periodCnt - 3;
		echo "<h2>Averages, Extremes, and Records</h2>";
		echo "<div class='detail-grid'>";

		echo "<div>";
		echo "<h3>Recent</h3>";

		Html::table(null, $wid . '%" align="center', 6);
		Html::tr();
		Html::td($this->label, $this->cssClass, "10%");
		for ($h = 0; $h < $splitOne; $h++) {
			Html::td($this->periods_all[self::$periods[$h]], $this->cssClass, '18%');
		}
		Html::tr_end();

		for ($r = 0; $r < count($measures); $r++) {
			Html::tr(($r === 6) ? 'table-top' : Html::colcol($r));
			Html::td(str_ireplace(' ', '<br />', $measures[$r]), $this->cssClass);

			for ($c = 0; $c < $splitOne; $c++) {
				if ($r != 6) {
					$pk = self::$periods[$c];
					$anom = $this->anomHint(isset($values[$r][$pk . 'anom']) ? $values[$r][$pk . 'anom'] : null);
					$date = $this->dateHtml(isset($values[$r][$pk . 'date']) ? $values[$r][$pk . 'date'] : '');
					Html::td('<b>' . $this->disp(isset($values[$r][$pk]) ? $values[$r][$pk] : null) . '</b>' . $anom . $date, $this->cssClass);
				} else {
					Html::td('&nbsp;', $this->cssClass);
				}
			}
			Html::tr_end();
		}
		Html::table_end();
		echo "</div>";

		echo "<div>";
		echo "<h3>Station Lifetime (2009-" . Date::$dyear . ")</h3>";

		Html::table(null, $wid . '%" align="center', 6);
		Html::tr();
		Html::td($this->label, $this->cssClass, "10%");
		for ($h = $splitOne; $h < self::$periodCnt; $h++) {
			Html::td($this->periods_all[self::$periods[$h]], $this->cssClass, '18%');
		}
		Html::tr_end();

		for ($r = 0; $r < count($measures); $r++) {
			Html::tr(($r === 6) ? 'table-top' : Html::colcol($r));
			Html::td(str_ireplace(' ', '<br />', $measures[$r]), $this->cssClass);

			for ($c = $splitOne; $c < self::$periodCnt; $c++) {
				if ($r != 6) {
					$pk = self::$periods[$c];
					$anom = $this->anomHint(isset($values[$r][$pk . 'anom']) ? $values[$r][$pk . 'anom'] : null);
					$date = $this->dateHtml(isset($values[$r][$pk . 'date']) ? $values[$r][$pk . 'date'] : '');
					Html::td('<b>' . $this->disp(isset($values[$r][$pk]) ? $values[$r][$pk] : null) . '</b>' . $anom . $date, $this->cssClass);
				} else {
					Html::td('&nbsp;', $this->cssClass);
				}
			}
			Html::tr_end();
		}
		Html::table_end();
		echo "</div>";
		echo "</div>";

		$this->recordPeriodAvgs();
	}

	function pastYearAvgsExtrms($measures = null, $wid = 99) {
		// Daily trends: Highcharts via histdata.php (same stack as wx3), replacing
		// the legacy graph_daily_trend.php jpgraph images.
		$yr = (int)Date::$dyear;
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

		echo '<h3>This year min/max daily trends in detail for ' . $this->label . '</h3>';
		echo '<div class="detail-grid charts">';
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

		$dat = $this->datMM;
		$measures = is_null($measures) ? self::$measuresGeneric : $measures;
		$values = array($dat['min'][0], $dat['max'][1], $dat['min'][1], $dat['max'][0], $dat['mean'][0], $dat['mean'][1], '---', $dat['mean'][2], $dat['min'][2], $dat['max'][2]);

		$pastStart = Date::mkdate(Date::$dmonth - 12, 15, Date::$dyear);
		$pastEnd = Date::mkdate(Date::$dmonth - 1, 15, Date::$dyear);
		echo "<h2>Past Year Monthly Averages and Extremes ("
			. date('F Y', $pastStart) . ' - ' . date('F Y', $pastEnd) . ")</h2>";
		Html::table(null, $wid . '%" align="center', 6);
		Html::tr();
		Html::td("Month", $this->cssClass);
		for ($r = 0; $r < count($measures); $r++) {
			if ($r != 6) {
				Html::td(str_ireplace(' ', '<br />', $measures[$r]), $this->cssClass);
			}
		}
		Html::tr_end();

		for ($m = 11; $m >= 0; $m--) {
			$mt = date('n', Date::mkdate(Date::$dmonth - $m - 1, 15)) - 1;
			Html::tr(Html::colcol($m));
			Html::td(Date::$months3[$mt], $this->cssClass);

			for ($r = 0; $r < count($measures); $r++) {
				if ($r != 6) {
					$cell = isset($values[$r][0][$mt]) ? $values[$r][0][$mt] : null;
					if ($cell !== null && isset($values[$r]['extr'][1]) && $cell == $values[$r]['extr'][1]) {
						$colour = '" style="color:#DF7401';
					} elseif ($cell !== null && isset($values[$r]['extr'][0]) && $cell == $values[$r]['extr'][0]) {
						$colour = '" style="color:#0101DF';
					} else {
						$colour = '';
					}
					$anom = $this->anomHint(isset($values[$r][2][$mt]) ? $values[$r][2][$mt] : null);
					if (array_key_exists(1, $values[$r]) && array_key_exists($mt, $values[$r][1]) && $values[$r][1][$mt] !== null) {
						$date = $this->dateHtml(Date::datefull($values[$r][1][$mt]));
					} else {
						$date = '';
					}
					Html::td('<b>' . $this->disp($cell) . '</b>' . $anom . $date, $this->cssClass . $colour);
				}
			}
			Html::tr_end();
		}

		Html::table_end();

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

		$this->seasonalAvgs();

		echo '<h3>Past 24hrs and past 12 months trends for ' . $this->label . '</h3>';
		echo '<div class="detail-grid">';
		echo '<div>';
		Charts::intraday(['num' => 1], $this->intradayVar, ['height' => 330]);
		echo '</div><div>';
		Charts::daily(['type' => $this->varMean, 'mode' => 'monthly', 'length' => 12, 'summary_type' => Data::SUMMARY_MEAN, 'lta' => 1], ['height' => 330]);
		echo '</div></div>';
		echo '<div class="detail-grid">';
		echo '<div>';
		Charts::daily(['type' => $this->varMin, 'mode' => 'monthly', 'length' => 12, 'summary_type' => Data::SUMMARY_MEAN], ['height' => 330]);
		echo '</div><div>';
		Charts::daily(['type' => $this->varMax, 'mode' => 'monthly', 'length' => 12, 'summary_type' => Data::SUMMARY_MEAN], ['height' => 330]);
		echo '</div></div>';
		echo '<p><a href="/charts.php">View more ' . $this->label . ' charts</a></p>';
	}

	private function seasonalAvgs($wid = 75) {
		$dat = $this->datSS;
		$datAnom = $this->getAnom ? $this->datSSanom : [];

		echo "<h2>Past Year Seasonal Averages</h2>";
		Html::table(null, $wid . '%" align="center', 6, true);

		Html::tr();
		Html::td("Season", $this->cssClass, "22%");
		Html::td("Daily Min", $this->cssClass, "26%");
		Html::td("Daily Max", $this->cssClass, "26%");
		Html::td("Mean", $this->cssClass, "26%");
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

			$statKeys = array('min', 'max', 'mean');
			for ($j = 0; $j < 3; $j++) {
				$sk = $statKeys[$j];
				$anom = ($this->getAnom && isset($datAnom[$sk][$i]) && is_numeric($datAnom[$sk][$i]))
					? ' (' . Wx::conv($datAnom[$sk][$i], Wx::AbsTemp, 1, 1) . ')' : '';
				$v = isset($dat[$sk][$i]) ? $dat[$sk][$i] : null;
				Html::td($this->disp($v) . $anom . '<br />', $this->cssClass . '" style="' . $hlite);
			}
			Html::tr_end();
		}

		Html::table_end();
	}

	private function recordPeriodAvgs($wid = 98) {
		$dat = $this->dat;
		$values = array($dat['mean'][0], $dat['mean'][1], $dat['min'][0], $dat['min'][1], $dat['max'][0], $dat['max'][1]);

		$periods = array('7cum', 'Ma', 'Mmr', '31cum', 'Ya', '365cum');
		$measures = array($this->superlativeLow, $this->superlativeHigh, 'Lowest Mean Daily-Min', 'Highest Mean Daily-Min', 'Lowest Mean Daily-Max', 'Highest Mean Daily-Max');

		echo "<h2>Record Period Averages</h2>";
		Html::table(null, $wid . '%" style="margin-bottom:28px;', 6);

		Html::tr();
		Html::td("Measure", $this->cssClass, "8%");
		for ($h = 0; $h < count($periods); $h++) {
			Html::td($this->periods_all[$periods[$h]], $this->cssClass, '15%');
		}
		Html::tr_end();

		for ($r = 0; $r < count($measures); $r++) {
			Html::tr(Html::colcol($r));
			Html::td(str_ireplace(' ', '<br />', $measures[$r]), $this->cssClass);

			for ($c = 0; $c < count($periods); $c++) {
				$pk = $periods[$c];
				$date = $this->dateHtml(isset($values[$r][$pk . 'date']) ? $values[$r][$pk . 'date'] : '');
				Html::td('<b>' . $this->disp(isset($values[$r][$pk]) ? $values[$r][$pk] : null) . '</b>' . $date, $this->cssClass);
			}
			Html::tr_end();
		}

		Html::table_end();
	}

	private function rankTablePair($rankArray, $rankNum, $type, $title, $label) {
		echo "<div class='detail-grid'>";

		echo "<div>";
		echo "<h3>" . $this->superlativeHigh . " " . $title . "</h3>";
		Html::table("table1", '99%');
		Html::tr();
		Html::td("Rank", $this->cssClass);
		Html::td($label . " Low", $this->cssClass);
		Html::td($label . " High", $this->cssClass);
		Html::td($label . " Mean", $this->cssClass);
		Html::tr_end();

		for ($i = 1; $i <= $rankNum; $i++) {
			Html::tr(Html::colcol($i));
			Html::td($i, $this->cssClass);
			for ($j = 0; $j < 3; $j++) {
				$v = isset($rankArray[$j][$type][1][0][$i]) ? $rankArray[$j][$type][1][0][$i] : null;
				$d = isset($rankArray[$j][$type][1][1][$i]) ? $rankArray[$j][$type][1][1][$i] : '';
				Html::td($this->disp($v) . $this->dateHtml($d), $this->cssClass);
			}
			Html::tr_end();
		}
		Html::table_end();
		echo "</div>";

		echo "<div>";
		echo "<h3>" . $this->superlativeLow . " " . $title . "</h3>";
		Html::table("table1", '99%');
		Html::tr();
		Html::td("Rank", $this->cssClass);
		Html::td($label . " Low", $this->cssClass);
		Html::td($label . " High", $this->cssClass);
		Html::td($label . " Mean", $this->cssClass);
		Html::tr_end();

		for ($i = 1; $i <= $rankNum; $i++) {
			Html::tr(Html::colcol($i));
			Html::td($i, $this->cssClass);
			for ($j = 0; $j < 3; $j++) {
				$v = isset($rankArray[$j][$type][0][0][$i]) ? $rankArray[$j][$type][0][0][$i] : null;
				$d = isset($rankArray[$j][$type][0][1][$i]) ? $rankArray[$j][$type][0][1][$i] : '';
				Html::td($this->disp($v) . $this->dateHtml($d), $this->cssClass);
			}
			Html::tr_end();
		}
		Html::table_end();
		echo "</div>";

		echo "</div>";
		echo "<p>
			 <a href='/RankDay.php?vartype=" . $this->letter . "mean'>View more " . $this->label . " rankings</a>
		</p>";
	}

	function rankTables($rankNum = 10, $rankNumM = 10, $rankNumCM = 5) {
		echo '<h2>Ranked Historical ' . $this->label . ' Data</h2>';

		self::rankTablePair($this->ranks, $rankNum, 'daily', "Days", "Daily");
		self::rankTablePair($this->ranks, $rankNumM, 'monthly', "Months", "Monthly");
		self::rankTablePair($this->ranks, $rankNumCM, 'dailyCM', "Days in " . Date::$monthname, "Daily");
	}
}
?>
