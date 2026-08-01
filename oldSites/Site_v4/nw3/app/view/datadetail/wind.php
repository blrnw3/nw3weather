<?php
$var = new nw3\app\api\Wind();

$this->viewette('datadetail_main', [
	'var' => $var,
	'title' => 'Wind'
]);
?>

<img src="../graph/daily/wmean" alt="Daily wind speeds - last 31 days" />
<img src="../graph/monthly/wmean" alt="Monthly mean wind speeds - last 12 months" />

<p>
	<a href="../datareport?type=wmean" title="<?php echo D_year; ?>daily wind speeds">
		<b>View daily wind speed data for the past year</b>
	</a>
</p>

<img id="now_graph" src="../graph/liveauto/wind" alt="Last 24hrs Wind speed" />

<hr />
<table id="windroses">
	<tr>
		<td>
			<?php echo date("F Y", D_yest); ?> Windrose
		</td>
		<td width="10%"></td>
		<td>
			<?php echo D_yest_year; ?> Windrose
		</td>
	</tr>
	<tr>
		<td>
			<img src="/<?php echo $var->monthly_windrose_path() ?>" alt="windrose month" title="Current month-to-date windrose" />
		</td>
		<td width="10%"></td>
		<td>
			<img src="/<?php echo $var->annual_windrose_path() ?>" alt="windrose year" title="Current year-to-date windrose" />
		</td>
	</tr>
</table>

<h2>Notes</h2>
<ul>
	<li>Wind records began on 1st Aug 2009</li>
	<li>Figures in brackets refer to departure from
		<a href="wxaverages.php" title="Long-term NW3 climate averages">average conditions</a>
	</li>
	<li>"Speed" is the wind speed sampled over a one minute period,
		"Gust" is the wind speed sampled over a 14 second period.</li>
	<li>All figures, unless specified, relate to the period midnight-midnight, this being when daily extremes are reset.</li>
</ul>
