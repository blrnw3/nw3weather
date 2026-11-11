<?php
/**
 * Homepage "Weather nearby" card: current temperature, today's lo–hi and today's
 * rain at this station and nearby official London sites.
 *
 * Observations are fetched by cron (Nearby::refresh) into cache/v5/nearby.json.
 * Page views only read that file — they never call third-party APIs.
 */

class Nearby {
	const CACHE_FILE = 'nearby.json';
	const USER_AGENT = 'nw3weather.co.uk/v5 (https://nw3weather.co.uk; bmr@nw3weather.co.uk)';
	const TIMEOUT = 8;
	const STALE_MAX = 10800; // 3 hours: keep last-good after a failed fetch
	const HILO_COVER_HOUR = 6; // local hour; range omitted until a sample exists at or before this

	const IEM_CURRENT = 'https://mesonet.agron.iastate.edu/json/current.py';
	const IEM_NETWORK = 'GB__ASOS';
	const IEM_SITE = 'https://mesonet.agron.iastate.edu/sites/site.php';
	const AW_METAR = 'https://aviationweather.gov/api/data/metar';
	const OGIMET_SYNOP = 'https://www.ogimet.com/cgi-bin/getsynop';
	const OGIMET_PAGE = 'https://www.ogimet.com/cgi-bin/gsynres';
	const WOW_TABLE = 'https://wow.metoffice.gov.uk/observations/details/tableviewdata';
	const WOW_PAGE = 'https://wow.metoffice.gov.uk/observations/details/';

	/** Official / airport stations (this station is added at render time from live data). */
	public static function catalog() {
		return array(
			'stjames' => array(
				'id' => 'stjames',
				'name' => "St James's Park",
				'lat' => 51.5045,
				'lon' => -0.1294,
				'elev_m' => 5,
				'elev_source' => 'published',
				'url' => self::OGIMET_PAGE . '?lang=en&ind=03770',
				'sources' => array('synop' => '03770', 'wow' => null),
			),
			'kew' => array(
				'id' => 'kew',
				'name' => 'Kew Gardens',
				'lat' => 51.48186,
				'lon' => -0.29435,
				'elev_m' => 6,
				'elev_source' => 'published',
				'url' => self::WOW_PAGE . '?site_id=961266001',
				'sources' => array('wow' => '961266001', 'synop' => null),
			),
			'heathrow' => array(
				'id' => 'heathrow',
				'name' => 'Heathrow',
				'lat' => 51.477,
				'lon' => -0.461,
				'elev_m' => 26,
				'elev_source' => 'published',
				'url' => self::IEM_SITE . '?network=GB__ASOS&station=EGLL',
				'sources' => array('iem' => 'EGLL', 'synop' => '03772'),
			),
			'northolt' => array(
				'id' => 'northolt',
				'name' => 'Northolt',
				'lat' => 51.549,
				'lon' => -0.417,
				'elev_m' => 33,
				'elev_source' => 'published',
				'url' => self::IEM_SITE . '?network=GB__ASOS&station=EGWU',
				'sources' => array('iem' => 'EGWU', 'synop' => '03672'),
			),
			'city' => array(
				'id' => 'city',
				'name' => 'London City',
				'lat' => 51.505,
				'lon' => 0.055,
				'elev_m' => 10,
				'elev_source' => 'published',
				'url' => self::IEM_SITE . '?network=GB__ASOS&station=EGLC',
				'sources' => array('iem' => 'EGLC', 'synop' => null),
			),
		);
	}

	public static function cachePath() {
		return V5_CACHE_ROOT . self::CACHE_FILE;
	}

	public static function haversineKm($lat, $lon) {
		$r = 6371.0;
		$p1 = deg2rad(Site::LATITUDE);
		$l1 = deg2rad(Site::LONGITUDE);
		$p2 = deg2rad($lat);
		$l2 = deg2rad($lon);
		$a = sin(($p2 - $p1) / 2) * sin(($p2 - $p1) / 2)
			+ cos($p1) * cos($p2) * sin(($l2 - $l1) / 2) * sin(($l2 - $l1) / 2);
		return round(2 * $r * asin(sqrt($a)), 1);
	}

	public static function httpGet($url, $accept = null) {
		// Prefer cURL: some PHP builds (including local php83) fail HTTPS
		// file_get_contents even when allow_url_fopen is on. Production cron
		// already uses cURL for some fetches (see getXml in cron_main.php).
		if (function_exists('curl_init')) {
			$headers = array('User-Agent: ' . self::USER_AGENT);
			if ($accept) {
				$headers[] = 'Accept: ' . $accept;
			}
			$ch = curl_init($url);
			curl_setopt_array($ch, array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => self::TIMEOUT,
				CURLOPT_CONNECTTIMEOUT => self::TIMEOUT,
				CURLOPT_HTTPHEADER => $headers,
				CURLOPT_FOLLOWLOCATION => true,
			));
			$raw = curl_exec($ch);
			$code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			if ($raw !== false && $raw !== '' && $code >= 200 && $code < 300) {
				return $raw;
			}
			return null;
		}
		$headers = "User-Agent: " . self::USER_AGENT . "\r\n";
		if ($accept) {
			$headers .= "Accept: $accept\r\n";
		}
		$ctx = stream_context_create(array(
			'http' => array(
				'header' => $headers,
				'timeout' => self::TIMEOUT,
				'ignore_errors' => true,
			),
			'https' => array(
				'header' => $headers,
				'timeout' => self::TIMEOUT,
				'ignore_errors' => true,
			),
		));
		$raw = @file_get_contents($url, false, $ctx);
		if ($raw === false || $raw === '') {
			return null;
		}
		return $raw;
	}

	public static function httpJson($url) {
		$raw = self::httpGet($url, 'application/json');
		if ($raw === null) {
			return null;
		}
		$data = json_decode($raw, true);
		return is_array($data) ? $data : null;
	}

	private static function fToC($f) {
		if ($f === null || $f === '') {
			return null;
		}
		return round((((float)$f) - 32.0) * 5.0 / 9.0, 1);
	}

	private static function inToMm($inches) {
		if ($inches === null || $inches === '') {
			return null;
		}
		return round(((float)$inches) * 25.4, 1);
	}

	private static function num($val) {
		if ($val === null || $val === '' || $val === false) {
			return null;
		}
		if (is_numeric($val)) {
			return (float)$val;
		}
		return null;
	}

	/** Parse an observation time to a UTC unix timestamp, or null. */
	private static function ts($isoOrUnix) {
		if ($isoOrUnix === null || $isoOrUnix === '') {
			return null;
		}
		if (is_numeric($isoOrUnix)) {
			$n = (float)$isoOrUnix;
			if ($n > 1e12) {
				$n = $n / 1000.0;
			}
			return ((int)$n > 0) ? (int)$n : null;
		}
		$t = strtotime($isoOrUnix);
		return ($t !== false) ? $t : null;
	}

	private static function isoZ($ts) {
		if (!$ts) {
			return null;
		}
		return gmdate('Y-m-d\TH:i:s\Z', $ts);
	}

	/**
	 * Empty / unavailable station record (catalog metadata only).
	 */
	public static function baseRecord($st) {
		return array(
			'id' => $st['id'],
			'name' => $st['name'],
			'url' => $st['url'],
			'lat' => $st['lat'],
			'lon' => $st['lon'],
			'elev_m' => isset($st['elev_m']) ? $st['elev_m'] : null,
			'elev_source' => isset($st['elev_source']) ? $st['elev_source'] : null,
			'dist_km' => self::haversineKm($st['lat'], $st['lon']),
			'temp_c' => null,
			'temp_min_c' => null,
			'temp_max_c' => null,
			'rain_today_mm' => null,
			'observed_at' => null,
			'available' => false,
			'fetched_at' => null,
			'source' => null,
		);
	}

	public static function parseIemCurrent($data) {
		if (!is_array($data) || empty($data['last_ob']) || !is_array($data['last_ob'])) {
			return null;
		}
		$ob = $data['last_ob'];
		$temp = self::fToC(isset($ob['airtemp[F]']) ? $ob['airtemp[F]'] : null);
		$min = self::fToC(isset($ob['min_dayairtemp[F]']) ? $ob['min_dayairtemp[F]'] : null);
		$max = self::fToC(isset($ob['max_dayairtemp[F]']) ? $ob['max_dayairtemp[F]'] : null);
		$rain = self::inToMm(isset($ob['precip_today[in]']) ? $ob['precip_today[in]'] : null);
		$ts = self::ts(isset($ob['utc_valid']) ? $ob['utc_valid'] : null);
		if ($temp === null && $rain === null && $min === null && $max === null) {
			return null;
		}
		return array(
			'temp_c' => $temp,
			'temp_min_c' => $min,
			'temp_max_c' => $max,
			'rain_today_mm' => $rain,
			'observed_at' => self::isoZ($ts),
			'source' => 'iem',
		);
	}

	public static function parseAviation($obs) {
		if (!is_array($obs)) {
			return null;
		}
		$temp = self::num(isset($obs['temp']) ? $obs['temp'] : null);
		$ts = self::ts(isset($obs['obsTime']) ? $obs['obsTime'] : (isset($obs['reportTime']) ? $obs['reportTime'] : null));
		if ($temp === null) {
			return null;
		}
		return array(
			'temp_c' => round($temp, 1),
			'temp_min_c' => null,
			'temp_max_c' => null,
			'rain_today_mm' => null,
			'observed_at' => self::isoZ($ts),
			'source' => 'aviationweather',
		);
	}

	/**
	 * Decode a SYNOP dry-bulb from section 1 group 1sTTT (°C).
	 */
	public static function synopTempC($raw) {
		if (!is_string($raw) || $raw === '') {
			return null;
		}
		// Stop at section 3 (333) so 1sTTT max-temp groups there are not used as current temp.
		$sec1 = $raw;
		$pos = strpos($raw, ' 333');
		if ($pos !== false) {
			$sec1 = substr($raw, 0, $pos);
		}
		if (!preg_match('/\b1([01])(\d{3})\b/', $sec1, $m)) {
			return null;
		}
		$val = ((int)$m[2]) / 10.0;
		if ($m[1] === '1') {
			$val = -$val;
		}
		return $val;
	}

	/**
	 * Parse an Ogimet getsynop CSV blob for one WMO block into a list of
	 * array(ts, temp_c, raw) covering the requested period.
	 */
	public static function parseOgimetSynop($text, $block) {
		$out = array();
		if (!is_string($text) || $text === '') {
			return $out;
		}
		foreach (preg_split('/\r\n|\r|\n/', $text) as $line) {
			$line = trim($line);
			if ($line === '' || $line[0] === '<') {
				continue;
			}
			$parts = explode(',', $line, 7);
			if (count($parts) < 7) {
				continue;
			}
			if (trim($parts[0]) !== (string)$block) {
				continue;
			}
			$y = (int)$parts[1];
			$mo = (int)$parts[2];
			$d = (int)$parts[3];
			$h = (int)$parts[4];
			$mi = (int)$parts[5];
			$raw = $parts[6];
			if ($y < 2000 || $mo < 1) {
				continue;
			}
			$ts = gmmktime($h, $mi, 0, $mo, $d, $y);
			if ($ts === false) {
				continue;
			}
			$temp = self::synopTempC($raw);
			$out[] = array('ts' => $ts, 'temp_c' => $temp, 'raw' => $raw);
		}
		return $out;
	}

	/**
	 * Build an observation from today's (Europe/London) SYNOP samples:
	 * latest temperature, lo–hi from the day's temps once a sample exists
	 * at or before 06:00 local, rain left unused (SYNOP 6-groups are period
	 * totals, not a reliable calendar-day accumulator).
	 */
	public static function summariseSynop($samples, $nowTs = null) {
		if (!$samples) {
			return null;
		}
		$tz = new DateTimeZone('Europe/London');
		$now = new DateTime('@' . ($nowTs ? $nowTs : time()));
		$now->setTimezone($tz);
		$dayStart = clone $now;
		$dayStart->setTime(0, 0, 0);
		$coverEnd = clone $dayStart;
		$coverEnd->setTime(self::HILO_COVER_HOUR, 0, 0);
		$coverTs = $coverEnd->getTimestamp();
		$startTs = $dayStart->getTimestamp();

		$today = array();
		foreach ($samples as $s) {
			if ($s['ts'] >= $startTs && $s['ts'] <= $now->getTimestamp() + 600) {
				$today[] = $s;
			}
		}
		if (!$today) {
			return null;
		}
		$latest = $today[0];
		foreach ($today as $s) {
			if ($s['ts'] >= $latest['ts']) {
				$latest = $s;
			}
		}
		$temps = array();
		$hasCover = false;
		foreach ($today as $s) {
			if ($s['temp_c'] === null) {
				continue;
			}
			$temps[] = $s['temp_c'];
			if ($s['ts'] <= $coverTs) {
				$hasCover = true;
			}
		}
		$min = $max = null;
		if ($hasCover && $temps) {
			$min = min($temps);
			$max = max($temps);
		}
		if ($latest['temp_c'] === null && $min === null) {
			return null;
		}
		return array(
			'temp_c' => $latest['temp_c'],
			'temp_min_c' => $min,
			'temp_max_c' => $max,
			'rain_today_mm' => null,
			'observed_at' => self::isoZ($latest['ts']),
			'source' => 'ogimet-synop',
		);
	}

	public static function parseWowTable($data) {
		if (!is_array($data) || empty($data['Observations']) || !is_array($data['Observations'])) {
			return null;
		}
		$obs = $data['Observations'];
		if (!$obs) {
			return null;
		}
		// Prefer the latest row with a dry-bulb temperature.
		$best = null;
		$temps = array();
		$rain = null;
		$tz = new DateTimeZone('Europe/London');
		$now = new DateTime('now', $tz);
		$dayStart = clone $now;
		$dayStart->setTime(0, 0, 0);
		$coverEnd = clone $dayStart;
		$coverEnd->setTime(self::HILO_COVER_HOUR, 0, 0);
		$hasCover = false;
		foreach ($obs as $row) {
			$ts = null;
			foreach (array('Date', 'ObservationDate', 'Time', 'ReportDateTime', 'DateTimeUtc', 'DateTime') as $k) {
				if (!empty($row[$k])) {
					$ts = self::ts($row[$k]);
					if ($ts) {
						break;
					}
				}
			}
			$temp = null;
			foreach (array('DryBulbTemperature_Celsius', 'AirTemperature_Celsius', 'AirIntakeTemperature_Celsius') as $k) {
				if (isset($row[$k]) && $row[$k] !== null && $row[$k] !== '') {
					$temp = self::num($row[$k]);
					break;
				}
			}
			// WOW sometimes nests values.
			if ($temp === null && isset($row['Parameters']) && is_array($row['Parameters'])) {
				foreach (array('DryBulbTemperature_Celsius', 'AirTemperature_Celsius') as $k) {
					if (isset($row['Parameters'][$k])) {
						$v = $row['Parameters'][$k];
						$temp = is_array($v) ? self::num(isset($v['Value']) ? $v['Value'] : null) : self::num($v);
						if ($temp !== null) {
							break;
						}
					}
				}
			}
			$dayRain = null;
			foreach (array('Rainfall_Millimetre', 'RainfallAmount_Millimetre') as $k) {
				if (isset($row[$k]) && $row[$k] !== null && $row[$k] !== '') {
					$dayRain = self::num($row[$k]);
					break;
				}
			}
			if ($temp === null && $dayRain === null) {
				continue;
			}
			if ($ts && $temp !== null && $ts >= $dayStart->getTimestamp() && $ts <= $now->getTimestamp() + 600) {
				$temps[] = $temp;
				if ($ts <= $coverEnd->getTimestamp()) {
					$hasCover = true;
				}
			}
			if ($dayRain !== null) {
				$rain = $dayRain;
			}
			if ($best === null || ($ts && $ts >= $best['ts'])) {
				$best = array('ts' => $ts, 'temp_c' => $temp, 'rain' => $dayRain);
			}
		}
		if ($best === null) {
			return null;
		}
		$min = $max = null;
		if ($hasCover && $temps) {
			$min = min($temps);
			$max = max($temps);
		}
		// Prefer published daily min/max from the latest row if present.
		$last = $obs[count($obs) - 1];
		$pubMin = self::num(isset($last['AirTemperatureMin_Celsius']) ? $last['AirTemperatureMin_Celsius'] : null);
		$pubMax = self::num(isset($last['AirTemperatureMax_Celsius']) ? $last['AirTemperatureMax_Celsius'] : null);
		if ($pubMin !== null && $pubMax !== null) {
			$min = $pubMin;
			$max = $pubMax;
		}
		return array(
			'temp_c' => $best['temp_c'],
			'temp_min_c' => $min,
			'temp_max_c' => $max,
			'rain_today_mm' => ($best['rain'] !== null) ? $best['rain'] : $rain,
			'observed_at' => self::isoZ($best['ts']),
			'source' => 'wow',
		);
	}

	private static function mergeObs($rec, $obs) {
		if (!$obs) {
			return $rec;
		}
		foreach (array('temp_c', 'temp_min_c', 'temp_max_c', 'rain_today_mm', 'observed_at', 'source') as $k) {
			if (isset($obs[$k]) && $obs[$k] !== null) {
				$rec[$k] = $obs[$k];
			}
		}
		$rec['available'] = ($rec['temp_c'] !== null || $rec['rain_today_mm'] !== null);
		$rec['fetched_at'] = time();
		return $rec;
	}

	private static function fetchIem($icao) {
		$url = self::IEM_CURRENT . '?station=' . rawurlencode($icao) . '&network=' . self::IEM_NETWORK;
		return self::parseIemCurrent(self::httpJson($url));
	}

	private static function fetchAviationMap($icaos) {
		$url = self::AW_METAR . '?ids=' . rawurlencode(implode(',', $icaos)) . '&format=json';
		$data = self::httpJson($url);
		$map = array();
		if (is_array($data)) {
			foreach ($data as $obs) {
				if (isset($obs['icaoId'])) {
					$map[$obs['icaoId']] = self::parseAviation($obs);
				}
			}
		}
		return $map;
	}

	private static function fetchSynop($block, $nowTs = null) {
		$now = $nowTs ? $nowTs : time();
		$begin = gmdate('YmdHi', $now - 36 * 3600);
		$end = gmdate('YmdHi', $now + 3600);
		$url = self::OGIMET_SYNOP . '?begin=' . $begin . '&end=' . $end . '&block=' . rawurlencode($block);
		$text = self::httpGet($url, 'text/plain');
		if ($text === null) {
			return null;
		}
		return self::summariseSynop(self::parseOgimetSynop($text, $block), $now);
	}

	private static function fetchWow($siteId) {
		$day = (new DateTime('now', new DateTimeZone('Europe/London')))->format('Y-m-d');
		$url = self::WOW_TABLE . '/' . rawurlencode($siteId) . '/details/' . $day;
		return self::parseWowTable(self::httpJson($url));
	}

	/**
	 * Fetch all stations and write cache/v5/nearby.json.
	 * Never throws; one station failing does not blank the others.
	 * Successful values are kept; after a failed fetch a last-good copy
	 * is served for up to STALE_MAX seconds.
	 */
	public static function refresh() {
		$prev = self::readCache();
		$prevById = array();
		if ($prev && !empty($prev['stations'])) {
			foreach ($prev['stations'] as $s) {
				$prevById[$s['id']] = $s;
			}
		}

		$icaos = array();
		foreach (self::catalog() as $st) {
			if (!empty($st['sources']['iem'])) {
				$icaos[] = $st['sources']['iem'];
			}
		}
		$awMap = array();
		try {
			$awMap = self::fetchAviationMap($icaos);
		} catch (Exception $e) {
			$awMap = array();
		}

		$stations = array();
		foreach (self::catalog() as $id => $st) {
			$rec = self::baseRecord($st);
			$obs = null;
			try {
				if (!empty($st['sources']['iem'])) {
					$obs = self::fetchIem($st['sources']['iem']);
					if (!$obs && !empty($awMap[$st['sources']['iem']])) {
						$obs = $awMap[$st['sources']['iem']];
					}
				}
				// SYNOP fills gaps (St James current + lo–hi; airport rain is usually
				// absent from METAR/IEM, and SYNOP 6-groups are not used as "today").
				if (!empty($st['sources']['synop'])) {
					$syn = self::fetchSynop($st['sources']['synop']);
					if ($syn) {
						if (!$obs) {
							$obs = $syn;
						} else {
							if ($obs['temp_c'] === null && $syn['temp_c'] !== null) {
								$obs['temp_c'] = $syn['temp_c'];
								$obs['observed_at'] = $syn['observed_at'];
							}
							if ($obs['temp_min_c'] === null && $syn['temp_min_c'] !== null) {
								$obs['temp_min_c'] = $syn['temp_min_c'];
								$obs['temp_max_c'] = $syn['temp_max_c'];
							}
						}
					}
				}
				if (!$obs && !empty($st['sources']['wow'])) {
					$obs = self::fetchWow($st['sources']['wow']);
				}
			} catch (Exception $e) {
				$obs = null;
			}

			if ($obs) {
				$rec = self::mergeObs($rec, $obs);
			} elseif (isset($prevById[$id]) && !empty($prevById[$id]['fetched_at'])
				&& (time() - (int)$prevById[$id]['fetched_at']) < self::STALE_MAX) {
				$rec = $prevById[$id];
			}
			$stations[] = $rec;
		}

		$payload = array(
			'updated_at' => self::isoZ(time()),
			'origin' => array(
				'lat' => Site::LATITUDE,
				'lon' => Site::LONGITUDE,
			),
			'stations' => $stations,
		);
		nw3_ensure_runtime_dirs();
		nw3_atomic_write(self::cachePath(), nw3_json_encode($payload));
		return $payload;
	}

	public static function readCache() {
		$path = self::cachePath();
		if (!is_file($path)) {
			return null;
		}
		$raw = @file_get_contents($path);
		if ($raw === false || $raw === '') {
			return null;
		}
		$data = json_decode($raw, true);
		return is_array($data) ? $data : null;
	}

	/** This station, from already-loaded live data (not the nearby cache). */
	public static function thisStation() {
		$NOW = isset(Live::$NOW) ? Live::$NOW : null;
		$min = null;
		$max = null;
		if (function_exists('nw3_live_get')) {
			$min = nw3_live_get($NOW, 'min', 'temp');
			$max = nw3_live_get($NOW, 'max', 'temp');
		} elseif (is_array($NOW)) {
			$min = isset($NOW['min']['temp']) ? $NOW['min']['temp'] : null;
			$max = isset($NOW['max']['temp']) ? $NOW['max']['temp'] : null;
		}
		$temp = isset(Live::$temp) ? Live::$temp : null;
		$rain = isset(Live::$rain) ? Live::$rain : null;
		$ts = isset(Live::$unix) ? Live::$unix : null;
		$has = ($temp !== null || $rain !== null);
		return array(
			'id' => 'nw3',
			'name' => 'Hampstead NW3',
			'url' => null,
			'lat' => Site::LATITUDE,
			'lon' => Site::LONGITUDE,
			'elev_m' => null,
			'elev_source' => null,
			'dist_km' => 0,
			'temp_c' => ($temp === null || $temp === '') ? null : (float)$temp,
			'temp_min_c' => ($min === null || $min === '') ? null : (float)$min,
			'temp_max_c' => ($max === null || $max === '') ? null : (float)$max,
			'rain_today_mm' => ($rain === null || $rain === '') ? null : (float)$rain,
			'observed_at' => self::isoZ($ts),
			'available' => $has,
			'fetched_at' => time(),
			'source' => 'live',
		);
	}

	public static function ageLabel($iso, $nowTs = null) {
		$ts = self::ts($iso);
		if (!$ts) {
			return null;
		}
		$sec = max(0, ($nowTs ? $nowTs : time()) - $ts);
		if ($sec < 45) {
			return 'just now';
		}
		if ($sec < 120) {
			return '1 min ago';
		}
		if ($sec < 3600) {
			return (string)round($sec / 60) . ' min ago';
		}
		if ($sec < 90 * 60) {
			return '1 h ago';
		}
		if ($sec < 36 * 3600) {
			return (string)round($sec / 3600) . ' h ago';
		}
		$dt = new DateTime('@' . $ts);
		$dt->setTimezone(new DateTimeZone('Europe/London'));
		return $dt->format('j M H:i');
	}

	public static function rows() {
		$cache = self::readCache();
		$rows = array(self::thisStation());
		if ($cache && !empty($cache['stations']) && is_array($cache['stations'])) {
			foreach ($cache['stations'] as $s) {
				$rows[] = $s;
			}
		}
		return $rows;
	}

	public static function renderHomeCard() {
		$rows = self::rows();
		$cache = self::readCache();
		$hasCache = ($cache && !empty($cache['stations']));
		echo '<div class="wx-cards home-nearby" id="nearby-wrap">';
		echo '<div class="wx-card wx-card-nearby" id="nearby-card">';
		echo '<div class="wx-card-head"><span class="wx-card-label">Weather nearby</span></div>';
		if (!$hasCache) {
			echo '<p class="nearby-unavailable">Nearby observations have not been cached yet. They appear after the next scheduled cron refresh (every 10 minutes, via cron_main.php).</p>';
		} else {
			echo '<div class="nearby-table" id="nearby-stations">';
			echo '<div class="nearby-row nearby-head">';
			echo '<div></div><div>Temp</div><div>Lo–Hi</div><div>Rain today</div>';
			echo '</div>';
			$now = time();
			foreach ($rows as $s) {
				self::renderRow($s, $now);
			}
			echo '</div>';
			echo '<p class="nearby-note">Airport readings from the Iowa Environmental Mesonet and NOAA Aviation Weather; St James’s Park from Ogimet SYNOP. Kew Gardens is a Met Office climate station and may not have a current public observation.</p>';
		}
		echo '</div></div>';
	}

	private static function renderRow($s, $now) {
		$stale = false;
		$ts = self::ts(isset($s['observed_at']) ? $s['observed_at'] : null);
		if ($ts && ($now - $ts) > 2 * 3600) {
			$stale = true;
		}
		$name = htmlspecialchars($s['name'], ENT_QUOTES);
		$url = isset($s['url']) ? $s['url'] : '';
		if ($url && preg_match('#^https://[^\s\'"<>\\\\]+$#i', $url)) {
			$nameHtml = '<a href="' . htmlspecialchars($url, ENT_QUOTES) . '" rel="noopener">' . $name . '</a>';
		} else {
			$nameHtml = $name;
		}
		$meta = array();
		if (!empty($s['id']) && $s['id'] === 'nw3') {
			$meta[] = 'this station';
		} elseif (isset($s['dist_km']) && $s['dist_km'] !== null) {
			$meta[] = number_format((float)$s['dist_km'], 1) . ' km';
		}
		if (isset($s['elev_m']) && $s['elev_m'] !== null) {
			$approx = (isset($s['elev_source']) && $s['elev_source'] === 'estimated') ? '~' : '';
			$meta[] = $approx . (string)round($s['elev_m']) . ' m';
		}
		$age = self::ageLabel(isset($s['observed_at']) ? $s['observed_at'] : null, $now);
		if ($age) {
			$meta[] = $age;
		}
		$sub = $meta ? htmlspecialchars(implode(' · ', $meta), ENT_QUOTES) : '';
		$ageClass = $stale ? ' nearby-age-stale' : '';

		echo '<div class="nearby-row' . (empty($s['available']) ? ' nearby-missing' : '') . '">';
		echo '<div class="nearby-name"><div class="nearby-title">' . $nameHtml . '</div>';
		if ($sub) {
			echo '<div class="nearby-meta' . $ageClass . '">' . $sub . '</div>';
		}
		echo '</div>';
		if (empty($s['available'])) {
			echo '<div class="nearby-nodata">no recent data</div>';
		} else {
			$temp = ($s['temp_c'] === null) ? '–' : Wx::conv($s['temp_c'], Wx::Temperature);
			$lo = $s['temp_min_c'];
			$hi = $s['temp_max_c'];
			if ($lo === null && $hi === null) {
				$hilo = '–';
			} else {
				$hilo = Wx::conv($lo, Wx::Temperature, false) . '–' . Wx::conv($hi, Wx::Temperature, false);
			}
			$rain = ($s['rain_today_mm'] === null) ? '–' : Wx::conv($s['rain_today_mm'], Wx::Rain);
			echo '<div class="nearby-temp">' . $temp . '</div>';
			echo '<div class="nearby-hilo">' . $hilo . '</div>';
			echo '<div class="nearby-rain">' . $rain . '</div>';
		}
		echo '</div>';
	}
}
