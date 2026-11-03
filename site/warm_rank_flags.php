<?php
/**
 * Pre-compute the home page "notable in the rankings" flag (RankFlags) into
 * CACHE_ROOT/rank_flags.json. Run by cron_main every 5 minutes in the background.
 *
 *   php warm_rank_flags.php         # build and write
 *   php warm_rank_flags.php show    # build and print, no write
 */
if (PHP_SAPI !== 'cli') {
	http_response_code(403);
	die("CLI only.\n");
}
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~2048);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('memory_limit', '1G');
date_default_timezone_set('Europe/London');

require __DIR__ . '/UtilsAndConsts.php';
require __DIR__ . '/WxDefinition.php';
require __DIR__ . '/WxFn.php';
require __DIR__ . '/Spells.php';
require __DIR__ . '/Report.php';
require __DIR__ . '/RankFlags.php';

$lock = fopen(CACHE_ROOT . 'rank_flags.lock', 'c');
if ($lock === false || !flock($lock, LOCK_EX | LOCK_NB)) {
	exit(0); // previous run still going
}
$show = (isset($argv[1]) && $argv[1] === 'show');
$data = $show ? RankFlags::build() : RankFlags::warm();
if ($show) {
	echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), "\n";
} else {
	echo count($data['items']) . ' items in ' . $data['ms'] . " ms\n";
}
