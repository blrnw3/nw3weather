<?php
namespace nw3\app\core;

use nw3\app\util\String;
use nw3\app\util\Http;
use nw3\config\Station;

/**
 * Session and Cookie handling
 */
class Session {

	/** * 100 days */
	const EXPIRY_SHORT = 8640000;
	/** * 10,000 days */
	const EXPIRY_LONG = 100000000000;

	static $browser;
	static $is_bot;

	private static $count_path;

	/**
	 * Starts/loads the PHP session
	 */
	static function initialise() {
		self::$browser = $_SERVER['HTTP_USER_AGENT'];
		self::bot_check();

		if (!isset($_SESSION)) {
			session_start();
			if (count($_SESSION['count']) === 0) {
				$_SESSION['count'] = array();
			}
		}


		//Me setting getter/saver (stops analytics and provides more debugging)
		if($_SERVER['REMOTE_ADDR'] === Station::IP) {
			$me = true;
		} else {
			if (isset($_GET['blr'])) {
				$me = true;
				self::cookify("me", true, true);
			} elseif(isset($_COOKIE['me']) && $_COOKIE['me'] == true) {
				$me = true;
			}
			if (isset($_GET['noblr'])) {
				$me = false;
				self::cookify("me", false, true);
			}
		}
	}

	static function increment($path) {
		$_SESSION['count'][$path]++;
		self::$count_path = $path;
	}
	static function page_count() {
		return $_SESSION['count'][self::$count_path];
	}

	static function cookify($name, $value, $long = false) {
		$expiry = $long ? self::EXPIRY_LONG : self::EXPIRY_SHORT;
		setcookie($name, $value, D_now + $expiry);
	}

	private static function bot_check() {
		$bad_bots = array('Ezooms', 'java');
		for($i = 0; $i < count($bad_bots); $i++) {
			if(String::contains( strtolower(self::$browser), $bad_bots[$i] )) {
				Http::response_code(403);
				die('Illegal bot.');
			}
		}

		$ok_bots = array('bot', 'crawl', 'wise', 'search', 'validator', 'lipperhey', 'spider', 'http', 'www');
		for($i = 0; $i < count($ok_bots); $i++) {
			if( String::contains( strtolower(self::$browser), $ok_bots[$i] )) {
				self::$is_bot = true;
				break;
			}
		}
	 }
}

?>
