<?php
/**
 * Fix corrupted air-frost-hours CSV headers in datYYYY.csv / dattYYYY.csv.
 *
 * Affected years name the frost-hours column "0" instead of "afhrs", so
 * serialiseCSV() stores those values under key "0" and
 * serialised_dat_new_afhrs.txt omits those years entirely.
 *
 * Only the header line is rewritten; the rest of the file (including its
 * original line endings) is left byte-for-byte unchanged.
 *
 * Usage (docroot = directory containing datYYYY.csv):
 *   php fix_afhrs_csv_headers.php /var/www/html --dry-run
 *   php fix_afhrs_csv_headers.php /var/www/html
 *   php fix_afhrs_csv_headers.php /var/www/html --reserialise
 *
 * Safe to re-run: files that already have an "afhrs" header are skipped.
 */

$root = isset($argv[1]) && substr($argv[1], 0, 2) !== '--'
	? rtrim($argv[1], '/') . '/'
	: __DIR__ . '/../site/';
$reserialise = in_array('--reserialise', $argv, true);
$dryRun = in_array('--dry-run', $argv, true);

$fixed = 0;
$skipped = 0;

/** datYYYY.csv / dattYYYY.csv in $root, oldest first. Excludes datm (manual data). */
function nw3_year_csv_files($root) {
	$files = array();
	foreach (glob($root . 'dat*.csv') as $path) {
		if (preg_match('/^datt?(\d{4})\.csv$/', basename($path), $m)) {
			$files[] = array($path, (int)$m[1]);
		}
	}
	usort($files, function ($a, $b) {
		return ($a[1] === $b[1]) ? strcmp($a[0], $b[0]) : $a[1] - $b[1];
	});
	return $files;
}

foreach (nw3_year_csv_files($root) as $entry) {
	list($file, $year) = $entry;
	$raw = file_get_contents($file);
	if ($raw === false || $raw === '') {
		echo "skip (empty): $file\n";
		$skipped++;
		continue;
	}

	// Split off the header line, keeping its EOL and the remaining bytes intact.
	$nlPos = strpos($raw, "\n");
	$headerLine = ($nlPos === false) ? $raw : substr($raw, 0, $nlPos);
	$rest = ($nlPos === false) ? '' : substr($raw, $nlPos);
	$eol = '';
	if (substr($headerLine, -1) === "\r") {
		$headerLine = substr($headerLine, 0, -1);
		$eol = "\r";
	}

	$header = explode(',', $headerLine);
	if (in_array('afhrs', $header, true)) {
		echo "skip (already ok): $file\n";
		$skipped++;
		continue;
	}
	$fmeanIdx = array_search('fmean', $header, true);
	if ($fmeanIdx === false || !isset($header[$fmeanIdx + 1])) {
		// Pre-afhrs years (no feels-like columns) have nothing to fix.
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
	$col = $fmeanIdx + 1;
	if ($dryRun) {
		echo "would fix: $file (col $col -> afhrs)\n";
		$fixed++;
		continue;
	}
	file_put_contents($file, implode(',', $header) . $eol . $rest);
	echo "fixed: $file (col $col -> afhrs)\n";
	$fixed++;
}

if ($dryRun) {
	echo "Dry run. would_fix=$fixed skipped=$skipped (no files written)\n";
	exit(0);
}

echo "Done. fixed=$fixed skipped=$skipped\n";

if (!$reserialise) {
	echo "Re-run with --reserialise to rebuild serialised_dat_new_afhrs.txt\n";
	exit(0);
}

if (!defined('ROOT')) {
	define('ROOT', '/var/www/html/');
	require $root . 'config/paths.php';
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
	file_put_contents(CACHE_ROOT . 'serialised_' . $csv . '.txt', serialize($data));
	file_put_contents(CACHE_ROOT . 'serialised_' . $csv . '_new.txt', serialize($dataNew));
	if ($csv === 'dat') {
		foreach ($data as $j => $dat) {
			file_put_contents(CACHE_ROOT . "serialised_dat_$j.txt", serialize($dat));
		}
		foreach ($dataNew as $j => $dat) {
			file_put_contents(CACHE_ROOT . "serialised_dat_new_$j.txt", serialize($dat));
		}
	}
	$years = isset($dataNew['afhrs']) ? implode(',', array_keys($dataNew['afhrs'])) : '(none)';
	echo "serialised $csv; afhrs years: $years\n";
}

nw3_serialise_csv_fixed('dat');
nw3_serialise_csv_fixed('datt');
echo "Reserialise complete.\n";
