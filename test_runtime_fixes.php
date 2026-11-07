<?php
/**
 * Test runtime fixes for PR#52 revision 5ee3ed1+ runtime failures.
 */

error_reporting(E_ALL);
$passed = 0; $failed = 0;
function test($desc, $cond) {
	global $passed, $failed;
	if ($cond) { echo "✓ PASS: $desc\n"; $passed++; }
	else { echo "✗ FAIL: $desc\n"; $failed++; }
}

echo "Runtime Fix Validation Tests\n";
echo str_repeat("=", 70) . "\n\n";

// Test 1: ChartHelper.php label fix
echo "Test 1: ChartHelper.php label access\n";
echo str_repeat("-", 70) . "\n";
$chartHelper = file_get_contents(__DIR__ . '/site/ChartHelper.php');
test('ChartHelper uses $vars[$default][\'label\'] not [\'description\']',
	preg_match('/\$vars\[\$default\]\[\'label\'\]/', $chartHelper) === 1);
test('ChartHelper does NOT use $vars[$default][\'description\']',
	preg_match('/\$vars\[\$default\]\[\'description\'\]/', $chartHelper) === 0);
test('ChartHelper maps varOptions keys to labels',
	preg_match('/\$varOptions\[\$k\]\s*=\s*\$v\[\'label\'\]/', $chartHelper) === 1);
test('ChartHelper uses $varOptions in groups array',
	preg_match('/\'options\'\s*=>\s*\$varOptions/', $chartHelper) === 1);

echo "\n";

// Test 2: histdata.php $cumeSeries fix
echo "Test 2: histdata.php cumulative envelope variable\n";
echo str_repeat("-", 70) . "\n";
$histdata = file_get_contents(__DIR__ . '/site/histdata.php');
test('addCumulativeEnvelope uses $thisYear not $cumeSeries',
	preg_match('/addCumulativeEnvelope\([^)]*\$thisYear/', $histdata) === 1);
test('No undefined $cumeSeries references',
	preg_match('/\$cumeSeries/', $histdata) === 0);

echo "\n";

// Test 3: wxcharts.js envelope/cume independence
echo "Test 3: wxcharts.js envelope/cumulative independence\n";
echo str_repeat("-", 70) . "\n";
$wxcharts = file_get_contents(__DIR__ . '/site/wxcharts.js');
// Extract just the envBtn click handler
$envStart = strpos($wxcharts, '$(envBtn).on(\'click\'');
$envEnd = strpos($wxcharts, '});', $envStart) + 3;
$envHandler = substr($wxcharts, $envStart, $envEnd - $envStart);
test('Envelope click does NOT set state.cume = false',
	preg_match('/state\.cume\s*=\s*false/', $envHandler) === 0);
// Extract just the cumeBtn click handler
$cumeStart = strpos($wxcharts, '$(cumeBtn).on(\'click\'');
$cumeEnd = strpos($wxcharts, '});', $cumeStart) + 3;
$cumeHandler = substr($wxcharts, $cumeStart, $cumeEnd - $cumeStart);
test('Cumulative click does NOT set state.env = false',
	preg_match('/state\.env\s*=\s*false/', $cumeHandler) === 0);
test('showEnv allows envelope without !state.cume condition',
	preg_match('/showEnv.*=.*daily.*yr.*envelope.*&&\s*!state\.cume/', $wxcharts) === 0);
test('Envelope handler has comment about cumulative support',
	preg_match('/cumulative.*envelope.*support/i', $wxcharts) === 1);

echo "\n";

// Test 4: RankFlags version check
echo "Test 4: RankFlags version check\n";
echo str_repeat("-", 70) . "\n";
$rankFlags = file_get_contents(__DIR__ . '/site/RankFlags.php');
test('RankFlags load() rejects cache when version absent',
	preg_match('/!isset\(\$data\[\'version\'\]\).*return\s+null/s', $rankFlags) === 1);
test('RankFlags load() checks version mismatch',
	preg_match('/\$data\[\'version\'\].*!==.*CACHE_VERSION.*return\s+null/', $rankFlags) === 1);

echo "\n";

// Test 5: CumulativeRecords calendar alignment
echo "Test 5: CumulativeRecords calendar date alignment\n";
echo str_repeat("-", 70) . "\n";
$cumeRec = file_get_contents(__DIR__ . '/site/CumulativeRecords.php');
test('CumulativeRecords uses [m][d] structure not [doy]',
	preg_match('/\$cumeSums\[\$y\]\[\$m\]\[\$d\]/', $cumeRec) === 1);
test('CumulativeRecords comment mentions calendar date alignment',
	preg_match('/calendar date.*alignment|aligned by calendar date/i', $cumeRec) === 1);
test('CumulativeRecords stops on missing data',
	preg_match('/\$hasMissing\s*=\s*true.*break/s', $cumeRec) === 1);
test('CumulativeRecords rejects incomplete years ending before Dec',
	preg_match('/\$lastValidMD.*<\s*12.*unset.*cumeSums/s', $cumeRec) === 1);
test('CumulativeRecords return structure uses [m][d]',
	preg_match('/\$lo\[\$m\]\[\$d\].*=.*min/', $cumeRec) === 1);

echo "\n";

// Test 6: addCumulativeEnvelope calendar alignment
echo "Test 6: addCumulativeEnvelope calendar date usage\n";
echo str_repeat("-", 70) . "\n";
test('addCumulativeEnvelope gets month/day from timestamp',
	preg_match('/date\([\'"]n[\'"]\s*,\s*\$ts\).*date\([\'"]j[\'"]\s*,\s*\$ts\)/s', $histdata) === 1);
test('addCumulativeEnvelope accesses rec[\'lo\'][$m][$d]',
	preg_match('/\$rec\[\'lo\'\]\[\$m\]\[\$d\]/', $histdata) === 1);
test('addCumulativeEnvelope accesses rec[\'hi\'][$m][$d]',
	preg_match('/\$rec\[\'hi\'\]\[\$m\]\[\$d\]/', $histdata) === 1);
test('addCumulativeEnvelope comment mentions calendar date alignment',
	preg_match('/calendar date.*alignment|Use calendar date/i', $histdata) === 1);

echo "\n";

// Summary
echo str_repeat("=", 70) . "\n";
echo "Test Summary\n";
echo str_repeat("=", 70) . "\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "\n";

if ($failed > 0) {
	echo "❌ SOME RUNTIME FIX TESTS FAILED\n";
	exit(1);
} else {
	echo "✅ ALL RUNTIME FIX TESTS PASSED\n";
	exit(0);
}
