<?php
use nw3\app\model\Variable;

$periods = array_keys($data[array_keys($data)[0]]['data']);
?>
<table>
	<caption><?php echo $caption ?></caption>
	<thead>
		<tr>
			<td rowspan="2">Measure</td>
			<td rowspan="2">Current<br /><?php echo D_time ?></td>
			<td colspan="<?php echo count($periods) ?>"><?php echo $trend_caption ?></td>
		</tr>
		<tr>
			<?php foreach ($periods as $period): ?>
				<td><?php echo $period ?></td>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($data as $dat): ?>
		<tr>
			<td><?php echo $dat['descrip'] ?></td>
			<td><?php echo Variable::conv($dat['now'], $dat['type']) ?></td>
			<?php foreach ($dat['data'] as $val): ?>
				<td>
					<?php echo Variable::conv($val, $dat['type'], false, $dat['is_abs']) ?>
				</td>
			<?php endforeach; ?>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
