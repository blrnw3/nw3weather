<?php
namespace nw3\app\util;
/**
 * Deals with timing of script execution
 *
 * @author Ben LR
 */
class ScriptTimer {
	private $start;
	private $end;

	function __construct() {
		$this->start();
	}

	private function start() {
		$this->start = microtime(true);
	}

	function stop() {
		$this->end = microtime(true) + 0.05;
	}

	function executionTime() {
		return Maths::round( $this->end - $this->start, 3 );
	}

	function current_runtime() {
		return $this->prettify( microtime(true) - $this->start );
	}

	private function prettify($val) {
		return "Script running for ". Maths::round($val, 3) ."s";
	}

}

?>
