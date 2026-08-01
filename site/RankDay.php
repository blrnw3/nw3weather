<?php
require "Page.php";
require "Report.php";
require "rankday-body.php";
Page::init([
	"fileNum" => 41,
	"title" => "Ranked Daily Data",
	"description" => "Historical ranked daily weather values for Hampstead, London (NW3) - temperature, rainfall, wind and more.",
	"needValcolStyle" => true,
]);
Page::Start();

$report = new Report(["default" => "rain", "badCats" => ["cloud"]]);
$report->controls([
	"heading" => "Historical Ranked Daily Data",
	"mode" => "rank-daily",
	"showYear" => false,
	"showMonth" => true,
	"showStartYear" => true,
	"showRankLimit" => true,
	"buttonSelectors" => true,
	"ajaxFragment" => "rankdaydata.php",
	"ajaxBodyId" => "rd-ajax",
]);

echo '<div id="rd-ajax">';
nw3_rankday_render($report);
echo '</div>';

Page::End();
