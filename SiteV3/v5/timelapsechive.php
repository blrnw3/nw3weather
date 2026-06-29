<?php
require("Page.php");
Page::init([
	"fileNum" => 2,
	"title" => "Webcam timelapse archive",
	"description" => "View daily historical web cam (weather skycam) image video timelapses from Hampstead Heath, North London."
]);
Page::Start();

$startYearCams = 2012;
?>

<h1>Webcam Timelapse Archive</h1>

<div style="padding:10px">
	<form method="get" action="" style="display:block">
		<?php
		echo '<span>Year</span><select class="timelapse" size="15" id="year" onchange="setVid();">';
		echo '<option value="' . Date::$yr_yest . '" selected="selected">' . Date::$yr_yest . '</option>';
		for ($i = Date::$yr_yest - 1; $i >= $startYearCams; $i--) {
			echo '<option value="' . $i . '">' . $i . '</option>';
		}
		echo '</select>';

		echo '<span>Month</span><select class="timelapse" size="15" id="month" onchange="setVid();">';
		echo '<option value="0" selected="selected">All</option>';
		for ($i = 1; $i <= 12; $i++) {
			echo '<option value="' . Util::zerolead($i) . '">', Date::$months3[$i - 1], '</option>';
		}
		echo '</select>';

		echo '<span>Day</span><select class="timelapse" disabled="disabled" size="15" id="day" onchange="setVid();">';
		echo '<option value="0" selected="selected">All</option>';
		for ($i = 1; $i <= 31; $i++) {
			echo '<option value="' . Util::zerolead($i) . '">', $i, '</option>';
		}
		echo '</select>';
		?>
	</form>
</div>
<h2 id="heading">Annual - <?php echo Date::$dyear ?></h2>

<div style="height: 590px" id="timelapse">Click on one of the options above to play</div>

<b>NB:</b> Daily timelapses are only available online for the past few years. Please contact me for older ones back to 2010 <br />

<script type="text/javascript">
//<![CDATA[
	function setVid(noautoplay) {
		var yr = $("#year").val();
		var mon = $("#month").val();
		var day = $("#day").val();
		var monName = $("#month option:selected").text();
		var seek = 0;
		var vidname;
		var heading;

		if (mon > 0) {
			$("#day").prop('disabled', false);
		} else {
			day = 0;
			$("#day").val(day);
			$("#day").prop('disabled', 'disabled');
		}
		if (day > 0 && mon > 0) {
			vidname = yr + mon + day;
			heading = 'Daily - ' + day + ' ' + monName + ' ' + yr;
		} else if (mon > 0) {
			vidname = 'monthly_' + yr + '_' + mon;
			heading = 'Monthly - ' + monName + ' ' + yr;
		} else {
			vidname = 'yearly_' + yr;
			heading = 'Annual - ' + yr;
		}

		var src = '/cam/timelapse/skycam_' + vidname + '.mp4';
		console.log("Loading " + src);
		var vidBox = document.getElementById('timelapse');
		vidBox.innerHTML = '<video id="timelapse-vid" width="864" height="576" controls><source src="' + src + '" type="video/mp4"></video>';

		var vid = document.getElementById('timelapse-vid');
		vid.currentTime = seek;
		if (!noautoplay) {
			vid.play();
		}

		$("#heading").text(heading);
	}

	$(document).ready(function() {
		setVid(true);
	});
//]]>
</script>

<?php Page::End(); ?>
