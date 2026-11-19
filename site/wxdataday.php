<?php
require "Page.php";
require "Report.php";
require "dataday-body.php";
require "ExpensivePage.php";
Page::init([
	"fileNum" => 40,
	"title" => "Daily Data Tables",
	"description" => "Detailed historical daily data tables with monthly summaries for Hampstead, London (NW3).",
	"needValcolStyle" => true,
]);
Page::Start();

$report = new Report(["default" => "rain", "badCats" => ["cloud"]]);
$report->controls([
	"heading" => "Daily Data Tables",
	"mode" => "daily",
	"showYear" => true,
	"buttonSelectors" => true,
	"ajaxFragment" => "datadaydata.php",
]);

echo '<div id="dd-ajax">';
nw3_expensive_html('dataday', $report, 'nw3_dataday_render', nw3_expensive_key_parts('dataday', $report));
echo '</div>';

Page::End();
