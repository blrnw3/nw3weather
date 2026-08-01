<?php // content="text/plain; charset=utf-8"

use nw3\app\model\Graphbar;
use nw3\app\model\Climate;

$cnt = count($this->data);
if($cnt === 0) {
	die('No data for any of those types.<br />'.
		'Possible values: '. implode(', ', Climate::$order) .'<br />'.
		$this->timer->current_runtime()
	);
}

$graph = new Graphbar([
	'labelsx' => Graphbar::LABELS_DATE_MONTHS,
	'title' => 'LTA for ',
	'footer' => 'Monthly climate averages, 1981-2010',
	'data' => $this->data
], $this->timer);

?>
