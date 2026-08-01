<?php
/**
 * Fix corrupted air-frost-hours CSV headers for 2014–2025.
 *
 * Those years name the frost-hours column "0" instead of "afhrs", so
 * serialiseCSV() stores values under key "0" and serialised_dat_new_afhrs.txt
 * omits 2014–2025 entirely.
 *
 * Usage (docroot = directory containing datYYYY.csv):
 *   php fix_afhrs_csv_headers.php /var/www/html
 *   php fix_afhrs_csv_headers.php /var/www/html --reserialise
 *
 * Safe to re-run: files that already have an "afhrs" header are skipped.
 */

$root = isset($argv[1]) ? rtrim($argv[1], '/') . '/' : __DIR__ . '/../site/';
$reserialise = in_array('--reserialise', $argv, true);

$fixed = 0;
$skipped = 0;

foreach (array('dat', 'datt') as $prefix) {
	for ($year = 2014; $year <= 2025; $year++) {
		$file = $root . $prefix . $year . '.csv';
		if (!file_exists($file)) {
			echo "skip (missing): $file\n";
			$skipped++;
			continue;
		}
		$raw = file_get_contents($file);
		if ($raw === false || $raw === '') {
			echo "skip (empty): $file\n";
			$skipped++;
			continue;
		}
		$lines = preg_split("/\r\n|\n|\r/", $raw);
		if (end($lines) === '') { array_pop($lines); }
		if (!$lines) {
			echo "skip (empty): $file\n";
			$skipped++;
			continue;
		}
		$header = explode(',', $lines[0]);
		if (in_array('afhrs', $header, true)) {
			echo "skip (already ok): $file\n";
			$skipped++;
			continue;
		}
		$fmeanIdx = array_search('fmean', $header, true);
		if ($fmeanIdx === false || !isset($header[$fmeanIdx + 1])) {
			echo "skip (no fmean/next col): $file\n";
			$skipped++;
			continue;
		}
		$next = (string)$header[$fmeanIdx + 1];
		if ($next !== '0' && $next !== '') {
			echo "skip (unexpected col after fmean='$next'): $file\n";
			$skipped++;
			continue;
		}
		$header[$fmeanIdx + 1] = 'afhrs';
		$lines[0] = implode(',', $header);
		$out = implode("\n", $lines) . "\n";
		file_put_contents($file, $out);
		echo "fixed: $file (col " . ($fmeanIdx + 1) . " -> afhrs)\n";
		$fixed++;
	}
}

echo "Done. fixed=$fixed skipped=$skipped\n";

if (!$reserialise) {
	echo "Re-run with --reserialise to rebuild serialised_dat_new_afhrs.txt\n";
	exit(0);
}

if (!defined('ROOT')) {
	require $root . 'basics.php';
}
global $dyear, $siteRoot;
if (!isset($siteRoot)) { $siteRoot = ROOT; }
if (!isset($dyear)) { $dyear = (int)date('Y'); }

function nw3_serialise_csv_fixed($csv) {
	global $dyear, $siteRoot;

	$data = array();
	$dataNew = array();

	for ($year = 2009; $year <= $dyear; $year++) {
		$yrfil = $siteRoot . $csv . $year . '.csv';
		if (!file_exists($yrfil)) { continue; }
		$raw = file($yrfil);
		$header = explode(',', trim($raw[0]));
		$cntRaw = count($raw);
		for ($i = 1; $i < $cntRaw; $i++) {
			$ts = strtotime('Jan 1st ' . (string)$year . ' + ' . (string)($i - 1) . ' days');
			$day = (int)date('j', $ts);
			$month = (int)date('n', $ts);
			$rawa = explode(',', $raw[$i]);
			$cntRawa = count($rawa);
			for ($j = 0; $j < $cntRawa; $j++) {
				if (!isset($header[$j])) { continue; }
				$data[$j][$year][$month][$day] = $rawa[$j];
				$dataNew[$header[$j]][$year][$month][$day] = $rawa[$j];
			}
		}
	}
	file_put_contents(ROOT . 'serialised_' . $csv . '.txt', serialize($data));
	file_put_contents(ROOT . 'serialised_' . $csv . '_new.txt', serialize($dataNew));
	if ($csv === 'dat') {
		foreach ($data as $j => $dat) {
			file_put_contents(ROOT . "serialised_dat_$j.txt", serialize($dat));
		}
		foreach ($dataNew as $j => $dat) {
			file_put_contents(ROOT . "serialised_dat_new_$j.txt", serialize($dat));
		}
	}
	$years = isset($dataNew['afhrs']) ? implode(',', array_keys($dataNew['afhrs'])) : '(none)';
	echo "serialised $csv; afhrs years: $years\n";
}

nw3_serialise_csv_fixed('dat');
nw3_serialise_csv_fixed('datt');
echo "Reserialise complete.\n";
