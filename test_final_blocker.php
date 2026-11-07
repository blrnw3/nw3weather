<?php
/**
 * Test final blocker fix: incomplete December years rejection.
 */

error_reporting(E_ALL);
$passed = 0; $failed = 0;
function test($desc, $cond) {
	global $passed, $failed;
	if ($cond) { echo "✓ PASS: $desc\n"; $passed++; }
	else { echo "✗ FAIL: $desc\n"; $failed++; }
}

echo "Final Blocker: Complete Year Validation\n";
echo str_repeat("=", 70) . "\n\n";

$cumeRec = file_get_contents(__DIR__ . '/site/CumulativeRecords.php');

// Blocker fix: Require last date exactly Dec 31
test('Checks lastValidMD[0] === 12 (month)',
	preg_match('/\$lastValidMD\[0\]\s*===\s*12/', $cumeRec) === 1);

test('Checks lastValidMD[1] === 31 (day)',
	preg_match('/\$lastValidMD\[1\]\s*===\s*31/', $cumeRec) === 1);

test('Both checks in same condition (AND)',
	preg_match('/\$lastValidMD\[0\]\s*===\s*12\s*&&\s*\$lastValidMD\[1\]\s*===\s*31/', $cumeRec) === 1);

test('Discards yearCume upon missing data',
	preg_match('/\$yearCume\s*=\s*\[\].*discard/is', $cumeRec) === 1);

test('Comment mentions "discard entire temporary year upon ANY missing day"',
	preg_match('/discard entire temporary year upon ANY missing day/i', $cumeRec) === 1);

test('Comment mentions "every expected calendar day" or "exactly Dec 31"',
	preg_match('/every expected calendar day|exactly Dec 31/i', $cumeRec) === 1);

test('Cache version is 3',
	preg_match('/const\s+CACHE_VERSION\s*=\s*3/', $cumeRec) === 1);

test('Cache version is NOT 2',
	preg_match('/const\s+CACHE_VERSION\s*=\s*2/', $cumeRec) === 0);

test('No pre-validation of December (removed)',
	preg_match('/Pre-validate.*December|check if year has data through December/is', $cumeRec) === 0);

echo "\n";
echo str_repeat("=", 70) . "\n";
echo "Test Summary\n";
echo str_repeat("=", 70) . "\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "\n";

if ($failed > 0) {
	echo "❌ SOME TESTS FAILED\n";
	exit(1);
} else {
	echo "✅ ALL TESTS PASSED\n";
	exit(0);
}
