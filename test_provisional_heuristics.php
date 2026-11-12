<?php
/**
 * Checks the ClimateCard 24h-mean projection against the Python study
 * (same climo table, same hour-boundary formula) and the v1 flag rules.
 *
 * Run: php test_provisional_heuristics.php
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

if (!function_exists('nw3_is_numeric')) {
	function nw3_is_numeric($v) { return $v !== null && $v !== '' && is_numeric($v); }
}

require_once __DIR__ . '/site/ClimateCard.php';

$passed = 0;
$failed = 0;
function check($desc, $cond, $detail = '') {
	global $passed, $failed;
	if ($cond) {
		echo "PASS  $desc\n";
		$passed++;
	} else {
		echo "FAIL  $desc" . ($detail !== '' ? "  ($detail)" : '') . "\n";
		$failed++;
	}
}

// --- Projection vs Python study (hour-boundary, minute=0) ---
// projectMean(month, hour, runningMean, currentTemp, minute=0)
// Python: n0 = minutes before H:00; these days are complete 1440-minute logs
// so n0=H*60, matching the default minutesSoFar.
$cases = array(
	// 2026-11-11, November
	array('2026-11-11 15:00', 11, 15, 8.448666666666667, 9.9, 8.362375),
	array('2026-11-11 16:00', 11, 16, 8.538645833333333, 9.8, 8.471056),
	array('2026-11-11 17:00', 11, 17, 8.600196078431373, 9.3, 8.491264),
	array('2026-11-11 18:00', 11, 18, 8.621388888888889, 8.9, 8.492625),
	// 2020-07-31, July (hot day)
	array('2020-07-31 15:00', 7, 15, 23.75577777777778, 35.8, 27.455111),
	array('2020-07-31 16:00', 7, 16, 24.5215625, 35.8, 27.403042),
	array('2020-07-31 18:00', 7, 18, 25.48953703703704, 30.0, 25.847111),
	// 2013-03-11, March (cold)
	array('2013-03-11 15:00', 3, 15, -0.4028888888888889, -0.3, -1.282056),
	array('2013-03-11 17:00', 3, 17, -0.4194117647058824, -0.8, -1.234375),
	// 2018-12-25, December
	array('2018-12-25 15:00', 12, 15, 2.356777777777778, 7.3, 3.788028),
	array('2018-12-25 18:00', 12, 18, 3.236759259259259, 7.7, 4.234819),
	// 2026-11-12 todaylog at 15:00–18:00 (complete hours)
	array('2026-11-12 15:00', 11, 15, 5.384555555555556, 7.0, 5.359806),
	array('2026-11-12 16:00', 11, 16, 5.4675, 6.3, 5.256958),
	array('2026-11-12 17:00', 11, 17, 5.492745098039216, 5.3, 5.123486),
	array('2026-11-12 18:00', 11, 18, 5.454351851851852, 4.2, 4.942347),
);

echo "Projection vs Python study\n";
foreach ($cases as $c) {
	list($label, $month, $hour, $run, $cur, $expect) = $c;
	$got = ClimateCard::projectMean($month, $hour, $run, $cur, 0);
	$err = ($got === null) ? INF : abs($got - $expect);
	check($label, $err < 5e-6, sprintf('got %.8f expect %.8f err %.2e', $got, $expect, $err));
}

// Mid-hour: remaining minutes of this hour + later hours, pin to current hour.
// At 16:30, n0=990, nRem=450, rem weights 30 of hour 16 + 60 of 17..23.
$m = 11; $H = 16; $min = 30;
$run = 5.5; $cur = 6.0;
$pin = ClimateMeanClimo::at($m, $H);
$wsum = 0; $sum = 0.0;
for ($k = 16; $k <= 23; $k++) {
	$w = ($k === 16) ? 30 : 60;
	$sum += ($cur + (ClimateMeanClimo::at($m, $k) - $pin)) * $w;
	$wsum += $w;
}
$expectMid = ($run * 990 + ($sum / $wsum) * 450) / 1440;
$gotMid = ClimateCard::projectMean($m, $H, $run, $cur, $min);
check('mid-hour 16:30 weighted remainder', abs($gotMid - $expectMid) < 5e-6,
	sprintf('got %.8f expect %.8f', $gotMid, $expectMid));

// Completed day: no remaining minutes → projection is the running mean.
check('24h complete returns running mean',
	abs(ClimateCard::projectMean(11, 23, 5.42, 3.9, 59, 1440) - 5.42) < 1e-12);

echo "\nLow day-max drop rule\n";
$dmCfg = array(
	'dp' => 1,
	'unit' => 'temp',
	'nameLo' => 'low day max',
	'flagLo' => array('from' => ClimateCard::LOW_DAYMAX_FROM_HOUR, 'final' => ClimateCard::DAYMAX_END_HOUR, 'drop' => ClimateCard::LOW_DAYMAX_DROP),
	'flagHi' => 'now',
);
$recs = array(
	'all' => array('lo' => array('v' => 0.0, 'n' => 1, 'at' => array('2010-01-01')), 'hi' => array('v' => 39.0, 'n' => 1, 'at' => array())),
	'month' => array('lo' => array('v' => 1.0, 'n' => 1, 'at' => array()), 'hi' => array('v' => 20.0, 'n' => 1, 'at' => array())),
	'day' => array('lo' => array('v' => 7.7, 'n' => 1, 'at' => array('2017-11-12')), 'hi' => array('v' => 16.3, 'n' => 1, 'at' => array())),
);
// Today: running day max 7.3 vs date record 7.7
$ctx15 = array('currentTemp' => 7.0, 'month' => 11, 'minute' => 0); // drop 0.3 < 0.5
$f = ClimateCard::flag($dmCfg, 'lo', 7.3, $recs, 15, $ctx15);
check('daymax lo at 15:00 with only 0.3°C drop is held', $f === null);

$ctx16 = array('currentTemp' => 6.3, 'month' => 11, 'minute' => 0); // drop 1.0
$f = ClimateCard::flag($dmCfg, 'lo', 7.3, $recs, 16, $ctx16);
check('daymax lo at 16:00 with 1.0°C drop is provisional record',
	is_array($f) && $f['status'] === ClimateCard::RECORD && $f['scope'] === 'day' && $f['soFar'] === true);

$ctx15b = array('currentTemp' => 6.8, 'month' => 11, 'minute' => 0); // drop 0.5 exactly
$f = ClimateCard::flag($dmCfg, 'lo', 7.3, $recs, 15, $ctx15b);
check('daymax lo at 15:00 with exactly 0.5°C drop is allowed',
	is_array($f) && $f['soFar'] === true && $f['status'] === ClimateCard::RECORD);

$ctx16shallow = array('currentTemp' => 7.0, 'month' => 11, 'minute' => 0); // drop 0.3, hour 16
$f = ClimateCard::flag($dmCfg, 'lo', 7.3, $recs, 16, $ctx16shallow);
check('daymax lo at 16:00 with only 0.3°C drop is still held (until 17:00)', $f === null);

$f = ClimateCard::flag($dmCfg, 'lo', 7.3, $recs, 17, $ctx16shallow);
check('daymax lo at 17:00 is shown even without the 0.5°C drop',
	is_array($f) && $f['soFar'] === true && $f['status'] === ClimateCard::RECORD);

$f = ClimateCard::flag($dmCfg, 'lo', 7.3, $recs, 21, $ctx16shallow);
check('daymax lo at 21:00 is confirmed (not provisional)',
	is_array($f) && $f['soFar'] === false && $f['status'] === ClimateCard::RECORD);

echo "\nAvg-temp projection flags (today 12 Nov, date record low 5.8°C)\n";
$tmCfg = array(
	'dp' => 1,
	'unit' => 'temp',
	'lo' => true, 'hi' => true,
	'flagLo' => array('from' => 15, 'final' => 24, 'project' => 'tmean', 'margin' => 0.5),
	'flagHi' => array('from' => 15, 'final' => 24, 'project' => 'tmean', 'margin' => 0.5),
);
$tmRecs = array(
	'all' => array('lo' => array('v' => -4.1, 'n' => 1, 'at' => array()), 'hi' => array('v' => 30.0, 'n' => 1, 'at' => array())),
	'month' => array('lo' => array('v' => -2.1, 'n' => 1, 'at' => array()), 'hi' => array('v' => 16.2, 'n' => 1, 'at' => array())),
	'day' => array('lo' => array('v' => 5.8, 'n' => 1, 'at' => array('2009-11-12')), 'hi' => array('v' => 14.5, 'n' => 1, 'at' => array())),
);
// 15:00: proj ≈ 5.360, conservative 5.860 is not < 5.8
$f = ClimateCard::flag($tmCfg, 'lo', 5.384555555555556, $tmRecs, 15, array('currentTemp' => 7.0, 'month' => 11, 'minute' => 0));
check('tmean lo at 15:00 is not flagged (projected+0.5 is not below 5.8)', $f === null);

// 16:00: proj ≈ 5.257, conservative 5.757 < 5.8 → record, provisional
$f = ClimateCard::flag($tmCfg, 'lo', 5.4675, $tmRecs, 16, array('currentTemp' => 6.3, 'month' => 11, 'minute' => 0));
check('tmean lo at 16:00 is a provisional date record',
	is_array($f) && $f['status'] === ClimateCard::RECORD && $f['scope'] === 'day' && $f['soFar'] === true);

// 18:00: proj ≈ 4.942, clearly a date record, still provisional (final is midnight)
$f = ClimateCard::flag($tmCfg, 'lo', 5.454351851851852, $tmRecs, 18, array('currentTemp' => 4.2, 'month' => 11, 'minute' => 0));
check('tmean lo at 18:00 is still provisional (confirmed at midnight)',
	is_array($f) && $f['status'] === ClimateCard::RECORD && $f['soFar'] === true);

// Before 15:00: no flag
$f = ClimateCard::flag($tmCfg, 'lo', 5.0, $tmRecs, 14, array('currentTemp' => 4.0, 'month' => 11, 'minute' => 0));
check('tmean lo before 15:00 is not flagged', $f === null);

// High: projected-0.5 must still beat the high record
$f = ClimateCard::flag($tmCfg, 'hi', 5.4675, $tmRecs, 16, array('currentTemp' => 6.3, 'month' => 11, 'minute' => 0));
check('tmean hi is not flagged on a cold day', $f === null);

// A clearly hot projection vs a date high of 14.5: use 31 Jul 2020 at 15:00, proj ≈ 27.46
$hotRecs = $tmRecs;
$hotRecs['day']['hi'] = array('v' => 14.5, 'n' => 1, 'at' => array());
$hotRecs['month']['hi'] = array('v' => 30.0, 'n' => 1, 'at' => array()); // not beaten after margin: 27.46-0.5=26.96 < 30
$f = ClimateCard::flag($tmCfg, 'hi', 23.75577777777778, $hotRecs, 15, array('currentTemp' => 35.8, 'month' => 7, 'minute' => 0));
check('tmean hi at 15:00 on 31 Jul 2020 is a provisional date record',
	is_array($f) && $f['status'] === ClimateCard::RECORD && $f['scope'] === 'day' && $f['soFar'] === true);

// Margin blocks a high that the raw projection only barely beats
$tight = $tmRecs;
$tight['day']['hi'] = array('v' => 27.2, 'n' => 1, 'at' => array()); // proj 27.46 would beat, 27.46-0.5=26.96 would not
$tight['month']['hi'] = array('v' => 30.0, 'n' => 1, 'at' => array());
$tight['all']['hi'] = array('v' => 30.0, 'n' => 1, 'at' => array());
$f = ClimateCard::flag($tmCfg, 'hi', 23.75577777777778, $tight, 15, array('currentTemp' => 35.8, 'month' => 7, 'minute' => 0));
check('tmean hi is held when projected-0.5 does not beat the record', $f === null);

echo "\nProjected-value marker\n";
$f = ClimateCard::flag($dmCfg, 'lo', 7.3, $recs, 16, $ctx16);
check('daymax flag is not marked projected', is_array($f) && empty($f['projected']));

$f = ClimateCard::flag($tmCfg, 'lo', 5.454351851851852, $tmRecs, 18, array('currentTemp' => 4.2, 'month' => 11, 'minute' => 0));
check('tmean provisional flag is marked projected',
	is_array($f) && !empty($f['projected']) && $f['value'] === 4.9);

// Completed day: remaining minutes 0, value is the running mean, not a projection
$f = ClimateCard::flag($tmCfg, 'lo', 5.2, $tmRecs, 23, array(
	'currentTemp' => 4.2, 'month' => 11, 'minute' => 0, 'minutesSoFar' => 1440,
));
check('completed-day tmean flag is not marked projected',
	is_array($f) && $f['status'] === ClimateCard::RECORD && empty($f['projected']) && $f['value'] === 5.2);

echo "\n$passed passed, $failed failed\n";
exit($failed ? 1 : 0);
