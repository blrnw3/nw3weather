<?php
require './chartgen.php';
require_once ($root.'jpgraph/src/jpgraph_line.php');

function graphYearly($type, $summary_type, $st, $en) {
	$graph = $labels = [];
	foreach( getAnnualData($type, $summary_type, $st, $en) as $y => $val ) {
		$graph[] = conv($val, typeToConvType($type), 0,0,1,0,0, true);
		$labels[] = $y;
	}
	return array($graph, $labels);
}

function graphYearlyForMonth($type, $summary_type, $month, $st, $en) {
	$graph = $labels = [];
	foreach( getMonthlyData($type, $summary_type, $st, $en) as $y => $months ) {
		$graph[] = conv($months[$month], typeToConvType($type), 0,0,1,0,0, true);
		$labels[] = $y;
	}
	return array($graph, $labels);
}

$mon = isset($_GET['month']) ? (int) $_GET['month'] : 0;  // 1-indexed months
$oneMonth = $mon > 0;

$start = isset($_GET['start']) ? (int) $_GET['start'] : 2009;
$end = isset($_GET['end']) ? (int) $_GET['end'] : $dyear - 1;
if($end > $dyear) {
	$end = $dyear;
}
if($start >= $end) {
	$start = $end - 1;
}
// INclude current year if month has elapsed
if($oneMonth && $mon < $dmonth && $end === $dyear - 1) {
	$end = $dyear;
}
// EXclude current year if month has elapsed
if($oneMonth && $mon > $dmonth && $end === $dyear) {
	$end--;
}

$goodIntervals = [1, 2, 3, 5, 10, 20];
$doBars = ($oneMonth || isset($_GET['bars']) || $isSum || $summary_type > 1) && !in_array($dtype, $categories["Pressure"]);


$datay = $oneMonth ? graphYearlyForMonth($dtype, $summary_type, $mon, $start, $end) : graphYearly($dtype, $summary_type, $start, $end);
$truStart = $datay[1][0];
$length = count($datay[0]);
$title = "$truStart - $end Annual" . ($oneMonth ? date(' (M)', mkdate($mon, 1)) : "");
$interval = find_nearest($length / 15, $goodIntervals);

$graph = new Graph($dimx,$dimy);
$graph->SetScale('textint');
$graph->img->SetAntiAliasing(false);

$bplot = $doBars ? new BarPlot($datay[0]) : new LinePlot($datay[0]);

if(isset($_GET['lta']) && array_key_exists($dtype, $vars_to_climav_annual) && $summary_type <= 1) {
	$lta_on = true;
	$lta_str = " vs avg";
	include_once('climavs.php');
	$climvals = [];
	for($i = 0; $i < $length; $i++) {
		$val = $oneMonth ? $vars_to_climav[$dtype][$mon-1] : $vars_to_climav_annual[$dtype];
		$climvals[] = conv($val, typeToConvType($dtype), 0,0,1,0,0, true);
	}
	$climplot = new LinePlot($climvals);
	$climplot->SetBarCenter();
//	$bplot->SetLegend($descriptions_all[$typeNum]);
} else {
	$lta_on = false;
	$lta_str = "";
}

$botMargin = 25;
$graph->SetShadow();
$graph->SetMargin(35+$bfix,10,20,$botMargin);
$graph->xaxis->SetTickLabels($datay[1]);
$graph->xaxis->SetTextTickInterval($interval);

$graph->Add($bplot);

$graph->title->Set($title .' '. $SUMMARY_NAMES[$summary_type] . ' '.
	$description . $lta_str . ' / ' . $std_units[$units_all[$typeNum]]);

$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->SetFont(FF_FONT1,FS_NORMAL);

// Colors have to be set here

$barCol = $colours_all[$typeNum];
if($doBars) {
	$bplot->SetFillColor($barCol);
	$bplot->SetWidth(($length > 100) ? 1 : 0.9);
} else {
	$bplot->SetWeight(3);
}
$bplot->setColor($barCol);

if($lta_on) {
	$graph->Add($climplot);
	$filcol = ($dtype == "rain") ? "#444" : "#666";
	$climplot->setColor("#444");
	$climplot->SetStyle("dotted");
	$climplot->SetWeight(2);
//	$climplot->SetLegend("Avg " . $descriptions_all[$typeNum]);

	// Legend has to be set here after the axis config
	$graph->legend->SetPos(0.5,0.94,'center','top');
	$graph->legend->SetLayout(LEGEND_HOR);
	$graph->legend->SetFrameWeight(2);
	$graph->graph_theme=null;  // Disable massive margin for legend
}
// Display the graph
$graph->Stroke($fileName);
?>