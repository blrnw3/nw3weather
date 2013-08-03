<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php 
	$file = 85; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Daily reports</title>

	<meta name="description" content="Detailed historical daily breakdown reports with graphs" />

	<?php require('chead.php'); ?>
	<?php include('ggltrack.php') ?>
</head>

<body>

	<!-- ##### Header ##### -->
	<?php require('header.php'); ?>

	<!-- ##### Left Sidebar ##### -->
	<?php require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->
	<div id="main">

<?php
$typeconv = $typeconv2 = $typeconv;

//pre-lim / day values and times
if(isset($_GET['day'])) { $dproc = intval($_GET['day']); } else { $dproc = $day_yest; }
if(isset($_GET['month'])) { $mproc = intval($_GET['month']); } else { $mproc = $mon_yest; }
if(isset($_GET['year'])) { $yproc = $_GET['year']; } else { $yproc = $yr_yest; }

if($mproc < 1 || $mproc > 12 || $yproc < 2009 || $yproc > $dyear || $dproc < 1 || $dproc > 31) { //dodgy input
	$badMessage = 'most recent';
	$dproc = $day_yest;
	$mproc = $mon_yest;
	$yproc = $yr_yest;
} else {  //check for out-of-range
	$sproc = mkdate($mproc,$dproc,$yproc);
	if($sproc >= mkdate($dmonth,$dday+1,$dyear)) {
		$badMessage = 'most recent';
		$dproc = $day_yest;
		$mproc = $mon_yest;
		$yproc = $yr_yest;
	} elseif($sproc < mkdate(2,1,2009)) {
		$badMessage = 'earliest';
		$dproc = 1;
		$mproc = 2;
		$yproc = 2009;

	}
}
$sproc = mkdate($mproc,$dproc,$yproc); // safe
//re-evaluate in case semi-dodgy date, e.g. 31st April, 30th Feb etc.
$mproc = (int)date('n', $sproc);
$dproc = (int)date('j', $sproc);

$fdat = $fullpath."dat".$yproc.".csv";
$fdatt = $fullpath."datt".$yproc.".csv";
$fdatm = $fullpath."datm".$yproc.".csv";
$dsun = file($root.'maxsun.csv');
$dtfanom = file($root.'tminmaxav.csv');
$dfdat = file($fdat);
$dfdatt = file($fdatt);
$dfdatm = file($fdatm);
$ddat = explode(',', $dfdat[date('z',$sproc)+1]);
$ddatt = explode(',', $dfdatt[date('z',$sproc)+1]);
$ddatm = explode(',', $dfdatm[date('z',$sproc)+1]);
$dtanom = explode(',', $dtfanom[date('z',$sproc)]); $dtanom[3] = $dtanom[1] - $dtanom[0];
$pastcamstamp = date("Y/Ymd", $sproc) . 'daily'; $todcond = true; $nmint = false;
if(mkdate() == $sproc) { //include today
	if(time() > mktime(0,6)) { $custom = $NOW; }
	if($dhr < 21) { $nmin = $NOW['min']['night']; } else { $nmin = ''; }
	unset($ddat,$ddatt);
	
	$ddat = array(
		$custom['min']['temp'], $custom['max']['temp'], $custom['mean']['temp'],
		$custom['min']['humi'], $custom['max']['humi'], $custom['mean']['humi'],
		$custom['min']['pres'], $custom['max']['pres'], $custom['mean']['pres'],
		$custom['mean']['wind'], $custom['max']['wind'], $custom['max']['gust'], $custom['mean']['wdir'],
		$custom['mean']['rain'], $custom['max']['rnhr'], $custom['max']['rn10'], $custom['max']['rate'],
		$custom['min']['dewp'], $custom['max']['dewp'], $custom['mean']['dewp'],
		$custom['min']['night'], $custom['max']['day'],
		$custom['max']['tchange10'], $custom['max']['tchangehr'], $custom['max']['hchangehr'],
		$custom['min']['tchange10'], $custom['min']['tchangehr'], $custom['min']['hchangehr'],
		$custom['max']['w10m'],
		$custom['min']['feel'], $custom['max']['feel'], $custom['mean']['feel'],
		$custom['misc']['frosthrs'],
		' \n'
	);

	$ddatt = array(
		$custom['timeMin']['temp'], $custom['timeMax']['temp'], '',
		$custom['timeMin']['humi'], $custom['timeMax']['humi'], '',
		$custom['timeMin']['pres'], $custom['timeMax']['pres'], '',
		'', $custom['timeMax']['wind'], $custom['timeMax']['gust'], '',
		'', $custom['timeMax']['rnhr'], $custom['timeMax']['rn10'], $custom['timeMax']['rate'],
		$custom['timeMin']['dewp'], $custom['timeMax']['dewp'], '',
		$custom['timeMin']['night'], $custom['timeMax']['day'],
		$custom['timeMax']['tchange10'], $custom['timeMax']['tchangehr'], $custom['timeMax']['hchangehr'],
		$custom['timeMin']['tchange10'], $custom['timeMin']['tchangehr'], $custom['timeMin']['hchangehr'],
		$custom['timeMax']['w10m'],
		$custom['timeMin']['feel'], $custom['timeMax']['feel'], '',
		'',
		' \n'
	);

	$todfix = '&amp;ts=' . (23-date('H')); $pastcamstamp = 'today';
	for($v = 0; $v < count($data_description); $v++) { $mcdat[$v] += $ddat[$v]/$dproc; }
	//for($v = 0; $v < 3; $v++) { $mctanom[$v] += $dtanom[$v]/$dproc; }
	$todcond = time() > mktime(0,6);
	$rec[$dyear] = $ddat; $rec = array_dswap($rec);
}
if(mkdate() == $sproc+24*3600) { if(time() > mktime(0,6)) { $todcond2 = true; } else { $todcond2 = false; } } else { $todcond2 = true; }
//Cumulative values
for($d = 0; $d < $dproc; $d++) {
	$mdat = explode(',', $dfdat[date('z',$sproc)+1-$d]); for($v = 0; $v < count($data_description); $v++) { $mcdat[$v] += $mdat[$v]/$dproc; }
	$mdatm = explode(',', $dfdatm[date('z',$sproc)+1-$d]); for($v = 0; $v < 2; $v++) { $mcdatm[$v] += $mdatm[$v]; } $mcdatm[7] += intval($mdatm[11]);
	$mtanom = explode(',', $dtfanom[date('z',$sproc)+1-$d]); $mctanom[3] = $mtanom[1] - $mtanom[0]; for($v = 0; $v < 3; $v++) { $mctanom[$v] += $mtanom[$v]/$dproc; }
	$mcsanom += $dsun[date('z',$sproc)+1-$d] / ($maxsun[$mproc-1]/$sunav[$mproc-1]); $maxsuntd += $dsun[date('z',$sproc)+1-$d];
}
$mcdat[13] *= $dproc;

//Record values
if(mkdate($mproc,$dproc,$dyear) < mkdate($dmonth,$dday,$dyear)) { $yrend = $dyear; } else { $yrend = $dyear-1; }
for($y = $temp_styr; $y <= $yrend; $y++) {
	$sprocR = mkdate($mproc,$dproc,$y);
	$dfdatR = file($fullpath."dat".$y.".csv");
	$dfdatmR = file($fullpath."datm".$y.".csv");
	$ddatR = explode(',', $dfdatR[date('z',$sprocR)+1]);
	$ddatmR = explode(',', $dfdatmR[date('z',$sprocR)+1]);
	for($v = 0; $v < count($data_description); $v++) { if($ddatR[$v] != '' && $ddatR[$v] != '-') { $rec[$v][$y] = $ddatR[$v]; } }
	for($v = 0; $v < 2; $v++) { $recm[$v][$y] = $ddatmR[$v]; if($ddatmR[$v] == 'b') { $recm[$v][$y] = 0; } }
	for($v = 0; $v < 3; $v++) { $recx[$v][$y] = $ddatR[3*$v+1]-$ddatR[3*$v]; }
	unset($dfdatR,$dfdatmR,$ddatR,$ddatmR);
}
for($v = 0; $v < count($data_description); $v++) {
	if(is_array($rec[$v])) {
		$hi[$v] = max($rec[$v]); $lo[$v] = min($rec[$v]); $av[$v] = mean($rec[$v]);
		$hiY[$v] = yrec_hlite(array_search($hi[$v],$rec[$v]), $yproc); $loY[$v] = yrec_hlite(array_search($lo[$v],$rec[$v]), $yproc);
	}
	else { $typeconv[$v] = false; $hi[$v] = $lo[$v] = 'n/a'; $hiY[$v] = $loY[$v] = ''; }
}
for($v = 0; $v < 2; $v++) {
	$him[$v] = max($recm[$v]); $lom[$v] = min($recm[$v]); $avm[$v] = mean($recm[$v]);
	$hiYm[$v] = yrec_hlite(array_search($him[$v],$recm[$v]), $yproc); $loYm[$v] = yrec_hlite(array_search($lom[$v],$recm[$v]), $yproc);
}
for($v = 0; $v < 3; $v++) {
	$hix[$v] = max($recx[$v]); $lox[$v] = min($recx[$v]); $avx[$v] = mean($recx[$v]);
	$hiYx[$v] = yrec_hlite(array_search($hix[$v],$recx[$v]), $yproc); $loYx[$v] = yrec_hlite(array_search($lox[$v],$recx[$v]), $yproc);
}

//Fix-up standard value-set
if($ddat[13] == 0.1) { $ddat[13] = 'trace'; $typeconv[13] = false; }
$ddanom[2] = ' (' . conv(($ddat[0]+$ddat[1]-$dtanom[0]-$dtanom[1])/2,1.1,0,1) . ')'; $mcanom[2] = ' (' . conv(($mcdat[0]+$mcdat[1]-$mctanom[0]-$mctanom[1])/2,1.1,0,1) . ')';
$ddanom[9] = ' (' . conv($ddat[9]-$windav[$mproc-1],4,0,1) . ')'; $mcanom[9] = ' (' . conv($mcdat[9]-$windav[$mproc-1],4,0,1) . ')';
$mcanom[13] = ' (' . round(100*$mcdat[13]/($rainav[$mproc-1]/date('t',$sproc)*$dproc)).'%)';
for($i = 0; $i < 2; $i++) {
	$ddanom[$i] = ' (' . conv($ddat[$i]-$dtanom[$i],1.1,0,1) . ')';
	$mcanom[$i] = ' (' . conv($mcdat[$i]-$mctanom[$i],1.1,0,1) . ')';
	$hianom[$i] = ' (' . conv($hi[$i]-$dtanom[$i],1.1,0,1) . ')';
	$loanom[$i] = ' (' . conv($lo[$i]-$dtanom[$i],1.1,0,1) . ')';
}
//echo 'mcdat='.$mcdat[0].'; mctanom=.'.$mctanom[0];
if(intval($ddat[12]) > 0) { $ddat[12] .= '&deg; [' . degname($ddat[12]) . ']'; }
$loY[12] = norain_fix($loY[12], $lo[12]);

//Fix-up standard 2 value-set
for($i=0;$i<2;$i++) { if($ddat[$i] != $ddat[20+$i]) { $bold[20+$i] = '* '; $boldE[20+$i] = ' *'; } }

//Fix-up extra value-set
$ddatx[0] = $ddat[1] - $ddat[0]; $mcdatx[0] = $mcdat[1] - $mcdat[0];
$ddatx[1] = $ddat[4] - $ddat[3]; $mcdatx[1] = round($mcdat[4] - $mcdat[3]);
$ddatx[2] = $ddat[7] - $ddat[6]; $mcdatx[2] = $mcdat[7] - $mcdat[6];
$ddanomx[0] = ' (' . conv($ddatx[0]-$dtanom[3],1.1,0,1) . ')'; $mcdanomx[0] = ' (' . conv($mcdat[1]-$mctanom[1]-$mcdat[0]+$mctanom[0],1.1,0,1) . ')';
$hianomx[0] = ' (' . conv($hix[0]-$dtanom[3],1.1,0,1) . ')'; $loanomx[0] = ' (' . conv($lox[0]-$dtanom[3],1.1,0,1) . ')';

//Fix-up manual value-set
// echo 'date-z + 1 = ' . (date('z',$sproc)+1) . '<br />';
$sunMax = $dsun[date('z',$sproc)];
if($ddatm[0] == 'b') { $ddatm[0] = 0; }
if($ddatm[0]/$sunMax > 0.95) { $maxsunevent = true; }
$ddatm[0] .= ' [' . acronym('Out of ' . conv($sunMax,0,0) . ' hrs possible', round($ddatm[0]/$sunMax*100) . '%',true) . ']';
if($ddatm[1] > 0 && $ddat[13] > 0.2) { $ddatm[1] .=  '<br />[Mean rain rate: '. conv($ddat[13]/$ddatm[1],2.1) . ']'; }
$mcdatm[0] = roundi($mcdatm[0]) . ' hrs ' . percent($mcdatm[0],$mcsanom) . ' ['. acronym('Of a possible '. roundi($maxsuntd),roundi(100*$mcdatm[0]/$maxsuntd),true) . '%]';
$mcdatm[1] = roundi($mcdatm[1]) . ' hrs ' . percent($mcdatm[1],$wetav[$mproc-1]*$dproc/date('t',$sproc)) . ' ['. acronym('Of a possible '. 24*$dproc,roundi(100*$mcdatm[1]/$dproc/24),true) . '%]';
$him[0] .= ' [' . acronym('Out of ' . conv($sunMax,0,0) . ' hrs possible', percent($him[0],$sunMax,0,1,0),true) . ']';
$lom[0] .= ' [' . acronym('Out of ' . conv($sunMax,0,0) . ' hrs possible', percent($lom[0],$sunMax,0,1,0),true) . ']';
$ts = $ddatm[2]; $tsl = strlen($ts); //cloud cover decode
if(strpos($ts,':')) {
	$tsa = explode(':',$ts);
	for($i = 0; $i < 2; $i++) {
		if(strlen($tsa[$i]) > 1) {
			$fs[$i] = str_split($tsa[$i]);
			$fs_f[$i] = $ccd[$fs[$i][0]] . ' ' . $ccd[$fs[$i][1]] . ' ' . $ccd[$fs[$i][2]];
		}
		else { $fs_f[$i] = $ccd[$tsa[$i]]; }
	}
	$fs_fc = 'am: ' . $fs_f[0] . '<br /> pm: ' . $fs_f[1];
}
else {
	if($tsl > 1) {
		$fs = str_split($ts);
		$fs_fc = $ccd[$fs[0]] . ' ' . $ccd[$fs[1]] . ' ' . $ccd[$fs[2]];
	}
	elseif($tsl > 0) { $fs_fc = $ccd[$ts]; }
	else { $fs_fc = 'not available'; }
}
$ddatm[2] = $fs_fc;
unset($fs);

$ts = $ddatm[3]; //Snowfall decode
if($ts != '') {
	$ec += 1; $snow = true;
	$fs = '<br />Snowfall: ';
	if($ts == 'y') { $fs .= '~' . conv($ddat[13],6); }
	elseif($ts == '0.1') { $fs .= 'trace amount'; }
	else { $fs .= '~' . conv($ts,6); }
}
$ts = $ddatm[4]; //Lying Snow decode
if($ts != '') {
	$ec += 1;
	$fs .= '<br />Lying Snow (at 09z): ';
	if($ts == '0.1') { $fs .= 'trace amount'; }
	else { $fs .= '~' . conv($ts,6); }
}
$ts = $ddatm[5]; //Hail decode
if($ts != '') {
	$ec += 1;
	$fs .= '<br />Hail: ';
	if($ts == '1') { $fs .= 'small stones'; }
	elseif($ts == '2') { $fs .= 'medium-size stones'; }
	else { $fs .= 'large stones'; }
}
$ts = $ddatm[6]; //Thunder decode
if($ts != '') {
	$ec += 1;
	$fs .= '<br />';
	if($ts == 1) { $fs .= 'Thunder'; }
	elseif($ts == '2') { $fs .= 'Light Thunderstorm'; }
	elseif($ts == '3') { $fs .= 'Moderate Thunderstorm'; }
	else { $fs .= 'Severe Thunderstorm'; }
}
$ts = $ddatm[7]; //Fog decode
if($ts != '') {
	$ec += 1;
	$fs .= '<br />Dense Fog';
}
if($ddat[20] < 0) { //AF decode
	$ec += 1;
	$fs .= '<br />Air Frost';
}
if($maxsunevent) { //Max Sun decode
	$ec += 1;
	$fs .= '<br />Maximum possible sunshine';
}

if($ec < 1) { $ddatm[3] = 'None'; } else { $ddatm[3] = substr($fs,6); }
if($snow) { $data_description[13] .= ' / Snow-melt approx.'; }

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
$ddatm[4] = str_replace($finds,$repls,$ddatm[8]);
$ddatm[5] = str_replace($finds,$repls,$ddatm[9]);
$finds2 = array('"','?'); $repls2 = array('',',');
$ddatm[6] = str_replace($finds2,$repls2,$ddatm[10]);
if($ddatm[11] == 1) { $ddatm[7] = 'Yes - observations may be unreliable'; } else { $ddatm[7] = 'No'; }
if(strlen($ddatm[6]) < 3) { $ddatm[6] = 'None known'; }

//Other touch-up work
$mcdat[12] = $mcdat[14] = $mcdat[15] = $mcdat[16] = $mcdatm[2] = $mcdatm[3] = $mcdatm[4] = $mcdatm[5] = $mcdatm[6] = $hi[12] = $hiY[12] = $lo[12] = $loY[12] = '';
$lo[13] = $lo[14] = $lo[15] = $lo[16] = $loY[13] = $loY[14] = $loY[15] = $loY[16] = '';
?>

<h1>Daily Report for <?php echo date('jS F Y', $sproc); ?></h1>
<?php
//if($mproc == 10 && $yproc == 2009) echo '<b>Special note</b>: Data is suspect for this month due to partial data loss';
//if($mproc < 8 && $mproc != 1 && $yproc == 2009) echo '<b>Special note</b>: Wind data not valid for this month (valid records began in Aug 2009)';
//if($mproc < 8 && $mproc > 3 && $yproc == 2010) echo '<b>Special note</b>: Wind data not valid for this month (suspended 17th April - 28th July; replaced by METAR data from Heathrow)';

if($badMessage) { echo "<br /><b>Bad date specified. Defaulted to $badMessage report available</b>"; }
if(mkdate() == $sproc && $todcond) { echo 'Based on data up to ', date('H:i',filemtime($root.'logfiles/daily/todaylog.txt')), '<br />'; }

$prevs = $sproc - 3600*24; $nexts = $sproc + 3600*24;
$prevd = date('j', $prevs); $prevm = date('n', $prevs); $prevy = date('Y', $prevs);
$nextd = date('j', $nexts); $nextm = date('n', $nexts); $nexty = date('Y', $nexts);
?>

<table width="800">
<tr>
<td align="left">
<?php
if($sproc > mkdate(2,1,2009)) {
	echo '<a href="wxhistday.php?year=', $prevy, '&amp;month=', $prevm, '&amp;day=', $prevd, '" title="View previous day&#39;s report">'; $c1 = true;
} ?>
&lt;&lt;Previous Day<?php if($c1) { echo '</a>'; } ?></td>
<td align="center"><form method="get" action="">
	<?php dateFormMaker($yproc, $mproc, $dproc); ?>
<input type="submit" value="View Report" />
</form>
<a href="wxhistday.php" title="Return to most recent day's report">Reset</a>
</td><td align="right">
<?php
if($sproc < mkdate($dmonth,$dday,$dyear) && $todcond2) {
	echo '<a href="wxhistday.php?year=', $nexty, '&amp;month=', $nextm, '&amp;day=', $nextd, '" title="View next day&#39;s report">'; $c2 = true;
} ?>
Next Day&gt;&gt;<?php if($c2) { echo '</a>'; } ?></td>
</tr></table>

<?php
$w1 = 22; $w2 = 23; $w3 = 8; $w4 = 17; $w5 = 15; $w6 = 15;
if($todcond) {
	 //standard
	echo '<table class="table1" width="98%" cellpadding="2" cellspacing="0">
		<tr class="table-top"> <td class="td4" width="',$w1,'%">Measure</td>	<td class="td4" width="',$w2,'%">Value (anomaly)</td> <td class="td4" width="',$w3,'">Time</td>
		<td class="td4" width="',$w4,'%">Month cumul.</td> <td class="td4" width="',$w5,'%">Record High</td> <td class="td4" width="',$w6,'%">Record Low</td> </tr>';

	for($i = 0; $i < 20; $i++) {
		if($ddat[$i] == '' || $ddat[$i] == '-') { $ddat[$i] = 'n/a'; $typeconv[$i] = false; }
		if($i % 2 == 0) { $style = 'light'; } else { $style = 'dark'; }
		if($ddatt[$i] < 1 && $ddatt[$i] > 0) { $ddatt[$i] = decimal_timefix($ddatt[$i]); } elseif(!strpos($ddatt[$i],'*')) { $ddatt[$i] = timeformat($ddatt[$i]); }
		echo '<tr class="row', $style, '">
			<td width="',$w1,'%" class="td', $data_num[$i]+10, 'C">', $data_description[$i], '</td>
			<td width="',$w2,'%" class="td', $data_num[$i]+10, 'C" style="font-size:105%">', conv($ddat[$i],$typeconv[$i]), $ddanom[$i], '</td>
			<td width="',$w3,'%" class="td', $data_num[$i]+10, 'C">', str_replace('*','',$ddatt[$i]), '</td>
			<td width="',$w4,'%" class="td', $data_num[$i]+10, 'C">', conv($mcdat[$i],$typeconv2[$i]), $mcanom[$i], '</td>
			<td width="',$w5,'%" class="td', $data_num[$i]+10, 'C" style="font-size:85%">', conv($hi[$i],$typeconv[$i]), $hianom[$i], $hiY[$i], '</td>
			<td width="',$w6,'%" class="td', $data_num[$i]+10, 'C" style="font-size:85%">', conv($lo[$i],$typeconv[$i]), $loanom[$i], $loY[$i], '</td>
			</tr>';
	}

	 //standard 2
	echo '	<tr class="table-top"> <td class="td4" width="',$w1,'%">Measure</td> <td class="td4" width="',$w2,'%">Value</td> <td class="td4" width="',$w3,'">Time</td>
		<td class="td4" width="',$w4,'%">Month cumul.</td> <td class="td4" width="',$w5,'%">Record High</td> <td class="td4" width="',$w6,'%">Record Low</td> </tr>';

	for($i = 20; $i < count($data_description); $i++) {
		if($ddat[$i] == '' || $ddat[$i] == '-') { $ddat[$i] = 'n/a'; $typeconv[$i] = false; }
		if($i % 2 == 0) { $style = 'light'; } else { $style = 'dark'; }
		if($ddatt[$i] < 1 && $ddatt[$i] > 0) { $ddatt[$i] = decimal_timefix($ddatt[$i]); } elseif(!strpos($ddatt[20],'*')) { $ddatt[$i] = timeformat($ddatt[$i]); } else { $nmint = true; }

		echo '<tr class="row', $style, '">
			<td width="',$w1,'%" class="td', $data_num[$i]+10, 'C">', $data_description[$i], '</td>
			<td width="',$w2,'%" class="td', $data_num[$i]+10, 'C" style="font-size:105%">', $bold[$i], conv($ddat[$i],$typeconv[$i]), $ddanom[$i], $boldE[$i], '</td>
			<td width="',$w3,'%" class="td', $data_num[$i]+10, 'C">', $ddatt[$i], '</td>
			<td width="',$w4,'%" class="td', $data_num[$i]+10, 'C">', conv($mcdat[$i],$typeconv2[$i]), '</td>
			<td width="',$w5,'%" class="td', $data_num[$i]+10, 'C" style="font-size:85%">', conv($hi[$i],$typeconv[$i]), $hiY[$i], '</td>
			<td width="',$w6,'%" class="td', $data_num[$i]+10, 'C" style="font-size:85%">', conv($lo[$i],$typeconv[$i]), $loY[$i], '</td>
			</tr>';
	}

	//extra
	echo '<tr class="table-top"> <td class="td4" width="',$w1,'%">Measure</td> <td class="td4" colspan="2" width="',$w2+$w3,'%">Value (anomaly)</td>
		<td class="td4" width="',$w4,'%">Month cumul.</td> <td class="td4" width="',$w5,'%">Record High</td> <td class="td4" width="',$w6,'%">Record Low</td> </tr>';

	for($i = 0; $i < 3; $i++) {
		if($i % 2 == 0) { $style = 'light'; } else { $style = 'dark'; }
		echo '<tr class="row', $style, '">
			<td width="',$w1,'%" class="td', $data_numx[$i]+10, 'C">', $data_descriptionx[$i], '</td>
			<td width="',$w2+$w3,'%" colspan="2" class="td', $data_numx[$i]+10, 'C" style="font-size:105%">', conv($ddatx[$i],$typeconvx[$i]), $ddanomx[$i], '</td>
			<td width="',$w4,'%" class="td', $data_numx[$i]+10, 'C">', conv($mcdatx[$i],$typeconvx[$i]), $mcdanomx[$i], '</td>
			<td width="',$w5,'%" class="td', $data_numx[$i]+10, 'C" style="font-size:85%">', conv($hix[$i],$typeconvx[$i]), $hianomx[$i], $hiYx[$i], '</td>
			<td width="',$w5,'%" class="td', $data_numx[$i]+10, 'C" style="font-size:85%">', conv($lox[$i],$typeconvx[$i]), $loanomx[$i], $loYx[$i], '</td>
			</tr>';
	}

	 //manual
	if(($ddatm[2] == 'Unknown' && $ddatm[4] == 'blr') || mkdate() == $sproc) {
		echo '<tr><td colspan="6" class="td4">Extra observations pending manual input by site administrator (typically done within 24hrs unless I am away);
			until then, all above data is subject to quality control.</td></tr></table>';
	}
	else {
		echo '<tr class="table-top"> <td class="td4" width="',$w1,'%">Measure</td> <td class="td4" colspan="2" width="',$w2+$w3,'%">Value [% of max]</td>
		<td class="td4" width="',$w4,'%">Month cumul.</td> <td class="td4" width="',$w5,'%">Record High</td> <td class="td4" width="',$w6,'%">Record Low</td> </tr>';

		for($i = 0; $i < count($data_m_description); $i++) {
			if($i % 2 == 0) { $style = 'light'; } else { $style = 'dark'; }
			if($i > 1) { $cspm = 5; } else { $cspm = 2; }

			echo '<tr class="row', $style, '">
				<td width="',$w1,'%" class="td', $data_m_num[$i]+10, 'C">', $data_m_description[$i], '</td>
				<td width="',$w2+$w3,'%" colspan="', $cspm, '" class="td', $data_m_num[$i]+10, 'C">', $ddatm[$i], '</td>';
			if($i < 2) {
				echo '<td width="',$w4,'%" class="td', $data_m_num[$i]+10, 'C">', $mcdatm[$i], '</td>
					<td width="',$w5,'%" class="td', $data_m_num[$i]+10, 'C" style="font-size:85%">', $him[$i], $hiYm[$i], '</td>
					<td width="',$w6,'%" class="td', $data_m_num[$i]+10, 'C" style="font-size:85%">', $lom[$i], $loYm[$i], '</td>';
			}
			echo '</tr>';
		}
		echo '</table>
			<dl>
				<dt>Notes</dt>
				<dd>Times of extremes are computed using the midpoint time of the extremum (if the value persisted for >1 minute). <br />
				Cumulative anomalies are reported according to the expected value for the month-to-date rather than month-end. <br />
				Sun hrs, Wet hrs, Cloud cover and Events are based on manual equipment/observations; their reliablity is questionable and they
				are provided for interest only.</dd>
			</dl>';
	}
	if($nmint) { echo '* Recorded in previous 24hr period<br />
		'; }
	//graphs
	echo '<a href="wxhistmonth.php?month=',$mproc,'&amp;year=',$yproc,'"
		title="Monthly report for ', monthfull($mproc), ' ', $yproc,'">View monthly summary</a> <br />
		<h2>Daily Graph of Conditions</h2>
	';

	//graphs
	$target_st = date('Y', $sproc) . '/stitchedmaingraph_';
	$target_en = date('Ymd', $sproc) .'.png';
	$graphres = $target_st .''. $target_en;
	if(file_exists(ROOT. $graphres) && $ukUnits) {
		echo '<img src="/'. $graphres .'" alt="day graph" '. GRAPH_DIMS_LARGE .' />
			';
	} else {
		echo '
			<img src="/graphday.php?date=', date("Ymd", $sproc), $todfix, '" alt="daygraph" />
			<img src="/graphday2.php?date=', date("Ymd", $sproc), $todfix, '" alt="daygraph 2" />
			<img src="/graphdayA.php?y=200&amp;type=wdir&amp;date=', date("Ymd", $sproc), $todfix, '" alt="daygraph-wdir" /> <br />
		';

	}
	echo '<h2>Webcam Summary of Cloud Conditions</h2>
		';

	//Webcam
	if($sproc < mkdate(6,27,2012)) { $endtag = 'gif'; } else { $endtag = 'jpg'; }
	if(file_exists($root. $pastcamstamp. 'webcam.'.$endtag)) { echo '<img src="/', $pastcamstamp, 'webcam.',$endtag,'" alt="daycamsum" />'; }
	else { echo '<br />Webcam summary not available for this day'; }
	if($endtag == 'gif' && file_exists($root. $pastcamstamp. 'webcam2.gif')) { echo '<img src="/', $pastcamstamp, 'webcam.gif" alt="daycamsum2" />'; }
}
else { echo 'Daily breakdown not available until 09:07, when a partial report will be generateable.'; }
?>
</div><!-- end main -->

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>
</body>
</html>
<?php
function yrec_hlite($val, $yr) {
	if($val == $yr) { $col = '#FF8989'; } else { $col = 'gray'; }
	return ', <span style="color:'.$col.'">'.$val.'</span>';
}
?>