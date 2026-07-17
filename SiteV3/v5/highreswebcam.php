<?php
require("Page.php");
Page::init([
	"fileNum" => 2,
	"title" => "Webcam Summary / Archive",
	"description" => "High resolution archive web cam images from NW3 weather, overlooking Hampstead Heath"
]);
Page::Start();

$url = 'highreswebcam.php';
$freqs = array(180, 60, 30, 15, 10, 5, 1);
$cols_opts = array(2, 3, 4, 5, 6, 8, 10);

$dproc = isset($_GET['day']) ? intval($_GET['day']) : Date::$dday;
$mproc = isset($_GET['month']) ? intval($_GET['month']) : Date::$dmonth;
$yproc = isset($_GET['year']) ? intval($_GET['year']) : Date::$dyear;
$sproc = Date::mkdate($mproc, $dproc, $yproc);
$datedescrip = date('jS F Y', $sproc);

$riseproc = date_sunrise($sproc, SUNFUNCS_RET_DOUBLE, Site::LATITUDE, Site::LONGITUDE, Site::ZENITH, date('I', $sproc)) - 1.5;
$setproc = date_sunset($sproc, SUNFUNCS_RET_DOUBLE, Site::LATITUDE, Site::LONGITUDE, Site::ZENITH, date('I', $sproc)) + 1.5;

$archive_start = Date::mkdate(8, 1, 2010);
$cond1 = $sproc > $archive_start;
$cond2 = $sproc < Date::mkdate(Date::$dmonth, Date::$dday, Date::$dyear);
if (!$cond1) { echo '<b>Archive begins on 1st August 2010</b><br />'; }
$is_today = (Date::mkdate() === $sproc);

// Hik high-res from 21 Jun 2018; older dates use legacy sky cam frames
$hik_start = Date::mkdate(6, 21, 2018);
$cam_type = ($sproc < $hik_start) ? 'sky' : 'hik';

$chosend = $chosena = '';
if (isset($_GET['light']) && $_GET['light'] == 'day') { $light = 'day'; $chosend = 'checked="checked"'; }
else { $light = 'all'; $chosena = 'checked="checked"'; }

if (isset($_GET['width']) && in_array((int)$_GET['width'], $cols_opts)) { $width_opt = (int)$_GET['width']; }
else { $width_opt = 4; }
if (isset($_GET['freq']) && in_array((int)$_GET['freq'], $freqs)) { $freq = (int)$_GET['freq']; }
else { $freq = 60; }
if (isset($_GET['frame']) && (int)$_GET['frame'] < 24) { $frame = intval($_GET['frame']); }
else { $frame = $is_today ? (int)date('H') : 12; }

$qrand = date('dmYH');

if ($freq === 1 && $sproc <= Date::mkdate() - 10 * 24 * 3600) {
	echo '<b>NB:</b> Images at a 1-min interval are only available for the past 10 days.<br />';
}
if ($sproc <= Date::mkdate(9, 24, 2016)) {
	echo '<b>NB:</b> Before 24th Sep 2016, individual archive frames are only available at 3hr intervals; '
		. 'otherwise a daily summary image is shown.<br />';
	$freq = 180;
}

if ($freq >= 5) {
	$pframe = $frame; $nframe = $frame;
	$prevs = $sproc - 3600 * 24; $nexts = $sproc + 3600 * 24;
	$prevd = date('j', $prevs); $prevm = date('n', $prevs); $prevy = date('Y', $prevs);
	$nextd = date('j', $nexts); $nextm = date('n', $nexts); $nexty = date('Y', $nexts);
} else {
	$pframe = ($frame === 0) ? 23 : $frame - 1; $nframe = ($frame + 1) % 24;
	$hsproc = mktime($frame, 0, 0, $mproc, $dproc, $yproc);
	$prevs = $hsproc - 3600; $nexts = $hsproc + 3600;
	$prevd = date('j', $prevs); $prevm = date('n', $prevs); $prevy = date('Y', $prevs);
	$nextd = date('j', $nexts); $nextm = date('n', $nexts); $nexty = date('Y', $nexts);
}

$prev_url = "$url?year=$prevy&month=$prevm&day=$prevd&light=$light&width=$width_opt&freq=$freq&frame=$pframe&cycle";
$next_url = "$url?year=$nexty&month=$nextm&day=$nextd&light=$light&width=$width_opt&freq=$freq&frame=$nframe&cycle";

/**
 * Path to the datestamped daily summary image (legacy wcarchive layout).
 * Returns array(url, filesystem_path) or null if missing.
 */
function hrw_daily_summary($sproc) {
	$yproc = (int) date('Y', $sproc);
	$datetag = date('Ymd', $sproc) . 'daily';
	$direc = ($yproc < (int) date('Y')) ? $yproc . '/' : '';
	$endtag = 'jpg';
	$extra = null;
	if ($sproc < Date::mkdate(6, 27, 2012)) {
		$endtag = 'gif';
	} else {
		$direc = date('Y', $sproc) . '/';
	}
	if (Date::mkdate() - $sproc < 24 * 3600) {
		$datetag = 'today';
		$direc = '';
	}
	$rel = $direc . $datetag . 'webcam.' . $endtag;
	$path = ROOT . $rel;
	if (!file_exists($path)) { return null; }
	$out = array(array('url' => '/' . $rel, 'path' => $path));
	// Early archive sometimes has a second summary strip
	if ($sproc < Date::mkdate(6, 27, 2012)) {
		$rel2 = $direc . $datetag . 'webcam2.gif';
		if (file_exists(ROOT . $rel2)) {
			$out[] = array('url' => '/' . $rel2, 'path' => ROOT . $rel2);
		}
	}
	return $out;
}
?>

<h1>Webcam Summary and Archive</h1>

<form method="get" action="" class="hrw-form">
<label>Date: <select name="year">
<?php
for ($i = 2010; $i <= Date::$dyear; $i++) {
	echo '<option value="', $i, '"';
	if ($yproc == $i) { echo ' selected="selected"'; }
	echo '>', $i, '</option>';
} ?>
</select></label>
<select name="month">
<?php
for ($i = 0; $i < 12; $i++) {
	echo '<option value="', sprintf('%1$02d', $i + 1), '"';
	if ($mproc == $i + 1) { echo ' selected="selected"'; }
	echo '>', Date::$months[$i], '</option>';
} ?>
</select>
<select name="day">
<?php
for ($i = 1; $i <= 31; $i++) {
	echo '<option value="', sprintf('%1$02d', $i), '"';
	if ($dproc == $i) { echo ' selected="selected"'; }
	echo '>', $i, '</option>';
} ?>
</select>

<span class="hrw-form-group"><b>Daylight only:</b>
	<label><input type="radio" name="light" value="day" <?php echo $chosend; ?> /> Yes</label>
	<label><input type="radio" name="light" value="all" <?php echo $chosena; ?> /> No</label>
</span>
<br />
<label>Images per row: <select name="width">
<?php foreach ($cols_opts as $wo) {
	$selec = ($wo === $width_opt) ? 'selected="selected"' : '';
	echo "<option value='$wo' $selec>$wo</option>";
} ?>
</select></label>
<label>Interval / mins: <select name="freq">
<?php foreach ($freqs as $f) {
	$selec = ($f === $freq) ? 'selected="selected"' : '';
	echo "<option value='$f' $selec>$f</option>";
} ?>
</select></label>
<?php
if ($freq < 5) {
	echo '<label>Hour: <select name="frame">';
	foreach (range(0, 23) as $h) {
		$selec = ($frame === $h) ? 'selected="selected"' : '';
		echo "<option value='$h' $selec>$h</option>";
	}
	echo '</select></label>';
}
?>
<input type="submit" value="Generate" />
&nbsp; <a href="<?php echo $url ?>" title="Reset page to default params">Reset</a>
<?php if ($cond1) { echo "<a class='hrw-nav' href='$prev_url'>&lt;&lt;Previous</a>"; } ?>
<?php if ($cond2) { echo "<a class='hrw-nav' href='$next_url'>Next&gt;&gt;</a>"; } ?>
</form>

<?php
# Don't hit the server too hard with img requests
if (isset($_GET['cycle'])) {
	$ni = ($freq === 1) ? 60 : 1440 / $freq;
	usleep(250000 + 5000 * $ni);
}

$offset = ($freq === 1) ? $frame * 60 : 0;
$num_imgs = ($freq === 1) ? 60 : 1440 / $freq;
$post_imgs = array();
$pre_imgs = array();
foreach (range(0, $num_imgs - 1) as $n) {
	$mins = $offset + $n * $freq;
	$minutes = mktime(0, $mins);
	$stamp = date('Hi', $minutes);
	$float_stamp = $mins / 60;
	if ($light === 'day' && ($float_stamp < $riseproc || $float_stamp > $setproc)) {
		continue;
	}
	if ($is_today && $freq >= 5 && $minutes > time()) {
		$pre_imgs[] = $stamp;
	} else {
		$post_imgs[] = $stamp;
	}
}

/**
 * Collect existing frame URLs for a list of Hi stamps.
 * @return array list of array(stamp, src, label, day)
 */
function hrw_existing_frames($imgs, $day_offset) {
	global $sproc, $cam_type;
	$day_ts = $sproc + 24 * 3600 * $day_offset;
	$dir = date('Y/m/d', $day_ts);
	$day = date('Y-m-d', $day_ts);
	$frames = array();
	foreach ($imgs as $img) {
		$rel = "camchive/$cam_type/$dir/$img$cam_type.jpg";
		if (file_exists(Site::CAM_ROOT . $rel)) {
			$frames[] = array(
				'stamp' => $img,
				'src' => '/' . $rel,
				'label' => substr($img, 0, 2) . ':' . substr($img, 2, 2),
				'day' => $day,
			);
		}
	}
	return $frames;
}

$pre_frames = $pre_imgs ? hrw_existing_frames($pre_imgs, -1) : array();
$post_frames = hrw_existing_frames($post_imgs, 0);
$all_frames = array_merge($pre_frames, $post_frames);
$modal_frames = $all_frames;

if (!$all_frames) {
	// No individual frames — fall back to datestamped daily summary (legacy archive)
	$summaries = hrw_daily_summary($sproc);
	echo '<div class="hrw-summary">';
	echo '<h2>', htmlspecialchars($is_today ? 'Today' : $datedescrip), '</h2>';
	if ($summaries) {
		foreach ($summaries as $sum) {
			echo '<img class="hrw-summary-img" src="', htmlspecialchars($sum['url']), '?', $qrand,
				'" alt="Webcam summary for ', htmlspecialchars($datedescrip), '" />';
		}
		echo '<p class="hrw-summary-note">Individual frames are not available for this day; showing the daily summary image.</p>';
	} else {
		echo '<p>No images available for this day.</p>';
	}
	echo '</div>';
} else {
	if ($pre_frames) {
		hrw_render_grid($pre_frames, 'Yesterday', $width_opt);
	}
	hrw_render_grid($post_frames, $is_today ? 'Today' : $datedescrip, $width_opt);
}

function hrw_render_grid($frames, $heading, $cols) {
	if (!$frames) { return; }
	echo '<h2 class="hrw-heading">', htmlspecialchars($heading), '</h2>';
	echo '<div class="hrw-grid" style="--hrw-cols:', (int)$cols, '">';
	foreach ($frames as $f) {
		echo '<figure>';
		echo '<button type="button" class="hrw-thumb" data-src="', htmlspecialchars($f['src']), '"'
			. ' data-label="', htmlspecialchars($f['label']), '"'
			. ' data-day="', htmlspecialchars($f['day']), '"'
			. ' title="View ', htmlspecialchars($f['label']), ' frame">'
			. '<img src="', htmlspecialchars($f['src']), '?', date('dmYH'), '" loading="lazy"'
			. ' alt="Skycam ', htmlspecialchars($f['label']), '" /></button>';
		echo '<figcaption>', htmlspecialchars($f['label']), '</figcaption></figure>';
	}
	echo '</div>';
}

if ($all_frames) {
?>
<div id="cam-modal" class="cam-modal" hidden>
	<div class="cam-modal-backdrop" data-cam-modal-close></div>
	<div class="cam-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="cam-modal-label">
		<button type="button" class="cam-modal-close" data-cam-modal-close aria-label="Close">&times;</button>
		<button type="button" class="cam-modal-nav cam-modal-prev" aria-label="Previous frame">&#9664;</button>
		<figure class="cam-modal-figure">
			<img id="cam-modal-img" src="" alt="" />
			<figcaption id="cam-modal-label"></figcaption>
		</figure>
		<button type="button" class="cam-modal-nav cam-modal-next" aria-label="Next frame">&#9654;</button>
	</div>
</div>

<script type="text/javascript">
//<![CDATA[
$(function() {
	var frames = <?php echo json_encode($modal_frames); ?>;
	var $modal = $('#cam-modal');
	var $img = $('#cam-modal-img');
	var $label = $('#cam-modal-label');
	var $btnPrev = $modal.find('.cam-modal-prev');
	var $btnNext = $modal.find('.cam-modal-next');
	var index = 0;

	function showFrame(i) {
		if (!frames.length) { return; }
		index = (i + frames.length) % frames.length;
		var f = frames[index];
		$img.attr({ src: f.src, alt: 'Skycam ' + f.label });
		$label.text(f.day + ' ' + f.label);
		$btnPrev.prop('disabled', frames.length < 2);
		$btnNext.prop('disabled', frames.length < 2);
	}

	function openModal(src) {
		index = 0;
		for (var i = 0; i < frames.length; i++) {
			if (frames[i].src === src) { index = i; break; }
		}
		$modal.removeAttr('hidden');
		$('body').addClass('cam-modal-open');
		showFrame(index);
	}

	function closeModal() {
		$modal.attr('hidden', 'hidden');
		$('body').removeClass('cam-modal-open');
		$img.removeAttr('src');
	}

	$('.hrw-thumb').on('click', function() {
		openModal($(this).attr('data-src'));
	});
	$modal.on('click', '[data-cam-modal-close]', closeModal);
	$btnPrev.on('click', function() { showFrame(index - 1); });
	$btnNext.on('click', function() { showFrame(index + 1); });

	$(document).on('keydown', function(e) {
		if ($modal.is('[hidden]')) { return; }
		if (e.keyCode === 27) { closeModal(); }
		else if (e.keyCode === 37) { showFrame(index - 1); }
		else if (e.keyCode === 39) { showFrame(index + 1); }
	});
});
//]]>
</script>
<?php
}
?>

<?php Page::End(); ?>
