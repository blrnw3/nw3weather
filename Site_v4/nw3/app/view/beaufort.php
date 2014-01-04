<?php use \nw3\app\model\Variable; ?>
<h1>Beaufort Scale</h1>

<table cellpadding="5" width="98%" class="table1">
	<tr class="table-top">
		<td class="td4" width="15%">Beaufort</td>
		<td class="td4" width="15%">1-min Wind speed</td>
		<td class="td4" width="70%">Effects</td>
	</tr>
	<?php for($level = 0; $level <= 12; $level++): ?>
		<tr class="row<?php echo (($level % 2 === 0) ? 'light' : 'dark') ?>">
			<td class="td4"><?php echo $level; ?><br /><?php echo $this->bft->word[$level]; ?></td>
			<td class="td4">
				<?php echo Variable::conv($this->bft->scale[$level], Variable::Wind, false, false, -1) .' - '.
					Variable::conv($this->bft->scale[$level + 1], Variable::Wind, true, false, -1); ?>
			</td>
			<td class="td4"><?php echo $this->bft->descrip[$level]; ?></td>
		</tr>
	<?php endfor; ?>
</table>

<p>Descriptions are derived from the page at Wikipedia</p>

<h2>Alternative, courtesy of NOAA</h2>
<table cellpadding="5" width="99%" border="0" align="center">
	<tr>
		<td>
			<img src="<?php echo ASSET_PATH; ?>img/img33_Beaufort_NOAA.gif" alt="Beaufort scale" />
		</td>
	</tr>
</table>
