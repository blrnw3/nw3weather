<?php
use nw3\app\util\Html;
use nw3\app\helper\Photos;
?>

<div align="center">
	<h1>Photo Albums</h1>

	<table cellspacing="5" cellpadding="5">
	<?php $cnt = 0;
	for ($i = count($this->albums)-1; $i >= 0; $i--):
		if($cnt % 4 === 0): ?>
			<tr valign="top" align="center">
		<?php endif; ?>
		<td align="center">
			<a href="./<?php echo $i; ?>" title="View full album">
				<?php Html::img(Photos::cover_image($i), 'album cover', 'album_cover') ?>
			</a>
			<br />
			<b><?php echo $this->albums[$i]['title'] ?></b>
			<br />
		</td>

		<?php if($cnt % 4 === 3 || $i === 0): ?>
			</tr>
		<?php endif;
		$cnt++;
	 endfor; ?>
	</table>

	<p align="center"> <i>&copy; Ben Lee-Rodgers</i></p>

	<br />

	<p><b>About:</b> Photos from after 24th December 2009 were taken with a basic Panasonic DMC-TZ6;
		before such date, a variety of cameras were used.
	</p>

	<p><b>NB:</b> All the photos present in these albums are my own, and consequently I hold their copyright.</p>
</div>