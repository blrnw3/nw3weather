<?php
date_default_timezone_set('Europe/London');

const ROOT = '/var/www/html/';
const VID_ROOT = '/mnt/nw3-vol1/html/';
const CAM_ROOT = '/mnt/webcam/html/';
const IMG_ROOT = '/static-images/';
$root = ROOT;

const EXEC_PATH = '/usr/bin/php -q /var/www/html/';

/** Path to the live data text file */
const LIVE_DATA_PATH = '/var/www/html/clientraw.txt';
const LIVE_DATA_PATH_ALT = '/var/www/html/EXTclientraw2.txt';

$fullpath = $siteRoot = ROOT;

$scriptbeg = microtime(get_as_float);

$rareTags = ROOT.'rareTags.php';

$months = array('Jan', 'Feb', 'March', 'April', 'May', 'June', 'July', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
$months3 = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');

$monthname = date('F');
$date = date('d M Y');
$time = date('H:i');
$dst = date("I") ? "BST" : "GMT";
$dday = date('j'); $dmonth = date('n'); $dyear = date('Y'); $dz = date('z'); $dhr = date('H'); $dt = date('t');
define('DDAY', $dday);
$da = array('j' => $dday, 'n' => $dmonth, 'Y' => $dyear);
$lyNum = 2; //number of leap years since 2009

$lat = 51.556;
$lng = -0.154;
$zenith = 90.2;
$sunrise = date_sunrise(time(), SUNFUNCS_RET_STRING, $lat, $lng, $zenith, date('I'));
$sunset = date_sunset(time(), SUNFUNCS_RET_STRING, $lat, $lng, $zenith, date('I'));

$yr_yest = date('Y',mktime(0,0,0,date('n'),date('j')-1, date('Y')));
$mon_yest = date('n',mktime(0,0,0,date('n'),date('j')-1, date('Y')));
$day_yest = date('j',mktime(0,0,0,date('n'),date('j')-1, date('Y')));
$dz_yest = date('z',mktime(0,0,0,$dmonth,$dday-1,$dyear));

$firstday = (date('j') == 1);
$too_early = $dday < 15;

const checkHTML = 'checked="checked"';
const disableHTML = 'disabled="disabled"';
const selectHTML = 'selected="selected"';

$dnm = array('min','max','mean');
$lhm = array('Low','High','Mean');
$lhmFull = array('Lowest','Highest','Mean');
$mmm = array('min', 'max', 'mean');
$mmmFull = array('Minimum','Maximum','Mean');
$mmmr = array('Min', 'Max', 'Mean', 'Range');
$meanOrTotal = array('Mean', 'Total');

$rankNum = 10;
$rankNumM = 10;
$rankNumCM = 5;

$temp_styr = 2009;
$startYear = 2009;

$pgather = array(7,31,365);

const PHP_INT_MIN = -92233720;

//Season processing
$sc = date('n') % 3 + 1; //Months elapsed during current meteorological season
$snums = array(array(0,1,11), array(2,3,4), array(5,6,7), array(8,9,10));
for($s2 = 0; $s2 < 4; $s2++) { if(in_array($dmonth-1,$snums[$s2])) { $season = $s2+1; } }
$snames = array('Winter', 'Spring', 'Summer', 'Autumn');
$seasonname = $snames[$season-1];

$mailBuffer = array(); //used for delaying email sending
$mailBufferCount = 0;

const HTML_START = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	';

const JQUERY = '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>';

const GRAPH_DIMS_LARGE = ' height="1017" width="850" ';
const GRAPH_DIMS_SMALL = ' height="619" width="542" ';

$sunGrabTime = '0653'; // When to scrape Wonline for EGLL Sun Hrs

/*
 * ###### BUGS #####
 *
 * ######  WD dependendecy reduction  #####
 * DONE: webcam timelapses (v. difficult in PHP, but could do ffmpeg locally
 * wind rose
 *
 * ##### Site switch ######
 * contact casa about clientraw backup
 *
 * ### Postpone to after site-launch ###
 * Complete redesign of software
 * wx16
 * dynamic-image caching, page cache control, wxhistyear, monthly ranks - extend to min/max/sum/count
 * index (random ranking stat), live compare to EGLL&EGWU&StJames
 * grapharchive default to mini-graphs but js-switchable - REWRITE TO BE TOTAL JS
 *
 * crontags - execution strategy change: some tags only need generating daily,
 * and SHOULD be to prevent records being set from incomplete day.
 * Same thing perhaps applies for monthly data in certain cases.
 * Then need to update today fn to allow for 'yesterday' highlighting.
 */
?>