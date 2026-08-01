<?php
namespace nw3\migrate;

use nw3\app\core\Db;
use nw3\app\util\String;
use nw3\app\util\Maths;

class Importdailylogs {

	const PATH = 'C:\Users\Ben\Documents\Weather\Backup\CurrentWebsiteBackup\DailyLogs\\';

	const DUPLICATE_SEARCH_DISTANCE = 3; //How far to search for a missing value from a duplicate when trying to resolve the missing
	const TIME_GAP_WARNING_THRESHOLD = 7; //Above this requires interpolation rather than padding
	const TIME_GAP_ERROR_THRESHOLD = 50; //Raises an error - may require manual intervention

	private $period;
	private $max;

	function __construct($start_date, $end_date) {
		ini_set('max_execution_time', 3600);

		$tz = new \DateTimeZone('Europe/London'); #Logs are in this TZ
		$interval = \DateInterval::createFromDateString('1 day');

		$begin = new \DateTime($start_date, $tz);
		if(!$end_date) {
			$end = new \DateTime($start_date, $tz);
			$end->add($interval);
		} else {
			$end = new \DateTime($end_date, $tz);
		}

		$this->period = new \DatePeriod($begin, $interval, $end);
	}

	function migrate($timer) {
		$db = new Db(false);
		$mass_insert = 'INSERT INTO `live`
			(`t`, `rain`, `humi`, `pres`, `wind`, `gust`, `temp`, `wdir`)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
		$db->prepare($mass_insert);

		foreach ($this->period as $dt) {
			echo "Processing ". $dt->format("jS M Y");

			$file_path = self::PATH. $dt->format('Ymd'). 'log.txt';
			if(!file_exists($file_path)) {
				echo ". Skipping - does not exist". $dt->format("jS M Y") . "<br />";
				continue;
			}
			echo "<br />";

			//Did DST switch today?
			$dst_start = $dt->format('I');
			$dt->setTime(6, 0);
			$dst_end = $dt->format('I');
			$dst_switch = (int)$dst_start - (int)$dst_end;
			$mar_switch = $dst_switch < 0;
			$oct_switch = $dst_switch > 0;

			$local_date = $dt->format('Y-m-d');
			$local_offset = (int)$dst_start; //default; deal with special cases when we know the time
			$dst_over = false;

			$live_vals_handle = file($file_path);
			$prev_rn = 0;
			foreach ($live_vals_handle as $live_vals) {
				$lives = [];
				$raw_live_vals = explode(',', $live_vals);

				//Attempt to get UTC datetime from ambiguous logs (no DST info stored)
				/*
				NB: PHP's DateTime is fucked (just as the old procedural date functions):
				It fails to handle the ambiguous datetime of e.g. 2013-10-27 01:30 (DST or not?!)
				Setting the TZ to Europe/London is insufficient (2013-10-27 01:30 Europe/London),
				 since we still don't know wheher it's 01:30 before or after the DST switch!
				The only correct way to handle this is by specifying the DST flag, or better-yet,
				 do it the ISO way (2013-10-27 01:30 +00:00 Europe/London) and specify the UTC offset and TZ.
				PHP doesn't allow you to set this offset, so the only way to handle this balls-up by PHP
				 is to use the date-parsing functions which accurately read this offset.
				 */
				$local_hour = String::zerolead($raw_live_vals[0]);
				$local_minute = String::zerolead($raw_live_vals[1]);
				$local_time = "$local_hour:$local_minute:00";

				//Trigger change at specific time
				if($mar_switch && $local_hour == 2) {
					$local_offset = 1;
				} elseif($oct_switch && $dst_over) {
					$local_offset = 0;
				}
				$utc_datetime = date('Y-m-d H:i:s', strtotime("$local_date $local_time +0{$local_offset}00"));

				//Convert accumRn to absRn
				$rain = $raw_live_vals[10] - $prev_rn;
				if($rain < 0) {
					echo "WARN! Bad rain value $rain detected at $utc_datetime. Forcing to 0<br />";
					$rain = 0;
				}
				# Handle special migration of rain values when the reset time was 21z
				# Breaks if there was a rain tip at midnight, or no midnight value present,
				#  but this has been manually checked.
				if(($local_hour + $local_minute == 0) && ($dt->getTimestamp() < mktime(0,0,0, 4, 10, 2009))) {
					$rain = 0;
				}
				$prev_rn = $raw_live_vals[10];

				$lives[0] = $utc_datetime;
				$lives[1] = $rain;
				$lives[2] = $raw_live_vals[7];
				$lives[3] = $raw_live_vals[8];
				$lives[4] = round($raw_live_vals[3], 1); #Some old logs store 10dp...
				$lives[5] = $raw_live_vals[4];
				$lives[6] = $raw_live_vals[6];
				$lives[7] = $raw_live_vals[5];

				$db->execute_proc($lives);

				if($oct_switch) {
					$dst_over = ($local_hour == 1 && $local_minute == 59); //Relies on this entry being present!
				}
			}
			unset($live_vals_handle, $raw_live_vals);
		}

		$timer->stop();
		echo $timer->executionTime();
	}

	function validate_raw_logs($sanitise = false) {
		$tots = array(
			'missing' => 0,
			'duplicate' => 0,
			'miss_dup_matches' => 0,
			'miss_blanks' => 0
		);
		$result = [];
		$period_count = 0;
		foreach ($this->period as $dt) {
			$period_count++;
			$key = $dt->format('Y-m-d: ');
			$result[$key] = array(
				'error' => [],
				'warning' => [],
				'miss_dup_match_count' => 0,
				'miss_blank_count' => 0,
				'miss' => [],
				'dup' => [],
			);
			$res = &$result[$key];

			$file_name = $dt->format('Ymd'). 'log.txt';
			$in_path = self::PATH. $file_name;
			if(!file_exists($in_path)) {
				$res['warning'][] = "Skipping - does not exist";
				continue;
			}

			//Did DST switch today?
			$dst_start = $dt->format('I');
			$dt->setTime(6, 0);
			$dst_end = $dt->format('I');
			$dst_switch = (int)$dst_start - (int)$dst_end;
			$mar_switch = $dst_switch < 0;
			$oct_switch = $dst_switch > 0;

			//Create an array of the valid times
			$expected_counts = array_fill(0, 1440, 1);
			if($mar_switch || $oct_switch) {
				//For times between 1am and 2am, decrease/increase expected count
				$one_am_val = $mar_switch ? 0 : 2;
				foreach ($expected_counts as $k => &$v) {
					if($k >= 60 && $k < 120)
						$v = $one_am_val;
				}
				unset($k, $v);
			}
			$octswitch_1am_raw = [];
			$records = array_fill(0, 1440, []);

			$expected_day = (int)$dt->format('j');
			$expected_year = (int)$dt->format('Y');

			$live_vals_handle = file($in_path);
			$prev_time = -1;
			foreach ($live_vals_handle as $live_vals) {
				$raw_live_vals = explode(',', $live_vals);

				$day = (int)($raw_live_vals[2]);
				$hour = (int)($raw_live_vals[0]);
				$minute = (int)($raw_live_vals[1]);
				$time = (60 * $hour) + $minute;

				//Annoying case whereby 2359 is the first line
				if($prev_time === -1 && $time === 1439) {
					$res['warning'][] = "Skipping faulty 23:59 start time";
					continue;
				}
				//Common cases in 2009
				if($expected_year === 2009) {
					if($prev_time === 58 && $hour === 23) {
						$res['warning'][] = "Skipping faulty 2009-special-case 00:58 jump to $hour:$minute";
						continue;
					} elseif($prev_time === 119 && $time === 60) {
						$res['warning'][] = "Skipping faulty 2009-special-case 01:59 to 01:00 jump";
						continue;
					}
				}
				//Catch times from the wrong day
				if($day !== $expected_day) {
					$res['warning'][] = "Skipping $hour:$minute from wrong day $day";
					continue;
				}

				$time_gap = $time - $prev_time;
				if($time_gap > self::TIME_GAP_WARNING_THRESHOLD || $time_gap < 0) {
					$prev_prettyish = round($prev_time/60.0, 1);
					# Big or negative jumps are serious
					$level = ($time_gap > self::TIME_GAP_ERROR_THRESHOLD || $time_gap < 0) ? 'error' : 'warning';
					$res[$level][] = "Time Jump! $time_gap mins skipped from $prev_prettyish to $hour:$minute";
				}
				$prev_time = $time;

				$records[$time][] = $raw_live_vals;
				if($oct_switch && $hour === 1) {
					$octswitch_1am_raw[] = $live_vals;
				}
			}

			if($sanitise) { //Tries to resolve some of the missing records
				if($oct_switch) {
					$res['warning'][] = 'SCREAM! Oct DST switch - sanitisation unimplemented for 1-2am';
					$skip_dst_switch_hrs = true;
				} else {
					$skip_dst_switch_hrs = false;
				}

				//Special sanitisation of midnight (no back checking possible)
				if(!$records[0]) {
					$midnight_loss_solved = false;
					$i = 0;
					while($i < self::TIME_GAP_ERROR_THRESHOLD && !$midnight_loss_solved) {
						if($records[$i]) {
							$records[0][] = $records[$i][0];
							$res['warning'][] = 'Lack of midnight value solved with '. round($i / 60.0, 1);
							$midnight_loss_solved = true;
						}
						$i++;
					}
					if(!$midnight_loss_solved) {
						$res['error'][] = 'Could not solve lack of midnight value - nearest accurate is too far away';
					}
				}

				//First pass - try to resolve missings using nearby duplicates
				unset($time, $i);
				foreach($records as $time => &$record) {
					//Skip DST switch region
					if($skip_dst_switch_hrs && ($time >= 60 && $time <= 120)) {
						continue;
					}
					$dup_count = count($record) - 1;
					//Only interested in duplicates for this pass
					if($dup_count < 1) {
						continue;
					}
					//Work out where the adjacent missing (if any) is
					if(!$records[$time+1]) {
						//Missing(s) are ahead of this (set of) dup(s)
						//First dup is probably the genuine value for this time, the rest need shifting
						for($i = 1; $i <= $dup_count; $i++) {
							if(!$records[$time+$i]) {
								$records[$time+$i][] = $record[$i];
								unset($record[$i]);
								$res['miss_dup_match_count']++;
							} else {
								//Careful of any discontinuities
								break;
							}
						}
					} elseif(!$records[$time-1]) {
						//Behind the dup(s)
						//Last dup is probably the genuine value for this time, the rest need moving
						for($i = 0; $i < $dup_count; $i++) {
							if(!$records[$time-$i]) {
								$records[$time-$i][] = $record[$i];
								$res['miss_dup_match_count']++;
							} else {
								break;
							}
						}
					}
				}
				unset($time, $record);
				//Second pass - resolve blanks using nearby valids
				//Avoid resolving blanks from a long gap - should be interpolated instead
//				$skipped_in_a_row = 1;
//				$prev_missing_time = -9;
//				foreach($missing as $time => $cnt) {
//					//Skip DST switch region
//					if($skip_dst_switch_hrs && ($time >= 60 && $time < 180)) {
//						continue;
//					}
//					if(($time - $prev_missing_time) === 1) {
//						$skipped_in_a_row++;
//					} else {
//						$skipped_in_a_row = 1;
//					}
//					$prev_missing_time = $time;
//					//Skip if the time gap is too large
//					if($skipped_in_a_row > self::TIME_GAP_ERROR_THRESHOLD) {
//						continue;
//					}
//					//Generally best to look back since start of gap is hit before the end,
//					// but sometimes can't do this
//					if($raw[$time-1]) {
//						$raw[$time] = $raw[$time-1];
//						$res['miss_blank_count']++;
//						$missing[$time] = 0;
//					} elseif($raw[$time+1]) {
//						$raw[$time] = $raw[$time+1];
//						$res['miss_blank_count']++;
//						$missing[$time] = 0;
//					}
//				}

				$out_path = self::PATH ."sanitised\\". $file_name;
				$handle = fopen($out_path, 'w');
				$special_dst_case_handled = false;

				//Work out how to format the output times
				$zero_lead = strlen($records[0][0][0]) > 1;

				foreach ($records as $time => $record) {
					if(!$record) {
						continue;
					}
					$hr = floor($time / 60);
					$min = $time % 60;
					//Oct DST special case
					if($oct_switch && $hr == 1) {
						if(!$special_dst_case_handled) {
							foreach ($octswitch_1am_raw as $value) {
								fwrite($handle, $value);
							}
							unset($value);
							$special_dst_case_handled = true;
						}
						continue;
					}
					//Set the time properly (correct key but wrong raw time)
					$new_record = $record[0]; //Ignore remnant dups
					if($zero_lead) {
						$new_record[0] = String::zerolead($hr);
						$new_record[1] = String::zerolead($min);
					} else {
						$new_record[0] = $hr;
						$new_record[1] = $min;
					}
					fwrite($handle, implode(',', $new_record));
				}
				fclose($handle);

				//Generate a diff with the unsanitised version
				$diff_path = self::PATH . 'sanitised_diff\\' . str_replace('txt', 'diff', $file_name);
				exec("git diff $in_path $out_path > $diff_path");
			}
			unset($time, $record);
			//Work out the duplicates and the missings
			foreach ($records as $time => $record) {
				$expected = $expected_counts[$time];
				$actual = count($record);
				$diff = $actual - $expected;
				if($diff < 0) {
					$res['miss'][$time] = $diff;
				} elseif ($diff > 0) {
					$res['dup'][$time] = $diff;
				}
			}

			$tots['missing'] += count($res['miss']);
			$tots['duplicate'] += count($res['dup']);
			$tots['miss_dup_matches'] += $res['miss_dup_match_count'];
			$tots['miss_blanks'] += $res['miss_blank_count'];

			unset($live_vals_handle);
		}
		$this->max = ($period_count > 1) ? ceil(100 / $period_count) : 1500;
//		xdebug_break();

		//Should move this to a dedicated View
		echo "<p>Total: {$tots['missing']} missing, {$tots['duplicate']} duplicate
			({$tots['miss_dup_matches']} dups resolved, {$tots['miss_blanks']} blanks resolved).</p>";
		foreach ($result as $at => $resi) {
			$miss_count = count($resi['miss']);
			$dup_count = count($resi['dup']);
			$is_err = count($resi['error']) > 0;
			$is_warn = count($resi['warning']) > 0;
			$all_good = ($miss_count + $dup_count === 0) && !$is_err && !$is_warn;
			$summary = $all_good ? 'All good' : "$miss_count missing, $dup_count duplicate";
			$fixes = ($resi['miss_dup_match_count'] + $resi['miss_blank_count'] == 0) ? 'No fixes' :
				"Fixed {$resi['miss_dup_match_count']} dups, {$resi['miss_blank_count']} blanks";
			echo $at . $summary . " ($fixes)<br />";

			if($is_err) {
				echo "<h1>!!! ERRORS !!!</h1>";
				foreach ($resi['error'] as $err) {
					echo "! <span style='font-size:110%'>$err</span> !<br />";
				}
			}
			if($is_warn) {
				echo "<h3>WARNINGS</h3>";
				foreach ($resi['warning'] as $warn) {
					echo "$warn<br />";
				}
			}
			if($miss_count) {
				echo $this->get_missdup($resi['miss'], $miss_count, 'MISSING');
			}
			if($dup_count) {
				echo $this->get_missdup($resi['dup'], $dup_count, 'DUPLICATES');
			}
			echo $all_good ? '' : "#############################<br />#############################<br />";
		}
	}

	function sanitise_raw_logs() {
		$this->validate_raw_logs(true);
	}

	function legacy_wind_speed_fix() {
		foreach ($this->period as $dt) {
			echo "Processing ". $dt->format('jS M Y') .'<br />';

			$file_name = $dt->format('Ymd'). 'log.txt';
			$wd_path = self::PATH. 'from_WD\\'. $file_name;
			$legacy_path = self::PATH . $file_name;
			if(!file_exists($wd_path) || !file_exists($legacy_path)) {
				echo "Skipping $file_name - does not exist<br />###<br />";
				continue;
			}
			$speeds = array_fill(0, 1440, []);
			$gusts = array_fill(0, 1440, []);

			# Rip out the WD-based wind speeds and gusts
			$wd_vals_handle = file($wd_path);
			foreach ($wd_vals_handle as $wd_vals) {
				$raw_wd_vals = explode(',', $wd_vals);
				$hour = (int)($raw_wd_vals[0]);
				$minute = (int)($raw_wd_vals[1]);
				$time = (60 * $hour) + $minute;
				$speeds[$time][] = $raw_wd_vals[3];
				$gusts[$time][] = $raw_wd_vals[4];
			}

			$leg_spds = [];
			$wd_spds = [];
			$leg_gsts = [];
			$wd_gsts = [];

			# Match to the legacy wind vals
			$ouput = [];
			$legacy_vals_handle = file($legacy_path);
			foreach ($legacy_vals_handle as $legacy_vals) {
				$raw_vals = explode(',', $legacy_vals);
				$hour = (int)($raw_vals[0]);
				$minute = (int)($raw_vals[1]);
				$time = (60 * $hour) + $minute;
				$speed = &$raw_vals[3];
				$gust = &$raw_vals[4];
				//Max and avgs
				$leg_spds[] = $raw_vals[3];
				$leg_gsts[] = $raw_vals[4];

				$wd_quantity = count($speeds[$time]);
				if($wd_quantity === 1) {
					//Sanitise, after check that the values are roughly the same
					$insane_speed = $this->is_legacy_wind_insane($speed, 'speed', $speeds[$time][0], $hour, $minute);
					$insane_gust = $this->is_legacy_wind_insane($gust, 'gust', $gusts[$time][0], $hour, $minute);
					if($insane_speed) {
						$speed = Maths::round(max(0, $speed));
					} else {
						$speed = $speeds[$time][0];
					}
					if($insane_gust) {
					} else {
						$gust = $gusts[$time][0];
					}
					$wd_spds[] = $speeds[$time][0];
					$wd_gsts[] = $gusts[$time][0];
				} else {
					//Clean-up existing values but don't modify (no WD val or too many => inaccurate)
					$speed = Maths::round(max(0, $speed));
					//Gust is fine
				}
				$ouput[] = $raw_vals;
			}

			//compare max and avg, spit out and flag major discrepancies
			$this->is_legacy_wind_insane(max($leg_spds), 'Max_Speed', max($wd_spds), 'MAX', 'MAX', 10, 0.8, 0.6);
			$this->is_legacy_wind_insane(max($leg_gsts), 'Max_Gust', max($wd_gsts), 'MAX', 'MAX', 15, 0.8, 0.6);
			$this->is_legacy_wind_insane(Maths::mean($leg_spds), 'Avg_Speed', Maths::mean($wd_spds), 'AVG', 'AVG', 2, 0.2, 0.1);
			$this->is_legacy_wind_insane(Maths::mean($leg_gsts), 'Avg_Gust', Maths::mean($wd_gsts), 'AVG', 'AVG', 2, 0.2, 0.1);

			$out_path = self::PATH ."legacy_wind_fix\\". $file_name;
			$handle = fopen($out_path, 'w');
			foreach ($ouput as $out) {
				fwrite($handle, implode(',', $out));
			}
			fclose($handle);

			$diff_path = self::PATH . 'leg_diff\\' . str_replace('txt', 'diff', $file_name);
			exec("git diff $legacy_path $out_path > $diff_path");
		}
	}

	private function is_legacy_wind_insane($legacy, $type, $wd, $hour, $minute,
						$thresh_ignore=10, $thresh_err=1.5, $thresh_warn=1.2) {
		if($legacy > $thresh_ignore && $wd > $thresh_ignore) {
			$speed_diff = abs($legacy - $wd) / $legacy;
			if($speed_diff > $thresh_err) {
				echo "<b>!!!!!!!! ERROR !!!!!!!!</b>$hour:$minute $type. Diff of $speed_diff. WD: $wd, legacy: $legacy<br />";
				return true;
			} elseif($speed_diff > $thresh_warn) {
				echo "Warning! $hour:$minute $type. Diff of $speed_diff. WD: $wd, legacy: $legacy<br />";
			}
		}
		return false;
	}

//	function raw_log_diff() {
//		foreach ($this->period as $dt) {
//			echo "Processing ". $dt->format("jS M Y");
//
//			$file_path1 = self::PATH. $dt->format('Ymd'). 'log.txt';
//			$file_path2 = self::PATH.'from_WD\\'. $dt->format('Ymd'). 'log.txt';
//			if(!file_exists($file_path1)) {
//				echo ". Skipping - main file does not exist". $dt->format("jS M Y");
//				continue;
//			}
//
//		}
//	}
//	private function get_records($filepath) {
//		if(!file_exists($filepath)) {
//			return false;
//		}
//		echo "<br />";
//
//		$live_vals_handle1 = file($file_path1);
//		$records = array_fill(0, 1440, []);
//		foreach ($live_vals_handle1 as $live_vals1) {
//			$raw_live_vals1 = explode(',', $live_vals1);
//			array_map(function($v){round($v, 1);}, $raw_live_vals1);
//			$hour = (int)($raw_live_vals[0]);
//			$minute = (int)($raw_live_vals[1]);
//			$time = (60 * $hour) + $minute;
//		}
//
//	}
//
//	function deformat() {
//		foreach ($this->period as $dt) {
//			echo "Processing ". $dt->format("jS M Y");
//
//			$file_path = self::PATH. $dt->format('Ymd'). 'log.txt';
//			if(!file_exists($file_path)) {
//				echo ". Skipping - main file does not exist". $dt->format("jS M Y");
//				continue;
//			}
//			echo "<br />";
//
//			$out_path = $file_path2 = self::PATH.'unformatted\\'. $dt->format('Ymd'). 'log.txt';
//			$out_handle = fopen($out_path, 'w');
//
//			$live_vals_handle = file($file_path);
//			foreach ($live_vals_handle as &$live_vals) {
//				fwrite($out_handle, implode(',', array_map(function($v){return round($v, 1);}, explode(',', $live_vals))) ."\r\n");
//			}
//			fclose($out_handle);
//		}
//	}

	private function get_missdup($missing, $count, $title) {
		$res = '';
		$sub = (count($missing) > $this->max) ? "first {$this->max}" : 'all';
		$res .= "<h5>$title ($sub):</h5>";
		$shown = 0;
		foreach ($missing as $time => $cnt) {
			if($shown == $this->max) {
				$rem = $count - $shown;
				$res .= "...$rem MORE...<br />";
				break;
			}
			$pretty_time = floor($time / 60) .':'. $time % 60;
			$res .= "$pretty_time _ $cnt<br />";
			$shown++;
		}
		return $res;
	}
}

?>
