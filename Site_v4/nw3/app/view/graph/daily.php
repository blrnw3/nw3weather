<?php // content="text/plain; charset=utf-8"

use nw3\app\model\Graphbar;
$format = 'D d M';

$min_dt = $this->data['labels'][0]->format($format);
$max_dt = $this->data['labels'][count($this->data['labels'])-1]->format($format);
$labels = [];
foreach ($this->data['labels'] as &$label) {
	$labels[] = $label->format('d');
}

$graph = new Graphbar([
	'labelsx' => $labels,
	'title' => 'Last 31-days daily ',
	'footer' => "$min_dt - $max_dt",
	'data' => $this->data['data']
], $this->timer);

?>
