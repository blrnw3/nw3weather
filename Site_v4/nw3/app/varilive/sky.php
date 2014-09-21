<?php
namespace nw3\app\varilive;

use nw3\app\model\Store;
use nw3\app\util\File;
use nw3\app\util\String;

/**
 * Sky condition and forecast and stuff
 */
class Sky {

	private $now;

	function __construct() {
		$this->now = Store::g();
	}

	function condition() {
		$METAR = File::live_data('METAR.txt');
		$rnrt = $this->now->hr24->rnrate;
		$nw3Raining = $this->now->mins_since_last_rain() < 60;
		list($weather, $cloud) = $this->metar_weather($METAR);

		if($nw3Raining) { //Rained in past hour, nw3 override
			//Detect showers by 30-min temp/hum change
			$isShower = ( $this->now->change('temp', 30) <= -0.3
				|| $this->now->change('hum', 30) >= 5 );
			$rnType = $isShower ? 'Shower' : 'Rain';

			$intensity = $this->get_intensity($rnrt, $isShower);
			$weather = $intensity .' '. $rnType;
			$cloud = 'Cloudy';
		}
		return [
			'cloud' => $cloud,
			'weather' => $weather,
			'metar' => $METAR,
			'rn_intensity' => $intensity,
			'rn_type' => $rnType
		];
	}

	function forecast() {
		$fcast = File::live_data("WUforecast.txt");
		$icon = 'cloudy';
		$forecastTerms = array('Rain', 'Clear', 'Partly', 'Thunderstorm', 'Snow');
		$forecastIcons = array('rain', 'clear', 'partlycloudy', 'tstorms', 'snow');
		for($i = 0; $i < count($forecastIcons); $i++) {
			if(strpos($fcast, $forecastTerms[$i]) !== false) {
				$icon = $forecastIcons[$i];
				break;
			}
		}
		if($i <= count($forecastIcons) && strpos($fcast, "Chance") !== false) {
			$icon .= '_showers';
		}
		elseif( D_time > D_sunset && ($icon == 'clear' || $icon == 'partlycloudy') ) {
			$icon = 'nt_'. $icon;
		}
		return [
			'icon' => $icon,
			'text' => $fcast
		];
	}

	private function get_intensity($rnrt, $isShower) {
		//Detect intensity based on current rain rate
		//If only 0.3mm, no rate is available (i.e. 0), so give no intensity
		$lastrnThresh = $isShower ? 20 : 35;
		if($this->now->mins_since_last_rain() > $lastrnThresh) {
			return 'Recent';
		}
		$intensities = ['', 'Slight', 'Light', 'Moderate', 'Heavy', 'Very Heavy', 'Torrential'];
		$intensityThresholds = [0.1, 0.5, 2, 8, 35, 60, INT_MAX];
		for ($i = 0; $i < count($intensityThresholds); $i++) {
			if($rnrt < $intensityThresholds[$i]) {
				return $intensities[$i];
			}
		}
	}

	private function metar_weather($METAR) {
		$metarRaining = String::contains($METAR, ['RA','DZ']);
		$foggy = String::contains($METAR, ['FG', 'BR']);
		$snowing = String::contains($METAR, ['SN','SG']);
		$showery = String::contains($METAR, 'SH');
		$stormy = String::contains($METAR, 'TS');

		//cloud
		$cloud = ($metarRaining || $foggy || $snowing) ? 'Cloudy' : 'Clear'; //default
		$METARcloudTypes = ['OVC', 'BKN', 'SCT', 'FEW', 'NSC'];
		$METARcloudDescrips = ['Overcast', 'Mostly cloudy', 'Partly cloudy', 'Mostly clear', 'Cloudy'];
		foreach ($METARcloudTypes as $i => $cloudSrch) {
			if(String::contains($METAR, $cloudSrch)) {
				$cloud = $METARcloudDescrips[$i];
				break;
			}
		}
		if(String::contains($METAR, 'CB')) {
			$cloud .= ', Cumulonimbus observed';
		}

		// weather
		$METARactives = [$snowing, $metarRaining, $foggy, $stormy];
		$METARdescrips = ['Snow', 'Rain', 'Mist/Fog', 'Thunderstorm'];
		$weather = 'Dry'; # Default weather
		foreach ($METARactives as $i => $wxMetar) {
			if($wxMetar) {
				$weather = $METARdescrips[$i];
				if($showery) {
					$weather .= ' Showers';
				}
				$weather .= ' Nearby';
				break;
			}
		}
		return [$weather, $cloud];
	}
}
