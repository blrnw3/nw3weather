<?php
/**
 * Best webcam shots for wx2 (see WebcamBest.php). NOT in any crontab yet. Suggested (once a day, after the
 * 23:54 daily summary sheet and cron_main's midnight run; as nw3, like the other site crons):
 *   40 0 * * * /usr/bin/php -q /var/www/html/cron_webcam_best.php > /dev/null 2>&1
 * First run, to score the whole Hikvision archive (from 21 Jun 2018, a few minutes):
 *   sudo -u nw3 /usr/bin/php -q /var/www/html/cron_webcam_best.php --rebuild
 * Options: --rebuild  rescore every day;  --days=N  rescore the last N days (default 3);  --no-thumbs;
 *          --dump=FILE  write every daylight cell's features as JSON lines (for tuning the scoring);
 *          --write-seed  also save the selection as config/webcam_best_seed.php (shown until this cron first runs)
 */
if(PHP_SAPI !== 'cli') {
	http_response_code(403);
	die("CLI only.\n");
}
require_once((defined('ROOT') ? ROOT : '/var/www/html/') . 'cron/bootstrap.php');
require_once(ROOT . 'WebcamBest.php');

$t0 = microtime(true);
$opts = getopt('', array('rebuild', 'days:', 'no-thumbs', 'dump:', 'write-seed'));
$rebuild = isset($opts['rebuild']);
if(isset($opts['dump'])) { WebcamBest::$dump = fopen($opts['dump'], 'w'); }
$recent = isset($opts['days']) ? max(1, (int)$opts['days']) : 3;

$days = array();
if(!$rebuild && is_file(WebcamBest::daysFile())) {
	$days = json_decode(file_get_contents(WebcamBest::daysFile()), true);
	if(!is_array($days)) { $days = array(); }
}
$sig = WebcamBest::daySignals();
$pins = WebcamBest::pins();
$today = date('Y-m-d');
$redo = date('Y-m-d', strtotime("-$recent days", strtotime($today)));

$scored = 0;
for($ts = strtotime(WebcamBest::HIK_START . ' 00:00'); date('Y-m-d', $ts) < $today; $ts = strtotime('+1 day', $ts)) {
	$ymd = date('Y-m-d', $ts);
	if(array_key_exists($ymd, $days) && $ymd < $redo) { continue; }
	$s = isset($sig[$ymd]) ? $sig[$ymd] : array();
	$days[$ymd] = WebcamBest::scoreDay($ts, $s);
	$scored++;
}
ksort($days);
nw3_atomic_write(WebcamBest::daysFile(), json_encode($days));

$sel = WebcamBest::select($days, $today, $sig, $pins['exclude']);
nw3_atomic_write(WebcamBest::selectionFile(), json_encode($sel, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

if(isset($opts['write-seed'])) {
	file_put_contents(WebcamBest::seedFile(), "<?php\n// Saved by cron_webcam_best.php --write-seed on " . date('j M Y')
		. ": shown on webcambest.php until the cron's own selection exists.\nreturn " . var_export($sel, true) . ";\n");
}

$thumbs = 0;
if(!isset($opts['no-thumbs'])) {
	$keep = array();
	foreach(WebcamBest::galleryFrames() as $e) {   // includes pins
		$cam = isset($e['cam']) ? $e['cam'] : 'hik';
		$keep[WebcamBest::thumbName($e['date'], $e['hm'], $cam)] = true;
		if(WebcamBest::makeThumb($e['date'], $e['hm'], $cam)) { $thumbs++; }
	}
	// drop thumbnails of frames no longer picked (after a week, in case a page is still cached)
	foreach((array)glob(WebcamBest::thumbDir() . '*.jpg') as $f) {
		if(!isset($keep[basename($f)]) && filemtime($f) < time() - 7 * 86400) { @unlink($f); }
	}
}
echo "webcam_best: scored $scored days, " . count($days) . " in cache, $thumbs thumbs ok, "
	. round(microtime(true) - $t0, 1) . " s\n";
