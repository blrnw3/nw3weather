<?php
use nw3\app\api as a;
use nw3\app\helper\Detail as D;
use nw3\app\model\Variable;

$rain = new a\Rain();
$recent = $rain->recent_values();
$extremes = $rain->extremes();
$ranks = $rain->ranks();
$rec24hr = $rain->record_24hrs()['wettest'];
$pastyr_monthly = $rain->past_yr_month_tots();
$pastyr_seasonal = $rain->past_yr_season_tots();
$show_now_graph = ($recent['rn24hr'] > 0);
?>

<h1>Detailed Rainfall Data</h1>

<table>
	<caption>Current / Latest</caption>
	<thead>
		<tr>
			<td>Measure</td>
			<td>Value</td>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($rain->current_latest() as $k => $val): ?>
		<tr>
			<td><?php echo $val['descrip'] ?></td>
			<td>
				<?php echo Variable::conv($val['val'], $val['type']) ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<table>
	<caption>Totals and Extremes for recent days</caption>
	<?php $this->viewette('period_tbl', $rain->recent_values()) ?>
</table>

<table>
	<caption>Totals and Extremes for recent periods</caption>
	<?php $this->viewette('period_tbl', D::filter_recent($extremes)) ?>
</table>

<table>
	<caption>All-time Totals and Record Extremes <?php echo D::record_yr_range(); ?></caption>
	<?php $this->viewette('period_tbl', D::filter_records($extremes)) ?>
</table>

<table>
	<caption>Monthly Records and Extremes</caption>
	<?php $this->viewette('period_tbl', $rain->extremes_month()) ?>
</table>

<table>
	<caption>Annual Records and Extremes</caption>
	<?php $this->viewette('period_tbl', $rain->extremes_year()) ?>
</table>
<table>
	<caption> N-day Period Records and Extremes</caption>
	<?php $this->viewette('period_tbl', $rain->extremes_nday()) ?>
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
	'ranks' => $ranks,
	'name' => 'Rankings'
]); ?>

<?php $this->viewette('pastyr_tbl', [
	'caption' => 'Rolling 12-months Monthly Totals and Extremes',
	'data' => $pastyr_monthly,
	'format' => 'M Y',
	'name' => 'Month'
]) ?>

<?php // $this->viewette('pastyr_tbl', [
//	'caption' => 'Past Year Seasonal Totals',
//	'data' => $pastyr_seasonal,
//	'format' => false,
//	'name' => 'Season'
//]) ?>

<img src="../graph/daily/rain" alt="Daily rain totals last 31 days" />
<img src="../graph/monthly/rain" alt="Monthly rain totals last 12 months" />

<p>
	<a href="../datareport?vartype=rain" title="<?php echo D_year; ?>daily rain totals">
		<b>View daily totals for the past year</b>
	</a>
</p>

<?php if($show_now_graph): ?>
	<img id="now_graph" src="../graph/liveauto/rain" alt="Last 24hrs Rainfall" />
<?php endif; ?>

<p>
	<b>Note 1:</b> Rain records began in February 2009<br />
	<b>Note 2:</b> The minimum recordable rain (the rain gauge resolution) is 0.2 mm<br />
	<b>Note 3:</b> Figures in brackets refer to departure from <a href="wxaverages.php" title="Long-term NW3 climate averages">average conditions</a><br />
	<b>Note 4:</b> Rain rate records are manually checked, and changed if necessary,
	due to occasional issues with the software. Initial high readings may well be corrected at a later date.<br />
</p>

