<?php require('unit-select.php');
		include("phptags.php");
		$file = 37;
	?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - Records</title>

	<meta name="description" content="Old v2 - Detailed weather records from NW3 weather station. Organised by data type - rain, wind, temperature, humidity, dew point;
	Find most extreme conditions for hampstead, london for this month, year and all time, as well as all-time records for the current month." />
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

<h3>All-Time Records</h3>

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
<td class="td14" width="30%"><?php echo conv(19.3,1,1); ?></td>
<td class="td14" width="35%">2<sup>nd</sup> July 2010</td> </tr>
<tr class="row-dark1">
<td class="td14" width="35%">Record Coldest Day (by mean)</td>
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
<td class="td14" width="35%">Record Warmest Day (by mean)</td>
<td class="td14" width="30%">', conv($max,1,1), '</td>
<td class="td14" width="35%">', datefull($maxta[0]), ' ',  monthfull($maxta[1]), ' ',  $maxta[2]; ?> </td> </tr>
<tr class="row-dark1">
<td class="td14" width="35%">Coldest Month</td>
<td class="td14" width="30%"><?php $year = date("Y");
$years = 1 + ($year - 2009); $dd = intval($date_day); $avetempMaveVmax = 0; $avetempMaveVmin = 99;
$minm = 100; $maxm = -100; $maxtempDV = 0; $mintempDV = 99; $avetempDVmax = 0; $avetempDVmin = 99; $avetempMVmax = 0; $avetempMVmin = 99;
for($y = 0; $y < $years; $y++) {
	$yx = $year - $y;
	for($m = 0; $m < 12; $m++) {
		$filename = date('F', mktime(0,0,0,$m+1)) . $yx . ".htm";
		if(file_exists($filename)) {
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
		for ($i = 0; $i <1200; $i++) {
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
<tr class="row-dark1">
<td class="td14" width="35%">Lowest <?php echo datefull($date_day), ' ', monthfull($date_month); ?> Temperature</td>
<td class="td14" width="30%"><?php echo conv(min($mintempDV,$mintemp),1,1); ?></td>
<td class="td14" width="35%"><?php if($mintempDV < $mintemp) { echo $mintempDY; } else { echo 'Today'; } ?></td> </tr>
<tr class="row-light2">
<td class="td14" width="35%">Highest <?php echo datefull($date_day), ' ', monthfull($date_month); ?> Temperature</td>
<td class="td14" width="30%"><?php echo conv(max($maxtempDV,$maxtemp),1,1); ?></td>
<td class="td14" width="35%"><?php if($maxtempDV > $maxtemp) { echo $maxtempDY; } else { echo 'Today'; } ?></td> </tr>
<tr class="row-dark1">
<td class="td14" width="35%">Coldest <?php echo datefull($date_day), ' ', monthfull($date_month); ?></td>
<td class="td14" width="30%"><?php echo conv(min($avetempDVmin,$avtempsincemidnight),1,1); ?></td>
<td class="td14" width="35%"><?php if($avetempDVmin < $avtempsincemidnight) { echo $avetempDYmin; } else { echo 'Today'; } ?></td> </tr>
<tr class="row-light2">
<td class="td14" width="35%">Warmest <?php echo datefull($date_day), ' ', monthfull($date_month); ?></td>
<td class="td14" width="30%"><?php echo conv(max($avetempDVmax,$avtempsincemidnight),1,1); ?></td>
<td class="td14" width="35%"><?php if($avetempDVmax > $avtempsincemidnight) { echo $avetempDYmax; } else { echo 'Today'; } ?></td> </tr>
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

function curmonthrec() {
	//$dat1 = file('dailydatalog.txt');

	//$dat2 = explode(',',$dat1);
}

$report = date("F", mktime(0,0,0,$date_month,$date_day-1)).date("Y", mktime(0,0,0,$date_month,$date_day-1,$date_year)).'.htm';
if(mktime()-filemtime($report) > 24.2*3600) { mail("blr@nw3weather.co.uk","Old Report","Warning! Latest report not uploaded! Act now!","From: server"); }
?>