<?php
namespace nw3\app\core;

use nw3\app\core\Singleton;
use nw3\app\util\String;

/**
 *
 * @author Ben LR
 */
class Logger extends Singleton {

	private $mailBuffer; //used for delaying email sending
	private $mailBufferCount = 0;

	private $logBuffer = array();
	private $info;

	function queue($filename, $message = '') {
		$this->logBuffer[$filename][] = $message;
	}

	function flush($is_http) {
		$this->info = $is_http ?
			String::fixed_length($_SERVER['REQUEST_URI'], 50) .
			str_pad($_SERVER['REMOTE_ADDR'], 16) .
			String::fixed_length($this->clean_useragent(), 80)
			: '';

		foreach ($this->logBuffer as $filepath => $logs) {
			$file_handle = fopen(__DIR__ ."/../../log/$filepath.txt", 'a');
			foreach ($logs as $log_message) {
				fwrite($file_handle, $this->log($log_message));
			}
			fclose($file_handle);
		}
	}

	function log($message) {
		$date = date('H:i:s d/m/Y', D_now);
		return "$date\t$message {$this->info}\r\n";
	}


	function server_mail($txtname, $content) {
		mail("alerts@nw3weather.co.uk","Logging threshold breached by " . $txtname, $content, "From: server");
	}

	private function clean_useragent() {
		$ua = $_SERVER['HTTP_USER_AGENT'];
		return preg_replace('#Mozilla/[0-9]\.0 \((compatible; )?#', '', $ua);
	}

}

?>
