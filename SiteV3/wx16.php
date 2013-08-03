<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php 
	$file = 16; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Pressure Detail</title>

	<meta name="description" content="Detailed latest pressure data/information and records from NW3 weather station" />

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

<?php
require './detailDataModules.php';

$pwType = isset($_GET['pwtype']) ? $_GET['pwtype'] : 'baro';
if($pwType == 'baro') {
	require $root.'PressureTags.php';
	$checkRel = checkHTML;
	$checkDew = '';
	$pwLabel = 'Barometric Pressure (MSL adjusted)';
	$mainTables = new DetailedDataModule("baro");
	$measures = array('Pressure','Pressure Trend','24hr Mean Pressure');
	$values = array($baro,conv($HR24['changeHr']['baro'],3,1,1) . ' /hr',$last24houravbaro);
	$conv = array(3,false,3);
} else {
	require $root.'WindTags.php';
	$checkRel = '';
	$checkDew = checkHTML;
	$pwLabel = 'Wind Speed (alternative)';
	$mainTables = new DetailedDataModule("wind");
	$measures = array('Wind Speed','Gust Speed','24hr Mean Speed');
	$values = array($wind,$gust,$last24houravwind);
	$conv = array(4,4,4);
}
/*
echo 'Type:
	<form style="margin-right:1em;" action="" type="get">
	<input name="pwtype" type="radio" value="rel" onclick="this.form.submit();" '. $checkRel . '/>
	 Pressure
	<input style="margin-left:1.5em;" name="pwtype" type="radio" value="dew" onclick="this.form.submit();" '. $checkDew . '/>
	 W
	</form>
	<a href="#help">Difference?</a>

	<h1>Detailed '.$pwLabel.' Data</h1>
';
 */

$mainTables->currentLatest($measures, $values, $conv);
$mainTables->recentAvgsExtrms();
$mainTables->graph31dump();
$mainTables->avgsExtrmsRecs();

echo '<br />';
$mainTables->pastYearAvgsExtrms();
echo '<br />';

$mainTables->seasonalAvgs();
$mainTables->graph12Dump();
$mainTables->recordPeriodAvgs();

$mainTables->rankTables();
?>

<h2>Notes:</h2>
<ul>
	<li>Valid Pressure records began in January 2009</li>
	<li>All figures, unless specified, relate to the period midnight-midnight, this being when daily extremes are reset</li>
	<li>All values are accurate to &plusmn;3 mb (though most to within 1) due to inaccuracies in this station's barometer.</li>
</ul>

</div>

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>

</body>
</html>