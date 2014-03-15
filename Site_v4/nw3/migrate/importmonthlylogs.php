<?php

require '../../../app/core/db.php';
require '../../../app/util/ScriptTimer.php';
require '../../../app/util/UtilHtml.php';

const PATH = 'D:\Archive\Weather\CurrentWebsiteBackup\MonthlyLogs\dat';

ini_set('max_execution_time', 50);

import('2013');

function import($year) {

	$timer = new ScriptTimer();
	$timer->start();

	$db = new Db(false);
	$var_names = array('day', 't24min', 't24max', 't24mean', 'hmin', 'hmax', 'hmean', 'pmin', 'pmax', 'pmean', 'wmean', 'wmax', 'gust', 'wdir',
		'rain', 'hrmax', 'r10max', 'ratemax', 'dmin', 'dmax', 'dmean', 'tmin', 'tmax',
		'tc10max', 'tchrmax', 'hchrmax', 'tc10min', 'tchrmin', 'hchrmin', 'w10max', 'fmin', 'fmax', 'fmean', 'afhrs',
		'sunhr', 'wethr', 'cloud', 'snow', 'lysnw', 'hail', 'thunder', 'fog', 'comms', 'extra', 'issues', 'away',
		't_t24min', 't_t24max', 't_hmax', 't_hmin', 't_pmax', 't_pmin', 't_wmax', 't_gust', 't_hrmax', 't_10max', 't_ratemax', 't_dmax', 't_dmin',
		't_tmax', 't_tmin', 't_tchrmax', 't_tchrmin', 't_tc10max', 't_tc10min', 't_hchrmax', 't_hchrmin', 't_w10max', 't_fmax', 't_fmin');

	array_map('backtickify', $var_names);

	$var_names_sql = implode(',', $var_names);
	$values_sql = implode(',', array_fill(0, count($var_names), '?'));

	$mass_insert = 'INSERT INTO `daily_vars`('. $var_names_sql .') VALUES ('. $values_sql .')';
	UtilHtml::print_m($mass_insert);
	$db->prepare($mass_insert);

	$core_handle = file(PATH. $year. '.csv');
	$time_handle = file(PATH. 't'. $year. '.csv');
	$extra_handle = file(PATH. 'm'. $year. '.csv');

	$vars = array();

	foreach ($core_handle as $day => $core_vars) {
		if($day === 0) {
			continue;
		}
		$vars[$day] = array();
		$vars[$day][] = date( 'Y-m-d', mktime(0,0,0, 1, $day, $year) );

		$raw_core_vals = explode(',', $core_vars);
		array_pop($raw_core_vals); //'spare' field

		foreach ($raw_core_vals as $raw_core_val) {
			$vars[$day][] = (strlen($raw_core_val) === 0 || $raw_core_val === '-') ? null : round($raw_core_val, 2);
		}
	}

	foreach ($extra_handle as $day => $extra_vars) {
		if($day === 0) {
			continue;
		}
		$raw_extra_vals = explode(',', $extra_vars);
		array_pop($raw_extra_vals);
		foreach ($raw_extra_vals as $raw_extra_val) {
			$vars[$day][] = (strlen($raw_extra_val) === 0) ? null : $raw_extra_val;
		}
	}

	$valids = array();
	foreach ($time_handle as $day => $time_vars) {
		if($day === 0) {
			//get valid columns (non-empty)
			$names = explode(',', $time_vars);
			foreach ($names as $i => $name) {
				if(strlen($name) > 0) {
					$valids[] = $i;
				}
			}
			continue;
		}

		$raw_time_vals = explode(',', $time_vars);
		array_pop($raw_time_vals);

		foreach ($raw_time_vals as $i => $raw_time_val) {
			if(in_array($i, $valids)) {
				$vars[$day][] = (strlen($raw_time_val) === 0 || $raw_time_val === '-') ? null : $raw_time_val;
			}
		}
		$vars[$day] = array_slice($vars[$day], 0, count($var_names));
	}

	unset($core_handle, $time_handle, $extra_handle);

//	UtilHtml::print_m($vars[1]);



	foreach ($vars as $i => $var) {
		if(count($var) !== count($var_names)) {
			var_dump($var);
		} else {
			$db->execute($var);
		}
	}

	$timer->stop();
	echo $timer->executionTime();
}

function backtickify($str) {
	return "`$str`";
}

?>
