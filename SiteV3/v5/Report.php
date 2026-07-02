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
	public $valcolConvert;      // convert value before colour lookup?
	public $hasToday;           // has a meaningful current-day value

	/** Manually-recorded variables that have no automatic "today" value. */
	private static $manualVars = [
		'sunhr', 'wethr', 'cloud', 'snow', 'lysnw', 'hail', 'thunder', 'fog',
		'comms', 'extra', 'issues', 'away', 'pond', 'spare',
	];

	// ---- Option lists ----
	public static $ranknumOptions = [10, 25, 50, 100, 250];
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
		'aqmin' => 11, 'aqmax' => 11, 'aqmean' => 11,
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
		12 => 'temp', 13 => 'dhrs',
	];

	/** Variable grouping for the colgroup drop-down (legacy $categories). */
	public static $categories = [
		'Temperature' => ['tmin', 'tmax', 'tmean'],
		'Rainfall' => ['rain', 'hrmax', '10max', 'ratemax'],
		'Wind' => ['wmean', 'wmax', 'gust', 'wdir'],
		'Humidity' => ['hmin', 'hmax', 'hmean'],
		'Pressure' => ['pmin', 'pmax', 'pmean'],
		'Dew Point' => ['dmin', 'dmax', 'dmean'],
		'Observations' => ['sunhr', 'wethr', 'ratemean', 'snow', 'lysnw', 'hail', 'thunder', 'fog', 'pond'],
		'Range' => ['trange', 'hrange', 'prange'],
		'Change' => ['tc10max', 'tchrmax', 'hchrmax', 'tc10min', 'tchrmin', 'hchrmin'],
		'Misc.' => ['nightmin', 'daymax', 'w10max', 'afhrs'],
		'Feels-like' => ['fmin', 'fmax', 'fmean'],
	];

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

		// Year / month selection
		$this->year = isset($_GET['year']) ? (int)$_GET['year'] : (int)Date::$yr_yest;
		if ($this->year < $this->startYear) { $this->year = (int)Date::$yr_yest; }
		if ($this->year > (int)Date::$dyear) { $this->year = (int)Date::$dyear; }
		$this->month = isset($_GET['month']) ? (int)$_GET['month'] : 0;
		if ($this->month < 0 || $this->month > 12) { $this->month = 0; }

		// Ranking / multi-year selectors
		$this->rankLimit = isset($_GET['rankLimit']) ? (int)$_GET['rankLimit'] : 25;
		if (!in_array($this->rankLimit, self::$ranknumOptions, true)) { $this->rankLimit = 25; }

		$this->startYearOptions = [(int)Date::$yr_yest - 1, 2020, 2009];
		if ($this->startYear < 2009) { array_push($this->startYearOptions, 2000, 1990, 1980, 1970, 1950); }
		if ($this->startYear < 1949) { $this->startYearOptions[] = 1910; }
		if ($this->startYear < 1910) { $this->startYearOptions[] = $this->startYear; }
		$this->startYrReport = isset($_GET['start_year_rep']) ? (int)$_GET['start_year_rep'] : 2009;
		if (!in_array($this->startYrReport, $this->startYearOptions, true)) {
			$this->startYrReport = end($this->startYearOptions);
		}

		// Available monthly-summary tab types (mirrors wxdatagen)
		$this->availSummaryTypes = $this->isCountOnly ? [Data::SUMMARY_COUNT] : [(int)$this->isSum];
		if ($this->isSum && !$this->isCountOnly) { $this->availSummaryTypes[] = Data::SUMMARY_COUNT; }
		array_push($this->availSummaryTypes, Data::SUMMARY_MIN, Data::SUMMARY_MAX);

		$g = isset($_GET['summary_type']) ? (int)$_GET['summary_type'] : 0;
		if ($g < 0 || $g > 4) { $g = 0; }
		if ($g <= 1) { $g = (int)$this->isSum; }
		if (!in_array($g, $this->availSummaryTypes, true)) { $g = $this->availSummaryTypes[0]; }
		$this->summaryType = $g;

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
		];
		self::$thresholdsReady = true;
	}

	/**
	 * Returns the CSS class for a (converted) value, based on this variable's scheme.
	 * @param float $value the value to shade
	 * @param bool $countable use the day-count scale instead of the value scale
	 */
	public function valcolr($value, $countable = false) {
		if ($this->valcolIdx === null) { return 'reportday'; }
		$level = self::$valcolLevel[$this->valcolIdx];
		$values = $countable ? [0, 1, 3, 5, 7, 10, 15, 20, 25, 30, 31] : self::$thresholds[$this->valcolIdx];
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
	 *                 isDaily, linkToOther, showTabs
	 */
	public function controls($o = []) {
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
			. strip_tags(Wx::getUnits($this->unit)) . '</h1>';

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

	public function historicalInfo() {
		if ($this->startYear < 2009 && $this->year < 2009) {
			echo '<p>*Data from before 2009 are mostly from the historical site at Whitestone Pond in Hampstead. '
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
	 * @param bool $isDaily   daily (vs monthly) labels in the footer
	 * @param bool $isCount   values are plain counts (no unit conversion)
	 * @param float $sumfix   divisor applied before colour lookup
	 */
	public function rankTable($values, $dates, $rankNum, $title, $alignLeft, $showToday, $showFoot, $isDaily = true, $isCount = false, $sumfix = 1) {
		$align = $alignLeft ? 'left' : 'center';
		HTML::table('table1', '49%" align="' . $align, 3);
		HTML::tableHead($title);
		HTML::tr();
		HTML::td('Rank'); HTML::td('Value'); HTML::td('Date');
		HTML::tr_end();

		for ($i = 1; $i <= $rankNum; $i++) {
			if (!isset($values[$i])) { continue; }
			HTML::tr('row' . HTML::colcol($i));
			HTML::td($i);
			HTML::td($this->rankVal($values[$i], $isCount), $this->rankClass($values[$i], $isCount, $sumfix));
			HTML::td(isset($dates[$i]) ? $dates[$i] : '');
			HTML::tr_end();
		}

		if ($showFoot) {
			$todayLbl = $isDaily ? 'Today' : 'Current Month';
			$yestLbl = $isDaily ? 'Yesterday' : 'Last Month';
			if ($showToday && isset($values['today'])) {
				HTML::tr('tblfoot" style="border-top:3px solid #6F7;');
				HTML::td(isset($dates['today']) ? $dates['today'] : '');
				HTML::td($this->rankVal($values['today'], $isCount), $this->rankClass($values['today'], $isCount, $sumfix));
				HTML::td($todayLbl);
				HTML::tr_end();
			}
			if (isset($values['yest']) && $values['yest'] !== null) {
				HTML::tr('tblfoot');
				HTML::td(isset($dates['yest']) ? $dates['yest'] : '');
				HTML::td($this->rankVal($values['yest'], $isCount), $this->rankClass($values['yest'], $isCount, $sumfix));
				HTML::td($yestLbl);
				HTML::tr_end();
			}
		}

		HTML::table_end();
	}

	private function rankVal($v, $isCount) {
		return $isCount ? $v : Wx::conv($v, $this->unit, false);
	}

	private function rankClass($v, $isCount, $sumfix) {
		$num = $this->valcolConvert ? Wx::convNum($v, $this->unit) : (float)$v;
		return $this->valcolr(($num === null ? 0 : $num) / $sumfix, $isCount);
	}

	public function rankLimitForm() {
		echo '<form method="get" action=""><span style="padding-left:25px" class="rep">Limit</span>'
			. '<select name="rankLimit" onchange="this.form.submit()">';
		foreach (self::$ranknumOptions as $opt) {
			echo '<option value="' . $opt . '"' . ($opt === $this->rankLimit ? ' selected="selected"' : '') . '>' . $opt . '</option>';
		}
		echo '</select></form>';
	}
}
