<?php
use nw3\app\helper\Detail as D;

$current = $var->current_latest();
$trends = $var->trend_diffs();
$trend_avgs = $var->trend_avgs();
$recent = $var->recent_values();
$extremes = $var->extremes_daily();
$rec24hr = $var->record_24hrs();
$ranks_daily = $var->ranks_daily();
$ranks_monthly = $var->ranks_monthly();
$ranks_daily_curr_month = $var->ranks_daily_curr_month();
$ranks_daily_past_year = $var->ranks_daily_past_year();
$pastyr_monthly = $var->past_yr_monthly_aggs();
$pastyr_monthly_extrms = $var->past_yr_monthly_extremes();
$pastyr_seasonal = $var->past_yr_season_aggs();

# TODO: descrip of each table for searching puropses
?>

<h1>Detailed <?php echo $title ?> Data</h1>

<?php $this->viewette('curr_latest_tbl', $current) ?>
<?php if($trends):
	$this->viewette('trend_tbl', [
	'data' => $var->trend_diffs(),
	'caption' => 'Trends - Changes',
	'trend_caption' => 'Change since last'
]);
endif; ?>
<?php if($trend_avgs):
	$this->viewette('trend_tbl', [
	'data' => $var->trend_avgs(),
	'caption' => 'Trends - Averages',
	'trend_caption' => 'Average over past'
]);
endif; ?>
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
	<?php $this->viewette('period_tbl', $var->extremes_monthly()) ?>
</table>

<table>
	<caption>Annual Records and Extremes</caption>
	<?php $this->viewette('period_tbl', $var->extremes_yearly()) ?>
</table>
<table>
	<caption> N-day Period Records and Extremes</caption>
	<?php $this->viewette('period_tbl', $var->extremes_nday()) ?>
</table>

<?php $this->viewette('rank_tbl', [
	'ranks' => $ranks_daily,
	'name' => 'Daily Rankings'
]); ?>

<?php if($ranks_monthly	) {
	$this->viewette('rank_tbl', [
		'ranks' => $ranks_monthly,
		'name' => 'Monthly Rankings'
	]);
} ?>

<?php if($ranks_daily_curr_month	) {
	$this->viewette('rank_tbl', [
		'ranks' => $ranks_daily_curr_month,
		'name' => 'Daily Rankings [CURR MON]'
	]);
} ?>

<?php if($ranks_daily_past_year	) {
	$this->viewette('rank_tbl', [
		'ranks' => $ranks_daily_past_year,
		'name' => 'Daily Rankings for the past year'
	]);
} ?>

<?php if($pastyr_monthly) {
	$this->viewette('pastyr_tbl', [
		'caption' => 'Rolling 12-months Means',
		'data' => $pastyr_monthly,
		'name' => 'Month',
		'summary_name' => 'Mean/Tot'
	]);
} ?>

<?php if($pastyr_monthly_extrms) {
	$this->viewette('pastyr_tbl', [
		'caption' => 'Rolling 12-months Extremes',
		'data' => $pastyr_monthly_extrms,
		'name' => 'Month',
		'no_summary' => true,
	]);
} ?>

<?php $this->viewette('pastyr_tbl', [
	'caption' => 'Past Year Seasonal Means',
	'data' => $pastyr_seasonal,
	'name' => 'Season',
	'summary_name' => 'Total'
]); ?>

