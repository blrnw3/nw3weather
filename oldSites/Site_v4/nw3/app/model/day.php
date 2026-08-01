<?php
namespace nw3\app\model;

use nw3\app\model\Variable;
use nw3\app\vari\Wdir;
use nw3\app\util\Maths;
use nw3\app\util\Time;
use nw3\app\util\Date;
use nw3\app\util\ScriptTimer;
use nw3\config\Station;
use nw3\app\core\Db;

/**
 * Daily stuff
 * @author Ben LR
 */
class Day {

	/** Length in minutes from before now over which to calculate 5-minutely trends (only for LATEST) */
	const TRNED_LEN = 120;

	const QUERY_COLS = '*, UNIX_TIMESTAMP(t) as unix';

	const LATEST = 24;

	protected $db;
	private $timer;

	function __construct() {
		$this->db = Db::g();
		$this->timer = new ScriptTimer();
	}

	/**
	 * Processes a day's data into useful summaries - max, mins, means etc.
	 * @param string $day [=null] unix timestamp for the day to process. Defaults to current day.
	 *	Special case for latest 24hrs data: pass the string 'latest'
	 * @return array of data for the chosen day
	 */
	function summary($day = null) {
		//some basic initialisation
		$dat = $datt = $trends = $trendshr = $rncumArr = $mins = $maxs = $means
			= $timesMin = $timesMax = [];
		$nightmin1 = $nightmin2 = $nightmin1T = $nightmin2T = INT_MAX;
		$daymax1 = $daymax2 = INT_MIN;
		$i = $rncum = $w10 = $frostMins = 0;
		foreach (Variable::$live as $vname => $var) {
			if ($var['minmax']) {
				$datt[$vname]['max'] = $datt[$vname]['max2'] = INT_MIN;
				$datt[$vname]['min'] = $datt[$vname]['min2'] = INT_MAX;
			}
		}
		$rate_thresh = Station::RAIN_TIP * 2 - 0.1;

		$is_latest = ($day === self::LATEST);
		//Get the data
		if($is_latest) {
			$start = date('Y-m-d H:i', D_now - Date::secs_DAY + 60);
			$end = date('Y-m-d H:i', D_now);
			$start_previous = date('Y-m-d H:i', D_now - Date::secs_DAY);
			$end_previous = date('Y-m-d H:i', D_now - Date::secs_DAY - 3600);
		} else {
			if(is_null($day)) {
				$day = D_now; //supply default
			}
			$start = date('Y-m-d', $day) .' 00:00';
			$end = date('Y-m-d', $day) .' 23:59';
			$start_previous = date('Y-m-d', $day - Date::secs_DAY) .' 21:00';
			$end_previous = date('Y-m-d', $day - Date::secs_DAY) .' 23:59';
		}
		$lives = $this->get_live_data($start, $end);
		// TODO Get a bit more data for the night min and trends # TODO
//		$query_prev = "WHERE t BETWEEN '$start_prev' AND '$end_previous'";
//		$prevs = $this->get_live_data($start_prev, $end_prev);

		if(count($lives) === 0) {
			return ['No data'];
		}

		foreach ($lives as $live) {
			$live['dewp'] = Variable::dewPoint($live['temp'], $live['humi']);
			$live['feel'] = Variable::feelsLike($live['temp'], $live['wind'], $live['dewp']);
			$stamp = $live['unix'];
			$t = round($stamp / 60); //Easy to seek by minute this way
			$hour = date('H', $stamp);

			//Setup for max/min and times-of for max-min vars
			foreach (Variable::$live as $vname => $var) {
				$live[$vname] = (float) $live[$vname];
				$dat[$vname][$t] = $live[$vname];
				if ($var['minmax']) {
					if ($live[$vname] >= $datt[$vname]['max']) {
						$datt[$vname]['max'] = $live[$vname];
						$datt[$vname]['timeLmax'] = $stamp;
					}
					if ($live[$vname] <= $datt[$vname]['min']) {
						$datt[$vname]['min'] = $live[$vname];
						$datt[$vname]['timeLmin'] = $stamp;
					}
					if ($live[$vname] > $datt[$vname]['max2']) {
						$datt[$vname]['max2'] = $live[$vname];
						$datt[$vname]['timeHmax'] = $stamp;
					}
					if ($live[$vname] < $datt[$vname]['min2']) {
						$datt[$vname]['min2'] = $live[$vname];
						$datt[$vname]['timeHmin'] = $stamp;
					}
				}
			}
			//cumulative rain
			$rncum += $live['rain'];
			$rncumArr[$t] = $rncum;

			//Frost hours
			if ($live['temp'] < 0) {
				$frostMins++;
			}
			//Day max
			if ($hour >= 9 && $hour < 21) {
				if ($live['temp'] >= $daymax1) {
					$daymax1 = $live['temp'];
					$daymaxt1 = $stamp;
				}
				if ($live['temp'] > $daymax2) {
					$daymax2 = $live['temp'];
					$daymaxt2 = $stamp;
				}
			}
			//Night Min
			if ($hour < 9) {
				if ($live['temp'] <= $nightmin1) {
					$nightmin1 = $live['temp'];
					$nightmint1 = $stamp;
				}
				if ($live['temp'] < $nightmin2) {
					$nightmin2 = $live['temp'];
					$nightmint2 = $stamp;
				}
			}
			//Max rain rate
			for ($r = 1; $r < 60; $r++) {
				if ($i > $r) {
					$rnr[$i] = $rncumArr[$t] - $rncumArr[$t - $r];
					if ($rnr[$i] > $rate_thresh) {
						$rr[$t] = ($r === 1) ? (60 * $rnr[$i]) : (round(60 / ($r - 1) * Station::RAIN_TIP, 1));
						break;
					}
				}
			}
			$w10 += $live['wind'];
			//10-min trend extremes
			if ($i >= 10) {
				$w10 -= $dat['wind'][$t - 10];
				$wind10[$t] = $w10 / 10;
				$rn10[$t] = $rncumArr[$t] - $rncumArr[$t - 10];
				$t10[$t] = $dat['temp'][$t] - $dat['temp'][$t - 10];
			}
			//hour trend extremes
			if ($i >= 60) {
				$tchangehr[$t] = $dat['temp'][$t] - $dat['temp'][$t - 60];
				$hchangehr[$t] = $dat['humi'][$t] - $dat['humi'][$t - 60];
				$rn60[$t] = $rncumArr[$t] - $rncumArr[$t - 60];
			}
			++$i;
		}
		//For clarity
		$t_last = $t;
		$t_first = $t_last - $i + 1;
		$i_last = $i;
		$rn_total = $rncum;


		//Latest values
		foreach (Variable::$live as $vname => $var) {
			$trends[0][$vname] = ($vname === 'rain') ? $rncumArr[$t_last] : $dat[$vname][$t_last];
		}
		//Hr trends
		for ($i = 59; $i <= $i_last; $i += 60) {
			$dat_pos = $t_last - $i;
			$hr = round($i / 60.0);
			foreach (Variable::$live as $vname => $var) {
				$trends[$hr.'h'][$vname] = ($vname === 'rain') ? $rncumArr[$dat_pos] : $dat[$vname][$dat_pos];
			}
		}
		//5-min trends
		if($is_latest) {
			for ($i = 5; $i <= self::TRNED_LEN; $i += 5) {
				$dat_pos = $t_last - $i;
				foreach (Variable::$live as $vname => $var) {
					$trends[$i][$vname] = ($vname === 'rain') ? $rncumArr[$dat_pos] : $dat[$vname][$dat_pos];
				}
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

		$means['w10m'] = Maths::mean($wind10);
		$means['wdir'] = Wdir::mean($dat);

		$means['rain'] = $rn_total;
		if ($rn_total == 0) {
			$maxs['rnhr'] = $maxs['rn10'] = null;
		}
		$trendshr[0]['rain'] = $rn_total;
		$has_rained_in_past_hour = (($trendshr[0]['rain'] - $trendshr[1]['rain']) !== 0);

		//rain duration
		if ($is_latest && $rn_total > 0 && $has_rained_in_past_hour) {
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
		if($is_latest && $has_rained_in_past_hour) {
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

		//Last rain
		if($is_latest) {
			$prevRn = $this->get_last_rain();
		}

		//maxhr gust
		$maxhrgst = 0;
		for ($i = 0; $i < 60; $i++) {
			if ($dat['gust'][$t_last - $i] > $maxhrgst) {
				$maxhrgst = $dat['gust'][$t_last - $i];
			}
		}

		$this->timer->stop();

		$data = [
			# Stat
			'period' => "$start to $end",
			'cnt' => $i_last,
			'exectime' => $this->timer->executionTimeMs(),
			# Singles
			'frosthrs' => $frostMins / 60.0,
			'wethrs' => $wethrs,
			'maxhrgst' => $maxhrgst,
			# Arrays
			'min' => $mins,
			'max' => $maxs,
			'mean' => $means,
			'timeMin' => $timesMin,
			'timeMax' => $timesMax,
		];
		if($is_latest) {
			$data += [
				'rnrate' => $currRate,
				'rnduration' => $duration / 60.0,
				'rnlast' => $prevRn,
				'trend' => $trends,
			];
		}
		return $data;
	}

	private function time_from_extremum($extremum, $arr) {
		$time = array_search($extremum, $arr) * 60;
		return Time::stamp($time);
	}

	private function mean_time($t1, $t2) {
		$time = ($t1 + $t2) / 2;
		return Time::stamp($time);
	}

	private function get_live_data($start_t, $end_t) {
		return $this->db->query(self::QUERY_COLS)->tbl('live')
			->filter(Db::btwn($start_t, $end_t, 't', true))
			->all()
		;
	}

	private function get_last_rain() {
		return (int)$this->db->query(Db::timestamp())->tbl('live')
			->filter('rain > 0')
			->extreme(Db::MAX, 't')
			->scalar()
		;
	}

}

?>
