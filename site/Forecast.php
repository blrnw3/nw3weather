<?php
/**
 * Local forecast helpers for the homepage strip and wx5 page.
 * Data is cached by cron_main.php under V5_CACHE_ROOT (Yr.no location 2-2647553).
 */
class Forecast {

	private static $data = null;

	/** Load (and memoize) the cached forecast JSON. */
	public static function load() {
		if (self::$data !== null) { return self::$data; }
		$path = V5_CACHE_ROOT . 'forecast_v5.json';
		if (!is_file($path)) {
			self::$data = array();
			return self::$data;
		}
		$json = json_decode(file_get_contents($path), true);
		self::$data = is_array($json) ? $json : array();
		return self::$data;
	}

	/** First N daily summaries (homepage uses 2). */
	public static function days($limit = null) {
		$data = self::load();
		$days = isset($data['days']) && is_array($data['days']) ? $data['days'] : array();
		if ($limit !== null) { $days = array_slice($days, 0, (int)$limit); }
		return $days;
	}

	public static function hourly() {
		$data = self::load();
		return isset($data['hourly']) && is_array($data['hourly']) ? $data['hourly'] : array();
	}

	public static function periods() {
		$data = self::load();
		return isset($data['periods']) && is_array($data['periods']) ? $data['periods'] : array();
	}

	public static function updated() {
		$data = self::load();
		return isset($data['updated']) ? (int)$data['updated'] : 0;
	}

	/** Prefer Open-Meteo 10-day meteogram cache; fall back to Yr hourly. */
	private static function meteogramHours() {
		$path = V5_CACHE_ROOT . 'forecast_meteogram.json';
		if (is_file($path)) {
			$json = json_decode(file_get_contents($path), true);
			if (is_array($json) && !empty($json['hourly']) && is_array($json['hourly'])) {
				return array($json['hourly'], isset($json['updated']) ? (int)$json['updated'] : 0, 'open-meteo');
			}
		}
		return array(self::hourly(), self::updated(), 'yr');
	}

	/**
	 * Unit-converted series for the stacked 10-day meteogram.
	 * Open-Meteo supplies 10-day temp/dew/precip/POP/cloud/wind; Yr fallback has no POP.
	 */
	public static function meteogramSeries() {
		$tempU = Wx::getUnitsText(Wx::Temperature);
		$rainU = Wx::getUnitsText(Wx::Rain);
		$windU = Wx::getUnitsText(Wx::Wind);
		list($hours, $updated, $src) = self::meteogramHours();

		$temp = $dew = $precip = $pop = $cloud = $wind = array();
		foreach ($hours as $iv) {
			if (empty($iv['t'])) { continue; }
			$t = (int)$iv['t'];
			if (isset($iv['temp']) && $iv['temp'] !== null) {
				$temp[] = array($t, Wx::convNum($iv['temp'], Wx::Temperature, 1));
			}
			if (isset($iv['dew']) && $iv['dew'] !== null) {
				$dew[] = array($t, Wx::convNum($iv['dew'], Wx::Temperature, 1));
			}
			if (isset($iv['precip']) && $iv['precip'] !== null) {
				$precip[] = array($t, Wx::convNum($iv['precip'], Wx::Rain, 2));
			}
			if (isset($iv['pop']) && $iv['pop'] !== null) {
				$pop[] = array($t, (float)$iv['pop']);
			}
			if (isset($iv['cloud']) && $iv['cloud'] !== null) {
				$cloud[] = array($t, (float)$iv['cloud']);
			}
			if (isset($iv['wind']) && $iv['wind'] !== null) {
				$wind[] = array($t, Wx::convNum($iv['wind'], Wx::Wind, 1));
			}
		}
		return array(
			'updated' => $updated,
			'source' => $src,
			'units' => array('temp' => $tempU, 'precip' => $rainU, 'wind' => $windU, 'pop' => '%', 'cloud' => '%'),
			// Match site chart palette (wxcharts.js INTRA_TABS).
			'colors' => array(
				'temp' => '#d34f00',
				'dew' => '#0b8043',
				'precip' => '#2554c7',
				'pop' => 'rgba(37,84,199,0.28)',
				'cloud' => '#5a7a9a',
				'wind' => '#c01717',
			),
			'temp' => $temp,
			'dew' => $dew,
			'precip' => $precip,
			'pop' => $pop,
			'cloud' => $cloud,
			'wind' => $wind,
		);
	}

	/** Cached Met Office London & SE regional text (OGL / Crown copyright). */
	public static function discussion() {
		$path = V5_CACHE_ROOT . 'forecast_discussion.json';
		if (!is_file($path)) { return array(); }
		$json = json_decode(file_get_contents($path), true);
		return is_array($json) ? $json : array();
	}

	/** Cached Met Office surface-pressure analysis pointer. */
	public static function pressureChart() {
		$path = V5_CACHE_ROOT . 'forecast_pressure.json';
		if (!is_file($path)) { return array(); }
		$json = json_decode(file_get_contents($path), true);
		return is_array($json) ? $json : array();
	}

	/** Expert-style regional discussion (Met Office London & South East). */
	public static function renderDiscussion() {
		$d = self::discussion();
		$srcUrl = 'https://weather.metoffice.gov.uk/forecast/regional/se';
		echo '<div class="fc-discuss">';
		echo '<h2>London &amp; South East forecast discussion</h2>';
		echo '<p class="fc-discuss-intro">Met Office regional guidance for London area. Full text and maps on Met Office site.</p>';
		if (!empty($d['sections']) && is_array($d['sections'])) {
			if (!empty($d['headline'])) {
				echo '<p class="fc-discuss-headline">' . htmlspecialchars($d['headline']) . '</p>';
			}
			echo '<div class="fc-discuss-body">';
			foreach ($d['sections'] as $sec) {
				$title = isset($sec['title']) ? $sec['title'] : '';
				$text = isset($sec['text']) ? $sec['text'] : '';
				if ($title === '' && $text === '') { continue; }
				echo '<div class="fc-discuss-sec">';
				if ($title !== '') {
					echo '<h3>' . htmlspecialchars($title) . '</h3>';
				}
				echo '<p>' . htmlspecialchars($text) . '</p>';
				echo '</div>';
			}
			echo '</div>';
			$when = '';
			if (!empty($d['issued'])) {
				$when = 'Issued ' . htmlspecialchars($d['issued']);
			} elseif (!empty($d['updated'])) {
				$when = 'Cached ' . date('D j M H:i', (int)$d['updated']) . ' local';
			}
			echo '<p class="fc-credit">Content supplied by the Met Office'
				. ($when !== '' ? (' · ' . $when) : '')
				. ' · <a href="' . htmlspecialchars($srcUrl) . '" rel="noopener" target="_blank">'
				. 'Full London &amp; South East forecast</a>'
				. ' · <a href="https://www.nationalarchives.gov.uk/doc/open-government-licence/version/3/" rel="noopener" target="_blank">OGL v3</a>'
				. '</p>';
		} else {
			echo '<p class="fc-discuss-fallback">Regional discussion text is temporarily unavailable. '
				. 'Read the latest on the '
				. '<a href="' . htmlspecialchars($srcUrl) . '" rel="noopener" target="_blank">Met Office London &amp; South East forecast</a>.</p>';
		}
		echo '</div>';
	}

	/** Format a Met Office ISO timestamp as e.g. "12:00 UTC Tue 10 Nov". */
	private static function fmtUtc($iso) {
		if (!$iso) { return ''; }
		$ts = strtotime($iso);
		return $ts ? gmdate('H:i \U\T\C D j M', $ts) : (string)$iso;
	}

	/** Current UK surface pressure: analysis plus T+24 and T+48 forecast charts. */
	public static function renderPressureChart() {
		$p = self::pressureChart();
		$charts = (isset($p['charts']) && is_array($p['charts'])) ? $p['charts'] : array();
		if (!$charts && (!empty($p['local']) || !empty($p['uri']) || is_file(V5_GENERATED_ROOT . 'surface_pressure.gif'))) {
			$charts[] = array(
				'lead' => 0,
				'label' => 'Analysis',
				'file' => 'surface_pressure.gif',
				'valid' => isset($p['valid']) ? $p['valid'] : '',
				'uri' => isset($p['uri']) ? $p['uri'] : '',
				'local' => !empty($p['local']) || is_file(V5_GENERATED_ROOT . 'surface_pressure.gif'),
			);
		}
		$page = 'https://weather.metoffice.gov.uk/maps-and-charts/surface-pressure';
		$bust = !empty($p['issued']) ? ('?t=' . rawurlencode(preg_replace('/\W+/', '', $p['issued']))) : ('?t=' . (int)(isset($p['updated']) ? $p['updated'] : time()));

		echo '<div class="fc-pressure">';
		echo '<h2>UK surface pressure</h2>';
		if (!$charts) {
			echo '<p>Surface pressure charts temporarily unavailable. See the '
				. '<a href="' . $page . '" rel="noopener" target="_blank">Met Office surface pressure charts</a>.</p>';
			echo '</div>';
			return;
		}

		echo '<div class="fc-pressure-grid">';
		foreach ($charts as $c) {
			$lead = isset($c['lead']) ? (int)$c['lead'] : 0;
			$label = isset($c['label']) ? $c['label'] : ($lead === 0 ? 'Analysis' : ('T+' . $lead . ' forecast'));
			$file = isset($c['file']) ? $c['file'] : '';
			$src = '';
			if (!empty($c['local']) && $file !== '' && is_file(V5_GENERATED_ROOT . $file)) {
				$src = '/' . $file;
			} elseif ($file !== '' && is_file(V5_GENERATED_ROOT . $file)) {
				$src = '/' . $file;
			} elseif (!empty($c['uri'])) {
				$src = $c['uri'];
			}
			if ($src === '') { continue; }
			$valid = self::fmtUtc(isset($c['valid']) ? $c['valid'] : '');
			$alt = 'Met Office UK surface pressure ' . $label . ($valid !== '' ? (' valid ' . $valid) : '');
			$cls = 'fc-pressure-item' . ($lead === 0 ? ' fc-pressure-item-analysis' : ' fc-pressure-item-forecast');
			echo '<figure class="' . $cls . '">';
			echo '<a href="' . htmlspecialchars($src . $bust) . '" rel="noopener" target="_blank" title="Open full-size ' . htmlspecialchars($label) . '">';
			echo '<img class="fc-pressure-img" src="' . htmlspecialchars($src . $bust) . '" alt="' . htmlspecialchars($alt) . '" /></a>';
			echo '<figcaption class="fc-pressure-cap"><b>' . htmlspecialchars($label) . '</b>';
			if ($valid !== '') { echo ' · valid ' . htmlspecialchars($valid); }
			echo '</figcaption></figure>';
		}
		echo '</div>';
		$issued = self::fmtUtc(isset($p['issued']) ? $p['issued'] : '');
		echo '<p class="fc-credit">Met Office surface pressure charts'
			. ($issued !== '' ? (' · issued ' . htmlspecialchars($issued)) : '')
			. ' · <a href="' . $page . '" rel="noopener" target="_blank">all charts</a></p>';
		echo '</div>';
	}

	/** Ben's favourite external forecast pages for Hampstead / NW3. */
	public static function renderFavouriteLinks() {
		$links = array(
			array(
				'label' => 'Netweather 7-day forecast — Hampstead',
				'url' => 'https://www.netweather.tv/weather-forecasts/uk/7-day/13501~Hampstead',
				'note' => 'Local model blend',
			),
			array(
				'label' => 'Met Office forecast — Hampstead',
				'url' => 'https://weather.metoffice.gov.uk/forecast/gcpv7fnqu',
				'note' => 'Official forecast for Hampstead (Greater London)',
			),
			array(
				'label' => 'Met Office — London &amp; South East regional',
				'url' => 'https://weather.metoffice.gov.uk/forecast/regional/se',
				'note' => 'Written regional discussion used above',
			),
		);
		echo '<div class="fc-favs">';
		echo '<h2>Favourite forecast pages</h2>';
		echo '<ul class="fc-fav-list">';
		foreach ($links as $L) {
			echo '<li><a href="' . htmlspecialchars($L['url']) . '" rel="noopener" target="_blank">'
				. $L['label'] . '</a>';
			if (!empty($L['note'])) {
				echo ' <span class="fc-fav-note">— ' . $L['note'] . '</span>';
			}
			echo '</li>';
		}
		echo '</ul></div>';
	}

	/** Daily forecast table (replaces the Yr table iframe). */
	public static function renderDailyTable() {
		$days = self::days();
		if (!count($days)) {
			echo '<p>Forecast data is temporarily unavailable.</p>';
			return;
		}

		echo '<div class="fc-table-wrap">';
		echo '<table class="table1 fc-table" width="100%" cellpadding="3" cellspacing="0">';
		echo '<tr class="table-head"><td class="td12" style="padding:0.5em" colspan="6">'
			. 'Daily forecast for Hampstead (NW3)</td></tr>';
		echo '<tr class="table-top">';
		echo '<td class="td4">Day</td><td class="td4" colspan="2">Conditions</td>'
			. '<td class="td4">High / Low</td><td class="td4">Rain</td><td class="td4">Wind</td>';
		echo '</tr>';

		foreach ($days as $i => $day) {
			HTML::tr(HTML::colcol($i));
			$icon = isset($day['icon']) ? $day['icon'] : 'cloudy';
			$desc = isset($day['desc']) ? $day['desc'] : '';
			$label = isset($day['label']) ? $day['label'] : '';
			$img = Site::IMG_ROOT . htmlspecialchars($icon) . '_lg.png';

			HTML::td(htmlspecialchars($label), 'td4');
			echo '<td class="td4 fc-icon"><img src="' . $img . '" width="40" height="40" alt="'
				. htmlspecialchars($desc) . '" title="' . htmlspecialchars($desc) . '" /></td>';
			HTML::td(htmlspecialchars($desc), 'td4');
			HTML::td(
				'<b>' . Wx::conv($day['tmax'], Wx::Temperature, true, false, -1) . '</b> / '
				. Wx::conv($day['tmin'], Wx::Temperature, true, false, -1),
				'td14C'
			);
			$precip = isset($day['precip']) ? $day['precip'] : null;
			HTML::td(
				($precip !== null && (float)$precip > 0) ? Wx::conv($precip, Wx::Rain, true) : '—',
				'td12C'
			);
			$wMin = isset($day['windMin']) ? $day['windMin'] : null;
			$wMax = isset($day['windMax']) ? $day['windMax'] : null;
			if ($wMin !== null && $wMax !== null) {
				$windTxt = Wx::conv($wMin, Wx::Wind, false, false, -1) . '–'
					. Wx::conv($wMax, Wx::Wind, true, false, -1);
			} elseif ($wMax !== null) {
				$windTxt = Wx::conv($wMax, Wx::Wind, true, false, -1);
			} else {
				$windTxt = '—';
			}
			HTML::td($windTxt, 'td13C');
			HTML::tr_end();
		}
		echo '</table></div>';

		$upd = self::updated();
		echo '<p class="fc-credit">Forecast data from '
			. '<a href="https://www.yr.no/en/forecast/daily-table/2-2647553/" rel="noopener">Yr.no / MET Norway</a>'
			. ($upd ? (' · updated ' . date('H:i', $upd)) : '')
			. '.</p>';
	}

	/** Near-term 6-hour period table (next ~2 days of longIntervals). */
	public static function renderPeriodTable($limit = 8) {
		$periods = array_slice(self::periods(), 0, (int)$limit);
		if (!count($periods)) { return; }

		echo '<div class="fc-table-wrap">';
		echo '<table class="table1 fc-table" width="100%" cellpadding="3" cellspacing="0">';
		echo '<tr class="table-head"><td class="td12" style="padding:0.5em" colspan="5">'
			. 'Next periods (6-hourly)</td></tr>';
		echo '<tr class="table-top">';
		echo '<td class="td4">Period</td><td class="td4" colspan="2">Conditions</td>'
			. '<td class="td4">Temp</td><td class="td4">Rain</td>';
		echo '</tr>';

		foreach ($periods as $i => $p) {
			HTML::tr(HTML::colcol($i));
			$icon = isset($p['icon']) ? $p['icon'] : 'cloudy';
			$desc = isset($p['desc']) ? $p['desc'] : '';
			$img = Site::IMG_ROOT . htmlspecialchars($icon) . '_lg.png';
			HTML::td(htmlspecialchars(isset($p['label']) ? $p['label'] : ''), 'td4');
			echo '<td class="td4 fc-icon"><img src="' . $img . '" width="36" height="36" alt="'
				. htmlspecialchars($desc) . '" title="' . htmlspecialchars($desc) . '" /></td>';
			HTML::td(htmlspecialchars($desc), 'td4');
			$tmax = isset($p['tmax']) ? $p['tmax'] : $p['temp'];
			$tmin = isset($p['tmin']) ? $p['tmin'] : $p['temp'];
			if ($tmax !== null && $tmin !== null && (float)$tmax !== (float)$tmin) {
				$tempTxt = Wx::conv($tmax, Wx::Temperature, true, false, -1) . ' / '
					. Wx::conv($tmin, Wx::Temperature, true, false, -1);
			} else {
				$tempTxt = Wx::conv($p['temp'], Wx::Temperature, true, false, -1);
			}
			HTML::td($tempTxt, 'td14C');
			$precip = isset($p['precip']) ? $p['precip'] : null;
			HTML::td(
				($precip !== null && (float)$precip > 0) ? Wx::conv($precip, Wx::Rain, true) : '—',
				'td12C'
			);
			HTML::tr_end();
		}
		echo '</table></div>';
	}
}
