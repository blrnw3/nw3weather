<?php
/**
 * Ranked Periods page.
 *
 * Bot mitigation (2026-08-06): full-page loads always render the *default*
 * report (ignore ranking query-string params). Custom combinations are only
 * computed via rankperiodsdata.php, which the UI loads over XHR when chips
 * are clicked. Deep links still work for real browsers: NW3_reportSel reads
 * location.search on boot and hydrates via that same AJAX path.
 */
$rpIgnoreKeys = [
	'month', 'period', 'no_overlap', 'rankLimit', 'summary_type',
	'start_year_rep', 'threshold', 'spell_dir', 'vartype', 'agg', 'year',
];
foreach ($rpIgnoreKeys as $k) {
	unset($_GET[$k]);
}

require "Page.php";
require "Report.php";
require "rankperiods-body.php";
require "rankperiods-cache.php";
Page::init([
	"fileNum" => 42.2,
	"title" => "Ranked Periods",
	"description" => "Historical ranked multi-day weather periods for Hampstead, London (NW3) - temperature, rainfall, wind and more.",
	"needValcolStyle" => true,
]);
Page::Start();

$report = new Report(["default" => "tmax", "badCats" => ["cloud"]]);
$report->controls([
	"heading" => "Historical Ranked Periods",
	"mode" => "rank-periods",
	"showYear" => false,
	"showMonth" => true,
	"showStartYear" => true,
	"showSummary" => true,
	"showRankLimit" => true,
	"showPeriod" => true,
	"buttonSelectors" => true,
	"ajaxFragment" => "rankperiodsdata.php",
	"ajaxBodyId" => "rp-ajax",
]);

echo '<div id="rp-ajax">';
nw3_rankperiods_cached_render($report);
echo '</div>';

Page::End();
