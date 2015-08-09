<?php
error_reporting(E_ERROR | E_PARSE);
$root = '/home/nwweathe/public_html/';
$t_start = microtime(get_as_float);
include($root.'basics.php');
include($fullpath.'functions.php');

//Webcam saving
$img = 'jpgwebcam.jpg';
$tstamp = date('Hi', mktime(date('H'), date('i')-1));
$wsizen1 = filesize($root.$img); usleep(500000); clearstatcache(); $wsizen2 = filesize($root.$img); $sleep = false;
if(abs($wsizen1 - $wsizen2) > 1) { sleep(4); $sleep = true; $wsizen3 = filesize($root.$img); }


//if(date('i') % 5 == 2) {
	//chmod($root.$img, 0644);
	//chmod($root.'jpggroundcam.jpg', 0644);
//}

$frac = 0.37;
$sunset = date_sunset(time(), SUNFUNCS_RET_STRING, 51.5, 0.2, 90, date('I'));
$image = imagecreatefromjpeg($root.$img);
if($image) {
	imagejpeg($image, $root.'currcam.jpg');
	if(date('H:i') == $sunset) { imagejpeg($image, $root.'sunsetcam.jpg'); }
	imagejpeg($image, $root.'currcam/'.$tstamp.'currcam.jpg', 60);
	$image_small = imagecreatetruecolor($frac*640,$frac*480);
	imagecopyresampled($image_small, $image, 0, 0, 0, 0, $frac*640, $frac*480, 640, 480);
	imagejpeg($image_small, $root.'currcam_small.jpg', 70);
	imagedestroy($image);
	imagedestroy($image_small);
}
else {
	copy($root.'currcam.jpg', $root.'currcam/'.$tstamp.'currcam.jpg');
	quick_log('cam_fail.txt', $tstamp .' Saved by primary net');
}

if($sleep) { quick_log('sleep_cam.txt', str_pad($wsizen1, 6) . ' ' . str_pad($wsizen2, 6) . ' ' . str_pad($wsizen3, 6)); }

//Check and fix missed file saves
for($i = 2; $i < 8; $i++) {
	$tstampL = date('Hi', mktime(date('H'), date('i')-$i));
	if(time() - filemtime($root.'currcam/'.$tstampL.'currcam.jpg') > 999) {
		copy($root.'currcam.jpg', $root.'currcam/'.$tstampL.'currcam.jpg');
		quick_log('cam_fail.txt', $tstampL . ' Saved by net ' . $i, $i == 7);
	}
	if($tstampL == $sunset && time() - filemtime($root.'sunsetcam.jpg') > 999) {
		copy($root.'currcam.jpg', $root.'sunsetcam.jpg');
		quick_log('cam_fail.txt', $tstampL . ' sunsetcam save from net ' . $i);
	}
}
//quick_log($fullpath.'cam_templog.txt', mktime() - filemtime($root.'currcam/'.$tstampL.'currcam.jpg'));
if(date('H:i',time()-60) == $sunset) { quick_log('cam_templog.txt', time() - filemtime($root.'sunsetcam.jpg') . ' sunsetcam age'); }

// if(date('i') % 5 == 3) {
	// chmod($root.$img, 0444);
	//chmod($root.'jpggroundcam.jpg', 0444);
// }

if(date('i') % 10 == 1) { //Groundcam saving
	$img = 'jpggroundcam.jpg';
	$wsizen1 = filesize($root.$img); usleep(500000); clearstatcache(); $wsizen2 = filesize($root.$img); $sleep = false;
	if(abs($wsizen1 - $wsizen2) > 1) { sleep(6); $sleep = true; $wsizen3 = filesize($root.$img); }
	$image = imagecreatefromjpeg($root.$img);
	imagejpeg($image, $root.'currgcam.jpg');
	imagejpeg($image, $root.'currgcam/'.$tstamp.'currgcam.jpg', 60);
	$image_small = imagecreatetruecolor($frac*640,$frac*480);
	imagecopyresampled($image_small, $image, 0, 0, 0, 0, $frac*640, $frac*480, 640, 480);
	imagejpeg($image_small, $root.'currgcam_small.jpg', 70);
	imagedestroy($image);
	imagedestroy($image_small);
	if($sleep) { quick_log('sleep_gcam.txt', str_pad($wsizen1, 6) . ' ' . str_pad($wsizen2, 6) . ' ' . str_pad($wsizen3, 6)); }
}

$daily_proctime = ( $tstamp == '2357' && !file_exists($root . date('Y/Ymd') . 'dailywebcam.jpg') ) ? '2357' : '2354';
 //Daily webcam procedure

if($tstamp == $daily_proctime) {

	for($i = 0; $i < 8; $i++) { //Copy select files to permanent date stamped versions
		copy($root.'currcam/' . zerolead($i*3) . '00currcam.jpg', $root.'img-save/skycam'.date('Y.m.d-') . zerolead($i*3) .'00.jpg');
		copy($root.'currgcam/' . zerolead($i*3) . '00currgcam.jpg', $root.'img-save/gndcam'.date('Y.m.d-') . zerolead($i*3) .'00.jpg');
	}
	for($i = 0; $i < 1440; $i++) { //Copy all files to yesterday directory
		$stamp = zerolead(floor($i / 60)) . zerolead($i % 60);
		$currcam = $root.'currcam/' . $stamp . 'currcam.jpg';
		copy($currcam, $root.'currcam/yest/' . $stamp . 'yestcam.jpg');
		if($i % 10 == 0) {
			$currgcam = $root.'currgcam/' . $stamp . 'currgcam.jpg';
			copy($currgcam, $root.'currgcam/yest/' . $stamp . 'yestgcam.jpg');
		}
	}
	webcam_summary(.22, 6, date('Y/Ymd') . 'dailywebcam.jpg');
	webcam_summary(.4, 6, date('Y/Ymd') . 'dailywebcam_large.jpg');
	//webcam_summary(.22, 6, date('Y/Ymd', true) . '/dailygwebcam.jpg');
	//webcam_summary(.4, 6, date('Y/Ymd', true) . '/dailygwebcam_large.jpg');
	quick_log( 'cronCamTimeDaily.txt', myround( microtime(get_as_float) - $t_start ) );
}


if(date('i') % 30 == 2) { //Produce dailywebcam.jpg
	$frac = 0.22; $w = 6; $dest = 'dailywebcam.jpg'; $imgno = 48; $tinc = 30; $g = '';
	$h = ceil($imgno / $w); $sw = 4; $sh = 25; $hstep = -1;
	$image_p = imagecreatetruecolor(640*$frac*$w+($w-1)*$sw, 480*$frac*$h+($h)*$sh+30);
	imagefill($image_p, 0, 0, imagecolorallocate($image_p, 223, 255, 185));
	$tstart_int = ceil((intval(substr(date('Hi'),0,2))*60+intval(substr(date('Hi'),2,2))) / $tinc) * $tinc;
	$imgno_y = round((1440 - $tstart_int) / $tinc);
	$day = 'yest'; $day0 = 'yest/';
	imagestring($image_p, 5, (640*$frac*$w+($w-1)*$sw)/2-20, 0, 'Yesterday', imagecolorallocate($image_p, 94, 11, 24));
	for($i = 0; $i < $imgno_y; $i++) {
		if(($i % $w) == 0) { $hstep += 1; }
		$j =  $tstart_int + $i * $tinc; $stamp = zerolead(floor($j / 60)) . zerolead($j % 60);
		$image = imagecreatefromjpeg($root.'curr'. $g . 'cam/'. $day0 . $stamp . $day. $g . 'cam.jpg');
		imagecopyresampled($image_p, $image, (640*$frac+$sw)*($i % $w), $hstep*(480*$frac+$sh)+15, 0, 0, 640*$frac, 480*$frac, 640, 480);
		imagestring($image_p, 4, (640*$frac+$sw)*($i % $w)+$frac*640*0.4, $hstep*(480*$frac+$sh)+480*$frac+18, $stamp, imagecolorallocate($image_p, 54, 14, 14));
		imagedestroy($image);
	}
	$day = 'curr'; $day0 = ''; $hstep_end = $hstep;
	imagestring($image_p, 5, (640*$frac*$w+($w-1)*$sw)/2-20, $hstep*(480*$frac+$sh)+480*$frac+33, 'Today', imagecolorallocate($image_p, 84, 11, 24));
	for($i = $imgno_y; $i < $imgno; $i++) {
		if(($i % $w) == 0) { $hstep += 1; }
		if($hstep_end == $hstep) { $spc = -15; } else { $spc = 0; }
		$j = ($i-$imgno_y) * $tinc; $stamp = zerolead(floor($j / 60)) . zerolead($j % 60);
		$image = imagecreatefromjpeg($root.'curr'. $g . 'cam/'. $day0 . $stamp . $day. $g . 'cam.jpg');
		imagecopyresampled($image_p, $image, (640*$frac+$sw)*($i % $w), $hstep*(480*$frac+$sh)+30+$spc, 0, 0, 640*$frac, 480*$frac, 640, 480);
		imagestring($image_p, 4, (640*$frac+$sw)*($i % $w)+$frac*640*0.4, $hstep*(480*$frac+$sh)+480*$frac+33+$spc, $stamp, imagecolorallocate($image_p, 54, 14, 14));
		imagedestroy($image);
	}
	imagejpeg($image_p, $root. $dest, 70);
	imagedestroy($image_p);

	$dest = 'todaywebcam.jpg'; $imgno = (date('H')+1)*2; $h = ceil($imgno / $w); $hstep = -1;
	$image_p = imagecreatetruecolor(640*$frac*$w+($w-1)*$sw, 480*$frac*$h+($h)*$sh+30);
	imagefill($image_p, 0, 0, imagecolorallocate($image_p, 223, 255, 185));
	$day = 'curr'; $day0 = ''; $hstep_end = $hstep;
	imagestring($image_p, 5, (640*$frac*$w+($w-1)*$sw)/2-20, $hstep*(480*$frac+$sh)+480*$frac+33, 'Today', imagecolorallocate($image_p, 84, 11, 24));
	for($i = 0; $i < $imgno; $i++) {
		if(($i % $w) == 0) { $hstep += 1; }
		if($hstep_end == $hstep) { $spc = -15; } else { $spc = 0; }
		$j = $i * $tinc; $stamp = zerolead(floor($j / 60)) . zerolead($j % 60);
		$image = imagecreatefromjpeg($root.'curr'. $g . 'cam/'. $day0 . $stamp . $day. $g . 'cam.jpg');
		imagecopyresampled($image_p, $image, (640*$frac+$sw)*($i % $w), $hstep*(480*$frac+$sh)+30+$spc, 0, 0, 640*$frac, 480*$frac, 640, 480);
		imagestring($image_p, 4, (640*$frac+$sw)*($i % $w)+$frac*640*0.4, $hstep*(480*$frac+$sh)+480*$frac+33+$spc, $stamp, imagecolorallocate($image_p, 54, 14, 14));
		imagedestroy($image);
	}
	imagejpeg($image_p, $root. $dest, 70);
	imagedestroy($image_p);
}

$tttl = microtime(get_as_float) - $t_start;
if($tttl > 5) {
	quick_log( 'cronCamTime.txt', myround($tttl) );
}

if($mailBufferCount > 0) {
	foreach($mailBuffer as $email) {
		server_mail($email['file'], $email['content']);
	}
}

function webcam_summary($frac, $w, $dest, $gcam = false, $yest = false) { //Produce webcam summary image
	global $root;
	if($gcam) { $g = 'g'; } else { $g = ''; }
	if($yest) { $day = 'yest'; $day0 = 'yest/'; } else { $day = 'curr'; $day0 = ''; }
	$imgno = 48;
	$h = ceil($imgno / $w); $sw = 4; $sh = 25; $hstep = -1;
	$image_p = imagecreatetruecolor(640*$frac*$w+($w-1)*$sw, 480*$frac*$h+($h)*$sh);
	imagefill($image_p, 0, 0, imagecolorallocate($image_p, 223, 255, 185));
	for($i = 0; $i < $imgno; $i++) {
		if(($i % $w) == 0) { $hstep += 1; }
		$stamp = zerolead(floor($i/2)); if($i % 2 == 0) { $stamp .= '00'; } else { $stamp .= '30'; }
		$image = imagecreatefromjpeg($root.'curr'. $g . 'cam/'. $day0 . $stamp . $day. $g . 'cam.jpg');
		imagecopyresampled($image_p, $image, (640*$frac+$sw)*($i % $w), $hstep*(480*$frac+$sh), 0, 0, 640*$frac, 480*$frac, 640, 480);
		imagestring($image_p, 4, (640*$frac+$sw)*($i % $w)+$frac*640*0.4, $hstep*(480*$frac+$sh)+480*$frac+3, $stamp, imagecolorallocate($image_p, 54, 14, 14));
		imagedestroy($image);
	}
	imagejpeg($image_p, $root. $dest, 60);
	imagedestroy($image_p);
}
?>