<?php
use nw3\app\api as a;
use nw3\app\helper\Detail as D;
use nw3\app\model\Variable;

$temp = new a\Temperature();
$recent = $temp->recent_values();
$extremes = $temp->extremes();
$ranks_day = $temp->ranks_day();
$ranks_month = $temp->ranks_month();
$ranks_day_curr_month = $temp->ranks_day_curr_month();
$rec24hr = $temp->record_24hrs();
$pastyr_monthly = $temp->past_yr_month_avg_extremes();
$pastyr_seasonal = $temp->past_yr_season_tots();
?>

<h1>Detailed Temperature Data</h1>

<table>
	<caption>Current / Latest</caption>
	<thead>
		<tr>
			<td>Measure</td>
			<td>Value</td>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($temp->current_latest() as $k => $val): ?>
		<tr>
			<td><?php echo $val['descrip'] ?></td>
			<td>
				<?php echo Variable::conv($val['val'], $val['type'], true, $val['sign']) ?>
				<?php if($val['dt']): ?>
					 at <?php echo $val['dt'] ?>
				<?php endif; ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<table>
	<caption>Totals and Extremes for recent days</caption>
	<?php $this->viewette('period_tbl', $temp->recent_values()) ?>
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
	<?php $this->viewette('period_tbl', $temp->extremes_month()) ?>
</table>

<table>
	<caption>Annual Records and Extremes</caption>
	<?php $this->viewette('period_tbl', $temp->extremes_year()) ?>
</table>
<table>
	<caption> N-day Period Records and Extremes</caption>
	<?php $this->viewette('period_tbl', $temp->extremes_nday()) ?>
</table>

<table>
	<caption>24hr Records</caption>
	<thead>
		<tr>
			<td><?php echo $rec24hr['descrip'] ?></td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<?php echo Variable::conv($rec24hr['data']['val'], $rec24hr['type']) ?>
				<br />
				<?php echo D::date($rec24hr['data']['dt'], 'rec', $rec24hr['rec_type']) ?>
			</td>
		</tr>
	</tbody>
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
	'name' => 'Month'
]) ?>

<table>
	<caption>Past Year Seasonal Means</caption>
	<?php $this->viewette('pastyr_tots_tbl', ['data' => $pastyr_seasonal, 'format' => false, 'name' => 'Season']) ?>
</table>

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
	 However, they have little physical meaning and their valid use is debatable, so are provided for interest only.
</p>
