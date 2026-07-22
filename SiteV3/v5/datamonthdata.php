<?php
/**
 * HTML fragment endpoint for monthly data tables (TablesDataMonth AJAX updates).
 * Params: vartype, start_year_rep, summary_type (same as TablesDataMonth.php).
 */
require __DIR__ . '/Page.php';
require __DIR__ . '/Report.php';
require __DIR__ . '/datamonth-body.php';

Page::init([
	'fileNum' => 40.1,
	'title' => 'Monthly Data Tables',
	'description' => 'Monthly data table fragment',
]);

$report = new Report(['default' => 'rain', 'badCats' => ['cloud']]);

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');

ob_start();
$meta = nw3_datamonth_render($report);
$html = ob_get_clean();

echo '<div id="dm-fragment"'
	. ' data-type="' . htmlspecialchars($meta['type']) . '"'
	. ' data-start-year-rep="' . (int)$meta['startYearRep'] . '"'
	. ' data-summary-type="' . (int)$meta['summaryType'] . '"'
	. ' data-summary-types="' . htmlspecialchars(implode(',', $meta['summaryTypes'])) . '"'
	. ' data-title="' . htmlspecialchars($meta['title']) . '"'
	. '>';
echo $html;
echo '</div>';
