<?php
/**
 * Verify that Year-to-date section is the final non-footer content on homepage.
 * Expected order: .home-links → Monthly Weather Report → Year-to-date → footer
 */

// Parse the index.php file directly to verify section order
$indexFile = __DIR__ . '/site/index.php';
if (!file_exists($indexFile)) {
	die("ERROR: site/index.php not found\n");
}

$content = file_get_contents($indexFile);

// Find section positions in the source file
$sections = [];

// Find .home-links
$homeLinksPos = strpos($content, 'class="home-links"');
if ($homeLinksPos !== false) {
	$sections['home-links'] = $homeLinksPos;
}

// Find Monthly Weather Report heading
$monthlyPos1 = strpos($content, 'Latest Monthly Weather Report');
$monthlyPos2 = strpos($content, 'Weather in brief');
if ($monthlyPos1 !== false) {
	$sections['monthly-report'] = $monthlyPos1;
} elseif ($monthlyPos2 !== false) {
	$sections['monthly-report'] = $monthlyPos2;
}

// Find YTD heading
$ytdPos = strpos($content, '<h2>Year-to-date</h2>');
if ($ytdPos !== false) {
	$sections['ytd'] = $ytdPos;
}

// Find Page::End() call (marks the footer)
$pageEndPos = strpos($content, 'Page::End()');
if ($pageEndPos !== false) {
	$sections['page-end'] = $pageEndPos;
}

// Verify order
$errors = [];

if (!isset($sections['home-links'])) {
	$errors[] = "home-links section not found";
}
if (!isset($sections['monthly-report'])) {
	$errors[] = "Monthly report section not found";
}
if (!isset($sections['ytd'])) {
	$errors[] = "YTD section not found";
} else {
	// YTD must be after home-links
	if (isset($sections['home-links']) && $sections['ytd'] <= $sections['home-links']) {
		$errors[] = "YTD section appears before home-links (YTD at {$sections['ytd']}, home-links at {$sections['home-links']})";
	}
	// YTD must be after monthly report
	if (isset($sections['monthly-report']) && $sections['ytd'] <= $sections['monthly-report']) {
		$errors[] = "YTD section appears before monthly report (YTD at {$sections['ytd']}, monthly report at {$sections['monthly-report']})";
	}
	// YTD must be before Page::End()
	if (isset($sections['page-end']) && $sections['ytd'] >= $sections['page-end']) {
		$errors[] = "YTD section appears after Page::End() (YTD at {$sections['ytd']}, Page::End at {$sections['page-end']})";
	}
}

if (empty($errors)) {
	echo "✓ PASS: YTD section is correctly positioned as the final non-footer content\n";
	echo "  Order: home-links → monthly-report → ytd → Page::End()\n";
	echo "  Positions: " . json_encode($sections) . "\n";
	exit(0);
} else {
	echo "✗ FAIL: YTD section order validation failed\n";
	foreach ($errors as $error) {
		echo "  - $error\n";
	}
	echo "  Found positions: " . json_encode($sections) . "\n";
	exit(1);
}
