<?php
$allDataNeeded = true;
require('unit-select.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php
	$file = 112; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - PHP test</title>

	<meta name="description" content="PHP script testing for NW3 weather" />

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
<?php include('site_status.php'); ?>

<h1>PHP test page 2</h1>
<?php

// ######## A lesson on why not to use (int) casting in PHP! ##########
echo '<h2>A lesson on why not to use (int) casting in PHP!</h2>
<h3>Code</h3>
<pre>
$num = 2.3;
$dpVal = ($num - 2) * 10;
$dpInt = (int)$dpVal;
$dpInt2 = (int)round($dpVal);
</pre>
<h3>var_dump() Output for each var above</h3>';

$num = 2.3;
$dpVal = ($num - 2) * 10;
$dpInt = (int)$dpVal;
$dpInt2 = (int)round($dpVal);

echo '<pre>';
var_dump($num);
var_dump($dpVal);
var_dump($dpInt);
var_dump($dpInt2);

echo("<h2>Conclusion: use <code>round(val)</code> NOT (int)val</h3>");

 //var_dump($vars);

$juwdfwffw = array($types_alltogether, $types_all, $colours_all, $descriptions_all, $units_all, $nums_all, $typeconvs_all,
	$wxtablecols_all, $roundsizes_all, $roundsizeis_all, $sumq_all, $anomq_all);
//var_dump($juwdfwffw);

if(false && !$nw3) {
	echo 'Not allowed on this ip. Admin only!';
	die();
}
if(isset($_GET['info']))
	phpinfo();

if(isset($_GET['serverBLR']))
	print_m($_SERVER);

if(isset($_GET['superdumpBLR']))
	var_dump($DATM[3][2013]);

if(isset($_GET['superdumpxBLR']))
	print_m($DATX);

/*
echo '</pre>';
$rdat = 'rain';
$tdat = 'temp';
$hdat = 'humidity';

$names = array('r', 't', 'h');

foreach($names as $name) {
	echo ${$name.'dat'} . '<br />';
}

$dayTP = isset($_GET['processDay']) ? $_GET['processDay'] : 'today';
//if(isset($_GET['globals']))
//	print_r($GLOBALS);

 *
 */
//echo "<br /><br />
//	phpIntMax: " . PHP_INT_MAX . ' <br />+1: ' . (PHP_INT_MAX + 1) . ' <br />*-1: ' . (PHP_INT_MAX * -1);

//for($i = 0; $i < count($types_original); $i++) {
//	echo $types_original[$i] . ': ' . count(newData($types_original[$i], true)) . '<br />';
//}

if(isset($_GET['sorttest'])) {
	$type = isset($_GET['vartype']) ? $_GET['vartype'] : 'rain';
	$raw = newData($type, true);
	print_m($raw);
	sort($raw);
	print_m($raw);
}

if(isset($_GET['exectest']))
	exec('/usr/local/bin/php -q /home/nwweathe/public_html/cron_tags.php blr ftw > /dev/null &');

if(isset($_GET['wotest'])) {
	$fileSun = file("http://www.weatheronline.co.uk/weather/maps/current?CONT=ukuk&TYP=sonne&ART=tabelle");
	$len = count($fileSun);
	for($i = 0; $i < $len; $i++) {
		if(strpos($fileSun[$i], 'Heathrow')) {
			$sunHrs = (int) strip_tags($fileSun[$i+1]);
			echo 'Heathrow sunHrs: '. $sunHrs;
			break;
		}
	}
	if($i === $len)
		echo 'Not found';
}
if(isset($_GET['imgtest'])) {
	$execPath = '/usr/local/bin/php -q /home/nwweathe/public_html/';
	exec($execPath. 'graphday.php temp1.png null 20130518');
	exec($execPath. 'graphday2.php temp2.png null 20130518');
	exec($execPath. 'graphdayA.php temp3.png wdir 20130518');

}

function graph_stitch_big($date) {
	$start = ROOT.'imgCache/graphday';
	$end = '_'.$date.'.png';
	$im1 = imagecreatefrompng($start .''. $end);
	$im2 = imagecreatefrompng($start .'2'. $end);
	$im3 = imagecreatefrompng($start .'A'. $end);
	if($im1) {
		$h1 = 407;	$h2 = 390;	$h3 = 220;
		$dimx = 850;
		$dimy = $h1+$h2+$h3;

		//full-size version
		$im_stitch = imagecreatetruecolor($dimx, $dimy);
		imagecopyresampled($im_stitch, $im1, 0, 0,   0, 0,  $dimx, $h1, $dimx, $h1);
		imagecopyresampled($im_stitch, $im2, 0, $h1, 0, 17, $dimx, $h2, $dimx, $h2);
		imagecopyresampled($im_stitch, $im3, 0, $h1+$h2, 0, 0,  $dimx, $h3, $dimx, $h3);
		imagepng($im_stitch, ROOT. substr($date, 0, 4) .'/stitchedmaingraph_'. $date .'.png', 9);

		imagedestroy($im1);
		imagedestroy($im2);
		imagedestroy($im3);
		imagedestroy($im_stitch);
	}
	else {
		error_log('bad image when trying to stitch');
	}
}

function graph_stitch_small($date) {
	$start = ROOT.'imgCache/graphday';
	$end = '_'.$date.'_s.png';
	$im1 = imagecreatefrompng($start .''. $end);
	$im2 = imagecreatefrompng($start .'2'. $end);
	$im3 = imagecreatefrompng($start .'A'. $end);
	if($im1) {
		//mini-version
		$h1 = 245;	$h2 = 225;	$h3 = 149;
		$dimy = $h1 + $h2 + $h3;
		$fix1 = 9;

		$im_stitch = imagecreatetruecolor(smallGraphWidth1 + $fix1, $dimy);
		imagefill( $im_stitch, 0, 0, imagecolorallocate($im_stitch, 255, 255, 255) );
		imagecopyresampled($im_stitch, $im1, $fix1, 0,   0, 0,  smallGraphWidth1, $h1, smallGraphWidth1, $h1);
		imagecopyresampled($im_stitch, $im2, 0, $h1, 0, 17, smallGraphWidth2, $h2, smallGraphWidth2, $h2);
		imagecopyresampled($im_stitch, $im3, 0, $h1+$h2, 0, 0,  smallGraphWidth3, $h3, smallGraphWidth3, $h3);
		imagepng($im_stitch, ROOT. substr($date, 0, 4) .'/stitchedmaingraph_small_'. $date .'.png', 9);

		imagedestroy($im1);
		imagedestroy($im2);
		imagedestroy($im3);
		imagedestroy($im_stitch);
	}
	else {
		echo('bad image when trying to stitch smalls');
	}
}

const smallGraphWidth1 = 533;
const smallGraphWidth2 = 500;
const smallGraphWidth3 = 505;
const smallGraphHeight1n2 = 260;
const smallGraphHeight3 = 150;

if(isset($_GET['cacheImages'])) {
	$yr = $_GET['y'];
	$mon = $_GET['m'];
	$len = isset($_GET['full']) ? get_days_in_month($mon, $yr) : 1;
	for($i = 1; $i <= $len; $i++) {
		$stamp = date('Ymd', mkdate($mon,$i,$yr));
		file_get_contents('http://nw3weather.co.uk/graphday.php?cache&date='. $stamp);
		file_get_contents('http://nw3weather.co.uk/graphday2.php?cache&date='. $stamp);
		file_get_contents('http://nw3weather.co.uk/graphdayA.php?cache&y=220&type=wdir&date='. $stamp);

		file_get_contents('http://nw3weather.co.uk/graphday.php?x=533&y=260&small&cache&date='. $stamp);
		file_get_contents('http://nw3weather.co.uk/graphday2.php?x=500&y=260&small&cache&date='. $stamp);
		file_get_contents('http://nw3weather.co.uk/graphdayA.php?x=505&small&cache&y=150&type=wdir&date='. $stamp);

		graph_stitch_big($stamp);
		graph_stitch_small($stamp);
	}
}

if(isset($_GET['errorpages'])) {
	$codes = array(400, 401, 403, 404, 412, 500);
	foreach ($codes as $code) {
		exec('/usr/local/bin/php -q /home/nwweathe/public_html/errorTemplate.php '. $code .' > /home/nwweathe/public_html/'. $code .'.shtml');
	}
}


if(isset($_GET['hlite'])) {
$highlightString = <<<'LOL'
	<?php
$allDataNeeded = true;
require('unit-select.php'); ?>
</html>
LOL;

highlight_file($root.'TablesDataMonth.php');
highlight_string($highlightString);
}

function comparator($anom, $isShort, $extremeLow, $extremeHigh, $thresh1, $thresh2, $thresh3) {
	$absAnom = abs($anom);
	if($absAnom < $thresh1)
		return $isShort ? 'normal' : 'close to';

	if($absAnom <= $thresh2) {
		$comp = $isShort ? 'slightly' : 'a little';
	} elseif($absAnom <= $thresh3) {
		$comp = $isShort ? 'reasonably' : 'somewhat';
	} else {
		$comp = $isShort ? 'very' : 'much';
	}
	$hl = ($anom < 0) ? $extremeLow : $extremeHigh;
	$than = $isShort ? '' : ' than ';
	return $comp .' '. $hl . $than;
}

function signify($val) {
	return ( ($val < 0) ? '' : '+' ) . $val;
}

function pluralFix($val, $wereWas = false, $unit = 'day') {
	if($val == 1) {
		$str = $unit;
		$str2 = 'was';
	} else {
		$str = $unit.'s';
		$str2 = 'were';
		$val = ($val == 0) ? 'no' : $val;
	}
	return $wereWas ? "$str2 <b>$val</b> $str" :
		"<b>$val</b> $str";
}

//$MDAT = DATtoMDAT($DATA);
//$MDATM = DATtoMDAT($DATM);

function monthlyReport($repMonth, $repYear) {
	global $rainav, $sunav, $DATA, $DATM, $MDAT, $MDATM;
		//var_dump($DATA[2][2008]);
	//echo '</pre>';
	$mmsm = array('min', 'max', 'sum', 'cnt');
	$req = array(0,1,2,13);
	$reqT = array(0,1,2,3);
	$reqm = array(0,3,4,5,6,7);
	$reqmT = array(1,2,3);
	$manualRaw = array();
	$manualRawM = array();

	foreach ($reqm as $value) {
		foreach ($reqmT as $value2) {
			$manualRawM[$value][$mmsm[$value2]] = $MDATM[$value][$repYear][$repMonth][$value2];
		}
	}
	foreach ($req as $value) {
		foreach ($reqT as $value2) {
			$manualRaw[$value][$mmsm[$value2]] = $MDAT[$value][$repYear][$repMonth][$value2];
		}
	}

	$tempAv = $manualRaw[2]['sum'];
	$tempAnomImd = $manualRaw[2]['sum'] - $GLOBALS['tdatav']['mean'][$repMonth-1];
	$tempComparator = comparator($tempAnomImd, false, 'colder', 'warmer', 0.5, 1, 2.5);
	$tempAnom = $tempAnomImd;//, 1.1, true, true);
	$tempLo = $manualRaw[0]['min'];
	$tempHi = $manualRaw[1]['max'];

	$rainAv = $manualRaw[13]['sum'];
	$rainAnomImd = percent($manualRaw[13]['sum'] - $rainav[$repMonth-1], $rainav[$repMonth-1], 0, false, false);
	$rainComparator = comparator($rainAnomImd, false, 'drier', 'wetter', 10, 20, 50);
	$rainAnom = signify($rainAnomImd);
	$rainCnt = $manualRaw[13]['cnt'];
	$rainHi = $manualRaw[13]['max'];
	$rainYrImd = $rainYrCnt = $annualsumCum = 0;
	for($m = 1; $m <= $repMonth; $m++) {
		$rainYrImd += $MDAT[13][$repYear][$m][2];
		$rainYrCnt += $MDAT[13][$repYear][$m][3];
		$annualsumCum += $rainav[$m-1];
	}
	$rainYr = $rainYrImd;
	$rainYrAnom =  percent($rainYrImd - $annualsumCum, $annualsumCum, 0, false, false);//, 0, false, true);

	$sunAv = $manualRawM[0]['sum'];
	$sunAnomImd = percent($manualRawM[0]['sum'] - $sunav[$repMonth-1], $sunav[$repMonth-1], 0, false, false);
	$sunComparator = comparator($sunAnomImd, true, 'dull', 'sunny', 6, 13, 32);
	$sunAnom = signify($sunAnomImd);
	$sunMax = $GLOBALS['maxsun'][$repMonth-1];
	$sunCnt = $manualRawM[0]['cnt'];
	$sunHi = $manualRawM[0]['max'];

	$AFs = sum_cond($DATA[0][$repYear][$repMonth], false, 0);
	$AFsFull = pluralFix($AFs, false, 'air frost');
	$bigRns = sum_cond($DATA[13][$repYear][$repMonth], true, 10);
	$bigRnsFull = pluralFix($bigRns);
	$bigGusts = sum_cond($DATA[11][$repYear][$repMonth], true, 30);
	$maxDepth = $manualRawM[4]['max'];
	$fallSnow = pluralFix($manualRawM[3]['cnt'], true);
	$fallSnowAnomI = round($manualRawM[3]['cnt'] - $GLOBALS['FSav'][$repMonth-1]);
	$fallSnowAnom = abs($fallSnowAnomI);
	$fallSnowAnom2 = ($fallSnowAnomI < 0) ? 'below' : 'above';
	$lySnow = pluralFix($manualRawM[4]['cnt']);
	$AFavr = signify($AFs - $GLOBALS['AFav'][$repMonth-1]);
	$LSavr = signify(round($manualRawM[4]['cnt'] - $GLOBALS['LSav'][$repMonth-1]));
	$hail  = pluralFix($manualRawM[5]['cnt'], true);
	$mm10 = 10; //, 2, true, false, -1);
	$mph30 = 30; //, 4, true, false, -1);

	$export = array(
		"date" => array($repMonth, $repYear),
		"temp" => array($tempComparator, $tempAv, $tempAnom, $tempLo, $tempHi),
		"rain" => array($rainComparator, $rainAv, $rainAnom, $rainCnt, $rainHi, $rainYr, $rainYrAnom, $rainYrCnt),
		"sun" => array($sunComparator, $sunAv, $sunAnom, $sunMax, $sunCnt, $sunHi),
		"winter" => array($AFs, $manualRawM[3]['cnt'], $fallSnow, $fallSnowAnom, $fallSnowAnom2, $AFsFull, $AFavr, $lySnow, $LSavr, $maxDepth),
		"other" => array($hail, $manualRawM[6]['cnt'], $manualRawM[7]['cnt'], $bigRnsFull, 10, $bigGusts, 30)
	);

	$output = '<?php
		$export = ' . var_export($export, true) . ';
		?>';
	file_put_contents(ROOT.$repYear."/report$repMonth.php", $output);
}

if(isset($_GET['repgenall'])) {
	$i = 0;
	do {
		$m = (int)date('n', mkdate($dmonth-$i, 1));
		$y = (int)date('Y', mkdate($dmonth-$i, 1));
		monthlyReport($m, $y);
		$i++;
	} while($y > 2008);
}

if(isset($_GET['mdattest'])) {
	//echo '<pre>';
	//var_dump($DATA[2][2008]);
	$MDAT = DATtoMDAT($DATA);
	$MDATM = DATtoMDAT($DATM);
	//echo '</pre>';
	$mmsm = array('min', 'max', 'sum', 'cnt');
	$req = array(0,1,2,13);
	$reqT = array(0,1,2,3);
	$reqm = array(0,3,4,5,6,7);
	$reqmT = array(1,2,3);
	$manualRaw = array();
	$manualRawM = array();

	for($i = 0; $i <= 4; $i++) {
		$repYear = $yr_yest;
		$repMonth = $mon_yest - $i;

		foreach ($reqm as $value) {
			foreach ($reqmT as $value2) {
				$manualRawM[$value][$mmsm[$value2]] = $MDATM[$value][$repYear][$repMonth][$value2];
			}
		}
		foreach ($req as $value) {
			foreach ($reqT as $value2) {
				$manualRaw[$value][$mmsm[$value2]] = $MDAT[$value][$repYear][$repMonth][$value2];
			}
		}

		$tempAv = conv($manualRaw[2]['sum'], 1);
		$tempAnomImd = $manualRaw[2]['sum'] - $tdatav['mean'][$repMonth-1];
		$tempComparator = comparator($tempAnomImd, false, 'colder', 'warmer', 0.5, 1, 2.5);
		$tempAnom = conv($tempAnomImd, 1.1, true, true);
		$tempLo = conv($manualRaw[0]['min'], 1);
		$tempHi = conv($manualRaw[1]['max'], 1);

		$rainAv = conv($manualRaw[13]['sum'], 2);
		$rainAnomImd = percent($manualRaw[13]['sum'] - $rainav[$repMonth-1], $rainav[$repMonth-1], 0, false, false);
		$rainComparator = comparator($rainAnomImd, false, 'drier', 'wetter', 10, 20, 50);
		$rainAnom = signify($rainAnomImd);
		$rainCnt = $manualRaw[13]['cnt'];
		$rainHi = conv($manualRaw[13]['max'], 2);
		$rainYrImd = $rainYrCnt = $annualsumCum = 0;
		for($m = 1; $m <= $repMonth; $m++) {
			$rainYrImd += $MDAT[13][$repYear][$m][2];
			$rainYrCnt += $MDAT[13][$repYear][$m][3];
			$annualsumCum += $rainav[$m-1];
		}
		$rainYr = conv($rainYrImd, 2);
		$rainYrAnom = conv( percent($rainYrImd - $annualsumCum, $annualsumCum, 0, false, false), 0, false, true);

		$sunAv = conv($manualRawM[0]['sum'], 9);
		$sunAnomImd = percent($manualRawM[0]['sum'] - $sunav[$repMonth-1], $sunav[$repMonth-1], 0, false, false);
		$sunComparator = comparator($sunAnomImd, true, 'dull', 'sunny', 6, 13, 32);
		$sunAnom = signify($sunAnomImd);
		$sunMax = $maxsun[$repMonth-1];
		$sunCnt = $manualRawM[0]['cnt'];
		$sunHi = conv($manualRawM[0]['max'], 9);

		$AFs = sum_cond($DATA[0][$repYear][$repMonth], false, 0);
		$AFsFull = pluralFix($AFs, false, 'air frost');
		$bigRns = sum_cond($DATA[13][$repYear][$repMonth], true, 10);
		$bigRnsFull = pluralFix($bigRns);
		$bigGusts = sum_cond($DATA[11][$repYear][$repMonth], true, 30);
		$maxDepth = conv($manualRawM[4]['max'], 6);
		$fallSnow = pluralFix($manualRawM[3]['cnt'], true);
		$fallSnowAnomI = round($manualRawM[3]['cnt'] - $FSav[$repMonth-1]);
		$fallSnowAnom = abs($fallSnowAnomI);
		$fallSnowAnom2 = ($fallSnowAnomI < 0) ? 'below' : 'above';
		$lySnow = pluralFix($manualRawM[4]['cnt']);
		$AFavr = signify($AFs - $AFav[$repMonth-1]);
		$LSavr = signify(round($manualRawM[4]['cnt'] - $LSav[$repMonth-1]));
		$hail  = pluralFix($manualRawM[5]['cnt'], true);
		$mm10 = conv(10, 2, true, false, -1);
		$mph30 = conv(30, 4, true, false, -1);

		echo "<dl>
			<dt class='temp'>Temperature</dt>
			<dd>Overall, the month was $tempComparator average, with a mean of <b>$tempAv</b> ($tempAnom from the <abbr title='Long-term average'>LTA</abbr>).
				<br />The absolute low was <b>$tempLo</b>, and the highest <b>$tempHi</b>.
			</dd>
			<dt class='rain'>Rainfall</dt>
			<dd>Came in $rainComparator the long-term average, at <b>$rainAv</b> ($rainAnom%) across <b>$rainCnt</b> days of <abbr title='&gt;0.25mm'>recordable rain</abbr>.
				The most rainfall recorded in a single day (starting at midnight) was <b>$rainHi</b>.
				The cumulative annual total for $repYear now stands at <b>$rainYr</b> ($rainYrAnom%) from <b>$rainYrCnt</b> rain days.
			</dd>
			<dt class='sun'>Sunshine</dt>
			<dd>A $sunComparator month, with <b>$sunAv</b> ($sunAnom%) from a possible $sunMax.
				<b>$sunCnt</b> days had more than a minute of sunshine, the maximum being <b>$sunHi</b>.
			</dd>
			<dt class='snow'>Winter Events</dt>
			<dd>";
		echo ($AFs == 0 && $manualRawM[3]['cnt'] == 0) ?
			"No snow or frost observed." :
			"There $fallSnow of falling snow or sleet
			($fallSnowAnom $fallSnowAnom2 the <abbr title='Long-term average'>LTA</abbr>),
				and $AFsFull ($AFavr).
			$lySnow of lying snow at 09z were observed ($LSavr), with a max depth of <b>$maxDepth</b>.
			";
		echo "</dd>
			<dt>Other Events</dt>
			<dd>There $hail of hail, <b>{$manualRawM[6]['cnt']}</b> of thunder,	<b>{$manualRawM[7]['cnt']}</b> with fog at 09z.
				$bigRnsFull had &gt;$mm10 of rain, and <b>$bigGusts</b> with gusts &gt;$mph30.
			</dd>
			</dl>";
	}
}


/*
$arr = array('', -4, 0.0, 4, 9, '-', 'xx', 'lol', 43, -0.0, 0);
$s1 = $s2 = $arr;
sort($s1);
sort($s2, SORT_NUMERIC);

print_m($s1);
print_m($s2);
print_m($arr);
*/

//echo '<h1>NOW</h1>';
//print_m($NOW);
echo '<h1>HR24</h1>';
print_m($HR24);
//echo '<h1>YEST</h1>';
//print_m($YEST);

//$windlabels = array ("N", "NE", "E", "SE", "S", "SW", "W", "NW", "N");

/*
for($y = 2009; $y < 2013; $y++) {
	echo '<h2>Year '. $y. '</h2>';
	$dat = MDtoZ($DATA[12][$y]);
	foreach ($dat as $wraw) {
		$pos = array_search($wraw, $windlabels);
		echo ($pos === false) ? $pos :
			$pos * 45;
		echo '<br />';
	}
	echo '<p>END</p>';
}
*/

// $typegraph31 = 'rain';
// if(isset($_GET['31type'])) { $typegraph31 = $_GET['31type']; }
// for($i = 0; $i < count($types); $i++) {
// 	echo '<option value="', $types_original[$i], '"';
// 	if($typegraph31 == $types_original[$i]) { echo ' selected="selected"'; }
// 	echo '>', $data_description[$i], '</option>
// 	';
// } ?>

<!-- <a href="phptest2.php">Self link</a> -->
<?php
// print_r( MDtoMsummary($DATA[1][2012]) );
// echo '<br /><br />';
// print_r( MDtoMsummary($DATA[12][2012], true) );
// echo '<br /><br />';
// print_r( MDtoMsummary($DATA[13][2011], true) );
//for($day = 0; $day < 31; $day++) { echo $day, ': ', $graph[$day], '<br />'; }
// echo '<img src="graph31.php?type=' . $typegraph31 . '" alt="Last 31 days graph" /><br />';

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

// serialiseCSV('dat');

// if(isset($_GET['custom']))
// echo '$rn10max, $rn60max, $wind10max, $wind10maxt, $wind60max, $wind60maxt, $t10min, $t10max, $t10mint, $t10maxt,
// 				$tchangehrmax, $tchangehrmaxt, $hchangehrmax, $hchangehrmaxt, $tchangehrmin, $tchangehrmint, $hchangehrmin, $hchangehrmint, $rn10maxt,
// 				$nmint, $daymaxt, $avminTime, $avmaxTime, $mins, $maxs, $means, $rnend, $dat, $rn60maxt, $maxgust, $maxgustt, $ttmax, $rrmax, $rrmaxt';
//print_r($custom);

//copy('http://nw3weather.co.uk/wx14.php',$fullpath.'test14.html');

//for($t = 0; $t < 12; $t++) { echo '<img src="/test', $t*5, '.png" /><br />'; }

// function logneaten() {
// 	global $root, $dday;
// 	$filelog = fopen($root.'logfiles/daily/Ymdlog_test.txt',"w");
// 	$filcust = file($root.'customtextout2.txt');
// 	$lastline = explode(',', $filcust[count($filcust)-1]);
// 	$finalsec = mktime($lastline[0], $lastline[1], 0, date('n'), $lastline[2], date('Y'));
// 	$firstsec = $finalsec - 1440*60;
// 	for($i = 0; $i < count($filcust); $i++) {
// 		$custl = explode(',', $filcust[$i]);
// 		if(mktime($custl[0], $custl[2], 0, $mon, $custl[0], $yr) > $firstsec) {
// 			$linewrite = '';
// 			for($t = 0; $t < count($custl); $t++) {
// 				//$dat[$t][$i] = floatval($custl[$t]);
// 				if($t == count($custl)-1) { $com = ''; } else { $com = ','; }
// 				$linewrite .= round($custl[$t],1). $com;
// 			}
// 			fwrite($filelog, $linewrite."\r\n");
// 		}
// 	}
// 	fclose($filelog);
// }

// function old_cam_stitch($date, $yr) {
// 	global $root;
// 	$im1 = imagecreatefromgif($root.$date.'dailywebcam.gif');
// 	$im2 = imagecreatefromgif($root.$date.'dailywebcam2.gif');
// 	if($im1) {
// 		$im_stitch = imagecreatetruecolor(612,930);
// 		imagecopyresampled($im_stitch, $im1, 0, 0, 0, 35, 612, 465, 612, 465);
// 		imagecopyresampled($im_stitch, $im2, 0, 465, 0, 35, 612, 465, 612, 465);
// 		imagegif($im_stitch, $root.$yr.'/'.$date.'dailywebcam.jpg');
// 		imagedestroy($im_stitch);
// 		imagedestroy($im1);
// 		imagedestroy($im2);
// 	}
// 	else { echo 'fail'; }
// }

// // $frac = (isset($_GET['frac'])) ? $_GET['frac'] : 1;
// // $per = (isset($_GET['per'])) ? $_GET['per'] : 1;

// function graph_stitch($frac = 1, $per) {
// 	global $root;
// 	$im1 = imagecreatefrompng('http://nw3weather.co.uk/graphday.php?x='. 850*$per.'&y='. 450*$per);
// 	$im2 = imagecreatefrompng('http://nw3weather.co.uk/graphday2.php?x='. 850*$per.'&y='. 450*$per);
// 	$im3 = imagecreatefrompng('http://nw3weather.co.uk/graphdayA.php?x='. 850*$per.'&y='. 220*$per.'&type1=wdir');
// 	if($im1) {
// 		$im_stitch = imagecreatetruecolor(850*$frac*$per,1017*$frac*$per);
// 		imagecopyresampled($im_stitch, $im1, 0, 0, 0, 0, 850*$frac*$per, 407*$frac*$per, 850*$per, 407*$per);
// 		imagecopyresampled($im_stitch, $im2, 0, 407*$frac*$per, 0, 17*$per, 850*$frac*$per, 390*$frac*$per, 850*$per, 390*$per);
// 		imagecopyresampled($im_stitch, $im3, 0, 797*$frac*$per, 0, 0, 850*$frac*$per, 220*$frac*$per, 850*$per, 220*$per);
// 		imagepng($im_stitch, $root.'stitchedmaingraph_lowq.png', 9);
// 		imagedestroy($im_stitch);
// 		imagedestroy($im1);
// 		imagedestroy($im2);
// 		imagedestroy($im3);
// 	}
// 	else { echo 'fail'; }
// }
 // resource $dst_image , resource $src_image , int $dst_x , int $dst_y , int $src_x , int $src_y , int $dst_w , int $dst_h , int $src_w , int $src_h )
 //graph_stitch($frac, $per);



?>
<br />
</div>

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>
</body>
</html>