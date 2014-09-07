<?php
namespace nw3\app\vari;

use nw3\app\model\Variable;

/**
 * Daily maximum Gust speed
 */
class Gust extends Max {
	function __construct() {
		$this->days_filter = '> 30';
		parent::__construct('wind');
	}

	protected function assign_descriptions() {
		$this->descrip_max = 'Max Gust Speed';
	}


	public function live() {
		return [[
			'val' => $this->now->{$this->live_var},
			'descrip' => $this->live['name'],
			'type' => $this->live['group']
		  ],[
			'val' => $this->now->maxhrgust,
			'descrip' => 'Max Gust Last Hour',
			'type' => Variable::Wind
			]
		];
	}
}

?>
