<?php
/**
 * Build / update the daily profile cache (DayProfile) from the minute logs. Run nightly from cron_main.php; reads the
 * logs, datm/dat CSVs and sunlog, and writes only CACHE_ROOT/dayprofile/.
 *   php warm_day_profile.php          incremental: rescan months whose sources changed
 *   php warm_day_profile.php full     rescan everything
 *   php warm_day_profile.php day YYYY-MM-DD   print one day's quality decisions (no writes)
 *   php warm_day_profile.php audit [FROM [TO]]  one JSON line per day with every variable's decision (no writes)
 */
if (PHP_SAPI !== 'cli') {
	http_response_code(403);
	die("CLI only.\n");
}
require_once __DIR__ . '/cron/bootstrap.php';
require_once ROOT . 'DayProfile.php';
ini_set('memory_limit', '512M');

$mode = $argv[1] ?? '';
if ($mode === 'day' && isset($argv[2]) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $argv[2])) {
	$notes = DayProfile::loadNotes();
	$acc = [];
	foreach (DayProfile::$VARS as $v => $c) { $acc[$v] = [array_fill(0, DayProfile::NBIN, 0.0), array_fill(0, DayProfile::NBIN, 0)]; }
	$r = DayProfile::scanDay($argv[2], $notes, $acc);
	echo json_encode(['notes' => $notes['notes'][$argv[2]] ?? null, 'text' => DayProfile::textRules($notes['notes'][$argv[2]] ?? null),
		'range' => DayProfile::rangeRules($argv[2])] + $r, JSON_PRETTY_PRINT), "\n";
	exit(0);
}
if ($mode === 'audit') {
	$notes = DayProfile::loadNotes();
	$d = strtotime($argv[2] ?? (DayProfile::FIRST_YEAR . '-01-01'));
	$end = strtotime($argv[3] ?? 'yesterday');
	for (; $d <= $end; $d = strtotime('+1 day', $d)) {
		$acc = [];
		foreach (DayProfile::$VARS as $v => $c) { $acc[$v] = [array_fill(0, DayProfile::NBIN, 0.0), array_fill(0, DayProfile::NBIN, 0)]; }
		$ymd = date('Y-m-d', $d);
		$r = DayProfile::scanDay($ymd, $notes, $acc);
		echo json_encode(['d' => $ymd, 'why' => $r['why'], 'dbg' => $r['debug'] ?? null, 'scaled' => $r['scaled'], 'sunEst' => $r['sunEst'] ?? false]), "\n";
	}
	exit(0);
}
$stats = DayProfile::build($mode === 'full', null, in_array('-v', $argv, true) ? function ($m) { echo $m, "\n"; } : null);
if ($stats === null) {
	echo date('c'), " day profile: another build is running (or the cache dir is not writable)\n";
	exit(0);
}
echo date('c'), ' day profile: ', json_encode($stats), "\n";
