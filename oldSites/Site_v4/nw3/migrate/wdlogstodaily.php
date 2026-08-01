<?php
namespace nw3\migrate;

use nw3\app\util\String;
use nw3\app\util\Maths;

class Wdlogstodaily {

	const PATH_IN = 'D:\Archive\Weather\ImportantLogfiles\\';
	const PATH_OUT = 'D:\Archive\Weather\CurrentWebsiteBackup\DailyLogs\from_WD\\';

	private $period;

	function __construct($start_date, $end_date) {
		ini_set('max_execution_time', 300);

		$tz = new \DateTimeZone('Europe/London'); #Logs are in this TZ
		$begin = new \DateTime($start_date, $tz);
		$end = new \DateTime($end_date, $tz);

		$interval = \DateInterval::createFromDateString('1 month');
		$this->period = new \DatePeriod($begin, $interval, $end);
	}

	function parse() {
		$lim = [1,1,1, 1,2,0.4, 5,2,2, 9,15,400,2.5,2.5];

		foreach ($this->period as $dt) {
			echo "Processing ". $dt->format("M Y");

			$file_path = self::PATH_IN. $dt->format('Y/nY') .'lgcsv.csv';

			if(!file_exists($file_path)) {
				echo ". Skipping - $file_path does not exist";
				continue;
			}
			echo "<br />";

			$handle = file($file_path);
			$raw_data = [];
			//Group data by day
			foreach ($handle as $record) {
				$vars = explode(',', $record);
				$day = $vars[0];
				if($day !== 'day')
					$raw_data[$day][] = $vars;
			}
			unset($handle);

			foreach ($raw_data as $day => $raw_record) {
				echo '########## Parsing '. $day .'################<br />';
				$path_out = self::PATH_OUT . $dt->format('Ym') . String::zerolead($day) .'log.txt';
				$filelog = fopen($path_out, "w");

				$old_vars = null;
				foreach ($raw_record as $vars) {
//					if($old_vars !== null) {
//						//Sanitisation attempt
//						for($t = 5; $t < 14; $t++) {
//							$diff = $vars[$t] - $old_vars[$t];
//							if($t != 10 && $t != 9 && abs($diff) > $lim[$t]) {
//								echo $diff .' - ', $vars[$t]. ' at '.
//									$vars[3] .':'. $vars[4]. '<br />';
//
//								if($t != 10 && $t != 9) {
//									$vars[$t] = $old_vars[$t];
//								}
//							}
//						}
//					}
//					$old_vars = $vars;

					$out_vars = array(
						String::zerolead($vars[3]), #Hour
						String::zerolead($vars[4]), #Minute
						String::zerolead($vars[0]), #Day
						# Wind speeds are logged in knots
					    Maths::round($vars[9] * 1.152), #Speed
						Maths::round($vars[10] * 1.152), #Gust
						$vars[11], #Direction
						$vars[5], #Temperature
						$vars[6], #Humidity
						round($vars[8]), #Pressure
						$vars[7], #Dew Point
						round($vars[13], 1) # Rain
					);
					fwrite($filelog, implode(',', $out_vars) ."\r\n");
				}
				fclose($filelog);

				//$this->logneatenandrepair($path_out);

			}
			unset($handle, $raw_data);
		}
	}

	function logneatenandrepair($path) {
		$cnt = 0;
		$new_path = str_ireplace('_raw','', $path);
		$filelog = fopen( $new_path, "w" );
		$filcust = file($path);
		$len = count($filcust);

		for($i = 0; $i < $len; $i++) {
			$custl[$i] = explode(',', $filcust[$i]);
			for($t = 0; $t < 11; $t++) {
				$custl[$i][$t] = round($custl[$i][$t],1);
			}
			$custl[$i][8] = round($custl[$i][8]);
		}

		$linewrite[0] = implode(',', $custl[0]);

		for($i = 1; $i < $len; $i++) {
			$diff = ( mktime($custl[$i][0], $custl[$i][1], 0) - mktime($custl[$i-1][0], $custl[$i-1][1], 0) ) / 60;
			if( $diff > 1 && $diff < 10 ) {
				for($j = 1; $j < $diff; $j++) {
					$linewrite[$i+$j-1+$cnt] = $linewrite[$i+$j-2+$cnt];
				}
				$cnt += $j - 1;
			}
			$linewrite[$i+$cnt] = implode(',', $custl[$i]);
		}

		$lincnt = count($linewrite);
		for($i = 0; $i < $lincnt; $i++) {
			if(strlen($linewrite[$i]) > 10) {
				fwrite($filelog, $linewrite[$i]."\r\n");
			}
		}

		fclose($filelog);
	}
}

?>
