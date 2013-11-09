<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("phptags.php");
	$file = 2; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="pragma" content="no-cache" />

	<title>NW3 Weather - Old(v2) - Webcam</title>

	<meta name="description" content="Old v2 - Hampstead, North London - Live Webcam and timelapses from NW3 weather station
	- last day and last hour timelapses, sky weathercam and ground weather cam." />

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

<h1>Webcam</h1>

<h3>Latest Skycam Image</h3>

<p>The camera is a Logitech C300 and is looking NE over Hampstead Heath (<a href="wx8.php#location" title="About page">see map</a>).</p>
<?php if($time < $sunrise || $time > $sunset) { $img = '/currcam.jpg'; $rt = 100; } else { $img = '/currcam.jpg'; $rt = 30; }
	$wsizen[0] = filesize($root.$img); usleep(50000); clearstatcache(); $wsizen[1] = filesize($root.$img); $endw = 20;
	for($wcnt = 0; $wcnt < $endw; $wcnt++) {
		if($wsizen[$wcnt+1] - $wsizen[$wcnt] > 1) { usleep(250000); clearstatcache(); $wsizen[$wcnt+2] = filesize($root.$img); $wcnt2++; } else { $endw = $wcnt; }
	}
	if($wcnt2 > 0) { $scriptbeg = $scriptbeg + 0.25*$wcnt2; }
?>
<img name="refresh" src="<?php echo $img; ?>" alt="Latest Webcam" width="640" height="480"></img><br />

<script type="text/javascript">
	<!--
	var t = <?php echo $rt; ?> // interval in seconds
	image = "<?php echo $img; ?>" //name of the image
	function Start() {
	var curr2 = new Date(); var currms2 = curr2.getTime(); var currsec2 = Math.round(currms2 / 1000);
	var visitl2 = currsec2-<?php echo date('U'); ?>; if(visitl2 > 1000) { t = 30000; }
	tmp = new Date();
	tmp = "?"+tmp.getTime()
	document.images["refresh"].src = image+tmp
	setTimeout("Start()", t*1000)
	}
	Start();
	// -->
</script>
<p>The image is updated automatically: every minute from approximately surise to sunset (<a href="wx6.php" title="Astronomy">see times</a>), and every 5 minutes otherwise.
<noscript><p>Javascript is required for the automatic updates</p></noscript>

<br />
<?php if($time < $sunrise || $time > $sunset) {
		echo '<h3>Latest daylight webcam image</h3><img src="/sunsetcam.jpg" alt="Latest Webcam" width="640" height="480" /><br /><br />';
	} ?>

A <a href="wx11.php" title="Contains skycam only">self-contained version</a> is also available.
</p>

<hr />

<h3>Latest Groundcam Image</h3>
<p>The camera is a Microsoft Lifecam VX-2000 and is looking NNE over Hampstead Heath (see <a href="wx8.php#location" title="About page">map</a>).</p>
<img src="/jpggroundcam.jpg" alt="Latest Groundcam" width="512" height="384" />
<br />
<p>The image updates every 5 minutes, 24 hours a day.</p>

<hr />

<h3>Skycam Timelapses</h3>

Two are available: a higher quality, slower video of the last hour, created hourly at 5 minutes past the hour;
<br /> or a sped-up one for the entire day, updated every 10 minutes.

<p><b>NB:</b> There is also a higher quality video of the all-day timelapse, created nightly at 22:05 <?php echo $dst; ?>.
The latest version is available for download
<a href="/
<?php if($time_hour > 21) {
			echo $date_year,$date_month,$date_day;
		}
		else {
			echo date("Ymd", mktime(0,0,0,$date_month,$date_day-1,$date_year));
		}
?>dayvideo.wmv" title="<? echo $tl_day; ?>'s full-day extended HQ timelapse">here</a>
</p>

<table border="0" cellpadding="10">
<tr><td align="center"><b> Last Hour</b> (daylight only) </td> <td align="center"><b> Last 24hrs</b> </td>
</tr>
<tr><td align="center" width="425"><embed src="/videolasthour.wmv" autostart="false" loop="false" height="350" width="425">
<noembed>Sorry, your browser doesn't support the embedding of multimedia.</noembed></embed>
<!--Temporarily disabled due to high traffic.<br /> Use download-link below to view video.--></td>
<td align="center"><object id="flowplayer" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="350" height="425">
<param name="flashvars"value='config={"key":"#@8d339434b223613a374","clip":"http://icons.wunderground.com/webcamcurrent/t/i/Timmead/1/current.mp4"}' />
<embed autostart="false" type="application/x-shockwave-flash" width="425" height="350"src="http://www.wunderground.com/swf/flowplayer.commercial-3.2.7.swf"
flashvars='config={"key":"#@8d339434b223613a374","clip":"http://icons.wunderground.com/webcamcurrent/t/i/Timmead/1/current.mp4"}'/>
</object></td>
</tr>
</table>

<br />
<b>If Last Hour video does not show</b>: Launch <a href="/videolasthour.wmv" title="Last Hour Timelapse">
<b>file</b></a> in external media player (right-click to save).
<?php if(strpos($browser,'MSIE') > 0) { echo '<!--'; $hideplugin = 1; } ?>
<br />
Or <a href="http://port25.technet.com/videos/downloads/wmpfirefoxplugin.exe" title="External Link">Download Firefox plugin </a>(Windows only).
<?php if($hideplugin == 1) { echo '-->'; } ?>

<p><b>Archive:</b>
The shorter versions are available <a href="http://www.wunderground.com/webcams/Timmead/1/video.html?year=2011&amp;month=01&amp;time=noon" title="courtesy of Wunderground">
<b>here</b>.</a>
<br />
The high-quality, extended full-day timelapses are available<b> <a href="contact.php" title="contact me">on request.</a></b>
</p>

<hr />

<h3>Skycam images from the last 24 hours</h3>
<p>A <a href="highreswebcam.php" title="Full-resolution summary"><b>higher resolution version</b></a> is also available.</p>
<img src="/dailywebcam.jpg" alt="Webcam summary" />
<br />
<a href="wcarchive.php" title="Webcam summary archive"><b>See full archive</b></a> (starting 01/08/10).
<table width="95%"> <tr> <td align="center">NB: Some a.m. images may be blurred due to condensation;
an absent image means conditions were too dark to record anything useful.</td></tr>
</table>

</div>

<!-- ##### Footer ##### -->
	<? require("footer.php"); ?>

</body>
</html>