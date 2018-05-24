<?php // content="text/plain; charset=utf-8"
include('/var/www/html/basics.php');
require_once ($root.'jpgraph/src/jpgraph.php');
require_once ($root.'jpgraph/src/jpgraph_line.php');
include($fullpath.'unit-select.php');
include($fullpath.'functions.php');
include_once($fullpath.'climavs.php');

if(isset($_GET['x'])) { $dimx = $_GET['x']; } else { $dimx = 850; }
if(isset($_GET['y'])) { $dimy = $_GET['y']; } else { $dimy = 350; }

for($i = 0; $i < count($lta); $i++) {
	if(isset($_GET['type'.$i])) {
		for($d = 0; $d < 365; $d++) { $datay[$i][$d] = conv($lta[$i][$d],$lta_unit[$i],0,0,2); $days[$d] = date('d M',mkz($d,2009)); }
		$typeset[$i] = true; $cnt++;
		if($cnt == 1) { $title = $lta_descrip[$i]; if($lta_unit[$i]) { $title .= ' / '. $unitconvs[$lta_unit[$i]]; } }
		elseif($cnt > 1) { $title .= ' & '. $lta_descrip[$i]; if($lta_unit[$i]) { $title .= ' / '. $unitconvs[$lta_unit[$i]]; } }
	}
}
if(!$cnt) { $datay[0] = $lta[0]; $typeset[0] = true; $title = $lta_descrip[0]; if($lta_unit[0]) { $title .= ' / '. $unitconvs[$lta_unit[0]]; }  }

// Create the graph. These two calls are always required
$graph = new Graph($dimx,$dimy);
$graph->SetScale('textint');
$graph->SetShadow();

// Adjust the margin a bit to make more room for titles
$graph->SetMargin(40,5,20,20);
$graph->xaxis->SetTickLabels($days);
$graph->xaxis->SetTextTickInterval(365/12);
$graph->xgrid->Show(true,false);
$graph->xgrid->SetColor('lightgray');

// Create a bar pot
for($i = 0; $i < count($lta); $i++) {
	if($typeset[$i]) {
		$bplot[$i] = new LinePlot($datay[$i]);
		$graph->Add($bplot[$i]);
		$bplot[$i]->SetColor($lta_colours[$i]);
		if($cnt > 1) { $bplot[$i]->SetLegend($lta_descrip[$i]); }
	}
}

// Setup the titles
$graph->title->Set('LTA for ' . $title);
//$graph->xaxis->title->Set('Day');

$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

$graph->legend->SetPos(0.5,0.85,'center','top');
$graph->legend->SetLayout(LEGEND_HOR);
$graph->legend->SetFrameWeight(2);
// Display the graph
$graph->Stroke();
?>