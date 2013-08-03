<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<?php 
	$file = 2;
	$subfile = true; ?>

	<title>NW3 Weather - 24hr Webcam Summary</title>

	<meta name="description" content="Web cam images from during the day (past 24hrs) from NW3 weather, overlooking Hampstead Heath" />

	<?php require('chead.php'); ?>
	<?php include_once("ggltrack.php"); ?>
</head>

<body>

	<!-- ##### Header ##### -->
	<?php require('header.php'); ?>

	<!-- ##### Left Sidebar ##### -->
	<?php require('leftsidebar.php'); ?>


	<!-- ##### Main Copy ##### -->
<div id="main">

<h1>Last 24 hours Webcam Image Summary</h1>

<?php
$checked = ' checked="checked"';
$qrand = date('dmYH');
if(isset($_GET['gcam']) && $_GET['gcam'] == 1) { $gornog = 'g'; $choseng = $checked; } else { $gornog = ''; $chosens = $checked; }
if(isset($_GET['yest']) && $_GET['yest'] == 1) { $day = 'yest'; $day0 = 'yest/'; $choseny = $checked; } else { $day = 'curr'; $day0 = ''; $chosent = $checked; }
if(isset($_GET['tstart']) && $_GET['tstart'] != '') { $tstart = substr($_GET['tstart'], 0, 4); } else { $tstart = '0000'; }
if(isset($_GET['tend']) && $_GET['tend'] != '') { $tend = substr($_GET['tend'], 0, 4); } else { $tend = '2359'; }
if(isset($_GET['imgno']) && $_GET['imgno'] != '') { $imgno = min( (int) $_GET['imgno'], 99 ); } else { $imgno = 48; }
if(isset($_GET['width']) && $_GET['width'] != '') { $width = min( (int) $_GET['width'], 640 ); } else { $width = 286; }
$height = 0.75 * $width;
$colnum = floor((865 - (( floor(865 / $width) )-1)*3) / $width); //account for cellpading

if( (int) $tstart > 2358 ) {
	$tstart = '2358';
}
if( (int) $tend > 2359 ) {
	$tend = '2359';
}

echo '<form method="get" action="">
	<b>Cam Type: </b>
	<label><input type="radio" name="gcam" value="1"', $choseng, ' /> Ground</label>
	<label><input type="radio" name="gcam" value="0"', $chosens, ' /> <span class="rightPad">Sky</span></label>
	<b>Day: </b>
	<label><input type="radio" name="yest" value="1"', $choseny, ' /> Yesterday</label>
	<label><input type="radio" name="yest" value="0"', $chosent, ' /> <span class="rightPad">24-hr</span></label>
	<label>Start Time: <input class="rightPad"  type="text" name="tstart" maxlength="4" size="4" value="'.$tstart.'" /></label>
	<label>End Time: <input type="text" name="tend"  maxlength="4" size="4" value="'.$tend.'" /></label>
	<br /> <label>Image number: <input class="rightPad" type="text" name="imgno"  maxlength="2" size="2" value="'.$imgno.'" /></label>
	<label>Image width:  <input class="rightPad" type="text" name="width"  maxlength="3" size="3" value="'.$width.'" /></label>
	<input type="submit" value="Generate Images" />
	</form><br />
	<table align="center" cellpadding="2" border="0" width="865">';

$tstart_int = intval(substr($tstart,0,2))*60+intval(substr($tstart,2,2));
$tend_int = intval(substr($tend,0,2))*60+intval(substr($tend,2,2));
$tinc = round(($tend_int - $tstart_int) / $imgno);
for($i = 0; $i < $imgno; $i++) { $j =  $tstart_int + $i * $tinc; $stamp[$i] = zerolead(floor($j / 60)) . zerolead($j % 60); }

if($day == 'curr' && $tstart == '0000' && $tend == '2359') {
	echo '<tr><th align="center" colspan="', $colnum, '"><h2>Yesterday</h2></th></tr>';
	$tstart_int = floor((intval(substr(date('Hi'),0,2))*60+intval(substr(date('Hi'),2,2))) / 30) * 30;
	$imgno_y = round(($tend_int - $tstart_int) / 30);
	for($i = 0; $i < $imgno_y; $i++) { $j =  $tstart_int + $i * $tinc; $stamp[$i] = zerolead(floor($j / 60)) . zerolead($j % 60); }
	for($i = $imgno_y; $i < $imgno; $i++) { $j = ($i-$imgno_y) * $tinc; $stamp[$i] = zerolead(floor($j / 60)) . zerolead($j % 60); }
	$day = 'yest'; $day0 = 'yest/';
	$carryon = true;
}
else { $imgno_y = $imgno; $carryon = false; }

for($i = 0; $i < $imgno_y; $i++) {
	if(($i+$colnum) % $colnum == 0) { echo '<tr>'; }
	echo '<td align="center">
		<img src="/curr', $gornog, 'cam/', $day0, camTimeFix($stamp[$i], $gornog), $day, $gornog, 'cam.jpg?', $qrand,'" alt="webcam" width="', $width, '" height="', $height, '" /></td>
		';
	if(($i+1) % $colnum == 0) {
		echo '</tr> <tr>';
		for($t=0; $t<$colnum; $t++) { echo '<td align="center" valign="top"> <b>', $stamp[$i+$t-$colnum+1], '</b> </td>'; }
		echo '</tr><tr> <td align="center" valign="top"> <span style="color: white;"> - </span> </td></tr>
			';
	}
	if($stamp[$i] > $tend) { $i = $imgno_y; }
}
if($carryon) { echo '<tr><th align="center" colspan="', $colnum, '"><h2>Today</h2></th></tr>'; $day = 'curr'; $day0 = ''; }
for($i = $imgno_y; $i < $imgno; $i++) {
	if(($i+$colnum) % $colnum == 0) { echo '<tr>'; }
	echo '<td align="center">
		<img src="/curr', $gornog, 'cam/', $day0, $stamp[$i], $day, $gornog, 'cam.jpg?', $qrand,'" alt="webcam" width="', $width, '" height="', $height, '" /></td>
		';
	if(($i+1) % $colnum == 0) {
		echo '</tr> <tr>';
		for($t=0; $t<$colnum; $t++) { echo '<td align="center" valign="top"> <b>', $stamp[$i+$t-$colnum+1], '</b> </td>'; }
		echo '</tr><tr> <td align="center" valign="top"> <span style="color: white;"> - </span> </td></tr>
			';
	}
}

function camTimeFix($stamp, $isG) {
	if(!$isG) {
		return $stamp;
	}

	$val = (int) $stamp;
	$fix = ($val < 999) ? '0' : '';

	return $fix . roundbig($val, 10, 0);
}
?>
</table>

	</div>

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>
 </body>
</html>