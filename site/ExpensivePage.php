<?php
/**
 * File-cache + concurrency cap for expensive report pages.
 *
 * Cached hits are served from cache/v5/reports/{page}/ (atomic pid-tmp rename,
 * same scheme as nw3_atomic_write / cache v5). Uncached renders take a slot
 * from a flock semaphore (global and per-IP); when no slot is free the
 * request returns 503 + Retry-After without doing the heavy work.
 */

if (!defined('NW3_EXPENSIVE_CACHE_TTL_TODAY')) {
	define('NW3_EXPENSIVE_CACHE_TTL_TODAY', 600); // 10 minutes when today can change the result
}
if (!defined('NW3_EXPENSIVE_CACHE_TTL_HIST')) {
	define('NW3_EXPENSIVE_CACHE_TTL_HIST', 1800); // 30 minutes for historical-only views
}
if (!defined('NW3_EXPENSIVE_BUSY_SLOTS')) {
	define('NW3_EXPENSIVE_BUSY_SLOTS', 3);
}
if (!defined('NW3_EXPENSIVE_BUSY_PER_IP')) {
	define('NW3_EXPENSIVE_BUSY_PER_IP', 1);
}
if (!defined('NW3_EXPENSIVE_RETRY_AFTER')) {
	define('NW3_EXPENSIVE_RETRY_AFTER', 20);
}

/**
 * @param string $page short slug, e.g. 'rankmonth'
 * @return string
 */
function nw3_expensive_cache_dir($page) {
	$page = preg_replace('/[^a-z0-9_-]/', '', strtolower($page));
	if ($page === '') {
		$page = 'unknown';
	}
	return V5_CACHE_ROOT . 'reports/' . $page;
}

/**
 * Build a stable cache filename from normalised key parts (plus display units).
 * @param string $page
 * @param array $parts already-normalised scalar parts
 * @return string absolute path
 */
function nw3_expensive_cache_file($page, $parts) {
	$units = isset(Page::$units) ? (string)(int)Page::$units : '0';
	$aq = (isset(Page::$aqUnits) && Page::$aqUnits !== null) ? (string)Page::$aqUnits : '';
	$keyParts = array_merge(array($page, $units, $aq), $parts);
	$key = sha1(implode('|', $keyParts));
	return nw3_expensive_cache_dir($page) . '/' . $key . '.json';
}

/**
 * True when a later data update (today / current period) can change the result.
 * @param string $kind
 * @param Report $report
 * @return bool
 */
function nw3_expensive_is_today_dependent($kind, $report) {
	$cy = isset(Date::$dyear) ? (int)Date::$dyear : (int)date('Y');
	$cm = isset(Date::$dmonth) ? (int)Date::$dmonth : (int)date('n');
	$span = isset($report->span) ? $report->span : 'full';
	if ($span === 'mtd' || $span === 'ytd') {
		return true;
	}
	switch ($kind) {
		case 'dataday':
			return (int)$report->year === $cy;
		case 'rankday':
			return (int)$report->month === 0 || (int)$report->month === $cm;
		case 'rankmonth':
			return (int)$report->month === 0 || (int)$report->month === $cm;
		case 'rankyear':
			return true;
		case 'rankspells':
		case 'rankperiods':
			return (int)$report->month === 0 || (int)$report->month === $cm;
		case 'datamonth':
			return true;
		default:
			return !empty($report->hasToday);
	}
}

/**
 * @param bool $todayDependent
 * @return int
 */
function nw3_expensive_ttl($todayDependent) {
	return $todayDependent ? NW3_EXPENSIVE_CACHE_TTL_TODAY : NW3_EXPENSIVE_CACHE_TTL_HIST;
}

/**
 * Newest mtime of the v5 serialised data files that can affect this view.
 * @param bool $todayDependent
 * @return int unix timestamp, or 0 if none are present
 */
function nw3_expensive_data_mtime($todayDependent) {
	$files = array(
		CACHE_ROOT . 'serialised_dat.txt',
		CACHE_ROOT . 'serialised_datm.txt',
	);
	if ($todayDependent) {
		$files[] = CACHE_ROOT . 'serialised_datNow.txt';
		$files[] = CACHE_ROOT . 'serialised_dat_new_rain.txt';
	}
	$max = 0;
	for ($i = 0, $n = count($files); $i < $n; $i++) {
		if (is_file($files[$i])) {
			$m = @filemtime($files[$i]);
			if ($m && $m > $max) {
				$max = $m;
			}
		}
	}
	return $max;
}

/**
 * @param string $file
 * @param int $ttl
 * @param int $dataMtime
 * @return array|null decoded payload
 */
function nw3_expensive_cache_get($file, $ttl, $dataMtime) {
	if (!is_readable($file)) {
		return null;
	}
	$mtime = @filemtime($file);
	if (!$mtime) {
		return null;
	}
	if ((time() - $mtime) >= $ttl) {
		return null;
	}
	if ($dataMtime && $mtime < $dataMtime) {
		return null;
	}
	$raw = @file_get_contents($file);
	if ($raw === false || $raw === '') {
		return null;
	}
	$payload = json_decode($raw, true);
	return is_array($payload) ? $payload : null;
}

/**
 * Atomic write consistent with cache v5 (pid tmp + rename).
 * @param string $file
 * @param array $payload
 * @return bool
 */
function nw3_expensive_cache_put($file, $payload) {
	$dir = dirname($file);
	if (!is_dir($dir)) {
		@mkdir($dir, 02775, true);
		@chmod($dir, 02775);
	}
	$json = function_exists('nw3_json_encode')
		? nw3_json_encode($payload)
		: json_encode($payload);
	if ($json === false || $json === null) {
		return false;
	}
	if (function_exists('nw3_atomic_write')) {
		return nw3_atomic_write($file, $json) !== false;
	}
	$tmp = $file . '.tmp.' . getmypid();
	if (@file_put_contents($tmp, $json, LOCK_EX) === false) {
		@unlink($tmp);
		return false;
	}
	if (!@rename($tmp, $file)) {
		@unlink($tmp);
		return false;
	}
	return true;
}

/**
 * @return string
 */
function nw3_expensive_lock_dir() {
	$dir = V5_CACHE_ROOT . 'locks';
	if (!is_dir($dir)) {
		@mkdir($dir, 02775, true);
		@chmod($dir, 02775);
	}
	return $dir;
}

/**
 * Non-blocking flock semaphore: one uncached render per IP, and at most
 * NW3_EXPENSIVE_BUSY_SLOTS global uncached renders.
 * @param string $ip
 * @return array|false handles to release later
 */
function nw3_expensive_busy_acquire($ip) {
	$dir = nw3_expensive_lock_dir();
	$held = array();

	$ipKey = preg_replace('/[^0-9a-fA-F:.]/', '_', (string)$ip);
	if ($ipKey !== '' && NW3_EXPENSIVE_BUSY_PER_IP > 0) {
		$ipPath = $dir . '/ip-' . $ipKey . '.lock';
		$ipFh = @fopen($ipPath, 'c');
		if ($ipFh === false) {
			return false;
		}
		if (!@flock($ipFh, LOCK_EX | LOCK_NB)) {
			fclose($ipFh);
			return false;
		}
		$held[] = $ipFh;
	}

	$slots = (int)NW3_EXPENSIVE_BUSY_SLOTS;
	if ($slots < 1) {
		$slots = 1;
	}
	$got = false;
	for ($i = 0; $i < $slots; $i++) {
		$path = $dir . '/slot-' . $i . '.lock';
		$fh = @fopen($path, 'c');
		if ($fh === false) {
			continue;
		}
		if (@flock($fh, LOCK_EX | LOCK_NB)) {
			$held[] = $fh;
			$got = true;
			break;
		}
		fclose($fh);
	}
	if (!$got) {
		nw3_expensive_busy_release($held);
		return false;
	}
	return $held;
}

/**
 * @param array $held
 * @return void
 */
function nw3_expensive_busy_release($held) {
	if (!is_array($held)) {
		return;
	}
	for ($i = 0, $n = count($held); $i < $n; $i++) {
		if ($held[$i]) {
			@flock($held[$i], LOCK_UN);
			@fclose($held[$i]);
		}
	}
}

/**
 * Fast 503 used when every uncached slot is taken. Discards any page chrome
 * already buffered by Page.php so the status code can still be set.
 * @return void
 */
function nw3_expensive_busy_reject() {
	while (ob_get_level() > 0) {
		ob_end_clean();
	}
	http_response_code(503);
	header('Retry-After: ' . (int)NW3_EXPENSIVE_RETRY_AFTER);
	header('Content-Type: text/plain; charset=utf-8');
	header('Cache-Control: no-store');
	header('Connection: close');
	echo "Service temporarily busy. Please retry shortly.\n";
	exit;
}

/**
 * @return string
 */
function nw3_expensive_client_ip() {
	if (class_exists('Page') && isset(Page::$ip) && Page::$ip !== '') {
		return Page::$ip;
	}
	return isset($_SERVER['REMOTE_ADDR']) ? (string)$_SERVER['REMOTE_ADDR'] : '';
}

/**
 * Run $renderFn (echoes HTML, returns $meta) under the cache + busy cap.
 * Cached hits skip the cap. On a miss, the cap is taken, the cache is checked
 * again (another worker may have filled it), then the renderer runs.
 *
 * @param string $kind
 * @param Report $report
 * @param callable $renderFn
 * @param array $keyParts
 * @return array meta from the renderer (or the cached copy)
 */
function nw3_expensive_html($kind, $report, $renderFn, $keyParts) {
	$today = nw3_expensive_is_today_dependent($kind, $report);
	$ttl = nw3_expensive_ttl($today);
	$dataMtime = nw3_expensive_data_mtime($today);
	$file = nw3_expensive_cache_file($kind, $keyParts);

	$payload = nw3_expensive_cache_get($file, $ttl, $dataMtime);
	if (is_array($payload) && isset($payload['html']) && array_key_exists('meta', $payload)) {
		echo $payload['html'];
		return is_array($payload['meta']) ? $payload['meta'] : array();
	}

	$locks = nw3_expensive_busy_acquire(nw3_expensive_client_ip());
	if ($locks === false) {
		nw3_expensive_busy_reject();
	}

	$payload = nw3_expensive_cache_get($file, $ttl, $dataMtime);
	if (is_array($payload) && isset($payload['html']) && array_key_exists('meta', $payload)) {
		nw3_expensive_busy_release($locks);
		echo $payload['html'];
		return is_array($payload['meta']) ? $payload['meta'] : array();
	}

	ob_start();
	$meta = call_user_func($renderFn, $report);
	$html = ob_get_clean();
	if (!is_array($meta)) {
		$meta = array();
	}
	nw3_expensive_cache_put($file, array('html' => $html, 'meta' => $meta));
	nw3_expensive_busy_release($locks);

	echo $html;
	return $meta;
}

/**
 * Same as nw3_expensive_html but the renderer returns the payload (JSON
 * endpoints such as datadaydata.php) instead of echoing HTML.
 *
 * @param string $kind
 * @param Report $report
 * @param callable $renderFn
 * @param array $keyParts
 * @return mixed
 */
function nw3_expensive_payload($kind, $report, $renderFn, $keyParts) {
	$today = nw3_expensive_is_today_dependent($kind, $report);
	$ttl = nw3_expensive_ttl($today);
	$dataMtime = nw3_expensive_data_mtime($today);
	$file = nw3_expensive_cache_file($kind, $keyParts);

	$payload = nw3_expensive_cache_get($file, $ttl, $dataMtime);
	if (is_array($payload) && array_key_exists('data', $payload)) {
		return $payload['data'];
	}

	$locks = nw3_expensive_busy_acquire(nw3_expensive_client_ip());
	if ($locks === false) {
		nw3_expensive_busy_reject();
	}

	$payload = nw3_expensive_cache_get($file, $ttl, $dataMtime);
	if (is_array($payload) && array_key_exists('data', $payload)) {
		nw3_expensive_busy_release($locks);
		return $payload['data'];
	}

	$data = call_user_func($renderFn, $report);
	nw3_expensive_cache_put($file, array('data' => $data));
	nw3_expensive_busy_release($locks);
	return $data;
}

/**
 * Normalised cache-key parts for a given report kind.
 * @param string $kind
 * @param Report $report
 * @return array
 */
function nw3_expensive_key_parts($kind, $report) {
	switch ($kind) {
		case 'rankmonth':
			return array(
				(string)$report->type,
				(string)(int)$report->month,
				(string)(int)$report->startYrReport,
				(string)(int)$report->summaryType,
				(string)(int)$report->rankLimit,
				(string)$report->spellThreshold,
				(string)$report->span,
			);
		case 'rankday':
			return array(
				(string)$report->type,
				(string)(int)$report->month,
				(string)(int)$report->day,
				(string)(int)$report->win,
				(string)(int)$report->startYrReport,
				(string)(int)$report->rankLimit,
			);
		case 'rankyear':
			return array(
				(string)$report->type,
				(string)(int)$report->startYrReport,
				(string)(int)$report->summaryType,
				(string)(int)$report->rankLimit,
				(string)$report->spellThreshold,
				(string)$report->span,
			);
		case 'rankspells':
			return array(
				(string)$report->type,
				(string)(int)$report->month,
				(string)(int)$report->startYrReport,
				(string)(int)$report->rankLimit,
				(string)$report->spellDir,
				(string)$report->spellThreshold,
			);
		case 'rankperiods':
			return array(
				(string)$report->type,
				(string)(int)$report->month,
				(string)(int)$report->startYrReport,
				(string)(int)$report->summaryType,
				(string)(int)$report->rankLimit,
				(string)(int)$report->periodLength,
				!empty($report->periodNoOverlap) ? '1' : '0',
				(string)$report->spellThreshold,
			);
		case 'datamonth':
			return array(
				(string)$report->type,
				(string)(int)$report->startYrReport,
				(string)(int)$report->summaryType,
				(string)$report->spellThreshold,
				(string)$report->span,
			);
		case 'dataday':
			return array(
				(string)$report->type,
				(string)(int)$report->year,
				(string)$report->dayAgg,
				!empty($report->showRecordYears) ? '1' : '0',
			);
		default:
			return array((string)$report->type);
	}
}
