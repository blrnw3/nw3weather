<?php
namespace nw3\app\helper;

use nw3\app\core\Db;
/**
 * Main helper funcs
 *
 * @author Ben
 */
class Main {

	static function db_stats() {
		if(Db::is_set()) {
			$db = Db::g();
			$query_time = round($db->query_time * 1000);
			return "$db->query_count executed in $query_time ms";
		}
		return 'None';
	}
}
