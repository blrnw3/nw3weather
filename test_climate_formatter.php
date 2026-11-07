<?php
/**
 * Test ClimateCard mean wind: 1dp formatting and cache invalidation.
 */

error_reporting(E_ALL);
$passed = 0; $failed = 0;
function test($desc, $cond) {
	global $passed, $failed;
	if ($cond) { echo "✓ PASS: $desc\n"; $passed++; }
	else { echo "✗ FAIL: $desc\n"; $failed++; }
}

echo "Climate Card Mean Wind Formatter & Cache Tests\n";
echo str_repeat("=", 70) . "\n\n";

$climateCard = file_get_contents(__DIR__ . '/site/ClimateCard.php');

echo "Formatter Tests\n";
echo str_repeat("-", 70) . "\n";

// Extract fmt() function
if (preg_match('/private static function fmt.*?\n\s*\}/ms', $climateCard, $m)) {
	$fmtFunc = $m[0];
	
	test('fmt() function found', true);
	
	// Check Wind unit uses 1 decimal place (not -1)
	test('Wind formatter uses 1 decimal place',
		preg_match('/Wx::Wind.*Wx::conv\([^,]+,[^,]+,[^,]+,[^,]+,\s*1\s*\)/', $fmtFunc) === 1);
	
	test('Wind formatter does NOT use -1 (whole numbers)',
		strpos($fmtFunc, ', -1)') === false);
	
	test('Wind formatter comment mentions 1 decimal place',
		preg_match('/1 decimal place|1dp|1 dp/i', $fmtFunc) === 1);
	
} else {
	test('fmt() function found', false);
}

echo "\n";

echo "Cache Version Tests\n";
echo str_repeat("-", 70) . "\n";

test('CACHE_VERSION is 5 (not 4)',
	preg_match('/const\s+CACHE_VERSION\s*=\s*5/', $climateCard) === 1);

test('CACHE_VERSION is NOT 4',
	preg_match('/const\s+CACHE_VERSION\s*=\s*4/', $climateCard) === 0);

echo "\n";

echo "Mean Wind Row Configuration\n";
echo str_repeat("-", 70) . "\n";

// Verify wmean row still correct
if (preg_match('/' . "'wmean'" . '\s*=>\s*array\((.*?)\),/s', $climateCard, $m)) {
	$wmeanRow = $m[0];
	
	test('wmean has dp => 1',
		preg_match('/["\']dp["\']\s*=>\s*1/', $wmeanRow) === 1);
	
	test('wmean has lo => true (LOW records)',
		preg_match('/["\']lo["\']\s*=>\s*true/', $wmeanRow) === 1);
	
	test('wmean has hi => true (HIGH records)',
		preg_match('/["\']hi["\']\s*=>\s*true/', $wmeanRow) === 1);
}

echo "\n";

echo str_repeat("=", 70) . "\n";
echo "Test Summary\n";
echo str_repeat("=", 70) . "\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "\n";

if ($failed > 0) {
	echo "❌ SOME FORMATTER TESTS FAILED\n";
	exit(1);
} else {
	echo "✅ ALL FORMATTER TESTS PASSED\n";
	exit(0);
}
