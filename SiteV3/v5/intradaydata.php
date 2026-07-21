<?php
/**
 * JSON data endpoint for intraday (per-minute) charts over one or more days.
 *
 * Replaces the legacy jpgraph endpoints graphday.php / graphday2.php /
 * graphdayA.php (engine graphdaygen.php) by serving the per-minute log for a
 * chosen day (and optionally the preceding N-1 days) as parallel arrays that the
 * Highcharts front-end renders and switches between client-side.
 *
 * Log line layout (logfiles/daily/{date}log.txt):
 *   H,i,d,wind,gust,wdir,temp,humi,pres,dewp,rain,pm25
 *
 * Params:
 *   date   Ymd day to plot (default: today)
 *   num    number of days, ending on `date` (default 1, max 92)
 *   ts,te  optional start/end hour offsets into the log buffer (legacy graphdaygen;
 *          e.g. ts=12 on a rolling ~24h today-log keeps the last 12 hours)
 */
require __DIR__ . '/Page.php';
ini_set('display_errors', '0');

$unitPref = isset($_GET['unit']) ? $_GET['unit'] : (isset($_COOKIE['SetUnits']) ? $_COOKIE['SetUnits'] : 'UK');
Page::$units = ($unitPref === 'US') ? UNIT_US : (($unitPref === 'EU') ? UNIT_EU : UNIT_UK);

header('Content-Type: application/json; charset=utf-8');

$procday = (isset($_GET['date']) && preg_match('/^\d{8}$/', $_GET['date'])) ? $_GET['date'] : date('Ymd');
$num = isset($_GET['num']) ? max(1, min(92, (int)$_GET['num'])) : 1;
$startHr = isset($_GET['ts']) ? max(0, (int)$_GET['ts']) : 0;
$endHr   = isset($_GET['te']) ? min(24, (int)$_GET['te']) : 24;

$mon = (int)substr($procday, 4, 2);
$dom = (int)substr($procday, 6, 2);
$yr  = (int)substr($procday, 0, 4);

$out = [
	'updated' => null,
	'date'    => $procday,
	'num'     => $num,
	'units'   => [
		'temp' => strip_tags(Wx::getUnits(Wx::Temperature)),
		'dewp' => strip_tags(Wx::getUnits(Wx::Temperature)),
		'humi' => '%',
		'pres' => strip_tags(Wx::getUnits(Wx::Pressure)),
		'wind' => strip_tags(Wx::getUnits(Wx::Wind)),
		'gust' => strip_tags(Wx::getUnits(Wx::Wind)),
		'rain' => strip_tags(Wx::getUnits(Wx::Rain)),
		'wdir' => '',
		'pm25' => 'ug/m3',
	],
	'time' => [], 'temp' => [], 'dewp' => [], 'humi' => [], 'pres' => [],
	'wind' => [], 'gust' => [], 'wdir' => [], 'rain' => [], 'pm25' => [],
];

// Gather the per-minute lines across the requested day span (oldest first).
// Match legacy graphdaygen.php: single-day plots use logfiles/daily/{Ymd}log.txt.
// For the current day that file is a rolling copy of goodlog (~24h), which is what
// "Past 24hrs" / ts=12 charts on the detailed pages need. todaylog.txt is
// calendar-day-so-far only (reset at midnight) — legacy only substitutes it when
// num>1 so concatenating yesterday's full day with a short todaylog doesn't leave
// a gap. When we do use todaylog, keep only today's DOM to avoid a backward jump.
$lines = [];
$lineDays = []; // Ymd stamp for each kept line, parallel to $lines
for ($d = $num - 1; $d >= 0; $d--) {
	$dayStamp = date('Ymd', Date::mkdate($mon, $dom - $d, $yr));
	$path = ROOT . 'logfiles/daily/' . $dayStamp . 'log.txt';
	$fromTodayLog = false;
	if ($num > 1 && $dayStamp === date('Ymd')) {
		$todayPath = ROOT . 'logfiles/daily/todaylog.txt';
		if (file_exists($todayPath)) { $path = $todayPath; $fromTodayLog = true; }
	}
	if (!file_exists($path)) { continue; }
	$dayLines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$todayDom = (int)substr($dayStamp, 6, 2);
	foreach ($dayLines as $ln) {
		if ($fromTodayLog) {
			$parts = explode(',', $ln);
			if (count($parts) < 3 || (int)$parts[2] !== $todayDom) { continue; }
		}
		$lines[] = $ln;
		$lineDays[] = $dayStamp;
	}
}

// ts/te are hour offsets into the concatenated buffer (legacy graphdaygen), not
// clock hours. ts=12 on a ~24h rolling today-log ⇒ skip the first 12h ⇒ last 12h.
// Only applied for single-day requests (detailed-page mini charts).
if ($num === 1 && count($lines) > 0 && ($startHr > 0 || isset($_GET['te']))) {
	$startIdx = (int)round(60 * $startHr);
	$endIdx = isset($_GET['te']) ? (int)round(60 * $endHr) : count($lines);
	$startIdx = max(0, min($startIdx, count($lines)));
	$endIdx = max($startIdx, min($endIdx, count($lines)));
	$lines = array_slice($lines, $startIdx, $endIdx - $startIdx);
	$lineDays = array_slice($lineDays, $startIdx, $endIdx - $startIdx);
}

if (!class_exists('IntradayDirMean')) {
	/** Rolling vector mean for wind direction; mirrors the legacy rollingMean. */
	class IntradayDirMean {
		const BITIFIER = 120;
		private $size; private $items = []; private $pointer = 0; private $curr = 0; private $oldMean = -99;
		function __construct($size) { $this->size = $size; }
		function add($item) { $this->curr = $item; $this->items[$this->pointer % $this->size] = $item; $this->pointer++; }
		function mean() {
			if ($this->pointer <= $this->size) { return $this->curr; }
			$freqs = [];
			for ($i = 0; $i <= 360 / self::BITIFIER; $i++) { $freqs[$i] = 0; }
			for ($i = 0; $i < $this->size; $i++) { $freqs[(int)round($this->items[$i] / self::BITIFIER)]++; }
			$pivot = array_search(min($freqs), $freqs) * self::BITIFIER;
			$sum = 0;
			for ($i = 0; $i < $this->size; $i++) { $sum += $this->items[$i]; if ($this->items[$i] > $pivot) { $sum -= 360; } }
			$mean = $sum / $this->size; if ($mean < 0) { $mean += 360; }
			$old = $this->oldMean; $this->oldMean = $mean;
			if ($old !== -99 && abs($mean - $old) > 5) { return -100; }
			return $mean;
		}
	}
}

$windBuf = []; $windSum = 0; $p = 0;
$wdirs = new IntradayDirMean(15);
$total = count($lines);

for ($i = 0; $i < $total; $i++) {
	$c = explode(',', $lines[$i]);
	if (count($c) < 11) { continue; }
	$h = (int)$c[0]; $mi = (int)$c[1]; $dd = (int)$c[2];
	// Timestamp from the line's own day-of-month. Dated files are usually one
	// calendar day, but today's {Ymd}log.txt is a rolling copy of goodlog that
	// crosses midnight — stamping every line with the filename date makes 00:00
	// land ~24h before 23:59 and Highcharts draws a diagonal.
	$ds = $lineDays[$i];
	$y = (int)substr($ds, 0, 4);
	$m = (int)substr($ds, 4, 2);
	$dStamp = (int)substr($ds, 6, 2);
	if ($dd !== $dStamp) {
		$prev = Date::mkdate($m, $dStamp - 1, $y);
		if ($dd === (int)date('j', $prev)) {
			$y = (int)date('Y', $prev);
			$m = (int)date('n', $prev);
			$dd = (int)date('j', $prev);
		}
	}
	$ts = mktime($h, $mi, 0, $m, $dd, $y);

	$wind = ($c[3] === '') ? 0 : (float)$c[3];
	$windSum += $wind; $windBuf[$p % 10] = $wind; $p++;
	$windOut = $wind;
	if ($p >= 10) { $windOut = $windSum / 10; $windSum -= $windBuf[$p % 10]; }

	$wdirs->add(($c[5] === '') ? 0 : (float)$c[5]);
	$dirOut = ($windOut > 0.5) ? $wdirs->mean() : -999;

	$out['time'][] = $ts * 1000;
	$out['temp'][] = Wx::convNum($c[6], Wx::Temperature, 1);
	$out['humi'][] = ($c[7] === '') ? null : (float)$c[7];
	$out['pres'][] = Wx::convNum($c[8], Wx::Pressure, 1);
	$out['dewp'][] = Wx::convNum($c[9], Wx::Temperature, 1);
	$out['rain'][] = Wx::convNum($c[10], Wx::Rain, 2);
	$out['wind'][] = Wx::convNum($windOut, Wx::Wind, 1);
	$out['gust'][] = Wx::convNum($c[4], Wx::Wind, 1);
	$out['wdir'][] = ($dirOut < 0) ? null : round($dirOut);
	$out['pm25'][] = (isset($c[11]) && $c[11] !== '') ? (float)$c[11] : null;
}

$n = count($out['time']);
// Sort + dedupe by minute so overlapping rolling/dated logs never go backwards.
if ($n > 1) {
	$order = range(0, $n - 1);
	usort($order, function ($a, $b) use ($out) {
		if ($out['time'][$a] == $out['time'][$b]) { return ($a < $b) ? -1 : 1; }
		return ($out['time'][$a] < $out['time'][$b]) ? -1 : 1;
	});
	$keys = ['time', 'temp', 'dewp', 'humi', 'pres', 'wind', 'gust', 'wdir', 'rain', 'pm25'];
	$sorted = [];
	foreach ($keys as $k) { $sorted[$k] = []; }
	$lastT = null;
	foreach ($order as $idx) {
		$t = $out['time'][$idx];
		if ($lastT !== null && $t === $lastT) {
			foreach ($keys as $k) { $sorted[$k][count($sorted[$k]) - 1] = $out[$k][$idx]; }
			continue;
		}
		foreach ($keys as $k) { $sorted[$k][] = $out[$k][$idx]; }
		$lastT = $t;
	}
	foreach ($keys as $k) { $out[$k] = $sorted[$k]; }
	$n = count($out['time']);
}
$out['updated'] = $n ? (int)($out['time'][$n - 1] / 1000) : null;

// Optional decimation: keep at most `maxpts` evenly-spaced samples (plus the last)
// so longer windows transfer far less data while preserving the overall shape.
$maxpts = isset($_GET['maxpts']) ? max(0, (int)$_GET['maxpts']) : 0;
if ($maxpts > 0 && $n > $maxpts) {
	$step = (int)ceil($n / $maxpts);
	$keys = ['time', 'temp', 'dewp', 'humi', 'pres', 'wind', 'gust', 'wdir', 'rain', 'pm25'];
	foreach ($keys as $k) {
		$src = $out[$k];
		$dst = [];
		for ($i = 0; $i < $n; $i += $step) { $dst[] = $src[$i]; }
		if (($n - 1) % $step !== 0) { $dst[] = $src[$n - 1]; }
		$out[$k] = $dst;
	}
}

echo json_encode($out);
