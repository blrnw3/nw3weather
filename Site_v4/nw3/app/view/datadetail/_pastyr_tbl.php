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
			<td><?php echo D::date($first['periods'][$i]['d'], null, $format) ?></td>
			<?php foreach ($data as $vals): ?>
				<?php $val = $vals['periods'][$i]; ?>
				<?php $this->viewette('val_date_anom', $val + $vals) ?>
			<?php endforeach; ?>
		</tr>
		<?php endfor; ?>
		<tr>
			<td>Total</td>
			<?php foreach ($data as $vals): ?>
				<?php $val = $vals['annual']; ?>
				<?php if($val): ?>
					<?php $this->viewette('val_date_anom', $val + $vals) ?>
				<?php endif; ?>
			<?php endforeach; ?>
		</tr>
	</tbody>
</table>
