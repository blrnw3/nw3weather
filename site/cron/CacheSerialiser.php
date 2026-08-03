<?php
/**
 * CSV → serialised cache rebuilders — ported from cron_main.php.
 */
class CacheSerialiser {
	public static function serialiseCSV($csv, $today = true) {
		$dyear = Date::$dyear;
		$dmonth = Date::$dmonth;
		$dday = Date::$dday;
		$newNOW = isset($GLOBALS['newNOW']) ? $GLOBALS['newNOW'] : null;

		$data = array();
		$dataNew = array();

		for($year = 2009; $year <= $dyear; $year++) {
			$yrfil = ROOT . $csv . $year . '.csv';
			if(file_exists($yrfil)) {
				$raw = file($yrfil);
				$header = explode(',', trim($raw[0]));
				$cntRaw = count($raw);
				for($i = 1; $i < $cntRaw; $i++) {
					$day = date('j', strtotime('Jan 1st ' . (string)$year . ' + ' . (string)($i - 1) . ' days'));
					$month = date('n', strtotime('Jan 1st ' . (string)$year . ' + ' . (string)($i - 1) . ' days'));
					$rawa = explode(',', $raw[$i]);
					$cntRawa = count($rawa);
					for($j = 0; $j < $cntRawa; $j++) {
						$data[$j][$year][$month][$day] = $rawa[$j];
						$dataNew[$header[$j]][$year][$month][$day] = $rawa[$j];
					}
				}
				if($year == $dyear && $today && $csv != 'datm' && is_array($newNOW)) {
					$list = array(
						$newNOW['min']['temp'], $newNOW['max']['temp'], $newNOW['mean']['temp'],
						$newNOW['min']['humi'], $newNOW['max']['humi'], $newNOW['mean']['humi'],
						$newNOW['min']['pres'], $newNOW['max']['pres'], $newNOW['mean']['pres'],
						$newNOW['mean']['wind'], $newNOW['max']['wind'], $newNOW['max']['gust'], $newNOW['mean']['wdir'],
						$newNOW['mean']['rain'], $newNOW['max']['rnhr'], $newNOW['max']['rn10'], $newNOW['max']['rate'],
						$newNOW['min']['dewp'], $newNOW['max']['dewp'], $newNOW['mean']['dewp'],
						$newNOW['min']['night'], $newNOW['max']['day'],
						$newNOW['max']['tchange10'], $newNOW['max']['tchangehr'], $newNOW['max']['hchangehr'],
						$newNOW['min']['tchange10'], $newNOW['min']['tchangehr'], $newNOW['min']['hchangehr'],
						$newNOW['max']['w10m'],
						$newNOW['min']['feel'], $newNOW['max']['feel'], $newNOW['mean']['feel'],
						$newNOW['misc']['frosthrs'],
						isset($newNOW['min']['pm25']) ? $newNOW['min']['pm25'] : '',
						isset($newNOW['max']['pm25']) ? $newNOW['max']['pm25'] : '',
						isset($newNOW['mean']['pm25']) ? $newNOW['mean']['pm25'] : '',
						' \n'
					);
					$listt = array(
						$newNOW['timeMin']['temp'], $newNOW['timeMax']['temp'], '',
						$newNOW['timeMin']['humi'], $newNOW['timeMax']['humi'], '',
						$newNOW['timeMin']['pres'], $newNOW['timeMax']['pres'], '',
						'', $newNOW['timeMax']['wind'], $newNOW['timeMax']['gust'], '',
						'', $newNOW['timeMax']['rnhr'], $newNOW['timeMax']['rn10'], $newNOW['timeMax']['rate'],
						$newNOW['timeMin']['dewp'], $newNOW['timeMax']['dewp'], '',
						$newNOW['timeMin']['night'], $newNOW['timeMax']['day'],
						$newNOW['timeMax']['tchange10'], $newNOW['timeMax']['tchangehr'], $newNOW['timeMax']['hchangehr'],
						$newNOW['timeMin']['tchange10'], $newNOW['timeMin']['tchangehr'], $newNOW['timeMin']['hchangehr'],
						$newNOW['timeMax']['w10m'],
						$newNOW['timeMin']['feel'], $newNOW['timeMax']['feel'], '',
						'',
						isset($newNOW['timeMin']['pm25']) ? $newNOW['timeMin']['pm25'] : '',
						isset($newNOW['timeMax']['pm25']) ? $newNOW['timeMax']['pm25'] : '',
						'',
						' \n'
					);
					for($j = 0; $j < $cntRawa; $j++) {
						$data[$j][$year][$dmonth][$dday] = ($csv == 'dat') ? $list[$j] : $listt[$j];
						$dataNew[$header[$j]][$year][$dmonth][$dday] = ($csv == 'dat') ? $list[$j] : $listt[$j];
					}
				}
			}
		}
		nw3_atomic_write(CACHE_ROOT . 'serialised_' . $csv . '.txt', serialize($data));
		nw3_atomic_write(CACHE_ROOT . 'serialised_' . $csv . '_new.txt', serialize($dataNew));
		if($csv === 'dat') {
			foreach($data as $j => $dat) {
				nw3_atomic_write(CACHE_ROOT . "serialised_dat_$j.txt", serialize($dat));
			}
			foreach($dataNew as $j => $dat) {
				nw3_atomic_write(CACHE_ROOT . "serialised_dat_new_$j.txt", serialize($dat));
			}
		}
	}

	public static function serialiseCSVm() {
		$data = array();
		$dataNew = array();
		$DATA = unserialize(file_get_contents(CACHE_ROOT . 'serialised_dat.txt'));

		for($year = 2009; $year <= date('Y'); $year++) {
			$yrfil = ROOT . 'datm' . $year . '.csv';
			if(file_exists($yrfil)) {
				$raw = file($yrfil);
				$header = explode(',', trim($raw[0]));
				$cnt1 = count($raw);
				for($i = 1; $i < $cnt1; $i++) {
					$rawa = explode(',', $raw[$i]);
					for($j = 0; $j <= 12; $j++) {
						if($j >= 8 && $j < 12) {
							continue;
						}
						$day = date('j', strtotime('Jan 1st ' . (string)$year . ' + ' . (string)($i - 1) . ' days'));
						$month = date('n', strtotime('Jan 1st ' . (string)$year . ' + ' . (string)($i - 1) . ' days'));
						if($j >= 3 && $j !== 12 && $rawa[$j] == '') {
							$rawa[$j] = '0';
						}
						if($j === 3) {
							$rawa[$j] = ($rawa[$j] == 'y') ? $DATA[13][$year][$month][$day] + 0.01 : $rawa[$j];
						}
						if($j === 12 && $year < 2019) {
							$rawa[$j] = '';
						}
						$data[$j][$year][$month][$day] = $rawa[$j];
						$dataNew[$header[$j]][$year][$month][$day] = $rawa[$j];
					}
				}
			}
		}
		nw3_atomic_write(CACHE_ROOT . 'serialised_datm.txt', serialize($data));
		nw3_atomic_write(CACHE_ROOT . 'serialised_datm_new.txt', serialize($dataNew));
		foreach($data as $j => $dat) {
			nw3_atomic_write(CACHE_ROOT . "serialised_datm_$j.txt", serialize($dat));
		}
		foreach($dataNew as $j => $dat) {
			nw3_atomic_write(CACHE_ROOT . "serialised_dat_new_$j.txt", serialize($dat));
		}
	}

	public static function serializeHistoricalData() {
		$raw = file(ROOT . 'historical.csv');
		$header = explode(',', trim($raw[0]));
		$col_count = count($header);
		$cnt = count($raw);
		$data = array();
		for($i = 1; $i < $cnt; $i++) {
			$rawa = explode(',', trim($raw[$i]));
			$dp = explode('-', $rawa[0]);
			$year = (int)$dp[0];
			$month = (int)$dp[1];
			$day = (int)$dp[2];
			for($j = 1; $j < $col_count; $j++) {
				if($rawa[$j] !== '') {
					$data[$header[$j]][$year][$month][$day] = (float)$rawa[$j];
				}
			}
			if($rawa[10] !== '' && $rawa[11] !== '') {
				$data['tmean'][$year][$month][$day] = ($rawa[10] + $rawa[11]) / 2;
				$data['trange'][$year][$month][$day] = $rawa[11] - $rawa[10];
			}
		}
		foreach($data as $var => $dat) {
			nw3_atomic_write(CACHE_ROOT . "serialised_historical_$var.txt", serialize($dat));
		}
	}
}
