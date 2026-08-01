<?php
/**
* Script to get data from a weather API (WorldWeatherOnline), and store it in a database on this web server
* Author: Ben Lee-Rodgers
* Date: Aug 2013
Version 2.0
*/

//FOR TESTING
$debugging = isset($_GET['debugBLR']);
//FOR PRODUCTION
//$debugging = false;
//FOR CRON
if(!$debugging)
	$debugging = ($argv[1] === "blr");

if(!$debugging) {
	//Only allow the cron and debuggers (me) to access the script
	file_put_contents($API_root."APIForbids.txt", date('r ') . $_SERVER['HTTP_USER_AGENT'] . " " . $_SERVER['REMOTE_ADDR'] . " \r\n", FILE_APPEND);
	die("Access Forbidden!");
}

//Allow script to take longer than default 30s
ini_set('max_execution_time', 150);
ini_set('default_socket_timeout', 10);
//date_default_timezone_set("GMT");
if(!$debugging) { error_reporting(E_ERROR | E_PARSE); }
$start = microtime(true);


//Set limits to prevent faulty data from getting through
$limitsH = array(60,100,100,100,1070,10,time()+999);
$limitsL = array(-90,0,0,1,900,0,time());

//simplify the set of possible icons: http://www.worldweatheronline.com/feed/wwoConditionCodes.xml
$iconTypesWWO = array(
		array(113),
		array(116),
		array(119, 122),
		array(176, 263, 266, 281, 284, 293,296,299,302,305,308,311,314,353,356,359),
		array(179, 182, 185, 227, 230, 317,320,323,326,329,332,335,338,350,362,365,368,371,374,377),
		array(248, 260),
		array(200, 386, 389,392,395) );

$varNames = array("Temperature", "Rain", "Wind", "Humidity", "Pressure", "Condition", "Updated");


require('/var/www/html/wxapp/config.php');

//Connect to SQL server
$con = mysql_connect("127.0.0.1",$db_username,$db_password);
if (!$con) {
	die('Connection error: ' . mysql_error());
}

//Debugging and management
$filelog = fopen($API_root."APIfeed.txt","w");
fwrite($filelog, "Opening database at " . date('r') . "\r\n");

//Connect to database
$db = mysql_select_db($db_name, $con);
if (!$db) {
	die('Database error: ' . mysql_error());
}
//Query database
$result = mysql_query("SELECT `Name`, `Latitude`, `Longitude`  FROM $db_table WHERE TRUE");
if (!$result) {
	die ('Query error: ' . mysql_error());
}

//Keep the old app version going
$db_table_old = 'CityWeather';
$resultOld = mysql_query("SELECT `Name` FROM $db_table_old WHERE TRUE");
if (!$resultOld) {
	die ('Query error: ' . mysql_error());
}
$namesOld = array();
while($row = mysql_fetch_row($resultOld)) {
	$namesOld[] = $row[0];
}
file_put_contents("/var/www/html/CP_Solutions/updated.txt", (time()+120));

fwrite($filelog, "Started processing at " . date('r') . "\r\n");
$cnt = 0;
$fatCnt = 0;
$constTime = (time() + 60);

//Produce output from query
while($row = mysql_fetch_assoc($result)) {
	$timeS = microtime(true);
	$APIquery = $row['Latitude'] . "," . $row['Longitude'];
	$cityName = $row['Name'];
	
	$keys = array(4, 7, 1, 5, 6);
	if($cityName == 'Hampstead') { // Special case for my weather station in Hampstead, North London
		$client = file("/var/www/html/clientraw.txt");
		$client[7] = ceil($client[7]);
		$nw3_icon = getIconNum( get_nw3_wxicon(), 51.5, 0 );
		
		$live = explode(" ", $client[0]);
		for($i = 0; $i < count($keys); $i++) {
			$rounding = ($i <= 2) ? 1 : 0;
			$vars[$i] = round($live[$keys[$i] ], $rounding);
		}
		$vars[5] = $nw3_icon;
		$vars[6] = time();
		$writable = ($vars[4] > 99);
	} else {
		$xmlTS = microtime(true);
		$url = "http://api.worldweatheronline.com/premium/v1/weather.ashx?key=".$APIkey."&query=".$APIquery."&format=xml&includeLocation=yes";
		$xml = getXml($url);
		$xmlTE = microtime(true);
		
		if($xml !== false) { //grab the data if available
			$temp = floatval($xml->current_condition->temp_C);
			$rain = floatval($xml->weather->precipMM);
			$wind = floatval($xml->current_condition->windspeedMiles);
			$humi = intval($xml->current_condition->humidity);
			$pres = intval($xml->current_condition->pressure);
			$lat = floatval($xml->nearest_area->latitude);
			$lng = floatval($xml->nearest_area->longitude);
			//Convert from WWO icon to one from the custom simplified set
			$raw = multiSearch( intval($xml->current_condition->weatherCode), $iconTypesWWO);
			//Modify simple icon to account for day/night
			$icon = getIconNum( $raw, $lat, $lng );
			$vars = array( $temp, $rain, $wind, $humi, $pres, $icon, (time() + 60) );
			$writable = true;
		} else {
			$writable = false;
			//Log xml errors for debugging
			file_put_contents($API_root."APIerrors.txt", date("H:i:s d/m/Y") . "\t" . $cityName . "\r\n", FILE_APPEND);
			mysql_query("UPDATE `$db_table` SET `Updated` = ". $constTime ." WHERE `Name` = '".$cityName."'"); //update time anyway
		}
		$xmlTE2 = microtime(true);
	}
	
	if($writable && $vars[3] > 1) { //write new data to the database if valid
		for($i = 0; $i < count($vars); $i++) { //error checking
			if($vars[$i] > $limitsH[$i] || $vars[$i] < $limitsL[$i]) {
				$vars[$i] = -999;
			}
		}

		//Build query
		$query = "";
		for($i = 0; $i < count($varNames); $i++) {
			$query .= "`" . $varNames[$i] . "`=" . $vars[$i] . ", ";
		}

		//Send query to db
		$result3 = mysql_query("UPDATE `$db_table` SET ". substr($query, 0, strlen($query)-2) ." WHERE `Name` = '".$cityName."'");
		if(!$result3) {
			die ('Query error: ' . mysql_error());
		}
		
		//Update old table
		if(array_search($cityName, $namesOld) !== false) {
			mysql_query("UPDATE `$db_table_old` SET ". substr($query, 0, strlen($query)-2) ." WHERE `Name` = '".$cityName."'");
		}

		//Log detailed sub-process times, and the data feed
		$line = str_pad($cityName,15);
		foreach($vars as $v) {
			$line .= $v . ',';
		}
		$xmlTE3 = microtime(true);
		$timeE = microtime(true);
		$line = str_pad($line, 50) . date("H:i:s, d M Y", (int)$v) . " ( " . sprintf("%.1f", ($timeE-$timeS)) . "s [".
			str_pad( round(($xmlTE-$xmlTS)*1000)."ms,", 7 ) . round(($xmlTE3-$xmlTE)*1000)."ms] )";

		fwrite($filelog, $line . "\r\n");
		$cnt++;
		
		if($debugging) {
			echo $APIquery, ' ', $cityName, ' ', $query, '<br />';
		}
	} else {
		if($debugging) {
			print_r($xml);
		}
	}
	$fatCnt++;
	usleep(350000);
}
fwrite($filelog, "Finished processing ".$cnt." cities at " . date('r'));

if($debugging) {
	echo 'A total of '. $fatCnt .' rows were returned';
}

//Finish-up
mysql_close($con);
fclose($filelog);

//Alert app users that new data has arrived
file_put_contents($API_root."updated.txt", time());

//Log script execution time for debugging purposes
$end = microtime(true);
file_put_contents( $API_root."APIprocessTimes.txt", date("H:i:s d/m/Y") . "\t" .
		round($end - $start) . " s $cnt \r\n", FILE_APPEND);


if($cnt < $fatCnt) {
	file_put_contents($API_root."APItimeouts.txt", date("H:i:s d/m/Y") . "\t" . $cnt . " \r\n", FILE_APPEND);
	if($cnt < 10) {
		mail("alerts@nw3weather.co.uk","WxApp Data Problem","Alert! APIgrabber only processed $cnt cities! Check WWO Now!", "From: server");
	}
}

/**
Search for a value in a 2D array and return the outer array index
 * @param number $value value to find
 * @param number[][] $array array to search
 * @return int the index of the array (row) in which the value was found, or 0 on failure
*/
function multiSearch($value, $array) {
	for($i = 0; $i < count($array); $i++) {
		if(array_search($value,$array[$i]) !== false) {
			return $i;
		}
	}
	return 0;
}

/**
Get the day/night status of a location
 * @param double $lat locations' latitude
 * @param double $lng location's longitude
 * @param double $zen location's zenith (fairly constant in Europe, I'm told)
 * @return int the index of the array (row) in which the value was found, or 0 on failure
*/
function isNighttime($lat, $lng, $zen) {
	$now = time();
	$sunrise = date_sunrise($now, SUNFUNCS_RET_TIMESTAMP, $lat, $lng, $zen, 0);
	$sunset = date_sunset($now, SUNFUNCS_RET_TIMESTAMP, $lat, $lng, $zen, 0);
	return !($now < $sunset && $now > $sunrise);
}

/**
Convert from simple icon set to a modified one that handles icons which depend on whether it is day or night,
e.g. clear sky icon in simplified set can be a sun or a moon in modified set.
 * @param int $int simplest icon set number
 * @param double $lat locations' latitude
 * @param double $lng location's longitude
 * @return int the modified icon set number
*/
function getIconNum($int, $lat, $lng) {
	if($int < 2) { //icon is sun/moon dependent so need to check day/night
		return (int) isNighttime($lat, $lng, 95) + ($int * 2);
	} else {
		// isNighttime($lat, $lng, 95);
		return (2 + $int);
	}
}


/**
Get an XML document over http with a 2s timeout
 * @param string $url
 * @return boolean true on success, false otherwise
 * @author http://stackoverflow.com/questions/4867086/timing-out-a-script-portion-and-allowing-the-rest-to-continue
*/
function getXml($url){
	$ch = curl_init($url);
	
	curl_setopt_array($ch, array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 2
	));

	if($xml = curl_exec($ch)){
		return simplexml_load_string($xml);
	}
	else {
		return false;
	}
}

/**
 * Searches a string for a number of terms, returning true if any are contained within
 * @param string $str subject
 * @param mixed $searchTerms array of search terms, or single string
 * @return boolean true if the string contains of the searched-for terms, false otherwise
 */
function strContains($str, $searchTerms) {
	if(is_array($searchTerms)) {
		foreach ($searchTerms as $search) {
			if(strpos($str, $search) !== false) {
				return true;
			}
		}
		return false;
	} else {
		return (strpos($str, $searchTerms) !== false);
	}
}

function get_nw3_wxicon() {
	$METAR = file_get_contents("/var/www/html/METAR.txt");
	echo ($METAR), '<br />';
	$metarRaining = strContains($METAR, array('RA','DZ', 'SH'));
	$foggy = strContains($METAR, array('FG', 'BR'));
	$snowing = strContains($METAR, array('SN','SG'));
	$stormy = strContains($METAR, 'TS');
	//cloud
	$METARcloudTypes = array('OVC', 'BKN', 'SCT', 'FEW');
	foreach ($METARcloudTypes as $cloudSrch) {
		if(strContains($METAR, $cloudSrch)) {
			$cloud = $cloudSrch;
			break;
		}
	}
	
	$icon = 0;
	if($stormy) {
		$icon = 6;
	} elseif($snowing) {
		$icon = 4;
	} elseif($foggy) {
		$icon = 5;
	} elseif($metarRaining) {
		$icon = 3;
	} elseif($cloud == 'OVC' || $cloud == 'BKN') {
		$icon = 2;
	} elseif($cloud == 'SCT' || $cloud == 'FEW') {
		$icon = 1;
	}
	
	return $icon;
}
?>
