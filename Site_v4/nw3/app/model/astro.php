<?php
namespace nw3\app\model;

use nw3\app\util\Date;
use nw3\app\util\Time;
use nw3\app\util\String;

/**
 * Calculating the various sun/moon properties, using both WD tags and
 * native techniques
 *
 * @author Ben LR
 */
class Astro {

	private $vars = array();

	function __construct() {
		include __DIR__ .'/../../data/live/rare.php';

		$this->day_length = D_sunset - D_sunrise;

		$this->moonrise = $moonrise;
		$this->moonset = $moonset;
		$this->moonphasename = str_replace(' Moon', '', $moonphasename);
		$this->illumination = $moonphase;

		$illumination = (int)$moonphase / 100;
		$age_fraction = String::contains('axing', $moonphasename) ?
			($illumination / 2) : //First half of phase
			1 - ($illumination / 2);
		$this->moon_img_num = round($age_fraction * 12);

		$this->moon_phase_dates = array(
			'first' => $firstquarter,
			'full' => $fullmoon,
			'last' => $lastquarter,
			'new' => $nextnewmoon
		);

		$this->suntransit = substr($suntransit, 0, 5);

		list($this->twirise, $this->twiset) = Date::get_rise_set(6);
		list($yest_rise, $yest_set) = Date::get_rise_set(0, D_yest);
		list($summer_rise, $summer_set) = Date::get_rise_set(0, Date::mkdate(6, 21));
		list($winter_rise, $winter_set) = Date::get_rise_set(0, Date::mkdate(12, 21));

		$this->day_length_change = $this->day_length - ($yest_set - $yest_rise);
		$this->day_length_change_summer = $this->day_length - ($summer_set - $summer_rise);
		$this->day_length_change_winter = $this->day_length - ($winter_set - $winter_rise);

		$this->marchequinox = $marchequinox;
		$this->junesolstice = $junesolstice;
		$this->sepequinox = $sepequinox;
		$this->decsolstice = $decsolstice;
		$this->suneclipse = $suneclipse;

		$moon_age_split = preg_split('/[^0-9]+/', $moonage);
		$this->moon_age = $moon_age_split[1]*86400 + $moon_age_split[2]*3600;

		$this->moontransit = substr($moontransit, 0, 5);
		$this->mooneclipse = $mooneclipse;
		$this->moonperigee = $moonperigee;
		$this->moonapogee = $moonapogee;


//		$stamps = array(0,1, 59, 60, 800, 3599, 3600,
//			8000, 86399, 86400, 100000, 1000000, 10000000);
//		foreach ($stamps as $stamp) {
//			echo "$stamp: ". Time::pretty_duration($stamp). '<br>';
//			echo "-$stamp: ". Time::pretty_duration(-$stamp). '<br>';
//		}

	}

	public function __set($key, $val) {
		$this->vars[$key] = $val;
	}

	public function __get($var) {
		if (array_key_exists($var, $this->vars)) {
			return $this->vars[$var];
		}
		return 'NULL - NO SUCH PROPERTY';
	}
}

?>
