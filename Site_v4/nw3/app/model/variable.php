<?php
namespace nw3\app\model;

use nw3\app\util\String;
use nw3\app\core\Units;

/*
 * Read-only, static, fixed properties of weather variables
 */
abstract class Variable {

	const None = 0;
	const Temperature = 1;
	const Rain = 2;
	const Pressure = 3;
	const Wind = 4;
	const Humidity = 5;
	const Snow = 6;
	const Distance = 7;
	const Days = 8;
	const Hours = 9;
	const Area = 10;
	const AbsTemp = 11;
	const RainRate = 12;
	const Direction = 13;

	/**
	 * A variable (daily, monthly etc.) can belong to a group and thus inherit
	 * common properties such as units. Also enables view-based grouping
	 * Description of properties is found as inline comments on first variable
	 * precision_increase_threshold: Increment precision for values between 0 and this
	 * @var Variable_Properties
	 */
	public static $_ = array(
		self::Temperature => array(
			'name' => 'temperature', #unique identifier
			'precision' => 1, #Number of decimal places to show
			'summable' => false, #Can be summed
			'imperial_divisor' => 0.555556, #Conversion factor to imperial
			'round_size' => 5, #For intelligent auto-scale of charts
			'thresholds_day' => array(-5,0,5, 10,15,20, 25,30,35), #For value-based colour banding
			'threshold_colours' => array('00c','035cb5','10afe4', '00ffc4','07ea57','c8fb11', 'fd0','f93','f65a17', 'da103c'), #Colours of those thresholds
			'threshold_txtcolours' => array('fff','fff',false, false,false,false, false,false,'fff', 'fff'), #Text colour (false == inherit)
		),
		self::Rain => array(
			'name' => 'rain',
			'precision' => 1,
			'summable' => true,
			'imperial_divisor' => 25.4,
			'round_size' => 5,
			'thresholds_day' => array(0.1,0.2,0.6, 1,2,5, 10,15,20, 25,40),
			'thresholds_month' => array(0.1,1,10, 15,25,35, 50,75,100, 125,150),
			'threshold_colours' => array('94939a','918aa7','cff', '9fc','9edffd','9aacff', '7980ff','3f48f9','010efe', '050eab','050d97','0b0b3b'),
			'threshold_txtcolours' => array(false,false,false, false,false,false, false,'fff','fff', 'fff','fff','fff')
		),
		self::Pressure => array(
			'name' => 'pressure',
			'precision' => 1,
			'summable' => false,
			'imperial_divisor' => 33.864,
			'round_size' => 10,
			'thresholds_day' => array(970,980,990, 1000,1010,1015, 1020,1030,1040),
			'threshold_colours' => array('11c','05b','1ad', '0ec','1e5','cf1', 'fe0','fa4','f62', 'd14'),
			'threshold_txtcolours' => array('fff','fff',false, false,false,false, false,false,'fff', 'fff')
		),
		self::Wind => array(
			'name' => 'wind',
			'precision' => 1,
			'summable' => false,
			'imperial_divisor' => 1,
			'round_size' => 5,
			'thresholds_day' => array(1,2,4, 7,10,15, 20,30,40),
			'threshold_colours' => array('d9fdfc','aff','6f9', '9f0','9c0','cc0', 'fc0','f90','f60', 'f00'),
			'threshold_txtcolours' => array(false,false,false, false,false,false, false,false,false, 'fff')
		),
		self::Humidity => array(
			'name' => 'humidity',
			'precision' => 0,
			'unit' => '%',
			'summable' => false,
			'imperial_divisor' => 1,
			'round_size' => 10,
			'thresholds_day' => array(30,40,50, 60,70,80, 90,98),
			'threshold_colours' => array('f3e2a9','f7d358','fb0', 'd7df01','a5df00','74df00', '31b404','329511','0b6121'),
			'threshold_txtcolours' => array(false,false,false, false,false,false, false,false, 'C4C9C2')
		),
		self::Snow => array(
			'name' => 'snow',
			'precision' => 0,
			'precison_increase_threshold' => 1,
			'summable' => true,
			'manual' => true, #Manual obs
			'imperial_divisor' => 2.54
		),
		self::Distance => array(
			'precision' => 0,
			'summable' => false,
			'name' => 'distance',
			'imperial_divisor' => 0.3048
		),
		self::Days => array(
			'name' => 'days',
			'precision' => 0,
			'unit' => 'days',
			'summable' => true,
			'imperial_divisor' => 1,
			'thresholds_month' => array(2,3,5, 7,10,12, 15,20,25, 30),
			'threshold_colours' => array('888','f5e2a9','f1d787', 'f2c181','f4b462','e9a245', 'f2a86b','ee9348','dc7b2b', 'eb8965','e46b3f','d85a3a'),
			'threshold_txtcolours' => array(false,false,false, false,false,false, false,false,false, false,'fff','fff')
		),
		self::Hours => array(
			'name' => 'hours',
			'precision' => 0,
			'precison_increase_threshold' => 1,
			'unit' => 'hrs',
			'summable' => true,
			'manual' => true,
			'imperial_divisor' => 1,
			'thresholds_day' => array(0.1,0.3,0.5, 1,2,3, 5,7,9, 12,15),
			'thresholds_month' => array(1,5,10, 25,50,75, 100,125,150, 200,250),
			'threshold_sum_coeff_month' => 20,
			'threshold_colours' => array('888','f5e2a9','f1d787', 'f2c181','f4b462','e9a245', 'f2a86b','ee9348','dc7b2b', 'eb8965','e46b3f','d85a3a'),
			'threshold_txtcolours' => array(false,false,false, false,false,false, false,false,false, false,'fff','fff')
		),
		self::RainRate => array(
			'name' => 'rain_rate',
			'precison_increase_threshold' => 5,
			'summable' => false,
			'imperial_divisor' => 25.4,
			'thresholds_day' => array(0.3,1,2, 3,5,10, 30,60,100, 150,300),
			'threshold_colours' => array('94939a','918aa7','cff', '99ffcc','9edffd','9aacff', '7980ff','3f48f9','010efe', '050eab','050d97','0b0b3b'),
			'threshold_txtcolours' => array(false,false,false, false,false,false, false,'fff','fff', 'fff','fff','fff')
		),
		self::Direction => array(
			'name' => 'direction',
			'precision' => 0,
			'unit' => 'degrees',
			'summable' => false,
			'imperial_divisor' => 1,
			'round_size' => 20,
			'thresholds_day' => array(45,90,135, 180,225,270, 315),
			'threshold_colours' => array('f6cece','f6e3ce','f5f6ce', 'e3f6ce','cef6d8','ced8f6', 'ceb3fa','f6cee3'),
			'threshold_txtcolours' => array(false,false,false, false,false,false, false,false)
		),
		self::Area => array(
			'name' => 'area',
			'summable' => true,
			'imperial_divisor' => 1.262
		),
		self::AbsTemp => array(
			'name' => 'absolute_temperature',
			'summable' => false,
			'imperial_divisor' => 0.55556,
			'round_size' => 5,
			'precision' => 1,
			'thresholds_day' => array(0.5,1,2, 2,3,5, 7,10,15),
			'threshold_colours' => array('00c','035cb5','10afe4', '00ffc4','07ea57','c8fb11', 'fd0','f93','f65a17', 'da103c'),
			'threshold_txtcolours' => array('fff','fff',false, false,false,false, false,false,'fff', 'fff')
		)
	);

	/**
	 * Live variables (continuous observation)
	 * Properties are inherited from core, but can be overriden
	 * @var type
	 */
	public static $live = array(
		'temp' => array(
			'name' => 'Temperature',
			'group' => self::Temperature,
			'colour' => 'orange',
			'minmax' => true //Has a sensible maximum and minimum
		),
		'humi' => array(
			'name' => 'Humidity',
			'group' => self::Humidity,
			'colour' => 'darkgreen',
			'minmax' => true
		),
		'pres' => array(
			'name' => 'Pressure',
			'group' => self::Pressure,
			'colour' => 'darkred',
			'minmax' => true
		),
		'rain' => array(
			'name' => 'Rainfall',
			'group' => self::Rain,
			'colour' => 'blue'
		),
		'wind' => array(
			'name' => 'Wind Speed',
			'group' => self::Wind,
			'colour' => 'darkblue',
			'maxonly' => true
		),
		'gust' => array(
			'name' => 'Wind Gust',
			'group' => self::Wind,
			'precision' => 0,
			'colour' => '#822',
			'maxonly' => true
		),
		'wdir' => array(
			'name' => 'Wind Direction',
			'group' => self::Direction,
			'colour' => 'red'
		),
		'dewp' => array(
			'name' => 'Dew Point',
			'group' => self::Temperature,
			'anomable' => false,
			'colour' => 'chartreuse',
			'minmax' => true
		),
		'feel' => array(
			'name' => 'Feels Like',
			'group' => self::Temperature,
			'anomable' => false,
			'colour' => 'paleblue',
			'minmax' => true
		)
	);

	public static $daily = array(
		'tmin' => array(
			'description' => 'Minimum Temperature',
			'anomable' => true, #Anomaly calculations possible
			'group' => self::Temperature, #Inheritance of properties,
			'category' => 'Temperature', #Practically, e.g. for use in drop-down grouping
			'colour' => '#33f' #for graphs
		),
		'tmax' => array(
			'description' => 'Maximum Temperature',
			'group' => self::Temperature,
			'category' => 'Temperature',
			'anomable' => true,
			'colour' => 'orange'
		),
		'tmean' => array(
			'description' => 'Mean Temperature',
			'group' => self::Temperature,
			'category' => 'Temperature',
			'anomable' => true,
			'colour' => '#aae'
		),

		'hmin' => array(
			'description' => 'Minimum Humidity',
			'group' => self::Humidity,
			'category' => 'Humidity',
			'colour' => 'chartreuse'
		),
		'hmax' => array(
			'description' => 'Maximum Humidity',
			'group' => self::Humidity,
			'category' => 'Humidity',
			'colour' => 'darkolivegreen'
		),
		'hmean' => array(
			'description' => 'Mean Humidity',
			'group' => self::Humidity,
			'category' => 'Humidity',
			'colour' => '#283'
		),

		'pmin' => array(
			'description' => 'Minimum Pressure',
			'group' => self::Pressure,
			'category' => 'Pressure',
			'colour' => '#fab'
		),
		'pmax' => array(
			'description' => 'Maximum Pressure',
			'group' => self::Pressure,
			'category' => 'Pressure',
			'colour' => '#e9a'
		),
		'pmean' => array(
			'description' => 'Mean Pressure',
			'group' => self::Pressure,
			'category' => 'Pressure',
			'colour' => 'purple'
		),

		'wmean' => array(
			'description' => 'Mean Wind Speed',
			'group' => self::Wind,
			'category' => 'Wind',
			'anomable' => true,
			'colour' => 'red'
		),
		'wmax' => array(
			'description' => 'Maximum Wind Speed',
			'group' => self::Wind,
			'category' => 'Wind',
			'colour' => '#f33'
		),
		'gust' => array(
			'description' => 'Maximum Gust Speed',
			'group' => self::Wind,
			'category' => 'Wind',
			'colour' => '#f44'
		),
		'wdir' => array(
			'description' => 'Mean Wind Direction',
			'group' => self::Direction,
			'nosummary' => true,
			'category' => 'Wind',
			'colour' => '#f77'
		),

		'rain' => array(
			'description' => 'Rainfall',
			'group' => self::Rain,
			'category' => 'Rainfall',
			'anomable' => true,
			'colour' => 'royalblue'
		),
		'hrmax' => array(
			'description' => 'Maximum Hourly Rain',
			'group' => self::Rain,
			'category' => 'Rainfall',
			'summable' => false,
			'colour' => '#55e'
		),
		'r10max' => array(
			'description' => 'Maximum 10-min Rain',
			'group' => self::Rain,
			'category' => 'Rainfall',
			'summable' => false,
			'colour' => '#77e'
		),
		'ratemax' => array(
			'description' => 'Maximum Rain Rate',
			'group' => self::RainRate,
			'category' => 'Rainfall',
			'colour' => '#99f',
			'precision' => 0,
			'precison_increase_threshold' => 5
		),

		'dmin' => array(
			'description' => 'Minimum Dew Point',
			'group' => self::Temperature,
			'category' => 'Dew Point',
			'colour' => 'darkseagreen'
		),
		'dmax' => array(
			'description' => 'Maximum Dew Point',
			'group' => self::Temperature,
			'category' => 'Dew Point',
			'colour' => 'darkslategray'
		),
		'dmean' => array(
			'description' => 'Mean Dew Point',
			'group' => self::Temperature,
			'category' => 'Dew Point',
			'colour' => '#2a3'
		),

		't24min' => array(
			'description' => '24hr Min Temperature (00-00)',
			'group' => self::Temperature,
			'category' => 'Other',
			'colour' => '#227'
		),

		'trange' => array(
			'description' => '24hr Temperature Range (09-09)',
			'group' => self::AbsTemp,
			'category' => 'Other',
			'anomable' => true,
			'colour' => 'green'
		),

		'sunhr' => array(
			'description' => 'Sun Hours',
			'group' => self::Hours,
			'category' => 'Observation',
			'anomable' => true,
			'colour' => '#ff3'
		),
		'wethr' => array(
			'description' => 'Wet Hours',
			'group' => self::Hours,
			'category' => 'Observation',
			'anomable' => true,
			'colour' => 'aqua'
		),

		'rdays' => array(
			'description' => 'Days of Rainfall',
			'group' => self::Days,
			'colour' => '#57d'
		),
		'days_frost' => array(
			'description' => 'Days of AirFrost',
			'group' => self::Days,
			'colour' => '#33f'
		),
		'days_storm' => array(
			'description' => 'Days of Thunder',
			'group' => self::Days,
			'colour' => '#ee5'
		),
		'days_snow' => array(
			'description' => 'Days of Lying Snow',
			'group' => self::Days,
			'colour' => '#4ad'
		),
		'days_snowfall' => array(
			'description' => 'Days of Falling Snow',
			'group' => self::Days,
			'colour' => '#5cf'
		),
		'sunmax' => array(
			'description' => 'Maximum Possible Sunshine',
			'group' => self::Hours,
			'colour' => '#883'
		),

	);

	public static function initialise() {
		$skip_keys = array('threshold_colours', 'threshold_txtcolours');
		//Do the inheritance (selective merge)
		foreach (self::$daily as $var_name => $var) {
			self::$daily[$var_name]['id'] = $var_name;

			$group = self::$_[$var['group']];
			foreach ($group as $group_property_name => $group_property) {
				if(!key_exists($group_property_name, $var) && !in_array($group_property_name, $skip_keys)) {
					self::$daily[$var_name][$group_property_name] = $group_property;
				}
			}
		}
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
		//Bad value checking
		if($type === self::None) {
			return $val;
		} elseif( is_null($val) ) {
			return null;
		}
		if(!key_exists($type, self::$daily)) {
			return "WARNING! '$type' is not a valid type";
		}
		//Special cases
		if($type === 'wdir') {
			return self::degname((int)$val);
		}
		$var = self::$daily[$type];

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
		if($var['group'] === self::Days && $value === 1) {
			$unit = 'day';
		}

		//Actual conversion
		if(Units::$is_us) {
			$value /= $var['imperial_divisor'];
			if($var['group'] === self::Temperature) { $value += 32; }
		} elseif(Units::$is_eu && $var['group'] === self::Wind) {
			$value *= 1.6093;
		}

		//Format
		$dpf = round($precision);
		$strret = $sign.'.'. $dpf.'f';
		return sprintf("%$strret", $value).$unit;
	}

	/**
	 * Returns class name of a vriable, based on its group
	 * @param type $var_name
	 * @return string class name
	 */
	static function get_class($var_name) {
		if(key_exists($var_name, self::$daily)) {
			$var = self::$daily[$var_name];
			return $var_name .' g_'. self::$_[$var['group']]['name'];
		} else {
			return 'unknown_var';
		}
	}

	/**
	 * Checks whether a variable name is valid
	 * @return boolean
	 */
	static function is_valid($var_name) {
		return key_exists($var_name, self::$daily);
	}


	/** Graph data must be indexed from 0 and clean, but converted */
	public static function clean_data($data, $type) {
		$values = array();
		foreach ($data as $val) {
			$values[] = (float) self::conv($val, self::$daily[$type]['id'], false, false, 1);
		}
		return $values;
	}

	/**
	 * Computes the appropriate feels-like temperature for given input
	 * @param float $t temperature
	 * @param float $v wind speed
	 * @param float $d dew point
	 * @return float the feels-like temp in degC
	 */
	static function feelsLike($t, $v, $d) {
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
	static function dewPoint($t, $h) {
		//http://en.wikipedia.org/wiki/Dew_point
		$gamma = (17.271*$t) / (237.7+$t) + log($h/100);
		return (237.7*$gamma) / (17.271-$gamma);
	}



	static function assign_units($units) {
		foreach ($units as $var => $unit) {
			self::$_[$var]['unit'] = $unit;
		}
	}
	static function assign_precisions($precisions) {
		foreach ($precisions as $var => $precision) {
			self::$_[$var]['precision'] = $precision;
		}
	}

	static function hailname($val) {
		$types = array('No', 'Small', 'Med', 'Large');
		return $types[$val];
	}
	static function thundername($val) {
		$types = array('N', 'Y', 'Light', 'Mod', 'Sevr');
		return $types[$val];
	}
	static function degname($winddegree) {
		$windlabels = array ("N","NNE", "NE", "ENE", "E", "ESE", "SE", "SSE", "S", "SSW","SW", "WSW", "W", "WNW", "NW", "NNW","N");
		return $windlabels[ round($winddegree / 22.5, 0) ];
	}

}
?>
