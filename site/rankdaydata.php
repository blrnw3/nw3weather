<?php
/**
 * HTML fragment endpoint for ranked daily data (RankDay AJAX updates).
 */
require __DIR__ . '/Page.php';
require __DIR__ . '/Report.php';
require __DIR__ . '/rankday-body.php';

// Daily fog rankings are binary (fog/no fog) and not meaningful; keep fog on monthly+.
if (isset($_GET['vartype']) && $_GET['vartype'] === 'fog') { unset($_GET['vartype']); }

Page::init([
	'fileNum' => 41,
	'title' => 'Ranked Daily Data',
	'description' => 'Ranked daily data fragment',
]);
Page::requireNw3Ajax();


$report = new Report(['default' => 'rain', 'badCats' => ['cloud', 'fog']]);

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');

ob_start();
$meta = nw3_rankday_render($report);
$html = ob_get_clean();

echo '<div id="rd-fragment"'
	. ' data-type="' . htmlspecialchars($meta['type']) . '"'
	. ' data-month="' . (int)$meta['month'] . '"'
	. ' data-day="' . (int)$meta['day'] . '"'
	. ' data-win="' . (int)$meta['win'] . '"'
	. ' data-start-year-rep="' . (int)$meta['startYearRep'] . '"'
	. ' data-start-years="' . htmlspecialchars(implode(',', $meta['startYearOptions'])) . '"'
	. ' data-rank-limit="' . (int)$meta['rankLimit'] . '"'
	. ' data-title="' . htmlspecialchars($meta['title']) . '"'
	. '>';
echo $html;
echo '</div>';
