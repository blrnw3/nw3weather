<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
date_default_timezone_set('Europe/London');

// NB: see end of file for other inclusions and init
require("UtilsAndConsts.php");
require("WxData.php");

const UNIT_UK = 0;
const UNIT_US = 1;
const UNIT_EU = 2;

class Page {
	const JQUERY = '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>';

	private static $cookieLifeSecs = 8640000; // cookie lifespan - 100 days;

	// Constructor args
	public static $title;
	public static $description;
	public static $fileNum;
	// optionals
	public static $isSubFile = false;
	public static $allDataNeeded = false;
	public static $needValcolStyle = false;

	public static $syr;
	public static $smo;
	public static $ip = "";
	public static $browser = "";
	public static $me = false;
	public static $nw3 = false;
	public static $auto = false;
	public static $isBot = false;
	public static $pageName = "";
	public static $units = UNIT_UK;

	// TODO: put somewhere better
	public static $DATA;
	public static $DATM;

	private static $start;
	private static $mailBuffer;
	private static $mailBufferCount;
	private static $styleSheet;

	static function init($opts) {
		self::$start = microtime(true);
		self::$mailBuffer = [];
		self::$mailBufferCount = 0;

		foreach ($opts as $k => $v) {
			self::${$k} = $v;
		}

		self::$ip = filter_input(INPUT_SERVER, "REMOTE_ADDR", FILTER_SANITIZE_STRING);
		self::$browser = filter_input(INPUT_SERVER, "HTTP_USER_AGENT", FILTER_SANITIZE_STRING);
		self::$nw3 = (self::$ip === '217.155.197.157');
		$cf = explode('/', filter_input(INPUT_SERVER, "PHP_SELF", FILTER_SANITIZE_STRING));
		self::$pageName = $cf[count($cf)-1];

		self::start_session();
		self::check_bots();

		// Init other static stuff
		Live::init();
		if (self::$allDataNeeded) {
			self::$DATA = unserialize(file_get_contents(ROOT . 'serialised_dat.txt'));
			self::$DATM = unserialize(file_get_contents(ROOT . 'serialised_datm.txt'));
		}
	}

	public static function Start() {
		//Session track and update dealings
		if(!isset($_SESSION['count'][self::$fileNum])) {
			$_SESSION['count'][self::$fileNum] = 0;
		}
		$_SESSION['count'][self::$fileNum]++;
		// Refresh settings
		$metaRefresh = "";
		$metaRefreshable = in_array(self::$fileNum, array(3,4,10,12,13,14,15)) && !self::$isSubFile;
		if(self::$auto && $metaRefreshable && !self::$isBot) {
			if($_SESSION['count'][self::$fileNum] < 50) {
				$reftime = 302 - ( time() - filemtime(ROOT.'serialised_datNow.txt') );
				if($reftime < 10) { $reftime = 30; }
				$metaRefresh = '<meta http-equiv="refresh" content="'. $reftime .'" />';
			}
		}
		$colorCss = self::$needValcolStyle ? '<link rel="stylesheet" type="text/css" href="/valcolstyle.css" media="screen" title="screen" />' : '';
		$updatedAt = self::getLastUpdateText();
		$navBar = self::navBar();
		$navSettings = self::getUnitsNav();
		$status = self::getStatus();

		$buffered = ob_start("ob_gzhandler");

		ob_start();
		require("JS_Scripts.php");
		$scripts = ob_get_contents();
		ob_end_clean();

		if(!self::$me && !self::$nw3) {
			ob_start();
			require("ggltrack.php");
			$scripts .= ob_get_contents();
			ob_end_clean();
		}
		$scripts .= self::JQUERY;

		$title = self::$title;
		$description = self::$description;
		$styleSheet = self::$styleSheet;

		echo <<<END
<!doctype html>
<html lang="en">
	<head>
		<title>NW3 Weather - $title</title>
		<meta charset="UTF-8" />
		<meta http-equiv="pragma" content="no-cache" />
		<meta name="author" content="Ben Masschelein Rodgers" />
		<meta name="description" content="$description" />
		<meta name="viewport" content="width=420, initial-scale=1.0" />
		<!-- Buffered: $buffered -->
		$metaRefresh
		<link rel="stylesheet" type="text/css" href="/v5/$styleSheet.css?20240412" media="screen" title="screen" />
		$colorCss
		$scripts
	</head>
	<body>
		<div id="background">
			<div id="page">
				<div id="header">
					<div id="banner">
						<div id="banner-main" onclick="location.href='/';">
							<div id="banner-nw3">
								<span class="nw3">nw3</span> weather
							</div>
							<div id="banner-location">
								<div>Hampstead</div>
								<div id="banner-location-comma">,</div>
								<div>London</div>
							</div>
						</div>
						<div id="banner-left"></div>
						<div id="banner-right"></div>
					</div>
					<div id="sub-header">
						<div id="live-wx"></div>
						<div id="last-updated">$updatedAt</div>
					</div>
				</div>
				<div id="nav-wrapper">
				<div id="nav">
					$navBar
					<div id="nav-settings">
						<p>&#x2699;&#xFE0E; Site units</p>
						<div>
							<form method="get" name="SetUnits" action="">
								$navSettings
							</form>
						</div>
					</div>
				</div>
				<div id="main">
					<div id="status">
					$status
					</div>
END;
	}

	public static function End() {
		$year = date("Y");
		echo <<<END
		</div> <!-- main -->
		</div> <!-- nav-wrapper -->
		<div id="footer">
			<div>
				<p><a href="#header">&#x2B06;&#xFE0E; Top</a></p>
				<p><a href="/mob.php" title="Very basic mobile browsing">&#x1F4F1;&#xFE0E; Mobile</a></p>
				<p><a href="/" title="Browse to homepage">&#x1F3E0;&#xFE0E; Home</a></p>
			</div>
			<div>
				<p>&#x2692;&#xFE0E; Built by Ben Masschelein-Rodgers</p>
				<p>&#x1f332;&#xFE0E; Located in Hampstead, London</p>
				<p>&#x2600;&#xFE0E; Sister station: <a href="https://rwcweather.com" target="_blank" title="Redwood City Weather, CA">RWC Weather</a></p>
			</div>
			<div>
				<p><a href="/contact.php" title="E-mail me">&#x1F4E7;&#xFE0E; Contact / Social</a></p>
				<p>&copy; nw3weather 2010-$year</p>
				<p>&#x1F527;&#xFE0E; Site version 5.0</p>
			</div>
		</div>
	</body>
</html>
END;
		ob_end_flush();
	}

	private static function getStatus() {
		$statusMessage = "";
		$isBad = false;
		$html = "";
		if(false) {
			$html .= showStatusDiv($statusMessage, $isBad);
		}
		if(Live::$outage) {
			$extraMessage = "";
			$html .= self::getStatusDiv("<b>". date('H:m d M: ', time() - Live::$diff) . "Weather server outage detected automatically</b>."
				. "<br /> Data is being served from nearby stations. Site admin has been notified. Problem age: "
				. intval(Live::$diff / 3600) . " hours<br />$extraMessage", true);
		}
		return $html;
	}

	private static function getLastUpdateText() {
		$dateTimeStamp = Live::$unix;
		$timestampCrontags = filemtime(ROOT.'RainTags.php');
		$timeStampBest = min($timestampCrontags, $dateTimeStamp); //oldest
		if(self::$fileNum== 20) return 'Last Updated: Jan 2022'; //climate
		if(self::$fileNum== 7) return 'Last Upload: Sep 2017';
		if(self::$fileNum== 8) return 'Last Updated: Mar 2023';
		if(self::$fileNum== 9) return 'Page Last Updated: Mar 2013';
		if(self::$fileNum== 0) return ''; //blank for generic e.g. error pages
		return 'Last Full Update: '. date('d M Y, H:i ', $timeStampBest) . Date::$dst;
	}

	private static function sidebarGroup($items) {
		$html = "";
		foreach($items as $item) {
			$cond = (self::$fileNum== $item["num"]);
			$classes = $cond ? ["curr"] : [];

			if(isset($item["subhead"])) {
				$classes[] = "subhead-item";
				if($item["subhead"] === true) {
				} else {
					$html .= '<p class="subhead">'. $item["subhead"]. '</p>';
				}
			}

			$class = $classes ? ' class="'. implode(" ", $classes) . '"' : '';
			$html .= '<a href="'. $item["page"]. '.php"' . $class . ' title="'. $item["text"]. '">'. $item["title"]. '</a>
			';
		}
		return $html;
	}

	private static function sidebarSubheading($title) {
		return '<div class="nav-heading">'.$title.'</div>
		';
	}

	private static function navBar() {
		$lastPost = Date::mkdate(6,22,2018); //MUST KEEP UPDATED - latest blog post
		$lastAlbum = Date::mkdate(9,6,2017); //MUST KEEP UPDATED - latest album upload

		$newLength = (3 * 3600 * 24);
		$blog = 'Blog'. ( ((time() - $lastPost < $newLength)) ?
			' <sup title="Last post: '. date('jS M Y', $lastPost) . '" style="color:#382">new post</sup>' : '' );
		$photos = 'Photos'. ( ((time() - $lastAlbum < $newLength)) ?
			' <sup title="Last upload: '. date('jS M Y', $lastAlbum) . '" style="color:#382">new album</sup>' : '' );

		$items = [
			"main" => [
				[
					"title" => "Home",
					"text" => "Return to main page",
					"page" => "index",
					"num" => 1,
				],
				[
					"title" => "Webcam",
					"text" => "Live Webcam and Timelapses",
					"page" => "wx2",
					"num" => 2,
				],
				[
					"title" => "Graphs",
					"text" => "Latest Daily and Monthly Graphs &amp; Charts",
					"page" => "wx3",
					"num" => 3,
				],
				[
					"title" => "Records",
					"text" => "Records, Extremes, Trends, and Averages",
					"page" => "wx4",
					"num" => 4,
				],
				[
					"title" => "Forecast",
					"text" => "Local Forecasts and Latest Maps",
					"page" => "wx5",
					"num" => 5,
				],
				[
					"title" => "Astronomy",
					"text" => "Sun and Moon Data",
					"page" => "wx6",
					"num" => 6,
				],
				[
					"title" => $photos,
					"text" => "My Weather Photography",
					"page" => "wx7",
					"num" => 7,
				],
				[
					"title" => "About",
					"text" => "About this Weather Station and Website",
					"page" => "wx8",
					"num" => 8,
				],
			],
			"detail" => [
				[
					"title" => "Rain",
					"text" => "Detailed Rain Data",
					"page" => "wx12",
					"num" => 12,
				],
				[
					"title" => "Temperature",
					"text" => "Detailed Temperature Data",
					"page" => "wx14",
					"num" => 14,
				],
				[
					"title" => "Wind",
					"text" => "Detailed Wind Data",
					"page" => "wx13",
					"num" => 13,
				],
				[
					"title" => "Humidity",
					"text" => "Detailed Humidity Data",
					"page" => "wx10",
					"num" => 10,
				],
				[
					"title" => "Pressure",
					"text" => "Detailed Pressure Data",
					"page" => "wx16",
					"num" => 16,
				],
				[
					"title" => "Climate",
					"text" => "Long-term climate averages",
					"page" => "wxaverages",
					"num" => 20,
				],
				[
					"title" => "Custom Graphs",
					"text" => "Short-scale weather graphs",
					"page" => "graphviewer",
					"num" => 31,
				],
			],
			"historical" => [
				[
					"title" => "Daily",
					"text" => "Tables of daily data by weather variable",
					"page" => "wxdataday",
					"num" => 40,
					"subhead" => "Data Tables",
				],
				[
					"title" => "Monthly",
					"text" => "Tables of monthly data by weather variabl",
					"page" => "TablesDataMonth",
					"num" => 40.1,
					"subhead" => true,
				],
				[
					"title" => "Daily",
					"text" => "Daily ranked data by weather variable",
					"page" => "RankDay",
					"num" => 41,
					"subhead" => "Rankings",
				],
				[
					"title" => "Monthly",
					"text" => "Monthly ranked data by weather variable",
					"page" => "RankMonth",
					"num" => 42,
					"subhead" => true,
				],
				[
					"title" => "Daily",
					"text" => "Daily detail reports",
					"page" => "wxhistday",
					"num" => 85,
					"subhead" => "Reports",
				],
				[
					"title" => "Monthly",
					"text" => "Monthly detail reports",
					"page" => "wxhistmonth",
					"num" => 86,
					"subhead" => true,
				],
				[
					"title" => "Annual",
					"text" => "Annual detail reports",
					"page" => "repyear",
					"num" => 87,
					"subhead" => true,
				],
				[
					"title" => "Charts",
					"text" => "In-depth historical data charts",
					"page" => "charts",
					"num" => 32,
				],
			],
			"other" => [
				[
					"title" => $blog,
					"text" => "Website and weather station blog and news",
					"page" => "news",
					"num" => 96,
				],
				[
					"title" => "System",
					"text" => "System Status and Miscellaneous",
					"page" => "wx15",
					"num" => 15,
				],
				[
					"title" => "External",
					"text" => "My Site on the Web and Useful Weather Links",
					"page" => "wx9",
					"num" => 9,
				],
			]
		];
		$html = self::sidebarSubheading("Main");
		$html .= self::sidebarGroup($items["main"]);
		$html .= self::sidebarSubheading("Detailed Data");
		$html .= self::sidebarGroup($items["detail"]);
		$html .= self::sidebarSubheading("Historical Data");
		$html .= self::sidebarGroup($items["historical"]);
		$html .= self::sidebarSubheading("Other");
		$html .= self::sidebarGroup($items["other"]);
		return $html;
	}

	private static function getUnitsNav() {
		$html = "";
		$html .= '<label><input name="unit" type="radio" value="US" onclick="this.form.submit();"';
		if (self::$units === UNIT_US) {
			$html .= ' checked="checked"';
		}
		$html .= ' />Imperial</label>
			<label><input name="unit" type="radio" value="UK" onclick="this.form.submit();"';
		if (self::$units === UNIT_UK) {
			$html .= ' checked="checked"';
		}
		$html .= ' />UK</label>
			<label><input name="unit" type="radio" value="EU" onclick="this.form.submit();"';
		if (self::$units === UNIT_EU) {
			$html .= ' checked="checked"';
		}
		$html .= ' />Metric</label>
			<noscript><input type="submit" value="Go" /></noscript>';
		return $html;
	}

	/**
	 * Returns a full-width padded divison
	 * @param String $message message to show
	 * @param bool $isWarning [=true] set false to show the info-style instead of the default red warning-style
	 */
	public static function getStatusDiv($message, $isWarning = true) {
		$messageType = $isWarning ? 'warning' : 'info';
		return "<div class='statusBox $messageType'>$message</div>";
	}

	/**
	* Logger for http-based requests. Line includes: date-time, custom content, requestURL, ip, userAgent
	* @param string $txtname name of file in the Logs directory to append
	* @param string $content the custom logging var
	*/
   public static function log_events($txtname, $content = "") {
	   if(strlen($content) > 0) {
		   $content .= "\t";
	   }

	   $fil = fopen(ROOT.'Logs/'.$txtname, "a");
	   fwrite( $fil, date("H:i:s d/m/Y") . "\t" . $content .
		   str_subpad( filter_input(INPUT_SERVER, "REQUEST_URI", FILTER_SANITIZE_URL), 100 ) .
		   str_subpad(filter_input(INPUT_SERVER, "HTTP_REFERER", FILTER_SANITIZE_URL), 120) .
		   str_pad(self::$ip, 16) .
		   substr(str_replace("Mozilla/5.0 (","",self::$browser), 0, 80) .
		   "\r\n" );
	   fclose($fil);
   }


   public static function quick_log($txtname, $content, $threshold = false) {
	   file_put_contents( ROOT.'Logs/'.$txtname, date("H:i:s d/m/Y") .
		   "\t" . $content . "\r\n", FILE_APPEND );

	   if($threshold !== false && (int)$content > $threshold) {
		   $mailBuffer[self::$mailBufferCount]['file'] = $txtname;
		   $mailBuffer[self::$mailBufferCount]['content'] = $content;
		   self::$mailBufferCount++;
	   }
   }
   public static function fileLog($txtname, $content, $isCron = false, $threshold = false) {
	   $extras = $isCron ? "" :
		   str_pad(self::$pageName, 20) . str_pad("User: ". self::$ip, 20) . '\t'.self::$browser;

	   file_put_contents( ROOT.'Logs/'.$txtname, date("H:i:s d/m/Y") ."\t". $content .
		   $extras . "\r\n", FILE_APPEND );

	   if($threshold && (int)$content > $threshold) {
		   $mailBuffer[self::$mailBufferCount]['file'] = $txtname;
		   $mailBuffer[self::$mailBufferCount]['content'] = $content;
		   self::$mailBufferCount++;
	   }
   }

	public static function server_mail($txtname, $content) {
		mail("alerts@nw3weather.co.uk","Logging threshold breached by " . $txtname, $content);
	}

	private static function start_session() {
		// Block excessive curler
		$curlers = array("35.176.125.39", "92.237.11.251", "71.187.230.231");
		if(in_array(self::$ip, $curlers) && strpos(self::$browser, "urllib") !== false) {
			http_response_code(429);
			die("Too many requests. IP temporarily blocked. Please reduce the request rate to under 6 per hour. Email blr@nw3weather.co.uk to appeal. Thank you");
		}
		if (!isset($_SESSION)) {
			session_start();
			if (!array_key_exists("count", $_SESSION)) {
				$_SESSION['count'] = [];
			}
		}
		//CSS setting getter/saver
		if (isset($_GET['css'])) {
			self::$styleSheet = $_GET['css'];
			setcookie("css", self::$styleSheet, time() + self::$cookieLifeSecs);
		} elseif (isset($_COOKIE['css'])) {
			self::$styleSheet = $_COOKIE['css'];
		} else {
			self::$styleSheet = 'mainstyle_v5';
		}

		//Unit setting getter/saver
		if (isset($_GET['unit'])) {
			setcookie("SetUnits", filter_input(INPUT_GET, "unit", FILTER_SANITIZE_STRING), time() + self::$cookieLifeSecs);
		}
		if (isset($_COOKIE['SetUnits'])) {
			if ($_COOKIE['SetUnits'] == 'US') {
				self::$units = UNIT_US;
			}
			if ($_COOKIE['SetUnits'] == 'UK') {
				self::$units = UNIT_UK;
			}
			if ($_COOKIE['SetUnits'] == 'EU') {
				self::$units = UNIT_EU;
			}
		}
		//Auto-update setting getter/saver
		if (isset($_GET['update'])) {
			setcookie("SetUpdate", $_GET['update'] === 'on' ? "on" : "off", time() + self::$cookieLifeSecs);
		}
		if (isset($_COOKIE['SetUpdate']) && $_COOKIE['SetUpdate'] === 'on') {
			self::$auto = true;
		}
		// Me setting getter/saver (stops analytics and provides more debugging)
		if (isset($_GET['blr'])) {
			setcookie("me", true, time() + self::$cookieLifeSecs * 10);
		}
		if (isset($_GET['noblr'])) {
			setcookie("me", false, time() + self::$cookieLifeSecs);
		}
		if($_COOKIE['me'] == true) {
			self::$me = true;
		}
		//Session setters
		if (isset($_GET['year'])) {
			self::$syr = (int)$_GET['year'];
			$_SESSION['year'] = (self::$syr >= 1871 && self::$syr <= Date::$dyear) ? self::$syr : Date::$dyear;
		}
		if (isset($_GET['month'])) {
			self::$smo = (int)$_GET['month'];
			$_SESSION['month'] = (self::$smo >= 0 && self::$smo <= 12) ? self::$smo : 0;
		}
		if (isset($_GET['vartype'])) {
			$_SESSION['vartype'] = $_GET['vartype'];
		}
		if (isset($_GET['rankLimit'])) {
			$_SESSION['rankLimit'] = (int)$_GET['rankLimit'];
		}
		if (isset($_GET['start_year_rep'])) {
			$_SESSION['start_year_rep'] = (int)$_GET['start_year_rep'];
		}
	}

	private static function check_bots() {
		$find_bot = array('bot', 'crawl', 'wise', 'search', 'validator', 'lipperhey', 'spider', 'http', 'java', 'www');
		for($i = 0; $i < count($find_bot); $i++) {
			if( strpos( strtolower(self::$browser), $find_bot[$i] ) !== false ) {
				self::$isBot = true;
				break;
			}
		}
		self::$isBot |= (strlen(self::$browser) === 0);
		if(strpos(self::$browser, 'Ezooms') !== false) {
			die('bad bot');
		}
	}
}

class DataPage extends Page {
	static function buildSlug($key, $val) {
		$form_params = ["vartype" => $GLOBALS["type"], "year" => $GLOBALS['year'], "month" => $GLOBALS['month'],
			"summary_type" => $GLOBALS['GET_SUMMARY_TYPE'], "start_year_rep" => $GLOBALS["startYrReport"]];
		$form_params[$key] = $val;
		$slug = "";
		foreach ($form_params as $k => $v) {
			$slug .= "&$k=$v";
		}
		return $slug;
	}

}

?>