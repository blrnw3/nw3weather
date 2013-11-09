<?php
/**
 * db connection management
 *
 * @author Ben LR
 */
class Db {
	private $db;
	private $proc;
	private $explosive;

	function __construct($explosive_override = null) {
		require '../../../config/config.php';
		$user = Config::$db['username'];
		$pass = Config::$db['password'];
		$db = Config::$db['database'];
		$host = Config::$db['host'];
		$port = Config::$db['port'];

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
			Config::$db['explosive'] : $explosive_override;
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
				echo time() .": ". $e->getMessage() ."<br />";
				return false;
			}
		}
	}


}
?>
