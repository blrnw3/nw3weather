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
		self::run('NW3.histChart(' . json_encode($id) . ',' . json_encode($url) . ');');
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

	/** Wind rose (polar stacked column) from rosedata.php. */
	public static function rose($params, $opts = array()) {
		self::assets();
		$id = self::container($opts);
		$url = self::url('rosedata.php', $params);
		self::run('NW3.windRose(' . json_encode($id) . ',' . json_encode($url) . ');');
	}
}
