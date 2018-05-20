<?php
$allDataNeeded = false;
require('unit-select.php'); ?>

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

			$("#loader").html("Loading...").css({"color": "red"});

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

			$("#chart").one("load", function() {
				$("#loader").html("OK").css({"color": "green"});
			}).attr('src', 'graph' + type + '.php?x=845&y=450&type='+ wxvar + extras);

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
		<select id="wxvar" size="20" style="margin-right:2em;" onchange="changeChart();" >
			<optgroup label="Temperature">
				<option value="tmin" >Minimum Temperature
				</option><option value="tmax" >Maximum Temperature
				</option><option value="tmean" selected="selected">Mean Temperature
				</option>
			</optgroup>
			<optgroup label="Rainfall">
				<option value="rain" >Rainfall
				</option><option value="hrmax" >Maximum Hourly Rain
				</option><option value="10max" >Maximum 10-min Rain
				</option><option value="ratemax" >Maximum Rain Rate
				</option>
			</optgroup>
			<optgroup label="Wind"><option value="wmean" >Mean Wind Speed
				</option><option value="wmax" >Maximum Wind Speed
				</option><option value="gust" >Maximum Gust
				</option><option value="wdir">Mean Wind Direction
				</option>
			</optgroup>
			<optgroup label="Humidity">
				<option value="hmin" >Minimum Humidity
				</option><option value="hmax" >Maximum Humidity
				</option><option value="hmean" >Mean Humidity
				</option>
			</optgroup>
			<optgroup label="Pressure">
				<option value="pmin" >Minimum Pressure
				</option><option value="pmax" >Maximum Pressure
				</option><option value="pmean" >Mean Pressure
				</option>
			</optgroup>
			<optgroup label="Dew Point"><option value="dmin" >Minimum Dew Point
				</option><option value="dmax" >Maximum Dew Point
				</option><option value="dmean" >Mean Dew Point
				</option>
			</optgroup>
			<optgroup label="Change"><option value="tc10max" >Max 10m Temp Rise
				</option><option value="tchrmax" >Max 1hr Temp Rise
				</option><option value="hchrmax" >Max 1hr Hum Rise
				</option><option value="tc10min" >Max 10m Temp Fall
				</option><option value="tchrmin" >Max 1hr Temp Fall
				</option><option value="hchrmin" >Max 1hr Hum Fall
				</option>
			</optgroup>
			<optgroup label="Range"><option value="trange" >Temperature Range
				</option><option value="hrange" >Humidity Range
				</option><option value="prange" >Pressure Range
				</option>
			</optgroup>
			<optgroup label="Observations"><option value="sunhr" >Sun Hours
				</option><option value="wethr" >Wet Hours
				</option><option value="ratemean" >Mean Rain Rate
				</option><option value="snow" >Falling Snow
				</option><option value="lysnw" >Lying Snow
				</option><option value="hail" >Hail
				</option><option value="thunder" >Thunder
				</option><option value="fog" >Dense Fog
				</option>
			</optgroup>
			<optgroup label="Anomalies"><option value="tmina">Min Temp Anomaly
				</option><option value="tmaxa">Max Temp Anomaly
				</option><option value="tmeana">Mean Temp Anom
				</option><option value="sunhrp">Sun % of max possible
				</option><option value="wethrp">Wet % of day
				</option>
			</optgroup>
			<optgroup label="Misc."><option value="nightmin" >Night Minimum (21-09)
				</option><option value="daymax" >Day Maximum (09-21)
				</option><option value="w10max" >Max 10-min Wind Speed
				</option><option value="whrmax" >Max Hourly Wind Speed
				</option>
			</optgroup>
		</select>

		<select id="type" size="5" style="margin-right:2em;" onchange="changeChart();" >
			<option value="31" selected="selected">Daily</option>
			<option value="2.2">Monthly-mean</option>
			<option value="2.0">Monthly-low</option>
			<option value="2.1">Monthly-high</option>
			<option value="2.3">Monthly-count</option>
		</select>

		<?php
//		var_dump(newData("tmina", 2018, 0));
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
		echo '<select style="display:none;" size="13" id="month" onchange="changeChart();">';
		echo '<option value="0" selected="selected">All</option>';
		for($i = 1; $i <= 12; $i++) {
			echo '<option value="' . $i . '"';
			echo '>', $months3[$i-1], '</option>
				';
		}
		echo '</select>';
		?>

		<select style="margin-left:2em" size="12" id="lengthD" onchange="changeChart();">
			<option value="31" selected="selected">31d</option>
			<option value="60">60d</option>
			<option value="90">90d</option>
			<option value="180">180d</option>
			<option value="365">365d</option>
			<option value="730">2 yrs</option>
			<option value="1095">3 yrs</option>
			<option value="1461">4 yrs</option>
			<option value="1826">5 yrs</option>
			<option value="2922">8 yrs</option>
			<option value="3652">10 yrs</option>
			<option value="99999">All</option>
		</select>

		<select style="display:none; margin-left:2em" size="9" id="lengthM" onchange="changeChart();">
			<option value="12" selected="selected">12</option>
			<option value="18">18</option>
			<option value="24">24</option>
			<option value="36">3 yrs</option>
			<option value="48">4 yrs</option>
			<option value="60">5 yrs</option>
			<option value="96">8 yrs</option>
			<option value="120">10 yrs</option>
			<option value="9999">All</option>
		</select>

	</form>
	<div style="margin-top:0.5em" id="loader">OK</div>
</div>

<img id="chart" src="graph31.php?x=840&amp;y=440&amp;type=tmean&amp;length=31" alt="Chart" />

</div>

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>

</body>
</html>