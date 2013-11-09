<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("main_tags.php");
	$file = 95; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - Site Map</title>

	<meta name="description" content="Old v2 - NW3 weather website map/directory." />
	
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
	<ul>
	<li><a href="index.php" title="Return to main page">Home</a></li>
	<li><a href="wx2.php" title="Live Webcam and Timelapses">Webcam</a>
		<ul> <li><a href="highreswebcam.php" title="High resolution Webcam Images from the last 24hrs">Webcam images from the last 24hrs </a></li> </ul></li>
	<li><a href="wx3.php" title="Latest Daily and Monthly Graphs &amp; Charts">Graphs</a></li>
	<li><a href="wx4.php" title="Extremes and Trends">Data</a></li>
	<li><a href="wx5.php" title="Full Local Forecasts and Latest Maps">Forecast</a></li>
	<li><a href="wx6.php" title="Sun and Moon Data">Astronomy</a></li>
	<li><a href="wx7.php" title="My Weather Photography">Photos</a>
		<ul> <?php for($i = 1; $i <= 10; $i++) { echo '<li><a href="album', $i, 'm.php" title="Photos from album ', $i, '">Album ', $i, '</a></li>'; } ?> </ul></li>
	<li><a href="wx8.php" title="About this Weather Station and Website">About</a></li>
	<li><a href="wx9.php" title="Useful Links">Links</a>
		<ul> <li><a href="http://nw3weather.co.uk/iwdl/" title="Mobile-optimised site version">Mobile Site</a></li>
			<li><a href="mob.php" title="Low-end Mobile-optimised site page">Mobile web page - resource unintensive</a></li>
		</ul></li>
	</ul><ul>
	<li><span style="color:#38610B"><b>Detailed Data:</b></span></li>
	<li><a href="wx12.php" title="Detailed Rain Data">Rain</a></li>	
	<li><a href="wx13.php" title="Detailed Wind Data">Wind</a></li>	
	<li><a href="wx14.php" title="Detailed Temperature Data">Temperature</a></li>
	<li><a href="wx10.php" title="Detailed Humidity Data">Humidity</a></li>
	<li><a href="wxaverages.php" title="Long-term climate averages">Climate</a>
		<ul> <li><a href="wxtempltas.php" title="Daily long-term average temperatures">Long-term average daily temperatures</a></li> </ul></li>
	<li><a href="wx15.php" title="System Status and Miscellaneous">System</a>
		<ul> <li><a href="hidden.htm" title="old home page 1">Old home page - Version 0</a></li>
			<li><a href="wx16.html" title="old home page 2">Old home page - Version 1</a></li>
			<li><a href="wxold.html" title="old home page 3">Old home page - Version 1.1</a></li> 
		</ul></li>
	</ul><ul>
	<li><span style="color:#0B614B"><b>Historical:</b></span></li>
	<li><a href="wxhist12.php" title="Detailed Historical Annual Rain Breakdowns">Annual Tables - Rain</a>
		<ul> <li><a href="wxhist14.php" title="Detailed Historical Annual Temperature Breakdowns">Annual Tables - Temperature</a></li>
			<li><a href="wxhist13.php" title="Detailed Historical Annual Wind Breakdowns">Annual Tables - Wind</a></li>
			<li><a href="wxhist10.php" title="Detailed Historical Annual Dew point">Annual Tables - Dew point</a></li>
			<li><a href="wxhist10.5.php" title="Detailed Historical Annual Relative humidity Breakdowns">Annual Tables - Relative humidity</a></li>
			<li><a href="wxhist0.php" title="Detailed Historical Annual Pressure Breakdowns">Annual Tables - Pressure</a></li>
		</ul></li>
	<li><a href="wxsumhist12.php" title="Detailed Historical Rain Summary Data">Summary Tables - Rain</a>
		<ul> <li><a href="wxhist14.php" title="Detailed Historical Summary Temperature Data">Summary Tables - Temperature</a></li>
			<li><a href="wxhist13.php" title="Detailed Historical Summary Wind Data">Summary Tables - Wind</a></li>
			<li><a href="wxhist10.php" title="Detailed Historical Summary Dew point">Summary Tables - Dew point</a></li>
			<li><a href="wxhist10.5.php" title="Detailed Historical Summary Relative humidity Data">Summary Tables - Relative humidity</a></li>
			<li><a href="wxhist0.php" title="Detailed Historical Summary Pressure Data">Summary Tables - Pressure</a></li> 
		</ul></li>
	<!--<li><a href="wxrecords.php" title="Detailed Historical Records">Records</a></li> -->
	<li><a href="wxhistmonth.php" title="Detailed Historical Monthly Reports">Monthly Reports</a></li>
	<li><a href="wxhistday.php" title="Detailed Historical Daily Reports">Daily Reports</a></li>
	<li><a href="Historical.php" title="Historical data about page and other links">Other</a>
		<ul> <li><a href="dailyreport.php" title="<?php echo $monthname, ' ', $date_year; ?> basic summary">Monthly weather summary</a></li>
			<li><a href="graphviewer.php" title="Weather data graphs archive">24hr Daily graphs Archive</a></li>
			<li><a href="wcarchive.php" title="Webcam image summaries archive">Daily Webcam image summaries Archive</a></li>
		</ul></li>
	</ul><ul>
	<li><a href="Beaufort Scale.php" title="Definition of Beaufort terms">Beaufort Scale</a></li>
	<li><a href="sitemap.php" title="Full website map/directory">Website map</a></li>
	</ul>
</div>

<!-- ##### Footer ##### -->
	<? require('footer.php'); ?>

</body>
</html>