<?php
require './chartgen.php';

$length = isset($_GET['length']) ? (int) $_GET['length'] : 31;
if($length > 500) {
	$length = 500;
}

if(isset($_GET['year'])) {
	$mon = (int)$_GET['month'];
	$yr = (int)$_GET['year'];
	if($yr > $dyear || ($yr == $dyear && $mon > $dmonth)) {
		die('Invalid Month');
	}

	$title = $months[$mon-1] . ' '. $yr .' ';
	$datay = graphMonth($dtype, $mon, $yr);
	$interval = 1;
} else {
	$datay = graphDaily($dtype, $length);
	$title = "Last $length days ";
	$interval = $datay[2];
}

//print_m($datay);

if($imperial) { $roundlevel = $roundsizeis_all[$types_all[$dtype]]; }
else { $roundlevel = $roundsizes_all[$types_all[$dtype]]; }
$smin = roundbig(min($datay[0]),$roundlevel,0);
$smax = roundbig(max($datay[0]),$roundlevel,1);
$bfix = (strpos($dtype, 'p') === 0) ? 5 : 0;

$graph = new Graph($dimx,$dimy);
$graph->SetScale('textlin',$smin,$smax);
$graph->SetShadow();

$graph->SetMargin(30 + $bfix, 10, 20, 35);
$graph->xaxis->SetTickLabels($datay[1]);
$graph->xaxis->SetTextTickInterval($interval);

// Create a bar pot
$bplot = new BarPlot($datay[0]);

$bplot->SetWidth(0.9);
$graph->Add($bplot);

// Setup the titles
$graph->title->Set($title.
	$descriptions_all[$types_all[$dtype]] .' / '. $std_units[$units_all[$types_all[$dtype]]]);
$graph->xaxis->title->Set('Day');

$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->SetFont(FF_FONT0,FS_BOLD);
$bplot->SetFillColor($colours_all[$types_all[$dtype]]);
// Display the graph
$graph->Stroke($fileName);
?>