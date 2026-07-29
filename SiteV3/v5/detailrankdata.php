<?php
/**
 * HTML fragment for Ranked Historical Data on variable detail pages.
 * Params: group (temp|baro|wind|rain|hum|dew|sun), start_year_rep
 */
require __DIR__ . '/Page.php';
require __DIR__ . '/ViewDetailedData.php';

$group = isset($_GET['group']) ? preg_replace('/[^a-z]/', '', $_GET['group']) : '';
$allowed = ['temp', 'baro', 'wind', 'rain', 'hum', 'dew', 'sun'];
if (!in_array($group, $allowed, true)) {
	header('HTTP/1.1 400 Bad Request');
	echo '<p>Unknown group.</p>';
	exit;
}

Page::init([
	'fileNum' => 14.2,
	'title' => 'Detail rankings fragment',
	'description' => 'Rankings fragment',
]);

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');

$start = isset($_GET['start_year_rep']) ? (int)$_GET['start_year_rep'] : 2009;
$vd = new ViewDetailedData($group, ['startYear' => $start, 'rankOnly' => true]);
$vd->renderRankTablesBody();
