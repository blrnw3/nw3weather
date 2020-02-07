<?php require('unit-select.php');

		$file = 13;
		$subfile = true;

		$startYear = 2009;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Wind roses</title>

	<meta name="description" content="View daily, monthly and annual wind roses for Hampstead, North London." />

	<?php require('chead.php'); ?>
	<?php include_once("ggltrack.php");
		echo JQUERY; ?>

<script type="text/javascript">
//<![CDATA[
	function setImg() {
		var yr = $("#year").val();
		var mon = $("#month").val();
		var day = $("#day").val();
		var sz = $('#sz').val();
		var monName = $("#month option:selected").text();
		var st;
		var en;
		var heading;

		if(mon > 0) {
			$("#day").prop('disabled', false);
		} else {
			day = 0;
			$("#day").val(day);
			$("#day").prop('disabled', 'disabled');
		}
		if(day > 0 && mon > 0 && yr > 0) {
			st = yr + mon + day;
			en = st;
			heading = 'Daily - ' + day + ' ' + monName + ' ' + yr;
		} else if(mon > 0 && yr > 0) {
			st = yr + mon + '01';
			en = 'month';
			heading = 'Monthly - ' + monName + ' ' + yr;
		} else if(yr > 0) {
			st = yr + '0101';
			en = 'year';
			heading = 'Annual - ' + yr;
		} else {
			st = '20110101';
			en = 'now';
			heading = '2011 - current';
		}

		var src = '/windrose.php?st=' + st + '&en=' + en + '&x='+ sz +'&y='+ sz +'';
		var imgBox = document.getElementById('windrose');
		imgBox.innerHTML = '<img id="windrose" src="' + src + '" width="'+ sz +'" height="'+ sz +'" title="London windrose" alt="Windrose" />';

		$("#heading").text(heading);
	}

	$(document).ready(function() {
		setImg();
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

<h1>Wind rose Viewer</h1>

<div style="padding:10px">
	<form method="get" action="" style="display:block">
		<?php
		//year select
		echo '<span>Year</span><select class="timelapse" size="13" id="year" onchange="setImg();">';
		echo '<option value="0">All</option>';
		echo '<option value="'. $yr_yest .'" selected="selected">'. $yr_yest .'</option>';
		for($i = $yr_yest-1; $i >= $startYear; $i--) {
			echo '<option value="'. $i .'">'. $i .'</option>
				';
		}
		echo '</select>';

		//month select
		echo '<span>Month</span><select class="timelapse" size="13" id="month" onchange="setImg();">';
		echo '<option value="0" selected="selected">All</option>';
		for($i = 1; $i <= 12; $i++) {
			echo '<option value="' . zerolead($i) . '">', $months3[$i-1], '</option>
				';
		}
		echo '</select>';

		//day select
		echo '<span>Day</span><select class="timelapse" disabled="disabled;" size="13" id="day" onchange="setImg();">';
		echo '<option value="0" selected="selected">All</option>';
		for($i = 1; $i <= 31; $i++) {
			echo '<option value="' . zerolead($i) . '">', $i, '</option>
				';
		}
		echo '</select>';
		?>
		<span>Size</span>
		<select class="timelapse" size="4" id="sz" onchange="setImg();">
			<option value="820">maximum</option>
			<option value="700" selected="selected">large</option>
			<option value="600">medium</option>
			<option value="500">small</option>
		</select>
	</form>
</div>
<h2 id="heading">Annual - <?php echo $dyear ?></h2>

<div style="margin:0.5em;" id="windrose">Click on one of the options above to play</div>

<div style="margin:1em;"><b>NB:</b> Much of 2009 and 2010, and a small proportion of other days, have bad or missing data<br /></div>
</div>

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>

</body>
</html>