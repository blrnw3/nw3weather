<?php
require "Page.php";
require "Report.php";
require "datamonth-body.php";
Page::init([
	"fileNum" => 40.1,
	"title" => "Monthly Data Tables",
	"description" => "Detailed historical monthly summary data tables with all-time summaries for Hampstead, London (NW3).",
	"needValcolStyle" => true,
]);
Page::Start();

$report = new Report(["default" => "rain", "badCats" => ["cloud"]]);
$report->controls([
	"heading" => "Monthly Data Tables",
	"showYear" => false,
	"showStartYear" => true,
	"showSummary" => true,
	"buttonSelectors" => true,
	"ajaxFragment" => "datamonthdata.php",
	"ajaxBodyId" => "dm-ajax",
]);

echo '<div id="dm-ajax">';
nw3_datamonth_render($report);
echo '</div>';

Page::End();
