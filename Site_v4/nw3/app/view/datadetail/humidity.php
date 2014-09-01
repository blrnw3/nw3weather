<?php
use nw3\app\api as a;
use nw3\app\helper\Detail as D;

$humi = new a\Humidity();
$recent = $humi->recent_values();
$extremes = $humi->extremes();
$ranks_day = $humi->ranks_day();
$ranks_month = $humi->ranks_month();
$ranks_day_curr_month = $humi->ranks_day_curr_month();
//$rec24hr = $humi->record_24hrs();
$pastyr_monthly = $humi->past_yr_month_avgs();
$pastyr_monthly_extrms = $humi->past_yr_month_extrms();
$pastyr_seasonal = $humi->past_yr_season_means();
?>

<h1>Detailed Relative Humidity Data</h1>

<?php $this->viewette('curr_latest_tbl', $humi->current_latest()) ?>

<table>
	<caption>Totals and Extremes for recent days</caption>
	<?php $this->viewette('period_tbl', $recent) ?>
</table>

<table>
	<caption>Totals and Extremes for recent periods</caption>
	<?php $this->viewette('period_tbl', D::filter_recent($extremes)) ?>
</table>

<table>
	<caption>Record Extremes <?php echo D::record_yr_range(); ?></caption>
	<?php $this->viewette('period_tbl', D::filter_records($extremes)) ?>
</table>

<table>
	<caption>Monthly Records and Extremes</caption>
	<?php $this->viewette('period_tbl', $humi->extremes_month()) ?>
</table>

<table>
	<caption>Annual Records and Extremes</caption>
	<?php $this->viewette('period_tbl', $humi->extremes_year()) ?>
</table>
<table>
	<caption> N-day Period Records and Extremes</caption>
	<?php $this->viewette('period_tbl', $humi->extremes_nday()) ?>
</table>

<?php $this->viewette('rank_tbl', [
	'ranks' => $ranks_day,
	'name' => 'Daily Rankings'
]); ?>

<?php $this->viewette('rank_tbl', [
	'ranks' => $ranks_month,
	'name' => 'Monthly Rankings'
]); ?>

<?php $this->viewette('rank_tbl', [
	'ranks' => $ranks_day_curr_month,
	'name' => 'Daily Rankings for Curr Month',
	'period' => 'rec'
]); ?>

<?php $this->viewette('pastyr_tbl', [
	'caption' => 'Rolling 12-months Monthly Means',
	'data' => $pastyr_monthly,
	'format' => 'M Y',
	'name' => 'Month',
	'summary_name' => 'Mean/Tot'
]); ?>

<?php $this->viewette('pastyr_tbl', [
	'caption' => 'Rolling 12-months Monthly Extremes',
	'data' => $pastyr_monthly_extrms,
	'format' => 'M Y',
	'name' => 'Month',
	'no_summary' => true,
]); ?>

<?php $this->viewette('pastyr_tbl', [
	'caption' => 'Past Year Seasonal Means',
	'data' => $pastyr_seasonal,
	'format' => D::SEASON,
	'name' => 'Season',
	'summary_name' => 'Total'
]); ?>

<img src="../graph/daily/hmean" alt="Daily mean humi last 31 days" />
<img src="../graph/monthly/hmean" alt="Monthly mean humi last 12 months" />

<p>
	<a href="../datareport?type=hmean" title="<?php echo D_year; ?>daily humis">
		<b>View daily min/max/mean humidities for the past year</b>
	</a>
</p>

<img id="now_graph" src="../graph/liveauto/humi" alt="Last 24hrs Temperature" />

<h2>Notes</h2>
<ul>
	<li>Humidity records began on 1st Feb 2009</li>
	<li>Figures in brackets refer to departure from
		<a href="wxaverages.php" title="Long-term NW3 climate averages">average conditions</a>
	</li>
	<li>All figures, unless specified, relate to the period midnight-midnight, this being when daily extremes are reset.</li>
	<li> 98% is the physical limit of the hygrometer (measuring RH); in reality this tends to means 100% saturation of the air.
	This value is achieved fairly frequently, any record with this value is just the first instance it occurred in the relevant timeframe.</li>
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
