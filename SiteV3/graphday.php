<?php // content="text/plain; charset=utf-8"
include('/home/nwweathe/public_html/basics.php');
require_once ($root.'jpgraph/src/jpgraph.php');
require_once ($root.'jpgraph/src/jpgraph_line.php');
include($root.'unit-select.php');
include($root.'functions.php');
include($root.'graphdaygen.php');

$lines = array('dew', 'temp', 'hum', 'rain');

if($imperial) {	$rscalemax = 0.75; $tscalemax = 90; $tscaleincr = 10; $rmar = 28;}
else { $tscalemax = 30; $rscalemax = 15; $tscaleincr = 5; $rmar = 15; }

if(max($data[$daytypes['temp']]) > $tscalemax) { $tscalemax += $tscaleincr; }

if($imperial) {	$tscalemin = $tscalemax - 75; }
else {
	if(min($data[$daytypes['dew']]) < $tscalemax - $tscaleincr*7) { $tscalemax -= $tscaleincr*2; }
	elseif(min($data[$daytypes['dew']]) < $tscalemax - $tscaleincr*6) { $tscalemax -= $tscaleincr; }
	$tscalemin = $tscalemax - 30;
}

if(max($data[$daytypes['rain']]) > $rscalemax) { $rscalemax *= 2; } if(max($data[$daytypes['rain']]) > $rscalemax) { $rscalemax *= 2; }

// Create the graph. These two calls are always required
$graph = new Graph($dimx,$dimy);

$graph->SetScale('textint',$tscalemin,$tscalemax);
$graph->SetYScale(0,'int',0,100);
$graph->SetYScale(1,'lin',0,$rscalemax);
$graph->SetYDeltaDist(40);
$graph->SetTickDensity(TICKD_DENSE);

$graph->SetShadow();

$graph->SetMargin(30,80,20,40); //left,right,top,bottom
$graph->xaxis->SetTickLabels($data['lab']);
$graph->xaxis->SetTextTickInterval($labu,-$shift);
if($num > 48 || $shift > 0) { $graph->xaxis->HideFirstTickLabel(); }
$graph->xaxis->SetPos('min');
if($num > 3) { $graph->xaxis->SetTitle('Day'); }

$graph->xgrid->Show(true,false);
$graph->xgrid->SetColor('lightgray');

// Create a bar pot
for($i = 0; $i < count($lines); $i++) {
	$bplot[$i] = new LinePlot($data[$daytypes[$lines[$i]]]);
	$bplot[$i]->SetLegend($daynames[$lines[$i]]);
	if($i > 1) { $graph->AddY($i-2,$bplot[$i]); } else { $graph->Add($bplot[$i]); }
	$bplot[$i]->SetColor($daycols[$lines[$i]]);
}
if($tscalemin < $lineAF) {
	$bplot[4] = new LinePlot($data['af']);
	$bplot[4]->SetLegend('Freezing Line');
	$graph->Add($bplot[4]);
	$bplot[4]->SetColor('lightsteelblue3');
}

// Setup the titles
$graph->title->Set($message.'Weather');

$graph->yaxis->SetTitle($unitT,'low');
$graph->yaxis->SetTitleMargin(20);
$graph->yaxis->SetColor('orange');
$graph->yaxis->title->SetColor('orange');
$graph->yaxis->title->SetAngle(0);
$graph->yaxis->SetLabelMargin(10);
//$graph->yaxis->scale->ticks->Set(2,1);
//$graph->yaxis->SetTitleSide(SIDE_RIGHT);
//$graph->yaxis->SetLabelSide(SIDE_RIGHT);

$graph->ynaxis[0]->SetTitle('%','low');
$graph->ynaxis[0]->SetTitleMargin(15);
$graph->ynaxis[0]->SetColor('darkgreen');
$graph->ynaxis[0]->SetLabelMargin(5);
$graph->ynaxis[0]->HideTicks();
$graph->ynaxis[0]->title->SetColor('darkgreen');
$graph->ynaxis[0]->title->SetAngle(0);
//$graph->ynaxis[0]->SetTextLabelInterval(2);

$graph->ynaxis[1]->SetTitle($unitR,'low');
$graph->ynaxis[1]->SetTitleMargin($rmar);
$graph->ynaxis[1]->SetLabelMargin(3);
$graph->ynaxis[1]->SetColor('blue');
$graph->ynaxis[1]->title->SetColor('blue');
$graph->ynaxis[1]->title->SetAngle(0);
$graph->ynaxis[1]->HideTicks();

$graph->footer->center->Set($footerstring);

$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
//$graph->xaxis->SetFont(FF_FONT0,FS_BOLD);
//$graph->ynaxis[1]->SetFont(FF_FONT0,FS_BOLD);
$graph->legend->SetPos(0.5,0.85,'center','top');
$graph->legend->SetLayout(LEGEND_HOR);
$graph->legend->SetFrameWeight($legendBoxWeight);
// Display the graph
//cache result
if(isset($_GET['cache']) ) {
	$graph->Stroke($cacheName);
	log_events("imageCache.txt", $cacheName);
} else {
	$graph->Stroke($fileName);
}

?>