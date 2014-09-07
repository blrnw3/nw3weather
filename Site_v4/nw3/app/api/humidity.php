<?php
namespace nw3\app\api;

use nw3\app\model\Detail;
/**
 * All Relative Humidity stats n stuff
 */
class Humidity extends \nw3\app\core\Api {

	function __construct() {
		parent::__construct([
			'hmin',
			'hmax',
			'hmean'
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
