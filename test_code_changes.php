#!/usr/bin/env php
<?php
/**
 * Direct code validation tests for PR #52 homepage improvements.
 * Tests code changes without requiring full app context.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$passed = 0;
$failed = 0;

function test($name, $condition, $message = '') {
	global $passed, $failed;
	if ($condition) {
		echo "✓ PASS: $name\n";
		$passed++;
		return true;
	} else {
		echo "✗ FAIL: $name" . ($message ? " - $message" : '') . "\n";
		$failed++;
		return false;
	}
}

echo "Direct Code Validation Tests (PR #52)\n";
echo str_repeat("=", 70) . "\n\n";

// Test 1: RankFlags.php changes
echo "Test 1: RankFlags.php - 90% Threshold and Wind Exclusions\n";
echo str_repeat("-", 70) . "\n";

$rankFlags = file_get_contents(__DIR__ . '/site/RankFlags.php');

test('COMPLETE_FRAC is 0.90',
	preg_match('/const\s+COMPLETE_FRAC\s*=\s*0\.90/', $rankFlags) === 1,
	'Should be 0.90 not 0.95');

test('User-facing text says "90% complete"',
	strpos($rankFlags, '90% complete') !== false);

test('No mention of "95% complete" in user text',
	strpos($rankFlags, '95% complete') === false);

test('dayLowBoring includes wmax',
	preg_match('/[\'"]wmax[\'"]/', $rankFlags) === 1);

test('dayLowBoring includes gust',
	preg_match('/dayLowBoring.*gust/s', $rankFlags) === 1);

test('dayLowBoring includes w10max',
	preg_match('/dayLowBoring.*w10max/s', $rankFlags) === 1);

// Check that dayLowBoring is passed to partialDirs for months/years
test('dayLowBoring passed to month partial dirs',
	preg_match('/partialDirs\(\$isSum.*\$monFrac.*dayLowBoring/', $rankFlags) === 1);

test('dayLowBoring passed to year partial dirs',
	preg_match('/partialDirs\(\$isSum.*\$yrFrac.*dayLowBoring/', $rankFlags) === 1);

test('dayLowBoring applied to completed previous month',
	preg_match('/dayLowBoring.*\$cur.*yest.*month/s', $rankFlags) === 1);

test('dayLowBoring applied to completed previous year',
	preg_match('/dayLowBoring.*\$cur.*yest.*year/s', $rankFlags) === 1);

echo "\n";

// Test 2: ClimateCard.php changes
echo "Test 2: ClimateCard.php - Mean Wind and Avg Temp\n";
echo str_repeat("-", 70) . "\n";

$climateCard = file_get_contents(__dir__ . '/site/ClimateCard.php');

test('CACHE_VERSION is 4',
	preg_match('/const\s+CACHE_VERSION\s*=\s*4/', $climateCard) === 1);

test('wmean row exists with label "Mean wind"',
	preg_match('/[\'"]wmean[\'"].*[\'"]label[\'"].*=>[^;]*[\'"]Mean wind[\'"]/', $climateCard) === 1);

test('gust row does NOT exist',
	preg_match('/[\'"]gust[\'"].*=>.*array\(/', $climateCard) === 0,
	'gust should have been replaced by wmean');

test('tmean label is "Avg temp"',
	preg_match('/[\'"]tmean[\'"].*[\'"]label[\'"].*=>[^;]*[\'"]Avg temp[\'"]/', $climateCard) === 1);

test('wmean does NOT have flagHi',
	!preg_match('/[\'"]wmean[\'"].*[\'"]flagHi[\'"].*=>.*[\'"]now[\'"]/', $climateCard),
	'Mean wind should not use instant flagHi=now');

test('todayValues returns wmean from mean wind',
	preg_match('/[\'"]wmean[\'"].*\$get.*[\'"]mean[\'"].*[\'"]wind[\'"]/', $climateCard) === 1);

echo "\n";

// Test 3: ChartHelper.php - YTD chart
echo "Test 3: ChartHelper.php - YTD Chart Configuration\n";
echo str_repeat("-", 70) . "\n";

$chartHelper = file_get_contents(__DIR__ . '/site/ChartHelper.php');

test('ytdHomeChart function exists',
	preg_match('/function\s+ytdHomeChart/', $chartHelper) === 1);

test('ytdHomeChart restricts to 5 variables',
	preg_match('/tmin.*tmax.*tmean.*rain.*sunhr/s', $chartHelper) === 1);

test('ytdHomeChart checks if January for year selector',
	preg_match('/\$inJanuary.*\$month.*===.*1/', $chartHelper) === 1);

// Extract just the ytdHomeChart function to test
if (preg_match('/public static function ytdHomeChart.*?^\t}/ms', $chartHelper, $ytdFunc)) {
	test('ytdHomeChart does NOT render normals toggle',
		!preg_match('/data-lta/', $ytdFunc[0]),
		'Should not have a normals toggle button');
} else {
	test('ytdHomeChart does NOT render normals toggle',
		!preg_match('/function ytdHomeChart[^}]*data-lta/s', $chartHelper),
		'Should not have a normals toggle button');
}

test('ytdHomeChart renders record range toggle',
	preg_match('/ytdHomeChart.*data-env.*Station record range/s', $chartHelper) === 1);

test('ytdHomeChart config has defaultLta true',
	preg_match('/ytdHomeChart.*defaultLta.*=>.*true/s', $chartHelper) === 1);

test('ytdHomeChart config has showNormals true',
	preg_match('/ytdHomeChart.*showNormals.*=>.*true/s', $chartHelper) === 1);

test('ytdHomeChart config has showYears conditional on January',
	preg_match('/showYears.*=>.*\$inJanuary/', $chartHelper) === 1);

echo "\n";

// Test 4: index.php - Page structure
echo "Test 4: index.php - Page Structure and Order\n";
echo str_repeat("-", 70) . "\n";

$index = file_get_contents(__DIR__ . '/site/index.php');

test('index.php contains 31-day trends',
	strpos($index, '31-day trends') !== false);

test('index.php contains climate wrap',
	strpos($index, 'id="clim-wrap"') !== false);

test('index.php contains YTD section',
	strpos($index, 'Year-to-date') !== false);

test('index.php calls ytdHomeChart',
	strpos($index, 'ytdHomeChart') !== false);

// Check order
$pos31d = strpos($index, '31-day trends');
$posClim = strpos($index, 'id="clim-wrap"');
$posYtd = strpos($index, 'Year-to-date');

if ($pos31d !== false && $posClim !== false && $posYtd !== false) {
	test('YTD comes AFTER climate/rankings',
		$posYtd > $posClim,
		"YTD at $posYtd should be after clim-wrap at $posClim");
	
	test('Order is: 31-day → climate → YTD',
		($pos31d < $posClim) && ($posClim < $posYtd),
		"Positions: 31d=$pos31d, clim=$posClim, ytd=$posYtd");
}

echo "\n";

// Test 5: JavaScript integration points
echo "Test 5: JavaScript Integration Points\n";
echo str_repeat("-", 70) . "\n";

$wxcharts = file_get_contents(__DIR__ . '/site/wxcharts.js');

test('histSelectGrouped function exists',
	preg_match('/function\s+histSelectGrouped\s*\(/', $wxcharts) === 1);

test('JS uses cfg.showYears',
	strpos($wxcharts, 'cfg.showYears') !== false);

test('JS uses cfg.showNormals',
	strpos($wxcharts, 'cfg.showNormals') !== false);

test('JS uses cfg.defaultLta',
	strpos($wxcharts, 'cfg.defaultLta') !== false ||
	strpos($wxcharts, 'defaultLta') !== false);

test('JS checks summable for cumulative',
	strpos($wxcharts, 'summable') !== false);

test('JS adds lta=1 parameter',
	preg_match('/lta.*=.*1/', $wxcharts) === 1);

test('JS adds cume=1 parameter',
	preg_match('/cume.*=.*1/', $wxcharts) === 1);

test('JS adds env=1 parameter for record range',
	preg_match('/env.*=.*1/', $wxcharts) === 1);

echo "\n";

// Test 6: Syntax validation (already done via php -l)
echo "Test 6: PHP Syntax Validation\n";
echo str_repeat("-", 70) . "\n";

$files = [
	'site/RankFlags.php',
	'site/ClimateCard.php',
	'site/ChartHelper.php',
	'site/index.php'
];

foreach ($files as $file) {
	exec("php -l " . escapeshellarg(__DIR__ . '/' . $file) . " 2>&1", $output, $ret);
	test(basename($file) . ' has valid PHP syntax',
		$ret === 0 && strpos(implode("\n", $output), 'No syntax errors') !== false);
	$output = [];
}

echo "\n";

// Summary
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
	echo "\nNote: These tests validate code structure and changes.\n";
	echo "Full functional testing requires production environment with data.\n";
	exit(0);
}
