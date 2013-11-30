<?php
namespace nw3\app\util;

/**
 * Miscellaneous library methods
 * @author Ben LR
 */
class Misc {
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


}

?>
