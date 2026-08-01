<?php require('unit-select.php'); ?>
<?php include("phptags.php");
	include("main_tags.php");
	$file = 1;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
<meta name="author" content="Ben Lee-Rodgers" />

	<title>NW3 Weather - Old(v2) - Latest and historical weather from Hampstead, London</title>

	<meta name="description" content="Old v2 - Live weather data from a personal automatic weather station located in Hampstead, North London." />

<?php require('chead.php'); ?>
<?php include_once("ggltrack.php") ?>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/
libs/jquery/1.3.0/jquery.min.js"></script>

<script type="text/javascript">
$(document).ready(function() {
	$("#lol").load("./ajax/wx-body.php");
var refreshId = setInterval(function() {
	$("#lol").load('./ajax/wx-body.php?randval='+ Math.random());
}, <?php if($auto == 'on') { echo '15000'; } else { echo '1500000'; } ?>);
<?php if(isset($_GET['cache'])) { echo  '//'; } ?>	$.ajaxSetup({ cache: false });
});
</script>
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
<td valign="top" height="388" width="529" rowspan="4" align="left"><img src="/static-images/main3.jpg" alt="Main image" title="Double rainbow with (part of) weather station in foreground" width="98%"></img></td>

<td align="center"><b><span style="color:#610B0B">Current Weather Report</span></b>
<br /><br /><?php
if($weathercond == "Moderate drizzle") { if($temp30minuteago - $temp0minuteago > 0.7) { $weathercond = 'Rain Shower'; } else { $weathercond = "Slight rain"; } }
elseif($weathercond == "Recent showers" || $weathercond == "Stopped raining") { $weathercond = "Recent rain"; }

$Cb="";
if(strpos($metarcloudreport,"nimb") > 0): $Cb= ", ".$stbfix ."<acronym title='Cumulonimbus cloud'>Cb</acronym>".$enbfix." observed"; endif;
if($metarcloudreport == '---') { $metarcloudreport = 'Cloud cover not known'; }

if($weathercond == "Dry") {
	 if(strpos($metarcloudreport,"rain") > 0 || strpos($metarcloudreport,"drizzle") > 0 || strpos($metarcloudreport,"showers") > 0) {
		 if(strpos($metarcloudreport,"partly") > 0) {
			 echo "<b>Rain nearby</b>; partly cloudy", $Cb;
			}
		 else {
			 echo "<b>Rain nearby</b>; mostly cloudy", $Cb;
			}
		}
	 elseif(strpos($metarcloudreport,"snow") > 0) {
		 echo "<b>Snow nearby</b>; Cloudy", $Cb;
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
<?php $img = '/currcam.jpg'; $rt = 100; ?>
<a href="wx11.php"><img <?php if($auto == 'on') { echo 'name="refresh"'; } ?> border="0" src="<?php echo $img; ?>"
 title="Click to enlarge" height="175" width="233" alt="Upload failed" /></a>
<br />
<a href="wx2.php" title="Full webcam image and timelapses">See more</a>
<script type="text/javascript">
	<!--
	var t = <?php echo $rt; ?> // interval in seconds
	image = "<?php echo $img; ?>" //name of the image
	function Start() {
	tmp = new Date();
	tmp =  Math.round(tmp.getTime()/60000)
	document.images["refresh"].src = image+'?'+tmp
	setTimeout("Start()", t*1000)
	}
	Start();
	// -->
</script>
</td>
</tr>

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

</div>
</div>
<div id="lol">
	<p>Current data table loading... Please wait </p>
	
	<noscript><p><b>Warning:</b> Javascript must be enabled to view this data <br />
	A <a href="wx_static.php" title="No live updates available but otherwise the same">non-JS version</a> is also available. </p></noscript>
</div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>
</body>
</html>