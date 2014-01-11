<?php
use nw3\app\util\Html;
use nw3\app\helper\Photos;
?>

<div align="center">
	<h1><?php echo $this->album['title'] ?></h1>
	<p><?php echo $this->album['description'] ?></p>
		<b>Overview</b>
		<br />
		<i>Click on thumbnail to view large-size photo</i>
		<br /><br />
		<div id="photos_overview">
			<?php for ($i = 1; $i <= count($this->album['photos']); $i++): ?>
				<a href="#pic<?php echo $i; ?>">
					<img src="<?php echo $this->album_path .$i; ?>s.JPG" class="mini_photo"
						 data-id="<?php echo $i ?>" alt="Photo <?php echo $i ?>" title="<?php echo $this->album['photos'][$i-1] ?>" />
				</a>
				<span style="color:white">\n</span>
				<?php if ($i % 3 == 0): ?>
					<br />
				<?php endif; ?>
			<?php endfor; ?>
		</div>

		<a href="<?php Html::href('photos'); ?>" title="Return to overview of photo galleries">Back to all albums</a>
		<br /><br />

		<button id="reveal_all">View all large images</button>

		<div id="large_photos">
			<?php for ($i = 1; $i <= count($this->album['photos']); $i++): ?>
				<div class="large_photo" style="display:none" id="large_photo-<?php echo $i ?>">
					<h3 id="pic<?php echo $i; ?>"><?php echo $this->album['photos'][$i-1] ?></h3>
					<div class="img_holder" data-src="<?php echo $this->album_path .$i; ?>.JPG" title="<?php echo $this->album['photos'][$i-1]; ?>" ></div>
					<a href="#photos_overview">Top</a>
				</div>
			<?php endfor; ?>
		</div>
		<div id="slideshow_control" style="display:none">
			<button id="photo_prev">&lt;&lt; Previous</button>
			<button id="photo_next">Next &gt;&gt;</button>
		</div>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		var img_count = <?php echo count($this->album['photos']); ?>;
		var curr_img = 0;

		$('.large_photo').find('.img_holder').css('max-height', $(window).height() - 95);

		$('#reveal_all').click(function() {
			$('.large_photo').show().find('.img_holder').each(function() {
					$(this).html('<img src="'+ $(this).attr('data-src') +'" alt="photo" />');
				});
			$('#slideshow_control').hide(0);
		});
		$('.mini_photo').click(function() {
			var id = $(this).attr('data-id');
			curr_img = id;
			$('#slideshow_control').show();
			switch_img();
		});

		$('#photo_prev').click(function() {
			curr_img = ((curr_img - 1) % img_count);
			switch_img();
		});
		$('#photo_next').click(function() {
			curr_img = ((curr_img + 1) % img_count);
			switch_img();
		});
		function switch_img() {
			curr_img = (curr_img === 0) ? img_count : curr_img;
			$('.large_photo').hide(0);
			$('#large_photo-'+ curr_img).show()
				.find('.img_holder').each(function() {
					$(this).html('<img src="'+ $(this).attr('data-src') +'" alt="photo" />');
				});
		}
	});
</script>