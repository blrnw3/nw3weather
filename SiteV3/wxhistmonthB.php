<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php 
	$file = 86;
	$subfile = true;
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Monthly Breakdowns</title>

	<meta name="description" content="Detailed historical monthly breakdown reports showing summary weather data for each day of the month" />

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

<?php
$typeconv = $typeconv2 = $typeconv;

if(isset($_GET['month'])) { $mproc = intval($_GET['month']); } else { $mproc = $mon_yest; }
if(isset($_GET['year'])) { $yproc = $_GET['year']; } else { $yproc = $yr_yest; }
$sproc1 = mkdate($mproc,1,$yproc); $dim = date('t',$sproc1); $sproc = mkdate($mproc,$dim,$yproc);
if($sproc >= mktime(0,0,0)) { $sproc = mktime()-3600*24; $dim = $day_yest; $num_adv = 31-$dim; }
if($sproc < mkdate(2,1,2009) || $sproc - mktime() > 3700*$dim) { $toofar = true; $yproc = $dyear; }
$dzed = date('z',$sproc)+1;
$fdat = $fullpath."dat".$yproc.".csv";
$fdatt = $fullpath."datt".$yproc.".csv";
$fdatm = $fullpath."datm".$yproc.".csv";
$dsun = file($root.'maxsun.csv');
$dtfanom = file($root.'tminmaxav.csv');
$dfdat = file($fdat);
$dfdatt = file($fdatt);
$dfdatm = file($fdatm);

if(true) {
	$custom = customlog('today');
	$avtmint = $custom[21]['temp'];	$avtmaxt = $custom[22]['temp'];	$avhmint = $custom[21]['hum']; $avhmaxt = $custom[22]['hum'];
	$mdat[$dday-1] = array($custom[23]['temp'], $custom[24]['temp'], $custom[25]['temp'],
				$custom[23]['hum'], $custom[24]['hum'], $custom[25]['hum'],
				round($custom[23]['baro']), round($custom[24]['baro']), round($custom[25]['baro']),
				$custom[25]['wind'], $maxavgspd, $custom[29], $custom[25]['wdir'],
				$custom[26], $custom[1], $custom[0], $custom[32],
				$custom[23]['dew'], $custom[24]['dew'], $custom[25]['dew'],
				'', $custom[31], $custom[7], $custom[10], $custom[12], $custom[6], $custom[14], $custom[16], $custom[2], $custom[4]);
	$mdatt[$dday-1] = array($avtmint, $avtmaxt, '',
				$avhmint, $avhmaxt, '',
				$custom[21]['baro'], $custom[22]['baro'], '',
				'',	$maxavgspdt, $custom[30], '',
				'',	$custom[28], $custom[18], $custom[33],
				$custom[21]['dew'], $custom[22]['dew'], '',
				'', $custom[20], $custom[9], $custom[11], $custom[13], $custom[8], $custom[15], $custom[17], $custom[3], $custom[5]);
	$mdatx[$dday-1] = array( $custom[24]['temp'], $custom[23]['temp'], $custom[24]['hum'], $custom[23]['hum'], $custom[24]['baro'], $custom[23]['baro'], '-' );
}

for($d = 0; $d < $dim; $d++) {
	$mdat[$d] = explode(',', $dfdat[$dzed-$d]);
	$mdatt[$d] = explode(',', $dfdatt[$dzed-$d]);
	$mdatm[$d] = explode(',', $dfdatm[$dzed-$d]);
	for($v = 0; $v < 3; $v++) { $mdatx[$v][$d] = $mdat[$d][$v*3+1] - $mdat[$d][$v*3]; }
}

//print_r($mdat);
$mdat = array_dswap($mdat); $mdatm = array_dswap($mdatm); $mdatt = array_dswap($mdatt);
$mdat[29] = array_map('clearblank2',$mdat[29]); //special case due to weird issue with csv end-of-line blanks
unset($mdat[30], $mdat[31]);
$alldat = array_merge($mdat, $mdatx, $mdatm);

/*
for($v = 0; $v < count($data_description); $v++) {
	$mdat[$v] = array_filter($mdat[$v],'clearblank');
	if(count($mdat[$v]) > 0) {
		$msdat[$v][2] = mean($mdat[$v]); $mtdat[$v][2] = 'From ' . count($mdat[$v]) . ' days data';
		$msdat[$v][0] = min($mdat[$v]); $mtdat[$v][0] = 'On day: ' . ($dim - intval(array_search(min($mdat[$v]), $mdat[$v])));
		$msdat[$v][1] = max($mdat[$v]); $mtdat[$v][1] = 'On day: ' . ($dim - intval(array_search(max($mdat[$v]), $mdat[$v])));
		$msdatt[$v] = time_av($mdatt[$v]);
	}
	else { for($i = 0; $i < 3; $i++) { $msdat[$v][$i] = $mtdat[$v][$i] = $msdatt[$v] = 'n/a'; $typeconv[$v] = false; } }
}
for($v = 0; $v < 2; $v++) {
	$msdatm[$v][2] = mean($mdatm[$v]); $mtdatm[$v][2] = 'From ' . $dim . ' days data';
	$msdatm[$v][0] = min($mdatm[$v]); $mtdatm[$v][0] = 'On day: ' . ($dim - intval(array_search(min($mdatm[$v]), $mdatm[$v])));
	$msdatm[$v][1] = max($mdatm[$v]); $mtdatm[$v][1] = 'On day: ' . ($dim - intval(array_search(max($mdatm[$v]), $mdatm[$v])));
	if($msdatm[$v][1] == 'b') { $mdatm[$v][intval(array_search('b', $mdatm[$v]))] = 0; $v--; }
}
for($v = 0; $v < 3; $v++) {
	$msdatx[$v][2] = mean($mdatx[$v]); $mtdatx[$v][2] = 'From ' . $dim . ' days data';
	$msdatx[$v][0] = min($mdatx[$v]); $mtdatx[$v][0] = 'On day: ' . ($dim - intval(array_search(min($mdatx[$v]), $mdatx[$v])));
	$msdatx[$v][1] = max($mdatx[$v]); $mtdatx[$v][1] = 'On day: ' . ($dim - intval(array_search(max($mdatx[$v]), $mdatx[$v])));
}


//standard fix-up
$mtdat[13][2] = 'Mean: ' . conv($msdat[13][2],2);
$msdat[13][2] *= $dim; //mean => sum conversion
for($i = 0; $i < 2; $i++) { $manom[$i][2] = ' (' . conv($msdat[$i][2]-$tdatav[$mmm[$i]][$mproc-1],1.1,0,1) . ')'; }
$manom[2][2] = ' (' . conv(($msdat[0][2]+$msdat[1][2])/2-$tdatav['mean'][$mproc-1],1.1,0,1) . ')';
$manom[9][2] = ' (' . conv($msdat[9][2]-$windav[$mproc-1],4,0,1) . ')';
$manom[13][2] = ' ' . percent($msdat[13][2],($rainav[$mproc-1]));
$msdat[12][2] = round($msdat[12][2]);
for($i = 0; $i < 3; $i++) { if(intval($msdat[12][$i]) > 0) { $msdat[12][$i] .= '&deg; [' . degname($msdat[12][$i]) . ']'; } else { $msdat[12][$i] = 'n/a'; } }


//extra fix-up
$manomx[0][2] = ' (' . conv($msdatx[0][2]-$tdatav['range'][$mproc-1],1.1,0,1) . ')';
$msdatx[1][2] = round($msdatx[1][2]);

//manual fix-up
for($i = 0; $i < 2; $i++) { $mtdatm[$i][2] = 'Mean: ' . round($msdatm[$i][2],1); $msdatm[$i][2] = roundi($msdatm[$i][2] * $dim); }
$manomm[0][2] = ' ' . percent($msdatm[0][2],$sunav[$mproc-1]).' ['.acronym('of a possible '.$maxsun[$mproc-1].' hrs', roundi(100*$msdatm[0][2]/$maxsun[$mproc-1]).'%',true).']';
$manomm[1][2] = ' ' . percent($msdatm[1][2],$wetav[$mproc-1]).' [' .acronym('of a possible '. (24*$dim) . ' hrs', roundi(100*$msdatm[1][2]/(24*$dim)) . '%', true) . ']';

//nullification
$msdatt[2] = $msdatt[5] = $msdatt[8] = $msdatt[9] = $msdatt[12] = $msdatt[13] = $msdatt[19] = '';


if(intval($ddat[12]) > 0) { $ddat[12] .= '&deg; [' . degname($ddat[12]) . ']'; }
$mcdat[12] = $mcdat[14] = $mcdat[15] = $mcdat[16] = $mcdatm[2] = $mcdatm[3] = $mcdatm[4] = $mcdatm[5] = $mcdatm[6] = '';
$mcdatm[0] = roundi($mcdatm[0]) . ' hrs (' . round(100*$mcdatm[0]/$mcsanom) . '%)';
$mcdatm[1] = roundi($mcdatm[1]) . ' hrs (' . round(100*$mcdatm[1]/$wetav[$mproc-1]/$dzed*$dim) . '%)';

$ts = $ddatm[2]; $tsl = strlen($ts); //cloud cover decode
if(strpos($ts,':')) {
	$tsa = explode(':',$ts);
	for($i = 0; $i < 2; $i++) {
		if(strlen($tsa[$i]) > 1) {
			$fs[$i] = str_split($tsa[$i]);
			$fs_f[$i] = $ccd[$fs[$i][0]] . ' ' . $ccd[$fs[$i][1]] . ' ' . $ccd[$fs[$i][2]];
		}
		else { $fs_f[$i] = $ccd[$tsa[$i]]; }
	}
	$fs_fc = 'am: ' . $fs_f[0] . '<br /> pm: ' . $fs_f[1];
}
else {
	if($tsl > 1) {
		$fs = str_split($ts);
		$fs_fc = $ccd[$fs[0]] . ' ' . $ccd[$fs[1]] . ' ' . $ccd[$fs[2]];
	}
	elseif($tsl > 0) { $fs_fc = $ccd[$ts]; }
	else { $fs_fc = 'not available'; }
}
$ddatm[2] = $fs_fc;
unset($fs);

$ts = $ddatm[3]; //Snowfall decode
if($ts != '') {
	$ec += 1; $snow = true;
	$fs = '<br />Snowfall: ';
	if($ts == 'y') { $fs .= '~' . conv($ddat[13],11); }
	elseif($ts == 't') { $fs .= 'trace amount'; }
	else { $fs .= '~' . conv($ts,11); }
}

$ts = $ddatm[4]; //Lying Snow decode
if($ts != '') {
	$ec += 1;
	$fs .= '<br />Lying Snow (at 09z): ';
	if($ts == 't') { $fs .= 'trace amount'; }
	else { $fs .= '~' . conv($ts,11); }
}

$ts = $ddatm[5]; //Hail decode
if($ts != '') {
	$ec += 1;
	$fs .= '<br />Hail: ';
	if($ts == 's') { $fs .= 'small stones'; }
	elseif($ts == 'm') { $fs .= 'medium-size stones'; }
	else { $fs .= 'large stones'; }
}

$ts = $ddatm[6]; //Thunder decode
if($ts != '') {
	$ec += 1;
	$fs .= '<br />';
	if($ts == 1) { $fs .= 'Thunder'; }
	elseif($ts == 'l') { $fs .= 'Light Thunderstorm'; }
	elseif($ts == 'm') { $fs .= 'Moderate Thunderstorm'; }
	else { $fs .= 'Severe Thunderstorm'; }
}

$ts = $ddatm[7]; //Fog decode
if($ts != '') {
	$ec += 1;
	$fs .= '<br />Dense Fog';
}

if($ec < 1) { $ddatm[3] = 'None'; } else { $ddatm[3] = substr($fs,6); }
if($snow) { $data_description[13] .= ' / Snow-melt approx.'; }

$ddatm[0] .= ' (' . acronym('Out of ' . conv($dsun[$dzed],0,0) . ' hrs possible', round($ddatm[0]/$dsun[$dzed]*100) . '%',true) . ')';
$finds = array('"','?','(S)','(M)','(L)','Shwr','L ','M ','H ','T ','S ','V ','-','Snw','L-Sn','Sn','LySn','w/ ','AF', 'T-S', //1
				'occ',"tr'cm",'bkn','Dz','Rn','oc','poss',' yy','aa','L/','M/','H/','T/','S/','V/', 'T-storm', 'T-Storm', //2
				'L-','M-','H-','T-','S-','V-','xx','Sh','Lyn','Drzl','Slt','SnowS','brks','inc ', //3
				"Sl't", 'sct', 'erws', 'bk', 'L,','M,','H,','T,','S,','V,', 'Heath', 'Severe Heat', 'Heat', 'Fz', ' w ', //4
				'L)','M)','H)','T)','S)','V)','L;','M;','H;','T;','S;','V;','nowow', //5
				'hhh'); //6
$repls = array('',',','','','','Sh','Light ','Moderate ','Heavy ','Torrential ','Slight ','Very Heavy ','-','Sn','LySn','Snow','Lying Snow','with ','Air frost', 'T-storm', //1
				'oc','trace','bk','Drizzle','Rain','occasional','possible','','','Light/','Moderate/','Heavy/','Torrential/','Slight/','Very Heavy/', 'T-Storm', 'Thunderstorm',//2
				'Light-','Moderate-','Heavy-','Torrential-','Slight-','Very Heavy-','','Shower','Lying','Drizzle','Sleet','Snow S','breaks', 'including ', //3
				'Sleet', 'scattered', 'ers', 'broken','Light,','Moderate,','Heavy,','Torrential,','Slight,','Very Heavy,', 'hhh', 'Heat', '', 'Freezing', ' with ', //4
				'Light)','Moderate)','Heavy)','Torrential)','Slight)','Very Heavy)','Light;','Moderate;','Heavy;','Torrential;','Slight;','Very Heavy;','now', //5
				'Heath'); //6
$ddatm[4] = str_replace($finds,$repls,$ddatm[8]);
$ddatm[5] = str_replace($finds,$repls,$ddatm[9]);
$ddatm[6] = str_replace('?',',',$ddatm[10]);
if($ddatm[11] == 1) { $ddatm[7] = 'Yes - observations may be unreliable'; } else { $ddatm[7] = 'No'; }
if(strlen($ddatm[6]) < 3) { $ddatm[6] = 'None known'; }
if($ddatm[1] > 0 && $ddat[13] > 0.2) { $ddatm[1] .=  '<br />[Mean rain rate: '. conv($ddat[13]/$ddatm[1],2.1) . ']'; }
if(strpos($ddatt[20],'*')) { $ddatt[20] = str_replace('*',' (prev. day)',$ddatt[20]); }

$typeconv2 = $typeconv; $typeconv2[14] = $typeconv2[15] = $typeconv2[16] = false;

//for($i=0; $i<$dzed; $i++) {
//	$ddatmm = explode(',', $dfdatm[$dzed-$i]);
//	echo date('jS M: ', $sproc-24*3600*$i), str_replace($finds,$repls,$ddatmm[8]), ' &nbsp; &nbsp; ', str_replace($finds,$repls,$ddatmm[9]), '<br />';
//}
*/
?>

<h1>Monthly Breakdown for <?php if($toofar) { echo 'Invalid Month!'; } else { echo date('F Y', $sproc); } ?></h1>

<?php
//if($mproc == 10 && $yproc == 2009) echo '<b>Special note</b>: Data is suspect for this month due to partial data loss';
//if($mproc < 8 && $mproc != 1 && $yproc == 2009) echo '<b>Special note</b>: Wind data not valid for this month (valid records began in Aug 2009)';
//if($mproc < 8 && $mproc > 3 && $yproc == 2010) echo '<b>Special note</b>: Wind data not valid for this month (suspended 17th April - 28th July; replaced by METAR data from Heathrow)';

if($sproc < mkdate(2,1,2009)) { echo '<br /><b>First report available is February 2009</b>'; }

$prevs = $sproc - 3600*24*$dim; $nexts = $sproc + 3600*24* (2 + $num_adv);
$prevm = date('n', $prevs); $prevy = date('Y', $prevs);
$nextm = date('n', $nexts); $nexty = date('Y', $nexts);

if($num_adv > 0) { echo 'Based on first ', $dim, ' days available <br />'; }
?>

<table width="800">
<tr>
<td align="left">
<?php
if($sproc1 > mkdate(2,10,2009) && $sproc < mkdate($dmonth,$dday,$dyear) && !$toofar) {
	echo '<a href="wxhistmonthB.php?year=', $prevy, '&amp;month=', $prevm, '" title="View previous month&#39;s report">'; $c1 = true;
} ?>
&lt;&lt;Previous Month<?php if($c1) { echo '</a>'; } ?></td>
<td align="center"><form method="get" action="">
<select name="year">
<?php
for($i = 2009; $i <= $dyear; $i++) {
	echo '<option value="', $i, '"';
	if(isset($_GET['year'])) { if(intval($_GET['year']) == $i) { echo ' selected="selected"'; } } else { if(date("Y", mkdate($dmonth,$dday-1,$dyear)) == $i) { echo ' selected="selected"'; } }
	echo '>', $i, '</option>';
} ?>
</select>
<select name="month">
<?php
for($i = 1; $i <= 12; $i++) {
	echo '<option value="', $i, '"';
	if(isset($_GET['month'])) { if($_GET['month'] == $i) { echo ' selected="selected"'; } } else { if(date("m", mkdate($dmonth,$dday-1)) == $i) { echo ' selected="selected"'; } }
	echo '>', $months[$i-1], '</option>';
} ?>
</select>
<input type="submit" value="View Report" />
</form>
<a href="wxhistmonth.php" title="Return to most recent month's report">Reset</a>
</td><td align="right">
<?php
if($sproc < mkdate($dmonth,$dday-1,$dyear) && mkdate($mproc,3,$yproc) > mkdate(1,1,2009)) {
	echo '<a href="wxhistmonthB.php?year=', $nexty, '&amp;month=', $nextm, '" title="View next month&#39;s report">'; $c2 = true;
} ?>
Next Month&gt;&gt;<?php if($c2) { echo '</a>'; } ?></td>
</tr></table>


<?php
$w1 = 12; $w2 = 44; $w3 = 11;
if($sproc1 < mkdate($dmonth,$dday+1,$dyear) && $sproc > mkdate(2,0,2009) && !$toofar) {
	//standard
	$order = array(0,1,2,30,13,14,15,16);
	$subs = array( array('Total', 'Hr-max', '10-min max', 'Max rate') );
	echo '<table class="table1" width="99%" cellpadding="0" cellspacing="0"> <tr class="table-top">';
	td('Day',null,$w1,1,2);
	td('Temperature / '. $unitT,'td14',$w2,4);
	td('Rainfall / '. $unitR,'td12',$w2,4);
	echo '</tr> <tr class="table-top">';
	for($i = 0; $i < 4; $i++) {
		td($mmmr[$i],'td14',$w3);
	}
	for($i = 0; $i < 4; $i++) {
		td($subs[0][$i],'td12',$w3);
	}

	echo '</tr>';
	for($d = 0; $d < $dim + 1; $d++) {
		$style = ($d % 2 == 0) ? 'light' : 'dark';
		echo '<tr class="row', $style, '">';
		td($d+1,null);
		for($i = 0; $i < count($order); $i++) {
			td( acronym($mtdat[$order[$i]][$d],conv($alldat[$order[$i]][$d],$typeconvs_all[$order[$i]],0)).' '.$manom[$order[$i]][$d], 'td'.($nums_all[$order[$i]]+10), $w3 );
		}
		echo '</tr>';
	}
	echo '</table>';
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	echo '<table class="table1" width="99%" cellpadding="0" cellspacing="0"> <tr class="table-top">';
	td('Day',null,$w1,1,2);
	td('Temperature / '. $unitT,'td14',$w2,4);
	td('Rainfall / '. $unitR,'td12',$w2,4);
	echo '</tr> <tr class="table-top">';
	for($i = 0; $i < 4; $i++) {
		td($mmmr[$i],'td14',$w3);
	}
	for($i = 0; $i < 4; $i++) {
		td($subs[0][$i],'td12',$w3);
	}

	echo '</tr>';
	for($d = 0; $d < $dim + 1; $d++) {
		$style = ($d % 2 == 0) ? 'light' : 'dark';
		echo '<tr class="row', $style, '">';
		td($d+1,null);
		for($i = 0; $i < count($order); $i++) {
			td( acronym($mtdat[$order[$i]][$d],conv($alldat[$order[$i]][$d],$typeconvs_all[$order[$i]],0)).' '.$manom[$order[$i]][$d], 'td'.($nums_all[$order[$i]]+10), $w3 );
		}
		echo '</tr>';
	}
	echo '</table>';


	/*
	// standard 2
	echo '<tr class="table-top"> <td class="td4" width="',$w1,'%">Measure</td>	<td class="td4" width="',$w2,'%">Min</td>
		<td class="td4" width="',$w2,'">Max</td> <td class="td4" width="',$w2,'%">Mean / Sum</td> <td class="td4" width="',$w3,'%">Mean Time</td> </tr>';

	for($i = 20; $i < count($data_description); $i++) {
		if($i % 2 == 0) { $style = 'light'; } else { $style = 'dark'; }
		echo '<tr class="row', $style, '"> <td width="',$w1,'%" class="td', $data_num[$i]+10, 'C">', $data_description[$i], '</td>';
		for($t = 0; $t < 3; $t++) { echo '<td width="',$w2,'%" class="td', $data_num[$i]+10, 'C">', acronym($mtdat[$i][$t],conv($msdat[$i][$t],$typeconv[$i])), '</td>'; }
		echo '<td width="',$w3,'%" class="td', $data_num[$i]+10, 'C">', $msdatt[$i], '</td>';
		echo '</tr>';
	}

	// extra
	echo '<tr class="table-top"> <td class="td4" width="',$w1,'%">Measure</td>	<td class="td4" width="',$w2,'%">Min</td>
		<td class="td4" width="',$w2,'">Max</td> <td class="td4" colspan="2" width="',$w2,'%">Mean / Sum</td>  </tr>';

	for($i = 0; $i < 3; $i++) {
		if($i % 2 == 0) { $style = 'light'; } else { $style = 'dark'; }
		echo '<tr class="row', $style, '">
			<td width="',$w1,'%" class="td', $data_numx[$i]+10, 'C">', $data_descriptionx[$i], '</td>';
		for($t = 0; $t < 3; $t++) {
			if($t == 2) { $cspanm = 2; } else { $cspanm = 1; }
				echo '<td width="',$w2,'%"  colspan="', $cspanm, '" class="td', $data_numx[$i]+10, 'C">',
				acronym($mtdatx[$i][$t], conv($msdatx[$i][$t],$typeconvx[$i])), $manomx[$i][$t], '</td>';
		}
		echo '</tr>';
	}

	// manual
	if($msdatm[0] == 0 && $dim > 25) {
		echo '<tr><td colspan="5" class="td4">Extra observations not available for this month</td></tr>';
	}
	else {
		echo '<tr class="table-top"> <td class="td4" width="',$w1,'%">Measure</td>	<td class="td4" width="',$w2,'%">Min</td>
		<td class="td4" width="',$w2,'">Max</td> <td class="td4" colspan="2" width="',$w2,'%">Sum (anom.) [% of max]</td>  </tr>';

		for($i = 0; $i < 2; $i++) {
			if($i % 2 == 0) { $style = 'light'; } else { $style = 'dark'; }

			echo '<tr class="row', $style, '">
				<td width="',$w1,'%" class="td', $data_m_num[$i]+10, 'C">', $data_m_description[$i], '</td>';
			for($t = 0; $t < 3; $t++) {
				if($t == 2) { $cspanm = 2; } else { $cspanm = 1; }
				echo '<td width="',$w2,'%"  colspan="', $cspanm, '" class="td', $data_m_num[$i]+10, 'C">',
					acronym($mtdatm[$i][$t], $msdatm[$i][$t]), $manomm[$i][$t], '</td>';
			}
		echo '</tr>';
		}
	}

	echo '</table><p><b>NB: </b>Hover over value to view the date it was recorded. <br />
			 &nbsp;&nbsp;&nbsp; Sun hrs, Wet hrs, Cloud cover and Events are based on manual equipment/observations.
			Consequently, their reliablity is questionable and they &nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; are provided for interest only.</p>';

	// if($nmint) { echo '* Recorded in previous 24hr period<br />'; }

	// graphs
	$dimp = find_nearest($dim,$nums_poss,true);
	echo '<br />
	 <a href="wxhistday.php?day=1&amp;month=',$mproc,'&amp;year=',$yproc,'" title="Daily report for 1st', monthfull($mproc), ' ', $yproc,'">View daily breakdown</a>

	<img src="/graphdayA.php?x=850&amp;y=450&amp;type2=rain&amp;type1=temp&amp;num=', $dimp, '&amp;date=', date("Ymd", $sproc), '" alt="daygraph" />
	<img src="/graphdayA.php?x=850&amp;y=450&amp;type=hum&amp;type2=dew&amp;num=', $dimp, '&amp;date=', date("Ymd", $sproc), '" alt="daygraph" />
	<img src="/graphday2.php?x=850&amp;y=450&amp;num=', $dimp, '&amp;date=', date("Ymd", $sproc), '" alt="daygraph 2" />
	<img src="/graphdayA.php?x=850&amp;y=200&amp;type=wdir&amp;num=', $dimp, '&amp;date=', date("Ymd", $sproc), '" alt="daygraph-wdir" />'; */

}
else { echo 'Monthly report not available'; }
?>
</div><!-- end main -->

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>
</body>
</html>
<?php
function yrec_hlite($val, $yr) {
	if($val == $yr) { $col = '#FF8989'; } else { $col = 'gray'; }
	return ', <span style="color:'.$col.'">'.$val.'</span>';
}
?>