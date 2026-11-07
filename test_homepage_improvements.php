#!/usr/bin/env php
<?php
/**
 * Test script for the three homepage improvements:
 * 1. Notable rankings: LOW wind maxima are excluded
 * 2. Climate card: Max gust replaced with Mean wind, "Mean" changed to "Avg temp"
 * 3. Homepage YTD chart: New chart with restricted selector
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');
date_default_timezone_set('Europe/London');

// Load required files
require_once __DIR__ . '/site/UtilsAndConsts.php';
require_once __DIR__ . '/site/WxDefinition.php';
require_once __DIR__ . '/site/WxFn.php';
require_once __DIR__ . '/site/RankFlags.php';
require_once __DIR__ . '/site/ClimateCard.php';
require_once __DIR__ . '/site/ChartHelper.php';

// Test results
$tests = [];
$passed = 0;
$failed = 0;

function test($name, $condition, $message = '') {
	global $tests, $passed, $failed;
	if ($condition) {
		$tests[] = "✓ PASS: $name";
		$passed++;
		return true;
	} else {
		$tests[] = "✗ FAIL: $name" . ($message ? " - $message" : '');
		$failed++;
		return false;
	}
}

echo "Testing Homepage Improvements\n";
echo "==============================\n\n";

// Test 1: Notable rankings - Check that LOW wind maxima are excluded
echo "Test 1: Notable rankings exclude LOW wind maxima\n";
echo "------------------------------------------------\n";

// Use reflection to access the private dayLowBoring method
$reflector = new ReflectionClass('RankFlags');
$method = $reflector->getMethod('dayLowBoring');
$method->setAccessible(true);

// Test that wmax, gust, and w10max return true (are boring/excluded for LOW)
test('wmax is excluded from LOW notable rankings', 
	$method->invoke(null, 'wmax') === true,
	'wmax should return true from dayLowBoring()');

test('gust is excluded from LOW notable rankings',
	$method->invoke(null, 'gust') === true,
	'gust should return true from dayLowBoring()');

test('w10max is excluded from LOW notable rankings',
	$method->invoke(null, 'w10max') === true,
	'w10max should return true from dayLowBoring()');

// Test that wmean (mean wind) is NOT excluded for LOW (returns false)
test('wmean is NOT excluded from LOW notable rankings',
	$method->invoke(null, 'wmean') === false,
	'wmean should return false from dayLowBoring() - LOW mean wind is still notable');

// Test that other rain maxima are still excluded
test('hrmax is still excluded from LOW notable rankings',
	$method->invoke(null, 'hrmax') === true,
	'hrmax should remain in the exclusion list');

test('10max is still excluded from LOW notable rankings',
	$method->invoke(null, '10max') === true,
	'10max should remain in the exclusion list');

echo "\n";

// Test 2: Climate card changes
echo "Test 2: Climate card uses wmean instead of gust, 'Avg temp' label\n";
echo "------------------------------------------------------------------\n";

$rows = ClimateCard::rows();

// Check that wmean exists and gust doesn't
test('Climate card includes wmean row',
	isset($rows['wmean']),
	'wmean should be in the climate card rows');

test('Climate card does not include gust row',
	!isset($rows['gust']),
	'gust should no longer be in the climate card rows');

// Check wmean configuration
if (isset($rows['wmean'])) {
	test('wmean label is "Mean wind"',
		$rows['wmean']['label'] === 'Mean wind',
		'Expected "Mean wind", got "' . $rows['wmean']['label'] . '"');
	
	test('wmean has correct unit',
		$rows['wmean']['unit'] === Wx::Wind,
		'wmean should use Wx::Wind unit');
	
	test('wmean has norm reference to wmean',
		$rows['wmean']['norm'] === 'wmean',
		'wmean should reference wmean normals');
	
	test('wmean has flagHi set to "now"',
		$rows['wmean']['flagHi'] === 'now',
		'wmean should flag high values immediately');
}

// Check tmean label changed to "Avg temp"
if (isset($rows['tmean'])) {
	test('tmean label is "Avg temp"',
		$rows['tmean']['label'] === 'Avg temp',
		'Expected "Avg temp", got "' . $rows['tmean']['label'] . '"');
}

// Check cache version was incremented
test('ClimateCard CACHE_VERSION was incremented',
	ClimateCard::CACHE_VERSION >= 4,
	'CACHE_VERSION should be 4 or higher, is ' . ClimateCard::CACHE_VERSION);

echo "\n";

// Test 3: YTD home chart function exists and has correct structure
echo "Test 3: Homepage YTD chart function\n";
echo "-----------------------------------\n";

$chartReflector = new ReflectionClass('Charts');
test('ytdHomeChart method exists',
	$chartReflector->hasMethod('ytdHomeChart'),
	'Charts::ytdHomeChart() method should exist');

// Test chart output (capture output buffer)
if ($chartReflector->hasMethod('ytdHomeChart')) {
	ob_start();
	try {
		// Mock Date class if needed
		if (!class_exists('Date')) {
			class Date {
				public static $dyear = 2026;
				public static $dmonth = 11;
			}
		}
		
		Charts::ytdHomeChart(['height' => 360, 'recordToggle' => true]);
		$output = ob_get_clean();
		
		test('YTD chart generates output',
			strlen($output) > 0,
			'Chart should generate HTML output');
		
		test('YTD chart includes measure selector',
			strpos($output, 'Measure') !== false,
			'Chart should have Measure label');
		
		test('YTD chart includes minimum temperature option',
			strpos($output, 'Minimum temperature') !== false,
			'Chart should include tmin variable');
		
		test('YTD chart includes maximum temperature option',
			strpos($output, 'Maximum temperature') !== false,
			'Chart should include tmax variable');
		
		test('YTD chart includes mean temperature option',
			strpos($output, 'Mean temperature') !== false,
			'Chart should include tmean variable');
		
		test('YTD chart includes rainfall option',
			strpos($output, 'Rainfall') !== false,
			'Chart should include rain variable');
		
		test('YTD chart includes sunshine option',
			strpos($output, 'Sunshine') !== false,
			'Chart should include sunhr variable');
		
		test('YTD chart has normals button',
			strpos($output, 'Normals') !== false,
			'Chart should have normals toggle');
		
		test('YTD chart normals button is disabled',
			strpos($output, 'disabled="disabled"') !== false && 
			strpos($output, 'Normals') !== false,
			'Normals button should be disabled (always on)');
		
		test('YTD chart has station record range toggle',
			strpos($output, 'Station record range') !== false,
			'Chart should have station record range toggle');
		
		// Check for year selector (should not be present in November)
		test('YTD chart year selector absent in non-January month',
			strpos($output, '<div class="wxsel-scale wxsel-years"') === false,
			'Year selector should not appear in November');
		
		test('YTD chart includes histogram config',
			strpos($output, 'histSelectGrouped') !== false,
			'Chart should initialize with histSelectGrouped');
			
	} catch (Exception $e) {
		ob_end_clean();
		test('YTD chart generates without errors', false, $e->getMessage());
	}
}

echo "\n";

// Summary
echo "Summary\n";
echo "=======\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "\nDetailed Results:\n";
foreach ($tests as $result) {
	echo "$result\n";
}

exit($failed > 0 ? 1 : 0);
