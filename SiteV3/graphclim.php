<?php // content="text/plain; charset=utf-8"
include('/var/www/html/basics.php');
require_once ($root.'jpgraph/src/jpgraph.php');
require_once ($root.'jpgraph/src/jpgraph_bar.php');
include($fullpath.'unit-select.php');
include($fullpath.'functions.php');
include('climavs.php');

if(isset($_GET['x'])) { $dimx = $_GET['x']; } else { $dimx = 850; }
if(isset($_GET['y'])) { $dimy = $_GET['y']; } else { $dimy = 350; }

for($i = 0; $i < count($vars); $i++) {
	if(isset($_GET['type'.$i])) {
		for($m = 0; $m < 12; $m++) { $datay[$i][$m] = conv($vars[$i][$m],$clim_unit[$i],0); } $typeset[$i] = true; $cnt++;
		if($cnt == 1) { $title = $clim_descrip[$i]; if($clim_unit[$i]) { $title .= ' / '. $unitconvs[$clim_unit[$i]]; } }
		elseif($cnt > 1) { $title .= ' & '. $clim_descrip[$i]; if($clim_unit[$i]) { $title .= ' / '. $unitconvs[$clim_unit[$i]]; } }
	}
}
if(!$cnt) { $datay[0] = $vars[0]; $typeset[0] = true; $title = $clim_descrip[0]; if($clim_unit[0]) { $title .= ' / '. $unitconvs[$clim_unit[0]]; }  }

// Create the graph. These two calls are always required
$graph = new Graph($dimx,$dimy);
$graph->SetScale('textlin');
$graph->SetShadow();
 
// Adjust the margin a bit to make more room for titles
$graph->SetMargin(40,5,20,0);
$graph->xaxis->SetTickLabels($months);
 
// Create a bar pot
for($i = 0; $i < count($vars); $i++) {
	if($typeset[$i]) { $bplot[$i] = new BarPlot($datay[$i]); if($cnt > 1) { $bplot[$i]->SetLegend($clim_descrip[$i]); } }
}
if($cnt > 1) { $gbplot = new GroupBarPlot(array_merge($bplot)); }
else { $splot = array_merge($bplot); $gbplot = $splot[0]; }

$gbplot->SetWidth(.9);
$graph->Add($gbplot);
 
// Setup the titles
$graph->title->Set('LTA for ' . $title);
//$graph->xaxis->title->Set('Day');

$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
for($i = 0; $i < count($vars); $i++) {
	if($typeset[$i]) { $bplot[$i]->SetFillColor($clim_colours[$i]); }
}
$graph->legend->SetPos(0.5,0.85,'center','top');
$graph->legend->SetLayout(LEGEND_HOR);
$graph->legend->SetFrameWeight(2);
// Display the graph
$graph->Stroke();
?>