<?php
/**
 * Home-page "notable rankings" flag: does today, the current month / year,
 * the latest N-day period or a current spell rank near the top or bottom of
 * the station's all-time rankings for any variable?
 *
 * Built by warm_rank_flags.php (cron_main, every 5 minutes, in the background)
 * into CACHE_ROOT/rank_flags.json; the home page only reads that file.
 *
 * Qualifies: percentile >= PCT, or rank <= TOP_N with percentile >= TOP_N_PCT
 * (so a top-10 inside a big tie, e.g. one of 200 frost-free Octobers, or in a
 * short record does not count), in either direction. Rank and percentile follow
 * the ranking pages (Report::rankStats): competition rank, percentile = share
 * of other entries strictly beaten in that direction.
 *
 * Partial items (today so far, month / year so far, a spell still running)
 * count in the direction more data cannot undo (see $certainDir: totals,
 * counts, maxima, ranges, largest rises / falls and spell lengths can only
 * rise, minima only fall), or in either direction (means
 * included) once the period is at least COMPLETE_FRAC done: by time of day for
 * today, by elapsed time for the month / year. They are labelled provisional.
 *
 * Each item carries the first year of the data it was ranked against ("since").
 */
class RankFlags {

	const TOP_N = 10;
	const PCT = 99.0;
	const TOP_N_PCT = 90.0;
	const FILE = 'rank_flags.json';
	const CACHE_VERSION = 3; // bump to invalidate cache when logic changes
	const MAX_AGE = 3600;     // ignore a cache older than this (cron stalled)
	const SHOW = 4;           // items shown before "more"
	const COMPLETE_FRAC = 0.90; // partial periods this complete rank in both directions

	/** Variables never ranked here (directions, notes, manual flags, pond). */
	private static $skip = ['wdir', 'cloud', 'comms', 'extra', 'issues', 'away', 'spare', 'pond'];

	/** Skip daily (today/yesterday) flags only; monthly/annual/period/spell still allowed. */
	private static $skipDaily = ['fog'];

	/** Day definitions differ from midnight-midnight: no "today so far" for these. */
	private static $noToday = ['nightmin', 'daymax'];

	/** Variables with N-day period checks, and the lengths checked. */
	private static $periodVars = ['tmax', 'tmin', 'tmean', 'rain'];
	private static $periodLengths = [7, 30];

	/** Spell checks: [var, direction, threshold]. */
	private static $spellChecks = [
		['rain', 'above', 0.2], ['rain', 'below', 0.2],
		['tmax', 'heatwave', 5], ['tmax', 'coldsnap', 5],
		['tmin', 'heatwave', 5], ['tmin', 'coldsnap', 5],
	];

	private static $manualVars = [
		'sunhr', 'wethr', 'cloud', 'snow', 'lysnw', 'hail', 'thunder', 'fog',
		'comms', 'extra', 'issues', 'away', 'pond', 'spare',
	];

	public static function file() {
		return CACHE_ROOT . self::FILE;
	}

	/** Ranked daily variables, in the data-table category order. */
	public static function vars() {
		$out = [];
		foreach (Report::$categories as $types) {
			foreach ($types as $t) {
				if (isset(Wx::$daily[$t]) && !in_array($t, self::$skip, true)) { $out[] = $t; }
			}
		}
		return $out;
	}

	private static function isSum($var) {
		$m = Wx::$daily[$var];
		return !empty($m['summable']) || !empty($m['count-only']);
	}

	/** Daily lows that are just "nothing happened" (dry, calm-rate, frost-free days, calm max winds). */
	private static function dayLowBoring($var) {
		// Summable totals (rain, sunshine) have boring low values (zero/low totals are common)
		// Plus specific maxima: hourly/10-min rain rates, and the three wind maxima
		if (self::isSum($var)) { return true; }
		// Specific non-summable maxima with boring low values
		return in_array($var, ['hrmax', '10max', 'ratemax', 'ratemean', 'wmax', 'gust', 'w10max'], true);
	}

	/** Wind maxima with boring LOW values (calm maxima) - excludes LOW from ALL ranking periods. */
	private static function windMaxBoring($var) {
		return in_array($var, ['wmax', 'gust', 'w10max'], true);
	}

	/**
	 * Direction a partial daily value can only move in as the day goes on:
	 * 'hi' = can only rise (totals, counts, daily maxima, ranges, and the
	 * largest rise / fall in 10 min or 1 hr, which are stored as positive
	 * magnitudes), 'lo' = can only fall (daily minima). Anything not listed
	 * (means, percentages, derived rates, new variables) is uncertain both
	 * ways and only ranks once the period is COMPLETE_FRAC done.
	 */
	private static $certainDir = [
		'tmax' => 'hi', 'rain' => 'hi', 'hrmax' => 'hi', '10max' => 'hi', 'ratemax' => 'hi',
		'wmax' => 'hi', 'gust' => 'hi', 'w10max' => 'hi', 'hmax' => 'hi', 'pmax' => 'hi',
		'dmax' => 'hi', 'fmax' => 'hi', 'daymax' => 'hi',
		'sunhr' => 'hi', 'wethr' => 'hi', 'afhrs' => 'hi', 'snow' => 'hi', 'lysnw' => 'hi',
		'hail' => 'hi', 'thunder' => 'hi', 'fog' => 'hi',
		'trange' => 'hi', 'hrange' => 'hi', 'prange' => 'hi',
		'tc10max' => 'hi', 'tchrmax' => 'hi', 'hchrmax' => 'hi',   // largest rises
		'tc10min' => 'hi', 'tchrmin' => 'hi', 'hchrmin' => 'hi',   // largest falls (positive)
		'tmin' => 'lo', 'hmin' => 'lo', 'pmin' => 'lo', 'dmin' => 'lo', 'fmin' => 'lo',
		'nightmin' => 'lo',
		'tmaxanom' => 'hi', 'tminanom' => 'lo',
	];

	public static function monotoneDir($var) {
		return isset(self::$certainDir[$var]) ? self::$certainDir[$var] : null;
	}

	private static function qualifies($st) {
		return $st['n'] > 1
			&& ($st['pct'] >= self::PCT || ($st['rank'] <= self::TOP_N && $st['pct'] >= self::TOP_N_PCT));
	}

	/** Evaluate one item in the allowed directions; append to $items when it qualifies. */
	private static function consider(&$items, $cur, $dirs, $base, $words = ['highest', 'lowest']) {
		if (!$cur) { return; }
		$best = null;
		foreach ($dirs as $d) {
			$st = ($d === 'hi') ? $cur['hi'] : $cur['lo'];
			if (!self::qualifies($st)) { continue; }
			if ($best === null || $st['rank'] < $best[1]['rank']) { $best = [$d, $st]; }
		}
		if ($best === null) { return; }
		list($d, $st) = $best;
		$items[] = $base + [
			'value' => $cur['v'],
			'dir' => $d,
			'word' => ($d === 'hi') ? $words[0] : $words[1],
			'rank' => $st['rank'],
			'n' => $st['n'],
			'tied' => $st['tied'],
			'pct' => round($st['pct'], 2),
		];
	}

	/** First year among ranked [value, ts, ...] rows (the record's real start). */
	private static function firstYear($ranked, $fallback) {
		$min = null;
		foreach ($ranked as $r) {
			if ($min === null || $r[1] < $min) { $min = $r[1]; }
		}
		return ($min === null) ? (int)$fallback : (int)date('Y', $min);
	}

	/** Directions allowed for a partial item: the certain one, or both when nearly complete. */
	private static function partialDirs($mono, $frac, $lowBoring = false) {
		if ($frac >= self::COMPLETE_FRAC) { return $lowBoring ? ['hi'] : ['hi', 'lo']; }
		return ($mono === null) ? [] : [$mono];
	}

	private static function url($page, $params) {
		return '/' . $page . '?' . http_build_query($params);
	}

	/**
	 * Build the full flag list (CLI / cron). Expensive: every variable's full record.
	 * @return array ['generated' => ts, 'items' => [...], 'ms' => int]
	 */
	public static function build() {
		$t0 = microtime(true);
		LTA::init();
		$items = [];
		$endY = (int)Date::$dyear;
		$dayKey = function ($ts) { return date('Y-m-d', $ts); };
		$monKey = function ($ts) { return date('Y-m', $ts); };
		$yrKey = function ($ts) { return date('Y', $ts); };
		$todayKey = $dayKey(Date::$dtstamp);
		$yestKey = $dayKey(Date::$dtstamp_yest);
		$curMonTs = Date::mkdate((int)Date::$dmonth, 1, (int)Date::$dyear);
		$lastMonTs = Date::mkdate((int)Date::$dmonth - 1, 1, (int)Date::$dyear);
		$dom = (int)Date::$dday;
		$doy = (int)date('z', Date::$dtstamp) + 1;
		// How complete today / this month / this year are (0–1).
		// Share of today elapsed, in London time (dtstamp = local midnight; the
		// day length allows for 23 / 25 h clock-change days).
		$dayLen = max(1, strtotime('tomorrow', Date::$dtstamp) - Date::$dtstamp);
		$dayFrac = min(1.0, max(0.0, (time() - Date::$dtstamp) / $dayLen));
		$monFrac = ($dom - 1 + $dayFrac) / (int)date('t', Date::$dtstamp);
		$yrFrac = ($doy - 1 + $dayFrac) / (365 + (int)date('L', Date::$dtstamp));

		foreach (self::vars() as $var) {
			$meta = Wx::$daily[$var];
			$start = isset($meta['start_year']) ? (int)$meta['start_year'] : Site::BASE_YEAR;
			$isSum = self::isSum($var);
			$mono = self::monotoneDir($var);
			$unit = isset($meta['unit']) ? $meta['unit'] : Wx::None;
			$common = ['var' => $var, 'desc' => isset($meta['description']) ? $meta['description'] : $var,
				'unit' => $unit, 'count' => !empty($meta['count-only'])];

			// Days (fog excluded: a 0/1 observation does not rank usefully day-by-day)
			if (!in_array($var, self::$skipDaily, true)) {
				$ranked = [];
				foreach (Data::getDailyData($var, $start) as $y => $months) {
					foreach ($months as $m => $daily) {
						foreach ($daily as $d => $v) {
							if (!Util::isBlank($v)) { $ranked[] = [(float)$v, Date::mkdate($m, $d, $y)]; }
						}
					}
				}
				sort($ranked);
				$common['since'] = self::firstYear($ranked, $start);
				$hasToday = !in_array($var, self::$manualVars, true)
					&& (empty($meta['derived']) || !empty($meta['anom_of']))
					&& !in_array($var, self::$noToday, true);
				$cur = Report::currentRanks($ranked, ['today' => $hasToday ? $todayKey : null, 'yest' => $yestKey], $dayKey);
				$dayUrl = self::url('RankDay.php', ['vartype' => $var, 'month' => 0, 'start_year_rep' => $start, 'rankLimit' => 25]);
				$tDirs = self::partialDirs($mono, $dayFrac, self::dayLowBoring($var));
				if (isset($cur['today']) && count($tDirs)) {
					self::consider($items, $cur['today'], $tDirs, $common + ['kind' => 'day', 'label' => 'Today', 'date' => $todayKey, 'soFar' => true, 'url' => $dayUrl]);
				}
				if (isset($cur['yest'])) {
					self::consider($items, $cur['yest'], self::dayLowBoring($var) ? ['hi'] : ['hi', 'lo'], $common + ['kind' => 'day', 'label' => 'Yesterday', 'date' => $yestKey, 'soFar' => false, 'url' => $dayUrl]);
				}
				unset($ranked);
			}

			// Months and years: totals for summable variables, else means.
			$st = $isSum ? Data::SUMMARY_SUM : Data::SUMMARY_MEAN;
			$mRanked = [];
			foreach (Data::getMonthlySummary($var, $st, $start, $endY, null) as $y => $months) {
				foreach ($months as $m => $v) {
					if (!Util::isBlank($v)) { $mRanked[] = [(float)$v, Date::mkdate($m, 1, $y)]; }
				}
			}
			sort($mRanked);
			$common['since'] = self::firstYear($mRanked, $start);
			$cur = Report::currentRanks($mRanked, ['today' => $monKey($curMonTs), 'yest' => $monKey($lastMonTs)], $monKey);
			$monUrl = self::url('RankMonth.php', ['vartype' => $var, 'month' => 0, 'start_year_rep' => $start, 'summary_type' => $st, 'rankLimit' => 25]);
			$sumWord = $isSum ? 'total' : 'mean';
			$mDirs = self::partialDirs($isSum ? 'hi' : null, $monFrac, self::windMaxBoring($var));
			if (isset($cur['today']) && count($mDirs)) {
				self::consider($items, $cur['today'], $mDirs, $common + ['kind' => 'month', 'label' => date('F', $curMonTs), 'agg' => $sumWord, 'soFar' => true, 'url' => $monUrl]);
			}
			if (isset($cur['yest']) && $dom <= 7) {
				self::consider($items, $cur['yest'], self::windMaxBoring($var) ? ['hi'] : ['hi', 'lo'], $common + ['kind' => 'month', 'label' => date('F', $lastMonTs), 'agg' => $sumWord, 'soFar' => false, 'url' => $monUrl]);
			}
			$yRanked = [];
			foreach (Data::getAnnualData($var, $st, $start, $endY, null) as $y => $v) {
				if (!Util::isBlank($v)) { $yRanked[] = [(float)$v, Date::mkdate(1, 1, $y)]; }
			}
			sort($yRanked);
			$common['since'] = self::firstYear($yRanked, $start);
			$cur = Report::currentRanks($yRanked, ['today' => (string)$endY, 'yest' => (string)($endY - 1)], $yrKey);
			$yrUrl = self::url('RankYear.php', ['vartype' => $var, 'start_year_rep' => $start, 'summary_type' => $st]);
			$yDirs = self::partialDirs($isSum ? 'hi' : null, $yrFrac, self::windMaxBoring($var));
			if (isset($cur['today']) && count($yDirs)) {
				self::consider($items, $cur['today'], $yDirs, $common + ['kind' => 'year', 'label' => (string)$endY, 'agg' => $sumWord, 'soFar' => true, 'url' => $yrUrl]);
			}
			if (isset($cur['yest']) && $doy <= 14) {
				self::consider($items, $cur['yest'], self::windMaxBoring($var) ? ['hi'] : ['hi', 'lo'], $common + ['kind' => 'year', 'label' => (string)($endY - 1), 'agg' => $sumWord, 'soFar' => false, 'url' => $yrUrl]);
			}
		}

		// Latest complete N-day periods (ending yesterday).
		$latestEnd = Date::mkdate((int)Date::$mon_yest, (int)Date::$day_yest, (int)Date::$yr_yest);
		foreach (self::$periodVars as $var) {
			if (!isset(Wx::$daily[$var])) { continue; }
			$meta = Wx::$daily[$var];
			$start = isset($meta['start_year']) ? (int)$meta['start_year'] : Site::BASE_YEAR;
			$st = self::isSum($var) ? Data::SUMMARY_SUM : Data::SUMMARY_MEAN;
			foreach (self::$periodLengths as $len) {
				$ranked = Data::getPeriodWindows($var, $len, $st, $start, 0, null);
				sort($ranked);
				$startKey = $dayKey($latestEnd - ($len - 1) * 86400);
				$cur = Report::currentRanks($ranked, ['today' => $startKey], $dayKey);
				if (!isset($cur['today'])) { continue; }
				self::consider($items, $cur['today'], ['hi', 'lo'], [
					'var' => $var, 'desc' => $meta['description'], 'unit' => $meta['unit'], 'count' => false,
					'since' => self::firstYear($ranked, $start),
					'kind' => 'period', 'label' => 'last ' . $len . ' days', 'agg' => self::isSum($var) ? 'total' : 'mean',
					'soFar' => false,
					'url' => self::url('RankPeriods.php', ['vartype' => $var, 'month' => 0, 'start_year_rep' => $start,
						'summary_type' => $st, 'period' => $len, 'no_overlap' => 0, 'rankLimit' => 25]),
				]);
			}
		}

		// Spells still running.
		foreach (self::$spellChecks as $chk) {
			list($var, $dir, $th) = $chk;
			if (!isset(Wx::$daily[$var])) { continue; }
			$meta = Wx::$daily[$var];
			$start = isset($meta['start_year']) ? (int)$meta['start_year'] : Site::BASE_YEAR;
			$res = Spells::rankLongest($var, $th, $dir, $start, 1, 0);
			$c = $res['current'];
			if (!is_array($c) || empty($c['length']) || !count($res['lengths'])) { continue; }
			$len = (int)$c['length'];
			$stats = Report::rankStats($res['lengths'], $len, true);
			self::consider($items, ['v' => $len, 'hi' => $stats, 'lo' => $stats], ['hi'], [
				'var' => $var, 'desc' => $meta['description'], 'unit' => Wx::None, 'count' => true,
				'since' => (int)$res['startYear'],
				'kind' => 'spell', 'spellDir' => $dir, 'th' => $th, 'label' => 'since ' . date('d M', strtotime($c['startDate'])), 'soFar' => true,
				'url' => self::url('RankSpells.php', ['vartype' => $var, 'month' => 0, 'start_year_rep' => $start,
					'spell_dir' => $dir, 'threshold' => $th, 'rankLimit' => 25]),
			], ['longest', 'longest']);
		}

		usort($items, function ($a, $b) {
			if ($a['rank'] !== $b['rank']) { return $a['rank'] - $b['rank']; }
			return ($a['pct'] > $b['pct']) ? -1 : (($a['pct'] < $b['pct']) ? 1 : 0);
		});
		return ['version' => self::CACHE_VERSION, 'generated' => time(), 'items' => $items, 'ms' => (int)round((microtime(true) - $t0) * 1000)];
	}

	/** Write the cache (atomic). */
	public static function warm() {
		$data = self::build();
		nw3_atomic_write(self::file(), json_encode($data));
		return $data;
	}

	/** Read the cache; null when missing, stale, or wrong version. */
	public static function load() {
		$f = self::file();
		if (!is_readable($f)) { return null; }
		$data = json_decode((string)@file_get_contents($f), true);
		if (!is_array($data) || !isset($data['generated'], $data['items'])) { return null; }
		// Reject cache if version is absent or mismatched
		if (!isset($data['version']) || (int)$data['version'] !== self::CACHE_VERSION) { return null; }
		if (time() - (int)$data['generated'] > self::MAX_AGE) { return null; }
		return $data;
	}

	/** Item headline text, e.g. "Rainfall, October total: 165.8 mm". */
	private static function itemText($it) {
		$val = $it['count'] ? ((int)$it['value'] . ' day' . ((int)$it['value'] === 1 ? '' : 's'))
			: Wx::plainText(Wx::conv($it['value'], $it['unit'], true));
		$so = $it['soFar'] ? ' so far' : '';
		switch ($it['kind']) {
			case 'spell':
				$dir = $it['spellDir'];
				$name = Spells::isAnomalyDir($dir)
					? (($dir === 'heatwave') ? 'Heatwave' : 'Cold snap') . ' (' . $it['desc'] . ' ' . Spells::anomalyPhrase($dir, $it['th']) . ')'
					: (($it['var'] === 'rain') ? (($dir === 'above') ? 'Wet spell' : 'Dry spell') : $it['desc'] . ' spell');
				$what = $name . ': ' . $val . $so . ' (' . $it['label'] . ')';
				break;
			case 'day':
				$what = $it['desc'] . ', ' . strtolower($it['label']) . $so
					. (isset($it['date']) ? ' (' . date('d M', strtotime($it['date'])) . ')' : '') . ': ' . $val;
				break;
			default:
				$what = $it['desc'] . ', ' . $it['label'] . ' ' . $it['agg'] . $so . ': ' . $val;
		}
		$scope = ['day' => 'days', 'month' => 'months', 'year' => 'years', 'period' => 'periods', 'spell' => 'spells'];
		$rank = ($it['tied'] ? '=' : '') . Report::ordinal($it['rank']) . ' ' . $it['word'];
		return [$what, $rank . ' of ' . number_format($it['n']) . ' ' . $scope[$it['kind']]
			. (!empty($it['since']) ? ' since ' . (int)$it['since'] : '')
			. ' (' . Report::pctText($it['pct']) . ')'];
	}

	/** Home-page card; renders nothing unless something qualifies. */
	public static function renderCard($data = null) {
		$data = ($data === null) ? self::load() : $data;
		if (!$data || empty($data['items'])) { return; }
		if (!class_exists('Report', false)) { require_once __DIR__ . '/Report.php'; }
		$items = $data['items'];
		$li = [];
		foreach ($items as $i => $it) {
			list($what, $rank) = self::itemText($it);
			$top = ($it['rank'] === 1 && !$it['tied']) ? ' rkf-record' : '';
			$li[] = '<li class="clim-note rkf-item' . $top . '"><span class="rkf-dot rkf-dot-' . htmlspecialchars($it['dir']) . '"></span>'
				. '<a href="' . htmlspecialchars($it['url']) . '" title="Open the ranking">' . htmlspecialchars($what) . '</a> '
				. '&middot; <b>' . htmlspecialchars($rank) . '</b>' . ($it['soFar'] ? ' <span class="rkf-prov">provisional</span>' : '') . '</li>';
		}
		echo '<div class="wx-card wx-card-clim wx-card-rkf" id="rkf-card">';
		echo '<div class="wx-card-head"><span class="wx-card-label">Notable in the rankings</span></div>';
		echo '<ul class="clim-notes rkf-list">' . implode('', array_slice($li, 0, self::SHOW)) . '</ul>';
		if (count($li) > self::SHOW) {
			echo '<details class="rkf-more"><summary>' . (count($li) - self::SHOW) . ' more</summary>'
				. '<ul class="clim-notes rkf-list">' . implode('', array_slice($li, self::SHOW)) . '</ul></details>';
		}
		echo '<div class="clim-foot">Ranks in the top 10 or above 99th percentile for historical record; '
			. 'provisional records shown if certain or period is 90% complete'
			. ' &middot; <a href="/RankDay.php">rankings</a></div>';
		echo '</div>';
	}
}
