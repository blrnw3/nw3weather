<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 

<?php include("phptags.php"); 
	$file = 13; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - Wind Detail</title>
	
	<meta name="description" content="Old v2 - Detailed latest wind speed and direction data and records from NW3 weather station" />
	
<?php require('chead.php'); ?>
<?php include_once("ggltrack.php") ?> 
</head>

<body>
	<!-- For non-visual user agents: -->
	<div id="top"><a href="#main-copy" class="doNotDisplay doNotPrint">Skip to main content.</a></div>

	<!-- ##### Header ##### -->
	<? require('header.php'); ?>

	<!-- ##### Left Sidebar ##### -->
	<? require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->
	<div id="main-copy">
<? require('site_status.php'); ?>

<h1>Detailed Wind Data</h1>

<table class="table1" width="40%" align="left" cellpadding="5" cellspacing="0">
<tr><td class="td13" colspan="2" align="center"><h3>Current/Latest</h3></td></tr>
<tr class="table-top">
<td class="td13" colspan="1">Measure</td><td class="td13" colspan="1">Value</td>
</tr>
<tr class="column-light">
<td class="td13" width="59%">Gust</td>
<td class="td13" width="41%"><?php echo conv($gstspd,4,1); ?></td> </tr>
<tr class="column-dark">
<td class="td13" width="59%">Speed</td>
<td class="td13" width="41%"><?php echo conv($avgspd,4,1); ?> </td> </tr>
<tr class="column-light">
<td class="td13" width="59%">10-min Speed</td>
<td class="td13" width="41%"><?php echo conv($avtenminutewind,4,1); ?> </td> </tr>
<tr class="column-dark">
<td class="td13" width="59%">30-min Speed</td>
<td class="td13" width="41%"><?php echo conv($avwindlastimediate60,4,1); ?></td> </tr>
<tr class="column-light">
<td class="td13" width="59%">1hr Speed</td>
<td class="td13" width="41%"><?php echo conv($avwindlastimediate120,4,1); ?></td> </tr>
<tr class="column-dark">
<td class="td13" width="59%">Beaufort Speed</td>
<td class="td13" width="41%">Force <?php echo $beaufortnum; ?></td> </tr>
<tr class="column-light">
<td class="td13" width="59%">10-min Bft</td>
<td class="td13" width="41%">Force <?php echo floatval($tenminuteavspeedbft); ?></td> </tr>
<tr class="column-dark">
<td class="td13" width="59%">---</td>
<td class="td13" width="41%">---</td> </tr>
<tr class="column-light">
<td class="td13" width="59%">Direction</td>
<td class="td13" width="41%"><?php echo $dirlabel; ?></td> </tr>
<tr class="column-dark">
<td class="td13" width="59%">10-min Direction</td>
<td class="td13" width="41%"><?php echo $curdir10minutelabel; ?></td> </tr>
<tr class="column-light">
<td class="td13" width="59%">30-min Direction</td>
<td class="td13" width="41%"><?php echo degname(intval($avdirlastimediate60)); ?></td> </tr>
<tr class="column-dark">
<td class="td13" width="59%">1hr Direction</td>
<td class="td13" width="41%"><?php echo degname(intval($avdirlastimediate120)); ?></td> </tr>
<tr class="column-light">
<td class="td13" width="59%">24hr Direction</td>
<td class="td13" width="41%"><?php echo degname(intval($last24houravdir)); ?></td> </tr>
<tr class="column-dark">
<td class="td13" width="59%">---</td>
<td class="td13" width="41%">---</td> </tr>
<tr class="column-light">
<td class="td13" width="59%">Day Average Speed</td>
<td class="td13" width="41%"><?php echo conv($avgspeedsincereset,4,1); ?></td> </tr>
<tr class="column-dark">
<td class="td13" width="59%">Week Average Speed</td>
<td class="td13" width="41%"><?php echo conv($avwindweek,4,1); ?></td></tr>
<tr class="column-light">
<td class="td13" width="59%">Month Average Speed</td>
<td class="td13" width="41%">&nbsp; &nbsp; &nbsp; <?php $i = intval($date_month); $currmonthwindav = array(5.2,5.1,5.2,4.9,4.7,4.4,4.3,4.0,3.9,4.1,4.6,5.1);
	if($firstday) { $monthtodateavspeed = $avgspeedsincereset; } echo conv($monthtodateavspeed,4,1),' (',conv2($monthtodateavspeed - $currmonthwindav[$i-1],4,0),')'; ?></td> </tr>
<tr class="column-dark">
<td class="td13" width="59%">Year Average Speed</td>
<td class="td13" width="41%">&nbsp; &nbsp; &nbsp; <?php echo conv($yeartodateavwind,4,1), ' (', conv2($yeartodateavwind - 4.6,4,0), ')'; ?></td> </tr>
<tr class="column-light">
<td class="td13" width="59%">Day Average Direction</td>
<td class="td13" width="41%"><?php echo degname(intval($last24houravdirday)); ?></td> </tr>
<tr class="column-dark">
<td class="td13" width="59%">Month Average Direction</td>
<td class="td13" width="41%"><?php echo degname(intval($monthtodateavdir)); ?></td> </tr>
</table>

<table class="table1" width="56%" align="center" cellpadding="5" cellspacing="0">
<tr><td class="td13" colspan="3" align="center"><h3>Current Day/Month/Year Records</h3></td></tr>
<tr class="table-top">
<td class="td13" colspan="1">Measure</td><td class="td13" colspan="1">Value</td><td class="td13" colspan="1">Time/Date</td>
</tr>
<tr class="column-light">
<td class="td13" width="45%">Max Gust Last Minute</td>
<td class="td13" width="25%"><?php echo conv($onemingustwind,4,1); ?></td>
<td class="td13" width="30%"><?php echo $time; ?></td> </tr>
<tr class="column-dark">
<td class="td13" width="45%">Max Gust Last 10-mins</td>
<td class="td13" width="25%"><?php echo conv($maxgustlastimediate10,4,1); ?></td>
<td class="td13" width="30%">N/A</td> </tr>
<tr class="column-light">
<td class="td13" width="45%">Max Gust Last Hour</td>
<td class="td13" width="25%"><?php echo conv($maxgsthr,4,1); ?></td>
<td class="td13" width="30%"><?php echo $maxgsthrt; ?></td> </tr>
<tr class="column-dark">
<td class="td13" width="45%">Max Gust Today</td>
<td class="td13" width="25%"><?php echo conv($maxgst,4,1); ?></td>
<td class="td13" width="30%"><?php echo $maxgstt; ?></td> </tr>
<tr class="column-light">
<td class="td13" width="45%">Max Gust Yesterday</td>
<td class="td13" width="25%"><?php echo conv($maxgustyest,4,1); ?></td>
<td class="td13" width="30%"><?php echo $maxgustyestt; ?></td> </tr>
<tr class="column-dark">
<td class="td13" width="45%">Max Gust this Week</td>
<td class="td13" width="25%"><?php if($maxgustweek > $mrecordwindgust):
echo conv($mrecordwindgust,4,1); else: echo conv($maxgustweek,4,1); endif; ?></td>
<td class="td13" width="30%"><?php echo $maxgustweekday; ?></td> </tr>
<tr class="column-light">
<td class="td13" width="45%">Max Gust this Month</td>
<td class="td13" width="25%"><?php echo conv($mrecordwindgust,4,1); ?></td>
<td class="td13" width="30%">Day <?php echo $mrecordhighgustday; ?></td> </tr>
<tr class="column-dark">
<td class="td13" width="45%">Max Gust this Year</td>
<td class="td13" width="25%"><?php echo conv($yrecordwindgust,4,1); ?></td>
<td class="td13" width="30%"><?php echo datefull($yrecordhighgustday), ' ', monthfull($yrecordhighgustmonth); ?></td> </tr>
<tr class="column-light">
<td class="td13" width="45%">---</td>
<td class="td13" width="25%">---</td>
<td class="td13" width="30%">---</td> </tr>
<tr class="column-dark">
<td class="td13" width="45%">Max Speed Today</td>
<td class="td13" width="25%"><?php echo conv($maxavgspd,4,1); ?></td>
<td class="td13" width="30%"><?php echo $maxavgspdt; ?> </td> </tr>
<tr class="column-light">
<td class="td13" width="45%">Max Speed Yesterday</td>
<td class="td13" width="25%"><?php echo conv($maxaverageyest,4,1); ?></td>
<td class="td13" width="30%"><?php echo $maxaverageyestt; ?></td> </tr>
<tr class="column-dark">
<td class="td13" width="45%">Max Speed this Week</td>
<td class="td13" width="25%"><?php if($maxwindweek > $mrecordwindspeed):
echo conv($mrecordwindspeed,4,1); else: echo conv($maxwindweek,4,1); endif; ?></td>
<td class="td13" width="30%"><?php echo $maxwindweekday; ?></td> </tr>
<tr class="column-light">
<td class="td13" width="45%">Max Speed this Month</td>
<td class="td13" width="25%"><?php echo conv($mrecordwindspeed,4,1); ?></td>
<td class="td13" width="30%">Day <?php echo $mrecordhighavwindday; ?></td> </tr>
<tr class="column-dark">
<td class="td13" width="45%">Max Speed this Year</td>
<td class="td13" width="25%"><?php echo conv($yrecordwindspeed,4,1); ?></td>
<td class="td13" width="30%"><?php echo datefull($yrecordhighavwindday), ' ', monthfull($yrecordhighavwindmonth); ?></td> </tr>
<tr class="column-light">
<td class="td13" width="45%">---<span style="color:white"> hi</span></td>
<td class="td13" width="25%">---</td>
<td class="td13" width="30%">---</td> </tr>
<tr class="column-dark">
<td class="td13" width="45%">Max 10-min Gust Last 12hrs</td>
<td class="td13" width="25%"><?php echo conv($highavtenminutewind,4,1); ?></td>
<td class="td13" width="30%">N/A</td> </tr>
<tr class="column-light">
<td class="td13" width="45%">Max 10-min Gust Last 24hrs</td>
<td class="td13" width="25%"><?php echo conv($highavtenminutewind24,4,1); ?></td>
<td class="td13" width="30%">N/A</td> </tr>
</table>

<br /><br /><br /><br /><br /><br />

<table class="table1" width="70%" align="center" cellpadding="5" cellspacing="0">
<tr><td class="td13" colspan="3" align="center"><h3>All-time Records</h3></td></tr>
<tr class="table-top">
<td class="td13" colspan="1">Measure</td><td class="td13" colspan="1">Value</td><td class="td13" colspan="1">Time/Date</td></tr>
<tr class="column-light">
<td class="td13" width="35%">Highest Wind Gust</td>
<td class="td13" width="30%"><?php echo conv($recordwindgust,4,1); ?></td>
<td class="td13" width="35%"><?php echo datefull($recordhighgustday), ' ', monthfull($recordhighgustmonth), ' ', $recordhighgustyear; ?></td> </tr>
<tr class="column-dark">
<td class="td13" width="35%">Highest Wind Speed</td>
<td class="td13" width="30%"><?php echo conv($recordwindspeed,4,1); ?></td>
<td class="td13" width="35%"><?php echo datefull($recordhighavwindday), ' ', monthfull($recordhighavwindmonth), ' ', $recordhighavwindyear; ?></td> </tr>
<tr class="column-light"> <?php
function getdata ($filename) {
	$rawdata = array();
	$fd = @fopen($filename,'r');
	while ( !feof($fd) ) { 
		$gotdat = trim ( fgets($fd) );
		$foundline = preg_split("/[\n\r\t, ]+/", $gotdat );
		$date = explode('/',$foundline[0]);
		$rawdata[intval((mktime(0,0,0,$date[1],floatval($date[0]),$date[2])-mktime(0,0,0,2,1,2009))/(24*3600))] = $foundline;
	}
	fclose($fd);
	return($rawdata);
}
	$test = getdata('dailydatalog.txt');
	$minw = 100; $maxw = -100;
	for ($i = 0; $i < intval((mktime(0,0,0,$date_month,$date_day,$date_year)-mktime(0,0,0,2,1,2009))/(24*3600)); $i++) {
		if ($test[$i][3] != "" && $i > 550) {
			if ($minw > $test[$i][3]): $minw = $test[$i][3]; $mintw = $test[$i][0]; endif;
			if ($maxw < $test[$i][3]): $maxw = $test[$i][3]; $maxtw = $test[$i][0]; endif;
		}
	}
	$mintwa = explode("/", $mintw);
	$maxtwa = explode("/", $maxtw);
	echo '<td class="td13" width="35%">Calmest Day (24h mean)</td>
<td class="td13" width="30%">', conv($minw*1.1508,4,1), '</td>
<td class="td13" width="35%">', datefull($mintwa[0]), ' ',  monthfull($mintwa[1]), ' ',  $mintwa[2], '</td> </tr>
<tr class="column-dark">
<td class="td13" width="35%">Windiest Day (24h mean)</td>
<td class="td13" width="30%">', conv($maxw*1.1508,4,1), '</td>
<td class="td13" width="35%">', datefull($maxtwa[0]), ' ',  monthfull($maxtwa[1]), ' ',  $maxtwa[2]; ?></td> </tr>
<tr class="column-light">
<td class="td13" width="35%">Calmest Month</td>
<td class="td13" width="30%"><?php $year = date("Y"); $end = 1200;
$years = 1 + ($year - 2009); $dd = intval($date_day); $avetempMaveVmax = 0; $avetempMaveVmin = 99;
$minm = 100; $maxm = -100; $maxtempDV = 0; $mintempDV = 0; $avetempDVmax = 0; $avetempDVmin = 99; $avetempMVmax = 0; $avetempMVmin = 99;
for($y = 0; $y < $years; $y++) {
	$yx = $year - $y; $end = 1200;
	for($m = 0; $m < 12; $m++) {
		$filename = date('F', mktime(0,0,0,$m+1)) . $yx . ".htm";
		if(file_exists($filename) && !(($yx == 2009 && $m < 7) || ($yx == 2010 && $m > 2 && $m < 7))) {
			$arr = file($filename);
			for ($i = 500; $i <1200; $i++) {
				if(strpos($arr[$i],"remes for the") > 0) { $line = $arr[$i+7]; }
			}
			$arr2 = explode(" ", $line);
			if ($minm > floatval($arr2[10]) && floatval($arr2[10]) > 1) { $minm = floatval($arr2[10]); $minmt = $m+1; $minmtY = $yx; }
			if ($maxm < floatval($arr2[10])) { $maxm = floatval($arr2[10]); $maxmt = $m+1; $maxmtY = $yx; }
		}
	}
	$dm = $date_month; $filenm = date('F', mktime(0,0,0,$dm)) . $yx . ".htm";
	if(file_exists($filenm) && !(($yx == 2009 && $dm < 7) || ($yx == 2010 && $dm > 2 && $dm < 7))) {
		$data = file($filenm);
		for ($i = 0; $i < $end; $i++) {
			if(strpos($data[$i],"remes for the month") > 0) { $end = $i; }
			if(strpos($data[$i],"remes for day") > 0) { $daya = explode(" ", $data[$i]); $a = intval(substr($daya[7],1,2)); }
			if(strpos($data[$i],"aximum win") > 0) { $tmaxa = explode(" ", $data[$i]); $tmaxv[$a] = floatval($tmaxa[10]); }
			if(strpos($data[$i],"aximum gus") > 0) { $tmina = explode(" ", $data[$i]); $tminv[$a] = floatval($tmina[10]); }
			if(strpos($data[$i],"verage win") > 0) { $tavea = explode(" ", $data[$i]); $tavev[$a] = floatval($tavea[10]); }
			if ($tavev[$a] > $avetempMVmax) { $avetempMVmax = $tavev[$a]; $avetempMYmax = $yx; $avetempMDmax = $a; }
			if ($tavev[$a] < $avetempMVmin && $tavev[$a] > 0.1) { $avetempMVmin = $tavev[$a]; $avetempMYmin = $yx; $avetempMDmin = $a; }
			if ($tminv[$a] > $maxgustMV) { $maxgustMV = $tminv[$a]; $maxgustMY = $yx; $maxgustMD = $a; }
			if ($tmaxv[$a] > $maxwindMV) { $maxwindMV = $tmaxv[$a]; $maxwindMY = $yx; $maxwindMD = $a; }
		}
		$tavevM[$y] = array_sum($tavev)/$a;
		if ($tavevM[$y] > $avetempMaveVmax && $y != 0) { $avetempMaveVmax = $tavevM[$y]; $avetempMaveYmax = $yx; }
		if ($tavevM[$y] < $avetempMaveVmin) { $avetempMaveVmin = $tavevM[$y]; $avetempMaveYmin = $yx; }
		if ($tmaxv[$dd] > $maxtempDV) { $maxtempDV = $tmaxv[$dd]; $maxtempDY = $yx; }
		if ($tminv[$dd] > $mintempDV) { $mintempDV = $tminv[$dd]; $mintempDY = $yx; }
		if ($tavev[$dd] > $avetempDVmax) { $avetempDVmax = $tavev[$dd]; $avetempDYmax = $yx; }
		if ($tavev[$dd] < $avetempDVmin && $tavev[$dd] > 0.1) { $avetempDVmin = $tavev[$dd]; $avetempDYmin = $yx; }
	}
}
echo conv($minm,4,1), '</td>
<td class="td13" width="35%">', monthfull($minmt), ' ', $minmtY, '</td> </tr>
<tr class="column-dark">
<td class="td13" width="35%">Windiest Month</td>
<td class="td13" width="30%">', conv($maxm,4,1), '</td>
<td class="td13" width="35%">', monthfull($maxmt), ' ', $maxmtY; ?></td> </tr>
<tr class="column-light">
<td class="td13" width="35%">---</td>
<td class="td13" width="30%">---</td>
<td class="td13" width="35%">---</td> </tr>
<tr class="column-dark">
<td class="td13" width="35%">Highest <?php echo $monthname; ?> Gust</td>
<td class="td13" width="30%"><?php echo conv(max($maxgustMV,$maxgst),4,1); ?></td>
<td class="td13" width="35%"><?php if($maxgst > $maxgustMV) { echo 'Today'; } else { echo 'Day ', $maxgustMD, ', ', $maxgustMY; } ?></td> </tr>
<tr class="column-light">
<td class="td13" width="35%">Highest <?php echo $monthname; ?> Speed</td>
<td class="td13" width="30%"><?php echo conv(max($maxwindMV,$maxavgspd),4,1); ?></td>
<td class="td13" width="35%"><?php if($maxavgspd > $maxwindMV) { echo 'Today'; } else { echo 'Day ', $maxwindMD, ', ', $maxwindMY; } ?></td> </tr>
<tr class="column-dark">
<td class="td13" width="35%">Calmest <?php echo $monthname; ?> Day</td>
<td class="td13" width="30%"><?php echo conv(min($avetempMVmin,$avgspeedsincereset),4,1); ?></td>
<td class="td13" width="35%"><?php if($avetempMVmin < $avgspeedsincereset) { echo 'Day ', $avetempMDmin, ', ',$avetempMYmin ; } else { echo 'Today'; } ?></td> </tr>
<tr class="column-light">
<td class="td13" width="35%">Windiest <?php echo $monthname; ?> Day</td>
<td class="td13" width="30%"><?php echo conv(max($avetempMVmax,$avgspeedsincereset),4,1); ?></td>
<td class="td13" width="35%"><?php if($avetempMVmax > $avgspeedsincereset) { echo 'Day ', $avetempMDmax, ', ',$avetempMYmax ; } else { echo 'Today'; } ?></td> </tr>
<tr class="column-dark">
<td class="td13" width="35%">Calmest <?php echo $monthname; ?></td>
<td class="td13" width="30%"><?php if($monthtodateavspeed < $avetempMaveVmin) { $avetempMaveVmin = $monthtodateavspeed; $avetempMaveYmin = date('Y'); } echo conv($avetempMaveVmin,4,1); ?></td>
<td class="td13" width="35%"><?php echo $avetempMaveYmin ?></td> </tr>
<tr class="column-light">
<td class="td13" width="35%">Windiest <?php echo $monthname; ?></td>
<td class="td13" width="30%"><?php if($monthtodateavspeed > $avetempMaveVmax) { $avetempMaveVmax = $monthtodateavspeed; $avetempMaveYmax = date('Y'); } echo conv($avetempMaveVmax,4,1); ?></td>
<td class="td13" width="35%"><?php echo $avetempMaveYmax; ?></td> </tr>
<tr class="column-dark">
<td class="td13" width="35%">---</td>
<td class="td13" width="30%">---</td>
<td class="td13" width="35%">---</td> </tr>
<tr class="column-light">
<td class="td13" width="35%">Highest <?php echo datefull($date_day), ' ', monthfull($date_month); ?> Gust</td>
<td class="td13" width="30%"><?php echo conv(max($mintempDV,$maxgst),4,1); ?></td>
<td class="td13" width="35%"><?php if($mintempDV > $maxgst) { echo $mintempDY; } else { echo 'Today'; } ?></td> </tr>
<tr class="column-dark">
<td class="td13" width="35%">Highest <?php echo datefull($date_day), ' ', monthfull($date_month); ?> Speed</td>
<td class="td13" width="30%"><?php echo conv(max($maxtempDV,$maxavgspd),4,1); ?></td>
<td class="td13" width="35%"><?php if($maxtempDV > $maxavgspd) { echo $maxtempDY; } else { echo 'Today'; } ?></td> </tr>
<tr class="column-light">
<td class="td13" width="35%">Calmest <?php echo datefull($date_day), ' ', monthfull($date_month); ?></td>
<td class="td13" width="30%"><?php echo conv(min($avetempDVmin,$avgspeedsincereset),4,1); ?></td>
<td class="td13" width="35%"><?php if($avetempDVmin < $avgspeedsincereset) { echo $avetempDYmin; } else { echo 'Today'; } ?></td> </tr>
<tr class="column-dark">
<td class="td13" width="35%">Windiest <?php echo datefull($date_day), ' ', monthfull($date_month); ?></td>
<td class="td13" width="30%"><?php echo conv(max($avetempDVmax,$avgspeedsincereset),4,1); ?></td>
<td class="td13" width="35%"><?php if($avetempDVmax > $avgspeedsincereset) { echo $avetempDYmax; } else { echo 'Today'; } ?></td> </tr>
</table>

<br />
<p align="center">
<b>Note 1:</b> Valid wind records began in August 2009.<br />
<b>Note 2:</b> Figures in brackets refer to departure from <a href="wxaverages.php" title="Long-term NW3 climate averages">average conditions</a>. </p>

<p>
<b>A note on "Speed" vs. "Gust":</b>
"Speed" is the windspeed sampled over a one minute period,
"Gust" is the windspeed sampled over a 14 second period.</p>

<hr />
<table cellpadding="5" cellspacing="5" align="center">
<tr>
<td class="td-center"><h4><?php echo date("F",mktime(0,0,0,$date_month,$date_day-1,$date_year)); ?> Windrose</h4></td>
<td width="10%"></td><td class="td-center"><h4><?php echo date("Y",mktime(0,0,0,$date_month,$date_day-1,$date_year)); ?> Windrose</h4></td></tr>
<tr><td class="td-center"><img src="/<?php if($firstday) { echo date("Yn",mktime(0,0,0,$date_month,$date_day-1,$date_year)); } ?>windrose.gif"
	alt="windrose month" title="Current month-to-date windrose" /></td>
<td width="10%"></td>
<td class="td-center"><img src="/windrose<?php if(date('n') > 1) { echo 'year'; } ?>.gif" alt="windrose year" title="Current year-to-date windrose" /></td></tr>
<tr><td colspan="3"></td></tr>
<tr>
<td colspan="3" class="td-center"><h4>Wind Energy</h4></td></tr>
<tr><td colspan="3" class="td-center"><img src="energy.gif" height="472" width="707" alt="Wind Energy" title="Wind Hours/Energy for today, and this month and year" /></td></tr>
<tr><td colspan="3" class="td-center"><i>This shows the number of hours that each wind speed has been reached.<br /> 
For generating energy with a turbine, an average of >4m/s is generally required for local use, and >6m/s for grid use,<br />
 so this location is unlikely to be suitable for either. </i></td></tr>
</table>

<p align="center"><b>NB: </b>Daily wind speed maxima &amp; averages for the current year (and all other years on record) can be viewed
<a href="wxhist13.php" title="<?php echo $year; ?> windspeeds"> here</a></p> 
  
</div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>  
 </body>
</html>