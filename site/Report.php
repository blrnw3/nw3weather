<?php

/**
 * Report - shared foundation for the v5 data-report pages (daily/monthly tables,
 * historical reports and ranking pages). This is the OO replacement for the
 * legacy procedural wxdatagen.php.
 *
 * It resolves per-variable metadata from Wx::$daily, renders the variable / year /
 * month navigation form, and provides the value-colour helper used to shade table
 * cells (CSS classes defined in /valcolstyle.css).
 *
 * Construct one per page, passing the page defaults; read selections from $_GET so
 * it stays consistent with DataPage::buildSlug (no hidden global/session state).
 */
class Report {

	// ---- Selection state (from $_GET) ----
	public $type;               // variable name, e.g. 'rain'
	public $year;               // selected year
	public $month;              // selected month (0 = whole year)
	public $startYrReport;      // start year for multi-year reports
	public $rankLimit;          // rows shown on ranking pages
	public $summaryType;        // Data::SUMMARY_* chosen via the tab buttons
	public $spellDir;           // 'above' | 'below' (spell rankings)
	public $spellThreshold;     // numeric threshold for spell rankings
	public $spellThresholds;    // preset chips for the current variable
	public $dayAgg;             // '' | 'min' | 'max' | 'mean' (calendar-day all-years)
	public $periodLength;       // N-day period length for RankPeriods
	public $periodNoOverlap;    // true = skip overlapping windows in period rankings

	// ---- Derived metadata for $type ----
	public $meta;               // Wx::$daily[$type]
	public $unit;               // Wx unit constant
	public $description;        // human label
	public $isSum;              // summable (totals rather than means)
	public $isAnom;             // has a climate anomaly
	public $isCountOnly;        // event count only (hail/thunder/fog)
	public $isNotSummarisable;  // wdir / cloud
	public $isDerived;          // computed from other variables
	public $startYear;          // first year data exists for this variable
	public $yearDefaulted;      // true when requested year was before startYear
	public $valcolConvert;      // convert value before colour lookup?
	public $hasToday;           // has a meaningful current-day value

	/** Manually-recorded variables that have no automatic "today" value. */
	private static $manualVars = [
		'sunhr', 'wethr', 'cloud', 'snow', 'lysnw', 'hail', 'thunder', 'fog',
		'comms', 'extra', 'issues', 'away', 'pond', 'spare',
	];

	// ---- Option lists ----
	public static $ranknumOptions = [10, 25, 50, 100, 250];
	public static $periodLengthOptions = [3, 5, 7, 14, 30, 90, 365];
	public $startYearOptions;
	public $availSummaryTypes;

	private static $thresholdsReady = false;
	private static $thresholds = [];   // descriptor name => [levels...]
	private $valcolIdx;                 // value-colour index for $type

	/**
	 * Maps each variable name to a value-colour scheme index (legacy wxtablecols_all).
	 */
	private static $valcolMap = [
		'tmin' => 0, 'tmax' => 0, 'tmean' => 0,
		'hmin' => 1, 'hmax' => 1, 'hmean' => 1,
		'pmin' => 2, 'pmax' => 2, 'pmean' => 2,
		'wmean' => 3, 'wmax' => 3, 'gust' => 3, 'wdir' => 4,
		'rain' => 5, 'hrmax' => 5, '10max' => 5, 'ratemax' => 6,
		'dmin' => 0, 'dmax' => 0, 'dmean' => 0,
		'nightmin' => 0, 'daymax' => 0,
		'tc10max' => 7, 'tchrmax' => 7, 'hchrmax' => 8, 'tc10min' => 7, 'tchrmin' => 7, 'hchrmin' => 8,
		'w10max' => 3,
		'fmin' => 0, 'fmax' => 0, 'fmean' => 0,
		'afhrs' => 10,
		'aqmin' => 14, 'aqmax' => 14, 'aqmean' => 14,
		'trange' => 0, 'hrange' => 8, 'prange' => 9, 'ratemean' => 5,
		'sunhr' => 10, 'wethr' => 10, 'cloud' => 10, 'snow' => 10, 'lysnw' => 10,
		'hail' => 10, 'thunder' => 10, 'fog' => 10, 'comms' => 10, 'extra' => 10,
		'issues' => 10, 'away' => 10, 'pond' => 0,
		'sunhrp' => 11, 'wethrp' => 11,
	];

	/** index => the CSS level name used in /valcolstyle.css */
	private static $valcolLevel = [
		0 => 'temp', 1 => 'humi', 2 => 'press', 3 => 'wind', 4 => 'degr', 5 => 'rain',
		6 => 'rtmax', 7 => 'tchg', 8 => 'hchg', 9 => 'prng', 10 => 'dhrs', 11 => 'dhrs',
		12 => 'temp', 13 => 'dhrs', 14 => 'aqi',
	];

	/** Variable grouping for the colgroup drop-down (legacy $categories). */
	public static $categories = [
		'Temperature' => ['tmin', 'tmax', 'tmean'],
		'Rainfall' => ['rain', 'hrmax', '10max', 'ratemax'],
		'Wind' => ['wmean', 'wmax', 'gust', 'wdir'],
		'Humidity' => ['hmin', 'hmax', 'hmean'],
		'Pressure' => ['pmin', 'pmax', 'pmean'],
		'Dew Point' => ['dmin', 'dmax', 'dmean'],
		'Observations' => ['sunhr', 'sunhrp', 'wethr', 'wethrp', 'ratemean', 'snow', 'lysnw', 'hail', 'thunder', 'fog', 'pond'],
		'Range' => ['trange', 'hrange', 'prange'],
		'Change' => ['tc10max', 'tchrmax', 'hchrmax', 'tc10min', 'tchrmin', 'hchrmin'],
		'Misc.' => ['nightmin', 'daymax', 'w10max', 'afhrs'],
		'Feels-like' => ['fmin', 'fmax', 'fmean'],
	];

	/**
	 * Explain the current weather variable (what it is + day definition).
	 * Emitted at the bottom of the page body so AJAX swaps refresh it with the table.
	 */
	public function echoVarAbout() {
		$about = Wx::measureAbout($this->type);
		if ($about === '') { return; }
		echo '<p class="report-var-about" id="report-var-about">'
			. htmlspecialchars($about) . '</p>';
	}

	/**
	 * Append a one-line runtime sample for a ranking data fetch.
	 * @param float $t0 microtime(true) taken before the fetch/render work
	 * @param string $kind day|month|year|spells|periods
	 */
	public function logRankRuntime($t0, $kind) {
		$ms = (int)round((microtime(true) - $t0) * 1000);
		$bits = [
			$kind,
			$ms . 'ms',
			'var=' . $this->type,
			'start=' . (int)$this->startYrReport,
		];
		if ($kind === 'day' || $kind === 'month' || $kind === 'spells' || $kind === 'periods') {
			$bits[] = 'month=' . (int)$this->month;
		}
		if ($kind === 'month' || $kind === 'year' || $kind === 'periods') {
			$bits[] = 'summary=' . (int)$this->summaryType;
			if ($this->needsThreshold()) {
				$bits[] = 'threshold=' . $this->spellThreshold;
			}
		}
		if ($kind === 'spells') {
			$bits[] = 'dir=' . $this->spellDir;
			$bits[] = 'threshold=' . $this->spellThreshold;
		}
		if ($kind === 'periods') {
			$bits[] = 'period=' . (int)$this->periodLength;
			$bits[] = 'no_overlap=' . ($this->periodNoOverlap ? 1 : 0);
		}
		if ($kind !== 'year') {
			$bits[] = 'limit=' . (int)$this->rankLimit;
		}
		Page::quick_log('rank_runtime.txt', implode(' ', $bits));
	}

	/**
	 * Unit used when formatting a summary value (temperature range uses AbsTemp
	 * so °C→°F is a delta, not an absolute conversion).
	 * @param int|null $summaryType defaults to current selection
	 */
	public function summaryDisplayUnit($summaryType = null) {
		$st = ($summaryType === null) ? $this->summaryType : (int)$summaryType;
		if ($st === Data::SUMMARY_RANGE && $this->unit === Wx::Temperature) {
			return Wx::AbsTemp;
		}
		return $this->unit;
	}

	/** True when the current (or given) summary type needs a threshold chip row. */
	public function needsThreshold($summaryType = null) {
		$st = ($summaryType === null) ? $this->summaryType : (int)$summaryType;
		return Data::isThresholdSummary($st);
	}

	/**
	 * Heading suffix for monthly / rank-monthly / rank-annual / rank-periods views.
	 * Count-above/below put the unit on the threshold, like spell rankings.
	 */
	public function summaryTitleSuffix($summaryType = null) {
		$st = ($summaryType === null) ? $this->summaryType : (int)$summaryType;
		if (Data::isThresholdSummary($st)) {
			return $this->description . ' · days ' . $this->summaryThresholdPhrase($st);
		}
		return $this->description . ' / ' . Wx::getUnitsText($this->summaryDisplayUnit($st));
	}

	/** Title suffix including period length for RankPeriods. */
	public function periodTitleSuffix($summaryType = null) {
		$st = ($summaryType === null) ? $this->summaryType : (int)$summaryType;
		$cap = $this->summaryCaptionName($st);
		if (Data::isThresholdSummary($st)) {
			return $this->description . ' · ' . $this->periodLength . '-day ' . $cap;
		}
		return $this->description . ' / ' . Wx::getUnitsText($this->summaryDisplayUnit($st))
			. ' · ' . $this->periodLength . '-day ' . $cap;
	}

	/**
	 * Threshold comparison phrase for count summaries, e.g. "≥ 10.0 °C".
	 * Empty string when the summary type does not use a threshold.
	 */
	public function summaryThresholdPhrase($summaryType = null) {
		$st = ($summaryType === null) ? $this->summaryType : (int)$summaryType;
		if (!Data::isThresholdSummary($st)) { return ''; }
		$dir = ($st === Data::SUMMARY_COUNT_ABOVE) ? 'above' : 'below';
		$sym = Spells::ruleSymbol($this->type, $dir, $this->spellThreshold);
		$threshTxt = Wx::plainText(Wx::conv($this->spellThreshold, $this->unit, true));
		return $sym . ' ' . $threshTxt;
	}

	/** Short summary label for table captions, including threshold when relevant. */
	public function summaryCaptionName($summaryType = null) {
		$st = ($summaryType === null) ? $this->summaryType : (int)$summaryType;
		$name = Data::$SUMMARY_NAMES[$st];
		if (Data::isThresholdSummary($st)) {
			return 'days ' . $this->summaryThresholdPhrase($st);
		}
		return $name;
	}

	/** Display labels for the current variable's threshold presets. */
	public function thresholdLabels() {
		$labels = [];
		foreach ($this->spellThresholds as $th) {
			$labels[] = Wx::plainText(Wx::conv($th, $this->unit, false));
		}
		return $labels;
	}

	/**
	 * @param array $opts default-bearing options:
	 *   'default'   => fallback variable name (default 'rain')
	 *   'badCats'   => variable names to hide unless selected
	 */
	function __construct($opts = []) {
		self::initThresholds();
		$default = isset($opts['default']) ? $opts['default'] : 'rain';
		$badCats = isset($opts['badCats']) ? $opts['badCats'] : [];

		$gType = isset($_GET['vartype']) ? $_GET['vartype'] : $default;
		$this->type = isset(Wx::$daily[$gType]) ? $gType : $default;

		$this->meta = Wx::$daily[$this->type];
		$this->unit = isset($this->meta['unit']) ? $this->meta['unit'] : Wx::None;
		$this->description = isset($this->meta['description']) ? $this->meta['description'] : $this->type;
		$this->isCountOnly = !empty($this->meta['count-only']);
		// Count-only event variables (hail/thunder/fog) are summed like totals.
		$this->isSum = !empty($this->meta['summable']) || $this->isCountOnly;
		$this->isAnom = !empty($this->meta['anomaly']);
		$this->hasToday = !in_array($this->type, self::$manualVars, true) && !$this->isDerived;
		$this->isDerived = !empty($this->meta['derived']);
		$this->isNotSummarisable = in_array($this->type, ['wdir', 'cloud'], true);
		$this->startYear = isset($this->meta['start_year']) ? (int)$this->meta['start_year'] : Site::BASE_YEAR;
		$this->valcolIdx = isset(self::$valcolMap[$this->type]) ? self::$valcolMap[$this->type] : null;
		$this->valcolConvert = !in_array($this->type, ['hail', 'thunder', 'wdir'], true);

		// Calendar-day all-years aggregation (daily tables): min | max | mean
		$agg = isset($_GET['agg']) ? $_GET['agg'] : '';
		$this->dayAgg = in_array($agg, array('min', 'max', 'mean'), true) ? $agg : '';

		// Year / month selection
		$this->year = isset($_GET['year']) ? (int)$_GET['year'] : (int)Date::$yr_yest;
		$this->yearDefaulted = false;
		if ($this->dayAgg === '') {
			if ($this->year < $this->startYear) {
				$this->year = min((int)Date::$dyear, $this->startYear);
				$this->yearDefaulted = true;
			}
			if ($this->year > (int)Date::$dyear) { $this->year = (int)Date::$dyear; }
		} else {
			if ($this->year < $this->startYear || $this->year > (int)Date::$dyear) {
				$this->year = (int)Date::$yr_yest;
			}
		}
		$this->month = isset($_GET['month']) ? (int)$_GET['month'] : 0;
		if ($this->month < 0 || $this->month > 12) { $this->month = 0; }

		// Ranking / multi-year selectors
		$this->rankLimit = isset($_GET['rankLimit']) ? (int)$_GET['rankLimit'] : 25;
		if (!in_array($this->rankLimit, self::$ranknumOptions, true)) { $this->rankLimit = 25; }

		$allStartOpts = [1871, 1950, 1980, 2000, 2009];
		$this->startYearOptions = [];
		foreach ($allStartOpts as $y) {
			if ($y >= $this->startYear) { $this->startYearOptions[] = $y; }
		}
		// Offer the variable's own first year too, else its earliest data is
		// unreachable (e.g. temperature from 1881 would be pegged at 1950).
		if (count($this->startYearOptions) && $this->startYear < $this->startYearOptions[0]) {
			if ($this->startYearOptions[0] - $this->startYear < 5) {
				array_shift($this->startYearOptions);
			}
			array_unshift($this->startYearOptions, (int)$this->startYear);
		}
		if (!count($this->startYearOptions)) {
			$this->startYearOptions[] = max(2009, $this->startYear);
		}
		$this->startYrReport = isset($_GET['start_year_rep']) ? (int)$_GET['start_year_rep'] : 2009;
		$this->startYrReport = self::nearestStartYear($this->startYrReport, $this->startYearOptions);

		// Spell ranking selectors
		$this->spellDir = (isset($_GET['spell_dir']) && $_GET['spell_dir'] === 'below') ? 'below' : 'above';
		// Spell / count-threshold selectors share the same preset list.
		$this->spellThresholds = Wx::thresholdPresets($this->type);
		$defThresh = Wx::defaultThreshold($this->type, $this->unit);
		if (isset($_GET['threshold']) && is_numeric($_GET['threshold'])) {
			$this->spellThreshold = (float)$_GET['threshold'];
		} else {
			$this->spellThreshold = $defThresh;
		}
		$matched = false;
		foreach ($this->spellThresholds as $p) {
			if (abs($p - $this->spellThreshold) < 1e-9) {
				$this->spellThreshold = $p;
				$matched = true;
				break;
			}
		}
		if (!$matched) {
			$this->spellThreshold = $defThresh;
		}

		// Available monthly-summary tab types. Non-zero "Count" is omitted —
		// use Count ≥ / Count < with threshold 0 (or the variable default) instead.
		$this->availSummaryTypes = [(int)$this->isSum];
		array_push($this->availSummaryTypes, Data::SUMMARY_MIN, Data::SUMMARY_MAX);
		if (!$this->isNotSummarisable) {
			if (!$this->isCountOnly) {
				$this->availSummaryTypes[] = Data::SUMMARY_RANGE;
			}
			$this->availSummaryTypes[] = Data::SUMMARY_COUNT_ABOVE;
			$this->availSummaryTypes[] = Data::SUMMARY_COUNT_BELOW;
		}

		$g = isset($_GET['summary_type']) ? (int)$_GET['summary_type'] : 0;
		if ($g < 0 || $g > 7) { $g = 0; }
		if ($g <= 1) { $g = (int)$this->isSum; }
		if (!in_array($g, $this->availSummaryTypes, true)) { $g = $this->availSummaryTypes[0]; }
		$this->summaryType = $g;

		$plen = isset($_GET['period']) ? (int)$_GET['period'] : 5;
		if (!in_array($plen, self::$periodLengthOptions, true)) { $plen = 5; }
		$this->periodLength = $plen;
		// Hide-overlapping defaults to off (allow overlaps).
		$this->periodNoOverlap = isset($_GET['no_overlap'])
			&& (string)$_GET['no_overlap'] !== '0'
			&& (string)$_GET['no_overlap'] !== '';

		$this->badCats = $badCats;
	}

	private $badCats = [];

	// ---- Value-colour ----

	private static function initThresholds() {
		if (self::$thresholdsReady) { return; }
		$us = (Page::$units === UNIT_US);
		$eu = (Page::$units === UNIT_EU);

		$temp = $us ? [10, 20, 30, 40, 50, 60, 70, 80, 90] : [-5, 0, 5, 10, 15, 20, 25, 30, 35];
		$press = $us ? [28.5, 28.75, 29, 29.25, 29.5, 29.75, 30, 30.25, 30.5] : [970, 980, 990, 1000, 1010, 1015, 1020, 1030, 1040];
		$rain = $us ? [0, 0.004, 0.02, 0.04, 0.08, 0.2, 0.4, 0.6, 0.8, 1, 2] : [0, 0.1, 0.6, 1, 2, 5, 10, 15, 20, 25, 50];
		$rtmax = $us ? [0.04, 0.08, 0.2, 0.4, 0.8, 1.2, 2, 3, 4, 6, 10] : [0.1, 1, 2, 3, 5, 10, 30, 60, 100, 150, 300];
		$prng = $us ? [0.03, 0.06, 0.1, 0.15, 0.17, 0.2, 0.25, 0.3, 0.35] : [1, 2, 3, 5, 7, 10, 15, 20, 25];
		$wind = $eu ? [2, 3, 5, 10, 15, 20, 30, 45, 70] : [1, 2, 4, 7, 10, 15, 20, 30, 40];

		self::$thresholds = [
			0 => $temp,
			1 => [30, 40, 50, 60, 70, 80, 90, 97],
			2 => $press,
			3 => $wind,
			4 => [45, 90, 135, 180, 225, 270, 315],
			5 => $rain,
			6 => $rtmax,
			7 => [0.3, 0.6, 1, 1.5, 2, 2.5, 3, 4, 5],
			8 => [2, 5, 10, 15, 20, 30, 40, 50],
			9 => $prng,
			10 => [0, 0.3, 0.5, 1, 2, 3, 5, 7, 9, 12, 15],
			11 => [0, 10, 20, 25, 35, 50, 65, 75, 85, 90, 95],
			12 => [-10, -5, -2, 0, 2, 5, 10, 15, 20],
			13 => [25, 50, 75, 90, 100, 110, 125, 150, 175, 200, 250],
			// US values have already been converted to AQI; others remain raw PM2.5.
			14 => $us ? [25, 50, 75, 100, 125, 150, 200, 300, 500] : [11, 23, 35, 41, 47, 53, 58, 64, 70],
		];
		self::$thresholdsReady = true;
	}

	/**
	 * Returns the CSS class for a (converted) value, based on this variable's scheme.
	 * @param float $value the value to shade
	 * @param bool $countable use the day-count scale instead of the value scale
	 */
	public function valcolr($value, $countable = false) {
		return self::valcolForType($this->type, $value, $countable);
	}

	/** Universal green day-count class shared with spell-length cells. */
	public static function dayCountClass($days) {
		return 'spell-length spell-length-' . Spells::lengthColourLevel($days);
	}

	/**
	 * Value-colour CSS class for any daily variable (does not require a Report instance).
	 * $value should already be in display units when the variable uses unit conversion.
	 */
	public static function valcolForType($type, $value, $countable = false) {
		self::initThresholds();
		if (!isset(self::$valcolMap[$type])) { return 'reportday'; }
		$idx = self::$valcolMap[$type];
		if (!isset(self::$valcolLevel[$idx], self::$thresholds[$idx])) { return 'reportday'; }
		$level = self::$valcolLevel[$idx];
		$values = $countable ? [0, 1, 3, 5, 7, 10, 15, 20, 25, 30, 31] : self::$thresholds[$idx];
		for ($i = 0; $i < count($values); $i++) {
			if ($value <= $values[$i]) { return 'level' . $level . '_' . $i; }
		}
		return 'level' . $level . '_' . $i;
	}

	/** Offset used to scale summed (total) values onto the per-value colour scale. */
	public function valcolSumOffset() {
		if ($this->valcolIdx === null) { return 1; }
		$t = self::$thresholds[$this->valcolIdx];
		return 250 / $t[count($t) - 1];
	}

	/**
	 * Pick a start-year chip that exists for the current variable.
	 * Prefer the same year, else the nearest earlier option; if none, the earliest.
	 * @param int $want
	 * @param int[] $opts ascending start-year options
	 * @return int
	 */
	public static function nearestStartYear($want, $opts) {
		if (!count($opts)) { return (int)$want; }
		$want = (int)$want;
		$best = null;
		foreach ($opts as $y) {
			$y = (int)$y;
			if ($y === $want) { return $y; }
			if ($y <= $want) { $best = $y; }
		}
		return $best !== null ? $best : (int)$opts[0];
	}

	// ---- Aggregation helper (legacy mom): 0=min 1=max 2=mean 3=count>0 ----
	public static function aggregate($arr, $kind) {
		switch ($kind) {
			case 0: return Util::mymin($arr);
			case 1: return Util::mymax($arr);
			case 3: return Util::cond_count($arr, true, 0);
			default: return Util::mean($arr);
		}
	}

	// ---- Monthly / cumulative anomalies via long-term averages ----
	public function anomMonth($value, $month) {
		$norm = LTA::getMonthlyAnom($this->type, $month);
		if ($norm === null) { return ''; }
		if ($this->isSum) {
			return Util::percent($value, $norm, 0, true, false);
		}
		return Wx::conv($value - $norm, Wx::AbsTemp, 0, 1);
	}

	public function anomYear($value) {
		$norm = LTA::getYearlyAnom($this->type);
		if ($norm === null) { return ''; }
		if ($this->isSum) {
			return Util::percent($value, $norm, 0, true, false);
		}
		return Wx::conv($value - $norm, Wx::AbsTemp, 0, 1);
	}

	public function anomMonthCum($value, $monthIdx) {
		$sum = 0; $have = false;
		for ($i = 1; $i <= $monthIdx + 1; $i++) {
			$n = LTA::getMonthlyAnom($this->type, $i);
			if ($n === null) { return ''; }
			$sum += $n; $have = true;
		}
		if (!$have) { return ''; }
		if ($this->isSum) {
			return Util::percent($value, $sum, 0, true, false);
		}
		$sum /= ($monthIdx + 1);
		return Wx::conv($value - $sum, Wx::AbsTemp, 0, 1);
	}

	// ---- Navigation / controls ----

	/**
	 * Renders the page heading plus variable/year/month selection form.
	 * @param array $o flags: heading, showYear, showMonth, showStartYear,
	 *                 isDaily, linkToOther, showTabs, buttonSelectors
	 */
	public function controls($o = []) {
		if (!empty($o['buttonSelectors'])) {
			$this->controlsButtonSelectors($o);
			return;
		}

		$heading = isset($o['heading']) ? $o['heading'] : 'Data Tables';
		$showYear = isset($o['showYear']) ? $o['showYear'] : true;
		$showMonth = !empty($o['showMonth']);
		$showStartYear = !empty($o['showStartYear']);
		$isDaily = isset($o['isDaily']) ? $o['isDaily'] : true;
		$linkToOther = isset($o['linkToOther']) ? $o['linkToOther'] : '';
		$showTabs = !empty($o['showTabs']);

		if ($showMonth && $this->month > 0) {
			$heading .= ' for ' . Date::$months[$this->month - 1];
		}

		if ($this->year < $this->startYear) {
			echo "<p style='font-weight:bold;font-size:130%;color:#9f9500;margin-left:2em;'>NB: Data for "
				. $this->description . " begins in " . $this->startYear . "</p>";
			$this->year = $this->startYear;
		}

		echo '<h1>' . $heading . ' - ' . $this->description . ' / '
			. Wx::getUnitsText($this->unit) . '</h1>';

		$disabled = ' disabled="disabled"';
		echo '<div style="padding:10px">';
		if ($linkToOther !== '') {
			echo '<form action="/' . $linkToOther . '.php">'
				. '<input type="submit" value="Daily"' . ($isDaily ? $disabled : '') . ' style="padding:0.4em" /></form>'
				. '<form action="/' . $linkToOther . '.php">'
				. '<input type="submit" value="Monthly"' . (!$isDaily ? $disabled : '') . ' style="padding:0.4em" /></form>';
		}
		echo '<span class="test" style="padding-left:20px;padding-right:4px;">Weather Variable:</span>';
		echo '<form method="get" action="">';
		echo '<select name="vartype" onchange="this.form.submit()">';
		foreach (self::$categories as $cat => $subCats) {
			echo '<optgroup label="' . $cat . '">';
			foreach ($subCats as $sub) {
				if (!isset(Wx::$daily[$sub])) { continue; }
				if (in_array($sub, $this->badCats, true) && $sub !== $this->type) { continue; }
				$sel = ($this->type === $sub) ? ' selected="selected"' : '';
				echo '<option value="' . $sub . '"' . $sel . '>' . Wx::$daily[$sub]['description'] . '</option>';
			}
			echo '</optgroup>';
		}
		echo '</select>';

		// prev / next variable arrows
		$flat = $this->flatCats();
		$cnt = count($flat);
		$idx = array_search($this->type, $flat, true);
		if ($idx !== false) {
			$prev = $flat[Util::mod($idx - 1, $cnt)];
			$next = $flat[($idx + 1) % $cnt];
			HTML::dropdownCycle(false, DataPage::buildSlug('vartype', $prev), Wx::$daily[$prev]['description']);
			HTML::dropdownCycle(true, DataPage::buildSlug('vartype', $next), Wx::$daily[$next]['description']);
		}

		if ($showYear) {
			$dyear = (int)Date::$dyear;
			$span = $dyear - $this->startYear + 1;
			$prevYear = Util::mod($this->year - 1 - $this->startYear, $span) + $this->startYear;
			$nextYear = (($this->year + 1 - $this->startYear) % $span) + $this->startYear;
			echo '<span style="padding-left:25px;padding-right:3px;" class="rep">Year</span>';
			HTML::dropdownCycle(false, DataPage::buildSlug('year', $prevYear), $prevYear);
			echo '<select name="year" onchange="this.form.submit()">';
			for ($i = $dyear; $i >= $this->startYear; $i--) {
				echo '<option value="' . $i . '"' . ($i === $this->year ? ' selected="selected"' : '') . '>' . $i . '</option>';
			}
			echo '</select>';
			HTML::dropdownCycle(true, DataPage::buildSlug('year', $nextYear), $nextYear);
		}

		if ($showMonth) {
			$prevMonth = ($this->month == 0) ? 12 : Util::mod($this->month - 1, 13);
			$nextMonth = $this->month % 12 + 1;
			echo '<span style="padding-left:25px;padding-right:3px;" class="rep">Month</span>';
			HTML::dropdownCycle(false, DataPage::buildSlug('month', $prevMonth), Date::$months[$prevMonth - 1]);
			echo '<select name="month" onchange="this.form.submit()">';
			echo '<option value="0"' . ($this->month == 0 ? ' selected="selected"' : '') . '>All</option>';
			for ($i = 1; $i <= 12; $i++) {
				echo '<option value="' . $i . '"' . ($i === $this->month ? ' selected="selected"' : '') . '>' . Date::$months3[$i - 1] . '</option>';
			}
			echo '</select>';
			HTML::dropdownCycle(true, DataPage::buildSlug('month', $nextMonth), Date::$months[$nextMonth - 1]);
		}

		if ($showStartYear) {
			echo '<span style="padding-left:25px" class="rep">Start Year</span>';
			echo '<select name="start_year_rep" onchange="this.form.submit()">';
			foreach ($this->startYearOptions as $opt) {
				echo '<option value="' . $opt . '"' . ($opt === $this->startYrReport ? ' selected="selected"' : '') . '>' . $opt . '</option>';
			}
			echo '</select>';
		}

		echo '<input id="summary-type-input" type="hidden" name="summary_type" value="' . $this->summaryType . '" />';
		echo '</form></div><a name="start"> </a>';

		if ($showTabs) {
			echo "<div class='rank-tab-buttons'>";
			foreach ($this->availSummaryTypes as $st) {
				$cls = ($st === $this->summaryType) ? ' disabled="disabled"' : '';
				echo '<button id="rank-btn-' . $st . '" class="rank-tab-button"' . $cls
					. ' onclick="changeTab(' . $st . ')">Monthly ' . ucfirst(Data::$SUMMARY_NAMES[$st]) . '</button>';
			}
			echo "</div>";
		}
	}

	/**
	 * Button-style selectors (group → measure) plus optional year / month /
	 * start-year / summary / rank-limit chips. Optional $o['ajaxFragment'] loads
	 * the body without a full page reload.
	 *
	 * $o['mode']: daily | monthly | rank-daily | rank-monthly | rank-annual | rank-spells
	 */
	private function controlsButtonSelectors($o = []) {
		require_once __DIR__ . '/ChartHelper.php';

		$heading = isset($o['heading']) ? $o['heading'] : 'Data Tables';
		$showYear = !empty($o['showYear']);
		$showMonth = !empty($o['showMonth']);
		$showStartYear = !empty($o['showStartYear']);
		$showSummary = !empty($o['showSummary']);
		$showRankLimit = !empty($o['showRankLimit']);
		$showSpell = !empty($o['showSpell']);
		$ajaxFragment = isset($o['ajaxFragment']) ? $o['ajaxFragment'] : '';
		$ajaxBodyId = isset($o['ajaxBodyId']) ? $o['ajaxBodyId'] : 'dd-ajax';
		if (isset($o['mode'])) {
			$mode = $o['mode'];
		} elseif ($showYear) {
			$mode = 'daily';
		} elseif ($showSummary) {
			$mode = 'monthly';
		} else {
			$mode = 'daily';
		}
		$isRank = ($mode === 'rank-daily' || $mode === 'rank-monthly' || $mode === 'rank-annual' || $mode === 'rank-spells' || $mode === 'rank-periods');

		$titleSuffix = $this->description . ' / ' . Wx::getUnitsText($this->unit);
		if ($mode === 'daily' && $this->dayAgg !== '') {
			$aggTitle = array('min' => 'Min', 'max' => 'Max', 'mean' => 'Mean');
			$titleSuffix .= ' · ' . $aggTitle[$this->dayAgg] . ' (all years)';
		}
		if ($mode === 'monthly' || $mode === 'rank-monthly' || $mode === 'rank-annual') {
			$titleSuffix = $this->summaryTitleSuffix();
		}
		if ($mode === 'rank-periods') {
			$titleSuffix = $this->periodTitleSuffix();
		}
		if ($mode === 'rank-spells') {
			// Unit belongs on the threshold ("Above 0.0 °C"), not after the measure name.
			$titleSuffix = $this->description . ' · '
				. Spells::directionLabel($this->type, $this->spellDir, $this->spellThreshold);
		}
		echo '<h1 id="report-sel-heading">' . htmlspecialchars($heading) . ' - '
			. htmlspecialchars($titleSuffix) . '</h1>';

		$groups = self::buttonSelectorGroups();
		foreach ($groups as &$g) {
			foreach (array_keys($g['options']) as $optType) {
				if (in_array($optType, $this->badCats, true) && $optType !== $this->type) {
					unset($g['options'][$optType]);
				}
			}
		}
		unset($g);
		$groups = array_values(array_filter($groups, function ($g) {
			return !empty($g['options']);
		}));

		$activeGroup = $groups[0]['id'];
		foreach ($groups as $g) {
			if (isset($g['options'][$this->type])) { $activeGroup = $g['id']; break; }
		}

		$selUrl = function ($overrides) use ($mode, $isRank, $showSpell) {
			$params = array('vartype' => $this->type);
			if ($mode === 'daily') {
				if ($this->dayAgg !== '') {
					$params['agg'] = $this->dayAgg;
					$params['start_year_rep'] = $this->startYrReport;
				} else {
					$params['year'] = $this->year;
				}
			} elseif ($mode === 'monthly') {
				$params['start_year_rep'] = $this->startYrReport;
				$params['summary_type'] = $this->summaryType;
				if ($this->needsThreshold()) {
					$params['threshold'] = $this->spellThreshold;
				}
			} elseif ($isRank) {
				$params['start_year_rep'] = $this->startYrReport;
				if ($mode !== 'rank-annual') {
					$params['rankLimit'] = $this->rankLimit;
				}
				if ($mode === 'rank-daily' || $mode === 'rank-monthly' || $mode === 'rank-spells' || $mode === 'rank-periods') {
					$params['month'] = $this->month;
				}
				if ($mode === 'rank-monthly' || $mode === 'rank-annual' || $mode === 'rank-periods') {
					$params['summary_type'] = $this->summaryType;
					if ($this->needsThreshold()) {
						$params['threshold'] = $this->spellThreshold;
					}
				}
				if ($mode === 'rank-periods') {
					$params['period'] = $this->periodLength;
					$params['no_overlap'] = $this->periodNoOverlap ? 1 : 0;
				}
				if ($mode === 'rank-spells' || $showSpell) {
					$params['spell_dir'] = $this->spellDir;
					$params['threshold'] = $this->spellThreshold;
				}
			}
			foreach ($overrides as $k => $v) { $params[$k] = $v; }
			if ($mode === 'daily') {
				if (isset($overrides['year'])) {
					unset($params['agg'], $params['start_year_rep']);
				}
				if (isset($overrides['agg'])) { unset($params['year']); }
			}
			return htmlspecialchars(Page::$pageName . '?' . http_build_query($params));
		};

		echo '<div class="report-sel" id="report-sel" data-mode="' . htmlspecialchars($mode) . '"'
			. ' data-type="' . htmlspecialchars($this->type) . '"'
			. ' data-year="' . (int)$this->year . '"'
			. ' data-agg="' . htmlspecialchars($this->dayAgg) . '"'
			. ' data-month="' . (int)$this->month . '"'
			. ' data-start-year-rep="' . (int)$this->startYrReport . '"'
			. ' data-summary-type="' . (int)$this->summaryType . '"'
			. ' data-rank-limit="' . (int)$this->rankLimit . '"'
			. ' data-spell-dir="' . htmlspecialchars($this->spellDir) . '"'
			. ' data-threshold="' . htmlspecialchars((string)$this->spellThreshold) . '"'
			. ' data-period="' . (int)$this->periodLength . '"'
			. ' data-no-overlap="' . ($this->periodNoOverlap ? '1' : '0') . '"'
			. ' data-body="' . htmlspecialchars($ajaxBodyId) . '"'
			. ($ajaxFragment !== '' ? ' data-fragment="' . htmlspecialchars($ajaxFragment) . '"' : '')
			. ' data-heading="' . htmlspecialchars($heading) . '">';

		echo '<div class="report-sel-row">';
		echo '<div class="wxsel-groups" role="tablist">';
		foreach ($groups as $g) {
			$active = ($g['id'] === $activeGroup) ? ' active' : '';
			$firstType = array_keys($g['options'])[0];
			$icon = isset($g['icon'])
				? '<img src="' . htmlspecialchars($g['icon']) . '" alt="" width="16" height="16" />'
				: '';
			echo '<button type="button" class="' . trim($active) . '" data-group="'
				. htmlspecialchars($g['id']) . '" data-default-type="' . htmlspecialchars($firstType)
				. '" title="' . htmlspecialchars($g['label']) . '">'
				. $icon . '<span>' . htmlspecialchars($g['label']) . '</span></button>';
		}
		echo '</div></div>';

		echo '<div class="report-sel-row">';
		echo '<div class="wxsel-subtypes" role="tablist">';
		foreach ($groups as $g) {
			if ($g['id'] !== $activeGroup) { continue; }
			foreach ($g['options'] as $type => $label) {
				$active = ($type === $this->type) ? ' active' : '';
				echo '<a class="wxsel-chip' . $active . '" data-vartype="' . htmlspecialchars($type)
					. '" href="' . $selUrl(array('vartype' => $type)) . '">'
					. htmlspecialchars($label) . '</a>';
			}
		}
		echo '</div></div>';

		if ($showSpell) {
			echo '<div class="report-sel-row report-sel-labelled">';
			echo '<div class="wxsel-label">Spell</div>';
			echo '<div class="wxsel-scale wxsel-spell-dir" role="tablist">';
			foreach (Spells::directionChipLabels($this->type) as $dir => $lab) {
				$active = ($dir === $this->spellDir) ? ' active' : '';
				echo '<a class="wxsel-chip' . $active . '" data-spell-dir="' . $dir
					. '" href="' . $selUrl(array('spell_dir' => $dir)) . '">'
					. htmlspecialchars($lab) . '</a>';
			}
			echo '</div></div>';
		}

		if ($showSummary) {
			if ($mode === 'rank-annual') { $sumLabel = 'Annual'; }
			elseif ($mode === 'rank-monthly' || $mode === 'monthly') { $sumLabel = 'Monthly'; }
			elseif ($mode === 'rank-periods') { $sumLabel = 'Aggregate'; }
			else { $sumLabel = 'Summary'; }
			echo '<div class="report-sel-row report-sel-labelled">';
			echo '<div class="wxsel-label">' . htmlspecialchars($sumLabel) . '</div>';
			echo '<div class="wxsel-scale wxsel-summary" role="tablist">';
			foreach ($this->availSummaryTypes as $st) {
				$active = ($st === $this->summaryType) ? ' active' : '';
				$label = ucfirst(Data::$SUMMARY_NAMES[$st]);
				$hrefParams = array('summary_type' => $st);
				if (Data::isThresholdSummary($st)) {
					$hrefParams['threshold'] = $this->spellThreshold;
				}
				echo '<a class="wxsel-chip' . $active . '" data-summary="' . (int)$st
					. '" href="' . $selUrl($hrefParams) . '">'
					. htmlspecialchars($label) . '</a>';
			}
			echo '</div></div>';
		}

		// Threshold chips for spell rankings and for count ≥ / count < summaries.
		$showThreshold = $showSpell || ($showSummary && $this->needsThreshold());
		echo '<div class="report-sel-row report-sel-labelled" id="report-sel-threshold"'
			. ($showThreshold ? '' : ' hidden') . '>';
		echo '<div class="wxsel-label">Threshold</div>';
		echo '<div class="wxsel-scale wxsel-threshold" role="tablist">';
		foreach ($this->spellThresholds as $th) {
			$active = (abs($th - $this->spellThreshold) < 1e-9) ? ' active' : '';
			$label = Wx::plainText(Wx::conv($th, $this->unit, false));
			echo '<a class="wxsel-chip' . $active . '" data-threshold="' . htmlspecialchars((string)$th)
				. '" href="' . $selUrl(array('threshold' => $th)) . '">'
				. htmlspecialchars($label) . '</a>';
		}
		echo '</div></div>';

		$showPeriod = !empty($o['showPeriod']) || ($mode === 'rank-periods');
		if ($showPeriod) {
			echo '<div class="report-sel-row report-sel-labelled">';
			echo '<div class="wxsel-label">Period</div>';
			echo '<div class="wxsel-scale wxsel-periods" role="tablist">';
			foreach (self::$periodLengthOptions as $plen) {
				$active = ($plen === $this->periodLength) ? ' active' : '';
				echo '<a class="wxsel-chip' . $active . '" data-period="' . (int)$plen
					. '" href="' . $selUrl(array('period' => $plen)) . '">'
					. (int)$plen . 'd</a>';
			}
			echo '</div></div>';

			echo '<div class="report-sel-row report-sel-labelled">';
			echo '<div class="wxsel-label">Overlaps</div>';
			echo '<div class="wxsel-scale wxsel-period-overlap" role="tablist">';
			$allowActive = !$this->periodNoOverlap ? ' active' : '';
			$hideActive = $this->periodNoOverlap ? ' active' : '';
			echo '<a class="wxsel-chip' . $allowActive . '" data-no-overlap="0" href="'
				. $selUrl(array('no_overlap' => 0)) . '">Allow</a>';
			echo '<a class="wxsel-chip' . $hideActive . '" data-no-overlap="1" href="'
				. $selUrl(array('no_overlap' => 1)) . '">Hide</a>';
			echo '</div></div>';
		}

		if ($showMonth) {
			if ($mode === 'rank-spells') { $monthLabel = 'Midpoint month'; }
			elseif ($mode === 'rank-periods') { $monthLabel = 'Month start'; }
			else { $monthLabel = 'Month'; }
			echo '<div class="report-sel-row report-sel-labelled">';
			echo '<div class="wxsel-label">' . htmlspecialchars($monthLabel) . '</div>';
			echo '<div class="wxsel-scale wxsel-months" role="tablist">';
			$active = ($this->month === 0) ? ' active' : '';
			echo '<a class="wxsel-chip' . $active . '" data-month="0" href="'
				. $selUrl(array('month' => 0)) . '">All</a>';
			for ($m = 1; $m <= 12; $m++) {
				$active = ($m === $this->month) ? ' active' : '';
				echo '<a class="wxsel-chip' . $active . '" data-month="' . $m
					. '" href="' . $selUrl(array('month' => $m)) . '">'
					. Date::$months3[$m - 1] . '</a>';
			}
			echo '</div></div>';
		}

		if ($showYear) {
			$dyear = (int)Date::$dyear;
			$yearFloor = $dyear;
			foreach ($groups as $g) {
				foreach (array_keys($g['options']) as $t) {
					$sy = isset(Wx::$daily[$t]['start_year']) ? (int)Wx::$daily[$t]['start_year'] : Site::BASE_YEAR;
					if ($sy < $yearFloor) { $yearFloor = $sy; }
				}
			}
			$recent = array();
			for ($i = 0; $i < 5; $i++) {
				$y = $dyear - $i;
				if ($y >= $yearFloor) { $recent[] = $y; }
			}
			$older = array();
			for ($y = $dyear - 5; $y >= $yearFloor; $y--) {
				$older[] = $y;
			}
			$yearInRecent = ($this->dayAgg === '') && in_array($this->year, $recent, true);

			echo '<div class="report-sel-row">';
			echo '<div class="wxsel-scale wxsel-years" role="tablist">';
			foreach ($recent as $y) {
				$active = ($this->dayAgg === '' && $y === $this->year) ? ' active' : '';
				echo '<a class="wxsel-chip' . $active . '" data-year="' . $y
					. '" href="' . $selUrl(array('year' => $y)) . '">' . $y . '</a>';
			}
			if (count($older)) {
				$sumCls = ($this->dayAgg === '' && !$yearInRecent) ? ' active' : '';
				$sumLabel = ($this->dayAgg === '' && !$yearInRecent) ? (string)$this->year : 'Older';
				echo '<details class="wxsel-overflow">';
				echo '<summary class="wxsel-chip' . $sumCls . '">' . htmlspecialchars($sumLabel) . '</summary>';
				echo '<div class="wxsel-overflow-menu">';
				foreach ($older as $y) {
					$active = ($this->dayAgg === '' && $y === $this->year) ? ' active' : '';
					echo '<a class="wxsel-chip' . $active . '" data-year="' . $y
						. '" href="' . $selUrl(array('year' => $y)) . '">' . $y . '</a>';
				}
				echo '</div></details>';
			}
			$aggOpts = array('min' => 'Min', 'max' => 'Max', 'mean' => 'Mean');
			$aggActive = ($this->dayAgg !== '');
			$aggSumLabel = $aggActive ? $aggOpts[$this->dayAgg] : 'Avg/Extreme';
			echo '<details class="wxsel-overflow wxsel-day-agg">';
			echo '<summary class="wxsel-chip' . ($aggActive ? ' active' : '') . '">'
				. htmlspecialchars($aggSumLabel) . '</summary>';
			echo '<div class="wxsel-overflow-menu">';
			foreach ($aggOpts as $aggKey => $aggLabel) {
				$active = ($this->dayAgg === $aggKey) ? ' active' : '';
				echo '<a class="wxsel-chip' . $active . '" data-agg="' . $aggKey
					. '" href="' . $selUrl(array('agg' => $aggKey)) . '">' . $aggLabel . '</a>';
			}
			echo '</div></details>';
			echo '</div></div>';
		}

		// Daily avg/extreme mode also needs a start-year filter (hidden until agg is on).
		$showStartForDailyAgg = ($mode === 'daily');
		if ($showStartYear || $showStartForDailyAgg) {
			$startHidden = ($showStartForDailyAgg && $this->dayAgg === '') ? ' hidden' : '';
			echo '<div class="report-sel-row report-sel-labelled" id="report-sel-start-year"'
				. $startHidden . '>';
			echo '<div class="wxsel-label">Start year</div>';
			echo '<div class="wxsel-scale wxsel-start-years" role="tablist">';
			foreach ($this->startYearOptions as $opt) {
				$active = ($opt === $this->startYrReport) ? ' active' : '';
				echo '<a class="wxsel-chip' . $active . '" data-start-year="' . (int)$opt
					. '" href="' . $selUrl(array('start_year_rep' => $opt)) . '" title="Show data from this year">'
					. (int)$opt . '</a>';
			}
			echo '</div></div>';
		}

		if ($showRankLimit) {
			echo '<div class="report-sel-row report-sel-labelled">';
			echo '<div class="wxsel-label">Show</div>';
			echo '<div class="wxsel-scale wxsel-rank-limit" role="tablist">';
			foreach (self::$ranknumOptions as $opt) {
				$active = ($opt === $this->rankLimit) ? ' active' : '';
				echo '<a class="wxsel-chip' . $active . '" data-rank-limit="' . (int)$opt
					. '" href="' . $selUrl(array('rankLimit' => $opt)) . '">'
					. (int)$opt . '</a>';
			}
			echo '</div></div>';
		}

		echo '</div>'; // .report-sel

		$warnText = ($showYear && $this->yearDefaulted)
			? ('No data for ' . $this->description . ' in the selected year; '
				. 'defaulted to ' . (int)$this->year . ' (earliest available).')
			: '';
		echo '<p id="report-year-warn" class="report-year-warn"'
			. (($showYear && $this->yearDefaulted) ? '' : ' hidden') . '>'
			. htmlspecialchars($warnText) . '</p>';

		echo '<a name="start"> </a>';

		$sumLabels = array();
		foreach (Data::$SUMMARY_NAMES as $i => $name) {
			$sumLabels[$i] = ucfirst($name);
		}

		$fragIds = array(
			'daily' => 'dd-fragment',
			'monthly' => 'dm-fragment',
			'rank-daily' => 'rd-fragment',
			'rank-monthly' => 'rm-fragment',
			'rank-annual' => 'ry-fragment',
			'rank-spells' => 'rs-fragment',
			'rank-periods' => 'rp-fragment',
		);
		$cfg = array(
			'groups' => $groups,
			'mode' => $mode,
			'type' => $this->type,
			'year' => (int)$this->year,
			'agg' => $this->dayAgg,
			'month' => (int)$this->month,
			'startYearRep' => (int)$this->startYrReport,
			'startYearOptions' => array_map('intval', $this->startYearOptions),
			'summaryType' => (int)$this->summaryType,
			'summaryTypes' => $this->availSummaryTypes,
			'summaryLabels' => $sumLabels,
			'rankLimit' => (int)$this->rankLimit,
			'spellDir' => $this->spellDir,
			'spellDirLabels' => Spells::directionChipLabels($this->type),
			'threshold' => $this->spellThreshold,
			'thresholds' => $this->spellThresholds,
			'thresholdLabels' => $this->thresholdLabels(),
			'periodLength' => (int)$this->periodLength,
			'periodLengthOptions' => self::$periodLengthOptions,
			'periodNoOverlap' => $this->periodNoOverlap ? 1 : 0,
			'unit' => (int)$this->unit,
			'page' => Page::$pageName,
			'fragment' => $ajaxFragment !== '' ? $ajaxFragment : null,
			'bodyId' => $ajaxBodyId,
			'headingPrefix' => $heading,
			'fragId' => isset($fragIds[$mode]) ? $fragIds[$mode] : 'dd-fragment',
		);
		echo '<script>NW3_reportSel(' . json_encode($cfg) . ');</script>';
	}

	private function flatCats() {
		$flat = [];
		foreach (self::$categories as $v) {
			foreach ($v as $name) {
				if (in_array($name, $this->badCats, true) && $name !== $this->type) { continue; }
				$flat[] = $name;
			}
		}
		return $flat;
	}

	/**
	 * Full variable/measure groups for button selectors: chart selectableGroups
	 * plus any daily-table variables from $categories that those omit (e.g. Change).
	 */
	private static function buttonSelectorGroups() {
		require_once __DIR__ . '/ChartHelper.php';
		$groups = Charts::selectableGroups();
		$covered = [];
		foreach ($groups as $g) {
			foreach (array_keys($g['options']) as $type) {
				$covered[$type] = true;
			}
		}

		// Short labels for leftover category vars (mainly the Change set).
		$extraLabels = [
			'tc10max' => 'T 10m max',
			'tchrmax' => 'T 1h max',
			'hchrmax' => 'H 1h max',
			'tc10min' => 'T 10m min',
			'tchrmin' => 'T 1h min',
			'hchrmin' => 'H 1h min',
		];
		$extrasByCat = [];
		foreach (self::$categories as $cat => $types) {
			foreach ($types as $type) {
				if (isset($covered[$type]) || !isset(Wx::$daily[$type])) { continue; }
				$label = isset($extraLabels[$type])
					? $extraLabels[$type]
					: (isset(Wx::$daily[$type]['description']) ? Wx::$daily[$type]['description'] : $type);
				$extrasByCat[$cat][$type] = $label;
			}
		}
		foreach ($extrasByCat as $cat => $options) {
			$groups[] = [
				'id' => 'cat-' . preg_replace('/[^a-z0-9]+/', '-', strtolower($cat)),
				'label' => $cat,
				'options' => $options,
			];
		}
		return $groups;
	}

	/**
	 * Footnote for pre-2009 (MIDAS / Whitestone) data. Pass the earliest year
	 * actually shown on the page (daily year, or monthly/rank window start).
	 * @param int|null $windowStart earliest year of data on this page
	 */
	public function historicalInfo($windowStart = null) {
		if ($windowStart === null) {
			$windowStart = ((int)$this->startYrReport < Site::BASE_YEAR)
				? (int)$this->startYrReport
				: (int)$this->year;
		}
		$windowStart = max((int)$windowStart, (int)$this->startYear);
		if ((int)$this->startYear < Site::BASE_YEAR && $windowStart < Site::BASE_YEAR) {
			echo '<p class="hist-note">*Data from before 2009 are mostly from the historical site at Whitestone Pond in Hampstead. '
				. 'Where data from that record is missing, other nearby sites were used, including St James Park, Heathrow, and Kew Gardens (pre-1910). '
				. 'Best efforts have been made to adjust for site differences, but uncertainties are somewhat greater for this data. '
				. 'I am grateful to the Met Office for making this data available for free through the '
				. '<a href="https://data.ceda.ac.uk/badc/ukmo-midas-open/">MIDAS Open database</a>.</p>';
		}
	}

	/**
	 * Renders a ranked Value/Date table (top $rankNum rows) with optional
	 * today/yesterday footer rows.
	 * @param array $values  rank-index => value (plus optional 'today'/'yest')
	 * @param array $dates   rank-index => date string (plus optional 'today'/'yest')
	 * @param int $rankNum   number of rows to show
	 * @param string $title  table heading
	 * @param bool $alignLeft float table left (else centre)
	 * @param bool $showToday include the 'today' footer row
	 * @param bool $showFoot  include the today/yesterday footer rows
	 * @param bool|string $isDaily  true/'daily', false/'monthly', or 'annual' footer labels
	 * @param bool $isCount   values are plain counts (no unit conversion)
	 * @param float $sumfix   divisor applied before colour lookup
	 */
	public function rankTable($values, $dates, $rankNum, $title, $alignLeft, $showToday, $showFoot, $isDaily = true, $isCount = false, $sumOff = 1, $unitOverride = null) {
		$side = $alignLeft ? 'rk-left' : 'rk-right';
		$unit = ($unitOverride !== null) ? $unitOverride : $this->unit;
		echo '<div class="rk-grid ' . $side . '">';
		echo '<div class="rk-caption">' . htmlspecialchars($title) . '</div>';
		echo '<div class="rk-row rk-head">';
		echo '<div class="rk-lab">#</div><div class="rk-lab">Value</div><div class="rk-lab">Date</div>';
		echo '</div>';

		for ($i = 1; $i <= $rankNum; $i++) {
			if (!isset($values[$i])) { continue; }
			echo '<div class="rk-row">';
			echo '<div class="rk-rank">' . $i . '</div>';
			echo '<div class="rk-val ' . $this->rankClass($values[$i], $isCount, $sumOff, $unit) . '">'
				. $this->rankVal($values[$i], $isCount, $unit) . '</div>';
			echo '<div class="rk-date">' . (isset($dates[$i]) ? $dates[$i] : '') . '</div>';
			echo '</div>';
		}

		$period = is_string($isDaily) ? $isDaily : ($isDaily ? 'daily' : 'monthly');
		if ($showFoot) {
			if ($period === 'annual') {
				$todayLbl = 'Current year';
				$yestLbl = 'Last year';
			} elseif ($period === 'monthly') {
				$todayLbl = 'Current month';
				$yestLbl = 'Last month';
			} elseif ($period === 'period') {
				$todayLbl = 'Latest';
				$yestLbl = 'Previous';
			} else {
				$todayLbl = 'Today';
				$yestLbl = 'Yesterday';
			}
			if ($showToday && isset($values['today'])) {
				echo '<div class="rk-row rk-foot rk-foot-today">';
				echo '<div class="rk-rank">' . (isset($dates['today']) ? $dates['today'] : '') . '</div>';
				echo '<div class="rk-val ' . $this->rankClass($values['today'], $isCount, $sumOff, $unit) . '">'
					. $this->rankVal($values['today'], $isCount, $unit) . '</div>';
				echo '<div class="rk-date">' . $todayLbl . '</div>';
				echo '</div>';
			}
			if (isset($values['yest']) && $values['yest'] !== null) {
				echo '<div class="rk-row rk-foot">';
				echo '<div class="rk-rank">' . (isset($dates['yest']) ? $dates['yest'] : '') . '</div>';
				echo '<div class="rk-val ' . $this->rankClass($values['yest'], $isCount, $sumOff, $unit) . '">'
					. $this->rankVal($values['yest'], $isCount, $unit) . '</div>';
				echo '<div class="rk-date">' . $yestLbl . '</div>';
				echo '</div>';
			}
		}

		echo '</div>';
	}

	private function rankVal($v, $isCount, $unit = null) {
		if ($isCount) { return $v; }
		$u = ($unit !== null) ? $unit : $this->unit;
		return Wx::conv($v, $u, false);
	}

	private function rankClass($v, $isCount, $sumOff, $unit = null) {
		if ($isCount) {
			return self::dayCountClass($v);
		}
		$u = ($unit !== null) ? $unit : $this->unit;
		$num = $this->valcolConvert ? Wx::convNum($v, $u) : (float)$v;
		return $this->valcolr(($num === null ? 0 : $num) / $sumOff, $isCount);
	}

	public function rankLimitForm() {
		echo '<form method="get" action=""><span style="padding-left:25px" class="rep">Limit</span>'
			. '<select name="rankLimit" onchange="this.form.submit()">';
		foreach (self::$ranknumOptions as $opt) {
			echo '<option value="' . $opt . '"' . ($opt === $this->rankLimit ? ' selected="selected"' : '') . '>' . $opt . '</option>';
		}
		echo '</select></form>';
	}

	/**
	 * Spell ranking table: # / Length / Period.
	 * @param array $spells list of spell records from Spells::rankLongest
	 * @param string $title
	 */
	public function spellRankTable($spells, $title) {
		echo '<div class="rk-grid rk-left" style="float:none;width:100%;max-width:42rem;">';
		echo '<div class="rk-caption">' . htmlspecialchars($title) . '</div>';
		echo '<div class="rk-row rk-head">';
		echo '<div class="rk-lab">#</div><div class="rk-lab">Length</div><div class="rk-lab">Period</div>';
		echo '</div>';

		$yearSecs = 86400 * 365;
		foreach ($spells as $i => $spell) {
			$n = (int)$spell['length'];
			$startTs = strtotime($spell['startDate']);
			$endTs = strtotime($spell['endDate']);
			$startLbl = $startTs ? date('j M Y', $startTs) : $spell['startDate'];
			$endLbl = !empty($spell['ongoing']) ? 'Current' : ($endTs ? date('j M Y', $endTs) : $spell['endDate']);
			$pre2009 = $startTs && (int)date('Y', $startTs) < Site::BASE_YEAR;
			$period = $startLbl . ' – ' . $endLbl . ($pre2009 ? '*' : '');
			if ($startTs && (Date::$dtstamp - $startTs) < $yearSecs) {
				$period = '<b>' . $period . '</b>';
			}
			echo '<div class="rk-row">';
			echo '<div class="rk-rank">' . ($i + 1) . '</div>';
			echo '<div class="rk-val spell-length spell-length-'
				. Spells::lengthColourLevel($n) . '">' . $n . ' day'
				. ($n === 1 ? '' : 's') . '</div>';
			echo '<div class="rk-date">' . $period . '</div>';
			echo '</div>';
		}
		if (!count($spells)) {
			echo '<div class="rk-row"><div class="rk-rank">–</div>'
				. '<div class="rk-val">No spells</div><div class="rk-date"></div></div>';
		}
		echo '</div>';
	}
}
