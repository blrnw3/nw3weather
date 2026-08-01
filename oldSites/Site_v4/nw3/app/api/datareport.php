<?php

namespace nw3\app\api;

use nw3\app\model\Report;
use nw3\app\model\Detail;

/**
 * Data Report API for specific variable
 *
 * @author Ben
 */
class Datareport {

	private $var;

	function __construct($varname) {
		if(!$varname) {
			$varname = Report::DEFAULT_VARNAME;
		}
		$this->var = Detail::get_instance($varname);
	}

	function meta() {
		return [

		] + $this->var->var;
	}

	function ranks_daily($month=0, $num=null) {
		$data = $this->var->get_record_daily_ranks_for_month($month, $num);
		if($month === 0 || $month === D_month) {
			$data['yest'] = $this->var->get_rank_of_day(Detail::YESTERDAY);
		}
		return $data;
	}

}
