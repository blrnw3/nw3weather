<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php
	$file = 86; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Monthly reports</title>

	<meta name="description" content="Detailed historical monthly breakdown and summary reports with graphs" />

	<?php require('chead.php'); ?>
	<?php include('ggltrack.php');
		echo JQUERY; ?>
</head>

<body>
	<!-- ##### Header ##### -->
	<?php require('header.php'); ?>

	<!-- ##### Left Sidebar ##### -->
	<?php require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->
	<div id="main">

<?php
if(isset($_GET['month'])) { $mproc = intval($_GET['month']); } else { $mproc = ($dmonth == 1) ? 12 : $dmonth-1; }
if(isset($_GET['year'])) { $yproc = (int) $_GET['year']; } else { $yproc = ($dmonth == 1) ? $dyear-1 : $dyear; }
$sproc1 = mkdate($mproc,1,$yproc); $dim = date('t',$sproc1); $sproc = mkdate($mproc,$dim,$yproc);
if($mproc == $dmonth && $yproc == $dyear) { $sproc = time()-3600*24; $dim = $day_yest; $num_adv = 31-$dim; }
if($sproc < mkdate(2,1,2009) || $sproc > mkdate($dmonth+1, 1, $dyear) || $mproc > 12 || $mproc < 1) {
	$toofar = true;
	$mproc = $dmonth;
	$yproc = $dyear;
	$sproc = $sproc1 = time()-3600*24;
	$dim = $day_yest;
}
$dzed = date('z',$sproc)+1;
//echo date('H:i, d/m/Y', $sproc), ' x ', $dzed;
$fdat = $fullpath."dat".$yproc.".csv";
$fdatt = $fullpath."datt".$yproc.".csv";
$fdatm = $fullpath."datm".$yproc.".csv";
$dsun = file($root.'maxsun.csv');
$dtfanom = file($root.'tminmaxav.csv');
$dfdat = file($fdat);
$dfdatt = file($fdatt);
$dfdatm = file($fdatm);
/*
if(mkdate() == $sproc) {
}
*/

for($d = 0; $d < $dim; $d++) {
	$mdat[$d] = explode(',', $dfdat[$dzed-$d]);
	$mdatt[$d] = explode(',', $dfdatt[$dzed-$d]);
	$mdatm[$d] = explode(',', $dfdatm[$dzed-$d]);
	for($v = 0; $v < 3; $v++) { $mdatx[$v][$d] = $mdat[$d][$v*3+1] - $mdat[$d][$v*3]; }
}
//print_r($mdat);
$mdat = array_dswap($mdat); $mdatm = array_dswap($mdatm); $mdatt = array_dswap($mdatt);
if(is_array($mdat[29])) { $mdat[29] = array_map('clearblank2',$mdat[29]); } //special case due to weird issue with csv end-of-line blanks

for($v = 0; $v < count($data_description); $v++) {
	if(is_array($mdat[$v])) { $mdat[$v] = array_filter($mdat[$v],'clearblank'); }
	if(count($mdat[$v]) > 0) {
		$msdat[$v][2] = mean($mdat[$v]); $mtdat[$v][2] = 'From ' . count($mdat[$v]) . ' days data';
		$msdat[$v][0] = min($mdat[$v]); $mtdat[$v][0] = 'On day: ' . ($dim - intval(array_search($msdat[$v][0], $mdat[$v])));
		$msdat[$v][1] = max($mdat[$v]); $mtdat[$v][1] = 'On day: ' . ($dim - intval(array_search($msdat[$v][1], $mdat[$v])));
		$msdatt[$v] = time_av($mdatt[$v]);
	}
	else { for($i = 0; $i < 3; $i++) { $msdat[$v][$i] = $mtdat[$v][$i] = $msdatt[$v] = 'n/a'; $typeconv[$v] = false; } }
}

for($v = 0; $v < 2; $v++) {
	$msdatm[$v][2] = mean($mdatm[$v]); $mtdatm[$v][2] = 'From ' . $dim . ' days data';
	$msdatm[$v][0] = mymin($mdatm[$v]); $mtdatm[$v][0] = 'On day: ' . ($dim - intval(array_search($msdatm[$v][0], $mdatm[$v])));
	$msdatm[$v][1] = mymax($mdatm[$v]); $mtdatm[$v][1] = 'On day: ' . ($dim - intval(array_search($msdatm[$v][1], $mdatm[$v])));
}
for($v = 0; $v < 3; $v++) {
	$msdatx[$v][2] = mean($mdatx[$v]); $mtdatx[$v][2] = 'From ' . $dim . ' days data';
	$msdatx[$v][0] = min($mdatx[$v]); $mtdatx[$v][0] = 'On day: ' . ($dim - intval(array_search($msdatx[$v][0], $mdatx[$v])));
	$msdatx[$v][1] = max($mdatx[$v]); $mtdatx[$v][1] = 'On day: ' . ($dim - intval(array_search($msdatx[$v][1], $mdatx[$v])));
}

//Days of... section
//$msdatmM = monthly_extras($mdat);
$threshsRn = array(0, 0.1, 0.3, 1, 5, 10, 20);
$threshsSun = array(0, 1, 3, 5, 10); // + 25%, max
$threshsTemp = array(-5,0,0, 20,25,30);
$typeTemp = array(0,0,2, 2,1,1);
$daysofRn = array(); $daysofSun = array(); $daysofOther = array(); $daysofTemp = array();
for ($i = 0; $i < count($typeTemp); $i++) {
	$daysofTemp[$i] = sum_cond($mdat[$typeTemp[$i]], $i > 2, $threshsTemp[$i]);
	$daysofTempDescrip[$i] = $mmmr[$typeTemp[$i]] .' &'. (($i > 2) ? 'g' : 'l') .'t; '. conv($threshsTemp[$i], 1);
}
if($mproc > 4 && $mproc < 10) {
	$daysofTemp[6] = sum_cond($mdat[20], true, 20); //warm summer nights
	$daysofTempDescrip[6] = 'Min &gt; '. conv(20, 1);
} else {
	$daysofTemp[6] = sum_cond($mdat[21], false, 0); //Ice days
	$daysofTempDescrip[6] = 'Ice Days';
}
for ($i = 0; $i < count($threshsRn); $i++) {
	$daysofRn[$i] = sum_cond($mdat[13], true, $threshsRn[$i]);
	$daysofRnDescrip[$i] = '&gt; '. conv($threshsRn[$i], 2);
}
for ($i = 0; $i < count($threshsSun); $i++) {
	$daysofSun[$i] = sum_cond($mdatm[0], true, $threshsSun[$i]);
	$daysofSunDescrip[$i] = '&gt; '. $threshsSun[$i].' hrs';
}

$daysofSun[5] = $daysofSun[6] = 0;
$days = count($mdatm[0]);
for($i = 0; $i < $days; $i++) {
	$maxSun = $dsun[date('z', $sproc1 + 3600 * 24 * $i)];
	//echo "<br />$i gives $maxSun, here: {$mdatm[0][$days-$i-1]}, bool: ". (int)($mdatm[0][$days-$i-1] > 0.95 * $maxSun) . ", thresh: ". (0.95 * $maxSun);
	$daysofSun[5] += (int)($mdatm[0][$days-$i-1] > 0.25 * $maxSun);
	$daysofSun[6] += (int)($mdatm[0][$days-$i-1] > 0.95 * $maxSun);
}
$daysofSunDescrip[5] = '&gt; 25&#37; max poss.';
$daysofSunDescrip[6] = '&gt; 95&#37; max poss.';
$daysofOther[6] = 0;
for($i = 0; $i < 5; $i++) {
	$daysofOther[$i] = (int)sum_cond($mdatm[$i+3], true, 0);
	$daysofOther[6] += $daysofOther[$i];
	$daysofOtherDescrip[$i] = $data_descriptionm[$i+3];
}
$daysofOther[5] = sum_cond($mdat[11], true, 30);
$daysofOtherDescrip[5] = 'Gusts > 30mph';
$daysofOther[6] += $daysofOther[5];
$daysofOtherDescrip[6] = 'Total Events';

//Misc.
$snowfall = 0;
foreach ($mdatm[3] as $d => $sn) {
	$snowfall += (float)($sn == 'y') ? $mdat[13][$d] : $sn;
}
if($daysofRn[2] > 0) {
	$misc[0] = 'Mean rainfall for days with >0.2mm: '. conv($msdat[13][2]*$dim / $daysofRn[2], 2);
	$misc[1] = 'Total snowfall: '. conv($snowfall, 6) .' - '. percent($snowfall, $msdat[13][2]*$dim, 0, true, false) .' of the total precipitation';
	$misc[2] = 'Total lying snow: '. conv(array_sum($mdatm[4]), 6);
} else {
	$misc[0] = 'Nothing to report';
}

//standard fix-up
$mtdat[13][2] = 'Mean: ' . conv($msdat[13][2],2);
$msdat[13][2] *= $dim; //mean => sum conversion
$msdat[count($data_description)-1][2] *= $dim; //mean => sum conversion
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
?>

<h1>Monthly Report for <?php if($toofar) { echo 'Invalid Month!'; } else { echo date('F Y', $sproc); } ?></h1>

<?php
//if($mproc == 10 && $yproc == 2009) echo '<b>Special note</b>: Data is suspect for this month due to partial data loss';
//if($mproc < 8 && $mproc != 1 && $yproc == 2009) echo '<b>Special note</b>: Wind data not valid for this month (valid records began in Aug 2009)';
//if($mproc < 8 && $mproc > 3 && $yproc == 2010) echo '<b>Special note</b>: Wind data not valid for this month (suspended 17th April - 28th July; replaced by METAR data from Heathrow)';

if($toofar) { echo '<br /><b>Reports are available from February 2009, and upto the current month from day 2</b>'; }

$prevs = $sproc - 3600*24*$dim; $nexts = $sproc + 3600*24* (2 + $num_adv);
$prevm = date('n', $prevs); $prevy = date('Y', $prevs);
$nextm = date('n', $nexts); $nexty = date('Y', $nexts);

if($num_adv > 0) { echo 'Based on first ', $dim, ' days available <br />'; }

$dimp = find_nearest($dim,$nums_poss,true);
?>

<script type="text/javascript">
	//<![CDATA[
	function changeChart() {
		var patt = new RegExp("type=[a-z0-9]+");
		var match = patt.exec( $("#chart").attr('src') );
		$("#chart").attr( 'src', $("#chart").attr('src').replace( match, 'type='+ $("#wxvar").val() ) );
	}

	function loadGraphs() {
		var graphs = '<?php echo '<img src="/graphdayA.php?x=850&amp;y=450&amp;type2=rain&amp;type1=temp&amp;num=', $dimp, '&amp;date=', date("Ymd", $sproc), '" alt="daygraph" />\' +
			\'<img src="/graphdayA.php?x=850&amp;y=450&amp;type=hum&amp;type2=dew&amp;num=', $dimp, '&amp;date=', date("Ymd", $sproc), '" alt="daygraph" />\' +
			\'<img src="/graphday2.php?x=850&amp;y=450&amp;num=', $dimp, '&amp;date=', date("Ymd", $sproc), '" alt="daygraph 2" />\' +
			\'<img src="/graphdayA.php?x=850&amp;y=200&amp;type=wdir&amp;num=', $dimp, '&amp;date=', date("Ymd", $sproc), '" alt="daygraph-wdir" />'; ?>';
		$("#graphs").html(graphs);
	}
	//]]>
</script>

<table width="800">
<tr>
<td align="left">
<?php
if($sproc1 > mkdate(2,10,2009) && $sproc < mkdate($dmonth,$dday,$dyear) && !$toofar) {
	echo '<a href="wxhistmonth.php?year=', $prevy, '&amp;month=', $prevm, '" title="View previous month&#39;s report">'; $c1 = true;
} ?>
&lt;&lt;Previous Month<?php if($c1) { echo '</a>'; } ?></td>
<td align="center"><form method="get" action="">
<?php dateFormMaker($yproc, $mproc); ?>
<input type="submit" value="View Report" />
</form>
<a href="wxhistmonth.php" title="Return to most recent month's report">Reset</a>
</td><td align="right">
<?php
if($sproc < mkdate($dmonth,$dday-1,$dyear) && mkdate($mproc,3,$yproc) > mkdate(1,1,2009)) {
	echo '<a href="wxhistmonth.php?year=', $nexty, '&amp;month=', $nextm, '" title="View next month&#39;s report">'; $c2 = true;
} ?>
Next Month&gt;&gt;<?php if($c2) { echo '</a>'; } ?></td>
</tr></table>


<?php
$w1 = 26; $w2 = 22; $w3 = 7;
$wAll = array($w2,$w2,$w2,$w1,$w3);
$headings = array("Measure", "Min", "Max", "Mean / Sum (anomaly)", "Mean Time");
$orders = array(0,3,1,2,4);

if($sproc1 < mkdate($dmonth,$dday+1,$dyear) && $sproc > mkdate(2,0,2009) && !$toofar) {
	table();
	//standard
	tableHead("Weather Summary", 5);

	tr();
	for($i = 0; $i < count($orders); $i++) {
		td($headings[$orders[$i]], null, $wAll[$orders[$i]]);
	}
	tr_end();

	for($i = 0; $i < count($data_description); $i++) {
		$tdClass = 'td'. ($data_num[$i] + 10) .'C';
		tr("row".colcol($i));
		td($data_description[$i], $tdClass);
		for($t = 0; $t < 3; $t++) {
			$index = $orders[$t+1] - 1;
			td( acronym($mtdat[$i][$index],conv($msdat[$i][$index],$typeconv[$i])) . $manom[$i][$index], $tdClass );
		}
		td($msdatt[$i],  $tdClass );
		tr_end();
	}

	//standard 2
// 	echo '<tr class="table-top"> <td class="td4" width="',$w1,'%">Measure</td>	<td class="td4" width="',$w2,'%">Min</td>
// 		<td class="td4" width="',$w2,'">Max</td> <td class="td4" width="',$w2,'%">Mean / Sum</td> <td class="td4" width="',$w3,'%">Mean Time</td> </tr>';

// 	for($i = 20; $i < count($data_description); $i++) {
// 		$style = colcol($i);
// 		echo '<tr class="row', $style, '"> <td width="',$w1,'%" class="td', $data_num[$i]+10, 'C">', $data_description[$i], '</td>';
// 		for($t = 0; $t < 3; $t++) { echo '<td width="',$w2,'%" class="td', $data_num[$i]+10, 'C">', acronym($mtdat[$i][$t],conv($msdat[$i][$t],$typeconv[$i])), '</td>'; }
// 		echo '<td width="',$w3,'%" class="td', $data_num[$i]+10, 'C">', $msdatt[$i], '</td>';
// 		echo '</tr>';
// 	}

	//extra
	tr();
	for($i = 0; $i < count($orders)-2; $i++) {
		td($headings[$orders[$i]]);
	}
	td("Max");
	tr_end();

	for($i = 0; $i < count($data_descriptionx)-1; $i++) {
		$tdClass = 'td'. ($data_numx[$i] + 10) .'C';
		tr("row".colcol($i));
		td($data_descriptionx[$i], $tdClass);
		for($t = 0; $t < 3; $t++) {
			$index = $orders[$t+1] - 1;
// 			if($t == 2) { $cspanm = 2; } else { $cspanm = 1; }
			td( acronym($mtdatx[$i][$index], conv($msdatx[$i][$index],$typeconvx[$i])) . $manomx[$i][$index], $tdClass );
		}
		tr_end();
	}

	//manual
	if($msdatm[0] == 0 && $dim > 25) {
		echo '<tr><td colspan="5" class="td4">Extra observations not available for this month</td></tr>';
	}
	else {
		tr();
			td('Measure');
			td('Sum (anom.) [% of max]');
			td('Min');
			td('Max');
		tr_end();

		for($i = 0; $i < 2; $i++) {
			$tdClass = 'td'. ($data_m_num[$i] + 10) .'C';
			tr( 'row'.colcol($i) );
			td( $data_m_description[$i], $tdClass );
			for($t = 0; $t < 3; $t++) {
				$index = $orders[$t+1] - 1;
// 				$cspanm = ($t == 2) ? 2 : 1;
				td( acronym($mtdatm[$i][$index], $msdatm[$i][$index]). $manomm[$i][$index], $tdClass );
			}
			tr_end();
		}
	}
	table_end();
	echo '<p><b>NB: </b>Hover over a value to view the date it was recorded. <br />
			 &nbsp;&nbsp;&nbsp; Sun hrs, Wet hrs, Cloud cover and Events are based on manual equipment/observations  (entered with a delay of 1-10 days).
			Consequently, their reliablity is questionable and they &nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; are provided for interest only.</p>';

	//Monthly specials
	$daysofHeadings = array("Rainfall", "Temperature", "Sunshine", "Other");
	$daysofVarNames = array('Rn', 'Temp', 'Sun', 'Other');
	$daysofClasses = array(12, 14, 18, 4);
	table();
	tableHead("Days Of...", 8);
	tr();
	foreach ($daysofHeadings as $value) {
		td($value, null, "25%", 2);
	}
	tr_end();
	for($i = 0; $i < 7; $i++) {
		tr( 'row'.colcol($i) );
		for($r = 0; $r < 4; $r++) {
			$style = $daysofClasses[$r];
			td( ${'daysof'.$daysofVarNames[$r].'Descrip'}[$i], 'td'. $style .'C', '17%' );
			td( ${'daysof'.$daysofVarNames[$r]}[$i], 'td'. $style .'C', '8%' );
		}
		tr_end();
	}
	table_end();
//	daysOfTable($daysofOther, $dayofOtherDescrip, "Other", 4, false);

	echo '<h2>Misc.</h2>';
	foreach ($misc as $value) {
		echo $value .'<br />
			';
	}

	//graphs
	echo '<h2>Graphs and Charts</h2>
		<img id="chart" src="graph31.php?x=675&amp;y=400&amp;type=tmean&amp;year='.$yproc.'&amp;month='.$mproc.'&amp;length=31" alt="Chart" />
		<select id="wxvar" size="25" style="margin-left:0.2em;" onchange="changeChart();" >
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
		<div id="graphs">
			<button style="margin:1em;" onclick="loadGraphs();">Load detailed graphs</button>
			<noscript>Javascript required to view graphs</noscript>
		</div>
	<a href="wxhistday.php?day=1&amp;month=',$mproc,'&amp;year=',$yproc,'" title="Daily report for 1st', monthfull($mproc), ' ', $yproc,'">View daily breakdown</a>
	';

}
else { echo 'Monthly report not available'; }
?>
</div><!-- end main -->
<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>
</body>
</html>