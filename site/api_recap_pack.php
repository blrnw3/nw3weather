<?php
/**
 * Data pack for a recap writer (see RecapPack.php): GET /api_recap_pack.php?kind=K&period=P
 *   kind: day | month_to_date | year_to_date | month | year; period: YYYY-MM-DD | YYYY-MM | YYYY (London calendar).
 * Public and read-only. Cached in V5_CACHE_ROOT/recap_pack/ and rebuilt when the data files change.
 */
require __DIR__ . '/Page.php';
require_once __DIR__ . '/Recap.php';
require_once __DIR__ . '/RecapPack.php';
ini_set('display_errors', '0');
ini_set('memory_limit', '256M');
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

$kind = isset($_GET['kind']) ? (string)$_GET['kind'] : '';
$period = isset($_GET['period']) ? (string)$_GET['period'] : '';
try {
	$pack = RecapPack::get($kind, $period);
	header('Cache-Control: public, max-age=300');
	$json = json_encode($pack, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION
		| (isset($_GET['pretty']) ? JSON_PRETTY_PRINT : 0));
	echo $json;
} catch (RecapError $e) {
	http_response_code($e->status);
	header('Cache-Control: no-store');
	if ($e->status === 503) { header('Retry-After: 5'); }
	echo json_encode(['error' => $e->getMessage()]);
}
