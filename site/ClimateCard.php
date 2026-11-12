<?php
/**
 * Home page "Climate & extremes" card: today's values against the daily
 * normals and the station's records for this calendar date, this month and
 * all time, with flags when today sets (or is on course for) a record.
 *
 * Record tables come from the station's daily data (2009 onwards) and are
 * precomputed into CACHE_ROOT/serialised_climrec.txt. Cron rebuilds the cache
 * after each data refresh; a request rebuilds it only when it is for another
 * day or the daily data files have changed since it was built (one request
 * at a time, others keep using the previous tables meanwhile). Today's row is
 * always left out of the tables, so today is compared with every earlier day.
 */
require_once __DIR__ . '/ClimateMeanClimo.php';

class ClimateCard {
	const CACHE_FILE = 'serialised_climrec.txt';
	const CACHE_VERSION = 5;

	/** First year used for records (the station record). */
	const SINCE_YEAR = Site::BASE_YEAR;

	/** Day max covers 09-21. A record low day max is provisional from 15:00
	 *  if the current temperature is at least LOW_DAYMAX_DROP below the running
	 *  day max, otherwise from 17:00, and is confirmed at 21:00. */
	const LOW_DAYMAX_FROM_HOUR = 15;
	const LOW_DAYMAX_FALLBACK_HOUR = 17;
	const LOW_DAYMAX_DROP = 0.5;
	const DAYMAX_END_HOUR = 21;
	/** Night min covers 21-09: a record high night min is confirmed at 09:00. */
	const NIGHTMIN_END_HOUR = 9;
	/** Avg temp (midnight-midnight): provisional low/high from 15:00 using the
	 *  projected 24h mean ± MEAN_MARGIN, confirmed at midnight. */
	const MEAN_PROVISIONAL_FROM_HOUR = 15;
	const MEAN_FINAL_HOUR = 24;
	const MEAN_MARGIN = 0.5;

	/** Wet-day threshold (mm) for the rain normal: % of years with rain on this date. */
	const WET_MM = 0.2;

	/** Dates kept per record (the latest ones); 'n' holds the full count. */
	const KEEP_DATES = 3;

	const RECORD = 'record';
	const TIE = 'tie';

	/**
	 * Card rows, keyed by daily data series. 'lo'/'hi' say which extremes are
	 * shown; 'flagLo'/'flagHi' say which can be flagged:
	 *   'now' - as soon as today passes the record (the value can only move further past it)
	 *   ['from' => h, 'final' => h] - shown as provisional ('so far') from hour 'from'
	 *     and as confirmed from hour 'final', when the series' period has ended.
	 *   ['from' => h, 'final' => h, 'project' => 'tmean', 'margin' => x] - from hour
	 *     'from', compare the projected 24h mean ± x instead of the running mean.
	 *   Low day max also requires a LOW_DAYMAX_DROP fall from the running max
	 *   before 17:00 (see flag()).
	 * 'dp' is the rounding (native units) used for records and comparisons.
	 * 'norm' is the normals series.
	 */
	public static function rows() {
		return array(
			'daymax' => array('label' => 'Day max', 'title' => 'Highest temperature 09:00-21:00',
				'unit' => Wx::Temperature, 'dp' => 1, 'norm' => 'tmax', 'lo' => true, 'hi' => true,
				'flagHi' => 'now', 'flagLo' => array('from' => self::LOW_DAYMAX_FROM_HOUR, 'final' => self::DAYMAX_END_HOUR,
					'drop' => self::LOW_DAYMAX_DROP),
				'nameHi' => 'high day max', 'nameLo' => 'low day max'),
			'nightmin' => array('label' => 'Night min', 'title' => 'Lowest temperature 21:00-09:00 (overnight into today)',
				'unit' => Wx::Temperature, 'dp' => 1, 'norm' => 'tmin', 'lo' => true, 'hi' => true,
				'flagLo' => 'now', 'flagHi' => array('from' => self::NIGHTMIN_END_HOUR, 'final' => self::NIGHTMIN_END_HOUR),
				'nameLo' => 'low night min', 'nameHi' => 'high night min'),
		'tmean' => array('label' => 'Avg temp', 'title' => 'Mean temperature (midnight to midnight)',
			'unit' => Wx::Temperature, 'dp' => 1, 'norm' => 'tmean', 'lo' => true, 'hi' => true,
			'flagLo' => array('from' => self::MEAN_PROVISIONAL_FROM_HOUR, 'final' => self::MEAN_FINAL_HOUR,
				'project' => 'tmean', 'margin' => self::MEAN_MARGIN),
			'flagHi' => array('from' => self::MEAN_PROVISIONAL_FROM_HOUR, 'final' => self::MEAN_FINAL_HOUR,
				'project' => 'tmean', 'margin' => self::MEAN_MARGIN),
			'nameHi' => 'high avg temp', 'nameLo' => 'low avg temp'),
			'rain' => array('label' => 'Rain', 'unit' => Wx::Rain, 'dp' => 1, 'norm' => 'rain', 'lo' => false, 'hi' => true,
				'flagHi' => 'now', 'nameHi' => 'daily rainfall'),
		'sunhr' => array('label' => 'Sunshine', 'unit' => Wx::Hours, 'dp' => 1, 'norm' => 'sunhr', 'lo' => 'day', 'hi' => true,
			'flagHi' => 'now', 'nameHi' => 'daily sunshine'),
		'wmean' => array('label' => 'Mean wind', 'unit' => Wx::Wind, 'dp' => 1, 'norm' => 'wmean', 'lo' => true, 'hi' => true),
		);
	}

	/** Daily data files the record tables are built from. */
	private static function sourceFiles() {
		$out = array();
		foreach (array_keys(self::rows()) as $var) {
			$out[$var] = CACHE_ROOT . "serialised_dat_new_$var.txt";
		}
		return $out;
	}

	/** Cheap fingerprint of the source files (mtime and size), so data edits invalidate the cache. */
	private static function signature() {
		clearstatcache();
		$sig = array();
		foreach (self::sourceFiles() as $var => $f) {
			$sig[$var] = is_file($f) ? filemtime($f) . ':' . filesize($f) : '-';
		}
		return $sig;
	}

	private static function readCache() {
		$path = CACHE_ROOT . self::CACHE_FILE;
		if (!is_file($path)) { return null; }
		$c = @unserialize(file_get_contents($path));
		return (is_array($c) && ($c['v'] ?? null) === self::CACHE_VERSION) ? $c : null;
	}

	private static function fresh($c, $ymd, $sig) {
		return $c !== null && ($c['date'] ?? null) === $ymd && ($c['sig'] ?? null) === $sig;
	}

	/**
	 * Record tables for the date of $now. Served from the cache; rebuilt when it
	 * is missing, for another day, or older than the data. Only one process
	 * rebuilds at a time; the others keep using a same-day cache meanwhile.
	 */
	public static function records($now = null) {
		$now = ($now === null) ? time() : (int)$now;
		$ymd = date('Y-m-d', $now);
		$sig = self::signature();
		$c = self::readCache();
		if (self::fresh($c, $ymd, $sig)) {
			return $c;
		}
		$sameDay = ($c !== null && ($c['date'] ?? null) === $ymd);
		$lockFile = CACHE_ROOT . 'serialised_climrec.lock';
		$lock = @fopen($lockFile, 'c');
		if ($lock) { @chmod($lockFile, 0664); } // cron and web users share it
		if ($lock && !flock($lock, LOCK_EX | LOCK_NB)) {
			if ($sameDay) {
				fclose($lock);
				return $c;
			}
			flock($lock, LOCK_EX);
		}
		// Another process may have finished a rebuild while we waited.
		$c2 = self::readCache();
		if (self::fresh($c2, $ymd, $sig)) {
			$out = $c2;
		} else {
			$out = self::build($now, $sig);
			nw3_atomic_write(CACHE_ROOT . self::CACHE_FILE, serialize($out));
		}
		if ($lock) { flock($lock, LOCK_UN); fclose($lock); }
		return $out;
	}

	/** Make sure the cache is current (called by cron after each data refresh). */
	public static function warm($now = null) {
		return self::records($now);
	}

	/**
	 * Extremes for each card row over days before today:
	 *   day   - this calendar date in every year
	 *   month - every day of this calendar month in every year (this month's earlier days included)
	 *   all   - every day
	 * Each extreme is ['v' => value, 'n' => number of days with that value, 'at' => the latest few of those dates, oldest first].
	 */
	public static function build($now = null, $sig = null) {
		$now = ($now === null) ? time() : (int)$now;
		$today = date('Y-m-d', $now);
		$m = (int)date('n', $now);
		$d = (int)date('j', $now);
		$out = array('v' => self::CACHE_VERSION, 'date' => $today, 'built' => time(),
			'sig' => ($sig === null) ? self::signature() : $sig, 'vars' => array());
		foreach (self::rows() as $var => $cfg) {
			$res = array('day' => self::blank(), 'month' => self::blank(), 'all' => self::blank(),
				'first' => null, 'dayYears' => 0, 'dayWet' => 0);
			foreach (Data::getAllData($var) as $y => $months) {
				if ((int)$y < self::SINCE_YEAR || !is_array($months)) { continue; }
				foreach ($months as $mm => $days) {
					if (!is_array($days)) { continue; }
					foreach ($days as $dd => $val) {
						if ($val === null || $val === '' || !is_numeric($val)) { continue; }
						$date = sprintf('%04d-%02d-%02d', $y, $mm, $dd);
						if ($date >= $today) { continue; }
						$v = round((float)$val, $cfg['dp']);
						if ($res['first'] === null || $date < $res['first']) { $res['first'] = $date; }
						self::fold($res['all'], $v, $date);
						if ((int)$mm === $m) {
							self::fold($res['month'], $v, $date);
							if ((int)$dd === $d) {
								self::fold($res['day'], $v, $date);
								$res['dayYears']++;
								if ($v >= self::WET_MM) { $res['dayWet']++; }
							}
						}
					}
				}
			}
			foreach (array('day', 'month', 'all') as $s) {
				foreach (array('lo', 'hi') as $k) {
					// Keep the count and only the latest few dates (a record of 0 mm is held by thousands of days)
					sort($res[$s][$k]['at']);
					$res[$s][$k]['n'] = count($res[$s][$k]['at']);
					$res[$s][$k]['at'] = array_slice($res[$s][$k]['at'], -self::KEEP_DATES);
				}
			}
			$out['vars'][$var] = $res;
		}
		return $out;
	}

	private static function blank() {
		return array('lo' => array('v' => null, 'n' => 0, 'at' => array()), 'hi' => array('v' => null, 'n' => 0, 'at' => array()));
	}

	private static function fold(&$ext, $v, $date) {
		foreach (array('lo' => -1, 'hi' => 1) as $k => $dir) {
			$cur = $ext[$k]['v'];
			if ($cur === null || ($v - $cur) * $dir > 0) {
				$ext[$k] = array('v' => $v, 'at' => array($date));
			} elseif ($v == $cur) {
				$ext[$k]['at'][] = $date;
			}
		}
	}

	/** Today's values (so far) from the live data, keyed like rows(); all null until cron has written today's summary. */
	public static function todayValues() {
		$f = CACHE_ROOT . 'serialised_datNow.txt';
		if (!is_file($f) || date('Ymd', filemtime($f)) !== date('Ymd')) {
			return array_fill_keys(array_keys(self::rows()), null);
		}
		$NOW = Live::$NOW;
		$get = function ($agg, $var) use ($NOW) {
			$v = $NOW[$agg][$var] ?? null;
			return ($v === null || $v === '' || !is_numeric($v)) ? null : (float)$v;
		};
		// Day max (09-21): '-' before 09:00. Night min (21-09): today's readings to 09:00
		// (99 when there are none) and yesterday's 21:00-midnight minimum, saved at midnight.
		$nightMin = $get('min', 'night');
		if ($nightMin !== null && $nightMin >= 99) { $nightMin = null; }
		$nminFile = ROOT . 'nmin.txt';
		if (is_file($nminFile) && date('Ymd', filemtime($nminFile)) === date('Ymd')) {
			$prev = explode(',', (string)file_get_contents($nminFile));
			if (is_numeric($prev[0]) && (float)$prev[0] < 99) {
				$nightMin = ($nightMin === null) ? (float)$prev[0] : min($nightMin, (float)$prev[0]);
			}
		}
		$sun = $NOW['sun']['hours'] ?? null;
		return array(
			'daymax' => $get('max', 'day'),
			'nightmin' => $nightMin,
			'tmean' => $get('mean', 'temp'),
			'rain' => is_numeric(Live::$rain) ? (float)Live::$rain : null,
			'sunhr' => is_numeric($sun) ? (float)$sun : null,
			'wmean' => $get('mean', 'wind'),
		);
	}

	/**
	 * Project today's midnight-to-midnight mean temperature (°C).
	 *
	 * Remaining hours follow this station's month-by-hour climatology, pinned
	 * to $currentTemp. Evaluated at clock $hour:$minute (local). At HH:00 this
	 * matches the historical study: samples so far are the previous $hour hours,
	 * the pin hour is $hour-1, and remaining hours are $hour..23 (60 min each).
	 *
	 * @param int $month 1–12
	 * @param int $hour 0–23
	 * @param float $runningMean mean of samples so far
	 * @param float $currentTemp latest temperature
	 * @param int $minute 0–59
	 * @param int|null $minutesSoFar defaults to $hour*60 + $minute
	 * @return float|null
	 */
	public static function projectMean($month, $hour, $runningMean, $currentTemp, $minute = 0, $minutesSoFar = null) {
		$month = (int)$month;
		$hour = (int)$hour;
		$minute = (int)$minute;
		if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59) { return null; }
		if ($minutesSoFar === null) {
			$minutesSoFar = $hour * 60 + $minute;
		}
		$minutesSoFar = max(0, (int)$minutesSoFar);
		$nRem = 1440 - $minutesSoFar;
		if ($nRem < 0) { return null; }
		if ($nRem === 0) { return (float)$runningMean; }

		// At HH:00, remaining hours are H..23, pinned to the last complete hour (H-1),
		// as in the study. Mid-hour, pin to the current hour and weight the rest of
		// this hour plus H+1..23.
		if ($minute === 0) {
			if ($hour < 1) { return null; }
			$pinHour = $hour - 1;
			$remHours = range($hour, 23);
			$weights = array_fill(0, count($remHours), 60);
		} else {
			$pinHour = $hour;
			$remHours = range($hour, 23);
			$weights = array(60 - $minute);
			for ($k = $hour + 1; $k <= 23; $k++) { $weights[] = 60; }
		}
		$pin = ClimateMeanClimo::at($month, $pinHour);
		if ($pin === null) { return null; }
		$sum = 0.0;
		$wsum = 0;
		foreach ($remHours as $i => $k) {
			$ck = ClimateMeanClimo::at($month, $k);
			if ($ck === null) { return null; }
			$w = $weights[$i];
			$sum += ((float)$currentTemp + ($ck - $pin)) * $w;
			$wsum += $w;
		}
		if ($wsum <= 0) { return null; }
		$projRem = $sum / $wsum;
		return (((float)$runningMean * $minutesSoFar) + ($projRem * $nRem)) / ($minutesSoFar + $nRem);
	}

	/**
	 * Flag for one direction of one row: null, or
	 * ['status' => record|tie, 'scope' => day|month|all, 'soFar' => bool, 'rec' => the extreme beaten or matched].
	 * The widest scope wins (all-time over monthly over the calendar date).
	 *
	 * $ctx may include currentTemp, month, minute, minutesSoFar (for mean
	 * projection and the low day-max drop test).
	 */
	public static function flag($cfg, $dir, $today, $recs, $hour, $ctx = array()) {
		$rule = $cfg[$dir === 'hi' ? 'flagHi' : 'flagLo'] ?? null;
		if ($rule === null || $today === null || $recs === null) { return null; }
		$soFar = false;
		if ($rule !== 'now') {
			$from = (int)$rule['from'];
			// Low day max: from 15:00 only if the temperature has already fallen
			// at least the configured drop from the running max; otherwise from 17:00.
			if ($dir === 'lo' && isset($rule['drop'])) {
				$curr = $ctx['currentTemp'] ?? null;
				$need = (float)$rule['drop'];
				if (!nw3_is_numeric($curr) || ((float)$today - (float)$curr) < $need - 1e-12) {
					$from = self::LOW_DAYMAX_FALLBACK_HOUR;
				}
			}
			if ($hour < $from) { return null; }
			$soFar = ($hour < (int)$rule['final']);
		}

		// Projected 24h mean: flag only if the conservative (margin-adjusted)
		// projection still beats the record. Status uses the projection itself.
		$isProjected = false;
		if (is_array($rule) && ($rule['project'] ?? null) === 'tmean') {
			$curr = $ctx['currentTemp'] ?? null;
			$month = $ctx['month'] ?? null;
			if (!nw3_is_numeric($curr) || !nw3_is_numeric($month)) { return null; }
			$minute = isset($ctx['minute']) ? (int)$ctx['minute'] : 0;
			$mins = $ctx['minutesSoFar'] ?? null;
			$proj = self::projectMean((int)$month, (int)$hour, (float)$today, (float)$curr, $minute, $mins);
			if ($proj === null) { return null; }
			$margin = isset($rule['margin']) ? (float)$rule['margin'] : self::MEAN_MARGIN;
			$conservative = ($dir === 'lo') ? ($proj + $margin) : ($proj - $margin);
			$compare = $proj;
			$today = $proj; // so rounding below is on the projection
			$minsUsed = ($mins !== null) ? (int)$mins : ((int)$hour * 60 + $minute);
			$isProjected = (1440 - $minsUsed) > 0;
		} else {
			$conservative = null;
		}

		$t = round($today, $cfg['dp']);
		// Totals that are still zero (no rain, sun or wind yet) only "tie" a record of zero.
		if ($dir === 'hi' && $t <= 0 && in_array($cfg['unit'], array(Wx::Rain, Wx::Hours, Wx::Wind), true)) { return null; }
		foreach (array('all', 'month', 'day') as $scope) {
			$rec = $recs[$scope][$dir] ?? null;
			if ($rec === null || $rec['v'] === null) { continue; }
			if ($conservative !== null) {
				$cDiff = ($conservative - $rec['v']) * ($dir === 'hi' ? 1 : -1);
				if ($cDiff <= 0.00001) { continue; } // margin-adjusted value must still beat the record
			}
			$diff = ($t - $rec['v']) * ($dir === 'hi' ? 1 : -1);
			if ($diff > 0.00001) {
				return array('status' => self::RECORD, 'scope' => $scope, 'soFar' => $soFar, 'rec' => $rec, 'value' => $t) + ($isProjected ? array('projected' => true) : array());
			}
			if (abs($diff) <= 0.00001) {
				return array('status' => self::TIE, 'scope' => $scope, 'soFar' => $soFar, 'rec' => $rec, 'value' => $t) + ($isProjected ? array('projected' => true) : array());
			}
		}
		return null;
	}

	private static function fmt($v, $unit, $showUnit = false) {
		if ($v === null) { return '&ndash;'; }
		if ($unit === Wx::Hours) {
			return nw3_number_format((float)$v, 1) . ($showUnit ? ' hrs' : '');
		}
		if ($unit === Wx::Wind) {
			return Wx::conv($v, $unit, $showUnit);
		}
		return Wx::conv($v, $unit, $showUnit);
	}

	private static function fmtDate($date, $withYear = true) {
		$ts = strtotime($date . ' 12:00');
		return date($withYear ? 'd M Y' : 'd M', $ts);
	}

	/** Who holds a record, for the notes: the most recent year (calendar date) or date. */
	private static function prevWhen($rec, $scope) {
		$at = $rec['at'];
		if (!$at) { return ''; }
		$last = end($at);
		return ($scope === 'day') ? substr($last, 0, 4) : self::fmtDate($last);
	}

	/** Small grey "(2014)" after a record value: the most recent year it was set, with all the dates in the tooltip. */
	private static function whenHtml($rec, $scope) {
		$at = $rec['at'];
		if (!$at) { return ''; }
		$n = $rec['n'] ?? count($at);
		$last = end($at);
		if ($n > self::KEEP_DATES) {
			$title = 'Set on ' . $n . ' days, most recently ' . self::fmtDate($last);
		} elseif ($n > 1) {
			$title = 'Set on ' . implode(', ', array_map(function ($x) { return self::fmtDate($x); }, $at));
		} else {
			$title = self::fmtDate($last);
		}
		return ' <span class="clim-yr" title="' . htmlspecialchars($title) . '">(' . substr($last, 0, 4) . ')</span>';
	}

	private static function dot($flag) {
		if ($flag === null) { return ''; }
		// Provisional flags are hollow rings in the same colour (red new record, orange equal)
		$cls = $flag['status'] . ($flag['soFar'] ? ' clim-dot-sofar' : '');
		$title = ($flag['status'] === self::TIE ? 'Equals record' : 'New record') . ($flag['soFar'] ? ' so far today (provisional)' : '');
		return '<span class="clim-dot clim-dot-' . $cls . '" title="' . $title . '"></span>';
	}

	/**
	 * Render the card.
	 * @param int|null $now London time to render for (default now)
	 * @param array|null $today today's values keyed like rows() (default: live data)
	 */
	public static function renderCard($now = null, $today = null) {
		$now = ($now === null) ? time() : (int)$now;
		$today = ($today === null) ? self::todayValues() : $today;
		$recs = self::records($now);
		$m = (int)date('n', $now);
		$d = (int)date('j', $now);
		$y = (int)date('Y', $now);
		$hour = (int)date('G', $now);
		$minute = (int)date('i', $now);
		$currTemp = (isset(Live::$temp) && nw3_is_numeric(Live::$temp)) ? (float)Live::$temp : null;
		$flagCtx = array(
			'currentTemp' => $currTemp,
			'month' => $m,
			'minute' => $minute,
		);
		$dateLabel = date('d M', $now);
		$monthLabel = date('F', $now);
		$scopeLabels = array('day' => $dateLabel, 'month' => $monthLabel, 'all' => 'All-time');

		$notes = array();
		$active = array();
		$body = '';
		foreach (self::rows() as $var => $cfg) {
			$r = $recs['vars'][$var] ?? null;
			$unit = $cfg['unit'];
			$tv = $today[$var] ?? null;
			$flags = array(
				'lo' => ($r && $cfg['lo']) ? self::flag($cfg, 'lo', $tv, $r, $hour, $flagCtx) : null,
				'hi' => ($r && $cfg['hi']) ? self::flag($cfg, 'hi', $tv, $r, $hour, $flagCtx) : null,
			);

			// Normal for the date
			$normTitle = 'Normal for ' . $dateLabel . ' (1991-2020)';
			$normV = null;
			if ($cfg['norm'] !== null) {
				$normV = LTA::getDailyAnom($cfg['norm'], $m, $d, $y);
				$normV = nw3_is_numeric($normV) ? (float)$normV : null;
			}
			if ($var === 'rain') {
				$n = $r['dayYears'] ?? 0;
				$normal = '<span title="Average daily rainfall for ' . $dateLabel . ' (1991-2020)">' . self::fmt($normV, $unit) . '</span>';
				if ($n) {
					$normal .= ' <span class="clim-wet" title="Rain (&ge;' . self::WET_MM . ' mm) fell on ' . $dateLabel . ' in '
						. $r['dayWet'] . ' of ' . $n . ' years since ' . self::SINCE_YEAR . '">'
						. (int)round(100 * $r['dayWet'] / $n) . '%</span>';
				}
				$normV = null; // no difference-from-normal for rain
			} elseif ($normV === null) {
				$normal = '&ndash;';
			} else {
				if ($var === 'daymax' || $var === 'nightmin') {
					$normTitle = 'Normal ' . ($var === 'daymax' ? 'maximum' : 'minimum') . ' for ' . $dateLabel . ' (1991-2020)';
				}
				$normal = '<span title="' . $normTitle . '">' . self::fmt($normV, $unit) . '</span>';
			}

			// Today (so far) with its difference from normal
			$todayHtml = '<b>' . self::fmt($tv, $unit, true) . '</b>' . self::dot($flags['hi'] ?? $flags['lo']);
			if ($tv !== null && $normV !== null && $unit === Wx::Temperature) {
				$todayHtml .= ' <span class="clim-anom" title="Difference from normal">'
					. Wx::conv($tv - $normV, Wx::AbsTemp, false, true) . '</span>';
			}

			$label = isset($cfg['title']) ? '<span title="' . $cfg['title'] . '">' . $cfg['label'] . '</span>' : $cfg['label'];
			$row = '<tr><th scope="row">' . $label . '</th>'
				. '<td class="clim-today">' . $todayHtml . '</td>'
				. '<td class="clim-norm">' . $normal . '</td>';
			foreach (array('day', 'month', 'all') as $scope) {
				foreach (array('lo', 'hi') as $dir) {
					$show = $cfg[$dir];
					if ($show === 'day') { $show = ($scope === 'day'); }
					$cell = '&ndash;';
					if ($show && $r && $r[$scope][$dir]['v'] !== null) {
						$rec = $r[$scope][$dir];
						$f = $flags[$dir];
						$cell = self::fmt($rec['v'], $unit)
							. (($f !== null && $f['scope'] === $scope) ? self::dot($f) : '')
							. self::whenHtml($rec, $scope);
					}
					$row .= '<td class="clim-s-' . $scope . ($dir === 'lo' ? ' clim-gs' : '') . '">' . $cell . '</td>';
				}
			}
			$body .= $row . '</tr>';

			foreach (array('hi', 'lo') as $dir) {
				$f = $flags[$dir];
				if ($f === null) { continue; }
				$active[$f['status'] . ($f['soFar'] ? '-sofar' : '')] = true;
				$name = $cfg[$dir === 'hi' ? 'nameHi' : 'nameLo'] ?? '';
				$what = ($f['scope'] === 'all') ? 'All-time record ' . $name
					: (($f['scope'] === 'month') ? $monthLabel . ' record ' . $name : 'Record ' . $name . ' for ' . $dateLabel);
				$prev = $f['rec'];
				$shown = $f['value'] ?? $tv;
				$fig = '<b>' . self::fmt($shown, $unit, true) . '</b>'
					. (!empty($f['projected']) ? ' (projected)' : '');
				$text = ($f['status'] === self::TIE ? 'Equals ' . lcfirst($what) : $what)
					. ($f['soFar'] ? ' so far' : '') . ': ' . $fig . ' '
					. ($f['status'] === self::TIE ? '' : 'beats ' . self::fmt($prev['v'], $unit, true) . ' ')
					. '(' . self::prevWhen($prev, $f['scope']) . ')';
				$rank = array('all' => 0, 'month' => 1, 'day' => 2);
				$notes[] = array($rank[$f['scope']], '<li class="clim-note">' . self::dot($f) . $text . '</li>');
			}
		}
		usort($notes, function ($a, $b) { return $a[0] - $b[0]; });

		$since = self::SINCE_YEAR;
		echo '<div class="wx-card wx-card-clim" id="clim-card">';
		echo '<div class="wx-card-head">' . self::icon()
			. '<span class="wx-card-label">Climate &amp; extremes for ' . $dateLabel . '</span>'
			. '<span class="clim-scopes" role="group" aria-label="Records to show">';
		foreach ($scopeLabels as $s => $lab) {
			echo '<button type="button" data-clim-scope="' . $s . '">' . ($s === 'all' ? 'All-time' : $lab) . '</button>';
		}
		echo '</span></div>';
		if ($notes) {
			echo '<ul class="clim-notes">' . implode('', array_map(function ($n) { return $n[1]; }, $notes)) . '</ul>';
		}
		echo '<div class="clim-table-wrap"><table class="clim-table">';
		echo '<thead><tr><th rowspan="2"></th><th rowspan="2">Today</th><th rowspan="2">Normal</th>';
		foreach ($scopeLabels as $s => $lab) {
			echo '<th colspan="2" class="clim-s-' . $s . ' clim-grp clim-gs">' . $lab . ' records</th>';
		}
		echo '</tr><tr>';
		foreach ($scopeLabels as $s => $lab) {
			echo '<th class="clim-s-' . $s . ' clim-gs">Low</th><th class="clim-s-' . $s . '">High</th>';
		}
		echo '</tr></thead><tbody>' . $body . '</tbody></table></div>';
		// Key: only the marker types shown on the card right now
		$key = '';
		foreach (array(self::RECORD => 'new record', self::TIE => 'equals record') as $st => $lab) {
			if (isset($active[$st])) {
				$key .= '<span class="clim-legend"><span class="clim-dot clim-dot-' . $st . '"></span>' . $lab . '</span> ';
			}
		}
		$rings = '';
		foreach (array(self::RECORD, self::TIE) as $st) {
			if (isset($active[$st . '-sofar'])) { $rings .= '<span class="clim-dot clim-dot-' . $st . ' clim-dot-sofar"></span>'; }
		}
		if ($rings !== '') {
			$key .= '<span class="clim-legend">' . $rings . 'provisional (so far today)</span> ';
		}
		echo '<div class="clim-foot">'
			. ($key !== '' ? $key . '&middot; ' : '')
			. 'Records since ' . $since . ' (ties show most recent year) &middot; '
			. 'day max 09-21, night min 21-09 &middot; '
			. '<a href="/wxaverages.php" title="Long-term NW3 climate averages">normals</a> 1991-2020; '
			. 'rain normal is average total and share of years with &ge;' . self::WET_MM . ' mm on this date &middot; '
			. '<a href="/RankDay.php" title="Daily records and rankings">more records</a></div>';
		echo '</div>';
	}

	private static function icon() {
		return '<svg class="wx-card-icon" width="36" height="36" viewBox="0 0 24 24" aria-hidden="true">'
			. '<path fill="currentColor" d="M7 2h10v2h4v3a5 5 0 0 1-4.6 5A5 5 0 0 1 13 15v3h3v2H8v-2h3v-3a5 5 0 0 1-3.4-3A5 5 0 0 1 3 7V4h4V2zm0 4H5v1a3 3 0 0 0 2 2.8V6zm10 0v3.8A3 3 0 0 0 19 7V6h-2z"/>'
			. '</svg>';
	}
}
