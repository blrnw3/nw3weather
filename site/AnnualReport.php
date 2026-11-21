<?php
/**
 * Annual report (wxhistyear.php): the data summary cards and the monthly
 * breakdown table for one year, built from the same daily series as the
 * monthly reports and data tables. The current year runs to yesterday.
 */
class AnnualReport {
	/** A year counts for the rankings when it has at least this share of days. */
	const RANK_COVERAGE = 0.9;
	const HOT_DAY = 25;
	const VERY_HOT_DAY = 30;
	const GALE_GUST = 30; // mph, as the monthly report's "Gusts > 30mph"

	public $year;
	public $partial;   // current year, so data only run to $endM / $endD
	public $endM = 12;
	public $endD = 31;
	public $lastMonth; // last month with any data in the span
	private $cache = [];
	private $allYears = [];

	function __construct($year) {
		$this->year = (int)$year;
		$this->partial = ($this->year === (int)Date::$dyear);
		if ($this->partial) {
			$this->endM = (int)Date::$mon_yest;
			$this->endD = (int)Date::$day_yest;
			if ((int)Date::$yr_yest !== $this->year) { $this->endM = 0; $this->endD = 0; }
		}
		$this->lastMonth = $this->partial ? $this->endM : 12;
	}

	/** True when the span holds at least one day. */
	function hasData() {
		return $this->endM > 0 && $this->count($this->values('tmean')) > 0;
	}

	/** Last day of month $m inside the span (0 when the month is beyond it). */
	function monthEnd($m) {
		if ($m > $this->endM) { return 0; }
		$dim = (int)Date::get_days_in_month($m, $this->year);
		return ($m === $this->endM) ? min($dim, $this->endD) : $dim;
	}

	/** True when month $m is only partly inside the span. */
	function monthPartial($m) {
		$e = $this->monthEnd($m);
		return $e > 0 && $e < (int)Date::get_days_in_month($m, $this->year);
	}

	/** Number of calendar days in the span. */
	function spanDays() {
		$n = 0;
		for ($m = 1; $m <= 12; $m++) { $n += $this->monthEnd($m); }
		return $n;
	}

	/** [m][d] => float for this year, clipped to the span, numeric values only. */
	function series($var) {
		if (!isset($this->cache[$var])) {
			$this->cache[$var] = $this->clip(Data::getDailyDataForYear($var, $this->year), $this->year);
		}
		return $this->cache[$var];
	}

	private function clip($months, $year) {
		$out = [];
		if (!is_array($months)) { return $out; }
		for ($m = 1; $m <= $this->endM; $m++) {
			if (!isset($months[$m]) || !is_array($months[$m])) { continue; }
			$e = ($m === $this->endM) ? $this->endD : 31;
			foreach ($months[$m] as $d => $v) {
				$d = (int)$d;
				if ($d > $e || $d > (int)Date::get_days_in_month($m, $year)) { continue; }
				if (Util::isBlank($v) || !nw3_is_numeric($v)) { continue; }
				$out[$m][$d] = (float)$v;
			}
		}
		return $out;
	}

	/** Flat list of values, for the whole span or one month. */
	function values($var, $m = null) {
		$s = $this->series($var);
		if ($m !== null) { return isset($s[$m]) ? array_values($s[$m]) : []; }
		$out = [];
		foreach ($s as $days) { foreach ($days as $v) { $out[] = $v; } }
		return $out;
	}

	function count($arr) { return count($arr); }

	function mean($var, $m = null) {
		$v = $this->values($var, $m);
		return $v ? array_sum($v) / count($v) : null;
	}

	function sum($var, $m = null) {
		$v = $this->values($var, $m);
		return $v ? array_sum($v) : null;
	}

	/** Days matching $fn (null when there are no readings). */
	function countIf($var, $fn, $m = null) {
		$v = $this->values($var, $m);
		if (!$v) { return null; }
		$n = 0;
		foreach ($v as $x) { if ($fn($x)) { $n++; } }
		return $n;
	}

	/** ['v' => value, 'm' => month, 'd' => day] of the highest / lowest day, or null. Ties keep the first. */
	function extreme($var, $high, $m = null) {
		$best = null;
		foreach ($this->series($var) as $mm => $days) {
			if ($m !== null && $mm !== $m) { continue; }
			foreach ($days as $d => $v) {
				if ($best === null || ($high ? $v > $best['v'] : $v < $best['v'])) {
					$best = ['v' => $v, 'm' => $mm, 'd' => $d];
				}
			}
		}
		return $best;
	}

	/** First / last day matching $fn within months $m1..$m2, or null. */
	function firstLast($var, $fn, $m1, $m2, $last) {
		$hit = null;
		foreach ($this->series($var) as $m => $days) {
			if ($m < $m1 || $m > $m2) { continue; }
			foreach ($days as $d => $v) {
				if ($fn($v)) {
					if (!$last) { return ['v' => $v, 'm' => $m, 'd' => $d]; }
					$hit = ['v' => $v, 'm' => $m, 'd' => $d];
				}
			}
		}
		return $hit;
	}

	/** Climate normal for the span (or one month of it): mean, or sum for summable variables. */
	function normal($type, $m = null) {
		LTA::init();
		if ($m !== null) {
			$e = $this->monthEnd($m);
			if ($e <= 0) { return null; }
			return $this->monthPartial($m)
				? LTA::getSpanAnom($type, $m, 1, $m, $e, $this->year)
				: LTA::getMonthlyAnom($type, $m);
		}
		if (!$this->partial) { return LTA::getYearlyAnom($type); }
		return LTA::getSpanAnom($type, 1, 1, $this->endM, $this->endD, $this->year);
	}

	/**
	 * Rank of this year's value among station years with enough data for the
	 * same span (Jan 1 to the span end), in whichever direction it ranks better.
	 * @param string $agg 'mean' | 'sum'
	 * @return array|null ['rank', 'n', 'tied', 'high' => bool, 'first', 'last']
	 */
	function rank($var, $agg) {
		if (!isset($this->allYears[$var])) { $this->allYears[$var] = Data::varToDatArray($var); }
		$need = self::RANK_COVERAGE * $this->spanDays();
		$vals = []; $mine = null; $years = [];
		foreach ($this->allYears[$var] as $y => $months) {
			$y = (int)$y;
			if ($y < Site::BASE_YEAR || $y > (int)Date::$yr_yest) { continue; }
			if ($y === (int)Date::$dyear && !$this->partial && $y !== $this->year) { continue; }
			$flat = [];
			foreach ($this->clip($months, $y) as $days) { foreach ($days as $v) { $flat[] = $v; } }
			if (count($flat) < $need || !$flat) { continue; }
			$v = ($agg === 'sum') ? array_sum($flat) : array_sum($flat) / count($flat);
			$vals[] = $v; $years[] = $y;
			if ($y === $this->year) { $mine = $v; }
		}
		if ($mine === null || count($vals) < 3) { return null; }
		sort($vals);
		$hi = Report::rankStats($vals, $mine, true);
		$lo = Report::rankStats($vals, $mine, false);
		$useHi = $hi['rank'] <= $lo['rank'];
		$st = $useHi ? $hi : $lo;
		return ['rank' => $st['rank'], 'n' => $st['n'], 'tied' => $st['tied'], 'high' => $useHi,
			'first' => min($years), 'last' => max($years)];
	}

	// ---- Formatting ----

	function ts($m, $d) { return Date::mkdate($m, $d, $this->year); }

	/** "1 Jul" linked to the daily report. */
	function dayLink($m, $d) {
		$url = Report::reportDayUrl($this->year, $m, $d);
		return Report::hiddenDateLink($url, date('j M', $this->ts($m, $d)), 'View daily report for ' . date('j M Y', $this->ts($m, $d)));
	}

	/** "Jun" linked to the monthly report. */
	function monthLink($m, $long = false) {
		$url = Report::reportMonthUrl($this->year, $m);
		$label = $long ? Date::monthfull($m) : Date::$months3[$m - 1];
		return Report::hiddenDateLink($url, $label, 'View monthly report for ' . Date::monthfull($m) . ' ' . $this->year);
	}

	static function tempAnom($v, $norm) {
		if ($v === null || $norm === null) { return ''; }
		$a = $v - $norm;
		return Wx::conv(abs($a) < 0.05 ? 0 : $a, Wx::AbsTemp, true, true);
	}

	static function pctOf($v, $norm) {
		if ($v === null || $norm === null || (float)$norm <= 0) { return ''; }
		return Util::percent($v, $norm, 0, true, false);
	}

	static function anomSpan($txt, $label = '') {
		if ($txt === '') { return ''; }
		return ' <span class="ar-anom"' . ($label !== '' ? ' title="' . htmlspecialchars($label) . '"' : '') . '>' . $txt . '</span>';
	}

	/** "2nd warmest" style text, or ''. */
	function rankText($r, $hiWord, $loWord) {
		if ($r === null) { return ''; }
		$span = $this->partial ? (' to ' . date('j M', $this->ts($this->endM, $this->endD))) : '';
		$word = $r['high'] ? $hiWord : $loWord;
		$pos = ($r['rank'] === 1 && !$r['tied']) ? '' : (($r['tied'] ? '=' : '') . Report::ordinal($r['rank']) . ' ');
		$cls = ($r['rank'] <= 3) ? ' ar-rank-top' : '';
		return '<span class="ar-rank' . $cls . '">' . $pos . $word . $span . ' of ' . $r['n']
			. ' years (' . $r['first'] . '&ndash;' . $r['last'] . ')</span>';
	}

	/** Month with the highest / lowest per-month metric. */
	private function pickMonth($fn, $high) {
		$best = null;
		for ($m = 1; $m <= $this->lastMonth; $m++) {
			$v = $fn($m);
			if ($v === null) { continue; }
			if ($best === null || ($high ? $v > $best[1] : $v < $best[1])) { $best = [$m, $v]; }
		}
		return $best;
	}

	/** Count-type day normal (afdays etc.) for the span, rounded for display. */
	private function countNormalText($type) {
		$n = $this->normal($type);
		return ($n === null) ? '' : ('normal ' . nw3_round($n));
	}

	// ---- Cards ----

	private static function card($mod, $icon, $label, $href, $nowHtml, $subHtml, $rows) {
		$h = '<div class="wx-card ar-card wx-card-' . $mod . '">';
		$h .= '<div class="wx-card-head">';
		if ($icon) {
			$src = preg_match('/\.(svg|png)$/', $icon) ? Site::IMG_ROOT . $icon : Site::IMG_ROOT . $icon . '_small.png';
			$h .= '<img class="wx-card-icon" src="' . $src . '" alt="" width="30" height="30" />';
		}
		$h .= $href ? '<a class="hidden-link wx-card-label" href="' . $href . '">' . $label . '</a>' : '<span class="wx-card-label">' . $label . '</span>';
		$h .= '</div>';
		if ($nowHtml !== '') { $h .= '<div class="wx-card-now">' . $nowHtml . '</div>'; }
		if ($subHtml !== '') { $h .= '<div class="wx-card-sub">' . $subHtml . '</div>'; }
		$h .= '<div class="wx-card-rows">';
		foreach ($rows as $r) {
			if ($r === null) { continue; }
			$h .= '<div><span class="k">' . $r[0] . '</span><span class="v">' . $r[1] . '</span></div>';
		}
		return $h . '</div></div>';
	}

	private function extremeRow($label, $var, $high, $unit) {
		$e = $this->extreme($var, $high);
		if ($e === null || ($high && $e['v'] <= 0)) { return null; }
		return [$label, Wx::conv($e['v'], $unit) . ' <span class="wx-when">' . $this->dayLink($e['m'], $e['d']) . '</span>'];
	}

	private function countRow($label, $n, $normType = null) {
		if ($n === null) { return null; }
		$norm = $normType ? $this->countNormalText($normType) : '';
		return [$label, '<b>' . $n . '</b>' . ($norm !== '' ? ' <span class="wx-when">' . $norm . '</span>' : '')];
	}

	function cardsHtml() {
		$T = Wx::Temperature;
		$out = [];

		// Temperature
		$tmean = $this->mean('tmean'); $tmax = $this->mean('tmax'); $tmin = $this->mean('tmin');
		$warm = $this->pickMonth(function ($m) { $v = $this->mean('tmean', $m); $n = $this->normal('tmean', $m); return ($v === null || $n === null) ? null : $v - $n; }, true);
		$cool = $this->pickMonth(function ($m) { $v = $this->mean('tmean', $m); $n = $this->normal('tmean', $m); return ($v === null || $n === null) ? null : $v - $n; }, false);
		$out[] = self::card('temp', 'thermom8', 'Temperature', 'wx14.php', $tmean === null ? 'n/a' :
			Wx::conv($tmean, $T) . self::anomSpan(self::tempAnom($tmean, $this->normal('tmean')), 'vs 1991-2020 normal'),
			$this->rankText($this->rank('tmean', 'mean'), 'warmest', 'coldest'),
			[
				$tmax === null ? null : ['Mean max', Wx::conv($tmax, $T) . self::anomSpan(self::tempAnom($tmax, $this->normal('tmax')))],
				$tmin === null ? null : ['Mean min', Wx::conv($tmin, $T) . self::anomSpan(self::tempAnom($tmin, $this->normal('tmin')))],
				$this->extremeRow('Highest', 'tmax', true, $T),
				$this->extremeRow('Lowest', 'tmin', false, $T),
				$warm === null ? null : ['Warmest vs normal', $this->monthLink($warm[0]) . ' ' . self::anomSpan(Wx::conv($warm[1], Wx::AbsTemp, true, true))],
				$cool === null ? null : ['Coolest vs normal', $this->monthLink($cool[0]) . ' ' . self::anomSpan(Wx::conv($cool[1], Wx::AbsTemp, true, true))],
			]);

		// Rainfall
		$rain = $this->sum('rain');
		$wet = $this->pickMonth(function ($m) { return $this->sum('rain', $m); }, true);
		$dry = $this->pickMonth(function ($m) { return $this->sum('rain', $m); }, false);
		$out[] = self::card('rain', 'icon-rain.svg', 'Rainfall', 'wx12.php', $rain === null ? 'n/a' :
			Wx::conv($rain, Wx::Rain) . self::anomSpan(self::pctOf($rain, $this->normal('rain')), '% of the 1991-2020 normal'),
			$this->rankText($this->rank('rain', 'sum'), 'wettest', 'driest'),
			[
				$this->countRow('Days &ge; ' . Wx::conv(1, Wx::Rain, true, false, -1), $this->countIf('rain', function ($v) { return $v >= 1; }), 'rdays'),
				$this->countRow('Days &ge; ' . Wx::conv(0.2, Wx::Rain), $this->countIf('rain', function ($v) { return $v >= 0.2; })),
				$this->extremeRow('Wettest day', 'rain', true, Wx::Rain),
				$this->extremeRow('Wettest hour', 'hrmax', true, Wx::Rain),
				$wet === null ? null : ['Wettest month', $this->monthLink($wet[0]) . ' ' . Wx::conv($wet[1], Wx::Rain) . self::anomSpan(self::pctOf($wet[1], $this->normal('rain', $wet[0])))],
				$dry === null ? null : ['Driest month', $this->monthLink($dry[0]) . ' ' . Wx::conv($dry[1], Wx::Rain) . self::anomSpan(self::pctOf($dry[1], $this->normal('rain', $dry[0])))],
			]);

		// Sunshine
		$sun = $this->sum('sunhr');
		$poss = $this->normal('maxsun');
		$sunny = $this->pickMonth(function ($m) { return $this->sum('sunhr', $m); }, true);
		$dull = $this->pickMonth(function ($m) { return $this->sum('sunhr', $m); }, false);
		$out[] = self::card('sun', 'icon-sun.svg', 'Sunshine', 'wx11.php', $sun === null ? 'n/a' :
			Wx::conv($sun, Wx::Hours) . self::anomSpan(self::pctOf($sun, $this->normal('sunhr')), '% of the 1991-2020 normal'),
			$this->rankText($this->rank('sunhr', 'sum'), 'sunniest', 'dullest'),
			[
				($sun === null || !$poss) ? null : ['Of the possible', '<b>' . Util::roundi(100 * $sun / $poss) . '%</b> <span class="wx-when">of ' . nw3_round($poss) . ' hrs</span>'],
				$this->extremeRow('Sunniest day', 'sunhr', true, Wx::Hours),
				$this->countRow('Sunless days', $this->countIf('sunhr', function ($v) { return $v <= 0; })),
				$sunny === null ? null : ['Sunniest month', $this->monthLink($sunny[0]) . ' ' . Wx::conv($sunny[1], Wx::Hours) . self::anomSpan(self::pctOf($sunny[1], $this->normal('sunhr', $sunny[0])))],
				$dull === null ? null : ['Dullest month', $this->monthLink($dull[0]) . ' ' . Wx::conv($dull[1], Wx::Hours) . self::anomSpan(self::pctOf($dull[1], $this->normal('sunhr', $dull[0])))],
			]);

		// Wind
		$wmean = $this->mean('wmean');
		$windy = $this->pickMonth(function ($m) { return $this->mean('wmean', $m); }, true);
		$out[] = self::card('wind', 'icon-wind.svg', 'Wind', 'wx13.php', $wmean === null ? 'n/a' :
			Wx::conv($wmean, Wx::Wind) . self::anomSpan(($n = $this->normal('wmean')) === null ? '' : Wx::conv($wmean - $n, Wx::Wind, true, true), 'vs 1991-2020 normal'),
			'Mean speed',
			[
				$this->extremeRow('Highest gust', 'gust', true, Wx::Wind),
				$this->extremeRow('Windiest day', 'wmean', true, Wx::Wind),
				$this->countRow('Days with gusts &gt; ' . Wx::conv(self::GALE_GUST, Wx::Wind, true, false, -1), $this->countIf('gust', function ($v) { return $v > self::GALE_GUST; })),
				$windy === null ? null : ['Windiest month', $this->monthLink($windy[0]) . ' ' . Wx::conv($windy[1], Wx::Wind)],
			]);

		// Frost & snow
		$af = $this->countIf('nightmin', function ($v) { return $v < 0; });
		$lastFrost = $this->firstLast('nightmin', function ($v) { return $v < 0; }, 1, 6, true);
		$firstFrost = $this->firstLast('nightmin', function ($v) { return $v < 0; }, 7, 12, false);
		$depth = $this->extreme('lysnw', true);
		$out[] = self::card('frost', 'snow.png', 'Frost &amp; snow', 'wx14.php', $af === null ? 'n/a' :
			$af . ' <span class="ar-unit">air frosts</span>' . self::anomSpan($this->countNormalText('afdays')),
			'',
			[
				$lastFrost === null ? null : ['Last spring frost', $this->dayLink($lastFrost['m'], $lastFrost['d'])],
				$firstFrost === null ? null : ['First autumn frost', $this->dayLink($firstFrost['m'], $firstFrost['d'])],
				$this->countRow('Falling snow days', $this->countIf('snow', function ($v) { return $v > 0; }), 'fsdays'),
				$this->countRow('Lying snow days', $this->countIf('lysnw', function ($v) { return $v > 0; }), 'lsdays'),
				($depth === null || $depth['v'] <= 0) ? null : ['Deepest snow', Wx::conv($depth['v'], Wx::Snow) . ' <span class="wx-when">' . $this->dayLink($depth['m'], $depth['d']) . '</span>'],
			]);

		// Heat & storms
		$hot = $this->countIf('tmax', function ($v) { return $v >= self::HOT_DAY; });
		$out[] = self::card('storm', 'sky3', 'Heat &amp; storms', 'wx14.php', $hot === null ? 'n/a' :
			$hot . ' <span class="ar-unit">days &ge; ' . Wx::conv(self::HOT_DAY, $T, true, false, -1) . '</span>',
			'',
			[
				$this->countRow('Days &ge; ' . Wx::conv(self::VERY_HOT_DAY, $T, true, false, -1), $this->countIf('tmax', function ($v) { return $v >= self::VERY_HOT_DAY; })),
				$this->countRow('Nights &ge; ' . Wx::conv(15, $T, true, false, -1), $this->countIf('tmin', function ($v) { return $v >= 15; })),
				$this->countRow('Thunder days', $this->countIf('thunder', function ($v) { return $v > 0; }), 'tsdays'),
				$this->countRow('Hail days', $this->countIf('hail', function ($v) { return $v > 0; })),
				$this->countRow('Dense fog days', $this->countIf('fog', function ($v) { return $v > 0; })),
			]);

		return '<div class="wx-cards ar-cards">' . implode('', $out) . '</div>';
	}

	// ---- Monthly breakdown ----

	private static function cellHtml($html, $class, $extra = '') {
		return '<td class="' . htmlspecialchars(trim($class . ' ' . $extra)) . '">' . $html . '</td>';
	}

	private static function blank() { return '<td class="reportday">-</td>'; }

	/** Colour class for a monthly (or annual) total on the per-day scale, as the data tables do. */
	private static function sumClass($type, $value, $unit, $months = 1) {
		$num = Wx::convNum($value, $unit);
		return Report::valcolForType($type, ($num === null ? 0 : $num) / Report::valcolSumOffsetForType($type) / max(1, $months));
	}

	private static function valClass($type, $value, $unit) {
		$num = Wx::convNum($value, $unit);
		return Report::valcolForType($type, $num === null ? (float)$value : $num);
	}

	/** One row of the breakdown: $m = 1..12, or null for the year total. */
	private function breakdownRow($m) {
		$T = Wx::Temperature;
		$year = ($m === null);
		$months = $year ? max(1, $this->lastMonth) : 1;
		$h = '';
		foreach (['tmean', 'tmax', 'tmin'] as $v) {
			$x = $this->mean($v, $m);
			if ($x === null) { $h .= self::blank(); continue; }
			$h .= self::cellHtml(Wx::conv($x, $T, false) . '<span class="ar-sub">' . self::tempAnom($x, $this->normal($v, $m)) . '</span>', self::valClass($v, $x, $T));
		}
		foreach ([['tmax', true], ['tmin', false]] as $e) {
			$x = $this->extreme($e[0], $e[1], $m);
			if ($x === null) { $h .= self::blank(); continue; }
			$h .= self::cellHtml(Wx::conv($x['v'], $T, false) . '<span class="ar-sub">' . $this->dayLink($x['m'], $x['d']) . '</span>', self::valClass($e[0], $x['v'], $T));
		}
		$rain = $this->sum('rain', $m);
		if ($rain === null) { $h .= self::blank() . self::blank() . self::blank(); }
		else {
			$h .= self::cellHtml(Wx::conv($rain, Wx::Rain, false) . '<span class="ar-sub">' . self::pctOf($rain, $this->normal('rain', $m)) . '</span>', self::sumClass('rain', $rain, Wx::Rain, $months));
			$rd = $this->countIf('rain', function ($v) { return $v >= 1; }, $m);
			$h .= self::cellHtml($rd, Report::dayCountClass($year ? $rd / $months : $rd));
			$x = $this->extreme('rain', true, $m);
			$h .= self::cellHtml(Wx::conv($x['v'], Wx::Rain, false) . ($x['v'] > 0 ? '<span class="ar-sub">' . $this->dayLink($x['m'], $x['d']) . '</span>' : ''), self::valClass('rain', $x['v'], Wx::Rain));
		}
		$sun = $this->sum('sunhr', $m);
		if ($sun === null) { $h .= self::blank(); }
		else {
			$h .= self::cellHtml(Wx::conv($sun, Wx::Hours, false) . '<span class="ar-sub">' . self::pctOf($sun, $this->normal('sunhr', $m)) . '</span>', self::sumClass('sunhr', $sun, Wx::Hours, $months));
		}
		$w = $this->mean('wmean', $m);
		$h .= ($w === null) ? self::blank() : self::cellHtml(Wx::conv($w, Wx::Wind, false), self::valClass('wmean', $w, Wx::Wind));
		$g = $this->extreme('gust', true, $m);
		$h .= ($g === null) ? self::blank() : self::cellHtml(Wx::conv($g['v'], Wx::Wind, false) . '<span class="ar-sub">' . $this->dayLink($g['m'], $g['d']) . '</span>', self::valClass('gust', $g['v'], Wx::Wind));
		foreach ([['nightmin', function ($v) { return $v < 0; }], ['tmax', function ($v) { return $v >= self::HOT_DAY; }],
			['snow', function ($v) { return $v > 0; }], ['thunder', function ($v) { return $v > 0; }]] as $c) {
			$n = $this->countIf($c[0], $c[1], $m);
			$h .= ($n === null) ? self::blank() : self::cellHtml($n, $n ? Report::dayCountClass($year ? $n / $months : $n) : 'ar-zero');
		}
		return $h;
	}

	function breakdownHtml() {
		$T = Wx::getUnitsText(Wx::Temperature);
		$R = Wx::getUnitsText(Wx::Rain);
		$W = Wx::getUnitsText(Wx::Wind);
		$cols = [
			['Mean', "Mean temperature ($T) and anomaly"],
			['Max', "Mean maximum temperature ($T) and anomaly"],
			['Min', "Mean minimum temperature ($T) and anomaly"],
			['High', "Highest maximum ($T)"],
			['Low', "Lowest minimum ($T)"],
			['Rain', "Rainfall ($R) and % of normal"],
			['Rain days', 'Days with at least ' . Wx::conv(1, Wx::Rain, true, false, -1)],
			['Wettest', "Wettest day ($R)"],
			['Sun', 'Sunshine (hrs) and % of normal'],
			['Wind', "Mean wind speed ($W)"],
			['Gust', "Highest gust ($W)"],
			['Air frosts', 'Nights below 0 ' . $T],
			['Days &ge; ' . Wx::conv(self::HOT_DAY, Wx::Temperature, false, false, -1), 'Days with a maximum of at least ' . Wx::conv(self::HOT_DAY, Wx::Temperature, true, false, -1)],
			['Snow', 'Days of falling snow'],
			['Thunder', 'Days of thunder'],
		];
		$h = '<div class="hm-break-scroll ar-break-scroll"><table class="hm-break ar-break">';
		$h .= '<thead><tr><th class="hm-day" scope="col">Month</th>';
		foreach ($cols as $c) { $h .= '<th scope="col" title="' . htmlspecialchars(html_entity_decode($c[1])) . '">' . $c[0] . '</th>'; }
		$h .= '</tr><tr class="ar-units"><th class="hm-day"></th>';
		foreach ([$T, $T, $T, $T, $T, $R, '', $R, 'hrs', $W, $W, '', '', '', ''] as $u) { $h .= '<th>' . $u . '</th>'; }
		$h .= '</tr></thead><tbody>';
		for ($m = 1; $m <= 12; $m++) {
			$h .= '<tr><th class="hm-day" scope="row">';
			if ($m > $this->endM) {
				$h .= Date::$months3[$m - 1] . '</th>';
				$h .= str_repeat('<td class="ar-future"></td>', count($cols));
			} else {
				$h .= $this->monthLink($m);
				if ($this->monthPartial($m)) { $h .= ' <span class="rkf-prov" title="to ' . date('j M', $this->ts($m, $this->monthEnd($m))) . '">so far</span>'; }
				$h .= '</th>' . $this->breakdownRow($m);
			}
			$h .= '</tr>';
		}
		$h .= '<tr class="ar-total"><th class="hm-day" scope="row">' . ($this->partial ? 'Year so far' : 'Year') . '</th>' . $this->breakdownRow(null) . '</tr>';
		return $h . '</tbody></table></div>';
	}
}
