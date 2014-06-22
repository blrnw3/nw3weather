<?php
use nw3\app\model\Detail as mD;
use nw3\app\helper\Detail as D;
use nw3\app\model\Variable;
?>
<thead>
	<tr>
		<td>Measure</td>
		<?php foreach (array_keys($data['rain']) as $p): ?>
			<td><?php echo mD::$periods[$p]['descrip'] ?></td>
		<?php endforeach; ?>
	</tr>
</thead>
<tbody>
	<?php foreach($data as $k => $vals): ?>
	<tr>
		<td><?php echo $vals['descrip'] ? $vals['descrip'] : Variable::$daily[$k]['description']; ?></td>
		<?php foreach ($vals as $p => $val): ?>
		<td>
			<?php echo Variable::conv($val['val'], $vals['type']) ?>
			<?php if(key_exists('anom', $val)): ?>
				<br />
				<?php if(key_exists('anom_f', $val)): ?>
					(<?php echo D::of_final_exp($val, $vals['type']) ?>)
				<?php else: ?>
					 (<?php echo Variable::conv_anom($val['anom'], $vals['type']) ?>)
				<?php endif; ?>
			<?php endif; ?>
			<?php if(key_exists('prop', $val)): ?>
				[<?php echo round($val['prop'] * 100) ?>%]
			<?php endif; ?>
			<br />
			<?php if(key_exists('dt', $val)): ?>
				<?php echo D::date($val['dt']) ?>
			<?php endif; ?>
		</td>
		<?php endforeach; ?>
	</tr>
	<?php endforeach; ?>
</tbody>
