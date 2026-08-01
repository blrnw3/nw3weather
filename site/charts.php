<?php
require("Page.php");
Page::init([
	"fileNum" => 32,
	"title" => "Chart Viewer",
	"description" => "Latest and historical 31-day, monthly and annual interactive charts of weather variables for Hampstead, London."
]);
Page::Start();
?>

<h1 id="chart-heading">Data Charts &ndash; Mean Temperature</h1>

<?php
Charts::chartViewer(array(
	'default' => 'tmean',
	'height' => 520,
	'headingId' => 'chart-heading',
));
?>

<?php Page::End(); ?>
