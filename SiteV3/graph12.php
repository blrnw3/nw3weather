<?php
require './chartgen.php';

function graphMonthlyRecent($type, $len, $summary_type) {
	$graph = $labels = [];
	$format = ($len > 18) ? 'M-y' : 'M';
	$monthly_data = getMonthlyData($type, $summary_type, $GLOBALS["dyear"] - intval($len / 12) - 1, $GLOBALS["yr_yest"]);
	foreach ($monthly_data as $y => $months) {
		foreach ($months as $m => $v) {
			$graph[] = floatval( conv($v, typeToConvType($type), 0, 0, 1, 0, 0, true) );
			$labels[] = date($format, mkdate($m, 3, $y));
		}
	}
	return [array_slice($graph, -$len), array_slice($labels, -$len)];
}

function graphMonthlyYear($type, $year, $summary_type) {
	$data = getMonthlyData($type, $summary_type, $year, $year);
	$graph = [];
	foreach ($data[$year] as $v) {
		$graph[] = conv($v, typeToConvType($type), 0, 0, 1,0,0, true);
	}
	return array($graph, $GLOBALS['months']);
}

$length = isset($_GET['length']) ? (int) $_GET['length'] : 12;

$goodIntervals = [1, 2, 3, 4, 6, 12];

if(isset($_GET['year'])) {
	$yproc = (int)$_GET['year'];
	$datay = graphMonthlyYear($dtype, $yproc, $summary_type);
	$title = $yproc;
	$interval = 1;
	$length = 12;
}
else {
	$datay = graphMonthlyRecent($dtype, $length, $summary_type);
	$length = min($length, count($datay[0]));
	$title = "Last $length-months";
	$labelNum = 12;
	$interval = find_nearest($length / $labelNum, $goodIntervals);
}

$barCol = $colours_all[$types_all[$dtype]];

if($imperial) { $smin = 29; $smax = 30.5; }
else { $smin = 990; $smax = 1040; }

$graph = new Graph($dimx,$dimy);
if(strpos($dtype,'p') === 0) { $graph->SetScale('textlin',$smin,$smax); $bfix = 5; }
else { $graph->SetScale('textlin'); }

$mainPlot = new BarPlot($datay[0]);

if(isset($_GET['lta']) && array_key_exists($dtype, $vars_to_climav) && $summary_type <= 1) {
	$lta_on = true;
	$lta_str = " vs avg";
	include_once('climavs.php');
	$climvals = [];
	$end_mon = isset($_GET['year']) ? 12 : (int)date('n');
	for($i = 0; $i < $length; $i++) {
		$offset = ($end_mon - $length + $i) % 12;
		if ($offset < 0) {
			$offset += 12; // Weird PHP mod behaviour
		}
		$climvals[] = conv($vars_to_climav[$dtype][$offset], typeToConvType($dtype), 0,0,1,0,0, true);
	}
	$climplot = new BarPlot($climvals);
	$bplot = new GroupBarPlot(array($mainPlot, $climplot));
	$mainPlot->SetLegend($descriptions_all[$types_all[$dtype]]);
	$botMargin = 55;
} else {
	$lta_on = false;
	$lta_str = "";
	$bplot = $mainPlot;
	$botMargin = 25;
}

$graph->SetShadow();
$graph->SetMargin(30+$bfix,10,20,$botMargin);
$graph->xaxis->SetTickLabels($datay[1]);
$graph->xaxis->SetTextTickInterval($interval);

$width = ($length > 100) ? 1 : 0.9;
$bplot->SetWidth($width);
$graph->Add($bplot);

$graph->title->Set($title .' monthly-'. $SUMMARY_NAMES[$summary_type] .' '.
	$descriptions_all[$types_all[$dtype]] . $lta_str . ' / ' . $std_units[$units_all[$types_all[$dtype]]]);

$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->SetFont(FF_FONT1,FS_NORMAL);

// Colors have to be set here
$mainPlot->SetFillColor($barCol);
$linCol = ($length > 100) ? $barCol : "#c0c0c0";
$mainPlot->setColor($linCol);

if($lta_on) {
	$filcol = ($dtype == "rain") ? "#444" : "#666";
	$climplot->setColor($barCol);
	$climplot->SetFillColor($filcol);
	$climplot->SetPattern(PATTERN_DIAG2, $barCol);
	$climplot->SetLegend("Avg " . $descriptions_all[$types_all[$dtype]]);

	// Legend has to be set here after the axis config
	$graph->legend->SetPos(0.5,0.94,'center','top');
	$graph->legend->SetLayout(LEGEND_HOR);
	$graph->legend->SetFrameWeight(2);
	$graph->graph_theme=null;  // Disable massive margin for legend
}
// Display the graph
$graph->Stroke($fileName);
?>