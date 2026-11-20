<?php
require("Page.php");
require_once("WebcamBest.php");
Page::init([
	"fileNum" => 2,
	"title" => "Best webcam shots",
	"description" => "The best skycam images from NW3 weather, overlooking Hampstead Heath: the past month, the past year and each month of the year."
]);
Page::Start();

// Best webcam shots: automatic picks (cron_webcam_best.php) with hand pins (config/webcam_best_pins.php)
$best = WebcamBest::gallery();
$best_month_names = array(1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August',
	'September', 'October', 'November', 'December');
function cam_best_figure($e, $heading) {
	$when = date('D j M Y, H:i', $e['ts']);
	$alt = 'Skycam ' . $when . ($e['caption'] !== '' ? ', ' . $e['caption'] : '');
	echo '<figure class="cam-best-item">'
		. '<a class="cam-best-link" href="', htmlspecialchars($e['src']), '" target="_blank" rel="noopener"'
		. ' data-day="', htmlspecialchars(date('D j M Y', $e['ts'])), '"'
		. ' data-label="', htmlspecialchars(date('H:i', $e['ts']) . ($e['caption'] !== '' ? ' · ' . $e['caption'] : '')), '">'
		. '<img src="', htmlspecialchars($e['thumb']), '" width="600" height="400" loading="lazy" alt="', htmlspecialchars($alt), '" /></a>'
		. '<figcaption><b>', htmlspecialchars($heading), '</b> <span class="cam-best-when">', htmlspecialchars($when), '</span>';
	if ($e['caption'] !== '') {
		echo '<span class="cam-best-caption">', htmlspecialchars($e['caption']), '</span>';
	}
	echo '</figcaption></figure>';
}
?>

<h1>Best Webcam Shots</h1>

<?php
$best_recent = array_filter(array('Past month' => $best['past_month'], 'Past year' => $best['past_year']));
$best_months = array();
foreach ($best_month_names as $mo => $name) {
	foreach ($best[$mo] as $e) { $best_months[] = array($name, $e); }   // a month can have several hand picks
}
if ($best_recent || $best_months) { ?>
<style>
.cam-best { display: grid; gap: 10px; margin: 0.5rem 0 0.8rem; }
.cam-best-recent { grid-template-columns: repeat(2, 1fr); }
.cam-best-months { grid-template-columns: repeat(4, 1fr); }
.cam-best-item { margin: 0; font-size: 0.8rem; line-height: 1.35; }
.cam-best-link { display: block; }
.cam-best-item img {
	display: block; width: 100%; height: auto; aspect-ratio: 3 / 2; object-fit: cover;
	border-radius: 4px; border: 1px solid #d6e2d8; background: #eef3ee;
}
.cam-best-link:hover img, .cam-best-link:focus img { border-color: #4a7c59; }
.cam-best-link:focus { outline: 2px solid #4a7c59; outline-offset: 2px; }
.cam-best-item figcaption { color: #444; padding-top: 3px; }
.cam-best-item figcaption b { color: #4a7c59; margin-right: 0.3em; }
.cam-best-when { white-space: nowrap; }
.cam-best-caption { display: block; color: #666; }
.cam-best-recent .cam-best-item { font-size: 0.85rem; }
.cam-modal-single .cam-modal-nav { display: none; }
@media only screen and (max-width: 900px) {
	.cam-best-months { grid-template-columns: repeat(3, 1fr); }
}
@media only screen and (max-width: 560px) {
	.cam-best-recent { grid-template-columns: 1fr; }
	.cam-best-months { grid-template-columns: repeat(2, 1fr); gap: 8px; }
	.cam-best-months figcaption b { display: block; }
}
</style>

<?php if ($best_recent) { ?>
<h3>Past month and past year</h3>
<div class="cam-best cam-best-recent">
<?php foreach ($best_recent as $heading => $e) { cam_best_figure($e, $heading); } ?>
</div>
<?php } ?>
<?php if ($best_months) { ?>
<h3>Best of each month, all years</h3>
<div class="cam-best cam-best-months">
<?php foreach ($best_months as $m) { cam_best_figure($m[1], $m[0]); } ?>
</div>
<?php } ?>
<p>Picked automatically from the daily webcam summaries (colourful skies, sunrises and sunsets, snow, frost and fog), with some chosen by hand. Click an image for the full-size frame.</p>
<?php } ?>

<div id="cam-modal" class="cam-modal cam-modal-single" hidden>
	<div class="cam-modal-backdrop" data-cam-modal-close></div>
	<div class="cam-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="cam-modal-label">
		<button type="button" class="cam-modal-close" data-cam-modal-close aria-label="Close">&times;</button>
		<figure class="cam-modal-figure">
			<img id="cam-modal-img" src="" alt="" />
			<figcaption id="cam-modal-label"></figcaption>
		</figure>
	</div>
</div>

<p><a href="wx2.php" title="Live webcam"><b>Live webcam</b></a> | <a href="highreswebcam.php" title="Webcam archive"><b>Webcam archive</b></a></p>

<script type="text/javascript">
//<![CDATA[
$(function() {
	// One full-size frame per click (the link still opens the image directly without JS)
	var $modal = $('#cam-modal');
	var $img = $('#cam-modal-img');
	function closeModal() {
		$modal.attr('hidden', 'hidden');
		$('body').removeClass('cam-modal-open');
		$img.removeAttr('src');
	}
	$('.cam-best-link').on('click', function(e) {
		var $a = $(this);
		e.preventDefault();
		$img.attr({ src: $a.attr('href'), alt: $a.find('img').attr('alt') });
		$('#cam-modal-label').text($a.attr('data-day') + ' ' + $a.attr('data-label'));
		$modal.removeAttr('hidden');
		$('body').addClass('cam-modal-open');
	});
	$modal.on('click', '[data-cam-modal-close]', closeModal);
	$(document).on('keydown', function(e) {
		if (!$modal.is('[hidden]') && e.keyCode === 27) { closeModal(); }
	});
});
//]]>
</script>

<?php Page::End(); ?>
