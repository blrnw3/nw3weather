<?php
use nw3\app\helper\Detail as D;
use nw3\app\model\Variable;

$first = $data[array_keys($data)[0]];
?>
<table>
	<caption><?php echo $caption ?></caption>
	<thead>
		<tr>
			<td><?php echo $name ?></td>
			<?php foreach ($data as $datum): ?>
				<td><?php echo $datum['descrip']; ?></td>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php for($i = 0; $i < count($first['periods']); $i++): ?>
		<tr>
			<td><?php echo D::date($first['periods'][$i]['d'], $first['period'], $first['rec_type']) ?></td>
			<?php foreach ($data as $vals): ?>
				<td class="<?php echo $vals['periods'][$i]['is_max'] ? 'val_max' : ''; echo $vals['periods'][$i]['is_min'] ? 'val_min' : ''?>">
				<?php $this->viewette('val_date_anom', $vals['periods'][$i] + $vals) ?>
				</td>
			<?php endforeach; ?>
		</tr>
		<?php endfor; ?>
		<?php if(!$no_summary): ?>
		<tr>
			<td><?php echo $summary_name; ?></td>
			<?php foreach ($data as $vals): ?>
				<?php $val = $vals['annual']; ?>
				<td>
				<?php if($val): ?>
					<?php $this->viewette('val_date_anom', $val + $vals) ?>
				<?php else: ?>
					-
				<?php endif; ?>
				</td>
			<?php endforeach; ?>
		</tr>
		<?php endif; ?>
	</tbody>
</table>
