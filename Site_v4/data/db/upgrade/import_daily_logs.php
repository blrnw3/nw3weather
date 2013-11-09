<?php

require '../../../app/core/db.php';
require '../../../app/util/ScriptTimer.php';

const PATH = 'D:\Archive\Weather\CurrentWebsiteBackup\DailyLogs\\';

ini_set('max_execution_time', 1000);

$begin = new DateTime('05 apr 2013');
$end = new DateTime('05 jun 2013');

$interval = DateInterval::createFromDateString('1 day');
$period = new DatePeriod($begin, $interval, $end);

import($period);

function import($period) {

	$timer = new ScriptTimer();
	$timer->start();

	$db = new Db(false);
	$mass_insert = 'INSERT INTO `live`
		(`t`, `rain`, `humi`, `pres`, `wind`, `gust`, `temp`, `wdir`)
		VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
	$db->prepare($mass_insert);

	foreach ($period as $dt) {
		echo "Processing ". $dt->format("jS M Y") . "<br />";

		$live_vals_handle = file(PATH. $dt->format('Ymd'). 'log.txt');
		foreach ($live_vals_handle as $live_vals) {
			$lives = array();
			$raw_live_vals = explode(',', $live_vals);

			$lives[0] = $dt->format('Y-m-d') .' '. $raw_live_vals[0] .':'. $raw_live_vals[1];
			$lives[1] = $raw_live_vals[10];
			$lives[2] = $raw_live_vals[7];
			$lives[3] = $raw_live_vals[8];
			$lives[4] = $raw_live_vals[3];
			$lives[5] = $raw_live_vals[4];
			$lives[6] = $raw_live_vals[6];
			$lives[7] = $raw_live_vals[5];

			$db->execute($lives);
		}
		unset($live_vals_handle, $raw_live_vals);
	}

	$timer->stop();
	echo $timer->executionTime();
}

?>
