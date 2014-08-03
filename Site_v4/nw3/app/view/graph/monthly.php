<?php // content="text/plain; charset=utf-8"

use nw3\app\model\Graphbar;

$min_dt = $this->data['labels'][0]->format('M Y');
$max_dt = $this->data['labels'][count($this->data['labels'])-1]->format('M Y');
$labels = [];
foreach ($this->data['labels'] as &$label) {
	$labels[] = $label->format('M');
}

$graph = new Graphbar([
	'labelsx' => $labels,
	'title' => 'Monthly ',
	'footer' => "$min_dt - $max_dt",
	'data' => $this->data['data']
], $this->timer);

?>
