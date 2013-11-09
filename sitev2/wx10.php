<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("phptags.php");
	$file = 10; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - Humidity Detail</title>

	<meta name="description" content="Old v2 - Detailed latest humidity (dew point, relative humidity etc.) data/information and records from NW3 weather station" />

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

<h1>Detailed Humidity Data</h1>

<table class="table1" width="40%" align="left" cellpadding="5" cellspacing="0">
<tr><td class="td10" colspan="2" align="center"><h3>Current/Latest</h3></td></tr>
<tr class="table-top">
<td class="td10" colspan="1">Measure</td><td class="td10" colspan="1">Value</td>
</tr>
<?php
$report = date("F", mktime(0,0,0,$date_month,$date_day-1)).date("Y", mktime(0,0,0,$date_month,$date_day-1,$date_year)).'.htm';
$tvar = gethistory($report);
 ?>
<tr class="column-light">
<td class="td10" width="60%">Relative Humidity</td>
<td class="td10" width="40%"><?php echo $hum; ?>%</td></tr>
<tr class="column-dark">
<td class="td10" width="60%">Rel. Humidity Trend</td>
<td class="td10" width="40%"><?php echo $humchangelasthour; ?>% /hr</td> </tr>
<tr class="column-light">
<td class="td10" width="60%">24hr Average RH </td>
<td class="td10" width="40%"><?php echo round($last24houravhum); ?>%</td> </tr>
<tr class="column-dark">
<td class="td10" width="60%">Yesterday Average RH </td>
<td class="td10" width="40%"><?php echo $tvar[5][date("j",mktime(0,0,0,$date_month,$date_day-1))-1]; ?>%</td> </tr>
<tr class="column-light">
<td class="td10" width="60%">Week Average RH</td>
<td class="td10" width="40%"><?php echo round($avhumweek,0) ?>%</td></tr>
<tr class="column-dark">
<td class="td10" width="60%">Month Average RH</td>
<td class="td10" width="40%"><?php echo round($monthtodateavhum); ?>%</td> </tr>
<tr class="column-light">
<td class="td10" width="60%">---</td>
<td class="td10" width="40%">---</td> </tr>
<tr class="column-dark">
<td class="td10" width="60%">Dew Point</td>
<td class="td10" width="40%"><?php echo conv($dew,1,1); ?></td> </tr>
<tr class="column-light">
<td class="td10" width="60%">Dew Point Trend</td>
<td class="td10" width="40%"><?php echo conv2($dewchangelasthour,1,1); ?> /hr</td> </tr>
<tr class="column-dark">
<td class="td10" width="60%">24hr Avg Dew Point</td>
<td class="td10" width="40%"><?php $gamma = (17.271*$last24houravtemp)/(237.7+$last24houravtemp)+log($last24houravhum/100); echo conv((237.7*$gamma)/(17.271-$gamma),1,1); ?></td> </tr>
<tr class="column-light">
<td class="td10" width="60%">Yesterday Avg Dew Point </td>
<td class="td10" width="40%"><?php echo conv($tvar[4][date("j",mktime(0,0,0,$date_month,$date_day-1))-1],1,1); ?></td> </tr>
<tr class="column-dark">
<td class="td10" width="60%">Week Avg Dew Point </td>
<td class="td10" width="40%"><?php $gamma = (17.271*$avtempweek)/(237.7+$avtempweek)+log($avhumweek/100); echo conv((237.7*$gamma)/(17.271-$gamma),1,1); ?></td> </tr>
<tr class="column-light">
<td class="td10" width="60%">Month Avg Dew Point</td>
<td class="td10" width="40%"><?php echo conv($monthtodateavdp,1,1); ?></td> </tr>
<tr class="column-dark">
<td class="td10" width="60%">---</td>
<td class="td10" width="40%">---</td> </tr>
<tr class="column-light">
<td class="td10" width="60%">Wet-Bulb Temperature</td>
<td class="td10" width="40%"><?php echo conv($wetbulb,1,1); ?></td> </tr>
<tr class="column-dark">
<td class="td10" width="60%">Absolute Humidity</td>
<td class="td10" width="40%"><?php echo conv4($abshum,5,1); ?><sup>-3</sup></td> </tr>
<tr class="column-light">
<td class="td10" width="60%">Air Density</td>
<td class="td10" width="40%"><?php echo conv3($airdensity,5,1); ?><sup>-3</sup></td> </tr>
</table>

<table class="table1" width="56%" align="center" cellpadding="5" cellspacing="0">
<tr><td class="td10" colspan="3" align="center"><h3>Current Day/Month/Year Records</h3></td></tr>
<tr class="table-top">
<td class="td10" colspan="1">Measure</td><td class="td10" colspan="1">Value</td><td class="td10" colspan="1">Time/Date</td>
</tr>
<tr class="column-light">
<td class="td10" width="40%">Min RH Today</td>
<td class="td10" width="30%"><?php echo $lowhum; ?>%</td>
<td class="td10" width="30%"><?php echo $lowhumt; ?></td> </tr>
<tr class="column-dark">
<td class="td10" width="40%">Max RH Today</td>
<td class="td10" width="30%"><?php echo $highhum; ?>%</td>
<td class="td10" width="30%"><?php echo $highhumt; ?></td> </tr>
<tr class="column-light">
<td class="td10" width="40%">Min RH Yesterday</td>
<td class="td10" width="30%"><?php echo $minhumyest; ?>%</td>
<td class="td10" width="30%"><?php echo $minhumyestt; ?></td> </tr>
<tr class="column-dark">
<td class="td10" width="40%">Max RH Yesterday</td>
<td class="td10" width="30%"><?php echo $maxhumyest; ?>%</td>
<td class="td10" width="30%"><?php echo $maxhumyestt; ?></td> </tr>
<tr class="column-light">
<td class="td10" width="40%">Min RH This Month</td>
<td class="td10" width="30%"><?php echo $mrecordlowhum; ?>%</td>
<td class="td10" width="30%">Day <?php echo $mrecordlowhumday; ?></td> </tr>
<tr class="column-dark">
<td class="td10" width="40%">Max RH This Month</td>
<td class="td10" width="30%"><?php echo $mrecordhighhum; ?>%</td>
<td class="td10" width="30%">Day <?php echo $mrecordhighhumday; ?></td> </tr>
<tr class="column-light">
<td class="td10" width="40%">Min RH This Year</td>
<td class="td10" width="30%"><?php echo $yrecordlowhum; ?>%</td>
<td class="td10" width="30%"><?php echo datefull($yrecordlowhumday), ' ', monthfull($yrecordlowhummonth); ?></td> </tr>
<tr class="column-dark">
<td class="td10" width="40%">Max RH This Year</td>
<td class="td10" width="30%"><?php echo $yrecordhighhum; ?>%</td>
<td class="td10" width="30%"><?php if($yrecordhighhum == 98) { echo '<i>Occurs regularly</i>'; } else { echo datefull($yrecordhighhumday), ' ', monthfull($yrecordhighhummonth); } ?></td> </tr>
<tr class="column-light">
<td class="td10" width="40%">---</td>
<td class="td10" width="30%">---</td>
<td class="td10" width="30%">---</td> </tr>
<tr class="column-dark">
<td class="td10" width="40%">Min Dew Point Today</td>
<td class="td10" width="30%"><?php echo conv($mindew,1,1); ?></td>
<td class="td10" width="30%"><?php echo $mindewt; ?></td> </tr>
<tr class="column-light">
<td class="td10" width="40%">Max Dew Point Today</td>
<td class="td10" width="30%"><?php echo conv($maxdew,1,1); ?></td>
<td class="td10" width="30%"><?php echo $maxdewt; ?></td> </tr>
<tr class="column-dark">
<td class="td10" width="40%">Min Dew Point Yesterday</td>
<td class="td10" width="30%"><?php echo conv($mindewyest,1,1); ?></td>
<td class="td10" width="30%"><?php echo $mindewyestt; ?></td> </tr>
<tr class="column-light">
<td class="td10" width="40%">Max Dew Point Yesterday</td>
<td class="td10" width="30%"><?php echo conv($maxdewyest,1,1); ?></td>
<td class="td10" width="30%"><?php echo $maxdewyestt; ?></td> </tr>
<tr class="column-dark">
<td class="td10" width="40%">Min Dew Point This Month</td>
<td class="td10" width="30%"><?php echo conv($mrecordlowdew,1,1); ?></td>
<td class="td10" width="30%">Day <?php echo $mrecordlowdewday; ?></td> </tr>
<tr class="column-light">
<td class="td10" width="40%">Max Dew Point This Month</td>
<td class="td10" width="30%"><?php echo conv($mrecordhighdew,1,1); ?></td>
<td class="td10" width="30%">Day <?php echo $mrecordhighdewday; ?></td> </tr>
<tr class="column-dark">
<td class="td10" width="40%">Min Dew Point This Year</td>
<td class="td10" width="30%"><?php echo conv($yrecordlowdew,1,1); ?> </td>
<td class="td10" width="30%"><?php echo datefull($yrecordlowdewday), ' ', monthfull($yrecordlowdewmonth); ?></td> </tr>
<tr class="column-light">
<td class="td10" width="40%">Max Dew Point This Year</td>
<td class="td10" width="30%"><?php echo conv($yrecordhighdew,1,1); ?></td>
<td class="td10" width="30%"><?php echo datefull($yrecordhighdewday), ' ', monthfull($yrecordhighdewmonth); ?></td> </tr>
</table>

<br />

<table class="table1" width="70%" align="center" cellpadding="5" cellspacing="0">
<tr><td class="td10" colspan="3" align="center"><h3>All-time Records</h3></td></tr>
<tr class="table-top">
<td class="td10" colspan="1">Measure</td><td class="td10" colspan="1">Value</td><td class="td10" colspan="1">Time/Date</td></tr>
<tr class="column-light">
<td class="td10" width="37%">Lowest Relative Humidity</td>
<td class="td10" width="26%"><?php echo $recordlowhum; ?>%</td>
<td class="td10" width="37%"><?php echo datefull($recordlowhumday), ' ', monthfull($recordlowhummonth), ' ', $recordlowhumyear; ?></td> </tr>
<tr class="column-dark">
<td class="td10" width="37%">Highest Relative Humidity</td>
<td class="td10" width="26%">98%</td>
<td class="td10" width="37%"><i>Occurs regularly</i></td> </tr>
<tr class="column-light">
<td class="td10" width="37%">Lowest RH Daily Mean</td>
<td class="td10" width="26%"><?php $tvar3 = wx10extras(5); echo $tvar3[0]; ?>%</td>
<td class="td10" width="37%"><?php $tv1 = explode(' ',$tvar3[5]); echo datefull($tv1[0]), ' ', monthfull($tv1[1]), ' ', $tv1[2]; ?></td> </tr>
<tr class="column-dark">
<td class="td10" width="37%">Highest RH Daily Mean</td>
<td class="td10" width="26%">98%</td>
<td class="td10" width="37%"><i>Several instances</i></td> </tr>
<tr class="column-light">
<td class="td10" width="37%">Lowest RH Monthly Mean</td>
<td class="td10" width="26%"><?php echo round($tvar3[2],0); ?>%</td>
<td class="td10" width="37%"><?php $tv1 = explode(' ',$tvar3[6]); echo monthfull($tv1[0]), ' ', $tv1[1]; ?></td> </tr>
<tr class="column-dark">
<td class="td10" width="37%">Highest RH Monthly Mean</td>
<td class="td10" width="26%"><?php echo round($tvar3[3],0); ?>%</td>
<td class="td10" width="37%"><?php $tv1 = explode(' ',$tvar3[7]); echo monthfull($tv1[0]), ' ', $tv1[1]; ?></td> </tr>
<tr class="column-light">
<td class="td10" width="37%">---</td>
<td class="td10" width="26%">---</td>
<td class="td10" width="37%">---</td> </tr>
<tr class="column-dark">
<td class="td10" width="37%">Lowest Dew Point</td>
<td class="td10" width="26%"><?php echo conv($recordlowdew,1,1); ?></td>
<td class="td10" width="37%"><?php echo datefull($recordlowdewday), ' ', monthfull($recordlowdewmonth), ' ', $recordlowdewyear; ?></td> </tr>
<tr class="column-light">
<td class="td10" width="37%">Highest Dew Point</td>
<td class="td10" width="26%"><?php echo conv($recordhighdew,1,1); ?></td>
<td class="td10" width="37%"><?php echo datefull($recordhighdewday), ' ', monthfull($recordhighdewmonth), ' ', $recordhighdewyear; ?></td> </tr>
<tr class="column-dark">
<td class="td10" width="37%">Lowest Dew Point Daily Mean</td>
<td class="td10" width="26%"><?php $tvar2 = wx10extras(4); echo conv($tvar2[0],1,1); ?></td>
<td class="td10" width="37%"><?php $tv1 = explode(' ',$tvar2[5]); echo datefull($tv1[0]), ' ', monthfull($tv1[1]), ' ', $tv1[2]; ?></td> </tr>
<tr class="column-light">
<td class="td10" width="37%">Highest Dew Point Daily Mean</td>
<td class="td10" width="26%"><?php echo conv($tvar2[1],1,1); ?></td>
<td class="td10" width="37%"><?php $tv1 = explode(' ',$tvar2[4]); echo datefull($tv1[0]), ' ', monthfull($tv1[1]), ' ', $tv1[2]; ?></td> </tr>
<tr class="column-dark">
<td class="td10" width="37%">Lowest Dew Point Monthly Mean</td>
<td class="td10" width="26%"><?php echo conv($tvar2[2],1,1); ?></td>
<td class="td10" width="37%"><?php $tv1 = explode(' ',$tvar2[6]); echo monthfull($tv1[0]), ' ', $tv1[1]; ?></td> </tr>
<tr class="column-light">
<td class="td10" width="37%">Highest Dew Point Monthly Mean</td>
<td class="td10" width="26%"><?php echo conv($tvar2[3],1,1); ?></td>
<td class="td10" width="37%"><?php $tv1 = explode(' ',$tvar2[7]); echo monthfull($tv1[0]), ' ', $tv1[1]; ?></td> </tr>
</table>

<br />
<p align="center"><b>Note 1:</b> Valid humidity records began in February 2009
<br /><b>Note 2:</b> 98% is the physical limit of the hygrometer (measuring RH); in reality this tends to means 100% saturation of the air
</p>

<p align="center"><b>NB: </b>Daily dew point max/mins for the current year (and all other years on record) can be viewed
<a href="wxhist10.php" title="<?php echo $year; ?> dew points"> here</a>.<br />
 Relative humidity max/mins are <a href="wxhist10.5.php" title="<?php echo $year; ?> relative humidities"> here</a></p>

<p><b><span style="color:green">A note on the different measures of humidity:</span></b> <br />

<b> The Dew Point</b> (or frost point if the temperature is &lt; <?php echo conv(0,1,1); ?>
is the saturation temperature of a parcel of air, i.e. the point at which water condenses out.
It is the temperature at which an object would need to be
for dew to form on it (dew forms because certain objects - like cars - cool more rapidly than the air, enabling them to
reach the dew point). It is directly proportional to the specific humidity, and very well correlated to the absolute humidity.
In everyday terms, high dew points are more uncomfortable as sweating is less effective.
<br />
<b>Relative Humidity</b>, on the other hand, is rather more abstract and has a very technical definition:
the ratio of the partial pressure of water vapour in the air to the saturated vapour pressure of that water.
The saturated vapour pressure is proportional to the air temperature, and the partial pressure indicates how much water vapour the air contains,
so at a given temperature, the RH is entirely dependent on this partial pressure, making the RH useful in determining the extent to which the air is water-saturated.
For example: When it rains the relative humidity <i>will</i> increase, but the dew point may not, as the temperature will usually fall as well.
If the dew point is the same when the RH increases, the air has the same amount of water but it is cooler,
so it is more saturated, as less water vapour can exist in cooler air.
<br />
<b>Simply (and concisely!):</b> Dew point is a rough measure of how much water vapour is physically in the air;
relative humidity is just a measure of the degree to which the air is full of water vapour (i.e. its saturation).
One is an absolute measure, the other is relative.</p>

</div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>

</body>
</html>

<?php
function wx10extras ($dat) {
	$year = date("Y"); $years = 1 + $year - 2009;
	for($y = 0; $y < $years ; $y ++ ) {
		$yx = $year - $y;	
		for ( $m = 0; $m < 12 ; $m ++ ) { 
			$filename = date('F', mktime(0,0,0,$m+1,1,$yx)) . $yx . ".htm";
			$raw[$y][1][$m][1] = gethistory($filename);
		}
	}
	$tempmonth = array(); $minmean = 200; $maxmean = -200; $mmin = 99; $mmax = -9;
	for ($yx = 0 ; $yx < $years ; $yx++ ) { 
		for ($mnt = 0 ; $mnt < 12 ; $mnt++ ) { 
			for ($day = 0 ; $day < 31 ; $day++ ) { 					
				$temp = $raw[$yx][1][$mnt][1][$dat][$day];
				if ( $temp != 0) {
					$tempmonth[$yx][$mnt][4] = $tempmonth[$yx][$mnt][4] + $temp;
					$tempmonth[$yx][$mnt][5] = $tempmonth[$yx][$mnt][5] + 1;
					if ($temp > $maxmean) { $maxmean = $temp; $maxmeanD1 = $day+1; $maxmeanD2 = $mnt+1; $maxmeanD3 = $year-$yx; }
					if ($temp < $minmean) { $minmean = $temp; $minmeanD1 = $day+1; $minmeanD2 = $mnt+1; $minmeanD3 = $year-$yx; }
				}
			}
		} 
	}
	$max = $maxmean; $min = $minmean; $maxD = ($maxmeanD1. ' '.$maxmeanD2. ' '.$maxmeanD3); $minD = ($minmeanD1. ' '.$minmeanD2. ' '.$minmeanD3);
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		for ( $y = 0 ; $y < $years ; $y++ ) {
			if($tempmonth[$y][$i][5] > 10) { $tvah[$i][$y] = $tempmonth[$y][$i][4] / $tempmonth[$y][$i][5]; }
			if($tvah[$i][$y] < $mmin && $tvah[$i][$y] != 0) { $mmin = $tvah[$i][$y]; $mminD1 = $i+1; $mminD2 = $year-$y;}
		}
	}
	for ( $i = 0 ; $i < 12 ; $i++ ) {
		for ( $y = 0 ; $y < $years ; $y++ ) {
			if($tempmonth[$y][$i][5] != 0) { $tvah[$i][$y] = $tempmonth[$y][$i][4] / $tempmonth[$y][$i][5]; }
			if($tvah[$i][$y] > $mmax) { $mmax = $tvah[$i][$y]; $mmaxD1 = $i+1; $mmaxD2 = $year-$y; }
		}
	}
	$mminD = ($mminD1 . ' ' . $mminD2); $mmaxD = ($mmaxD1 . ' ' . $mmaxD2);
	
	return array($min,$max,$mmin,$mmax,$maxD,$minD,$mminD,$mmaxD);
}

function gethistory($file) {
	if(file_exists($file)) {
		$data = file($file);
		$end = 1200;
		for ($i = 1; $i < $end; $i++) {
			if(strpos($data[$i],"remes for the month") > 0) { $end = $i; }
			if(strpos($data[$i],"remes for day") > 0) { $daya = explode(" ", $data[$i]); $a = intval(substr($daya[7],1,2))-1; }
			if(strpos($data[$i],"verage dew") > 0): $davea = explode(" ", $data[$i]); $davev[$a] = floatval($davea[11])-0.001; endif;
			if(strpos($data[$i],"verage hum") > 0): $havea = explode(" ", $data[$i]); $havev[$a] = floatval($havea[11]); endif;
		}
		return array(1,1,1,1,$davev,$havev);
	}
}

?>