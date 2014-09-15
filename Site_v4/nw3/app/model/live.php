<?php
namespace nw3\app\model;

use nw3\config\Station;
use nw3\app\core\Db;
use nw3\app\model\Store;
use nw3\app\util\Date;
use nw3\app\util\File;
use nw3\app\util\String;

/**
 *
 * @author Ben LR
 */
class Live {

	private $now;
	private $today;
	private $hr24;

	function __construct() {
		$this->now = Store::g();
		$this->today = $this->now->today;
		$this->hr24 = $this->now->hr24;
	}

	function condition() {
		$METAR = File::live_data('METAR.txt');
		$rnrt = $this->hr24->rnrate;
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

	function station_stats() {
		$st = strtotime(Station::START_DATE);
		$st_nw3 = strtotime(Station::START_DATE_NW3);
		return [
			'days_running' => (int)((D_now - $st)/Date::secs_DAY),
			'days_running_nw3' => (int)((D_now - $st_nw3)/Date::secs_DAY)
		];
	}

	function monthly_report($mon, $yr) {
		# TODO
		$repFile = ROOT. $yr."/report$mon.php";
		if(!file_exists($repFile)) { //try previous month
			$repStamp = mkdate($mon-1, 1, $yr);
			$repMonth = date('n', $repStamp);
			$repYear = date('Y', $repStamp);
			$repFile = ROOT.$repYear."/report$repMonth.php";
		}
		if(file_exists($repFile)) {
			include $repFile;
		}
		else {
			echo 'Report not available.';
			return null;
		}

		$repMonth = $export['date'][0];
		$repYear = $export['date'][1];

		$tempComparator = $export['temp'][0];
		$tempAv = conv($export['temp'][1], 1);
		$tempAnom = conv($export['temp'][2], 1.1, true, true);
		$tempLo = conv($export['temp'][3], 1);
		$tempHi = conv($export['temp'][4], 1);

		$rainComparator = $export['rain'][0];
		$rainAv = conv($export['rain'][1], 2);
		$rainAnom = $export['rain'][2];
		$rainCnt = $export['rain'][3];
		$rainHi = conv($export['rain'][4], 2);
		$rainYr = conv($export['rain'][5], 2);
		$rainYrAnom = $export['rain'][6];
		$rainYrCnt = $export['rain'][7];

		$sunComparator = $export['sun'][0];
		$sunAv = conv($export['sun'][1], 9);
		$sunAnom = $export['sun'][2];
		$sunMax = $export['sun'][3];
		$sunCnt = $export['sun'][4];
		$sunHi = $export['sun'][5];

		$notWintry = ($export['winter'][0] == 0 && $export['winter'][1] == 0);
		$fallSnow = $export['winter'][2];
		$fallSnowAnom = $export['winter'][3];
		$fallSnowAnom2 = $export['winter'][4];
		$AFsFull = $export['winter'][5];
		$AFavr = $export['winter'][6];
		$lySnow = $export['winter'][7];
		$LSavr = $export['winter'][8];
		$maxDepth = conv($export['winter'][9], 6);

		$hail = $export['other'][0];
		$thunder = $export['other'][1];
		$fog = $export['other'][2];
		$bigRnsFull = $export['other'][3];
		$mm10 = conv($export['other'][4], 2, true, false, -1);
		$bigGusts = $export['other'][5];
		$mph30 = conv($export['other'][6], 4, true, false, -1);

		$output = "<h2>".date('F Y', mkdate($repMonth, 1, $repYear)) ."</h2>
			<dl>
			<dt class='temp'>Temperature</dt>
			<dd>Overall, the month was $tempComparator average, with a mean of <b>$tempAv</b> ($tempAnom from the <abbr title='Long-term average'>LTA</abbr>).
				<br />The absolute low was <b>$tempLo</b>, and the highest <b>$tempHi</b>.
			</dd>
			<dt class='rain'>Rainfall</dt>
			<dd>Came in $rainComparator the long-term average, at <b>$rainAv</b> ($rainAnom%) across <b>$rainCnt</b> days of <abbr title='&gt;0.25mm'>recordable rain</abbr>.
				The most rainfall recorded in a single day (starting at midnight) was <b>$rainHi</b>.
				The cumulative annual total for $repYear now stands at <b>$rainYr</b> ($rainYrAnom%) from <b>$rainYrCnt</b> rain days.
			</dd>
			<dt class='sun'>Sunshine</dt>
			<dd>A $sunComparator month, with <b>$sunAv</b> ($sunAnom%) from a possible $sunMax. <br />
				<b>$sunCnt</b> days had more than a minute of sunshine, the maximum being <b>$sunHi hrs</b>.
			</dd>
			<dt class='snow'>Winter Events</dt>
			<dd>";
		$output .= $notWintry ?
			"No snow or frost observed." :
			"There $fallSnow of falling snow or sleet
			($fallSnowAnom $fallSnowAnom2 the <abbr title='Long-term average'>LTA</abbr>),
				and $AFsFull ($AFavr). <br />
			$lySnow of lying snow at 09z were observed ($LSavr), with a max depth of <b>$maxDepth</b>.
			";
		$output .= "</dd>
			<dt>Other Events</dt>
			<dd>There $hail of hail, <b>$thunder</b> of thunder, <b>$fog</b> with fog at 09z.
				$bigRnsFull had &gt;$mm10 of rain, and <b>$bigGusts</b> with gusts &gt;$mph30.
			</dd>
			</dl>
			<p>
			All long-term <a href='wxaverages.php' title='Long-term NW3 climate averages'>climate averages</a>
			are with respect to the period 1971-2000. &nbsp;
			<a href='wxhistmonth.php'>View full report</a>
			</p>
			";

		echo $output;
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

?>
