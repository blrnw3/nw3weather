<?php // content="text/plain; charset=utf-8"
include('/home/nwweathe/public_html/basics.php');
require_once ($root.'jpgraph/src/jpgraph.php');
require_once ($root.'jpgraph/src/jpgraph_line.php');
require_once ($root.'jpgraph/src/jpgraph_scatter.php');
include($root.'unit-select.php');
include($root.'functions.php');
$autoscale = true;
include($root.'graphdaygen.php');

//print_m($dextra);
$rnmaxScale = roundbig($unitcorr2);
if(false && $me) {
	echo $rnmaxScale;
	echo "<br />". $data['rnmax'];
}

$graph = new Graph($dimx,$dimy); $marg2 = 10;
if($dtype == 'rain') { $graph->SetScale('text'.$rnlin,0,$rnmaxScale); $marg3 = 28; }
elseif($dtype == 'wdir') { $graph->SetScale('textlin',0,360); $wdirSet = true; $marg3 = 22; }
else { $graph->SetScale('textint'); }

for($i = 0; $i < 5; $i++) {
	if($dble[$i]) {
		if($dextra[$i] == 'rain') { $graph->SetYScale($i,$rnlin,0,$rnmaxScale); $marg3 = 28; }
		else { $graph->SetYScale($i,'int'); }
	$extra .= ' & '.$daynames[$dextra[$i]]; $marg2 +=45; }
}

$graph->SetShadow();

if($argc > 3) { //main-page mini-graphs margin override
	$marg2 = 40;
	$marg3 = $argv[5];
}
$graph->SetMargin(30+$marg3,$marg2,20,$fmarge); //left,right,top,bottom

$graph->xaxis->SetTickLabels($data['lab']);
$graph->xaxis->SetTextTickInterval($labu,-$shift);
if($num > 48 || $shift > 0) { $graph->xaxis->HideFirstTickLabel(); }
$graph->xaxis->SetPos('min');
if($num > 3) { $graph->xaxis->SetTitle('Day'); }

$graph->xgrid->Show(true,false);
$graph->xgrid->SetColor('lightgray');

// Create plot
if($wdirSet) { $bplot = new ScatterPlot($data[$daytypes[$dtype]]); }
else { $bplot = new LinePlot($data[$daytypes[$dtype]]); }
$graph->Add($bplot);
$bplot->SetColor($daycols[$dtype]);

for($i = 0; $i < 5; $i++) {
	if($dble[$i]) { ###
		$bplot2[$i] = new LinePlot($data[$daytypes[$dextra[$i]]]);
		$graph->AddY($i,$bplot2[$i]);
		$bplot2[$i]->SetColor($daycols[$dextra[$i]]);
	} ###
}

if($wdirSet) {	// special case
	$wdirSet_os = 45 - floor($dimy / 10); if($wdirSet_os < 3) { $wdirSet_os = 3; }
	$dirs = array_reverse(array('N','NW','W','SW','S','SE','E','NE','N'));
	for($i = 0; $i < 9; $i++) {
		$txt[$i]=new Text($dirs[$i]);
		$txt[$i]->SetScalePos(0,$i*45+$wdirSet_os); //(45+((9-$i)/3))
		$txt[$i]->SetFont(FF_FONT1,FS_BOLD);
		$txt[$i]->SetColor('red');
		$graph->AddText($txt[$i]);
	}
	$bplot->mark->SetType(MARK_FILLEDCIRCLE);
	$bplot->mark->SetFillColor("red");
	$bplot->mark->SetColor("red");
	$bplot->mark->SetWidth(1);

	$graph->yaxis->scale->ticks->Set(90,10);
}

// Setup the titles
$graph->title->Set($message.$daynames[$dtype].$extra);

$graph->yaxis->SetColor($daycols[$dtype]);
$graph->yaxis->SetLabelMargin(10);
if(!$skipYaxis) { $graph->yaxis->SetTitle($daynames[$dtype]); }
$graph->yaxis->SetTitleMargin(15+$marg3);
$graph->yaxis->title->SetColor($daycols[$dtype]);

for($i = 0; $i < 5; $i++) {
	if($dble[$i]) { ###
		$graph->ynaxis[$i]->SetTitle($daynames[$dextra[$i]]);
		$graph->ynaxis[$i]->SetTitleMargin(35);
		$graph->ynaxis[$i]->SetColor($daycols[$dextra[$i]]);
		$graph->ynaxis[$i]->SetLabelMargin(3);
		$graph->ynaxis[$i]->HideTicks();
		$graph->ynaxis[$i]->title->SetColor($daycols[$dextra[$i]]);
	} ###
}

// if($sciptime) {
// 	$phpload = microtime(get_as_float) - $scriptbeg;
// 	$footerstring .= '; Final Load time: ' . round($phpload, 2) . ' s';
// }

if(!$nofooter) { $graph->footer->center->Set($footerstring); }

$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

// Display the graph
if(isset($_GET['cache']) ) {
	$graph->Stroke($cacheName);
	log_events("imageCache.txt", $cacheName);
} else {
	$graph->Stroke($fileName);
}
?>