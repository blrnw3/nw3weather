<?php
require './chartgen.php';

$length = isset($_GET['length']) ? (int) $_GET['length'] : 12;

$goodIntervals = [1, 2, 3, 4, 6, 12];

$avgType = isset($_GET['mmm']) ? $_GET['mmm'] : 2.2;
$addTit = ($avgType == 2.2) ? $sumormean[$sumq_all[$types_all[$dtype]]] :
	( ($avgType == 2.1) ? 'highest' : ( ($avgType == 2.3) ? 'count' : 'lowest' ) );

if(isset($_GET['year'])) {
	$yproc = (int)$_GET['year'];
	$datay = graphYear($dtype, $yproc, $avgType);
	$title = $yproc;
	$interval = 1;
}
else {
	$datay = graphMonthly($dtype, $length, $avgType);
	$length = min($length, count($datay[0]));
	$title = "Last $length-months";
	$labelNum = 12;
	$interval = find_nearest($length / $labelNum, $goodIntervals);
}

if($imperial) { $smin = 29; $smax = 30.5; }
else { $smin = 990; $smax = 1040; }

$graph = new Graph($dimx,$dimy);
if(strpos($dtype,'p') === 0) { $graph->SetScale('textlin',$smin,$smax); $bfix = 5; }
else { $graph->SetScale('textlin'); }

$graph->SetShadow();
$graph->SetMargin(30+$bfix,10,20,20);
$graph->xaxis->SetTickLabels($datay[1]);
$graph->xaxis->SetTextTickInterval($interval);

$bplot = new BarPlot($datay[0]);

$width = ($length > 100) ? 1 : 0.9;
$bplot->SetWidth($width);
$graph->Add($bplot);

$graph->title->Set($title .' monthly-'. $addTit .' '.
	$descriptions_all[$types_all[$dtype]] . ' / ' . $std_units[$units_all[$types_all[$dtype]]]);

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