<?php // content="text/plain; charset=utf-8"
include('/home/nwweathe/public_html/basics.php');
require_once (ROOT.'jpgraph/src/jpgraph.php');
require_once (ROOT.'jpgraph/src/jpgraph_windrose.php');
//include($root.'unit-select.php');
//include($root.'functions.php');
//$autoscale = true;
//include($root.'graphdaygen.php');

$data = array(
    0 => array(1,1,2.5,4),
    1 => array(3,4,1,4),
    'wsw' => array(1,5,5,3),
    'N' => array(2,7,5,4,2),
    15 => array(2,7,12));

// First create a new windrose graph with a title
$graph = new WindroseGraph(400, 400);

// Setup title
$graph->title->Set('Windrose');
$graph->title->SetFont(FF_VERDANA,FS_BOLD,12);
$graph->title->SetColor('navy');

// Create the windrose plot.
$wp = new WindrosePlot($data);
$wp->SetRadialGridStyle('solid');
$graph->Add($wp);

// Send the graph to the browser
$graph->Stroke();
?>
