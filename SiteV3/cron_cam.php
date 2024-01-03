<?php
# NB: see also: cron_hikcam

error_reporting(E_ERROR | E_PARSE);
$root = '/var/www/html/';
$t_start = microtime(get_as_float);
include($root.'basics.php');
include($fullpath.'functions.php');

echo "START: ". date('r'). "\n";

// Rolling long-term cam save
$tstamp = date('Hi', mktime(date('H'), date('i')-1));
$dstamp = date('Y/m/d');
$stamp = date('Y/m/d/Hi');
$src = "skycam.jpg";
mkdir(CAM_ROOT . 'camchive/hik/'. $dstamp, 0775, true);
copy($root.$src, CAM_ROOT .'camchive/hik/'. $stamp .'hik.jpg');

$daily_proctime = ( $tstamp == '2357' && !file_exists($root . date('Y/Ymd') . 'dailywebcam.jpg') ) ? '2357' : '2354';
 //Daily webcam procedure

$frac = 0.078;
$wo = 1850; $ho = 1233; // slighlty reduced from original to truncate extraneous parts of the image

if($tstamp == $daily_proctime) {
	// High-freq cam-save cleanup
	$conf = ["hik" => [5, 3]];
	foreach($conf as $cam_type => $settings) {
		$keep_freq = $settings[0];
		$keep_days = $settings[1];
		$stamp = date("Y/m/d", mkdate($dmonth, $dday-$keep_days, $dyear));
		$camdir = CAM_ROOT . "camchive/$cam_type/$stamp/";
		for($i = 0; $i < 1440; $i++) {
			$f = $camdir . date("Hi", mktime(0, $i, 0)) . "$cam_type.jpg";
			if($i % $keep_freq !== 0 && file_exists($f)) {
				unlink($f);
			}
		}
	}

	webcam_summary($frac, 6, date('Y/Ymd') . 'dailywebcam.jpg', $cam_type = "hik", $offset = 0, $wo, $ho);
	//webcam_summary(.22, 6, date('Y/Ymd', true) . '/dailygwebcam.jpg');

	$FREE_THRESH = 256 * 1024 * 1024;
	$cam_free = disk_free_space("/mnt/webcam");
	$vid_free = disk_free_space("/mnt/nw3-vol1");
	if($cam_free < $FREE_THRESH  || $vid_free < $FREE_THRESH) {
		mail("alerts@nw3weather.co.uk","Cam disk filling up", "Cam free: $cam_free. Vid free: $vid_free");
	}

	quick_log( 'cronCamTimeDaily.txt', myround( microtime(get_as_float) - $t_start ) );
}

if(date('i') == '39') {
	// Videos
	$FRAME_RATE = 24;
	$SCALE = "1080*720";
	$CRF = 25;

	$offset = date('H') == '00' ? 1 : 0;
	$indate = date('Y/m/d',mkdate(date('n'),date('j')-$offset, date('Y')));
	$outdate = date('Ymd',mkdate(date('n'),date('j')-$offset, date('Y')));
	$inglob = CAM_ROOT."camchive/hik/$indate/*.jpg";
	$outfile = VID_ROOT."timelapse/skycam_$outdate.mp4";
	$today = VID_ROOT."timelapse/skycam_today.mp4";
	$yest = VID_ROOT."timelapse/skycam_yest.mp4";
	$cmd = "/usr/bin/ffmpeg -r $FRAME_RATE -pattern_type glob -y -i \"$inglob\" -crf $CRF -vf scale=$SCALE $outfile";

	if(date('H') == '01') {
		copy($today, $yest);
	}

	$ffmpeg_res = shell_exec($cmd);
	copy($outfile, $today);

	quick_log( 'timelapse.txt', myround( microtime(get_as_float) - $t_start ) ." DAILY ". $cmd);
}

if($tstamp == '0107') {
	// Monthly timelapse
	$freq = 30;
	$twiset = 90;
	$cam = "hik";
	$rate = 18;
	$qual = 24;
	$mon_yest_zero = zerolead($mon_yest);
	$name = "skycam_monthly_${yr_yest}_${mon_yest_zero}";
	$filf = extract_for_timelapse($yr_yest, $mon_yest, 0, $freq, $twiset, $cam, $rate, $qual, $name);
	quick_log( 'timelapse.txt', myround( microtime(get_as_float) - $t_start ) ." MONTHLY ". $filf);
}

if($tstamp == '0112') {
	// Yearly timelapse
	$freq = 180;
	$twiset = -30;
	$cam = "hik";
	$rate = 15;
	$qual = 24;
	$name = "skycam_yearly_${yr_yest}";
	$filf = extract_for_timelapse($yr_yest, 0, 0, $freq, $twiset, $cam, $rate, $qual, $name);
	quick_log( 'timelapse.txt', myround( microtime(get_as_float) - $t_start ) ." YEARLY ". $filf);
}

if($tstamp == '0118') {
	// Yearly midday timelapse
	$freq = 720;
	$twiset = 0;
	$cam = "hik";
	$rate = 6;
	$qual = 24;
	$name = "skycam_yearly_midday_${yr_yest}";
	$filf = extract_for_timelapse($yr_yest, 0, 0, $freq, $twiset, $cam, $rate, $qual, $name);
	quick_log( 'timelapse.txt', myround( microtime(get_as_float) - $t_start ) ." YEARLY_MIDDAY ". $filf);
}

# w = images per row
# sw = padding btwn cols
# sh = padding btwn rows (including space for txt)
# st = space for title
# tinc = time increment
# tfs = time font size, ttfs = title font size
if(date('i') % 30 == 2) { //Produce dailywebcam.jpg
	$w = 6; $dest = 'dailywebcam.jpg'; $imgno = 48; $tinc = 30; $g = 'hik';
	$h = ceil($imgno / $w); $sw = 2; $sh = 18; $hstep = -1; $st = 28; $tfs = 4; $ttfs = 5;
	$title_x_offset = ($wo*$frac*$w+($w-1)*$sw)/2-25;  # half the image size minus approx title length
	# Create the blank background and fill with the base colour
	$image_p = imagecreatetruecolor($wo*$frac*$w+($w-1)*$sw, $ho*$frac*$h+($h)*$sh+$st);
	imagefill($image_p, 0, 0, imagecolorallocate($image_p, 223, 255, 185));
	# time label setup
	$tstart_int = ceil((intval(substr(date('Hi'),0,2))*60+intval(substr(date('Hi'),2,2))) / $tinc) * $tinc;
	$imgno_y = round((1440 - $tstart_int) / $tinc);
	# Set the first title
	imagestring($image_p, $ttfs, $title_x_offset, 0, 'Yesterday', imagecolorallocate($image_p, 94, 11, 24));
	# Paste the images for yesterday
	for($i = 0; $i < $imgno_y; $i++) {
		if(($i % $w) == 0) { $hstep += 1; }
		$j =  $tstart_int + $i * $tinc; $stamp = zerolead(floor($j / 60)) . zerolead($j % 60);
		$image = imagecreatefromjpeg(cam_location($g, 1, $stamp));
		imagecopyresampled($image_p, $image, ($wo*$frac+$sw)*($i % $w), $hstep*($ho*$frac+$sh)+15, 0, 0, $wo*$frac, $ho*$frac, $wo, $ho);
		imagestring($image_p, $tfs, ($wo*$frac+$sw)*($i % $w)+$frac*$wo*0.4, $hstep*($ho*$frac+$sh)+$ho*$frac+15, $stamp, imagecolorallocate($image_p, 54, 14, 14));
		imagedestroy($image);
	}
	$hstep_end = $hstep;
	imagestring($image_p, $ttfs, $title_x_offset, $hstep*($ho*$frac+$sh)+$ho*$frac+33, 'Today', imagecolorallocate($image_p, 84, 11, 24));
	for($i = $imgno_y; $i < $imgno; $i++) {
		if(($i % $w) == 0) { $hstep += 1; }
		if($hstep_end == $hstep) { $spc = -15; } else { $spc = 0; }
		$j = ($i-$imgno_y) * $tinc; $stamp = zerolead(floor($j / 60)) . zerolead($j % 60);
		$image = imagecreatefromjpeg(cam_location($g, 0, $stamp));
		imagecopyresampled($image_p, $image, ($wo*$frac+$sw)*($i % $w), $hstep*($ho*$frac+$sh)+30+$spc, 0, 0, $wo*$frac, $ho*$frac, $wo, $ho);
		imagestring($image_p, $tfs, ($wo*$frac+$sw)*($i % $w)+$frac*$wo*0.4, $hstep*($ho*$frac+$sh)+$ho*$frac+30+$spc, $stamp, imagecolorallocate($image_p, 54, 14, 14));
		imagedestroy($image);
	}
	imagejpeg($image_p, $root. $dest, 70);
	imagedestroy($image_p);

	$dest = 'todaywebcam.jpg'; $imgno = (date('H')+1)*2; $h = ceil($imgno / $w); $hstep = -1;
	$image_p = imagecreatetruecolor($wo*$frac*$w+($w-1)*$sw, $ho*$frac*$h+($h)*$sh+30);
	imagefill($image_p, 0, 0, imagecolorallocate($image_p, 223, 255, 185));
	$hstep_end = $hstep;
	imagestring($image_p, $ttfs, ($wo*$frac*$w+($w-1)*$sw)/2-20, $hstep*($ho*$frac+$sh)+$ho*$frac+30, 'Today', imagecolorallocate($image_p, 84, 11, 24));
	for($i = 0; $i < $imgno; $i++) {
		if(($i % $w) == 0) { $hstep += 1; }
		if($hstep_end == $hstep) { $spc = -15; } else { $spc = 0; }
		$j = $i * $tinc; $stamp = zerolead(floor($j / 60)) . zerolead($j % 60);
		$image = imagecreatefromjpeg(cam_location($g, 0, $stamp));
		imagecopyresampled($image_p, $image, ($wo*$frac+$sw)*($i % $w), $hstep*($ho*$frac+$sh)+30+$spc, 0, 0, $wo*$frac, $ho*$frac, $wo, $ho);
		imagestring($image_p, $tfs, ($wo*$frac+$sw)*($i % $w)+$frac*$wo*0.4, $hstep*($ho*$frac+$sh)+$ho*$frac+30+$spc, $stamp, imagecolorallocate($image_p, 54, 14, 14));
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

function webcam_summary($frac, $w, $dest, $cam_type = "hik", $offset = 0, $wo = 640, $ho = 480) { //Produce webcam summary image (no today/yest titles)
	global $root;
	$imgno = 48;
	$h = ceil($imgno / $w); $sw = 4; $sh = 18; $hstep = -1;
	$image_p = imagecreatetruecolor($wo*$frac*$w+($w-1)*$sw, $ho*$frac*$h+($h)*$sh);
	imagefill($image_p, 0, 0, imagecolorallocate($image_p, 223, 255, 185));
	for($i = 0; $i < $imgno; $i++) {
		if(($i % $w) == 0) { $hstep += 1; }
		$stamp = zerolead(floor($i/2)); if($i % 2 == 0) { $stamp .= '00'; } else { $stamp .= '30'; }
		$image = imagecreatefromjpeg(cam_location($cam_type, $offset, $stamp));
		imagecopyresampled($image_p, $image, ($wo*$frac+$sw)*($i % $w), $hstep*($ho*$frac+$sh), 0, 0, $wo*$frac, $ho*$frac, $wo, $ho);
		imagestring($image_p, 4, ($wo*$frac+$sw)*($i % $w)+$frac*$wo*0.4, $hstep*($ho*$frac+$sh)+$ho*$frac+3, $stamp, imagecolorallocate($image_p, 54, 14, 14));
		imagedestroy($image);
	}
	imagejpeg($image_p, $root. $dest, 60);
	imagedestroy($image_p);
}

function cam_location($cam_type, $offset, $stamp) {
	global $dmonth, $dday;
	$ymd = date("Y/m/d", mktime(12, 0, 0, $dmonth, $dday - $offset));
	return CAM_ROOT."camchive/$cam_type/$ymd/$stamp$cam_type.jpg";
}
?>
