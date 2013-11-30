<?php
/**
 * Core install-dependent configuration
 *
 * @author Ben LR
 */
class Config {

	const ROOT = '/home/nwweathe/public_html/';
	const IMG_ROOT = '/static-images/';
	const EXEC_PATH = '/usr/local/bin/php -q /home/nwweathe/public_html/';

	/** Path to the live data text file */
	const LIVE_DATA_PATH = '/home/nwweathe/public_html/clientraw.txt';

//	public static $rareTags = ROOT.'rareTags.php';

	const GRAPH_DIMS_LARGE = ' height="1017" width="850" ';
	const GRAPH_DIMS_SMALL = ' height="619" width="542" ';

	public static $db = array (
		//Basic db settings
		'username' => 'root',
		'password' => '',
		'database' => 'nw3wx_4',
		'host' => '127.0.0.1',
		'port' => '3306',
		//When true, errors are not caught so script execution terminate
		//and the error message (with stack trace) is shown.
		//Good for use in dev/test stage.
		'explosive' => true
	);

	public static $install = array (
		'directory_nesting_level' => 1
	);

}

?>
