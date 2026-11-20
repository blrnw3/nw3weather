<?php
/**
 * HTML fragment endpoint for monthly data tables (TablesDataMonth AJAX updates).
 * Params: vartype, start_year_rep, summary_type, threshold (same as TablesDataMonth.php).
 */
require __DIR__ . '/Page.php';
require __DIR__ . '/Report.php';
require __DIR__ . '/datamonth-body.php';
require __DIR__ . '/ExpensivePage.php';

Page::init([
	'fileNum' => 40.1,
	'title' => 'Monthly Data Tables',
	'description' => 'Monthly data table fragment',
]);
Page::requireNw3Ajax();


$report = new Report(['default' => 'rain', 'badCats' => ['cloud'], 'spellSummary' => true]);

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');

ob_start();
$meta = nw3_expensive_html('datamonth', $report, 'nw3_datamonth_render', nw3_expensive_key_parts('datamonth', $report));
$html = ob_get_clean();

$thLabels = isset($meta['thresholdLabels']) ? $meta['thresholdLabels'] : [];
$thresholds = isset($meta['thresholds']) ? $meta['thresholds'] : [];
$spellDirLabels = isset($meta['spellDirLabels']) ? $meta['spellDirLabels'] : [];
$spellDirAttr = [];
foreach ($spellDirLabels as $k => $v) { $spellDirAttr[] = $k . ':' . $v; }

echo '<div id="dm-fragment"'
	. ' data-type="' . htmlspecialchars($meta['type']) . '"'
	. ' data-start-year-rep="' . (int)$meta['startYearRep'] . '"'
	. ' data-start-years="' . htmlspecialchars(implode(',', $meta['startYearOptions'])) . '"'
	. ' data-summary-type="' . (int)$meta['summaryType'] . '"'
	. ' data-summary-types="' . htmlspecialchars(implode(',', $meta['summaryTypes'])) . '"'
	. ' data-threshold="' . htmlspecialchars((string)$meta['threshold']) . '"'
	. ' data-thresholds="' . htmlspecialchars(implode(',', $thresholds)) . '"'
	. ' data-threshold-labels="' . htmlspecialchars(implode('|', $thLabels)) . '"'
	. ' data-spell-dir="' . htmlspecialchars(isset($meta['spellDir']) ? $meta['spellDir'] : '') . '"'
	. ' data-spell-dir-labels="' . htmlspecialchars(implode(',', $spellDirAttr)) . '"'
	. ' data-title="' . htmlspecialchars($meta['title']) . '"'
	. ' data-span="' . htmlspecialchars(isset($meta['span']) ? $meta['span'] : 'full') . '"'
	. '>';
echo $html;
echo '</div>';
