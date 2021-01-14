<?php
/**
 * Core install-dependent configuration
 * The settings in here are for the development environment,
 * the live release config file is obviously private for security reasons
 * @author Ben LR
 */
class Config {

	/** * File root - absolute path of the project directory */
//	const ROOT = 'C:\Users\Ben\Documents\SiteDevelopment\Site_v4\\';
	const ROOT = '/var/www/html/oldSites/Site_v4/';
	/** * PHP root */
	const PHP_ROOT = '/oldSites/Site_v4/';
	/** * URL root */
	const HTML_ROOT = '/oldSites/Site_v4/';
	const EXEC_PATH = '/usr/local/bin/php -q /home/dev';

//	public static $rareTags = ROOT.'rareTags.php';

	const GRAPH_DIMS_LARGE = ' height="1017" width="850" ';
	const GRAPH_DIMS_SMALL = ' height="619" width="542" ';

	public static $db = array (
		//Basic db settings
		'username' => 'nwweathe_blr',
		'password' => 'uclgc02appwx',
		'database' => 'nw3wx_4',
		'host' => '127.0.0.1',
		'port' => '3306',
		//When true, errors are not caught so script execution terminates
		//and the error message (with stack trace) is shown.
		//Good for use in dev/test stage.
		'explosive' => true
	);

	public static $install = array (
		'directory_nesting_level' => 2
	);

}
?>
