<?php
/**
 * Legacy-only derived outputs. Schedule once per minute, after cron_main.php.
 */
if(PHP_SAPI !== 'cli') {
	http_response_code(403);
	die("CLI only.\n");
}

const smallGraphWidth1 = 533;
const smallGraphWidth2 = 500;
const smallGraphWidth3 = 505;

$started = microtime(true);
require_once('/var/www/html/basics.php');
require_once(ROOT . 'functions.php');
nw3_ensure_runtime_dirs();

$lock = fopen(ROOT . 'Logs/cron_legacy.lock', 'c');
if(!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
	echo "cron_legacy already running\n";
	exit(0);
}

$fiveMinutely = date('i') % 5 == 0;
$tstamp = date('Hi');

// Maintain a distinct v3 serialization snapshot.
$cacheFiles = glob(V5_CACHE_ROOT . 'serialised_*.txt');
if($cacheFiles !== false) {
	foreach($cacheFiles as $source) {
		$target = LEGACY_CACHE_ROOT . basename($source);
		if(!file_exists($target) || filemtime($source) > filemtime($target)
				|| filesize($source) !== filesize($target)) {
			nw3_atomic_write($target, file_get_contents($source));
		}
	}
}

legacy_camera_outputs();
if(date('i') % 30 == 2) {
	legacy_webcam_composite(48, LEGACY_GENERATED_ROOT . 'dailywebcam.jpg');
	legacy_webcam_composite((date('H') + 1) * 2, LEGACY_GENERATED_ROOT . 'todaywebcam.jpg');
}

if($fiveMinutely) {
	$graphs = array(
		array('graphday.php', '1.png', ''),
		array('graphday2.php', '2.png', ''),
		array('graphdayA.php', '3.png', ' wdir'),
		array('graphday.php', '1s.png', ' s 260 ' . smallGraphWidth1),
		array('graphday2.php', '2s.png', ' s 260 ' . smallGraphWidth2),
		array('graphdayA.php', '3s.png', ' wdir s ' . smallGraphWidth3),
	);
	foreach($graphs as $graph) {
		exec(EXEC_PATH . $graph[0] . ' ' . escapeshellarg(LEGACY_GENERATED_ROOT . $graph[1]) . $graph[2]);
	}
	legacy_graph_stitch();

	if(date('i') != 0) {
		$vars = array('temp', 'rain', 'hum', 'dew', 'wind', 'baro', 'wdir', 'gust');
		$margs = array(7, 12, 23, 21);
		for($i = 1; $i <= 4; $i++) {
			$arg1 = $vars[$i * 2 - 2];
			$arg2 = $vars[$i * 2 - 1];
			$arg3 = (int)($i % 2 === 1);
			$arg4 = $margs[$i - 1];
			exec(EXEC_PATH . 'graphdayA.php ' . escapeshellarg(LEGACY_GENERATED_ROOT . "mainGraph$i.png")
				. " $arg1 $arg2 $arg3 $arg4 miniMain");
		}
	}
	foreach(array('24hrs', 'month', 'year') as $roseType) {
		exec(EXEC_PATH . 'windrose.php ' . $roseType . ' '
			. escapeshellarg(LEGACY_GENERATED_ROOT . "rose_$roseType.png"));
	}

	exec(EXEC_PATH . 'cron_tags.php blr ftw > ' . escapeshellarg(ROOT . 'Logs/cronsuntaglog.txt'), $tagOut, $tagRc);
	if($tagRc !== 0) {
		quick_log('cron_legacy_tags_bad.txt', 'rc=' . $tagRc);
		flock($lock, LOCK_UN);
		fclose($lock);
		exit($tagRc);
	}
}

if($tstamp == '1656') {
	exec(EXEC_PATH . 'windrose.php now ' . escapeshellarg(LEGACY_GENERATED_ROOT . 'rose_all.png'));
}
if($tstamp == '0000') {
	$targetStart = ROOT . date('Y', time() - 60) . '/stitchedmaingraph_';
	$targetEnd = date('Ymd', time() - 60) . '.png';
	@copy(LEGACY_GENERATED_ROOT . 'stitchedmaingraph.png', $targetStart . $targetEnd);
	@copy(LEGACY_GENERATED_ROOT . 'stitchedmaingraph_small.png', $targetStart . 'small_' . $targetEnd);
}

nw3_atomic_write(
	ROOT . 'Logs/cron_legacy_success.txt',
	date('c') . "\t" . round(microtime(true) - $started, 3) . "s\n"
);
flock($lock, LOCK_UN);
fclose($lock);

function legacy_graph_stitch() {
	$im1 = @imagecreatefrompng(LEGACY_GENERATED_ROOT . '1.png');
	$im2 = @imagecreatefrompng(LEGACY_GENERATED_ROOT . '2.png');
	$im3 = @imagecreatefrompng(LEGACY_GENERATED_ROOT . '3.png');
	if($im1 && $im2 && $im3) {
		$im = imagecreatetruecolor(850, 1017);
		imagecopyresampled($im, $im1, 0, 0, 0, 0, 850, 407, 850, 407);
		imagecopyresampled($im, $im2, 0, 407, 0, 17, 850, 390, 850, 390);
		imagecopyresampled($im, $im3, 0, 797, 0, 0, 850, 220, 850, 220);
		imagepng($im, LEGACY_GENERATED_ROOT . 'stitchedmaingraph.png', 9);
		imagedestroy($im);
	}
	foreach(array($im1, $im2, $im3) as $source) {
		if($source) imagedestroy($source);
	}

	$im1 = @imagecreatefrompng(LEGACY_GENERATED_ROOT . '1s.png');
	$im2 = @imagecreatefrompng(LEGACY_GENERATED_ROOT . '2s.png');
	$im3 = @imagecreatefrompng(LEGACY_GENERATED_ROOT . '3s.png');
	if($im1 && $im2 && $im3) {
		$im = imagecreatetruecolor(542, 619);
		imagefill($im, 0, 0, imagecolorallocate($im, 255, 255, 255));
		imagecopyresampled($im, $im1, 9, 0, 0, 0, 533, 245, 533, 245);
		imagecopyresampled($im, $im2, 0, 245, 0, 17, 500, 225, 500, 225);
		imagecopyresampled($im, $im3, 0, 470, 0, 0, 505, 149, 505, 149);
		imagepng($im, LEGACY_GENERATED_ROOT . 'stitchedmaingraph_small.png', 9);
		imagedestroy($im);
	}
	foreach(array($im1, $im2, $im3) as $source) {
		if($source) imagedestroy($source);
	}
}

function legacy_camera_outputs() {
	global $sunset;
	$source = V5_GENERATED_ROOT . 'skycam.jpg';
	if(!file_exists($source)) return;
	$image = @imagecreatefromjpeg($source);
	if(!$image) return;

	foreach(array(
		'skycam_small.jpg' => array(864, 576, 65),
		'skycam_small_small.jpg' => array(315, 210, 60),
	) as $name => $size) {
		$output = imagecreatetruecolor($size[0], $size[1]);
		imagecopyresampled($output, $image, 0, 0, 0, 0, $size[0], $size[1], imagesx($image), imagesy($image));
		$tmp = LEGACY_GENERATED_ROOT . $name . '.tmp';
		imagejpeg($output, $tmp, $size[2]);
		rename($tmp, LEGACY_GENERATED_ROOT . $name);
		imagedestroy($output);
	}
	if(date('H:i') == $sunset) {
		copy(LEGACY_GENERATED_ROOT . 'skycam_small.jpg', LEGACY_GENERATED_ROOT . 'skycam_sunset.jpg');
	}
	imagedestroy($image);
}

function legacy_webcam_composite($count, $destination) {
	$columns = 6;
	$thumbW = 144;
	$thumbH = 96;
	$labelH = 16;
	$rows = (int)ceil($count / $columns);
	$output = imagecreatetruecolor($columns * $thumbW, $rows * ($thumbH + $labelH));
	imagefill($output, 0, 0, imagecolorallocate($output, 223, 255, 185));
	$text = imagecolorallocate($output, 54, 14, 14);
	$base = floor(time() / 1800) * 1800;
	for($i = 0; $i < $count; $i++) {
		$ts = $base - ($count - 1 - $i) * 1800;
		$source = CAM_ROOT . 'camchive/hik/' . date('Y/m/d/Hi', $ts) . 'hik.jpg';
		if(!file_exists($source)) continue;
		$image = @imagecreatefromjpeg($source);
		if(!$image) continue;
		$x = ($i % $columns) * $thumbW;
		$y = (int)floor($i / $columns) * ($thumbH + $labelH);
		imagecopyresampled($output, $image, $x, $y, 0, 0, $thumbW, $thumbH, imagesx($image), imagesy($image));
		imagestring($output, 2, $x + 50, $y + $thumbH + 2, date('Hi', $ts), $text);
		imagedestroy($image);
	}
	$tmp = $destination . '.tmp';
	imagejpeg($output, $tmp, 70);
	rename($tmp, $destination);
	imagedestroy($output);
}
