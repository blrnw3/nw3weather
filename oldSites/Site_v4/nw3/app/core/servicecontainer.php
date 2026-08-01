<?php
namespace nw3\app\core;
/**
 * Holds core objects for global use
 *
 * @author Ben LR
 */
class Servicecontainer {

	private $db;
	private $logger;

	function __construct() {
	}

	function get($service_name) {
		$service = $this->{$service_name};
		if(isset($service)) {
			return $service;
		}
		//else construct
		switch ($service_name) {
			case 'logger':
				$this->logger = new Logger();
				break;
			case 'db':
				$this->db = new \nw3\app\core\Db();
				break;
			default:
				trigger_error("Invalid service name $service_name", E_USER_ERROR);
		}
		return $this->get($service_name);
	}
}

?>
