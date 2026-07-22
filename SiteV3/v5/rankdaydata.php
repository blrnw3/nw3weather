<?php
/**
 * HTML fragment endpoint for ranked daily data (RankDay AJAX updates).
 */
require __DIR__ . '/Page.php';
require __DIR__ . '/Report.php';
require __DIR__ . '/rankday-body.php';

Page::init([
	'fileNum' => 41,
	'title' => 'Ranked Daily Data',
	'description' => 'Ranked daily data fragment',
]);

$report = new Report(['default' => 'rain', 'badCats' => ['cloud']]);

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');

ob_start();
$meta = nw3_rankday_render($report);
$html = ob_get_clean();

echo '<div id="rd-fragment"'
	. ' data-type="' . htmlspecialchars($meta['type']) . '"'
	. ' data-month="' . (int)$meta['month'] . '"'
	. ' data-start-year-rep="' . (int)$meta['startYearRep'] . '"'
	. ' data-rank-limit="' . (int)$meta['rankLimit'] . '"'
	. ' data-title="' . htmlspecialchars($meta['title']) . '"'
	. '>';
echo $html;
echo '</div>';
