<?php
/**
 * Precompute the recap data packs a writer will ask for just after midnight (RecapPack::warmList), so
 * api_recap_pack.php answers from cache. Run from cron_main.php after the overnight data is written; a pack is
 * also rebuilt on demand whenever the data files change.
 *   php warm_recap_pack.php [kind period ...]
 *
 * After the packs, also curate the home-page notable-rankings card (once a day,
 * with this daily run): LLM if a key is configured, otherwise the deterministic
 * fallback. Does not rebuild the raw ranks; the 15-minute warmer also refreshes
 * the plan.
 */
if (PHP_SAPI !== 'cli') {
	http_response_code(403);
	die("CLI only.\n");
}
require_once __DIR__ . '/cron/bootstrap.php';
require_once ROOT . 'Recap.php';
require_once ROOT . 'RecapPack.php';
ini_set('memory_limit', '256M');

$list = [];
for ($i = 1; $i + 1 < $argc; $i += 2) { $list[] = [$argv[$i], $argv[$i + 1]]; }
if (!$list) { $list = RecapPack::warmList(); }
foreach ($list as $kp) {
	$t = microtime(true);
	try {
		RecapPack::get($kp[0], $kp[1]);
		echo "{$kp[0]} {$kp[1]} ok " . round(microtime(true) - $t, 2) . "s\n";
	} catch (RecapError $e) {
		echo "{$kp[0]} {$kp[1]} {$e->status} {$e->getMessage()}\n";
	}
}

require_once ROOT . 'RankFlags.php';
require_once ROOT . 'RankFlagsCurate.php';
$rkf = RankFlags::load();
if ($rkf) {
	$plan = RankFlagsCurate::refresh($rkf);
	echo 'rank flags curate ' . $plan['source'] . ' ' . count($plan['groups']) . " groups\n";
} else {
	echo "rank flags curate skipped (no fresh rank_flags cache)\n";
}
