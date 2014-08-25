<?php
namespace nw3\app\core;

use Config as Conf;
use \PDO as PDO;
use \PDOException as PDOException;
use nw3\app\core\Singleton;
use nw3\app\core\Query;
use nw3\app\util\Html;
use nw3\app\util\Maths;
/**
 * db connection management
 *
 * @author Ben LR
 */
class Db extends Singleton {

	const DATE_FORMAT = 'Y-m-d';

	# Funcs
	const COUNT = 'COUNT';
	const SUM = 'SUM';
	const AVG = 'AVG';
	const MAX = 'MAX';
	const MIN = 'MIN';
	const DESC = 'DESC';
	const ASC = 'ASC';

	const SEASON_COL = 'FLOOR((MONTH(d) % 12)/3) AS season';

	# Return types
	const SCALAR = 0;
	const SINGLE = 1;

	private $db;
	private $proc;
	private $debug;
	public $query_count = 0;
	public $query_time = 0;

	/**
	 * Create connection
	 * @param bool[=null] $explosive_override pass to override the config value
	 */
	function __construct($explosive_override = null) {
		$user = Conf::$db['username'];
		$pass = Conf::$db['password'];
		$db = Conf::$db['database'];
		$host = Conf::$db['host'];
		$port = Conf::$db['port'];

		$flags = [
			PDO::ATTR_PERSISTENT => true,
			PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		];
		try {
			$this->db = new PDO("mysql:host={$host};port={$port};dbname={$db}", $user, $pass, $flags);
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch(PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
		}

		$this->debug = ($explosive_override === null) ?
			Conf::$db['explosive'] : $explosive_override;
	}

	function query() {
		return new Query(func_get_args());
	}

	/**
	 * Executes a raw SQL statement
	 * ALL raw external queries should come through here
	 * @param string $statement raw SQL
	 * @param boolean $force_debug Override config-level debug
	 * @return PDOQuery the query object
	 */
	public function execute($statement, $force_debug=false) {
		# TODO - log queries using debug_query
		$this->query_count++;
		try {
			$st = microtime(true);
			$exec = $this->db->query($statement);
			$query_time = microtime(true) - $st;
			$this->query_time += $query_time;
			if($force_debug && $this->debug) {
				$this->debug_query($statement, $query_time);
			}
			return $exec;
		} catch (PDOException $e) {
			var_dump($statement);
			var_dump($e->getMessage());
			throw new \Exception('Fatal Error.');
		}
	}

	/**
	 * Issue update command to DB.
	 * @param string $table table name
	 * @param array $update key-value pairs of cols/vals to update
	 * @param string $conds 'where' conditions
	 * @return int number of rows affected
	 */
	function update($table, $update, $conds=null) {
		$where = ($conds === null) ? "" : " WHERE $conds";

		$set_items = [];
		foreach ($update as $key => &$value) {
			$val = is_string($value) ? "$value" : $value;
			$set_items[] = "$key=$val";
		}
		$set = implode(',', $set_items);

		$q = "UPDATE $table SET $set $where";
		$this->db->exec($q);
	}

	private function debug_query($query, $query_time) {
		$query_time = Maths::round($query_time * 1000, 1);
		$trace = debug_backtrace(FALSE);
		$entries = [];
		$st_frame = 4;
		$en_frame = 5;
		$frame_cnt = min([count($trace), $en_frame]);
		for ($f = $st_frame; $f < $frame_cnt; $f++) {
			$frame = &$trace[$f];
			$file = substr($frame['file'], -15);
			$class = str_replace('nw3\app\\', '',$frame['class']);
			$func = $frame['function'];
			$line = $frame['line'];

			$entries[] = "$class:{$line}->$func";
		}
		Html::raw($query_time .': '. $query .' -> '. implode(' > ', $entries));
	}

	/* Utilities for (MY)SQL fragments */
	static function dt_stamp($timestamp) {
		return "FROM_UNIXTIME($timestamp)";
	}
	static function dt($timestamp) {
		$dt = date(self::DATE_FORMAT, $timestamp);
		return "'$dt'";
	}
	static function where($x) {
		return ($x === null) ? "" : "WHERE $x";
	}
	static function btwn($a, $b, $col, $escape=false) {
		return $escape ? "$col BETWEEN '$a' AND '$b'" : "$col BETWEEN $a AND $b";
	}
	static function and_($conds) {
		$conds = array_merge(array_filter((array)$conds));
		switch (count($conds)) {
			case 0:
				return null;
			case 1:
				return $conds[0];
			default:
				$all = implode(' AND ', $conds);
				return "($all)";
		}
	}
	static function or_($a, $b) {
		return "($a OR $b)";
	}
	static function sum($a) {
		return "SUM($a)";
	}
	static function count($a) {
		return "COUNT($a)";
	}
	static function avg($a) {
		return "AVG($a)";
	}
	static function agg($a, $type) {
		return "$type($a)";
	}
	static function min($a) {
		return "MIN($a)";
	}
	static function null_($a) {
		return "$a IS NULL";
	}
	static function not_null($a) {
		return "$a IS NOT NULL";
	}


	static function timestamp($col='t', $alias=true) {
		$col = "UNIX_TIMESTAMP($col)";
		return $alias ? new Alias($col, 'dt') : $col;
	}
	static function time_field($field) {
		return "DATE_FORMAT(t_$field, '%H:%i') AS t";
	}

	/*
	 * CONVENIENCE WRAPPERS TO PDO CORE FUNCTIONS
	 */

	function prepare($statement) {
		$this->proc = $this->db->prepare($statement);
	}
	function execute_proc($values) {
		if($this->debug) {
			$this->proc->execute($values);
		} else {
			try {
				$this->proc->execute($values);
				return true;
			} catch(PDOException $e) {
				echo $values[0] .": ". $e->getMessage() ."<br />";
				return false;
			}
		}
	}
}
?>
