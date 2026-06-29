<?php
require "Page.php";
Page::init([
	"fileNum" => 20,
	"title" => "Climate",
	"description" => "Long-term climate averages for Hampstead, North London NW3. 30-year period weather averages for rain, temperature, air frost, thunder, wind, snow and sun.",
]);
Page::Start();

// Monthly climate normals: LTA-backed where available, plus manual event normals.
$tmin = LTA::$vars['tmin']['monthly'];
$tmax = LTA::$vars['tmax']['monthly'];
$tmean = []; $trange = [];
for ($m = 0; $m < 12; $m++) { $tmean[$m] = ($tmin[$m] + $tmax[$m]) / 2; $trange[$m] = $tmax[$m] - $tmin[$m]; }
$rain = LTA::$vars['rain']['monthly'];
$rdays = LTA::$vars['rdays']['monthly'];
$wind = LTA::$vars['wmean']['monthly'];
$wethr = LTA::$vars['wethr']['monthly'];
$sun = LTA::$vars['sunhr']['monthly'];
$maxsun = LTA::$vars['maxsun']['monthly'];
$AF = [6, 6, 3, 0.7, 0.0, 0, 0, 0, 0, 0.3, 2, 5];
$TS = [0.4, 0.3, 0.6, 1.0, 2.0, 3.0, 2.5, 2.5, 2.0, 1.0, 0.4, 0.3];
$LS = [2.5, 2.5, 0.4, 0.2, 0, 0, 0, 0, 0, 0, 0.3, 1];
$FS = [5, 5, 4, 2, 0, 0, 0, 0, 0, 0, 1, 3];

// Column order matches the table (13 displayed columns; sun uses maxsun for %).
$vars = [$tmin, $tmax, $tmean, $trange, $rain, $rdays, $wind, $AF, $TS, $LS, $FS, $wethr, $sun];
$ctype = [Wx::Temperature, Wx::Temperature, Wx::Temperature, Wx::AbsTemp, Wx::Rain, Wx::None, Wx::Wind, Wx::None, Wx::None, Wx::None, Wx::None, Wx::None, Wx::None];
$stype = [14, 14, 14, 14, 12, 12, 13, 4, 4, 4, 4, 19, 18];
$sumorno = [false, false, false, false, true, true, false, true, true, true, true, true, true];
$ncol = count($vars);

$cv = function ($v, $type, $dpa = 0) { return Wx::conv($v, $type, false, false, $dpa); };
?>

<h1>Climate of NW3</h1>
<p>Much like the rest of London, NW3's climate is a function of its proximity to the European continent, its position close to the North
	Atlantic and North Sea, the Urban Heat Island effect, and London's northerly latitude. With the prevailing wind being broadly
	south-westerly, bringing tropical maritime air, London has consistent rainfall throughout the year, relatively low sunshine totals,
	few snow days, and a lack of temperature extremes - those generally coming when the wind switches to the Arctic north or polar
	north-east (cold), or to the continental south or south-east (heat).</p>
<p>Thunderstorms are infrequent and generally weak compared with those of the near continent. The main weather hazards in NW3 are
	strong winds, dense fog, and the occasional heat wave or icy cold snap. Although not subject to river flooding (NW3 is home to
	inner London's highest point, Whitestone Pond, 134 m), brief flash flooding has occurred from
	localised slow-moving thunderstorms.</p>

<h1>Long-term Climate Averages</h1>
<p>These are estimates for the long-term average conditions at NW3, derived from data for 1991-2020 - the
	<acronym title="World Meteorological Organisation">WMO</acronym> standard reference period - from nearby official Met Office
	sites (Heathrow and Northolt), adjusted for local differences.</p>

<table class="table1" width="100%" cellpadding="2" cellspacing="0">
<tr class="table-top">
<td rowspan="2" class="td4">&nbsp;</td>
<td colspan="4" width="30%" class="td14C">Temperature / <?php echo strip_tags(Wx::getUnits(Wx::Temperature)); ?></td>
<td colspan="2" width="16%" class="td12C">Rain / <?php echo strip_tags(Wx::getUnits(Wx::Rain)); ?></td>
<td rowspan="2" width="8%" class="td13C">Wind<br />Speed / <?php echo strip_tags(Wx::getUnits(Wx::Wind)); ?></td>
<td colspan="2" width="15%" class="td4C">Days Of</td>
<td colspan="2" width="14%" class="td4C">Days Of Snow</td>
<td rowspan="2" width="7%" class="td19C">Wet<br />Hours</td>
<td rowspan="2" width="10%" class="td18C">Sun Hrs<br />(% of max)</td>
</tr>
<tr class="table-top">
<td class="td14C" width="8%">Min</td><td class="td14C" width="8%">Max</td><td class="td14C" width="7%">Mean</td><td class="td14C" width="7%">Range</td>
<td class="td12C" width="9%">Rainfall</td><td class="td12C" width="7%">&gt;1mm<br />Days</td>
<td class="td4C" width="8%">Air<br />Frost</td><td class="td4C" width="7%">Thun<br />der</td>
<td class="td4C" width="7%">Lying</td><td class="td4C" width="7%">Falling</td>
</tr>
<?php
$sunCell = function ($sun, $maxsun) {
	return $sun . ' (' . HTML::acronym('Maximum possible: ' . round($maxsun) . ' hrs', round(100 * $sun / $maxsun), true) . '%)';
};
$wetCell = function ($wet, $maxhrs) {
	return HTML::acronym(Util::roundi(100 * $wet / $maxhrs) . '% of a possible ' . $maxhrs . ' hrs', $wet);
};

// Monthly rows
for ($m = 0; $m < 12; $m++) {
	$style = ($m + 1 == (int)Date::$dmonth) ? 'hlite' : (($m % 2 == 0) ? 'light' : 'dark');
	echo '<tr class="row' . $style . '"><td class="td4" style="font-weight:bold">' . Date::$months[$m] . '</td>';
	for ($v = 0; $v < $ncol; $v++) {
		$dpa = ($v == 4) ? -1 : 0;
		$val = $cv($vars[$v][$m], $ctype[$v], $dpa);
		if ($v == 12) { $val = $sunCell($vars[12][$m], $maxsun[$m]); }
		if ($v == 11) { $val = $wetCell($vars[11][$m], Date::get_days_in_month($m + 1, 2009) * 24); }
		echo '<td class="td' . $stype[$v] . 'C">' . $val . '</td>';
	}
	echo '</tr>';
}
echo '<tr class="rowlight"><td class="td4" colspan="' . ($ncol + 1) . '">&nbsp;</td></tr>';

// Seasonal rows
for ($s = 0; $s < 4; $s++) {
	$style = ($s % 2 == 1) ? 'light' : 'dark';
	echo '<tr class="row' . $style . '"><td class="td4" style="font-weight:bold">' . Date::$snames[$s] . '</td>';
	$seasonVals = [];
	for ($v = 0; $v < $ncol; $v++) {
		$divor = $sumorno[$v] ? 1 : 3;
		$sv = 0;
		foreach (Date::$snums[$s] as $mi) { $sv += $vars[$v][$mi] / $divor; }
		$seasonVals[$v] = $sv;
	}
	$seasonMaxSun = 0; foreach (Date::$snums[$s] as $mi) { $seasonMaxSun += $maxsun[$mi]; }
	for ($v = 0; $v < $ncol; $v++) {
		$dpa = ($v == 4) ? -1 : 0;
		$val = $cv($seasonVals[$v], $ctype[$v], $dpa);
		if ($v == 12) { $val = $sunCell($seasonVals[12], $seasonMaxSun); }
		if ($v == 11) { $val = $wetCell($seasonVals[11], Date::get_seasondays($s) * 24); }
		echo '<td class="td' . $stype[$v] . 'C">' . $val . '</td>';
	}
	echo '</tr>';
}
echo '<tr class="rowdark"><td class="td4" colspan="' . ($ncol + 1) . '">&nbsp;</td></tr>';

// Sum row
$annualSum = []; $annualAv = []; $annualRange = [];
for ($v = 0; $v < $ncol; $v++) {
	$annualSum[$v] = array_sum($vars[$v]);
	$annualAv[$v] = Util::mean($vars[$v]);
	$annualRange[$v] = max($vars[$v]) - min($vars[$v]);
}
$annualMaxSun = array_sum($maxsun);
echo '<tr class="rowlight"><td class="td4" style="font-weight:bold">Sum</td>';
for ($v = 0; $v < $ncol; $v++) {
	if (!$sumorno[$v]) { echo '<td class="td' . $stype[$v] . 'C">&nbsp;</td>'; continue; }
	$dpa = ($v == 4 || $v == 7) ? -1 : 0;
	$val = $cv($annualSum[$v], $ctype[$v], $dpa);
	if ($v == 12) { $val = $sunCell($annualSum[12], $annualMaxSun); }
	if ($v == 11) { $val = $wetCell($annualSum[11], 365 * 24); }
	echo '<td class="td' . $stype[$v] . 'C">' . $val . '</td>';
}
echo '</tr>';

// Annual (mean) row
echo '<tr class="rowdark"><td class="td4" style="font-weight:bold">Annual</td>';
for ($v = 0; $v < $ncol; $v++) {
	$dpa = ($v == 4) ? -1 : 0;
	$val = $cv($annualAv[$v], $ctype[$v], $dpa);
	if ($v == 12) { $val = $sunCell($annualAv[12], $annualMaxSun / 12); }
	if ($v == 11) { $val = $wetCell($annualAv[11], 365 * 24 / 12); }
	echo '<td class="td' . $stype[$v] . 'C">' . $val . '</td>';
}
echo '</tr>';
echo '<tr class="rowlight"><td class="td4" colspan="' . ($ncol + 1) . '">&nbsp;</td></tr>';

// Range row
echo '<tr class="rowdark"><td class="td4" style="font-weight:bold">Range</td>';
for ($v = 0; $v < $ncol; $v++) {
	$dpa = ($v == 4) ? -1 : 0;
	echo '<td class="td' . $stype[$v] . 'C">' . $cv($annualRange[$v], $ctype[$v], $dpa) . '</td>';
}
echo '</tr>';
?>
</table>

<p>A day-by-day progression of the temperature averages can be found <a href="/wxtempltas.php" title="Daily long-term average temperatures">here</a>.</p>

<h2>Climate charts</h2>
<?php
foreach (['tmax' => 'Maximum Temperature', 'tmin' => 'Minimum Temperature', 'rain' => 'Rainfall', 'sunhr' => 'Sun Hours', 'wmean' => 'Wind Speed'] as $cvar => $lbl) {
	Charts::daily(['type' => $cvar, 'mode' => 'climate'], ['height' => 300]);
}
Page::End();
