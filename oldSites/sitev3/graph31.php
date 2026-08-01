<?php
require './chartgen.php';

$length = isset($_GET['length']) ? (int) $_GET['length'] : 31;

$goodIntervals = [1, 2, 3, 4, 5, 10, 15.2, 30.44, 60.9, 91.3, 121.8, 182.6, 365.3];

if(isset($_GET['year'])) {
	$mon = (int)$_GET['month'];
	$yr = (int)$_GET['year'];
	if($yr > $dyear || ($yr == $dyear && $mon > $dmonth)) {
		die('Invalid Month');
	}
	if($mon === 0) {
		$title = "$yr ";
		$datay = graphDailyYear($dtype, $yr);
		$interval = 30.4;
		$length = count($datay[0]);
	} else {
		$title = $months[$mon-1] . ' '. $yr .' ';
		$datay = graphDailyMonth($dtype, $mon, $yr);
		$interval = 1;
	}
} else {
	$datay = graphDaily($dtype, $length);
	$length = min($length, count($datay[0]));
	$title = "Last $length days ";
	$labelNum = ($length < 60) ? 21 : 12;
	$interval = find_nearest($length / $labelNum, $goodIntervals);
}

if($imperial) { $roundlevel = $roundsizeis_all[$types_all[$dtype]]; }
else { $roundlevel = $roundsizes_all[$types_all[$dtype]]; }
$smin = roundbig(min($datay[0]),$roundlevel,0);
$smax = roundbig(max($datay[0]),$roundlevel,1);
$bfix = (strpos($dtype, 'p') === 0) ? 5 : 0;

$graph = new Graph($dimx,$dimy);
$graph->SetScale('textlin',$smin,$smax);
//$graph->SetShadow();

$graph->SetMargin(30 + $bfix, 10, 20, 35);
$graph->xaxis->SetTickLabels($datay[1]);
$graph->xaxis->SetTextTickInterval($interval);

// Create a bar pot
$bplot = new BarPlot($datay[0]);

$width = ($length > 100) ? 1 : 0.9;
$bplot->SetWidth($width);
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
$linCol = ($length > 100) ? $colours_all[$types_all[$dtype]] : "#c0c0c0";
$bplot->setColor($linCol);

// Display the graph
$graph->Stroke($fileName);
?>