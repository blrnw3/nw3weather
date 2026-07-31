<?php
/**
 * HTML fragment endpoint for ranked spell data (RankSpells AJAX updates).
 */
require __DIR__ . '/Page.php';
require __DIR__ . '/Report.php';
require __DIR__ . '/rankspells-body.php';

Page::init([
	'fileNum' => 43,
	'title' => 'Ranked Spells',
	'description' => 'Ranked spells fragment',
]);

$report = new Report([
	'default' => 'rain',
	'badCats' => ['wdir', 'cloud', 'comms', 'extra', 'issues', 'away', 'spare'],
]);

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');

ob_start();
$meta = nw3_rankspells_render($report);
$html = ob_get_clean();

$threshList = array_map(function ($t) { return (string)$t; }, $meta['thresholds']);

echo '<div id="rs-fragment"'
	. ' data-type="' . htmlspecialchars($meta['type']) . '"'
	. ' data-month="' . (int)$meta['month'] . '"'
	. ' data-start-year-rep="' . (int)$meta['startYearRep'] . '"'
	. ' data-start-years="' . htmlspecialchars(implode(',', $meta['startYearOptions'])) . '"'
	. ' data-rank-limit="' . (int)$meta['rankLimit'] . '"'
	. ' data-spell-dir="' . htmlspecialchars($meta['spellDir']) . '"'
	. ' data-threshold="' . htmlspecialchars((string)$meta['threshold']) . '"'
	. ' data-thresholds="' . htmlspecialchars(implode(',', $threshList)) . '"'
	. ' data-title="' . htmlspecialchars($meta['title']) . '"'
	. '>';
echo $html;
echo '</div>';
