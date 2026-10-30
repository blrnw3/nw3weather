<?php
/**
 * Runtime output paths.
 *
 * Source code, FTP uploads and canonical weather data intentionally remain in
 * ROOT. Only rebuildable caches and generated presentation assets live below
 * these directories.
 */
if(!defined('ROOT')) {
	define('ROOT', '/var/www/html/');
}

if(!defined('V5_CACHE_ROOT')) {
	define('V5_CACHE_ROOT', ROOT . 'cache/v5/');
}
if(!defined('LEGACY_CACHE_ROOT')) {
	define('LEGACY_CACHE_ROOT', ROOT . 'cache/legacy/');
}
if(!defined('V5_GENERATED_ROOT')) {
	define('V5_GENERATED_ROOT', ROOT . 'generated/v5/');
}
if(!defined('LEGACY_GENERATED_ROOT')) {
	define('LEGACY_GENERATED_ROOT', ROOT . 'generated/legacy/');
}

// Current code and shared data helpers use the v5 cache unless a legacy
// entrypoint explicitly uses LEGACY_CACHE_ROOT.
if(!defined('CACHE_ROOT')) {
	define('CACHE_ROOT', V5_CACHE_ROOT);
}

function nw3_runtime_dirs() {
	return array(V5_CACHE_ROOT, LEGACY_CACHE_ROOT, V5_GENERATED_ROOT, LEGACY_GENERATED_ROOT);
}

function nw3_ensure_runtime_dirs() {
	foreach(nw3_runtime_dirs() as $dir) {
		if(!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
			throw new RuntimeException('Unable to create runtime directory: ' . $dir);
		}
		// Cron runs as apache while deployment/migration runs as ben. Keep the
		// shared group writable and inherited by newly created files.
		@chmod($dir, 02775);
	}
}

function nw3_atomic_write($path, $contents) {
	$dir = dirname($path);
	if(!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
		return false;
	}
	@chmod($dir, 02775);
	$tmp = $path . '.tmp.' . getmypid();
	if(file_put_contents($tmp, $contents) === false) {
		return false;
	}
	if(!rename($tmp, $path)) {
		@unlink($tmp);
		return false;
	}
	// Cron runs as apache; deploys/manual runs as ben. Keep the file group-writable
	// so either can replace it without a permission-denied on the next write.
	@chmod($path, 0664);
	return strlen($contents);
}

// Webcam sunshine (sun_model). Per-minute logfiles + latest.json written by scripts/run_sun_classify.sh.
if(!defined('NW3_SUNLOG_DIR')) {
	define('NW3_SUNLOG_DIR', ROOT . 'sunlog/');
}
// On: the webcam model writes yesterday's datm sunhr (cron_main 00:40, retried at the 06:03 check; only appends a
// missing row, so existing / hand-edited rows are never changed). sun_post_saver.php then only records the old
// classifier's value in NW3_SUN_COMPARE_CSV. Set false to return datm sunhr to the old classifier's nightly post.
if(!defined('NW3_SUN_AUTO_DATM')) {
	define('NW3_SUN_AUTO_DATM', true);
}
if(!defined('NW3_SUN_COMPARE_CSV')) {
	define('NW3_SUN_COMPARE_CSV', ROOT . 'sun/sun_old_posts.csv');
}
