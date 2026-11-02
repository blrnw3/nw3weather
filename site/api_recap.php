<?php
/**
 * Written recaps (see Recap.php).
 *
 * POST /api_recap.php            ingest one recap; "Authorization: Bearer <token>" (or "X-Recap-Token: <token>"),
 *                                compared with NW3_RECAP_TOKEN from secrets.php. Upserts on (kind, period) and
 *                                answers {status: inserted | updated | unchanged, record, html, ignored}.
 *                                503 while the token or the storage dir is not set up.
 * POST /api_recap.php?dry_run=1  validate and render only; writes nothing (works before the storage dir exists).
 * GET  /api_recap.php            the latest entry of each kind, with a "fresh" flag and the expected periods.
 * GET  /api_recap.php?kind=K&period=P   one entry.      GET /api_recap.php?kind=K   the stored periods of a kind.
 */
require __DIR__ . '/Page.php';
require_once __DIR__ . '/Recap.php';
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function recap_send($code, $data, $cacheSecs = 0) {
	http_response_code($code);
	header('Cache-Control: ' . ($cacheSecs > 0 ? "public, max-age=$cacheSecs" : 'no-store'));
	echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	exit;
}

function recap_error($code, $msg) {
	recap_send($code, ['error' => $msg]);
}

/** The configured token, or '' when it is missing, too short or still a placeholder. */
function recap_server_token() {
	if (!defined('NW3_RECAP_TOKEN') && is_readable(ROOT . 'secrets.php')) {
		require_once ROOT . 'secrets.php';
	}
	if (!defined('NW3_RECAP_TOKEN')) {
		return '';
	}
	$t = constant('NW3_RECAP_TOKEN');
	if (!is_string($t) || strlen($t) < 32 || stripos($t, 'CHANGE_ME') !== false) {
		return '';
	}
	return $t;
}

function recap_given_token() {
	$h = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '');
	if ($h === '' && function_exists('getallheaders')) {
		foreach (getallheaders() as $k => $v) {
			if (strcasecmp($k, 'Authorization') === 0) { $h = $v; }
		}
	}
	if (preg_match('/^Bearer[ \t]+(\S+)[ \t]*$/', (string)$h, $m)) {
		return $m[1];
	}
	return trim((string)($_SERVER['HTTP_X_RECAP_TOKEN'] ?? ''));
}

function recap_flag($name) {
	$v = isset($_GET[$name]) ? strtolower(trim((string)$_GET[$name])) : '';
	return in_array($v, ['1', 'true', 'yes', 'on'], true);
}

function recap_log($line) {
	$dir = Recap::dir();
	if ($dir !== null && is_writable($dir)) {
		@file_put_contents($dir . 'ingest.log', gmdate('Y-m-d\TH:i:s\Z') . "\t" . $line . "\n", FILE_APPEND | LOCK_EX);
	}
}

function recap_post() {
	$token = recap_server_token();
	if ($token === '') {
		recap_error(503, 'ingest disabled: server token not configured');
	}
	$given = recap_given_token();
	if ($given === '' || !hash_equals($token, $given)) {
		usleep(250000);
		recap_error(401, 'unauthorized');
	}
	$len = isset($_SERVER['CONTENT_LENGTH']) ? (int)$_SERVER['CONTENT_LENGTH'] : -1;
	if ($len <= 0 || $len > Recap::MAX_BYTES) {
		recap_error(413, 'body required and must be at most ' . Recap::MAX_BYTES . ' bytes');
	}
	$raw = (string)file_get_contents('php://input', false, null, 0, Recap::MAX_BYTES + 1);
	if (strlen($raw) > Recap::MAX_BYTES) {
		recap_error(413, 'body must be at most ' . Recap::MAX_BYTES . ' bytes');
	}
	$body = json_decode($raw, true, 8);
	$now = time();
	try {
		$parsed = Recap::validate($body, $now);
	} catch (RecapError $e) {
		recap_error($e->status, $e->getMessage());
	}
	$rec = $parsed['record'];
	$today = Recap::today($now);
	$out = ['record' => Recap::publicRecord($rec, $today), 'html' => Recap::render($rec['body_md']), 'ignored' => $parsed['ignored']];
	if (recap_flag('dry_run')) {
		recap_send(200, ['status' => 'dry_run', 'dry_run' => true] + $out);
	}
	try {
		$status = Recap::upsert($rec, $now);
	} catch (RecapError $e) {
		recap_error($e->status, $e->getMessage());
	}
	recap_log("{$rec['kind']}\t{$rec['period']}\t$status\t" . mb_strlen($rec['body_md'], 'UTF-8') . " chars\tgenerated {$rec['generated_at']}");
	recap_send(200, ['status' => $status, 'dry_run' => false] + $out);
}

function recap_get() {
	$today = Recap::today();
	$available = Recap::dir() !== null;
	$kind = isset($_GET['kind']) ? (string)$_GET['kind'] : null;
	if ($kind !== null) {
		if (!Recap::isKind($kind)) {
			recap_error(400, 'kind must be one of ' . implode(', ', Recap::KINDS));
		}
		if (isset($_GET['period'])) {
			$rec = Recap::get($kind, (string)$_GET['period']);
			if ($rec === null) {
				recap_error(404, 'no such recap');
			}
			recap_send(200, ['result' => Recap::publicRecord($rec, $today)], 300);
		}
		recap_send(200, ['result' => ['kind' => $kind, 'periods' => Recap::periods($kind), 'available' => $available]], 300);
	}
	$result = ['available' => $available];
	$expected = [];
	foreach (Recap::KINDS as $k) {
		$rec = Recap::latest($k);
		$result[$k] = $rec ? Recap::publicRecord($rec, $today) : null;
		$expected[$k] = Recap::expected($k, $today);
	}
	$result['expected'] = $expected;
	recap_send(200, ['result' => $result, 'server' => ['date' => $today, 'now' => gmdate('Y-m-d\TH:i:s\Z')]], 60);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'POST') {
	recap_post();
} elseif ($method === 'GET' || $method === 'HEAD') {
	recap_get();
} else {
	header('Allow: GET, POST');
	recap_error(405, 'method not allowed');
}
