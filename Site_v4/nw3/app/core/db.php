<?php
namespace nw3\app\core;

use Config as Conf;
use \PDO as PDO;
use \PDOException as PDOException;
/**
 * db connection management
 *
 * @author Ben LR
 */
class Db {

	const DATE_FORMAT = 'Y-m-d';

	# Funcs
	const COUNT = 'COUNT';
	const SUM = 'SUM';
	const AVG = 'AVG';
	const MAX = 'MAX';
	const MIN = 'MIN';

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
	 * @param string $conditions raw sql conditions - where, order by, group by etc.
	 * @param array $cols [=null] If present, an array of the field names to select, else all fields (*).
	 * @return array rows, as associative arrays
	 */
	function select($table, $conditions, $cols=null) {
		$cols = ($cols === null) ? '*' : implode(',', (array)$cols);
		$q = "SELECT $cols FROM $table $conditions";
		return $this->db->query($q)->fetchAll(PDO::FETCH_ASSOC);
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

	/* Utilities for (MY)SQL fragments */
	static function dt($timestamp) {
		return "FROM_UNIXTIME($timestamp)";
	}
	static function where($x) {
		return ($x === null) ? "" : "WHERE $x";
	}
	static function btwn($a, $b) {
		return "BETWEEN ($a AND $b)";
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
	static function min($a) {
		return "MIN($a)";
	}
	static function max($a) {
		return "MAX($a)";
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
