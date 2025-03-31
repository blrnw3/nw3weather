<?php require('unit-select.php');
		include $rareTags; ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php

$file = 5;
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="pragma" content="no-cache" />
		<title>NW3 Weather - Forecast | Latest Maps</title>
		<meta name="description" content="London Forecast and Latest European Weather Maps courtesy of NW3 weather, provided by external weather services." />

		<?php require('chead.php'); ?>
<?php include_once("ggltrack.php") ?>
	</head>

	<body>

		<!-- ##### Header ##### -->
		<?php require('header.php'); ?>
		<!-- ##### Left Sidebar ##### -->
<?php require('leftsidebar.php'); ?>

		<!-- ##### Main Copy ##### -->

<div id="main">

	<h1>Latest Forecasts and Weather Maps</h1>

	<p><b>NB:</b> NW3 is an observation site, which means it cannot provide detailed forecasts and therefore uses those that are externally produced.<br />
		Proper forecasting relies on running sophisticated computer models using data from a wide array of sources, including land-based observations sites like this one.
		A full discussion of this can be found <a href="wx8.php#Forecasting" title="Found on the about page">here</a>. </p>
	<p>
		Simplistic, local, live pressure-based forecast: <?php echo $vpforecasttext; ?>
	</p>

	<div id="widgets">
	<div>
		<h2>Hampstead Meteogram</h2>
		<img src="https://www.yr.no/en/content/2-2647553/meteogram.svg?mode=dark" alt="Yr.no meteogram for nw3" title="Yr.no meteogram for nw3" width="95%" height="100%" />
	</div>

	<div style="height: 550px; max-width: 900px;";>
		<h2>Forecast for NW3</h2>
		<iframe src="https://www.yr.no/en/content/2-2647553/table.html?mode=dark" width="95%" height="90%" frameborder="0" scrolling="no" title="Yr.no forecast for nw3"></iframe>
	</div>
	
	<div>
		<h2>Surface Pressure Chart</h2>
		<img src="http://www.weathercharts.net/noaa_ukmo_prognosis/PPVE89.gif" alt="fax" title="Surface pressure analysis chart from the Met Office" />
	</div>

</div>

</div>

		<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>
</body>
</html>