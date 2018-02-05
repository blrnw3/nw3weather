<?php
error_reporting(E_ERROR | E_PARSE);
$root = '/var/www/html/';
$t_start = microtime(get_as_float);
include($root.'basics.php');
include($fullpath.'functions.php');

echo "START: ". date('r'). "\n";

//Webcam saving
$img = 'jpgwebcam.jpg';
$tstamp = date('Hi', mktime(date('H'), date('i')-1));
$wsizen1 = filesize($root.$img);
usleep(500000);
clearstatcache();
$wsizen2 = filesize($root.$img);
clearstatcache();
$sleep = false;

if(abs($wsizen1 - $wsizen2) > 1) {
	sleep(5);
	$sleep = true;
	$wsizen3 = filesize($root.$img);
}

$frac = 0.37;
$sunset = date_sunset(time(), SUNFUNCS_RET_STRING, 51.5, 0.2, 90, date('I'));
$image = imagecreatefromjpeg($root.$img);
if($image) {
	imagejpeg($image, $root.'currcam.jpg');
	if(date('H:i') == $sunset) { imagejpeg($image, $root.'sunsetcam.jpg'); }
	$image_small = imagecreatetruecolor($frac*640,$frac*480);
	imagecopyresampled($image_small, $image, 0, 0, 0, 0, $frac*640, $frac*480, 640, 480);
	imagejpeg($image_small, $root.'currcam_small.jpg', 70);
	imagedestroy($image);
	imagedestroy($image_small);
} else {
	quick_log('cam_fail.txt', $tstamp .' No live cam');
}

if($sleep) { quick_log('sleep_cam.txt', str_pad($wsizen1, 6) . ' ' . str_pad($wsizen2, 6) . ' ' . str_pad($wsizen3, 6)); }

if(date('H:i',time()-60) == $sunset) {
	quick_log('cam_templog.txt', time() - filemtime($root.'sunsetcam.jpg') . ' sunsetcam age');
}

if(date('i') % 2 == 1) { //Groundcam saving
	$img = 'jpggroundcam.jpg';
	$wsizen1 = filesize($root.$img);
	usleep(500000);
	clearstatcache();
	$wsizen2 = filesize($root.$img);
	clearstatcache();
	$sleep = false;
	if(abs($wsizen1 - $wsizen2) > 1) { sleep(6); $sleep = true; $wsizen3 = filesize($root.$img); }
	$image = imagecreatefromjpeg($root.$img);
	imagejpeg($image, $root.'currgcam.jpg');
	$image_small = imagecreatetruecolor($frac*640,$frac*480);
	imagecopyresampled($image_small, $image, 0, 0, 0, 0, $frac*640, $frac*480, 640, 480);
	imagejpeg($image_small, $root.'currgcam_small.jpg', 70);
	imagedestroy($image);
	imagedestroy($image_small);
	if($sleep) { quick_log('sleep_gcam.txt', str_pad($wsizen1, 6) . ' ' . str_pad($wsizen2, 6) . ' ' . str_pad($wsizen3, 6)); }
}

// Rolling long-term cam save
$sky_freq = 5;
$gnd_freq = 30;
$dstamp = date('Y/m/d');
$stamp = date('Y/m/d/Hi');
mkdir($root . 'camchive/sky/'. $dstamp, 0775, true);
mkdir($root . 'camchive/gnd/'. $dstamp, 0775, true);
copy($root.'currcam.jpg', $root .'camchive/sky/'. $stamp .'sky.jpg');
copy($root.'currgcam.jpg', $root .'camchive/gnd/'. $stamp .'gnd.jpg');

$daily_proctime = ( $tstamp == '2357' && !file_exists($root . date('Y/Ymd') . 'dailywebcam.jpg') ) ? '2357' : '2354';
 //Daily webcam procedure

if($tstamp == $daily_proctime) {
	// High-freq cam-save cleanup
	$highfreq_keep_days = 10;
	$minfreq_to_keep = 5;
	$stamp = date("Y/m/d", mkdate($dmonth, $dday-$highfreq_keep_days, $dyear));
	foreach(["sky", "gnd"] as $cam_type) {
		$camdir = "$root/camchive/$cam_type/$stamp/";
		for($i = 0; $i < 1440; $i++) {
			$f = $camdir . date("Hi", mktime(0, $i, 0)) . "$cam_type.jpg";
			if($i % $minfreq_to_keep !== 0 && file_exists($f)) {
				echo "$f <br />";
				unlink($f);
			}
		}
	}

	webcam_summary(.22, 6, date('Y/Ymd') . 'dailywebcam.jpg');
	webcam_summary(.4, 6, date('Y/Ymd') . 'dailywebcam_large.jpg');
	//webcam_summary(.22, 6, date('Y/Ymd', true) . '/dailygwebcam.jpg');
	//webcam_summary(.4, 6, date('Y/Ymd', true) . '/dailygwebcam_large.jpg');
	quick_log( 'cronCamTimeDaily.txt', myround( microtime(get_as_float) - $t_start ) );
}

if(date('i') == '02') {
	// Videos
	$FRAME_RATE = 24;
	$CRF = 25;

	$offset = date('H') == '00' ? 1 : 0;
	$indate = date('Y/m/d',mkdate(date('n'),date('j')-$offset, date('Y')));
	$outdate = date('Ymd',mkdate(date('n'),date('j')-$offset, date('Y')));
	$inglob = "/var/www/html/camchive/sky/$indate/*.jpg";
	$outfile = "/var/www/html/camchive/timelapse/skycam_$outdate.mp4";
	$today = "/var/www/html/camchive/timelapse/skycam_today.mp4";
	$yest = "/var/www/html/camchive/timelapse/skycam_yest.mp4";
	$cmd = "/usr/bin/ffmpeg -r $FRAME_RATE -pattern_type glob -y -i \"$inglob\" -crf $CRF $outfile";

	if(date('H') == '01') {
		copy($today, $yest);
	}

	$ffmpeg_res = shell_exec($cmd);
	copy($outfile, $today);

	quick_log( 'timelapse.txt', myround( microtime(get_as_float) - $t_start ) ." DAILY ". $cmd);
}

if($tstamp == '0107') {
	// Monthly timelapse
	$freq = 10;
	$twiset = 90;
	$cam = "sky";
	$rate = 18;
	$qual = 24;
	$mon_yest_zero = zerolead($mon_yest);
	$name = "skycam_monthly_${yr_yest}_${mon_yest_zero}";
	$filf = extract_for_timelapse($yr_yest, $mon_yest, 0, $freq, $twiset, $cam, $rate, $qual, $name);
	quick_log( 'timelapse.txt', myround( microtime(get_as_float) - $t_start ) ." MONTHLY ". $filf);
}

if($tstamp == '0112') {
	// Yearly timelapse
	$freq = 120;
	$twiset = -30;
	$cam = "sky";
	$rate = 15;
	$qual = 24;
	$name = "skycam_yearly_${yr_yest}";
	$filf = extract_for_timelapse($yr_yest, 0, 0, $freq, $twiset, $cam, $rate, $qual, $name);
	quick_log( 'timelapse.txt', myround( microtime(get_as_float) - $t_start ) ." YEARLY ". $filf);
}

if($tstamp == '0117') {
	// Yearly midday timelapse
	$freq = 720;
	$twiset = 0;
	$cam = "sky";
	$rate = 6;
	$qual = 24;
	$name = "skycam_yearly_midday_${yr_yest}";
	$filf = extract_for_timelapse($yr_yest, 0, 0, $freq, $twiset, $cam, $rate, $qual, $name);
	quick_log( 'timelapse.txt', myround( microtime(get_as_float) - $t_start ) ." YEARLY_MIDDAY ". $filf);
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
		$image = imagecreatefromjpeg(cam_location($g, $day0, $stamp));
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
		$image = imagecreatefromjpeg(cam_location($g, $day0, $stamp));
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
		$image = imagecreatefromjpeg(cam_location($g, $day0, $stamp));
		imagecopyresampled($image_p, $image, (640*$frac+$sw)*($i % $w), $hstep*(480*$frac+$sh)+30+$spc, 0, 0, 640*$frac, 480*$frac, 640, 480);
		imagestring($image_p, 4, (640*$frac+$sw)*($i % $w)+$frac*640*0.4, $hstep*(480*$frac+$sh)+480*$frac+33+$spc, $stamp, imagecolorallocate($image_p, 54, 14, 14));
		imagedestroy($image);
	}
	imagejpeg($image_p, $root. $dest, 70);
	imagedestroy($image_p);
}

$tttl = microtime(get_as_float) - $t_start;
if($tttl > 10) {
	quick_log( 'cronCamTime.txt', myround($tttl) );
}

if($mailBufferCount > 0) {
	foreach($mailBuffer as $email) {
		server_mail($email['file'], $email['content']);
	}
}

echo "END: ". date('r'). "\n";

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
		$image = imagecreatefromjpeg(cam_location($g, $day0, $stamp));
		imagecopyresampled($image_p, $image, (640*$frac+$sw)*($i % $w), $hstep*(480*$frac+$sh), 0, 0, 640*$frac, 480*$frac, 640, 480);
		imagestring($image_p, 4, (640*$frac+$sw)*($i % $w)+$frac*640*0.4, $hstep*(480*$frac+$sh)+480*$frac+3, $stamp, imagecolorallocate($image_p, 54, 14, 14));
		imagedestroy($image);
	}
	imagejpeg($image_p, $root. $dest, 60);
	imagedestroy($image_p);
}

function cam_location($g, $day0, $stamp) {
	global $root, $dmonth, $dday;
	$cam_type = ($g == '') ? "sky" : "gnd";
	$offset = ($day0 == "") ? 0 : 1;
	$ymd = date("Y/m/d", mktime(12, 0, 0, $dmonth, $dday - $offset));
	return "$root/camchive/$cam_type/$ymd/$stamp$cam_type.jpg";
}
?>
