<?php require('unit-select.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php 
	$file = 20;
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Climate</title>

	<meta name="description" content="Long-term climate averages for Hampstead, North London NW3.
		30-year period weather averages/means/sums/totals for rain, temperature, air frost, thunder, wind, snow and sun" />

	<?php require('chead.php'); ?>
	<?php include_once("ggltrack.php") ?>
</head>

<body>
	<!-- ##### Header ##### -->
	<?php require('header.php'); ?>
	<!-- ##### Left Sidebar ##### -->
	<?php require('leftsidebar.php');  ?>

	<!-- ##### Main Copy ##### -->
	<div id="main">

<h1>Climate of NW3</h1>

<p>
	Much like the rest of London, this is a function of its proximity to the European continent, positioning close to the North Atlantic
	and the North Sea, and to some extent the Urban Heat Island effect and London's rather northerly latitude.
	The direction of the wind (or more precisely the air mass this brings) is largely responsible for which of these sources most influences the day-to-day weather.
	With the prevailing wind being broadly south-westerly (bringing tropical maritime air), this gives London its climate of consistent rainfall throughout the year,
	relatively low sunshine total and few snow days, as well as a lack of extremes of temperature,	those generally coming when the wind switches away from this direction
	-  to the Arctic north or Polar north-east for cold, and to the Continental south or south east for heat.
</p>
<p>
	Thunderstorms are not frequent, and generally comparatively weak
	compared to those of the near continent. Days of strong winds do occasionally occur, and along with the odd short-lived heat wave or icy cold snap,
	are the only real hazards in NW3, flooding not being a problem to this hilly area of London, home to inner London's highest point (Whitestone Pond,
	<?php echo conv(134,7); ?>).
</p>

<h1>Long-term Climate Averages</h1>

<p>
	These are estimates for the long-term average weather conditions, i.e. the climate, at NW3.
	<br /> They were derived from data for the period 1971-2000 - the <acronym title="World Meteorological Organisation">WMO</acronym>
	 standard reference period - from nearby official Met Office sites.
	 (Mostly the one at Whitestone Pond (see above), which although less than a couple of miles away, is some <?php echo conv(80,7); ?>
	 higher up in terms of elevation.)<br />Some adjustments have therefore been made to reflect the different siting conditions.
	 Rainfall values were updated in Sep 2012 to reflect more accurate analysis of nearby station data.
</p>

<table class="table1" width="100%" cellpadding="2" cellspacing="0">

<tr class="table-top">
<td rowspan="2" class="td4">&nbsp;</td>
<td colspan="4" width="30%" class="td14C">Temperature / &deg;<?php echo $unitT; ?></td>
<td colspan="2" width="16%" class="td12C">Rain / <?php echo $unitR; ?></td>
<td rowspan="2" width="8%" class="td13C">Wind<br />Speed<br />/ <?php echo $unitW; ?></td>
<td colspan="2" width="15%" class="td4C">Days Of</td>
<td colspan="2" width="14%" class="td4C">Days Of Snow</td>
<td rowspan="2" width="7%" class="td19C">Wet<br />Hours</td>
<td rowspan="2" width="10%" class="td18C">Sun Hrs<br />(% of max)</td>
</tr>
<tr class="table-top">
<td class="td14C" width="8%">Min</td>
<td class="td14C" width="8%">Max</td>
<td class="td14C" width="7%">Mean</td>
<td class="td14C" width="7%">Range</td>
<td class="td12C" width="9%">Rainfall</td>
<td class="td12C" width="7%">&gt;1mm<br />Days</td>
<td class="td4C" width="8%">Air<br />Frost</td>
<td class="td4C" width="7%">Thun<br />der</td>
<td class="td4C" width="7%">Lying</td>
<td class="td4C" width="7%">Falling</td>
</tr>
<?php

$ctype = array(1,1,1,1.1, 2,false, 4, false,false,false,false,false,false);
$stype = array(14,14,14,14, 12,12, 13, 4,4,4,4,19,18);
$bigstyle = ' style="font-size:105%;font-weight:bold"';

//Monthly
for($m = 0; $m < 12; $m++) {
	$vars[12][$m] .= ' (' . acronym('Maximum Possible: ' . $maxsun[$m] . ' hrs',round(100*$vars[12][$m]/$maxsun[$m]),true) . ')';
	$vars[11][$m] = acronym(roundi(100*$vars[11][$m]/get_days_in_month($m+1,2009)/24) . '% of a maximum possible: '. get_days_in_month($m+1,2009)*24 . ' hrs',$vars[11][$m]);
	$style = ($m+1 == $dmonth) ? 'hlite' : ( ($m % 2 == 0) ? 'light' : 'dark' );
	echo '<tr class="row', $style, '"', $currbig, '>
		<td class="td4" style="font-weight:bold">',$months[$m],'</td>';
	for($v = 0; $v < count($ctype); $v++) {
		if($v == 4) { $dpa = -1; } else { $dpa = 0; }
		echo '<td class="td',$stype[$v],'C">', conv($vars[$v][$m],$ctype[$v],0,0,$dpa), '</td>
			';
	}
	echo '</tr>';
}


echo '<tr class="rowlight">';
for($v = 0; $v < count($ctype)+1; $v++) { echo '<td class="td4">&nbsp;</td>'; }
echo '</tr>';

//Seasonal
for($s = 0; $s < 4; $s++) {
	$seasonav[12][$s] .= ' (' . acronym('Maximum Possible: ' . $seasonav[13][$s] . ' hrs',round(100*$seasonav[12][$s]/$seasonav[13][$s]),true) . ')';
	$seasonav[11][$s] = acronym(roundi(100*$seasonav[11][$s]/get_seasondays($s)/24) . '% of a maximum possible: '. get_seasondays($s)*24 . ' hrs',$seasonav[11][$s]);
	$style = ($s+1 == $season) ? 'hlite' : ( ($s % 2 == 1) ? 'light' : 'dark' );
	echo '<tr class="row', $style, '">
		<td class="td4" style="font-weight:bold">',$snames[$s],'</td>';
	for($v = 0; $v < count($ctype); $v++) {
		if($v == 4) { $dpa = -1; } else { $dpa = 0; }
		echo '<td class="td',$stype[$v],'C">', conv($seasonav[$v][$s],$ctype[$v],0,0,$dpa), '</td>
			';
	}
	echo '</tr>';
}

echo '<tr class="rowdark">';
for($v = 0; $v < count($ctype)+1; $v++) { echo '<td class="td4">&nbsp;</td>'; }
echo '</tr>';

//Sum
$annualsum[12] .= ' (' . acronym('Maximum Possible: ' . $annualsum[13] . ' hrs',round(100*$annualsum[12]/$annualsum[13]),true) . ')';
$annualsum[11] = acronym(roundi(100*$annualsum[11]/365/24) . '% of a maximum possible: '. 365*24 . ' hrs',$annualsum[11]);
echo '<tr class="rowlight">
	<td class="td4" style="font-weight:bold">Sum</td>';
for($v = 0; $v < count($ctype); $v++) {
	if($v == 4 || $v == 7) { $dpa = -1; } else { $dpa = 0; }
	echo '<td class="td',$stype[$v],'C">';
	if($sumorno[$v]) { echo conv($annualsum[$v],$ctype[$v],0,0,$dpa); } else { echo '&nbsp;'; }
	echo '</td>
		';
}
echo '</tr>';

//Annual
$annualav[12] .= ' (' . acronym('Maximum Possible: ' . $annualav[13] . ' hrs',round(100*$annualav[12]/$annualav[13]),true) . ')';
$annualav[11] = acronym(roundi(100*$annualav[11]/365/24*12) . '% of a maximum possible: '. 365*24/12 . ' hrs',$annualav[11]);
echo '<tr class="rowdark">
	<td class="td4" style="font-weight:bold">Annual</td>';
for($v = 0; $v < count($ctype); $v++) {
	if($v == 4) { $dpa = -1; } else { $dpa = 0; }
	echo '<td class="td',$stype[$v]+$extra,'C">', conv($annualav[$v],$ctype[$v],0,0,$dpa), '</td>
		';
}
echo '</tr>';

echo '<tr class="rowlight">';
for($v = 0; $v < count($ctype)+1; $v++) { echo '<td class="td4">&nbsp;</td>'; }
echo '</tr>';

//Range
echo '<tr class="rowdark">
	<td class="td4" style="font-weight:bold">Range</td>';
for($v = 0; $v < count($ctype); $v++) {
	if($v == 4) { $dpa = -1; } else { $dpa = 0; }
	echo '<td class="td',$stype[$v],'C">', conv($annualrange[$v],$ctype[$v],0,0,$dpa), '</td>
		';
}

echo '</tr>';
?>
</table>

<p>A day-by-day progression of the temperature averages can be found <a href="wxtempltas.php" title="Daily long-term average temperatures">here</a>.</p>

<img src="graphclim.php?type0&amp;type1" alt="climgraph1" />
<img src="graphclim.php?type2&amp;type3" alt="climgraph2" /><br /><br />
<img src="graphclim.php?type4&amp;y=300" alt="climgraph3" /><br /><br />
<img src="graphclim.php?type5&amp;y=300" alt="climgraph3.5" /><br /><br />
<img src="graphclim.php?type6&amp;y=300" alt="climgraph4" /> <br /><br />
<img src="graphclim.php?type7&amp;type8&amp;type9&amp;type10" alt="climgraph5" /><br /><br />
<img src="graphclim.php?type11&amp;y=300" alt="climgraph6" /><br /><br />
<img src="graphclim.php?type13&amp;type12" alt="climgraph7" />
</div>

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>

</body>
</html>