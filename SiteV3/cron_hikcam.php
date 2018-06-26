<?php
error_reporting(E_ERROR | E_PARSE);
$root = '/var/www/html/';
include($root.'basics.php');
include($fullpath.'functions.php');

echo "START: ". date('r'). "\n";

//Webcam saving
$FONT = $root."jpgraph/src/fonts/DejaVuSans.ttf";
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
	$name = file_get_contents($root."skycam_name.txt");
	$dateraw = substr($name, 15, 14);
	$datetime = mktime(
		(int)substr($dateraw, 8, 2), (int)substr($dateraw, 10, 2), (int)substr($dateraw, 12, 2),
		(int)substr($dateraw, 4, 2), (int)substr($dateraw, 6, 2), (int)substr($dateraw, 0, 4)
	);
	$datestr = date("H:i:s Y-m-d", $datetime);

	$str = "$datestr         ${temp}C ${humi}% ${gst} mph ${rain} mm";
	$copystr = "@nw3weather";

	$image_small = imagecreatetruecolor($wtm, $htm);
	$col = imagecolorallocate($image_small, 250, 250, 250);
	imagecopyresampled($image_small, $image_raw, 0, 0, 0, 0, $wtm, $htm, $w, $h);
	imagestring($image_small, 4, 10, $htm - 20, $str, $col);
	imagestring($image_small, 4, $wtm - 90, $htm - 20, $copystr, $col);
	imagejpeg($image_small, $root.'skycam_small.jpg', 65);
	if(date('H:i') == $sunset) { imagejpeg($image_small, $root.'skycam_sunset.jpg'); }

	// HD for saving
	$image_hd = imagecreatetruecolor($wt, $ht);
	$col3 = imagecolorallocate($image_hd, 250, 250, 250);
	imagecopyresampled($image_hd, $image_raw, 0, 0, 0, 0, $wt, $ht, $w, $h);
	imagettftext($image_hd, 20, 0, 15, $ht - 30, $col3, $FONT, $str);
	imagettftext($image_hd, 20, 0, $wt - 210, $ht - 30, $col3, $FONT, $copystr);
	imagejpeg($image_hd, $root.'skycam.jpg', 65);

	// Really small
	$image_vsmall = imagecreatetruecolor($wts, $hts);
	$col2 = imagecolorallocate($image_vsmall, 250, 250, 250);
	imagecopyresampled($image_vsmall, $image_raw, 0, 0, 0, 0, $wts, $hts, $w, $h);
	imagestring($image_vsmall, 1, 4, $hts - 10, $datestr, $col2);   // Full $str doesn't fit
	imagestring($image_vsmall, 1, $wts - 60, $hts - 10, $copystr, $col2);
	imagejpeg($image_vsmall, $root.'skycam_small_small.jpg', 60);

	imagedestroy($image_raw);
	imagedestroy($image_hd);
	imagedestroy($image_small);
	imagedestroy($image_vsmall);
} else {
	quick_log('hik_fail.txt', $tstamp .' No live cam');
}

$phpload = myround(microtime(get_as_float) - $scriptbeg, 3);
echo "END: ". date('r'). "\n";
echo "Runtime: $phpload s";

?>
