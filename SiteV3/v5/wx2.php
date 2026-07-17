<?php
require("Page.php");
Page::init([
	"fileNum" => 2,
	"title" => "Webcam",
	"description" => 'Hampstead, North London - Live Webcam and timelapses from NW3 weather station
	- last day and last hour timelapses, sky weathercam and ground weather cam.'
]);

/** Relative path for a hik archive frame (Hi stamp under Y/m/d). */
function hik_frame_rel($ts) {
	return 'camchive/hik/' . date('Y/m/d', $ts) . '/' . date('Hi', $ts) . 'hik.jpg';
}
function hik_frame_path($ts) {
	return Site::CAM_ROOT . hik_frame_rel($ts);
}
function hik_frame_url($ts) {
	return '/' . hik_frame_rel($ts);
}

Page::Start();

$sunrise_hr = date_sunrise(time(), SUNFUNCS_RET_DOUBLE, Site::LATITUDE, Site::LONGITUDE, Site::ZENITH, date('I'));
$risetime = $sunrise_hr - 1;
$risesecs = 2.5 * $risetime;  // 2.5s per hour timelapse
$yr_yest = Date::$yr_yest;
$mon_yest_zero = Util::zerolead(Date::$mon_yest);
$lastmonth = date("Y_m", Date::mkdate(Date::$dmonth - 1, 1, Date::$dyear));
$lastyear = intval(Date::$dyear) - 1;
$today_seek = (intval(Date::$dhr) - 2) < $risetime ? 0 : $risesecs;
$yest_seek = $risesecs;

// Rolling 24hr half-hour thumbnails + high-res frame index for the modal
$base = floor(time() / 1800) * 1800; // most recent completed half-hour
$grid_frames = array();
$days_seen = array();
for ($i = 47; $i >= 0; $i--) {
	$ts = $base - $i * 1800;
	$stamp = date('Hi', $ts);
	$thumb_rel = "camchive/thumbs/hik/${stamp}hik.jpg";
	$has_thumb = file_exists(Site::CAM_ROOT . $thumb_rel);
	$day = date('Y-m-d', $ts);
	$days_seen[$day] = true;
	$grid_frames[] = array(
		'ts' => $ts,
		'stamp' => $stamp,
		'label' => date('H:i', $ts),
		'day' => $day,
		'thumb' => '/' . $thumb_rel,
		'src' => hik_frame_url($ts),
		'has' => $has_thumb,
	);
}

// Full calendar-day frame lists (30-min) for modal prev/next within a day
$day_frames = array();
foreach (array_keys($days_seen) as $day) {
	$parts = explode('-', $day);
	$day_start = Date::mkdate((int)$parts[1], (int)$parts[2], (int)$parts[0]);
	$frames = array();
	for ($m = 0; $m < 1440; $m += 30) {
		$ts = $day_start + $m * 60;
		if ($ts > time()) { break; }
		$path = hik_frame_path($ts);
		if (file_exists($path) && filesize($path) > 1024) {
			$frames[] = array(
				'src' => hik_frame_url($ts),
				'label' => date('H:i', $ts),
				'day' => $day,
			);
		}
	}
	$day_frames[$day] = $frames;
}

// Timelapse placeholder: sunrise frame (assumed present every minute), else midnight if before sunrise
$rise_mins = (int) round($sunrise_hr * 60);
$now_mins = (int) Date::$dhr * 60 + (int) date('i');
if ($now_mins >= $rise_mins) {
	$placeholder_stamp = Util::zerolead((int) floor($rise_mins / 60)) . Util::zerolead($rise_mins % 60);
	$placeholder_label = 'sunrise';
} else {
	$placeholder_stamp = '0000';
	$placeholder_label = 'midnight';
}
$placeholder_src = '/camchive/hik/' . date('Y/m/d') . '/' . $placeholder_stamp . 'hik.jpg';
$placeholder_time = substr($placeholder_stamp, 0, 2) . ':' . substr($placeholder_stamp, 2, 2);
?>

<h1>Webcam</h1>

<h3>High-resolution Skycam</h3>

<p>The camera is a 5MP Hikvision DS-2CD2055FWD-I with 4mm focal length, and is looking NE over Hampstead Heath.</p>
<img name="refresh-new" id="live-skycam" class="live-skycam" src="/skycam_wx2.jpg" title="Latest skycam" alt="Live skycam" />

<noscript>JavaScript is required for the automatic updates</noscript>

<p>The image is updated automatically every 10s, day and night, operating with a delay of about 20s.<br />
<a href="#" id="cam-res-toggle" class="cam-res-toggle">Switch to full resolution (high-bandwidth)</a> <span id="cam-bw-warn" class="cam-bw-warn" hidden></span>
</p>
<hr />

<br />
<?php if(DATE::$time < DATE::$sunrise || DATE::$time > DATE::$sunset) {
		echo '<h3>Latest daylight webcam image</h3><img src="/skycam_wx2_sunset.jpg" alt="Latest sunsetcam" class="live-skycam" /><br /><br />';
	} ?>

<script type="text/javascript">
//<![CDATA[
$(function() {
	var camStd = '/skycam_wx2.jpg';
	var camFull = '/skycam.jpg';
	var fullRes = false;
	var $img = $('#live-skycam');
	var $toggle = $('#cam-res-toggle');
	var $warn = $('#cam-bw-warn');

	$toggle.on('click', function(e) {
		e.preventDefault();
		fullRes = !fullRes;
		// imageNew is the global used by the 10s refresher in JS_Scripts.php
		imageNew = fullRes ? camFull : camStd;
		$img.attr('src', imageNew + '?' + timePHP);
		$toggle.text(fullRes ? 'Revert to standard resolution' : 'Switch to full resolution (high-bandwidth)');
		if (fullRes) { $warn.removeAttr('hidden'); }
		else { $warn.attr('hidden', 'hidden'); }
	});
});
//]]>
</script>

<h3>Skycam images from the last 24 hours</h3>
<p>Click a thumbnail to view the higher-resolution frame. A <a href="highreswebcam.php" title="Full-resolution summary"><b>full-day higher resolution view</b></a> is also available.</p>
<div class="cam-grid">
<?php
foreach ($grid_frames as $f) {
	echo '<figure>';
	if ($f['has']) {
		echo '<button type="button" class="cam-grid-thumb" data-day="', htmlspecialchars($f['day']), '"'
			. ' data-src="', htmlspecialchars($f['src']), '"'
			. ' data-label="', htmlspecialchars($f['label']), '"'
			. ' title="View ', htmlspecialchars($f['label']), ' frame">'
			. '<img src="', htmlspecialchars($f['thumb']), '" width="300" height="200" loading="lazy"'
			. ' alt="Skycam ', htmlspecialchars($f['label']), '" /></button>';
	} else {
		echo '<span class="cam-grid-missing">No image</span>';
	}
	echo '<figcaption>', htmlspecialchars($f['label']), '</figcaption></figure>';
}
?>
</div>
<a href="highreswebcam.php" title="Webcam archive"><b>See full archive</b></a> (starting 01/08/10).

<hr />

<h3>Skycam Timelapses</h3>

<div id="skycam-selector">
	<span id="timelapse-0" onclick="loadVid('skycam_today', <?php echo $today_seek; ?>, 0)">Today</span>
	<span id="timelapse-1" onclick="loadVid('skycam_yest', <?php echo $yest_seek; ?>, 1)">Yesterday</span>
	<span id="timelapse-2" onclick="loadVid('<?php echo "skycam_monthly_${yr_yest}_${mon_yest_zero}"; ?>', 0, 2)">This month</span>
	<span id="timelapse-3" onclick="loadVid('<?php echo "skycam_monthly_${lastmonth}"; ?>', 0, 3)">Last month</span>
	<span id="timelapse-4" onclick="loadVid('<?php echo "skycam_yearly_${yr_yest}"; ?>', 0, 4)">This year</span>
	<span id="timelapse-5" onclick="loadVid('<?php echo "skycam_yearly_${lastyear}"; ?>', 0, 5)">Last year</span>
</div>

<div id="timelapse">
<?php if ($placeholder_src) { ?>
	<div class="timelapse-placeholder">
		<img src="<?php echo htmlspecialchars($placeholder_src); ?>" alt="Skycam <?php echo htmlspecialchars($placeholder_label); ?> frame" width="864" height="576" />
		<div class="timelapse-placeholder-caption">
			<?php echo "Click an option above to play"; ?>		</div>
	</div>
<?php } else { ?>
	Click on one of the options above to play
<?php } ?>
</div>

<p>Today's timelapse is updated hourly. Monthly and annual timelapses update daily.
<br />
<a href="timelapsechive.php" title="Webcam timelapse archive"><b>See full timelapse archive</b></a>
</p>

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
	var dayFrames = <?php echo json_encode($day_frames); ?>;
	var $modal = $('#cam-modal');
	var $img = $('#cam-modal-img');
	var $label = $('#cam-modal-label');
	var $btnPrev = $modal.find('.cam-modal-prev');
	var $btnNext = $modal.find('.cam-modal-next');
	var frames = [];
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

	function openModal(day, src) {
		frames = dayFrames[day] || [];
		if (!frames.length) {
			frames = [{ src: src, label: '', day: day }];
		}
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

	$('.cam-grid-thumb').on('click', function() {
		var $btn = $(this);
		openModal($btn.attr('data-day'), $btn.attr('data-src'));
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

<?php Page::End(); ?>
