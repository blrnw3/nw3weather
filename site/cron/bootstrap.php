<?php
/**
 * Shared CLI bootstrap for current-site crons.
 * Replaces the legacy basics.php + functions.php include chain.
 */
if(PHP_SAPI !== 'cli' && empty($GLOBALS['NW3_ALLOW_WEB_BOOTSTRAP'])) {
	http_response_code(403);
	die("CLI only.\n");
}

date_default_timezone_set('Europe/London');

if(!defined('ROOT')) {
	define('ROOT', '/var/www/html/');
}
require_once(ROOT . 'config/paths.php');
require_once(ROOT . 'UtilsAndConsts.php');
require_once(ROOT . 'WxDefinition.php');
require_once(ROOT . 'WxFn.php');
require_once(ROOT . 'Page.php');

if(!defined('PHP_BIN')) {
	define('PHP_BIN', getenv('NW3_PHP_BIN') ? getenv('NW3_PHP_BIN') : PHP_BINARY);
}
if(!defined('EXEC_PATH')) {
	define('EXEC_PATH', escapeshellarg(PHP_BIN) . ' -q ' . ROOT);
}
if(!defined('LIVE_DATA_PATH')) {
	define('LIVE_DATA_PATH', Site::LIVE_DATA_PATH);
}
if(!defined('CAM_ROOT')) {
	define('CAM_ROOT', Site::CAM_ROOT);
}
if(!defined('VID_ROOT')) {
	define('VID_ROOT', Site::VID_ROOT);
}

Date::init();
nw3_ensure_runtime_dirs();

class Cron {
	const DATM_CHECK_TIME = '0603';

	public static function myround($val, $dp = 1) {
		return Util::roundToDp($val, $dp);
	}

	public static function quick_log($txtname, $content, $threshold = false) {
		Page::quick_log($txtname, $content, $threshold);
	}

	/** Bind Live::init() results to the procedural globals cron_main still uses. */
	public static function bindLiveGlobals() {
		$GLOBALS['temp'] = Live::$temp;
		$GLOBALS['humi'] = Live::$humi;
		$GLOBALS['pres'] = Live::$pres;
		$GLOBALS['rain'] = Live::$rain;
		$GLOBALS['wind'] = Live::$wind;
		$GLOBALS['gust'] = Live::$gust;
		$GLOBALS['gustRaw'] = Live::$gustRaw;
		$GLOBALS['w10m'] = Live::$w10m;
		$GLOBALS['wdir'] = Live::$wdir;
		$GLOBALS['dewp'] = Live::$dewp;
		$GLOBALS['feel'] = Live::$feel;
		$GLOBALS['unix'] = Live::$unix;
		$GLOBALS['diff'] = Live::$diff;
		$GLOBALS['OUTAGE'] = Live::$outage;
		$GLOBALS['badCRdata'] = (filesize(Site::LIVE_DATA_PATH) === 0);
		$GLOBALS['NOW'] = Live::$NOW;
		$GLOBALS['HR24'] = Live::$HR24;
	}

	/** Bind Date::init() results to legacy global names used by cron scripts. */
	public static function bindDateGlobals() {
		$GLOBALS['root'] = ROOT;
		$GLOBALS['siteRoot'] = ROOT;
		$GLOBALS['fullpath'] = ROOT;
		$GLOBALS['dday'] = Date::$dday;
		$GLOBALS['dmonth'] = Date::$dmonth;
		$GLOBALS['dyear'] = Date::$dyear;
		$GLOBALS['dz'] = Date::$dz;
		$GLOBALS['dtstamp'] = Date::$dtstamp;
		$GLOBALS['dtstamp_yest'] = Date::$dtstamp_yest;
		$GLOBALS['yr_yest'] = Date::$yr_yest;
		$GLOBALS['mon_yest'] = Date::$mon_yest;
		$GLOBALS['day_yest'] = Date::$day_yest;
		$GLOBALS['firstday'] = Date::$firstday;
		$GLOBALS['sunset'] = Date::$sunset;
		$GLOBALS['sunrise'] = Date::$sunrise;
		$GLOBALS['datmCheckTime'] = self::DATM_CHECK_TIME;
		$GLOBALS['scriptbeg'] = microtime(true);
		$GLOBALS['tstamp'] = date('Hi');
	}
}
