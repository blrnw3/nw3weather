<?php
namespace nw3\app\vari;

use nw3\app\model\Variable;


/**
 * All tmean stats n stuff
 */
class Tmean extends Live {

	const AGG = 'mean';

	function __construct() {
		parent::__construct('temp');
		$this->abs_type = Variable::AbsTemp;
	}

	public function live() {
		return [
			$this->main_live(), [
				'val' => $this->now->feel,
				'descrip' => 'Feels Like',
				'type' => Variable::Temperature
			]
		];
	}

	public function record_24hr() {
		$data = ['max' => [], 'min' => []];
		$now = Store::g()->hr24->temp;
//		$data['max'] = ($now <= 54.2) ? [
//			'val' => 54.2,
//			'dt' => 1272805680
//		] : [
//			'val' => $now,
//			'dt' => D_now
//		];
		return $data;
	}

}
?>
