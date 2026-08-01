<?php
namespace nw3\app\vari;

use nw3\app\model\Variable;

/**
 * Daily Mean Wind Direction
 */
class Wdir extends Live {

	function __construct() {
//		$this->days_filter = '> 10';
		parent::__construct('wdir');
		$this->abs_type = Variable::DirectionRaw;
	}

	/**
	 * Good implementation of calculating the mean wind direction from an array of wdirs and speeds
	 * @param type $vals - numeric array of (wdir, wind assoc arrays)
	 * @return int
	 */
	public static function mean(&$vals) {
		$bitifier = 36; //constant - the quantisation level to convert 360 degrees into a bittier signal
		$calmThreshold = 1; //constant - values when the wind speed was below this are ignored

		$freqs = [];
		for($i = 0; $i <= 360/$bitifier; $i++) {
			$freqs[$i] = 0;
		}

		//get frequencies for each bitified angle
		foreach($vals as $val) {
			if($val['wind'] > $calmThreshold) { // pivot not to be affected by calm times
				$freqs[round($val['wdir'] / $bitifier)]++;
			}
		}

		//choose a pivot
		$minfreq = min($freqs);
		$pivot = array_search($minfreq, $freqs);
		$pivot *= $bitifier;

		//calculate the mean about this method
		$sum = 0;
		$count = 0;
		foreach($vals as $val) {
			//values from calm times or near pivot are anomalous => ignore
			if(abs($val['wdir'] - $pivot) >= $bitifier && $val['wind'] > $calmThreshold) {
				$sum += $val['wdir'];
				$count++;
				if($val['wdir'] > $pivot) {
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

	public function trend_avgs($avg_periods) {
		$data = [];
		$limit = $avg_periods[count($avg_periods)-1] + 1;
		$vals = $this->db->query('wdir', 'wind')->tbl('live')
			->limit($limit)->order(MAX, 't')->all();
		foreach ($avg_periods as $duration) {
			$dat = array_slice($vals, 0, $duration);
			$data[$duration] = self::mean($dat);
		}
		return $data;
	}
}

?>
