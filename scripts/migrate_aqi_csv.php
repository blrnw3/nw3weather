<?php
// One-time, idempotent migration to add AQI (PM2.5) daily-aggregate columns
// (amin, amax, amean) to the current-year dat/datt CSVs.
//
// The new columns are inserted *before* the legacy trailing pad field so they
// line up with $types_original (…, afhrs, amin, amax, amean) and the extended
// $list/$listt arrays in data.php / cron_main.php serialiseCSV().
//
// Usage:  php migrate_aqi_csv.php /path/to/docroot/ [year]
// Safe to re-run: files already containing an "amin" header are skipped.

$root = isset($argv[1]) ? rtrim($argv[1], '/') . '/' : './';
$year = isset($argv[2]) ? (int)$argv[2] : (int)date('Y');

foreach (array('dat', 'datt') as $prefix) {
	$file = $root . $prefix . $year . '.csv';
	if (!file_exists($file)) {
		echo "skip (missing): $file\n";
		continue;
	}
	$lines = file($file, FILE_IGNORE_NEW_LINES);
	if (!$lines) {
		echo "skip (empty): $file\n";
		continue;
	}
	if (strpos($lines[0], 'amin') !== false) {
		echo "skip (already migrated): $file\n";
		continue;
	}

	// Header: append the three new column names.
	$lines[0] = $lines[0] . ',amin,amax,amean';

	// Data rows: insert three empty fields before the final (pad) field.
	for ($i = 1; $i < count($lines); $i++) {
		if ($lines[$i] === '') continue;
		$cells = explode(',', $lines[$i]);
		$pad = array_pop($cells);
		$cells[] = '';
		$cells[] = '';
		$cells[] = '';
		$cells[] = $pad;
		$lines[$i] = implode(',', $cells);
	}

	file_put_contents($file, implode("\n", $lines) . "\n");
	echo "migrated: $file (" . (count($lines) - 1) . " rows)\n";
}
