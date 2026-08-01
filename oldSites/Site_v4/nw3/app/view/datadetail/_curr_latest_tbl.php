<?php
use nw3\app\model\Variable;
?>
<table>
	<caption>Current / Latest</caption>
	<thead>
		<tr>
			<td>Measure</td>
			<td>Value</td>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($_this as $val): ?>
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
