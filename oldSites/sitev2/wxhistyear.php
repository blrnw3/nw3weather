<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("phptags.php");
	$file = 88; 
	if(isset($_GET['year'])) { $yproc = $_GET['year']; } else { $yproc = date('Y',mktime(0,0,0,date('m'),date('j')-1,date('Y')-1)); }	
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - Annual Reports</title>
	<meta name="description" content="Old v2 - Detailed historical annual weather summary report for <?php echo date('Y',mktime(0,0,0,1,1,$yproc)); ?> from NW3 weather" />

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
$rep = date("F", mktime(0,0,0,1)).date("Y", mktime(0,0,0,1,1,$yproc)).'.htm';
if($yproc == 2009) { $rep = date("F", mktime(0,0,0,2)).date("Y", mktime(0,0,0,1,1,$yproc)).'.htm'; }
if(file_exists($rep)) { $dat = yrepdata($yproc); }
$nexty = $yproc+1; $prevy = $yproc-1;
?>

<div id="main-copy">

<h1>Annual Weather Reports</h1>

<div align="center">

<?php
if($yproc == 2009) echo '<b>Special note</b>: Data is suspect for this year as no automatic data was recorded in January';
if($yproc == 2010) echo '<b>Special note</b>: Wind data not valid for this year (suspended 17th April - 28th July)'; 
if($yproc < 2009) { echo '<br /><b>First report available is 2009</b>'; }
$cond1 = $yproc > 2009;
$cond2 = $yproc < date('Y');
?>

<table width="750">
<tr>
<td align="left">
<?php if($cond1) { echo '<a href="wxhistyear.php?year=', $prevy, '" title="View previous year&#39;s report">'; } ?>
&lt;&lt;Previous Year<?php if($cond1) { echo '</a>'; } ?></td>
<td align="center"><b>Select Year:</b>&nbsp;<form method="get" action="">
<select name="year" onchange='this.form.submit()'>
<?php 
for($i = 2009; $i <= $date_year; $i++) {
	echo '<option value="', $i, '"';
	if(isset($_GET['year'])) { if(intval($_GET['year']) == $i) { echo ' selected="selected"'; } } else { if(date("Y", mktime(0,0,0,$date_month,$date_day-1,$date_year-1)) == $i) { echo ' selected="selected"'; } }
	echo '>', $i, '</option>'; 
} ?>
</select>
</form>
</td><td align="right">
<?php if($cond2) { echo '<a href="wxhistyear.php?year=', $nexty, '" title="View next year&#39;s report">'; } ?>
Next Year&gt;&gt;<?php if($cond2) { echo '</a>'; } ?></td>
</tr></table>

<h2>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Report for <?php echo $yproc; ?></h2>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="#summary">Jump to monthly breakdown</a>
<?php 
if(!file_exists($rep)) {
	echo '<p>Report not available for this year</p>';
	if(date('j') == 1 && $yproc == date('Y') && date('n') == 1) { echo 'First report data for this year available from 00:12 on the 2nd'; }
	//if($yproc == 2009 ) echo ' <br />Data collection began in Feb 2009<br />';
	echo '<!--';
} ?>

<table class="table1" width="750" cellpadding="3" cellspacing="0">
<tr><td class="td14" colspan="3" align="center"><h3>Temperature</h3></td></tr>
<tr class="table-top">
<td class="td14" width="40%">Measure</td><td class="td14" width="39%">Value (anomaly)</td><td class="td14" width="21%">Day</td>
</tr>
<?php
$tmeasure = array('Lowest Temperature', 'Highest Temperature', 'Mean Temperature', 'Mean Minimum', 'Mean Maximum', 'Highest Minimum', 'Lowest Maximum', 'Coldest Day', 'Warmest Day', 'Coldest Month', 'Warmest Month', 'Most -ve anom Month', 'Most +ve anom Month');
for($i = 0; $i < count($dat[0]); $i++) {
	if($i % 2 == 0) { $style = 'column-light'; } else { $style = 'column-dark'; }
	if($i == 9) { $hlite = 'border-top: 2px solid gray'; } else { $hlite = ''; }
	echo '<tr class="', $style, '"><td width="40%" style="', $hlite, '" class="td14">', $tmeasure[$i], '</td><td width="39%" style="', $hlite, '" class="td14">', 
	conv($dat[0][$i],1,1); if($dat[1][$i] == '') { echo '<span style="color:white">.</span>'; } else { echo ' (', conv2($dat[1][$i],1,0), ')'; }
	echo '</td><td width="21%" style="', $hlite, '" class="td14">'; if($dat[2][$i] == '') { echo '<span style="color:white">.</span>'; } else { echo $dat[2][$i]; } echo '</td></tr>';
} ?>
</table>

<table class="table1" width="750" cellpadding="3" cellspacing="0">
<tr><td class="td12" colspan="3" align="center"><h3>Rainfall*</h3></td></tr>
<tr class="table-top">
<td class="td12" width="40%">Measure</td><td class="td12" width="39%">Value (anomaly)</td><td class="td12" width="21%">Day</td>
</tr>
<?php
$rmeasure = array('Total Rain', 'Wettest Day', 'Rain Days', 'Rain Days >' . conv(1,2,1), 'Mean Rain-day rain', 'Max Rain rate**', 'Wettest hour', 'Wettest Month', 'Driest Month'); $rfmat = array(2,2,0,0,2,8,2,2,2);
for($i = 0; $i < count($dat[3]); $i++) {
	if($i % 2 == 0) { $style = 'column-light'; } else { $style = 'column-dark'; }
	if($i == 7) { $hlite = 'border-top: 2px solid gray'; } else { $hlite = ''; }
	echo '<tr class="', $style, '"><td width="40%" style="', $hlite, '" class="td12">', $rmeasure[$i], '</td><td width="39%" style="', $hlite, '" class="td12">', 
	conv($dat[3][$i],$rfmat[$i],1); if($dat[4][$i] == '') { echo '<span style="color:white">.</span>'; } else { echo ' (', $dat[4][$i], ')'; }
	echo '</td><td width="21%" style="', $hlite, '" class="td12">'; if($dat[5][$i] == '') { echo '<span style="color:white">.</span>'; } else { echo $dat[5][$i]; } echo '</td></tr>';
} ?>
</table>

<table class="table1" width="750" cellpadding="3" cellspacing="0">
<tr><td class="td13" colspan="3" align="center"><h3>Wind Speed</h3></td></tr>
<tr class="table-top">
<td class="td13" width="40%">Measure</td><td class="td13" width="39%">Value (anomaly)</td><td class="td13" width="21%">Day</td>
</tr>
<?php
$wmeasure = array('Mean Wind speed', 'Maximum Gust', 'Maximum Speed', 'Calmest Day', 'Windiest Day', 'Windy Days (>'. conv3(25,4,1). ' gusts)', 'Mode Direction', 'Calmest Month', 'Windiest Month');
 $wfmat = array(4,4,4,4,4,0,0,4,4);
for($i = 0; $i < count($dat[6]); $i++) {
	if($i % 2 == 0) { $style = 'column-light'; } else { $style = 'column-dark'; }
	if($i == 7) { $hlite = 'border-top: 2px solid gray'; } else { $hlite = ''; }
	echo '<tr class="', $style, '"><td width="40%" style="', $hlite, '" class="td13">', $wmeasure[$i], '</td><td width="39%" style="', $hlite, '" class="td13">'; 
	if($i!=6) { echo conv($dat[6][$i],$wfmat[$i],1); } else { echo $stbfix, '<abbr title="Click to reveal breakdown" onclick="Ywdirfreq()">', $dat[6][6], '</abbr>', $enbfix; }
	if($dat[7][$i] == '') { echo '<span style="color:white">.</span>'; } else { echo ' (', conv2($dat[7][$i],4,0), ')'; } 
	echo '</td><td width="21%" style="', $hlite, '" class="td13">'; if($dat[8][$i] == '') { echo '<span style="color:white">.</span>'; } else { echo $dat[8][$i]; } echo '</td></tr>';
} ?>
</table>

<table class="table1" width="750" cellpadding="3" cellspacing="0">
<tr><td class="td10" colspan="3" align="center"><h3>Relative Humidity</h3></td></tr>
<tr class="table-top">
<td class="td10" width="40%">Measure</td><td class="td10" width="39%">Value</td><td class="td10" width="21%">Day</td>
</tr>
<?php
$hmeasure = array('Lowest RH', 'Highest RH', 'Mean RH', 'Mean Minimum', 'Mean Maximum', 'Highest Minimum', 'Lowest Maximum', 'Lowest Mean', 'Highest Mean', 'Least Humid Month', 'Most Humid Month');
for($i = 0; $i < count($dat[9]); $i++) {
	if($i % 2 == 0) { $style = 'column-light'; } else { $style = 'column-dark'; }
	if($i == 9) { $hlite = 'border-top: 2px solid gray'; } else { $hlite = ''; }
	echo '<tr class="', $style, '"><td width="40%" style="', $hlite, '" class="td10">', $hmeasure[$i], '</td><td width="39%" style="', $hlite, '" class="td10">', 
	conv($dat[9][$i],0,0), '%'; if($dat[10][$i] == '') { echo '<span style="color:white">.</span>'; } else { echo ' (', conv2($dat[10][$i],1,0), ')'; }
	echo '</td><td width="21%" style="', $hlite, '" class="td10">'; if($dat[11][$i] == '') { echo '<span style="color:white">.</span>'; } else { echo $dat[11][$i]; } echo '</td></tr>';
} ?>
</table>

<table class="table1" width="750" cellpadding="3" cellspacing="0">
<tr><td class="td10" colspan="3" align="center"><h3>Dew Point</h3></td></tr>
<tr class="table-top">
<td class="td10" width="40%">Measure</td><td class="td10" width="39%">Value</td><td class="td10" width="21%">Day</td>
</tr>
<?php
$dmeasure = array('Lowest Dew Point', 'Highest Dew Point', 'Mean Dew Point', 'Mean Minimum', 'Mean Maximum', 'Highest Minimum', 'Lowest Maximum', 'Lowest Mean', 'Highest Mean', 'Least Humid Month', 'Most Humid Month');
for($i = 0; $i < count($dat[12]); $i++) {
	if($i % 2 == 0) { $style = 'column-light'; } else { $style = 'column-dark'; }
	if($i == 9) { $hlite = 'border-top: 2px solid gray'; } else { $hlite = ''; }
	echo '<tr class="', $style, '"><td width="40%" style="', $hlite, '" class="td10">', $dmeasure[$i], '</td><td width="39%" style="', $hlite, '" class="td10">', 
	conv($dat[12][$i],1,1); if($dat[13][$i] == '') { echo '<span style="color:white">.</span>'; } else { echo ' (', conv2($dat[13][$i],1,0), ')'; }
	echo '</td><td width="21%" style="', $hlite, '" class="td10">'; if($dat[14][$i] == '') { echo '<span style="color:white">.</span>'; } else { echo $dat[14][$i]; } echo '</td></tr>';
} ?>
</table>

<table class="table1" width="750" cellpadding="3" cellspacing="0">
<tr><td class="td4" colspan="3" align="center"><h3>Pressure</h3></td></tr>
<tr class="table-top">
<td class="td4" width="40%">Measure</td><td class="td4" width="39%">Value</td><td class="td4" width="21%">Day</td>
</tr>
<?php
$pmeasure = array('Lowest Pressure', 'Highest Pressure', 'Mean Pressure', 'Lowest month average SLP', 'Highest month average SLP');
for($i = 0; $i < count($dat[15]); $i++) {
	if($i % 2 == 0) { $style = 'column-light'; } else { $style = 'column-dark'; }
	if($i == 3) { $hlite = 'border-top: 2px solid gray'; } else { $hlite = ''; }
	echo '<tr class="', $style, '"><td width="40%" style="', $hlite, '" class="td4">', $pmeasure[$i], '</td><td width="39%" style="', $hlite, '" class="td4">', 
	conv($dat[15][$i],3,1); if($dat[16][$i] == '') { echo '<span style="color:white">.</span>'; } else { echo ' (', conv2($dat[16][$i],3,0), ')'; }
	echo '</td><td width="21%" style="', $hlite, '" class="td4">'; if($dat[17][$i] == '') { echo '<span style="color:white">.</span>'; } else { echo $dat[17][$i]; } echo '</td></tr>';
} ?>
</table>

<p><b>*: </b>Includes snowfall (if recorded), which is manually entered so may take a few days to appear for the current year. NB that rain rate and max in 1hr are incalculable for snow events.
<br /><b>**: </b>Rain rates are manually checked and readjusted from software-given values (within a week or so), so may differ from those quoted during current rain events.</p>

<br /><hr /><br />
<a name="summary"></a>

<table width="1120">
<tr>
<td align="left">
<?php if($cond1) { echo '<a href="wxhistyear.php?year=', $prevy, '#summary" title="View previous year&#39;s report">'; } ?>
&lt;&lt;Previous Year<?php if($cond1) { echo '</a>'; } ?></td>
<td align="center"><b>Select Year:</b>&nbsp;<form method="get" action="">
<select name="year" onchange='this.form.submit()'>
<?php 
for($i = 2009; $i <= $date_year; $i++) {
	echo '<option value="', $i, '"';
	if(isset($_GET['year'])) { if(intval($_GET['year']) == $i) { echo ' selected="selected"'; } } else { if(date("Y", mktime(0,0,0,$date_month,$date_day-1,$date_year-1)) == $i) { echo ' selected="selected"'; } }
	echo '>', $i, '</option>'; 
} ?>
</select>
</form>
</td><td align="right">
<?php if($cond2) { echo '<a href="wxhistyear.php?year=', $nexty, '#summary" title="View next year&#39;s report">'; } ?>
Next Year&gt;&gt;<?php if($cond2) { echo '</a>'; } ?></td>
</tr></table>

<table class="table1" width="1120" cellpadding="7" cellspacing="1">
<tr><td class="td4" colspan="13" align="center"><h3>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Summary</h3></td></tr>
<tr class="table-top">
<td class="td4" style="border-right: 2px solid gray" width="11%">Measure</td>
<?php for($i = 1; $i <= 12; $i++) {
		echo '<td class="td4" width="7%"><a title="View monthly detail" style="font-weight:normal; color:black" href="wxhistmonth.php?year=', $yproc, '&amp;month=', $i, '">', substr(month($i),0,3), '</a></td>';
	} ?>
</tr>
<?php
$measure = array('Lowest Temp', 'Highest Temp', 'Mean Temp', 'Mean Minimum', 'Mean Maximum', 'Total Rain', 'Wettest Day', 'Rain Days',
	'Mean Wind spd', 'Maximum Gust', 'Lowest RH', 'Highest RH', 'Mean RH','Lowest Dew Pt', 'Highest Dew Pt', 'Mean Dew Point', 'Lowest Pressure', 'Highest Pressure', 'Mean Pressure');
$tdclass = array(14,14,14,14,14,12,12,12,13,13,10,10,10,10,10,10,4,4,4);
$type = array(1,1,1,1,1,2,2,0,4,4,9,9,9,1,1,1,3,3,3);
$type2 = array(1,1,1,1,1,6,2,2,4,4,9,9,9,1,1,1,3,3,3);
for($i = 0; $i < count($dat[19]); $i++) {
	if($i % 2 == 0) { $style = 'column-light'; } else { $style = 'column-dark'; }
	//if($i == 5 || $i == 8 || $i == 10 || $i == 13 || $i == 16) { $hlite = 'border-top: 2px solid black'; } else { $hlite = ''; }
	echo '<tr class="', $style, '"><td width="11%" style="', $hlite, '; border-right: 2px solid gray" class="td', $tdclass[$i],'">', $measure[$i], '</td>';
	for($m=1; $m<=12; $m++) {
		echo '<td width="7%" style="', $hlite, '" class="td', $tdclass[$i],'">';
		if($dat[21][$i][$m] != '') { echo '<acronym style="border-bottom-width: 0" title="', date('jS', mktime(1,1,1,1,$dat[21][$i][$m])), '">'; }
		echo conv($dat[19][$i][$m],$type[$i],1);
		if($dat[21][$i][$m] != '') { echo '</acronym>'; }
		if($dat[20][$i][$m] == '') { echo '<span style="color:white">.</span>'; } else { echo '<br />(', conv2($dat[20][$i][$m],$type2[$i],0), ')'; }
		echo '</td>'; 
	}
	echo '</tr>'; //<td width="21%" style="', $hlite, '" class="td', $tdclass[$i],'">'; if($dat[2][$i] == '') { echo '<span style="color:white">.</span>'; } else { echo $dat[2][$i]; } echo '</td></tr>';
} ?>
</table>
<span style="font-style: italic">Hover over an extreme value to get the date it was recorded</span>
<?php if(!file_exists($rep)) { echo '-->'; } ?>
</div>

</div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>


<script type="text/javascript">
	function Ywdirfreq() {
	alert("<?php echo $dat[18]; ?>");
	}
</script>
</body>
</html>

<?php
function yrepdata($yea) {
	global $myavjanrain,$myavfebrain,$myavmarrain,$myavaprrain,$myavmayrain,$myavjunrain,$myavjulrain,$myavaugrain,$myavseprain,$myavoctrain,$myavnovrain,$myavdecrain;
	global $mrecordrainrateday, $mrecordrainrateperhr;
	$mcnt = 12; if($yea == date('Y')) { $mcnt = date('n'); }
	for($m=1; $m<=12; $m++) {
		//for($b=1;$b<=31;$b++) { $hmaxv[$m][$b] = 0; $hminv[$m][$b] = 0; $tminv[$m][$b] = 0; $tmaxv[$m][$b] = 0; $rainv[$m][$b] = 0; }
		$report = date("F", mktime(1,1,1,$m)).date("Y", mktime(1,1,1,1,1,$yea)).'.htm';
	if(file_exists($report)) {
		$data = file($report);
		$end = 1200;
		for ($i = 1; $i < $end; $i++) {
			if(strpos($data[$i],"remes for the month") > 0) { $end = $i; }
			if(strpos($data[$i],"remes for day") > 0) { $daya = explode(" ", $data[$i]); $a = intval(substr($daya[7],1,2)); }
			if(strpos($data[$i],"aximum hum") > 0) { $hmaxa = explode(" ", $data[$i]); $hmaxv[$m][$a] = intval($hmaxa[12]); $hmaxt[$a] = trim($hmaxa[18]); }
			if(strpos($data[$i],"inimum hum") > 0) { $hmina = explode(" ", $data[$i]); $hminv[$m][$a] = intval($hmina[11]); $hmint[$a] = trim($hmina[17]); }
			if(strpos($data[$i],"aximum dew") > 0) { $dmaxa = explode(" ", $data[$i]); $dmaxv[$m][$a] = floatval($dmaxa[11]); }
			if(strpos($data[$i],"inimum dew") > 0) { $dmina = explode(" ", $data[$i]); $dminv[$m][$a] = floatval($dmina[11]); }
			if(strpos($data[$i],"aximum pre") > 0) { $pmaxa = explode(" ", $data[$i]); $pmaxv[$m][$a] = floatval($pmaxa[11]); }
			if(strpos($data[$i],"inimum pre") > 0) { $pmina = explode(" ", $data[$i]); $pminv[$m][$a] = floatval($pmina[11]); }
			if(strpos($data[$i],"aximum tem") > 0) { $tmaxa = explode(" ", $data[$i]); $tmaxv[$m][$a] = floatval($tmaxa[9]); $tmaxt[$a] = trim($tmaxa[15]); }
			if(strpos($data[$i],"inimum tem") > 0) { $tmina = explode(" ", $data[$i]); $tminv[$m][$a] = floatval($tmina[9]); $tmint[$a] = trim($tmina[15]); }
			if(strpos($data[$i],"verage tem") > 0) { $tavea = explode(" ", $data[$i]); $tavev[$m][$a] = floatval($tavea[8]); }
			if(strpos($data[$i],"verage dew") > 0) { $davea = explode(" ", $data[$i]); $davev[$m][$a] = floatval($davea[11]); }
			if(strpos($data[$i],"verage win") > 0) { $wavea = explode(" ", $data[$i]); $wavev[$m][$a] = floatval($wavea[10]); }
			if(strpos($data[$i],"aximum gus") > 0) { $gusta = explode(" ", $data[$i]); $gustv[$m][$a] = floatval($gusta[10]); if($gustv[$m][$a] > 25) { $wave10[$m] = $wave10[$m] + 1; } }
			if(strpos($data[$i],"aximum win") > 0) { $speda = explode(" ", $data[$i]); $spedv[$m][$a] = floatval($speda[10]); }
			if(strpos($data[$i],"verage hum") > 0) { $havea = explode(" ", $data[$i]); $havev[$m][$a] = intval($havea[11]); }
			if(strpos($data[$i],"verage bar") > 0) { $pavea = explode(" ", $data[$i]); $pavev[$m][$a] = intval($pavea[10]); }
			if(strpos($data[$i],"all for da") > 0) { $raina = explode(" ", $data[$i]); $rainv[$m][$a] = floatval($raina[12]); $rainv2[$m][$a] = $raina[12];
				if($rainv[$m][$a] > 0) { $rdays[$m] = $rdays[$m] + 1; } if($rainv[$m][$a] > 1) { $r1days[$m] = $r1days[$m] + 1; }
				if(strpos($rainv2[$m][$a],'*')) { $sndays = $sndays + 1; }
			}
			if(strpos($data[$i]," direction") > 0) { $wdira = explode(" ", $data[$i]); if(intval($wdira[10]) == 0) { $wdirv[$a] = degname(intval($wdira[11]));
				$wdirv2[$a] = intval($wdira[11]); } else { $wdirv[$a] = degname(intval($wdira[10])); $wdirv2[$a] = intval($wdira[10]); }
			}
		}

		$wlabels = array('N','NNE','NE','ENE','E','ESE','SE','SSE','S','SSW','SW','WSW','W','WNW','NW','NNW','N');
		foreach($wdirv as $val) {
			for($k = 0; $k < 16; $k++) { if($val == $wlabels[$k]) { $wdircount[$k][$m] = $wdircount[$k][$m] + 1; } else { $wdircount[$k][$m] = $wdircount[$k][$m] + 0; } }
		}

		//Temperature
		$tmin[$m] = min($tminv[$m]); $tmax[$m] = max($tmaxv[$m]); $tave[$m] = array_sum($tavev[$m])/$a; $tminave[$m] = array_sum($tminv[$m])/$a; $tmaxave[$m] = array_sum($tmaxv[$m])/$a;
		$tminmax[$m] = max($tminv[$m]); $tmaxmin[$m] = min($tmaxv[$m]); $tavemin[$m] = min($tavev[$m]); $tavemax[$m] = max($tavev[$m]);
		$tminD[$m] = array_search($tmin[$m],$tminv[$m]); $tmaxD[$m] = array_search($tmax[$m],$tmaxv[$m]); $tminmaxD[$m] = array_search($tminmax[$m],$tminv[$m]); $tmaxminD[$m] = array_search($tmaxmin[$m],$tmaxv[$m]);
		$taveminD[$m] = array_search($tavemin[$m],$tavev[$m]); $tavemaxD[$m] = array_search($tavemax[$m],$tavev[$m]); $tanom = array(4.7, 4.9, 7, 8.9, 12.5, 15.65, 17.9, 17.7, 14.9, 11.7, 7.6, 5.5);
		$tanoml = array(2.4,2.2,3.6,5.0,8.3,11.1,13.4,13.2,11.1,8.8,5.0,3.2); $tanomh = array(7.0,7.6,10.4,12.8,16.7,20.2,22.4,22.2,18.7,14.6,10.2,7.8);
		$taveA[$m] = $tave[$m] - $tanom[$m-1]; $tminaveA[$m] = $tminave[$m] - $tanoml[$m-1]; $tmaxaveA[$m] = $tmaxave[$m] - $tanomh[$m-1];
		//Rain
		$rtot[$m] = array_sum($rainv[$m]); $rmax[$m] = max($rainv[$m]); if($rdays[$m] > 0) { $rdmean[$m] = $rtot[$m]/$rdays[$m]; } else { $rdmean[$m] = 'n/a'; } $r1danom = array(11,9,10,10,9,9,7,8,9,10,10,11);
		$rainav = array($myavjanrain,$myavfebrain,$myavmarrain,$myavaprrain,$myavmayrain,$myavjunrain,$myavjulrain,$myavaugrain,$myavseprain,$myavoctrain,$myavnovrain,$myavdecrain);  
		$rtotA[$m] = round(100*$rtot[$m]/$rainav[$m-1],0) . '%'; $r1daysA[$m] = conv2($r1days[$m] - $r1danom[$m-1],0,0); $rmaxD[$m] = array_search($rmax[$m],$rainv[$m]);
		$moredata = file('mrep.csv'); $st = intval((mktime(12,0,0,$m,1,$yea)-mktime(0,0,0,2,1,2009))/(24*3600))+1; $en = $st + date('t', mktime(0,0,0,$m,1,$yea));
		for($l = $st; $l < $en; $l++) {
			$mhrndata[$l] = explode(',',$moredata[$l]);
			if(floatval($mhrndata[$l][5]) > $rhmax[$m]) { $rhmax[$m] = floatval($mhrndata[$l][5]); $rhmaxD[$m] = $mhrndata[$l][0]; }
			if(floatval($mhrndata[$l][6]) > $rrmax[$m]) { $rrmax[$m] = floatval($mhrndata[$l][6]); $rrmaxD[$m] = $mhrndata[$l][0]; }
		}
		// if(mktime(0,0,0,$mon,1,$yea) == mktime(0,0,0,date('m'),1,date('Y'))) { $rrmax = $mrecordrainrateperhr; $rrmaxD = $mrecordrainrateday; }
		//Wind
		$wave[$m] = array_sum($wavev[$m])/$a; $gmax[$m] = max($gustv[$m]); $amax[$m] = max($spedv[$m]); $wmin[$m] = min($wavev[$m]); $wmax[$m] = max($wavev[$m]);
		$wanom = array(5.2,5.1,5.2,4.9,4.7,4.4,4.3,4.0,3.9,4.1,4.6,5.1); $waveA[$m] = $wave[$m] - $wanom[$m-1]; $wdir[$m] = $wmdirv2 . '&deg; (' . $wmdirv . ')';
		$gmaxD[$m] = array_search($gmax[$m],$gustv[$m]); $amaxD[$m] = array_search($amax[$m],$spedv[$m]); $wminD[$m] = array_search($wmin[$m],$wavev[$m]); $wmaxD[$m] = array_search($wmax[$m],$wavev[$m]);
		//Relative Humidity
		$hmin[$m] = min($hminv[$m]); $hmax[$m] = max($hmaxv[$m]); $have[$m] = array_sum($havev[$m])/$a; $hminave[$m] = array_sum($hminv[$m])/$a; $hmaxave[$m] = array_sum($hmaxv[$m])/$a;
		$hminmax[$m] = max($hminv[$m]); $hmaxmin[$m] = min($hmaxv[$m]); $havemin[$m] = min($havev[$m]); $havemax[$m] = max($havev[$m]);
		$hminD[$m] = array_search($hmin[$m],$hminv[$m]); $hmaxD[$m] = array_search($hmax[$m],$hmaxv[$m]); $hminmaxD[$m] = array_search($hminmax[$m],$hminv[$m]); $hmaxminD[$m] = array_search($hmaxmin[$m],$hmaxv[$m]);
		$haveminD[$m] = array_search($havemin[$m],$havev[$m]); $havemaxD[$m] = array_search($havemax[$m],$havev[$m]);
		//Dew Point
		$dmin[$m] = min($dminv[$m]); $dmax[$m] = max($dmaxv[$m]); $dave[$m] = array_sum($davev[$m])/$a; $dminave[$m] = array_sum($dminv[$m])/$a; $dmaxave[$m] = array_sum($dmaxv[$m])/$a;
		$dminmax[$m] = max($dminv[$m]); $dmaxmin[$m] = min($dmaxv[$m]); $davemin[$m] = min($davev[$m]); $davemax[$m] = max($davev[$m]);
		$dminD[$m] = array_search($dmin[$m],$dminv[$m]); $dmaxD[$m] = array_search($dmax,$dmaxv[$m]); $dminmaxD[$m] = array_search($dminmax[$m],$dminv[$m]); $dmaxminD[$m] = array_search($dmaxmin[$m],$dmaxv[$m]);
		$daveminD[$m] = array_search($davemin[$m],$davev[$m]); $davemaxD[$m] = array_search($davemax[$m],$davev[$m]);
		//Pressure
		$pmin[$m] = min($pminv[$m]); $pmax[$m] = max($pmaxv[$m]); $pave[$m] = array_sum($pavev[$m])/$a; $pminD[$m] = array_search($pmin[$m],$pminv[$m]); $pmaxD[$m] = array_search($pmax[$m],$pmaxv[$m]);
	} }//End monthly data collection
	
	//Annual data collection
	//Temperature
	$Ytmin = min($tmin); $Ytmax = max($tmax); $Ytave = array_sum($tave)/$mcnt; $Ytminave = array_sum($tminave)/$mcnt; $Ytmaxave = array_sum($tmaxave)/$mcnt;
	$Ytminmax = max($tminmax); $Ytmaxmin = min($tmaxmin); $Ytavemin = min($tavemin); $Ytavemax = max($tavemax);
	$YtminD = datefull($tminD[array_search($Ytmin,$tmin)]) . ' ' . monthfull(array_search($Ytmin,$tmin));
	$YtmaxD = datefull($tmaxD[array_search($Ytmax,$tmax)]) . ' ' . monthfull(array_search($Ytmax,$tmax));
	$YtminmaxD = datefull($tminmaxD[array_search($Ytminmax,$tminmax)]) . ' ' . monthfull(array_search($Ytminmax,$tminmax));
	$YtmaxminD = datefull($tmaxminD[array_search($Ytmaxmin,$tmaxmin)]) . ' ' . monthfull(array_search($Ytmaxmin,$tmaxmin));
	$YtavemaxD = datefull($tavemaxD[array_search($Ytavemax,$tavemax)]) . ' ' . monthfull(array_search($Ytavemax,$tavemax));
	$YtaveminD = datefull($taveminD[array_search($Ytavemin,$tavemin)]) . ' ' . monthfull(array_search($Ytavemin,$tavemin));
	$YtaveA = average($taveA,$mcnt); $YtminaveA = average($tminaveA,$mcnt); $YtmaxaveA = average($tmaxaveA,$mcnt);
	$Mtmax = max($tave); $MtmaxD = array_search($Mtmax,$tave); $MtmaxA = $Mtmax-$tanom[$MtmaxD-1]; $MtmaxD = monthfull($MtmaxD);
	$Mtmin = min($tave); $MtminD = array_search($Mtmin,$tave); $MtminA = $Mtmin-$tanom[$MtminD-1]; $MtminD = monthfull($MtminD);
	$MAtmaxA = max($taveA); $MAtmaxD = array_search($MAtmaxA,$taveA); $MAtmax = $tave[$MAtmaxD]; $MAtmaxD = monthfull($MAtmaxD);
	$MAtminA = min($taveA); $MAtminD = array_search($MAtminA,$taveA); $MAtmin = $tave[$MAtminD]; $MAtminD = monthfull($MAtminD);
	//Rain
	$Yrtot = average($rtot); $Yrmax = max($rmax); $Yrdays = average($rdays); $Yr1days = average($r1days);
	$Yrdmean = $Yrtot/$Yrdays; $Yrrmax = max($rrmax); $Yrhmax = max($rhmax); $YrtotA = round(100*$Yrtot/average($rainav)).'%'; $Yr1daysA = $Yr1days - average($r1danom);
	$YrmaxD = datefull($rmaxD[array_search($Yrmax,$rmax)]) . ' ' . monthfull(array_search($Yrmax,$rmax));
	$YrrmaxD = datefull($rrmaxD[array_search($Yrrmax,$rrmax)]) . ' ' . monthfull(array_search($Yrrmax,$rrmax));
	$YrhmaxD = datefull($rhmaxD[array_search($Yrhmax,$rhmax)]) . ' ' . monthfull(array_search($Yrhmax,$rhmax));
	$Mrmax = max($rtot); $MrmaxD = array_search($Mrmax,$rtot); $MrmaxA = round(100*$Mrmax/$rainav[$MrmaxD-1]) . '%'; $MrmaxD = monthfull($MrmaxD);
	$Mrmin = min($rtot); $MrminD = array_search($Mrmin,$rtot); $MrminA = round(100*$Mrmin/$rainav[$MrminD-1]) . '%'; $MrminD = monthfull($MrminD);	
	//Wind
	$wdirfreq = 'Breakdown:\n';
	for($k = 0; $k < 16; $k++) { $Ywdircount[$k] = average($wdircount[$k]); $Ywdirfreq .= $Ywdircount[$k] . ' ' . $wlabels[$k] . '\n'; }
	$Ywave = average($wave,$mcnt); $YwaveA = $Ywave - average($wanom,12); $Ywave10 = average($wave10); $Ywdir = $wlabels[array_search(max($Ywdircount),$Ywdircount)];
	$Ywmin = min($wmin); $YwminD = datefull($wminD[array_search($Ywmin,$wmin)]) . ' ' . monthfull(array_search($Ywmin,$wmin));
	$Ywmax = max($wmax); $YwmaxD = datefull($wmaxD[array_search($Ywmax,$wmax)]) . ' ' . monthfull(array_search($Ywmax,$wmax));
	$Ygmax = max($gmax); $YgmaxD = datefull($gmaxD[array_search($Ygmax,$gmax)]) . ' ' . monthfull(array_search($Ygmax,$gmax));
	$Yamax = max($amax); $YamaxD = datefull($amaxD[array_search($Yamax,$amax)]) . ' ' . monthfull(array_search($Yamax,$amax));
	$Mwmax = max($wave); $MwmaxD = array_search($Mwmax,$wave); $MwmaxA = $Mwmax-$wanom[$MwmaxD-1]; $MwmaxD = monthfull($MwmaxD);
	$Mwmin = min($wave); $MwminD = array_search($Mwmin,$wave); $MwminA = $Mwmin-$wanom[$MwminD-1]; $MwminD = monthfull($MwminD);
	//Rel Hum
	$Yhmin = min($hmin); $Yhmax = max($hmax); $Yhave = array_sum($have)/$mcnt; $Yhminave = array_sum($hminave)/$mcnt; $Yhmaxave = array_sum($hmaxave)/$mcnt;
	$Yhminmax = max($hminmax); $Yhmaxmin = min($hmaxmin); $Yhavemin = min($havemin); $Yhavemax = max($havemax);
	$YhminD = datefull($hminD[array_search($Yhmin,$hmin)]) . ' ' . monthfull(array_search($Yhmin,$hmin));
	$YhmaxD = datefull($hmaxD[array_search($Yhmax,$hmax)]) . ' ' . monthfull(array_search($Yhmax,$hmax));
	$YhminmaxD = datefull($hminmaxD[array_search($Yhminmax,$hminmax)]) . ' ' . monthfull(array_search($Yhminmax,$hminmax));
	$YhmaxminD = datefull($hmaxminD[array_search($Yhmaxmin,$hmaxmin)]) . ' ' . monthfull(array_search($Yhmaxmin,$hmaxmin));
	$YhavemaxD = datefull($havemaxD[array_search($Yhavemax,$havemax)]) . ' ' . monthfull(array_search($Yhavemax,$havemax));
	$YhaveminD = datefull($haveminD[array_search($Yhavemin,$havemin)]) . ' ' . monthfull(array_search($Yhavemin,$havemin));
	$Mhmax = max($have); $MhmaxD = array_search($Mhmax,$have); $MhmaxD = monthfull($MhmaxD);
	$Mhmin = min($have); $MhminD = array_search($Mhmin,$have); $MhminD = monthfull($MhminD);
	//Dew Point
	$Ydmin = min($dmin); $Ydmax = max($dmax); $Ydave = array_sum($dave)/$mcnt; $Ydminave = array_sum($dminave)/$mcnt; $Ydmaxave = array_sum($dmaxave)/$mcnt;
	$Ydminmax = max($dminmax); $Ydmaxmin = min($dmaxmin); $Ydavemin = min($davemin); $Ydavemax = max($davemax);
	$YdminD = datefull($dminD[array_search($Ydmin,$dmin)]) . ' ' . monthfull(array_search($Ydmin,$dmin));
	$YdmaxD = datefull($dmaxD[array_search($Ydmax,$dmax)]) . ' ' . monthfull(array_search($Ydmax,$dmax));
	$YdminmaxD = datefull($dminmaxD[array_search($Ydminmax,$dminmax)]) . ' ' . monthfull(array_search($Ydminmax,$dminmax));
	$YdmaxminD = datefull($dmaxminD[array_search($Ydmaxmin,$dmaxmin)]) . ' ' . monthfull(array_search($Ydmaxmin,$dmaxmin));
	$YdavemaxD = datefull($davemaxD[array_search($Ydavemax,$davemax)]) . ' ' . monthfull(array_search($Ydavemax,$davemax));
	$YdaveminD = datefull($daveminD[array_search($Ydavemin,$davemin)]) . ' ' . monthfull(array_search($Ydavemin,$davemin));
	$Mdmax = max($dave); $MdmaxD = array_search($Mdmax,$dave); $MdmaxD = monthfull($MdmaxD);
	$Mdmin = min($dave); $MdminD = array_search($Mdmin,$dave); $MdminD = monthfull($MdminD);
	//Pressure
	$Ypmin = min($pmin); $Ypmax = max($pmax); $Ypave = array_sum($pave)/$mcnt;
	$YpminD = datefull($pminD[array_search($Ypmin,$pmin)]) . ' ' . monthfull(array_search($Ypmin,$pmin));
	$YpmaxD = datefull($pmaxD[array_search($Ypmax,$pmax)]) . ' ' . monthfull(array_search($Ypmax,$pmax));
	$Mpmax = max($pave); $MpmaxD = array_search($Mpmax,$pave); $MpmaxD = monthfull($MpmaxD);
	$Mpmin = min($pave); $MpminD = array_search($Mpmin,$pave); $MpminD = monthfull($MpminD);
	
	return array(
			array($Ytmin, $Ytmax, $Ytave, $Ytminave, $Ytmaxave, $Ytminmax, $Ytmaxmin, $Ytavemin, $Ytavemax, $Mtmin, $Mtmax, $MAtmin, $MAtmax),
			array('', '', $YtaveA, $YtminaveA, $YtmaxaveA, '', '', '', '', $MtminA, $MtmaxA, $MAtminA, $MAtmaxA),
			array($YtminD, $YtmaxD, '', '', '', $YtminmaxD, $YtmaxminD, $YtaveminD, $YtavemaxD, $MtminD, $MtmaxD, $MAtminD, $MAtmaxD),
			
			array($Yrtot, $Yrmax, $Yrdays, $Yr1days, $Yrdmean, $Yrrmax, $Yrhmax, $Mrmax, $Mrmin),
			array($YrtotA, '', '', $Yr1daysA, '', '', '', $MrmaxA, $MrminA),
			array('', $YrmaxD, '', '', '', $YrrmaxD, $YrhmaxD, $MrmaxD, $MrminD),
			
			array($Ywave, $Ygmax, $Yamax, $Ywmin, $Ywmax, $Ywave10, $Ywdir, $Mwmin, $Mwmax),
			array($YwaveA,'', '', '', '', '', '', $MwminA, $MwmaxA),
			array('', $YgmaxD, $YamaxD, $YwminD, $YwmaxD, '', '', $MwminD, $MwmaxD),
			
			array($Yhmin, $Yhmax, $Yhave, $Yhminave, $Yhmaxave, $Yhminmax, $Yhmaxmin, $Yhavemin, $Yhavemax, $Mhmin, $Mhmax),
			array('', '', '', '', '', '', '', '', ''),
			array($YhminD, $YhmaxD, '', '', '', $YhminmaxD, $YhmaxminD, $YhaveminD, $YhavemaxD, $MhminD, $MhmaxD),
			
			array($Ydmin, $Ydmax, $Ydave, $Ydminave, $Ydmaxave, $Ydminmax, $Ydmaxmin, $Ydavemin, $Ydavemax, $Mdmin, $Mdmax),
			array('', '', '', '', '', '', '', '', ''),
			array($YdminD, $YdmaxD, '', '', '', $YdminmaxD, $YdmaxminD, $YdaveminD, $YdavemaxD, $MdminD, $MdmaxD),
			
			array($Ypmin, $Ypmax, $Ypave, $Mpmin, $Mpmax),
			array('', '', ''),
			array($YpminD, $YpmaxD, '', $MpminD, $MpmaxD),
			
			$Ywdirfreq,
			
			array($tmin, $tmax, $tave, $tminave, $tmaxave, $rtot, $rmax, $rdays, $wave, $gmax, $hmin, $hmax, $have, $dmin, $dmax, $dave, $pmin, $pmax, $pave),
			array('', '', $taveA, $tminaveA, $tmaxaveA, $rtotA, '', '', $waveA, '', '', '', '', '', '', '', '', '', ''),
			array($tminD, $tmaxD, '', '', '', '', $rmaxD, '', '', $gmaxD, $hminD, $hmaxD, '', $dminD, $dmaxD, '', $pminD, $pmaxD, '')
			);
}
?>