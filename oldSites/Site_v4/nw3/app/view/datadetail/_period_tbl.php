<?php
use nw3\app\model\Detail as mD;
use nw3\app\helper\Detail as D;
use nw3\app\model\Variable;

$headings = array_keys($_this[array_keys($_this)[0]]['data']);
?>
<thead>
	<tr>
		<td>Measure</td>
		<?php foreach ($headings as $p): ?>
			<td><?php echo mD::$periods[$p]['descrip'] ?></td>
		<?php endforeach; ?>
	</tr>
</thead>
<tbody>
	<?php foreach($_this as $k => $vals): ?>
	<tr>
		<td><?php echo $vals['descrip']; ?></td>
		<?php foreach ($headings as $p): ?>
			<?php $val = $vals['data'][$p]; ?>
		<td>
			<?php
			if(is_null($val)) {
				echo '-';
				continue;
			}
			?>
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
				<?php echo D::date($val['dt'], $p, $vals['rec_type']) ?>
			<?php endif; ?>
		</td>
		<?php endforeach; ?>
	</tr>
	<?php endforeach; ?>
</tbody>
