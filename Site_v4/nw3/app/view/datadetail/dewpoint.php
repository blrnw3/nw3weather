<?php
$var = new nw3\app\api\Dewpoint();

$this->viewette('datadetail_main', [
	'var' => $var,
	'title' => 'Dew Point'
]);
?>

<img src="../graph/daily/dmean" alt="Daily mean humi last 31 days" />
<img src="../graph/monthly/dmean" alt="Monthly mean humi last 12 months" />

<p>
	<a href="../datareport?type=dmean" title="<?php echo D_year; ?>daily dew points">
		<b>View daily min/max/mean dew points for the past year</b>
	</a>
</p>

<img id="now_graph" src="../graph/liveauto/dewp" alt="Last 24hrs Dew Point" />

<h2>Notes</h2>
<ul>
	<li>Dew Point records began on 1st Feb 2009</li>
	<li>Figures in brackets refer to departure from
		<a href="wxaverages.php" title="Long-term NW3 climate averages">average conditions</a>
	</li>
	<li>All figures, unless specified, relate to the period midnight-midnight, this being when daily extremes are reset.</li>
</ul>

<p id="humidity_explained">
	<b<span style="color:green">The different measures of humidity:</span></b> <br />

	<b>The Dew Point</b> (or frost point if the temperature is below freezing)
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
	One is an absolute measure, the other is relative.
</p>
