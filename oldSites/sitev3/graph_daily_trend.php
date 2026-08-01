<?php
require './chartgen.php';
require_once ($root.'jpgraph/src/jpgraph_line.php');

$lta_on = false;
if(array_key_exists($dtype, $vars_to_climav_daily)) {
	$lta_on = true;
	include_once('climavs.php');
}

$length = isset($_GET['length']) ? (int) $_GET['length'] : 31;

$linCol = $colours_all[$types_all[$dtype]];
if($dtype == "sunhr") {  // Too pale
	$linCol = "#b59504";
}
$colorOpts = ["green", "#ffb473", "#557", "teal", "#336", "#aac", "#349", "#88b", "#14b", "#a86"];
$goodIntervals = [1, 2, 3, 4, 5, 10, 15.2, 30.44, 60.9, 91.3, 121.8, 182.6, 365.3];
$ltaTitle = "";
$botMargin = 35;
$maxVals = [];
$legPos = 0.94;
$needLegend = false;
$multiplot = [];
$mpColors = [];
$cumeable = $sumq_all[$types_all[$dtype]];

if(isset($_GET['multiyr'])) {
	$lastyr = $dyear - 1;
	$multiYrs = $_GET['multiyr'] === "last" ? "" : $_GET['multiyr'] . ',';
	$yrs = explode(",",$multiYrs . "$dyear,$lastyr");
	foreach($yrs as $i => $yr) {
		$datay = graphDailyYear($dtype, (int)$yr, $vars_to_climav_daily[$dtype], $cumeable);
		$multiplot[(int)$yr] = new LinePlot($datay[0]);
		$maxVals[] = max($datay[0]);
		$mpColors[(int)$yr] = $colorOpts[$i];
	}
	$mpColors[$dyear] = $linCol;
	unset($multiplot[$lastyr]); // Added via main plot datay
	$title = $_GET['multiyr'] === "last" ? "Current and last year " : "Recent and most extreme years ";
	$interval = 30.4;
	$length = count($datay[0]);
	$mainLegend = $lastyr;
	$botMargin = 55;
}
elseif(isset($_GET['year'])) {
	$mon = (int)$_GET['month'];
	$yr = (int)$_GET['year'];
	if($yr > $dyear || ($yr == $dyear && $mon > $dmonth)) {
		die('Invalid Month');
	}
	if($mon === 0) {
		$title = "$yr ";
		$datay = graphDailyYear($dtype, $yr, $vars_to_climav_daily[$dtype], $cumeable);
		$interval = 30.4;
		$length = count($datay[0]);
		$mainLegend = $yr;
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
$maxVals[] = max($datay[0]);
if($datay[2]) {
	$maxVals[] = max($datay[2]);
}

if($imperial) { $roundlevel = $roundsizeis_all[$types_all[$dtype]]; }
else { $roundlevel = $roundsizes_all[$types_all[$dtype]]; }
$smin = roundbig(min($datay[0]),$roundlevel,0);
$smax = roundbig(max($maxVals),$roundlevel,1);
$bfix = (strpos($dtype, 'p') === 0) ? 5 : 0;
if($smin < 0) {
	$botMargin = 0;
	$legPos = 0.92;
}
if($dtype === "rain" && count($datay[0]) > 360) {
	$smax = 900;
}

$graph = new Graph($dimx,$dimy);
$graph->SetScale('textlin',$smin,$smax);
//$graph->SetShadow();

if($lta_on) {
	$ltaTitle = " vs climate normal (dashed)";
}

$graph->SetMargin(30 + $bfix, 10, 20, $botMargin);
$graph->xaxis->SetTickLabels($datay[1]);
$graph->xaxis->SetTextTickInterval($interval);

// Main plot
$lplot = new LinePlot($datay[0]);
$graph->Add($lplot);
// LTA
if($datay[2]) {
	$lta_plot = new LinePlot($datay[2]);
	$graph->Add($lta_plot);
}
foreach($multiplot as $mp) {
	$graph->Add($mp);
	$needLegend = true;
}

// Setup the titles
$graph->title->Set($title.
	$descriptions_all[$types_all[$dtype]] .' / '. $std_units[$units_all[$types_all[$dtype]]] . $ltaTitle);

$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->SetFont(FF_FONT1,FS_NORMAL);
$lplot->setColor($linCol);
if($lta_on) {
	$lta_plot->setColor($linCol);
	$lta_plot->SetStyle("dotted");
//	$lta_plot->SetLegend("Normal");  // no color shows up due to use of line style
}
foreach($multiplot as $yr => $mp) {
	$mp->setColor($mpColors[$yr]);
	$mp->SetLegend($yr);
	$lplot->setColor("#999");
}
if($needLegend) {
	$lplot->SetLegend($mainLegend);
	// Legend has to be set here after the axis config
	$graph->legend->SetPos(0.5,$legPos,'center','top');
	$graph->legend->SetLayout(LEGEND_HOR);
	$graph->legend->SetFrameWeight(2);
	$graph->graph_theme=null;  // Disable massive margin for legend
}

// Display the graph
$graph->Stroke($fileName);
?>