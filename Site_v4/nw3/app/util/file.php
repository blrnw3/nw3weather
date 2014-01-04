<?php
namespace nw3\app\util;

/**
 * File access library methods
 * @author Ben LR
 */
class File {
	/**
	 * Safely accesses a url (using a timeout) and gets the file into an array
	 * @param string $url to parse
	 * @param int $timeout [=5] in seconds
	 * @return array of each line, or false on failure
	 */
	static function urlToArray($url, $timeout = 5) {
		$ctx = stream_context_create( array( 'http'=> array('timeout' => $timeout) ) );
		return file($url, false, $ctx);
	}

	static function live_data($file, $write = null) {
		$path = __DIR__.'/../../data/live/'. $file;
		if($write !== null) {
			file_put_contents($path, $write);
		} else {
			return file_get_contents($path);
		}
	}


}

?>
