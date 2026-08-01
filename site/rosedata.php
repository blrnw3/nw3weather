<?php
/**
 * JSON data endpoint for wind roses, replacing the legacy jpgraph windrose.php.
 *
 * Returns 16 compass directions x speed-bucket percentages, ready for a
 * Highcharts polar stacked column (one series per speed bucket).
 *
 * Params:
 *   st   start datestamp (Ymd); defaults to the 1st of last month
 *   en   month | year | now | 24hrs | <Ymd>  (default: month)
 */
require __DIR__ . '/Page.php';
ini_set('display_errors', '0');
Live::init();

header('Content-Type: application/json; charset=utf-8');

$stRaw = isset($_GET['st']) ? preg_replace('/[^0-9]/', '', $_GET['st']) : '';
$enRaw = isset($_GET['en']) ? preg_replace('/[^a-z0-9]/', '', $_GET['en']) : 'month';

$st = ($stRaw === '') ? Date::mkdate(Date::$mon_yest, 1, Date::$yr_yest) : Date::datestamp_to_ts($stRaw);

if ($enRaw === 'month') {
	$en = Date::datestamp_to_ts(date('Ym', $st) . Util::zerolead(Date::get_days_in_month(date('m', $st), date('Y', $st))));
	$roseDate = date('M Y', $st);
} elseif ($enRaw === 'year') {
	$en = Date::datestamp_to_ts(date('Y', $st) . '1231');
	$roseDate = date('Y', $st);
} elseif ($enRaw === 'now') {
	$en = time();
	$roseDate = date('d M Y', $st) . ' to present';
} elseif ($enRaw === '24hrs') {
	$st = time(); $en = time();
	$roseDate = 'past 24 hours';
} else {
	$en = Date::datestamp_to_ts($enRaw);
	$roseDate = ($en === $st) ? date('d M Y', $st) : date('d M Y', $st) . ' to ' . date('d M Y', $en);
}

$buckets = [1, 3, 5, 8, 11, 15, 20, 50];

/** Returns the array of per-day wind-direction roses covering [st, en]. */
function extractRoses($st, $en, $enRaw) {
	$today = date('Ymd');
	if ($enRaw === '24hrs') {
		return [Live::$HR24['windDirs']];
	}
	$stStamp = date('Ymd', $st);
	$enStamp = date('Ymd', $en);
	if ($stStamp === $enStamp && $stStamp === $today) {
		return [Live::$NOW['windDirs']];
	}
	$roses = [];
	$file = ROOT . 'datwdirdaily.dat';
	if (file_exists($file)) {
		foreach (file($file) as $i => $line) {
			$rose = @unserialize($line);
			if (!is_array($rose) || !isset($rose['dt'])) { continue; }
			if ($rose['dt'] > $enStamp) { break; }
			if ($rose['dt'] >= $stStamp) { $roses[$i] = $rose; }
		}
	}
	if ($enStamp >= $today && $stStamp <= $today) {
		$roses[] = Live::$NOW['windDirs'];
	}
	return $roses;
}

/** Sums many per-day roses into one [dir][speed] => count map (+ tot_cnt). */
function combineRoses($roses) {
	$overall = []; $totCnt = 0; $size = count($roses);
	foreach ($roses as $rose) {
		if (!is_array($rose)) { continue; }
		foreach ($rose as $dir => $speeds) {
			if ($dir === 'dt' || !is_array($speeds)) { continue; }
			foreach ($speeds as $spd => $cnt) {
				if ($cnt > 1300 && $size > 7) { continue; } // skip suspect days
				if (!isset($overall[$dir][$spd])) { $overall[$dir][$spd] = 0; }
				$overall[$dir][$spd] += $cnt;
				$totCnt += $cnt;
			}
		}
	}
	$overall['tot_cnt'] = $totCnt;
	return $overall;
}

$combined = combineRoses(extractRoses($st, $en, $enRaw));
$totCnt = $combined['tot_cnt'];

$dirLabels = Wx::$windlabels; // 16 dirs, 0 = N
$bucketLabels = [];
$prev = 0;
foreach ($buckets as $i => $b) {
	$bucketLabels[] = ($i === count($buckets) - 1) ? "&ge;{$prev} mph" : "{$prev}-{$b} mph";
	$prev = $b;
}

// series[bucket] => data[16 dirs] as percentage of all observations
$series = [];
foreach ($buckets as $bi => $b) { $series[$bi] = array_fill(0, 16, 0.0); }
if ($totCnt > 0) {
	foreach ($combined as $dir => $speeds) {
		if ($dir === 'tot_cnt' || !is_array($speeds)) { continue; }
		$prev = 0;
		foreach ($buckets as $bi => $b) {
			foreach ($speeds as $spd => $cnt) {
				if ($spd >= $prev && $spd < $b) {
					$series[$bi][(int)$dir] += $cnt / $totCnt * 100;
				}
			}
			$prev = $b;
		}
	}
}

$colours = ['#999999', '#f1c40f', '#e67e22', '#e03131', '#2554c7', '#7b2fb0', '#111111'];
$outSeries = [];
foreach ($buckets as $bi => $b) {
	$data = array_map(function($v) { return round($v, 2); }, $series[$bi]);
	$outSeries[] = ['name' => $bucketLabels[$bi], 'data' => $data, 'color' => $colours[$bi % count($colours)]];
}

echo json_encode([
	'title'      => "Wind rose for $roseDate",
	'total'      => $totCnt,
	'categories' => $dirLabels,
	'series'     => $outSeries,
]);
