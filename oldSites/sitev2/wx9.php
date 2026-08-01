<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("main_tags.php");
	$file = 9; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - Useful Links</title>

	<meta name="description" content="Old v2 - NW3 weather's External Links to other useful weather forecasting &amp; monitoring websites." />
	
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

	<h1>Useful/Interesting Weather-related Links</h1>
	
	<h3>My Favourites - Forecasting</h3> 

		<ul>
		<li> <a href="http://www.netweather.tv/index.cgi?action=nwdc;sess="
			title="Netweather Charts">
			Weather forecasting charts</a></li>
		<li> <a href="http://www.wxmaps.org/pix/temp4.html"
			title="Maps of temp and precip">
			Next 10 days European temperatures and precipitation </a></li>
		<li> <a href="http://www.estofex.org/"
			title="ESTOFEX">
			Storm forecasts for Europe</a></li>
		</ul>


	<h3>My Favourites - Tracking</h3> 

		<ul>
		<li>	<a href="http://www.metoffice.gov.uk/weather/uk/se/se_latest_weather.html"
			title="Met Office Latest/Recent">
			Latest official weather across London and SE of England</a></li>
		<li>	<a href="http://www.raintoday.co.uk/"
			title="Rain Today UK Radar">
			High resolution rainfall radar</a></li>
	<li>	<a href="http://www.sat24.com/gb"
			title="Meteostat satellite loops">
			Satellite Imagery</a></li>
	<li>	<a href="http://andvari.vedur.is/athuganir/eldingar/i_dag_na.html"
			title="ATD reports from Icelandic(!) Met site">
			Latest lightning strikes across UK</a></li>
		<li>	<a href="http://www.nhc.noaa.gov/index.shtml"
			title="National Hurricane Center">
			Hurricane tracking</a> (May-Nov only)</li>
	<li>	<a href="http://www.osdpd.noaa.gov/ml/ocean/sst/anomaly.html"
			title="NOAA SSTAs">
			Latest global sea surface temperature anomalies</a></li>
		
		</ul>

	<h3>Weather Software and Publication</h3> 

		<ul>
		<li>	<a href="http://www.weather-display.com"
			title="Weather Station Software">
			Weather Display</a></li>
		<li>	<a href="http://www.weather-watch.com/smf/"
			title="Weather Forums">
			Weather Watch Forums</a></li>
		<li>	<a href="http://www.wunderground.com/weatherstation/index.asp"
			title="Weather Underground">
			Weather Underground Personal Station Signup</a></li>
		
		</ul>

	<h3>Weather Education</h3> 

		<ul>
		<li>	<a href="http://www.tsgc.utexas.edu/stars/metgloss.html"
			title="Meterology Terms">
			Glossary of Meteorology</a></li>
		<li>	<a href="http://www.metoffice.gov.uk/corporate/library/factsheets.html"
			title="Met Office Factsheets - really good!">
			Meteorological Factsheets from the Met Office</a></li>
	<li>	<a href="http://en.wikipedia.org/wiki/Weather_forecasting"
			title="Weather Forecasting Methods">
			How Weather Forecasting Works</a></li>
	<li>	<a href="http://forum.netweather.tv/topic/19418-hurricane-tutorial/"
			title="From Netweather Forums">
			Hurricane Tutorial</a></li>
		
		</ul>

	<h3>Weather Station Info</h3> 

		<ul>
		
		<li>	<a href="http://www.oregonscientific.co.uk/cat-Weather-sub-Professional.html"
			title="Oregon Scientific">
			Oregon Scientific</a></li>
	<li>	<a href="http://www.weatherstations.co.uk/gooddata.htm"
			title="Pro Data">
			Tips on siting of sensors</a></li>
			<li>	<a href="http://users.otenet.gr/~meteo/project_stevenson-screen-box.html"
			title="The site I used to help me build mine">
			Constructing a Stevenson Screen</a></li>

	</ul>

	<h3>Website Design</h3> 

		<ul>
		<li>	<a href="http://www.w3schools.com/html/default.asp"
			title="W3schools HTML tutorial">
			Writing HTML</a></li>
					
		<li>  <a href="http://www.php.net/manual/en/language.functions.php"
			title="PHP function reference">
			PHP manual - for adding PHP functionality</a></li>
	
		<li>	<a href="http://www.newton-noss.co.uk/weather/tech/weather_scripts/wd_tags/tagsref.php"
			title="WD Web Development">
			Weather Display Tags</a></li>
		<li>	<a href="http://validator.w3.org/"
			title="W3C XHTML/HTML validator service">
			Web page markup validation</a></li>		
		</ul>
		
	<hr />	
<br />
<h3>Mobile-optimised versions of NW3 weather are also available:</h3><ul>
<li><a href="http://nw3weather.co.uk/iwdl/" title="Mobile-optimised site version">High-end, smartphone suitable site</a> - with live data as well as graphs and records</li>
<li><a href="mob.php" title="Mobile-optimised site version">Low-end, resource-unintensive page</a> - with live data and daily extremes (like the data table on the home page)</li>
</ul>

<br />
<h1>Other</h1>

<table cellpadding="10"><tr><td style="font-weight:bold; font-size:110%">
<a href="http://www.wunderground.com/weatherstation/WXDailyHistory.asp?ID=ILONDONL9"
			title="Weather Underground Hampstead PWS">
			My Wunderground page</a> - includes real time updates and data logs<br />
<a href="http://www.wunderground.com/weatherstation/WXDailyHistory.asp?ID=ILONDONL9">
<img src="http://banners.wunderground.com/cgi-bin/banner/ban/wxBanner?bannertype=WeatherStationCount&amp;weatherstationcount=ILONDONL9"
 height="160" width="163" border="0" alt="Weather Underground PWS ILONDONL9" /></a>
</td>
<td style="font-weight:bold; font-size:110%">
<a href="http://wow.metoffice.gov.uk/latestobservationservlet?siteID=3329071" title="Link to my UKMO WOW Site">My UK Met Office WOW page<br />
<img src="/static-images/WOWbanner.png" alt="WOW banner" /> </a></td></tr></table>

<br />
<h4>Member of UK and Global "Weather Station Topsites" networks</h4>
<a href="http://www.martynhicks.co.uk/weather/topsites/index.php?a=in&amp;u=Timmead">
<img src="http://www.martynhicks.co.uk/weather/topsites/button.php?u=Timmead" alt="UK - WEATHER STATION TOPSITES - UK" border="0" /></a>
<a href="http://www.axelvold.net/weather_topsites/"><img src="http://www.axelvold.net/weather_topsites/button.php?u=Timmead" alt="Weather Topsites" border="0" /></a>

</div>

<!-- ##### Footer ##### -->
	<? require('footer.php'); ?>

</body>
</html>