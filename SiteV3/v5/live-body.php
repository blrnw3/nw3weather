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

/**
 * Short/long-window trend arrow for a base variable (ported from the legacy
 * home page). Compares the current value to a short- and long-ago reading and
 * returns a small coloured glyph: rising / falling (double glyph when rapid),
 * or a steady dash. Thresholds and windows are in native units/minutes.
 * Returns '' when the trend data isn't available yet.
 */
function nw3_arrow($var, $shortMins, $longMins, $small, $big) {
	$T = isset(Live::$HR24['trend']) ? Live::$HR24['trend'] : null;
	if (!$T || !isset($T[0][$var], $T[$shortMins][$var], $T[$longMins][$var])) {
		return '';
	}
	$dShort = $T[0][$var] - $T[$shortMins][$var];
	$dLong = $T[0][$var] - $T[$longMins][$var];
	// Steady: negligible short move, short/long disagree, or long move too small.
	if ($dShort == 0 || (($dShort > 0) !== ($dLong > 0)) || abs($dLong) < $small) {
		return '<span class="wx-arrow wx-arrow-steady" title="Trend: steady">&ndash;</span>';
	}
	$rising = ($dLong > 0);
	$rapid = (abs($dLong) > $big);
	$glyph = $rising ? ($rapid ? '&uArr;' : '&uarr;') : ($rapid ? '&dArr;' : '&darr;');
	$cls = $rising ? 'wx-arrow-up' : 'wx-arrow-down';
	$title = 'Trend: ' . ($rising ? 'rising' : 'falling') . ($rapid ? ' rapidly' : '');
	return '<span class="wx-arrow ' . $cls . '" title="' . $title . '">' . $glyph . '</span>';
}

/** Format a signed delta (e.g. 24hr change): small up/down arrow + bold value. */
function nw3_delta($change, $unit) {
	if ($change === null) {
		return '-';
	}
	$arrow = '';
	if ($change > 0) {
		$arrow = '<span class="wx-arrow wx-arrow-up">&uarr;</span> ';
	} elseif ($change < 0) {
		$arrow = '<span class="wx-arrow wx-arrow-down">&darr;</span> ';
	}
	return $arrow . '<b>' . Wx::conv($change, $unit, true, true) . '</b>';
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
	return ($v === null) ? '-' : '<b>' . Wx::conv($v, $unit) . '</b>';
}

/** "low &rarr; high" range for yesterday for a base var. */
function nw3_yest_range($var, $unit) {
	$Y = nw3_yest_data();
	$lo = isset($Y['min'][$var]) ? $Y['min'][$var] : null;
	$hi = isset($Y['max'][$var]) ? $Y['max'][$var] : null;
	if ($lo === null && $hi === null) {
		return '-';
	}
	return (($lo === null) ? '-' : '<b>' . Wx::conv($lo, $unit) . '</b>') . ' &rarr; ' . (($hi === null) ? '-' : '<b>' . Wx::conv($hi, $unit) . '</b>');
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

	// Numeric live values per var-id (var0..var6), used by the flash-on-change JS.
	$flashData = array(
		(float)Live::$temp, (float)Live::$humi, (float)Live::$dewp, (float)Live::$pres,
		(float)Live::$wind, (float)Live::$rain,
		(Live::$pm25 === null ? null : (float)Live::$pm25),
	);
	echo '<input id="newData" type="hidden" value="' . htmlspecialchars(json_encode($flashData), ENT_QUOTES) . '" />';

	// Temperature
	$nowHtml = '<span id="var0">' . Wx::conv(Live::$temp, Wx::Temperature) . '</span> '
		. nw3_trend(nw3_live_get($HR, 'changeHr', 'temp'), Wx::AbsTemp) . ' '
		. nw3_arrow('temp', 15, 30, 0.3, 0.8);
	// Only surface "feels like" when it differs meaningfully from the actual temp.
	$feelHtml = (abs((float)Live::$feel - (float)Live::$temp) > 0.5)
		? 'Feels like <b>' . Wx::conv(Live::$feel, Wx::Temperature) . '</b>'
		: '';
	nw3_card('temp', 'thermom8', 'Temperature', 'wx14.php', 'Detailed temperature data',
		$nowHtml,
		$feelHtml,
		array(
			array('Low', nw3_at(nw3_live_get($NOW, 'min', 'temp'), Wx::Temperature, nw3_live_get($NOW, 'timeMin', 'temp'))),
			array('High', nw3_at(nw3_live_get($NOW, 'max', 'temp'), Wx::Temperature, nw3_live_get($NOW, 'timeMax', 'temp'))),
			array('24hr mean', nw3_at(nw3_live_get($HR, 'mean', 'temp'), Wx::Temperature, null)),
			array('Yesterday', nw3_yest_range('temp', Wx::Temperature)),
			array('24hr trend', nw3_delta(nw3_live_get($HR, 'changeDay', 'temp'), Wx::AbsTemp)),
		));

	// Rain
	$rn24 = nw3_live_get($HR, 'trendRn', 0);
	$rnHr = (($rn24 !== null) && (nw3_live_get($HR, 'trendRn', 1) !== null)) ? $rn24 - $HR['trendRn'][1] : null;
	$rn10 = (($rn24 !== null) && (nw3_live_get($HR, 'trendRn', '10m') !== null)) ? $rn24 - $HR['trendRn']['10m'] : null;
	$rate = nw3_live_get($NOW, 'misc', 'rnrate');
	$rnlast = nw3_live_get($NOW, 'misc', 'rnlast');
	$monthrn = null;
	$yearrn = null;
	if (file_exists(ROOT . 'RainTags.php')) {
		include ROOT . 'RainTags.php';
	}
	$rained1h = ($rnHr !== null && $rnHr > 0);
	$nowHtml = '<span id="var5">' . Wx::conv(Live::$rain, Wx::Rain) . '</span> '
		. (($rate === null) ? '' : '<span class="wx-rate">' . Wx::conv($rate, Wx::RainRate) . '</span>')
		. ($rained1h ? ' ' . nw3_arrow('rain', 20, 45, 0.1, 1) : '');
	nw3_card('rain', 'rain2', 'Rainfall', 'wx12.php', 'Detailed rainfall data',
		$nowHtml,
		(($rained1h && $rn10 !== null) ? '10-min: <b>' . Wx::conv($rn10, Wx::Rain) . '</b>' : ''),
		array(
			array('Last hour', (($rnHr === null) ? '-' : '<b>' . Wx::conv($rnHr, Wx::Rain) . '</b>')),
			array('Yesterday', nw3_yest_val('mean', 'rain', Wx::Rain)),
			array('Month total', (($monthrn === null) ? '-' : '<b>' . Wx::conv($monthrn, Wx::Rain) . '</b>')),
			array('Annual total', (($yearrn === null) ? '-' : '<b>' . Wx::conv($yearrn, Wx::Rain) . '</b>')),
			array('Last rain', (($rnlast === null || $rnlast === '') ? '-' : $rnlast)),
		));

	// Wind
	$maxGust = nw3_live_get($NOW, 'max', 'gust');
	$nowHtml = '<span id="var4">' . Wx::conv(Live::$wind, Wx::Wind) . '</span> ' . Wx::conv(Live::$wdir, Wx::Direction);
	nw3_card('wind', 'windy', 'Wind', 'wx13.php', 'Detailed wind data',
		$nowHtml,
		'Gusting to <b>' . Wx::conv(Live::$gustRaw, Wx::Wind) . '</b> &middot; ' . nw3_bft_force(Live::$wind),
		array(
			array('Max gust', nw3_at($maxGust, Wx::Wind, nw3_live_get($NOW, 'timeMax', 'gust'))),
			array('Max hr gust', (Live::$maxgsthr === null ? '-' : '<b>' . Wx::conv(Live::$maxgsthr, Wx::Wind) . '</b>')),
			array('Today avg', nw3_at(nw3_live_get($NOW, 'mean', 'wind'), Wx::Wind, null)),
			array('Yesterday avg', nw3_yest_val('mean', 'wind', Wx::Wind)),
		));

	// Humidity
	$nowHtml = '<span id="var1">' . Wx::conv(Live::$humi, Wx::Humidity) . '</span> '
		. nw3_trend(nw3_live_get($HR, 'changeHr', 'humi'), Wx::Humidity) . ' '
		. nw3_arrow('humi', 15, 45, 2, 8);
	nw3_card('humi', 'humidity', 'Relative Humidity', 'wx10.php', 'Detailed humidity data',
		$nowHtml, '',
		array(
			array('Low', nw3_at(nw3_live_get($NOW, 'min', 'humi'), Wx::Humidity, nw3_live_get($NOW, 'timeMin', 'humi'))),
			array('High', nw3_at(nw3_live_get($NOW, 'max', 'humi'), Wx::Humidity, nw3_live_get($NOW, 'timeMax', 'humi'))),
			array('Yesterday', nw3_yest_range('humi', Wx::Humidity)),
			array('24hr trend', nw3_delta(nw3_live_get($HR, 'changeDay', 'humi'), Wx::Humidity)),
		));

	// Dew point
	$nowHtml = '<span id="var2">' . Wx::conv(Live::$dewp, Wx::Temperature) . '</span> '
		. nw3_trend(nw3_live_get($HR, 'changeHr', 'dewp'), Wx::AbsTemp) . ' '
		. nw3_arrow('dewp', 15, 30, 0.4, 0.9);
	nw3_card('humi', 'dewy', 'Dew Point', 'wx10.php', 'Detailed dew point data',
		$nowHtml, '',
		array(
			array('Low', nw3_at(nw3_live_get($NOW, 'min', 'dewp'), Wx::Temperature, nw3_live_get($NOW, 'timeMin', 'dewp'))),
			array('High', nw3_at(nw3_live_get($NOW, 'max', 'dewp'), Wx::Temperature, nw3_live_get($NOW, 'timeMax', 'dewp'))),
			array('Yesterday', nw3_yest_range('dewp', Wx::Temperature)),
			array('24hr trend', nw3_delta(nw3_live_get($HR, 'changeDay', 'dewp'), Wx::AbsTemp)),
		));

	// Pressure
	$nowHtml = '<span id="var3">' . Wx::conv(Live::$pres, Wx::Pressure) . '</span> '
		. nw3_trend(nw3_live_get($HR, 'changeHr', 'pres'), Wx::Pressure) . ' '
		. nw3_arrow('pres', 60, 120, 1, 2);
	nw3_card('pres', 'pressure2', 'Pressure', 'wx16.php', 'Detailed pressure data',
		$nowHtml, '',
		array(
			array('Low', nw3_at(nw3_live_get($NOW, 'min', 'pres'), Wx::Pressure, nw3_live_get($NOW, 'timeMin', 'pres'))),
			array('High', nw3_at(nw3_live_get($NOW, 'max', 'pres'), Wx::Pressure, nw3_live_get($NOW, 'timeMax', 'pres'))),
			array('Yesterday', nw3_yest_range('pres', Wx::Pressure)),
			array('24hr trend', nw3_delta(nw3_live_get($HR, 'changeDay', 'pres'), Wx::Pressure)),
		));

	// Air quality (raw PM2.5 -> UK DAQI band + US AQI)
	list($daqiBand, $daqiName, $daqiClass) = Wx::daqi(Live::$pm25);
	$usAqi = Wx::usAqi(Live::$pm25);
	$pm25Now = (Live::$pm25 === null)
		? '<span id="var6" class="daqi-status daqi-unknown"><span class="daqi-dot"></span>No data</span>'
		: '<span id="var6" class="daqi-status ' . $daqiClass . '"><span class="daqi-dot"></span>DAQI ' . $daqiBand . ' &middot; ' . $daqiName . '</span>';
	$pm25Sub = (Live::$pm25 === null) ? ''
		: 'US AQI <b>' . $usAqi . '</b> &middot; PM2.5 <b>' . Wx::conv(Live::$pm25, Wx::Pm25) . '</b>';
	nw3_card('pm25', 'sky3', 'Air Quality', '', 'Air quality - UK DAQI band &amp; US AQI (from PM2.5)',
		$pm25Now,
		$pm25Sub,
		array(
			array('High', nw3_at(nw3_live_get($NOW, 'max', 'pm25'), Wx::Pm25, nw3_live_get($NOW, 'timeMax', 'pm25'))),
			array('24hr avg', nw3_at(nw3_live_get($HR, 'mean', 'pm25'), Wx::Pm25, null)),
			array('Yesterday avg', nw3_yest_val('mean', 'pm25', Wx::Pm25)),
		));
}
?>
