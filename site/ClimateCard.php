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
class ClimateCard {
	const CACHE_FILE = 'serialised_climrec.txt';
	const CACHE_VERSION = 3;

	/** First year used for records (the station record). */
	const SINCE_YEAR = Site::BASE_YEAR;

	/** Day max covers 09-21: a record low day max is provisional from 15:00 and confirmed at 21:00. */
	const LOW_DAYMAX_FROM_HOUR = 15;
	const DAYMAX_END_HOUR = 21;
	/** Night min covers 21-09: a record high night min is confirmed at 09:00. */
	const NIGHTMIN_END_HOUR = 9;

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
	 * 'dp' is the rounding (native units) used for records and comparisons.
	 * 'norm' is the normals series.
	 */
	public static function rows() {
		return array(
			'daymax' => array('label' => 'Day max', 'title' => 'Highest temperature 09:00-21:00',
				'unit' => Wx::Temperature, 'dp' => 1, 'norm' => 'tmax', 'lo' => true, 'hi' => true,
				'flagHi' => 'now', 'flagLo' => array('from' => self::LOW_DAYMAX_FROM_HOUR, 'final' => self::DAYMAX_END_HOUR),
				'nameHi' => 'high day max', 'nameLo' => 'low day max'),
			'nightmin' => array('label' => 'Night min', 'title' => 'Lowest temperature 21:00-09:00 (overnight into today)',
				'unit' => Wx::Temperature, 'dp' => 1, 'norm' => 'tmin', 'lo' => true, 'hi' => true,
				'flagLo' => 'now', 'flagHi' => array('from' => self::NIGHTMIN_END_HOUR, 'final' => self::NIGHTMIN_END_HOUR),
				'nameLo' => 'low night min', 'nameHi' => 'high night min'),
			'tmean' => array('label' => 'Mean', 'title' => 'Mean temperature (midnight to midnight)',
				'unit' => Wx::Temperature, 'dp' => 1, 'norm' => 'tmean', 'lo' => true, 'hi' => true),
			'rain' => array('label' => 'Rain', 'unit' => Wx::Rain, 'dp' => 1, 'norm' => 'rain', 'lo' => false, 'hi' => true,
				'flagHi' => 'now', 'nameHi' => 'daily rainfall'),
			'sunhr' => array('label' => 'Sunshine', 'unit' => Wx::Hours, 'dp' => 1, 'norm' => 'sunhr', 'lo' => 'day', 'hi' => true,
				'flagHi' => 'now', 'nameHi' => 'daily sunshine'),
			'gust' => array('label' => 'Max gust', 'unit' => Wx::Wind, 'dp' => 0, 'norm' => null, 'lo' => false, 'hi' => true,
				'flagHi' => 'now', 'nameHi' => 'gust'),
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
			'gust' => $get('max', 'gust'),
		);
	}

	/**
	 * Flag for one direction of one row: null, or
	 * ['status' => record|tie, 'scope' => day|month|all, 'soFar' => bool, 'rec' => the extreme beaten or matched].
	 * The widest scope wins (all-time over monthly over the calendar date).
	 */
	public static function flag($cfg, $dir, $today, $recs, $hour) {
		$rule = $cfg[$dir === 'hi' ? 'flagHi' : 'flagLo'] ?? null;
		if ($rule === null || $today === null || $recs === null) { return null; }
		$soFar = false;
		if ($rule !== 'now') {
			if ($hour < (int)$rule['from']) { return null; }
			$soFar = ($hour < (int)$rule['final']);
		}
		$t = round($today, $cfg['dp']);
		// Totals that are still zero (no rain, sun or wind yet) only "tie" a record of zero.
		if ($dir === 'hi' && $t <= 0 && in_array($cfg['unit'], array(Wx::Rain, Wx::Hours, Wx::Wind), true)) { return null; }
		foreach (array('all', 'month', 'day') as $scope) {
			$rec = $recs[$scope][$dir] ?? null;
			if ($rec === null || $rec['v'] === null) { continue; }
			$diff = ($t - $rec['v']) * ($dir === 'hi' ? 1 : -1);
			if ($diff > 0.00001) {
				return array('status' => self::RECORD, 'scope' => $scope, 'soFar' => $soFar, 'rec' => $rec);
			}
			if (abs($diff) <= 0.00001) {
				return array('status' => self::TIE, 'scope' => $scope, 'soFar' => $soFar, 'rec' => $rec);
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
			return Wx::conv($v, $unit, $showUnit, false, -1); // whole numbers
		}
		return Wx::conv($v, $unit, $showUnit);
	}

	private static function fmtDate($date, $withYear = true) {
		$ts = strtotime($date . ' 12:00');
		return date($withYear ? 'j M Y' : 'j M', $ts);
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
		$title = ($flag['status'] === self::TIE ? 'Equals the record' : 'New record') . ($flag['soFar'] ? ' so far today (provisional)' : '');
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
		$dateLabel = date('j M', $now);
		$monthLabel = date('F', $now);
		$scopeLabels = array('day' => $dateLabel, 'month' => $monthLabel, 'all' => 'All-time');

		$notes = array();
		$body = '';
		foreach (self::rows() as $var => $cfg) {
			$r = $recs['vars'][$var] ?? null;
			$unit = $cfg['unit'];
			$tv = $today[$var] ?? null;
			$flags = array(
				'lo' => ($r && $cfg['lo']) ? self::flag($cfg, 'lo', $tv, $r, $hour) : null,
				'hi' => ($r && $cfg['hi']) ? self::flag($cfg, 'hi', $tv, $r, $hour) : null,
			);

			// Normal for the date
			$normTitle = 'Normal for ' . $dateLabel . ' (1971-2000)';
			$normV = null;
			if ($cfg['norm'] !== null) {
				$normV = LTA::getDailyAnom($cfg['norm'], $m, $d, $y);
				$normV = nw3_is_numeric($normV) ? (float)$normV : null;
			}
			if ($var === 'rain') {
				$n = $r['dayYears'] ?? 0;
				$normal = '<span title="Average daily rainfall for ' . $dateLabel . ' (1971-2000)">' . self::fmt($normV, $unit) . '</span>';
				if ($n) {
					$normal .= ' <span class="clim-wet" title="Rain (&ge;' . self::WET_MM . ' mm) fell on ' . $dateLabel . ' in '
						. $r['dayWet'] . ' of ' . $n . ' years since ' . self::SINCE_YEAR . '">'
						. (int)round(100 * $r['dayWet'] / $n) . '% wet</span>';
				}
				$normV = null; // no difference-from-normal for rain
			} elseif ($normV === null) {
				$normal = '&ndash;';
			} else {
				if ($var === 'daymax' || $var === 'nightmin') {
					$normTitle = 'Normal ' . ($var === 'daymax' ? 'maximum' : 'minimum') . ' for ' . $dateLabel . ' (1971-2000)';
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
				$name = $cfg[$dir === 'hi' ? 'nameHi' : 'nameLo'];
				$what = ($f['scope'] === 'all') ? 'All-time record ' . $name
					: (($f['scope'] === 'month') ? $monthLabel . ' record ' . $name : 'Record ' . $name . ' for ' . $dateLabel);
				$prev = $f['rec'];
				$text = ($f['status'] === self::TIE ? 'Equals the ' . lcfirst($what) : $what)
					. ($f['soFar'] ? ' so far' : '') . ': <b>' . self::fmt($tv, $unit, true) . '</b> '
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
		echo '<div class="clim-foot">'
			. '<span class="clim-legend"><span class="clim-dot clim-dot-record"></span>new record</span> '
			. '<span class="clim-legend"><span class="clim-dot clim-dot-tie"></span>equals record</span> '
			. '<span class="clim-legend"><span class="clim-dot clim-dot-record clim-dot-sofar"></span>'
			. '<span class="clim-dot clim-dot-tie clim-dot-sofar"></span>provisional (so far today)</span> &middot; '
			. 'Records since ' . $since . ' (station data; ties show the most recent year) &middot; '
			. 'day max 09-21, night min 21-09 &middot; '
			. '<a href="/wxaverages.php" title="Long-term NW3 climate averages">normals</a> 1971-2000; '
			. 'rain normal is the average total and the share of years with &ge;' . self::WET_MM . ' mm on this date &middot; '
			. '<a href="/RankDay.php" title="Daily records and rankings">more records</a></div>';
		echo '</div>';
	}

	private static function icon() {
		return '<svg class="wx-card-icon" width="36" height="36" viewBox="0 0 24 24" aria-hidden="true">'
			. '<path fill="currentColor" d="M7 2h10v2h4v3a5 5 0 0 1-4.6 5A5 5 0 0 1 13 15v3h3v2H8v-2h3v-3a5 5 0 0 1-3.4-3A5 5 0 0 1 3 7V4h4V2zm0 4H5v1a3 3 0 0 0 2 2.8V6zm10 0v3.8A3 3 0 0 0 19 7V6h-2z"/>'
			. '</svg>';
	}
}
