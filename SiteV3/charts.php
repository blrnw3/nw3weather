<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php
	$file = 32;
	//$showMonth = true;
	//$showYear = true;
	$showNum = true;
	$isDaily = true;
	$linkToOther = 'chartMonthly';
	$datgenHeading = 'Daily Data Charts';
	$needValcolStyle = true;
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="pragma" content="no-cache" />

	<title>NW3 Weather - Chart Viewer</title>

	<meta name="description" content="Latest and Historical 31-day and longer graphs/charts of various weather variables." />

<?php require('chead.php'); ?>
<?php include('ggltrack.php');
	echo JQUERY; ?>

	<script type="text/javascript">
		//<![CDATA[
		/**
		 * Changes chart based on dynamic input
		 * @returns {undefined}
		 * @author &copy; Ben Lee-Rodgers, nw3weather, April 2013
		 */
		function changeChart() {
			var extras = '';
			var len;

			var type = $("#type").val();
			var yr = $("#year").val();
			var wxvar = $("#wxvar").val();

			if(type == 31) {
				$("#lengthM").hide();
				$("#lengthD").show();
				len = $("#lengthD").val();

				if(yr > 0) {
					$("#month").show();
					$("#lengthD").val(31);
					$("#lengthD").prop('disabled', 'disabled');
					extras += '&year='+ yr;
					extras += '&month='+ $("#month").val();
				} else {
					$("#month").hide();
					$("#lengthD").prop('disabled', false);
				}
			} else {
				$("#lengthD").hide();
				$("#month").hide();
				$("#lengthM").show();
				len = $("#lengthM").val();

				extras += '&mmm=' + type;

				if(yr > 0) {
					extras += '&year='+ yr;
					$("#lengthM").val(12);
					$("#lengthM").prop('disabled', 'disabled');
				} else {
					$("#lengthM").prop('disabled', false);
				}
				type = '12';
			}

			extras += '&length=' + len;

			$("#chart").attr('src', 'graph' + type + '.php?x=845&y=450&type='+ wxvar + extras);
			$("#heading").text('Daily Data Charts - ' + $("#wxvar option:selected").text());
		}
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

<h1 id="heading">Daily Data Charts - Mean Temperature<br /></h1>

<h2>Select Weather Variable, Chart Type, Period, and Period Length</h2>
<div style="padding:10px">
	<form method="get" action="" style="display:block">
		<select id="wxvar" size="12" style="margin-right:2em;" onchange="changeChart();" >
			<optgroup label="Temperature">
				<option value="tmin" >Minimum Temperature
				</option><option value="tmax" >Maximum Temperature
				</option><option value="tmean" selected="selected">Mean Temperature
				</option>
			</optgroup>
			<optgroup label="Humidity"><option value="hmin" >Minimum Humidity
				</option><option value="hmax" >Maximum Humidity
				</option><option value="hmean" >Mean Humidity
				</option>
			</optgroup>
			<optgroup label="Pressure"><option value="pmin" >Minimum Pressure
				</option><option value="pmax" >Maximum Pressure
				</option><option value="pmean" >Mean Pressure
				</option></optgroup><optgroup label="Wind"><option value="wmean" >Mean Wind Speed
				</option><option value="wmax" >Maximum Wind Speed
				</option><option value="gust" >Maximum Gust
				</option><option value="wdir">Mean Wind Direction
				</option></optgroup><optgroup label="Rainfall"><option value="rain" >Rainfall
				</option><option value="hrmax" >Maximum Hourly Rain
				</option><option value="10max" >Maximum 10-min Rain
				</option><option value="ratemax" >Maximum Rain Rate
				</option></optgroup><optgroup label="Dew Point"><option value="dmin" >Minimum Dew Point
				</option><option value="dmax" >Maximum Dew Point
				</option><option value="dmean" >Mean Dew Point
				</option></optgroup><optgroup label="Change"><option value="tc10max" >Max 10m Temp Rise
			</option><option value="tchrmax" >Max 1hr Temp Rise
			</option><option value="hchrmax" >Max 1hr Hum Rise
			</option><option value="tc10min" >Max 10m Temp Fall
			</option><option value="tchrmin" >Max 1hr Temp Fall
			</option><option value="hchrmin" >Max 1hr Hum Fall
				</option></optgroup><optgroup label="Range"><option value="trange" >Temperature Range
				</option><option value="hrange" >Humidity Range
				</option><option value="prange" >Pressure Range
				</option></optgroup><optgroup label="Observations"><option value="sunhr" >Sun Hours
				</option><option value="wethr" >Wet Hours
				</option><option value="ratemean" >Mean Rain Rate
				</option><option value="snow" >Falling Snow
				</option><option value="lysnw" >Lying Snow
				</option><option value="hail" >Hail
				</option><option value="thunder" >Thunder
				</option><option value="fog" >Dense Fog
				</option></optgroup>
			<optgroup label="Misc."><option value="nightmin" >Night Minimum (21-09)
				</option><option value="daymax" >Day Maximum (09-21)
				</option><option value="w10max" >Max 10-min Wind Speed
				</option><option value="whrmax" >Max Hourly Wind Speed
				</option></optgroup>
		</select>

		<select id="type" size="4" style="margin-right:2em;" onchange="changeChart();" >
			<option value="31" selected="selected">Daily</option>
			<option value="2.2">Monthly-mean</option>
			<option value="2.0">Monthly-low</option>
			<option value="2.1">Monthly-high</option>
		</select>

<!--		<br />

		<select id="hist" size="2" onchange="changeChart();" >
			<option value="0" selected="selected">Current</option>
			<option value="1">Monthly-mean</option>
		</select>-->

		<?php
		//year select
		echo '<select style="margin-right:0.5em;" size="'.
			($dyear-$startYear+2) .'" id="year" onchange="changeChart();">
				<option selected="selected" value="0">Current</option>';

		for($i = $dyear; $i >= $startYear; $i--) {
			echo '<option value="'. $i .'">'. $i .'</option>
				';
		}
		echo '</select>';

		//month select
		echo '<select style="display:none;" size="12" id="month" onchange="changeChart();">';
		for($i = 1; $i <= 12; $i++) {
			echo '<option value="' . $i . '"';
			if($i == $dmonth) { echo ' selected="selected"'; }
			echo '>', $months3[$i-1], '</option>
				';
		}
		echo '</select>';
		?>

		<select style="margin-left:2em" size="6" id="lengthD" onchange="changeChart();">
			<option value="15">15</option>
			<option value="31" selected="selected">31</option>
			<option value="50">50</option>
			<option value="100">100</option>
			<option value="200">200</option>
			<option value="365">365</option>
		</select>

		<select style="display:none; margin-left:2em" size="6" id="lengthM" onchange="changeChart();">
			<option value="6">6</option>
			<option value="12" selected="selected">12</option>
			<option value="18">18</option>
			<option value="24">24</option>
			<option value="36">36</option>
			<option value="48">48</option>
		</select>

	</form>
</div>

<img id="chart" src="graph31.php?x=840&amp;y=440&amp;type=tmean&amp;length=31" alt="Chart" />

</div>

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>

</body>
</html>