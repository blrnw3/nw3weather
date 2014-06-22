<?php
namespace nw3\app\core;

use nw3\app\core\Session;
use nw3\app\model\Variable;

/**
 * Handles units for all physical variables (weather mostly)
 */
class Units {

	public static $names = array('UK', 'EU', 'US');
	public static $names_full = array('UK', 'Metric', 'Imperial');

	static $degs = 'degrees';
	static $humi = '%';
	static $temp;
	static $rain;
	static $wind;
	static $pres;
	static $snow;
	static $distance;
	static $area;
	static $rate;

	static $is_eu = false;
	static $is_us = false;
	static $is_uk = false;

	static $type;

	/**
	 * Assigns the core units based on session/cookie/default (in that order)
	 */
	static function initialise() {
		//Unit setting getter/saver
		if (isset($_GET['unit']) && in_array($_GET['unit'], self::$names)) {
			call_user_func('self::assign_'. $_GET['unit']);
			Session::cookify('unit', $_GET['unit']);
		} elseif (isset($_COOKIE['unit']) && in_array($_COOKIE['unit'], self::$names)) {
			call_user_func('self::assign_'. $_COOKIE['unit']);
		} else { //No cookies or GET - use default (UK) units
			self::assign_UK();
		}

		//Globally-agreed units
		self::$rate = self::$rain . '/h';

		self::assign_to_variables();

		//For data tables and reports
//		$std_units = array($unitT, $unitR, $unitW, $unitP, $unitH, $unitD, $unitRR, 'hrs', 'views', 'days', 'shorthand', $unitS);
//		$conv_units = array('', ' &deg;' . $unitT, ' ' . $unitR, ' ' . $unitP, ' ' . $unitW, $unitH, ' ' . $unitS, ' ' . $unitL, ' day', ' hrs', ' ' . $unitA, ' degrees');

	}

	private static function assign_US() {
		self::$area = 'ozft<sup>-3</sup>';
		self::$distance = 'ft';
		self::$pres = 'inHg';
		self::$rain = 'in';
		self::$snow = 'in';
		self::$temp = 'F';
		self::$wind = 'mph';

		self::$is_us = true;
		self::$type = 'US';
	}
	private static function assign_EU() {
		self::$area = 'gm<sup>-3</sup>';
		self::$distance = 'm';
		self::$pres = 'mb';
		self::$rain = 'mm';
		self::$snow = 'cm';
		self::$temp = 'C';
		self::$wind = 'kph';

		self::$is_eu = true;
		self::$type = 'EU';
	}
	private static function assign_UK() {
		self::$area = 'gm<sup>-3</sup>';
		self::$distance = 'm';
		self::$pres = 'hPa';
		self::$rain = 'mm';
		self::$snow = 'cm';
		self::$temp = 'C';
		self::$wind = 'mph';

		self::$is_uk = true;
		self::$type = 'UK';
	}

	private static function assign_to_variables() {
		$units = array(
			Variable::Temperature => self::$temp,
			Variable::AbsTemp => self::$temp,
			Variable::Rain => self::$rain,
			Variable::RainRate => self::$rate,
			Variable::Pressure => self::$pres,
			Variable::Wind => self::$wind,
			Variable::Snow => self::$snow,
			Variable::Distance => self::$distance,
			Variable::Area => self::$area
		);
		Variable::assign_units($units);

		$precision_adjust = self::$is_us ? 1 : 0;
		$precisions = array(
			Variable::Rain => 1 + $precision_adjust,
			Variable::RainRate => 0 + $precision_adjust,
			Variable::Pressure => 0 + ($precision_adjust * 2),
			Variable::Snow => 0 + $precision_adjust,
			Variable::Area => 2 + $precision_adjust
		);
		Variable::assign_precisions($precisions);
	}
}

?>
