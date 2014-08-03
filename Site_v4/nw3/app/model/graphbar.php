<?php
namespace nw3\app\model;

/**
 * jpgraph
 *
 * @author Ben LR
 */
class Graphbar extends Graph {

	function set_data($data) {
		$streams = count($data);
		$is_multi = ($streams > 1);
		$bplots = [];
		foreach ($data as $i => $var) {
			$bplots[] = new \BarPlot($var['values']);
			if($is_multi) {
				$bplots[$i]->SetLegend($var['group']['description']);
			}
		}
		//Must add to graph before setting colours
		$this->Add(new \GroupBarPlot($bplots));

		$names = [];
		foreach ($data as $i => $var) {
			$bplots[$i]->SetColor($var['group']['colour']);
			$bplots[$i]->SetFillColor($var['group']['colour']);

			$names[] = $var['group']['description'] .' / '. $var['group']['unit'];
		}
		$this->title_from_data = implode(', ', $names);
	}

}

?>
