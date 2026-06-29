<?php

class Wx {
	public static $lhm = ['Low','High','Mean'];
	public static $lhmFull = ['Lowest','Highest','Mean'];
	public static $mmm = ['min', 'max', 'mean'];
	public static $mmmFull = ['Minimum','Maximum','Mean'];
	public static $mmmr = ['Min', 'Max', 'Mean', 'Range'];
	public static $meanOrTotal = ['Mean', 'Total'];

	const rankNum = 10;
	const rankNumM = 10;
	const rankNumCM = 5;
	public static $periods = [7,31,365];

	public static $mappingsToDailyDataKey = [
		't' => 'temp', 'h' => 'humi', 'p' => 'pres', 'd' => 'dewp', 'w'=> 'wind', 'r' => 'rain', 'f' => 'feel', 'a' => 'pm25'
	];

	static function _conv_temp($val) { return ($val * 1.8) + 32; }
	static function _conv_abstemp($val) { return $val * 1.8; }
	static function _conv_rain($val) { return $val / 25.4; }
	static function _conv_wind($val) { return $val * 1.6093; }
	static function _conv_pres($val) { return $val / 33.864; }
	static function _conv_snow($val) { return $val / 2.54; }
	static function _conv_density($val) { return $val / 1.262; }

	// Units Enum
	const None = 0;
	const Temperature = 'temp';
	const Rain = 'rain';
	const Pressure = 'pres';
	const Wind = 'wind';
	const Humidity = 'humi';
	const Snow = 'snow';
	const Distance = 'dist';
	const Days = 'days';
	const Hours = 'hrs';
	const Seconds = 'secs';
	const Timestamp = 'unix';
	const Laststamp = 'last'; //Timestamp for most recent event (e.g. last rain tip)
	const Density = 'density';
	const AbsTemp = 'abstemp';
	const RainRate = 'rnrt';
	const Direction = 'dir';
	const DirectionRaw = 'dir_raw';
	const Pm25 = 'pm25'; // Air quality (raw PM2.5); banded to UK DAQI for display

	public static $windlabels = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE',
		'S', 'SSW','SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];

	public static $UNITS = [
		self::Temperature => [
			'name' => self::Temperature, # unique identifier
			"unit" => '&deg;C',
			'precision' => 1, # Number of decimal places to show
			'summable' => false, # Can be summed
			'minmax' => true, //Has a sensible maximum and minimum
			'round_size' => 5, # For intelligent auto-scale of charts
			'thresholds_day' => [-5,0,5, 10,15,20, 25,30,35], # For value-based colour banding
			'threshold_colours' => ['00c','035cb5','10afe4', '00ffc4','07ea57','c8fb11', 'fd0','f93','f65a17', 'da103c'], # Colours of those thresholds
			'threshold_txtcolours' => ['fff','fff',false, false,false,false, false,false,'fff', 'fff'], # Text colour (false == inherit]
			UNIT_US => [
				'unit' => '&deg;F',
				# NB: php 5.4 cannot use anon fns in static context
				'conversion' => true,
			]
		],
		self::Rain => [
			'name' => self::Rain,
			'unit' => 'mm',
			'precision' => 1,
			'summable' => true,
			'imperial_divisor' => 25.4,
			'round_size' => 5,
			'thresholds_day' => [0.1,0.2,0.6, 1,2,5, 10,15,20, 25,40],
			'thresholds_month' => [0.1,1,10, 15,25,35, 50,75,100, 125,150],
			'threshold_colours' => ['94939a','9D91C5','cff', '9fc','9edffd','9aacff', '7980ff','3f48f9','010efe', '050eab','050d97','0b0b3b'],
			'threshold_txtcolours' => [false,false,false, false,false,false, false,'ddd','eee', 'fff','fff','fff'],
			UNIT_US => [
				'unit' => 'in',
				'conversion' => true,
			]
		],
		self::Pressure => [
			'name' => self::Pressure,
			'unit' => 'hPa',
			'precision' => 0,
			'summable' => false,
			'minmax' => true,
			'round_size' => 10,
			'thresholds_day' => [970,980,990, 1000,1010,1015, 1020,1030,1040],
			'threshold_colours' => ['11c','05b','1ad', '0ec','1e5','cf1', 'fe0','fa4','f62', 'd14'],
			'threshold_txtcolours' => ['fff','fff',false, false,false,false, false,false,'fff', 'fff'],
			UNIT_US => [
				'unit' => 'inHg',
				'conversion' => true,
			]
		],
		self::Wind => [
			'name' => self::Wind,
			'unit' => 'mph',
			'precision' => 1,
			'summable' => false,
			'round_size' => 5,
			'thresholds_day' => [1,2,4, 7,10,15, 20,30,40],
			'threshold_colours' => ['d9fdfc','aff','6f9', '9f0','9c0','cc0', 'fc0','f90','f60', 'f00'],
			'threshold_txtcolours' => [false,false,false, false,false,false, false,false,'fff', 'fff'],
			UNIT_EU => [
				'unit' => 'kph',
				'conversion' => true,
			]
		],
		self::Humidity => [
			'name' => self::Humidity,
			'unit' => '%',
			'precision' => 0,
			'summable' => false,
			'minmax' => true,
			'round_size' => 10,
			'thresholds_day' => [30,40,50, 60,70,80, 90,98],
			'threshold_colours' => ['f3e2a9','f7d358','fb0', 'd7df01','a5df00','74df00', '31b404','329511','0b6121'],
			'threshold_txtcolours' => [false,false,false, false,false,false, false,false, 'C4C9C2']
		],
		self::Snow => [
			'name' => self::Snow,
			'unit' => 'cm',
			'precision' => 0,
			'precison_increase_threshold' => 1,
			'summable' => true,
			'manual' => true, # Manual obs
			UNIT_US => [
				'unit' => 'in',
				'conversion' => true,
			],
		],
		self::Distance => [
			'name' => self::Distance,
			'unit' => 'm',
			'precision' => 0,
			'summable' => false,
			UNIT_US => [
				'unit' => 'ft',
				'conversion' => true,
			],
		],
		self::Days => [
			'name' => self::Days,
			'unit' => 'days',
			'precision' => 0,
			'summable' => true,
			'thresholds_month' => [2,3,5, 7,10,12, 15,20,25, 30],
			'threshold_colours' => ['888','f5e2a9','f1d787', 'f2c181','f4b462','e9a245', 'f2a86b','ee9348','dc7b2b', 'eb8965','e46b3f','d85a3a'],
			'threshold_txtcolours' => [false,false,false, false,false,false, false,false,false, false,'fff','fff']
		],
		self::Hours => [
			'name' => self::Hours,
			'unit' => 'hrs',
			'precision' => 0,
			'precison_increase_threshold' => 1,
			'summable' => true,
			'manual' => true,
			'thresholds_day' => [0.1,0.3,0.5, 1,2,3, 5,7,9, 12,15],
			'thresholds_month' => [1,5,10, 25,50,75, 100,125,150, 200,250],
			'threshold_sum_coeff_month' => 20,
			'threshold_colours' => ['888','f5e2a9','f1d787', 'f2c181','f4b462','e9a245', 'f2a86b','ee9348','dc7b2b', 'eb8965','e46b3f','d85a3a'],
			'threshold_txtcolours' => [false,false,false, false,false,false, false,false,false, false,'fff','fff']
		],
		self::RainRate => [
			'name' => self::RainRate,
			'unit' => 'mm/h',
			'precision' => 0,
			'precison_increase_threshold' => 5,
			'summable' => false,
			'thresholds_day' => [0.3,1,2, 3,5,10, 30,60,100, 150,300],
			'threshold_colours' => ['94939a','918aa7','cff', '99ffcc','9edffd','9aacff', '7980ff','3f48f9','010efe', '050eab','050d97','0b0b3b'],
			'threshold_txtcolours' => [false,false,false, false,false,false, false,'fff','fff', 'fff','fff','fff'],
			UNIT_US => [
				'unit' => 'in/h',
				'conversion' => true,
			]
		],
		self::Direction => [
			'name' => self::Direction,
			'unit' => '',
			'precision' => 0,
			'summable' => false,
			'round_size' => 20,
			'thresholds_day' => [45,90,135, 180,225,270, 315],
			'threshold_colours' => ['f6cece','f6e3ce','f5f6ce', 'e3f6ce','cef6d8','ced8f6', 'ceb3fa','f6cee3'],
			'threshold_txtcolours' => [false,false,false, false,false,false, false,false]
		],
		self::DirectionRaw => [
			'name' => self::DirectionRaw,
			'unit' => '&deg;',
			'precision' => 0,
			'summable' => false,
			'round_size' => 20,
			'thresholds_day' => [45,90,135, 180,225,270, 315],
			'threshold_colours' => ['f6cece','f6e3ce','f5f6ce', 'e3f6ce','cef6d8','ced8f6', 'ceb3fa','f6cee3'],
			'threshold_txtcolours' => [false,false,false, false,false,false, false,false]
		],
		self::Density => [
			'name' => self::Density,
			'unit' => 'g/m<sup>3</sup>',
			'precision' => 2,
			'summable' => true,
			UNIT_US => [
				'unit' => 'oz/ft<sup>3</sup>',
				'conversion' => true,
			]
		],
		self::Timestamp => [
			'name' => self::Timestamp,
		],
		self::AbsTemp => [
			'name' => self::AbsTemp,
			'unit' => '&deg;C',
			'summable' => false,
			'minmax' => true,
			'round_size' => 5,
			'precision' => 1,
			'thresholds_day' => [0.5,1,2, 2,3,5, 7,10,15],
			'threshold_colours' => ['00c','035cb5','10afe4', '00ffc4','07ea57','c8fb11', 'fd0','f93','f65a17', 'da103c'],
			'threshold_txtcolours' => ['fff','fff',false, false,false,false, false,false,'fff', 'fff'],
			UNIT_US => [
				'unit' => '&deg;F',
				'conversion' => true,
			]
		],
		self::Pm25 => [
			'name' => self::Pm25,
			'unit' => '&micro;g/m<sup>3</sup>',
			'precision' => 1,
			'summable' => false,
			'minmax' => true,
			'round_size' => 10,
			'thresholds_day' => [11,23,35, 41,47,53, 58,64,70],
			'threshold_colours' => ['9cff9c','31ff00','31cf00', 'ffff00','ffcf00','ff9a00', 'ff6464','ff0000','990000', '7d0023'],
			'threshold_txtcolours' => [false,false,false, false,false,false, 'fff','fff','fff', 'fff'],
		]
	];

	public static function getUnits($type) {
		if(key_exists($type, self::$UNITS)) {
			$var = self::$UNITS[$type];
			if(key_exists(Page::$units, $var)) {
				$var = array_merge($var, $var[Page::$units]);
			}
			return $var["unit"];
		}
		return null;
	}

	/**
	 * Numeric unit conversion for charting: returns the value converted to the
	 * user's selected units as a float (no formatting/units string), or null for
	 * blank input. Mirrors conv() but is safe to feed straight into a chart.
	 * @param mixed $val raw value (UK units)
	 * @param string $type a Wx unit constant
	 * @param int|null $dp optional decimal places to round to
	 * @return float|null
	 */
	public static function convNum($val, $type, $dp = null) {
		if($val === null || $val === '' || $val === '-' || !is_numeric($val)) {
			return null;
		}
		$value = (float)$val;
		if(key_exists($type, self::$UNITS)) {
			$var = self::$UNITS[$type];
			if(key_exists(Page::$units, $var)) {
				$var = array_merge($var, $var[Page::$units]);
			}
			if(key_exists('conversion', $var)) {
				$value = call_user_func('Wx::_conv_'. $var['name'], $value);
			}
		}
		return ($dp === null) ? $value : round($value, $dp);
	}

	/**
	 * Resolves a legacy R-style colour name (e.g. 'royalblue1', 'tan3') to a hex
	 * string usable by Highcharts. Unknown names fall back to a neutral blue.
	 * @param string $name
	 * @return string hex colour
	 */
	public static function colourHex($name) {
		if($name === null || $name === '') {
			return '#4f81bd';
		}
		if($name[0] === '#') {
			return $name;
		}
		// Strip a trailing R-style shade index (e.g. 'royalblue2' -> 'royalblue')
		$base = preg_replace('/\d+$/', '', $name);
		$map = [
			'tan' => '#cd853f', 'royalblue' => '#4169e1', 'firebrick' => '#b22222',
			'orange' => '#ff8c00', 'chartreuse' => '#7fb800', 'darkolivegreen' => '#556b2f',
			'darkorchid' => '#9932cc', 'orchid' => '#da70d6', 'purple' => '#800080',
			'red' => '#e03131', 'darkseagreen' => '#8fbc8f', 'darkslategray' => '#2f4f4f',
			'peachpuff' => '#ffb38a', 'darkgoldenrod' => '#b8860b', 'lightpink' => '#f4a8b8',
			'azure' => '#7fb0c8', 'bisque' => '#e8c9a0', 'beige' => '#c8b88a',
			'cadetblue' => '#3a7a86', 'sienna' => '#a0522d', 'rosybrown' => '#bc8f8f',
			'green' => '#2f9e44', 'darkred' => '#8b0000', 'black' => '#333333',
			'cyan' => '#1cb5bf', 'yellow' => '#e6c000', 'aqua' => '#1cbfbf',
			'gold' => '#d4a017',
		];
		return isset($map[$base]) ? $map[$base] : (isset($map[$name]) ? $map[$name] : '#4f81bd');
	}

	/**
	* Convert from UK units to US or EU, and neaten-up <br />
	* @param mixed $tag the value to convert
	* @param int $type the coversion type (one of the class constants)
	* @param boolean $unit display unit [= true]
	* @param boolean $sign display sign +/- [= false]
	* @param int $dpa decimal pt adjustment [= 0]
	* @param boolean $abs take the absolute value [= false]
	* @param boolean $debug [= false]
	* @param boolean $noText [= false] pass true to prevent textual output (e.g. for charting)
	* @return string of the converted value
	*/
	static function conv($val, $type, $show_unit = true, $show_sign = false, $dpa = 0, $abs = false) {
		//Bad value checking and special cases
		if($type === self::None) {
			return $val;
		} elseif( is_null($val) ) {
			return 'null';
		} elseif($type === 'wdir' || $type === self::Direction) {
			return self::degname((int)$val);
		} elseif($type === self::Timestamp) {
			return date('H:i d M', $val);
		} elseif($type === self::Laststamp) {
			return self::pretty_last_stamp($val);
		}

		if(key_exists($type, self::$UNITS)) {
			$var = self::$UNITS[$type];
			if(key_exists(Page::$units, $var)) {
				$var = array_merge($var, $var[Page::$units]);
			}
		}
		else {
			return "WARNING! '$type' is not a valid type";
		}

		//Prettifier preparation
		$value = $abs ? abs((float)$val) : (float)$val;
		$space = ($type === self::Humidity) ? '' : ' ';
		$unit = $show_unit ? $space. $var['unit'] : '';
		$sign = $show_sign ? '+' : '';
		$precision = $var['precision'] + $dpa;

		//Special case handling
		if(key_exists('precison_increase_threshold', $var)
			&& ($value < $var['precison_increase_threshold'])
			&& ($value > 0)) {
			$precision++;
		}
		if(($type === self::Days) && $value === 1) {
			$unit = 'day';
		}

		//Actual conversion
		if(key_exists('conversion', $var)) {
			$value = call_user_func('Wx::_conv_'. $var['name'], [$value]);
		}

		//Format
		$strret = $sign.'.'. $precision.'f';
		return sprintf("%$strret", $value).$unit;
	}

	public static function hailname($val) {
		$types = array('No', 'Small', 'Med', 'Large');
		return $types[$val];
	}
	public static function thundername($val) {
		$types = array('N', 'Y', 'Light', 'Mod', 'Sevr');
		return $types[$val];
	}
	public static function degname($winddegree) {
		$windlabels = array ("N","NNE", "NE", "ENE", "E", "ESE", "SE", "SSE", "S", "SSW","SW", "WSW", "W", "WNW", "NW", "NNW","N");
		return $windlabels[ round($winddegree / 22.5, 0) ];
	}
	/**
	 * Convert from wind speeed in mph to a beaufort value and descriptive string
	 * @param int $mph raw value
	 * @return string bft-descrip + bft-force
	 */
	public static function bft($mph) {
		$bftscale = array(1,3,7, 12,17,24, 30,38,46, 54,63,73, 99);
		$bftword = array('Calm', 'Light air', 'Light breeze', 'Gentle breeze', 'Moderate breeze', 'Fresh breeze', 'Strong breeze', 'Near gale', 'Gale',
				'Severe gale', 'Storm', 'Violent storm', 'Hurricane');
		$cnt = 0;
		foreach ($bftscale as $value) {
			if($mph <= $value) {
				return $bftword[$cnt] . " (F$cnt)";
			}
			$cnt++;
		}
		return "Apocalypse";
	}
	/**
	 * Band a raw PM2.5 value (ug/m3) into the UK Daily Air Quality Index (DAQI).
	 * Uses DEFRA PM2.5 breakpoints (officially a 24h running mean - we band the
	 * latest reading as a live approximation).
	 * @param mixed $pm25 raw PM2.5
	 * @return array [band 1-10 (0 = unknown), name Low/Moderate/High/Very High, css-class]
	 */
	public static function daqi($pm25) {
		if($pm25 === null || $pm25 === '' || !is_numeric($pm25)) {
			return array(0, 'Unknown', 'daqi-unknown');
		}
		$pm = (float)$pm25;
		$bands = array(11, 23, 35, 41, 47, 53, 58, 64, 70); // upper bound of bands 1-9
		$band = 10;
		foreach($bands as $i => $hi) {
			if($pm <= $hi) { $band = $i + 1; break; }
		}
		if($band <= 3) { return array($band, 'Low', 'daqi-low'); }
		if($band <= 6) { return array($band, 'Moderate', 'daqi-mod'); }
		if($band <= 9) { return array($band, 'High', 'daqi-high'); }
		return array($band, 'Very High', 'daqi-vhigh');
	}
	/**
	 * US EPA Air Quality Index from raw PM2.5 (ug/m3), using the 2024-revised
	 * PM2.5 breakpoints. Concentration is truncated to 1 dp per EPA method.
	 * @param mixed $pm25 raw PM2.5
	 * @return int|null AQI 0-500, or null if no value
	 */
	public static function usAqi($pm25) {
		if($pm25 === null || $pm25 === '' || !is_numeric($pm25)) {
			return null;
		}
		$c = floor((float)$pm25 * 10) / 10; // truncate to 1 dp
		$bp = array(
			array(0.0,   9.0,   0,   50),
			array(9.1,   35.4,  51,  100),
			array(35.5,  55.4,  101, 150),
			array(55.5,  125.4, 151, 200),
			array(125.5, 225.4, 201, 300),
			array(225.5, 325.4, 301, 500),
		);
		foreach($bp as $b) {
			if($c <= $b[1]) {
				return (int)round(($b[3] - $b[2]) / ($b[1] - $b[0]) * ($c - $b[0]) + $b[2]);
			}
		}
		return 500;
	}
	public static function rate_fix($rate) {
		if(round($rate) > 5) { return round($rate); } else { return $rate; }
	}
	public static function norain_fix($time, $val, $rate = false) {
		$thresh = 0.1; if($rate) { $thresh = 0.5; }
		if($val < $thresh) { return 'n/a'; }
		else { return $time; }
	}
	/**
	 * Computes the appropriate feels-like temperature for given input
	 * @param float $t temperature
	 * @param float $v wind speed
	 * @param float $d dew point
	 * @return float the feels-like temp in degC
	 */
	public static function feelsLike($t, $v, $d) {
		if($t < 10 && $v > 3) {
			//http://en.wikipedia.org/wiki/Wind_chill#North_American_and_UK_wind_chill_index
			$x = pow($v * 1.61, 0.16);
			$windChill = 13.12 + $t * (0.6215 + 0.3965 * $x) - 11.37 * $x;
			return $windChill;
		} elseif($d > 7) { // humidex is less than T for sub-7 Td
			//http://en.wikipedia.org/wiki/Humidex
			$humidex = $t + 0.5555 * (6.11 * pow(M_E, 5417.753*(0.003660858-1/($d+273.15))) - 10);
			return $humidex;
		}
		return $t;
	}
	/**
	 * Dew point from temp and hum. <br />
	 * Found to be within 0.1&deg;C of WD's value across the lifetime range (-12 to +20)
	 * @param float $t temp
	 * @param int $h hum
	 * @return float dew point
	 */
	public static function dewPoint($t, $h) {
		//http://en.wikipedia.org/wiki/Dew_point
		$gamma = (17.271*$t) / (237.7+$t) + log($h/100);
		return (237.7*$gamma) / (17.271-$gamma);
	}

    public static $daily = [
		'tmin' => [
			'description' => 'Minimum Temperature',
			'unit' => Wx::Temperature,
			'colour' => '#FFD750',
			'anomaly' => true,
			'start_year' => 1881,
		],
		'tmax' => [
			'description' => 'Maximum Temperature',
			'unit' => Wx::Temperature,
			'colour' => 'orange',
			'anomaly' => true,
			'start_year' => 1881,
		],
		'tmean' => [
			'description' => 'Mean Temperature',
			'unit' => Wx::Temperature,
			'colour' => 'tan3',
			'spread' => true,
			'anomaly' => true,
			'start_year' => 1881,
		],
		'hmin' => [
			'description' => 'Minimum Humidity',
			'unit' => Wx::Humidity,
			'colour' => 'chartreuse',
			'start_year' => 2009,
		],
		'hmax' => [
			'description' => 'Maximum Humidity',
			'unit' => Wx::Humidity,
			'colour' => 'darkolivegreen',
			'start_year' => 2009,
		],
		'hmean' => [
			'description' => 'Mean Humidity',
			'unit' => Wx::Humidity,
			'colour' => 'chartreuse3',
			'spread' => true,
			'start_year' => 2009,
		],
		'pmin' => [
			'description' => 'Minimum Pressure',
			'unit' => Wx::Pressure,
			'colour' => 'darkorchid4',
			'start_year' => 2009,
		],
		'pmax' => [
			'description' => 'Maximum Pressure',
			'unit' => Wx::Pressure,
			'colour' => 'orchid1',
			'start_year' => 2009,
		],
		'pmean' => [
			'description' => 'Mean Pressure',
			'unit' => Wx::Pressure,
			'colour' => 'purple',
			'spread' => true,
			'start_year' => 2009,
		],
		'wmean' => [
			'description' => 'Mean Wind Speed',
			'unit' => Wx::Wind,
			'colour' => 'red',
			'spread' => true,
			'anomaly' => true,
			'start_year' => 1949,
		],
		'wmax' => [
			'description' => 'Maximum Wind Speed',
			'unit' => Wx::Wind,
			'colour' => 'firebrick1',
			'start_year' => 2009,
		],
		'gust' => [
			'description' => 'Maximum Gust Speed',
			'unit' => Wx::Wind,
			'colour' => 'firebrick2',
			'start_year' => 1959,
		],
		'wdir' => [
			'description' => 'Mean Wind Direction',
			'unit' => Wx::Direction,
			'colour' => 'firebrick3',
			'spread' => true,
			'start_year' => 2009,
		],
		'rain' => [
			'description' => 'Rainfall',
			'unit' => Wx::Rain,
			'colour' => 'royalblue',
			'spread' => true,
			'summable' => true,
			'anomaly' => true,
			'start_year' => 1871,
		],
		'hrmax' => [
			'description' => 'Maximum Hourly Rain',
			'unit' => Wx::Rain,
			'colour' => 'royalblue1',
			'start_year' => 2009,
		],
		'10max' => [
			'description' => 'Maximum 10-min Rain',
			'unit' => Wx::Rain,
			'colour' => 'royalblue2',
			'start_year' => 2009,
		],
		'ratemax' => [
			'description' => 'Maximum Rain Rate',
			'unit' => Wx::RainRate,
			'colour' => 'royalblue3',
			'start_year' => 2009,
		],
		'dmin' => [
			'description' => 'Minimum Dew Point',
			'unit' => Wx::Temperature,
			'colour' => 'darkseagreen',
			'start_year' => 2009,
		],
		'dmax' => [
			'description' => 'Maximum Dew Point',
			'unit' => Wx::Temperature,
			'colour' => 'darkslategray',
			'start_year' => 2009,
		],
		'dmean' => [
			'description' => 'Mean Dew Point',
			'unit' => Wx::Temperature,
			'colour' => 'darkseagreen4',
			'spread' => true,
			'start_year' => 2009,
		],
		'nightmin' => [
			'description' => 'Night Minimum (21-09)',
			'unit' => Wx::Temperature,
			'colour' => 'peachpuff',
			'start_year' => 1881,
		],
		'daymax' => [
			'description' => 'Day Maximum (09-21)',
			'unit' => Wx::Temperature,
			'colour' => 'darkgoldenrod1',
			'start_year' => 1881,
		],
		'tc10max' => [
			'description' => 'Max 10m Temp Rise',
			'unit' => Wx::Temperature,
			'colour' => 'tan1',
			'start_year' => 2009,
		],
		'tchrmax' => [
			'description' => 'Max 1hr Temp Rise',
			'unit' => Wx::Temperature,
			'colour' => 'tan2',
			'start_year' => 2009,
		],
		'hchrmax' => [
			'description' => 'Max 1hr Hum Rise',
			'unit' => Wx::Humidity,
			'colour' => 'darkolivegreen4',
			'start_year' => 2009,
		],
		'tc10min' => [
			'description' => 'Max 10m Temp Fall',
			'unit' => Wx::Temperature,
			'colour' => 'darkgoldenrod2',
			'start_year' => 2009,
		],
		'tchrmin' => [
			'description' => 'Max 1hr Temp Fall',
			'unit' => Wx::Temperature,
			'colour' => 'darkgoldenrod3',
			'start_year' => 2009,
		],
		'hchrmin' => [
			'description' => 'Max 1hr Hum Fall',
			'unit' => Wx::Humidity,
			'colour' => 'darkolivegreen1',
			'start_year' => 2009,
		],
		'w10max' => [
			'description' => 'Max 10m Wind Speed',
			'unit' => Wx::Wind,
			'colour' => 'lightpink2',
			'start_year' => 2009,
		],
		'fmin' => [
			'description' => 'Minimum Feels-like',
			'unit' => Wx::Temperature,
			'colour' => 'azure3',
			'start_year' => 2009,
		],
		'fmax' => [
			'description' => 'Maximum Feels-like',
			'unit' => Wx::Temperature,
			'colour' => 'bisque2',
			'start_year' => 2009,
		],
		'fmean' => [
			'description' => 'Mean Feels-like',
			'unit' => Wx::Temperature,
			'colour' => 'beige',
			'spread' => true,
			'start_year' => 2009,
		],
		'afhrs' => [
			'description' => 'Air-frost Hrs',
			'unit' => Wx::Hours,
			'colour' => 'cadetblue4',
			'summable' => true,
			'anomaly' => true,
			'start_year' => 2009,
		],
		'amin' => [
			'description' => 'Minimum PM2.5',
			'unit' => Wx::Pm25,
			'colour' => 'darkseagreen3',
			'start_year' => 2026,
		],
		'amax' => [
			'description' => 'Maximum PM2.5',
			'unit' => Wx::Pm25,
			'colour' => 'sienna',
			'start_year' => 2026,
		],
		'amean' => [
			'description' => 'Mean PM2.5',
			'unit' => Wx::Pm25,
			'colour' => 'rosybrown',
			'spread' => true,
			'start_year' => 2026,
		],
		// Derived quantities
		'trange' => [
			'description' => 'Temperature Range',
			'unit' => Wx::AbsTemp,
			'colour' => 'green',
			'anomaly' => true,
			'start_year' => 1881,
			'derived' => true,
		],
		'hrange' => [
			'description' => 'Humidity Range',
			'unit' => Wx::Humidity,
			'colour' => 'darkred',
			'start_year' => 2009,
			'derived' => true,
		],
		'prange' => [
			'description' => 'Pressure Range',
			'unit' => Wx::Pressure,
			'colour' => 'black',
			'start_year' => 2009,
			'derived' => true,
		],
		'ratemean' => [
			'description' => 'Mean Rain Rate',
			'unit' => Wx::RainRate,
			'colour' => 'cyan',
			'start_year' => 2009,
			'derived' => true,
		],
		'sunhrp' => [
			'description' => 'Sun % of max possible',
			'unit' => Wx::None,
			'colour' => '#ceca4a',
			'start_year' => 1910,
			'derived' => true,
		],
		'wethrp' => [
			'description' => 'Wet % of day',
			'unit' => Wx::None,
			'colour' => '#1cbfbf',
			'start_year' => 2009,
			'derived' => true,
		],
		// Manual-input variables
		'sunhr' => [
			'description' => 'Sun Hours',
			'unit' => Wx::Hours,
			'colour' => 'yellow',
			'summable' => true,
			'anomaly' => true,
			'start_year' => 1910,
		],
		'wethr' => [
			'description' => 'Wet Hours',
			'unit' => Wx::Hours,
			'colour' => 'aqua',
			'summable' => true,
			'anomaly' => true,
			'start_year' => 2009,
		],
		'cloud' => [
			'description' => 'Cloud Cover',
			'unit' => Wx::None,
			'colour' => 'black',
			'start_year' => 2009,
		],
		'snow' => [
			'description' => 'Falling Snow',
			'unit' => Wx::Snow,
			'colour' => 'black',
			'summable' => true,
			'start_year' => 1959,
		],
		'lysnw' => [
			'description' => 'Lying Snow',
			'unit' => Wx::Snow,
			'colour' => 'black',
			'summable' => true,
			'start_year' => 1949,
		],
		'hail' => [
			'description' => 'Hail',
			'unit' => Wx::None,
			'colour' => 'black',
			'count-only' => true,
			'start_year' => 2009,
		],
		'thunder' => [
			'description' => 'Thunder',
			'unit' => Wx::None,
			'colour' => 'black',
			'count-only' => true,
			'start_year' => 1949,
		],
		'fog' => [
			'description' => 'Dense Fog',
			'unit' => Wx::None,
			'colour' => 'black',
			'count-only' => true,
			'start_year' => 2009,
		],
		'comms' => [
			'description' => 'Comments',
			'unit' => Wx::None,
			'colour' => 'black',
			'start_year' => 2009,
		],
		'extra' => [
			'description' => 'Comms+',
			'unit' => Wx::None,
			'colour' => 'black',
			'start_year' => 2009,
		],
		'issues' => [
			'description' => 'Issues',
			'unit' => Wx::None,
			'colour' => 'black',
			'start_year' => 2009,
		],
		'away' => [
			'description' => 'Observer Absent?',
			'unit' => Wx::None,
			'colour' => 'black',
			'start_year' => 2009,
		],
		'pond' => [
			'description' => 'Pond Temperature',
			'unit' => Wx::Temperature,
			'colour' => 'orange',
			'start_year' => 2009,
		],
		// 'spare' => [
		// 	'description' => 'null',
		// 	'unit' => Wx::None,
		// 	'colour' => 'black',
		// ]
	];

	static $multiDay = [
		'af_days' => [
			'description' => 'Air Frost Days',
			'unit' => Wx::Days,
			'colour' => 'black',
			'start_year' => 2009,
		],
		'rain_days' => [
			'description' => 'Rainy Days',
			'unit' => Wx::Days,
			'colour' => 'black',
			'start_year' => 1871,
		],		

	];
}

class LTA {

	static function init() {
		//365-day clim avs
		$dtfanomcc = file(ROOT . 'tminmaxav.csv');
		$dsuncc = file(ROOT . 'maxsun.csv');
		for($z = 0; $z <= 365; $z++) {
			$dtanomcc = explode(',', $dtfanomcc[$z]);
			self::$vars["tmin"]["daily"][$z] = floatval($dtanomcc[0]);
			self::$vars["tmax"]["daily"][$z] = floatval($dtanomcc[1]);
			self::$vars["maxsun"]["daily"][$z] = $dsuncc[$z];
			$month_idx = date('n', Date::mkdate(1, min($z+1, 365), 2023)) - 1;
			$month_days = date('t', Date::mkdate(1, min($z+1, 365), 2023));
			$month_midpoint_idx = date("z", Date::mkdate($month_idx + 1, 15, Date::$dyear));
			self::$vars["rain"]["daily"][$z] = self::$vars["rain"]["monthly"][$month_idx] / $month_days;
			self::$vars["sunhr"]["daily"][$z] = self::$vars["sunhr"]["monthly"][$month_idx] / $month_days * ($dsuncc[$z] / $dsuncc[$month_midpoint_idx]);
			self::$vars["wmean"]["daily"][$z] = self::$vars["wmean"]["monthly"][$month_idx];
		}
		foreach(self::$vars as $key => &$obj) {
			if(is_array($obj) && array_key_exists("monthly", $obj)) {
				$obj["year_sum"] = array_sum($obj["monthly"]);
				$obj["year_mean"] = $obj["year_sum"] / 12;
				$obj["season_sum"] = [];
				$obj["season_mean"] = [];
				foreach(Date::$snums as $si => $months) {
					$season_sum = 0;
					foreach($months as $mi) {
						$season_sum += $obj["monthly"][$mi];
					}
					$obj["season_sum"][$si] = $season_sum;
					$obj["season_mean"][$si] = $season_sum / 3;
				}
			}
		}
		unset($obj);
	}

	public static function getDailyAnom($type, $month, $day, $yr = null) {
		if($yr === null) {
			$yr = Date::$dyear;
		}
		$z = date("z", Date::mkdate($month, $day, $yr));
		if(!isset(self::$vars[$type]["daily"][$z])) {
			return null;
		}
		return self::$vars[$type]["daily"][$z];
	}

	public static function getMonthlyAnom($type, $month) {
		if(!isset(self::$vars[$type]) || !is_array(self::$vars[$type]) || !isset(self::$vars[$type]["monthly"])) {
			return null;
		}
		return self::$vars[$type]["monthly"][$month-1];
	}
	
	public static function getYearlyAnom($type) {
		if(!isset(self::$vars[$type]) || !is_array(self::$vars[$type])) {
			return null;
		}
		$key = (isset(Wx::$daily[$type]["summable"]) && Wx::$daily[$type]["summable"]) ? "year_sum" : "year_mean";
		return isset(self::$vars[$type][$key]) ? self::$vars[$type][$key] : null;
	}

	public static function getSeasonAnom($type, $season) {
		if(!isset(self::$vars[$type]) || !is_array(self::$vars[$type])) {
			return null;
		}
		$key = (isset(Wx::$daily[$type]["summable"]) && Wx::$daily[$type]["summable"]) ? "season_sum" : "season_mean";
		return isset(self::$vars[$type][$key][$season]) ? self::$vars[$type][$key][$season] : null;
	}

	public static function getDateEndingAnom($type, $end, $duration) {
		$anom = 0;
		for($i = 0; $i < $duration; $i++) {
			$anom += self::getDailyAnom($type, Date::$dmonth, Date::$dday - $i, Date::$dyear) / ($this->summable ? 1 : $period);
		}
	}

	public static $vars = [
		"tmin" => [
			"monthly" => [3.0,3.0,4.3,6.0,9.0,12.0,14.2,14.1,11.5,8.6,5.5,3.6],
			"daily" => [],
			"desription" => "Min Temp",
			"unit" => 1,
			"color" => "blue"
		],
		"tmax" => [
			"monthly" => [7.8,8.2,10.9,14.3,17.8,20.9,23.0,22.5,19.1,14.8,10.7,8.2],
			"daily" => [],
			"desription" => "Max Temp",
			"unit" => 1,
			"color" => "orange"
		],
		"tmean" => "dynamic",
		"trange" => "dynamic",
		"rain" => [
			"monthly" => [59,45,39,42,46,47,46,54,50,65,67,57],
			"desription" => "Rainfall",
			"unit" => 2,
			"color" => "cadetblue3"
		],
		"rdays" => [
			"monthly" => [11,9,10,10,9,9,8,8,9,11,10,11],
			"desription" => "Rain Days > 1mm",
			"unit" => false,
			"color" => "cadetblue4"
		],
		"wmean" => [
			"monthly" => [5.2,5.1,5.2,4.9,4.7,4.4,4.3,4.0,3.9,4.1,4.6,5.1],
			"desription" => "Rain Days > 1mm",
			"unit" => false,
			"color" => "cadetblue4"
		],
		"sunhr" => [
			"monthly" => [69,78,113,161,189,194,195,185,147,118,84,65],
			"daily" => [],
			"desription" => "Sun hours",
			"unit" => false,
			"color" => "gold"
		],
		"maxsun" => [
			"monthly" => [233,249,331,376,440,452,454,410,342,295,237,219],
			"daily" => [],
			"desription" => "Max Sun hours",
			"unit" => false,
			"color" => "gold3"
		],
		"wethr" => [
			"monthly" => [67,52,53,46,40,37,34,38,41,49,63,62],
			"desription" => "Max Sun hours",
			"unit" => false,
			"color" => "aquamarine4"
		],
	];

}
