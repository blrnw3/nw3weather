<?php
namespace nw3\app\model;

use \nw3\app\util\String;
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
	 * summable: quantity
	 * anomable: quantity has an anomaly
	 * imperial_devisor: float
	 * @var Variable_Properties
	 */
	public static $_ = array(
		self::Temperature => array(
			'precision' => 1, #Number of decimal places to show
			'summable' => false, #Can be summed
			'anomable' => true, #Whether a corresponding climate variable exists (i.e. an anomaly can be computed
			'name' => 'temperature', #unique identifier
			'imperial_divisor' => 0.555556
		),
		self::Rain => array(
			'summable' => true,
			'anomable' => true,
			'name' => 'rain',
			'imperial_divisor' => 25.4
		),
		self::Pressure => array(
			'summable' => false,
			'anomable' => false,
			'name' => 'pressure',
			'imperial_divisor' => 33.864
		),
		self::Wind => array(
			'precision' => 1,
			'summable' => false,
			'anomable' => true,
			'name' => 'wind',
			'imperial_divisor' => 1
		),
		self::Humidity => array(
			'precision' => 0,
			'unit' => '%',
			'summable' => false,
			'anomable' => false,
			'name' => 'humidity',
			'imperial_divisor' => 1
		),
		self::Snow => array(
			'precison_increase_threshold' => 1,
			'summable' => true,
			'anomable' => true,
			'name' => 'snow',
			'imperial_divisor' => 2.54
		),
		self::Distance => array(
			'precision' => 0,
			'summable' => false,
			'anomable' => false,
			'name' => 'rain_rate',
			'imperial_divisor' => 0.3048
		),
		self::Days => array(
			'precision' => 0,
			'unit' => 'days',
			'summable' => true,
			'anomable' => false,
			'name' => 'days',
			'imperial_divisor' => 1
		),
		self::Hours => array(
			'precision' => 0,
			'precison_increase_threshold' => 1,
			'unit' => 'hrs',
			'summable' => true,
			'anomable' => false,
			'name' => 'hours',
			'imperial_divisor' => 1
		),
		self::RainRate => array(
			'precison_decrease_threshold' => 5,
			'summable' => false,
			'anomable' => false,
			'name' => 'rain_rate',
			'imperial_divisor' => 25.4
		),
		self::Direction => array(
			'precision' => 0,
			'unit' => 'degrees',
			'summable' => false,
			'anomable' => false,
			'name' => 'direction',
			'imperial_divisor' => 1
		),
		self::Area => array(
			'summable' => true,
			'anomable' => false,
			'name' => 'area',
			'imperial_divisor' => 1.262
		),
		self::AbsTemp => array(
			'summable' => false,
			'anomable' => false,
			'name' => 'absolute_temperature',
			'imperial_divisor' => 0.55556
		)
	);

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
			return 'null'; //null takes grater precedence than blank
		} elseif(String::isBlank($val) ) {
			return '';
		}
		$var = self::$_[$type];

		//Prettifier preparation
		$value = $abs ? abs((float)$val) : (float)$val;
		$unit = $show_unit ? $var['unit'] : '';
		$sign = $show_sign ? '+' : '';
		$precision = $var['precision'] + $dpa;

		//Special case handling
		if(array_key_exists('precison_increase_threshold', $var) && $value < $var['precison_increase_threshold'] && $value > 0) {
			$precision++;
		} elseif(array_key_exists('precison_decrease_threshold', $var) && $value > $var['precison_decrease_threshold']) {
			$precision--;
		}
		if($type === self::Days && $value === 1) {
			$unit = 'day';
		}

		//Actual conversion
		if(Units::$is_us) {
			$value /= $var['imperial_divisor'];
			if($type === self::Temperature) { $value += 32; }
		} elseif(Units::$is_eu && $type === self::Wind) {
			$value *= 1.6093;
		}

		//Format
		$dpf = round($precision);
		$strret = $sign.'.'. $dpf.'f ';
		return sprintf("%$strret", $value).$unit;
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


	public static $live = array(
		'temp' => array(
			'name' => 'Temperature',
			'group' => 'Temperature',
			'round' => 1,
			'minmax' => true //Has a sensible maximum and minimum
		),
		'humi' => array(
			'name' => 'Humidity',
			'round' => 0,
			'minmax' => true
		),
		'pres' => array(
			'name' => 'Pressure',
			'round' => 0,
			'minmax' => true
		),
		'rain' => array(
			'name' => 'Rainfall',
			'round' => 1
		),
		'wind' => array(
			'name' => 'Wind Speed',
			'round' => 1,
			'maxonly' => true
		),
		'gust' => array(
			'name' => 'Wind Gust',
			'round' => 1,
			'maxonly' => true
		),
		'wdir' => array(
			'name' => 'Wind Direction',
			'round' => 0
		),
		'dewp' => array(
			'name' => 'Dew Point',
			'round' => 1,
			'minmax' => true
		),
		'feel' => array(
			'name' => 'Feels Like',
			'round' => 0,
			'minmax' => true
		)
	);


	public static $daily = array(
		'tmin' => array(
			'description' => 'Minimum Temperature',
			'group' => self::Temperature,
			'colour' => '#FFD750' #for graphs
		),
		'tmax' => array(
			'description' => 'Maximum Temperature',
			'group' => self::Temperature,
			'colour' => 'orange'
		),
		'tmean' => array(
			'description' => 'Mean Temperature',
			'group' => self::Temperature,
			'colour' => 'tan3'
		),

		'hmin' => array(
			'description' => 'Minimum Humidity',
			'group' => self::Humidity,
			'colour' => 'chartreuse'
		),
		'hmax' => array(
			'description' => 'Maximum Humidity',
			'group' => 'humidity',
			'colour' => 'darkolivegreen'
		),
		'hmean' => array(
			'description' => 'Mean Humidity',
			'group' => 'humidity',
			'colour' => 'chartreuse3'
		),

		'pmin' => array(
			'description' => 'Minimum Pressure',
			'group' => 'pressure',
			'colour' => 'darkorchid4'
		),
		'pmax' => array(
			'description' => 'Maximum Pressure',
			'group' => 'pressure',
			'colour' => 'orchid1'
		),
		'pmean' => array(
			'description' => 'Mean Pressure',
			'group' => 'pressure',
			'colour' => 'purple'
		),

		'wmean' => array(
			'description' => 'Mean Wind Speed',
			'group' => 'wind',
			'colour' => 'red'
		),
		'wmax' => array(
			'description' => 'Maximum Wind Speed',
			'group' => 'wind',
			'colour' => 'firebrick1'
		),
		'gust' => array(
			'description' => 'Maximum Gust Speed',
			'group' => 'wind',
			'colour' => 'firebrick2'
		),
		'wdir' => array(
			'description' => 'Mean Wind Direction',
			'group' => 'wind',
			'colour' => 'firebrick3'
		),

		'rain' => array(
			'description' => 'Rainfall',
			'group' => 'rain',
			'colour' => 'royalblue'
		),
		'hrmax' => array(
			'description' => 'Maximum Hourly Rain',
			'group' => 'rain',
			'colour' => 'royalblue1'
		),
		'10max' => array(
			'description' => 'Maximum 10-min Rain',
			'group' => 'rain',
			'colour' => 'royalblue2'
		),
		'rate' => array(
			'description' => 'Maximum Rain Rate',
			'group' => 'rain',
			'colour' => 'royalblue3'
		),

		'dmin' => array(
			'description' => 'Minimum Dew Point',
			'group' => 'dew',
			'colour' => 'darkseagreen'
		),
		'dmax' => array(
			'description' => 'Maximum Dew Point',
			'group' => 'dew',
			'colour' => 'darkslategray'
		),
		'dmean' => array(
			'description' => 'Mean Dew Point',
			'group' => 'dew',
			'colour' => 'darkseagreen4'
		),

		't24min' => array(
			'description' => '24hr Min Temperature (00-00)',
			'group' => 'temperature',
			'colour' => 'darkseagreen4'
		),

	);

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


}
?>
