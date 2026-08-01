<?php
namespace nw3\app\api;

use nw3\app\model\Detail;
/**
 * All Dew Point stats n stuff
 */
class Dewpoint extends Datadetail {

	function __construct() {
		parent::__construct([
			'dmin',
			'dmax',
			'dmean'
		]);
	}

	public function ranks_daily_curr_month() {
		return $this->get_ranks([
			self::ALL => [
				[Detail::MONTHLY, Detail::RECORD_M, MINMAX],
			]
		]);
	}

}
?>
