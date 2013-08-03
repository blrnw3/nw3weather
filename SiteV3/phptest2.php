<?php require('unit-select.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php 
	$file = 112; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - PHP test 2</title>

	<meta name="description" content="PHP script testing 2 for NW3 weather" />

	<meta http-equiv="content-type" content="application/xhtml+xml; charset=ISO-8859-1" />
	<link rel="stylesheet" type="text/css" href="./mainstyle.css" media="screen" />
	<?php include_once("ggltrack.php"); ?>
</head>

<body>

	<!-- ##### Header ##### -->
	<?php require('header.php'); ?>

	<!-- ##### Left Sidebar ##### -->
	<?php require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->

<div id="main">
<?php
if(!$nw3) {
	echo 'Not allowed on this ip. Admin only!';
	die();
}
?>

<h1>PHP test page 2</h1>
<form method="get" action="">
<select name="31type" onchange='this.form.submit()'>
<?php
$typegraph31 = 'rain';
if(isset($_GET['31type'])) { $typegraph31 = $_GET['31type']; }
for($i = 0; $i < count($types); $i++) {
	echo '<option value="', $types_original[$i], '"';
	if($typegraph31 == $types_original[$i]) { echo ' selected="selected"'; }
	echo '>', $data_description[$i], '</option>
	';
} ?>
</select>
</form>
<a href="phptest2.php">Self link</a>
<?php
//for($day = 0; $day < 31; $day++) { echo $day, ': ', $graph[$day], '<br />'; }
echo '<img src="graph31.php?type=' . $typegraph31 . '" alt="Last 31 days graph" /><br />';

echo "Daytotime 40: " . date('d M Y', daytotimeCM(40));

//webcam_summary(.6, 4, 'dailywebcam.jpg');
//webcam_summary(.3, 7, 'dailygwebcam.jpg', true);


/*
$image_p = imagecreatetruecolor(4000, 112);
imagefill($image_p, 0, 0, imagecolorallocate($image_p, 11, 97, 33));
imagejpeg($image_p, $root. 'background.jpg');


list($width, $height) = getimagesize($root.'static-images/main.JPG');
//$im = imagecreatetruecolor($width, $height);

$image = imagecreatefromjpeg($root.'static-images/main.JPG');
//imagecopyresampled($im, $image, 0, 0, 0, 0, 300, 100, 529, 388);

$font = '/home/nwweathe/public_html/jpgraph/src/fonts/DejaVuSans.ttf';

imagettftext($image, 36, 0, $width/5.5, $height/1.5, imagecolorallocate($image, 188, 245, 169), $font, 'nw3 weather');
//imagestring($image, 5, $width/2, $height/2, 'nw3 weather', imagecolorallocate($image, 225, 25, 125));

imagejpeg($image, $root. 'static-images/newmain.jpg', 100);
imagedestroy($image);
*/
/*
$vert = 150; $dest_width = $width_tot = 0;
for($i = 1; $i <= 3; $i++) {
	list($width[$i], $height[$i]) = getimagesize($root.'static-images/'. $i .'.JPG');
	$frac[$i] = $vert / $height[$i];
	$widthn[$i] = $width[$i] * $frac[$i];
	$width_tot += $widthn[$i];
}
$im = imagecreatetruecolor($width_tot-2, 100);

for($i = 1; $i <= 3; $i++) {
	$image = imagecreatefromjpeg($root.'static-images/'. $i .'.JPG');
	imagecopyresampled($im, $image, $dest_width, 0, 0, $vert-100, $widthn[$i], 100, $width[$i], $height[$i]);
	imagedestroy($image);
	$dest_width += $widthn[$i]-1;
}

$font = '/home/nwweathe/public_html/jpgraph/src/fonts/DejaVuSans.ttf';
imagettftext($im, 36, 0, $widthn[1]-10, 100/2.2, imagecolorallocate($im, 168, 245, 159), $font, 'nw3 weather');
imagettftext($im, 18, 0, $widthn[1]+$widthn[2]+50, 100/3.4, imagecolorallocate($im, 20, 63, 8), $font, 'Hampstead');
imagettftext($im, 18, 0, $widthn[1]+$widthn[2]+50, 100/1.8, imagecolorallocate($im, 42, 71, 34), $font, 'London');

imagejpeg($im, $root. 'static-images/newmain.jpg');
imagedestroy($im);

$image_p = imagecreatetruecolor(300, 100);
$image = imagecreatefromjpeg($root.'main3.jpg');
imagecopyresampled($image_p, $image, 0, 0, 0, 0, 300, 100, 529, 388);
imagestring($image_p, 5, 90, 40, 'NW3 Weather', imagecolorallocate($image_p, 240, 240, 140));
imagedestroy($image);
imagejpeg($image_p, $root. 'newmain.jpg', 70);
imagedestroy($image_p);

echo '<br />';
$datay = graph12();
for($mon = 1; $mon <= 12; $mon++) {
	echo $mon, ' ', $datay[0][$mon], ' ',$datay[1][$mon], '<br />';
}

//echo 'month: ', true_z(date('z'),2012), '; day: ', true_z(date('z'),2012,'j');
$times = array('12:45', '13:42', '', 4, '14:16', '-', '13:03');
echo time_av($times), ' ';

print_r(datt('ratemax', 2012, null, true, true));
*/

// mkdir($root.'2013');


// $custom = customlog('today');
// echo '$rn10max, $rn60max, $wind10max, $wind10maxt, $wind60max, $wind60maxt, $t10min, $t10max, $t10mint, $t10maxt,
				// $tchangehrmax, $tchangehrmaxt, $hchangehrmax, $hchangehrmaxt, $tchangehrmin, $tchangehrmint, $hchangehrmin, $hchangehrmint, $rn10maxt,
				// $nmint, $daymaxt, $avminTime, $avmaxTime, $mins, $maxs, $means, $rnend, $dat, $rn60maxt, $maxgust, $maxgustt, $ttmax, $rrmax, $rrmaxt';
// print_r($custom);

$mon_st = 10; $mon_en = 12; $day_st = 1; $day_en = 31;

for($m = $mon_st; $m <= $mon_en; $m++) {
	echo '<br />', $months[$m-1], '<br />';

	for($i = $day_st; $i <= $day_en; $i++) {
		$name = $root.date('Y/Ymd', mkdate($m,$i,2012)) . 'dailywebcam';
		if(file_exists($name.'.gif')) {
			unlink($name.'.gif');
			unlink($name.'2.gif');
			echo $name . ' deleted <br />';
		}
	}
}


//copy('http://nw3weather.co.uk/wx14.php',$fullpath.'test14.html');

//for($t = 0; $t < 12; $t++) { echo '<img src="/test', $t*5, '.png" /><br />'; }

// webcam_summary(.22, 6, date('Y/Ymd',time()-14*3600) . 'dailywebcam.jpg', false, true);
// webcam_summary(.4, 6, date('Y/Ymd',time()-14*3600) . 'dailywebcam_large.jpg', false, true);

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

function old_cam_stitch($date, $yr) {
	global $root;
	$im1 = imagecreatefromgif($root.$date.'dailywebcam.gif');
	$im2 = imagecreatefromgif($root.$date.'dailywebcam2.gif');
	if($im1) {
		$im_stitch = imagecreatetruecolor(612,930);
		imagecopyresampled($im_stitch, $im1, 0, 0, 0, 35, 612, 465, 612, 465);
		imagecopyresampled($im_stitch, $im2, 0, 465, 0, 35, 612, 465, 612, 465);
		imagegif($im_stitch, $root.$yr.'/'.$date.'dailywebcam.jpg');
		imagedestroy($im_stitch);
		imagedestroy($im1);
		imagedestroy($im2);
	}
	else { echo 'fail'; }
}

$frac = (isset($_GET['frac'])) ? $_GET['frac'] : 1;
$per = (isset($_GET['per'])) ? $_GET['per'] : 1;

function graph_stitch($frac = 1, $per) {
	global $root;
	$im1 = imagecreatefrompng('http://nw3weather.co.uk/graphday.php?x='. 850*$per.'&y='. 450*$per);
	$im2 = imagecreatefrompng('http://nw3weather.co.uk/graphday2.php?x='. 850*$per.'&y='. 450*$per);
	$im3 = imagecreatefrompng('http://nw3weather.co.uk/graphdayA.php?x='. 850*$per.'&y='. 220*$per.'&type1=wdir');
	if($im1) {
		$im_stitch = imagecreatetruecolor(850*$frac*$per,1017*$frac*$per);
		imagecopyresampled($im_stitch, $im1, 0, 0, 0, 0, 850*$frac*$per, 407*$frac*$per, 850*$per, 407*$per);
		imagecopyresampled($im_stitch, $im2, 0, 407*$frac*$per, 0, 17*$per, 850*$frac*$per, 390*$frac*$per, 850*$per, 390*$per);
		imagecopyresampled($im_stitch, $im3, 0, 797*$frac*$per, 0, 0, 850*$frac*$per, 220*$frac*$per, 850*$per, 220*$per);
		imagepng($im_stitch, $root.'stitchedmaingraph_lowq.png', 9);
		imagedestroy($im_stitch);
		imagedestroy($im1);
		imagedestroy($im2);
		imagedestroy($im3);
	}
	else { echo 'fail'; }
}
 // resource $dst_image , resource $src_image , int $dst_x , int $dst_y , int $src_x , int $src_y , int $dst_w , int $dst_h , int $src_w , int $src_h )
 //graph_stitch($frac, $per);

?>
<br />
</div>

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>
</body>
</html>