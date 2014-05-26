<?php
namespace nw3\app\core;

use Config as Conf;
use \PDO as PDO;
use \PDOException as PDOException;
use nw3\app\core\Singleton;
use nw3\app\util\Html;
use nw3\app\util\Date;
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

	# Return types
	const SCALAR = 0;
	const SINGLE = 1;

	private $db;
	private $proc;
	private $explosive;

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

		$flags = array(
			PDO::ATTR_PERSISTENT => true,
			PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		);

		try {
			$this->db = new PDO("mysql:host={$host};port={$port};dbname={$db}", $user, $pass, $flags);
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch(PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
		}

		$this->explosive = ($explosive_override === null) ?
			Conf::$db['explosive'] : $explosive_override;
	}

	/**
	 * Issue select query to DB
	 * @param string $table name of db table
	 * @param array $cols [=null] If present, an array of the field names to select, else all fields (*).
	 * @param string $conditions [=''] raw sql conditions - where, order by, group by etc.
	 * @return array rows, as associative arrays
	 */
	function select($table, $cols=null, $conditions='', $type=null) {
		$cols = ($cols === null) ? '*' : implode(',', (array)$cols);
		$q = "SELECT $cols FROM $table $conditions";
//		$this->debug_query($q);
		try {
			if($type === self::SCALAR) {
				return $this->db->query($q)->fetchColumn();
			} elseif($type === self::SINGLE) {
				return $this->db->query($q)->fetch(PDO::FETCH_ASSOC);
			}
			return $this->db->query($q)->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			var_dump($q);
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

		$set_items = array();
		foreach ($update as $key => &$value) {
			$val = is_string($value) ? "$value" : $value;
			$set_items[] = "$key=$val";
		}
		$set = implode(',', $set_items);

		$q = "UPDATE $table SET $set $where";
		$this->db->exec($q);
	}

	private function debug_query($query) {
		$trace = debug_backtrace(FALSE);
		$entries = array();
		$st_frame = 2;
		$en_frame = 4;
		$frame_cnt = min(array(count($trace), $en_frame));
		for ($f = $st_frame; $f < $frame_cnt; $f++) {
			$frame = &$trace[$f];
			$file = substr($frame['file'], -15);
			$class = str_replace('nw3\app\\', '',$frame['class']);
			$func = $frame['function'];
			$line = $frame['line'];

			$entries[] = "$class:{$line}->$func";
		}
		Html::out($query .' -> '. implode(' > ', $entries));
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
		$conds = array_filter((array)$conds);
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
	static function timestamp($col='t') {
		return "UNIX_TIMESTAMP($col)";
	}

	/*
	 * CONVENIENCE WRAPPERS TO PDO CORE FUNCTIONS
	 */

	function prepare($statement) {
		$this->proc = $this->db->prepare($statement);
	}
	function execute($values) {
		if($this->explosive) {
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
