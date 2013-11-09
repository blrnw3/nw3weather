<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<?php
//if(!file_exists($root.'phptags.php')) { echo '<meta http-equiv="refresh" content="2">'; }
 include("phptags.php");
	$file = 4;  ?>
	
	<title>NW3 Weather - Old(v2) - Trends and Extremes</title>

	<meta name="description" content="Old v2 - Trends and Extremes from NW3 weather station in Hampstead, North London - live weather updates and extensive historical data.
	Find out the difference in temperature, wind, rain, pressure, dew point from this time yesterday, one month ago and last year; view max and min records for the site" />

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
	<?php require('site_status.php'); ?>

<h1>Trends and Extremes</h1>	

<table class="table1" width="100%" cellpadding="3" cellspacing="0">
<tr><td class="td4" colspan="7" align="center"><h2>Extreme Conditions</h2></td></tr>
<tr class="table-top">
<td class="td4" colspan="1">Measure</td>
<td class="td4" colspan="1">Today</td><td class="td4" colspan="1">Yesterday</td><td class="td4" colspan="1">Last 7 Days</td>
<td class="td4" colspan="1">Month</td><td class="td4" colspan="1">Year</td><td class="td4" colspan="1">All Time</td>
</tr>
<tr class="row-light1">
<td class="td4" width="15%">Min Temperature</td>
<td class="td4" width="15%"><b><?php echo conv($mintemp,1,1); ?></b><br /> at <?php echo $mintempt; ?></td>
<td class="td4" width="14%"><b><?php echo conv($mintempyest,1,1); ?></b><br /> at <?php echo $mintempyestt; ?></td>
<td class="td4" width="14%"><b><?php echo conv($mintempweek,1,1); ?></b><br /> on <?php echo $mintempweekday; ?></td>
<td class="td4" width="14%"><b><?php echo conv($mrecordlowtemp,1,1); ?></b><br /> on Day <?php echo $mrecordlowtempday; ?></td>
<td class="td4" width="14%"><b><?php echo conv($yrecordlowtemp,1,1); ?></b><br /> on <?php echo sprintf('%02d', $yrecordlowtempday), ' ', monthfull($yrecordlowtempmonth); ?></td>
<td class="td4" width="14%"><b><?php echo conv($recordlowtemp,1,1); ?></b><br /> on <?php echo sprintf('%02d', $recordlowtempday), ' ', monthfull($recordlowtempmonth), ' ', $recordlowtempyear; ?></td></tr>
<tr class="row-light2">
<td class="td4" width="15%">Max Temperature</td>
<td class="td4" width="15%"><b><?php echo conv($maxtemp,1,1); ?></b><br /> at <?php echo $maxtempt; ?></td>
<td class="td4" width="14%"><b><?php echo conv($maxtempyest,1,1); ?></b><br /> at <?php echo $maxtempyestt; ?></td>
<td class="td4" width="14%"><b><?php echo conv($maxtempweek,1,1); ?></b><br /> on <?php echo $maxtempweekday; ?></td>
<td class="td4" width="14%"><b><?php echo conv($mrecordhightemp,1,1); ?></b><br /> on Day <?php echo $mrecordhightempday; ?></td>
<td class="td4" width="14%"><b><?php echo conv($yrecordhightemp,1,1); ?></b><br /> on <?php echo sprintf('%02d', $yrecordhightempday), ' ', monthfull($yrecordhightempmonth); ?></td>
<td class="td4" width="14%"><b><?php echo conv($recordhightemp,1,1); ?></b><br /> on <?php echo sprintf('%02d', $recordhightempday), ' ', monthfull($recordhightempmonth), ' ', $recordhightempyear; ?></td></tr>
<tr class="row-dark1a">
<td class="td4" width="15%">Min Relative Humidity</td>
<td class="td4" width="15%"><b><?php echo $lowhum; ?>%</b><br /> at <?php echo $lowhumt; ?></td>
<td class="td4" width="14%"><b><?php echo $minhumyest; ?>%</b><br /> at <?php echo $minhumyestt; ?></td>
<td class="td4" width="14%"><b><?php echo round($minhumweek,0); ?>%</b><br /> on <?php echo $minhumweekday; ?></td>
<td class="td4" width="14%"><b><?php echo $mrecordlowhum; ?>%</b><br /> on Day <?php echo $mrecordlowhumday; ?></td>
<td class="td4" width="14%"><b><?php echo $yrecordlowhum; ?>%</b><br /> on <?php echo sprintf('%02d', $yrecordlowhumday), ' ', monthfull($yrecordlowhummonth); ?></td>
<td class="td4" width="14%"><b><?php echo $recordlowhum; ?>%</b><br /> on <?php echo sprintf('%02d', $recordlowhumday), ' ', monthfull($recordlowhummonth), ' ', $recordlowhumyear; ?></td></tr>
<tr class="row-dark2a">
<td class="td4" width="15%">Max Relative Humidity</td>
<td class="td4" width="15%"><b><?php echo $highhum; ?>%</b><br /> at <?php echo $highhumt; ?></td>
<td class="td4" width="14%"><b><?php echo $maxhumyest; ?>%</b><br /> at <?php echo $maxhumyestt; ?></td>
<td class="td4" width="14%"><b><?php echo round($maxhumweek,0); ?>%</b><br /> on <?php echo $maxhumweekday; ?></td>
<td class="td4" width="14%"><b><?php echo $mrecordhighhum; ?>%</b><br /> on Day <?php echo $mrecordhighhumday; ?></td>
<td class="td4" width="14%"><b><?php echo $yrecordhighhum; ?>%</b><br /> on <?php echo sprintf('%02d', $yrecordhighhumday), ' ', monthfull($yrecordhighhummonth); ?></td>
<td class="td4" width="14%"><b><?php echo $recordhighhum; ?>%</b><br /><i>Occurs regularly</i></td></tr>
<tr class="row-light4">
<td class="td4" width="15%">Max Wind Speed</td>
<td class="td4" width="15%"><b><?php echo conv($maxavgspd,4,1); ?></b><br /> at <?php echo $maxavgspdt; ?></td>
<td class="td4" width="14%"><b><?php echo conv($maxaverageyestnodir,4,1); ?></b><br /> at <?php echo $maxaverageyestt; ?></td>
<td class="td4" width="14%"><b><?php if($maxwindweek > $mrecordwindspeed && $date_day > 6): echo conv($mrecordwindspeed,4,1); else: echo conv($maxwindweek,4,1); endif; ?></b><br /> on <?php echo $maxwindweekday; ?></td>
<td class="td4" width="14%"><b><?php echo conv($mrecordwindspeed,4,1); ?></b><br /> on Day <?php echo $mrecordhighavwindday; ?></td>
<td class="td4" width="14%"><b><?php echo conv($yrecordwindspeed,4,1); ?></b><br /> on <?php echo sprintf('%02d', $yrecordhighavwindday), ' ', monthfull($yrecordhighavwindmonth); ?></td>
<td class="td4" width="14%"><b><?php echo conv($recordwindspeed,4,1); ?></b><br /> on <?php echo sprintf('%02d', $recordhighavwindday), ' ', monthfull($recordhighavwindmonth), ' ', $recordhighavwindyear; ?></td></tr>
<tr class="row-light3">
<td class="td4" width="15%">Max Gust</td>
<td class="td4" width="15%"><b><?php echo conv($maxgst,4,1); ?></b><br /> at <?php echo $maxgstt; ?></td>
<td class="td4" width="14%"><b><?php echo conv($maxgustyestnodir,4,1); ?></b><br /> at <?php echo $maxgustyestt; ?></td>
<td class="td4" width="14%"><b><?php if($maxgustweek > $mrecordwindgust && $date_day > 6): echo conv($mrecordwindgust,4,1); else: echo conv($maxgustweek,4,1); endif; ?></b><br /> on <?php echo $maxgustweekday; ?></td>
<td class="td4" width="14%"><b><?php echo conv($mrecordwindgust,4,1); ?></b><br /> on Day <?php echo $mrecordhighgustday; ?></td>
<td class="td4" width="14%"><b><?php echo conv($yrecordwindgust,4,1); ?></b><br /> on <?php echo sprintf('%02d', $yrecordhighgustday), ' ', monthfull($yrecordhighgustmonth); ?></td>
<td class="td4" width="14%"><b><?php echo conv($recordwindgust,4,1); ?></b><br /> on <?php echo sprintf('%02d', $recordhighgustday), ' ', monthfull($recordhighgustmonth), ' ', $recordhighgustyear; ?></td></tr>
<tr class="row-dark1a">
<td class="td4" width="15%">Min Dew Point</td>
<td class="td4" width="15%"><b><?php echo conv($mindew,1,1); ?></b><br /> at <?php echo $mindewt; ?></td>
<td class="td4" width="14%"><b><?php echo conv($mindewyest,1,1); ?></b><br /> at <?php echo $mindewyestt; ?></td>
<td class="td4" width="14%"><b><?php echo conv($mindewweek,1,1); ?></b><br /> on <?php echo $mindewweekday; ?></td>
<td class="td4" width="14%"><b><?php echo conv($mrecordlowdew,1,1); ?></b><br /> on Day <?php echo $mrecordlowdewday; ?></td>
<td class="td4" width="14%"><b><?php echo conv($yrecordlowdew,1,1); ?></b><br /> on <?php echo sprintf('%02d', $yrecordlowdewday), ' ', monthfull($yrecordlowdewmonth); ?></td>
<td class="td4" width="14%"><b><?php echo conv($recordlowdew,1,1); ?></b><br /> on <?php echo sprintf('%02d', $recordlowdewday), ' ', monthfull($recordlowdewmonth), ' ', $recordlowdewyear; ?></td></tr>
<tr class="row-dark2a">
<td class="td4" width="15%">Max Dew Point</td>
<td class="td4" width="15%"><b><?php echo conv($maxdew,1,1); ?></b><br /> at <?php echo $maxdewt; ?></td>
<td class="td4" width="14%"><b><?php echo conv($maxdewyest,1,1); ?></b><br /> at <?php echo $maxdewyestt; ?></td>
<td class="td4" width="14%"><b><?php echo conv($maxdewweek,1,1); ?></b><br /> on <?php echo $maxdewweekday; ?></td>
<td class="td4" width="14%"><b><?php echo conv($mrecordhighdew,1,1); ?></b><br /> on Day <?php echo $mrecordhighdewday; ?></td>
<td class="td4" width="14%"><b><?php echo conv($yrecordhighdew,1,1); ?></b><br /> on <?php echo sprintf('%02d', $yrecordhighdewday), ' ', monthfull($yrecordhighdewmonth); ?></td>
<td class="td4" width="14%"><b><?php echo conv($recordhighdew,1,1); ?></b><br /> on <?php echo sprintf('%02d', $recordhighdewday), ' ', monthfull($recordhighdewmonth), ' ', $recordhighdewyear; ?></td></tr>
<tr class="row-light1">
<td class="td4" width="15%">Min Pressure</td>
<td class="td4" width="15%"><b><?php echo conv($lowbaro,3,1); ?></b><br /> at <?php echo $lowbarot; ?></td>
<td class="td4" width="14%"><b><?php echo conv($minbaroyest,3,1); ?></b><br /> at <?php echo $minbaroyestt; ?></td>
<td class="td4" width="14%"><b><?php echo conv($minbaroweek,3,1); ?></b><br /> on <?php echo $minbaroweekday; ?></td>
<td class="td4" width="14%"><b><?php echo conv($mrecordlowbaro,3,1); ?></b><br /> on Day <?php echo $mrecordlowbaroday; ?></td>
<td class="td4" width="14%"><b><?php echo conv($yrecordlowbaro,3,1); ?></b><br /> on <?php echo sprintf('%02d', $yrecordlowbaroday), ' ', monthfull($yrecordlowbaromonth); ?></td>
<td class="td4" width="14%"><b><?php echo conv($recordlowbaro,3,1); ?></b><br /> on <?php echo sprintf('%02d', $recordlowbaroday), ' ', monthfull($recordlowbaromonth), ' ', $recordlowbaroyear; ?></td></tr>
<tr class="row-light2">
<td class="td4" width="15%">Max Pressure</td>
<td class="td4" width="15%"><b><?php echo conv($highbaro,3,1); ?></b><br /> at <?php echo $highbarot; ?></td>
<td class="td4" width="14%"><b><?php echo conv($maxbaroyest,3,1); ?></b><br /> at <?php echo $maxbaroyestt; ?></td>
<td class="td4" width="14%"><b><?php echo conv($maxbaroweek,3,1); ?></b><br /> on <?php echo $maxbaroweekday; ?></td>
<td class="td4" width="14%"><b><?php echo conv($mrecordhighbaro,3,1); ?></b><br /> on Day <?php echo $mrecordhighbaroday; ?></td>
<td class="td4" width="14%"><b><?php echo conv($yrecordhighbaro,3,1); ?></b><br /> on <?php echo sprintf('%02d', $yrecordhighbaroday), ' ', monthfull($yrecordhighbaromonth); ?></td>
<td class="td4" width="14%"><b><?php echo conv($recordhighbaro,3,1); ?></b><br /> on <?php echo sprintf('%02d', $recordhighbaroday), ' ', monthfull($recordhighbaromonth), ' ', $recordhighbaroyear; ?></td></tr>
<tr class="row-dark1a">
<td class="td4" width="15%">Min Wind Chill</td>
<td class="td4" width="15%"><b><?php echo conv($minwindch,1,1); ?></b><br /> at <?php echo $minwindcht; ?></td>
<td class="td4" width="14%"><b><?php echo conv($minchillyest,1,1); ?></b><br /> at <?php echo $minchillyestt; ?></td>
<td class="td4" width="14%"><b><?php echo conv($minchillweek,1,1); ?></b><br /> on <?php echo $minchillweekday; ?></td>
<td class="td4" width="14%"><b><?php echo conv($mrecordlowchill,1,1); ?></b><br /> on Day <?php echo $mrecordlowchillday; ?></td>
<td class="td4" width="14%"><b><?php echo conv($yrecordlowchill,1,1); ?></b><br /> on <?php echo sprintf('%02d', $yrecordlowchillday), ' ', monthfull($yrecordlowchillmonth); ?></td>
<td class="td4" width="14%"><b><?php echo conv($recordlowchill,1,1); ?></b><br /> on <?php echo sprintf('%02d', $recordlowchillday), ' ', monthfull($recordlowchillmonth), ' ', $recordlowchillyear; ?></td></tr>
<tr class="row-light5">
<td class="td4" width="15%">Max Rain Rate</td>
<td class="td4" width="15%"><b><?php if($maxrainratehr > 5): echo conv3($maxrainratehr,2,1); else: echo conv($maxrainratehr,2,1); endif; ?>/h</b><br /> at <?php if($dayrn==0): echo "n/a"; else: echo $maxrainratetime; endif; ?></td>
<td class="td4" width="14%"><b><?php if($maxrainrateyesthr > 5): echo conv3($maxrainrateyesthr,2,1); else: echo conv($maxrainrateyesthr,2,1); endif; ?>/h</b><br /> at <?php if($ystdyrain==0): echo "n/a"; else: echo $maxrainrateyesttime; endif; ?></td>
<td class="td4" width="14%"><b>-</b><br /></td>
<td class="td4" width="14%"><b><?php echo conv3($mrecordrainrateperhr,2,1); ?>/h</b><br /> on Day <?php if(floatval($monthrn)==0): echo "n/a"; else: echo $mrecordrainrateday; endif; ?></td>
<td class="td4" width="14%"><b><?php echo conv3($yrecordrainrateperhr,2,1); ?>/h</b><br /> on <?php echo sprintf('%02d', $yrecordrainrateday), ' ', monthfull($yrecordrainratemonth); ?></td>
<td class="td4" width="14%"><b><?php echo conv3($recordrainrateperhr,2,1); ?>/h</b><br /> on <?php echo sprintf('%02d', $recordrainrateday), ' ', monthfull($recordrainratemonth), ' ', $recordrainrateyear; ?></td></tr>
<tr class="row-light6">
<td class="td4" width="15%">Max Rain in 1h</td>
<td class="td4" width="15%"><b><?php echo conv($maxhourrn,2,1); ?></b><br /> at <?php if($maxhourrn == 0): echo 'n/a'; else: echo $maxhourrnt; endif; ?></td>
<td class="td4" width="14%"><b><?php $filex = file('wx18.html'); $mry = explode("=", $filex[15]); $mryt = explode("=", $filex[17]); echo conv($mry[1],2,1); ?></b><br /> at <?php if($ystdyrain==0): echo "n/a"; else: echo $mryt[1]; endif; ?></td>
<td class="td4" width="14%"><b>-</b><br /></td>
<td class="td4" width="14%"><b><?php echo conv($mhrrecordrainrate,2,1); ?></b><br /> on Day <?php if(floatval($monthrn)==0): echo "n/a"; else: echo $mhrrecordrainrateday; endif; ?></td>
<td class="td4" width="14%"><b><?php echo conv($yhrrecordrainrate,2,1); ?></b><br /> on <?php echo sprintf('%02d', $yhrrecordrainrateday), ' ', monthfull($yhrrecordrainratemonth); ?></td>
<td class="td4" width="14%"><b><?php echo conv($hrrecordrainrate,2,1); ?></b><br /> on <?php echo sprintf('%02d', $hrrecordrainrateday), ' ', monthfull($hrrecordrainratemonth), ' ', $hrrecordrainrateyear; ?></td></tr>
<tr class="row-light7">
<td class="td4" width="15%">Max Daily Rain</td>
<td class="td4" width="15%"><b>-</b><br /></td>
<td class="td4" width="14%"><b>-</b><br /></td>
<td class="td4" width="14%"><b>-</b><br /></td>
<td class="td4" width="14%"><b><?php echo conv($mrecorddailyrain,2,1); ?></b><br /> on Day <?php echo $mrecorddailyrainday; ?></td>
<td class="td4" width="14%"><b><?php echo conv($yrecorddailyrain,2,1); ?></b><br /> on <?php echo sprintf('%02d', $yrecorddailyrainday), ' ', monthfull($yrecorddailyrainmonth); ?></td>
<td class="td4" width="14%"><b><?php echo conv($recorddailyrain,2,1); ?></b><br /> on <?php echo sprintf('%02d', $recorddailyrainday), ' ', monthfull($recorddailyrainmonth), ' ', $recorddailyrainyear; ?></td></tr>
<tr class="row-darka">
<td class="td4" width="15%">Rain Total</td>
<td class="td4" width="15%"><b><?php echo conv($dayrn,2,1); ?></b><br /></td>
<td class="td4" width="14%"><b><?php echo conv($ystdyrain,2,1); ?> </b><br /></td>
<td class="td4" width="14%"><b><?php echo conv($raincurrentweek,2,1); ?></b><br /></td>
<td class="td4" width="14%"><b><?php echo conv($monthrn,2,1); ?></b><br /></td>
<td class="td4" width="14%"><b><?php echo conv($yearrn,2,1); ?></b><br /></td>
<td class="td4" width="14%"><b>-</b><br /></td></tr>
</table>

<p><b>NB:</b> This station defines the start of the meteorological day to be midnight; this is when daily values are reset.</p>

<hr />
<br />

<table class="table1" width="90%" align="center" cellpadding="8" cellspacing="1">
<tr><td class="td4" colspan="8" align="center"><h2>Current Trends</h2></td></tr>
<tr class="table-top"><td rowspan="2" class="td4e" colspan="1">Measure</td><td class="td4e" colspan="1" rowspan="2">Current<? if($me == 1) { echo '<br />', $time; }?></td>
<td class="td4" colspan="6">Change Since</td></tr><tr class="table-top"><td class="td4" colspan="1">Last 10 mins</td><td class="td4" colspan="1">Last 30 mins</td>
<td class="td4" colspan="1">Last hour</td><td class="td4e" colspan="1">Last 24hrs</td><td class="td4" colspan="1">Last Month</td><td class="td4" colspan="1">Last Year</td>
</tr>
<tr class="column-light">
<td class="td4e" width="16%"><b>Temperature / &deg;<?php echo $unitT; ?></b></td>
<td class="td4e" width="12%"><?php echo conv($temp0minuteago,1,0); ?></td>
<td class="td4" width="12%"><?php echo conv2($temp0minuteago-$temp10minuteago,1,0); ?></td>
<td class="td4" width="12%"><?php echo conv2($temp0minuteago-$temp30minuteago,1,0); ?></td>
<td class="td4" width="12%"><?php echo conv2($temp0minuteago-$temp60minuteago,1,0); ?></td>
<td class="td4e" width="12%"><?php echo conv2($tempchange24hour,1,0); ?></td>
<td class="td4" width="12%"><?php echo conv2($tempchangemonth,1,0); ?></td>
<td class="td4" width="12%"><?php echo conv2($tempchangeyear,1,0); ?></td>
</tr>
<tr class="column-dark">
<td class="td4e" width="16%"><b>Humidity / %</b></td>
<td class="td4e" width="12%"><?php echo $hum0minuteago; ?></td>
<td class="td4" width="12%"><?php echo $hum0minuteago-$hum10minuteago; ?></td>
<td class="td4" width="12%"><?php echo $hum0minuteago-$hum30minuteago; ?></td>
<td class="td4" width="12%"><?php echo $hum0minuteago-$hum60minuteago; ?></td>
<td class="td4e" width="12%"><?php echo conv2($humchange24hour,0,0); ?></td>
<td class="td4" width="12%"><?php echo conv2($humchangemonth,0,0); ?></td>
<td class="td4" width="12%"><?php echo conv2($humchangeyear,0,0); ?></td>
</tr>
<tr class="column-light">
<td class="td4e" width="16%"><b>Wind / <?php echo $unitW; ?></b></td>
<td class="td4e" width="12%"><?php echo conv($wind0minuteago,4,0); ?></td>
<td class="td4" width="12%"><?php echo conv2($wind0minuteago-$wind10minuteago,4,0); ?></td>
<td class="td4" width="12%"><?php echo conv2($wind0minuteago-$wind30minuteago,4,0); ?></td>
<td class="td4" width="12%"><?php echo conv2($wind0minuteago-$wind60minuteago,4,0); ?></td>
<td class="td4e" width="12%"><?php echo conv2($windchange24hour,4,0); ?></td>
<td class="td4" width="12%"><?php echo conv2($windchangemonth,4,0); ?></td>
<td class="td4" width="12%"><?php echo conv2($windchangeyear,4,0); ?></td>
</tr>
<tr class="column-dark">
<td class="td4e" width="16%"><b>Dew Point / &deg;<?php echo $unitT; ?></b></td>
<td class="td4e" width="12%"><?php echo conv($dew0minuteago,1,0); ?></td>
<td class="td4" width="12%"><?php echo conv2($dew0minuteago-$dew10minuteago,1,0); ?></td>
<td class="td4" width="12%"><?php echo conv2($dew0minuteago-$dew30minuteago,1,0); ?></td>
<td class="td4" width="12%"><?php echo conv2($dew0minuteago-$dew60minuteago,1,0); ?></td>
<td class="td4e" width="12%"><?php echo conv2($dewchange24hour,1,0); ?></td>
<td class="td4" width="12%"><?php echo conv2($dewchangemonth,1,0); ?></td>
<td class="td4" width="12%"><?php echo conv2($dewchangeyear,1,0); ?></td>
</tr>
<tr class="column-light">
<td class="td4e" width="16%"><b>Pressure / <?php echo $unitP; ?></b></td>
<td class="td4e" width="12%"><?php echo conv($baro0minuteago,3,0); ?></td>
<td class="td4" width="12%"><?php echo conv2($baro0minuteago-$baro10minuteago,3,0); ?></td>
<td class="td4" width="12%"><?php echo conv2($baro0minuteago-$baro30minuteago,3,0); ?></td>
<td class="td4" width="12%"><?php echo conv2($baro0minuteago-$baro60minuteago,3,0); ?></td>
<td class="td4e" width="12%"><?php echo conv2($barochange24hour,3,0); ?></td>
<td class="td4" width="12%"><?php echo conv2($barochangemonth,3,0); ?></td>
<td class="td4" width="12%"><?php echo conv2($barochangeyear,3,0); ?></td>
</tr>
<tr class="column-dark">
<td class="td4e" width="16%"><b>Rain Total / <?php echo $unitR; ?></b></td>
<td class="td4e" width="12%"><?php echo conv($rain0minuteago,2,0); ?></td>
<td class="td4" width="12%"><?php echo conv2($rain0minuteago-$rain10minuteago,2,0); ?></td>
<td class="td4" width="12%"><?php echo conv2($rain0minuteago-$rain30minuteago,2,0); ?></td>
<td class="td4" width="12%"><?php echo conv2($rain0minuteago-$rain60minuteago,2,0); ?></td>
<td class="td4e" width="12%"><?php echo conv2($rainchange24hour,2,0); ?></td>
<td class="td4" width="12%"><?php echo conv2($monthrn-$rainchangemonth,2,0); ?></td>
<td class="td4" width="12%"><?php echo conv2($yearrn-$rainchangeyear,2,0); ?></td>
</tr>
</table>
<p align="center"><b>NB: </b>For the month and year rain trends, values refer to differences from cumulative rainfall;
 i.e. the difference between the rain-to-date for this month/year, and that of the previous month/year.</p>
 
<br />
<table class="table1" width="90%" align="center" cellpadding="7" cellspacing="1">
<tr><td class="td4" colspan="13" align="center"><h2>Extremes' Trends</h2></td></tr>
<tr class="table-top"><td rowspan="2" class="td4e" colspan="1">Measure</td><td class="td4e" colspan="3">Today</td><td class="td4e" colspan="3">Yesterday</td>
<td class="td4e" colspan="3">Last Month</td><td class="td4" colspan="3">Last Year</td></tr>
<tr class="table-top"><td class="td4" colspan="1">Min</td><td class="td4" colspan="1">Max</td><td class="td4e" colspan="1">Avg</td>
<td class="td4" colspan="1">Min</td><td class="td4" colspan="1">Max</td><td class="td4e" colspan="1">Avg</td>
<td class="td4" colspan="1">Min</td><td class="td4" colspan="1">Max</td><td class="td4e" colspan="1">Avg</td>
<td class="td4" colspan="1">Min</td><td class="td4" colspan="1">Max</td><td class="td4" colspan="1">Avg</td>
</tr>
<tr class="column-light">
<td class="td4e" width="16%"><b>Temperature / &deg;<?php echo $unitT; ?></b></td>
<td class="td4" width="7%"><?php echo conv($mintemp,1,0); ?></td>
<td class="td4" width="7%"><?php echo conv($maxtemp,1,0); ?></td>
<td class="td4e" width="7%"><?php echo conv($avtempsincemidnight,1,0); ?></td>
<td class="td4" width="7%"><?php echo conv($mintempyest,1,0); ?></td>
<td class="td4" width="7%"><?php echo conv($maxtempyest,1,0); ?></td>
<td class="td4e" width="7%"><?php echo conv($yesterdayavtemp,1,0); ?></td>
<td class="td4" width="7%"><?php echo conv($mintempmtago,1,0); ?></td>
<td class="td4" width="7%"><?php echo conv($maxtempmtago,1,0); ?></td>
<td class="td4e" width="7%"><?php echo conv($avtempmtago,1,0); ?></td>
<td class="td4" width="7%"><?php echo conv($mintempyrago,1,0); ?></td>
<td class="td4" width="7%"><?php echo conv($maxtempyrago,1,0); ?></td>
<td class="td4" width="7%"><?php echo conv($avtempyrago,1,0); ?></td>
</tr>
<?php
$report = date("F", mktime(0,0,0,$date_month,$date_day-1)).date("Y", mktime(0,0,0,$date_month,$date_day-1,$date_year)).'.htm';
$tvar = gethistory($report, 31*date("j", mktime(0,0,0,$date_month,$date_day-1)));
 ?>
<tr class="column-dark">
<td class="td4e" width="16%"><b>Humidity / %</b></td>
<td class="td4" width="7%"><?php echo conv($lowhum,0,0); ?></td>
<td class="td4" width="7%"><?php echo conv($highhum,0,0); ?></td>
<td class="td4e" width="7%"><?php echo conv($last24houravhum,0,0); ?></td>
<td class="td4" width="7%"><?php echo conv($minhumyest,0,0); ?></td>
<td class="td4" width="7%"><?php echo conv($maxhumyest,0,0); ?></td>
<td class="td4e" width="7%"><?php echo conv($tvar[0][date("j",mktime(0,0,0,$date_month,$date_day-1))],0,0); ?></td>
<td class="td4" width="7%"><?php echo conv($minhummtago,0,0); ?></td>
<td class="td4" width="7%"><?php echo conv($maxhummtago,0,0); ?></td>
<td class="td4e" width="7%"><?php echo conv($avhummtago,0,0); ?></td>
<td class="td4" width="7%"><?php echo conv($minhumyrago,0,0); ?></td>
<td class="td4" width="7%"><?php echo conv($maxhumyrago,0,0); ?></td>
<td class="td4" width="7%"><?php echo conv($avhumyrago,0,0); ?></td>
</tr>
<tr class="column-light">
<td class="td4e" width="16%"><b>Wind / <?php echo $unitW; ?></b></td>
<td class="td4" width="7%">N/A</td>
<td class="td4" width="7%"><?php echo conv($maxgst,4,0); ?></td>
<td class="td4e" width="7%"><?php echo conv($avgspeedsincereset,4,0); ?></td>
<td class="td4" width="7%">N/A</td>
<td class="td4" width="7%"><?php echo conv($maxgustyest,4,0); ?></td>
<td class="td4e" width="7%"><?php echo conv($tvar[1][date("j",mktime(0,0,0,$date_month,$date_day-1))],4,0); ?></td>
<td class="td4" width="7%"><?php echo conv($minwindmtago,4,0); ?></td>
<td class="td4" width="7%"><?php echo conv($maxwindmtago,4,0); ?></td>
<td class="td4e" width="7%"><?php echo conv($avwindmtago,4,0); ?></td>
<td class="td4" width="7%"><?php echo conv($minwindyrago,4,0); ?></td>
<td class="td4" width="7%"><?php echo conv($maxwindyrago,4,0); ?></td>
<td class="td4" width="7%"><?php echo conv($avwindyrago,4,0); ?></td>
</tr>
<tr class="column-dark">
<td class="td4e" width="16%"><b>Dew Point / &deg;<?php echo $unitT; ?></b></td>
<td class="td4" width="7%"><?php echo conv($mindew,1,0); ?></td>
<td class="td4" width="7%"><?php echo conv($maxdew,1,0); ?></td>
<td class="td4e" width="7%"><?php $gamma = (17.271*$last24houravtemp)/(237.7+$last24houravtemp)+log($last24houravhum/100); echo conv((237.7*$gamma)/(17.271-$gamma),1,0); ?></td>
<td class="td4" width="7%"><?php echo conv($mindewyest,1,0); ?></td>
<td class="td4" width="7%"><?php echo conv($maxdewyest,1,0); ?></td>
<td class="td4e" width="7%"><?php echo conv($tvar[2][date("j",mktime(0,0,0,$date_month,$date_day-1))],1,0); ?></td>
<td class="td4" width="7%"><?php echo conv($mindewmtago,1,0); ?></td>
<td class="td4" width="7%"><?php echo conv($maxdewmtago,1,0); ?></td>
<td class="td4e" width="7%"><?php echo conv($avdewmtago,1,0); ?></td>
<td class="td4" width="7%"><?php echo conv($mindewyrago,1,0); ?></td>
<td class="td4" width="7%"><?php echo conv($maxdewyrago,1,0); ?></td>
<td class="td4" width="7%"><?php echo conv($avdewyrago,1,0); ?></td>
</tr>
<tr class="column-light">
<td class="td4e" width="16%"><b>Pressure / <?php echo $unitP; ?></b></td>
<td class="td4" width="7%"><?php echo conv($lowbaro,3,0); ?></td>
<td class="td4" width="7%"><?php echo conv($highbaro,3,0); ?></td>
<td class="td4e" width="7%"><?php echo conv($last24houravbaro,3,0); ?></td>
<td class="td4" width="7%"><?php echo conv($minbaroyest,3,0); ?></td>
<td class="td4" width="7%"><?php echo conv($maxbaroyest,3,0); ?></td>
<td class="td4e" width="7%"><?php echo conv($tvar[3][date("j",mktime(0,0,0,$date_month,$date_day-1))],3,0); ?></td>
<td class="td4" width="7%"><?php echo conv($minbaromtago,3,0); ?></td>
<td class="td4" width="7%"><?php echo conv($maxbaromtago,3,0); ?></td>
<td class="td4e" width="7%"><?php echo conv($avbaromtago,3,0); ?></td>
<td class="td4" width="7%"><?php echo conv($minbaroyrago,3,0); ?></td>
<td class="td4" width="7%"><?php echo conv($maxbaroyrago,3,0); ?></td>
<td class="td4" width="7%"><?php echo conv($avbaroyrago,3,0); ?></td>
</tr>
<?php
$report = date("F", mktime(0,0,0,$date_month-1,$date_day)).date("Y", mktime(0,0,0,$date_month-1,$date_day,$date_year)).'.htm';
$tvar2 = gethistory($report, 31*(date("d")));
 ?>
<tr class="column-dark">
<td class="td4e" width="16%"><b>Rain Total / <?php echo $unitR; ?></b></td>
<td colspan="3" class="td4e" width="7%"><?php echo conv($dayrn,2,0); ?></td>
<td colspan="3" class="td4e" width="7%"><?php echo conv($ystdyrain,2,0); ?></td>
<td colspan="3" class="td4e" width="7%"><?php echo conv($tvar2[4][$date_day],2,0); ?></td>
<td colspan="3" class="td4" width="7%"><?php $lyear = date("Y")-1;
	$filename = date('F', mktime(0,0,0,$date_month)) . $lyear . ".htm";
	if(file_exists($filename)) {
		$data = file($filename);
		for ($i = 0; $i <1200; $i++) {
			if(strpos($data[$i],"remes for day") > 0) { $daya = explode(" ", $data[$i]); $a = intval(substr($daya[7],1,2)); }
			if(strpos($data[$i],"all for da") > 0) { $raina = explode(" ", $data[$i]); $rainv[$a] = $raina[12]; }
		}
	}
echo conv($rainv[intval($date_day)],2,0); ?></td>
</tr>
</table>

<p align="center"><b>Description: </b>Today's averages and extremes compared to yesterday's, and those of this day one month/year ago.</p>

<br />
<h3>Last 2hrs Trends in Detail</h3>

<table width="99%" cellpadding="1" cellspacing="0" border="0">
<tr class="table-top">
<td>TIME</td>
<td>TEMP/ &deg;<?php echo $unitT; ?></td>
<td>WIND SPEED/ <?php echo $unitW; ?></td>
<td>WIND GUST/ <?php echo $unitW; ?></td>
<td>WIND DIR</td>
<td>HUMIDITY / %</td>
<td>PRESSURE/ <?php echo $unitP; ?></td>
<td>RAIN TOTAL/ <?php echo $unitR; ?></td>
</tr>
<tr class="column-light">
<td>Current</td>
<td><?php echo conv($temp0minuteago,1,0), ' </td><td> ', conv($wind0minuteago,4,0), ' </td><td> ', conv($gust0minuteago,4,0), ' </td><td> ', $dir0minuteago, ' </td><td> ', $hum0minuteago, '% </td><td> ', conv($baro0minuteago,3,0), ' </td><td> ', conv($rain0minuteago,2,0); ?></td>
</tr>
<tr class="column-dark">
<td>5 minutes ago</td>
<td><?php echo conv($temp5minuteago,1,0), ' </td><td> ', conv($wind5minuteago,4,0), ' </td><td> ', conv($gust5minuteago,4,0), ' </td><td> ', $dir5minuteago, ' </td><td> ', $hum5minuteago, '% </td><td> ', conv($baro5minuteago,3,0), ' </td><td> ', conv($rain5minuteago,2,0); ?></td>
</tr>
<tr class="column-light">
<td>10 minutes ago</td>
<td><?php echo conv($temp10minuteago,1,0), ' </td><td> ', conv($wind10minuteago,4,0), ' </td><td> ', conv($gust10minuteago,4,0), ' </td><td> ', $dir10minuteago, ' </td><td> ', $hum10minuteago, '% </td><td> ', conv($baro10minuteago,3,0), ' </td><td> ', conv($rain10minuteago,2,0); ?></td>
</tr>

<tr class="column-dark">
<td>15 minutes ago</td>
<td><?php echo conv($temp15minuteago,1,0), ' </td><td> ', conv($wind15minuteago,4,0), ' </td><td> ', conv($gust15minuteago,4,0), ' </td><td> ', $dir15minuteago, ' </td><td> ', $hum15minuteago, '% </td><td> ', conv($baro15minuteago,3,0), ' </td><td> ', conv($rain15minuteago,2,0); ?></td>
</tr>

<tr class="column-light">
<td>20 minutes ago</td>
<td><?php echo conv($temp20minuteago,1,0), ' </td><td> ', conv($wind20minuteago,4,0), ' </td><td> ', conv($gust20minuteago,4,0), ' </td><td> ', $dir20minuteago, ' </td><td> ', $hum20minuteago, '% </td><td> ', conv($baro20minuteago,3,0), ' </td><td> ', conv($rain20minuteago,2,0); ?></td>
</tr>

<tr class="column-dark">
<td>30 minutes ago</td>
<td><?php echo conv($temp30minuteago,1,0), ' </td><td> ', conv($wind30minuteago,4,0), ' </td><td> ', conv($gust30minuteago,4,0), ' </td><td> ', $dir30minuteago, ' </td><td> ', $hum30minuteago, '% </td><td> ', conv($baro30minuteago,3,0), ' </td><td> ', conv($rain30minuteago,2,0); ?></td>
</tr>

<tr class="column-light">
<td>45 minutes ago</td>
<td><?php echo conv($temp45minuteago,1,0), ' </td><td> ', conv($wind45minuteago,4,0), ' </td><td> ', conv($gust45minuteago,4,0), ' </td><td> ', $dir45minuteago, ' </td><td> ', $hum45minuteago, '% </td><td> ', conv($baro45minuteago,3,0), ' </td><td> ', conv($rain45minuteago,2,0); ?></td>
</tr>

<tr class="column-dark">
<td>60 minutes ago</td>
<td><?php echo conv($temp60minuteago,1,0), ' </td><td> ', conv($wind60minuteago,4,0), ' </td><td> ', conv($gust60minuteago,4,0), ' </td><td> ', $dir60minuteago, ' </td><td> ', $hum60minuteago, '% </td><td> ', conv($baro60minuteago,3,0), ' </td><td> ', conv($rain60minuteago,2,0); ?></td>
</tr>

<tr class="column-light">
<td>75 minutes ago</td>
<td><?php echo conv($temp75minuteago,1,0), ' </td><td> ', conv($wind75minuteago,4,0), ' </td><td> ', conv($gust75minuteago,4,0), ' </td><td> ', $dir75minuteago, ' </td><td> ', $hum75minuteago, '% </td><td> ', conv($baro75minuteago,3,0), ' </td><td> ', conv($rain75minuteago,2,0); ?></td>
</tr>

<tr class="column-dark">
<td>90 minutes ago</td>
<td><?php echo conv($temp90minuteago,1,0), ' </td><td> ', conv($wind90minuteago,4,0), ' </td><td> ', conv($gust90minuteago,4,0), ' </td><td> ', $dir90minuteago, ' </td><td> ', $hum90minuteago, '% </td><td> ', conv($baro90minuteago,3,0), ' </td><td> ', conv($rain90minuteago,2,0); ?></td>
</tr>

<tr class="column-light">
<td>105 minutes ago</td>
<td><?php echo conv($temp105minuteago,1,0), ' </td><td> ', conv($wind105minuteago,4,0), ' </td><td> ', conv($gust105minuteago,4,0), ' </td><td> ', $dir105minuteago, ' </td><td> ', $hum105minuteago, '% </td><td> ', conv($baro105minuteago,3,0), ' </td><td> ', conv($rain105minuteago,2,0); ?></td>
</tr>

<tr class="column-dark">
<td>120 minutes ago</td>
<td><?php echo conv($temp120minuteago,1,0), ' </td><td> ', conv($wind120minuteago,4,0), ' </td><td> ', conv($gust120minuteago,4,0), ' </td><td> ', $dir120minuteago, ' </td><td> ', $hum120minuteago, '% </td><td> ', conv($baro120minuteago,3,0), ' </td><td> ', conv($rain120minuteago,2,0); ?></td>
</tr>
</table>

<br /><br />
<p><b>NB:</b> For historical data, view the items towards the bottom of the side bar.</p>

</div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>

</body>
</html>

<?php
//Function to get data from monthyyyy.htm files
function gethistory($file, $length) {
	$data = file($file);
	for ($i = 3; $i < $length; $i++) {
		if(strpos($data[$i],"inimum dew") > 0): $dmina = explode(" ", $data[$i]); $d = intval($dmina[14]); endif;
		if(strpos($data[$i],"verage dew") > 0): $davea = explode(" ", $data[$i]); $davev[$d+1] = floatval($davea[11]); endif;
		if(strpos($data[$i],"verage win") > 0): $wavea = explode(" ", $data[$i]); $wavev[$d+1] = floatval($wavea[10]); endif;
		if(strpos($data[$i],"verage hum") > 0): $havea = explode(" ", $data[$i]); $havev[$d+1] = intval($havea[11]); endif;
		if(strpos($data[$i],"verage bar") > 0): $pavea = explode(" ", $data[$i]); $pavev[$d+1] = intval($pavea[10]); endif;
		if(strpos($data[$i],"all for da") > 0): $raina = explode(" ", $data[$i]); $rainv[$d+1] = $raina[12]; endif;
	}
	return array($havev,$wavev,$davev,$pavev,$rainv);
}
?>