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
	 *
	 * @param string $table name of db table
	 * @param string $conditions raw sql conditions - where, order by, group by etc.
	 * @param array $cols[=null] If present, an array of the field names to select, else all fields (*).
	 */
	function select($table, $conditions, $cols = null) {
		$cols = ($cols === null) ? '*' : implode(',', (array)$cols);
		$q = "SELECT $cols FROM $table $conditions";
		return $this->db->query($q)->fetchAll(PDO::FETCH_ASSOC);
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
