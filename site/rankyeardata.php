<?php
/**
 * HTML fragment endpoint for ranked annual data (RankYear AJAX updates).
 */
require __DIR__ . '/Page.php';
require __DIR__ . '/Report.php';
require __DIR__ . '/rankyear-body.php';

Page::init([
	'fileNum' => 42.1,
	'title' => 'Ranked Annual Data',
	'description' => 'Ranked annual data fragment',
]);

$report = new Report(['default' => 'rain', 'badCats' => ['cloud']]);

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');

ob_start();
$meta = nw3_rankyear_render($report);
$html = ob_get_clean();

echo '<div id="ry-fragment"'
	. ' data-type="' . htmlspecialchars($meta['type']) . '"'
	. ' data-start-year-rep="' . (int)$meta['startYearRep'] . '"'
	. ' data-start-years="' . htmlspecialchars(implode(',', $meta['startYearOptions'])) . '"'
	. ' data-summary-type="' . (int)$meta['summaryType'] . '"'
	. ' data-summary-types="' . htmlspecialchars(implode(',', $meta['summaryTypes'])) . '"'
	. ' data-rank-limit="' . (int)$meta['rankLimit'] . '"'
	. ' data-title="' . htmlspecialchars($meta['title']) . '"'
	. '>';
echo $html;
echo '</div>';
