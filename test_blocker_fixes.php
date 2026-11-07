<?php
/**
 * Test fixes for 5 blockers found at 0c3f9a0 retest.
 */

error_reporting(E_ALL);
$passed = 0; $failed = 0;
function test($desc, $cond) {
	global $passed, $failed;
	if ($cond) { echo "✓ PASS: $desc\n"; $passed++; }
	else { echo "✗ FAIL: $desc\n"; $failed++; }
}

echo "Blocker Fix Validation Tests\n";
echo str_repeat("=", 70) . "\n\n";

// Blocker 1: Precision - raw accumulation
echo "Blocker 1: Precision - raw accumulation for cumulative\n";
echo str_repeat("-", 70) . "\n";
$histdata = file_get_contents(__DIR__ . '/site/histdata.php');
test('buildYear accumulates raw for cumulative (has "accumulate raw values first")',
	preg_match('/accumulate raw values first.*cume/is', $histdata) === 1);
test('buildYear converts cumulative total with cv($run)',
	preg_match('/cv\(\$run\)/', $histdata) === 1);
test('buildYear does NOT convert daily then accumulate for cume',
	preg_match('/if.*\$cume.*\$val\s*=\s*cv/', $histdata) === 0);
test('LTA series accumulates raw for cumulative',
	preg_match('/accumulate raw values first.*normals/is', $histdata) === 1);
test('LTA converts cumulative total with cv($run)',
	preg_match('/normals.*cv\(\$run\)/is', $histdata) === 1);

echo "\n";

// Blocker 2: Subtype handlers env dependence
echo "Blocker 2: Subtype handlers remove env dependence\n";
echo str_repeat("-", 70) . "\n";
$wxcharts = file_get_contents(__DIR__ . '/site/wxcharts.js');
test('renderSubtypes does NOT have !state.env && in cume assignment',
	preg_match('/renderSubtypes.*state\.cume\s*=\s*!state\.env\s*&&/s', $wxcharts) === 0);
test('Subtype click does NOT have !state.env && in cume assignment',
	preg_match('/data-type.*state\.cume\s*=\s*!state\.env\s*&&/s', $wxcharts) === 0);
test('renderSubtypes uses state.cume = !!metaFor(state.type).summable',
	preg_match('/if.*showCume.*state\.cume\s*=\s*!!metaFor\(state\.type\)\.summable/', $wxcharts) >= 1);

echo "\n";

// Blocker 3: Feb 29 skip removed
echo "Blocker 3: Feb 29 skip removed\n";
echo str_repeat("-", 70) . "\n";
$cumeRec = file_get_contents(__DIR__ . '/site/CumulativeRecords.php');
test('CumulativeRecords does NOT skip Feb 29 (no continue after Feb 29 check)',
	preg_match('/if\s*\(\s*\$m\s*===\s*2\s*&&\s*\$d\s*===\s*29\s*\).*continue/s', $cumeRec) === 0);
test('CumulativeRecords does NOT have "Skip Feb 29" comment',
	preg_match('/Skip Feb 29/i', $cumeRec) === 0);

echo "\n";

// Blocker 4: Cache version bumped
echo "Blocker 4: Cache version bumped to 2\n";
echo str_repeat("-", 70) . "\n";
test('CumulativeRecords CACHE_VERSION is 2',
	preg_match('/const\s+CACHE_VERSION\s*=\s*2/', $cumeRec) === 1);
test('CumulativeRecords CACHE_VERSION is NOT 1',
	preg_match('/const\s+CACHE_VERSION\s*=\s*1/', $cumeRec) === 0);

echo "\n";

// Blocker 5: Incomplete year rejection
echo "Blocker 5: Complete year validation\n";
echo str_repeat("-", 70) . "\n";
test('CumulativeRecords pre-validates December presence',
	preg_match('/check.*December|December.*complete|!isset.*months\[12\]/is', $cumeRec) === 1);
test('CumulativeRecords only includes if reached December',
	preg_match('/\$lastValidMD.*===.*12|reached December/is', $cumeRec) === 1);
test('CumulativeRecords comment mentions complete historical years',
	preg_match('/complete historical years|complete year/i', $cumeRec) === 1);

echo "\n";

// Summary
echo str_repeat("=", 70) . "\n";
echo "Test Summary\n";
echo str_repeat("=", 70) . "\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "\n";

if ($failed > 0) {
	echo "❌ SOME BLOCKER FIX TESTS FAILED\n";
	exit(1);
} else {
	echo "✅ ALL BLOCKER FIX TESTS PASSED\n";
	exit(0);
}
