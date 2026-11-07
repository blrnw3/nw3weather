<?php
$wxcharts = file_get_contents(__DIR__ . '/site/wxcharts.js');

// Extract syncCumeUi function
if (preg_match('/function syncCumeUi\(\)\s*\{([^\}]*(?:\{[^\}]*\}[^\}]*)*)\}/s', $wxcharts, $m)) {
	$syncFunc = $m[0];
	echo "Found syncCumeUi function:\n";
	echo str_repeat("=", 70) . "\n";
	echo $syncFunc . "\n";
	echo str_repeat("=", 70) . "\n";
	
	// Check order
	$lines = explode("\n", $syncFunc);
	$envLine = -1;
	$returnLine = -1;
	foreach ($lines as $i => $line) {
		if (strpos($line, 'envBtn') !== false && $envLine === -1) {
			$envLine = $i;
			echo "Line $i: envBtn handling found\n";
		}
		if (strpos($line, 'if (!cumeBtn') !== false && $returnLine === -1) {
			$returnLine = $i;
			echo "Line $i: early return check found\n";
		}
	}
	
	echo "\n";
	if ($envLine !== -1 && $returnLine !== -1 && $envLine < $returnLine) {
		echo "✓ envBtn code ($envLine) comes BEFORE early return ($returnLine)\n";
	} elseif ($envLine !== -1 && $returnLine !== -1) {
		echo "✗ envBtn code ($envLine) comes AFTER early return ($returnLine)\n";
	} else {
		echo "✗ Could not find both markers\n";
	}
} else {
	echo "Could not extract syncCumeUi function\n";
}
