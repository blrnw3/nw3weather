<?php
/**
 * Pre-warm serialised DataSummarizer caches for wx10–wx16 detail pages.
 * Invoked by cron_main after serialiseCSV('dat') so the first visitor of the
 * day (or after a data refresh) is not stuck rebuilding ranks/spells/windows.
 *
 * Usage:
 *   php warm_detail_summaries.php           # station start year (2009)
 *   php warm_detail_summaries.php 2009      # one start year only
 *   php warm_detail_summaries.php datm      # only the datm-sourced vars
 *   php warm_detail_summaries.php hist      # pre-2009 windows (daily / hist.csv)
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
date_default_timezone_set('Europe/London');

require __DIR__ . '/UtilsAndConsts.php';
require __DIR__ . '/WxDefinition.php';
require __DIR__ . '/WxFn.php';
require __DIR__ . '/Spells.php';

// Climate normals drive both the anomaly figures and the sunhrp values
// (sun hours / max possible). Without this the warmed caches would be written
// with null percentages and no seasonal/annual anomalies.
LTA::init();

$arg = isset($argv[1]) ? (string)$argv[1] : '';
$onlyDatm = ($arg === 'datm');
$onlyHist = ($arg === 'hist' || $arg === 'historical');
$start = ctype_digit($arg) ? (int)$arg : null;
$vars = $onlyDatm ? DataSummarizer::datmDetailVars() : null;

$t0 = microtime(true);
if ($onlyHist) {
	DataSummarizer::warmHistoricalDetailSummaries($vars);
} else {
	DataSummarizer::warmDetailSummaries($start, $vars);
}
$ms = round((microtime(true) - $t0) * 1000);
if ($onlyHist) {
	$jobs = DataSummarizer::historicalWarmJobs($vars);
	$label = 'hist=' . count($jobs) . ' jobs';
} else {
	$label = ($start === null)
		? 'all=' . implode(',', DataSummarizer::$detailStartYearOptions)
		: "start=$start";
}
if ($onlyDatm) { $label .= ' vars=' . implode(',', DataSummarizer::datmDetailVars()); }
fwrite(STDOUT, "warm_detail_summaries $label done in {$ms}ms\n");
