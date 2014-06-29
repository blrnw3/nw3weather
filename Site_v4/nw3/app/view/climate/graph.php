<?php // content="text/plain; charset=utf-8"
require $this->jpgraph_root.'jpgraph.php';
require $this->jpgraph_root.'jpgraph_bar.php';

use nw3\app\model\Graph;
use nw3\app\model\Climate;
use nw3\app\util\Date;

$cnt = count($this->data);
if($cnt === 0) {
	die('No data for any of those types.<br />'.
		'Possible values: '. implode(', ', Climate::$order) .'<br />'.
		$this->timer->current_runtime()
	);
}
$is_multi = ($cnt > 1);

$graph = new Graph($this->timer);
$graph->SetScale('textint');

$graph->xaxis->SetTickLabels(Date::$months3);
$graph->SetTickDensity(TICKD_SPARSE);


$bplots = [];
foreach ($this->data as $i => $var) {
	$bplots[] = new BarPlot($var['values']);
	if($is_multi) {
		$bplots[$i]->SetLegend($var['group']['description']);
	}
}
//Must add to graph before setting colours
$graph->Add(new GroupBarPlot($bplots));

$names = [];
foreach ($this->data as $i => $var) {
	$bplots[$i]->SetColor($var['group']['colour']);
	$bplots[$i]->SetFillColor($var['group']['colour']);

	$names[] = $var['group']['description'] .' / '. $var['group']['unit'];
}
$title = 'LTA for '. implode(', ', $names);

if($is_multi) {
	//Multiple yaxes
}

$graph->set_footer('Monthly climate averages, 1981-2010');
$graph->set_title($title);

$graph->Stroke();
?>
