<?php
$var = new nw3\app\api\Pressure();

$this->viewette('datadetail_main', [
	'var' => $var,
	'title' => 'Pressure'
]);
?>

<img src="../graph/daily/pmean" alt="Daily mean humi last 31 days" />
<img src="../graph/monthly/pmean" alt="Monthly mean humi last 12 months" />

<p>
	<a href="../datareport?type=pmean" title="<?php echo D_year; ?>daily pressure">
		<b>View daily min/max/mean pressures for the past year</b>
	</a>
</p>

<img id="now_graph" src="../graph/liveauto/dewp" alt="Last 24hrs Dew Point" />

<h2>Notes</h2>
<ul>
	<li>Pressure records began on 1st Jan 2009</li>
	<li>Figures in brackets refer to departure from
		<a href="wxaverages.php" title="Long-term NW3 climate averages">average conditions</a>
	</li>
	<li>All figures, unless specified, relate to the period midnight-midnight, this being when daily extremes are reset.</li>
	<li>Daily max/min values may up to &plusmn;2 mb off due to the use of a low quality barometer. Averages tend to be better.</li>
</ul>
