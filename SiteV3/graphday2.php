<?php // content="text/plain; charset=utf-8"
include('/var/www/html/basics.php');
require_once ($root.'jpgraph/src/jpgraph.php');
require_once ($root.'jpgraph/src/jpgraph_line.php');
require_once ($root.'jpgraph/src/jpgraph_date.php');
include($root.'unit-select.php');
include($root.'functions.php');
include($root.'graphdaygen.php');

$lines = array('wind', 'gust', 'baro');

if($unitW == 'kph') { $ucorrW = 2; } else { $ucorrW = 1; }
if($imperial) { $pminscale = 28; $pmaxscale = 31; } else { $pminscale = 970; $pmaxscale = 1045; }

$graph = new Graph($dimx,$dimy);
$graph->SetScale('textint',0,30*$ucorrW);
$graph->SetYScale(0,'lin',$pminscale,$pmaxscale);
$graph->SetTickDensity(TICKD_DENSE);

$graph->SetShadow(); // Add a drop shadow

$graph->SetMargin(40,38,20,10); //left,right,top,bottom
$graph->xaxis->SetTickLabels($data['lab']);
$graph->xaxis->SetTextTickInterval($labu,-$shift);
if($num > 48 || $shift > 0) { $graph->xaxis->HideFirstTickLabel(); }
$graph->xaxis->SetPos('min');
if($num > 3) { $graph->xaxis->SetTitle('Day'); }

$graph->xgrid->Show(true,false);
$graph->xgrid->SetColor('lightgray');

// Create line plot
for($i = 0; $i < count($lines); $i++) {
	$bplot[$i] = new LinePlot($data[$daytypes[$lines[$i]]]);
	$bplot[$i]->SetLegend($daynames[$lines[$i]]);
	if($i > 1) { $graph->AddY($i-2,$bplot[$i]); } else { $graph->Add($bplot[$i]); }
	$bplot[$i]->SetColor($daycols[$lines[$i]]);
}

// Setup the titles
$graph->title->Set($message.'Weather');

$graph->yaxis->SetTitle($unitW,'low');
$graph->yaxis->SetTitleMargin(17);
$graph->yaxis->SetColor($daycols['wind']);
$graph->yaxis->title->SetColor($daycols['wind']);
$graph->yaxis->title->SetAngle(0);
$graph->yaxis->SetLabelMargin(8);

$graph->ynaxis[0]->SetTitle($unitP,'high');
$graph->ynaxis[0]->SetTitleMargin(10);
$graph->ynaxis[0]->SetColor($daycols['baro']);
$graph->ynaxis[0]->SetLabelMargin(3);
$graph->ynaxis[0]->HideTicks();
$graph->ynaxis[0]->title->SetColor($daycols['baro']);
$graph->ynaxis[0]->title->SetAngle(0);
$graph->ynaxis[0]->SetTextLabelInterval(2);

$graph->footer->center->Set($footerstring);

$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
//$graph->xaxis->SetFont(FF_FONT0,FS_BOLD);
//$graph->ynaxis[1]->SetFont(FF_FONT0,FS_BOLD);
$graph->legend->SetPos(0.5,0.85,'center','top');
$graph->legend->SetColumns(count($lines));
$graph->legend->SetFrameWeight($legendBoxWeight);
// Display the graph
if(isset($_GET['cache']) ) {
	$graph->Stroke($cacheName);
	log_events("imageCache.txt", $cacheName);
} else {
	$graph->Stroke($fileName);
}
?>