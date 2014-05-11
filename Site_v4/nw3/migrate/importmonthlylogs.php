<?php
namespace nw3\migrate;

use nw3\app\core\Db;
use nw3\app\util\Html;
use nw3\app\util\ScriptTimer;
use nw3\data\Climate;

class Importmonthlylogs {

	const PATH = 'C:\Users\Ben\Documents\Weather\Backup\CurrentWebsiteBackup\MonthlyLogs\dat';

	function import_upto($year) {
		for($y = 2009; $y <= $year; $y++) {
			$this->import($y);
		}
	}

	function import($year) {

		$timer = new ScriptTimer();

		$db = new Db(false);
		$var_names = array('d', 't24min', 't24max', 't24mean', 'hmin', 'hmax', 'hmean', 'pmin',
			'pmax', 'pmean', 'wmean', 'wmax', 'gust', 'wdir', 'rain', 'hrmax',
			'r10max', 'ratemax', 'dmin', 'dmax', 'dmean', 'tmin', 'tmax', 'tc10max',
			'tchrmax', 'hchrmax', 'tc10min', 'tchrmin', 'hchrmin', 'w10max', 'fmin', 'fmax', 'fmean', 'afhrs',
			'sunhr', 'wethr', 'cloud', 'snow', 'lysnw', 'hail', 'thunder', 'fog',
			'comms', 'extra', 'issues', 'away',
			't_t24min', 't_t24max', 't_hmax', 't_hmin', 't_pmax', 't_pmin', 't_wmax', 't_gust',
			't_hrmax', 't_10max', 't_ratemax', 't_dmax', 't_dmin', 't_tmax', 't_tmin', 't_tchrmax',
			't_tchrmin', 't_tc10max', 't_tc10min', 't_hchrmax', 't_hchrmin', 't_w10max', 't_fmax', 't_fmin',
			'a_wmean', 'a_rain', 'a_tmin', 'a_tmax', 'a_sunhr', 'a_wethr'
		);

		array_map('self::backtickify', $var_names);

		$var_names_sql = implode(',', $var_names);
		$values_sql = implode(',', array_fill(0, count($var_names), '?'));

		$mass_insert = 'INSERT INTO `daily`('. $var_names_sql .') VALUES ('. $values_sql .')';
//		Html::print_m($mass_insert);
		$db->prepare($mass_insert);

		$core_handle = file(self::PATH. $year. '.csv');
		$time_handle = file(self::PATH. 't'. $year. '.csv');
		$extra_handle = file(self::PATH. 'm'. $year. '.csv');

		$vars = array();
		$anoms = array();
		foreach ($core_handle as $day => $core_vars) {
			if($day === 0) {
				continue;
			}
			$vars[$day] = array();
			$vars[$day][] = date( 'Y-m-d', mktime(0,0,0, 1, $day, $year) );

			$anoms[$day] = array();
			# 29th Feb same as 28th feb
			$anom_day = ($year % 4 === 0 && $day >= 60) ? $day - 2: $day - 1;

			$raw_core_vals = explode(',', $core_vars);
			array_pop($raw_core_vals); //'spare' field

			foreach ($raw_core_vals as $k => $raw_core_val) {
				$val = (strlen($raw_core_val) === 0 || $raw_core_val === '-') ? null : round($raw_core_val, 2);
				$vars[$day][] = $val;
				# Anoms
				if(key_exists($var_names[$k+1], Climate::$LTA_daily)) {
					$anoms[$day][] = ($val === null) ? null :
						$val - Climate::$LTA_daily[$var_names[$k+1]][$anom_day];
				}
			}

		}
		$main_count = $k+2;

		foreach ($extra_handle as $day => $extra_vars) {
			if($day === 0) {
				continue;
			}
			$anom_day = ($year % 4 === 0 && $day >= 60) ? $day - 2: $day - 1;

			$raw_extra_vals = explode(',', $extra_vars);
			array_pop($raw_extra_vals);
			foreach ($raw_extra_vals as $i => $raw_extra_val) {
				# Blank fields need sanitising
				$null_val = 0; # false or 0
				if($i === 1 || $i === 2) $null_val = null; # wethrs, cloud
				if($i >= 8 && $i <= 10) $null_val = ''; # comms, extra, issues

				# Special comms sanitisation
				if($i === 8 && $raw_extra_val === 'blr') {
					$raw_extra_val = '-';
				}

				# Convert snow 'y' to rain val
				if($i === 3 && $raw_extra_val === 'y') {
					$raw_extra_val = $vars[$day][14];
				}

				$val = (strlen($raw_extra_val) === 0) ? $null_val : $raw_extra_val;
				$vars[$day][] = $val;
				# Anom
				$var_name = $var_names[$i + $main_count];
				if(key_exists($var_name, Climate::$LTA_daily)) {
					$anoms[$day][] = ($val === null) ? null :
						(float)$val - Climate::$LTA_daily[$var_name][$anom_day];
				}
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
			# After 15th June 2013, extra col exists
			$vars[$day] = array_slice($vars[$day], 0, count($var_names)-6); #6 anoms
		}

		#Anom merge
		foreach ($vars as $d => &$var) {
			$var = array_merge($var, $anoms[$d]);
		}

		unset($core_handle, $time_handle, $extra_handle, $var);

		foreach ($vars as $i => &$var) {
			if(count($var) !== count($var_names)) {
				xdebug_break();
				var_dump($var);
			} else {
				$db->execute($var);
			}
		}

		# Various fixes
//		$db->update('daily', array('comms' => '-'), "comms = 'blr' OR comms IS NULL");

		$timer->stop();
		var_dump($timer->executionTime());
	}

	static function backtickify($str) {
		return "`$str`";
	}
}
?>
