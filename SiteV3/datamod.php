<?php $allDataNeeded = true;
require('unit-select.php');
	 ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Data Input</title>

	<meta name="description" content="Data input/mod" />

	<meta http-equiv="content-type" content="application/xhtml+xml; charset=ISO-8859-1" />
	<link rel="stylesheet" type="text/css" href="widestyle.css" media="screen" title="screen" />

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
	die('Not allowed on this ip. Admin only!');
}

if(isset($_GET['cerealify'])) {
	serialiseCSVm();
	die();
}

if(isset($_GET['dtm'])) { $dtm = $_GET['dtm']; } else { $dtm = 1; }
//Link to EGLC pressure
echo '<a href="http://www.wunderground.com/history/airport/EGLC/',
	date('Y/n/d',mktime(1,1,1,date('n'),date('d')-$dtm)),
	'/DailyHistory.html">EGLC History for yesterday</a><br />';

echo 'datt size in B: ', filesize($fullpath."datt" . date('Y',mktime(1,1,1,date('n'),date('j')-$dtm,date('Y'))) . ".csv"), '<br />';
if(!isset($_POST['pwd'])) {
	$wufil = urlToArray('http://www.wunderground.com/history/airport/EGLC/' . date('Y/n/d',mktime(1,1,1,date('n'),date('d')-$dtm)) . '/DailyHistory.html?format=1');
	$eglc_pmax = 1; $eglc_pmin = 1100;
	for($i = 1; $i < count($wufil); $i++) {
		$eglc = explode(',', $wufil[$i]);
		$eglc_p = intval($eglc[4]);
		if($eglc_p > $eglc_pmax) { $eglc_pmax = $eglc_p; }
		if($eglc_p < $eglc_pmin && $eglc_p > 9) { $eglc_pmin = $eglc_p; }
	}
	echo $wufil ? ($eglc_pmax .' '. $eglc_pmin) : 'Timeout';

	$sun = file(ROOT.'maxsun.csv');
	echo '<br />Max sun for this day: ', $sun[date('z',mkdate(date('n'),date('j')-$dtm, date('Y')))], ' hours';
}
echo '<br /><a href="datamod.php?dtm=', $dtm, '">Self link</a>';

$moddata = file($fullpath."dat" . date('Y',mktime(1,1,1,date('n'),date('j')-$dtm,date('Y'))) . ".csv");
$modline = explode(',', $moddata[count($moddata)-$dtm]);

$moddatat = file($fullpath."datt" . date('Y',mktime(1,1,1,date('n'),date('j')-$dtm,date('Y'))) . ".csv");
$modlinet = explode(',', $moddatat[count($moddatat)-$dtm]);

$moddatam = file($fullpath."datm" . date('Y',mktime(1,1,1,date('n'),date('j')-$dtm,date('Y'))) . ".csv");
$modlinem = explode(',', $moddatam[count($moddatam)-$dtm]);
$modlinem = str_ireplace('?', ',', $modlinem);

if(isset($_POST['pwd'])) {
	if($_POST['pwd'] == 'datachanges') {
		for($i = 0; $i < count($modline)-2; $i++) {
			if(isset($_POST[$types_original[$i]]) && $_POST[$types_original[$i]] != '') {
				$modline[$i] = $_POST[$types_original[$i]];
			}
			if(isset($_POST[$types_original[$i].'t']) && $_POST[$types_original[$i].'t'] != '') {
				$modlinet[$i] = $_POST[$types_original[$i].'t'];
			}
		}
		$moddata[count($moddata)-$dtm] = implode(',', $modline);
		$fildat = fopen($fullpath."dat" . date('Y',mktime(1,1,1,date('n'),date('j')-$dtm,date('Y'))) . ".csv","w");
		for($i = 0; $i < count($moddata); $i++) {
			$newline[$i] = explode(',', $moddata[$i]);
			$newline[$i][count($newline[$i])-1] = intval($newline[$i][count($newline[$i])-1]);
			//array_splice($newline[$i],-1);
			fputcsv($fildat, $newline[$i]);
		}
		fclose($fildat);

		$moddatat[count($moddatat)-$dtm] = implode(',', $modlinet);
		$fildatt = fopen($fullpath."datt" . date('Y',mktime(1,1,1,date('n'),date('j')-$dtm,date('Y'))) . ".csv","w");
		for($i = 0; $i < count($moddatat); $i++) {
			$newlinet[$i] = explode(',', $moddatat[$i]);
			$newlinet[$i][count($newlinet[$i])-1] = intval($newlinet[$i][count($newlinet[$i])-1]);
			fputcsv($fildatt, $newlinet[$i]);
		}
		fclose($fildatt);

		for($i = 0; $i < count($modlinem); $i++) {
			if(isset($_POST[$types_m_original[$i]]) && $_POST[$types_m_original[$i]] != '') {
				$modlinem[$i] = str_ireplace(',', '?',$_POST[$types_m_original[$i]]);
			}
		}
		$moddatam[count($moddatam)-$dtm] = implode(',', $modlinem);
		$fildatm = fopen($fullpath."datm" . date('Y',mktime(1,1,1,date('n'),date('j')-$dtm,date('Y'))) . ".csv","w");
		for($i = 0; $i < count($moddatam); $i++) {
			$newlinem[$i] = str_ireplace('"','',explode(',', $moddatam[$i]));
			$newlinem[$i][count($newlinem[$i])-1] = intval($newlinem[$i][count($newlinem[$i])-1]);
			fputcsv($fildatm, $newlinem[$i]);
		}
		fclose($fildatm);
//		serialiseCSVm();

//		exec('/usr/local/bin/php -q /home/nwweathe/public_html/cron_tags.php blr ftw > /dev/null &');
	}
	else {
		echo 'password fail';
		print_m($_POST);
	}
}

//Form
$modTimestamp = mkdate($dmonth,$dday-$dtm,$dyear);
$target_st = date('Y', $modTimestamp) . '/stitchedmaingraph_';
$target_en = date('Ymd', $modTimestamp) .'.png';
echo '<form method="get" action=""><input type="text" name="dtm" /> <input type="submit" value="Choose day" /></form><br />';
echo 'Viewing ', date( 'jS F Y', $modTimestamp );
echo '<br />
	<form method="post" action="">
	<table border="1" cellpadding="4">';
for($i = 0; $i < count($modline); $i++) {
	echo '<tr>
		<td>', $types_original[$i], '</td>
		<td>', $modline[$i], ' </td>
		<td><input type="text" name="', $types_original[$i], '" /> </td>
		<td>', $modlinet[$i], ' </td>
		<td><input type="text" name="', $types_original[$i], 't" /> </td>';
		if($i==0) {
			echo '<td align="center" rowspan=', count($modline), '">';

			$graphres = $target_st .''. $target_en;
			if(file_exists(ROOT. $graphres)) {
				echo '<img src="/'. $graphres .'" alt="day graph" '. GRAPH_DIMS_LARGE .' />
					';
			} else {
				echo '<h3>FAIL GRAPH BIG!</h3>';
			}
				echo '</td>';
		}
		echo '</tr>';
}
echo '</table>
	<br />
	<table border="1" cellpadding="5">';
for($i = 0; $i < count($modlinem); $i++) {
	echo '<tr><td>', $types_m_original[$i], '</td>
		<td>', $modlinem[$i], ' </td>
		<td><input type="text" name="', $types_m_original[$i], '" /> </td>
		</tr>';
}
echo '</table><br />
	<input type="text" name="user" />
	<input type="password" name="pwd" />
	<select name="dtm"><option value="', $dtm, '">Yesterday-', $dtm, '</option></select>
	<input type="submit" value="Submit Changes" />
	</form>';
	echo '<br />
	';
	$graphres = $target_st .'small_'. $target_en;
	if(file_exists(ROOT. $graphres)) {
		echo '<img src="/'. $graphres .'" alt="day graph" '. GRAPH_DIMS_SMALL .' />
			';
	} else {
		echo '<h3>FAIL GRAPH SMALL!</h3>';
	}

//when editing last day of the month
if(isset($_POST['pwd']) && (date('j', $modTimestamp) == date('t', $modTimestamp)) || isset($_GET['reportRegen'])) { //generate month report
	$DATM = array();
	$DATM = unserialize(file_get_contents(ROOT.'serialised_datm.txt'));
	$MDAT = DATtoMDAT($DATA);
	$MDATM = DATtoMDAT($DATM);
	monthlyReport((int)date('n', $modTimestamp), (int)date('Y', $modTimestamp));
	echo '<h1>Mmonthly report for '.date('n', $modTimestamp). (int)date('Y', $modTimestamp).' has been produced </h1>';
}
?>

<img src="/<?php echo date("Y/Ymd", $modTimestamp); ?>dailywebcam.jpg" alt="daycamsum" />
<h2>Rules for manual observations</h2>
<p>
	<dl>
		<dt>Snow</dt>
		<dd>y: all snow; 0.1: trace; float: estimate of snow amount if there was some rain too</dd>
		<dt>Ly snow</dt>
		<dd>0.1: trace; float: non-trace, specified quantity</dd>
		<dt>hail</dt>
		<dd>1-3 scale: 1 small, 2 med, 3 large</dd>
		<dt>Thunder</dt>
		<dd>1-4 scale: 1 thunder; 2 light TS, 3 med TS, 4 sev TS</dd>
		<dt>Fog</dt>
		<dd>blank or 1</dd>
		<dt>comms</dt>
		<dd>
			<pre>
$finds = array('"','?','(S)','(M)','(L)','Shwr','L ','M ','H ','T ','S ','V ','-','Snw','L-Sn','Sn','LySn','w/ ','AF', 'T-S', //1
	'occ',"tr'cm",'bkn','Dz','Rn','oc','poss',' yy','aa','L/','M/','H/','T/','S/','V/', 'T-storm', 'T-Storm', //2
	'L-','M-','H-','T-','S-','V-','xx','Sh','Lyn','Drzl','Slt','SnowS','brks','inc ', //3
	"Sl't", 'sct', 'erws', 'bk', 'L,','M,','H,','T,','S,','V,', 'Heath', 'Severe Heat', 'Heat', 'Fz', ' w ', //4
	'L)','M)','H)','T)','S)','V)','L;','M;','H;','T;','S;','V;','nowow', //5
	'hhh', 'Max Sun'); //6
$repls = array('',',','','','','Sh','Light ','Moderate ','Heavy ','Torrential ','Slight ','Very Heavy ','-','Sn','LySn','Snow','Lying Snow','with ','Air frost', 'T-storm', //1
	'oc','trace','bk','Drizzle','Rain','occasional','possible','','','Light/','Moderate/','Heavy/','Torrential/','Slight/','Very Heavy/', 'T-Storm', 'Thunderstorm',//2
	'Light-','Moderate-','Heavy-','Torrential-','Slight-','Very Heavy-','','Shower','Lying','Drizzle','Sleet','Snow S','breaks', 'including ', //3
	'Sleet', 'scattered', 'ers', 'broken','Light,','Moderate,','Heavy,','Torrential,','Slight,','Very Heavy,', 'hhh', 'Heat', '', 'Freezing', ' with ', //4
	'Light)','Moderate)','Heavy)','Torrential)','Slight)','Very Heavy)','Light;','Moderate;','Heavy;','Torrential;','Slight;','Very Heavy;','now', //5
	'Heath',''); //6
			</pre>
		</dd>
	</dl>
</p>
</div>

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>
</body>
</html>
<?php
##################################### MONTHLY REPORT GENERATION ###################################################
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
?>