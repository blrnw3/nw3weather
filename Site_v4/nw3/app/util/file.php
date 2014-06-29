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
		$ctx = stream_context_create(['http'=> ['timeout' => $timeout]]);
		return file($url, false, $ctx);
	}

	static function live_data($file, $write = null) {
		$path = self::live_path($file);
		if($write !== null) {
			file_put_contents($path, $write);
		} else {
			return file_get_contents($path);
		}
	}

	static function live_path($filename) {
		return __DIR__.'/../../data/live/'.$filename;
	}


}

?>
