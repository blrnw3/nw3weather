<?php
use nw3\app\helper\Detail as D;
use nw3\app\model\Variable;

$pastyr = $data['data'];
?>
<thead>
	<tr>
		<td><?php echo $data['name'] ?></td>
		<td colspan="2">Total</td>
	</tr>
</thead>
<tbody>
	<?php foreach ($pastyr['periods'] as $tot): ?>
	<tr>
		<td><?php echo D::date($tot['d'], 'rec', $data['format']) ?></td>
		<td><?php echo Variable::conv($tot['val'], Variable::Rain) ?></td>
		<td><?php echo Variable::conv_anom($tot['anom'], Variable::Rain) ?></td>
	</tr>
	<?php endforeach; ?>
	<tr>
		<td>Total</td>
		<td><?php echo Variable::conv($pastyr['annual']['val'], Variable::Rain) ?></td>
		<td><?php echo Variable::conv_anom($pastyr['annual']['anom'], Variable::Rain) ?></td>
	</tr>
</tbody>
