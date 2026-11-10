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

		$temp = $dew = $precip = $pop = $cloud = $wind = $gust = array();
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
			if (isset($iv['gust']) && $iv['gust'] !== null) {
				$gust[] = array($t, Wx::convNum($iv['gust'], Wx::Wind, 1));
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
				'gust' => '#c23b86',
			),
			'temp' => $temp,
			'dew' => $dew,
			'precip' => $precip,
			'pop' => $pop,
			'cloud' => $cloud,
			'wind' => $wind,
			'gust' => $gust,
		);
	}

	/**
	 * Parse the Met Office London & South East regional forecast page.
	 * The headline is taken only from the regional "Headline" section inside
	 * #fiveDayText — never from #breaking-news-headline (a national article title).
	 * Returns headline, issued, and sections, or null if the regional block is missing.
	 */
	public static function parseRegionalHtml($raw) {
		if (!is_string($raw) || $raw === '') { return null; }
		$sections = array();
		$block = '';
		if (preg_match('/id="fiveDayText"[^>]*>(.*?)<\/div>\s*<\/div>\s*<\/section>/s', $raw, $mBlock)) {
			$block = $mBlock[1];
			if (preg_match_all('/<div class="forecast-text-section">\s*<h4>(.*?)<\/h4>\s*<p>(.*?)<\/p>\s*<\/div>/s', $block, $mm, PREG_SET_ORDER)) {
				foreach ($mm as $row) {
					$title = trim(html_entity_decode(strip_tags($row[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
					$text = trim(html_entity_decode(strip_tags($row[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
					$text = preg_replace('/\s+/u', ' ', $text);
					if ($title === '' && $text === '') { continue; }
					$sections[] = array('title' => $title, 'text' => $text);
				}
			}
		}
		$headline = '';
		foreach ($sections as $sec) {
			$label = rtrim($sec['title'], ':');
			if (strcasecmp($label, 'Headline') === 0) {
				$headline = $sec['text'];
				break;
			}
		}
		// Fallback if the section wrapper markup changed but Headline is still in the regional block.
		if ($headline === '' && $block !== '' && preg_match('/<h4>[^<]*Headline:?[^<]*<\/h4>\s*<p>(.*?)<\/p>/is', $block, $mh)) {
			$headline = trim(html_entity_decode(strip_tags($mh[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
			$headline = preg_replace('/\s+/u', ' ', $headline);
		}
		$issued = '';
		if (preg_match('/id="fiveDayText"[\s\S]{0,2500}?<time datetime="([^"]+)"/', $raw, $mt)) {
			$issued = $mt[1];
		}
		if ($headline === '' && !$sections) {
			return null;
		}
		return array(
			'headline' => $headline,
			'issued' => $issued,
			'sections' => $sections,
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

	/** OGL credit line on the wx5 discussion (full attribution). */
	private static function discussionCreditHtml($d, $srcUrl) {
		$when = '';
		if (!empty($d['issued'])) {
			$fmt = self::fmtUk($d['issued']);
			$when = 'Issued ' . htmlspecialchars($fmt !== '' ? $fmt : $d['issued']);
		} elseif (!empty($d['updated'])) {
			$fmt = self::fmtUk((int)$d['updated']);
			$when = 'Cached ' . htmlspecialchars($fmt !== '' ? $fmt : date('H:i D j M', (int)$d['updated']));
		}
		return '<p class="fc-credit">Content supplied by the Met Office'
			. ($when !== '' ? (' · ' . $when) : '')
			. ' · <a href="' . htmlspecialchars($srcUrl) . '" rel="noopener" target="_blank">'
			. 'Full London &amp; South East forecast</a>'
			. ' · <a href="https://www.nationalarchives.gov.uk/doc/open-government-licence/version/3/" rel="noopener" target="_blank">OGL v3</a>'
			. '</p>';
	}

	/** Met Office regional headline for the homepage forecast card. */
	public static function renderHomeHeadline() {
		$d = self::discussion();
		if (empty($d['headline'])) { return; }
		echo '<p class="fcast-headline">' . htmlspecialchars($d['headline']) . '</p>';
	}

	/** Short Met Office credit, same placement/styling as the card's Yr.no credit. */
	public static function renderHomeSrc() {
		$d = self::discussion();
		if (empty($d['headline'])) { return; }
		$srcUrl = 'https://weather.metoffice.gov.uk/forecast/regional/se';
		echo '<span class="fcast-src"> · data <a href="' . htmlspecialchars($srcUrl) . '" rel="noopener">Met Office</a></span>';
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
				// Headline is already shown unlabelled above; skip the stored "Headline:" section.
				if (strcasecmp(rtrim($title, ':'), 'Headline') === 0) { continue; }
				echo '<div class="fc-discuss-sec">';
				if ($title !== '') {
					echo '<h3>' . htmlspecialchars($title) . '</h3>';
				}
				echo '<p>' . htmlspecialchars($text) . '</p>';
				echo '</div>';
			}
			echo '</div>';
			echo self::discussionCreditHtml($d, $srcUrl);
		} else {
			echo '<p class="fc-discuss-fallback">Regional discussion text is temporarily unavailable. '
				. 'Read the latest on the '
				. '<a href="' . htmlspecialchars($srcUrl) . '" rel="noopener" target="_blank">Met Office London &amp; South East forecast</a>.</p>';
		}
		echo '</div>';
	}

	/** Format a timestamp (ISO string or unix time) as UK local e.g. "16:00 Mon 9 Nov". */
	private static function fmtUk($time) {
		if ($time === null || $time === '') { return ''; }
		try {
			if (is_int($time) || (is_string($time) && ctype_digit($time))) {
				$dt = new DateTime('@' . (string)((int)$time));
			} else {
				$dt = new DateTime((string)$time);
			}
			$dt->setTimezone(new DateTimeZone('Europe/London'));
			return $dt->format('H:i D j M');
		} catch (Exception $e) {
			return is_string($time) ? (string)$time : '';
		}
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
		echo '<h2 id="fc-pressure-heading">UK surface pressure</h2>';
		if (!$charts) {
			echo '<p>Surface pressure charts temporarily unavailable. See the '
				. '<a href="' . $page . '" rel="noopener" target="_blank">Met Office surface pressure charts</a>.</p>';
			echo '</div>';
			return;
		}

		$items = array();
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
			$valid = self::fmtUk(isset($c['valid']) ? $c['valid'] : '');
			$cap = '<b>' . htmlspecialchars($label) . '</b>';
			if ($valid !== '') { $cap .= ' · valid ' . htmlspecialchars($valid); }
			$items[] = array(
				'lead' => $lead,
				'label' => $label,
				'src' => $src . $bust,
				'cap' => $cap,
				'alt' => 'Met Office UK surface pressure ' . $label . ($valid !== '' ? (' valid ' . $valid) : ''),
			);
		}
		if (!$items) {
			echo '<p>Surface pressure charts temporarily unavailable. See the '
				. '<a href="' . $page . '" rel="noopener" target="_blank">Met Office surface pressure charts</a>.</p>';
			echo '</div>';
			return;
		}

		echo '<div class="fc-pressure-tabs" role="tablist" aria-labelledby="fc-pressure-heading">';
		foreach ($items as $i => $it) {
			$sel = ($i === 0) ? 'true' : 'false';
			$tabIndex = ($i === 0) ? '0' : '-1';
			$active = ($i === 0) ? ' active' : '';
			echo '<button type="button" class="fc-pressure-tab' . $active . '" role="tab"'
				. ' id="fc-p-tab-' . $i . '" aria-controls="fc-p-panel-' . $i . '"'
				. ' aria-selected="' . $sel . '" tabindex="' . $tabIndex . '" data-fc-p-index="' . $i . '">'
				. $it['cap'] . '</button>';
		}
		echo '</div>';
		echo '<div class="fc-pressure-scroller" tabindex="0">';
		echo '<div class="fc-pressure-track">';
		foreach ($items as $i => $it) {
			$lead = $it['lead'];
			$cls = 'fc-pressure-item' . ($lead === 0 ? ' fc-pressure-item-analysis' : ' fc-pressure-item-forecast');
			echo '<div class="' . $cls . '" role="tabpanel" id="fc-p-panel-' . $i . '"'
				. ' aria-labelledby="fc-p-tab-' . $i . '" data-fc-p-index="' . $i . '">';
			echo '<figure>';
			echo '<a href="' . htmlspecialchars($it['src']) . '" rel="noopener" target="_blank" title="Open full-size ' . htmlspecialchars($it['label']) . '">';
			echo '<img class="fc-pressure-img" src="' . htmlspecialchars($it['src']) . '" alt="' . htmlspecialchars($it['alt']) . '" /></a>';
			echo '<figcaption class="fc-pressure-cap">' . $it['cap'] . '</figcaption></figure>';
			echo '</div>';
		}
		echo '</div></div>';
		echo '<script>(function(){var r=document.currentScript&&document.currentScript.parentElement;if(!r)return;'
			. 'var tabs=[].slice.call(r.querySelectorAll("[role=tab]")),panels=[].slice.call(r.querySelectorAll("[role=tabpanel]")),'
			. 'sc=r.querySelector(".fc-pressure-scroller");if(!tabs.length||!sc)return;'
			. 'function go(i){i=Math.max(0,Math.min(i,tabs.length-1));'
			. 'tabs.forEach(function(t,n){var on=n===i;t.setAttribute("aria-selected",on?"true":"false");t.tabIndex=on?0:-1;t.classList.toggle("active",on);});'
			. 'var p=panels[i];if(p){var left=p.getBoundingClientRect().left-sc.getBoundingClientRect().left+sc.scrollLeft;sc.scrollTo({left:left,behavior:"auto"});}}'
			. 'function fromScroll(){var x=sc.scrollLeft,best=0,bd=1e9;panels.forEach(function(p,n){var d=Math.abs(p.offsetLeft-x);if(d<bd){bd=d;best=n;}});go(best);}'
			. 'tabs.forEach(function(t,i){t.addEventListener("click",function(){go(i);t.focus();});'
			. 't.addEventListener("keydown",function(e){var n=i;if(e.key==="ArrowRight"||e.key==="ArrowDown")n=i+1;else if(e.key==="ArrowLeft"||e.key==="ArrowUp")n=i-1;else if(e.key==="Home")n=0;else if(e.key==="End")n=tabs.length-1;else return;e.preventDefault();go(n);tabs[n].focus();});});'
			. 'var tmo;sc.addEventListener("scroll",function(){clearTimeout(tmo);tmo=setTimeout(fromScroll,60);},{passive:true});'
			. 'window.NW3=window.NW3||{};window.NW3.selectPressureTab=function(i){go(i);};'
			. 'go(0);})();</script>';
		$issued = self::fmtUk(isset($p['issued']) ? $p['issued'] : '');
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
