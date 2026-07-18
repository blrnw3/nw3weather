<?php
require("Page.php");
Page::init([
	"fileNum" => 31,
	"title" => "Daily Historical Graphs",
	"description" => "View customisable per-minute graphs of temperature, wind, rain, pressure and more over a chosen day or range of days."
]);
Page::Start();
?>

<h1>Daily Historical Graphs</h1>
<p>Per-minute data for a chosen end date and number of days. Choose a multi-variable fixed-scale chart, or a single auto-scaled series.</p>

<?php
Charts::graphViewer(array(
	'default' => 'temp',
	'defaultMode' => 'thrd',
	'height' => 500,
));
?>

<?php Page::End(); ?>
