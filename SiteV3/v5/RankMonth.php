<?php
require "Page.php";
require "Report.php";
require "rankmonth-body.php";
Page::init([
	"fileNum" => 42,
	"title" => "Ranked Monthly Data",
	"description" => "Historical ranked monthly weather values for Hampstead, London (NW3) - temperature, rainfall, wind and more.",
	"needValcolStyle" => true,
]);
Page::Start();

$report = new Report(["default" => "rain", "badCats" => ["cloud"]]);
$report->controls([
	"heading" => "Historical Ranked Monthly Data",
	"mode" => "rank-monthly",
	"showYear" => false,
	"showMonth" => true,
	"showStartYear" => true,
	"showSummary" => true,
	"showRankLimit" => true,
	"buttonSelectors" => true,
	"ajaxFragment" => "rankmonthdata.php",
	"ajaxBodyId" => "rm-ajax",
]);

echo '<div id="rm-ajax">';
nw3_rankmonth_render($report);
echo '</div>';

Page::End();
