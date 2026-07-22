<?php
require "Page.php";
require "Report.php";
require "rankyear-body.php";
Page::init([
	"fileNum" => 42.1,
	"title" => "Ranked Annual Data",
	"description" => "Historical ranked annual weather values for Hampstead, London (NW3) - temperature, rainfall, wind and more.",
	"needValcolStyle" => true,
]);
Page::Start();

$report = new Report(["default" => "rain", "badCats" => ["cloud"]]);
$report->controls([
	"heading" => "Historical Ranked Annual Data",
	"mode" => "rank-annual",
	"showYear" => false,
	"showMonth" => false,
	"showStartYear" => true,
	"showSummary" => true,
	"showRankLimit" => false,
	"buttonSelectors" => true,
	"ajaxFragment" => "rankyeardata.php",
	"ajaxBodyId" => "ry-ajax",
]);

echo '<div id="ry-ajax">';
nw3_rankyear_render($report);
echo '</div>';

Page::End();
