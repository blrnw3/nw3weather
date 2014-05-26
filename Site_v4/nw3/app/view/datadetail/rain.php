<?php
use nw3\app\api as a;
use nw3\app\helper\Detail as D;
use nw3\app\model\Variable;

$rain = new a\Rain();
$cur_lat = $rain->current_latest();

// Put a table break after these
$cat_ends = array('rndur24', 'rnseas');
?>

<h1>Detailed Rainfall Data</h1>

<table>
	<caption>Current / Latest</caption>
	<thead>
		<tr>
			<td>Measure</td>
			<td>Value</td>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($cur_lat as $k => $val): ?>
		<tr>
			<td><?php echo $val['descrip'] ?></td>
			<td>
				<?php echo Variable::conv($val['val'], $val['type']) ?>
				<?php if(key_exists('anom', $val)): ?>
					<?php if(key_exists('anom_f', $val)): ?>
						(<?php echo D::of_final_exp($val) ?>)
					<?php else: ?>
						 (<?php echo Variable::conv_anom($val['anom'], $val['type']) ?>)
					<?php endif; ?>
				<?php endif; ?>
				<?php if(key_exists('prop', $val)): ?>
					[<?php echo round($val['prop'] * 100) ?>%]
				<?php endif; ?>
			</td>
				<?php if(array_search($k, $cat_ends) !== false): ?>
					</tr><tr><td colspan="2">---</td>
				<?php endif; ?>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>