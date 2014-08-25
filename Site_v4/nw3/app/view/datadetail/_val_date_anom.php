<?php
use nw3\app\helper\Detail as D;
use nw3\app\model\Variable;
?>
<?php echo Variable::conv($val, $type) ?>
<?php if($anom !== null && !$no_anom): ?>
	<br />
	(<?php echo Variable::conv_anom($anom, $type) ?>)
<?php endif; ?>
<?php if($d !== null && !$agg): ?>
	<br />
	<?php echo D::date($d, $period, $rec_type) ?>
<?php endif; ?>
<?php if($prop !== null): ?>
	<br />
	[<?php echo round($prop * 100) ?>%]
<?php endif; ?>
