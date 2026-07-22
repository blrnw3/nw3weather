<?php
/**
 * HTML fragment endpoint for the daily data matrix (wxdataday AJAX updates).
 * Params: vartype, year (same as wxdataday.php).
 */
require __DIR__ . '/Page.php';
require __DIR__ . '/Report.php';
require __DIR__ . '/dataday-body.php';

Page::init([
	'fileNum' => 40,
	'title' => 'Daily Data Tables',
	'description' => 'Daily data table fragment',
]);

$report = new Report(['default' => 'rain', 'badCats' => ['cloud']]);

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');

ob_start();
$meta = nw3_dataday_render($report);
$html = ob_get_clean();

$warn = !empty($meta['yearDefaulted'])
	? ('No data for ' . $meta['description'] . ' in the selected year; '
		. 'defaulted to ' . (int)$meta['year'] . ' (earliest available).')
	: '';

echo '<div id="dd-fragment"'
	. ' data-type="' . htmlspecialchars($meta['type']) . '"'
	. ' data-year="' . (int)$meta['year'] . '"'
	. ' data-start-year="' . (int)$meta['startYear'] . '"'
	. ' data-title="' . htmlspecialchars($meta['title']) . '"'
	. ' data-year-defaulted="' . (!empty($meta['yearDefaulted']) ? '1' : '0') . '"'
	. ' data-year-warn="' . htmlspecialchars($warn) . '"'
	. '>';
echo $html;
echo '</div>';
