<?php require('unit-select.php');

		$file = 2;
		$subfile = true;

		$startYearCams = 2012;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Webcam timelapse archive</title>

	<meta name="description" content="View daily historical web cam (weather skycam) image video timelapses from Hampstead Heath, North London." />

	<?php require('chead.php'); ?>
	<?php include_once("ggltrack.php");
		echo JQUERY; ?>

<script type="text/javascript">
//<![CDATA[
	function setVid(noautoplay) {
		var yr = $("#year").val();
		var mon = $("#month").val();
		var day = $("#day").val();
		var monName = $("#month option:selected").text();
		var seek = 0;  // TODO
		var vidname;
		var heading;

		if(mon > 0) {
			$("#day").prop('disabled', false);
		} else {
			day = 0;
			$("#day").val(day);
			$("#day").prop('disabled', 'disabled');
		}
		if(day > 0 && mon > 0) {
			vidname = yr + mon + day;
			heading = 'Daily - ' + day + ' ' + monName + ' ' + yr;
		} else if(mon > 0) {
			vidname = 'monthly_' + yr + '_' + mon;
			heading = 'Monthly - ' + monName + ' ' + yr;
		} else {
			vidname = 'yearly_' + yr;
			heading = 'Annual - ' + yr;
		}

		var src = '/camchive/timelapse/skycam_' + vidname + '.mp4';
		console.log("Loading " + src);
		var vidBox = document.getElementById('timelapse');
		vidBox.innerHTML = '<video id="timelapse-vid" width="640" height="480" controls><source src="' + src + '" type="video/mp4"></video>';

		var vid = document.getElementById('timelapse-vid');
		vid.currentTime = seek;
		if(!noautoplay) {
			vid.play();
		}

		$("#heading").text(heading);
	}

	$(document).ready(function() {
		setVid(true);
	});

//]]>
</script>
</head>

<body>
	<!-- ##### Header ##### -->
	<?php require('header.php'); ?>

	<!-- ##### Left Sidebar ##### -->
	<?php require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->

<div id="main">

<h1>Webcam Timelapse Archive</h1>

<div style="padding:10px">
	<form method="get" action="" style="display:block">

		<?php
		//year select
		echo '<span>Year</span><select class="timelapse" size="15" id="year" onchange="setVid();">';
		echo '<option value="'. $yr_yest .'" selected="selected">'. $yr_yest .'</option>';
		for($i = $yr_yest-1; $i >= $startYearCams; $i--) {
			echo '<option value="'. $i .'">'. $i .'</option>
				';
		}
		echo '</select>';

		//month select
		echo '<span>Month</span><select class="timelapse" size="15" id="month" onchange="setVid();">';
		echo '<option value="0" selected="selected">All</option>';
		for($i = 1; $i <= 12; $i++) {
			echo '<option value="' . zerolead($i) . '">', $months3[$i-1], '</option>
				';
		}
		echo '</select>';

		//day select
		echo '<span>Day</span><select class="timelapse" disabled="disabled;" size="15" id="day" onchange="setVid();">';
		echo '<option value="0" selected="selected">All</option>';
		for($i = 1; $i <= 31; $i++) {
			echo '<option value="' . zerolead($i) . '">', $i, '</option>
				';
		}
		echo '</select>';
		?>
	</form>
</div>
<h2 id="heading">Annual - <?php echo $dyear ?></h2>

<div style="height: 490px" id="timelapse">Click on one of the options above to play</div>

<b>NB:</b> Before 24th Sep 2016, daily timelapses are unavailable <br />
</div>

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>

</body>
</html>