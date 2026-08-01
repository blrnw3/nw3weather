<?php require('unit-select.php');
		include("phptags.php");
		$file = 14;	?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - Temperature Detail</title>

	<meta name="description" content="Old v2 - Detailed latest/current temperature data and records from NW3 weather station.
	Find warmest and coldest, view wind chill, humidex etc." />
	
	<? require('chead.php'); ?>
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

<h1>Detailed Temperature Data</h1>

<table class="table1" width="40%" align="left" cellpadding="5" cellspacing="0">
<tr><td class="td14" colspan="2" align="center"><h3>Current/Latest</h3></td></tr>
<tr class="table-top">
<td class="td14" colspan="1">Measure</td><td class="td14" colspan="1">Value</td>
</tr>

<tr class="column-light">
<td class="td14" width="57%">Temperature</td>
<td class="td14" width="43%"><?php echo conv($temp,1,1); ?></td></tr>
<tr class="column-dark">
<td class="td14" width="57%">Temperature Trend</td>
<td class="td14" width="43%"><?php echo conv2($tempchangehour,1,1); ?> /h</td> </tr>
<tr class="column-light">
<td class="td14" width="57%">Feels-like</td>
<td class="td14" width="43%"><?php if(($temp < 27 && $heati >= $temp) || ($temp >= 27 && $heati < $temp) && $humidex > $temp): echo  conv($humidex,1,1);
 elseif(($humidex < $temp && $windch >= $temp) || ($temp > 14 && $feelslikedp < $temp)): echo conv($temp,1,1); else: echo conv($feelslikedp,1,1); endif; ?></td></tr>
<tr class="row-dark8">
<td class="td14" width="57%">Windchill</td>
<td class="td14" width="43%"><?php if($windch+0.1 > $temp): echo "n/a"; else: echo conv($windch,1,1); endif; ?></td></tr>
<tr class="row-light9">
<td class="td14" width="57%">Heat Index</td>
<td class="td14" width="43%"><?php if($heati-0.1 < $temp || $temp < 27): echo "n/a"; else: echo conv($heati,1,1); endif; ?></td></tr>
<tr class="column-dark">
<td class="td14" width="57%">Humidex</td>
<td class="td14" width="43%"><?php if($humidex-0.1 < $temp): echo "n/a"; else: echo conv($humidex,1,1); endif; ?></td></tr>
<tr class="column-light">
<td class="td14" width="57%">---</td>
<td class="td14" width="43%">---</td></tr>
<tr class="column-dark">
<td class="td14" width="57%">Day Average</td>
<td class="td14" width="43%"><?php echo conv($avtempsincemidnight,1,1); ?></td></tr>
<tr class="column-light">
<td class="td14" width="57%">24hr Average</td>
<td class="td14" width="43%"><?php echo conv($last24houravtemp,1,1); ?></td></tr>
<tr class="column-dark">
<td class="td14" width="57%">Yesterday Average</td>
<td class="td14" width="43%"><?php echo conv($yesterdayavtemp,1,1); ?></td></tr>
<tr class="column-light">
<td class="td14" width="57%">Week Average</td>
<td class="td14" width="43%"><?php echo conv($avtempweek,1,1); ?></td></tr>
<tr class="column-dark">
<td class="td14" width="57%">Month Average</td>
<td class="td14" width="43%">&nbsp; &nbsp; <?php if(date('j') == 1) { $monthtodateavtemp = $avtempsincemidnight; }
	echo conv($monthtodateavtemp,1,1), ' (', conv2($monthtodateavtemp - $currentmonthaveragetemp,1,1), ')'; ?></td></tr>
<tr class="column-light">
<td class="td14" width="57%">31-Day Average</td>
<td class="td14" width="43%"><?php echo conv($monthtodateavtemp31,1,1); ?></td></tr>
<?php $report2 = date("FY", mktime(0,0,0,$date_month,$date_day,$date_year)).'.htm'; $minmax = getmaxmin($report2);
$anoml = array(2.4,2.2,3.6,5.0,8.3,11.1,13.4,13.2,11.1,8.8,5.0,3.2); $anomh = array(7.0,7.6,10.4,12.8,16.7,20.2,22.4,22.2,18.7,14.6,10.2,7.8); ?>
<tr class="column-dark">
<td class="td14" width="57%">Month Average Daily Min</td>
<td class="td14" width="43%"><?php if(date('j') == 1) { echo 'n/a'; }
	else { echo '&nbsp; &nbsp; ', conv($minmax[0],1,1), ' (', conv2($minmax[0] - $anoml[intval($date_month)-1],1,1), ')'; } ?></td></tr>
<tr class="column-light">
<td class="td14" width="57%">Month Average Daily Max</td>
<td class="td14" width="43%"><?php if(date('j') == 1) { echo 'n/a'; }
	else { echo '&nbsp; &nbsp; ', conv($minmax[1],1,1), ' (', conv2($minmax[1] - $anomh[intval($date_month)-1],1,1), ')'; } ?></td></tr>
<tr class="column-dark">
<td class="td14" width="57%">---</td>
<td class="td14" width="43%">---</td></tr>
<tr class="column-light">
<td class="td14" width="57%">Air Frost Hours Today</td>
<td class="td14" width="43%"><?php echo $hrsfrostmidnight; ?></td></tr>
<tr class="column-dark">
<td class="td14" width="57%">Monthly Air Frosts</td>
<td class="td14" width="43%"><?php echo $daysTminL0C; ?></td></tr>
<tr class="column-light">
<td class="td14" width="57%">Monthly Ice Days</td>
<td class="td14" width="43%"><?php echo ($daysTmaxGm5C - $daysTmaxG0C); ?></td></tr>
<tr class="column-dark">
<td class="td14" width="57%">Annual Air Frosts</td>
<td class="td14" width="43%"><?php echo $daysTminyearL0C; ?></td></tr>
<tr class="column-light">
<td class="td14" width="57%">Annual Ice Days</td>
<td class="td14" width="43%"><?php echo ($daysTmaxyearGm5C - $daysTmaxyearG0C); ?></td>
</tr>
</table>

<table class="table1" width="56%" align="center" cellpadding="5" cellspacing="0">
<tr><td class="td14" colspan="3" align="center"><h3>Current Day/Month/Year Records</h3></td></tr>
<tr class="table-top">
<td class="td14" colspan="1">Measure</td><td class="td14" colspan="1">Value</td><td class="td14" colspan="1">Time/Date</td>
</tr>
<tr class="row-light1">
<td class="td14" width="40%">Min Temp Today</td>
<td class="td14" width="27%"><?php echo conv($mintemp,1,1); ?></td>
<td class="td14" width="33%"><?php echo $mintempt; ?></td> </tr>
<tr class="row-dark2">
<td class="td14" width="40%">Max Temp Today</td>
<td class="td14" width="27%"><?php echo conv($maxtemp,1,1); ?></td>
<td class="td14" width="33%"><?php echo $maxtempt; ?></td> </tr>
<tr class="row-light">
<td class="td14" width="40%">Night Min (9pm-9am)</td>
<td class="td14" width="27%"><?php echo conv($mintempovernight,1,1); ?></td>
<td class="td14" width="33%">-</td> </tr>
<tr class="row-dark">
<td class="td14" width="40%">Day Max (6am-6pm)</td>
<td class="td14" width="27%"><?php echo conv($maxtempoverday2,1,1); ?></td>
<td class="td14" width="33%">-</td> </tr>
<tr class="row-light8">
<td class="td14" width="40%">Min Windchill Today</td>
<td class="td14" width="27%"><?php echo conv($minwindch,1,1); ?></td>
<td class="td14" width="33%"><?php echo $minwindcht; ?></td> </tr>
<tr class="column-dark">
<td class="td14" width="40%">---</td>
<td class="td14" width="27%">---</td>
<td class="td14" width="33%">---</td> </tr>
<tr class="row-light1">
<td class="td14" width="40%">Min Temp Yesterday</td>
<td class="td14" width="27%"><?php echo conv($mintempyest,1,1); ?></td>
<td class="td14" width="33%"><?php echo $mintempyestt; ?></td> </tr>
<tr class="row-dark2">
<td class="td14" width="40%">Max Temp Yesterday</td>
<td class="td14" width="27%"><?php echo conv($maxtempyest,1,1); ?></td>
<td class="td14" width="33%"><?php echo $maxtempyestt; ?></td> </tr>
<tr class="row-light8">
<td class="td14" width="40%">Min Windchill Yesterday</td>
<td class="td14" width="27%"><?php echo conv($minchillyest,1,1); ?></td>
<td class="td14" width="33%"><?php echo $minchillyestt; ?></td> </tr>
<tr class="column-dark">
<td class="td14" width="40%">---</td>
<td class="td14" width="27%">---</td>
<td class="td14" width="33%">---</td> </tr>
<tr class="row-light1">
<td class="td14" width="40%">Min Temp Last 7 Days</td>
<td class="td14" width="27%"><?php echo conv($mintempweek,1,1); ?></td>
<td class="td14" width="33%"><?php echo $mintempweekday; ?></td> </tr>
<tr class="row-dark2">
<td class="td14" width="40%">Max Temp Last 7 Days</td>
<td class="td14" width="27%"><?php echo conv($maxtempweek,1,1); ?></td>
<td class="td14" width="33%"><?php echo $maxtempweekday; ?></td> </tr>
<tr class="row-light8">
<td class="td14" width="40%">Min Windchill Last 7 Days</td>
<td class="td14" width="27%"><?php echo conv($minchillweek,1,1); ?></td>
<td class="td14" width="33%"><?php echo $minchillweekday; ?></td> </tr>
<tr class="column-dark">
<td class="td14" width="40%">---</td>
<td class="td14" width="27%">---</td>
<td class="td14" width="33%">---</td> </tr>
<tr class="row-light1">
<td class="td14" width="40%">Min Temp This Month</td>
<td class="td14" width="27%"><?php echo conv($mrecordlowtemp,1,1); ?></td>
<td class="td14" width="33%">Day <?php echo $mrecordlowtempday; ?></td> </tr>
<tr class="row-dark2">
<td class="td14" width="40%">Max Temp This Month</td>
<td class="td14" width="27%"><?php echo conv($mrecordhightemp,1,1); ?></td>
<td class="td14" width="33%">Day <?php echo $mrecordhightempday; ?></td> </tr>
<tr class="row-light8">
<td class="td14" width="40%">Min Windchill This Month</td>
<td class="td14" width="27%"><?php echo conv($mrecordlowchill,1,1); ?></td>
<td class="td14" width="33%">Day <?php echo $mrecordlowchillday; ?></td> </tr>
<tr class="column-dark">
<td class="td14" width="40%">---</td>
<td class="td14" width="27%">---</td>
<td class="td14" width="33%">---</td> </tr>
<tr class="row-light1">
<td class="td14" width="40%">Min Temp This year</td>
<td class="td14" width="27%"><?php echo conv($yrecordlowtemp,1,1); ?></td>
<td class="td14" width="33%"><?php echo datefull($yrecordlowtempday), ' ', monthfull($yrecordlowtempmonth); ?></td> </tr>
<tr class="row-dark2">
<td class="td14" width="40%">Max Temp This Year</td>
<td class="td14" width="27%"><?php echo conv($yrecordhightemp,1,1); ?></td>
<td class="td14" width="33%"><?php echo datefull($yrecordhightempday), ' ', monthfull($yrecordhightempmonth); ?></td> </tr>
<tr class="row-light8">
<td class="td14" width="40%">Min Windchill This Year</td>
<td class="td14" width="27%"><?php echo conv($yrecordlowchill,1,1); ?></td>
<td class="td14" width="33%"><?php echo datefull($yrecordlowchillday), ' ', monthfull($yrecordlowchillmonth); ?></td> </tr>
</table>

<br />

<table class="table1" width="70%" align="center" cellpadding="5" cellspacing="0">
<tr><td class="td14" colspan="3" align="center"><h3>All-time Records</h3></td></tr>
<tr class="table-top">
<td class="td14" colspan="1">Measure</td><td class="td14" colspan="1">Value</td><td class="td14" colspan="1">Date</td></tr>
<tr class="row-light1">
<td class="td14" width="35%">Lowest Temperature</td>
<td class="td14" width="30%"><?php echo conv($recordlowtemp,1,1); ?></td>
<td class="td14" width="35%"><?php echo datefull($recordlowtempday), ' ', monthfull($recordlowtempmonth), ' ', $recordlowtempyear; ?></td> </tr>
<tr class="row-dark2">
<td class="td14" width="35%">Highest Temperature</td>
<td class="td14" width="30%"><?php echo conv($recordhightemp,1,1); ?></td>
<td class="td14" width="35%"><?php echo datefull($recordhightempday), ' ', monthfull($recordhightempmonth), ' ', $recordhightempyear; ?></td> </tr>
<tr class="row-light8">
<td class="td14" width="35%">Lowest Windchill</td>
<td class="td14" width="30%"><?php echo conv($recordlowchill,1,1); ?></td>
<td class="td14" width="35%"><?php echo datefull($recordlowchillday), ' ', monthfull($recordlowchillmonth), ' ', $recordlowchillyear; ?></td> </tr>
<tr class="column-dark">
<td class="td14" width="35%">Lowest Day Max</td>
<td class="td14" width="30%"><?php echo conv(-2.0,1,1); ?></td>
<td class="td14" width="35%">10<sup>th</sup> Jan 2009</td> </tr>
<tr class="row-light">
<td class="td14" width="35%">Highest Night Min</td>
<td class="td14" width="30%"><?php echo conv(20.0,1,1); ?></td>
<td class="td14" width="35%">18<sup>th</sup> Aug 2012</td> </tr>
<tr class="row-dark1">
<td class="td14" width="35%">Coldest Day (by mean)</td>
<td class="td14" width="30%"><?php
	$test = getdata('dailydatalog.txt');
	$min = 100; $max = -100;
	for ($i = 0; $i < intval((mktime(0,0,0,$date_month,$date_day,$date_year)-mktime(0,0,0,2,1,2009))/(24*3600)); $i++) {
		if ($test[$i][9] != "") {
			if ($min > $test[$i][9]): $min = $test[$i][9]; $mint = $test[$i][0]; endif;
			if ($max < $test[$i][9]): $max = $test[$i][9]; $maxt = $test[$i][0]; endif;
		}
	}
	$minta = explode("/", $mint);
	$maxta = explode("/", $maxt);
	echo conv($min,1,1) , '</td>
<td class="td14" width="35%">', datefull($minta[0]), ' ', monthfull($minta[1]), ' ',  $minta[2],'</td> </tr>
<tr class="row-light2">
<td class="td14" width="35%">Warmest Day (by mean)</td>
<td class="td14" width="30%">', conv($max,1,1), '</td>
<td class="td14" width="35%">', datefull($maxta[0]), ' ',  monthfull($maxta[1]), ' ',  $maxta[2]; ?> </td> </tr>
<tr class="row-dark1">
<td class="td14" width="35%">Coldest Month</td>
<td class="td14" width="30%"><?php $year = date("Y");
$years = 1 + ($year - 2009); $dd = intval($date_day); $avetempMaveVmax = 0; $avetempMaveVmin = 99;
$minm = 100; $maxm = -100; $maxtempDV = 0; $mintempDV = 99; $avetempDVmax = 0; $avetempDVmin = 99; $avetempMVmax = 0; $avetempMVmin = 99;
for($y = 0; $y < $years; $y++) {
	$end = 1200;
	$yx = $year - $y;
	for($m = 0; $m < 12; $m++) {
		$filename = date('F', mktime(0,0,0,$m+1)) . $yx . ".htm";
		if(file_exists($filename) && !(date('j') < 20 && $m+1 == date('n') && $y == 0)) {
			$arr = file($filename);
			for ($i = 500; $i <1200; $i++) {
				if(strpos($arr[$i],"remes for the") > 0) { $line = $arr[$i+3]; }
			}
			$arr2 = explode(" ", $line);
			if ($minm > floatval($arr2[8])) { $minm = floatval($arr2[8]); $minmt = $m+1; $minmtY = $yx; }
			if ($maxm < floatval($arr2[8])) { $maxm = floatval($arr2[8]); $maxmt = $m+1; $maxmtY = $yx; }
		}
	}
	$filenm = date('F', mktime(0,0,0,$date_month)) . $yx . ".htm";
	if(file_exists($filenm)) {
		$data = file($filenm);
		for ($i = 0; $i < $end; $i++) {
			if(strpos($data[$i],"remes for the month") > 0) { $end = $i; }
			if(strpos($data[$i],"remes for day") > 0) { $daya = explode(" ", $data[$i]); $a = intval(substr($daya[7],1,2)); }
			if(strpos($data[$i],"aximum tem") > 0) { $tmaxa = explode(" ", $data[$i]); $tmaxv[$a] = floatval($tmaxa[9])+.0001; }
			if(strpos($data[$i],"inimum tem") > 0) { $tmina = explode(" ", $data[$i]); $tminv[$a] = floatval($tmina[9])+.0001; }
			if(strpos($data[$i],"verage tem") > 0) { $tavea = explode(" ", $data[$i]); $tavev[$a] = floatval($tavea[8])+.0001; }
			if ($tavev[$a] > $avetempMVmax) { $avetempMVmax = $tavev[$a]; $avetempMYmax = $yx; $avetempMDmax = $a; }
			if ($tavev[$a] < $avetempMVmin && $tavev[$a] != 0) { $avetempMVmin = $tavev[$a]; $avetempMYmin = $yx; $avetempMDmin = $a; }
		}
		$tavevM[$y] = array_sum($tavev)/$a;
		if ($tavevM[$y] > $avetempMaveVmax && $y != 0) { $avetempMaveVmax = $tavevM[$y]; $avetempMaveYmax = $yx; }
		if ($tavevM[$y] < $avetempMaveVmin) { $avetempMaveVmin = $tavevM[$y]; $avetempMaveYmin = $yx; }
		if ($tmaxv[$dd] > $maxtempDV) { $maxtempDV = $tmaxv[$dd]; $maxtempDY = $yx; }
		if ($tminv[$dd] < $mintempDV && $tminv[$dd] != 0) { $mintempDV = $tminv[$dd]; $mintempDY = $yx; }
		if ($tavev[$dd] > $avetempDVmax) { $avetempDVmax = $tavev[$dd]; $avetempDYmax = $yx; }
		if ($tavev[$dd] < $avetempDVmin && $tavev[$dd] != 0) { $avetempDVmin = $tavev[$dd]; $avetempDYmin = $yx; }
	}
}
echo conv($minm,1,1), '</td>
<td class="td14" width="35%">', monthfull($minmt), ' ', $minmtY, '</td> </tr>
<tr class="row-light2">
<td class="td14" width="35%">Warmest Month</td>
<td class="td14" width="30%">', conv($maxm,1,1), '</td>
<td class="td14" width="35%">', monthfull($maxmt), ' ', $maxmtY; ?></td> </tr>
<tr class="column-dark">
<td class="td14" width="35%">---</td>
<td class="td14" width="30%">---</td>
<td class="td14" width="35%">---</td> </tr>
<tr class="row-light1">
<td class="td14" width="35%">Lowest <?php echo $monthname; ?> Temperature</td>
<td class="td14" width="30%"><?php echo conv($recordlowtempcurrentmonth,1,1); ?></td>
<td class="td14" width="35%">Day <?php echo $recordlowtempcurrentmonthday, ', ', $recordlowtempcurrentmonthyear; ?></td> </tr>
<tr class="row-dark2">
<td class="td14" width="35%">Highest <?php echo $monthname; ?> Temperature</td>
<td class="td14" width="30%"><?php echo conv($recordhightempcurrentmonth,1,1); ?></td>
<td class="td14" width="35%">Day <?php echo $recordhightempcurrentmonthday, ', ', $recordhightempcurrentmonthyear; ?></td> </tr>
<tr class="row-light1">
<td class="td14" width="35%">Coldest <?php echo $monthname; ?></td>
<td class="td14" width="30%"><?php if($monthtodateavtemp < $avetempMaveVmin) { $avetempMaveVmin = $monthtodateavtemp; $avetempMaveYmin = date('Y'); } echo conv($avetempMaveVmin,1,1); ?></td>
<td class="td14" width="35%"><?php echo $avetempMaveYmin ?></td> </tr>
<tr class="row-dark2">
<td class="td14" width="35%">Warmest <?php echo $monthname; ?></td>
<td class="td14" width="30%"><?php if($monthtodateavtemp > $avetempMaveVmax) { $avetempMaveVmax = $monthtodateavtemp; $avetempMaveYmax = date('Y'); } echo conv($avetempMaveVmax,1,1); ?></td>
<td class="td14" width="35%"><?php echo $avetempMaveYmax; ?></td> </tr>
<tr class="row-light1">
<td class="td14" width="35%">Coldest <?php echo $monthname; ?> Day</td>
<td class="td14" width="30%"><?php echo conv(min($avetempMVmin,$avtempsincemidnight),1,1); ?></td>
<td class="td14" width="35%"><?php if($avetempMVmin < $avtempsincemidnight) { echo 'Day ', $avetempMDmin, ', ',$avetempMYmin ; } else { echo 'Today'; } ?></td> </tr>
<tr class="row-dark2">
<td class="td14" width="35%">Warmest <?php echo $monthname; ?> Day</td>
<td class="td14" width="30%"><?php echo conv(max($avetempMVmax,$avtempsincemidnight),1,1); ?></td>
<td class="td14" width="35%"><?php if($avetempMVmax > $avtempsincemidnight) { echo 'Day ', $avetempMDmax, ', ',$avetempMYmax ; } else { echo 'Today'; } ?></td> </tr>
<tr class="column-light">
<td class="td14" width="35%">---</td>
<td class="td14" width="30%">---</td>
<td class="td14" width="35%">---</td> </tr>
<?php $oldt = file($absRoot.'extraTdata.csv'); $mm = intval($date_month);
for($l = 0; $l < count($oldt); $l++) {
	$oldtl = explode(',', $oldt[$l]); $oldtmin[$oldtl[0]][$oldtl[1]] = floatval($oldtl[2]); $oldtmax[$oldtl[0]][$oldtl[1]] = floatval($oldtl[3]);
	$oldtmean[$oldtl[0]][$oldtl[1]] = floatval($oldtl[4]);
}
if(!is_float($oldtmin[$mm][$dd])) { $oldtmin[$mm][$dd] = 100; }
if(!is_float($oldtmean[$mm][$dd])) { $oldtmean[$mm][$dd] = $avetempDVmin+0.2; }
?>
<tr class="row-dark1">
<td class="td14" width="35%">Lowest <?php echo datefull($date_day), ' ', monthfull($date_month); ?> Temperature</td>
<td class="td14" width="30%"><?php $ovtmin = min($mintempDV,$mintemp,$oldtmin[$mm][$dd]); echo conv($ovtmin,1,1); ?></td>
<td class="td14" width="35%"><?php if($mintempDV == $ovtmin) { echo $mintempDY; } elseif($oldtmin[$mm][$dd] == $ovtmin) { if($mm == 1) { echo '2009'; } else { echo '2008'; } }
	else { echo 'Today'; } ?></td> </tr>
<tr class="row-light2">
<td class="td14" width="35%">Highest <?php echo datefull($date_day), ' ', monthfull($date_month); ?> Temperature</td>
<td class="td14" width="30%"><?php $ovtmax = max($maxtempDV,$maxtemp,$oldtmax[$mm][$dd]); echo conv($ovtmax,1,1); ?></td>
<td class="td14" width="35%"><?php if($maxtempDV == $ovtmax) { echo $maxtempDY; } elseif($oldtmax[$mm][$dd] == $ovtmax) { if($mm == 1) { echo '2009'; } else { echo '2008'; } }
	else { echo 'Today'; } ?></td> </tr>
<tr class="row-dark1">
<td class="td14" width="35%">Coldest <?php echo datefull($date_day), ' ', monthfull($date_month); ?></td>
<td class="td14" width="30%"><?php $ovtmean = min($avetempDVmin,$avtempsincemidnight,$oldtmean[$mm][$dd]); echo conv($ovtmean,1,1); ?></td>
<td class="td14" width="35%"><?php if($avetempDVmin == $ovtmean) { echo $avetempDYmin; } elseif($oldtmean[$mm][$dd] == $ovtmean) { if($mm == 1) { echo '2009'; } else { echo '2008'; } }
	else { echo 'Today'; } ?></td> </tr>
<tr class="row-light2">
<td class="td14" width="35%">Warmest <?php echo datefull($date_day), ' ', monthfull($date_month); ?></td>
<td class="td14" width="30%"><?php $ovtmean = max($avetempDVmax,$avtempsincemidnight,$oldtmean[$mm][$dd]); echo conv($ovtmean,1,1); ?></td>
<td class="td14" width="35%"><?php if($avetempDVmax == $ovtmean) { echo $avetempDYmax; } elseif($oldtmean[$mm][$dd] == $ovtmean) { if($mm == 1) { echo '2009'; } else { echo '2008'; } }
	else { echo 'Today'; } ?></td> </tr>
</table>

<br />
<p align="center"><b>NB:</b> Daily temperature records began in March 2008; monthly extremes go back to November 2007.<br />
Figures in brackets refer to departure from <a href="wxaverages.php" title="Long-term NW3 climate averages">average conditions</a>.</p>

<br />

<p><b>A note on Heat Index/Windchill/Humidex:</b> These are all technically-defined measures of what the air actually feels like on one's skin - 
The Heat and Humi indices are useful for factoring in the extra "warming effect" of high relative humidity in warm air, whilst
Windchill obviously factors in the effect of the wind. By definition, they are only valid within certain temperature and humidity ranges; 
in the table 'n/a' is displayed when outside this range,
and the 'Feels-like' value displays the most useful of the three at any given moment.
</p>

<table class="table1" align="center" width="65%" cellpadding="5" cellspacing="0">
<tr><td class="td14" colspan="4" ><h3>Past Year Monthly Averages</h3></td></tr>
<tr class="table-top"><td class="td14">Month</td><td class="td14">Mean</td><td class="td14">Daily Min</td><td class="td14">Daily Max</td></tr>
<?php $mnames = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
$tempav = array(4.7, 4.9, 7, 8.9, 12.5, 15.65, 17.9, 17.7, 14.9, 11.7, 7.6, 5.5);
$anoml = array(2.4,2.2,3.6,5.0,8.3,11.1,13.4,13.2,11.1,8.8,5.0,3.2); $anomh = array(7.0,7.6,10.4,12.8,16.7,20.2,22.4,22.2,18.7,14.6,10.2,7.8);
for($i = 0; $i < 12; $i++) {
	if($i+1 > intval($date_month)-1) { $datyr = $date_year-1; } else { $datyr = $date_year; }
	$report3[$i] = date("FY", mktime(0,0,0,$i+1,1,$datyr)).'.htm'; $minmax[$i] = getmaxmin($report3[$i]);
	if($i % 2 == 0) { $style = 'column-light'; } else { $style = 'column-dark'; } $hlite = ''; $yr2 = '';
	if($i+1 == intval($date_month)) { $yr2 = $date_year-1; }
	if($i+1 == intval($date_month)-1) { $hlite = 'border-bottom:3px solid #8181F7'; }
	$yr1 = ''; if($i == 0) { $yr1 = $date_year; } if($yr1 == $yr2) { $yr1 = ''; }
	if(date('m') < 2) { $yr1 = ''; }
	echo '<tr class="', $style, '"><td style="', $hlite, '" class="td14">', $mnames[$i], ' ', $yr1, $yr2, '</td>
		<td style="', $hlite, '" class="td14">', conv($minmax[$i][2],1,1), ' (', conv2($minmax[$i][2]-$tempav[$i],1,0), ')</td>
		<td style="', $hlite, '" class="td14">', conv($minmax[$i][0],1,1), ' (', conv2($minmax[$i][0]-$anoml[$i],1,0), ')</td>
		<td style="', $hlite, '" class="td14">', conv($minmax[$i][1],1,1), ' (', conv2($minmax[$i][1]-$anomh[$i],1,0), ')</td></tr>';
	$month12_mean[$i] = $minmax[$i][2]; $month12_min[$i] = $minmax[$i][0]; $month12_max[$i] = $minmax[$i][1]; 
}
$style_end = 'border-top:4px solid #0431B4;border-bottom:4px solid #0431B4;font-size:110% '; ?>
<? //if($me != 1) { echo '<!--'; } ?>
<tr class="column-light">
	<td style="<?php echo $style_end; ?>" class="td14">12-month Mean</td>
	<td style="<?php echo $style_end; ?>" class="td14"><?php echo conv(average($month12_mean,12),1,1), ' (', conv2(average($month12_mean,12)-average($tempav,12),1,0); ?>)</td>
	<td style="<?php echo $style_end; ?>" class="td14"><?php echo conv(average($month12_min,12),1,1), ' (', conv2(average($month12_min,12)-average($anoml,12),1,0); ?>)</td>
	<td style="<?php echo $style_end; ?>" class="td14"><?php echo conv(average($month12_max,12),1,1), ' (', conv2(average($month12_max,12)-average($anomh,12),1,0); ?>)</td>
</tr>
<? //if($me != 1) { echo '-->'; } ?>
</table>

<br />

<table class="table1" align="center" width="65%" cellpadding="5" cellspacing="0">
<tr><td class="td14" colspan="4" ><h3>Past Year Seasonal Averages</h3></td></tr>
<tr class="table-top"><td class="td14">Season</td><td class="td14">Mean</td><td class="td14">Daily Min</td><td class="td14">Daily Max</td></tr>
<?php if($date_month == 12 || $date_month < 3): $season = '1'; elseif($date_month > 2 && $date_month < 6): $season = '2';
	elseif($date_month > 5 && $date_month < 9): $season = '3';else: $season = '4'; endif;
$snames = array('Winter', 'Spring', 'Summer', 'Autumn');
$stempav = array($tempav[0]+$tempav[1]+$tempav[11], $tempav[2]+$tempav[3]+$tempav[4], $tempav[5]+$tempav[6]+$tempav[7], $tempav[8]+$tempav[9]+$tempav[10]);
$sanoml = array($anoml[0]+$anoml[1]+$anoml[11], $anoml[2]+$anoml[3]+$anoml[4], $anoml[5]+$anoml[6]+$anoml[7], $anoml[8]+$anoml[9]+$anoml[10]);
$sanomh = array($anomh[0]+$anomh[1]+$anomh[11], $anomh[2]+$anomh[3]+$anomh[4], $anomh[5]+$anomh[6]+$anomh[7], $anomh[8]+$anomh[9]+$anomh[10]);
$sminmax = array( array($minmax[0][0]+$minmax[1][0]+$minmax[11][0], $minmax[2][0]+$minmax[3][0]+$minmax[4][0], $minmax[5][0]+$minmax[6][0]+$minmax[7][0], $minmax[8][0]+$minmax[9][0]+$minmax[10][0]),
		array($minmax[0][1]+$minmax[1][1]+$minmax[11][1], $minmax[2][1]+$minmax[3][1]+$minmax[4][1], $minmax[5][1]+$minmax[6][1]+$minmax[7][1], $minmax[8][1]+$minmax[9][1]+$minmax[10][1]),
		array($minmax[0][2]+$minmax[1][2]+$minmax[11][2], $minmax[2][2]+$minmax[3][2]+$minmax[4][2], $minmax[5][2]+$minmax[6][2]+$minmax[7][2], $minmax[8][2]+$minmax[9][2]+$minmax[10][2]));
for($i = 0; $i < 4; $i++) {
	if($i % 2 == 0) { $style = 'column-light'; } else { $style = 'column-dark'; } $hlite = '';
	if($i+1 == $season-1) { $hlite = 'border-bottom:3px solid #8181F7'; }
	if($i+1 < $season || date('n') == 12) { $nwint = $date_year; } else { $nwint = $date_year-1; } $dfo1 = date('Y')-2001; $dfo2 = date('Y')-2000; $dfo3 = date('Y')-2002; 
	if(date('n') > 2) { $wint = $dfo1 .'/' .$dfo2; } else { $wint = $dfo3 .'/' .$dfo1; } $yr3 = array($wint, $nwint, $nwint, $nwint);
	echo '<tr class="', $style, '"><td style="', $hlite, '" class="td14">', $snames[$i], ' ', $yr3[$i], '</td>
		<td style="', $hlite, '" class="td14">', conv($sminmax[2][$i]/3,1,1), ' (', conv2($sminmax[2][$i]/3-$stempav[$i]/3,1,0),  ')</td>
		<td style="', $hlite, '" class="td14">', conv($sminmax[0][$i]/3,1,1), ' (', conv2($sminmax[0][$i]/3-$sanoml[$i]/3,1,0),  ')</td>
		<td style="', $hlite, '" class="td14">', conv($sminmax[1][$i]/3,1,1), ' (', conv2($sminmax[1][$i]/3-$sanomh[$i]/3,1,0),  ')</td></tr>';
} ?>
</table>

<p align="center"><a href="wxhist14.php" title="<?php echo $year; ?> min/max temperatures"><b>View daily min/max data for the past year</b></a><br />
<a href="wxsumhist14.php" title="All-time monthly temperature records"><b>View monthly averages/extremes for all years on record</b></a></p>

<br />
<hr />

<h4>More Info</h4>

<table class="table1" width="40%" align="left" cellpadding="4" cellspacing="0">
<tr><td class="td14" colspan="3" align="center"><h3>This Month</h3></td></tr>
<tr class="table-top">
<td class="td14" colspan="1">Days When...</td><td class="td14" colspan="1">Value</td></tr>
<tr class="column-light">
<td class="td14" width="55%">Min Temp Was &lt; <?php echo conv3(25 ,1,1); ?></td>
<td class="td14" width="45%"><?php echo $daysTminL25C; ?></td></tr>
<tr class="column-dark">
<td class="td14" width="55%">Min Temp Was &lt; <?php echo conv3(20 ,1,1); ?></td>
<td class="td14" width="45%"><?php echo $daysTminL20C; ?></td></tr>
<tr class="column-light">
<td class="td14" width="55%">Min Temp Was &lt; <?php echo conv3(15 ,1,1); ?></td>
<td class="td14" width="45%"><?php echo $daysTminL15C; ?></td></tr>
<tr class="column-dark">
<td class="td14" width="55%">Min Temp Was &lt; <?php echo conv3(10 ,1,1); ?></td>
<td class="td14" width="45%"><?php echo $daysTminL10C; ?></td></tr>
<tr class="column-light">
<td class="td14" width="55%">Min Temp Was &lt; <?php echo conv3(5 ,1,1); ?></td>
<td class="td14" width="45%"><?php echo $daysTminL5C; ?></td></tr>
<tr class="column-dark">
<td class="td14" width="55%">Min Temp Was &lt; <?php echo conv3(0 ,1,1); ?></td>
<td class="td14" width="45%"><?php echo $daysTminL0C; ?></td></tr>
<tr class="column-light">
<td class="td14" width="55%">Min Temp Was &lt; <?php echo conv3(-5 ,1,1); ?></td>
<td class="td14" width="45%"><?php echo $daysTminLm5C; ?></td></tr>
<tr class="column-dark">
<td class="td14" width="55%">---</td>
<td class="td14" width="45%">---</td></tr>
<tr class="column-light">
<td class="td14" width="55%">Max Temp Was &gt; <?php echo conv3(-5 ,1,1); ?></td>
<td class="td14" width="45%"><?php echo $daysTmaxGm5C; ?></td></tr>
<tr class="column-dark">
<td class="td14" width="55%">Max Temp Was &gt; <?php echo conv3(0 ,1,1); ?></td>
<td class="td14" width="45%"><?php echo $daysTmaxG0C; ?></td></tr>
<tr class="column-light">
<td class="td14" width="55%">Max Temp Was &gt; <?php echo conv3(5 ,1,1); ?></td>
<td class="td14" width="45%"><?php echo $daysTmaxG5C; ?></td></tr>
<tr class="column-dark">
<td class="td14" width="55%">Max Temp Was &gt; <?php echo conv3(10 ,1,1); ?></td>
<td class="td14" width="45%"><?php echo $daysTmaxG10C; ?></td></tr>
<tr class="column-light">
<td class="td14" width="55%">Max Temp Was &gt; <?php echo conv3(15 ,1,1); ?></td>
<td class="td14" width="45%"><?php echo $daysTmaxG15C; ?></td></tr>
<tr class="column-dark">
<td class="td14" width="55%">Max Temp Was &gt; <?php echo conv3(20 ,1,1); ?></td>
<td class="td14" width="45%"><?php echo $daysTmaxG20C; ?></td></tr>
<tr class="column-light">
<td class="td14" width="55%">Max Temp Was &gt; <?php echo conv3(25 ,1,1); ?></td>
<td class="td14" width="45%"><?php echo $daysTmaxG25C; ?></td></tr>
<tr class="column-dark">
<td class="td14" width="55%">Max Temp Was &gt; <?php echo conv3(30 ,1,1); ?></td>
<td class="td14" width="45%"><?php echo $daysTmaxG30C; ?></td></tr>
</table>

<table class="table1" width="40%" align="center" cellpadding="4" cellspacing="0">
<tr><td class="td14" colspan="3" align="center"><h3>This Year</h3></td></tr>
<tr class="table-top">
<td class="td14" colspan="1">Days When...</td><td class="td14" colspan="1">Value</td></tr>
<tr class="column-light">
<td class="td14" width="55%">Min Temp Was &lt; <?php echo conv3(25 ,1,1); ?></td>
<td class="td14" width="45%"><?php echo $daysTminyearL25C; ?></td></tr>
<tr class="column-dark">
<td class="td14" width="55%">Min Temp Was &lt; <?php echo conv3(20 ,1,1); ?></td>
<td class="td14" width="45%"><?php echo $daysTminyearL20C; ?></td></tr>
<tr class="column-light">
<td class="td14" width="55%">Min Temp Was &lt; <?php echo conv3(15 ,1,1); ?></td>
<td class="td14" width="45%"><?php echo $daysTminyearL15C; ?></td></tr>
<tr class="column-dark">
<td class="td14" width="55%">Min Temp Was &lt; <?php echo conv3(10 ,1,1); ?></td>
<td class="td14" width="45%"><?php echo $daysTminyearL10C; ?></td></tr>
<tr class="column-light">
<td class="td14" width="55%">Min Temp Was &lt; <?php echo conv3(5 ,1,1); ?></td>
<td class="td14" width="45%"><?php echo $daysTminyearL5C; ?></td></tr>
<tr class="column-dark">
<td class="td14" width="55%">Min Temp Was &lt; <?php echo conv3(0 ,1,1); ?></td>
<td class="td14" width="45%"><?php echo $daysTminyearL0C; ?></td></tr>
<tr class="column-light">
<td class="td14" width="55%">Min Temp Was &lt; <?php echo conv3(-5 ,1,1); ?></td>
<td class="td14" width="45%"><?php echo $daysTminyearLm5C; ?></td></tr>
<tr class="column-dark">
<td class="td14" width="55%">---</td>
<td class="td14" width="45%">---</td></tr>
<tr class="column-light">
<td class="td14" width="55%">Max Temp Was &gt; <?php echo conv3(-5 ,1,1); ?></td>
<td class="td14" width="45%"><?php echo $daysTmaxyearGm5C; ?></td></tr>
<tr class="column-dark">
<td class="td14" width="55%">Max Temp Was &gt; <?php echo conv3(0 ,1,1); ?></td>
<td class="td14" width="45%"><?php echo $daysTmaxyearG0C; ?></td></tr>
<tr class="column-light">
<td class="td14" width="55%">Max Temp Was &gt; <?php echo conv3(5 ,1,1); ?></td>
<td class="td14" width="45%"><?php echo $daysTmaxyearG5C; ?></td></tr>
<tr class="column-dark">
<td class="td14" width="55%">Max Temp Was &gt; <?php echo conv3(10 ,1,1); ?></td>
<td class="td14" width="45%"><?php echo $daysTmaxyearG10C; ?></td></tr>
<tr class="column-light">
<td class="td14" width="55%">Max Temp Was &gt; <?php echo conv3(15 ,1,1); ?></td>
<td class="td14" width="45%"><?php echo $daysTmaxyearG15C; ?></td></tr>
<tr class="column-dark">
<td class="td14" width="55%">Max Temp Was &gt; <?php echo conv3(20 ,1,1); ?></td>
<td class="td14" width="45%"><?php echo $daysTmaxyearG20C; ?></td></tr>
<tr class="column-light">
<td class="td14" width="55%">Max Temp Was &gt; <?php echo conv3(25 ,1,1); ?></td>
<td class="td14" width="45%"><?php echo $daysTmaxyearG25C; ?></td></tr>
<tr class="column-dark">
<td class="td14" width="55%">Max Temp Was &gt; <?php echo conv3(30,1,1); ?></td>
<td class="td14" width="45%"><?php echo $daysTmaxyearG30C; ?></td></tr>
</table>

</div>

	<!-- ##### Footer ##### -->
<? require('footer.php'); ?>    

 </body>
</html>

<?php
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

function getmaxmin($file) {
	if(file_exists($file)) {
		$data = file($file);
		$end = count($data);
		for ($i = $end-55; $i < $end; $i++) {
			if(strpos($data[$i],"daily max") > 0) { $daymax = explode(" ", $data[$i]); $daymaxv = floatval(substr($daymax[5],1)); }
			if(strpos($data[$i],"daily min") > 0) { $daymin = explode(" ", $data[$i]); $dayminv = floatval(substr($daymin[5],1)); }
			if(strpos($data[$i],"verage temp") > 0) { $daymean = explode(" ", $data[$i]); $daymeanv = floatval($daymean[8]); }
		}
	}
	return array($dayminv, $daymaxv, $daymeanv);
}
?>