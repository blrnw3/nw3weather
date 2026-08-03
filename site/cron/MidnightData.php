<?php
/**
 * Midnight CSV append — ported from site/data.php.
 */
class MidnightData {
	public static function run() {
		$t_start = microtime(true);
		$stamp = date('Ymd', time() - 22 * 3600);
		$yestNow = Data::dailyData($stamp);
		$yr_yest = Date::$yr_yest;
		$day_yest = Date::$day_yest;
		$root = ROOT;

		$list = array(
			$yestNow['min']['temp'], $yestNow['max']['temp'], $yestNow['mean']['temp'],
			$yestNow['min']['humi'], $yestNow['max']['humi'], $yestNow['mean']['humi'],
			$yestNow['min']['pres'], $yestNow['max']['pres'], $yestNow['mean']['pres'],
			$yestNow['mean']['wind'], $yestNow['max']['wind'], $yestNow['max']['gust'], $yestNow['mean']['wdir'],
			$yestNow['mean']['rain'], $yestNow['max']['rnhr'], $yestNow['max']['rn10'], $yestNow['max']['rate'],
			$yestNow['min']['dewp'], $yestNow['max']['dewp'], $yestNow['mean']['dewp'],
			$yestNow['min']['night'], $yestNow['max']['day'],
			$yestNow['max']['tchange10'], $yestNow['max']['tchangehr'], $yestNow['max']['hchangehr'],
			$yestNow['min']['tchange10'], $yestNow['min']['tchangehr'], $yestNow['min']['hchangehr'],
			$yestNow['max']['w10m'],
			$yestNow['min']['feel'], $yestNow['max']['feel'], $yestNow['mean']['feel'],
			$yestNow['misc']['frosthrs'],
			isset($yestNow['min']['pm25']) ? $yestNow['min']['pm25'] : '',
			isset($yestNow['max']['pm25']) ? $yestNow['max']['pm25'] : '',
			isset($yestNow['mean']['pm25']) ? $yestNow['mean']['pm25'] : '',
			' \n'
		);

		$listt = array(
			$yestNow['timeMin']['temp'], $yestNow['timeMax']['temp'], '',
			$yestNow['timeMin']['humi'], $yestNow['timeMax']['humi'], '',
			$yestNow['timeMin']['pres'], $yestNow['timeMax']['pres'], '',
			'', $yestNow['timeMax']['wind'], $yestNow['timeMax']['gust'], '',
			'', $yestNow['timeMax']['rnhr'], $yestNow['timeMax']['rn10'], $yestNow['timeMax']['rate'],
			$yestNow['timeMin']['dewp'], $yestNow['timeMax']['dewp'], '',
			$yestNow['timeMin']['night'], $yestNow['timeMax']['day'],
			$yestNow['timeMax']['tchange10'], $yestNow['timeMax']['tchangehr'], $yestNow['timeMax']['hchangehr'],
			$yestNow['timeMin']['tchange10'], $yestNow['timeMin']['tchangehr'], $yestNow['timeMin']['hchangehr'],
			$yestNow['timeMax']['w10m'],
			$yestNow['timeMin']['feel'], $yestNow['timeMax']['feel'], '',
			'',
			isset($yestNow['timeMin']['pm25']) ? $yestNow['timeMin']['pm25'] : '',
			isset($yestNow['timeMax']['pm25']) ? $yestNow['timeMax']['pm25'] : '',
			'',
			' \n'
		);

		$nminData = file_exists(ROOT . 'nmin.txt') ? file_get_contents(ROOT . 'nmin.txt') : '99,';
		$nminSplit = explode(',', $nminData);
		$nminyest = (float)$nminSplit[0];
		if($nminyest < $list[20]) {
			$list[20] = $nminyest;
			$listt[20] = $nminSplit[1];
		}
		file_put_contents(ROOT . 'nmin.txt', $yestNow['min']['nightTomoz'] . ',' . $yestNow['timeMin']['nightTomoz']);
		file_put_contents(ROOT . 'wethrs.txt', $yestNow['misc']['wethrs']);

		$fildat = fopen(ROOT . 'dat' . $yr_yest . '.csv', 'a');
		fputcsv($fildat, $list);
		fclose($fildat);

		$fildatt = fopen(ROOT . 'datt' . $yr_yest . '.csv', 'a');
		fputcsv($fildatt, $listt);
		fclose($fildatt);

		$yestNow['windDirs']['dt'] = $stamp;
		file_put_contents(ROOT . 'datwdirdaily.dat', serialize($yestNow['windDirs']) . "\r\n", FILE_APPEND);

		if(date('n') == 1 && date('j') == 1) {
			$fildat = fopen(ROOT . 'dat' . date('Y') . '.csv', 'w');
			fputcsv($fildat, DatmWriter::datHeaders());
			fclose($fildat);
			$fildatt = fopen(ROOT . 'datt' . date('Y') . '.csv', 'w');
			fputcsv($fildatt, DatmWriter::datHeaders());
			fclose($fildatt);
			$fildatm = fopen(ROOT . 'datm' . date('Y') . '.csv', 'w');
			fputcsv($fildatm, DatmWriter::datmHeaders());
			fclose($fildatm);
		}

		@copy(ROOT . 'dat' . $yr_yest . '.csv', ROOT . 'backup/dat' . $yr_yest . '-' . $day_yest . '.csv');
		@copy(ROOT . 'datt' . $yr_yest . '.csv', ROOT . 'backup/datt' . $yr_yest . '-' . $day_yest . '.csv');

		$vid_dtd = $root . date('Ymd', Date::mkdate(date('n'), date('j') - 10)) . 'dayvideo.wmv';
		if(file_exists($vid_dtd)) { unlink($vid_dtd); }
		$cam_dtd1 = $root . $stamp . 'dailywebcam.gif';
		$cam_dtd2 = $root . $stamp . 'dailywebcam2.gif';
		if(file_exists($cam_dtd1)) { unlink($cam_dtd1); }
		if(file_exists($cam_dtd2)) { unlink($cam_dtd2); }

		if(date('j') == 1 && date('n') == 1) {
			@mkdir(ROOT . date('Y'), 0755);
		}

		Page::quick_log('data_crontime.txt', microtime(true) - $t_start);
	}
}
