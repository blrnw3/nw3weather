<?php
/**
 * Deals with timing of script execution
 *
 * @author Ben LR
 */
class ScriptTimer {
	private $start;
	private $end;

	function __construct() {
		$this->start = 0;
		$this->end = 0;
	}

	function start() {
		$this->start = microtime(true);
	}

	function stop() {
		$this->end = microtime(true);
	}

	function executionTime() {
		return $this->prettify( $this->end - $this->start );
	}

	function runtime() {
		return $this->prettify( microtime(true) - $this->start );
	}

	private function prettify($val) {
		return "Script running for ". round($val, 3) ."s";
	}

}

?>
