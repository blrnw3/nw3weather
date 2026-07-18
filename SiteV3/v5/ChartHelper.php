<?php

/**
 * Charts - server-side helper for embedding the v5 Highcharts widgets.
 *
 * Each public method emits a container div plus the JS needed to populate it
 * from one of the JSON endpoints (histdata.php / intradaydata.php / rosedata.php),
 * which are rendered client-side by /v5/wxcharts.js (the NW3.* API).
 *
 * The Highcharts and wxcharts.js assets are injected once per page on first use.
 *
 * NB: this is the helper CLASS file. The interactive viewer page lives in the
 * lowercase charts.php; the two filenames must stay distinct because some
 * filesystems (e.g. macOS) are case-insensitive.
 */
class Charts {

	private static $assetsDone = false;
	private static $seq = 0;

	/** Inject Highcharts + wxcharts.js exactly once per request. */
	public static function assets() {
		if (self::$assetsDone) { return; }
		self::$assetsDone = true;
		echo '<script src="https://code.highcharts.com/highcharts.js"></script>' . "\n";
		echo '<script src="https://code.highcharts.com/highcharts-more.js"></script>' . "\n";
		echo '<script src="/v5/wxcharts.js?20260717v"></script>' . "\n";
	}

	/** Emit a uniquely-identified chart container div; returns its id. */
	public static function container($opts = array()) {
		$id = 'wxc' . (++self::$seq);
		$class = isset($opts['class']) ? $opts['class'] : 'wxchart';
		$class .= ' wxchart-loading';
		$height = isset($opts['height']) ? (int)$opts['height'] : 320;
		$style = 'min-height:' . $height . 'px;' . (isset($opts['style']) ? $opts['style'] : '');
		echo '<div id="' . $id . '" class="' . htmlspecialchars($class) . '" style="' . $style . '"></div>' . "\n";
		return $id;
	}

	/** Wrap a JS snippet in a CDATA-guarded script tag. */
	public static function run($js) {
		echo '<script>//<![CDATA[' . "\n" . $js . "\n//]]></script>\n";
	}

	/** Build a URL for one of the JSON endpoints under /v5/. */
	public static function url($endpoint, $params = array()) {
		$qs = http_build_query($params);
		return '/v5/' . $endpoint . ($qs !== '' ? ('?' . $qs) : '');
	}

	/**
	 * Historical categorical chart (daily / monthly / annual / climate).
	 * $params are passed straight through to histdata.php.
	 */
	public static function daily($params, $opts = array()) {
		self::assets();
		$id = self::container($opts);
		$url = self::url('histdata.php', $params);
		self::run('NW3.histChart(' . json_encode($id) . ',' . json_encode($url) . ',' . json_encode($opts) . ');');
	}

	/** Alias for daily(). */
	public static function hist($params, $opts = array()) {
		self::daily($params, $opts);
	}

	/**
	 * Single-variable intraday (per-minute) chart from intradaydata.php.
	 * $var is the series key (temp, dewp, humi, pres, wind, rain, wdir, ...).
	 */
	public static function intraday($params, $var = 'temp', $opts = array()) {
		self::assets();
		$id = self::container($opts);
		$url = self::url('intradaydata.php', $params);
		self::run('NW3.intradayChart(' . json_encode($id) . ',' . json_encode($url) . ',' . json_encode($var) . ');');
	}

	/**
	 * Multi-variable intraday panel: fetches intradaydata.php once and wires a row
	 * of buttons that switch the displayed variable client-side. $vars maps the
	 * series key to its button label; pass null for a sensible default set.
	 */
	public static function intradayPanel($params, $vars = null, $opts = array()) {
		self::assets();
		if ($vars === null) {
			$vars = array(
				'temp' => 'Temp', 'dewp' => 'Dew pt', 'humi' => 'Humidity',
				'pres' => 'Pressure', 'wind' => 'Wind', 'rain' => 'Rain', 'wdir' => 'Direction'
			);
		}
		$panelId = 'wxp' . (self::$seq + 1);
		echo '<div id="' . $panelId . '-vars" class="wxchart-tabs">';
		$first = null;
		foreach ($vars as $k => $label) {
			if ($first === null) { $first = $k; }
			echo '<button type="button" class="' . ($first === $k ? 'active' : '') . '" data-var="'
				. htmlspecialchars($k) . '">' . $label . '</button> ';
		}
		echo '</div>' . "\n";
		$id = self::container($opts);
		$url = self::url('intradaydata.php', $params);
		$initial = isset($opts['initial']) ? $opts['initial'] : $first;
		self::run('NW3.intradayPanel(' . json_encode($id) . ',' . json_encode($url) . ','
			. json_encode($initial) . ',' . json_encode('#' . $panelId . '-vars button') . ');');
	}

	/** Wind rose (polar stacked column) from rosedata.php. Pass $opts['legend']=false to hide the legend. */
	public static function rose($params, $opts = array()) {
		self::assets();
		$id = self::container($opts);
		$url = self::url('rosedata.php', $params);
		$jsOpts = array('legend' => !(isset($opts['legend']) && $opts['legend'] === false));
		self::run('NW3.windRose(' . json_encode($id) . ',' . json_encode($url) . ',' . json_encode($jsOpts) . ');');
	}

	/**
	 * Interactive live dashboard: two fixed multi-variable charts (temp/humidity/
	 * rain/dew point and wind/gust/pressure) that always show the last 24h, plus a
	 * single-variable chart with an icon+label variable toggle and a 6h..7d range
	 * toggle. Short ranges (<=24h) reuse one full-resolution intradaydata.php fetch;
	 * longer ranges fetch a server-decimated dataset (maxpts) to limit transfer.
	 */
	public static function livePanel($opts = array()) {
		self::assets();
		$mainHeight  = isset($opts['height']) ? (int)$opts['height'] : 600;
		$multiHeight = isset($opts['multiHeight']) ? (int)$opts['multiHeight'] : 680;
		$img = Site::IMG_ROOT;

		$vars = array(
			'temp' => array('Temp', 'thermom8_small.png'),
			'rain' => array('Rain', 'icon-rain.svg'),
			'wind' => array('Wind', 'icon-wind.svg'),
			'humi' => array('Humidity', 'humidity_small.png'),
			'dewp' => array('Dew point', 'dewy_small.png'),
			'pres' => array('Pressure', 'pressure2_small.png'),
			'pm25' => array('Air quality', 'icon-airpollution.svg'),
			'wdir' => array('Wind dir', 'icon-compass.svg'),
		);
		// Each range: hours window, days to fetch (cover the window across midnight),
		// and a server decimation cap (maxpts) for the longer ones to limit transfer.
		$ranges = array(
			array('h' => 6,   'days' => 2, 'label' => '6h'),
			array('h' => 12,  'days' => 2, 'label' => '12h'),
			array('h' => 24,  'days' => 2, 'label' => '24h'),
			array('h' => 48,  'days' => 3, 'maxpts' => 720, 'label' => '48h'),
			array('h' => 72,  'days' => 4, 'maxpts' => 720, 'label' => '3d'),
			array('h' => 120, 'days' => 6, 'maxpts' => 720, 'label' => '5d'),
			array('h' => 168, 'days' => 8, 'maxpts' => 720, 'label' => '7d'),
		);
		$defaultVar = 'temp';
		$defaultRangeIdx = 1; // 12h

		echo '<div class="wx3-multi">' . "\n";
		echo '<div id="wx3-multi-a" class="wx3-chart wxchart-loading" style="min-height:' . $multiHeight . 'px;"></div>' . "\n";
		echo '<div id="wx3-multi-b" class="wx3-chart wxchart-loading" style="min-height:' . $multiHeight . 'px;"></div>' . "\n";
		echo '</div>' . "\n";

		echo '<h2>Detailed view</h2>';
		echo '<div class="wx3-live-bar">' . "\n";
		echo '<div id="wx3-vars" class="wx3-live-vars" role="tablist">';
		foreach ($vars as $k => $v) {
			$active = ($k === $defaultVar) ? ' active' : '';
			echo '<button type="button" class="' . trim($active) . '" data-var="' . htmlspecialchars($k) . '" title="' . htmlspecialchars($v[0]) . '">'
				. '<img src="' . $img . htmlspecialchars($v[1]) . '" alt="" width="22" height="22" />'
				. '<span>' . htmlspecialchars($v[0]) . '</span></button>';
		}
		echo '</div>' . "\n";
		echo '<div id="wx3-range" class="wx3-live-range" role="group" aria-label="Chart time range">';
		foreach ($ranges as $i => $r) {
			$active = ($i === $defaultRangeIdx) ? ' class="active"' : '';
			echo '<button type="button"' . $active . ' data-idx="' . (int)$i . '">' . htmlspecialchars($r['label']) . '</button>';
		}
		echo '</div>' . "\n";
		echo '</div>' . "\n";

		echo '<div id="wx3-main" class="wx3-chart wxchart-loading" style="min-height:' . $mainHeight . 'px;"></div>' . "\n";

		$cfg = array(
			'url' => self::url('intradaydata.php'),
			'mainId' => 'wx3-main',
			'varSel' => '#wx3-vars button',
			'rangeSel' => '#wx3-range button',
			'initialVar' => $defaultVar,
			'initialRangeIdx' => $defaultRangeIdx,
			'baseDays' => 2,
			'ranges' => $ranges,
			'refresh' => 300000,
			'multi' => array(
				array('id' => 'wx3-multi-a', 'kind' => 'thrd'),
				array('id' => 'wx3-multi-b', 'kind' => 'wgp'),
			),
		);
		self::run('NW3.intradayPage(' . json_encode($cfg) . ');');
	}

	/**
	 * Flat per-minute variable list for the graph viewer (same set as the wx3 live detailed view).
	 * Each entry is [label, icon filename under Site::IMG_ROOT].
	 */
	public static function intradayVars() {
		return array(
			'temp' => array('Temp', 'thermom8_small.png'),
			'rain' => array('Rain', 'icon-rain.svg'),
			'wind' => array('Wind', 'icon-wind.svg'),
			'humi' => array('Humidity', 'humidity_small.png'),
			'dewp' => array('Dew point', 'dewy_small.png'),
			'pres' => array('Pressure', 'pressure2_small.png'),
			'pm25' => array('Air quality', 'icon-airpollution.svg'),
			'wdir' => array('Wind dir', 'icon-compass.svg'),
		);
	}

	/**
	 * Custom Graph Viewer: LHS controls (chart type, variable, period) + large chart.
	 * Supports multi-variable fixed-scale presets (temp/hum/rain and wind/gust/pressure)
	 * and a single-variable dynamic-scale mode.
	 */
	public static function graphViewer($opts = array()) {
		self::assets();
		$vars = self::intradayVars();
		$img = Site::IMG_ROOT;
		$default = isset($opts['default']) ? $opts['default'] : 'temp';
		if (!isset($vars[$default])) { $default = 'temp'; }
		$height = isset($opts['height']) ? (int)$opts['height'] : 480;
		$defaultMode = isset($opts['defaultMode']) ? $opts['defaultMode'] : 'thrd';
		$defaultNum = isset($opts['defaultNum']) ? (int)$opts['defaultNum'] : 3;
		$yrNow = (int)Date::$dyear;
		$moNow = (int)Date::$dmonth;
		$dyNow = (int)Date::$dday;
		$dayOpts = array(1, 2, 3, 7, 14, 31, 60);

		$id = 'gv-chart';
		$panelId = 'gv-panel';

		echo '<div class="wxsel-chart wxsel-viewer">' . "\n";
		echo '<div id="' . $panelId . '" class="wxsel-panel" role="group" aria-label="Graph controls">' . "\n";

		echo '<div class="wxsel-label">Chart type</div>' . "\n";
		echo '<div class="wxsel-scale" role="tablist" id="gv-mode">';
		$modes = array(
			'thrd' => array('Multi (temp)', 'Temperature, humidity, dew point and rain (fixed scale)'),
			'wgp' => array('Multi (wind)', 'Wind, gust and pressure (fixed scale)'),
			'single' => array('Single var', 'One variable with auto-scaled axis'),
		);
		foreach ($modes as $k => $lab) {
			$active = ($k === $defaultMode) ? ' active' : '';
			echo '<button type="button" class="' . trim($active) . '" data-mode="'
				. $k . '" title="' . htmlspecialchars($lab[1]) . '">' . htmlspecialchars($lab[0]) . '</button>';
		}
		echo '</div>' . "\n";

		echo '<div id="gv-var-wrap"' . ($defaultMode === 'single' ? '' : ' hidden') . '>' . "\n";
		echo '<div class="wxsel-label">Variable</div>' . "\n";
		echo '<div id="gv-vars" class="wx3-live-vars gv-vars" role="tablist">';
		foreach ($vars as $k => $v) {
			$active = ($k === $default) ? ' active' : '';
			echo '<button type="button" class="' . trim($active) . '" data-var="'
				. htmlspecialchars($k) . '" title="' . htmlspecialchars($v[0]) . '">'
				. '<img src="' . $img . htmlspecialchars($v[1]) . '" alt="" width="22" height="22" />'
				. '<span>' . htmlspecialchars($v[0]) . '</span></button>';
		}
		echo '</div>' . "\n";
		echo '</div>' . "\n";

		echo '<div class="wxsel-label">Days</div>' . "\n";
		echo '<div id="gv-num" class="wxsel-scale gv-num" role="group" aria-label="Number of days">';
		foreach ($dayOpts as $n) {
			$active = ($n === $defaultNum) ? ' active' : '';
			echo '<button type="button" class="' . trim($active) . '" data-num="' . $n . '">' . $n . '</button>';
		}
		echo '</div>' . "\n";

		echo '<div class="wxsel-label">End date</div>' . "\n";
		echo '<div class="wxsel-period">' . "\n";

		echo '<label class="wxsel-field"><span>Year</span>'
			. '<select id="gv-year" class="wxsel">';
		for ($y = $yrNow; $y >= 2009; $y--) {
			echo '<option value="' . $y . '">' . $y . '</option>';
		}
		echo '</select></label>' . "\n";

		echo '<label class="wxsel-field"><span>Month</span>'
			. '<select id="gv-month" class="wxsel">';
		for ($m = 1; $m <= 12; $m++) {
			$sel = ($m === $moNow) ? ' selected="selected"' : '';
			echo '<option value="' . $m . '"' . $sel . '>' . Date::$months3[$m - 1] . '</option>';
		}
		echo '</select></label>' . "\n";

		echo '<label class="wxsel-field"><span>Day</span>'
			. '<select id="gv-day" class="wxsel">';
		for ($d = 1; $d <= 31; $d++) {
			$sel = ($d === $dyNow) ? ' selected="selected"' : '';
			echo '<option value="' . $d . '"' . $sel . '>' . Util::zerolead($d) . '</option>';
		}
		echo '</select></label>' . "\n";

		echo '</div>' . "\n"; // period
		echo '</div>' . "\n"; // panel

		echo '<div id="' . $id . '" class="wxchart wxchart-loading" style="min-height:' . $height . 'px;"></div>' . "\n";
		echo '</div>' . "\n";

		$cfg = array(
			'containerId' => $id,
			'panelId' => $panelId,
			'url' => self::url('intradaydata.php'),
			'opts' => array('height' => $height),
			'defaultType' => $default,
			'defaultMode' => $defaultMode,
			'defaultNum' => $defaultNum,
		);
		self::run('NW3.graphViewer(' . json_encode($cfg) . ');');
	}

	/**
	 * Full list of daily variables offered in selectable trend charts, built from
	 * the data definitions (mirrors the Chart Viewer picker in charts.php), skipping
	 * non-numeric / unsupported series.
	 */
	public static function selectableVars() {
		$skip = array('comms', 'extra', 'issues', 'away', 'cloud', 'spare', 'sunhrp', 'wethrp');
		$vars = array();
		foreach (Wx::$daily as $key => $meta) {
			if (in_array($key, $skip, true)) { continue; }
			$vars[$key] = $meta['description'];
		}
		return $vars;
	}

	/**
	 * Variable groups for the two-tier selector (group → subtype). Each option maps
	 * a histdata type key to a short button label. Icons are under Site::IMG_ROOT.
	 */
	public static function selectableGroups() {
		$img = Site::IMG_ROOT;
		return array(
			array(
				'id' => 'temp', 'label' => 'Temp', 'icon' => $img . 'thermom8_small.png',
				'options' => array(
					'tmean' => 'Mean', 'tmin' => 'Min', 'tmax' => 'Max', 'trange' => 'Range',
					'nightmin' => 'Night min', 'daymax' => 'Day max',
				),
			),
			array(
				'id' => 'rain', 'label' => 'Rain', 'icon' => $img . 'icon-rain.svg',
				'options' => array(
					'rain' => 'Total', 'hrmax' => 'Max hour', '10max' => 'Max 10-min',
					'ratemax' => 'Max rate', 'ratemean' => 'Mean rate', 'wethr' => 'Wet hours',
				),
			),
			array(
				'id' => 'pres', 'label' => 'Pressure', 'icon' => $img . 'pressure2_small.png',
				'options' => array(
					'pmean' => 'Mean', 'pmin' => 'Min', 'pmax' => 'Max', 'prange' => 'Range',
				),
			),
			array(
				'id' => 'wind', 'label' => 'Wind', 'icon' => $img . 'icon-wind.svg',
				'options' => array(
					'wmean' => 'Mean', 'wmax' => 'Max', 'gust' => 'Gust',
					'wdir' => 'Direction', 'w10max' => 'Max 10-min',
				),
			),
			array(
				'id' => 'humi', 'label' => 'Humidity', 'icon' => $img . 'humidity_small.png',
				'options' => array(
					'hmean' => 'Mean', 'hmin' => 'Min', 'hmax' => 'Max', 'hrange' => 'Range',
				),
			),
			array(
				'id' => 'dewp', 'label' => 'Dew point', 'icon' => $img . 'dewy_small.png',
				'options' => array(
					'dmean' => 'Mean', 'dmin' => 'Min', 'dmax' => 'Max',
				),
			),
			array(
				'id' => 'feel', 'label' => 'Feels-like', 'icon' => $img . 'thermom8_small.png',
				'options' => array(
					'fmean' => 'Mean', 'fmin' => 'Min', 'fmax' => 'Max',
				),
			),
			array(
				'id' => 'aq', 'label' => 'Air quality', 'icon' => $img . 'icon-airpollution.svg',
				'options' => array(
					'aqmean' => 'Mean', 'aqmin' => 'Min', 'aqmax' => 'Max',
				),
			),
			array(
				'id' => 'sun', 'label' => 'Sun', 'icon' => $img . 'icon-sun.svg',
				'options' => array(
					'sunhr' => 'Hours',
				),
			),
			array(
				'id' => 'other', 'label' => 'Other',
				'options' => array(
					'afhrs' => 'Air frost', 'snow' => 'Falling snow', 'lysnw' => 'Lying snow',
					'hail' => 'Hail', 'thunder' => 'Thunder', 'fog' => 'Fog', 'pond' => 'Pond temp',
				),
			),
		);
	}

	/**
	 * Monthly aggregation types valid for a daily variable, from Wx::$daily flags.
	 * Summable vars get Total (not Mean); meanable get Mean (not Total).
	 * Count (non-zero days) is offered for summable / count-only vars only.
	 * Min/Max are always available except pure count-only still gets them (as Report).
	 */
	public static function aggregationsForType($type) {
		$meta = isset(Wx::$daily[$type]) ? Wx::$daily[$type] : array();
		$countOnly = !empty($meta['count-only']);
		$summable = !empty($meta['summable']) || $countOnly;
		if ($countOnly) {
			$aggs = array(Data::SUMMARY_COUNT);
		} else {
			$aggs = array($summable ? Data::SUMMARY_SUM : Data::SUMMARY_MEAN);
			if ($summable) { $aggs[] = Data::SUMMARY_COUNT; }
		}
		$aggs[] = Data::SUMMARY_MIN;
		$aggs[] = Data::SUMMARY_MAX;
		return $aggs;
	}

	/** Short labels for Data::SUMMARY_* buttons. */
	public static function aggregationLabels() {
		return array(
			Data::SUMMARY_MEAN => 'Mean',
			Data::SUMMARY_SUM => 'Total',
			Data::SUMMARY_COUNT => 'Count',
			Data::SUMMARY_MIN => 'Low',
			Data::SUMMARY_MAX => 'High',
		);
	}

	/**
	 * Per-type metadata for selectable charts: aggregations, description, start year.
	 */
	public static function selectableVarMeta() {
		$map = array();
		foreach (self::selectableGroups() as $g) {
			foreach (array_keys($g['options']) as $type) {
				$meta = isset(Wx::$daily[$type]) ? Wx::$daily[$type] : array();
				$aggs = self::aggregationsForType($type);
				$map[$type] = array(
					'aggregations' => $aggs,
					'primary' => $aggs[0],
					'description' => isset($meta['description']) ? $meta['description'] : $type,
					'startYear' => isset($meta['start_year']) ? (int)$meta['start_year'] : 2009,
					'summable' => !empty($meta['summable']),
				);
			}
		}
		return $map;
	}

	/** Emit group buttons for a selector panel; returns the default group's id. */
	private static function emitGroupButtons($groups, $defaultGroup) {
		echo '<div class="wxsel-label">Variable</div>' . "\n";
		echo '<div class="wxsel-groups" role="tablist">';
		foreach ($groups as $g) {
			$active = ($g['id'] === $defaultGroup) ? ' active' : '';
			$icon = isset($g['icon'])
				? '<img src="' . htmlspecialchars($g['icon']) . '" alt="" width="16" height="16" />'
				: '';
			echo '<button type="button" class="' . trim($active) . '" data-group="'
				. htmlspecialchars($g['id']) . '" title="' . htmlspecialchars($g['label']) . '">'
				. $icon . '<span>' . htmlspecialchars($g['label']) . '</span></button>';
		}
		echo '</div>' . "\n";
	}

	/**
	 * Year-to-date daily line chart with a left-hand variable selector.
	 * Cumulative is available for summable vars (default on for rain); normals on by default.
	 */
	public static function cumeSelectable($opts = array(), $default = 'rain') {
		self::assets();
		$groups = self::selectableGroups();
		$varMeta = self::selectableVarMeta();
		$id = 'wxc' . (++self::$seq);
		$panelId = $id . '-panel';
		$height = isset($opts['height']) ? (int)$opts['height'] : 360;
		$year = isset($opts['year']) ? (int)$opts['year'] : (int)Date::$dyear;
		$defaultCume = !isset($opts['cume']) || !empty($opts['cume']);
		$defaultLta = !isset($opts['lta']) || !empty($opts['lta']);

		$defaultGroup = $groups[0]['id'];
		foreach ($groups as $g) {
			if (isset($g['options'][$default])) { $defaultGroup = $g['id']; break; }
		}
		$defaultSummable = !empty($varMeta[$default]['summable']);
		if (!$defaultSummable) { $defaultCume = false; }
		$headingId = $id . '-heading';
		$defaultDesc = isset($varMeta[$default]['description']) ? $varMeta[$default]['description'] : $default;

		echo '<div class="wxsel-wrap">' . "\n";
		echo '<h3 id="' . $headingId . '" class="wxsel-heading">' . htmlspecialchars($year . ($defaultCume ? ' Cumulative ' : ' ') . $defaultDesc) . '</h3>' . "\n";
		echo '<div class="wxsel-chart">' . "\n";
		echo '<div id="' . $panelId . '" class="wxsel-panel" role="group" aria-label="Year-to-date variable">' . "\n";
		self::emitGroupButtons($groups, $defaultGroup);
		echo '<div class="wxsel-label">Measure</div>' . "\n";
		echo '<div class="wxsel-subtypes" role="tablist"></div>' . "\n";
		echo '<div class="wxsel-label">Year</div>' . "\n";
		echo '<div class="wxsel-scale wxsel-years" role="tablist">';
		for ($i = 0; $i < 4; $i++) {
			$y = $year - $i;
			$active = ($i === 0) ? ' active' : '';
			echo '<button type="button" class="' . trim($active) . '" data-year="' . $y . '">' . $y . '</button>';
		}
		echo '</div>' . "\n";
		echo '<div class="wxsel-label">Options</div>' . "\n";
		echo '<div class="wxsel-normals">'
			. '<button type="button" class="wxsel-toggle' . ($defaultLta ? ' active' : '')
			. '" data-lta="1" title="Climate normals">Normals</button>'
			. '<button type="button" class="wxsel-toggle' . ($defaultCume ? ' active' : '')
			. ($defaultSummable ? '' : ' disabled') . '"'
			. ($defaultSummable ? '' : ' disabled="disabled"')
			. ' data-cume="1" title="Running total (summable variables only)">Cumulative</button>'
			. '</div>' . "\n";
		echo '</div>' . "\n";
		echo '<div id="' . $id . '" class="wxchart wxchart-loading" style="min-height:' . $height . 'px;"></div>' . "\n";
		echo '</div>' . "\n"; // wxsel-chart
		echo '</div>' . "\n"; // wxsel-wrap

		$url = self::url('histdata.php', array(
			'mode' => 'daily',
			'month' => 0,
		));
		$cfg = array(
			'containerId' => $id,
			'panelId' => $panelId,
			'headingId' => $headingId,
			'url' => $url,
			'opts' => $opts,
			'groups' => $groups,
			'defaultType' => $default,
			'defaultGroup' => $defaultGroup,
			'showAggregation' => false,
			'showNormals' => true,
			'showCume' => true,
			'showYears' => true,
			'defaultYear' => $year,
			'defaultSummary' => Data::SUMMARY_SUM, // so normals stay eligible when toggled
			'defaultLta' => $defaultLta,
			'defaultCume' => $defaultCume,
			'varMeta' => $varMeta,
		);
		self::run('NW3.histSelectGrouped(' . json_encode($cfg) . ');');
	}

	/**
	 * Categorical trend chart with a left-hand selector panel (~1/3) and chart (~2/3).
	 * Group → subtype buttons; monthly mode also gets aggregation + normals toggles.
	 * $params holds the shared mode/length (but no type); lta/summary_type are
	 * controlled client-side when shown.
	 */
	public static function dailySelectable($params, $opts = array(), $vars = null, $default = 'wmean') {
		self::assets();
		$groups = self::selectableGroups();
		$varMeta = self::selectableVarMeta();
		$aggLabels = self::aggregationLabels();
		$id = 'wxc' . (++self::$seq);
		$panelId = $id . '-panel';
		$height = isset($opts['height']) ? (int)$opts['height'] : 320;
		$isMonthly = (isset($params['mode']) && $params['mode'] === 'monthly');

		$defaultGroup = $groups[0]['id'];
		foreach ($groups as $g) {
			if (isset($g['options'][$default])) { $defaultGroup = $g['id']; break; }
		}
		$defaultAggs = isset($varMeta[$default]) ? $varMeta[$default]['aggregations'] : array(Data::SUMMARY_MEAN);
		$defaultSummary = isset($params['summary_type']) ? (int)$params['summary_type'] : $defaultAggs[0];
		if (!in_array($defaultSummary, $defaultAggs, true)) { $defaultSummary = $defaultAggs[0]; }
		$defaultLta = !empty($params['lta']);
		$headingId = $id . '-heading';
		$defaultDesc = isset($varMeta[$default]['description']) ? $varMeta[$default]['description'] : $default;
		$headingInit = $defaultDesc;
		if ($isMonthly) {
			$aggName = isset($aggLabels[$defaultSummary]) ? $aggLabels[$defaultSummary] : '';
			if ($aggName !== '' && stripos($defaultDesc, $aggName) !== 0) {
				$headingInit = $aggName . ' ' . $defaultDesc;
			}
		}

		echo '<div class="wxsel-wrap">' . "\n";
		echo '<h3 id="' . $headingId . '" class="wxsel-heading">' . htmlspecialchars($headingInit) . '</h3>' . "\n";
		echo '<div class="wxsel-chart">' . "\n";
		echo '<div id="' . $panelId . '" class="wxsel-panel" role="group" aria-label="Chart variable">' . "\n";
		self::emitGroupButtons($groups, $defaultGroup);
		echo '<div class="wxsel-label">Measure</div>' . "\n";
		echo '<div class="wxsel-subtypes" role="tablist"></div>' . "\n";
		if ($isMonthly) {
			echo '<div class="wxsel-label">Aggregation</div>' . "\n";
			echo '<div class="wxsel-agg" role="tablist"></div>' . "\n";
			echo '<div class="wxsel-label">Overlay</div>' . "\n";
			echo '<div class="wxsel-normals">'
				. '<button type="button" class="wxsel-toggle' . ($defaultLta ? ' active' : '')
				. '" data-lta="1" title="Climate normals (mean/total only)">Normals</button>'
				. '</div>' . "\n";
		}
		echo '</div>' . "\n";
		echo '<div id="' . $id . '" class="wxchart wxchart-loading" style="min-height:' . $height . 'px;"></div>' . "\n";
		echo '</div>' . "\n"; // wxsel-chart
		echo '</div>' . "\n"; // wxsel-wrap

		$baseParams = $params;
		unset($baseParams['type'], $baseParams['summary_type'], $baseParams['lta']);
		$url = self::url('histdata.php', $baseParams);
		$cfg = array(
			'containerId' => $id,
			'panelId' => $panelId,
			'headingId' => $headingId,
			'url' => $url,
			'opts' => $opts,
			'groups' => $groups,
			'defaultType' => $default,
			'defaultGroup' => $defaultGroup,
			'showAggregation' => $isMonthly,
			'showNormals' => $isMonthly,
			'defaultSummary' => $defaultSummary,
			'defaultLta' => $defaultLta,
			'varMeta' => $varMeta,
			'aggLabels' => $aggLabels,
		);
		self::run('NW3.histSelectGrouped(' . json_encode($cfg) . ');');
	}

	/**
	 * Full Chart Viewer: LHS selector (variable group/measure, timescale, aggregation,
	 * period/length, normals/cumulative) + large chart. Used by charts.php.
	 */
	public static function chartViewer($opts = array()) {
		self::assets();
		$groups = self::selectableGroups();
		$varMeta = self::selectableVarMeta();
		$aggLabels = self::aggregationLabels();
		$default = isset($opts['default']) ? $opts['default'] : 'tmean';
		$height = isset($opts['height']) ? (int)$opts['height'] : 460;
		$headingId = isset($opts['headingId']) ? $opts['headingId'] : 'chart-heading';

		$defaultGroup = $groups[0]['id'];
		foreach ($groups as $g) {
			if (isset($g['options'][$default])) { $defaultGroup = $g['id']; break; }
		}
		$defaultAggs = isset($varMeta[$default]) ? $varMeta[$default]['aggregations'] : array(Data::SUMMARY_MEAN);
		$defaultSummary = $defaultAggs[0];
		$yrYest = (int)Date::$yr_yest;
		$yrNow = (int)Date::$dyear;

		$id = 'cv-chart';
		$panelId = 'cv-panel';

		echo '<div class="wxsel-chart wxsel-viewer">' . "\n";
		echo '<div id="' . $panelId . '" class="wxsel-panel" role="group" aria-label="Chart controls">' . "\n";
		self::emitGroupButtons($groups, $defaultGroup);
		echo '<div class="wxsel-label">Measure</div>' . "\n";
		echo '<div class="wxsel-subtypes" role="tablist"></div>' . "\n";

		echo '<div class="wxsel-label">Timescale</div>' . "\n";
		echo '<div class="wxsel-scale" role="tablist">';
		foreach (array('daily' => 'Daily', 'monthly' => 'Monthly', 'annual' => 'Annual') as $k => $lab) {
			$active = ($k === 'daily') ? ' active' : '';
			echo '<button type="button" class="' . trim($active) . '" data-scale="'
				. $k . '">' . $lab . '</button>';
		}
		echo '</div>' . "\n";

		echo '<div class="wxsel-agg-wrap">' . "\n";
		echo '<div class="wxsel-label">Aggregation</div>' . "\n";
		echo '<div class="wxsel-agg" role="tablist"></div>' . "\n";
		echo '</div>' . "\n";

		echo '<div class="wxsel-label">Period</div>' . "\n";
		echo '<div class="wxsel-period">' . "\n";

		echo '<label class="wxsel-field" id="cv-year-wrap"><span>Year</span>'
			. '<select id="cv-year" class="wxsel">';
		echo '<option value="0" selected="selected">Recent</option>';
		for ($i = $yrYest; $i >= 1871; $i--) {
			echo '<option value="' . $i . '">' . $i . '</option>';
		}
		echo '</select></label>' . "\n";

		echo '<label class="wxsel-field" id="cv-month-wrap" hidden><span>Month</span>'
			. '<select id="cv-month" class="wxsel">';
		echo '<option value="0" selected="selected">Whole year</option>';
		for ($i = 1; $i <= 12; $i++) {
			echo '<option value="' . $i . '">' . Date::$months3[$i - 1] . '</option>';
		}
		echo '</select></label>' . "\n";

		echo '<label class="wxsel-field" id="cv-lengthD-wrap"><span>Length</span>'
			. '<select id="cv-lengthD" class="wxsel">';
		foreach (array(
			31 => '31 days', 60 => '60 days', 90 => '90 days', 180 => '180 days',
			365 => '1 year', 730 => '2 years', 1095 => '3 years', 1826 => '5 years', 3653 => '10 years',
		) as $v => $lab) {
			$sel = ($v === 31) ? ' selected="selected"' : '';
			echo '<option value="' . $v . '"' . $sel . '>' . $lab . '</option>';
		}
		echo '</select></label>' . "\n";

		echo '<label class="wxsel-field" id="cv-lengthM-wrap" hidden><span>Length</span>'
			. '<select id="cv-lengthM" class="wxsel">';
		foreach (array(
			12 => '12 months', 24 => '2 years', 36 => '3 years',
			60 => '5 years', 120 => '10 years', 240 => '20 years',
		) as $v => $lab) {
			$sel = ($v === 12) ? ' selected="selected"' : '';
			echo '<option value="' . $v . '"' . $sel . '>' . $lab . '</option>';
		}
		echo '</select></label>' . "\n";

		echo '<label class="wxsel-field" id="cv-start-wrap" hidden><span>Start</span>'
			. '<select id="cv-start" class="wxsel">';
		foreach (array(1871, 1910, 1950, 1980, 1990, 2000, 2009, 2015) as $y) {
			$sel = ($y === 2009) ? ' selected="selected"' : '';
			echo '<option value="' . $y . '"' . $sel . '>' . $y . '</option>';
		}
		echo '</select></label>' . "\n";

		echo '<label class="wxsel-field" id="cv-end-wrap" hidden><span>End</span>'
			. '<select id="cv-end" class="wxsel">';
		for ($i = $yrNow; $i >= 1950; $i -= ($i > 2000 ? 1 : 10)) {
			$sel = ($i === $yrNow) ? ' selected="selected"' : '';
			echo '<option value="' . $i . '"' . $sel . '>' . $i . '</option>';
		}
		echo '</select></label>' . "\n";

		echo '</div>' . "\n"; // .wxsel-period

		echo '<div class="wxsel-label">Options</div>' . "\n";
		echo '<div class="wxsel-options">'
			. '<button type="button" class="wxsel-toggle active" id="cv-lta" title="Climate normals (mean/total only)">Normals</button>'
			. '<button type="button" class="wxsel-toggle" id="cv-cume" hidden title="Running total across the year">Cumulative</button>'
			. '</div>' . "\n";

		echo '</div>' . "\n"; // panel
		echo '<div id="' . $id . '" class="wxchart wxchart-loading" style="min-height:' . $height . 'px;"></div>' . "\n";
		echo '</div>' . "\n";

		echo '<p id="cv-disclaimer" hidden class="note">'
			. 'Data from before 2009 are mostly from the historical site at Whitestone Pond, Hampstead; '
			. 'where missing, nearby sites (St James\'s Park, Heathrow, Kew) were used, adjusted for site differences. '
			. 'Source: Met Office <a href="https://data.ceda.ac.uk/badc/ukmo-midas-open/">MIDAS Open</a>.'
			. '</p>' . "\n";

		$cfg = array(
			'containerId' => $id,
			'panelId' => $panelId,
			'headingId' => $headingId,
			'url' => self::url('histdata.php'),
			'opts' => array('height' => $height),
			'groups' => $groups,
			'defaultType' => $default,
			'defaultGroup' => $defaultGroup,
			'defaultSummary' => $defaultSummary,
			'defaultLta' => true,
			'varMeta' => $varMeta,
			'aggLabels' => $aggLabels,
		);
		self::run('NW3.histViewer(' . json_encode($cfg) . ');');
	}
}
