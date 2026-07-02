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
		echo '<script src="/v5/wxcharts.js"></script>' . "\n";
	}

	/** Emit a uniquely-identified chart container div; returns its id. */
	public static function container($opts = array()) {
		$id = 'wxc' . (++self::$seq);
		$class = isset($opts['class']) ? $opts['class'] : 'wxchart';
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
			'rain' => array('Rain', 'rain2_small.png'),
			'wind' => array('Wind', 'windy_small.png'),
			'humi' => array('Humidity', 'humidity_small.png'),
			'dewp' => array('Dew point', 'dewy_small.png'),
			'pres' => array('Pressure', 'pressure2_small.png'),
			'pm25' => array('Air quality', 'sky3_small.png'),
			'wdir' => array('Wind dir', 'compass_small.png'),
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
		echo '<div id="wx3-multi-a" class="wx3-chart" style="min-height:' . $multiHeight . 'px;"></div>' . "\n";
		echo '<div id="wx3-multi-b" class="wx3-chart" style="min-height:' . $multiHeight . 'px;"></div>' . "\n";
		echo '</div>' . "\n";

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

		echo '<div id="wx3-main" class="wx3-chart" style="min-height:' . $mainHeight . 'px;"></div>' . "\n";

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
	 * Categorical trend chart whose variable is chosen from a dropdown. $params holds
	 * the shared mode/length/lta (but no type); the picked type is appended client-side.
	 */
	public static function dailySelectable($params, $opts = array(), $vars = null, $default = 'wmean') {
		self::assets();
		if ($vars === null) { $vars = self::selectableVars(); }
		$id = 'wxc' . (++self::$seq);
		$selId = $id . '-sel';
		$height = isset($opts['height']) ? (int)$opts['height'] : 320;

		echo '<div class="wxsel-chart">' . "\n";
		echo '<div class="wxsel-bar"><label for="' . $selId . '">Variable:</label> '
			. '<select id="' . $selId . '" class="wxsel">';
		foreach ($vars as $type => $label) {
			$sel = ($type === $default) ? ' selected' : '';
			echo '<option value="' . htmlspecialchars($type) . '"' . $sel . '>' . htmlspecialchars($label) . '</option>';
		}
		echo '</select></div>' . "\n";
		echo '<div id="' . $id . '" class="wxchart" style="min-height:' . $height . 'px;"></div>' . "\n";
		echo '</div>' . "\n";

		$url = self::url('histdata.php', $params);
		self::run('NW3.histSelect(' . json_encode($id) . ',' . json_encode($selId) . ','
			. json_encode($url) . ',' . json_encode($opts) . ');');
	}
}
