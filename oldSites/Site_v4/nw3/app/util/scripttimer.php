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
		$this->end = microtime(true) + 0.001;
	}

	function executionTime() {
		return Maths::round( $this->end - $this->start, 3 );
	}
	function executionTimeMs() {
		return round( ($this->end - $this->start) * 1000 ) . ' ms';
	}

	function current_runtime() {
		return $this->prettify( microtime(true) - $this->start );
	}

	private function prettify($val) {
		return "Script running for ". round($val * 1000) ."ms";
	}

}

?>
