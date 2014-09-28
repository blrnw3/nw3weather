<?php
use nw3\app\model\Variable;
use nw3\app\helper\Detail as hD;
?>
<table class="ranks">
	<caption><?php echo $caption ?></caption>
	<thead>
		<td>Rank</td>
		<td>Value</td>
		<td>Date</td>
	</thead>
	<?php if ($yest): ?>
	<tfoot>
		<tr>
			<td><?php echo $yest['rank'] ?></td>
			<td class="<?php echo $rep->class_level($yest['val'], $rep->class_bands['day']) ?>">
				<?php echo Variable::conv($yest['val'], $meta['group'], false) ?>
			</td>
			<td>Yesterday</td>
		</tr>
	</tfoot>
	<?php endif; ?>
	<tbody>
		<?php foreach($ranks as $i => $rnk): ?>
		<tr>
			<td><?php echo ($i+1) ?></td>
			<td class="<?php echo $rep->class_level($rnk['val'], $rep->class_bands['day']) ?>">
				<?php echo Variable::conv($rnk['val'], $meta['group'], false) ?>
			</td>
			<td><?php echo hD::date_custom($rnk['dt'], $format) ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>