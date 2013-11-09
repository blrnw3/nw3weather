<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("phptags.php");
	$file = 3; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="pragma" content="no-cache" />

	<title>NW3 Weather - Old(v2) - Latest Graphs</title>

	<meta name="description" content="Old v2 - Latest NW3 weather station daily and monthly charts and graphs - temperature, wind speed and direction, rainfall, dew point;
	wind direction plot; tempereature, wind, rain trends; wind rose." />

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

<h1>Latest Graphs &amp; Charts</h1>

<table border="0" cellpadding="2" cellspacing="0" align="left">
<tr>
<td align="center"><b>Last 24hrs</b></td></tr>
<tr><td align="center"><img src="curr24hourgraph.gif" alt="Upload failed, please wait 5 minutes and try again"
	title="Latest 24hrs Wind, Temp, Hum, Dew, Baro, Rain; Updated: <?php echo date('H:i', filemtime('curr24hourgraph.gif')); ?>" /></td></tr>
<tr><td align="center"><i>Top blue line is av. speed; pink is max gust for that minute</i></td></tr>
<tr><td align="center"><span style="color:white"> -- </span></td></tr>
<tr>
<td align="center"><b>Last 72hrs</b></td></tr>
<tr><td align="center"><img src="curr72hourgraph.gif" alt="Last 72 hours" title="Latest 72hrs Wind, Temp, Hum, Dew, Baro, Rain" /></td></tr>
<tr><td align="center"><i>---</i></td></tr>
<tr><td align="center"><span style="color:white"> -- </span></td></tr>
<tr>
<td align="center"><b>Last Week</b></td></tr>
<tr><td align="center"><img src="weekgra.gif" alt="Last Week" title="Latest Week Wind, Temp, Hum, Dew, Baro, Rain" /></td></tr>
<tr><td align="center"><i>Updated daily at 00:20</i></td></tr>
<tr><td align="center"><span style="color:white"> -- </span></td></tr>
</table>

<table border="0" cellpadding="2" cellspacing="0" align="center">
<tr>
<td align="center"><b>Last 12hrs Wind-Direction Plot</b></td></tr>
<tr><td align="center"><img src="/dirplot.gif" alt="Upload failed, please wait 5 minutes and try again" title="Latest Wind Direction Plot" /></td></tr>
<tr><td align="center"><i>Oldest data is in the centre</i></td></tr>
<tr><td align="center"><span style="color:white"> -- </span></td></tr>
<tr>
<td align="center"><b><?php echo date("F",mktime(0,0,0,$date_month,$date_day-1,$date_year)); ?> Wind Rose</b></td></tr>
<tr><td align="center"><img src="/<?php if($firstday) { echo date("Yn",mktime(0,0,0,$date_month,$date_day-1,$date_year)); } ?>windrose.gif"
	alt="Windrose" title="<?php echo date("F",mktime(0,0,0,$date_month,$date_day-1,$date_year)); ?> Windrose to-date; Updated: <?php echo date('H:i, jS M', filemtime($absRoot.'windrose.gif')); ?>" /></td></tr>
<tr><td align="center"><i>The further out from the centre, the more frequent that direction;<br /> colours represent the proportion of that direction at each speed interval.</i></td></tr>
<tr><td align="center"><span style="color:white"> -- </span></td></tr>
<tr>
<td align="center"><b>31-Day Trends</b></td></tr>
<tr><td align="center"><img src="windtempraintrend.gif" width="100%" alt="31-Day Trends" title="Wind, Temp and Rain trends for the last 31 days" /></td></tr>
<tr><td align="center"><i>Updated every 24hrs at 03:35</i></td></tr>
<tr><td align="center"><span style="color:white"> -- </span></td></tr>
<tr>
<td align="center"><b><?php echo $date_year; ?> Monthly Trends</b></td></tr>
<tr><td align="center"><img src="windtempraintrendyear.gif" width="234" alt="Annual Trends" title="Monthly Wind, Temp and Rain trends for the current year" /></td></tr>
<tr><td align="center"><i>Updated every 24hrs at 03:35</i></td></tr>
<tr><td align="center"><span style="color:white"> -- </span></td></tr>
</table>

<a href="graphviewer.php" title="Daily graph archive starting 2009">View archive of 24hr daily graphs</a>
</div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>

</body>
</html>