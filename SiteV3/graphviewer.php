<?php require('unit-select.php');

		$file = 31;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Custom Graphs</title>

	<meta name="description" content="View advanced, customisable graphs for a multitude of weather parameters over a series of period lengths" />

	<?php require('chead.php'); ?>
	<?php include_once('ggltrack.php');
	echo JQUERY; ?>

	<script type="text/javascript">
		/**
		 * Changes chart based on dynamic input
		 * @returns {undefined}
		 * @param {bool} show show or hide, innit
		 * @author &copy; Ben Masschelein-Rodgers, nw3weather, April 2013
		 */
		function autoscale(show) {
			if(show)
				$("#autoscale").show();
			else
				$("#autoscale").hide();
		}
	</script>
</head>

<body>
	<!-- ##### Header ##### -->
	<?php require('header.php'); ?>

	<!-- ##### Left Sidebar ##### -->
	<?php require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->
	<div id="main">

<h1>Custom Graph Viewer</h1>

<?php
if(isset($_GET['x'])) { $dimx = $_GET['x']; } else { $dimx = 870; }
if(isset($_GET['y'])) { $dimy = $_GET['y']; } else { $dimy = 450; }
$qstring = 'x='.$dimx.'&amp;y='.$dimy;
//if(isset($_GET['ts'])) { $stt = $_GET['ts']; } else { $stt = false; }
//if(isset($_GET['te'])) { $end = $_GET['te']; } else { $end = false; }
if(isset($_GET['day'])) {
	$dproc = $_GET['day']; $mproc = $_GET['month']; $yproc = $_GET['year']; $sproc = mkdate($mproc,$dproc,$yproc);
	if($sproc > mkdate()) { $dproc = $dday; $mproc = $dmonth; $yproc = $dyear; $future = true; }
	elseif($sproc < mkdate(2,1,2009)) {  $dproc = 1; $mproc = 2; $yproc = 2009; $past2 = true; }
	else { $qstring .= '&amp;date='.$yproc.zerolead($mproc).zerolead($dproc); }
}
else { $dproc = $dday; $mproc = $dmonth; $yproc = $dyear; }

if(isset($_GET['num'])) {
	$num = $_GET['num'];
	if(mkdate($mproc,$dproc-$num,$yproc) < mkdate(2,1,2009) && !$past2) { $num = 1; $past = true; }
}
else { $num = 3; }
$qstring .= '&amp;num='.$num;

$gtype = isset($_GET['altg']) ? $_GET['altg'] : 'y';
if($gtype == 'yA') {
	for($i = 1; $i < 9; $i++) {
		if(isset($_GET['type'.$i])) {
			$type[$i] = $_GET['type'.$i];
			$qstring .= '&amp;type'.$i.'='.$type[$i]; $more += 1;
		}
	}
}
?>


<form method="get" action="">
<table width="850" cellpadding="5"><tr><td>
	<b>Graph Type: </b>
	<?php $daytypes = array_merge(array_flip($daytypes));
	if($gtype == 'y') { $chosen = ' checked="checked"'; } elseif($gtype == 'y2') { $chosen2 = ' checked="checked"'; } else { $chosen1 = ' checked="checked"'; }
	echo '
		<label onclick="autoscale(false);"><input type="radio" name="altg" value="y"', $chosen, ' /> Temp/Hum/Dew/Rain &nbsp; </label>
		<label onclick="autoscale(false);"><input type="radio" name="altg" value="y2"', $chosen2, ' /> Wind/Gust/Baro &nbsp; </label>
		<label onclick="autoscale(true);"><input type="radio" name="altg" value="yA"', $chosen1, ' /> Autoscale (choose data types below)</label>
		</td></tr> <tr id="autoscale"><td> <b>Autoscale Data Types: </b>';
	for($t = 1; $t <= count($daytypes); $t++) {
		$find = array(' (10-min)', 'Direction', 'Wind Speed'); $repl = array('','Drctn','Speed');
		$tvargv = explode('/',$daynames[$daytypes[$t-1]]); $datnam = str_replace($find,$repl,$tvargv[0]);
		if(($daytypes[$t-1] == $type[$t] || (!$more && $t == 1)) && $gtype == 'yA') { $checked = 'checked="checked" '; } else { $checked = ''; }
		echo '<label><input type="checkbox" name="type',$t,'" value="', $daytypes[$t-1], '" ', $checked, '/> ', $datnam, ' &nbsp;</label>
			';
	}
	echo '</td></tr><tr><td> <b>Period length / days </b><select name="num">
		';
	for($n = 0; $n < count($nums); $n++) {
		$selected = ($num == $nums[$n]) ? 'selected="selected"' : '';
		echo '<option value="', $nums[$n], '" ', $selected, '>', $nums[$n], '</option>
			';
	}
	echo '</select> &nbsp; &nbsp; <b>End Date </b>
		<select name="year">';
	for($y = 2009; $y <= $dyear; $y++) {
		$selected = ($y == $yproc) ? 'selected="selected"' : '';
		echo '<option value="', $y, '" ', $selected, '>', $y, '</option>
			';
	}
	echo '</select>
		<select name="month">';
	for($m = 1; $m <= 12; $m++) {
		$selected = ($m == $mproc) ? 'selected="selected"' : '';
		echo '<option value="', $m, '" ', $selected, '>', $months[$m-1], '</option>
			';
	}
	echo '</select>
		<select name="day">';
	for($d = 1; $d <= 31; $d++) {
		$selected = ($d == $dproc) ? 'selected="selected"' : '';
		echo '<option value="', $d, '" ', $selected, '>', zerolead($d), '</option>
			';
	}
	echo '</select> &nbsp;';
	?>
	<input type="submit" value="Generate Graph" /> &nbsp;
	<a href="graphviewer.php" title="Reset all parameters to default"> <b>Reset</b> </a>
</td></tr></table>
</form>

<script type="text/javascript">
	autoscale(<?php echo makeBool($gtype == 'yA') ?>);
</script>
<?php
echo '<img alt="Advanced graph: ', $qstring, '" src="./graphda',$gtype,'.php?',$qstring,'" /> <br /> ';
//if($num > 40) { echo '<p>Please note that long-period graphs may take up to 30s to generate, and will occasionally fail to load.</p>'; }
if($more > 6) { echo '<b>Only six variables can be shown at one time (too messy).</b>'; }
if($furure) { echo '<br /><b>Selecting a future date forces a default back to the present day.</b>'; }
if($past) { echo '<br /><b>WARNING: Selected date range overlaps with non-extant period (data collection began on 1st Feb 2009). Period has been set to 1.</b>'; }
if($past2) { echo '<br /><b>Data collection began on 1st Feb 2009. Graph defaulted to first available.</b>'; }
?>
</div>
<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>

</body>
</html>