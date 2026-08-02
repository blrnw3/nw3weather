<?php
/**
 * Pre-warm serialised DataSummarizer caches for wx10–wx16 detail pages.
 * Invoked by cron_main after serialiseCSV('dat') so the first visitor of the
 * day (or after a data refresh) is not stuck rebuilding ranks/spells/windows.
 *
 * Usage:
 *   php warm_detail_summaries.php           # all detail start-year chips
 *   php warm_detail_summaries.php 2009      # one start year only
 */
error_reporting(E_ALL);
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

$start = isset($argv[1]) && ctype_digit((string)$argv[1])
	? (int)$argv[1]
	: null;

$t0 = microtime(true);
DataSummarizer::warmDetailSummaries($start);
$ms = round((microtime(true) - $t0) * 1000);
$label = ($start === null)
	? 'all=' . implode(',', DataSummarizer::$detailStartYearOptions)
	: "start=$start";
fwrite(STDOUT, "warm_detail_summaries $label done in {$ms}ms\n");
