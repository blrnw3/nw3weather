<?php require('unit-select.php');
	$file = 14;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Temperature Detail</title>

	<meta name="description" content="Detailed latest/current temperature data and records from NW3 weather station." />

	<?php require('chead.php'); ?>
	<?php include('ggltrack.php') ?>
</head>

<body>

	<!-- ##### Header ##### -->
	<?php require('header.php'); ?>

	<!-- ##### Left Sidebar ##### -->
	<?php require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->

	<div id="main">

	<?php require('site_status.php'); ?>

<?php require $root.'TemperatureTags.php'; ?>

<h1>Detailed Temperature Data</h1>

<?php
require './detailDataModules.php';
$mainTables = new DetailedDataModule("temp");

$tchangehour = conv($HR24['changeHr']['temp'], 1.1, true, true);
$measures = array('Temperature','Temperature Trend / hr','Feels-like','Night Minimum (21-09)','Day Maximum (09-21)','24hr Average',
	'Daily Air Frost Hours', 'Monthly Air Frosts', 'Annual Air Frosts');
$values = array($temp, $tchangehour, $feel, $nighttimeMin, $daytimeMax, $last24houravtemp,
	$hrsfrostmidnight, $daysTminL0C, $daysTminyearL0C );
$conv = array(1,false,1,1,1,1,false);

$mainTables->currentLatest($measures, $values, $conv);
$mainTables->recentAvgsExtrms();
$mainTables->graph31dump();

$measures2 = array('Lowest Min','Highest Max','Highest Min','Lowest Max','Coldest Day','Warmest Day','Averages','Mean','Mean Minimum','Mean Maximum');
$mainTables->avgsExtrmsRecs($measures2);

echo '<br />';

$mainTables->pastYearAvgsExtrms($measures2);

echo '<br />';

$mainTables->seasonalAvgs();
$mainTables->graph12Dump();
$mainTables->recordPeriodAvgs();

$mainTables->rankTables();
?>

<h2>Notes</h2>
<ul>
	<li>Temperature records began on 1st Jan 2009</li>
	<li>Figures in brackets refer to departure from
		<a href="wxaverages.php" title="Long-term NW3 climate averages">average conditions</a>
	</li>
	<li>An air frost requires the <b>overnight</b> (21-09) temperature to fall <i>below</i> freezing</li>
	<li>All figures, unless specified, relate to the period midnight-midnight, this being when daily extremes are reset.</li>
</ul>

<b>Definition of 'Feels-like' temperature:</b> This displays either the Wind Chill index or the
'Humidex', depending on the temperature and humidity.
These indices are attempts to depict what the air actually feels like on a human's skin -
 either from the warming effect of high humidity, or the cooling effect of the wind.
 However, they have little physical meaning and their valid use is debatable, so are provided for interest only.

	</div>

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>
 </body>
</html>