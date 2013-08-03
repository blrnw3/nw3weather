<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php
	$file = 12;
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Rain Detail</title>

	<meta name="description" content="Detailed latest rainfall data, graphs and records from NW3 weather station" />

	<?php require('chead.php'); ?>
	<?php include('ggltrack.php') ?>
</head>

<body>
	<!-- ##### Header ##### -->
	<?php require('header.php'); ?>

	<!-- ##### Left Sidebar ##### -->
	<?php require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->

	<div id="main">

<?php require('site_status.php'); ?>
<?php require $root.'RainTags.php'; ?>

<h1>Detailed Rainfall Data</h1>

<table class="table1" width="34%" align="left" cellpadding="5" cellspacing="0">
<?php
tableHead("Current / Latest", 2);
echo '<tr class="table-top"><td class="td12" colspan="1">Measure</td><td class="td12" colspan="1">Value</td></tr>';
$measures = array('Daily Rain','Rain Last 10 mins','Rain Last Hour','Rain Last 3 hrs','Rain Last 6 hrs','Rain Last 24 hrs',
	'Rain Duration','Rain Rate','Past 24hrs Duration', '---','Most Recent Rain',
	'Yesterday&#39;s Rain','Last 7 Days&#39; Rain','Last 31 Days&#39; Rain','Last 365 Days&#39; Rain','Monthly Rain','Annual Rain',$seasonname.' Rain','---',
	'Consecutive '.$drywet.' Days',	'Monthly Rain Days','Annual Rain Days','Daily Rain Last Year','Last Month Rain-to-date','Last Year Rain-to-date');
$rn24 = $HR24['trendRn'][0];
$rnxx = $HR24['trendRn'];
$values = array($rain, $rn24 - $rnxx['10m'], $rn24 - $rnxx[1], $rn24 - $rnxx[3], $rn24 - $rnxx[6], $rn24,
	$HR24['misc']['rnduration'] .' hrs', $HR24['misc']['rnrate'], $HR24['misc']['wethrs'].' hrs',  '---', $HR24['misc']['rnlast'],
	$yestrn, $rainweek, $rain31, $rain365, conv($monthrn, 2).$monthrnF, conv($yearrn, 2).$yearrnF, conv($seasonrn, 2).$seasonrnF, '---',
	$drywetdays, $raindays_monthF, $raindays_yearF, $day_rain_last_year, $raintodmonthago, $raintodayearago);
//$order = array(2,1,0,3,4,5,6,7,8);
$conv = array(2,2,2,2,2,2,false,2.1,false,false,false,2,2,2,2,false,false,false,false,8,false,false,2,2,2);
for($r = 0; $r < count($measures); $r++) {
	if($r % 2 == 0) { $colcol = 'light'; } else { $colcol = 'dark'; }
	echo '<tr class="row', $colcol, '">
		<td class="td12" width="55%">', $measures[$r], '</td>
		<td class="td12" width="45%">', conv($values[$r],$conv[$r],true,false,0,false,true), '</td>
		</tr>';
}
?>
</table>


<table class="table1" width="65%" align="center" cellpadding="5" cellspacing="0" >
<?php
tableHead("Current Extremes", 5);
tr();
$headings = array('Measure', 'Year', 'Month', 'Today', 'Yesterday');
$measures = array('Wettest Day','Most Rain In One Hour','Max Rain Rate','Longest Wet Spell','Longest Dry Spell','Wettest Month','Driest Month','Most Rain Days',
	'Fewest Rain Days');
$widthsTbl2 = array(20,23,23,17,17);
$order = array(2,1,0,3,4,5,6,7,8); $conv = array(2,2,2.1,8,8,2,2,0,0);
$valuesD = array('', $maxhourrn, $maxrainratehr, '', '', '', '', '', '');
$valuesM = array($mrecorddailyrain, $mrecorddailyhrmax, $mrecorddailyrate, $mrecwetlength, $mrecdrylength, '', '', '', '');
$valuesY = array($yrecorddailyrain, $yrecorddailyhrmax, $yrecorddailyrate, $yrecwetlength, $yrecdrylength, $yrmonthrn_max, $yrmonthrn_min, $yrndays_max, $yrndays_min);
$valuesB = array('', $maxhourrnyest, $maxrainrateyest, '', '', '', '', '', '');
$timesD = array('', norain_fix($maxhourrnt,$rain), norain_fix($maxrainratetime,$rain,true), '', '', '', '', '', '');
$timesM = array($mrecorddailyraindate, $mrecorddailyhrmaxdate, $mrecorddailyratedate, $mrecwetlengthdate, $mrecdrylengthdate, '', '', '', '');
$timesY = array($yrecorddailyraindate, $yrecorddailyhrmaxdate, $yrecorddailyratedate, $yrecwetlengthdate, $yrecdrylengthdate, $yrmonthrn_maxdate, $yrmonthrn_mindate, $yrndays_maxdate, $yrndays_mindate);
$timesB = array('', norain_fix($maxhourrnyesttime,$yestrn), norain_fix($maxrainrateyesttime,$yestrn,true), '', '', '', '', '', '');

for($h = 0; $h < count($headings); $h++) {
	td($headings[$h], "td12", $widthsTbl2[$h]);
}
tr_end();

for($r = 0; $r < count($measures); $r++) {
	if($r % 2 == 0) { $colcol = 'light'; } else { $colcol = 'dark'; }
	echo '<tr class="row', $colcol, '">
		<td class="td12" width="20%">', $measures[$order[$r]], '</td>
		<td class="td12" width="20%"><b>'; if($valuesY[$order[$r]] == '') { echo '-'; } else {
			echo conv($valuesY[$order[$r]],$conv[$order[$r]]); } echo '</b><br />', $timesY[$order[$r]], '</td>
		<td class="td12" width="20%"><b>'; if($valuesM[$order[$r]] == '') { echo '-'; } else {
			echo conv($valuesM[$order[$r]],$conv[$order[$r]]); } echo '</b><br />', $timesM[$order[$r]], '</td>
		<td class="td12" width="20%"><b>'; if($valuesD[$order[$r]] == '') { echo '-'; } else {
			echo conv($valuesD[$order[$r]],$conv[$order[$r]]); } echo '</b><br />', $timesD[$order[$r]], '</td>
		<td class="td12" width="20%"><b>'; if($valuesB[$order[$r]] == '') { echo '-'; } else {
			echo conv($valuesB[$order[$r]],$conv[$order[$r]]); } echo '</b><br />', $timesB[$order[$r]], '</td>
		</tr>';
}
?>
</table>

<img style="margin:5px" src="graph31.php?type=rain&amp;x=565&amp;y=300" width="565" height="300" alt="31dayrain" />

<br /><br />

<table class="table1" width="65%" align="left" cellpadding="5" cellspacing="0">
<?php
tableHead("Records", 4);
tr();
$headings = array('Measure', 'Overall', $monthname, datefull($dday) . ' ' .monthfull($dmonth));
$marginsRecords = array(21, 29, 27, 23);
//$measures = array('Max Rain In One Day','Most Rain In One Hour','Max Rain Rate','Longest Wet Spell','Longest Dry Spell','Wettest Month','Driest Month','Most Rain Days',
//	'Fewest Rain Days');
//$order = (2,1,0,5,6,7,8,3,4);
$valuesD = array($maxrainD, $maxhrmaxD, $maxrateD, '', '', '', '', '', '');
$valuesM = array($maxrainMD, $maxhrmaxMD, $maxrateMD, $recwetlength_curr, $recdrylength_curr, $monthrn_max_curr, $monthrn_min_curr, $rndays_max_curr, $rndays_min_curr);
$valuesA = array($recorddailyrain, $recorddailyhrmax, $recorddailyrate, $recwetlength, $recdrylength, $monthrn_max, $monthrn_min, $rndays_max, $rndays_min);
$timesD = array($maxrainDdate, $maxhrmaxDdate, $maxrateDdate, '', '', '', '', '', '');
$timesM = array($maxrainMDdate, $maxhrmaxMDdate, $maxrateMDdate, $recwetlength_currdate, $recdrylength_currdate, $monthrn_max_currdate, $monthrn_min_currdate, $rndays_max_currdate, $rndays_min_currdate);
$timesA = array($recorddailyraindate, $recorddailyhrmaxdate, $recorddailyratedate, $recwetlengthdate, $recdrylengthdate, $monthrn_maxdate, $monthrn_mindate, $rndays_maxdate, $rndays_mindate);
for($h = 0; $h < count($headings); $h++) {
	td($headings[$h], "td12", $marginsRecords[$h]);
}
tr_end();
for($r = 0; $r < count($measures); $r++) {
	if($r % 2 == 0) { $colcol = 'light'; } else { $colcol = 'dark'; }
	echo '<tr class="row', $colcol, '">
		<td class="td12" width="25%">', $measures[$order[$r]], '</td>
		<td class="td12" width="25%"><b>', conv($valuesA[$order[$r]],$conv[$order[$r]]), '</b><br />', $timesA[$order[$r]], '</td>
		<td class="td12" width="25%"><b>', conv($valuesM[$order[$r]],$conv[$order[$r]]), '</b><br />', $timesM[$order[$r]], '</td>
		<td class="td12" width="25%"><b>'; if($valuesD[$order[$r]] == '') { echo '-</b>'; } else {
			echo conv($valuesD[$order[$r]],$conv[$order[$r]]), '</b><br />', $timesD[$order[$r]]; } echo '</td>
		</tr>';
}
?>
</table>

<table class="table1" align="center" width="34%" cellpadding="5" cellspacing="0">
<?php
tableHead("Wettest Days");
tr();
echo '<td class="td12" colspan="1">Rank</td><td class="td12" colspan="1">Value</td><td class="td12" colspan="1">Date</td></tr>';

for($i = 1; $i <= $ranknum; $i++) {
	$style = colcol($i);
	$curr_hlite = ($wettest_day[$i] == 'current') ? ' style="font-weight:bold"' : '';
	echo '<tr class="row', $style, '"', $curr_hlite,'>
		<td class="td12">', $i, '</td>
		<td class="td12">', conv($wettest[$i],2), '</td>
		<td class="td12">', $wettest_day[$i], '</td>
		</tr>';
}
?>
</table>

<br />

<table class="table1" width="65%" align="left" cellpadding="5" cellspacing="0">
<?php
tableHead("More Records");
tr();
echo '<td class="td12" colspan="1">Measure</td><td class="td12" colspan="1">Value and Date</td></tr>';
if($rn24 > 54.2) {
	$record24hrrain = $rn24;
	$rn24Recorddate = '<span style="color:red; font-weight:bold;">Now!</span>';
} else {
	$record24hrrain = 54.2;
	$rn24Recorddate = 'Ending 14:08, 2nd May 2010';
}
$measures = array('Wettest Year','Driest Year','Wettest 31 days','Driest 31 days','Wettest 365 days','Driest 365 days','Wettest 24hrs');
$order = array(0,1,4,5,2,3,6); $conv = array(2,2,2,2,2,2,2);
$values = array($wettestyr, $driestyr, $wettest31, $driest31, $wettest365, $driest365, $record24hrrain);
$times = array($wettestyrdate, $driestyrdate, $wettest31date, $driest31date, $wettest365date, $driest365date, $rn24Recorddate);
for($r = 0; $r < count($measures); $r++) {
	if($r % 2 == 0) { $colcol = 'light'; } else { $colcol = 'dark'; }
	echo '<tr class="row', $colcol, '">
		<td class="td12" width="25%">', $measures[$order[$r]], '</td>
		<td class="td12" width="75%"><b>', conv($values[$order[$r]],$conv[$order[$r]]), '</b>, ', $times[$order[$r]], '</td>
		</tr>';
}
?>
</table>

<table class="table1" align="center" width="34%" cellpadding="5" cellspacing="0">
<?php
tableHead("Wettest Months");
tr();
echo '<td class="td12" colspan="1">Rank</td><td class="td12" colspan="1">Value</td><td class="td12" colspan="1">Date</td></tr>';
for($i = 1; $i <= $ranknumM; $i++) {
	$style = colcol($i);
	$curr_hlite = ($wettest_dayM[$i] == 'current') ? ' style="font-weight:bold"' : '';
	echo '<tr class="row', $style, '"', $curr_hlite,'>
		<td class="td12">', $i, '</td>
		<td class="td12">', conv($wettestM[$i],2), '</td>
		<td class="td12">', $wettest_dayM[$i], '</td>
		</tr>';
}
?>
</table>

<br />

<table class="table1" align="left" width="50%" cellpadding="5" cellspacing="0">
<?php
tableHead("Past Year Monthly Totals");
tr();
echo '<td class="td12">Month</td><td class="td12">Total</td><td class="td12">Anomaly</td></tr>';
for($i = 0; $i < 12; $i++) {
	if($i % 2 == 0) { $style = 'rowlight'; } else { $style = 'rowdark'; } $hlite = ''; $yr2 = '';
	if($i+1 == intval($dmonth)) { $raintots[$i] = $lymrain; $yr2 = $dyear-1; }
	if($i+1 == intval($dmonth)-1) { $hlite = 'border-bottom:3px solid #8181F7'; }
	$yr1 = ''; if($i == 0) { $yr1 = $dyear; } if($yr1 == $yr2) { $yr1 = ''; }
	if(date('m') < 2) { $yr1 = ''; }
	echo '<tr class="', $style, '"><td style="', $hlite, '" class="td12">', date('F ',mkdate($i+1, 1)), $yr1, $yr2, '</td><td style="', $hlite, '" class="td12">',
	conv($raintots[$i],2,1), '</td><td style="', $hlite, '" class="td12">', percent($raintots[$i],$rainav[$i],0,true,false), '</td></tr>
		';
}
$style_end = 'border-top:4px solid #0431B4;border-bottom:4px solid #0431B4;font-size:110% '; ?>
<tr class="rowlight">
	<td style="<?php echo $style_end; ?>" class="td12">12-month Total</td>
	<td style="<?php echo $style_end; ?>" class="td12"><?php echo conv(array_sum($raintots),2,1); ?></td>
	<td style="<?php echo $style_end; ?>" class="td12"><?php echo round(array_sum($raintots)*100/array_sum($rainav)); ?>%</td>
</tr>
</table>

<table class="table1" align="center" width="34%" cellpadding="5" cellspacing="0">
<?php
tableHead("Driest Months");
tr();
echo '<td class="td12" colspan="1">Rank</td><td class="td12" colspan="1">Value</td><td class="td12" colspan="1">Date</td></tr>';

for($i = 1; $i <= $ranknumM; $i++) {
	$style = colcol($i);
	$curr_hlite = ($driest_dayM[$i] == 'Current') ? ' style="font-weight:bold"' : '';
	echo '<tr class="row', $style, '"', $curr_hlite,'>
		<td class="td12">', $i, '</td>
		<td class="td12">', conv($driestM[$i],2), '</td>
		<td class="td12">', $driest_dayM[$i], '</td>
		</tr>';
}
?>
</table>

<br />

<table class="table1" align="center" width="48%" cellpadding="5" cellspacing="0">
<?php
tableHead("Past Year Seasonal Totals");
tr();
echo '<td class="td12">Season</td><td class="td12">Total</td><td class="td12">Anomaly</td></tr>';

for($i = 0; $i < 12; $i++) {
	if($i % 3 == 0) { $alttots[$i] = $lymrain+$lymrain1+$raintots[$i+1]; }
	elseif($i % 3 == 1) { $alttots[$i] = $lymrain+$lymrain1+$lymrain2; }
	else { $alttots[$i] = $lymrain+$raintots[$i+1]+$raintots[$i+2]; }
}
$sraintots = array($raintots[0]+$raintots[1]+$raintots[11], $raintots[2]+$raintots[3]+$raintots[4], $raintots[5]+$raintots[6]+$raintots[7], $raintots[8]+$raintots[9]+$raintots[10]);
$srainav = array($rainav[0]+$rainav[1]+$rainav[11], $rainav[2]+$rainav[3]+$rainav[4], $rainav[5]+$rainav[6]+$rainav[7], $rainav[8]+$rainav[9]+$rainav[10]);
for($i = 0; $i < 4; $i++) {
	if($i % 2 == 0) { $style = 'rowlight'; } else { $style = 'rowdark'; } $hlite = '';
	if($i+1 == $season) { $sraintots[$i] = $alttots[date('n')-1]; }
	if($i+1 == $season-1) { $hlite = 'border-bottom:3px solid #8181F7'; }
	if($i+1 < $season || date('n') == 12) { $nwint = $dyear; } else { $nwint = $dyear-1; } $dfo1 = date('Y')-2001; $dfo2 = date('Y')-2000; $dfo3 = date('Y')-2002;
	if(date('n') > 2) { $wint = $dfo1 .'/' .$dfo2; } else { $wint = $dfo3 .'/' .$dfo1; } $yr3 = array($wint, $nwint, $nwint, $nwint);
	echo '<tr class="', $style, '"><td style="', $hlite, '" class="td12">', $snames[$i], ' ', $yr3[$i], '</td><td style="', $hlite, '" class="td12">',
	conv($sraintots[$i],2,1), '</td><td style="', $hlite, '" class="td12">', percent($sraintots[$i],$srainav[$i],0,true,false), '</td></tr>';
}
?>
</table>

<br />

<img style="margin:5px" src="graph12.php?type=rain&amp;x=600&amp;y=300" alt="12monthrain" />

<p><b>Note 1:</b> Rain records began in February 2009<br />
<b>Note 2:</b> The minimum recordable rain (the rain guage resolution) is <?php if($unitT == 'C') { echo '0.25 mm'; } else { echo '0.01 in'; } ?><br />
<b>Note 3:</b> Figures in brackets refer to departure from <a href="wxaverages.php" title="Long-term NW3 climate averages">average conditions</a><br />
<b>Note 4:</b> Rain rate records are manually checked, and changed if necessary, due to occasional issues with the software. Inital high readings may well be corrected at a later date.<br />
</p>

<p><a href="wxdataday.php?vartype=rain" title="<?php echo $year; ?>daily rain totals"><b>View daily totals for the past year</b></a></p>

<?php if($rn24 > 0) { echo '<img style="margin:5px" src="graphdayA.php?type1=rain&amp;x=800&amp;y=400" alt="Last 24hrs Rainfall" />'; } ?>

</div>

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>
 </body>
</html>