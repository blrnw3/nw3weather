<?php
/**
 * File-cache wrapper for nw3_rankperiods_render().
 * RankPeriods queries are deterministic over short windows; cache HTML+meta
 * under cache/v5/rankperiods/ to absorb scraper/param-grid repeats.
 */

if (!defined('NW3_RANKPERIODS_CACHE_TTL')) {
	define('NW3_RANKPERIODS_CACHE_TTL', 600); // 10 minutes
}

/**
 * @param Report $report
 * @return string
 */
function nw3_rankperiods_cache_key(Report $report) {
	$parts = array(
		(string)$report->type,
		(string)(int)$report->month,
		(string)(int)$report->startYrReport,
		(string)(int)$report->summaryType,
		(string)(int)$report->rankLimit,
		(string)(int)$report->periodLength,
		!empty($report->periodNoOverlap) ? '1' : '0',
		(string)$report->spellThreshold,
	);
	return sha1(implode('|', $parts));
}

/**
 * @param Report $report
 * @return array meta for AJAX clients (same as nw3_rankperiods_render)
 */
function nw3_rankperiods_cached_render(Report $report) {
	$dir = __DIR__ . '/cache/v5/rankperiods';
	if (!is_dir($dir)) {
		@mkdir($dir, 0775, true);
	}
	$file = $dir . '/' . nw3_rankperiods_cache_key($report) . '.json';

	if (is_readable($file) && (time() - filemtime($file)) < NW3_RANKPERIODS_CACHE_TTL) {
		$raw = @file_get_contents($file);
		$payload = $raw !== false ? json_decode($raw, true) : null;
		if (is_array($payload) && isset($payload['html'], $payload['meta']) && is_array($payload['meta'])) {
			echo $payload['html'];
			return $payload['meta'];
		}
	}

	ob_start();
	$meta = nw3_rankperiods_render($report);
	$html = ob_get_clean();

	$payload = array('html' => $html, 'meta' => $meta);
	$json = json_encode($payload);
	if ($json !== false) {
		$tmp = $file . '.' . getmypid() . '.tmp';
		if (@file_put_contents($tmp, $json, LOCK_EX) !== false) {
			@rename($tmp, $file);
		} else {
			@unlink($tmp);
		}
	}

	echo $html;
	return $meta;
}
