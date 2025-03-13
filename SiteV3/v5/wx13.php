<?php $allDataNeeded = true;
	require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php
	$file = 13; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Wind Detail</title>
	<meta name="description" content="Detailed latest wind speed and direction data and records from NW3 weather station" />

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
	<?php require $root.'WindTags.php'; ?>

<?php
///////////////////////////////////////////////////////WIND TAGS////////////////////////////////////////////////////////////
$windav_curr = $windav[$dmonth-1];
$windall = array();
for($y = 2010; $y <= $dyear; $y++) { //Wind speed tags
	$winds[$y] = $DATA[9][$y];
	$wind2[$y] = MDtoZ($winds[$y]);
	$wind3[$y] = MDtoMsummary($winds[$y]);
	$windall = array_merge($windall, $wind2[$y]);
}
$totCnt = count($windall);

$calmest31 = 99; $calmest365 = 99;
for($i = 0; $i < $totCnt; $i++) {
	$cumwd31 += floatval($windall[$i]);
	if($i > 30) { $cumwd31 -= floatval($windall[$i-30]); }
	if($cumwd31 > $windiest31) { $windiest31 = $cumwd31; $windiest31_end = $i; }
	if($cumwd31 < $calmest31 && $i > 30) { $calmest31 = $cumwd31; $calmest31_end = $i; }
	$cumwd365 += floatval($windall[$i]);
	if($i > 364) { $cumwd365 -= floatval($windall[$i-364]); }
	if($cumwd365 > $windiest365) { $windiest365 = $cumwd365; $windiest365_end = $i; }
	if($cumwd365 < $calmest365 && $i > 364) { $calmest365 = $cumwd365; $calmest365_end = $i; }
}

$windiest31date = 'Ending '.today(true,true,true,null,daytotime($windiest31_end-1));
$calmest31date = 'Ending '.today(true,true,true,null,daytotime($calmest31_end-1));
$windiest365date = 'Ending '.today(true,true,true,null,daytotime($windiest365_end-1));
$calmest365date = 'Ending '.today(true,true,true,null,daytotime($calmest365_end-1));

$wind365a = $wind2[$dyear] + $wind2[$dyear-1];
$windtots = array_merge($wind3[$dyear] + $wind3[$dyear-1]);
//$day_wind_last_year = $winds[$dyear-1][$dmonth][$dday];
$monthwd = mean($winds[$dyear][$dmonth]);
$yearwd = mean($wind2[$dyear]);
$yestwd = $ystdwind = $wind2[$yr_yest][$dz_yest+1];

$wmindailywind = 99;
for($d = 0; $d < 7; $d++) {
	$avwindweek += $windall[$totCnt-1-$d]/7;
	if($windall[$totCnt-1-$d] > $wmaxdailywind) { $wmaxdailywind = $windall[$totCnt-1-$d];
	$wmaxdailywinddate = today(null,true,true,null,mkday($dday-$d)); }
	if($windall[$totCnt-1-$d] < $wmindailywind) { $wmindailywind = $windall[$totCnt-1-$d];
	$wmindailywinddate = today(null,true,true,null,mkday($dday-$d)); }
}
for($d = 0; $d < 31; $d++) { $wind31 += $windall[$totCnt-1-$d]/31; }
$wind365 = mean($wind365a);
//for($d = 1; $d <= count($winds[$dyear][$dmonth]); $d++) { if($winds[$dyear][$dmonth][$d] > 0.1) { $winddays_month += 1; } }
//for($d = 1; $d <= count($wind2[$dyear]); $d++) { if($wind2[$dyear][$d] > 0.1) { $winddays_year += 1; } }
//for($d = 1; $d <= count($winds[$dyear][$dmonth]); $d++) { $windtodmonthago += $winds[$yr_yest][date('n',mkdate($dmonth-1,1,$dyear))][$d]; }
//for($d = 1; $d <= count($wind2[$dyear]); $d++) { $windtodyearago += $wind2[$dyear-1][$d]; }

for($e=0; $e < $sc; $e++) { $total += floatval($windtots[date('n',mkdate($e+$s_offset, 1))-1]); }
for($f=0; $f < 3; $f++) { $s_avgW += floatval($windav[date('n',mkdate($f+$s_offset, 1))-1]); }
for($g=1; $g < $sc; $g++) { $s_avg_wd += floatval($windav[date('n',mkdate($g+$s_offset, 1))-1]); } $s_avg_wd += $windav_curr/date('t')*$dday;

$lymwind = $wind3[$dyear-1][$dmonth];
$lymwind1 = $wind3[date('Y',mkdate($dmonth-1,1,$dyear-1))][date('n',mkdate($dmonth-1,1,$dyear-1))];
$lymwind2 = $wind3[date('Y',mkdate($dmonth-2,1,$dyear-1))][date('n',mkdate($dmonth-2,1,$dyear-1))];

$monthwdF = conv($monthwd,4) .' ('. conv($monthwd-$windav_curr,4,0,true).')';
for($m = 0; $m < $dmonth; $m++) { $curryearwindav += $windav[$m]/$dmonth; }
$yearwdF = conv($yearwd,4) .' ('. conv($yearwd-$curryearwindav,4,0,true).')';
//$seasonwdF = conv($total,2) .' ('.acronym('Of final expected: '.round(100*$total/$s_avgW). '%', round(100*$total/$s_avg_wd). '%', true).')';
//$winddays_monthF = acronym('Proportion of days: '.round(100*$winddays_month/($dday)). '%', $winddays_month, true);
//$winddays_yearF = acronym('Proportion of days: '.round(100*$winddays_year/(date('z')+1)). '%', $winddays_year, true);

for($y = 2010; $y <= $dyear; $y++) {
	$maxwindDa[$y] = $winds[$y][$dmonth][$dday];
	if(is_array($winds[$y][$dmonth])) { $maxwindMDa[$y] = max($winds[$y][$dmonth]); $maxwindMDdatea[$y] = array_search($maxwindMDa[$y], $winds[$y][$dmonth]);
	$minwindMDa[$y] = min($winds[$y][$dmonth]); $minwindMDdatea[$y] = array_search($minwindMDa[$y], $winds[$y][$dmonth]); }
	$monthwd_max_curra[$y] = $monthwd_min_curra[$y] = $wind3[$y][$dmonth];
	$monthwd_mina[$y] = min($wind3[$y]); $monthwd_mindatea[$y] = array_search($monthwd_mina[$y], $wind3[$y]);
	$monthwd_maxa[$y] = max($wind3[$y]); $monthwd_maxdatea[$y] = array_search($monthwd_maxa[$y], $wind3[$y]);
	$maxwindYDa[$y] = max($wind2[$y]); $maxwindYDdatea[$y] = array_search($maxwindYDa[$y], $wind2[$y]);
	$minwindYDa[$y] = min($wind2[$y]); $minwindYDdatea[$y] = array_search($minwindYDa[$y], $wind2[$y]);
}

$maxwindD = max($maxwindDa);
$maxwindDdate = today(array_search($maxwindD, $maxwindDa));
$maxwindMD = max($maxwindMDa);
$maxwindMDdate = today(array_search($maxwindMD, $maxwindMDa),null,$maxwindMDdatea[array_search($maxwindMD, $maxwindMDa)]);
$minwindD = min( array_filter($maxwindDa, 'clearblank') );
$minwindDdate = today(array_search($minwindD, $maxwindDa));
$minwindMD = min($minwindMDa);
$minwindMDdate = today(array_search($minwindMD, $minwindMDa),null,$minwindMDdatea[array_search($minwindMD, $minwindMDa)]);

$monthwd_max_curr = max($monthwd_max_curra);
$monthwd_max_currdate = today(array_search($monthwd_max_curr, $monthwd_max_curra),null,null,true);
$monthwd_min_curr = min($monthwd_min_curra);
$monthwd_min_currdate = today(array_search($monthwd_min_curr, $monthwd_min_curra),null,null,true);

$monthwd_min = min($monthwd_mina);
$monthwd_mindate = today(array_search($monthwd_min, $monthwd_mina), $monthwd_mindatea[array_search($monthwd_min, $monthwd_mina)],null,true);
$monthwd_max = max($monthwd_maxa);
$monthwd_maxdate = today(array_search($monthwd_max, $monthwd_maxa), $monthwd_maxdatea[array_search($monthwd_max, $monthwd_maxa)],null,true);

$yrmonthwd_max = $monthwd_maxa[$dyear];
$yrmonthwd_maxdate = today(null,$monthwd_maxdatea[$dyear],null,true);
$yrmonthwd_min = $monthwd_mina[$dyear];
$yrmonthwd_mindate = today(null,$monthwd_mindatea[$dyear],null,true);

$maxwindYD = max($maxwindYDa);
$tempyr = array_search($maxwindYD, $maxwindYDa);
$maxwindYDdate = today($tempyr, true_z($maxwindYDdatea[$tempyr], $tempyr), true_z($maxwindYDdatea[$tempyr], $tempyr, 'j'));
$minwindYD = min($minwindYDa);
$tempyr = array_search($minwindYD, $minwindYDa);
$minwindYDdate = today($tempyr, true_z($minwindYDdatea[$tempyr], $tempyr), true_z($minwindYDdatea[$tempyr], $tempyr, 'j'));

$ymaxdailywind = $maxwindYDa[$dyear];
$ymaxdailywinddate = today(null, true_z($maxwindYDdatea[$dyear], $dyear), true_z($maxwindYDdatea[$dyear], $dyear, 'j'));
$mmaxdailywind = $maxwindMDa[$dyear];
$mmaxdailywinddate = today(null,null,$maxwindMDdatea[$dyear]);
$ymindailywind = $minwindYDa[$dyear];
$ymindailywinddate = today(null, true_z($minwindYDdatea[$dyear], $dyear), true_z($minwindYDdatea[$dyear], $dyear, 'j'));
$mmindailywind = $minwindMDa[$dyear];
$mmindailywinddate = today(null,null,$minwindMDdatea[$dyear]);

unset($winds,$wind2,$wind3,$windall); //destroy variables


$gustall = $gustallt = array();
for($y = 2010; $y <= $dyear; $y++) { //gust speed tags
	$gusts[$y] = $DATA[11][$y];
	$gust2[$y] = MDtoZ($gusts[$y]);
//	$gustt[$y] = datt('gust', $y, true, true);
	$gustall = array_merge($gustall, $gust2[$y]);
//	if(is_array($gustt[$y])) { $gustallt = array_merge($gustallt, $gustt[$y]); }
}

//$maxgustyest = $gustall[count($gustall)-2];
//$maxgustyestdate = $gustallt[count($gustallt)-2];
$maxgustweek = 0; for($d = 1; $d <= 7; $d++) { $gustweek = $gustall[count($gustall)-$d]; if($gustweek > $maxgustweek) { $maxgustweek = $gustweek; $maxgustweekday = $d; } }
$maxgustweekdate = today(null,true,true,null,mkday($dday-$maxgustweekday+1));
//$maxgustweekdate = $gustallt[count($gustallt)-$maxgustweekday] . ', ' . $maxgustweekdate;

for($y = 2010; $y <= $dyear; $y++) {
	$maxgustDa[$y] = $gusts[$y][$dmonth][$dday];
	if(is_array($gusts[$y][$dmonth])) { $maxgustMDa[$y] = max($gusts[$y][$dmonth]); $maxgustMDdatea[$y] = array_search($maxgustMDa[$y], $gusts[$y][$dmonth]); }
	$recorddailygusta[$y] = max($gust2[$y]); $recorddailygustdatea[$y] = array_search($recorddailygusta[$y], $gust2[$y]);
}

$maxgustD = max($maxgustDa);
$maxgustDdate = today(array_search($maxgustD, $maxgustDa));
$maxgustMD = max($maxgustMDa);
$maxgustMDdate = today(array_search($maxgustMD, $maxgustMDa),null,$maxgustMDdatea[array_search($maxgustMD, $maxgustMDa)]);

$recorddailygust = max($recorddailygusta);
$tempyr = array_search($recorddailygust, $recorddailygusta);
$recorddailygustdate = today($tempyr, true_z($recorddailygustdatea[$tempyr], $tempyr), true_z($recorddailygustdatea[$tempyr], $tempyr, 'j'));
//$recorddailygustdate = $gustt[$tempyr][$recorddailygustdatea[$tempyr]] . ', ' . $recorddailygustdate;
$yrecorddailygust = $recorddailygusta[$dyear];
$yrecorddailygustdate = today(null, true_z($recorddailygustdatea[$dyear], $dyear), true_z($recorddailygustdatea[$dyear], $dyear, 'j'));
//$yrecorddailygustdate = $gustt[$dyear][$recorddailygustdatea[$dyear]] . ', ' . $yrecorddailygustdate;
$mrecorddailygust = $maxgustMDa[$dyear];
$mrecorddailygustdate = today(null,null,$maxgustMDdatea[$dyear]);
//$mrecorddailygustdate = $gustt[$dyear][1+date('z',mkday($maxgustMDdatea[$dyear]))] . ', ' . $mrecorddailygustdate;

unset($gusts,$gust2,$gustall,$gustallt,$gustt); //destroy variables


$speedall = $speedallt = array();
for($y = 2010; $y <= $dyear; $y++) { //speed speed tags
	$speed[$y] = $DATA[10][$y];
	$speed2[$y] = MDtoZ($speed[$y]);
//	$speedt[$y] = datt('wmax', $y, true, true);
	$speedall = array_merge($speedall, $speed2[$y]);
//	if(is_array($speedt[$y])) { $speedallt = array_merge($speedallt, $speedt[$y]); }
}

//$maxspeedyest = $speedall[count($speedall)-2];
//$maxspeedyestdate = $speedallt[count($speedallt)-2];
$maxspeedweek = 0; for($d = 1; $d <= 7; $d++) { $speedweek = $speedall[count($speedall)-$d]; if($speedweek > $maxspeedweek) { $maxspeedweek = $speedweek; $maxspeedweekday = $d; } }
$maxspeedweekdate = today(null,true,true,null,mkday($dday-$maxspeedweekday+1));
//$maxspeedweekdate = $speedallt[count($speedallt)-$maxspeedweekday] . ', ' . $maxspeedweekdate;

for($y = 2010; $y <= $dyear; $y++) {
	$maxspeedDa[$y] = $speed[$y][$dmonth][$dday];
	if(is_array($speed[$y][$dmonth])) { $maxspeedMDa[$y] = max($speed[$y][$dmonth]); $maxspeedMDdatea[$y] = array_search($maxspeedMDa[$y], $speed[$y][$dmonth]); }
	$recorddailyspeeda[$y] = max($speed2[$y]); $recorddailyspeeddatea[$y] = array_search($recorddailyspeeda[$y], $speed2[$y]);
}

$maxspeedD = max($maxspeedDa);
$maxspeedDdate = today(array_search($maxspeedD, $maxspeedDa));
$maxspeedMD = max($maxspeedMDa);
$maxspeedMDdate = today(array_search($maxspeedMD, $maxspeedMDa),null,$maxspeedMDdatea[array_search($maxspeedMD, $maxspeedMDa)]);

$recorddailyspeed = max($recorddailyspeeda);
$tempyr = array_search($recorddailyspeed, $recorddailyspeeda);
$recorddailyspeeddate = today($tempyr, true_z($recorddailyspeeddatea[$tempyr], $tempyr), true_z($recorddailyspeeddatea[$tempyr], $tempyr, 'j'));
//$recorddailyspeeddate = $speedt[$tempyr][$recorddailyspeeddatea[$tempyr]] . ', ' . $recorddailyspeeddate;
$yrecorddailyspeed = $recorddailyspeeda[$dyear];
$yrecorddailyspeeddate = today(null, true_z($recorddailyspeeddatea[$dyear], $dyear), true_z($recorddailyspeeddatea[$dyear], $dyear, 'j'));
//$yrecorddailyspeeddate = $speedt[$dyear][$recorddailyspeeddatea[$dyear]] . ', ' . $yrecorddailyspeeddate;
$mrecorddailyspeed = $maxspeedMDa[$dyear];
$mrecorddailyspeeddate = today(null,null,$maxspeedMDdatea[$dyear]);
//$mrecorddailyspeeddate = $speedt[$dyear][1+date('z',mkday($maxspeedMDdatea[$dyear]))] . ', ' . $mrecorddailyspeeddate;

unset($speed,$speed2,$speedall,$speedallt,$speedt); //destroy variables


$wd10all = $wd10allt = array();
for($y = 2010; $y <= $dyear; $y++) { //wd10 speed tags
	$wd10[$y] = $DATA[28][$y];
	$wd102[$y] = MDtoZ($wd10[$y]);
	if(is_array($wd102[$y])) { $wd10all = array_merge($wd10all, $wd102[$y]); }
}

$maxwd10week = 0; for($d = 1; $d <= 7; $d++) { $wd10week = $wd10all[count($wd10all)-$d]; if($wd10week > $maxwd10week) { $maxwd10week = $wd10week; $maxwd10weekday = $d; } }
$maxwd10weekdate = today(null,true,true,null,mkday($dday-$maxwd10weekday+1));
//$maxwd10weekdate = $wd10allt[count($wd10allt)-$maxwd10weekday] . ', ' . $maxwd10weekdate;

for($y = 2010; $y <= $dyear; $y++) {
	$maxwd10Da[$y] = $wd10[$y][$dmonth][$dday];
	if(is_array($wd10[$y][$dmonth])) { $maxwd10MDa[$y] = max($wd10[$y][$dmonth]); $maxwd10MDdatea[$y] = array_search($maxwd10MDa[$y], $wd10[$y][$dmonth]); }
	if(is_array($wd102[$y])) { $recorddailywd10a[$y] = max($wd102[$y]); $recorddailywd10datea[$y] = array_search($recorddailywd10a[$y], $wd102[$y]); }
}

$maxwd10D = max($maxwd10Da);
$maxwd10Ddate = today(array_search($maxwd10D, $maxwd10Da));
$maxwd10MD = max($maxwd10MDa);
$maxwd10MDdate = today(array_search($maxwd10MD, $maxwd10MDa),null,$maxwd10MDdatea[array_search($maxwd10MD, $maxwd10MDa)]);

$recorddailywd10 = max($recorddailywd10a);
$tempyr = array_search($recorddailywd10, $recorddailywd10a);
$recorddailywd10date = today($tempyr, true_z($recorddailywd10datea[$tempyr], $tempyr), true_z($recorddailywd10datea[$tempyr], $tempyr, 'j'));
//$recorddailywd10date = $wd10t[$tempyr][$recorddailywd10datea[$tempyr]] . ', ' . $recorddailywd10date;
$yrecorddailywd10 = $recorddailywd10a[$dyear];
$yrecorddailywd10date = today(null, true_z($recorddailywd10datea[$dyear], $dyear), true_z($recorddailywd10datea[$dyear], $dyear, 'j'));
//$yrecorddailywd10date = $wd10t[$dyear][$recorddailywd10datea[$dyear]] . ', ' . $yrecorddailywd10date;
$mrecorddailywd10 = $maxwd10MDa[$dyear];
$mrecorddailywd10date = today(null,null,$maxwd10MDdatea[$dyear]);
//$mrecorddailywd10date = $wd10t[$dyear][1+date('z',mkday($maxwd10MDdatea[$dyear]))] . ', ' . $mrecorddailywd10date;

unset($wd10,$wd102,$wd10all); //destroy variables

/**
 * calculate a real-time trend average for a weather type (taken from 24hr data log)
 * @param int $type index of column in customlog
 * @param int $length multiple of 5 mins, the period to average over
 * @return float mean
 */
function trendAv($type, $length) {
	global $HR24;
	$av = 0;
	for($i = 0; $i <= $length; $i += 5) {
		$av += $HR24['trend'][$i][$type];
	}
	return $av / ($i / 5 + 1);
}

$dirs30 = array();
$dirs60 = array();
$bypass = array();
for($i = 0; $i <= 60; $i+=5) {
	$bypass[] = 3; //don't let wdir mean reject based on low wind speed, in this case
	$dirs60[] = $HR24['trend'][$i]['wdir'];
	if($i <= 30) $dirs30[] = $HR24['trend'][$i]['wdir'];
}
//give them a little more data than strictly accurate -> better averaging performance
$avwind30mins = trendAv('wind', 40);
$avwind60mins = trendAv('wind', 80);
$avwind24 = $HR24['mean']['wind'];
$dir10 = $HR24['trend'][$i]['wdir'];
$dir30 = wdirMean($dirs30, $bypass);
$dir60 = wdirMean($dirs60, $bypass);
$dir24hr = $HR24['mean']['wdir'];
$dirmonth = wdirMean($DATA[12][$dyear][$dmonth], $DATA[9][$dyear][$dmonth]);
$beaufortnum = bft($wind);
?>

<h1>Detailed Wind Data</h1>

<?php
table(null, '30%" align="left', 5);
tableHead("Current", 2);
tr(); td("Measure", "td13", "59%"); td("Value", "td13", "41%"); tr_end();
$measures = array('Gust','Max Gust Last Hour','Speed', '10-min Speed','30-min Speed','1hr Speed', '24hr Speed','Beaufort Speed','Direction',
	'10-min Direction','30-min Direction','1-hr Direction', '24-hr Direction','---','Day Average Speed', 'Week Average Speed','Month Average Speed','Year Average Speed',
	'31-day Speed','365-day Speed','Day Average Direction', 'Month Mean Direction');
$values = array($gust,$maxgsthr,$wind, $w10m,$avwind30mins,$avwind60mins, $avwind24, $beaufortnum, degname($wdir),
	$dir10,$dir30,$dir60, $HR24['mean']['wdir'],'---',$NOW['mean']['wind'], $avwindweek,$monthwdF,$yearwdF,
	$wind31,$wind365,$dir24hr, $dirmonth);
//$order = array(2,1,0,3,4,5,6,7,8);
$conv = array(4,4,4, 4,4,4, 4,false,false,
	4.5,4.5,4.5, 4.5,false,4, 4,false,false,
	4,4,4.5, 4.5);
for($r = 0; $r < count($measures); $r++) {
	if($r % 2 == 0) { $colcol = 'light'; } else { $colcol = 'dark'; }
	tr("row".$colcol);
	td($measures[$r], "td13");
	td(conv($values[$r],$conv[$r]), "td13");
	tr_end();
}
table_end();
?>

<?php
$YEST = dailyData( date('Ymd', mkdate($mon_yest, $day_yest, $yr_yest)) );

table(null, '69%" align="center', 3);
tableHead("Current Extremes", 6);
tr();
$measures = array('Max Gust', 'Max Windspeed', 'Max 10-min Speed', 'Calmest Day', 'Windiest Day');
$headings = array('Measure', 'Today', 'Yesterday', 'Week', 'Month', 'Year');
$valuesD = array($NOW['max']['gust'], $NOW['max']['wind'], $NOW['max']['w10m'],'','');
$valuesB = array($YEST['max']['gust'], $YEST['max']['wind'], $YEST['max']['w10m'],'','');
$valuesW = array($maxgustweek, $maxspeedweek, $maxwd10week, $wmindailywind, $wmaxdailywind);
$valuesM = array($mrecorddailygust, $mrecorddailyspeed, $mrecorddailywd10, $mmindailywind, $mmaxdailywind);
$valuesY = array($yrecorddailygust, $yrecorddailyspeed, $yrecorddailywd10, $ymindailywind, $ymaxdailywind);
$timesD = array($NOW['timeMax']['gust'], $NOW['timeMax']['wind'], $NOW['timeMax']['w10m'],'','');
$timesB = array($YEST['timeMax']['gust'], $YEST['timeMax']['wind'], $YEST['timeMax']['w10m'],'','');
$timesW = array($maxgustweekdate, $maxspeedweekdate, $maxwd10weekdate, $wmindailywinddate, $wmaxdailywinddate);
$timesM = array($mrecorddailygustdate, $mrecorddailyspeeddate, $mrecorddailywd10date, $mmindailywinddate, $mmaxdailywinddate);
$timesY = array($yrecorddailygustdate, $yrecorddailyspeeddate, $yrecorddailywd10date, $ymindailywinddate, $ymaxdailywinddate);
//$order = array(2,1,0,3,4,5,6,7,8);
$conv = array(4,4,4,4,4);
for($h = 0; $h < count($headings); $h++) { echo '<td class="td13">', $headings[$h], '</td>'; }
echo '</tr>';
for($r = 0; $r < count($measures); $r++) {
	if($r % 2 == 0) { $colcol = 'light'; } else { $colcol = 'dark'; }
	echo '<tr class="row', $colcol, '">
		<td class="td13" width="15%">', $measures[$r], '</td>
		<td class="td13" width="17%"><b>'; if($valuesD[$r] == '') { echo '-</b>'; } else {
			echo conv($valuesD[$r],$conv[$r]), '</b><br />', $timesD[$r]; } echo '</td>
		<td class="td13" width="17%"><b>'; if($valuesB[$r] == '') { echo '-</b>'; } else {
			echo conv($valuesB[$r],$conv[$r]), '</b><br />', $timesB[$r]; } echo '</td>
		<td class="td13" width="17%"><b>', conv($valuesW[$r],$conv[$r]), '</b><br />', $timesW[$r], '</td>
		<td class="td13" width="17%"><b>', conv($valuesM[$r],$conv[$r]), '</b><br />', $timesM[$r], '</td>
		<td class="td13" width="17%"><b>', conv($valuesY[$r],$conv[$r]), '</b><br />', $timesY[$r], '</td>
		</tr>';
}
table_end();
?>

<table width="69%" align="center" cellpadding="15" cellspacing="0">
<tr><td>
<img align="right" src="graph12.php?type=wmean&amp;x=550&amp;y=320&amp;lta" width="550" height="320" alt="12monthwind" />
</td></tr>

<tr><td align="center">
<b>Note 1:</b> Valid wind records began in August 2009.<br />
<b>Note 2:</b> Figures in brackets refer to departure from <a href="wxaverages.php" title="Long-term NW3 climate averages">average conditions</a>.<br />
</td></tr>

<tr><td align="center">
<b>A note on "Speed" vs. "Gust":</b>
"Speed" is the windspeed sampled over a one minute period,
"Gust" is the windspeed sampled over a 14 second period.<br /><br />
</td></tr>
</table>

<?php
table(null, '80%" align="center', 5);
tableHead("Records", 4);
tr();
$headings = array('Measure', 'Overall', $monthname, datefull($dday) . ' ' .monthfull($dmonth));
//$measures = array();
$order = array(0,1,2,3,4);
$valuesD = array($maxgustD, $maxspeedD, $maxwd10D, $minwindD, $maxwindD);
$valuesM = array($maxgustMD, $maxspeedMD, $maxwd10MD, $minwindMD, $maxwindMD);
$valuesA = array($recorddailygust, $recorddailyspeed, $recorddailywd10, $minwindYD, $maxwindYD);
$timesD = array($maxgustDdate, $maxspeedDdate, $maxwd10Ddate, $minwindDdate, $maxwindDdate);
$timesM = array($maxgustMDdate, $maxspeedMDdate, $maxwd10MDdate, $minwindMDdate, $maxwindMDdate);
$timesA = array($recorddailygustdate, $recorddailyspeeddate, $recorddailywd10date, $minwindYDdate, $maxwindYDdate);
for($h = 0; $h < count($headings); $h++) { echo '<td class="td13" colspan="1">', $headings[$h], '</td>'; }
echo '</tr>';
for($r = 0; $r < count($measures); $r++) {
	if($r % 2 == 0) { $colcol = 'light'; } else { $colcol = 'dark'; }
	echo '<tr class="row', $colcol, '">
		<td class="td13" width="25%">', $measures[$order[$r]], '</td>
		<td class="td13" width="25%"><b>', conv($valuesA[$order[$r]],$conv[$order[$r]]), '</b><br />', $timesA[$order[$r]], '</td>
		<td class="td13" width="25%"><b>', conv($valuesM[$order[$r]],$conv[$order[$r]]), '</b><br />', $timesM[$order[$r]], '</td>
		<td class="td13" width="25%"><b>'; if($valuesD[$order[$r]] == '') { echo '-</b>'; } else {
			echo conv($valuesD[$order[$r]],$conv[$order[$r]]), '</b><br />', $timesD[$order[$r]]; } echo '</td>
		</tr>';
}
?>
</table>

<p align="center"><b>NB: </b>Daily wind speed maxima &amp; averages for the current year (and all other years on record) can be viewed
<a href="wxdataday.php?vartype=wmean" title="<?php echo $year; ?> windspeeds"> here</a></p>

<hr />
<h2>Wind roses and graphs</h2>

<img src="/rose_month.png" alt="windrose month" title="Current month-to-date windrose" width="432" height="460" />
<img src="/rose_year.png" alt="windrose year" title="Current year-to-date windrose" width="432" height="460" />
<p align="center"><a href="/windrose_viewer.php">See wind roses for all months, days and years</a></p>

<h3>Latest wind charts</h3>
<img src="graph31.php?type=wmean&amp;x=800&amp;y=400" alt="31-day wind speed graph" width="800" height="400" />
<img style="margin:5px" src="graphdayA.php?type1=wind&type2=wdir&amp;x=800&amp;y=400" alt="Last 24hrs wind speed and direction" width="800" height="400" />
<img style="margin:5px" src="graph_daily_trend.php?x=845&y=450&type=wmean&year=<?php echo $yr_yest ?>" alt="daily wind speed this year" />

<h3>All time wind rose for nw3</h3>
<img style="margin:8px" src="/rose_all.png" alt="windrose all time" title="All-time-to-date windrose" width="800" height="820" />

</div>

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>
 </body>
</html>