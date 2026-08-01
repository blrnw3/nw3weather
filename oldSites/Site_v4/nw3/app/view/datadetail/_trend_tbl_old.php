<?php
use nw3\app\model\Variable;
?>
<table>
	<caption><?php echo $caption ?></caption>
	<thead>
		<tr>
			<td>Period</td>
			<?php foreach ($descrips as $descrip): ?>
				<td><?php echo $descrip ?></td>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($data as $period => $dat): ?>
		<tr>
			<td>Past <?php echo $period ?></td>
			<?php foreach ($dat as $val): ?>
				<td>
					<?php echo Variable::conv($val['val'], $val['type'], true, $val['sign']) ?>
				</td>
			<?php endforeach; ?>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
