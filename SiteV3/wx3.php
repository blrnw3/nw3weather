<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php 
	$file = 3; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="pragma" content="no-cache" />

	<title>NW3 Weather - Latest Graphs</title>

	<meta name="description" content="Latest NW3 weather station daily and monthly charts and graphs - temperature, wind speed and direction, rainfall, dew point;
	wind direction plot; tempereature, wind, rain trends; wind rose." />

<?php require('chead.php'); ?>
<?php include_once("ggltrack.php");
	echo JQUERY; ?>

	<script type="text/javascript">
		function showMiniGraph() {
			$("#bigiGraph").fadeToggle("fast");
			$("#miniGraph").fadeToggle("slow");
		}
	</script>
</head>

<body>
	<!-- ##### Header ##### -->
	<?php require('header.php'); ?>
	<!-- ##### Left Sidebar ##### -->
	<?php require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->
	<div id="main">

<?php require('site_status.php'); ?>

<h1>Latest Graphs &amp; Charts</h1>

<h2>Past 24hrs</h2>
<?php
if(!$ukUnits) {
	echo '<img src="graphday.php?x=850&y=450" alt="graph 1" />
		<img src="graphday2.php?x=850&y=450" alt="graph 2" />
		<img src="graphdayA.php?x=850&y=220&type1=wdir" alt="graph 3" />
		';
	log_events("nonUKwx3.txt", "Imperial: ". makeBool($imperial) . ', Metric:  '. makeBool($metric));
} else {
	echo '<button onclick="showMiniGraph();"> Toggle Graph Size </button>
		<img id="bigiGraph" src="/stitchedmaingraph.png?id='. round(time()/10).'" alt="Graph of last 24hrs weather data" '. GRAPH_DIMS_LARGE .' />
		<img id="miniGraph" src="/stitchedmaingraph_small.png?id='. round(time()/10).'" alt="Mini-Graph of last 24hrs weather" style="display:none" '. GRAPH_DIMS_SMALL .' />
	';
}
?>
<p>To view different timescales, use the <a href="graphviewer.php">Custom Graphs</a> page.</p>

<h2>31-Day Trends</h2>
<img src="./graph31.php?type=tmean&amp;x=430" alt="31-Day temp Trends" /> <img src="./graph31.php?type=rain&amp;x=430" alt="31-Day rain Trends" />
<img src="./graph31.php?type=wmean&amp;x=430" alt="31-Day wind Trends" /> <img src="./graph31.php?type=pmean&amp;x=430" alt="31-Day pressure Trends" />

<h2>12-Month Trends</h2>
<img src="./graph12.php?type=tmean&amp;x=430" alt="12-Month temp Trends" /> <img src="./graph12.php?type=rain&amp;x=430" alt="12-Month rain Trends" />
<img src="./graph12.php?type=wmean&amp;x=430" alt="12-Month wind Trends" /> <img src="./graph12.php?type=pmean&amp;x=430" alt="12-Month pressure Trends" />
<p>More data types available on the <a href="charts.php">Charts</a> page.</p>

<table cellpadding="26" width="98%">
<tr> <td align="center"><h2>12-hr Wind Direction Plot</h2></td> <td align="center"><h2>Monthly Wind Rose</h2></td> </tr>
<tr> <td align="right"> <img src="/dirplot.gif" alt="12-hr wind direction radar plot" /> </td> <td><img src="/windrose.gif" alt="Latest month windrose" /> </td> </tr>
</table>

<br />
<a href="grapharchive.php" title="Daily graph archive starting Feb 2009">View archive of 24hr daily graphs</a>
</div>

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>

</body>
</html>