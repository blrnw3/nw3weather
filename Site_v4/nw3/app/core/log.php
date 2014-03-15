<?php
namespace nw3\app\core;

/**
 * Log message
 *
 * @author Ben LR
 */
class Log {

	public $file;
	public $message;

	/**
	 *
	 * @param type $message
	 * @param type $file_name
	 * @param type $is_http_based
	 */
	function __construct($message, $file_name) {
		$this->message = $message;
		$this->file = $file_name .'.txt';
	}

	function append($text, $delimiter = '; ') {
		$this->message .= ($delimiter . $text);
	}

//	function fileLog($txtname, $content, $isCron = false, $threshold = false) {
//		global $cfile, $browser, $root, $ip, $mailBuffer, $mailBufferCount;
//
//		$extras = $isCron ? "" :
//			str_pad($cfile[count($cfile)-1], 20) . str_pad("User: ". $ip, 20) . '\t'.$browser;
//
//		file_put_contents( $root.'Logs/'.$txtname, date("H:i:s d/m/Y") ."\t". $content .
//			$extras . "\r\n", FILE_APPEND );
//
//		if($threshold && (int)$content > $threshold) {
//			$mailBuffer[$mailBufferCount]['file'] = $txtname;
//			$mailBuffer[$mailBufferCount]['content'] = $content;
//			$mailBufferCount++;
//		}
//	}
}

?>
