<?php
/**
 * Test ClimateCard mean wind changes: 1dp and LOW records populated.
 */

error_reporting(E_ALL);
$passed = 0; $failed = 0;
function test($desc, $cond) {
	global $passed, $failed;
	if ($cond) { echo "✓ PASS: $desc\n"; $passed++; }
	else { echo "✗ FAIL: $desc\n"; $failed++; }
}

echo "Climate Card Mean Wind Tests\n";
echo str_repeat("=", 70) . "\n\n";

$climateCard = file_get_contents(__DIR__ . '/site/ClimateCard.php');

echo "Mean Wind Configuration\n";
echo str_repeat("-", 70) . "\n";

// Extract wmean row definition
if (preg_match('/' ."'wmean'" . '\s*=>\s*array\((.*?)\),/s', $climateCard, $m)) {
	$wmeanRow = $m[0];
	echo "wmean row definition found\n";
	
	// 1dp rounding
	test('wmean has dp => 1 for 1 decimal place formatting',
		preg_match('/["\']dp["\']\s*=>\s*1/', $wmeanRow) === 1);
	
	// LOW records enabled
	test('wmean has lo => true to show LOW records',
		preg_match('/["\']lo["\']\s*=>\s*true/', $wmeanRow) === 1);
	
	// HIGH records enabled
	test('wmean has hi => true to show HIGH records',
		preg_match('/["\']hi["\']\s*=>\s*true/', $wmeanRow) === 1);
	
	// Unit is Wind
	test('wmean unit is Wx::Wind',
		strpos($wmeanRow, 'Wx::Wind') !== false);
	
	// No flagHi='now' (mean wind is not instantaneous)
	test('wmean does NOT have flagHi (not instantaneous like gust)',
		strpos($wmeanRow, 'flagHi') === false);
	
	// Label
	test('wmean label is "Mean wind"',
		preg_match('/["\']label["\']\s*=>\s*["\']Mean wind["\']/', $wmeanRow) === 1);
	
} else {
	test('wmean row definition found', false);
	echo "Could not extract wmean row for detailed tests\n";
}

// Verify gust removed
test('gust row does NOT exist in ClimateCard',
	strpos($climateCard, "'gust' =>") === false);

echo "\n";

echo str_repeat("=", 70) . "\n";
echo "Test Summary\n";
echo str_repeat("=", 70) . "\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "\n";

if ($failed > 0) {
	echo "❌ SOME CLIMATE WIND TESTS FAILED\n";
	exit(1);
} else {
	echo "✅ ALL CLIMATE WIND TESTS PASSED\n";
	exit(0);
}
