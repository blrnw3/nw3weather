<?php
namespace nw3\app\core;

use nw3\app\core\Session;

/**
 * Handles units for all physical variables (weather mostly)
 */
class Units {

	static $degs = 'degrees';
	static $humi = '%';
	static $temp;
	static $rain;
	static $wind;
	static $pres;
	static $snow;
	static $length;
	static $area;
	static $rate;

	static $is_eu = false;
	static $is_us = false;
	static $is_uk = false;

	/**
	 * Assigns the core units based on session/cookie/default (in that order)
	 */
	static function initialise() {

		//Unit setting getter/saver
		if (isset($_GET['unit'])) {
			if ($_GET['unit'] == 'US') {
				self::assign_US();
				Session::cookify("SetUnits", "US");
			} elseif ($_GET['unit'] == 'UK') {
				self::assign_UK();
				Session::cookify("SetUnits", "UK");
			} else { //unit is EU
				self::assign_EU();
				Session::cookify("SetUnits", "EU");
			}
		} elseif (isset($_COOKIE['SetUnits'])) {
			if ($_COOKIE['SetUnits'] == 'US') {
				self::assign_US();
			}
			if ($_COOKIE['SetUnits'] == 'UK') {
				self::assign_UK();
			}
			if ($_COOKIE['SetUnits'] == 'EU') {
				self::assign_EU();
			}
		} else { //No cookies or GET - use default (UK) units
			self::assign_UK();
		}

		//Globally-agreed units
		self::$rate = self::$rain . '/h';

		//For data tables and reports
//		$std_units = array($unitT, $unitR, $unitW, $unitP, $unitH, $unitD, $unitRR, 'hrs', 'views', 'days', 'shorthand', $unitS);
//		$conv_units = array('', ' &deg;' . $unitT, ' ' . $unitR, ' ' . $unitP, ' ' . $unitW, $unitH, ' ' . $unitS, ' ' . $unitL, ' day', ' hrs', ' ' . $unitA, ' degrees');

	}

	private static function assign_US() {
		self::$area = 'ozft<sup>-3</sup>';
		self::$length = 'ft';
		self::$pres = 'inHg';
		self::$rain = 'in';
		self::$snow = 'in';
		self::$temp = 'F';
		self::$wind = 'mph';
		self::$is_us = true;
	}
	private static function assign_EU() {
		self::$area = 'gm<sup>-3</sup>';
		self::$length = 'm';
		self::$pres = 'mb';
		self::$rain = 'mm';
		self::$snow = 'cm';
		self::$temp = 'C';
		self::$wind = 'kph';
		self::$is_eu = true;
	}
	private static function assign_UK() {
		self::$area = 'gm<sup>-3</sup>';
		self::$length = 'm';
		self::$pres = 'hPa';
		self::$rain = 'mm';
		self::$snow = 'cm';
		self::$temp = 'C';
		self::$wind = 'mph';
		self::$is_uk = true;
	}
}

?>
