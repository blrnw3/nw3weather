<?php
/**
 * HTML fragment endpoint for ranked monthly data (RankMonth AJAX updates).
 */
require __DIR__ . '/Page.php';
require __DIR__ . '/Report.php';
require __DIR__ . '/rankmonth-body.php';

Page::init([
	'fileNum' => 42,
	'title' => 'Ranked Monthly Data',
	'description' => 'Ranked monthly data fragment',
]);
Page::requireNw3Ajax();


$report = new Report(['default' => 'rain', 'badCats' => ['cloud']]);

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');

ob_start();
$meta = nw3_rankmonth_render($report);
$html = ob_get_clean();

$thLabels = isset($meta['thresholdLabels']) ? $meta['thresholdLabels'] : [];
$thresholds = isset($meta['thresholds']) ? $meta['thresholds'] : [];

echo '<div id="rm-fragment"'
	. ' data-type="' . htmlspecialchars($meta['type']) . '"'
	. ' data-month="' . (int)$meta['month'] . '"'
	. ' data-start-year-rep="' . (int)$meta['startYearRep'] . '"'
	. ' data-start-years="' . htmlspecialchars(implode(',', $meta['startYearOptions'])) . '"'
	. ' data-summary-type="' . (int)$meta['summaryType'] . '"'
	. ' data-summary-types="' . htmlspecialchars(implode(',', $meta['summaryTypes'])) . '"'
	. ' data-rank-limit="' . (int)$meta['rankLimit'] . '"'
	. ' data-threshold="' . htmlspecialchars((string)$meta['threshold']) . '"'
	. ' data-thresholds="' . htmlspecialchars(implode(',', $thresholds)) . '"'
	. ' data-threshold-labels="' . htmlspecialchars(implode('|', $thLabels)) . '"'
	. ' data-title="' . htmlspecialchars($meta['title']) . '"'
	. '>';
echo $html;
echo '</div>';
