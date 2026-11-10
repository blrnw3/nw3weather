<?php
/**
 * Renders the live current-conditions cards for the home page.
 * Used both inline (index.php) and by the AJAX refresh endpoint (ajaxwxbody.php).
 * Relies on Live::init() having been run (via Page::init).
 */
require_once __DIR__ . '/SunTracker.php';
require_once __DIR__ . '/ClimateCard.php';
require_once __DIR__ . '/RankFlags.php';
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
		// An icon ending in .svg is used verbatim; a bare name maps to a *_small.png.
		$iconSrc = (substr($icon, -4) === '.svg') ? Site::IMG_ROOT . $icon : Site::IMG_ROOT . $icon . '_small.png';
		echo '<img class="wx-card-icon" src="' . $iconSrc . '" alt="" width="36" height="36" />';
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
function nw3_trend($change, $unit, $showUnit = true) {
	if ($change === null) {
		return '';
	}
	return '<span class="wx-rate">' . Wx::conv($change, $unit, $showUnit, true) . ' /hr</span>';
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

/** Format "value @ time", with a dash when missing. $dpa adjusts decimal places. */
function nw3_at($val, $unit, $time, $dpa = 0) {
	$v = ($val === null) ? '-' : Wx::conv($val, $unit, true, false, $dpa);
	return '<b>' . $v . '</b>' . ($time ? ' <span class="wx-when">@ ' . $time . '</span>' : '');
}

/**
 * Yesterday's daily summary, computed and cached by cron (serialised_datYest.txt).
 * Same shape as Live::$NOW: ['min'|'max'|'mean'][var]. Loaded once per request.
 */
function nw3_yest_data() {
	static $YEST = null;
	if ($YEST === null) {
		$f = CACHE_ROOT . 'serialised_datYest.txt';
		$YEST = file_exists($f) ? unserialize(file_get_contents($f)) : array();
		if (!is_array($YEST)) {
			$YEST = array();
		}
	}
	return $YEST;
}

/** Single yesterday summary value ($agg in min|max|mean) for a base var. */
function nw3_yest_val($agg, $var, $unit, $dpa = 0) {
	$Y = nw3_yest_data();
	$v = isset($Y[$agg][$var]) ? $Y[$agg][$var] : null;
	return ($v === null) ? '-' : '<b>' . Wx::conv($v, $unit, true, false, $dpa) . '</b>';
}

/** "low &rarr; high" range for yesterday for a base var. */
function nw3_yest_range($var, $unit) {
	$Y = nw3_yest_data();
	$lo = isset($Y['min'][$var]) ? $Y['min'][$var] : null;
	$hi = isset($Y['max'][$var]) ? $Y['max'][$var] : null;
	if ($lo === null && $hi === null) {
		return '-';
	}
	return (($lo === null) ? '-' : '<b>' . Wx::conv($lo, $unit, false) . '</b>') . ' &rarr; ' . (($hi === null) ? '-' : '<b>' . Wx::conv($hi, $unit, false) . '</b>');
}

/** Beaufort force number (e.g. "F4"), linked to the scale page. */
function nw3_bft_force($mph) {
	$num = preg_match('/F(\d+)/', Wx::bft($mph), $mm) ? $mm[1] : '';
	return '<a href="/BeaufortScale.php" title="Beaufort scale">F' . $num . '</a>';
}


/** Site rain-day threshold (mm): gauge resolution, same as ClimateCard::WET_MM. */
function nw3_rain_day_mm() {
	return 0.2;
}

/**
 * Count rain days (>= 0.2 mm) from $monthFrom/$dayFrom through $monthTo/$dayTo
 * in $year, using Live today's total for the current date so the count stays
 * in step with the card's other live figures.
 */
function nw3_count_rain_days($year, $monthFrom, $dayFrom, $monthTo, $dayTo, $todayRain) {
	$n = 0;
	$ty = (int)Date::$dyear;
	$tm = (int)Date::$dmonth;
	$td = (int)Date::$dday;
	$thresh = nw3_rain_day_mm();
	for ($m = $monthFrom; $m <= $monthTo; $m++) {
		$dim = (int)date('t', Date::mkdate($m, 1, $year));
		$d0 = ($m === $monthFrom) ? $dayFrom : 1;
		$d1 = ($m === $monthTo) ? min($dayTo, $dim) : $dim;
		for ($d = $d0; $d <= $d1; $d++) {
			if ($year === $ty && $m === $tm && $d === $td) {
				$v = $todayRain;
			} else {
				$v = Data::get('rain', $year, $m, $d);
			}
			if (nw3_is_numeric($v) && (float)$v >= $thresh) { $n++; }
		}
	}
	return $n;
}

/**
 * Colour-coded rain status for the home card.
 * Rate bands match the existing homepage weather-decode scale in index.php
 * (Slight / Light / Moderate / Heavy / Very Heavy / Torrential).
 * Recent = not raining now (rate <= 0) but rain in the last hour.
 */
function nw3_rain_status($rate, $rnHr) {
	$raining = ($rate !== null && (float)$rate > 0);
	$recentHr = ($rnHr !== null && (float)$rnHr > 0);
	if ($raining) {
		$r = (float)$rate;
		if ($r < 0.5) { $label = 'Slight'; $cls = 'slight'; }
		elseif ($r < 2) { $label = 'Light'; $cls = 'light'; }
		elseif ($r < 8) { $label = 'Moderate'; $cls = 'moderate'; }
		elseif ($r < 35) { $label = 'Heavy'; $cls = 'heavy'; }
		elseif ($r < 60) { $label = 'Very Heavy'; $cls = 'very-heavy'; }
		else { $label = 'Torrential'; $cls = 'torrential'; }
	} elseif ($recentHr) {
		$label = 'Recent'; $cls = 'recent';
	} else {
		$label = 'Dry'; $cls = 'dry';
	}
	$tip = ($rate === null) ? 'Rain rate unavailable' : ('Rain rate: ' . Wx::conv($rate, Wx::RainRate));
	if ($cls === 'recent') {
		$tip = 'Rain in the last hour. ' . $tip;
	}
	return '<span class="rain-status rain-status-' . $cls . '" title="' . htmlspecialchars($tip, ENT_QUOTES) . '">'
		. $label . '</span>';
}

/** Signed percent vs normal-to-date, e.g. +12% / &minus;30%. */
function nw3_pct_vs_normal($pct) {
	if ($pct === null || !nw3_is_numeric($pct)) { return ''; }
	$n = (int)round($pct);
	if ($n > 0) { $s = '+' . $n . '%'; }
	elseif ($n < 0) { $s = '&minus;' . abs($n) . '%'; }
	else { $s = '0%'; }
	return ' <span class="clim-anom" title="Difference from normal to date">' . $s . '</span>';
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
	echo '<input id="newData" type="hidden" value="' . htmlspecialchars(nw3_json_encode($flashData), ENT_QUOTES) . '" />';

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
	// rnlast (last-rain timestamp) is only computed when dailyData() runs for the
	// dated file, so it lives in the HR24 snapshot rather than NOW ('today').
	$rnlast = nw3_live_get($HR, 'misc', 'rnlast');
	$monthrn = null;
	$yearrn = null;
	$rainSummary = DataSummarizer::summarizeCached('rain', Site::BASE_YEAR);
	if (isset($rainSummary['period_summaries']['curr_month']['sum'])) {
		$monthrn = $rainSummary['period_summaries']['curr_month']['sum'];
	}
	if (isset($rainSummary['period_summaries']['curr_year']['sum'])) {
		$yearrn = $rainSummary['period_summaries']['curr_year']['sum'];
	}
	$rained24 = ($rn24 !== null && (float)$rn24 > 0);
	$nowHtml = '<span id="var5">' . Wx::conv(Live::$rain, Wx::Rain) . '</span> '
		. nw3_rain_status($rate, $rnHr);
	$subParts = array();
	if ($rained24) {
		if ($rn10 !== null) { $subParts[] = '10-min: <b>' . Wx::conv($rn10, Wx::Rain) . '</b>'; }
		if ($rnHr !== null) { $subParts[] = 'Last hour: <b>' . Wx::conv($rnHr, Wx::Rain) . '</b>'; }
	}
	$monthPct = (isset($rainSummary['period_summaries']['curr_month']['anom_pct'])
		? $rainSummary['period_summaries']['curr_month']['anom_pct'] : null);
	$yearPct = (isset($rainSummary['period_summaries']['curr_year']['anom_pct'])
		? $rainSummary['period_summaries']['curr_year']['anom_pct'] : null);
	$monthDays = nw3_count_rain_days((int)Date::$dyear, (int)Date::$dmonth, 1, (int)Date::$dmonth, (int)Date::$dday, Live::$rain);
	$yearDays = nw3_count_rain_days((int)Date::$dyear, 1, 1, (int)Date::$dmonth, (int)Date::$dday, Live::$rain);
	nw3_card('rain', 'icon-rain.svg', 'Rainfall', 'wx12.php', 'Detailed rainfall data',
		$nowHtml,
		implode(' &middot; ', $subParts),
		array(
			array('Yesterday', nw3_yest_val('mean', 'rain', Wx::Rain)),
			array('Month total', (($monthrn === null) ? '-' : '<b>' . Wx::conv($monthrn, Wx::Rain) . '</b>' . nw3_pct_vs_normal($monthPct))),
			array('Annual total', (($yearrn === null) ? '-' : '<b>' . Wx::conv($yearrn, Wx::Rain) . '</b>' . nw3_pct_vs_normal($yearPct))),
			array('<span title="Days with &ge;0.2 mm this month / year">Rain days</span>', '<b>' . $monthDays . '</b> / <b>' . $yearDays . '</b>'),
			array('Last rain', (($rnlast === null || $rnlast === '') ? '-' : $rnlast)),
		));

	// Wind
	$maxGust = nw3_live_get($NOW, 'max', 'gust');
	$nowHtml = '<span id="var4">' . Wx::conv(Live::$wind, Wx::Wind) . '</span> ' . Wx::conv(Live::$wdir, Wx::Direction);
	nw3_card('wind', 'icon-wind.svg', 'Wind', 'wx13.php', 'Detailed wind data',
		$nowHtml,
		'Gusting to <b>' . Wx::conv(Live::gustNow(), Wx::Wind, true, false, -1) . '</b> &middot; ' . nw3_bft_force(Live::$wind),
		array(
			array('Max gust', nw3_at($maxGust, Wx::Wind, nw3_live_get($NOW, 'timeMax', 'gust'), -1)),
			array('Max hr gust', (Live::$maxgsthr === null ? '-' : '<b>' . Wx::conv(Live::$maxgsthr, Wx::Wind, true, false, -1) . '</b>')),
			array('Today avg', nw3_at(nw3_live_get($NOW, 'mean', 'wind'), Wx::Wind, null)),
			array('Yesterday avg', nw3_yest_val('mean', 'wind', Wx::Wind)),
		));

	// Sun & moon: webcam sunshine today, sunrise-to-sunset strip, day length, moon (refreshed with the cards)
	SunTracker::renderCard();

	// Humidity
	$nowHtml = '<span id="var1">' . Wx::conv(Live::$humi, Wx::Humidity) . '</span> '
		. nw3_trend(nw3_live_get($HR, 'changeHr', 'humi'), Wx::Humidity) . ' '
		. nw3_arrow('humi', 15, 45, 2, 8);
	nw3_card('humi', 'humidity', 'Relative Humidity', 'wx10.php', 'Detailed humidity data',
		$nowHtml, '',
		array(
			array('Low', nw3_at(nw3_live_get($NOW, 'min', 'humi'), Wx::Humidity, nw3_live_get($NOW, 'timeMin', 'humi'))),
			array('High', nw3_at(nw3_live_get($NOW, 'max', 'humi'), Wx::Humidity, nw3_live_get($NOW, 'timeMax', 'humi'))),
			array('24hr avg', nw3_at(nw3_live_get($HR, 'mean', 'humi'), Wx::Humidity, null)),
			array('Yesterday', nw3_yest_range('humi', Wx::Humidity)),
			array('24hr trend', nw3_delta(nw3_live_get($HR, 'changeDay', 'humi'), Wx::Humidity)),
		));

	// Dew point
	$nowHtml = '<span id="var2">' . Wx::conv(Live::$dewp, Wx::Temperature) . '</span> '
		. nw3_trend(nw3_live_get($HR, 'changeHr', 'dewp'), Wx::AbsTemp) . ' '
		. nw3_arrow('dewp', 15, 30, 0.4, 0.9);
	nw3_card('humi', 'dewy', 'Dew Point', 'wx10.php?humtype=dew', 'Detailed dew point data',
		$nowHtml, '',
		array(
			array('Low', nw3_at(nw3_live_get($NOW, 'min', 'dewp'), Wx::Temperature, nw3_live_get($NOW, 'timeMin', 'dewp'))),
			array('High', nw3_at(nw3_live_get($NOW, 'max', 'dewp'), Wx::Temperature, nw3_live_get($NOW, 'timeMax', 'dewp'))),
			array('24hr avg', nw3_at(nw3_live_get($HR, 'mean', 'dewp'), Wx::Temperature, null)),
			array('Yesterday', nw3_yest_range('dewp', Wx::Temperature)),
			array('24hr trend', nw3_delta(nw3_live_get($HR, 'changeDay', 'dewp'), Wx::AbsTemp)),
		));

	// Pressure
	$nowHtml = '<span id="var3">' . Wx::conv(Live::$pres, Wx::Pressure) . '</span> '
		. nw3_trend(nw3_live_get($HR, 'changeHr', 'pres'), Wx::Pressure, false) . ' '
		. nw3_arrow('pres', 60, 120, 1, 2);
	nw3_card('pres', 'pressure2', 'Pressure', 'wx16.php', 'Detailed pressure data',
		$nowHtml, '',
		array(
			array('Low', nw3_at(nw3_live_get($NOW, 'min', 'pres'), Wx::Pressure, nw3_live_get($NOW, 'timeMin', 'pres'))),
			array('High', nw3_at(nw3_live_get($NOW, 'max', 'pres'), Wx::Pressure, nw3_live_get($NOW, 'timeMax', 'pres'))),
			array('24hr avg', nw3_at(nw3_live_get($HR, 'mean', 'pres'), Wx::Pressure, null)),
			array('Yesterday', nw3_yest_range('pres', Wx::Pressure)),
			array('24hr trend', nw3_delta(nw3_live_get($HR, 'changeDay', 'pres'), Wx::Pressure)),
		));

	// Air pollution (raw PM2.5 -> UK DAQI band + US AQI)
	list($daqiBand, $daqiName, $daqiClass) = Wx::daqi(Live::$pm25);
	$usAqi = Wx::usAqi(Live::$pm25);
	// Per-band class graduates the dot colour within each band group (1-10).
	$daqiBandClass = ($daqiBand > 0) ? ' daqi-b' . $daqiBand : '';
	// Clickable status: opens the DAQI scale popover (aq.js, delegated so it survives the live refresh).
	// Bands above Low put the (longer) band name on its own line under the index.
	$daqiBtn = function ($band, $name) use ($daqiClass, $daqiBandClass) {
		$wrap = ($name !== 'Low');
		$info = '<span class="daqi-info" aria-hidden="true">i</span>';
		$body = $wrap
			? 'DAQI ' . $band . $info . '<span class="daqi-name">' . $name . '</span>'
			: 'DAQI ' . $band . ' &middot; ' . $name . $info;
		return '<button type="button" id="var6" class="daqi-status daqi-btn ' . $daqiClass . $daqiBandClass
			. ($wrap ? ' daqi-wrap' : '') . '"'
			. ' data-pm="' . htmlspecialchars((string)Live::$pm25) . '" data-band="' . (int)$band . '"'
			. ' aria-haspopup="dialog" aria-expanded="false" aria-controls="daqi-pop"'
			. ' title="Show the DAQI scale"><span class="daqi-dot"></span>' . $body . '</button>';
	};
	// Top of the card always shows all three scales; the rows below follow the visitor's air-quality units.
	$pmDpa = (Page::aqUnits() === 'pm') ? -1 : 0;
	$pm25Now = (Live::$pm25 === null)
		? '<span id="var6" class="daqi-status daqi-unknown"><span class="daqi-dot"></span>No data</span>'
		: $daqiBtn($daqiBand, $daqiName);
	$pm25Sub = (Live::$pm25 === null) ? ''
		: 'AQI <b>' . $usAqi . '</b> &middot; PM2.5 <b>' . nw3_number_format((float)Live::$pm25, 0) . ' &micro;g/m&sup3;</b>';
	$pmHigh = nw3_at(nw3_live_get($NOW, 'max', 'pm25'), Wx::Pm25, nw3_live_get($NOW, 'timeMax', 'pm25'), $pmDpa);
	if (Page::aqUnits() !== 'pm') { // an index from a single max reading is only indicative
		$pmHigh = preg_replace('#</b>#', '<sup class="aq-ind" title="Indicative: converted from the max reading">*</sup></b>', $pmHigh, 1);
	}
	nw3_card('pm25', 'icon-airpollution.svg', 'Air pollution', '', 'Air pollution - UK DAQI band &amp; AQI (from PM2.5)',
		$pm25Now,
		$pm25Sub,
		array(
			array('High', $pmHigh),
			array('24hr avg', nw3_at(nw3_live_get($HR, 'mean', 'pm25'), Wx::Pm25, null, $pmDpa)),
			array('Yesterday avg', nw3_yest_val('mean', 'pm25', Wx::Pm25, $pmDpa)),
		));
}

/** Climate & extremes card (home page, below the 31-day trends; refreshed with the live cards). */
function nw3_render_clim_card() {
	ClimateCard::renderCard();
	// Notable rankings (precomputed by warm_rank_flags.php); nothing when none qualify.
	RankFlags::renderCard();
}
?>
