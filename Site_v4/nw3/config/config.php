<?php
namespace nw3\config;
/**
 * Core configuration
 *
 * @author Ben LR
 */
class Config {

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
}

?>
