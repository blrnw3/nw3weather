<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>NW3 Weather - Old(v2) - Mobile 2</title>

<meta name="description" content="Old v2 - NW3 weather mobile site version 2. Live data table and daily extremes. Optimised for mobile / handheld browsing" />
<meta name="HandheldFriendly" content="true" />
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="content-language" content="en-GB" />
<meta http-equiv="content-type" content="application/xhtml+xml; charset=ISO-8859-1" />
<?php if(isset($_GET['hits'])) { echo '<meta http-equiv="refresh" content="',$_GET['hits'],'" />'; } ?>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.0/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	$("#lol").load("./ajax/wx-body-mob<?php if(isset($_GET['type'])) { echo $_GET['type']; } ?>.php?randval="+ Math.random());
var refreshId = setInterval(function() {
	$("#lol").load('./ajax/wx-body-mob<?php if(isset($_GET['type'])) { echo $_GET['type']; } ?>.php?randval='+ Math.random());
}, <?php if(isset($_GET['reload'])) { echo $_GET['reload']*1000; } else { echo 30000; } ?>);
	$.ajaxSetup({ cache: false });
});
</script>
</head>

<body>
<div id="lol">
<p>Current data table loading... Please wait </p>
<noscript><p><b>Warning:</b> Javascript required <br /></p></noscript>
</div>
Links: <ul><li><a href="index.php">Main Site</a></li>
<li><a href="mob.php?img=1#graph">Latest Graph</a></li>
<li><a href="/iwdl/">In-depth Mobile Site</a></li></ul>

<?php if(isset($_GET['img']) && $_GET['img'] == 1) { echo '<img src="curr24hourgraph.gif?', time(), '" alt="24hr weather graph" title="Latest weather data over the last 24hrs" />'; }

if(isset($_GET['img']) && $_GET['img'] == 2) {
	include("main_tags.php");
	$icon[0] = 'day_clear.gif';
	$icon[1] = 'night_clear.gif';
	$icon[2] = 'day_partly_cloudy.gif';
	$icon[3] = 'day_partly_cloudy.gif';
	$icon[4] = 'night_partly_cloudy.gif';
	$icon[5] = 'day_partly_cloudy.gif';
	$icon[6] = 'fog.gif';
	$icon[7] = 'haze.gif';
	$icon[8] = 'day_heavy_rain.gif';
	$icon[9] = 'day_mostly_sunny.gif';
	$icon[10] = 'mist.gif';
	$icon[11] = 'fog.gif';
	$icon[12] = 'night_heavy_rain.gif';
	$icon[13] = 'night_cloudy.gif';
	$icon[14] = 'night_rain.gif';
	$icon[15] = 'night_light_rain.gif';
	$icon[16] = 'night_snow.gif';
	$icon[17] = 'night_tstorm.gif';
	$icon[18] = 'day_cloudy.gif';
	$icon[19] = 'day_partly_cloudy.gif';
	$icon[20] = 'day_rain.gif';
	$icon[21] = 'day_rain.gif';
	$icon[22] = 'day_light_rain.gif';
	$icon[23] = 'sleet.gif';
	$icon[24] = 'sleet.gif';
	$icon[25] = 'snow.gif';
	$icon[26] = 'snow.gif';
	$icon[27] = 'snow.gif';
	$icon[28] = 'day_clear.gif.gif';
	$icon[29] = 'day_tstorm.gif';
	$icon[30] = 'day_tstorm.gif';
	$icon[31] = 'day_tstorm.gif';
	$icon[33] = 'windy.gif';
	$icon[34] = 'day_partly_cloudy.gif';
	$icon[35] = 'windyrain.gif';
	echo '<img src="/static-images/',$icon[$iconnumber],'" alt="Current weather icon" title="Current Weather" /><br />';
}

if(isset($_GET['hits'])) { echo $_SERVER['REMOTE_ADDR'], '<br /><span>
		<a href="http://www.martynhicks.co.uk/weather/topsites/index.php?a=in&amp;u=Timmead">
		<img src="http://www.martynhicks.co.uk/weather/topsites/button.php?u=Timmead" alt="UK - WEATHER STATION TOPSITES - UK" border="0" /></a>
		<a href="http://www.axelvold.net/weather_topsites/"><img src="http://www.axelvold.net/weather_topsites/button.php?u=Timmead" alt="Weather Topsites" border="0" /></a>
	</span>'; } 
?>
<a name="graph"></a>
</body>
</html>