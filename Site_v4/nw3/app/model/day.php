<?php
namespace nw3\app\model;

use nw3\app\model\Variable;
use nw3\app\util\Maths;
use nw3\app\util\Time;
use nw3\app\util\Date;
use nw3\app\util\Html;
use nw3\app\util\File;
use nw3\config\Station;

/**
 *
 * @author Ben LR
 */
class Day {

	/** Length in minutes from before now over which to calculate 5-minutely trends */
	const TRNED_LEN = 120;
	/** Length in minutes from before now over which to calculate hourly rain trends */
	const TRNED_LEN_RN = 360;

	const QUERY_COLS = '*, UNIX_TIMESTAMP(t) as unix';

	protected $db;

	function __construct(\nw3\app\core\Db $db) {
		$this->db = $db;
	}

	/**
	 * Processes a day's data into useful summaries - max, mins, means etc.
	 * @param string $day [=null] unix timestamp for the day to process. Defaults to current day.
	 *	Special case for latest 24hrs data: pass the string 'latest'
	 * @return array of data for the chosen day
	 */
	function summary($day = null) {
		//some basic initialisation
		$dat = $datt = $trends = $rnCums = $rncumArr = $mins = $maxs = $means
			= $timesMin = $timesMax = array();
		$nightmin1 = $nightmin2 = $nightmin1T = $nightmin2T = INT_MAX;
		$daymax1 = $daymax2 = INT_MIN;
		$i = $rncum = $w10 = $frostMins = 0;
		foreach (Variable::$live as $vname => $var) {
			if ($var['minmax']) {
				$datt[$vname]['max'] = $datt[$vname]['max2'] = Maths::PHP_INT_MIN;
				$datt[$vname]['min'] = $datt[$vname]['min2'] = INT_MAX;
			}
		}
		$rate_thresh = Station::RAIN_TIP * 2 - 0.1;

		//Get the data
		if($day === 'latest') {
			$start = date('Y-m-d H:i', D_now - 86400);
			$end = date('Y-m-d H:i', D_now);
			$start_previous = date('Y-m-d H:i', D_now - 86400);
			$end_previous = date('Y-m-d H:i', D_now - 86400 - 3600);
		} else {
			$full_day = true;
			$day = $day || D_now; //supply default
			$start = date('Y-m-d', $day) .'00:00';
			$end = date('Y-m-d', $day) .'23:59';
			$start_previous = date('Y-m-d', $day - 86400) .'21:00';
			$end_previous = date('Y-m-d', $day - 86400) .'23:59';
		}
		$query = "WHERE t BETWEEN '$start' AND '$end'";
		$query_prev = "WHERE t BETWEEN '$start_prev' AND '$end_previous'";
		$lives = $this->get_live_data($start, $end);
		//Get a bit more data for the night min and trends
		$prevs = $this->get_live_data($start_prev, $end_prev);


		foreach ($lives as $live) {
			$live['dewp'] = Variable::dewPoint($live['temp'], $live['humi']);
			$live['feel'] = Variable::feelsLike($live['temp'], $live['wind'], $live['dewp']);
			$t = $live['unix'];
			$hour = date('H', $t);

			//Setup for max/min and times-of for max-min vars
			foreach (Variable::$live as $vname => $var) {
				$live[$vname] = (float) $live[$vname];
				$dat[$vname][round($t / 60)] = $live[$vname];
				if ($var['minmax']) {
					if ($live[$vname] >= $datt[$vname]['max']) {
						$datt[$vname]['max'] = $live[$vname];
						$datt[$vname]['timeLmax'] = $t;
					}
					if ($live[$vname] <= $datt[$vname]['min']) {
						$datt[$vname]['min'] = $live[$vname];
						$datt[$vname]['timeLmin'] = $t;
					}
					if ($live[$vname] > $datt[$vname]['max2']) {
						$datt[$vname]['max2'] = $live[$vname];
						$datt[$vname]['timeHmax'] = $t;
					}
					if ($live[$vname] < $datt[$vname]['min2']) {
						$datt[$vname]['min2'] = $live[$vname];
						$datt[$vname]['timeHmin'] = $t;
					}
				}
			}

			//cumulative rain
			$rnChange = $dat['rain'][$t] - (($i > 0) ? $dat['rain'][$t - 1] : 0);
			// account for reset-time crossover (cumulative rn resets to 0)
			$rncum += ($rnChange > 0) ? $rnChange : 0;
			$rncumArr[$t] = $rncum;

			//Frost hours
			if ($live['temp'] < 0) {
				$frostMins++;
			}
			//Day max
			if ($hour >= 9 && $hour < 21) {
				if ($live['temp'] >= $daymax1) {
					$daymax1 = $live['temp'];
					$daymaxt1 = $t;
				}
				if ($live['temp'] > $daymax2) {
					$daymax2 = $live['temp'];
					$daymaxt2 = $t;
				}
			}
			//Night Min
			if ($hour < 9) {
				if ($live['temp'] <= $nightmin1) {
					$nightmin1 = $live['temp'];
					$nightmint1 = $t;
				}
				if ($live['temp'] < $nightmin2) {
					$nightmin2 = $live['temp'];
					$nightmint2 = $t;
				}
			}
			//Max rain rate
			for ($r = 1; $r < 60; $r++) {
				if ($i > $r) {
					$rnr[$i] = $dat['rain'][$t] - $dat['rain'][$t - $r];
					if ($rnr[$i] > $rate_thresh) {
						$rr[$t] = ($r === 1) ? (60 * $rnr[$i]) : (round(60 / ($r - 1) * Station::RAIN_TIP, 1));
						break;
					}
				}
			}
			$w10 += $dat['wind'][$i];
			//10-min trend extremes
			if ($i >= 10) {
				$w10 -= $dat['wind'][$t - 10];
				$wind10[$t] = $w10 / 10;
				$rn10[$t] = $dat['rain'][$t] - $dat['rain'][$t - 10];
				$t10[$t] = $dat['temp'][$t] - $dat['temp'][$t - 10];
			}
			//hour trend extremes
			if ($i >= 60) {
				$tchangehr[$t] = $dat['temp'][$t] - $dat['temp'][$t - 60];
				$hchangehr[$t] = $dat['humi'][$t] - $dat['humi'][$t - 60];
				$rn60[$t] = $dat['rain'][$t] - $dat['rain'][$t - 60];
			}
			++$i;
		}
		//For clarity
		$t_last = $t;
		$i_last = $i;
		$rn_total = $rncum;

		$t_first = $t_last - $i_last;

		//Trends
		if ($i_last > self::TRNED_LEN_RN) {
			$rnCums['10m'] = $rncumArr[$t_last - 10];
			for ($i = 0; $i <= self::TRNED_LEN_RN; $i += 60) { //last 1-6hrs rain
				$rnCums[] = $rncumArr[$t_last - $i];
			}

			for ($i = 0; $i <= self::TRNED_LEN; $i += 5) {
				$dat_pos = $t_last - $i;
				foreach (Variable::$live as $vname => $var) {
					$trends[$i][$vname] = $dat[$vname][$dat_pos];
				}
				$trends[$i]['rain'] = $rncumArr[$dat_pos];
			}
		}

		//min, max, mean (and times of)
		foreach (Variable::$live as $vname => $var) {
			if ($var['minmax']) {
				$timesMin[$vname] = $this->mean_time($datt[$vname]['timeHmin'], $datt[$vname]['timeLmin']);
				$timesMax[$vname] = $this->mean_time($datt[$vname]['timeHmax'], $datt[$vname]['timeLmax']);
				$mins[$vname] = $datt[$vname]['min'];
				$maxs[$vname] = $datt[$vname]['max'];
			} elseif ($var['maxonly']) {
				$maxs[$vname] = max($dat[$vname]);
				$timesMax[$vname] = $this->time_from_extremum($maxs[$vname], $dat[$vname]);
			}
			$means[$vname] = Maths::mean($dat[$vname]);
			if ($i_last > 60) {
				$hrChanges[$vname] = $dat[$vname][$t_last] - $dat[$vname][$t_last - 60];
				$hr24Changes[$vname] = $dat[$vname][$t_last] - $dat[$vname][$t_first];
			}
		}

		if ($daymax1 < -99) {
			$daymax1 = $timesMax['day'] = '';
		} else {
			$timesMax['day'] = $this->mean_time($daymaxt1, $daymaxt2);
		}
		$mins['night'] = $nightmin1;
		$timesMin['night'] = $this->mean_time($nightmint1, $nightmint2);
		$maxs['day'] = $daymax1;


		if (is_array($rn60)) {
			$maxs['rnhr'] = max($rn60);
			if ($maxs['rnhr'] > 0) {
				$timesMax['rnhr'] = $this->time_from_extremum($maxs['rnhr'], $rn60);
			}
			$maxs['tchangehr'] = max($tchangehr);
			$timesMax['tchangehr'] = $this->time_from_extremum($maxs['tchangehr'], $tchangehr);
			$maxs['hchangehr'] = max($hchangehr);
			$timesMax['hchangehr'] = $this->time_from_extremum($maxs['hchangehr'], $hchangehr);
			$tchhr = min($tchangehr);
			$timesMin['tchangehr'] = $this->time_from_extremum($tchhr, $tchangehr);
			$hchhr = min($hchangehr);
			$timesMin['hchangehr'] = $this->time_from_extremum($hchhr, $hchangehr);
			$mins['tchangehr'] = -1 * $tchhr;
			$mins['hchangehr'] = -1 * $hchhr;
		}
		if (is_array($t10)) {
			$w10max = max($wind10);
			$timesMax['w10m'] = $this->time_from_extremum($w10max, $wind10);
			$maxs['w10m'] = $w10max;

			$maxs['rn10'] = max($rn10);
			if ($maxs['rn10'] > 0) {
				$timesMax['rn10'] = $this->time_from_extremum($maxs['rn10'], $rn10);
			}

			$t10min = min($t10);
			$timesMin['tchange10'] = $this->time_from_extremum($t10min, $t10);
			$mins['tchange10'] = -1 * $t10min;
			$maxs['tchange10'] = max($t10);
			$timesMax['tchange10'] = $this->time_from_extremum($maxs['tchange10'], $t10);
		}
		if (is_array($rr)) {
			$maxs['rate'] = max($rr);
			$timesMax['rate'] = $this->time_from_extremum($maxs['rate'], $rr);
			$maxs['rate'] = $maxs['rate'];
		}

		$means['w10m'] = mean($wind10);
		$means['wdir'] = $this->wdirMean($dat['wdir'], $dat['wind']);

		$means['rain'] = $rn_total;
		if ($rn_total == 0) {
			$maxs['rnhr'] = $maxs['rn10'] = null;
		}
		$rnCums[0] = $rn_total;
		$has_rained_in_past_hour = (($rnCums[0] - $rnCums[1]) != 0);

		//rain duration
		if ($rn_total > 0 && $has_rained_in_past_hour) {
			$duration = 0;
			$lastTip = 1;
			for ($i = 0; $i <= $i_last; $i++) {
				if ($rncumArr[$t_last - $i] == $rncumArr[$t_last - $i - 1]) {
					$lastTip++;
				} else {
					$duration += $lastTip;
					$lastTip = 1;
				}
				if ($lastTip >= 60) {
					break;
				}
			}
		}

		//wet hours rough estimate (pretty good)
		$wetmins = 0;
		if ($rn_total > 0) {
			$notRained = 0;
			$raining = false;
			for ($i = $t_first; $i < $t_last; $i++) {
				$notRained++;
				if ($rncumArr[$i] != $rncumArr[$i + 1]) {
					$notRained = 0;
					$raining = true;
				}
				if ($raining) {
					$wetmins++;
				}
				if ($notRained > 30) {
					$raining = false;
				}
			}
		}
		$wethrs = ceil($wetmins / 60);

		//current rain rate guess (based on last rain tip - so inaccurate when tipped after long break -> revert to max rate
		if ($has_rained_in_past_hour) {
			$last = 60;
			for ($i = 1; $i <= 60; $i++) {
				if ($rncumArr[$t_last - $i] != $rn_total) {
					$last = $i;
					break;
				}
			}
			$tipQuantity = ($last === 1) ? (round(($rn_total - $rncumArr[$t_last - 1]) / Station::RAIN_TIP)) : 1;
			$currRateGuess = round(60 / $last * Station::RAIN_TIP * $tipQuantity, 1);
			$currRate = ($currRateGuess > $maxs['rate']) ? $maxs['rate'] : $currRateGuess;
		} else {
			$currRate = 0;
		}

		if ($day === 'latest') {
			//last rain
			$prevRnOld = File::live_data("lastrn");
			if ($rn_total > 0) {
				//Only look at recent values, since this script is meant to be run every minute anyway,
				// so in ideal conditions only really need to check most recent two rnCumArr values.
				//Also, this fixes an awkward bug that presents itself 24hrs after rain, ie. in rnCumArr[0] territory,
				// so it is best to avoid this
				$limitRnLook = 300;
				for ($i = 1; $i < $limitRnLook; $i++) {
					if ($rncumArr[$t_last - $i] != $rn_total) {
						$prevRn = $t_last - ($i * 60);
						if ($prevRn != $prevRnOld) {
							File::live_data("lastrn", $prevRn);
						}
						break;
					}
				}
				if ($i === $limitRnLook) {
					$prevRn = $prevRnOld;
				}
			} else {
				$prevRn = $prevRnOld;
			}
		}

		//maxhr gust
		$maxhrgst = 0;
		for ($i = 0; $i < 60; $i++) {
			if ($dat['gust'][$t_last - $i] > $maxhrgst) {
				$maxhrgst = $dat['gust'][$t_last - $i];
			}
		}

		$frosthrs = round($frostMins / 60, (int) ($frostMins < 10) + 1);
		$rnDuration = roundToDp($duration / 60, 1);

		return array('min' => $mins, 'max' => $maxs, 'mean' => $means, 'timeMin' => $timesMin, 'timeMax' => $timesMax,
			'trend' => $trends, 'trendRn' => $rnCums, 'changeHr' => $hrChanges, 'changeDay' => $hr24Changes,
			'misc' => array('frosthrs' => $frosthrs, 'rnrate' => $currRate, 'rnduration' => $rnDuration,
				'rnlast' => $prevRn, 'wethrs' => $wethrs, 'maxhrgst' => $maxhrgst, 'cnt' => ($i_last + 1),
				'prevRn' => date('r', $prevRn), 'prevRnOld' => date('r', $prevRnOld)
			)
		);
	}

	function latest() {

	}


	/**
	 * Good implementation of calculating the mean wind direction from an array of wdirs and speeds
	 * @param array $wdir raw array
	 * @param array $speed so calm times can be ignored
	 * @return int
	 */
	function wdirMean($wdirs, $speeds) {
		$bitifier = 36; //constant - the quantisation level to convert 360 degrees into a bittier signal
		$calmThreshold = 1; //constant - values when the wind speed was below this are ignored

		$freqs = array();
		for($i = 0; $i <= 360/$bitifier; $i++) {
			$freqs[$i] = 0;
		}

		//get frequencies for each bitified angle
		foreach($wdirs as $t => $dir) {
			if($speeds[$t] > $calmThreshold) { // pivot not to be affected by calm times
				$freqs[round($dir / $bitifier)]++;
			}
		}

		//choose a pivot
		$minfreq = min($freqs);
		$pivot = array_search($minfreq, $freqs);
		$pivot *= $bitifier;

		//calculate the mean about this method
		$sum = 0;
		$count = 0;
		foreach($wdirs as $t => $dir) {
			//values from calm times or near pivot are anomalous => ignore
			if(abs($dir - $pivot) >= $bitifier && $speeds[$t] > $calmThreshold) {
				$sum += $dir;
				$count++;
				if($dir > $pivot) {
					$sum -= 360;
				}
			}
		}
		//clean-up
		$mean = ($count === 0) ? 0 : round($sum / $count);
		if($mean < 0) {
			$mean += 360;
		}

		return $mean;
	}

	/**
	 * TODO: move to View layer
	 * @param type $last_rn
	 * @return type
	 */
	private function last_rain_neat($last_rn) {
		$diff = D_now - $last_rn;
		$ago = Time::secsToReadable($diff);
		$dateAgo = date('jS M', $last_rn);
		if (date('Ymd') == date('Ymd', $last_rn)) {
			$dateAgo = 'Today';
		} elseif (date('Ymd', mkdate(date('n'), date('j') - 1)) == date('Ymd', $last_rn)) {
			$dateAgo = 'Yesterday';
		}
		return Html::acronym(date('H:i ', $last_rn) . ' ' . $dateAgo, $ago . ' ago', true);
	}

	private function time_from_extremum($extremum, $arr) {
		$time = array_search($extremum, $arr);
		return Time::stamp($time);
	}

	private function mean_time($t1, $t2) {
		$time = ($t1 + $t2) / 2;
		return Time::stamp($time);
	}

	private function get_live_data($start_t, $end_t) {
		$query = "WHERE t BETWEEN '$start_t' AND '$end_t'";
		return $this->db->select('live', $query, self::QUERY_COLS);
	}

}

?>
