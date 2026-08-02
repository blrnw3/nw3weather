<?php
require "Page.php";
require "Report.php";
require "rankperiods-body.php";
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
nw3_rankperiods_render($report);
echo '</div>';

Page::End();
