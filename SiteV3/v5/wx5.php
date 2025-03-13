<?php

require("Page.php");
Page::init([
	"fileNum" => 6,
	"title" => "Forecast | Latest Maps",
	"description" => 'London Forecast and Latest European Weather Maps courtesy of NW3 weather, provided by external weather services.'
]);
Page::Start();

require Site::$rareTags;
?>
<h1>Latest Forecasts and Weather Maps</h1>

<p><b>NB:</b> NW3 is an observation site, which means it cannot provide detailed forecasts and therefore uses those that are externally produced.<br />
	Proper forecasting relies on running sophisticated computer models using data from a wide array of sources, including land-based observations sites like this one.
	A full discussion of this can be found <a href="wx8.php#Forecasting" title="Found on the about page">here</a>. </p>
<p>
	Simplistic, local, live pressure-based forecast: <?php echo $vpforecasttext; ?>
</p>

<table cellpadding="5" border="0" width="99%" align="center">
	<tr>
		<td align="center"><h4>Met Office Forecast</h4></td>
		<td align="center"><h4>Rainfall Loop</h4></td>
	</tr>
	<tr>
		<td width="30%" align="center">
			<script type="text/javascript">
				moDays = 5;
				moColourScheme = "green";
				moFSSI = 3779;
				moDomain = "www.metoffice.gov.uk";
				moMapDisplay = "side";
				moMapsRequired = "Precip Rate LR,Pressure MSL,Temperature LR";
				moTemperatureUnits = "C";
				moSpeedUnits = "M";
				moShowWind = "true";
			</script>
			<script type="text/javascript" src="http://www.metoffice.gov.uk/public/pws/components/yoursite/loader.js"></script>
		</td>
		<td width="35%" align="center"><img src="http://www.meteox.co.uk/images.aspx?jaar=-3&amp;soort=loop1uur" alt="radar" width="300" height="300" title="Latest Rainfall Radar Loop for NW Europe" /></td>
	</tr>
	<tr>
		<td align="center"><i>Forecast produced using the latest <a href="http://www.metoffice.gov.uk/research/modelling-systems" title="UKMO Modelling Overview">Met Office</a> model output</i></td>
		<td align="center"><i>NB: Times are 1hr ahead of UK</i></td>
	</tr>
</table>

<br /><br />

<table cellpadding="1" border="0" width="99%" align="center">
	<tr>
		<td align="center" valign="bottom"><h4>Netweather Forecast</h4></td>
		<td align="center" valign="bottom"><h4>Satellite Image</h4></td>
	</tr>
	<tr>
		<td width="30%" align="center"><a href="http://www.netweather.tv/" target="_blank"><img border="0" src="http://www.netweather.tv/4web2/netweather4webi.pl?lat=51.6;lon=-0.16;title=Hampstead;template=2" alt="Netweather" /></a> </td>
		<td width="70%" align="center"><img width="95%" src="http://www.sat24.com/images.php?country=gb&amp;sat=&amp;1208253450032" alt="satellite" title="Latest Satellite Image for NW Europe" /></td>
	</tr>
	<tr>
		<td align="center"><i>Forecast produced using the latest <a href="http://en.wikipedia.org/wiki/Global_Forecast_System" title="Global Forecast System">GFS</a> model output</i></td>
		<td align="center"><i>Source: <a href="http://www.sat24.com/gb" title="Link to Sat24 Website">Sat24</a></i></td>
	</tr>
</table>

<br /><br />

<h2>Surface Pressure Chart</h2>
<img src="http://www.weathercharts.net/noaa_ukmo_prognosis/PPVE89.gif" alt="fax" title="Surface pressure analysis chart from the Met Office" />
<i>Source: UK Met Office</i>

<br />

<h2><a href="http://www.wunderground.com/" title="Provided by The Weather Underground">Wunderground</a> European Live Weather Maps</h2>

<table border="0" width="99%" align="center">
	<tr>
		<td align="center"><h4>Temperature</h4></td>
		<td align="center"><h4>Wind Speed</h4></td>
	</tr>
	<tr>
		<td width="50%" align="center"><img src="http://icons-ak.wunderground.com/data/images/eu_st.gif" alt="Jet" width="384" height="288" title="Temperatures across Europe" /></td>
		<td width="50%" align="center"><img src="http://icons-ak.wunderground.com/data/images/eu_ws.gif" alt="wind" width="384" height="288" title="Wind Speeds across Europe" /></td>
	</tr>

</table>


<?php Page::End(); ?>
