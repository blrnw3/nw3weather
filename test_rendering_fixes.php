<?php
/**
 * Test rendering fixes for YTD chart blank issue.
 */

error_reporting(E_ALL);
$passed = 0; $failed = 0;
function test($desc, $cond) {
	global $passed, $failed;
	if ($cond) { echo "✓ PASS: $desc\n"; $passed++; }
	else { echo "✗ FAIL: $desc\n"; $failed++; }
}

echo "YTD Chart Rendering Fixes\n";
echo str_repeat("=", 70) . "\n\n";

// Fix 1: recordToggle removed from opts
echo "Fix 1: Remove recordToggle from homepage opts\n";
echo str_repeat("-", 70) . "\n";
$chartHelper = file_get_contents(__DIR__ . '/site/ChartHelper.php');
test('ChartHelper creates chartOpts with array_diff_key',
	preg_match('/\$chartOpts\s*=\s*array_diff_key\(\$opts.*recordToggle/', $chartHelper) === 1);
test('ChartHelper passes chartOpts to config (not $opts directly)',
	preg_match('/\'opts\'\s*=>\s*\$chartOpts/', $chartHelper) === 1);
test('ChartHelper comment mentions preventing duplicate controls',
	preg_match('/duplicate controls|recordToggle.*prevent/i', $chartHelper) === 1);

echo "\n";

// Fix 2: env button sync before early return
echo "Fix 2: Sync env button state independently\n";
echo str_repeat("-", 70) . "\n";
$wxcharts = file_get_contents(__DIR__ . '/site/wxcharts.js');

// Check structure: env button sync comes before early return in the function
$lines = explode("\n", $wxcharts);
$inSyncCume = false;
$foundEnv = false;
$foundReturn = false;
$envLineNum = -1;
$returnLineNum = -1;
foreach ($lines as $num => $line) {
	if (strpos($line, 'function syncCumeUi') !== false) {
		$inSyncCume = true;
	}
	if ($inSyncCume && !$foundEnv && strpos($line, 'envBtn') !== false) {
		$foundEnv = true;
		$envLineNum = $num;
	}
	if ($inSyncCume && !$foundReturn && strpos($line, 'if (!cumeBtn') !== false) {
		$foundReturn = true;
		$returnLineNum = $num;
	}
	if ($inSyncCume && preg_match('/^\s*function /', $line) && strpos($line, 'syncCumeUi') === false) {
		// Next function, stop
		break;
	}
}

test('syncCumeUi has envBtn handling',
	$foundEnv);
test('syncCumeUi envBtn code comes before early return check',
	$foundEnv && $foundReturn && $envLineNum < $returnLineNum);
test('syncCumeUi has comment about env independence',
	preg_match('/Sync env.*independent|env button.*first/i', $wxcharts) === 1);

echo "\n";

// Fix 3: Heading shows year with defaultYear
echo "Fix 3: Update heading to show year with defaultYear\n";
echo str_repeat("-", 70) . "\n";
test('updateHeading checks defaultYear in addition to showYears',
	preg_match('/showYears\s*\|\|\s*cfg\.defaultYear.*state\.year/s', $wxcharts) === 1);
test('updateHeading comment mentions YTD charts',
	preg_match('/defaultYear.*YTD|YTD charts/i', $wxcharts) === 1);

echo "\n";

// Fix 4: headingPrefix set to empty string
echo "Fix 4: Homepage sets headingPrefix to empty string\n";
echo str_repeat("-", 70) . "\n";
test('ChartHelper sets headingPrefix to empty string',
	preg_match('/[\'"]headingPrefix[\'"]\s*=>\s*[\'"]{2}/', $chartHelper) === 1);
test('ChartHelper comment mentions clean heading',
	preg_match('/empty prefix.*clean|clean.*heading/i', $chartHelper) === 1);

echo "\n";

echo str_repeat("=", 70) . "\n";
echo "Test Summary\n";
echo str_repeat("=", 70) . "\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "\n";

if ($failed > 0) {
	echo "❌ SOME RENDERING FIX TESTS FAILED\n";
	exit(1);
} else {
	echo "✅ ALL RENDERING FIX TESTS PASSED\n";
	exit(0);
}
