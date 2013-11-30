<?php
namespace nw3\app\core;

/**
 *
 * @author Ben LR
 */
class Logger {

	private $mailBuffer; //used for delaying email sending
	private $mailBufferCount = 0;

	 /**
	 * Logger for http-based requests. Line includes: date-time, custom content, requestURL, ip, userAgent
	 * @param string $txtname name of file in the Logs directory to append
	 * @param string $content the custom logging var
	 */
	static function log_events($txtname, $content = "") {
		if(strlen($content) > 0) {
			$content .= "\t";
		}

		$file_1 = fopen(ROOT.'Logs/'.$txtname, "a");
		fwrite( $file_1, date("H:i:s d/m/Y") . "\t" . $content .
			str_subpad( $_SERVER['REQUEST_URI'], 50 ) .
			str_pad($_SERVER['REMOTE_ADDR'], 16) .
			substr(str_replace("Mozilla/5.0 (","",$_SERVER['HTTP_USER_AGENT']), 0, 100) . "\r\n" );
		fclose($file_1);
	}


	static function quick_log($txtname, $content, $threshold = false) {
		global $root, $mailBuffer, $mailBufferCount;

		file_put_contents( $root.'Logs/'.$txtname, date("H:i:s d/m/Y") .
			"\t" . $content . "\r\n", FILE_APPEND );

		if($threshold !== false && (int)$content > $threshold) {
			$mailBuffer[$mailBufferCount]['file'] = $txtname;
			$mailBuffer[$mailBufferCount]['content'] = $content;
			$mailBufferCount++;
		}
	}
	static function fileLog($txtname, $content, $isCron = false, $threshold = false) {
		global $cfile, $browser, $root, $ip, $mailBuffer, $mailBufferCount;

		$extras = $isCron ? "" :
			str_pad($cfile[count($cfile)-1], 20) . str_pad("User: ". $ip, 20) . '\t'.$browser;

		file_put_contents( $root.'Logs/'.$txtname, date("H:i:s d/m/Y") ."\t". $content .
			$extras . "\r\n", FILE_APPEND );

		if($threshold && (int)$content > $threshold) {
			$mailBuffer[$mailBufferCount]['file'] = $txtname;
			$mailBuffer[$mailBufferCount]['content'] = $content;
			$mailBufferCount++;
		}
	}

	static function server_mail($txtname, $content) {
		mail("alerts@nw3weather.co.uk","Logging threshold breached by " . $txtname, $content, "From: server");
	}

}

?>
