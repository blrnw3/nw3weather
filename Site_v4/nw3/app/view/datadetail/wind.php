<?php
use nw3\app\api as a;
use nw3\app\helper\Detail as D;

$wind = new a\Wind();
$extremes = $wind->extremes();
$recent = $wind->recent_values();
$trends = $wind->trend_avgs();
$ranks_day = $wind->ranks_day();
$ranks_month = $wind->ranks_month();
//$rec24hr = $wind->record_24hrs(); //TODO
$pastyr_monthly = $wind->past_yr_month_avgs_extrms();
$pastyr_seasonal = $wind->past_yr_season_means();
?>

<h1>Detailed Wind Data</h1>

<?php $this->viewette('curr_latest_tbl', $wind->current_latest()) ?>

<table>
	<caption>Totals and Extremes for recent days</caption>
	<?php $this->viewette('period_tbl', $recent) ?>
</table>

<?php $this->viewette('trend_tbl', $trends); ?>

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
	<?php $this->viewette('period_tbl', $wind->extremes_month()) ?>
</table>

<table>
	<caption>Annual Records and Extremes</caption>
	<?php $this->viewette('period_tbl', $wind->extremes_year()) ?>
</table>
<table>
	<caption> N-day Period Records and Extremes</caption>
	<?php $this->viewette('period_tbl', $wind->extremes_nday()) ?>
</table>


<?php $this->viewette('rank_tbl', [
	'ranks' => $ranks_day,
	'name' => 'Daily Rankings'
]); ?>

<?php $this->viewette('rank_tbl', [
	'ranks' => $ranks_month,
	'name' => 'Monthly Rankings'
]); ?>

<?php $this->viewette('pastyr_tbl', [
	'caption' => 'Rolling 12-months Monthly Stats',
	'data' => $pastyr_monthly,
	'format' => 'M Y',
	'name' => 'Month',
	'summary_name' => 'Mean'
]); ?>

<?php $this->viewette('pastyr_tbl', [
	'caption' => 'Past Year Seasonal Means',
	'data' => $pastyr_seasonal,
	'format' => D::SEASON,
	'name' => 'Season',
	'summary_name' => 'Total'
]); ?>

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
			<img src="/<?php echo $wind->monthly_windrose_path() ?>" alt="windrose month" title="Current month-to-date windrose" />
		</td>
		<td width="10%"></td>
		<td>
			<img src="/<?php echo $wind->annual_windrose_path() ?>" alt="windrose year" title="Current year-to-date windrose" />
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
