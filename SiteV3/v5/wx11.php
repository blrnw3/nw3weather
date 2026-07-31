<?php
require "Page.php";
require "ViewDetailedData.php";
Page::init([
	"fileNum" => 11,
	"title" => "Sunshine Detail",
	"description" => 'Detailed sunshine hours data, graphs and records from NW3 weather station.'
]);
Page::Start();
?>

<h1>Detailed Sunshine Data</h1>

<?php
$mainTables = new ViewDetailedData("sun");


$mainTables->currentLatest([], [], []);

$mainTables->avgsExtrmsRecs();
$mainTables->pastYearAvgsExtrms();
$mainTables->rankTables();
?>

<h2>Notes</h2>
<ul>
	<li>Sunshine totals are currently only available for prior days because they are computed daily from webcam footage using an image classification model</li>
	<li>Sunshine records at NW3 began in 2009</li>
	<?php $mainTables->historicalNoteItem(); ?>
	<li>Figures in brackets refer to departure from
		<a href="/wxaverages.php" title="Long-term NW3 climate averages">average conditions</a>
		(as a percentage for sunshine)
	</li>
	<li>Sun days are days with more than 0 hours of sunshine recorded</li>
	<li>% of max is sunshine hours as a percentage of the maximum possible for that period.</li>
</ul>

<p><a href="/wxdataday.php?vartype=sunhr" title="<?php echo Date::$dyear; ?> daily sun totals"><b>View daily totals for the past year</b></a></p>


<?php Page::End(); ?>
