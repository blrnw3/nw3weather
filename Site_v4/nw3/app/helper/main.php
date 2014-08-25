<?php
namespace nw3\app\helper;

use nw3\app\core\Db;
use nw3\app\util\Maths;
/**
 * Main helper funcs
 *
 * @author Ben
 */
class Main {

	static function db_stats($exec_time) {
		if(Db::is_set()) {
			$db = Db::g();
			$query_time = round($db->query_time * 1000);
			$frac = round($query_time / $exec_time * 100);
			return "$db->query_count executed in $query_time ms ($frac%)";
		}
		return 'None';
	}

	static function mem_stats() {
		$mem_usage = Maths::round(memory_get_usage() / 1024 / 1024, 1);
		$mem_peak = Maths::round(memory_get_peak_usage() / 1024 / 1024, 1);
		echo "$mem_usage  MB ($mem_peak peak)";
	}
}
