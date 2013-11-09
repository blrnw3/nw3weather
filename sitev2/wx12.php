<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

   <?php include("phptags.php");
	$file = 12; ?>
	
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - Rain Detail</title>

	<meta name="description" content="Old v2 - Detailed latest rainfall data and records from NW3 weather station" />

	<? require('chead.php'); ?>
	<?php include_once("ggltrack.php") ?> 
</head>

<body>
	<!-- For non-visual user agents: -->
	<div id="top"><a href="#main-copy" class="doNotDisplay doNotPrint">Skip to main content.</a></div>

	<!-- ##### Header ##### -->
	<? require('header.php'); ?>

	<!-- ##### Left Sidebar ##### -->
	<? require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->

	<div id="main-copy">

<? if(isset($_GET['blr'])) { $blr = '2'; } require('site_status'. $blr .'.php'); ?>

<h1>Detailed Rainfall Data</h1>

<table class="table1" width="40%" align="left" cellpadding="5" cellspacing="0">
<tr><td class="td12" colspan="2" ><h3>Current/Latest</h3></td></tr>
<tr class="table-top">
<td class="td12" colspan="1">Measure</td><td class="td12" colspan="1">Value</td>
</tr>

<tr class="column-light">
<td class="td12" width="57%">Daily Rain</td>
<td class="td12" width="43%"><?php echo conv($dayrn,2,1); ?></td>
</tr>
<tr class="column-dark">
<td class="td12" width="57%">Rain Last 10 mins</td>
<td class="td12" width="43%"><?php echo conv($rainlast10min,2,1); ?></td></tr>
<tr class="column-light">
<td class="td12" width="57%">Rain Last Hour</td>
<td class="td12" width="43%"><?php echo conv($rainlasthourmm,2,1); ?></td></tr>
<tr class="column-dark">
<td class="td12" width="57%">Rain Last 3 hrs</td>
<td class="td12" width="43%"><?php echo conv($rainlast3hourmm,2,1); ?></td></tr>
<tr class="column-light">
<td class="td12" width="57%">Rain Last 6 hrs</td>
<td class="td12" width="43%"><?php echo conv($rainlast6hourmm,2,1); ?></td></tr>
<tr class="column-dark">
<td class="td12" width="57%">Rain Last 24 hrs</td>
<td class="td12" width="43%"><?php echo conv($totalrainlast24hours,2,1); ?></td></tr>
<tr class="column-light">
<td class="td12" width="57%">Rain Duration</td>
<td class="td12" width="43%"><?php echo $rainduration; ?> mins</td></tr>
<tr class="column-dark">
<td class="td12" width="57%">Rain Rate</td>
<td class="td12" width="43%"><?php echo conv($currentrainratehr,2,1); ?>/h</td></tr>
<tr class="column-light">
<td class="td12" width="57%">---</td>
<td class="td12" width="43%">---</td></tr>
<tr class="column-dark">
<td class="td12" width="57%">Most Recent Rain</td>
<td class="td12" width="43%"><?php $splitdolra = explode("/", $dateoflastrainalways); echo $timeoflastrainalways, ', '; if($splitdolra[0] == $date_day && $splitdolra[1] == $date_month):
echo "Today"; elseif($splitdolra[0] == $date_day-1 && $splitdolra[1] == $date_month): echo "Yesterday"; else: echo datefull($splitdolra[0]),' ',monthfull($splitdolra[1]); endif; ?></td></tr>
<tr class="column-light">
<td class="td12" width="57%">Yesterday's Rain</td>
<td class="td12" width="43%"><?php echo conv($ystdyrain,2,1); ?></td></tr>
<tr class="column-dark">
<td class="td12" width="57%">Previous 7 Days' Rain</td>
<td class="td12" width="43%"><?php echo conv($raincurrentweek,2,1); ?></td></tr>
<tr class="column-light">
<td class="td12" width="57%">Monthly Rain</td>
<td class="td12" width="43%"><?php echo conv($monthrn,2,1), ' (', $stbfix, '<acronym title="Of final expected: ', round(100*$monthrn/$currentmonthaveragerain,0), '%">',
	round(100*$monthrn/($currentmonthaveragerain/date("t")*$date_day),0),'%</acronym>', $enbfix, ')'; ?></td></tr>
<tr class="column-dark">
<td class="td12" width="57%">Annual Rain</td>
<td class="td12" width="43%"><?php
$rainav = array($myavjanrain, $myavfebrain, $myavmarrain, $myavaprrain, $myavmayrain, $myavjunrain, $myavjulrain, $myavaugrain, $myavseprain, $myavoctrain, $myavnovrain, $myavdecrain); 
for($m = 0; $m < $date_month-1; $m++) {
	$curryearrainav = $curryearrainav + $rainav[$m];
}
echo conv($yearrn,2,1), ' (', $stbfix, '<acronym title="Of final expected: ', round(100*$yearrn/array_sum($rainav),0), '%">',
	round(100*$yearrn/($currentmonthaveragerain/date("t")*$date_day + $curryearrainav)), '%</acronym>', $enbfix, ')'; ?></td></tr>
<tr class="column-light">
<td class="td12" width="57%"><?php echo $seasonname; ?> Rain</td>
<td class="td12" width="43%"><?php
$raintots = array($nowrainjan, $nowrainfeb, $nowrainmar, $nowrainapr, $nowrainmay, $nowrainjun, $nowrainjul, $nowrainaug, $nowrainsep, $nowrainoct, $nowrainnov, $nowraindec);
$s_offset = $season*3 - 3;
for($e=0; $e < $sc; $e++) { $total += floatval($raintots[date('n',mktime(0,0,0,$e+$s_offset))-1]); }
for($f=0; $f < 3; $f++) { $s_avg += floatval($rainav[date('n',mktime(0,0,0,$f+$s_offset))-1]); }
for($g=1; $g < $sc; $g++) { $s_avg_r += floatval($rainav[date('n',mktime(0,0,0,$g+$s_offset))-1]); } $s_avg_r += $currentmonthaveragerain/date('t')*$date_day;
echo conv($total,2,1), ' (', $stbfix, '<acronym title="Of final expected: ', round(100*$total/$s_avg), '%">', round(100*$total/$s_avg_r), '%</acronym>', $enbfix, ')'; ?></td></tr>
<tr class="column-dark">
<td class="td12" width="57%">---</td>
<td class="td12" width="43%">---</td> </tr>
<tr class="column-light">
<td class="td12" width="57%">Consecutive Dry Days</td>
<td class="td12" width="43%"><?php echo $dayswithnorain; ?></td></tr>
<tr class="column-dark">
<td class="td12" width="57%">Consecutive Rain Days</td>
<td class="td12" width="43%"><?php echo $consecdayswithrain; ?></td></tr>
<tr class="column-light">
<td class="td12" width="57%">Monthly Rain Days</td>
<td class="td12" width="43%"><?php echo $dayswithrain; ?></td></tr>
<tr class="column-dark">
<td class="td12" width="57%">Annual Rain Days</td>
<? if(date('m') < 2) { $dayswithrainyear = $dayswithrain; } ?>
<td class="td12" width="43%"><?php echo $stbfix, '<acronym title="Proportion of days: ', round(100*$dayswithrainyear/(date('z')+1),0), '%">', $dayswithrainyear, '</acronym>', $enbfix; ?></td>
</tr>
</table>

<table class="table1" width="56%" align="center" cellpadding="5" cellspacing="0" >
<tr><td class="td12" colspan="3" ><h3>Current Day/Month/Year Records</h3></td></tr>
<tr class="table-top">
<td class="td12" colspan="1">Measure</td><td class="td12" colspan="1">Value</td><td class="td12" colspan="1">Time/Date</td>
</tr>
<tr class="column-light">
<td class="td12" width="45%">Max Rain in 1hr Today</td>
<td class="td12" width="25%"><?php echo conv($maxhourrn,2,1); ?></td>
<td class="td12" width="30%"><?php if($dayrn==0): echo "n/a"; else: echo $maxhourrnt; endif; ?></td> </tr>
<tr class="column-dark">
<td class="td12" width="45%">Max Rain Rate Today</td>
<td class="td12" width="25%"><?php if($maxrainratehr > 5): echo conv3($maxrainratehr,2,1); else: echo conv($maxrainratehr,2,1); endif; ?>/h</td>
<td class="td12" width="30%"><?php if($dayrn==0): echo "n/a"; else: echo $maxrainratetime; endif; ?></td> </tr>
<tr class="column-light">
<td class="td12" width="45%">Daily Rain Last Year</td>
<td class="td12" width="25%"><?php $year = date("Y");
$years = 1 + ($year - 2009);
$minrc = 99; $maxrainD = 0; $dd = intval($date_day); $maxrainMD = 0;
for($y = 0; $y < $years ; $y ++) {
	$yx = $year - $y; $m = $date_month;
	$filename = date('F', mktime(0,0,0,$m)) . $yx . ".htm";
	if(file_exists($filename) && $filename != date("F",mktime(0,0,0,$m)) . $date_year . ".htm") {
		$arr = file($filename);
		for ($i = 0; $i <1200; $i++) {
			if(strpos($arr[$i],"for the month of") > 0): $line = $arr[$i+10]; endif;
			if(strpos($arr[$i],"Daily report for") > 0): $linem = $arr[$i]; endif;
		}
		$arr2 = explode(" ", $line);
		if ($minrc > ($arr2[10])) { $minrc = ($arr2[10]); $minrct = $year-$y; }
	}
	if(file_exists($filename)) {
		$data = file($filename); $end = 1200;
		for ($i = 0; $i < $end; $i++) {
			if(strpos($data[$i],"remes for the month") > 0) { $end = $i; }
			if(strpos($data[$i],"remes for day") > 0) { $daya = explode(" ", $data[$i]); $a = intval(substr($daya[7],1,2)); }
			if(strpos($data[$i],"all for da") > 0) { $raina = explode(" ", $data[$i]); $rainv[$a][$y] = $raina[12]; }
			//if ($maxrainMD < $rainv[$a][$y]) { $maxrainMD = $rainv[$a][$y]; $maxrainMY = $year-$y; $maxrainMDD = $a; }
		}
		if ($rainv[$dd][$y] > $maxrainD) { $maxrainD = $rainv[$dd][$y]; $maxrainY = $year-$y; }
	}
}
for($i = 1; $i <=date('t', mktime(1,1,1,$m,1,$year-1)); $i++) { $maxMrain[$i] = floatval(max($rainv[$i])); }
$maxrainMD = max($maxMrain); $maxrainMDD = array_search($maxrainMD,$maxMrain); $maxrainMY = $year - array_search($maxrainMD,$rainv[$maxrainMDD]);
echo conv($rainv[$dd][1],2,1); ?></td>
<td class="td12" width="30%"><?php echo datefull($date_day), ' ', monthfull($date_month), ' ', $date_year-1; ?></td> </tr>			
<tr class="column-dark">
<td class="td12" width="45%">---</td>
<td class="td12" width="25%">---</td>
<td class="td12" width="30%">---</td> </tr>
<tr class="column-light">
<td class="td12" width="45%">Max Rain in 1hr Yesterday</td>
<td class="td12" width="25%"><?php $filex = file('wx18.html'); $mry = explode("=", $filex[15]); $dat = floatval($mry[1]); echo conv($dat,2,1); ?></td>
<td class="td12" width="30%"><?php $mryt = explode("=", $filex[17]); if($ystdyrain==0): echo "n/a"; else: echo $mryt[1]; endif; ?></td> </tr>
<tr class="column-dark">
<td class="td12" width="45%">Max Rain Rate Yesterday</td>
<td class="td12" width="25%"><?php if($maxrainrateyesthr > 5): echo conv3($maxrainrateyesthr,2,1); else: echo conv($maxrainrateyesthr,2,1); endif; ?>/h</td>
<td class="td12" width="30%"><?php if($ystdyrain==0): echo "n/a"; else: echo $maxrainrateyesttime; endif; ?></td> </tr>
<tr class="column-light">
<td class="td12" width="45%">---</td>
<td class="td12" width="25%">---</td>
<td class="td12" width="30%">---</td> </tr>
<tr class="column-dark">
<td class="td12" width="45%">This Month Max Daily Rain</td>
<td class="td12" width="25%"><?php echo $stbfix, '<acronym title="Proportion of monthly total: '; if($monthrn < 0.3) { echo 'n/a">'; }
	else { echo round(100*$mrecorddailyrain/$monthrn), '%">'; } echo conv($mrecorddailyrain,2,1), '</acronym>', $enbfix; ?></td>
<td class="td12" width="30%"><?php if(floatval($monthrn)==0): echo 'n/a'; else: echo 'Day ', $mrecorddailyrainday; endif; ?></td> </tr>
<tr class="column-light">
<td class="td12" width="45%">This Month Max Rain Rate</td>
<td class="td12" width="25%"><?php echo conv3($mrecordrainrateperhr,2,1); ?>/h</td> 
<td class="td12" width="30%"><?php if(floatval($monthrn)==0): echo 'n/a'; else: echo sprintf('%02d', $mrecordrainratehour) ,':', sprintf('%02d', $mrecordrainratemin), ', Day ', $mrecordrainrateday; endif; ?></td> </tr>
<tr class="column-dark">
<td class="td12" width="45%">This Month Max Rain in 1hr</td>
<td class="td12" width="25%"><?php echo conv($mhrrecordrainrate,2,1); ?></td>
<td class="td12" width="30%"><?php if(floatval($monthrn)==0): echo 'n/a'; else: echo sprintf('%02d', $mhrrecordrainratehour),':', sprintf('%02d', $mhrrecordrainratemin), ', Day ', $mhrrecordrainrateday; endif; ?></td> </tr>
<tr class="column-light">
<td class="td12" width="45%">This Month Longest Dry Spell</td>
<td class="td12" width="25%"><?php if($mrecorddaysnorain == 1) { $plur = ''; } else { $plur = 's'; } echo $mrecorddaysnorain, ' day', $plur; ?></td>
<td class="td12" width="30%"><?php if($mrecorddaysnorain == 0): echo 'n/a'; elseif($dayswithnorain == $mrecorddaysnorain): echo 'current'; else: echo 'Up to Day ', $mrecorddaysnorainday; endif; ?></td> </tr>
<tr class="column-dark">
<td class="td12" width="45%">This Month Longest Wet Spell</td>
<td class="td12" width="25%"><?php if($mrecorddayswithrain == 1) { $plur = ''; } else { $plur = 's'; } echo $mrecorddayswithrain, ' day', $plur; ?></td>
<td class="td12" width="30%"><?php if($mrecorddayswithrain == 0): echo 'n/a'; elseif($consecdayswithrain == $mrecorddayswithrain): echo 'current'; else: echo 'Up to Day ', $mrecorddayswithrainday; endif; ?></td> </tr>
<tr class="column-light">
<td class="td12" width="45%">Last Month Rain-to-date</td>
<td class="td12" width="25%"><?php echo conv($raintodatemonthago,2,1); ?></td>
<td class="td12" width="30%">1<sup>st</sup> - <?php echo datefull($date_day), ' ';
 $months = array('Jan', 'Feb', 'March', 'April', 'May', 'June', 'July', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'); echo $months[intval($date_month)-2]; ?></td> </tr>
<tr class="column-dark">
<td class="td12" width="45%">---</td>
<td class="td12" width="25%">---</td>
<td class="td12" width="30%">---</td> </tr>
<tr class="column-light">
<td class="td12" width="45%">This Year Max Daily Rain</td>
<td class="td12" width="25%"><?php echo conv($yrecorddailyrain,2,1); ?></td>
<td class="td12" width="30%"><?php echo datefull($yrecorddailyrainday), ' ', monthfull($yrecorddailyrainmonth); ?></td> </tr>
<tr class="column-dark">
<td class="td12" width="45%">This Year Max Rain Rate</td>
<td class="td12" width="25%"><?php echo conv3($yrecordrainrateperhr,2,1); ?>/h</td>
<td class="td12" width="30%"><?php echo sprintf('%02d', $yrecordrainratehour), ':', sprintf('%02d', $yrecordrainratemin), ', ', datefull($yrecordrainrateday), ' ', monthfull($yrecordrainratemonth); ?></td> </tr>
<tr class="column-light">
<td class="td12" width="45%">This Year Max Rain in 1hr</td>
<td class="td12" width="25%"><?php echo conv($yhrrecordrainrate,2,1); ?></td>
<td class="td12" width="30%"><?php echo sprintf('%02d', $yhrrecordrainratehour),':', sprintf('%02d', $yhrrecordrainratemin), ', ', datefull($yhrrecordrainrateday), ' ', monthfull($yhrrecordrainratemonth); ?></td> </tr>
<tr class="column-dark">
<td class="td12" width="45%">This Year Longest Dry Spell</td>
<td class="td12" width="25%"><?php if($yrecorddaysnorain == 1) { $plur = ''; } else { $plur = 's'; } echo $yrecorddaysnorain, ' day', $plur; ?></td>
<td class="td12" width="30%"><?php $spellstart = mktime(0,0,0,$yrecorddaysnorainmonth,$yrecorddaysnorainday-$yrecorddaysnorain);
if($yrecorddaysnorainmonth == date("m", $spellstart)) { $mspellstart = ''; } else { $mspellstart = monthfull(date("m", $spellstart)); }
 if($dayswithnorain == $yrecorddaysnorain): echo 'current'; else:
 echo datefull(date("j", $spellstart)), ' ', $mspellstart, ' - ', datefull($yrecorddaysnorainday-1), ' ', monthfull($yrecorddaysnorainmonth); endif; ?></td> </tr>
<tr class="column-light">
<td class="td12" width="45%">This Year Longest Wet Spell</td>
<td class="td12" width="25%"><?php if($yrecorddayswithrain == 1) { $plur = ''; } else { $plur = 's'; }  echo $yrecorddayswithrain, ' day', $plur; ?></td>
<td class="td12" width="30%"><?php $spellstart = mktime(0,0,0,$yrecorddayswithrainmonth,$yrecorddayswithrainday-$yrecorddayswithrain+1);
if($yrecorddayswithrainmonth == date("m", $spellstart)) { $mspellstart = ''; } else { $mspellstart = monthfull(date("m", $spellstart)); }
 if($consecdayswithrain == $yrecorddayswithrain): echo 'current'; else:
 echo datefull(date("j", $spellstart)), ' ', $mspellstart, ' - ', datefull($yrecorddayswithrainday), ' ', monthfull($yrecorddayswithrainmonth); endif; ?></td> </tr>
<tr class="column-dark">
<td class="td12" width="45%">Last Year Annual Rain-to-date</td>
<td class="td12" width="25%"><?php echo conv($raintodateyearago,2,1); ?></td>
<td class="td12" width="30%"><?php echo '1<sup>st</sup> Jan', ' - ', datefull($date_day), ' ', monthfull($date_month), ' ',$date_year-1; ?></td> </tr>
</table>

<br /><br />

<table class="table1" align="center" width="70%" cellpadding="5" cellspacing="0">
<tr><td class="td12" colspan="3" ><h3>All-time Records</h3></td></tr>
<tr class="table-top">
<td class="td12" colspan="1">Measure</td><td class="td12" colspan="1">Value</td><td class="td12" colspan="1">Time/Date</td></tr>
<tr class="column-light">
<td class="td12" width="35%">Wettest Day</td>
<td class="td12" width="30%"><?php echo conv($recorddailyrain,2,1); ?></td>
<td class="td12" width="35%"><?php echo datefull($recorddailyrainday), ' ', monthfull($recorddailyrainmonth), ' ', $recorddailyrainyear; ?></td> </tr>
<tr class="column-dark">
<td class="td12" width="35%">Highest Rain Rate</td>
<td class="td12" width="30%"><?php echo conv3($recordrainrateperhr,2,1); ?>/h</td>
<td class="td12" width="35%"><?php echo sprintf('%02d', $recordrainratehour), ':', sprintf('%02d', $recordrainratemin), ', ', datefull($recordrainrateday), ' ', monthfull($recordrainratemonth), ' ', $recordrainrateyear; ?></td> </tr>
<tr class="column-light">
<td class="td12" width="35%">Most Rain in 1hr</td>
<td class="td12" width="30%"><?php echo conv($hrrecordrainrate,2,1); ?></td>
<td class="td12" width="35%"><?php echo sprintf('%02d', $hrrecordrainratehour), ':', sprintf('%02d', $hrrecordrainratemin), ', ', datefull($hrrecordrainrateday), ' ', monthfull($hrrecordrainratemonth), ' ', $hrrecordrainrateyear; ?></td> </tr>
<tr class="column-dark">
<td class="td12" width="35%">Driest Month</td>
<td class="td12" width="30%"><?php $year = date("Y");
$years = 1 + ($year - 2009);
$minr = 100; $maxr = 0;
$report = date("F", mktime(0,0,0,$date_month,$date_day-1)).date("Y", mktime(0,0,0,$date_month,$date_day-1,$date_year)).'.htm';
for($y = 0; $y < $years ; $y ++) {
	$yx = $year - $y;	
	for($m = 0; $m < 12 ; $m ++) {       
		$filename = date('F', mktime(0,0,0,$m+1,1,$yx)) . $yx . ".htm";
		if(file_exists($filename) && $filename != $report) {
			$arr = file($filename);
			for ($i = 500; $i < 1200; $i++) {
				if(strpos($arr[$i],"all for month") > 0) { $totala = $arr[$i]; }
			}
			$arr2 = explode(" ", $totala);
			if ($minr > ($arr2[10])) { $minr = ($arr2[10]); $minrt = monthfull($m+1) . ' ' . $yx; }
			if ($maxr < ($arr2[10])) { $maxr = ($arr2[10]); $maxrt = monthfull($m+1) . ' ' . $yx; }
			if($m+1 == $date_month && $yx == $date_year-1) { $lymrain = $arr2[10]; }
			if($m+1 == date("n", mktime(0,0,0,$date_month-1)) && $yx == date("Y", mktime(0,0,0,$date_month-1,1,$date_year-1))) { $lymrain1 = $arr2[10]; }
			if($m+1 == date("n", mktime(0,0,0,$date_month-2)) && $yx == date("Y", mktime(0,0,0,$date_month-2,1,$date_year-1))) { $lymrain2 = $arr2[10]; }
		}
	}
}
echo conv($minr,2,1); ?></td>
<td class="td12" width="35%"> <?php echo $minrt; ?></td> </tr>
<tr class="column-light">
<td class="td12" width="35%">Wettest Month</td>
<td class="td12" width="30%"><?php echo conv($maxr,2,1); ?></td>
<td class="td12" width="35%"><?php echo $maxrt; ?></td> </tr>
<tr class="column-dark">
<td class="td12" width="35%">Longest Dry Spell</td>
<td class="td12" width="30%"><?php echo $recorddaysnorain; ?> days</td>
<td class="td12" width="35%">Ending <?php echo datefull($recorddaysnorainday), ' ', monthfull($recorddaysnorainmonth), ' ', $recorddaysnorainyear; ?></td> </tr>
<tr class="column-light">
<td class="td12" width="35%">Longest Wet Spell</td>
<td class="td12" width="30%"><?php echo $recorddayswithrain; ?> days</td>
<td class="td12" width="35%"><?php if($consecdayswithrain == $recorddayswithrain): echo 'current'; else: echo 'Ending ', datefull($recorddayswithrainday), ' ', monthfull($recorddayswithrainmonth), ' ', $recorddayswithrainyear; endif; ?></td> </tr>
<tr class="column-dark">
<td class="td12" width="35%">---</td>
<td class="td12" width="30%">---</td>
<td class="td12" width="35%">---</td> </tr>
<tr class="column-light">
<td class="td12" width="35%">Driest <?php echo $monthname; ?></td>
<td class="td12" width="30%"><?php echo conv($minrc,2,1); ?></td>
<td class="td12" width="35%"><?php echo $minrct; ?></td> </tr>
<tr class="column-dark">
<td class="td12" width="35%">Wettest <?php echo $monthname; ?></td>
<td class="td12" width="30%"><?php $splitwetcur = explode(" ", $wettestcurrmonth); echo conv($splitwetcur[0],2,1), '</td>
<td class="td12" width="35%">', floatval($splitwetcur[1]); ?></td> </tr>
<tr class="column-light">
<td class="td12" width="35%">Wettest <?php echo $monthname; ?> Day</td>
<td class="td12" width="35%"><?php $maxrainMdate = 'Day '.$maxrainMDD.', '.$maxrainMY; if($maxrainMD < $dayrn) { $maxrainMD = $dayrn; $maxrainMdate = 'Today'; } echo conv($maxrainMD,2,1); ?></td>
<td class="td12" width="30%"><?php echo $maxrainMdate; ?></td> </tr>
<tr class="column-dark">
<td class="td12" width="35%">Wettest <?php echo datefull($date_day), ' ', monthfull($date_month); ?></td>
<td class="td12" width="35%"><?php if($maxrainD < floatval($dayrn)) { $maxrainD = $dayrn; $maxrainY = 'Today'; } echo conv($maxrainD,2,1); ?></td>
<td class="td12" width="30%"><?php if($maxrainD < 0.1) { echo 'n/a'; } else { echo $maxrainY; } ?></td> </tr>
</table>

<br />
<p align="center"><b>Note 1:</b> Rain records began in February 2009<br />
<b>Note 2:</b> The minimum recordable rain (the rain guage resolution) is <?php if($unitT == 'C') { echo '0.25 mm'; } else { echo '0.01 in'; } ?><br />
<b>Note 3:</b> Figures in brackets refer to departure from <a href="wxaverages.php" title="Long-term NW3 climate averages">average conditions</a><br />
<b>Note 4:</b> Rain rate records are manually checked, and changed if necessary, due to occasional issues with the software. Inital high readings may well be corrected at a later date.<br />
</p> 

<table class="table1" align="center" width="50%" cellpadding="5" cellspacing="0">
<tr><td class="td12" colspan="3" ><h3>Past Year Monthly Totals</h3></td></tr>
<tr class="table-top"><td class="td12">Month</td><td class="td12">Total</td><td class="td12">Anomaly</td></tr>
<?php $mnames = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
for($i = 0; $i < 12; $i++) {
	if($i % 2 == 0) { $style = 'column-light'; } else { $style = 'column-dark'; } $hlite = ''; $yr2 = '';
	if($i+1 == intval($date_month)) { $raintots[$i] = $lymrain; $yr2 = $date_year-1; }
	if($i+1 == intval($date_month)-1) { $hlite = 'border-bottom:3px solid #8181F7'; }
	$yr1 = ''; if($i == 0) { $yr1 = $date_year; } if($yr1 == $yr2) { $yr1 = ''; }
	if(date('m') < 2) { $yr1 = ''; }
	echo '<tr class="', $style, '"><td style="', $hlite, '" class="td12">', $mnames[$i], ' ', $yr1, $yr2, '</td><td style="', $hlite, '" class="td12">',
	conv($raintots[$i],2,1), '</td><td style="', $hlite, '" class="td12">', conv4($raintots[$i]/$rainav[$i],0,0), '</td></tr>';
} 
$style_end = 'border-top:4px solid #0431B4;border-bottom:4px solid #0431B4;font-size:110% '; ?>
<tr class="column-light">
	<td style="<?php echo $style_end; ?>" class="td12">12-month Total</td>
	<td style="<?php echo $style_end; ?>" class="td12"><?php echo conv(array_sum($raintots),2,1); ?></td>
	<td style="<?php echo $style_end; ?>" class="td12"><?php echo round(array_sum($raintots)*100/array_sum($rainav)); ?>%</td>
</tr>
</table>

<br />

<table class="table1" align="center" width="50%" cellpadding="5" cellspacing="0">
<tr><td class="td12" colspan="3" ><h3>Past Year Seasonal Totals</h3></td></tr>
<tr class="table-top"><td class="td12">Season</td><td class="td12">Total</td><td class="td12">Anomaly</td></tr>
<?php
$alttots = array($nowrainfeb+$lymrain+$lymrain1, $lymrain+$lymrain1+$lymrain2, $lymrain+$nowrainapr+$nowrainmay, $lymrain+$lymrain1+$nowrainmay, $lymrain+$lymrain1+$lymrain2,
		$lymrain+$nowrainjul+$nowrainaug, $lymrain+$lymrain1+$nowrainaug, $lymrain+$lymrain1+$lymrain2,$lymrain+$nowrainoct+$nowrainnov, $lymrain+$lymrain1+$nowrainnov,
		$lymrain+$lymrain1+$lymrain2, $lymrain+$nowrainjan+$nowrainfeb);
$sraintots = array($nowrainjan + $nowraindec + $nowrainfeb, $nowrainmar + $nowrainapr + $nowrainmay, $nowrainjun + $nowrainjul + $nowrainaug, $nowrainoct + $nowrainnov + $nowrainsep);
$srainav = array($rainav[0]+$rainav[1]+$rainav[11], $rainav[2]+$rainav[3]+$rainav[4], $rainav[5]+$rainav[6]+$rainav[7], $rainav[8]+$rainav[9]+$rainav[10]);
for($i = 0; $i < 4; $i++) {
	if($i % 2 == 0) { $style = 'column-light'; } else { $style = 'column-dark'; } $hlite = '';
	if($i+1 == $season) { $sraintots[$i] = $alttots[intval($date_month)-1]; }
	if($i+1 == $season-1) { $hlite = 'border-bottom:3px solid #8181F7'; }
	if($i+1 < $season || date('n') == 12) { $nwint = $date_year; } else { $nwint = $date_year-1; } $dfo1 = date('Y')-2001; $dfo2 = date('Y')-2000; $dfo3 = date('Y')-2002; 
	if(date('n') > 2) { $wint = $dfo1 .'/' .$dfo2; } else { $wint = $dfo3 .'/' .$dfo1; } $yr3 = array($wint, $nwint, $nwint, $nwint);
	echo '<tr class="', $style, '"><td style="', $hlite, '" class="td12">', $snames[$i], ' ', $yr3[$i], '</td><td style="', $hlite, '" class="td12">',
	conv($sraintots[$i],2,1), '</td><td style="', $hlite, '" class="td12">', conv4($sraintots[$i]/$srainav[$i],0,0), '</td></tr>';
} ?>
</table>

<p align="center"><a href="wxhist12.php" title="<?php echo $year; ?>daily rain totals"><b>View daily totals for the past year</b></a><br />
<a href="wxsumhist12.php" title="All-time monthly rain totals"><b>View monthly totals for all years on record</b></a></p>

</div>

<!-- ##### Footer ##### -->
<? require('footer.php');// for($i = 1; $i <32; $i++) { echo max($rainv[$i]), ', '; } ?>  

 </body>
</html>