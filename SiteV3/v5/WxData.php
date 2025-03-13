<?php

class Wx {
	public static $dnm = ['min','max','mean'];
	public static $lhm = ['Low','High','Mean'];
	public static $lhmFull = ['Lowest','Highest','Mean'];
	public static $mmm = ['min', 'max', 'mean'];
	public static $mmmFull = ['Minimum','Maximum','Mean'];
	public static $mmmr = ['Min', 'Max', 'Mean', 'Range'];
	public static $meanOrTotal = ['Mean', 'Total'];

	const rankNum = 10;
	const rankNumM = 10;
	const rankNumCM = 5;
	const temp_styr = 2009;
	public static $pgather = [7,31,365];

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
	const Area = 'area';
	const AbsTemp = 'abstemp';
	const RainRate = 'rnrt';
	const Direction = 'dir';
	const DirectionRaw = 'dir_raw';

	public static $windlabels = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE',
		'S', 'SSW','SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];

	public static $UNITS = [
		self::Temperature => [
			'name' => self::Temperature, # unique identifier
			"unit" => 0,
			'precision' => 1, # Number of decimal places to show
			'summable' => false, # Can be summed
			'minmax' => true, //Has a sensible maximum and minimum
			'imperial_divisor' => 0.555556, # Conversion factor to imperial
			'round_size' => 5, # For intelligent auto-scale of charts
			'thresholds_day' => [-5,0,5, 10,15,20, 25,30,35], # For value-based colour banding
			'threshold_colours' => ['00c','035cb5','10afe4', '00ffc4','07ea57','c8fb11', 'fd0','f93','f65a17', 'da103c'], # Colours of those thresholds
			'threshold_txtcolours' => ['fff','fff',false, false,false,false, false,false,'fff', 'fff'], # Text colour (false == inherit]
		],
		self::Rain => [
			'name' => self::Rain,
			'precision' => 1,
			'summable' => true,
			'imperial_divisor' => 25.4,
			'round_size' => 5,
			'thresholds_day' => [0.1,0.2,0.6, 1,2,5, 10,15,20, 25,40],
			'thresholds_month' => [0.1,1,10, 15,25,35, 50,75,100, 125,150],
			'threshold_colours' => ['94939a','9D91C5','cff', '9fc','9edffd','9aacff', '7980ff','3f48f9','010efe', '050eab','050d97','0b0b3b'],
			'threshold_txtcolours' => [false,false,false, false,false,false, false,'ddd','eee', 'fff','fff','fff']
		],
		self::Pressure => [
			'name' => self::Pressure,
			'precision' => 1,
			'summable' => false,
			'minmax' => true,
			'imperial_divisor' => 33.864,
			'round_size' => 10,
			'thresholds_day' => [970,980,990, 1000,1010,1015, 1020,1030,1040],
			'threshold_colours' => ['11c','05b','1ad', '0ec','1e5','cf1', 'fe0','fa4','f62', 'd14'],
			'threshold_txtcolours' => ['fff','fff',false, false,false,false, false,false,'fff', 'fff']
		],
		self::Wind => [
			'name' => self::Wind,
			'precision' => 1,
			'summable' => false,
			'imperial_divisor' => 1,
			'round_size' => 5,
			'thresholds_day' => [1,2,4, 7,10,15, 20,30,40],
			'threshold_colours' => ['d9fdfc','aff','6f9', '9f0','9c0','cc0', 'fc0','f90','f60', 'f00'],
			'threshold_txtcolours' => [false,false,false, false,false,false, false,false,'fff', 'fff']
		],
		self::Humidity => [
			'name' => self::Humidity,
			'precision' => 0,
			'unit' => '%',
			'summable' => false,
			'minmax' => true,
			'imperial_divisor' => 1,
			'round_size' => 10,
			'thresholds_day' => [30,40,50, 60,70,80, 90,98],
			'threshold_colours' => ['f3e2a9','f7d358','fb0', 'd7df01','a5df00','74df00', '31b404','329511','0b6121'],
			'threshold_txtcolours' => [false,false,false, false,false,false, false,false, 'C4C9C2']
		],
		self::Snow => [
			'name' => self::Snow,
			'precision' => 0,
			'precison_increase_threshold' => 1,
			'summable' => true,
			'manual' => true, # Manual obs
			'imperial_divisor' => 2.54
		],
		self::Distance => [
			'precision' => 0,
			'summable' => false,
			'name' => self::Distance,
			'imperial_divisor' => 0.3048
		],
		self::Days => [
			'name' => self::Days,
			'precision' => 0,
			'unit' => 'days',
			'summable' => true,
			'imperial_divisor' => 1,
			'thresholds_month' => [2,3,5, 7,10,12, 15,20,25, 30],
			'threshold_colours' => ['888','f5e2a9','f1d787', 'f2c181','f4b462','e9a245', 'f2a86b','ee9348','dc7b2b', 'eb8965','e46b3f','d85a3a'],
			'threshold_txtcolours' => [false,false,false, false,false,false, false,false,false, false,'fff','fff']
		],
		self::Hours => [
			'name' => self::Hours,
			'precision' => 0,
			'precison_increase_threshold' => 1,
			'unit' => 'hrs',
			'summable' => true,
			'manual' => true,
			'imperial_divisor' => 1,
			'thresholds_day' => [0.1,0.3,0.5, 1,2,3, 5,7,9, 12,15],
			'thresholds_month' => [1,5,10, 25,50,75, 100,125,150, 200,250],
			'threshold_sum_coeff_month' => 20,
			'threshold_colours' => ['888','f5e2a9','f1d787', 'f2c181','f4b462','e9a245', 'f2a86b','ee9348','dc7b2b', 'eb8965','e46b3f','d85a3a'],
			'threshold_txtcolours' => [false,false,false, false,false,false, false,false,false, false,'fff','fff']
		],
		self::RainRate => [
			'name' => self::RainRate,
			'precision' => 0,
			'precison_increase_threshold' => 5,
			'summable' => false,
			'imperial_divisor' => 25.4,
			'thresholds_day' => [0.3,1,2, 3,5,10, 30,60,100, 150,300],
			'threshold_colours' => ['94939a','918aa7','cff', '99ffcc','9edffd','9aacff', '7980ff','3f48f9','010efe', '050eab','050d97','0b0b3b'],
			'threshold_txtcolours' => [false,false,false, false,false,false, false,'fff','fff', 'fff','fff','fff']
		],
		self::Direction => [
			'name' => self::Direction,
			'precision' => 0,
			'unit' => '',
			'summable' => false,
			'imperial_divisor' => 1,
			'round_size' => 20,
			'thresholds_day' => [45,90,135, 180,225,270, 315],
			'threshold_colours' => ['f6cece','f6e3ce','f5f6ce', 'e3f6ce','cef6d8','ced8f6', 'ceb3fa','f6cee3'],
			'threshold_txtcolours' => [false,false,false, false,false,false, false,false]
		],
		self::DirectionRaw => [
			'name' => self::DirectionRaw,
			'precision' => 0,
			'unit' => 'degs',
			'summable' => false,
			'imperial_divisor' => 1,
			'round_size' => 20,
			'thresholds_day' => [45,90,135, 180,225,270, 315],
			'threshold_colours' => ['f6cece','f6e3ce','f5f6ce', 'e3f6ce','cef6d8','ced8f6', 'ceb3fa','f6cee3'],
			'threshold_txtcolours' => [false,false,false, false,false,false, false,false]
		],
		self::Area => [
			'name' => self::Area,
			'summable' => true,
			'imperial_divisor' => 1.262
		],
		self::Timestamp => [
			'name' => self::Timestamp,
			'imperial_divisor' => 1
		],
		self::AbsTemp => [
			'name' => self::AbsTemp,
			'summable' => false,
			'minmax' => true,
			'imperial_divisor' => 0.55556,
			'round_size' => 5,
			'precision' => 1,
			'thresholds_day' => [0.5,1,2, 2,3,5, 7,10,15],
			'threshold_colours' => ['00c','035cb5','10afe4', '00ffc4','07ea57','c8fb11', 'fd0','f93','f65a17', 'da103c'],
			'threshold_txtcolours' => ['fff','fff',false, false,false,false, false,false,'fff', 'fff']
		]
	];

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
		}
//		elseif(key_exists($type, self::$daily)) {
//			$var = self::$daily[$type];
//		}
		else {
			return "WARNING! '$type' is not a valid type";
		}

		//Prettifier preparation
		$value = $abs ? abs((float)$val) : (float)$val;
		$unit = $show_unit ? ' '. $var['unit'] : '';
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
		if(Page::$units === UNIT_US) {
			$value /= $var['imperial_divisor'];
			if($type === self::Temperature) { $value += 32; }
		} elseif(Page::$units === UNIT_EU && $type === self::Wind) {
			$value *= 1.6093;
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
}

class Live {
	// Live Data
	public static $unix;
	public static $temp;
	public static $humi;
	public static $pres;
	public static $rain;
	public static $wind;
	public static $gust;
	public static $gustRaw;
	public static $w10m;
	public static $wdir;
	public static $dewp;
	public static $feel;

	// 24hr / today
	public static $NOW;
	public static $HR24;

	// Other multi-use weather vars
	public static $maxgsthr;
	public static $maxgstToday;

	public static $diff = 0;
	public static $updated;
	public static $outage = false;

	public static function init() {
		self::$NOW = unserialize(file_get_contents(ROOT . 'serialised_datNow.txt'));
		self::$HR24 = unserialize(file_get_contents(ROOT . 'serialised_datHr24.txt'));

		$crsizeFinal = filesize(Site::LIVE_DATA_PATH);

		//Select appropriate file to use
		$usePath = Site::LIVE_DATA_PATH;
		$badCRdata = false;
		if($crsizeFinal === 0) {
			$usePath = Site::MAIN_ROOT.'clientrawBackup.txt';
			$badCRdata = true;
		}

		$client = file($usePath);
		$mainData = explode(" ", $client[0]);

		if($badCRdata) {
			Page::log_events('clientrawBad.txt', $crsizeFinal ."B ");
		}

		$kntsToMph = 1.152;
		// Main current weather variables
		self::$temp = $mainData[4];
		self::$humi = $mainData[5];
		self::$pres = $mainData[6];
		self::$rain = $mainData[7];
		self::$wind = $mainData[1] * $kntsToMph;
		self::$gust = $mainData[140] * $kntsToMph; //actually the max 1-min gust
		self::$gustRaw = $mainData[2] * $kntsToMph; //true 14s gust
		self::$w10m = $mainData[158] * $kntsToMph;
		self::$wdir = $mainData[3];

		// Time variables
		self::$unix = mktime(intval($mainData[29]), intval($mainData[30]), intval($mainData[31]),
				intval($mainData[36]), intval($mainData[35]), intval($mainData[141]));

		self::$diff = time() - self::$unix;
		self::$outage = self::$diff > 3600;

		// Other multi-use weather vars
		self::$maxgsthr = self::$HR24['misc']['maxhrgst'];
		self::$maxgstToday = self::$NOW['max']['gust'];

		// Harpenden data
		if(self::$outage) {
			$extClient = file(ROOT.'EXT_harpenden.txt');
			$extData = explode(" ", $extClient[0]);
			self::$unix =  mktime(intval($extData[29]), intval($extData[30]), intval($extData[31]),
				intval($extData[36]), intval($extData[35]), intval($extData[141]));
			$harpenden_age = time() - self::$unix;
			if($harpenden_age < 600) {
				$extOffset = 0.9; // 0.91; //1.3 - tott;
				self::$wind = $extData[1] * $kntsToMph * $extOffset;
				self::$gust = $extData[140] * $kntsToMph * $extOffset; //actually the max 1-min gust
				self::$gustRaw = $extData[2] * $kntsToMph * $extOffset; //true 14s gust
				self::$w10m = $extData[158] * $kntsToMph * $extOffset;
				self::$wdir = $extData[3];
				self::$pres = $extData[6];
				self::$temp = $extData[4] + 1;
				self::$humi = $extData[5];
			}
		}
		// Synoptic data from James park
		if(self::$outage && false) {
			$mod_james = filemtime(ROOT.'EXT_james.json');
			$alt_age = time() - $mod_james;
			if($alt_age < 600) {
				self::$unix = $mod_james;
				$james_data = json_decode(file_get_contents(ROOT."EXT_james.json"), true);
				self::$temp = $james_data["STATION"][0]["OBSERVATIONS"]["air_temp_value_1"]["value"] - 0.5;
				self::$dewp = $james_data["STATION"][0]["OBSERVATIONS"]["dew_point_temperature_value_1"]["value"] - 0.5;
		//		$rain = $james_data["STATION"][0]["OBSERVATIONS"]["precip_accum_12_hour_value_1"]["value"];
				// https://www.omnicalculator.com/physics/relative-humidity
				self::$humi = intval(100 * exp((17.625 * self::$dewp) / (243.04 + self::$dewp)) / exp((17.625 * self::$temp) / (243.04 + self::$temp)));
		//		$humi = (int)(100 - ($temp - $dewp) * 5);  // TODO better
			}
		}
		// CWOP Islington data
		if(self::$outage) {
			$isl_data = json_decode(file_get_contents(ROOT."EXT_islington.json"), true);
			$isl_unix = intval($isl_data["weather"]["timestamp"] / 1000);
			if((time() - $isl_unix) < 900) {
				$unix = $isl_unix;
				$temp = (float)$isl_data["weather"]["wx"]["temp"];
				$humi = $isl_data["weather"]["wx"]["humidity"];
				$rain = (float)$isl_data["weather"]["wx"]["rain_midnight"];
				$pres = (float)$isl_data["weather"]["wx"]["pressure"];
			}
		}

		// Derived current weather variables
		self::$feel = Wx::feelsLike(self::$temp, self::$gust, self::$dewp);
		self::$dewp = Wx::dewPoint(self::$temp, self::$humi);
	}
}

class LTA {

	public static $vars = [
		"tmin" => [
			"monthly" => [3.0,3.0,4.3,6.0,9.0,12.0,14.2,14.1,11.5,8.6,5.5,3.6],
			"desription" => "Min Temp",
			"unit" => 1,
			"color" => "blue"
		],
		"tmax" => [
			"monthly" => [7.8,8.2,10.9,14.3,17.8,20.9,23.0,22.5,19.1,14.8,10.7,8.2],
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
			"desription" => "Sun hours",
			"unit" => false,
			"color" => "gold"
		],
		"maxsun" => [
			"monthly" => [233,249,331,376,440,452,454,410,342,295,237,219],
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

class Data {

	public static $CACHE_DAT = [];
	public static $CACHE_DAT_HIST = [];

	const SUMMARY_MEAN = 0;
	const SUMMARY_SUM = 1;
	const SUMMARY_COUNT = 2;
	const SUMMARY_MIN = 3;
	const SUMMARY_MAX = 4;

	public static $SUMMARY_NAMES = ["mean", "total", "count", "lowest", "highest"];
	public static $SUMMARY_EXPLAIN = ["monthly average", "monthly total", "number of non-zero days",  "lowest <b>daily</b> value in each month",  "highest <b>daily</b> value in each month"];


	public static $daily = [
		'tmin' => [
			'description' => 'Minimum Temperature',
			'group' => self::Temperature,
			'colour' => '#FFD750',
		],

		'trange' => [
			'description' => '24hr Temperature Range (09-09)',
			'group' => self::AbsTemp,
			'category' => 'Other',
			'anomable' => true,
			'colour' => 'green',
			'spread' => true,
		],
		'nightmin' => [
			'description' => 'Night Minimum (21-09)',
			'descrip' => 'Night Min', # Short description
			'anomable' => true, #Anomaly calculations possible
			'group' => self::Temperature, #Inheritance of properties,
			'category' => 'Temperature', #Practically, e.g. for use in drop-down grouping
			'colour' => '#33f', #for graphs
			'is_min' => 'true'
		],
		'daymax' => [
			'description' => 'Day Maximum (09-21)',
			'descrip' => 'Daytime Max',
			'group' => self::Temperature,
			'category' => 'Temperature',
			'anomable' => true,
			'colour' => 'orange'
		],
		'tmean' => [
			'description' => 'Mean Temperature',
			'descrip' => 'Mean Temp',
			'group' => self::Temperature,
			'category' => 'Temperature',
			'anomable' => true,
			'colour' => '#aae',
			'spread' => true, # Quantity is an average/sum, so has no point instance and therefore no time_of
		],

		'hmin' => [
			'description' => 'Minimum Humidity',
			'descrip' => 'Min Humidity',
			'group' => self::Humidity,
			'category' => 'Humidity',
			'colour' => 'chartreuse',
			'is_min' => 'true'
		],
		'hmax' => [
			'description' => 'Maximum Humidity',
			'descrip' => 'Max Humidity',
			'group' => self::Humidity,
			'category' => 'Humidity',
			'colour' => 'darkolivegreen'
		],
		'hmean' => [
			'description' => 'Mean Humidity',
			'group' => self::Humidity,
			'category' => 'Humidity',
			'colour' => '#283',
			'spread' => true,
		],

		'pmin' => [
			'description' => 'Minimum Pressure',
			'group' => self::Pressure,
			'category' => 'Pressure',
			'colour' => '#fab',
			'is_min' => 'true'
		],
		'pmax' => [
			'description' => 'Maximum Pressure',
			'group' => self::Pressure,
			'category' => 'Pressure',
			'colour' => '#e9a'
		],
		'pmean' => [
			'description' => 'Mean Pressure',
			'group' => self::Pressure,
			'category' => 'Pressure',
			'colour' => 'purple',
			'spread' => true,
		],

		'wmean' => [
			'description' => 'Mean Wind Speed',
			'group' => self::Wind,
			'anom_day_ignore' => true,
			'category' => 'Wind',
			'anomable' => true,
			'minmax' => true,
			'colour' => 'red',
			'spread' => true,
		],
		'wmax' => [
			'description' => 'Maximum Wind Speed',
			'group' => self::Wind,
			'anomable' => false,
			'category' => 'Wind',
			'colour' => '#f33'
		],
		'gust' => [
			'description' => 'Maximum Gust Speed',
			'group' => self::Wind,
			'anomable' => false,
			'precision' => 0,
			'category' => 'Wind',
			'colour' => '#f44'
		],
		'wdir' => [
			'description' => 'Mean Wind Direction',
			'group' => self::Direction,
			'anomable' => false,
			'nosummary' => true,
			'category' => 'Wind',
			'colour' => '#f77',
			'spread' => true,
		],
		'w10max' => [
			'description' => 'Max 10m Speed',
			'group' => self::Wind,
			'anomable' => false,
			'category' => 'Wind',
			'colour' => '#f99',
		],
		'rain' => [
			'description' => 'Rainfall',
			'group' => self::Rain,
			'category' => 'Rainfall',
			'anomable' => true,
			'colour' => 'royalblue',
			'spread' => true,
		],
		'hrmax' => [
			'description' => 'Maximum Hourly Rain',
			'group' => self::Rain,
			'category' => 'Rainfall',
			'summable' => false,
			'colour' => '#55e'
		],
		'r10max' => [
			'description' => 'Maximum 10-min Rain',
			'group' => self::Rain,
			'category' => 'Rainfall',
			'summable' => false,
			'colour' => '#77e'
		],
		'ratemax' => [
			'description' => 'Maximum Rain Rate',
			'group' => self::RainRate,
			'category' => 'Rainfall',
			'colour' => '#99f',
		],

		'dmin' => [
			'description' => 'Minimum Dew Point',
			'group' => self::Temperature,
			'category' => 'Dew Point',
			'colour' => 'darkseagreen',
			'is_min' => 'true'
		],
		'dmax' => [
			'description' => 'Maximum Dew Point',
			'group' => self::Temperature,
			'category' => 'Dew Point',
			'colour' => 'darkslategray'
		],
		'dmean' => [
			'description' => 'Mean Dew Point',
			'group' => self::Temperature,
			'category' => 'Dew Point',
			'colour' => '#2a3',
			'spread' => true,
		],

		'fmin' => [
			'description' => 'Minimum Windchill',
			'group' => self::Temperature,
			'category' => 'Feels Like',
			'colour' => 'darkseagreen',
			'is_min' => 'true'
		],
		'fmax' => [
			'description' => 'Maximum Humidex',
			'group' => self::Temperature,
			'category' => 'Feels Like',
			'colour' => 'darkslategray'
		],

		'sunhr' => [
			'description' => 'Sun Hours',
			'group' => self::Hours,
			'category' => 'Sun',
			'anomable' => true,
			'colour' => '#ff3',
			'spread' => true,
		],
		'wethr' => [
			'description' => 'Wet Hours',
			'group' => self::Hours,
			'category' => 'Observation',
			'anomable' => true,
			'colour' => 'aqua',
			'spread' => true,
		],
		'afhrs' => [
			'description' => 'Frost Hours',
			'group' => self::Hours,
			'category' => 'Temperature',
			'anomable' => false,
			'colour' => '#67f',
			'spread' => true,
		],

		'rdays' => [
			'description' => 'Days of Rain',
			'group' => self::Days,
			'colour' => '#57d'
		],
		'days_frost' => [
			'description' => 'Days of AirFrost',
			'group' => self::Days,
			'colour' => '#33f'
		],
		'days_storm' => [
			'description' => 'Days of Thunder',
			'group' => self::Days,
			'colour' => '#ee5'
		],
		'days_snow' => [
			'description' => 'Days of Lying Snow',
			'group' => self::Days,
			'colour' => '#4ad'
		],
		'days_snowfall' => [
			'description' => 'Days of Falling Snow',
			'group' => self::Days,
			'colour' => '#5cf'
		],
		'sunmax' => [
			'description' => 'Maximum Possible Sunshine',
			'group' => self::Hours,
			'colour' => '#883'
		]
	];

	/**
	 * Converts an array indexed as [month][day] to one indexed by [dayOfYear]
	 * @param mixed array[][]
	 * @return mixed array[]
	 */
	public static function MDtoZ($arr) {
		$z = array();
		$cnt = count($arr);
		for($mon = 1; $mon <= $cnt; $mon++) {
			if(is_array($arr[$mon])) {
				$z = array_merge($z, $arr[$mon]);
			}
		}
		return $z;
	}

	/**
	 * Converts an array indexed as [month][day] to one indexed by [month], containing means/sums over the number of days
	 * @param mixed $arr 2D array
	 * @param boolean $sum [=false] true if sum rather than mean
	 * @param int $type [=2] 0: min, 1:max, 2:mean, 3:count
	 * @return mixed 1D array of means/sums
	 */
	public static function MDtoMsummary($arr, $sum = false, $type = 2) {
		//WARNING: DO NOT MODIFY!!!!! Direct casting to int results in very weird bug (3 -> 2)
		$type = round($type);
		$type = (int)$type;
		for($mon = 1; $mon <= count($arr); $mon++) {
			$div = (!$sum) ? count($arr[$mon]) : 1;

			$summary[$mon] = ($type === 2) ? mean($arr[$mon], $div) :
				( ($type === 1) ? mymax($arr[$mon]) :
					( ($type === 0) ? mymin($arr[$mon]) : sum_cond($arr[$mon], true, 0) ) );
		}
		return $summary;
	}

	public static function datAnom($varName, $sourceData, $originalVarNum) {
		global $lta, $vars, $sumq_all;

		$ltaRefDaily = ['tmina' => 0, 'tmaxa' => 1, 'tmeana' => 2, 'sunhrp' => 4];
		$ltaRefMonthly = ['raina' => 4, 'wmeana' => 6, 'wethra' => 11, 'sunhra' => 12];

		$originalSummable = $sumq_all[$originalVarNum];
		$anomType = substr($varName, strlen($varName)-1, 1);  // a or p

		$res = [];
		foreach ($sourceData as $year => $arr1) {
			foreach ($arr1 as $month => $arr2) {
				$daysInMonth = get_days_in_month($month, $year);
				foreach ($arr2 as $day => $v) {
					if(array_key_exists($varName, $ltaRefDaily)) {
						$climVal = $lta[$ltaRefDaily[$varName]][date('z', mkdate($month, $day, $year))];
					} elseif(array_key_exists($varName, $ltaRefMonthly)) {
						$divisor = $originalSummable ? $daysInMonth : 1;
						$climVal = $vars[$ltaRefMonthly[$varName]][$month-1] / $divisor;
					} else {
						$climVal = 24;
					}
					if($originalSummable) {
						$val = $v / $climVal * 100;
					} else {
						$val = $v - $climVal;
					}
					if($val > 100 && $anomType === 'p') {
						$val = 100;
					}
					$res[$year][$month][$day] = $val;
				}
			}
		}
		return $res;
	}

	public static function datDerived($varName, $include_historic) {
		global $types_all;

		$srcMap = ["ratemean" => ["rain", "wethr"], "trange" => ["tmin", "tmax"], "hrange" => ["hmin", "hmax"], "prange" => ["pmin", "pmax"]];
		$src = $srcMap[$varName];
		$var1 = varNumToDatArray($types_all[$src[0]], $include_historic);
		$var2 = varNumToDatArray($types_all[$src[1]], $include_historic);

		$res = [];
		foreach ($var1 as $year => $arr1) {
			foreach ($arr1 as $month => $arr2) {
				foreach ($arr2 as $day => $val) {
					if($varName === "ratemean") {
						$res[$year][$month][$day] = ($var2[$year][$month][$day]  > 0.4 && $val > 0.3) ?
							$val / $var2[$year][$month][$day] : null;
					}
					else {
						$res[$year][$month][$day] = $var2[$year][$month][$day] - $val;
					}
				}
			}
		}
		return $res;
	}


	/**
	 * Gets data from the right global ALL array (DATA, DATAM), or derives it (anoms, ranges, rates).
	 * @param int $varNum
	 * @param mixed $include_historic false to exclude, int to set start year for historic data, true to include all
	 * @return mixed
	 */
	public static function varNumToDatArray($varNum, $include_historic = false) {
		global $types, $types_alltogether, $types_derived, $types_all, $types_anom, $start_year_all, $CACHE_DAT, $CACHE_DAT_HIST;

		$varName = $types_alltogether[$varNum];
		$isAnom = in_array($varName, $types_anom);
		if($isAnom) {
			$anomVarName = $varName;
			$varNum = $types_all[substr($varName, 0, strlen($varName)-1)];  // e.g. tmina -> 0
			$varName = $types_alltogether[$varNum];
		}
		if(in_array($varName, $types_derived)) {
			return datDerived($varName, $include_historic);
		}
		if($varNum < count($types)) {
			if (!array_key_exists($varName, $CACHE_DAT)) {
				$CACHE_DAT[$varName] = unserialize(file_get_contents(ROOT . "serialised_dat_$varNum.txt"));
			}
		} else {
			if (!array_key_exists($varName, $CACHE_DAT)) {
				$idx = $varNum - $types_all['sunhr'];
				$CACHE_DAT[$varName] = unserialize(file_get_contents(ROOT . "serialised_datm_$idx.txt"));
			}
		}
		$arr = $CACHE_DAT[$varName];
		if($include_historic !== false && $start_year_all[$varNum] < 2009) {
			if($include_historic < 2009) {
				// Populate cache
				if (!array_key_exists($varName, $CACHE_DAT_HIST)) {
					$CACHE_DAT_HIST[$varName] = unserialize(file_get_contents(ROOT."serialised_historical_$varName.txt"));
				}
				$arr = $CACHE_DAT_HIST[$varName] + $arr;
			}
		}
		if($isAnom) {
			// NB: passing the original varname
			return datAnom($anomVarName, $arr, $varNum);
		}
		return $arr;
	}


	public static function summarize($arr, $summary_type) {
		if($summary_type === SUMMARY_MEAN) {
			return mean($arr);
		}
		if($summary_type === SUMMARY_SUM) {
			return mean($arr, 1);
		}
		if($summary_type === SUMMARY_COUNT) {
			return sum_cond($arr, true, 0);
		}
		if($summary_type === SUMMARY_MIN) {
			return mymin($arr);
		}
		if($summary_type === SUMMARY_MAX) {
			return mymax($arr);
		}
	}
	public static function summarize2D($arr2D, $summary_type) {
		$summary = [];
		foreach($arr2D as $k => $arr) {
			$summary[$k] = summarize($arr, $summary_type);
		}
		return $summary;
	}


	// GLOBAL DATA ACCESS FUNCTIONS
	/**
	 * Returns array[year][month][day] = val
	 * @param type $var
	 * @param type $start_year
	 * @return type
	 */
	public static function getDailyData($var, $start_year) {
		$data = [];
		foreach( varNumToDatArray($GLOBALS["types_all"][$var], $start_year) as $y => $dat ) {
			if($y >= $start_year) {
				$data[$y] = $dat;
			}
		}
		return $data;
	}
	/**
	 * Returns array[month][day] = val
	 * @param type $var
	 * @param type $year
	 * @return type
	 */
	public static function getDailyDataForYear($var, $year) {
		$data = varNumToDatArray($GLOBALS["types_all"][$var], $year);
		return $data[$year];
	}
	/**
	 * Returns array[year][month] = summary_val
	 * @param type $var
	 * @param type $summary_type
	 * @param type $start_year
	 * @param type $end_year
	 * @return type
	 */
	public static function getMonthlyData($var, $summary_type, $start_year, $end_year) {
		$data = [];
		foreach (varNumToDatArray($GLOBALS["types_all"][$var], $start_year) as $year => $months) {
			if($year >= $start_year && $year <= $end_year) {
				$data[$year] = summarize2D($months, $summary_type);
			}
		}
		return $data;
	}
	/**
	 * Returns array[year] = summary_val
	 * @param type $var
	 * @param type $summary_type
	 * @param type $start_year
	 * @param type $end_year
	 * @return type
	 */
	public static function getAnnualData($var, $summary_type, $start_year, $end_year) {
		$data = [];
		foreach (varNumToDatArray($GLOBALS["types_all"][$var], $start_year) as $year => $months) {
			if($year >= $start_year && $year <= $end_year) {
				$dat = [];
				foreach($months as $daily) {
					$dat = array_merge($dat, $daily);
				}
				$data[$year] = summarize($dat, $summary_type);
			}
		}
		return $data;
	}

	public static function typeToConvType($type) {
		return $GLOBALS['typeconvs_all'][$GLOBALS['types_all'][$type]];
	}

	/**
	 * Good implementation of calculating the mean wind direction from an array of wdirs and speeds
	 * @param array $wdir raw array
	 * @param array $speed so calm times can be ignored
	 * @return int
	 */
	public static function wdirMean($wdir, $speed) {
		$bitifier = 36; //constant - the quantisation level to convert 360 degrees into a bitier signal
		$calmThreshold = 1; //constant - values when the wind speed was below this are ignored

		$end = count($wdir);

		$freqs = array();
		for($i = 0; $i <= 360/$bitifier; $i++) {
			$freqs[$i] = 0;
		}

		//get frequencies for each bitified angle
		for($i = 0; $i < $end; $i++) {
			if($speed[$i] > $calmThreshold) { // pivot not to be affected by calm times
				$freqs[round($wdir[$i] / $bitifier)]++;
			}
		}

		//choose a pivot
		$minfreq = min($freqs);
		$pivot = array_search($minfreq, $freqs);
		$pivot *= $bitifier;

		//calculate the mean
		$sum = 0;
		$count = 0;
		for($i = 0; $i < $end; $i++) {
			//values from calm times or near pivot are anomalous => ignore
			if(abs($wdir[$i] - $pivot) >= $bitifier && $speed[$i] > $calmThreshold) {
				$sum += $wdir[$i];
				$count++;
				if($wdir[$i] > $pivot) {
					$sum -= 360;
				}
			}
		}
		//clean-up
		$mean = ($count === 0) ? 0 : roundToDp($sum / $count, 0);
		if($mean < 0) {
			$mean += 360;
		}

		return $mean;
	}

	/**
	 * Processes a daily logfile into useful data - max, mins, means etc.
	 * @param string $procfil [=today] Ymd format for the day to process
	 * @return array of data for the chosen daily logfile
	 */
	public static function dailyData($procfil = 'today') {
		$datt = $dat = array();
		for($t = 6; $t < 10; $t++) {
			$datt[$t]['max'] = -99999999;
			$datt[$t]['min'] = 99999999;
		}
		$round_pt = array(0,0,0,1,0,0, 2,1,1,2);
		$trendKeys = array('wind', 'gust', 'wdir', 'temp', 'humi', 'pres', 'dewp');
		$daytypes = array_flip(array('temp' => 6, 'humi' => 7, 'dewp' => 9, 'rain' => 10, 'pres' => 8, 'wdir' => 5, 'gust' => 4, 'wind' => 3));
		$rntipmm = 0.24; //constant
		$RATE_THRESH = 0.4; //Two tips' worth

		$daymax1 = $daymax2 = -99;
		$nightmin1 = $nightmin2 = $nightmin1T = $nightmin2T = 99;
		$frostMins = 0;
		$lineLength = 11;
		$trends = $rnCums = $rncumArr = array();
		$rncum = $w10 = 0;
		$mins = $maxs = $means = $timesMin = $timesMax = array();

		$windDirs = [];

		$filcust = file(ROOT. "logfiles/daily/" . $procfil . 'log.txt');
		$end = count($filcust); //should be 1440

		for($i = 0; $i < $end; $i++) {
			$custl = explode(',', $filcust[$i]);
			$custmin[$i] = intval($custl[1]);
			$custhr[$i] = intval($custl[0]);

			for($t = 0; $t < $lineLength; $t++) {
				$dat[$t][$i] = floatval($custl[$t]);
				if($t > 5 && $t < 10) {
					$custl[$t] = floatval($custl[$t]);
					// Set max/min, and find _every_ time of max/min
					if($custl[$t] > $datt[$t]['max']) {
						$datt[$t]['max'] = $custl[$t];
						$datt[$t]['timesMax'] = array(mktime($custhr[$i],$custmin[$i]));
					}
					if($custl[$t] === $datt[$t]['max']) {
						$datt[$t]['timesMax'][] = mktime($custhr[$i],$custmin[$i]);
					}
					if($custl[$t] < $datt[$t]['min']) {
						$datt[$t]['min'] = $custl[$t];
						$datt[$t]['timesMin'] = array(mktime($custhr[$i],$custmin[$i]));
					}
					if($custl[$t] === $datt[$t]['min']) {
						$datt[$t]['timesMin'][] = mktime($custhr[$i],$custmin[$i]);
					}
				}
			}

			$feels[$i] = feelsLike($custl[6], $custl[4], $custl[9]);

			//cumulative rain
			if($i > 0) {
				$rnChange = $dat[10][$i] - $dat[10][$i-1];
				// account for potential glitches where rain decreases
				$rncum += ($rnChange > 0) ? $rnChange : 0;
			}
			$rncumArr[$i] = $rncum;

			//Frost hours
			if($custl[6] < 0) {
				$frostMins++;
			}
			//Day max
			if($custhr[$i] >= 9 && $custhr[$i] < 21) {
				if($custl[6] >= $daymax1) { $daymax1 = $custl[6]; $daymaxt1 = mktime($custhr[$i],$custmin[$i]); }
				if($custl[6] > $daymax2) { $daymax2 = $custl[6]; $daymaxt2 = mktime($custhr[$i],$custmin[$i]); }
			}
			//Night Min
			if($custhr[$i] < 9) {
				if($custl[6] <= $nightmin1) { $nightmin1 = $custl[6]; $nightmint1 = mktime($custhr[$i],$custmin[$i]); }
				if($custl[6] < $nightmin2) { $nightmin2 = $custl[6]; $nightmint2 = mktime($custhr[$i],$custmin[$i]); }
			}
			//Night Min Tomorrow
			if($custhr[$i] >= 21) {
				if($custl[6] <= $nightmin1T) { $nightmin1T = $custl[6]; $nightmint1T = mktime($custhr[$i],$custmin[$i]); }
				if($custl[6] < $nightmin2T) { $nightmin2T = $custl[6]; $nightmint2T = mktime($custhr[$i],$custmin[$i]); }
			}
			//Max rain rate
			for($r=1; $r<60; $r++) {
				if($i > $r) {
					$rnr[$i] = $dat[10][$i] - $dat[10][$i-$r];
					if($rnr[$i] > $RATE_THRESH) {
						if($r === 1) { $rr[$i] = 60*$rnr[$i]; }
						else { $rr[$i] = round(60/($r-1)*$rntipmm, 1); }
						break;
					}
				}
			}
			$w10 += $dat[3][$i];
			//10-min trend extremes
			if($i >= 10) {
				$w10 -= $dat[3][$i-10];
				$wind10[$i] = $w10 / 10;
				$rn10[$i] = $dat[10][$i] - $dat[10][$i-10];
				$t10[$i] = $dat[6][$i] - $dat[6][$i-10];
			}
	//		$w60 += $dat[3][$i]/60;
	//		$wind60[$i] = $w60;
			//hour trend extremes
			if($i > 60) {
				$tchangehr[$i] = $dat[6][$i] - $dat[6][$i-60];
				$hchangehr[$i] = $dat[7][$i] - $dat[7][$i-60];
	//			$w60 -= $dat[3][$i-60]/60;
				$rn60[$i] = $dat[10][$i] - $dat[10][$i-60];
			}

			// Wdir
			$dir_quantised = floor(($dat[5][$i] + 11.25) / 22.5) % 16;
			$windDirs[$dir_quantised][floor($dat[3][$i])]++;
		}

		//Trends
		if($end > 400) {
			$rnCums['10m'] = $rncumArr[$end-11];
			for($i = 1; $i <= 361; $i += 60) { //last 1-6hrs rain
				$rnCums[] = $rncumArr[$end-$i];
			}

			$trendLen = count($trendKeys);
			for($i = 1; $i <= 121; $i += 5) {
				for($j = 0; $j < $trendLen; $j++) {
					$trends[$i-1][$trendKeys[$j]] = $dat[$j+3][$end-$i];
				}
				$trends[$i-1]['rain'] = $rncumArr[$end-$i];
			}
		}

		if($daymax1 == -99) {
			$daymax1 = $timesMax['day'] = '-';
		} else {
			$timesMax['day'] = date( 'H:i', ($daymaxt1 + $daymaxt2) / 2 );
		}
		$mins['night'] = $nightmin1;
		$mins['nightTomoz'] = $nightmin1T;
		$maxs['day'] = $daymax1;
		$timesMin['night'] = date( 'H:i', ($nightmint1 + $nightmint2) / 2 );
		$timesMin['nightTomoz'] = date( 'H:i', ($nightmint1T + $nightmint2T) / 2 );

		$maxs['wind'] = max($dat[3]); $timesMax['wind'] = timeFromMM($maxs['wind'], $dat[3], $custhr, $custmin);
		$maxs['gust'] = max($dat[4]); $timesMax['gust'] = timeFromMM($maxs['gust'], $dat[4], $custhr, $custmin);

		$minFeel = min($feels); $timesMin['feel'] = timeFromMM($minFeel, $feels, $custhr, $custmin);
		$maxFeel = max($feels); $timesMax['feel'] = timeFromMM($maxFeel, $feels, $custhr, $custmin);
		$mins['feel'] = round($minFeel, 1);
		$maxs['feel'] = round($maxFeel, 1);

		if(is_array($rn60)) {
			$maxs['rnhr'] = max($rn60); if($maxs['rnhr'] > 0.2) { $timesMax['rnhr'] = timeFromMM($maxs['rnhr'], $rn60, $custhr, $custmin); }
			$maxs['tchangehr'] = max($tchangehr); $timesMax['tchangehr'] = timeFromMM($maxs['tchangehr'], $tchangehr, $custhr, $custmin);
			$maxs['hchangehr'] = max($hchangehr); $timesMax['hchangehr'] = timeFromMM($maxs['hchangehr'], $hchangehr, $custhr, $custmin);
			$tchhr = min($tchangehr); $timesMin['tchangehr'] = timeFromMM($tchhr, $tchangehr, $custhr, $custmin);
			$hchhr = min($hchangehr); $timesMin['hchangehr'] = timeFromMM($hchhr, $hchangehr, $custhr, $custmin);
			$mins['tchangehr'] = -1 * $tchhr;
			$mins['hchangehr'] = -1 * $hchhr;

		}
		if(is_array($t10)) {
			$w10max = max($wind10); $timesMax['w10m'] = timeFromMM($w10max, $wind10, $custhr, $custmin);
			$maxs['w10m'] = round($w10max, 1);
			$maxs['rn10'] = max($rn10); if($maxs['rn10'] > 0.2) { $timesMax['rn10'] = timeFromMM($maxs['rn10'], $rn10, $custhr, $custmin); }
			$t10min = min($t10); $timesMin['tchange10'] = timeFromMM($t10min, $t10, $custhr, $custmin);
			$mins['tchange10'] = -1 * $t10min;
			$maxs['tchange10'] = max($t10); $timesMax['tchange10'] = timeFromMM($maxs['tchange10'], $t10, $custhr, $custmin);
		}
		if(is_array($rr)) {
			$maxs['rate'] = max($rr);
			$timesMax['rate'] = timeFromMM($maxs['rate'], $rr, $custhr, $custmin);
			$maxs['rate'] = $maxs['rate'];
		}
		for($t = 6; $t < 10; $t++) {
			// Time of max/min is the mean time of the longest continuous period at that value
			$timesMin[$daytypes[$t]] = date('H:i', midpoint_of_longest($datt[$t]['timesMin'], 120));
			$timesMax[$daytypes[$t]] = date('H:i', midpoint_of_longest($datt[$t]['timesMax'], 120));
			$mins[$daytypes[$t]] = $datt[$t]['min'];
			$maxs[$daytypes[$t]] = $datt[$t]['max'];
			$means[$daytypes[$t]] = round( mean($dat[$t]), $round_pt[$t] );

			if($end > 61) {
				$hrChanges[$daytypes[$t]] = $dat[$t][$end-1] - $dat[$t][$end-61];
				$hr24Changes[$daytypes[$t]] = $dat[$t][$end-1] - $dat[$t][1];
			}
		}

		$hrChanges['wind'] = $dat[3][$end-1] - $dat[3][$end-61];
		$hr24Changes['wind'] = $dat[3][$end-1] - $dat[3][1];

		$means['wind'] = round(mean($dat[3]), 1);
		$means['w10m'] = round(mean($wind10), 1);
		$means['wdir'] = wdirMean($dat[5], $dat[3]);
		$means['feel'] = round(mean($feels), 1);
		$means['rain'] = $rncum;
		if($means['rain'] < 0.2) {
			$maxs['rnhr'] = $maxs['rn10'] = null;
		}
		$rnCums[0] = $rncum;

		//rain duration
		if($rncum > 0 && $rnCums[0] - $rnCums[1] != 0) {
			$duration = 0;
			$lastTip = 1;
			for($i = 0; $i < $end; $i++) {
				if($rncumArr[$end-$i-1] == $rncumArr[$end-$i-2]) {
					$lastTip++;
				} else {
					$duration += $lastTip;
					$lastTip = 1;
				}
				if($lastTip >= 60) {
					break;
				}
			}
		}

		//wet hours rough estimate
		$wetmins = 0;
		if($rncum > 0) {
			$notRained = 0;
			$raining = false;
			for($i = 1; $i < $end-1; $i++) {
				$notRained++;
				if($rncumArr[$i] != $rncumArr[$i+1]) {
					$notRained = 0;
					$raining = true;
				}
				if($raining) {
					$wetmins++;
				}
				if($notRained > 30) {
					$raining = false;
				}
			}
		}
		$wethrs = ceil($wetmins / 60);

		//current rain rate guess (based on last rain tip - so inaccurate when tipped after long break -> revert to max rate
		if($rnCums[0] - $rnCums[1] != 0) {
			$last = 60;
			for($i = 1; $i < 61; $i++) {
				if($rncumArr[$end-$i-1] != $rncum) {
					$last = $i;
					break;
				}
			}
			$tipQuantity = ($last === 1) ? round(($rncum - $rncumArr[$end-2])/$rntipmm) : 1;
			$currRateGuess = round(60/$last*$rntipmm*$tipQuantity, 1);
			$currRate = ($currRateGuess > $maxs['rate']) ? $maxs['rate'] : $currRateGuess;
		} else {
			$currRate = 0;
		}

		if($procfil == date('Ymd')) {
			//last rain
			$prevRnOld = file_get_contents("lastrn");
			if($rncum > 0) {
				//Only look at recent values, since this script is meant to be run every minute anyway,
				// so in ideal conditions only really need to check most recent two rnCumArr values.
				//Also, this fixes an awkward bug that presents itself 24hrs after rain, ie. in rnCumArr[0] territory,
				// so it is best to avoid this
				$limitRnLook = 300;
				for($i = 1; $i < $limitRnLook; $i++) {
					if($rncumArr[$end-$i-1] != $rncum) {
						$prevRn = mktime($custhr[$end-1], $custmin[$end-1] - $i, 0);
						if($prevRn != $prevRnOld) {
							file_put_contents("lastrn", $prevRn);
						}
						break;
					}
				}
				if($i === $limitRnLook) {
					$prevRn = $prevRnOld;
				}
			} else {
				$prevRn = $prevRnOld;
			}

			$diff = time() - $prevRn;
			$ago = secsToReadable($diff);
			$dateAgo = date('jS M', $prevRn);
			if(date('Ymd') == date('Ymd', $prevRn)) {
				$dateAgo = 'Today';
			} elseif(date('Ymd', mkdate(date('n'), date('j')-1)) == date('Ymd', $prevRn)) {
				$dateAgo = 'Yesterday';
			}
			$lastRnFull = acronym(date('H:i ', $prevRn) .' '. $dateAgo, $ago . ' ago', true);
		}

		//maxhr gust
		$maxhrgst = 0;
		for($i = 1; $i <= 60; $i++) {
			if($dat[4][$end-$i] > $maxhrgst) {
				$maxhrgst = $dat[4][$end-$i];
			}
		}

		// Pond temp
		if($procfil === "today" || $procfil == date('Ymd')) {
			$fildatm = file(ROOT."datm". Date::$yr_yest .".csv");
			$last_line_raw = $fildatm[count($fildatm) - 1];
			$last_line = split(',', $last_line_raw);
			$pond_temp = $last_line[12];
		} else {
			$pond_temp = null;
		}

		$frosthrs = round($frostMins / 60, (int)($frostMins < 10) + 1);
		$rnDuration = roundToDp($duration / 60, 1);

		return array("min" => $mins, "max" => $maxs, "mean" => $means, "timeMin" => $timesMin, "timeMax" => $timesMax,
					"trend" => $trends, "trendRn" => $rnCums, "changeHr" => $hrChanges, "changeDay" => $hr24Changes,
					"misc" => array("frosthrs" => $frosthrs, "rnrate" => $currRate, "rnduration" => $rnDuration,
									"rnlast" => $lastRnFull, "wethrs" => $wethrs, "maxhrgst" => $maxhrgst, "cnt" => $end,
									"prevRn" => date('r', $prevRn), "prevRnOld" => date('r', $prevRnOld),
									"pondTemp" => $pond_temp
								),
					"windDirs" => $windDirs
				);
	}

	public static function timeFromMM($mm, $arr, $hrs, $mins) {
		$line = array_search($mm, $arr);
		return zerolead($hrs[$line]).':'.zerolead($mins[$line]);
	}

	public static function midpoint_of_longest($arr, $max_gap) {
		$curr_period = 0;
		$longest_period = 0;
		$longest_p_end = 0;
		$arrlen = count($arr);
		$arr[-1] = $arr[0];

		for($i = 0; $i < $arrlen; $i++) {
			if(abs($arr[$i] - $arr[$i - 1]) > $max_gap) {
				$curr_period = 0;
			}
			$curr_period++;
			if($curr_period > $longest_period) {
				$longest_period = $curr_period;
				$longest_p_end = $i;
			}
		}
		return $arr[$longest_p_end - floor($longest_period / 2)];
	}
}

?>