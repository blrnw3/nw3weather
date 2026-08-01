<?php
/**
 * Local forecast helpers for the homepage strip and wx5 page.
 * Data is cached by cron_main.php into ROOT/forecast_v5.json (Yr.no location 2-2647553).
 */
class Forecast {

	private static $data = null;

	/** Load (and memoize) the cached forecast JSON. */
	public static function load() {
		if (self::$data !== null) { return self::$data; }
		$path = ROOT . 'forecast_v5.json';
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

	/** Unit-converted series payload for the meteogram chart. */
	public static function meteogramSeries() {
		$tempU = Wx::getUnitsText(Wx::Temperature);
		$rainU = Wx::getUnitsText(Wx::Rain);
		$windU = Wx::getUnitsText(Wx::Wind);

		$temp = array();
		$precip = array();
		$wind = array();
		foreach (self::hourly() as $iv) {
			if (empty($iv['t'])) { continue; }
			$t = (int)$iv['t'];
			$temp[] = array($t, Wx::convNum($iv['temp'], Wx::Temperature, 1));
			$precip[] = array($t, Wx::convNum($iv['precip'], Wx::Rain, 2));
			$wind[] = array($t, Wx::convNum($iv['wind'], Wx::Wind, 1));
		}
		return array(
			'updated' => self::updated(),
			'units' => array('temp' => $tempU, 'precip' => $rainU, 'wind' => $windU),
			'temp' => $temp,
			'precip' => $precip,
			'wind' => $wind,
		);
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
