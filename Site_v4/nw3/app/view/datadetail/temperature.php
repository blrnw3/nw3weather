<?php
$var = new nw3\app\api\Temperature();

$this->viewette('datadetail_main', [
	'var' => $var,
	'title' => 'Temperature'
]);
?>

<img src="../graph/daily/tmean" alt="Daily rain totals last 31 days" />
<img src="../graph/monthly/tmean" alt="Monthly rain totals last 12 months" />

<p>
	<a href="../datareport?type=tmin" title="<?php echo D_year; ?>daily temps">
		<b>View daily min/max/mean temperatures for the past year</b>
	</a>
</p>

<img id="now_graph" src="../graph/liveauto/temp" alt="Last 24hrs Temperature" />

<h2>Notes</h2>
<ul>
	<li>Temperature records began on 1st Jan 2009</li>
	<li>Figures in brackets refer to departure from
		<a href="wxaverages.php" title="Long-term NW3 climate averages">average conditions</a>
	</li>
	<li>An air frost requires the <b>overnight</b> (21-09) temperature to fall <i>below</i> freezing</li>
	<li>All figures, unless specified, relate to the period midnight-midnight, this being when daily extremes are reset.</li>
</ul>

<p id="feels_like_explained">
	<b>Definition of 'Feels-like' temperature:</b> This displays either the Wind Chill index or the
	'Humidex', depending on the temperature and humidity.
	These indices are attempts to depict what the air actually feels like on a human's skin -
	 either from the warming effect of high humidity, or the cooling effect of the wind.
	 However, they have little physical meaning and their validity is debatable, so are provided for interest only.
</p>
