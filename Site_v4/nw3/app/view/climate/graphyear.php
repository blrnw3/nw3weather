<?php // content="text/plain; charset=utf-8"
require $this->jpgraph_root.'jpgraph_line.php';
require $this->jpgraph_root.'jpgraph_date.php';
require $this->jpgraph_root.'jpgraph_utils.inc.php';

use nw3\app\model\Graph;
use nw3\app\model\Climate;
use nw3\app\util\Date;

$cnt = count($this->data);
if($cnt === 0) {
	die('No data for any of those types.<br />'.
		'Possible values: '. implode(', ', Climate::$order_daily) .'<br />'.
		$this->timer->current_runtime()
	);
}
$is_multi = ($cnt > 1);

$x_labels = Climate::get_timestamps_annual();
// Now get labels at the start of each month
$dateUtils = new DateScaleUtils();
list($tickPositions, $minTickPositions) = $dateUtils->GetTicks($x_labels);

// We add some grace to the end of the X-axis scale so that the first and last
// data point isn't exactly at the very end or beginning of the scale
$grace = 0;
$xmin = $x_labels[0]-$grace;
$xmax = $x_labels[count($x_labels)-1]+$grace;

$graph = new \Graph();
$graph->SetScale('intint',0,0, $xmin, $xmax);

//$graph->xaxis->SetTickLabels(Date::$months3);
//$graph->SetTickDensity(TICKD_DENSE);

// Make sure that the X-axis is always at the bottom of the scale
// (By default the X-axis is alwys positioned at Y=0 so if the scale
// doesn't happen to include 0 the axis will not be shown)
$graph->xaxis->SetPos('min');
// Now set the tic positions
$graph->xaxis->SetTickPositions($tickPositions, $minTickPositions);
// The labels should be formatted at dates with "Year-month"
$graph->xaxis->SetLabelFormatString('M',true);
// Add an X-grid
$graph->xgrid->Show();

// Adjust the start time for an "even" 5 minute, i.e. 5,10,15,20,25, ...
//$graph->xaxis->scale->SetTimeAlign(MINADJ_5);
//// Force labels to only be displayed every 5 minutes
//$graph->xaxis->scale->ticks->Set(86400*31);
//// Use hour:minute format for the labels
//$graph->xaxis->scale->SetDateFormat('d M');


$lplots = [];
$names = [];
foreach ($this->data as $i => $var) {
	$lplots[$i] = new LinePlot($var['values'], $x_labels);
	$graph->Add($lplots[$i]);
	$lplots[$i]->SetColor($var['group']['colour']);
	if($is_multi) {
		$lplots[$i]->SetLegend($var['group']['description']);
	}
	$names[] = $var['group']['description'] .' / '. $var['group']['unit'];
}

$title = 'LTA for '. implode(', ', $names);

if($is_multi) {
	//Multiple yaxes
}

$graph->set_footer('Daily climate averages, 1981-2010');
$graph->set_title($title);

$graph->Stroke();
?>
