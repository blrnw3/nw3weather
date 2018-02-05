<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<?php
	$file = 2;
	$subfile = true; ?>

	<title>NW3 Weather - High res Webcam Summary / Archive</title>

	<meta name="description" content="High resolution archive web cam images from NW3 weather, overlooking Hampstead Heath" />

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

<h1>High res Webcam Summary and Archive</h1>

<?php
$url = 'highreswebcam.php';

$widths = [
	1 => 640,
	2 => 430,
	3 => 285,
	4 => 210,
	5 => 170,
	6 => 141,
	8 => 105,
	10 => 82
//	12 => 68,
//	15 => 53
];
$freqs = [180, 60, 30, 15, 10, 5, 1];

if(isset($_GET['day'])) { $dproc = intval($_GET['day']); } else { $dproc = $dday; }
if(isset($_GET['month'])) { $mproc = intval($_GET['month']); } else { $mproc = $dmonth; }
if(isset($_GET['year'])) { $yproc = $_GET['year']; } else { $yproc = $dyear; }
$sproc = mkdate($mproc,$dproc,$yproc);
$datetag = date('Ymd',$sproc) . 'daily';
$datedescrip = date('jS F Y',$sproc);

$riseproc = date_sunrise($sproc, SUNFUNCS_RET_DOUBLE, $lat, $lng, $zenith, date('I', $sproc)) - 1.5;
$setproc = date_sunset($sproc, SUNFUNCS_RET_DOUBLE, $lat, $lng, $zenith, date('I', $sproc)) + 1.5;

if($me) { echo " Sunrise: $riseproc; &nbsp; Sunset: $setproc <br />"; }

$cond1 = $sproc > mkdate(3,20,2012);
$cond2 = $sproc < mkdate($dmonth,$dday,$dyear);
if(!$cond1) { echo '<b>Archive begins on 20th March 2012 (patchy until 27 Jun 2012)</b><br />'; }
$is_today = (mkdate() === $sproc);

if(isset($_GET['camtype']) && $_GET['camtype'] == "gnd") { $cam_type = "gnd"; $choseng = checkHTML; } else { $cam_type = "sky"; $chosens = checkHTML; }
if(isset($_GET['light']) && $_GET['light'] == "day") { $light = "day"; $chosend = checkHTML; } else { $light = "all"; $chosena = checkHTML; }
if(isset($_GET['width']) && $widths[(int)$_GET['width']] ) { $width_opt = (int)$_GET['width']; } else { $width_opt = 3; }
if(isset($_GET['freq']) && in_array((int)$_GET['freq'], $freqs) ) { $freq = (int)$_GET['freq']; } else { $freq = $freqs[2]; }
if(isset($_GET['frame']) && (int)$_GET['frame'] < 24) { $frame = intval($_GET['frame']); } else { $frame = $is_today ? (int)date("H") : 12; }

$qrand = date('dmYH');
$width = $widths[$width_opt];
$padding = ($width < 200) ? 0 : 2;
$height = 0.75 * $width;
$font_size = ($width < 200) ? 80 : 100;

if($freq === 1 && $sproc <= mkdate() - 10 * 24 * 3600) {
	echo "<b>NB:</b> Images at a 1-min interval are only available for the past 10 days.<br />";
}
if($sproc <= mkdate(9, 24, 2016)) {
	echo "<b>NB:</b> Before 24th Sep 2016, archive images are only avalable at 3hr intervals. <br />"
	. "To view 30-min summaries, please <a href='./wcarchive.php'>visit the older archive</a>. <br />";
	$freq = 180;
}

if($freq >= 5) {
	$pframe = $frame; $nframe = $frame;
	$prevs = $sproc - 3600*24; $nexts = $sproc + 3600*24;
	$prevd = date('j', $prevs); $prevm = date('n', $prevs); $prevy = date('Y', $prevs);
	$nextd = date('j', $nexts); $nextm = date('n', $nexts); $nexty = date('Y', $nexts);
} else {
	$pframe = ($frame === 0) ? 23 : $frame - 1; $nframe = ($frame + 1) % 24;
	$hsproc = mktime($frame, 0, 0, $mproc,$dproc,$yproc);
	$prevs = $hsproc - 3600; $nexts = $hsproc + 3600;
	$prevd = date('j', $prevs); $prevm = date('n', $prevs); $prevy = date('Y', $prevs);
	$nextd = date('j', $nexts); $nextm = date('n', $nexts); $nexty = date('Y', $nexts);
}

$prev_url = "$url?year=$prevy&month=$prevm&day=$prevd&camtype=$cam_type&light=$light&width=$width_opt&freq=$freq&frame=$pframe&cycle";
$next_url = "$url?year=$nexty&month=$nextm&day=$nextd&camtype=$cam_type&light=$light&width=$width_opt&freq=$freq&frame=$nframe&cycle";
?>

<form method="get" action="">
<label>Date: <select name="year">
<?php
for($i = 2012; $i <= $dyear; $i++) {
	echo '<option value="', $i, '"';
	if($yproc == $i) { echo ' selected="selected"'; }
	echo '>', $i, '</option>';
} ?>
</select></label>
<select name="month">
<?php $months = array('Jan', 'Feb', 'March', 'April', 'May', 'June', 'July', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
for($i = 0; $i < 12; $i++) {
	echo '<option value="', sprintf('%1$02d',$i+1), '"';
	if($mproc == $i+1) { echo ' selected="selected"'; }
	echo '>', $months[$i], '</option>';
} ?>
</select>
<select name="day" style="margin: 0.5em">
<?php
for($i = 1; $i <= 31; $i++) {
	echo '<option value="', sprintf('%1$02d',$i), '"';
	if($dproc == $i) { echo ' selected="selected"'; }
	echo '>', $i, '</option>'; } ?>
</select>

<?php
echo '<b>Cam Type: </b>
	<label><input type="radio" name="camtype" value="gnd" ', $choseng, ' /> Ground</label>
	<label><input type="radio" name="camtype" value="sky" ', $chosens, ' /> <span class="rightPad">Sky</span></label>
	<span style="padding-left:0.7em"><b>Daylight only: </b></span>
	<label><input type="radio" name="light" value="day" ', $chosend, ' /> <span class="rightPad">Yes</span></label>
	<label><input type="radio" name="light" value="all" ', $chosena, ' /> <span class="rightPad">No</span></label>';
?>
	<br />
<label style='padding-left:0.2em'>Images per row: <select name="width">
<?php foreach($widths as $wo => $null) {
	$selec = ($wo === $width_opt) ? selectHTML : '';
	echo "<option value='$wo' $selec>$wo</option>'"; } ?>
</select></label>
<label style='padding-left:1em'>Interval / mins: <select name="freq">
<?php foreach($freqs as $f) {
	$selec = ($f === $freq) ? selectHTML : '';
	echo "<option value='$f' $selec>$f</option>'"; } ?>
</select></label>
<?php
if($freq < 5) {
	echo '<label>Hour: <select name="frame">';
	foreach(range(0, 23) as $h) {
		$selec = ($frame === $h) ? selectHTML : '';
		echo "<option value='$h' $selec>$h</option>'";
	}
}
?>
</select></label>
<input style='margin-left:0.7em' type="submit" value="Generate" />
&nbsp; <a href="<?php echo $url ?>" title="Reset page to default params">Reset</a>
<?php if($cond1) { echo "<a style='padding-left:2em' href='$prev_url'>&lt;&lt;Previous</a>"; } ?>
<?php if($cond2) { echo "<a style='padding-left:1em' href='$next_url'>Next&gt;&gt;</a>"; } ?>
</form><br />

<?php
# Don't hit the server too hard with img requests
if(isset($_GET['cycle'])) {
	$ni = ($freq === 1) ? 60 : 1440 / $freq;
	usleep(250000 + 5000 * $ni);
}

echo '<table align="center" cellpadding="'. $padding .'" border="0" width="865">';

$offset = ($freq === 1) ? $frame * 60 : 0;
$num_imgs = ($freq === 1) ? 60 : 1440 / $freq;
$post_imgs = [];
$pre_imgs = [];
foreach(range(0, $num_imgs-1) as $n) {
	$mins = $offset + $n * $freq;
	$minutes = mktime(0, $mins);
	$stamp = date("Hi", $minutes);
	$float_stamp = $mins / 60;
	if($light === "day" && ($float_stamp < $riseproc || $float_stamp > $setproc)) {
		continue;
	}
	if($is_today && $freq >= 5 && $minutes > time()) {
		$pre_imgs[] = $stamp;
	} else {
		$post_imgs[] = $stamp;
	}
}
if($pre_imgs) cam_table($pre_imgs, "Yesterday", -1);
cam_table($post_imgs, ($is_today) ? "Today" : $datedescrip, 0);

function cam_table($imgs, $heading, $day_offset) {
	global $sproc, $font_size, $qrand, $width_opt, $cam_type, $width, $height, $root;

	$img_rows = [];
	foreach($imgs as $n => $img_stamp) {
		$img_rows[floor($n / $width_opt)][] = $img_stamp;
	}

	echo '<tr><th align="center" colspan="', $width_opt, '"><h2>', $heading ,'</h2></th></tr>';
	foreach($img_rows as $null => $img_row) {
		echo "<tr>";
		foreach($img_row as $img) {
			$dir = date('Y/m/d', $sproc + 24 * 3600 * $day_offset);
			$src = "/camchive/$cam_type/$dir/$img$cam_type.jpg";
			if(file_exists($root.$src)) {
				echo '<td align="center"><img src="', $src, '?', $qrand ,'" alt="webcam" width="', $width, '" height="', $height, '" /></td>';
			} else {
				echo '<td align="center">No image</td>';
			}
		}
		echo "</tr>";
		reset($img_row);
		echo "<tr>";
		foreach($img_row as $img) {
			echo '<td align="center" valign="top" style="font-size:', $font_size ,'%"> <b>', $img, '</b> </td>';
		}
		echo "</tr>";
	}
}
?>
</table>

</div>

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>
 </body>
</html>
