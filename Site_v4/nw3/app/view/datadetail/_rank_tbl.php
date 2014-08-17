<?php
use nw3\app\helper\Detail as D;
use nw3\app\model\Variable;

$ranklen = count($ranks[array_keys($ranks)[0]]['data']);
?>

<table>
	<caption><?php echo $name ?></caption>
	<thead>
		<tr>
			<td>Rank</td>
			<?php foreach ($ranks as $rank): ?>
				<td><?php echo $rank['descrip']; ?></td>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php for ($i = 0; $i < $ranklen; $i++): ?>
		<tr>
			<td><?php echo ($i+1) ?></td>
			<?php foreach ($ranks as $rank): ?>
			<td>
				<?php echo Variable::conv($rank['data'][$i]['val'], $rank['type']) ?>
				<br />
				<?php echo D::date($rank['data'][$i]['dt'], $rank['period'], $rank['rec_type']) ?>
			</td>
			<?php endforeach; ?>
		</tr>
		<?php endfor; ?>
	</tbody>
</table>