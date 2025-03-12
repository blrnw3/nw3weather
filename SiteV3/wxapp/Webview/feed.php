<?php
/**
* Web page aimed at users of the Europe Live Weather Compare App, which hosts a webview of this page.
* Live weather data for cities across Europe is shown in tabular form and is sortable.
* Author: Ben Lee-Rodgers
* Date: Aug 2013
* Version 2.0
*/

header("Content-Type: application/json");

require('../config.php');
require './functions.php';

$currDate = date('D d M Y, H:i', 70 + (int) file_get_contents($API_root ."updated.txt")) .' GMT';

$onlyEuro = (isset($_GET['euroOnly']) && (int)$_GET['euroOnly'] <= 1) ? "WHERE `isEuro` = ".(int)$_GET['euroOnly'] : "";

//Connect to MySQL server
$con = mysql_connect("localhost",$db_username,$db_password);
if (!$con) {
// 	die('Connection error: ' . mysql_error());
	die('Fatal error');
}
//Connect to database
$db = mysql_select_db($db_name, $con);
if (!$db) {
// 	die('Database error: ' . mysql_error());
	die('Fatal error');
}
//Query database
$result = mysql_query("SELECT `Name`,`Country`, `Temperature`,`Rain`,`Wind`,`Humidity`,`Pressure`,`Condition`, `isEuro`
		 FROM `$db_table`
		$onlyEuro
		 ORDER BY `Name` ASC");
if (!$result) {
// 	die ('Query error: ' . mysql_error());
	die('Fatal error');
}

//read and process user input
$query = isset($_GET['cities']) ? $_GET['cities'] : 'Hampstead';
$userCities = explode(',',$query);

//Produce output from SQL query
$cnt = 0;
$data = array();

while($row = mysql_fetch_assoc($result)) {
	$subCnt = 0;
	foreach($row as $col) {
		$data[$subCnt][$cnt] = $col;
		$subCnt++;
	}
	$isUserCity = in_array($data[0][$cnt], $userCities);
	if($isUserCity) {
		$data[$subCnt-1][$cnt] = 2;
	}
   $cnt++;
}

$json = array(
	"data" => $data,
	"date" => $currDate
);
//Store data on the webpage for the JS script to use, source:
echo json_encode($json);


if(!$me) {
	file_put_contents( $WEB_root."visitLog.txt", date("H:i:s d/m/Y") . "\t" .
		$phpload ."\t" .  $_SERVER['REMOTE_ADDR']. ' ' .
		$_SERVER['HTTP_USER_AGENT']	. "\r\n", FILE_APPEND );
}
?>
