<?php
use nw3\app\model\Report;
use nw3\app\helper\Reports;

$rep = $this->report;
?>
<?php $yr_string = ($rep->year === 0) ? 'the last 12 months' : $rep->year; ?>
<h1>Daily Data Report for <?php echo $yr_string ?> - <?php echo $rep->var['description'] ?> / <?php echo $rep->var['unit'] ?></h1>

<div id="data_report_header">
	<a href="monthlyreports"></a>
	<form method="get" action="#data_report_header">
		<div>
			<label class="variable">Weather Variable
				<?php Reports::var_dropdown($rep->categories, $rep->var['id']); ?>
			</label>
			<?php $this->viewette('arrow_cycle') ?>
		</div>
		<div>
			<label class="year">Year
				<?php Reports::year_dropdown($rep->year); ?>
			</label>
			<?php $this->viewette('arrow_cycle') ?>
		</div>
	</form>
</div>

<table class="data_report">
	<?php Reports::months_head($rep->months); ?>
	<tbody>
		<?php for($day = 1; $day <= 31; $day++): ?>
		<tr>
			<td class="day"><?php echo $day ?></td>
			<?php foreach ($rep->months as $month): ?>
				<?php $val = $rep->data['daily'][$month][$day] ?>
				<td class="<?php echo $rep->get_class_day($val, $day, $month) ?>">
					<?php echo $rep->finalise($val) ?>
				</td>
			<?php endforeach; ?>
		</tr>
		<?php endfor; ?>
	</tbody>
</table>
<?php if(!$rep->var['nosummary']): ?>
<table class="data_report summary">
	<?php Reports::months_head($rep->months, true); ?>
	<?php if($rep->data['monthly_min']): ?>
	<tr>
		<td>Low</td>
		<?php foreach ($rep->months as $month): ?>
			<?php $val = $rep->data['monthly_min'][$month] ?>
			<td class="<?php echo $rep->get_class_month($val, $month) ?>">
				<?php echo $rep->finalise($val) ?>
			</td>
		<?php endforeach; ?>
	</tr>
	<?php endif; ?>
	<tr>
		<td>High</td>
		<?php foreach ($rep->months as $month): ?>
			<?php $val = $rep->data['monthly_max'][$month] ?>
			<td class="<?php echo $rep->get_class_month($val, $month) ?>">
				<?php echo $rep->finalise($val) ?>
			</td>
		<?php endforeach; ?>
	</tr>
	<tr>
		<td><?php echo $rep->var['summable'] ? 'Total' : 'Mean'; ?></td>
		<?php foreach ($rep->months as $month): ?>
			<?php $val = $rep->data['monthly_mean'][$month] ?>
			<td class="<?php echo $rep->get_class_month($val, $month, Report::BANDING_MONTHLY) ?>">
				<?php echo $rep->finalise($val) ?>
				<?php if($rep->data['monthly_anom'][$month] !== null): ?>
					<br />
					(<?php echo $rep->data['monthly_anom'][$month] ?>)
				<?php endif; ?>
			</td>
		<?php endforeach; ?>
	</tr>
	<?php if($rep->data['monthly_count']): ?>
	<tr>
		<td>Count</td>
		<?php foreach ($rep->months as $month): ?>
			<?php $val = $rep->data['monthly_count'][$month] ?>
			<td class="<?php echo $rep->get_class_month($val, $month, Report::BANDING_COUNTS) ?>">
				<?php echo $val ?>
			</td>
		<?php endforeach; ?>
	</tr>
	<?php endif; ?>
	<?php if(!$rep->rolling12): ?>
	<tr>
		<td>Cumul</td>
		<?php foreach ($rep->months as $month): ?>
			<?php $val = $rep->data['monthly_cumul'][$month] ?>
			<td class="<?php echo $rep->get_class_month($val, $month, Report::BANDING_CUMULATIVE) ?>">
				<?php echo $rep->finalise($val) ?>
				<?php if($rep->data['monthly_anom'][$month] !== null): ?>
					<br />
					(<?php echo $rep->data['monthly_cumul_anom'][$month] ?>)
				<?php endif; ?>
			</td>
		<?php endforeach; ?>
	</tr>
	<?php endif; ?>
</table>
<?php endif; ?>

<p>
	<?php $summary_string = ($rep->var['nosummary']) ? '' : ', along with summary data'; ?>
	<?php echo $rep->var['description'] ?> for every available day of <?php echo $yr_string.$summary_string ?>.
	<?php if(!($rep->var['nosummary'])): ?>

		<?php if(($rep->var['anomable'])): ?>
			<?php $anom_current_string = ($rep->year === D_year || $rep->rolling12) ?
				' (note that the anomaly for the current month is unadjusted for the month\'s degree of completeness)' : ''; ?>
			<br />Figures in brackets refer to departure from
			<a href="./climate" title="Long-term NW3 climate averages">average conditions</a><?php echo $anom_current_string ?>.
		<?php endif; ?>

		<?php if(($rep->year === D_year || $rep->rolling12)): ?>
			<br />Cumulative values are for the year to the month's end.
			<br />Values for recent days are subject to quality control and may be adjusted at any time.
			<?php if($rep->var['manual']): ?>
				<br />Values for the current day normally become available by 9am the following day, though potentially much later.
			<?php endif; ?>
		<?php endif; ?>
	<?php endif; ?>
</p>
<?php $this->js_script('datareport') ?>