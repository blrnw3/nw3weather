<?php
require "Page.php";
Page::init([
	"fileNum" => 86,
	"title" => "Monthly Reports",
	"description" => "Detailed historical monthly breakdown and summary weather reports for Hampstead, London (NW3).",
	"isSubFile" => true,
]);
Page::Start();

$DESC = ['Minimum Temperature','Maximum Temperature','Mean Temperature','Minimum Humidity','Maximum Humidity','Mean Humidity',
	'Minimum Pressure','Maximum Pressure','Mean Pressure','Mean Wind Speed','Maximum Wind Speed','Maximum Gust','Mean Wind Direction',
	'Rainfall','Maximum Hourly Rain','Maximum 10-min Rain','Maximum Rain Rate','Minimum Dew Point','Maximum Dew Point','Mean Dew Point',
	'Night Minimum (21-09)','Day Maximum (09-21)','Max 10m Temp Rise','Max 1hr Temp Rise','Max 1hr Hum Rise','Max 10m Temp Fall',
	'Max 1hr Temp Fall','Max 1hr Hum Fall','Max 10m Wind Speed','Minimum Feels-like','Maximum Feels-like','Mean Feels-like','Air-frost Hrs'];
$T = Wx::Temperature; $H = Wx::Humidity; $P = Wx::Pressure; $W = Wx::Wind; $D = Wx::Direction; $R = Wx::Rain; $RR = Wx::RainRate; $HR = Wx::Hours;
$UNIT = [$T,$T,$T, $H,$H,$H, $P,$P,$P, $W,$W,$W, $D, $R,$R,$R, $RR, $T,$T,$T, $T,$T, $T,$T,$H,$T,$T,$H, $W, $T,$T,$T, $HR];
$CNUM = [4,4,4, 0,0,0, 6,6,6, 3,3,3,3, 2,2,2,2, 0,0,0, 4,4, 4,4,0,4,4,0, 3, 4,4,4, 4];
$NCOL = count($DESC);

// ---- Date validation ----
$mproc = isset($_GET['month']) ? intval($_GET['month']) : ((int)Date::$dmonth == 1 ? 12 : (int)Date::$dmonth - 1);
$yproc = isset($_GET['year']) ? intval($_GET['year']) : ((int)Date::$dmonth == 1 ? (int)Date::$dyear - 1 : (int)Date::$dyear);
$toofar = false; $num_adv = 0;
$sproc1 = Date::mkdate($mproc, 1, $yproc);
$dim = (int)date('t', $sproc1);
$sproc = Date::mkdate($mproc, $dim, $yproc);
if ($mproc == (int)Date::$dmonth && $yproc == (int)Date::$dyear) { $sproc = time() - 86400; $dim = (int)Date::$day_yest; $num_adv = 31 - $dim; }
if ($sproc < Date::mkdate(2, 1, 2009) || $sproc > Date::mkdate((int)Date::$dmonth + 1, 1, (int)Date::$dyear) || $mproc > 12 || $mproc < 1) {
	$toofar = true; $mproc = (int)Date::$dmonth; $yproc = (int)Date::$dyear; $sproc = $sproc1 = time() - 86400; $dim = (int)Date::$day_yest;
}
$dzed = (int)date('z', $sproc) + 1;

// ---- Load CSVs ----
$datFile = ROOT . "dat$yproc.csv";
$dattFile = ROOT . "datt$yproc.csv";
$datmFile = ROOT . "datm$yproc.csv";
$datLines = is_file($datFile) ? file($datFile) : [];
$dattLines = is_file($dattFile) ? file($dattFile) : [];
$datmLines = is_file($datmFile) ? file($datmFile) : [];
$sunLines = is_file(ROOT . 'maxsun.csv') ? file(ROOT . 'maxsun.csv') : [];

$haveData = (count($datLines) > 0 && !$toofar && $sproc1 < Date::mkdate((int)Date::$dmonth, (int)Date::$dday + 1, (int)Date::$dyear) && $sproc > Date::mkdate(2, 0, 2009));

// ---- Build per-variable arrays for the month ----
$mdat = []; $mdatt = []; $mdatm = []; $mdatx = [[], [], []];
for ($d = 0; $d < $dim; $d++) {
	$ln = $dzed - $d;
	$row = isset($datLines[$ln]) ? explode(',', $datLines[$ln]) : [];
	$rowt = isset($dattLines[$ln]) ? explode(',', $dattLines[$ln]) : [];
	$rowm = isset($datmLines[$ln]) ? explode(',', $datmLines[$ln]) : [];
	for ($v = 0; $v < $NCOL; $v++) { if (isset($row[$v]) && Util::clearblank($row[$v])) { $mdat[$v][] = (float)$row[$v]; } }
	for ($v = 0; $v < $NCOL; $v++) { if (isset($rowt[$v]) && Util::clearblank($rowt[$v])) { $mdatt[$v][] = $rowt[$v]; } }
	for ($v = 0; $v < 16; $v++) { $mdatm[$v][] = isset($rowm[$v]) ? $rowm[$v] : ''; }
	for ($x = 0; $x < 3; $x++) {
		if (isset($row[$x * 3], $row[$x * 3 + 1]) && Util::clearblank($row[$x * 3]) && Util::clearblank($row[$x * 3 + 1])) {
			$mdatx[$x][] = (float)$row[$x * 3 + 1] - (float)$row[$x * 3];
		}
	}
}

// ---- Per-variable summaries ----
$msdat = []; $mtdat = []; $msdatt = [];
for ($v = 0; $v < $NCOL; $v++) {
	$arr = isset($mdat[$v]) ? $mdat[$v] : [];
	if (count($arr) > 0) {
		$msdat[$v][2] = Util::mean($arr); $mtdat[$v][2] = 'From ' . count($arr) . ' days data';
		$msdat[$v][0] = min($arr); $mtdat[$v][0] = '';
		$msdat[$v][1] = max($arr); $mtdat[$v][1] = '';
		$msdatt[$v] = (isset($mdatt[$v]) && count($mdatt[$v])) ? Date::time_av($mdatt[$v]) : '';
	} else {
		$msdat[$v] = ['n/a', 'n/a', 'n/a']; $mtdat[$v] = ['', '', '']; $msdatt[$v] = '';
	}
}
// Range summaries
$msdatx = [];
for ($x = 0; $x < 3; $x++) {
	$arr = $mdatx[$x];
	if (count($arr)) { $msdatx[$x] = [min($arr), max($arr), Util::mean($arr)]; }
	else { $msdatx[$x] = ['n/a', 'n/a', 'n/a']; }
}
// Manual sun/wet
$msdatm = [];
for ($v = 0; $v < 2; $v++) {
	$arr = array_map(function ($x) { return is_numeric($x) ? (float)$x : 0; }, isset($mdatm[$v]) ? $mdatm[$v] : []);
	$msdatm[$v] = count($arr) ? [Util::mymin($arr), Util::mymax($arr), Util::mean($arr)] : [0, 0, 0];
}

// ---- Monthly long-term normals ----
$tminN = LTA::getMonthlyAnom('tmin', $mproc);
$tmaxN = LTA::getMonthlyAnom('tmax', $mproc);
$tmeanN = ($tminN !== null && $tmaxN !== null) ? ($tminN + $tmaxN) / 2 : null;
$windN = LTA::getMonthlyAnom('wmean', $mproc);
$rainN = LTA::getMonthlyAnom('rain', $mproc);
$sunN = LTA::getMonthlyAnom('sunhr', $mproc);
$wetN = LTA::getMonthlyAnom('wethr', $mproc);
$maxsunN = LTA::getMonthlyAnom('maxsun', $mproc);

// ---- Fix-ups & anomalies ----
$manom = []; for ($v = 0; $v < $NCOL; $v++) { $manom[$v] = ['', '', '']; }
$manomx = [['', '', ''], ['', '', ''], ['', '', '']];
$manomm = [['', '', ''], ['', '', '']];
if ($haveData) {
	$mtdat[13][2] = 'Mean: ' . Wx::conv($msdat[13][2], Wx::Rain, true);
	if (is_numeric($msdat[13][2])) { $msdat[13][2] *= $dim; }
	if (is_numeric($msdat[$NCOL - 1][2])) { $msdat[$NCOL - 1][2] *= $dim; }

	if ($tminN !== null && is_numeric($msdat[0][2])) { $manom[0][2] = ' (' . Wx::conv($msdat[0][2] - $tminN, Wx::AbsTemp, false, true) . ')'; }
	if ($tmaxN !== null && is_numeric($msdat[1][2])) { $manom[1][2] = ' (' . Wx::conv($msdat[1][2] - $tmaxN, Wx::AbsTemp, false, true) . ')'; }
	if ($tmeanN !== null && is_numeric($msdat[0][2]) && is_numeric($msdat[1][2])) {
		$manom[2][2] = ' (' . Wx::conv(($msdat[0][2] + $msdat[1][2]) / 2 - $tmeanN, Wx::AbsTemp, false, true) . ')';
	}
	if ($windN !== null && is_numeric($msdat[9][2])) { $manom[9][2] = ' (' . Wx::conv($msdat[9][2] - $windN, Wx::Wind, false, true) . ')'; }
	if ($rainN !== null && $rainN > 0) { $manom[13][2] = ' ' . Util::percent($msdat[13][2], $rainN); }

	if (is_numeric($msdat[12][2])) { $msdat[12][2] = round($msdat[12][2]); }
	for ($i = 0; $i < 3; $i++) {
		if (is_numeric($msdat[12][$i]) && intval($msdat[12][$i]) > 0) { $msdat[12][$i] .= '&deg; [' . Wx::degname((int)$msdat[12][$i]) . ']'; }
		else { $msdat[12][$i] = 'n/a'; }
	}
	$rangeN = ($tminN !== null && $tmaxN !== null) ? ($tmaxN - $tminN) : null;
	if ($rangeN !== null && is_numeric($msdatx[0][2])) { $manomx[0][2] = ' (' . Wx::conv($msdatx[0][2] - $rangeN, Wx::AbsTemp, false, true) . ')'; }

	for ($i = 0; $i < 2; $i++) { $msdatm[$i][2] = Util::roundi($msdatm[$i][2] * $dim); }
	if ($sunN !== null && $maxsunN) {
		$manomm[0][2] = ' ' . Util::percent($msdatm[0][2], $sunN) . ' [' . HTML::acronym('of a possible ' . round($maxsunN) . ' hrs', Util::roundi(100 * $msdatm[0][2] / $maxsunN) . '%', true) . ']';
	}
	if ($wetN !== null) {
		$manomm[1][2] = ' ' . Util::percent($msdatm[1][2], $wetN) . ' [' . HTML::acronym('of a possible ' . (24 * $dim) . ' hrs', Util::roundi(100 * $msdatm[1][2] / (24 * $dim)) . '%', true) . ']';
	}
}
// Times that are not meaningful
foreach ([2, 5, 8, 9, 12, 13, 19] as $ni) { if (isset($msdatt[$ni])) { $msdatt[$ni] = ''; } }
?>

<h1>Monthly Report for <?php echo $toofar ? 'Invalid Month!' : date('F Y', $sproc); ?></h1>
<?php
if ($toofar) { echo '<p><b>Reports are available from February 2009, and up to the current month from day 2.</b></p>'; }
if ($num_adv > 0) { echo '<p>Based on the first ' . $dim . ' days available.</p>'; }

// Navigation
$prevs = $sproc - 86400 * $dim; $nexts = $sproc + 86400 * (2 + $num_adv);
echo '<table width="800"><tr><td align="left">';
if ($sproc1 > Date::mkdate(2, 10, 2009) && $sproc < Date::mkdate((int)Date::$dmonth, (int)Date::$dday, (int)Date::$dyear) && !$toofar) {
	echo '<a href="/wxhistmonth.php?year=' . date('Y', $prevs) . '&amp;month=' . date('n', $prevs) . '" title="Previous month">&lt;&lt;Previous Month</a>';
} else { echo '&lt;&lt;Previous Month'; }
echo '</td><td align="center"><form method="get" action="">';
HTML::dateFormMaker($yproc, $mproc);
echo '<input type="submit" value="View Report" /></form> <a href="/wxhistmonth.php" title="Most recent month">Reset</a></td><td align="right">';
if ($sproc < Date::mkdate((int)Date::$dmonth, (int)Date::$dday - 1, (int)Date::$dyear) && Date::mkdate($mproc, 3, $yproc) > Date::mkdate(1, 1, 2009)) {
	echo '<a href="/wxhistmonth.php?year=' . date('Y', $nexts) . '&amp;month=' . date('n', $nexts) . '" title="Next month">Next Month&gt;&gt;</a>';
} else { echo 'Next Month&gt;&gt;'; }
echo '</td></tr></table>';

if (!$haveData) {
	echo '<p>Monthly report not available.</p>';
	Page::End();
	return;
}

$fmt = function ($v, $unit) { return (is_numeric($v)) ? Wx::conv($v, $unit, true) : $v; };

// ---- Summary table (column order: Measure | Mean/Sum (anom) | Min | Max | Mean Time) ----
HTML::table();
HTML::tableHead('Weather Summary', 5);
HTML::tr();
HTML::td('Measure', null, 26); HTML::td('Mean / Sum (anomaly)', null, 22); HTML::td('Min', null, 22); HTML::td('Max', null, 22); HTML::td('Mean Time', null, 7);
HTML::tr_end();
$order = [2, 0, 1]; // mean, min, max
for ($i = 0; $i < $NCOL; $i++) {
	$c = 'td' . ($CNUM[$i] + 10) . 'C';
	HTML::tr('row' . HTML::colcol($i));
	HTML::td($DESC[$i], $c);
	foreach ($order as $idx) {
		$disp = ($i == 12) ? $msdat[12][$idx] : $fmt($msdat[$i][$idx], $UNIT[$i]);
		$cell = ($mtdat[$i][$idx] !== '') ? HTML::acronym($mtdat[$i][$idx], $disp) : $disp;
		HTML::td($cell . $manom[$i][$idx], $c);
	}
	HTML::td($msdatt[$i], $c);
	HTML::tr_end();
}
// Range rows
$rangeDesc = ['Temperature Range', 'Humidity Range', 'Pressure Range'];
$rangeUnit = [Wx::AbsTemp, Wx::Humidity, Wx::Pressure];
$rangeCNum = [4, 0, 6];
for ($x = 0; $x < 3; $x++) {
	$c = 'td' . ($rangeCNum[$x] + 10) . 'C';
	HTML::tr('row' . HTML::colcol($x));
	HTML::td($rangeDesc[$x], $c);
	foreach ($order as $idx) { HTML::td($fmt($msdatx[$x][$idx], $rangeUnit[$x]) . $manomx[$x][$idx], $c); }
	HTML::td('', $c);
	HTML::tr_end();
}
// Manual sun/wet
$mDesc = ['Sun Hours', 'Wet Hours'];
for ($i = 0; $i < 2; $i++) {
	HTML::tr('row' . HTML::colcol($i));
	HTML::td($mDesc[$i], 'td18C');
	HTML::td($msdatm[$i][2] . $manomm[$i][2], 'td18C');
	HTML::td('', 'td18C'); HTML::td('', 'td18C'); HTML::td('', 'td18C');
	HTML::tr_end();
}
HTML::table_end();
echo '<p><b>NB:</b> Hover over a value to see how many days of data it is from. Sun hrs, Wet hrs, Cloud cover and Events are based on manual observation and provided for interest only.</p>';

// ---- Days Of... ----
$daysofRn = []; $daysofRnD = [];
$threshsRn = [0, 0.1, 0.3, 1, 5, 10, 20];
for ($i = 0; $i < count($threshsRn); $i++) {
	$daysofRn[$i] = Util::cond_count(isset($mdat[13]) ? $mdat[13] : [], true, $threshsRn[$i]);
	$daysofRnD[$i] = '&gt; ' . Wx::conv($threshsRn[$i], Wx::Rain, true);
}
$daysofTemp = []; $daysofTempD = [];
$threshsTemp = [-5, 0, 0, 20, 25, 30]; $typeTemp = [0, 0, 2, 2, 1, 1];
$tlabels = ['Min', 'Min', 'Mean', 'Mean', 'Max', 'Max'];
for ($i = 0; $i < count($typeTemp); $i++) {
	$daysofTemp[$i] = Util::cond_count(isset($mdat[$typeTemp[$i]]) ? $mdat[$typeTemp[$i]] : [], $i > 2, $threshsTemp[$i]);
	$daysofTempD[$i] = $tlabels[$i] . ' &' . ($i > 2 ? 'g' : 'l') . 't; ' . Wx::conv($threshsTemp[$i], Wx::Temperature, true);
}
if ($mproc > 4 && $mproc < 10) { $daysofTemp[6] = Util::cond_count(isset($mdat[20]) ? $mdat[20] : [], true, 20); $daysofTempD[6] = 'Min &gt; ' . Wx::conv(20, Wx::Temperature, true); }
else { $daysofTemp[6] = Util::cond_count(isset($mdat[21]) ? $mdat[21] : [], false, 0); $daysofTempD[6] = 'Ice Days'; }

$daysofSun = []; $daysofSunD = [];
$threshsSun = [0, 1, 3, 5, 10];
for ($i = 0; $i < count($threshsSun); $i++) {
	$daysofSun[$i] = Util::cond_count(isset($mdatm[0]) ? array_map('floatval', $mdatm[0]) : [], true, $threshsSun[$i]);
	$daysofSunD[$i] = '&gt; ' . $threshsSun[$i] . ' hrs';
}
$daysofSun[5] = $daysofSun[6] = 0;
$sunArr = isset($mdatm[0]) ? $mdatm[0] : []; $days = count($sunArr);
for ($i = 0; $i < $days; $i++) {
	$zi = (int)date('z', $sproc1 + 86400 * $i);
	$maxSun = isset($sunLines[$zi]) ? (float)$sunLines[$zi] : 0;
	$val = (float)$sunArr[$days - $i - 1];
	if ($maxSun > 0) { $daysofSun[5] += (int)($val > 0.25 * $maxSun); $daysofSun[6] += (int)($val > 0.95 * $maxSun); }
}
$daysofSunD[5] = '&gt; 25&#37; max poss.'; $daysofSunD[6] = '&gt; 95&#37; max poss.';

$daysofOther = []; $daysofOtherD = [];
$mDescM = ['Falling Snow', 'Lying Snow', 'Hail', 'Thunder', 'Dense Fog'];
$daysofOther[6] = 0;
for ($i = 0; $i < 5; $i++) {
	$daysofOther[$i] = (int)Util::cond_count(isset($mdatm[$i + 3]) ? array_map('floatval', $mdatm[$i + 3]) : [], true, 0);
	$daysofOther[6] += $daysofOther[$i];
	$daysofOtherD[$i] = $mDescM[$i];
}
$daysofOther[5] = Util::cond_count(isset($mdat[11]) ? $mdat[11] : [], true, 30);
$daysofOtherD[5] = 'Gusts &gt; 30mph'; $daysofOther[6] += $daysofOther[5]; $daysofOtherD[6] = 'Total Events';

HTML::table();
HTML::tableHead('Days Of...', 8);
HTML::tr();
foreach (['Rainfall', 'Temperature', 'Sunshine', 'Other'] as $h) { HTML::td($h, null, '25%', 2); }
HTML::tr_end();
$groups = [['Rn', $daysofRn, $daysofRnD, 12], ['Temp', $daysofTemp, $daysofTempD, 14], ['Sun', $daysofSun, $daysofSunD, 18], ['Other', $daysofOther, $daysofOtherD, 4]];
for ($i = 0; $i < 7; $i++) {
	HTML::tr('row' . HTML::colcol($i));
	foreach ($groups as $g) {
		HTML::td(isset($g[2][$i]) ? $g[2][$i] : '', 'td' . $g[3] . 'C', '17%');
		HTML::td(isset($g[1][$i]) ? $g[1][$i] : '', 'td' . $g[3] . 'C', '8%');
	}
	HTML::tr_end();
}
HTML::table_end();

// ---- Misc ----
$snowfall = 0;
if (isset($mdatm[3])) {
	foreach ($mdatm[3] as $d => $sn) { $snowfall += ($sn === 'y') ? (isset($mdat[13][$d]) ? (float)$mdat[13][$d] : 0) : (float)$sn; }
}
echo '<h2>Misc.</h2>';
if ($daysofRn[2] > 0) {
	echo 'Mean rainfall for days with &gt;0.3mm: ' . Wx::conv($msdat[13][2] / $daysofRn[2], Wx::Rain, true) . '<br />';
	echo 'Total snowfall: ' . Wx::conv($snowfall, Wx::Rain, true) . '<br />';
} else {
	echo 'Nothing to report<br />';
}

// ---- Charts (replacing legacy JPGraph embeds) ----
echo '<h2>Graphs and Charts</h2>';
Charts::daily(['type' => 'tmean', 'mode' => 'daily', 'year' => $yproc, 'month' => $mproc], ['height' => 360]);
echo '<p><a href="/charts.php?vartype=tmean&year=' . $yproc . '&month=' . $mproc . '">View more charts for this month</a></p>';
echo '<h3>Daily detail across the month</h3>';
$lastStamp = date('Ymd', $sproc);
Charts::intradayPanel(['date' => $lastStamp, 'num' => $dim], null, ['height' => 420]);
echo '<h2>Wind rose</h2>';
Charts::rose(['st' => $yproc . Util::zerolead($mproc) . '01', 'en' => 'month'], ['height' => 460]);

echo '<p><a href="/wxhistday.php?day=1&amp;month=' . $mproc . '&amp;year=' . $yproc . '" title="Daily report for 1st ' . Date::monthfull($mproc) . ' ' . $yproc . '">View daily breakdown for the month</a></p>';

Page::End();
