<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("main_tags.php"); 
	$file = 5; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="pragma" content="no-cache" />
	<title>NW3 Weather - Old(v2) - Forecast | Latest Maps</title>
	<meta name="description" content="Old v2 - London Forecast and Latest European Weather Maps courtesy of NW3 weather, provided by external weather services." />

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

<h1>Latest Forecasts and Weather Maps</h1>

<p><b>NB:</b> NW3 is an observation site, which means it cannot provide detailed forecasts and therefore uses those that are externally produced.<br />
Proper forecasting relies on running sophisticated computer models using data from a wide array of sources, including land-based observations sites like this one.<br />
A full discussion of this can be found <a href="wx8.php#Forecasting" title="Found on the about page">here</a>. </p>

<table cellpadding="5" border="0" width="99%" align="center">
<tr>
<td align="center"><h4>Met Office Forecast</h4></td>
<td align="center"><h4>Rainfall Loop</h4></td>
<td align="center"><h4>Jet Stream</h4></td></tr>
<tr>
<td width="30%" align="center"><script type="text/javascript" > moDays=5; moColourScheme="green"; moFSSI=3779; moDomain="www.metoffice.gov.uk"; moMapDisplay="side"; moMapsRequired="Precip Rate LR,Pressure MSL,Temperature LR"; moTemperatureUnits="C"; moSpeedUnits="M"; moShowWind="true"; </script ><script type="text/javascript" src="http://www.metoffice.gov.uk/public/pws/components/yoursite/loader.js">  </script></td>
<td width="35%" align="center"><img src="http://m.meteox.co.uk/images.aspx?jaar=-3&amp;voor=&amp;soort=loop3uur256&amp;c=uk&amp;n" alt="radar" width="300" height="300" title="Latest Rainfall Radar Loop for NW Europe" /></td>
<td width="35%" align="center"><img src="http://icons-ak.wunderground.com/data/images/eu_jt.gif" alt="Jet" width="384" height="288" title="Position of Jet Stream across Europe" /></td></tr>
<tr>
<td align="center"><i>Forecast produced using the latest <a href="http://www.metoffice.gov.uk/research/modelling-systems" title="UKMO Modelling Overview">Met Office</a> model output</i></td>
<td align="center"><i>NB: Times are 1hr ahead of UK</i></td>
<td align="center"><i>Source: <a href="http://www.wunderground.com/global/Region/EU/JetStream.html" title="Weather Underground Maps">Wunderground</a></i></td></tr>
</table><br /><br />

<table cellpadding="1" border="0" width="99%" align="center">
<tr>
<td align="center" valign="bottom"><h4>Netweather Forecast</h4></td>
<td align="center"><h4>Europe Wind Speeds</h4></td>
<td align="center"><h4>Europe Temperatures</h4></td></tr>
<tr>
<td width="30%" align="center"><a href="http://www.netweather.tv/" target="_blank"><img border="0" src="http://www.netweather.tv/4web2/netweather4webi.pl?lat=51.6;lon=-0.16;title=Hampstead;template=2" alt="Netweather" /></a> </td>
<td width="35%" align="center"><img src="http://icons-ak.wunderground.com/data/images/eu_ws.gif" alt="wind" width="384" height="288" title="Wind Speeds across Europe" /></td>
<td width="35%" align="center"><img src="http://icons-ak.wunderground.com/data/images/eu_st.gif" alt="temp" width="384" height="288" title="Temperatures across Europe" /></td></tr>
<tr>
<td align="center"><i>Forecast produced using the latest <a href="http://en.wikipedia.org/wiki/Global_Forecast_System" title="Global Forecast System">GFS</a> model output</i></td>
<td align="center"><i>Source: Wunderground</i></td>
<td align="center"><i>Source: Wunderground</i></td></tr>
</table><br /><br />

<table border="0" width="99%" align="center"><tr>
<td align="center" valign="bottom"><h4>Satellite Image</h4></td>
<td align="center"><h4>Surface Pressure Chart</h4></td>
</tr><tr>
<td width="50%" align="center"><img width="90%" src="http://www.sat24.com/images.php?country=gb&amp;sat=&amp;1208253450032" alt="satellite" title="Latest Satellite Image for NW Europe" /></td>
<td width="50%" align="center"><img width="95%" src="http://www.metoffice.gov.uk/weather/charts/FSXX00T_00.jpg" alt="fax" title="Surface pressure analysis chart from the Met Office" /></td>
</tr><tr>
<td align="center"><i>Source: <a href="http://www.sat24.com/gb" title="Link to Sat24 Website">Sat24</a></i></td>
<td align="center"><i>Source: UK Met Office</i></td><td></td></tr>
</table>

</div>	  

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>
</body>
</html>