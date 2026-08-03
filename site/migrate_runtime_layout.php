<?php
/**
 * Move rebuildable root outputs into versioned runtime directories.
 * CLI-only; dry-run unless --go or --rollback is passed.
 */
if(PHP_SAPI !== 'cli') {
	http_response_code(403);
	die("CLI only.\n");
}
$root = getenv('NW3_ROOT');
if(!$root) {
	$root = '/var/www/html/';
}
$root = rtrim($root, '/') . '/';
if(!defined('ROOT')) {
	define('ROOT', $root);
}
require_once(ROOT . 'config/paths.php');

$apply = in_array('--go', $argv, true);
$rollback = in_array('--rollback', $argv, true);
if($apply && $rollback) {
	fwrite(STDERR, "Choose either --go or --rollback.\n");
	exit(2);
}

$moves = array();
foreach(glob(ROOT . 'serialised_*.txt') as $path) {
	$moves[$path] = V5_CACHE_ROOT . basename($path);
}
foreach(array('forecast_v5.json', 'pm25_latest.txt', 'windy_widget.txt') as $name) {
	$moves[ROOT . $name] = V5_CACHE_ROOT . $name;
}
foreach(array(
	'RainTags.php', 'TemperatureTags.php', 'HumidityTags.php', 'PressureTags.php',
	'FeelTags.php', 'WindTags.php', 'Rain2Tags.php', 'SunTags.php',
) as $name) {
	$moves[ROOT . $name] = LEGACY_CACHE_ROOT . $name;
}
foreach(array('skycam.jpg', 'skycam_home.jpg', 'skycam_wx2.jpg', 'skycam_wx2_sunset.jpg') as $name) {
	$moves[ROOT . $name] = V5_GENERATED_ROOT . $name;
}
foreach(array(
	'skycam_small.jpg', 'skycam_sunset.jpg', 'skycam_small_small.jpg',
	'stitchedmaingraph.png', 'stitchedmaingraph_small.png',
	'mainGraph1.png', 'mainGraph2.png', 'mainGraph3.png', 'mainGraph4.png',
	'rose_24hrs.png', 'rose_month.png', 'rose_year.png', 'rose_all.png',
	'dailywebcam.jpg', 'todaywebcam.jpg',
	'1.png', '2.png', '3.png', '1s.png', '2s.png', '3s.png',
) as $name) {
	$moves[ROOT . $name] = LEGACY_GENERATED_ROOT . $name;
}
if($rollback) {
	$moves = array_flip($moves);
}

$verb = ($apply || $rollback) ? 'MOVE' : 'WOULD MOVE';
$changed = 0;
foreach($moves as $source => $target) {
	if(!file_exists($source)) {
		continue;
	}
	if(file_exists($target)) {
		fwrite(STDERR, "Refusing to overwrite existing target: $target\n");
		exit(1);
	}
	echo "$verb $source -> $target\n";
	if($apply || $rollback) {
		$dir = dirname($target);
		if(!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
			fwrite(STDERR, "Unable to create $dir\n");
			exit(1);
		}
		if(!rename($source, $target)) {
			fwrite(STDERR, "Unable to move $source\n");
			exit(1);
		}
	}
	$changed++;
}

if(!$rollback && ($apply || $changed)) {
	foreach(glob(V5_CACHE_ROOT . 'serialised_*.txt') as $source) {
		$target = LEGACY_CACHE_ROOT . basename($source);
		if(file_exists($target)) {
			continue;
		}
		echo ($apply ? 'COPY' : 'WOULD COPY') . " $source -> $target\n";
		if($apply && !copy($source, $target)) {
			fwrite(STDERR, "Unable to seed $target\n");
			exit(1);
		}
	}
}
echo ($apply || $rollback ? 'Processed' : 'Dry run:') . " $changed move(s).\n";
