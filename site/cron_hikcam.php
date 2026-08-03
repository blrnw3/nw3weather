<?php
if(PHP_SAPI !== 'cli') {
	http_response_code(403);
	die("CLI only.\n");
}
error_reporting(E_ERROR | E_PARSE);
$root = '/var/www/html/';
include($root.'basics.php');
include($fullpath.'functions.php');
nw3_ensure_runtime_dirs();

echo "START: ". date('r'). "\n";

//Webcam saving
$FONT = $root."jpgraph/src/fonts/DejaVuSans.ttf";
$img_name = 'skycam_raw.jpg';
$w = 3072;
$h = 2048;
$wt = 1980;
$ht = 1320;
$wh = 700;   // v5 home column
$hh = 467;
$ww = 1400;  // v5 wx2 near-full-width
$hw = 933;

$image_raw = imagecreatefromjpeg($root.$img_name);
if($image_raw) {
	$gst = round($gust);
	$name = file_get_contents($root."skycam_name.txt");
	$dateraw = substr($name, 15, 14);
	$datetime = mktime(
		(int)substr($dateraw, 8, 2), (int)substr($dateraw, 10, 2), (int)substr($dateraw, 12, 2),
		(int)substr($dateraw, 4, 2), (int)substr($dateraw, 6, 2), (int)substr($dateraw, 0, 4)
	);
	$datestr = date("H:i:s Y-m-d", $datetime);

	$str = "$datestr         ${temp}C ${humi}% ${gst} mph ${rain} mm";
	$copystr = "@nw3weather";

	// HD for saving
	$image_hd = imagecreatetruecolor($wt, $ht);
	$col3 = imagecolorallocate($image_hd, 250, 250, 250);
	imagecopyresampled($image_hd, $image_raw, 0, 0, 0, 0, $wt, $ht, $w, $h);
	imagettftext($image_hd, 20, 0, 15, $ht - 30, $col3, $FONT, $str);
	imagettftext($image_hd, 20, 0, $wt - 210, $ht - 30, $col3, $FONT, $copystr);
	safe_cam_save($image_hd, V5_GENERATED_ROOT.'skycam.jpg', 65);

	// v5 home column
	$image_home = imagecreatetruecolor($wh, $hh);
	$col_home = imagecolorallocate($image_home, 250, 250, 250);
	imagecopyresampled($image_home, $image_raw, 0, 0, 0, 0, $wh, $hh, $w, $h);
	imagestring($image_home, 4, 10, $hh - 20, $str, $col_home);
	imagestring($image_home, 4, $wh - 90, $hh - 20, $copystr, $col_home);
	safe_cam_save($image_home, V5_GENERATED_ROOT.'skycam_home.jpg', 70);

	// v5 wx2 near-full-width
	$image_wx2 = imagecreatetruecolor($ww, $hw);
	$col_wx2 = imagecolorallocate($image_wx2, 250, 250, 250);
	imagecopyresampled($image_wx2, $image_raw, 0, 0, 0, 0, $ww, $hw, $w, $h);
	imagettftext($image_wx2, 16, 0, 15, $hw - 22, $col_wx2, $FONT, $str);
	imagettftext($image_wx2, 16, 0, $ww - 170, $hw - 22, $col_wx2, $FONT, $copystr);
	safe_cam_save($image_wx2, V5_GENERATED_ROOT.'skycam_wx2.jpg', 70);
	if(date('H:i') == $sunset) {
		copy(V5_GENERATED_ROOT.'skycam_wx2.jpg', V5_GENERATED_ROOT.'skycam_wx2_sunset.jpg');
	}

	imagedestroy($image_raw);
	imagedestroy($image_hd);
	imagedestroy($image_home);
	imagedestroy($image_wx2);
} else {
	quick_log('hik_fail.txt', $tstamp .' No live cam');
}

$phpload = myround(microtime(get_as_float) - $scriptbeg, 3);
echo "END: ". date('r'). "\n";
echo "Runtime: $phpload s";

function safe_cam_save($img, $dst, $q) {
	$tmp = $dst .'.temp';
	imagejpeg($img, $tmp, $q);
	rename($tmp, $dst);
}

?>
