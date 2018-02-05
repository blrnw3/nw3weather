<?php require('unit-select.php');
		include $rareTags;
	$file = 1;

	require_once 'functions.php';
	$mainDataCritical = true; //see mainData.php
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
<meta name="author" content="Ben Lee-Rodgers" />

	<title>NW3 Weather - Live and historical weather from Hampstead, London</title>

	<meta name="description" content="Live weather data from a personal automatic weather station located in Hampstead, North London." />

<?php require('chead.php'); ?>
<?php include_once("ggltrack.php") ?>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.0/jquery.min.js"></script>

<script type="text/javascript">
//<![CDATA[
var updateable = true;

var timeServer;
var timeWD;

var wxvars = new Array();
var wxvarsNew = new Array();

$(document).ready(function() {
	timeWD = $("#WDtime").val();
	timeServer = $("#Servertime").val();
	updater();
});

function refreshImage(id) {
	var patt = new RegExp("currid=[0-9]+");
	var url = $("#"+id).attr('src');
	var match = patt.exec(url);
	$("#"+id).attr( 'src', url.replace(match, "currid="+ timePHP) );
}

var currImage = true;
function imgSwap(id) {
	var num1 = (currImage ? 1 : 3) + id -1;
	var num2 = (!currImage ? 1 : 3) + id -1;
	$("#graph"+id).attr( 'src', $("#graph"+id).attr('src').replace('Graph'+num1, 'Graph'+num2) );
	currImage = !currImage;
}

var currCam = true;
function camChange() {
	var num1 = currCam ? '' : 'g';
	var num2 = !currCam ? '' : 'g';
	$("#cam").attr( 'src', $("#cam").attr('src').replace('curr'+num1, 'curr'+num2) );
	currCam = !currCam;
}

var cnt = 0;
function newify() {
	wxvars = JSON.parse( $("#newData").val() );
//	console.log($("#newData").val());
	 $.ajax({
       url: "ajax/wx-body.php",
       dataType: "html",
       cache: false,
       success: function ( data, textStatus, jqXHR ) {
				$("#lol").html(data);
				timeWD = $("#WDtime").val();
				timeServer = $("#Servertime").val();
//				console.log($("#newData").val());
				wxvarsNew = JSON.parse( $("#newData").val() );

				for(var i = 0; i < wxvars.length; i++) {
					if(wxvars[i] !== wxvarsNew[i]) {
						var colour = (wxvars[i] > wxvarsNew[i]) ? 'red' : 'green';
						$("#var"+i).attr("style", "color:" + colour);
					} else {
						$("#var"+i).attr("style", "color:black");
					}
				}

				cnt++;
			}
		});
}

var autoupdateCount = 0;
var totalCnt = 1;
function updater() {
	timeServer++;
	var elapsedSeconds = timeServer - timeWD - 1;
	var target = document.getElementById('elapsedTime');

	if(elapsedSeconds > 99) {
		elapsedSeconds = '>99';
		target.style.color = 'red';
	} else {
		target.style.color = (elapsedSeconds < 5) ? 'green' : 'black';
	}
	var message = (elapsedSeconds < 0 || elapsedSeconds > 9999) ? '' : ' - ' + elapsedSeconds + ' s ago';

	target.innerHTML = message;

	if(updateable && totalCnt % 20 === 0) {
		if(autoupdateCount < 30) {
			newify();
			autoupdateCount++;
		} else {
			$("#info").html(" - AutoUpdates paused. Click to resume");
		}
	}

	if(dateJS.getMinutes() % 5 === 0 && dateJS.getSeconds() === 10) {
		refreshImage('graph1');
		refreshImage('graph2');
		console.log("refreshing images");
	}

	totalCnt++;
	setTimeout('updater()', 1000);
}

function resume() {
	autoupdateCount = 0;
	$("#info").html("");
	newify();
}
function pause() {
	var pr = updateable ? 'Resume' : 'Pause';
	var colr = !updateable ? '3a4' : 'a34';
	$("#pauser").html(pr +" live updates");
	$("#pauser").attr("style", "color:#" + colr);
	updateable = !updateable;
	if(updateable) {
		resume();
	}
}
//]]>
</script>
<style type="text/css">
	#pauser {
		font-size:90%;
		border-bottom: 1px dotted;
	}
</style>
</head>

<body onload="camRefesh();">
	<!-- ##### Header ##### -->
	<?php require('header.php'); ?>

	<!-- ##### Left Sidebar ##### -->
	<?php require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->
	<div id="main">

<?php require('site_status.php'); ?>

		<h1>Hampstead nw3, London - Current Weather</h1>

<div>
<table width="99%" cellpadding="2" cellspacing="0" align="center" border="0" rules="none">
<tr class="rowdarkmain">
<td width="25%" align="center"><b><span style="color:#610B0B">Weather Report</span></b>
<br /><br /><?php //decode WD cond, using metar too
$rnrt = $HR24['misc']['rnrate'];
$nw3Raining = (($HR24['trendRn'][0] - $HR24['trendRn'][1]) > 0);
$METAR = file_get_contents("METAR.txt");

//weather
$weather = 'Dry'; //default
if($nw3Raining) { //Rained in past hour
	//Detect showers by 30-min temp/hum change
	$isShower = ( $HR24['trend'][0]['temp'] - $HR24['trend'][30]['temp'] <= -0.3
		|| $HR24['trend'][0]['hum'] - $HR24['trend'][30]['hum'] >= 5 );
	$rnType = $isShower ? 'Shower' : 'Rain';

	//Detect intensity based on current rain rate
	//If only 0.3mm, no rate is available (i.e. 0), so give no intensity
	$intensities = array('', 'Slight', 'Light', 'Moderate', 'Heavy', 'Very Heavy', 'Torrential');
	$intensityThresholds = array(0.1, 0.5, 2, 8, 35, 60, 500);
	for ($i = 0; $i < count($intensityThresholds); $i++) {
		if($rnrt < $intensityThresholds[$i]) {
			$intensity = $intensities[$i];
			break;
		}
	}
	$lastrnThresh = $isShower ? 12 : 20;
	$rn_ago = (time() - strtotime($HR24['misc']['prevRn']));
	if($rn_ago > ($lastrnThresh * 60)) {
		$intensity = 'Recent';
	}
	$weather = $intensity .' '. $rnType;
} else { // check the METAR
	$metarRaining = strContains($METAR, array('RA','DZ'));
	$foggy = strContains($METAR, array('FG', 'BR'));
	$snowing = strContains($METAR, array('SN','SG'));
	$showery = strContains($METAR, 'SH');
	$stormy = strContains($METAR, 'TS');

	$METARactives = array($snowing, $metarRaining, $foggy, $stormy);
	$METARdescrips = array('Snow', 'Rain', 'Mist/Fog', 'Thunderstorm');
	foreach ($METARactives as $i => $wxMetar) {
		if($wxMetar) {
			$weather = $METARdescrips[$i];
			if($showery) {
				$weather .= ' Showers';
			}
			$weather .= ' Nearby';
			break;
		}
	}
}

//cloud
$cloud = ($nw3Raining || $metarRaining || $foggy || $snowing) ? 'Cloudy' : 'Clear'; //default
$cumulonimbus = strContains($METAR, array('CB')) ? acronym('Cumulonimbus cloud', 'Cb cloud', true) ." observed" : "";
$METARcloudTypes = array('OVC', 'BKN', 'SCT', 'FEW', 'NSC');
$METARcloudDescrips = array('Overcast', 'Mostly cloudy', 'Partly cloudy', 'Mostly clear', 'Cloudy');
foreach ($METARcloudTypes as $i => $cloudSrch) {
	if(strContains($METAR, $cloudSrch)) {
		$cloud = $METARcloudDescrips[$i];
		break;
	}
}

echo "<b>$weather</b>; ". acronym("Raw METAR: ". $METAR, $cloud);
?> </td>

<td width="30%" rowspan="3" align="center"><b><span title="Clickable!" onclick="camChange();" style="color:#336666">Weathercam</span></b>
<br /><br />
<?php $img = 'currcam_small.jpg'; ?>
<a href="wx11.php">
	<img id="cam" name="refresh" border="0" src="<?php echo $camImg; ?>" title="Click to enlarge" alt="Web cam" width="236" height="177" /></a>
<br />
<a href="wx2.php" title="Full webcam image and timelapses">See more</a>

</td>
<td width="45%" rowspan="3" align="center">
<?php
if($imperial) {
	$img1 = 'graphdayA.php?type1=temp&amp;type2=rain&amp;ts=12&amp;x=400&amp;y=160&amp;nofooter';
	$img2 = 'graphdayA.php?type1=hum&amp;type2=dew&amp;ts=12&amp;x=400&amp;y=160';
	$click = '';
} else {
	$timeID = date('dmYHi');
	$img1 = '/mainGraph1.png?reqid='. $timeID;
	$img2 = '/mainGraph2.png?reqid='. $timeID;
	$click = $metric ? 'title="You can click, but the units will be mph!"' : 'title="Click to change graph variables" ';
}
echo '<img '.$click.'id="graph1" src="'.$img1.'&amp;currid='. time().'" alt="Last 12-hours weather" onclick="imgSwap(1);" width="400" height="160" />
	  <img '.$click.'id="graph2" src="'.$img2.'&amp;currid='. time().'" alt="Last 12-hours weather" onclick="imgSwap(2);" width="400" height="160" />';
?>
</td>
</tr>

<tr class="rowdarkmain">
<td><span style="color:rgb(243,242,235)">-</span></td></tr>
<tr class="rowdarkmain">
<td align="center"><b><span style="color:#6A4EC6">Local Forecast</span></b>
<br /><br />
<?php
$fcast = file_get_contents("WUforecast.txt");
$icon = 'cloudy';
$forecastTerms = array('Rain', 'Clear', 'Partly', 'Thunderstorm', 'Snow');
$forecastIcons = array('rain', 'clear', 'partlycloudy', 'tstorms', 'snow');
for($i = 0; $i < count($forecastIcons); $i++) {
	if(strpos($fcast, $forecastTerms[$i]) !== false) {
		$icon = $forecastIcons[$i];
		break;
	}
}
if($i <= count($forecastIcons) && strpos($fcast, "Chance") !== false) {
	$icon .= '_showers';
}
elseif( $time > $sunset && ($icon == 'clear' || $icon == 'partlycloudy') ) {
	$icon = 'nt_'. $icon;
}
echo '<img src="/static-images/'.$icon.'_lg.png" style="background-color:#CCCEEC;" title="'.$fcast.'" width="83" height="81" alt="London Forecast icon" />';
?>
<br />
<a href="wx5.php" title="5-Day Local Forecast and Maps">Full forecast</a></td></tr>

<tr class="rowlight">
<td colspan="3" align="center"><table align="center" class="table2" width="100%"><tr class="rowlight">
<td><b>NW3 Weather</b> is a meteorological observation site located near Hampstead, in North London, UK. <br />
	The site was established with an <a href="wx8.php" title="Detailed station and website information">automatic personal weather station</a>
	in July 2010 and runs continuously, with updates at least every 60s.
</td></tr>
</table></td></tr>
</table>

<h1>Live Weather</h1>
</div>

<div id="lol">
	<?php include('ajax/wx-body.php'); ?>

	<noscript><p><b>Warning:</b> Javascript must be enabled for live updates to function</p></noscript>
</div>
<span id="pauser" style="color:#3a5;" onclick="pause();">
	Pause live updates
</span>
<p>Live pressure-based <a href="/wx5.php" title="View detailed forecasts">forecast</a>: <?php echo $vpforecasttext; ?></p>

<!--
<h1 id="2017-review">2017 Weather Report</h1>
<p style="margin-bottom: 0.5em;" id="report-2017">

<p>
<a href="/TablesDataMonth.php?vartype=rain" title="Monthly Rain Records">
	These pages are useful for comparing all aspects of the weather across the years
</a>.
</p>
<p>
<h2>Weather cam timelapse for 2017 (9am - 3pm)</h2>
<video width="640" height="480" controls>
  <source src="/camchive/skycam_2017_9to3.mp4" type="video/mp4">
Your browser does not support the video tag.
</video>
<br />
<a href="/repyear.php">Report is also archived along with previous years' reports</a>
</p>
</p>
<span style="color:#555; font-size: 90%">
	Produced: 15th Jan 2018
</span>
-->

<a href="/repyear.php">View nw3weather's 2017 Annual Report</a>

<h1>Latest Monthly Weather Report</h1>
<?php
$repStamp = mkdate($dmonth-1, 1);
$repMonth = date('n', $repStamp);
$repYear = date('Y', $repStamp);
displayMonthlyReport($repMonth, $repYear);
?>

<div id="twitter-feed" style="margin: 2em">
	<a class="twitter-timeline"
		href="https://twitter.com/nw3weather"
		data-widget-id="650374428325474304"
		data-chrome="nofooter transparent">
		Tweets by @nw3weather
	</a>
	<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>

</div>

<p style="margin-top: 2em;">This weather station has been recording data for
	<abbr title="Since 1st Feb 2009"><b> <?php echo intval((mkdate($dmonth,$dday,$dyear)-mkdate(2,1,2009))/(24*3600)); ?></b></abbr> days
	(<abbr title="Since 18th Jul 2010"><?php echo intval((mkdate($dmonth,$dday,$dyear)-mkdate(7,18,2010))/(24*3600)); ?></abbr> at NW3)
</p>
</div>
<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>
</body>
</html>
<?php
function displayMonthlyReport($mon, $yr) {

	$repFile = ROOT. $yr."/report$mon.php";
	if(!file_exists($repFile)) { //try previous month
		$repStamp = mkdate($mon-1, 1, $yr);
		$repMonth = date('n', $repStamp);
		$repYear = date('Y', $repStamp);
		$repFile = ROOT.$repYear."/report$repMonth.php";
	}
	if(file_exists($repFile)) {
		include $repFile;
	}
	else {
		echo 'Report not available.';
		return null;
	}

	$repMonth = $export['date'][0];
	$repYear = $export['date'][1];

	$tempComparator = $export['temp'][0];
	$tempAv = conv($export['temp'][1], 1);
	$tempAnom = conv($export['temp'][2], 1.1, true, true);
	$tempLo = conv($export['temp'][3], 1);
	$tempHi = conv($export['temp'][4], 1);

	$rainComparator = $export['rain'][0];
	$rainAv = conv($export['rain'][1], 2);
	$rainAnom = $export['rain'][2];
	$rainCnt = $export['rain'][3];
	$rainHi = conv($export['rain'][4], 2);
	$rainYr = conv($export['rain'][5], 2);
	$rainYrAnom = $export['rain'][6];
	$rainYrCnt = $export['rain'][7];

	$sunComparator = $export['sun'][0];
	$sunAv = conv($export['sun'][1], 9);
	$sunAnom = $export['sun'][2];
	$sunMax = $export['sun'][3];
	$sunCnt = $export['sun'][4];
	$sunHi = $export['sun'][5];

	$notWintry = ($export['winter'][0] == 0 && $export['winter'][1] == 0);
	$fallSnow = $export['winter'][2];
	$fallSnowAnom = $export['winter'][3];
	$fallSnowAnom2 = $export['winter'][4];
	$AFsFull = $export['winter'][5];
	$AFavr = $export['winter'][6];
	$lySnow = $export['winter'][7];
	$LSavr = $export['winter'][8];
	$maxDepth = conv($export['winter'][9], 6);

	$hail = $export['other'][0];
	$thunder = $export['other'][1];
	$fog = $export['other'][2];
	$bigRnsFull = $export['other'][3];
	$mm10 = conv($export['other'][4], 2, true, false, -1);
	$bigGusts = $export['other'][5];
	$mph30 = conv($export['other'][6], 4, true, false, -1);

	$output = "<h2>".date('F Y', mkdate($repMonth, 1, $repYear)) ."</h2>
		<dl>
		<dt class='temp'>Temperature</dt>
		<dd>Overall, the month was $tempComparator average, with a mean of <b>$tempAv</b> ($tempAnom from the <abbr title='Long-term average'>LTA</abbr>).
			<br />The absolute low was <b>$tempLo</b>, and the highest <b>$tempHi</b>.
		</dd>
		<dt class='rain'>Rainfall</dt>
		<dd>Came in $rainComparator the long-term average, at <b>$rainAv</b> ($rainAnom%) across <b>$rainCnt</b> days of <abbr title='&ge;0.2mm'>recordable rain</abbr>.
			The most rainfall recorded in a single day (starting at midnight) was <b>$rainHi</b>.
			The cumulative annual total for $repYear now stands at <b>$rainYr</b> ($rainYrAnom%) from <b>$rainYrCnt</b> rain days.
		</dd>
		<dt class='sun'>Sunshine</dt>
		<dd>A $sunComparator month, with <b>$sunAv</b> ($sunAnom%) from a possible $sunMax. <br />
			<b>$sunCnt</b> days had more than a minute of sunshine, the maximum being <b>$sunHi hrs</b>.
		</dd>
		<dt class='snow'>Winter Events</dt>
		<dd>";
	$output .= $notWintry ?
		"No snow or frost observed." :
		"There $fallSnow of falling snow or sleet
		($fallSnowAnom $fallSnowAnom2 the <abbr title='Long-term average'>LTA</abbr>),
			and $AFsFull ($AFavr). <br />
		$lySnow of lying snow at 09z were observed ($LSavr), with a max depth of <b>$maxDepth</b>.
		";
	$output .= "</dd>
		<dt>Other Events</dt>
		<dd>There $hail of hail, <b>$thunder</b> of thunder, <b>$fog</b> with fog at 09z.
			$bigRnsFull had &gt;$mm10 of rain, and <b>$bigGusts</b> with gusts &gt;$mph30.
		</dd>
		</dl>
		<p>
		All long-term <a href='wxaverages.php' title='Long-term NW3 climate averages'>climate averages</a>
		are with respect to the period 1971-2000. &nbsp;
		<a href='wxhistmonth.php'>View full report</a>
		</p>
		";

	echo $output;
}
?>