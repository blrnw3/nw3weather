<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("main_tags.php");
		$file = 8; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - About</title>

	<meta name="description" content="Old v2 - Weather station and Website Information, accuracy, and comments, set-up, location, pictures" />

	<? require('chead.php'); ?>
	<?php include_once("ggltrack.php") ?>

	<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDwThmJT7UIczMkMIEq9CkaZ-mYoEhur58&amp;sensor=false"></script>
	<script type="text/javascript">
			//<![CDATA[
			function initialise() {
				var mapOptions = {
					center: new google.maps.LatLng(51.557, -0.156),
					zoom: 12,
					mapTypeId: google.maps.MapTypeId.ROADMAP
				};
				map = new google.maps.Map(document.getElementById("map"),
					mapOptions);

				var homeMarker = new google.maps.Marker({
					position: map.getCenter(),
					map: map,
					title: 'nw3weather home',
					icon: '/favicon.ico'
				});
				var nw3Bubble = new google.maps.InfoWindow({
					content: '<div style="width: 270px; padding-right: 20px; height: 125px"><h2>nw3weather base!</h2>\n\
						<img src="/static-images/gmaps3.jpg" alt="pic">'
				});
				google.maps.event.addListener(homeMarker, 'click', function() {
					nw3Bubble.open(map, homeMarker);
					map.setCenter(homeMarker.getPosition());
				});
			}
			//]]>
		</script>
</head>

<body onload="initialise()">
	<!-- For non-visual user agents: -->
	<div id="top"><a href="#main-copy" class="doNotDisplay doNotPrint">Skip to main content.</a></div>

	<!-- ##### Header ##### -->
<? require('header.php'); ?>

	<!-- ##### Left Sidebar ##### -->
<? require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->

	<div id="main-copy">

<h1> About </h1>

<h3>Weather Station</h3>
<p><b>Technical:</b> I am operating an Oregon Scientific WMR-200 and using Weather Display software to collect the data in real time,
which is then broadcast to this website. The Home page updates every minute (current data every 15s), and the webcam every minute; other live data is updated every 5 minutes, and the graphs every 10;
 the historical pages get new data at 00:10. It is, however, important to note that these updates will not always be carried out - software glitches,
internet connection losses and internet crashes can and do happen! Thankfully the WMR-200 has an onboard data logger,
so most, if not all data can be recovered in case of a computer crash.</p>

<p><b>Accuracy</b>: The data comes from a personal automatic weather station (PWS/AWS) so there can be no guarantees of accuracy or reliability,
and the data is not immediately quality controlled, but the setup shown below is designed to provide the most accurate readings possible.
For temperature and humidity readings this means shielding the sensors from
 <?php echo $stbfix; ?><acronym title="incident solar radiation">insolation</acronym><?php echo $enbfix; ?> and providing them with good airflow;
for rain data the sensor needs to be in as open a place as possible to avoid rain shadow, but not too open since a high wind can cause problems;
and for wind readings the sensors should be 10m above ground and clear from obstructions (to provide a 'clean' flow of air).
In an urban environment these conditions are difficult to meet, and this must be considered when using data from such sites,
especially when comparing them to non-urban sites. 
A more in-depth guide to siting weather stations can be found <a href="http://www.weatherstations.co.uk/gooddata.htm" title="Optimal siting of a PWS">here.</a> </p>
<p> It should also be noted that at no point do I quote figures for the errors associated with my readings, though of course they technically do exist.
This is partly due to conventions in displaying weather data, but also because it can be very difficult to ascertain such values, especially for urban sites:
For a given sensor the errors can vary widely from day to day and are sensitive to quite specific factors. 
All I can say is that I have tried to minimise errors where possible, but some large errors may well exist,
and where possible I have attempted to highlight these in my personal data sets (available <a href="Historical.php#xls" title="Historical data">here</a>).</p>

<a name="Forecasting"></a>
<p><b>A note on forecasting:</b> The accuracy of the local forecast on the home page is considered to be low, since it is based entirely on pressure
trends - falling pressure means cloud, and if it continues to fall, then rain;
rising pressure signals sunny intervals then clear conditions. This is obviously very simplistic. The forecasting
done by national meteorological offices requires the amassing of a huge amount of data from observation sites on land (like this one), 
on the sea, in the air and even from satellites in space. The data is then fed through supercomputers running numerical models
that supposedly represent science's current best view of extremely complex atmospheric processes.
The model outputs then need processing and interpreting before a forecast is produced.
The whole operation costs many millions of pounds to set up and run. Barometric prediction, on the other hand, is very cheap but has
severe limitations: it cannot offer regional guidance, it is only relevant for the next 24 hours or so, and it breaks down
completely in more complex atmospheric set-ups.</p>

<p><b>Extra Info:</b> Every 30 minutes the software downloads the latest weather report, a
 <?php echo $stbfix; ?><acronym title="aviation routine weather report"> METAR</acronym> <?php echo $enbfix; ?>
from the nearest airport, Heathrow;
this provides the cloud reports information found on the home page.
It is also useful for checking the reliability of my own readings from this<?php echo $stbfix; ?> <acronym title="personal weather station">PWS</acronym><?php echo $enbfix; ?>. 
Incidentally, the data gathered at airports, both commercial and military, around the world is a key component to the information needed by forecasting models,
which form the basis of modern weather forecasting.</p>

<a name="location"></a>

<p><b>Location:</b><br />
NW3 weather station is situated on the south-eastern edge of London's Hampstead Heath, a 790 acre ancient park just a few miles north of central London.
<a href="http://hampsteadheath.net/" title="External Link">View more information about The Heath.</a><br />
Nearby areas include Hampstead village to the North-West, Highgate to the North, Belsize Park to the South-West, and Kentish Town to the South East.</p>
	<div id="map" style="width: 500px; height: 300px"></div><noscript>Javascript required to display map<br /></noscript>
	 (note that Google Maps displays names out of position!)<p>
The station co-ordinates are approx. 51.556, -0.155, which is ~55m (190ft) above mean sea level.
</p>
<h3>Setup</h3>
The station comprises several sensors: a combined thermometer/hygrometer housed in a home-built Stevenson Screen (a radiation shield);
<br /> a combined anemometer &amp; wind vane, located atop a 6m pole attached to the chimney of the house;
<br /> a self-tipping rain gauge modified to a resolution of 0.3mm, which is attached to the roof of the Stevenson Screen;
<br />and a barometer integrated into the receiving unit (indoors).<br /><br />
<img src="/P1010070.JPG" alt="Thermo/Hygro and Rain Gauge" title="Thermo/Hygro in radiation shield, Rain Gauge on top" width="448" height="352" /> 
<img src="/P1010074.JPG" alt="Wind Sensors" title="Anemometer &amp; Wind Vane on top of pole" width="448" height="352" />
<p>All the sensors are automatic and send data wirelessly to the receiving unit every 14-60s.</p>
<img src="/PICT2516.JPG" alt="Thermo/Hygro close-up" title="Thermo/Hygro close-up inside radiation shield" width="348" height="242" /> 
<img src="/P1010076.JPG" alt="Wind Sensors close-up" title="Wind Sensors close-up" width="348" height="242" /> 
<br /><br />

<a name="History"></a>
<h3>History</h3>
<p>I started collecting data at this site on 28th July 2010. Prior to that I had been running the same setup in East Finchley, a town 5 km north of here, since February 2009,
but I have kept temperature records since March 2008.
Consequently, any records from before July 2010 are for East Finchley, though given the small distance involved, any data comparisons are largely valid.
However, there is one period which is an exception to this - from 17th April to 27th July 2010 the
<?php echo $stbfix; ?> <acronym title="Automatic Weather Station">AWS</acronym><?php echo $enbfix; ?> was on a temporary site
in Finchley Central (~1 km from East Finchley). This site was smaller and had more obstructions. Consequently I am not confident
in the accuracy of the maximum temperatures for this period - I believe them to be a little inflated (especially on sunny days).
Also, The rain gauge had more of a rain-shadow and so probably under recorded to some extent. Furthermore, I collected absolutely no
wind records for this period - I instead used the data from the METARs of Heathrow, which reports higher wind speeds due
to its greater exposure and better equipment. </p>
<p> I added a webcam (Logitech C300) to the station on 1st August 2010. This provides the latest and historical webcam images, as well as all the timelapses.
Another webcam was added on 19th December 2010 - this one is positioned to overlook the ground, so that snow cover and fog can be observed.
<br /><br />

<b>Technical history:</b></p>
<ul>
<li type="disc">The rain gauge was modified in March 2009 - the original had a resolution of only 1mm.
This involved fitting a funnel to the collecting channel so that less rain is needed to cause a 'tip' in the internal bucket
(each tip of 10ml results in a signal being sent to the receiving unit, which registers the rain).</li>
<li type="disc"> In July 2009 I built the radiation shield for the temp/hum sensor to improve the accuracy of the readings:
The walls are double louvred hardwood, the roof is double insulated plywood with a cavity for air flow, and
the whole thing is painted in white gloss. Maintenance is carried out twice a year, mainly to keep the paint glossy for maximal radiation reflection.</li>
<li type="disc">The combined wind sensor unit was mounted on a 6.1m aluminium-alloy aerial pole in August 2009.</li>
<li type="disc">A manual rain gauge was installed on 4th September 2011 - this is for the verification of the automatic gauge data. Results are pending,
but may result in a re-calibration of the electronic gauge.</li></ul>
<br />

<h3>Website</h3>
<p>The site was developed, and is therefore best viewed, in Firefox and on a widescreen display, but other browsers and aspect ratios have been tested and found to display most things correctly.
<br />I built the website in early September 2010, and it launched in a testing stage on 10th September 2010. This ended on the 20th September, but
some new content was still added upto April 2011, when work began on a more serious update.<br />

<a name="upgrade"></a>
<br /><b>Site version 2</b> launched on 10th September. The new site involves a change in the procedure for getting data, using 
<?php echo $stbfix; ?><acronym title="PHP: Hypertext Preprocessor (processed by the server into HTML before the page loads)">PHP</acronym><?php echo $enbfix; ?>, which
is also used extensively across the site to enable most of the new features and site-areas: obtaining all the historical data, displaying when
a record is set, the unit selection module, records for the current day (in the detailed pages), and a lot more besides.
The process for displaying live/latest data is now as follows: All data (as Weather Display 'tags') is locally created in a single PHP file,
each one with a different PHP variable name. This file is updated and uploaded every five minutes (another one with less data is updated every 1 minute for the home page)
 to the web server. The PHP files on the web server,the ones that visitors view and contain all the HTML, contain code which calls on those variables in the required places
 and subsequently results in their being rendered into HTML (hence the 'preprocessor' part of the PHP initialism). The source file for all the data can be viewed
 <a href="/phptags.php?sce=view" title="raw data">here</a>. A sample unprocessed web-page is available for viewing <a href="wx12.txt" title="wx12 - rain detail">here</a>.<br />
JavaScript and AJAX have also been deployed to develop some of the new features, in particular the auto-updating option.<br />
Another file is used to allow the 15s current data updates, and can viewed <a href="clientraw.txt" title="raw live data">here</a>.</p>


<p><b>Technical:</b> The CSS template, and some of the original HTML from site version 1, was sourced from 
<a href="http://www.carterlake.org/webtemplates.php" title="Weather Display Templates">carterlake.org</a>, though I have written all of the new code myself.
Thanks go to TNET weather and Wildwood Weather for script ideas which helped in the development of the historical data pages.
<br />The live data is automatically generated using Weather Display tags
(see <a href="wx9.php" title="Useful Links">links</a> page).
These convert the data from the software (Weather Display) to readable information on this site.
<br /> The process involved in converting readings from the sensors to the data viewable on this website can be summarised thus: <br />
<a href="/Sensors to WWW.jpg">
<img src="/Sensors to WWW3.jpg" alt="Data flow chart" title="Click for full-size image" width="589" height="318" border="0"></img></a> 
<br /> Each sensor wireless sends data to the base station, which renders this data and transmits it to my home PC (a dedicated low-power machine running Windows 7).
Weather Display software displays this data, and at every specified upload time, converts the tags in the locally-written files to viewable information.
It then uploads the newly generated HTML files (<b>Site version 1 only</b>, see above for new procedure) via
<?php echo $stbfix; ?> <acronym title="file transfer protocol">FTP</acronym><?php echo $enbfix; ?> to my hosting server - 
<a href="http://orchardhosting.com/" title="External Link">Orchard Hosting</a> - 
which broadcasts the web pages to the World Wide Web for anyone with an internet connection and web browser to view.</p>

</div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>

</body>
</html>