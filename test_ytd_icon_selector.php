<?php
/**
 * Test YTD chart uses icon-based grouped selector with controls above chart.
 */

error_reporting(E_ALL);

$passed = 0; $failed = 0;
function test($desc, $cond) {
	global $passed, $failed;
	if ($cond) { echo "✓ PASS: $desc\n"; $passed++; }
	else { echo "✗ FAIL: $desc\n"; $failed++; }
}

echo "YTD Chart Icon-Based Selector Tests\n";
echo str_repeat("=", 70) . "\n\n";

$chartHelper = file_get_contents(__DIR__ . '/site/ChartHelper.php');
$css = file_get_contents(__DIR__ . '/site/mainstyle_v5.css');

echo "Structure Tests (ChartHelper.php)\n";
echo str_repeat("-", 70) . "\n";

// Icon-based groups defined
test('ytdHomeChart defines Temp group with icon',
	preg_match('/thermom8_small\.png/', $chartHelper) === 1);
test('ytdHomeChart defines Rain group with icon',
	preg_match('/icon-rain\.svg/', $chartHelper) === 1);
test('ytdHomeChart defines Sun group with icon',
	preg_match('/icon-sun\.svg/', $chartHelper) === 1);

// Groups structure
test('ytdHomeChart creates groups array',
	preg_match('/\$groups\s*=\s*array\s*\(/m', $chartHelper) === 1);
test('Groups include temp with tmin/tmean/tmax',
	preg_match('/["\']temp["\'].*tmin.*tmean.*tmax/s', $chartHelper) === 1);
test('Groups include rain with rain=>Total',
	preg_match('/["\']rain["\'].*["\']rain["\'].*["\']Total["\']/s', $chartHelper) === 1);
test('Groups include sun with sunhr=>Total',
	preg_match('/["\']sun["\'].*["\']sunhr["\'].*["\']Total["\']/s', $chartHelper) === 1);

// emitGroupButtons called
test('ytdHomeChart calls emitGroupButtons',
	preg_match('/self::emitGroupButtons\(\$groups,\s*\$defaultGroup\)/', $chartHelper) === 1);

// wxsel-panel-above class for above-chart layout
test('ytdHomeChart uses wxsel-panel-above class',
	strpos($chartHelper, 'wxsel-panel-above') !== false);

// Controls above chart (panel before chart container)
$ytdFunc = '';
if (preg_match('/public static function ytdHomeChart.*?^\s*\}/ms', $chartHelper, $m)) {
	$ytdFunc = $m[0];
	test('ytdHomeChart function extracted', true);
	
	// Check order: panel comes before chart container
	$panelPos = strpos($ytdFunc, 'wxsel-panel');
	$chartPos = strpos($ytdFunc, 'id="\'.\$id.\'"');
	test('Panel rendered before chart container',
		$panelPos !== false && $chartPos !== false && $panelPos < $chartPos);
	
	// No wxsel-chart wrapper around panel (controls at top level)
	$panelLine = '';
	foreach (explode("\n", $ytdFunc) as $line) {
		if (strpos($line, 'wxsel-panel') !== false) {
			$panelLine = $line;
			break;
		}
	}
	test('Panel not wrapped in wxsel-chart div (direct sibling)',
		$panelLine && strpos($panelLine, '<div id') !== false);
	
} else {
	test('ytdHomeChart function extracted', false);
}

// No flat button structure
test('ytdHomeChart does NOT manually emit flat measure buttons',
	preg_match('/foreach.*\$vars.*data-type/', $chartHelper) === 0 ||
	strpos($ytdFunc, 'foreach') === false);

// JS config passes groups
test('ytdHomeChart JS config includes groups',
	preg_match('/["\']groups["\']\s*=>\s*\$groups/', $chartHelper) === 1);

echo "\n";

echo "CSS Tests (mainstyle_v5.css)\n";
echo str_repeat("-", 70) . "\n";

test('CSS defines .ytd-home-wrap .wxsel-panel-above',
	strpos($css, '.ytd-home-wrap .wxsel-panel-above') !== false);
test('CSS sets wxsel-panel-above to full width',
	preg_match('/\.ytd-home-wrap\s+\.wxsel-panel-above\s*\{[^}]*width:\s*100%/', $css) === 1);
test('CSS sets ytd-home-wrap wxchart to full width',
	preg_match('/\.ytd-home-wrap\s+\.wxchart\s*\{[^}]*width:\s*100%/', $css) === 1);
test('CSS gives panel order: 0 (above)',
	preg_match('/\.ytd-home-wrap\s+\.wxsel-panel-above\s*\{[^}]*order:\s*0/', $css) === 1);
test('CSS gives chart order: 1 (below)',
	preg_match('/\.ytd-home-wrap\s+\.wxchart\s*\{[^}]*order:\s*1/', $css) === 1);

echo "\n";

echo str_repeat("=", 70) . "\n";
echo "Test Summary\n";
echo str_repeat("=", 70) . "\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "\n";

if ($failed > 0) {
	echo "❌ SOME YTD ICON SELECTOR TESTS FAILED\n";
	exit(1);
} else {
	echo "✅ ALL YTD ICON SELECTOR TESTS PASSED\n";
	exit(0);
}
