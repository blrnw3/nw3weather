<?php
/**
 * Renders the live current-conditions cards for the home page.
 * Used both inline (index.php) and by the AJAX refresh endpoint (ajaxwxbody.php).
 * Relies on Live::init() having been run (via Page::init).
 */
function nw3_live_get($arr, $k1, $k2 = null) {
	if ($k2 === null) {
		return isset($arr[$k1]) ? $arr[$k1] : null;
	}
	return isset($arr[$k1][$k2]) ? $arr[$k1][$k2] : null;
}

/**
 * Renders a single dashboard card.
 */
function nw3_card($mod, $icon, $label, $href, $title, $nowHtml, $subHtml, $rows) {
	echo '<div class="wx-card wx-card-' . $mod . '">';
	echo '<div class="wx-card-head">';
	if ($icon) {
		echo '<img class="wx-card-icon" src="' . Site::IMG_ROOT . $icon . '_small.png" alt="" width="36" height="36" />';
	}
	if ($href) {
		echo '<a class="hidden-link" href="' . $href . '" title="' . $title . '">' . $label . '</a>';
	} else {
		echo '<span class="wx-card-label" title="' . $title . '">' . $label . '</span>';
	}
	echo '</div>';
	echo '<div class="wx-card-now">' . $nowHtml . '</div>';
	if ($subHtml !== '' && $subHtml !== null) {
		echo '<div class="wx-card-sub">' . $subHtml . '</div>';
	}
	echo '<div class="wx-card-rows">';
	foreach ($rows as $r) {
		echo '<div><span class="k">' . $r[0] . '</span><span class="v">' . $r[1] . '</span></div>';
	}
	echo '</div>';
	echo '</div>';
}

/** Format a current value with its 24hr-trend (per hour). */
function nw3_trend($change, $unit) {
	if ($change === null) {
		return '';
	}
	return '<span class="wx-rate">' . Wx::conv($change, $unit, true, true) . ' /hr</span>';
}

/** Format a signed delta (e.g. 24hr change), with a dash when missing. */
function nw3_delta($change, $unit) {
	return ($change === null) ? '-' : Wx::conv($change, $unit, true, true);
}

/** Format "value @ time", with a dash when missing. */
function nw3_at($val, $unit, $time) {
	$v = ($val === null) ? '-' : Wx::conv($val, $unit);
	return '<b>' . $v . '</b>' . ($time ? ' <span class="wx-when">@ ' . $time . '</span>' : '');
}

/**
 * Yesterday's daily summary, computed and cached by cron (serialised_datYest.txt).
 * Same shape as Live::$NOW: ['min'|'max'|'mean'][var]. Loaded once per request.
 */
function nw3_yest_data() {
	static $YEST = null;
	if ($YEST === null) {
		$f = ROOT . 'serialised_datYest.txt';
		$YEST = file_exists($f) ? unserialize(file_get_contents($f)) : array();
		if (!is_array($YEST)) {
			$YEST = array();
		}
	}
	return $YEST;
}

/** Single yesterday summary value ($agg in min|max|mean) for a base var. */
function nw3_yest_val($agg, $var, $unit) {
	$Y = nw3_yest_data();
	$v = isset($Y[$agg][$var]) ? $Y[$agg][$var] : null;
	return ($v === null) ? '-' : Wx::conv($v, $unit);
}

/** "low &rarr; high" range for yesterday for a base var. */
function nw3_yest_range($var, $unit) {
	$Y = nw3_yest_data();
	$lo = isset($Y['min'][$var]) ? $Y['min'][$var] : null;
	$hi = isset($Y['max'][$var]) ? $Y['max'][$var] : null;
	if ($lo === null && $hi === null) {
		return '-';
	}
	return (($lo === null) ? '-' : Wx::conv($lo, $unit)) . ' &rarr; ' . (($hi === null) ? '-' : Wx::conv($hi, $unit));
}

/** Beaufort force number (e.g. "F4"), linked to the scale page. */
function nw3_bft_force($mph) {
	$num = preg_match('/F(\d+)/', Wx::bft($mph), $mm) ? $mm[1] : '';
	return '<a href="/BeaufortScale.php" title="Beaufort scale">F' . $num . '</a>';
}

/**
 * Renders the per-variable dashboard cards for the home page.
 * Replaces the old single live table; refreshed via the AJAX endpoint.
 */
function nw3_render_cards() {
	$NOW = Live::$NOW;
	$HR = Live::$HR24;

	echo '<input id="WDtime" type="hidden" value="' . Live::$unix . '" />';
	echo '<input id="Servertime" type="hidden" value="' . time() . '" />';

	// Temperature
	$nowHtml = '<span id="var0">' . Wx::conv(Live::$temp, Wx::Temperature) . '</span> '
		. nw3_trend(nw3_live_get($HR, 'changeHr', 'temp'), Wx::AbsTemp);
	nw3_card('temp', 'thermom8', 'Temperature', 'wx14.php', 'Detailed temperature data',
		$nowHtml,
		'Feels like <b>' . Wx::conv(Live::$feel, Wx::Temperature) . '</b>',
		array(
			array('Low', nw3_at(nw3_live_get($NOW, 'min', 'temp'), Wx::Temperature, nw3_live_get($NOW, 'timeMin', 'temp'))),
			array('High', nw3_at(nw3_live_get($NOW, 'max', 'temp'), Wx::Temperature, nw3_live_get($NOW, 'timeMax', 'temp'))),
			array('24hr avg', nw3_at(nw3_live_get($HR, 'mean', 'temp'), Wx::Temperature, null)),
			array('24hr trend', nw3_delta(nw3_live_get($HR, 'changeDay', 'temp'), Wx::AbsTemp)),
			array('Yesterday', nw3_yest_range('temp', Wx::Temperature)),
		));

	// Humidity
	$nowHtml = '<span id="var1">' . Wx::conv(Live::$humi, Wx::Humidity) . '</span> '
		. nw3_trend(nw3_live_get($HR, 'changeHr', 'humi'), Wx::Humidity);
	nw3_card('humi', 'humidity', 'Relative Humidity', 'wx10.php', 'Detailed humidity data',
		$nowHtml, '',
		array(
			array('Low', nw3_at(nw3_live_get($NOW, 'min', 'humi'), Wx::Humidity, nw3_live_get($NOW, 'timeMin', 'humi'))),
			array('High', nw3_at(nw3_live_get($NOW, 'max', 'humi'), Wx::Humidity, nw3_live_get($NOW, 'timeMax', 'humi'))),
			array('24hr avg', nw3_at(nw3_live_get($HR, 'mean', 'humi'), Wx::Humidity, null)),
			array('24hr trend', nw3_delta(nw3_live_get($HR, 'changeDay', 'humi'), Wx::Humidity)),
			array('Yesterday', nw3_yest_range('humi', Wx::Humidity)),
		));

	// Dew point
	$nowHtml = '<span id="var2">' . Wx::conv(Live::$dewp, Wx::Temperature) . '</span> '
		. nw3_trend(nw3_live_get($HR, 'changeHr', 'dewp'), Wx::AbsTemp);
	nw3_card('humi', 'dewy', 'Dew Point', 'wx10.php', 'Detailed dew point data',
		$nowHtml, '',
		array(
			array('Low', nw3_at(nw3_live_get($NOW, 'min', 'dewp'), Wx::Temperature, nw3_live_get($NOW, 'timeMin', 'dewp'))),
			array('High', nw3_at(nw3_live_get($NOW, 'max', 'dewp'), Wx::Temperature, nw3_live_get($NOW, 'timeMax', 'dewp'))),
			array('24hr avg', nw3_at(nw3_live_get($HR, 'mean', 'dewp'), Wx::Temperature, null)),
			array('24hr trend', nw3_delta(nw3_live_get($HR, 'changeDay', 'dewp'), Wx::AbsTemp)),
			array('Yesterday', nw3_yest_range('dewp', Wx::Temperature)),
		));

	// Pressure
	$nowHtml = '<span id="var3">' . Wx::conv(Live::$pres, Wx::Pressure) . '</span> '
		. nw3_trend(nw3_live_get($HR, 'changeHr', 'pres'), Wx::Pressure);
	nw3_card('pres', 'pressure2', 'Pressure', 'wx16.php', 'Detailed pressure data',
		$nowHtml, '',
		array(
			array('Low', nw3_at(nw3_live_get($NOW, 'min', 'pres'), Wx::Pressure, nw3_live_get($NOW, 'timeMin', 'pres'))),
			array('High', nw3_at(nw3_live_get($NOW, 'max', 'pres'), Wx::Pressure, nw3_live_get($NOW, 'timeMax', 'pres'))),
			array('24hr avg', nw3_at(nw3_live_get($HR, 'mean', 'pres'), Wx::Pressure, null)),
			array('24hr trend', nw3_delta(nw3_live_get($HR, 'changeDay', 'pres'), Wx::Pressure)),
			array('Yesterday', nw3_yest_range('pres', Wx::Pressure)),
		));

	// Wind
	$maxGust = nw3_live_get($NOW, 'max', 'gust');
	$maxSpeed = nw3_live_get($NOW, 'max', 'wind');
	$nowHtml = '<span id="var4">' . Wx::conv(Live::$wind, Wx::Wind) . '</span> ' . Wx::conv(Live::$wdir, Wx::Direction);
	nw3_card('wind', 'windy', 'Wind', 'wx13.php', 'Detailed wind data',
		$nowHtml,
		'Gusting to <b>' . Wx::conv(Live::$gustRaw, Wx::Wind) . '</b> &middot; ' . nw3_bft_force(Live::$wind),
		array(
			array('Max gust', nw3_at($maxGust, Wx::Wind, nw3_live_get($NOW, 'timeMax', 'gust'))),
			array('Max hr gust', (Live::$maxgsthr === null ? '-' : '<b>' . Wx::conv(Live::$maxgsthr, Wx::Wind) . '</b>')),
			array('Max speed', nw3_at($maxSpeed, Wx::Wind, nw3_live_get($NOW, 'timeMax', 'wind'))),
			array('Today avg', nw3_at(nw3_live_get($NOW, 'mean', 'wind'), Wx::Wind, null)),
			array('Yesterday avg', nw3_yest_val('mean', 'wind', Wx::Wind)),
		));

	// Rain
	$rn24 = nw3_live_get($HR, 'trendRn', 0);
	$rnHr = (($rn24 !== null) && (nw3_live_get($HR, 'trendRn', 1) !== null)) ? $rn24 - $HR['trendRn'][1] : null;
	$rn10 = (($rn24 !== null) && (nw3_live_get($HR, 'trendRn', '10m') !== null)) ? $rn24 - $HR['trendRn']['10m'] : null;
	$rate = nw3_live_get($NOW, 'misc', 'rnrate');
	$rnlast = nw3_live_get($NOW, 'misc', 'rnlast');
	$monthrn = null;
	if (file_exists(ROOT . 'RainTags.php')) {
		include ROOT . 'RainTags.php';
	}
	$nowHtml = '<span id="var5">' . Wx::conv(Live::$rain, Wx::Rain) . '</span> '
		. (($rn10 === null) ? '' : '<span class="wx-rate">' . Wx::conv($rn10, Wx::Rain) . ' /10min</span>');
	nw3_card('rain', 'rain2', 'Rainfall', 'wx12.php', 'Detailed rainfall data',
		$nowHtml,
		'Rate: <b>' . (($rate === null) ? '-' : Wx::conv($rate, Wx::RainRate)) . '</b>',
		array(
			array('Last hour', (($rnHr === null) ? '-' : Wx::conv($rnHr, Wx::Rain))),
			array('Last 24hr', (($rn24 === null) ? '-' : Wx::conv($rn24, Wx::Rain))),
			array('Month total', (($monthrn === null) ? '-' : Wx::conv($monthrn, Wx::Rain))),
			array('Yesterday', nw3_yest_val('mean', 'rain', Wx::Rain)),
			array('Last rain', (($rnlast === null || $rnlast === '') ? '-' : $rnlast)),
		));

	// Air quality (raw PM2.5 -> UK DAQI band + US AQI)
	list($daqiBand, $daqiName, $daqiClass) = Wx::daqi(Live::$pm25);
	$usAqi = Wx::usAqi(Live::$pm25);
	$pm25Now = (Live::$pm25 === null)
		? '<span id="var6" class="daqi-band daqi-unknown">No data</span>'
		: '<span id="var6" class="daqi-band ' . $daqiClass . '">DAQI ' . $daqiBand . ' &middot; ' . $daqiName . '</span>';
	$pm25Sub = (Live::$pm25 === null) ? ''
		: 'US AQI <b>' . $usAqi . '</b> &middot; PM2.5 <b>' . Wx::conv(Live::$pm25, Wx::Pm25) . '</b>';
	nw3_card('pm25', 'sky3', 'Air Quality', '', 'Air quality - UK DAQI band &amp; US AQI (from PM2.5)',
		$pm25Now,
		$pm25Sub,
		array(
			array('Low', nw3_at(nw3_live_get($NOW, 'min', 'pm25'), Wx::Pm25, nw3_live_get($NOW, 'timeMin', 'pm25'))),
			array('High', nw3_at(nw3_live_get($NOW, 'max', 'pm25'), Wx::Pm25, nw3_live_get($NOW, 'timeMax', 'pm25'))),
			array('24hr avg', nw3_at(nw3_live_get($HR, 'mean', 'pm25'), Wx::Pm25, null)),
			array('Yesterday avg', nw3_yest_val('mean', 'pm25', Wx::Pm25)),
		));
}
?>
