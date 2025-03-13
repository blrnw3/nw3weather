<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php
	$file = 10; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Humidity Detail</title>

	<meta name="description" content="Detailed latest humidity (dew point, relative humidity etc.) data/information and records from NW3 weather station" />

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
<?php require $root.'HumidityTags.php'; ?>

<?php
//http://www.gorhamschaffler.com/humidity_formulas.htm
$airdensity = $pres / (($temp + 273.15) * 287) * 100000;
//http://forum.onlineconversion.com/showthread.php?t=567
$B44 = $temp; $C42 = $humi;
$abshum = ((0.000002*pow($B44,4))+(0.0002*pow($B44,3))+(0.0095*pow($B44,2))+(0.337*$B44)+4.9034)*$C42/100;

//http://forum.weatherzone.com.au/ubbthreads.php/topics/1096474/Wet_Bulb_Temperature_calculati
$Tc = $temp; $Tdc = $dewp; $P = $pres;
$E = 6.11 * pow(10, (7.5 * $Tdc / (237.7 + $Tdc)));
$wetbulb = (((0.00066 * $P) * $Tc) + ((4098 * $E) / ( pow(($Tdc + 237.7), 2)) * $Tdc)) / ((0.00066 * $P) + (4098 * $E) / ( pow($Tdc + 237.7, 2)));

require './detailDataModules.php';

$humType = isset($_GET['humtype']) ? $_GET['humtype'] : 'rel';
if($humType == 'rel') {
	$checkRel = checkHTML;
	$checkDew = '';
	$humLabel = 'Relative Humidity';
	$mainTables = new DetailedDataModule("hum");
	$measures = array('Relative Humidity','Humidity Trend','24hr Mean Humidity', 'Wet-Bulb Temperature','Absolute Humidity','Air Density');
	$values = array($humi,conv($HR24['changeHr']['humi'],5,1,1) . ' /hr',$HR24['mean']['humi'], $wetbulb,$abshum,$airdensity);
	$conv = array(5,false,5, 1,10,10);
} else {
	$checkRel = '';
	$checkDew = checkHTML;
	$humLabel = 'Dew Point';
	$mainTables = new DetailedDataModule("dew");
	$measures = array('Dew Point','Dew Pt Trend','24hr Mean Dew Pt', 'Wet-Bulb Temperature','Absolute Humidity','Air Density');
	$values = array($dewp,conv($HR24['changeHr']['dewp'],1.1,1,1) . ' /hr',$HR24['mean']['dewp'], $wetbulb,$abshum,$airdensity);
	$conv = array(1,false,1, 1,10,10);
}
echo 'Humidity Type:
	<form style="margin-right:1em;" action="" method="get">
	<label><input name="humtype" type="radio" value="rel" onclick="this.form.submit();" '. $checkRel . ' />
	 Rel. Hum.</label>
	<label><input style="margin-left:1.5em;" name="humtype" type="radio" value="dew" onclick="this.form.submit();" '. $checkDew . ' />
	 Dew Point</label>
	</form>
	<a href="#help">Difference?</a>

	<h1>Detailed '.$humLabel.' Data</h1>
';


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
	<li>Valid humidity records began in February 2009</li>
	<li>All figures, unless specified, relate to the period midnight-midnight, this being when daily extremes are reset</li>
	<li> 98% is the physical limit of the hygrometer (measuring RH); in reality this tends to means 100% saturation of the air.
	This value is achieved fairly frequently, any record with this value is just the first instance it occurred in the relevant timeframe.</li>
</ul>

<a name="help"></a>
<p><b><span style="color:green">The different measures of humidity:</span></b> <br />

<b>The Dew Point</b> (or frost point if the temperature is &lt; <?php echo conv(0,1,1); ?>&nbsp;
is the saturation temperature of a parcel of air, i.e. the point at which water condenses out.
It is the temperature at which an object would need to be
for dew to form on it (dew forms because certain objects - like cars - cool more rapidly than the air, enabling them to
reach the dew point). It is directly proportional to the specific humidity, and very well correlated to the absolute humidity.
In everyday terms, high dew points are more uncomfortable as sweating is less effective.
<br />
<b>Relative Humidity</b>, on the other hand, is rather more abstract and has a very technical definition:
the ratio of the partial pressure of water vapour in the air to the saturated vapour pressure of that water.
The saturated vapour pressure is proportional to the air temperature, and the partial pressure indicates how much water vapour the air contains,
so at a given temperature, the RH is entirely dependent on this partial pressure, making the RH useful in determining the extent to which the air is water-saturated.
For example: When it rains the relative humidity <i>will</i> increase, but the dew point may not, as the temperature will usually fall as well.
If the dew point is the same when the RH increases, the air has the same amount of water but it is cooler,
so it is more saturated, as less water vapour can exist in cooler air.
<br />
<b>Simply and concisely:</b> Dew point is a rough measure of how much water vapour is physically in the air;
relative humidity is just a measure of the degree to which the air is full of water vapour (i.e. its saturation).
One is an absolute measure, the other is relative.</p>

</div>

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>

</body>
</html>