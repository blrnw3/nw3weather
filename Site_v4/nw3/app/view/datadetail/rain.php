<?php
use nw3\app\api as a;
use nw3\app\helper\Detail as D;
use nw3\app\model\Variable;

$rain = new a\Rain();
$recent = $rain->recent_values();
$extremes = $rain->extremes();
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