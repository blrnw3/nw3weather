<?php
namespace nw3\app\model;

use nw3\app\core\Db;
use nw3\app\util\File;

/**
 *
 * @author Ben LR
 */
class Live {

	public $metar;

	function __construct() {
		//Load up the data
		$this->metar = File::live_data("METAR.txt");
	}


	function condition() {
		$rnrt = $HR24['misc']['rnrate'];
		$nw3Raining = (($HR24['trendRn'][0] - $HR24['trendRn'][1]) > 0);
		//weather
		$weather = 'Dry'; //default
		if($nw3Raining) { //Rained in past hour
			//Detect showers by 30-min temp/hum change
			$isShower = ( $HR24['trend'][0]['temp'] - $HR24['trend'][30]['temp'] <= -0.3
				|| $HR24['trend'][0]['hum'] - $HR24['trend'][30]['hum'] >= 5 );
			$rnType = $isShower ? 'Shower' : 'Rain';

			//Detect intensity based on current rain rate
			//If only 0.3mm, no rate is available (i.e. 0), so give no intensity
			$intensities = array('', 'Slight', 'Light', 'Moderate', 'Heavy', 'Very Heavy', 'Torrential');
			$intensityThresholds = array(0.1, 0.5, 2, 8, 35, 60, 500);
			for ($i = 0; $i < count($intensityThresholds); $i++) {
				if($rnrt < $intensityThresholds[$i]) {
					$intensity = $intensities[$i];
					break;
				}
			}
			$lastrnThresh = $isShower ? 20 : 35;
			if((int) substr($HR24['misc']['rnlast'], 0, 2) > $lastrnThresh && strpos($HR24['misc']['rnlast'], 'mins')) {
				$intensity = 'Recent';
			}
			$weather = $intensity .' '. $rnType;
		} else { // check the METAR
			$metarRaining = strContains($METAR, array('RA','DZ'));
			$foggy = strContains($METAR, array('FG', 'BR'));
			$snowing = strContains($METAR, array('SN','SG'));
			$showery = strContains($METAR, 'SH');
			$stormy = strContains($METAR, 'TS');

			$METARactives = array($snowing, $metarRaining, $foggy, $stormy);
			$METARdescrips = array('Snow', 'Rain', 'Mist/Fog', 'Thunderstorm');
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
		}
		return $weather;
	}

	function cloud() {

		//cloud
		$cloud = ($nw3Raining || $metarRaining || $foggy || $snowing) ? 'Cloudy' : 'Clear'; //default
		$cumulonimbus = strContains($METAR, array('CB')) ? acronym('Cumulonimbus cloud', 'Cb cloud', true) ." observed" : "";
		$METARcloudTypes = array('OVC', 'BKN', 'SCT', 'FEW', 'NSC');
		$METARcloudDescrips = array('Overcast', 'Mostly cloudy', 'Partly cloudy', 'Mostly clear', 'Cloudy');
		foreach ($METARcloudTypes as $i => $cloudSrch) {
			if(strContains($METAR, $cloudSrch)) {
				$cloud = $METARcloudDescrips[$i];
				break;
			}
		}
		return $cloud;
	}

}

?>
