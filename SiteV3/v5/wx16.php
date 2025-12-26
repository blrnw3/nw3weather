<?php
require("Page.php");
require("ViewDetailedData.php");
Page::init([
	"fileNum" => 16,
	"title" => "Pressure Detail",
	"description" => 'Detailed latest barometric pressure data/information and records from NW3 weather station.'
]);
Page::Start();

require ROOT.'PressureTags.php';
?>

<h1>Detailed Barometric Pressure Data</h1>

<?php
$mainTables = new ViewDetailedData("baro");
$measures = ['Pressure','Pressure Trend','24hr Mean Pressure'];
$values = [
	Wx::conv(Live::$pres, Wx::Pressure, 1, 0, 1),
	Wx::conv(Live::$HR24['changeHr']['pres'], Wx::Pressure, 1, 1, 1) . ' /hr',
	Wx::conv(Live::$HR24['mean']['pres'], Wx::Pressure, 1, 0, 1)
];
$conv = [Wx::None, Wx::None, Wx::None];

$mainTables->currentLatest($measures, $values, $conv);
$mainTables->avgsExtrmsRecs();
$mainTables->pastYearAvgsExtrms();
$mainTables->rankTables();
?>

<h2>Notes:</h2>
<ul>
	<li>Valid Pressure records began in January 2009</li>
	<li>All figures, unless specified, relate to the period midnight-midnight, this being when daily extremes are reset</li>
	<li>All values are accurate to &plusmn;0.2 mb since Sept 2017; before that, to within &plusmn;2 mb</li>
</ul>

<?php Page::End(); ?>
