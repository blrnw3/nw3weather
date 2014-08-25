<?php
use nw3\app\model\Variable;
?>
<table>
	<caption>Current Trends</caption>
	<thead>
		<tr>
			<td>Period</td>
			<?php foreach ($data as $val): ?>
				<td><?php echo $val['descrip'] ?></td>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($periods as $period): ?>
		<tr>
			<td>Past <?php echo $period ?> mins</td>
			<?php foreach ($data as $val): ?>
				<td>
					<?php echo Variable::conv($val['data'][$period], $val['type']) ?>
				</td>
			<?php endforeach; ?>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
