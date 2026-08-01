<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("phptags.php");
	$file = 86; 
	if(isset($_GET['month'])) { $mproc = $_GET['month']; } else { $mproc = date('m',mktime(0,0,0,date('m')-1,date('j'))); }
	if(isset($_GET['year'])) { $yproc = $_GET['year']; } else { $yproc = date('Y',mktime(0,0,0,date('m')-1,date('j'),date('Y'))); }	
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - Monthly Reports</title>
	<meta name="description" content="Old v2 - Detailed historical monthly weather summary report for <?php echo date('F Y',mktime(0,0,0,$mproc,1,$yproc)); ?> from NW3 weather" />

	<?php require('chead.php'); ?>
	<?php include_once("ggltrack.php") ?>
</head>

<body>
	<!-- For non-visual user agents: -->
	<div id="top"><a href="#main-copy" class="doNotDisplay doNotPrint">Skip to main content.</a></div>

	<!-- ##### Header ##### -->
	<?php require('header.php'); ?>

	<!-- ##### Left Sidebar ##### -->
	<?php require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->
<?php
$rep = date("F", mktime(0,0,0,$mproc)).date("Y", mktime(0,0,0,1,1,$yproc)).'.htm';
if(file_exists($rep)) { $dat = mrepdata($mproc, $yproc); }
$prevm = date("m", mktime(0,0,0,$mproc-1)); $nextm = date("m", mktime(0,0,0,$mproc+1));
if(intval($mproc) == 12) { $nexty = $yproc+1; } else { $nexty = $yproc; } if(intval($mproc) == 1) { $prevy = $yproc-1; } else { $prevy = $yproc; }
?>

<div id="main-copy">

<h1>Monthly Weather Reports</h1>

<div align="center">

<?php
$dim = date('t',mktime(0,0,0,$mproc,1,$yproc));
if(intval($mproc) == 10 && $yproc == 2009) echo '<b>Special note</b>: Data is suspect for this month due to partial data loss';
if(intval($mproc) < 8 && intval($mproc) != 1 && $yproc == 2009) echo '<b>Special note</b>: Wind data not valid for this month (valid records began in Aug 2009)';
if(intval($mproc) < 8 && intval($mproc) > 3 && $yproc == 2010) echo '<b>Special note</b>: Wind data not valid for this month (suspended 17th April - 28th July; replaced by METAR data from Heathrow)'; 
$cond1 = mktime(0,0,0,$mproc,1,$yproc) > mktime(0,0,0,2,2,2009) && mktime(0,0,0,$mproc,1,$yproc) < mktime(1,0,0,$date_month+1,1,$date_year);
$cond2 = mktime(0,0,0,$mproc,$dim,$yproc) < mktime(0,0,0,$date_month,$date_day-1,$date_year) && mktime(1,0,0,$mproc,3,$yproc) > mktime(0,0,0,1,1,2009);
$cond3 = mktime(0,0,0,$mproc,1,$yproc) < mktime(0,0,0,$date_month,$date_day-1,$date_year) && mktime(1,0,0,$mproc,3,$yproc) > mktime(0,0,0,2,1,2009);
if(!$cond1 && mktime(0,0,0,$mproc,1,$yproc) < time()) { echo '<br /><b>First report available is February 2009</b>'; }
?>

<table width="750">
<tr>
<td align="left">
<?php if($cond1) { echo '<a href="wxhistmonth.php?year=', $prevy, '&amp;month=', $prevm, '" title="View previous month&#39;s report">'; } ?>
&lt;&lt;Previous Month<?php if($cond1) { echo '</a>'; } ?></td>
<td align="center"><form method="get" action="">
<select name="year">
<?php 
for($i = 2009; $i <= $date_year; $i++) {
	echo '<option value="', $i, '"';
	if(isset($_GET['year'])) { if(intval($_GET['year']) == $i) { echo ' selected="selected"'; } } else { if(date("Y", mktime(0,0,0,$date_month-1,$date_day,$date_year)) == $i) { echo ' selected="selected"'; } }
	echo '>', $i, '</option>'; 
} ?>
</select>
<select name="month">
<?php $months = array('Jan', 'Feb', 'March', 'April', 'May', 'June', 'July', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
for($i = 0; $i < 12; $i++) { 
	echo '<option value="', sprintf('%1$02d',$i+1), '"';
	if(isset($_GET['month'])) { if(intval($_GET['month']) == $i+1) { echo ' selected="selected"'; } } else { if(date("m", mktime(0,0,0,$date_month-1,$date_day)) == $i+1) { echo ' selected="selected"'; } }
	echo '>', $months[$i], '</option>'; 
} ?>
</select>
<input type="submit" value="View Report" />
</form>
</td><td align="right">
<?php if($cond2) { echo '<a href="wxhistmonth.php?year=', $nexty, '&amp;month=', $nextm, '" title="View next month&#39;s report">'; } ?>
Next Month&gt;&gt;<?php if($cond2) { echo '</a>'; } ?></td>
</tr></table>

<h2>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Report for <?php echo date('F',mktime(0,0,0,intval($mproc))), ' ', $yproc; ?></h2>

<?php 
if(!file_exists($rep)) {
	echo '<p>Report not available for this month</p>';
	if(date('j') == 1 && $mproc == intval(date('m'))) { echo 'First report data for this month available from 00:12 on the 2nd'; }
	if($yproc == 2009 && intval($mproc == 1)) echo ' <br />Records began in Feb 2009<br />';
	echo '<!--';
} ?>

<table class="table1" width="750" cellpadding="3" cellspacing="0">
<tr><td class="td14" colspan="3" align="center"><h3>Temperature</h3></td></tr>
<tr class="table-top">
<td class="td14" width="40%">Measure</td><td class="td14" width="39%">Value (anomaly)</td><td class="td14" width="21%">Day</td>
</tr>
<?php
$tmeasure = array('Lowest Temperature', 'Highest Temperature', 'Mean Temperature', 'Mean Minimum', 'Mean Maximum', 'Highest Minimum', 'Lowest Maximum', 'Coldest Day', 'Warmest Day');
for($i = 0; $i < count($dat[0]); $i++) {
	if($i % 2 == 0) { $style = 'column-light'; } else { $style = 'column-dark'; }
	echo '<tr class="', $style, '"><td width="40%" style="', $hlite, '" class="td14">', $tmeasure[$i], '</td><td width="39%" style="', $hlite, '" class="td14">', 
	conv($dat[0][$i],1,1); if($dat[1][$i] == '') { echo '<span style="color:white">.</span>'; } else { echo ' (', conv2($dat[1][$i],1,0), ')'; }
	echo '</td><td width="21%" style="', $hlite, '" class="td14">'; if($dat[2][$i] == '') { echo '<span style="color:white">.</span>'; } else { echo datefull($dat[2][$i]); } echo '</td></tr>';
} ?>
</table>

<table class="table1" width="750" cellpadding="3" cellspacing="0">
<tr><td class="td12" colspan="3" align="center"><h3>Rainfall*</h3></td></tr>
<tr class="table-top">
<td class="td12" width="40%">Measure</td><td class="td12" width="39%">Value (anomaly)</td><td class="td12" width="21%">Day</td>
</tr>
<?php
$rmeasure = array('Total Rain', 'Wettest Day', 'Rain Days', 'Rain Days >' . conv(1,2,1), 'Mean Rain-day rain', 'Max Rain rate**', 'Wettest hour'); $rfmat = array(2,2,0,0,2,8,2);
for($i = 0; $i < count($dat[3]); $i++) {
	if($i % 2 == 0) { $style = 'column-light'; } else { $style = 'column-dark'; }
	echo '<tr class="', $style, '"><td width="40%" style="', $hlite, '" class="td12">', $rmeasure[$i], '</td><td width="39%" style="', $hlite, '" class="td12">', 
	conv($dat[3][$i],$rfmat[$i],1); if($dat[4][$i] == '') { echo '<span style="color:white">.</span>'; } else { echo ' (', $dat[4][$i], ')'; }
	echo '</td><td width="21%" style="', $hlite, '" class="td12">'; if($dat[5][$i] == '') { echo '<span style="color:white">.</span>'; } else { echo datefull($dat[5][$i]); } echo '</td></tr>';
} ?>
</table>

<table class="table1" width="750" cellpadding="3" cellspacing="0">
<tr><td class="td13" colspan="3" align="center"><h3>Wind Speed</h3></td></tr>
<tr class="table-top">
<td class="td13" width="40%">Measure</td><td class="td13" width="39%">Value (anomaly)</td><td class="td13" width="21%">Day</td>
</tr>
<?php
$wmeasure = array('Mean Wind speed', 'Maximum Gust', 'Maximum Speed', 'Calmest Day', 'Windiest Day', 'Windy Days (>'. conv3(25,4,1). ' gusts)', 'Mean Direction');
 $wfmat = array(4,4,4,4,4,0,0);
for($i = 0; $i < count($dat[6]); $i++) {
	if($i % 2 == 0) { $style = 'column-light'; } else { $style = 'column-dark'; }
	echo '<tr class="', $style, '"><td width="40%" style="', $hlite, '" class="td13">', $wmeasure[$i], '</td><td width="39%" style="', $hlite, '" class="td13">'; 
	if($i<6) { echo conv($dat[6][$i],$wfmat[$i],1); } else { echo $stbfix, '<abbr title="Click to reveal breakdown" onclick="wdirfreq()">', $dat[6][6], '</abbr>', $enbfix; }
	if($dat[7][$i] == '') { echo '<span style="color:white">.</span>'; } else { echo ' (', conv2($dat[7][$i],4,0), ')'; } 
	echo '</td><td width="21%" style="', $hlite, '" class="td13">'; if($dat[8][$i] == '') { echo '<span style="color:white">.</span>'; } else { echo datefull($dat[8][$i]); } echo '</td></tr>';
} ?>
</table>

<table class="table1" width="750" cellpadding="3" cellspacing="0">
<tr><td class="td10" colspan="3" align="center"><h3>Relative Humidity</h3></td></tr>
<tr class="table-top">
<td class="td10" width="40%">Measure</td><td class="td10" width="39%">Value</td><td class="td10" width="21%">Day</td>
</tr>
<?php
$hmeasure = array('Lowest RH', 'Highest RH', 'Mean RH', 'Mean Minimum', 'Mean Maximum', 'Highest Minimum', 'Lowest Maximum', 'Lowest Mean', 'Highest Mean');
for($i = 0; $i < count($dat[9]); $i++) {
	if($i % 2 == 0) { $style = 'column-light'; } else { $style = 'column-dark'; }
	echo '<tr class="', $style, '"><td width="40%" style="', $hlite, '" class="td10">', $hmeasure[$i], '</td><td width="39%" style="', $hlite, '" class="td10">', 
	conv($dat[9][$i],0,0), '%'; if($dat[10][$i] == '') { echo '<span style="color:white">.</span>'; } else { echo ' (', conv2($dat[10][$i],1,0), ')'; }
	echo '</td><td width="21%" style="', $hlite, '" class="td10">'; if($dat[11][$i] == '') { echo '<span style="color:white">.</span>'; } else { echo datefull($dat[11][$i]); } echo '</td></tr>';
} ?>
</table>

<table class="table1" width="750" cellpadding="3" cellspacing="0">
<tr><td class="td10" colspan="3" align="center"><h3>Dew Point</h3></td></tr>
<tr class="table-top">
<td class="td10" width="40%">Measure</td><td class="td10" width="39%">Value</td><td class="td10" width="21%">Day</td>
</tr>
<?php
$dmeasure = array('Lowest Dew Point', 'Highest Dew Point', 'Mean Dew Point', 'Mean Minimum', 'Mean Maximum', 'Highest Minimum', 'Lowest Maximum', 'Lowest Mean', 'Highest Mean');
for($i = 0; $i < count($dat[12]); $i++) {
	if($i % 2 == 0) { $style = 'column-light'; } else { $style = 'column-dark'; }
	echo '<tr class="', $style, '"><td width="40%" style="', $hlite, '" class="td10">', $dmeasure[$i], '</td><td width="39%" style="', $hlite, '" class="td10">', 
	conv($dat[12][$i],1,1); if($dat[13][$i] == '') { echo '<span style="color:white">.</span>'; } else { echo ' (', conv2($dat[13][$i],1,0), ')'; }
	echo '</td><td width="21%" style="', $hlite, '" class="td10">'; if($dat[14][$i] == '') { echo '<span style="color:white">.</span>'; } else { echo datefull($dat[14][$i]); } echo '</td></tr>';
} ?>
</table>

<table class="table1" width="750" cellpadding="3" cellspacing="0">
<tr><td class="td4" colspan="3" align="center"><h3>Pressure</h3></td></tr>
<tr class="table-top">
<td class="td4" width="40%">Measure</td><td class="td4" width="39%">Value</td><td class="td4" width="21%">Day</td>
</tr>
<?php
$pmeasure = array('Lowest Pressure', 'Highest Pressure', 'Mean Pressure');
for($i = 0; $i < count($dat[15]); $i++) {
	if($i % 2 == 0) { $style = 'column-light'; } else { $style = 'column-dark'; }
	echo '<tr class="', $style, '"><td width="40%" style="', $hlite, '" class="td4">', $pmeasure[$i], '</td><td width="39%" style="', $hlite, '" class="td4">', 
	conv($dat[15][$i],3,1); if($dat[16][$i] == '') { echo '<span style="color:white">.</span>'; } else { echo ' (', conv2($dat[16][$i],3,0), ')'; }
	echo '</td><td width="21%" style="', $hlite, '" class="td4">'; if($dat[17][$i] == '') { echo '<span style="color:white">.</span>'; } else { echo datefull($dat[17][$i]); } echo '</td></tr>';
} ?>
</table>

<p><b>*: </b>Includes snowfall (if recorded), which is manually entered so may take a few days to appear for the current month. NB that rain rate and max in 1hr are incalculable for snow events.
<br /><b>**: </b>Rain rates are manually checked and readjusted from software-given values (within a week or so), so may differ from those quoted during current rain events.</p>

<br /><hr /><br />
<a name="summary"></a>
NB: Everything below here is currently in development
<table width="1000">
<tr>
<td align="left">
<?php if($cond1) { echo '<a href="wxhistmonth.php?year=', $prevy, '&amp;month=', $prevm, '#summary" title="View previous month&#39;s report">'; } ?>
&lt;&lt;Previous Month<?php if($cond1) { echo '</a>'; } ?></td>
<td align="center"><form method="get" action="">
<select name="year">
<?php 
for($i = 2009; $i <= $date_year; $i++) {
	echo '<option value="', $i, '"';
	if(isset($_GET['year'])) { if(intval($_GET['year']) == $i) { echo ' selected="selected"'; } } else { if(date("Y", mktime(0,0,0,$date_month-1,$date_day-1,$date_year)) == $i) { echo ' selected="selected"'; } }
	echo '>', $i, '</option>'; 
} ?>
</select>
<select name="month">
<?php $months = array('Jan', 'Feb', 'March', 'April', 'May', 'June', 'July', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
for($i = 0; $i < 12; $i++) { 
	echo '<option value="', sprintf('%1$02d',$i+1), '"';
	if(isset($_GET['month'])) { if(intval($_GET['month']) == $i+1) { echo ' selected="selected"'; } } else { if(date("m", mktime(0,0,0,$date_month-1,$date_day-1)) == $i+1) { echo ' selected="selected"'; } }
	echo '>', $months[$i], '</option>'; 
} ?>
</select>
<input type="submit" value="View Report" />
</form>
</td><td align="right">
<?php if($cond2) { echo '<a href="wxhistmonth.php?year=', $nexty, '&amp;month=', $nextm, '#summary" title="View next month&#39;s report">'; } ?>
Next Month&gt;&gt;<?php if($cond2) { echo '</a>'; } ?></td>
</tr></table>

<table class="table1" width="1000" cellpadding="3" cellspacing="0">
<tr><td class="td4" colspan="15" align="center"><h3>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Summary</h3></td></tr>
<tr class="table-top">
<td class="td4" rowspan="2" style="border-right: 2px solid gray; border-bottom: 2px solid gray" width="3%">Day</td>
<td class="td4" colspan="3" width="20%">Temperature</td>
<td class="td4" colspan="1" width="7%">Rainfall</td>
<td class="td4" colspan="2" width="15%">Wind Speed</td>
<td class="td4" colspan="3" width="20%">Rel Humidity</td>
<td class="td4" colspan="3" width="20%">Dew Point</td>
<td class="td4" colspan="2" width="12%">Pressure</td>
</tr>
<tr class="table-top"><?php
$measure = array('Min', 'Max', 'Avg', 'Total', 'Avg', 'Gust', 'Min', 'Max', 'Avg', 'Min', 'Max', 'Avg', 'Min', 'Max');
$tdclass = array(14,14,14,12,13,13,10,10,10,10,10,10,4,4);
$type = array(1,1,1,2,4,4,9,9,9,1,1,1,3,3);
$type2 = array(1,1,1,2,4,4,9,9,9,1,1,1,3,3);
for($i = 0; $i < count($dat[19]); $i++) { echo '<td class="td4" width="7%" style="border-bottom: 2px solid gray">', $measure[$i],'</td>'; }
?></tr>
<?php
if(mktime(0,0,0,$mproc,$dim,$yproc) > mktime(0,0,0,$date_month,$date_day-1,$date_year)) { $drun = date('j')-1; } else { $drun = $dim; }
for($d = 1; $d <= $drun; $d++) {
	if($d % 2 == 0) { $style = 'column-light'; } else { $style = 'column-dark'; }
	echo '<tr class="', $style, '">';
	echo '<td class="td4" style="border-right: 2px solid gray" width="3%">', $d,'</td>';
	for($i = 0; $i < count($dat[19]); $i++) {
		echo '<td width="7%" style="', $hlite, '" class="td', $tdclass[$i],'">';
		//if($dat[21][$i][$m] != '') { echo '<acronym style="border-bottom-width: 0" title="', date('jS', mktime(1,1,1,1,$dat[21][$i][$m])), '">'; }
		if($i == 3 && $dat[19][$i][$d] == 0) { echo '<span style="color: gray">'; }
		echo conv($dat[19][$i][$d],$type[$i],0);
		if($i == 3 && $dat[19][$i][$d] == 0) { echo '</span>'; }
		//if($dat[21][$i][$m] != '') { echo '</acronym>'; }
		//if($dat[20][$i][$m] == '') { echo '<span style="color:white">.</span>'; } else { echo '<br />(', conv2($dat[20][$i][$m],$type2[$i],0), ')'; }
		echo '</td>'; 
	}
	echo '</tr>';
} ?>
</table>
<!-- <span style="font-style: italic">Hover over an extreme value to get the time it was recorded</span> -->

<?php if(!file_exists($rep)) { echo '-->'; } ?>
<?
$wtr_img = 'windtempraintrend' . intval($mproc) . $yproc;
if (is_file("$wxh_graphs_path$wtr_img.gif")) {
	echo '<br /><b>Monthly trends</b><br /><br /> ', '<img src="', $wtr_img, '.gif" title="Wind, temp, rain trends"></img>';
} 
else { echo "<br />Graph for this month not available ($wtr_img.gif)"; } 
?>
</div>

<?php if($cond3) { echo '<a href="wxhistday.php?month=',$mproc,'&amp;year=',$yproc,'" title="Daily reports for ', monthfull($mproc), ' ', $yproc,'">View daily breakdown</a>'; } ?>

</div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>

	<script type="text/javascript">
	function wdirfreq() {
	alert("<?php echo $dat[18]; ?>");
	}
	</script>
</body>
</html>

<?php
function mrepdata($mon, $yea) {
	global $myavjanrain,$myavfebrain,$myavmarrain,$myavaprrain,$myavmayrain,$myavjunrain,$myavjulrain,$myavaugrain,$myavseprain,$myavoctrain,$myavnovrain,$myavdecrain;
	global $mrecordrainrateday, $mrecordrainrateperhr;
	$report = date("F", mktime(0,0,0,$mon)).date("Y", mktime(0,0,0,1,1,$yea)).'.htm';
	$data = file($report);
	$end = 1200; $rdays = 0; $r1days = 0; $wave10 = 0; $sndays = 0;
	for ($i = 1; $i < $end; $i++) {
		if(strpos($data[$i],"remes for the month") > 0) { $end = $i; }
		if(strpos($data[$i],"remes for day") > 0) { $daya = explode(" ", $data[$i]); $a = intval(substr($daya[7],1,2)); }
		if(strpos($data[$i],"aximum hum") > 0) { $hmaxa = explode(" ", $data[$i]); $hmaxv[$a] = intval($hmaxa[12]); $hmaxt[$a] = trim($hmaxa[18]); }
		if(strpos($data[$i],"inimum hum") > 0) { $hmina = explode(" ", $data[$i]); $hminv[$a] = intval($hmina[11]); $hmint[$a] = trim($hmina[17]); }
		if(strpos($data[$i],"aximum dew") > 0) { $dmaxa = explode(" ", $data[$i]); $dmaxv[$a] = floatval($dmaxa[11]); }
		if(strpos($data[$i],"inimum dew") > 0) { $dmina = explode(" ", $data[$i]); $dminv[$a] = floatval($dmina[11]); }
		if(strpos($data[$i],"aximum pre") > 0) { $pmaxa = explode(" ", $data[$i]); $pmaxv[$a] = floatval($pmaxa[11]); }
		if(strpos($data[$i],"inimum pre") > 0) { $pmina = explode(" ", $data[$i]); $pminv[$a] = floatval($pmina[11]); }
		if(strpos($data[$i],"aximum tem") > 0) { $tmaxa = explode(" ", $data[$i]); $tmaxv[$a] = floatval($tmaxa[9]); $tmaxt[$a] = trim($tmaxa[15]); }
		if(strpos($data[$i],"inimum tem") > 0) { $tmina = explode(" ", $data[$i]); $tminv[$a] = floatval($tmina[9]); $tmint[$a] = trim($tmina[15]); }
		if(strpos($data[$i],"verage tem") > 0) { $tavea = explode(" ", $data[$i]); $tavev[$a] = floatval($tavea[8]); }
		if(strpos($data[$i],"verage dew") > 0) { $davea = explode(" ", $data[$i]); $davev[$a] = floatval($davea[11]); }
		if(strpos($data[$i],"verage win") > 0) { $wavea = explode(" ", $data[$i]); $wavev[$a] = floatval($wavea[10]); }
		if(strpos($data[$i],"aximum gus") > 0) { $gusta = explode(" ", $data[$i]); $gustv[$a] = floatval($gusta[10]); if($gustv[$a] > 25) { $wave10 = $wave10 + 1; } }
		if(strpos($data[$i],"aximum win") > 0) { $speda = explode(" ", $data[$i]); $spedv[$a] = floatval($speda[10]); }	
		if(strpos($data[$i],"verage hum") > 0) { $havea = explode(" ", $data[$i]); $havev[$a] = intval($havea[11]); }
		if(strpos($data[$i],"verage bar") > 0) { $pavea = explode(" ", $data[$i]); $pavev[$a] = intval($pavea[10]); }
		if(strpos($data[$i],"all for da") > 0) { $raina = explode(" ", $data[$i]); $rainv[$a] = $raina[12];
		if($rainv[$a] > 0) { $rdays = $rdays + 1; } if($rainv[$a] > 1) { $r1days = $r1days + 1; } if(strpos($rainv[$a],'*')) { $sndays = $sndays + 1; }
		}
		if(strpos($data[$i]," direction") > 0) { $wdira = explode(" ", $data[$i]); if(intval($wdira[10]) == 0):	$wdirv[$a] = degname(intval($wdira[11]));
		$wdirv2[$a] = intval($wdira[11]); else: $wdirv[$a] = degname(intval($wdira[10])); $wdirv2[$a] = intval($wdira[10]); endif;
		}
	}
	for ($j = $end; $j < $end+20; $j++) {
		if(strpos($data[$j]," direction") > 0) { $wmdira = explode(" ", $data[$j]); if(intval($wmdira[10]) == 0) { $wmdirv = degname(intval($wmdira[11]));
		$wmdirv2 = intval($wmdira[11]); } else { $wmdirv = degname(intval($wmdira[10])); $wmdirv2 = intval($wmdira[10]); } }
	}
	
	$wlabels = array('N','NNE','NE','ENE','E','ESE','SE','SSE','S','SSW','SW','WSW','W','WNW','NW','NNW','N');
	foreach($wdirv as $val) {
		for($k = 0; $k < 16; $k++) { if($val == $wlabels[$k]) { $wdircount[$k] = $wdircount[$k] + 1; } else { $wdircount[$k] = $wdircount[$k] + 0; } }
	}
	$wdirfreq = 'Breakdown:\n';
	for($k = 0; $k < 16; $k++) { $wdirfreq .= $wdircount[$k] . ' ' . $wlabels[$k] . '\n'; }
	
	//Temperature
	$tmin = min($tminv); $tmax = max($tmaxv); $tave = array_sum($tavev)/$a; $tminave = array_sum($tminv)/$a; $tmaxave = array_sum($tmaxv)/$a;
	$tminmax = max($tminv); $tmaxmin = min($tmaxv); $tavemin = min($tavev); $tavemax = max($tavev);
	$tminD = array_search($tmin,$tminv); $tmaxD = array_search($tmax,$tmaxv); $tminmaxD = array_search($tminmax,$tminv); $tmaxminD = array_search($tmaxmin,$tmaxv);
	$taveminD = array_search($tavemin,$tavev); $tavemaxD = array_search($tavemax,$tavev); $tanom = array(4.7, 4.9, 7, 8.9, 12.5, 15.65, 17.9, 17.7, 14.9, 11.7, 7.6, 5.5);
	$tanoml = array(2.4,2.2,3.6,5.0,8.3,11.1,13.4,13.2,11.1,8.8,5.0,3.2); $tanomh = array(7.0,7.6,10.4,12.8,16.7,20.2,22.4,22.2,18.7,14.6,10.2,7.8);
	$taveA = $tave - $tanom[intval($mon)-1]; $tminaveA = $tminave - $tanoml[intval($mon)-1]; $tmaxaveA = $tmaxave - $tanomh[intval($mon)-1];
	//Rain
	$rtot = array_sum($rainv); $rmax = max($rainv); if($rdays > 0) { $rdmean = $rtot/$rdays; } else { $rdmean = 'n/a'; } $r1danom = array(11,9,10,10,9,9,7,8,9,10,10,11);
	$rainav = array($myavjanrain,$myavfebrain,$myavmarrain,$myavaprrain,$myavmayrain,$myavjunrain,$myavjulrain,$myavaugrain,$myavseprain,$myavoctrain,$myavnovrain,$myavdecrain);  
	$rtotA = round(100*$rtot/$rainav[intval($mon)-1],0) . '%'; $r1daysA = conv2($r1days - $r1danom[intval($mon)-1],0,0); $rmaxD = array_search($rmax,$rainv);
	$moredata = file('mrep.csv'); $st = intval((mktime(12,0,0,$mon,1,$yea)-mktime(0,0,0,2,1,2009))/(24*3600))+1; $en = $st + date('t', mktime(0,0,0,$mon,1,$yea));
	for($l = $st; $l < $en; $l++) {
		$mhrndata[$l] = explode(',',$moredata[$l]);
		if(floatval($mhrndata[$l][5]) > $rhmax) { $rhmax = floatval($mhrndata[$l][5]); $rhmaxD = $mhrndata[$l][0]; }
		if(floatval($mhrndata[$l][6]) > $rrmax) { $rrmax = floatval($mhrndata[$l][6]); $rrmaxD = $mhrndata[$l][0]; }
	}
	// if(mktime(0,0,0,$mon,1,$yea) == mktime(0,0,0,date('m'),1,date('Y'))) { $rrmax = $mrecordrainrateperhr; $rrmaxD = $mrecordrainrateday; }
	//Wind
	$wave = array_sum($wavev)/$a; $gmax = max($gustv); $amax = max($spedv); $wmin = min($wavev); $wmax = max($wavev);
	$wanom = array(5.2,5.1,5.2,4.9,4.7,4.4,4.3,4.0,3.9,4.1,4.6,5.1); $waveA = $wave - $wanom[intval($mon)-1]; $wdir = $wmdirv2 . '&deg; (' . $wmdirv . ')';
	$gmaxD = array_search($gmax,$gustv); $amaxD = array_search($amax,$spedv); $wminD = array_search($wmin,$wavev); $wmaxD = array_search($wmax,$wavev);
	//Relative Humidity
	$hmin = min($hminv); $hmax = max($hmaxv); $have = array_sum($havev)/$a; $hminave = array_sum($hminv)/$a; $hmaxave = array_sum($hmaxv)/$a;
	$hminmax = max($hminv); $hmaxmin = min($hmaxv); $havemin = min($havev); $havemax = max($havev);
	$hminD = array_search($hmin,$hminv); $hmaxD = array_search($hmax,$hmaxv); $hminmaxD = array_search($hminmax,$hminv); $hmaxminD = array_search($hmaxmin,$hmaxv);
	$haveminD = array_search($havemin,$havev); $havemaxD = array_search($havemax,$havev);
	//Dew Point
	$dmin = min($dminv); $dmax = max($dmaxv); $dave = array_sum($davev)/$a; $dminave = array_sum($dminv)/$a; $dmaxave = array_sum($dmaxv)/$a;
	$dminmax = max($dminv); $dmaxmin = min($dmaxv); $davemin = min($davev); $davemax = max($davev);
	$dminD = array_search($dmin,$dminv); $dmaxD = array_search($dmax,$dmaxv); $dminmaxD = array_search($dminmax,$dminv); $dmaxminD = array_search($dmaxmin,$dmaxv);
	$daveminD = array_search($davemin,$davev); $davemaxD = array_search($davemax,$davev);
	//Pressure
	$pmin = min($pminv); $pmax = max($pmaxv); $pave = array_sum($pavev)/$a; $pminD = array_search($pmin,$pminv); $pmaxD = array_search($pmax,$pmaxv);
	
	return array(
			array($tmin, $tmax, $tave, $tminave, $tmaxave, $tminmax, $tmaxmin, $tavemin, $tavemax),
			array('', '', $taveA, $tminaveA, $tmaxaveA, '', '', '', ''),
			array($tminD, $tmaxD, '', '', '', $tminmaxD, $tmaxminD, $taveminD, $tavemaxD),
			
			array($rtot, $rmax, $rdays, $r1days, $rdmean),
			array($rtotA, '', '', $r1daysA, ''),
			array('', $rmaxD, '', '', ''),
			
			array($wave, $gmax, $amax, $wmin, $wmax, $wave10, $wdir),
			array($waveA,'', '', '', '', '', ''),
			array('', $gmaxD, $amaxD, $wminD, $wmaxD, '', ''),
			
			array($hmin, $hmax, $have, $hminave, $hmaxave, $hminmax, $hmaxmin, $havemin, $havemax),
			array('', '', '', '', '', '', '', '', ''),
			array($hminD, $hmaxD, '', '', '', $hminmaxD, $hmaxminD, $haveminD, $havemaxD),
			
			array($dmin, $dmax, $dave, $dminave, $dmaxave, $dminmax, $dmaxmin, $davemin, $davemax),
			array('', '', '', '', '', '', '', '', ''),
			array($dminD, $dmaxD, '', '', '', $dminmaxD, $dmaxminD, $daveminD, $davemaxD),
			
			array($pmin, $pmax, $pave),
			array('', '', ''),
			array($pminD, $pmaxD, ''),
			
			$wdirfreq,
			
			array($tminv, $tmaxv, $tavev, $rainv, $wavev, $gustv, $hminv, $hmaxv, $havev, $dminv, $dmaxv, $davev, $pminv, $pmaxv),
			array()
			);
}
?>