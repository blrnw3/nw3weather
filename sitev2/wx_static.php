<?php require('unit-select.php'); ?>
<?php include("phptags.php");
	include("main_tags.php");
	$file = 1.1;
	$client = file('clientraw.txt');
	$live = explode(" ", $client[0]);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
<meta name="author" content="Ben Lee-Rodgers" />
<meta http-equiv="refresh" content="600" />

	<title>NW3 Weather - Old(v2) - Home (Static)</title>

	<meta name="description" content="Old v2 - Live weather data from a personal automatic weather station located in Hampstead, North London (non javascript static version)." />

<?php require('chead.php'); ?>
<?php if($_SESSION['count'][$file] < 2) { include_once("ggltrack.php"); } ?>
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

<div style="column-dark2">

<table width="99%" cellpadding="2" cellspacing="0" align="center" border="0" rules="none">
<tr class="column-dark2">
<td valign="top" height="388" width="529" rowspan="4" align="left"><img src="main3.jpg" alt="Main image" title="Double rainbow with (part of) weather station in foreground" width="98%"></img></td>

<td align="center"><b><span style="color:#610B0B">Current Weather Report</span></b>
<br /><br /><?php
if($weathercond == "Moderate drizzle"): $weathercond = "Slight rain";
elseif($weathercond == "Recent showers" || $weathercond == "Stopped raining"): $weathercond = "Recent rain"; endif;

$Cb="";
if(strpos($metarcloudreport,"nimb") > 0): $Cb=", <acronym title='Cumulonimbus cloud'>Cb</acronym> observed"; endif;

if($weathercond == "Dry") {
	 if(strpos($metarcloudreport,"rain") > 0 || strpos($metarcloudreport,"drizzle") > 0 || strpos($metarcloudreport,"showers") > 0) {
		 if(strpos($metarcloudreport,"partly") > 0) {
			 echo "<b>Rain nearby</b>; partly cloudy", $Cb;
			}
		 else {
			 echo "<b>Rain nearby</b>; mostly cloudy", $Cb;
			}
		}
	 else {
		echo '<b>', $weathercond, '</b>; ', $metarcloudreport;
		}
	}
else {
	if(strpos($metarcloudreport,"rain") > 0 || strpos($metarcloudreport,"drizzle") > 0 || strpos($metarcloudreport,"showers") > 0) {
		if(strpos($metarcloudreport,"partly") > 0) {
			echo '<b>', $weathercond, '</b>; ', "partly cloudy", $Cb;
			}
		else {
			echo '<b>', $weathercond, '</b>; ', "mostly cloudy", $Cb;
			}
		}
	else {
		echo '<b>', $weathercond, '</b>; ', $metarcloudreport;
		}
}
?> </td>

<td rowspan="3" align="center"><b><span style="color:#336666">Latest Webcam Image</span></b>
<br /><br />
<a href="wx11.php"><img border="0" src="<?php if($time < $sunrise || $time > $sunset): echo 'webcamimage.gif'; else: echo 'currcam.jpg'; endif; ?>"
 title="Click to enlarge" height="175" width="233" alt="Upload failed" /></a>
<br />
<a href="wx2.php" title="Full webcam image and timelapses">See more</a></td></tr>

<tr class="column-dark2">
<td ><span style="color:rgb(243,242,235)">-</span></td></tr>
<tr class="column-dark2">
<td align="center"><b><span style="color:#6A4EC6">Local Forecast</span></b>
<br /><br />
<img src="/static-images/<?php if($forecasticonword == "Rain"): echo "rain"; elseif($forecasticonword == "Partly cloudy"): echo "partc"; else: echo "sun"; endif; ?>.gif"
 title="<?php echo $forecasticonword; ?>" height="60" width="75" alt="Station forecast icon" />
<br />
<a href="wx5.php" title="5-Day Local Forecast and Maps">Full forecast</a></td></tr>

<tr class="column-dark2">
<td colspan="2" align="center"><table align="center" class="table2" width="89%"><tr class="column-dark2">
<td><b>NW3 Weather</b> is a meteorological observation site located near Hampstead, in North London, UK. <br />
The site was established with an automatic, server-linked personal weather station in July 2010 and runs continuously.
<br />The Home page is updated every minute, but the current data every 15s; the rest of the live data pages and graphs are updated every 5 or 10 minutes.
More info can be found on the <a href="wx8.php" title="Detailed station and website information">About</a> page.</td></tr> 
</table></td></tr>
</table>

<br />

<table cellpadding="5" cellspacing="0" width="99%">
<tr class="table-top">
<td width="21%"><b>Measure</b></td>
<td width="24%"><b>Current</b></td>
<td width="21%"><b>Max/Min</b></td>
<td width="20%"><b>Rate</b></td>
<td width="14%"><b>24hr Mean</b></td>
</tr>

<tr class="column-light">
<td><b>Temperature </b></td>
<td><b><?php echo conv($live[4],1,1); ?></b> &nbsp; <?php arrow($temp0minuteago, $temp10minuteago, $temp30minuteago, 0.4, 0.9, 0.8); ?></td>
<td><span style="font-size: 10px;"><?php echo conv($maxtemp,1,1), ' at ', $maxtempt; ?><br/>
<?php echo conv($mintemp,1,1), ' at ', $mintempt; ?></span></td>
<td><span style="font-size: 10px;"><?php echo conv2($tempchangehour,1,1) ?> /hr</span></td>
<td><?php echo conv($last24houravtemp,1,1); ?></td>
</tr>

<tr class="column-dark2">
<td><b>Relative Humidity</b></td>
<td><b><?php echo $live[5]; ?>%</b> &nbsp; <?php arrow($hum0minuteago, $hum15minuteago, $hum45minuteago, 2, 9, 8); ?></td>
<td><span style="font-size: 10px;"><?php echo $highhum; ?>% at <?php echo $highhumt; ?><br/>
<?php echo $lowhum; ?>% at <?php echo $lowhumt; ?></span></td>
<td><span style="font-size: 10px;"><?php echo $humchangelasthour; ?>% /hr</span></td>
<td><?php echo round($last24houravhum,0); ?>%</td>
</tr>

<tr class="column-light">
<td height="31"><b>Dew Point</b></td>
<td><b><?php echo conv($live[72],1,1); ?></b> &nbsp; <?php arrow($dew0minuteago, $dew10minuteago, $dew30minuteago, 0.4, 0.8, 0.7); ?></td>
<td><span style="font-size: 10px;"><?php echo conv($maxdew,1,1), ' at ', $maxdewt, '<br/>', conv($mindew,1,1), ' at ', $mindewt; ?></span>
</td>
<td><span style="font-size: 10px;"><?php echo conv2($dewchangelasthour,1,1); ?>/hr</span></td>
<td><?php $gamma = (17.271*$last24houravtemp)/(237.7+$last24houravtemp)+log($last24houravhum/100); echo conv((237.7*$gamma)/(17.271-$gamma),1,1); ?></td>
</tr>

<tr class="column-dark2">
<td><b>Pressure</b></td>
<td><b><?php echo conv($baro,3,1); ?></b> &nbsp; <?php arrow($baro0minuteago, $baro90minuteago, $baro120minuteago, 0, 3, 2); ?></td>
<td><span style="font-size: 10px;"><?php echo conv($highbaro,3,1); ?> at <?php echo $highbarot; ?><br/>
<?php echo conv($lowbaro,3,1); ?> at <?php echo $lowbarot; ?></span></td>
<td><span style="font-size: 10px;"><?php echo conv2($trend,3,1); ?>/hr<br/><?php echo $pressuretrendname; ?></span></td>
<td><?php echo conv($last24houravbaro,3,1); ?></td>
</tr>

<tr class="column-light">
<td height="31"><b>Wind</b></td>
<td><b><?php echo conv($live[1]*1.152,4,1), ' ', degname($live[3]); ?></b> -
 Gusting to <b><?php if(floatval($live[140]) < floatval($live[1])) { echo conv3(ceil($live[1]*1.152),4,1); } else { echo conv3($live[140]*1.152,4,1); } ?></b></td>
<td><span style="font-size: 10px;"><b>Max gust last hour:</b> <?php echo conv($maxgsthr,4,1); ?><br/><b>Max gust today:</b> <?php echo conv($maxgst,4,1); ?></span>
</td>
<td><span style="font-size: 10px;"><a href="Beaufort Scale.php" title="Definition of Beaufort terms">Beaufort Scale:</a><br /><?php echo $bftspeedtext; ?> </span></td>
<td><span style="font-size: 11px;"><?php echo conv($avgspeedsincereset,4,1), ' <br />', degname($last24houravdir), ' (', $last24houravdir; ?>&deg;)</span></td>
</tr>

<tr class="column-dark2">
<td height="31"><b>Daily Rain</b></td>
<td><b><?php echo conv($live[7],2,1); ?></b> &nbsp; <?php if(floatval($dayrn) > 0) { arrow($rain0minuteago, $rain20minuteago, $rain45minuteago, 0.1, 2.0, 1.9); } ?></td>
<td><span style="font-size: 10px;"><b>Last Hour:</b> <?php echo conv($hourrn,2,1); ?><br/><b>Month Rain:</b> <?php echo conv($live[8],2,1); ?></span></td>
<td><span style="font-size: 10px;"><?php echo conv($live[10],2,1); ?>/h<br/><b>Last 10 mins:</b> <?php echo conv($rainlast10min,2,1); ?></span></td>
<td><b>Total: </b><?php echo conv($totalrainlast24hours,2,1); ?></td>
</tr>

<tr class="column-light">
<td height="7"><b></b></td>
<td><b></b></td>
<td><span style="font-size: 10px;"></span></td>
<td><span style="font-size: 10px;"></span></td>
<td></td>
</tr>

<tr>
<td colspan="5">

<table cellspacing="2" cellpadding="3" border="1" width="100%" style="border-collapse: collapse">
<tr>
<td width="21%"><span style="font-size: 10px;"><b>Feels Like:</b><br/><?php if(($temp < 27 && $heati >= $temp && $humidex > $temp) || $humidex > $temp): echo conv3($humidex,1,1);
elseif($humidex < $temp && $windch >= $temp): echo conv3($temp,1,1); else: echo conv3($feelslikedp,1,1); endif; ?> &nbsp; (Daily Min: <?php echo conv3($minwindch,1,1); ?>)</span></td>
<td width="24%"><span style="font-size: 10px;"><b>10-min Av Wind:</b><br/><?php echo conv($avtenminutewind,4,1), ' ', $curdir10minutelabel; ?></span></td>
<td width="21%"><span style="font-size: 10px;"><b>Annual Rain:</b><br/><?php echo conv($yearlyraininmm,2,1); ?></span></td>
<td width="20%"><span style="font-size: 10px;"><b>Last Rain:</b><br/><?php $splitdolra = explode("/", $dateoflastrain); echo $timeoflastrainalways, ', '; 
if($splitdolra[0] == $date_day && $splitdolra[1] == $date_month):
echo "Today"; elseif($splitdolra[0] == $date_day-1 && $splitdolra[1] == $date_month): echo "Yesterday"; else: echo $dateoflastrain; endif; ?></span></td>
<td width="14%"><span style="font-size: 10px;"><b>Free Memory:</b><br/><?php if($freememory<0): echo 4000+$freememory; else: echo $freememory; endif; ?> MB</span></td>
</tr>
</table>

</td>
</tr>
</table>

</div>

<p>
To see how these figures compare to expected values,
view the <a href="wxaverages.php" title="Long-term NW3 climate averages">climate averages</a> page.</p>

<p>This weather station has been recording data for<b> <?php echo intval((mktime(0,0,0,$date_month,$date_day,$date_year)-mktime(0,0,0,2,1,2009))/(24*3600)); ?></b> days
(<?php echo intval((mktime(0,0,0,$date_month,$date_day,$date_year)-mktime(0,0,0,7,18,2010))/(24*3600)); ?> at NW3)</p>
</div>

  
<!-- ##### Footer ##### -->
<? require('footer.php'); ?>
</body>
</html>