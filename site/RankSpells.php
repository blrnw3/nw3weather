<?php
require "Page.php";
require "Report.php";
require "rankspells-body.php";
Page::init([
	"fileNum" => 43,
	"title" => "Ranked Spells",
	"description" => "Longest wet, dry and threshold spells for Hampstead, London (NW3) weather variables.",
	"needValcolStyle" => true,
]);
Page::Start();

$report = new Report([
	"default" => "rain",
	"badCats" => ["wdir", "cloud", "comms", "extra", "issues", "away", "spare"],
]);
$report->controls([
	"heading" => "Historical Ranked Spells",
	"mode" => "rank-spells",
	"showYear" => false,
	"showMonth" => true,
	"showStartYear" => true,
	"showRankLimit" => true,
	"showSpell" => true,
	"buttonSelectors" => true,
	"ajaxFragment" => "rankspellsdata.php",
	"ajaxBodyId" => "rs-ajax",
]);

echo '<div id="rs-ajax">';
nw3_rankspells_render($report);
echo '</div>';

Page::End();
