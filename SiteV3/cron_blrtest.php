<?php
error_reporting(E_ERROR | E_PARSE);
$root = '/var/www/html/';
include($root.'basics.php');
include($fullpath.'functions.php');

echo "START: ". date('r'). "\n";

//Webcam saving
$img_name = 'skycam_raw.jpg';
$w = 3072;
$h = 2048;
$wt = 1980;
$ht = 1320;
$wtm = 864;
$htm = 576;
$wts = 315;
$hts = 210;

$image_raw = imagecreatefromjpeg($root.$img_name);
if($image_raw) {
	$gst = round($gust);
	$datestr = date("H:i:s Y-m-d", mktime());

	$str = "$datestr         ${temp}C ${humi}% ${gst} mph ${rain} mm";
	$copystr = "@nw3weather";

	// HD for saving
	$image_hd = imagecreatetruecolor($wt, $ht);
	$col = imagecolorallocate($image_hd, 250, 250, 250);
	imagecopyresampled($image_hd, $image_raw, 0, 0, 0, 0, $wt, $ht, $w, $h);
	imagettftext($image_hd, 20, 0, 15, $ht - 30, $col, $root."jpgraph/src/fonts/DejaVuSans.ttf", $str);
	imagettftext($image_hd, 20, 0, $wt - 200, $ht - 30, $col, $root."jpgraph/src/fonts/DejaVuSans.ttf", $copystr);
	imagejpeg($image_hd, $root.'skycam_blr.jpg', 75);

	imagedestroy($image_raw);
	imagedestroy($image_hd);
} else {
	quick_log('hik_fail.txt', $tstamp .' No live cam');
}

$phpload = myround(microtime(get_as_float) - $scriptbeg, 3);
echo "END: ". date('r'). "\n";
echo "Runtime: $phpload s";

?>
