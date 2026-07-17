<?php
require "Page.php";
Page::init([
	"fileNum" => 85,
	"title" => "Daily Reports",
	"description" => "Detailed historical daily weather breakdown reports with graphs for Hampstead, London (NW3).",
	"isSubFile" => true,
]);
Page::Start();

// ---- Column metadata (matches the dat{year}.csv layout, 33 data columns) ----
$DESC = ['Minimum Temperature','Maximum Temperature','Mean Temperature','Minimum Humidity','Maximum Humidity','Mean Humidity',
	'Minimum Pressure','Maximum Pressure','Mean Pressure','Mean Wind Speed','Maximum Wind Speed','Maximum Gust','Mean Wind Direction',
	'Rainfall','Maximum Hourly Rain','Maximum 10-min Rain','Maximum Rain Rate','Minimum Dew Point','Maximum Dew Point','Mean Dew Point',
	'Night Minimum (21-09)','Day Maximum (09-21)','Max 10m Temp Rise','Max 1hr Temp Rise','Max 1hr Hum Rise','Max 10m Temp Fall',
	'Max 1hr Temp Fall','Max 1hr Hum Fall','Max 10m Wind Speed','Minimum Feels-like','Maximum Feels-like','Mean Feels-like','Air-frost Hrs'];
$T = Wx::Temperature; $H = Wx::Humidity; $P = Wx::Pressure; $W = Wx::Wind; $D = Wx::Direction; $R = Wx::Rain; $RR = Wx::RainRate; $HR = Wx::Hours;
$UNIT = [$T,$T,$T, $H,$H,$H, $P,$P,$P, $W,$W,$W, $D, $R,$R,$R, $RR, $T,$T,$T, $T,$T, $T,$T,$H,$T,$T,$H, $W, $T,$T,$T, $HR];
$CNUM = [4,4,4, 0,0,0, 6,6,6, 3,3,3,3, 2,2,2,2, 0,0,0, 4,4, 4,4,0,4,4,0, 3, 4,4,4, 4];
$NCOL = count($DESC);
$ccd = ['c'=>'Sunny','f'=>'Mostly Sunny','p'=>'Partly Cloudy','b'=>'Mostly Cloudy','o'=>'Overcast','-'=>'transitioned to',
	';'=>'with periods of','/'=>'or','h'=>'Hazy','u'=>'unknown'];

// ---- Date validation ----
$dproc = isset($_GET['day']) ? intval($_GET['day']) : (int)Date::$day_yest;
$mproc = isset($_GET['month']) ? intval($_GET['month']) : (int)Date::$mon_yest;
$yproc = isset($_GET['year']) ? intval($_GET['year']) : (int)Date::$yr_yest;
$badMessage = '';
if ($mproc < 1 || $mproc > 12 || $yproc < 2009 || $yproc > (int)Date::$dyear || $dproc < 1 || $dproc > 31) {
	$badMessage = 'most recent'; $dproc = (int)Date::$day_yest; $mproc = (int)Date::$mon_yest; $yproc = (int)Date::$yr_yest;
} else {
	$sproc = Date::mkdate($mproc, $dproc, $yproc);
	if ($sproc >= Date::mkdate((int)Date::$dmonth, (int)Date::$dday + 1, (int)Date::$dyear)) {
		$badMessage = 'most recent'; $dproc = (int)Date::$day_yest; $mproc = (int)Date::$mon_yest; $yproc = (int)Date::$yr_yest;
	} elseif ($sproc < Date::mkdate(2, 1, 2009)) {
		$badMessage = 'earliest'; $dproc = 1; $mproc = 2; $yproc = 2009;
	}
}
$sproc = Date::mkdate($mproc, $dproc, $yproc);
$stamp = date('Ymd', $sproc);
$mproc = (int)date('n', $sproc);
$dproc = (int)date('j', $sproc);
$zidx = (int)date('z', $sproc);
$isToday = (Date::mkdate() == $sproc);

// ---- Load CSV data for the chosen year ----
$readRow = function ($file, $line) {
	if (!is_file($file)) { return null; }
	$f = file($file);
	return isset($f[$line]) ? explode(',', $f[$line]) : null;
};
$datFile = ROOT . "dat$yproc.csv";
$dattFile = ROOT . "datt$yproc.csv";
$datmFile = ROOT . "datm$yproc.csv";
$anomFile = ROOT . 'tminmaxav.csv';
$sunFile = ROOT . 'maxsun.csv';

$ddat = $readRow($datFile, $zidx + 1);
$ddatt = $readRow($dattFile, $zidx + 1);
$ddatm = $readRow($datmFile, $zidx + 1);
$anomLines = is_file($anomFile) ? file($anomFile) : [];
$sunLines = is_file($sunFile) ? file($sunFile) : [];
$dtanom = isset($anomLines[$zidx]) ? explode(',', $anomLines[$zidx]) : array_fill(0, 4, 0);
$dtanom[3] = $dtanom[1] - $dtanom[0];

// Monthly long-term normals (from LTA)
$windav = LTA::$vars['wmean']['monthly'];
$rainav = LTA::$vars['rain']['monthly'];

if ($ddat === null) { $ddat = array_fill(0, $NCOL + 1, ''); }
if ($ddatt === null) { $ddatt = array_fill(0, $NCOL + 1, ''); }
if ($ddatm === null) { $ddatm = array_fill(0, 16, ''); }

// ---- Today: rebuild the row from live data ----
$todcond = true;
if ($isToday) {
	$todcond = (time() > mktime(0, 6));
	$N = Live::$NOW;
	if ($todcond && is_array($N)) {
		$g = function ($a, $b) { $N = Live::$NOW; return isset($N[$a][$b]) ? $N[$a][$b] : ''; };
		$ddat = [$g('min','temp'),$g('max','temp'),$g('mean','temp'), $g('min','humi'),$g('max','humi'),$g('mean','humi'),
			$g('min','pres'),$g('max','pres'),$g('mean','pres'), $g('mean','wind'),$g('max','wind'),$g('max','gust'),$g('mean','wdir'),
			$g('mean','rain'),$g('max','rnhr'),$g('max','rn10'),$g('max','rate'), $g('min','dewp'),$g('max','dewp'),$g('mean','dewp'),
			$g('min','night'),$g('max','day'), $g('max','tchange10'),$g('max','tchangehr'),$g('max','hchangehr'),
			$g('min','tchange10'),$g('min','tchangehr'),$g('min','hchangehr'), $g('max','w10m'),
			$g('min','feel'),$g('max','feel'),$g('mean','feel'), $g('misc','frosthrs')];
		$ddatt = [$g('timeMin','temp'),$g('timeMax','temp'),'', $g('timeMin','humi'),$g('timeMax','humi'),'',
			$g('timeMin','pres'),$g('timeMax','pres'),'', '',$g('timeMax','wind'),$g('timeMax','gust'),'',
			'',$g('timeMax','rnhr'),$g('timeMax','rn10'),$g('timeMax','rate'), $g('timeMin','dewp'),$g('timeMax','dewp'),'',
			$g('timeMin','night'),$g('timeMax','day'), $g('timeMax','tchange10'),$g('timeMax','tchangehr'),$g('timeMax','hchangehr'),
			$g('timeMin','tchange10'),$g('timeMin','tchangehr'),$g('timeMin','hchangehr'), $g('timeMax','w10m'),
			$g('timeMin','feel'),$g('timeMax','feel'),''];
	}
}

// ---- Month-to-date cumulative ----
$mcdat = array_fill(0, $NCOL, 0);
$mctanom = array_fill(0, 4, 0);
$datLines = is_file($datFile) ? file($datFile) : [];
for ($d = 0; $d < $dproc; $d++) {
	$ln = $zidx + 1 - $d;
	if (isset($datLines[$ln])) {
		$row = explode(',', $datLines[$ln]);
		for ($v = 0; $v < $NCOL; $v++) { if (isset($row[$v]) && is_numeric($row[$v])) { $mcdat[$v] += (float)$row[$v] / $dproc; } }
	}
	$az = $zidx - $d;
	if (isset($anomLines[$az])) {
		$ma = explode(',', $anomLines[$az]);
		for ($v = 0; $v < 2; $v++) { $mctanom[$v] += (float)$ma[$v] / $dproc; }
	}
}
$mcdat[13] *= $dproc; // rain accumulates as a total, not a mean

// ---- Records for this calendar date across years ----
$yrend = (Date::mkdate($mproc, $dproc, (int)Date::$dyear) < Date::mkdate((int)Date::$dmonth, (int)Date::$dday, (int)Date::$dyear))
	? (int)Date::$dyear : (int)Date::$dyear - 1;
$rec = [];
for ($y = 2009; $y <= $yrend; $y++) {
	$f = ROOT . "dat$y.csv";
	if (!is_file($f)) { continue; }
	$lines = file($f);
	$zy = (int)date('z', Date::mkdate($mproc, $dproc, $y));
	if (!isset($lines[$zy + 1])) { continue; }
	$row = explode(',', $lines[$zy + 1]);
	for ($v = 0; $v < $NCOL; $v++) {
		if (isset($row[$v]) && !Util::isBlank($row[$v])) { $rec[$v][$y] = (float)$row[$v]; }
	}
}
$hi = $lo = $av = $hiY = $loY = [];
$yrecHlite = function ($yr, $sel) { $col = ($yr == $sel) ? '#FF8989' : 'gray'; return ', <span style="color:' . $col . '">' . $yr . '</span>'; };
for ($v = 0; $v < $NCOL; $v++) {
	if (isset($rec[$v]) && is_array($rec[$v]) && count($rec[$v])) {
		$hi[$v] = max($rec[$v]); $lo[$v] = min($rec[$v]); $av[$v] = Util::mean($rec[$v]);
		$hiY[$v] = $yrecHlite(array_search($hi[$v], $rec[$v]), $yproc);
		$loY[$v] = $yrecHlite(array_search($lo[$v], $rec[$v]), $yproc);
	} else { $hi[$v] = $lo[$v] = 'n/a'; $hiY[$v] = $loY[$v] = ''; }
}

// ---- Formatting helpers ----
$blank = function ($v) { return $v === '' || $v === '-' || $v === null || !is_numeric($v); };
$fmt = function ($v, $unit) use ($blank) { return $blank($v) ? 'n/a' : Wx::conv($v, $unit, true); };
$anomT = function ($v, $n) { return ' (' . Wx::conv($v - $n, Wx::AbsTemp, false, true) . ')'; };

// Daily anomalies (temp/mean/wind only)
$ddanom = array_fill(0, $NCOL, '');
$mcanom = array_fill(0, $NCOL, '');
if (!$blank($ddat[0]) && !$blank($ddat[1])) {
	$ddanom[0] = $anomT($ddat[0], $dtanom[0]);
	$ddanom[1] = $anomT($ddat[1], $dtanom[1]);
	$ddanom[2] = ' (' . Wx::conv((($ddat[0] + $ddat[1]) - ($dtanom[0] + $dtanom[1])) / 2, Wx::AbsTemp, false, true) . ')';
	$mcanom[0] = $anomT($mcdat[0], $mctanom[0]);
	$mcanom[1] = $anomT($mcdat[1], $mctanom[1]);
	$mcanom[2] = ' (' . Wx::conv((($mcdat[0] + $mcdat[1]) - ($mctanom[0] + $mctanom[1])) / 2, Wx::AbsTemp, false, true) . ')';
}
if (!$blank($ddat[9])) {
	$ddanom[9] = ' (' . Wx::conv($ddat[9] - $windav[$mproc - 1], Wx::Wind, false, true) . ')';
	$mcanom[9] = ' (' . Wx::conv($mcdat[9] - $windav[$mproc - 1], Wx::Wind, false, true) . ')';
}
$daysInMon = (int)date('t', $sproc);
if ($rainav[$mproc - 1] > 0) {
	$mcanom[13] = ' (' . round(100 * $mcdat[13] / ($rainav[$mproc - 1] / $daysInMon * $dproc)) . '%)';
}
$hianom = $loanom = array_fill(0, $NCOL, '');
for ($i = 0; $i < 2; $i++) {
	if (is_numeric($hi[$i])) { $hianom[$i] = $anomT($hi[$i], $dtanom[$i]); }
	if (is_numeric($lo[$i])) { $loanom[$i] = $anomT($lo[$i], $dtanom[$i]); }
}

// Wind direction text
if (isset($ddat[12]) && intval($ddat[12]) > 0) { $ddat[12] = $ddat[12] . '&deg; [' . Wx::degname((int)$ddat[12]) . ']'; }

// Range (extra) section
$ddatx = $mcdatx = $hix = $lox = $hiYx = $loYx = $ddanomx = [];
$rangeUnits = [Wx::AbsTemp, Wx::Humidity, Wx::Pressure];
$rangeDesc = ['Temperature Range', 'Humidity Range', 'Pressure Range'];
$rangePairs = [[0, 1], [3, 4], [6, 7]];
$recx = [];
for ($y = 2009; $y <= $yrend; $y++) {
	foreach ($rangePairs as $xi => $pair) {
		if (isset($rec[$pair[0]][$y], $rec[$pair[1]][$y])) { $recx[$xi][$y] = $rec[$pair[1]][$y] - $rec[$pair[0]][$y]; }
	}
}
for ($xi = 0; $xi < 3; $xi++) {
	$p = $rangePairs[$xi];
	$ddatx[$xi] = (!$blank($ddat[$p[0]]) && !$blank($ddat[$p[1]])) ? ($ddat[$p[1]] - $ddat[$p[0]]) : '';
	$mcdatx[$xi] = $mcdat[$p[1]] - $mcdat[$p[0]];
	$hix[$xi] = isset($recx[$xi]) && count($recx[$xi]) ? max($recx[$xi]) : 'n/a';
	$lox[$xi] = isset($recx[$xi]) && count($recx[$xi]) ? min($recx[$xi]) : 'n/a';
	$hiYx[$xi] = (isset($recx[$xi]) && count($recx[$xi])) ? $yrecHlite(array_search($hix[$xi], $recx[$xi]), $yproc) : '';
	$loYx[$xi] = (isset($recx[$xi]) && count($recx[$xi])) ? $yrecHlite(array_search($lox[$xi], $recx[$xi]), $yproc) : '';
}
$ddanomx0 = (!$blank($ddat[0]) && !$blank($ddat[1])) ? ' (' . Wx::conv(($ddat[1] - $ddat[0]) - $dtanom[3], Wx::AbsTemp, false, true) . ')' : '';

// ---- Manual observations decode ----
$decodeCloud = function ($ts) use ($ccd) {
	if ($ts === '' || $ts === null) { return 'not available'; }
	if (strpos($ts, ':') !== false) {
		$parts = explode(':', $ts); $out = [];
		foreach ($parts as $pi => $p) {
			if (strlen($p) > 1) { $cs = str_split($p); $out[$pi] = trim(($ccd[$cs[0]] ?? '') . ' ' . ($ccd[$cs[1]] ?? '') . ' ' . (isset($cs[2]) ? ($ccd[$cs[2]] ?? '') : '')); }
			else { $out[$pi] = $ccd[$p] ?? $p; }
		}
		return 'am: ' . ($out[0] ?? '') . '<br /> pm: ' . ($out[1] ?? '');
	}
	if (strlen($ts) > 1) { $cs = str_split($ts); return trim(($ccd[$cs[0]] ?? '') . ' ' . ($ccd[$cs[1]] ?? '') . ' ' . (isset($cs[2]) ? ($ccd[$cs[2]] ?? '') : '')); }
	if (strlen($ts) > 0) { return $ccd[$ts] ?? $ts; }
	return 'not available';
};
$cloudText = $decodeCloud($ddatm[2] ?? '');

$events = []; $afFlag = (isset($ddat[20]) && is_numeric($ddat[20]) && $ddat[20] < 0);
$sn = $ddatm[3] ?? ''; if ($sn !== '') { $events[] = 'Snowfall' . ($sn === '0.1' ? ' (trace)' : ''); }
$ly = $ddatm[4] ?? ''; if ($ly !== '') { $events[] = 'Lying Snow' . ($ly === '0.1' ? ' (trace)' : ''); }
$hl = $ddatm[5] ?? ''; if ($hl !== '') { $events[] = 'Hail (' . ($hl == '1' ? 'small' : ($hl == '2' ? 'medium' : 'large')) . ' stones)'; }
$th = $ddatm[6] ?? ''; if ($th !== '') { $events[] = ($th == 1 ? 'Thunder' : ($th == '2' ? 'Light Thunderstorm' : ($th == '3' ? 'Moderate Thunderstorm' : 'Severe Thunderstorm'))); }
$fg = $ddatm[7] ?? ''; if ($fg !== '') { $events[] = 'Dense Fog'; }
if ($afFlag) { $events[] = 'Air Frost'; }
$eventsText = count($events) ? implode(', ', $events) : 'None';

$comments = trim($ddatm[8] ?? '');
$awayText = (isset($ddatm[11]) && $ddatm[11] == 1) ? 'Yes - observations may be unreliable' : 'No';
$pondText = (isset($ddatm[12]) && $ddatm[12] !== '') ? Wx::conv($ddatm[12], Wx::Temperature, true) : 'n/a';
$sunMax = isset($sunLines[$zidx]) ? (float)$sunLines[$zidx] : 0;
$sunVal = ($ddatm[0] ?? '') === 'b' ? 0 : ($ddatm[0] ?? '');
$sunText = is_numeric($sunVal) ? (round($sunVal, 1) . ' hrs' . ($sunMax > 0 ? ' [' . HTML::acronym('Out of ' . round($sunMax) . ' hrs possible', round($sunVal / $sunMax * 100) . '%', true) . ']' : '')) : 'n/a';
$wetText = is_numeric($ddatm[1] ?? '') ? round($ddatm[1], 1) . ' hrs' : 'n/a';
?>

<h1>Daily Report for <?php echo date('jS F Y', $sproc); ?></h1>
<?php
if ($badMessage) { echo "<p><b>Bad date specified. Defaulted to $badMessage report available.</b></p>"; }

// ---- Day navigation ----
$prevs = $sproc - 86400; $nexts = $sproc + 86400;
echo '<table width="800"><tr><td align="left">';
if ($sproc > Date::mkdate(2, 1, 2009)) {
	echo '<a href="/wxhistday.php?year=' . date('Y', $prevs) . '&amp;month=' . date('n', $prevs) . '&amp;day=' . date('j', $prevs) . '" title="View previous day">&lt;&lt;Previous Day</a>';
} else { echo '&lt;&lt;Previous Day'; }
echo '</td><td align="center"><form method="get" action="">';
HTML::dateFormMaker($yproc, $mproc, $dproc);
echo '<input type="submit" value="View Report" /></form> <a href="/wxhistday.php" title="Most recent day">Reset</a></td><td align="right">';
if ($sproc < Date::mkdate((int)Date::$dmonth, (int)Date::$dday, (int)Date::$dyear)) {
	echo '<a href="/wxhistday.php?year=' . date('Y', $nexts) . '&amp;month=' . date('n', $nexts) . '&amp;day=' . date('j', $nexts) . '" title="View next day">Next Day&gt;&gt;</a>';
} else { echo 'Next Day&gt;&gt;'; }
echo '</td></tr></table>';

if (!$todcond) {
	echo '<p>Daily breakdown not available until 09:07, when a partial report can be generated.</p>';
	Page::End();
	return;
}

$timeCell = function ($t) {
	if ($t === '' || $t === null) { return ''; }
	if (is_numeric($t) && $t < 1 && $t > 0) { return Date::decimal_timefix($t); }
	if (strpos($t, '*') === false) { return Date::timeformat($t); }
	return str_replace('*', '', $t);
};

echo '<table class="table1" width="98%" cellpadding="2" cellspacing="0">';
echo '<tr class="table-top"><td class="td4" width="22%">Measure</td><td class="td4" width="23%">Value (anomaly)</td><td class="td4" width="8%">Time</td>'
	. '<td class="td4" width="17%">Month cumul.</td><td class="td4" width="15%">Record High</td><td class="td4" width="15%">Record Low</td></tr>';

$renderRow = function ($i) use ($DESC, $UNIT, $CNUM, $ddat, $ddatt, $ddanom, $mcdat, $mcanom, $hi, $lo, $hiY, $loY, $hianom, $loanom, $fmt, $blank, $timeCell) {
	$c = 'td' . ($CNUM[$i] + 10) . 'C';
	$style = ($i % 2 == 0) ? 'light' : 'dark';
	$valDisp = ($i == 12) ? ($blank($ddat[12]) ? 'n/a' : $ddat[12]) : $fmt($ddat[$i], $UNIT[$i]);
	echo '<tr class="row' . $style . '">'
		. '<td width="22%" class="' . $c . '">' . $DESC[$i] . '</td>'
		. '<td width="23%" class="' . $c . '" style="font-size:105%">' . $valDisp . $ddanom[$i] . '</td>'
		. '<td width="8%" class="' . $c . '">' . $timeCell(isset($ddatt[$i]) ? $ddatt[$i] : '') . '</td>'
		. '<td width="17%" class="' . $c . '">' . $fmt($mcdat[$i], $UNIT[$i]) . $mcanom[$i] . '</td>'
		. '<td width="15%" class="' . $c . '" style="font-size:85%">' . $fmt($hi[$i], $UNIT[$i]) . $hianom[$i] . $hiY[$i] . '</td>'
		. '<td width="15%" class="' . $c . '" style="font-size:85%">' . $fmt($lo[$i], $UNIT[$i]) . $loanom[$i] . $loY[$i] . '</td>'
		. '</tr>';
};
for ($i = 0; $i < 20; $i++) { $renderRow($i); }

echo '<tr class="table-top"><td class="td4">Measure</td><td class="td4">Value</td><td class="td4">Time</td>'
	. '<td class="td4">Month cumul.</td><td class="td4">Record High</td><td class="td4">Record Low</td></tr>';
for ($i = 20; $i < $NCOL; $i++) { $renderRow($i); }

// Range section
echo '<tr class="table-top"><td class="td4">Measure</td><td class="td4" colspan="2">Value (anomaly)</td>'
	. '<td class="td4">Month cumul.</td><td class="td4">Record High</td><td class="td4">Record Low</td></tr>';
for ($xi = 0; $xi < 3; $xi++) {
	$style = ($xi % 2 == 0) ? 'light' : 'dark';
	$anom = ($xi === 0) ? $ddanomx0 : '';
	echo '<tr class="row' . $style . '">'
		. '<td width="22%" class="td14C">' . $rangeDesc[$xi] . '</td>'
		. '<td colspan="2" class="td14C" style="font-size:105%">' . $fmt($ddatx[$xi], $rangeUnits[$xi]) . $anom . '</td>'
		. '<td class="td14C">' . $fmt($mcdatx[$xi], $rangeUnits[$xi]) . '</td>'
		. '<td class="td14C" style="font-size:85%">' . $fmt($hix[$xi], $rangeUnits[$xi]) . $hiYx[$xi] . '</td>'
		. '<td class="td14C" style="font-size:85%">' . $fmt($lox[$xi], $rangeUnits[$xi]) . $loYx[$xi] . '</td>'
		. '</tr>';
}
echo '</table>';

// Manual observations
if ($isToday) {
	echo '<p class="note">Extra observations (cloud, events, comments) are added by the site administrator, typically within 24hrs.</p>';
} else {
	echo '<table class="table1" width="98%" cellpadding="2" cellspacing="0" style="margin-top:12px">';
	echo '<tr class="table-top"><td class="td4" width="22%">Observation</td><td class="td4" colspan="5">Detail</td></tr>';
	$obs = [['Sun Hours', $sunText], ['Wet Hours', $wetText], ['Cloud Cover', $cloudText],
		['Events', $eventsText], ['Comments', $comments !== '' ? $comments : 'None known'],
		['Observer Absent?', $awayText], ['Pond Temperature', $pondText]];
	foreach ($obs as $oi => $row) {
		$style = ($oi % 2 == 0) ? 'light' : 'dark';
		echo '<tr class="row' . $style . '"><td class="td10C">' . $row[0] . '</td><td colspan="5" class="td10C">' . $row[1] . '</td></tr>';
	}
	echo '</table>';
}

echo '<dl><dt>Notes</dt><dd>Detailed daily weather report for Hampstead, NW3, London on ' . date('jS F Y', $sproc) . '. '
	. 'Times of extremes use the midpoint of the longest continuous period at that value. '
	. 'Cumulative anomalies are relative to the expected value for the month-to-date. '
	. 'Sun and wet hours are derived from webcam/rainfall analysis and may be adjusted. Data should be viewed with appropriate caution.</dd></dl>';

echo '<p><a href="/wxhistmonth.php?month=' . $mproc . '&amp;year=' . $yproc . '" title="Monthly report for ' . Date::monthfull($mproc) . ' ' . $yproc . '">View monthly summary</a></p>';

// ---- Charts (replacing legacy JPGraph day graphs) ----
echo '<h2>Daily Graph of Conditions</h2>';
Charts::intradayPanel(['date' => $stamp, 'num' => 1], null, ['height' => 420]);
echo '<h3>Wind direction</h3>';
Charts::intraday(['date' => $stamp, 'num' => 1], 'wdir', ['height' => 280]);
echo '<h2>Wind rose</h2>';
Charts::rose(['st' => $stamp, 'en' => $stamp], ['height' => 460]);

// ---- Webcam / timelapse (best-effort, files live on mounted volumes) ----
echo '<h2>Webcam Summary of Cloud Conditions</h2>';
$camStamp = date('Y/Ymd', $sproc) . 'daily';
$endtag = ($sproc < Date::mkdate(6, 27, 2012)) ? 'gif' : 'jpg';
if (is_file(ROOT . $camStamp . 'webcam.' . $endtag)) {
	echo '<img src="/' . $camStamp . 'webcam.' . $endtag . '" alt="daycamsum" />';
} else {
	echo '<p>Webcam summary not available for this day.</p>';
}
if ($sproc > Date::mkdate(6, 20, 2018)) {
	echo '<p><a href="highreswebcam.php?year=' . $yproc . '&month=' . $mproc . '&day=' . $dproc . '&light=all&width=4&freq=30&frame=1&cycle">Full resolution images at up-to 5 minute intervals</a></p>';
}
if ($sproc > Date::mkdate(9, 23, 2016)) {
	echo '<div style="height:586px;margin:0.6em"><video id="tvid" width="864" height="576" controls>'
		. '<source src="/cam/timelapse/skycam_' . date('Ymd', $sproc) . '.mp4" type="video/mp4"></video></div>';
}

Page::End();
