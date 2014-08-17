<?php
namespace nw3\app\model;

use nw3\app\core\Db;
use nw3\app\util\Date;
use nw3\app\model\Store;

/**
 * All temp (mean, min, or max) stats n stuff
 */
class Tmean extends Detail {

	const AGG = 'mean';
	const hr24rec = 99;

	function __construct() {
		parent::__construct('tmean');
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
