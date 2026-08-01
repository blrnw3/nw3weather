<?php
/**
 * JSON data endpoint for the home-page interactive charts.
 *
 * Slices the 24hr rolling log (goodlog.txt) to the requested window and returns
 * every plottable variable as parallel arrays, so the front-end can switch
 * variables purely client-side (only a range change needs a new fetch).
 *
 * Log line layout (see cron_main.php): H,i,d,wind,gust,wdir,temp,humi,pres,dewp,rain,pm25
 * Wind is reported as a 10-min rolling mean and direction as a 15-min vector mean,
 * mirroring the legacy jpgraph charts (graphdaygen.php). Rain is the cumulative
 * running total as logged (not reindexed).
 */
require __DIR__ . '/Page.php';
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

$range = isset($_GET['range']) ? (int)$_GET['range'] : 6;
if (!in_array($range, array(6, 12, 24), true)) {
	$range = 6;
}
$wantLines = $range * 60;

$file = ROOT . 'goodlog.txt';
$out = array(
	'updated' => null,
	'range'   => $range,
	'time'    => array(),
	'temp'    => array(),
	'dewp'    => array(),
	'humi'    => array(),
	'pres'    => array(),
	'wind'    => array(),
	'gust'    => array(),
	'wdir'    => array(),
	'rain'    => array(),
	'pm25'    => array(),
);

if (is_readable($file)) {
	$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$total = count($lines);
	$slice = array_slice($lines, max(0, $total - $wantLines));

	$now   = time();
	$month = (int)Date::$dmonth;
	$year  = (int)Date::$dyear;

	// Wind: 10-min rolling mean (circular buffer). Direction: 15-min vector mean.
	$windBuf = array();
	$windSum = 0;
	$p = 0;
	$wdirs = new ChartDirMean(15);

	foreach ($slice as $ln) {
		$c = explode(',', $ln);
		if (count($c) < 11) {
			continue;
		}
		$h   = (int)$c[0];
		$mi  = (int)$c[1];
		$dom = (int)$c[2];
		$ts  = mktime($h, $mi, 0, $month, $dom, $year);
		// If the window straddles the 1st, earlier rows belong to the previous month.
		if ($ts > $now + 3600) {
			$ts = mktime($h, $mi, 0, $month - 1, $dom, $year);
		}

		$wind = ($c[3] === '') ? 0 : (float)$c[3];
		$windSum += $wind;
		$windBuf[$p % 10] = $wind;
		$p++;
		$windOut = $wind;
		if ($p >= 10) {
			$windOut = $windSum / 10;
			$windSum -= $windBuf[$p % 10];
		}

		$wdirs->add(($c[5] === '') ? 0 : (float)$c[5]);
		$dirOut = ($windOut > 0.5) ? $wdirs->mean() : -999;

		$out['time'][] = $ts * 1000;
		$out['temp'][] = ($c[6] === '') ? null : (float)$c[6];
		$out['humi'][] = ($c[7] === '') ? null : (float)$c[7];
		$out['pres'][] = ($c[8] === '') ? null : (float)$c[8];
		$out['dewp'][] = ($c[9] === '') ? null : (float)$c[9];
		$out['rain'][] = ($c[10] === '') ? null : (float)$c[10];
		$out['wind'][] = round($windOut, 1);
		$out['gust'][] = ($c[4] === '') ? null : (float)$c[4];
		$out['wdir'][] = ($dirOut < 0) ? null : round($dirOut);
		$out['pm25'][] = (isset($c[11]) && $c[11] !== '') ? (float)$c[11] : null;
	}

	$n = count($out['time']);
	$out['updated'] = $n ? (int)($out['time'][$n - 1] / 1000) : filemtime($file);
}

echo json_encode($out);

/**
 * Rolling vector mean for wind direction (degrees), ported from the legacy
 * jpgraph rollingMean class so the interactive charts match the old graphs.
 * mean() returns -100 for a heavily drifting average; callers treat <0 as no data.
 */
class ChartDirMean {
	const BITIFIER = 120;
	private $size;
	private $items = array();
	private $pointer = 0;
	private $curr = 0;
	private $oldMean = -99;

	function __construct($size) {
		$this->size = $size;
	}

	function add($item) {
		$this->curr = $item;
		$this->items[$this->pointer % $this->size] = $item;
		$this->pointer++;
	}

	function mean() {
		if ($this->pointer <= $this->size) {
			return $this->curr;
		}
		$freqs = array();
		for ($i = 0; $i <= 360 / self::BITIFIER; $i++) {
			$freqs[$i] = 0;
		}
		for ($i = 0; $i < $this->size; $i++) {
			$freqs[(int)round($this->items[$i] / self::BITIFIER)]++;
		}
		$pivot = array_search(min($freqs), $freqs) * self::BITIFIER;
		$sum = 0;
		for ($i = 0; $i < $this->size; $i++) {
			$sum += $this->items[$i];
			if ($this->items[$i] > $pivot) {
				$sum -= 360;
			}
		}
		$mean = $sum / $this->size;
		if ($mean < 0) {
			$mean += 360;
		}
		$old = $this->oldMean;
		$this->oldMean = $mean;
		if ($old !== -99 && abs($mean - $old) > 5) {
			return -100;
		}
		return $mean;
	}
}
