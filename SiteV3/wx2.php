<?php require('unit-select.php');
require('functions.php');

	$file = 2;

	$risetime = (date_sunrise(time(), SUNFUNCS_RET_DOUBLE, $lat, $lng, $zenith, date('I')) - 1);
	$risesecs = 2.5 * $risetime;  // 2.5s per hour timelapse
	$mon_yest_zero = zerolead($mon_yest);
	$lastmonth = date("Y_m", mkdate($dmonth - 1, 1, $dyear));
	$lastyear = intval($dyear) - 1;
	$today_seek = (intval($dhr) - 2) < $risetime ? 0 : $risesecs;
	$yest_seek = $risesecs;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">


<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="pragma" content="no-cache" />

	<title>NW3 Weather - Webcam</title>

	<meta name="description" content="Hampstead, North London - Live Webcam and timelapses from NW3 weather station
	- last day and last hour timelapses, sky weathercam and ground weather cam." />

<?php require('chead.php'); ?>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.0/jquery.min.js"></script>
<script type="text/javascript">
	//<![CDATA[
	function loadVid(vid, seek, sel, noautoplay) {
		$("#skycam-selector span").removeClass("selected");
		$("#timelapse-" + sel).addClass("selected");

		var src = '/cam/timelapse/' + vid + '.mp4';
		console.log("Loading " + src);
		var vidBox = document.getElementById('timelapse');
		vidBox.innerHTML = '<video id="timelapse-vid" width="864" height="576" controls><source src="' + src + '" type="video/mp4"></video>';

		var vid = document.getElementById('timelapse-vid');
		vid.currentTime = seek;
		if(!noautoplay) {
			vid.play();
		}
	}
	//]]>
</script>

<?php include_once('ggltrack.php') ?>
</head>

<body>
	<!-- ##### Header ##### -->
	<?php require('header.php'); ?>

	<!-- ##### Left Sidebar ##### -->
	<?php require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->
	<div id="main">

<?php require('site_status.php'); ?>

<h1>Webcam</h1>

<h3>High-resolution Skycam</h3>

<p>The camera is a Hikvision 5MP H.265+ DS-2CD2055FWD-I with 4mm focal length, and is looking NE over Hampstead Heath.</p>
<img name="refresh-new" src="<?php echo $camImgNew; ?>" title="Latest new skycam" width="864" height="576" alt="skycam_new" />

<noscript>JavaScript is required for the automatic updates</noscript>

<p>The image is updated automatically every 10s, day and night, operating with a delay of about 20s.<br />
<a href="./skycam.php">Check out an even higher resolution version here</a>
<hr />

<br />
<?php if($time < $sunrise || $time > $sunset) {
		echo '<h3>Latest daylight webcam image</h3><img src="/skycam_sunset.jpg" alt="Latest sunsetcam" width="864" height="576" /><br /><br />';
	} ?>

</p>

<h3>Skycam images from the last 24 hours</h3>
<p>A <a href="highreswebcam.php" title="Full-resolution summary"><b>higher resolution version</b></a> is also available.</p>
<img title="Last 24hrs summary" src="/dailywebcam.jpg" alt="Webcam summary, past 24hrs" width="871" height="986" />
<br />
<a href="wcarchive.php" title="Webcam summary archive"><b>See full archive</b></a> (starting 01/08/10).

<hr />

<h3>Skycam Timelapses</h3>

<div id="skycam-selector">
	<span id="timelapse-0" onclick="loadVid('skycam_today', <?php echo $today_seek; ?>, 0)">Today</span>
	<span id="timelapse-1" onclick="loadVid('skycam_yest', <?php echo $yest_seek; ?>, 1)">Yesterday</span>
	<span id="timelapse-2" onclick="loadVid('<?php echo "skycam_monthly_${yr_yest}_${mon_yest_zero}"; ?>', 0, 2)">This month</span>
	<span id="timelapse-3" onclick="loadVid('<?php echo "skycam_monthly_${lastmonth}"; ?>', 0, 3)">Last month</span>
	<span id="timelapse-4" onclick="loadVid('<?php echo "skycam_yearly_${yr_yest}"; ?>', 0, 4)">This year</span>
	<span id="timelapse-5" onclick="loadVid('<?php echo "skycam_yearly_${lastyear}"; ?>', 0, 5)">Last year</span>
</div>

<div style="height: 586px" id="timelapse">Click on one of the options above to play</div>

<p>Today's timelapse is updated hourly. Monthly and annual timelapses update daily.
<br />
<a href="timelapsechive.php" title="Webcam timelapse archive"><b>See full timelapse archive</b></a>
</p>


</div>

<!-- ##### Footer ##### -->
	<?php require("footer.php"); ?>

</body>
</html>