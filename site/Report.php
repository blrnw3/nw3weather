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
	public $day;                // specific calendar day for rank-daily (0 = all days in the month)
	public $win;                // ±N days around $day on rank-daily (0 = exact date)
	public static $dateWinOptions = [0, 3, 5, 10];
	public $startYrReport;      // start year for multi-year reports
	public $rankLimit;          // rows shown on ranking pages
	public $summaryType;        // Data::SUMMARY_* chosen via the tab buttons
	public $spellDir;           // 'above' | 'below' (spell rankings)
	public $spellThreshold;     // numeric threshold for spell rankings
	public $spellThresholds;    // preset chips for the current variable
	public $dayAgg;             // '' | 'min' | 'max' | 'mean' (calendar-day all-years)
	public $showRecordYears = true; // daily min/max: show the year each record was set
	public $periodLength;       // N-day period length for RankPeriods
	public $periodNoOverlap;    // true = skip overlapping windows in period rankings
	public $span;               // period key: full, mtd, ytd, winter/spring/summer/autumn, h1/h2
	public $periodAnom = false; // Ranked Periods offers the anomaly summary (temperature)
	public $valcolIdxOverride = null; // colour scheme override (e.g. anomaly tables)

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
	public static $spanOptions = ['full', 'mtd', 'ytd', 'winter', 'spring', 'summer', 'autumn', 'seasonal', 'h1', 'h2'];
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
		'tminanom' => 15, 'tmaxanom' => 15, 'tmeananom' => 15,
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
		12 => 'temp', 13 => 'dhrs', 14 => 'aqi', 15 => 'temp',
	];

	/** Variable grouping for the colgroup drop-down (legacy $categories). */
	public static $categories = [
		'Temperature' => ['tmin', 'tmax', 'tmean', 'tminanom', 'tmaxanom', 'tmeananom'],
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
	 * Append a one-line runtime sample for a ranking or data-table fetch.
	 * @param float $t0 microtime(true) taken before the fetch/render work
	 * @param string $kind day|month|year|spells|periods|dataday|datamonth
	 */
	public function logRankRuntime($t0, $kind) {
		$ms = (int)nw3_round((microtime(true) - $t0) * 1000);
		Page::timeMetric('render', $ms, $kind);
		$ip = filter_var(Page::$ip, FILTER_VALIDATE_IP);
		$userAgent = preg_replace('/\s+/', '_', trim((string)Page::$browser));
		$bits = [
			$kind,
			$ms . 'ms',
			'ip=' . ($ip !== false ? $ip : 'unknown'),
			'ua=' . ($userAgent !== '' ? substr($userAgent, 0, 80) : 'unknown'),
			'var=' . $this->type,
			'start=' . (int)$this->startYrReport,
		];
		if ($kind === 'dataday') {
			$bits[] = 'year=' . (int)$this->year;
			$bits[] = 'agg=' . ($this->dayAgg !== '' ? $this->dayAgg : 'none');
		}
		if ($kind === 'day' || $kind === 'month' || $kind === 'spells' || $kind === 'periods') {
			$bits[] = 'month=' . (int)$this->month;
		}
		if ($kind === 'month' || $kind === 'year' || $kind === 'periods' || $kind === 'datamonth') {
			$bits[] = 'summary=' . (int)$this->summaryType;
			if ($this->needsThreshold()) {
				$bits[] = 'threshold=' . $this->spellThreshold;
			}
		}
		if ($kind === 'month' || $kind === 'year' || $kind === 'datamonth') {
			$bits[] = 'span=' . $this->span;
		}
		if ($kind === 'spells') {
			$bits[] = 'dir=' . $this->spellDir;
			$bits[] = 'threshold=' . $this->spellThreshold;
		}
		if ($kind === 'periods') {
			$bits[] = 'period=' . (int)$this->periodLength;
			$bits[] = 'no_overlap=' . ($this->periodNoOverlap ? 1 : 0);
		}
		if ($kind === 'day' || $kind === 'month' || $kind === 'spells' || $kind === 'periods') {
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
		if ($st === Data::SUMMARY_ANOM) { return Wx::AbsTemp; }
		if ($st === Data::SUMMARY_RANGE && $this->unit === Wx::Temperature) {
			return Wx::AbsTemp;
		}
		return $this->unit;
	}

	/** True when the current (or given) summary type needs a threshold chip row. */
	public function needsThreshold($summaryType = null) {
		$st = ($summaryType === null) ? $this->summaryType : (int)$summaryType;
		return Data::isThresholdSummary($st) || $st === Data::SUMMARY_ANOM;
	}

	/** Display labels for anomaly presets ("±5 °C"). */
	public static function anomThresholdLabel($th) {
		return '±' . Wx::plainText(Wx::conv(abs((float)$th), Wx::AbsTemp, false));
	}

	/**
	 * Heading suffix for monthly / rank-monthly / rank-annual / rank-periods views.
	 * Count-above/below put the unit on the threshold, like spell rankings.
	 */
	public function summaryTitleSuffix($summaryType = null) {
		$st = ($summaryType === null) ? $this->summaryType : (int)$summaryType;
		if (Data::isThresholdSummary($st)) {
			$base = $this->description . ' · days ' . $this->summaryThresholdPhrase($st);
		} elseif (!empty($this->meta['annual_only'])) {
			$base = $this->description;
		} else {
			$unitTxt = Wx::getUnitsText($this->summaryDisplayUnit($st));
			$base = ($unitTxt === '') ? $this->description : ($this->description . ' / ' . $unitTxt);
		}
		$spanBit = $this->spanTitleBit();
		return ($spanBit === '') ? $base : $base . ' · ' . $spanBit;
	}

	/** "month-to-date (1–12 Nov)" etc., or empty for a full period. */
	public function spanTitleBit() {
		if ($this->span === 'mtd') {
			return 'month-to-date (' . Data::spanRangeText('mtd') . ')';
		}
		if ($this->span === 'ytd') {
			return 'year-to-date (' . Data::spanRangeText('ytd') . ')';
		}
		if ($this->span === 'seasonal') {
			return 'seasonal';
		}
		if (Data::isSeasonSpan($this->span)) {
			return Data::seasonRangeText($this->span);
		}
		if ($this->span === 'h1') {
			if ((int)$this->month === 0) { return '1st half (days 1–15)'; }
			return '1st half (' . Data::halfRangeText('h1', (int)Date::$dyear, (int)$this->month) . ')';
		}
		if ($this->span === 'h2') {
			if ((int)$this->month === 0) { return '2nd half (day 16–end of month)'; }
			return '2nd half (' . Data::halfRangeText('h2', (int)Date::$dyear, (int)$this->month) . ')';
		}
		return '';
	}

	/** Valid period chips for a report page mode (and selected month, for RankMonth MTD). */
	public static function spanOptionsFor($mode, $month = 0, $type = null) {
		if ($mode === 'rank-annual') {
			return ['full', 'ytd', 'winter', 'spring', 'summer', 'autumn'];
		}
		if ($mode === 'rank-monthly') {
			$opts = ['full', 'h1', 'h2'];
			if ((int)$month === (int)Date::$dmonth) {
				array_splice($opts, 1, 0, ['mtd']);
			}
			return $opts;
		}
		if ($mode === 'monthly') {
			return ['full', 'ytd', 'seasonal'];
		}
		return ['full'];
	}

	public static function spanChipLabels($mode, $month = 0, $type = null) {
		$all = array(
			'full' => array('Full', 'Full period'),
			'mtd' => array('MTD', 'Month-to-date (' . Data::spanRangeText('mtd') . ')'),
			'ytd' => array('YTD', 'Year-to-date (' . Data::spanRangeText('ytd') . ')'),
			'seasonal' => array('Seasonal', 'Seasonal view (Winter–Autumn)'),
			'winter' => array('Winter', 'Winter (Dec–Feb)'),
			'spring' => array('Spring', 'Spring (Mar–May)'),
			'summer' => array('Summer', 'Summer (Jun–Aug)'),
			'autumn' => array('Autumn', 'Autumn (Sep–Nov)'),
			'h1' => array('1st half', '1st half (days 1–15)'),
			'h2' => array('2nd half', '2nd half (day 16–end of month)'),
		);
		$out = array();
		# Rank-monthly always lists MTD (visible from any month; the chip jumps to the current month).
		$keys = self::spanOptionsFor($mode, $month, $type);
		if ($mode === 'rank-monthly' && !in_array('mtd', $keys, true)) {
			array_splice($keys, 1, 0, ['mtd']);
		}
		foreach ($keys as $k) {
			if (isset($all[$k])) { $out[$k] = $all[$k]; }
		}
		return $out;
	}

	/** Coerce $this->span to a value allowed on this page. */
	public function normalizeSpanFor($mode) {
		if ($this->isAnnualOnly()) {
			$this->span = 'full';
			return;
		}
		if ($mode === 'monthly' && Data::isSeasonSpan($this->span)) {
			$this->span = 'seasonal';
			return;
		}
		if ($mode === 'monthly' && $this->span === 'mtd') {
			$this->span = 'ytd';
			return;
		}
		if ($mode === 'rank-monthly' && $this->span === 'mtd') {
			$this->month = (int)Date::$dmonth;
		}
		$allowed = self::spanOptionsFor($mode, $this->month, $this->type);
		if (!in_array($this->span, $allowed, true)) {
			$this->span = 'full';
		}
	}

	/** "13 Nov" or "13 Nov ±5 days" for a rank-daily specific date. */
	public function rankDayDateLabel() {
		if ($this->day <= 0 || $this->month < 1 || $this->month > 12) { return ''; }
		$lab = $this->day . ' ' . Date::$months3[$this->month - 1];
		if ($this->win > 0) { $lab .= ' ±' . $this->win . ' days'; }
		return $lab;
	}

	/** Anomaly vs the climate normal for a (possibly year-crossing) bounds array. */
	public function anomBounds($value, $b) {
		if (!is_array($b)) { return ''; }
		$norm = LTA::getRangeAnom($this->type, $b['sy'], $b['sm'], $b['sd'], $b['ey'], $b['em'], $b['ed']);
		if ($norm === null) { return ''; }
		if ($this->isSum) {
			return Util::percent($value, $norm, 0, true, false);
		}
		return Wx::conv($value - $norm, Wx::AbsTemp, 0, 1);
	}

	/** Anomaly of $value vs the climate normal for an inclusive calendar span. */
	public function anomSpan($value, $startMonth, $startDay, $endMonth, $endDay, $year) {
		$norm = LTA::getSpanAnom($this->type, $startMonth, $startDay, $endMonth, $endDay, $year);
		if ($norm === null) { return ''; }
		if ($this->isSum) {
			return Util::percent($value, $norm, 0, true, false);
		}
		return Wx::conv($value - $norm, Wx::AbsTemp, 0, 1);
	}

	/** Title suffix including period length for RankPeriods. */
	public function periodTitleSuffix($summaryType = null) {
		$st = ($summaryType === null) ? $this->summaryType : (int)$summaryType;
		$cap = $this->summaryCaptionName($st);
		if ($st === Data::SUMMARY_ANOM) {
			return $this->description . ' · ' . $this->periodLength . '-day anomaly vs 1991-2020 normal';
		}
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
		$anom = ($this->spellThresholds === Spells::$anomPresets);
		foreach ($this->spellThresholds as $th) {
			$labels[] = $anom ? self::anomThresholdLabel($th) : Wx::plainText(Wx::conv($th, $this->unit, false));
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
		if (isset(Wx::$daily[$gType]) && !empty(Wx::$daily[$gType]['annual_only']) && empty($opts['allowAnnualOnly'])) {
			$gType = $default;
		}
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
		$yearsParam = isset($_GET['years']) ? strtolower(trim((string)$_GET['years'])) : '';
		$this->showRecordYears = !in_array($yearsParam, array('0', 'off', 'false', 'no'), true);

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
		$this->day = isset($_GET['day']) ? (int)$_GET['day'] : 0;
		if ($this->day > 0) {
			if ($this->month < 1 || $this->month > 12) {
				$this->month = (int)Date::$dmonth;
			}
			# Allow 29 Feb (leap years only contribute data).
			$maxD = (int)Date::get_days_in_month($this->month, 2000);
			if ($this->day > $maxD) { $this->day = $maxD; }
		} else {
			$this->day = 0;
		}
		$this->win = 0;
		if ($this->day > 0 && isset($_GET['win'])) {
			$w = (int)$_GET['win'];
			if (in_array($w, [3, 5, 10], true)) { $this->win = $w; }
		}

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

		// Spell ranking selectors (heatwave / cold snap only for temperature normals).
		$dirIn = isset($_GET['spell_dir']) ? (string)$_GET['spell_dir'] : 'above';
		if ($dirIn === 'below') {
			$this->spellDir = 'below';
		} elseif (Spells::isAnomalyDir($dirIn) && Spells::supportsAnomaly($this->type)) {
			$this->spellDir = $dirIn;
		} else {
			$this->spellDir = 'above';
		}
		$this->periodAnom = !empty($opts['periodAnom']) && Spells::supportsAnomaly($this->type);
		$gSum = isset($_GET['summary_type']) ? (int)$_GET['summary_type'] : 0;
		$anomThresh = !empty($opts['spellPage'])
			? Spells::isAnomalyDir($this->spellDir)
			: ($this->periodAnom && $gSum === Data::SUMMARY_ANOM);
		// Spell / count-threshold selectors share the same preset list; anomaly
		// modes use °C-from-normal presets instead.
		$this->spellThresholds = $anomThresh ? Spells::$anomPresets : Wx::thresholdPresets($this->type);
		$defThresh = $anomThresh ? Spells::ANOM_DEFAULT : Wx::defaultThreshold($this->type, $this->unit);
		if (isset($_GET['threshold']) && nw3_is_numeric($_GET['threshold'])) {
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
		// Annual-only date variables (e.g. first air frost) have a single implicit view.
		if (!empty($this->meta['annual_only'])) {
			$this->availSummaryTypes = [Data::SUMMARY_MEAN];
		} else {
		$this->availSummaryTypes = [(int)$this->isSum];
		array_push($this->availSummaryTypes, Data::SUMMARY_MIN, Data::SUMMARY_MAX);
		if (!$this->isNotSummarisable) {
			if (!$this->isCountOnly) {
				$this->availSummaryTypes[] = Data::SUMMARY_RANGE;
			}
			$this->availSummaryTypes[] = Data::SUMMARY_COUNT_ABOVE;
			$this->availSummaryTypes[] = Data::SUMMARY_COUNT_BELOW;
		}
		if ($this->periodAnom) {
			$this->availSummaryTypes[] = Data::SUMMARY_ANOM;
		}
		}

		$g = isset($_GET['summary_type']) ? (int)$_GET['summary_type'] : 0;
		if ($g < 0 || $g > 8) { $g = 0; }
		if ($g <= 1) { $g = (int)$this->isSum; }
		if (!in_array($g, $this->availSummaryTypes, true)) { $g = $this->availSummaryTypes[0]; }
		$this->summaryType = $g;

		$plen = isset($_GET['period']) ? (int)$_GET['period'] : 5;
		if (!in_array($plen, self::$periodLengthOptions, true)) { $plen = 5; }
		$this->periodLength = $plen;
		// Hide-overlapping defaults to ON (disallow overlaps).
		$this->periodNoOverlap = isset($_GET['no_overlap']) ? (int)$_GET['no_overlap'] !== 0 : true;

		$spanIn = isset($_GET['span']) ? strtolower((string)$_GET['span']) : 'full';
		$this->span = in_array($spanIn, self::$spanOptions, true) ? $spanIn : 'full';

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
			// Temperature anomaly vs normal, symmetric about zero (shaded in °C).
			15 => [-8, -6, -4, -2, 0, 2, 4, 6, 8],
			13 => [25, 50, 75, 90, 100, 110, 125, 150, 175, 200, 250],
			// Shaded on the converted value: AQI, DAQI index (band n -> level n-1) or raw PM2.5.
			14 => (Page::aqUnits() === 'aqi') ? [25, 50, 75, 100, 125, 150, 200, 300, 500]
				: ((Page::aqUnits() === 'daqi') ? [1, 2, 3, 4, 5, 6, 7, 8, 9] : Wx::DAQI_PM25),
		];
		self::$thresholdsReady = true;
	}

	/**
	 * Returns the CSS class for a (converted) value, based on this variable's scheme.
	 * @param float $value the value to shade
	 * @param bool $countable use the day-count scale instead of the value scale
	 */
	public function valcolr($value, $countable = false) {
		if ($this->valcolIdxOverride !== null && !$countable) {
			return self::valcolForIdx($this->valcolIdxOverride, $value);
		}
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

	/** Value-colour class for an explicit scheme index (e.g. 15 = temperature anomaly). */
	public static function valcolForIdx($idx, $value) {
		self::initThresholds();
		if (!isset(self::$valcolLevel[$idx], self::$thresholds[$idx])) { return 'reportday'; }
		$level = self::$valcolLevel[$idx];
		$values = self::$thresholds[$idx];
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
		return DataSummarizer::nearestStartYear($want, $opts);
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

	/**
	 * Wrap an anomaly string for a table cell, collapsing to nothing when the
	 * variable has no climate normal (the anom* methods return '' for those).
	 * @param string $anom output of anomMonth / anomYear / anomMonthCum
	 * @return string
	 */
	public function anomCell($anom) {
		return ($anom === '' || $anom === null) ? '' : '<br />(' . $anom . ')';
	}

	/** Daily/monthly/annual reports are available from 2009 (monthly from Feb 2009). */
	const HIST_REPORT_YEAR = 2009;
	/** Periods this long (or longer) link to daily data tables instead of start/end daily reports. */
	const LONG_RANGE_DAYS = 32;

	public static function reportDayUrl($year, $month, $day) {
		$year = (int)$year; $month = (int)$month; $day = (int)$day;
		if ($year < self::HIST_REPORT_YEAR || $month < 1 || $month > 12 || $day < 1 || $day > 31) {
			return null;
		}
		return '/wxhistday.php?year=' . $year . '&month=' . $month . '&day=' . $day;
	}

	public static function reportMonthUrl($year, $month) {
		$year = (int)$year; $month = (int)$month;
		if ($month < 1 || $month > 12) { return null; }
		if ($year < self::HIST_REPORT_YEAR || ($year === self::HIST_REPORT_YEAR && $month < 2)) {
			return null;
		}
		return '/wxhistmonth.php?year=' . $year . '&month=' . $month;
	}

	public static function reportYearUrl($year) {
		$year = (int)$year;
		if ($year < self::HIST_REPORT_YEAR) { return null; }
		return '/wxhistyear.php?year=' . $year;
	}

	public static function dataDayTableUrl($type, $year) {
		return '/wxdataday.php?vartype=' . rawurlencode((string)$type) . '&year=' . (int)$year;
	}

	/**
	 * @param string $href
	 * @param string $text already-safe visible text (no user HTML)
	 * @param string $title
	 * @param bool $bold
	 * @return string
	 */
	public static function hiddenDateLink($href, $text, $title, $bold = false) {
		if ($href) {
			$html = '<a class="hidden-link" href="' . htmlspecialchars($href)
				. '" title="' . htmlspecialchars($title) . '">' . $text . '</a>';
		} else {
			$html = $text;
		}
		return $bold ? ('<b>' . $html . '</b>') : $html;
	}

	/** Clickable single-day label for ranking tables. */
	public static function dayDateHtml($ts, $format = 'd M Y') {
		$y = (int)date('Y', $ts);
		$m = (int)date('n', $ts);
		$d = (int)date('j', $ts);
		$star = ($y < Site::BASE_YEAR) ? '*' : '';
		$bold = ((Date::$dtstamp - $ts) < 86400 * 365);
		$label = date($format, $ts) . $star;
		$url = self::reportDayUrl($y, $m, $d);
		return self::hiddenDateLink($url, $label, 'View daily report for ' . date('j M Y', $ts), $bold);
	}

	/** Clickable month label (e.g. "Nov 2025") for ranking tables. */
	public static function monthDateHtml($ts) {
		$y = (int)date('Y', $ts);
		$m = (int)date('n', $ts);
		$star = ($y < Site::BASE_YEAR) ? '*' : '';
		$bold = ((Date::$dtstamp - $ts) < 86400 * 365);
		$label = date('M Y', $ts) . $star;
		$url = self::reportMonthUrl($y, $m);
		return self::hiddenDateLink($url, $label, 'View monthly report for ' . date('F Y', $ts), $bold);
	}

	/**
	 * Clickable year label. 2009+ goes to the annual report; earlier years
	 * fall back to the daily data table for that year (no annual report exists).
	 */
	public static function yearDateHtml($ts, $type = 'rain') {
		$y = (int)date('Y', $ts);
		$star = ($y < Site::BASE_YEAR) ? '*' : '';
		$bold = ((Date::$dtstamp - $ts) < 86400 * 365);
		$label = (string)$y . $star;
		$url = self::reportYearUrl($y);
		if ($url) {
			return self::hiddenDateLink($url, $label, 'View annual report for ' . $y, $bold);
		}
		return self::hiddenDateLink(self::dataDayTableUrl($type, $y), $label, 'View daily data for ' . $y, $bold);
	}

	/**
	 * Clickable start–end range for spell and period rankings.
	 * Short ranges: start and end each link to their daily report (2009+).
	 * Ranges of LONG_RANGE_DAYS or more: link to the daily data table for the
	 * year (or start and end years if the window crosses a year boundary).
	 * @param int $startTs
	 * @param int $endTs
	 * @param string $type variable name for daily-table links
	 * @param array $opts ongoing?: bool (end label "Current")
	 * @return string
	 */
	public static function rangeDateHtml($startTs, $endTs, $type = 'rain', $opts = []) {
		$ongoing = !empty($opts['ongoing']);
		if (!$startTs) { return ''; }
		if (!$endTs) { $endTs = $startTs; }
		$sy = (int)date('Y', $startTs); $sm = (int)date('n', $startTs); $sd = (int)date('j', $startTs);
		$ey = (int)date('Y', $endTs); $em = (int)date('n', $endTs); $ed = (int)date('j', $endTs);
		$days = (int)round(($endTs - $startTs) / 86400) + 1;
		$star = ($sy < Site::BASE_YEAR) ? '*' : '';
		$bold = ((Date::$dtstamp - $endTs) < 86400 * 365);
		$long = ($days >= self::LONG_RANGE_DAYS);

		if ($sy === $ey && $sm === $em) {
			$startText = date('d', $startTs);
			$endText = date('d M Y', $endTs);
			$plain = $startText . '–' . $endText . $star;
		} elseif ($sy === $ey) {
			$startText = date('d M', $startTs);
			$endText = date('d M Y', $endTs);
			$plain = $startText . ' – ' . $endText . $star;
		} else {
			$startText = date('d M Y', $startTs);
			$endText = date('d M Y', $endTs);
			$plain = $startText . ' – ' . $endText . $star;
		}
		if ($ongoing) {
			$endText = 'Current';
			$plain = date('d M Y', $startTs) . ' – Current' . $star;
		}

		if ($long) {
			$startUrl = self::dataDayTableUrl($type, $sy);
			if ($sy === $ey && !$ongoing) {
				return self::hiddenDateLink($startUrl, $plain, 'View daily data for ' . $sy, $bold);
			}
			$endUrl = $ongoing ? self::reportDayUrl((int)Date::$dyear, (int)Date::$dmonth, (int)Date::$dday) : self::dataDayTableUrl($type, $ey);
			$startTitle = 'View daily data for ' . $sy;
			$endTitle = $ongoing ? 'View daily report for today' : ('View daily data for ' . $ey);
			$html = self::hiddenDateLink($startUrl, $startText, $startTitle)
				. ($sy === $ey && $sm === $em && !$ongoing ? '–' : ' – ')
				. self::hiddenDateLink($endUrl, $endText, $endTitle) . $star;
			return $bold ? ('<b>' . $html . '</b>') : $html;
		}

		$startUrl = self::reportDayUrl($sy, $sm, $sd);
		$endUrl = $ongoing
			? self::reportDayUrl((int)Date::$dyear, (int)Date::$dmonth, (int)Date::$dday)
			: self::reportDayUrl($ey, $em, $ed);
		$sep = ($sy === $ey && $sm === $em && !$ongoing) ? '–' : ' – ';
		$html = self::hiddenDateLink($startUrl, $startText, 'View daily report for ' . date('j M Y', $startTs))
			. $sep
			. self::hiddenDateLink($endUrl, $endText, $ongoing ? 'View daily report for today' : ('View daily report for ' . date('j M Y', $endTs)))
			. $star;
		return $bold ? ('<b>' . $html . '</b>') : $html;
	}

	/**
	 * Linked MTD/YTD span text (no year prefix), following the short/long range rule.
	 * MTD: "1–12 Nov". YTD: "1 Jan–12 Nov".
	 * $endTextExtra is appended to the end label (e.g. " 2025" for month-row MTD).
	 */
	public static function spanRangeLinkedHtml($span, $year, $endMonth, $endDay, $type, $endTextExtra = '') {
		$year = (int)$year;
		$endMonth = (int)$endMonth;
		$endDay = Data::clampDay($year, $endMonth, $endDay);
		if ($span === 'mtd') {
			$startTs = Date::mkdate($endMonth, 1, $year);
			$endTs = Date::mkdate($endMonth, $endDay, $year);
			$startText = '1';
			$sep = '–';
		} else {
			$startTs = Date::mkdate(1, 1, $year);
			$endTs = Date::mkdate($endMonth, $endDay, $year);
			$startText = '1 Jan';
			$sep = '–';
		}
		$endText = $endDay . ' ' . Date::$months3[$endMonth - 1] . $endTextExtra;
		$days = (int)round(($endTs - $startTs) / 86400) + 1;
		$plain = $startText . $sep . $endText;
		if ($days >= self::LONG_RANGE_DAYS) {
			return self::hiddenDateLink(self::dataDayTableUrl($type, $year), $plain, 'View daily data for ' . $year);
		}
		$sm = ($span === 'mtd') ? $endMonth : 1;
		$startUrl = self::reportDayUrl($year, $sm, 1);
		$endUrl = self::reportDayUrl($year, $endMonth, $endDay);
		return self::hiddenDateLink($startUrl, $startText, 'View daily report for ' . date('j M Y', $startTs))
			. $sep
			. self::hiddenDateLink($endUrl, $endText, 'View daily report for ' . date('j M Y', $endTs));
	}

	/** Linked start–end text for an inclusive day range, following the short/long range rule. */
	public static function boundsRangeLinkedHtml($b, $type, $startText, $endText, $sep = '–') {
		$startTs = Date::mkdate($b['sm'], $b['sd'], $b['sy']);
		$endTs = Date::mkdate($b['em'], $b['ed'], $b['ey']);
		$days = Data::boundsDayCount($b);
		$plain = $startText . $sep . $endText;
		if ($days >= self::LONG_RANGE_DAYS) {
			if ($b['sy'] === $b['ey']) {
				return self::hiddenDateLink(self::dataDayTableUrl($type, $b['sy']), $plain, 'View daily data for ' . $b['sy']);
			}
			return self::hiddenDateLink(self::dataDayTableUrl($type, $b['sy']), $startText, 'View daily data for ' . $b['sy'])
				. $sep
				. self::hiddenDateLink(self::dataDayTableUrl($type, $b['ey']), $endText, 'View daily data for ' . $b['ey']);
		}
		$startUrl = self::reportDayUrl($b['sy'], $b['sm'], $b['sd']);
		$endUrl = self::reportDayUrl($b['ey'], $b['em'], $b['ed']);
		return self::hiddenDateLink($startUrl, $startText, 'View daily report for ' . date('j M Y', $startTs))
			. $sep
			. self::hiddenDateLink($endUrl, $endText, 'View daily report for ' . date('j M Y', $endTs));
	}

	/**
	 * Link a month-only range endpoint: monthly report when it exists,
	 * otherwise the long-range daily data table for that year.
	 */
	public static function monthEndLink($year, $month, $text, $type) {
		$year = (int)$year;
		$month = (int)$month;
		$url = self::reportMonthUrl($year, $month);
		if ($url) {
			$ts = Date::mkdate($month, 1, $year);
			return self::hiddenDateLink($url, $text, 'View monthly report for ' . date('F Y', $ts));
		}
		return self::hiddenDateLink(self::dataDayTableUrl($type, $year), $text, 'View daily data for ' . $year);
	}

	/** Seasonal ranking date. Complete seasons: months only, e.g. "Winter 2026 (Dec 2025–Feb 2026)". */
	public static function seasonDateHtml($year, $span, $type = 'rain') {
		$year = (int)$year;
		$si = Data::seasonIndex($span);
		$b = Data::seasonBounds($span, $year, true);
		if ($si === null || $b === null) { return (string)$year; }
		$name = Date::$snames[$si];
		$star = ($b['sy'] < Site::BASE_YEAR) ? '*' : '';
		$endTs = Date::mkdate($b['em'], $b['ed'], $b['ey']);
		$bold = ((Date::$dtstamp - $endTs) < 86400 * 365);
		$complete = Data::seasonIsComplete($span, $year);
		if ($complete) {
			if ($b['sy'] === $b['ey']) {
				$startText = Date::$months3[$b['sm'] - 1];
				$endText = Date::$months3[$b['em'] - 1] . ' ' . $b['ey'];
			} else {
				$startText = Date::$months3[$b['sm'] - 1] . ' ' . $b['sy'];
				$endText = Date::$months3[$b['em'] - 1] . ' ' . $b['ey'];
			}
			$range = self::monthEndLink($b['sy'], $b['sm'], $startText, $type)
				. '–'
				. self::monthEndLink($b['ey'], $b['em'], $endText, $type);
		} else {
			if ($b['sy'] === $b['ey']) {
				$startText = $b['sd'] . ' ' . Date::$months3[$b['sm'] - 1];
				$endText = $b['ed'] . ' ' . Date::$months3[$b['em'] - 1];
			} else {
				$startText = $b['sd'] . ' ' . Date::$months3[$b['sm'] - 1] . ' ' . $b['sy'];
				$endText = $b['ed'] . ' ' . Date::$months3[$b['em'] - 1] . ' ' . $b['ey'];
			}
			$range = self::boundsRangeLinkedHtml($b, $type, $startText, $endText);
		}
		$html = $name . ' ' . $year . $star . ' (' . $range . ')';
		return $bold ? ('<b>' . $html . '</b>') : $html;
	}

	/** Half-month ranking date, e.g. "1–15 Nov 2024", start/end daily reports. */
	public static function halfDateHtml($ts, $span, $type = 'rain') {
		$y = (int)date('Y', $ts);
		$m = (int)date('n', $ts);
		$b = Data::halfBounds($span, $y, $m, true);
		if ($b === null) { return self::monthDateHtml($ts); }
		$star = ($y < Site::BASE_YEAR) ? '*' : '';
		$bold = ((Date::$dtstamp - $ts) < 86400 * 365);
		$startText = (string)$b['sd'];
		$endText = $b['ed'] . ' ' . Date::$months3[$m - 1] . ' ' . $y;
		$html = self::boundsRangeLinkedHtml($b, $type, $startText, $endText) . $star;
		return $bold ? ('<b>' . $html . '</b>') : $html;
	}

	/** Month ranking date, including an MTD/YTD or half-month span when selected. */
	public static function monthSpanDateHtml($ts, $span, $endMonth, $endDay, $type = 'rain') {
		if ($span === 'h1' || $span === 'h2') {
			return self::halfDateHtml($ts, $span, $type);
		}
		if ($span !== 'mtd' && $span !== 'ytd') {
			return self::monthDateHtml($ts);
		}
		$y = (int)date('Y', $ts);
		$m = (int)date('n', $ts);
		$star = ($y < Site::BASE_YEAR) ? '*' : '';
		$bold = ((Date::$dtstamp - $ts) < 86400 * 365);
		if ($span === 'mtd') {
			$html = self::spanRangeLinkedHtml('mtd', $y, $m, $endDay, $type, ' ' . $y) . $star;
		} else {
			$yearUrl = self::reportYearUrl($y);
			$yearHtml = $yearUrl
				? self::hiddenDateLink($yearUrl, (string)$y, 'View annual report for ' . $y)
				: self::hiddenDateLink(self::dataDayTableUrl($type, $y), (string)$y, 'View daily data for ' . $y);
			$html = $yearHtml . $star . ' (' . self::spanRangeLinkedHtml('ytd', $y, $m, $endDay, $type) . ')';
		}
		return $bold ? ('<b>' . $html . '</b>') : $html;
	}

	/** Annual ranking date, including a YTD or season span when selected. */
	public static function yearSpanDateHtml($ts, $span, $endMonth, $endDay, $type = 'rain') {
		if (Data::isSeasonSpan($span)) {
			return self::seasonDateHtml((int)date('Y', $ts), $span, $type);
		}
		if ($span !== 'mtd' && $span !== 'ytd') {
			return self::yearDateHtml($ts, $type);
		}
		$y = (int)date('Y', $ts);
		$star = ($y < Site::BASE_YEAR) ? '*' : '';
		$bold = ((Date::$dtstamp - $ts) < 86400 * 365);
		$yearUrl = self::reportYearUrl($y);
		$yearHtml = $yearUrl
			? self::hiddenDateLink($yearUrl, (string)$y, 'View annual report for ' . $y)
			: self::hiddenDateLink(self::dataDayTableUrl($type, $y), (string)$y, 'View daily data for ' . $y);
		$html = $yearHtml . $star . ' (' . self::spanRangeLinkedHtml($span, $y, (int)$endMonth, $endDay, $type) . ')';
		return $bold ? ('<b>' . $html . '</b>') : $html;
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
		$idx = nw3_array_search($this->type, $flat, true);
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
		if ($mode === 'monthly' || $mode === 'rank-monthly' || $mode === 'rank-annual') {
			$this->normalizeSpanFor($mode);
		}

		$unitTxt0 = Wx::getUnitsText($this->unit);
		$titleSuffix = ($unitTxt0 === '') ? $this->description : ($this->description . ' / ' . $unitTxt0);
		if ($mode === 'daily' && $this->dayAgg !== '') {
			$aggTitle = array('min' => 'Min', 'max' => 'Max', 'mean' => 'Mean');
			$titleSuffix .= ' · ' . $aggTitle[$this->dayAgg] . ' (all years)';
		}
		if ($mode === 'rank-daily' && $this->day > 0) {
			$titleSuffix .= ' · ' . $this->rankDayDateLabel();
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

		$groups = self::buttonSelectorGroups($mode);
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
				$params['span'] = $this->span;
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
				if ($mode === 'rank-daily' && $this->day > 0) {
					$params['day'] = $this->day;
					if ($this->win > 0) { $params['win'] = $this->win; }
				}
				if ($mode === 'rank-monthly' || $mode === 'rank-annual' || $mode === 'rank-periods') {
					$params['summary_type'] = $this->summaryType;
					if ($this->needsThreshold()) {
						$params['threshold'] = $this->spellThreshold;
					}
				}
				if ($mode === 'rank-monthly' || $mode === 'rank-annual') {
					$params['span'] = $this->span;
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
			if (isset($params['day']) && (int)$params['day'] <= 0) {
				unset($params['day'], $params['win']);
			}
			if (isset($params['win']) && (int)$params['win'] <= 0) { unset($params['win']); }
			if ($mode === 'daily') {
				if (isset($overrides['year'])) {
					unset($params['agg'], $params['start_year_rep']);
				}
				if (isset($overrides['agg'])) { unset($params['year']); }
				$showY = $this->showRecordYears;
				if (array_key_exists('years', $overrides)) {
					$showY = ((string)$overrides['years'] !== '0');
				}
				if ($showY) { unset($params['years']); }
				else { $params['years'] = 0; }
			}
			return htmlspecialchars(Page::$pageName . '?' . http_build_query($params));
		};

		echo '<div class="report-sel" id="report-sel" data-mode="' . htmlspecialchars($mode) . '"'
			. ' data-type="' . htmlspecialchars($this->type) . '"'
			. ' data-year="' . (int)$this->year . '"'
			. ' data-agg="' . htmlspecialchars($this->dayAgg) . '"'
			. ' data-month="' . (int)$this->month . '"'
			. ' data-day="' . (int)$this->day . '"'
			. ' data-win="' . (int)$this->win . '"'
			. ' data-start-year-rep="' . (int)$this->startYrReport . '"'
			. ' data-summary-type="' . (int)$this->summaryType . '"'
			. ' data-rank-limit="' . (int)$this->rankLimit . '"'
			. ' data-spell-dir="' . htmlspecialchars($this->spellDir) . '"'
			. ' data-threshold="' . htmlspecialchars((string)$this->spellThreshold) . '"'
			. ' data-period="' . (int)$this->periodLength . '"'
			. ' data-no-overlap="' . ($this->periodNoOverlap ? '1' : '0') . '"'
			. ' data-span="' . htmlspecialchars($this->span) . '"'
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
			$sumRowHidden = (count($this->availSummaryTypes) <= 1) ? ' hidden' : '';
			echo '<div class="report-sel-row report-sel-labelled"' . $sumRowHidden . '>';
			echo '<div class="wxsel-label">' . htmlspecialchars($sumLabel) . '</div>';
			echo '<div class="wxsel-scale wxsel-summary" role="tablist">';
			foreach ($this->availSummaryTypes as $st) {
				$active = ($st === $this->summaryType) ? ' active' : '';
				$label = ucfirst(Data::$SUMMARY_NAMES[$st]);
				$hrefParams = array('summary_type' => $st);
				if ($this->needsThreshold($st)) {
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
		$thLabels = $this->thresholdLabels();
		foreach ($this->spellThresholds as $ti => $th) {
			$active = (abs($th - $this->spellThreshold) < 1e-9) ? ' active' : '';
			$label = $thLabels[$ti];
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

		$showSpan = !empty($o['showSpan']) || ($mode === 'monthly' || $mode === 'rank-monthly' || $mode === 'rank-annual');
		if ($showSpan) {
			$spanLabels = self::spanChipLabels($mode, $this->month, $this->type);
			$mtdMonth = (int)Date::$dmonth;
			$spanRowHidden = ($mode === 'rank-annual' && $this->isAnnualOnly()) ? ' hidden' : '';
			echo '<div class="report-sel-row report-sel-labelled"' . $spanRowHidden . '>';
			echo '<div class="wxsel-label">Period</div>';
			echo '<div class="wxsel-scale wxsel-span" role="tablist">';
			foreach ($spanLabels as $key => $pair) {
				$active = ($key === $this->span) ? ' active' : '';
				$hrefParams = array('span' => $key);
				$extra = '';
				if ($mode === 'rank-monthly' && $key === 'mtd') {
					$hrefParams['month'] = $mtdMonth;
					$extra = ' data-month="' . $mtdMonth . '"';
				}
				echo '<a class="wxsel-chip' . $active . '" data-span="' . $key . '"' . $extra
					. ' href="' . $selUrl($hrefParams) . '" title="'
					. htmlspecialchars($pair[1]) . '">' . $pair[0] . '</a>';
			}
			echo '</div></div>';
		}

		if ($showMonth) {
			if ($mode === 'rank-spells') { $monthLabel = 'Midpoint month'; }
			elseif ($mode === 'rank-periods') { $monthLabel = 'Month start'; }
			else { $monthLabel = 'Month'; }
			$dateMode = ($mode === 'rank-daily' && $this->day > 0);
			echo '<div class="report-sel-row report-sel-labelled">';
			echo '<div class="wxsel-label">' . htmlspecialchars($monthLabel) . '</div>';
			echo '<div class="wxsel-scale wxsel-months" role="tablist">';
			$active = (!$dateMode && $this->month === 0) ? ' active' : '';
			echo '<a class="wxsel-chip' . $active . '" data-month="0" href="'
				. $selUrl(array('month' => 0, 'day' => 0)) . '">All</a>';
			for ($m = 1; $m <= 12; $m++) {
				$active = (!$dateMode && $m === $this->month) ? ' active' : '';
				echo '<a class="wxsel-chip' . $active . '" data-month="' . $m
					. '" href="' . $selUrl(array('month' => $m, 'day' => 0)) . '">'
					. Date::$months3[$m - 1] . '</a>';
			}
			if ($mode === 'rank-daily') {
				$dm = $dateMode ? (int)$this->month : (int)Date::$dmonth;
				$dd = $dateMode ? (int)$this->day : (int)Date::$dday;
				$active = $dateMode ? ' active' : '';
				echo '<a class="wxsel-chip' . $active . '" data-datesel="1" data-month="'
					. $dm . '" data-day="' . $dd . '" href="'
					. $selUrl(array('month' => $dm, 'day' => $dd))
					. '" title="Specific date">Specific date</a>';
			}
			echo '</div></div>';
			if ($mode === 'rank-daily') {
				$dm = $dateMode ? (int)$this->month : (int)Date::$dmonth;
				$dd = $dateMode ? (int)$this->day : (int)Date::$dday;
				$maxD = (int)Date::get_days_in_month($dm, 2000);
				$hide = $dateMode ? '' : ' hidden';
				echo '<div class="report-sel-row report-sel-labelled" id="report-sel-datesel"' . $hide . '>';
				echo '<div class="wxsel-label">Date</div>';
				echo '<div class="wxsel-date-wrap">';
				echo '<form class="wxsel-date-form" method="get" action="'
					. htmlspecialchars(Page::$pageName) . '" id="wxsel-date-form">';
				echo '<input type="hidden" name="vartype" value="' . htmlspecialchars($this->type) . '" />';
				echo '<input type="hidden" name="start_year_rep" value="' . (int)$this->startYrReport . '" />';
				echo '<input type="hidden" name="rankLimit" value="' . (int)$this->rankLimit . '" />';
				echo '<input type="hidden" name="win" id="wxsel-date-win" value="' . (int)$this->win . '" />';
				echo '<select name="month" id="wxsel-date-month" aria-label="Month">';
				for ($m = 1; $m <= 12; $m++) {
					$sel = ($m === $dm) ? ' selected="selected"' : '';
					echo '<option value="' . $m . '"' . $sel . '>' . Date::$months[$m - 1] . '</option>';
				}
				echo '</select>';
				echo '<select name="day" id="wxsel-date-day" aria-label="Day">';
				for ($d = 1; $d <= $maxD; $d++) {
					$sel = ($d === $dd) ? ' selected="selected"' : '';
					echo '<option value="' . $d . '"' . $sel . '>' . $d . '</option>';
				}
				echo '</select>';
				echo '<noscript><button type="submit" class="wxsel-chip">Show</button></noscript>';
				echo '</form>';
				echo '<div class="wxsel-scale wxsel-win" id="wxsel-win" role="tablist">';
				foreach (self::$dateWinOptions as $w) {
					$lab = ($w === 0) ? 'Exact' : ('±' . $w);
					$title = ($w === 0) ? 'Exact date' : ('±' . $w . ' days');
					$active = ((int)$this->win === $w) ? ' active' : '';
					echo '<a class="wxsel-chip' . $active . '" data-win="' . $w
						. '" href="' . $selUrl(array('month' => $dm, 'day' => $dd, 'win' => $w))
						. '" title="' . $title . '">' . $lab . '</a>';
				}
				echo '</div></div></div>';
			}
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

		if ($mode === 'daily') {
			$yearsOn = !empty($this->showRecordYears);
			$yearsRowHidden = ($this->dayAgg === 'min' || $this->dayAgg === 'max') ? '' : ' hidden';
			echo '<div class="report-sel-row report-sel-labelled" id="report-sel-show-years"'
				. $yearsRowHidden . '>';
			echo '<form class="dd-years-form" id="dd-years-form" method="get" action="'
				. htmlspecialchars(Page::$pageName) . '">';
			echo '<input type="hidden" name="vartype" value="' . htmlspecialchars($this->type) . '" />';
			if ($this->dayAgg !== '') {
				echo '<input type="hidden" name="agg" value="' . htmlspecialchars($this->dayAgg) . '" />';
				echo '<input type="hidden" name="start_year_rep" value="' . (int)$this->startYrReport . '" />';
			} else {
				echo '<input type="hidden" name="year" value="' . (int)$this->year . '" />';
			}
			echo '<input type="hidden" name="years" value="0" />';
			echo '<label class="dd-years-label"><input type="checkbox" id="dd-show-years" name="years" value="1"'
				. ($yearsOn ? ' checked="checked"' : '') . ' /> Show years</label>';
			echo '<noscript><button type="submit" class="wxsel-chip">Update</button></noscript>';
			echo '</form>';
			echo '</div>';
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
		if ($mode === 'daily') {
			$hydrateJs = __DIR__ . '/reporthydrate.js';
			$ver = is_file($hydrateJs) ? filemtime($hydrateJs) : time();
			echo '<script src="/reporthydrate.js?' . (int)$ver . '"></script>' . "\n";
		}

		$cfg = array(
			'groups' => $groups,
			'mode' => $mode,
			'type' => $this->type,
			'year' => (int)$this->year,
			'agg' => $this->dayAgg,
			'month' => (int)$this->month,
			'day' => (int)$this->day,
			'win' => (int)$this->win,
			'currentDay' => (int)Date::$dday,
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
			'span' => $this->span,
			'currentMonth' => (int)Date::$dmonth,
			'unit' => (int)$this->unit,
			'page' => Page::$pageName,
			'fragment' => $ajaxFragment !== '' ? $ajaxFragment : null,
			'bodyId' => $ajaxBodyId,
			'headingPrefix' => $heading,
			'fragId' => isset($fragIds[$mode]) ? $fragIds[$mode] : 'dd-fragment',
			'showYears' => $this->showRecordYears ? 1 : 0,
			'annualOnlyTypes' => self::annualOnlyTypes(),
		);
		echo '<script>NW3_reportSel(' . nw3_json_encode($cfg) . ');</script>';
	}


	/** Variable names that exist only on annual rankings (not daily/monthly/charts). */
	public static function annualOnlyTypes() {
		$out = [];
		foreach (Wx::$daily as $k => $meta) {
			if (!empty($meta['annual_only'])) { $out[] = $k; }
		}
		return $out;
	}

	public function isAnnualOnly() {
		return !empty($this->meta['annual_only']);
	}

	/** Linked "14 Nov" / "17 Mar 1975", or "none" / "none yet (so far)". */
	public static function firstFrostValueHtml($row) {
		if (!empty($row['none'])) {
			return !empty($row['provisional']) ? 'none yet (so far)' : 'none';
		}
		$y = (int)$row['year'];
		$m = (int)$row['month'];
		$d = (int)$row['day'];
		$ts = Date::mkdate($m, $d, $y);
		$crossYear = ($y !== (int)$row['seasonYear']);
		$format = $crossYear ? 'j M Y' : 'j M';
		return self::dayDateHtml($ts, $format);
	}

	/**
	 * Value-colour class for a first-frost season offset (days after 1 Sep).
	 * Reuses the site temperature scale (leveltemp_0..9): early = cold/blue,
	 * late = warm/orange-red. 'none' / 'none yet (so far)' use the latest
	 * (warmest) bin, since they rank as the latest outcomes.
	 *
	 * Bins (inclusive): mid-Oct, 16 Oct, 24 Oct, 31 Oct, 15 Nov, 30 Nov,
	 * 15 Dec, 31 Dec, 31 Jan, then Feb–Mar / none.
	 */
	public static function firstFrostValClass($offset) {
		if ($offset === null || (int)$offset >= Data::FIRST_FROST_NONE) {
			return 'leveltemp_9';
		}
		$o = (int)$offset;
		$bounds = [39, 46, 54, 61, 76, 91, 106, 122, 153];
		foreach ($bounds as $i => $lim) {
			if ($o <= $lim) { return 'leveltemp_' . $i; }
		}
		return 'leveltemp_9';
	}

	/** Date HTML wrapped in the season-lateness colour chip (for banners). */
	public static function firstFrostValueChip($row) {
		return '<span class="rk-frost-chip ' . self::firstFrostValClass($row['offset']) . '">'
			. self::firstFrostValueHtml($row) . '</span>';
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
	private static function buttonSelectorGroups($mode = '') {
		require_once __DIR__ . '/ChartHelper.php';
		$groups = Charts::selectableGroups();
		if ($mode === 'rank-annual') {
			foreach ($groups as &$grp) {
				if ($grp['id'] !== 'temp' || empty($grp['options']['daymax'])) { continue; }
				$opts = [];
				foreach ($grp['options'] as $k => $lab) {
					$opts[$k] = $lab;
					if ($k === 'daymax') { $opts['affirst'] = 'First frost'; }
				}
				$grp['options'] = $opts;
			}
			unset($grp);
		}
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
		echo DataSummarizer::historicalNoteHtml($windowStart);
	}

	// ---- Current-item rank / percentile ----

	/**
	 * Competition rank and percentile of $v within an ascending list of values.
	 * Rank = 1 + entries strictly better in the ranking direction (ties share a
	 * rank, shown "=37th"). Percentile = share of the other entries it strictly
	 * beats in that direction: 100 = beats all, 0 = beats none.
	 * @param float[] $sortedAsc all entries, ascending (including the item itself)
	 * @param float $v
	 * @param bool $fromHigh true = higher is better (top / highest / longest)
	 * @return array rank, n, tied, pct
	 */
	public static function rankStats($sortedAsc, $v, $fromHigh) {
		$n = count($sortedAsc);
		$v = (float)$v;
		$eps = 1e-9;
		// lower = count(< v), upper = count(<= v)
		$lo = 0; $hi = $n;
		while ($lo < $hi) { $mid = ($lo + $hi) >> 1; if ((float)$sortedAsc[$mid] < $v - $eps) { $lo = $mid + 1; } else { $hi = $mid; } }
		$lower = $lo;
		$lo = $lower; $hi = $n;
		while ($lo < $hi) { $mid = ($lo + $hi) >> 1; if ((float)$sortedAsc[$mid] <= $v + $eps) { $lo = $mid + 1; } else { $hi = $mid; } }
		$upper = $lo;
		$better = $fromHigh ? ($n - $upper) : $lower;
		$worse = $fromHigh ? $lower : ($n - $upper);
		return array(
			'rank' => $better + 1,
			'n' => $n,
			'tied' => ($upper - $lower) > 1,
			'pct' => ($n > 1) ? (100 * $worse / ($n - 1)) : 100.0,
		);
	}

	/** 1 → "1st", 22 → "22nd", 113 → "113th" (with thousands separators). */
	public static function ordinal($n) {
		$n = (int)$n;
		$m100 = $n % 100;
		$m10 = $n % 10;
		$suf = 'th';
		if ($m100 < 11 || $m100 > 13) {
			if ($m10 === 1) { $suf = 'st'; } elseif ($m10 === 2) { $suf = 'nd'; } elseif ($m10 === 3) { $suf = 'rd'; }
		}
		return number_format($n) . $suf;
	}

	/** Percentile text, floored to 0.1 so only a true record reads 100.0th. */
	public static function pctText($pct) {
		$p = floor((float)$pct * 10 + 1e-6) / 10;
		$txt = number_format($p, 1);
		$tenths = (int)round($p * 10);
		$last = $tenths % 10;
		$suf = 'th';
		if ($last === 0) {
			$suf = 'th';
		} elseif ($last === 1) { $suf = 'st'; } elseif ($last === 2) { $suf = 'nd'; } elseif ($last === 3) { $suf = 'rd'; }
		return $txt . $suf . ' %ile';
	}

	/** "=37th highest of 6,140 (99.4th %ile)" */
	public static function rankStatsText($st, $word) {
		return ($st['tied'] ? '=' : '') . self::ordinal($st['rank']) . ($word !== '' ? ' ' . $word : '')
			. ' of ' . number_format($st['n']) . ' (' . self::pctText($st['pct']) . ')';
	}

	/**
	 * One-line summary above a ranking: where the current item ranks, in the
	 * direction it ranks better.
	 * @param string $label e.g. "Today (so far)"
	 * @param array $hi rankStats from the high end
	 * @param array $lo rankStats from the low end
	 * @param string $valueHtml formatted value
	 */
	public static function currentRankLine($label, $hi, $lo, $valueHtml, $hiWord = 'highest', $loWord = 'lowest') {
		$useHi = ($hi['rank'] <= $lo['rank']);
		$st = $useHi ? $hi : $lo;
		$top = ($st['rank'] <= 10 || $st['pct'] >= 99) ? ' rk-current-line-top' : '';
		return '<p class="rk-current-line' . $top . '"><b>' . htmlspecialchars($label) . ':</b> '
			. $valueHtml . ' &middot; ' . htmlspecialchars(self::rankStatsText($st, $useHi ? $hiWord : $loWord)) . '</p>';
	}

	/**
	 * Rank stats for the current / previous item of a ranking.
	 * @param array $ranked ascending list of [value, ts, ...]
	 * @param array $keys 'today' / 'yest' => key string to match
	 * @param callable $keyFn ts => key string
	 * @return array 'today' / 'yest' => [v, hi, lo] for the keys found
	 */
	public static function currentRanks($ranked, $keys, $keyFn) {
		$vals = array();
		foreach ($ranked as $r) { $vals[] = (float)$r[0]; }
		$out = array();
		foreach ($ranked as $r) {
			$k = $keyFn($r[1]);
			foreach ($keys as $name => $want) {
				if ($want !== null && $k === $want && !isset($out[$name])) {
					$out[$name] = array(
						'v' => (float)$r[0],
						'hi' => self::rankStats($vals, $r[0], true),
						'lo' => self::rankStats($vals, $r[0], false),
					);
				}
			}
		}
		return $out;
	}

	/**
	 * Footer / highlight context for rankTable from currentRanks() output.
	 * @param array $cur currentRanks() result
	 * @param array $rowKeys rowIdx => key string of the rows shown in this table
	 * @param array $keys 'today' / 'yest' => key string
	 * @param bool $fromHigh which direction this table ranks
	 * @param array $values table values, updated with 'today' / 'yest'
	 * @return array ctx for rankTable
	 */
	public static function rankCtx($cur, $rowKeys, $keys, $fromHigh, &$values, $labels = array()) {
		$ctx = $labels;
		$ctx['hl'] = array();
		foreach ($rowKeys as $i => $k) {
			foreach ($keys as $name => $want) {
				if ($want !== null && $k === $want && isset($cur[$name])) { $ctx['hl'][$i] = $name; }
			}
		}
		foreach (array('today', 'yest') as $name) {
			if (isset($cur[$name])) {
				$values[$name] = $cur[$name]['v'];
				$ctx[$name] = $fromHigh ? $cur[$name]['hi'] : $cur[$name]['lo'];
			}
		}
		return $ctx;
	}

	/** True when this variable is a signed difference (temperature anomaly). */
	public function isSigned() {
		return !empty($this->meta['signed']);
	}

	/** Format a value, showing a sign for anomaly series. */
	public function convVal($v, $showUnit = true, $unit = null) {
		$u = ($unit !== null) ? $unit : $this->unit;
		return Wx::conv($v, $u, $showUnit, $this->isSigned());
	}

	/** Formatted value as shown in rank tables (public wrapper). */
	public function rankValueText($v, $isCount = false, $unit = null) {
		if ($isCount) { return (int)$v . ' day' . ((int)$v === 1 ? '' : 's'); }
		if ($this->valcolIdxOverride === 15) { return Spells::anomText($v); }
		$u = ($unit !== null) ? $unit : $this->unit;
		if ($u === Wx::Pm25) { return $this->rankVal($v, $isCount, $unit); }
		return $this->convVal($v, true, $u);
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
	public function rankTable($values, $dates, $rankNum, $title, $alignLeft, $showToday, $showFoot, $isDaily = true, $isCount = false, $sumOff = 1, $unitOverride = null, $ctx = array(), $valHtml = array()) {
		// $ctx: 'today' / 'yest' => rankStats() for footer rows; 'hl' => [rowIdx => 'today'|'yest'];
		//       'todayLabel' / 'yestLabel' override the footer labels.
		$hl = isset($ctx['hl']) ? $ctx['hl'] : array();
		$side = $alignLeft ? 'rk-left' : 'rk-right';
		$unit = ($unitOverride !== null) ? $unitOverride : $this->unit;
		echo '<div class="rk-grid ' . $side . '">';
		echo '<div class="rk-caption">' . htmlspecialchars($title) . '</div>';
		echo '<div class="rk-row rk-head">';
		echo '<div class="rk-lab">#</div><div class="rk-lab">Value</div><div class="rk-lab">Date</div>';
		echo '</div>';

		for ($i = 1; $i <= $rankNum; $i++) {
			if (!isset($values[$i])) { continue; }
			$hlCls = isset($hl[$i]) ? (($hl[$i] === 'today') ? ' rk-current' : ' rk-current-prev') : '';
			echo '<div class="rk-row' . $hlCls . '">';
			echo '<div class="rk-rank">' . $i . '</div>';
			$valOut = isset($valHtml[$i]) ? $valHtml[$i] : $this->rankVal($values[$i], $isCount, $unit);
			if (isset($valHtml[$i]) && $this->isAnnualOnly()) {
				$valCls = self::firstFrostValClass($values[$i]);
			} elseif (isset($valHtml[$i])) {
				$valCls = 'rk-val-date';
			} else {
				$valCls = $this->rankClass($values[$i], $isCount, $sumOff, $unit);
			}
			echo '<div class="rk-val ' . $valCls . '">' . $valOut . '</div>';
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
			if (isset($ctx['todayLabel'])) { $todayLbl = $ctx['todayLabel']; }
			if (isset($ctx['yestLabel'])) { $yestLbl = $ctx['yestLabel']; }
			foreach (array('today' => $todayLbl, 'yest' => $yestLbl) as $key => $lbl) {
				if ($key === 'today' && !$showToday) { continue; }
				if (!isset($values[$key]) || $values[$key] === null) { continue; }
				$st = isset($ctx[$key]) ? $ctx[$key] : null;
				// Narrow rank column: show ranks up to 999 there; the full
				// "=1,685th of 1,871 · 9.8th %ile" goes under the label.
				$rankCell = $st ? (($st['rank'] <= 999) ? (($st['tied'] ? '=' : '') . $st['rank']) : '&ndash;')
					: (isset($dates[$key]) ? $dates[$key] : '');
				$pctl = $st ? ('<span class="rk-pctl">' . htmlspecialchars(self::rankStatsText($st, '')) . '</span>') : '';
				echo '<div class="rk-row rk-foot' . ($key === 'today' ? ' rk-foot-today' : '') . '">';
				echo '<div class="rk-rank">' . $rankCell . '</div>';
				$valOut = isset($valHtml[$key]) ? $valHtml[$key] : $this->rankVal($values[$key], $isCount, $unit);
				if (isset($valHtml[$key]) && $this->isAnnualOnly()) {
					$valCls = self::firstFrostValClass($values[$key]);
				} elseif (isset($valHtml[$key])) {
					$valCls = 'rk-val-date';
				} else {
					$valCls = $this->rankClass($values[$key], $isCount, $sumOff, $unit);
				}
				echo '<div class="rk-val ' . $valCls . '">' . $valOut . '</div>';
				echo '<div class="rk-date">' . htmlspecialchars($lbl) . $pctl . '</div>';
				echo '</div>';
			}
		}

		echo '</div>';
	}

	private function rankVal($v, $isCount, $unit = null) {
		if ($isCount) { return $v; }
		if ($this->valcolIdxOverride === 15) { return Spells::anomText($v); }
		$u = ($unit !== null) ? $unit : $this->unit;
		if ($u === Wx::Pm25) { return Wx::aqSpan($v, Wx::conv($v, $u, false), Wx::aqAvg($this->type)); }
		return $this->convVal($v, false, $u);
	}

	private function rankClass($v, $isCount, $sumOff, $unit = null) {
		if ($isCount) {
			return self::dayCountClass($v);
		}
		$u = ($unit !== null) ? $unit : $this->unit;
		$num = ($this->valcolConvert && $this->valcolIdxOverride === null) ? Wx::convNum($v, $u) : (float)$v;
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
	 * Spell ranking table: # / Length / Period (+ Mean, Anomaly (mean) and Highest/Lowest (+ peak anomaly) for heatwave / cold snap).
	 * The ongoing spell's row is highlighted.
	 * @param array $spells list of spell records from Spells::rankLongest
	 * @param string $title
	 * @param bool $withAnom add a mean / peak anomaly column
	 */
	public function spellRankTable($spells, $title, $withAnom = false) {
		echo '<div class="rk-grid rk-left rk-spells' . ($withAnom ? ' rk-grid-anom' : '') . '">';
		echo '<div class="rk-caption">' . htmlspecialchars($title) . '</div>';
		echo '<div class="rk-row rk-head">';
		echo '<div class="rk-lab">#</div><div class="rk-lab">Length</div><div class="rk-lab">Period</div>';
		$hotTbl = ($this->spellDir === 'heatwave');
		if ($withAnom) {
			echo '<div class="rk-lab" title="Mean value over the spell">Mean</div>';
			echo '<div class="rk-lab" title="Mean difference from the daily 1991-2020 normal">Anomaly</div>';
			echo '<div class="rk-lab" title="' . ($hotTbl ? 'Highest' : 'Lowest') . ' value during the spell (peak anomaly) and its date">'
				. ($hotTbl ? 'Highest' : 'Lowest') . '</div>';
		}
		echo '</div>';

		$yearSecs = 86400 * 365;
		foreach ($spells as $i => $spell) {
			$n = (int)$spell['length'];
			$startTs = strtotime($spell['startDate']);
			$endTs = strtotime($spell['endDate']);
			$period = ($startTs)
				? self::rangeDateHtml($startTs, $endTs ? $endTs : $startTs, $this->type, array('ongoing' => !empty($spell['ongoing'])))
				: (htmlspecialchars((string)$spell['startDate']) . ' – ' . htmlspecialchars((string)$spell['endDate']));
			$cur = !empty($spell['ongoing']);
			echo '<div class="rk-row' . ($cur ? ' rk-current' : '') . '"'
				. ($cur ? ' title="Current spell (so far)"' : '') . '>';
			echo '<div class="rk-rank">' . ($i + 1) . '</div>';
			echo '<div class="rk-val spell-length spell-length-'
				. Spells::lengthColourLevel($n) . '">' . $n . ' day'
				. ($n === 1 ? '' : 's') . ($cur ? '+' : '') . '</div>';
			echo '<div class="rk-date">' . $period . '</div>';
			if ($withAnom) {
				$mean = isset($spell['meanAnom']) ? (float)$spell['meanAnom'] : 0.0;
				$peak = isset($spell['peakAnom']) ? (float)$spell['peakAnom'] : $mean;
				if (isset($spell['meanVal']) && $spell['meanVal'] !== null) {
					$mv = (float)$spell['meanVal'];
					echo '<div class="rk-val rk-meanval ' . $this->rankClass($mv, false, 1) . '">' . $this->rankVal($mv, false) . '</div>';
				} else {
					echo '<div class="rk-val"></div>';
				}
				echo '<div class="rk-val rk-anom ' . self::valcolForIdx(15, $mean) . '">'
					. htmlspecialchars(Spells::anomText($mean)) . '</div>';
				if (isset($spell['peakVal']) && $spell['peakVal'] !== null) {
					$pv = (float)$spell['peakVal'];
					$pd = !empty($spell['peakDate']) ? strtotime($spell['peakDate']) : false;
					echo '<div class="rk-val rk-peakval ' . $this->rankClass($pv, false, 1) . '">'
						. $this->rankVal($pv, false)
						. ' <span class="rk-anom-peak rk-peak-anom" title="Peak anomaly">(' . htmlspecialchars(Spells::anomText($peak)) . ')</span>'
						. ($pd ? ' <span class="rk-anom-peak rk-peak-date">'
						. self::hiddenDateLink(
							self::reportDayUrl((int)date('Y', $pd), (int)date('n', $pd), (int)date('j', $pd)),
							date('d M', $pd),
							'View daily report for ' . date('j M Y', $pd)
						) . '</span>' : '') . '</div>';
				} else {
					echo '<div class="rk-val"></div>';
				}
			}
			echo '</div>';
		}
		if (!count($spells)) {
			echo '<div class="rk-row"><div class="rk-rank">–</div>'
				. '<div class="rk-val">No spells</div><div class="rk-date"></div>'
				. ($withAnom ? '<div class="rk-val"></div><div class="rk-val"></div><div class="rk-val"></div>' : '') . '</div>';
		}
		echo '</div>';
	}
}
