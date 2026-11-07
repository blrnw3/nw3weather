<?php
/**
 * Test CSS cache buster updated after stylesheet changes.
 */

error_reporting(E_ALL);
$passed = 0; $failed = 0;
function test($desc, $cond) {
	global $passed, $failed;
	if ($cond) { echo "✓ PASS: $desc\n"; $passed++; }
	else { echo "✗ FAIL: $desc\n"; $failed++; }
}

echo "CSS Cache Buster Tests\n";
echo str_repeat("=", 70) . "\n\n";

$page = file_get_contents(__DIR__ . '/site/Page.php');

echo "Stylesheet URL Tests\n";
echo str_repeat("-", 70) . "\n";

test('Page.php contains mainstyle_v5.css reference',
	strpos($page, 'mainstyle_v5.css') !== false);

test('CSS uses 20261102w cache buster (next after 20261102v)',
	preg_match('/mainstyle_v5\.css\?20261102w/', $page) === 1);

test('CSS does NOT use old 20261102v buster',
	strpos($page, 'mainstyle_v5.css?20261102v') === false);

echo "\n";

echo str_repeat("=", 70) . "\n";
echo "Test Summary\n";
echo str_repeat("=", 70) . "\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "\n";

if ($failed > 0) {
	echo "❌ SOME CSS BUSTER TESTS FAILED\n";
	exit(1);
} else {
	echo "✅ ALL CSS BUSTER TESTS PASSED\n";
	exit(0);
}
