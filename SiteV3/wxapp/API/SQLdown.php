<?php
//Just get the current server time
if( isset($_GET['time']) ) {
	exit( (string)time() );
}


require('../config.php');

//Connect to server
$con = mysql_connect("localhost",$db_username,$db_password);
if (!$con) {
	// die('Connection error: ' . mysql_error());
	die('Fatal Error');
}

//read and process web query
$query = isset($_GET['city']) ? $_GET['city'] : 'London';
$cities = explode(',', mysql_real_escape_string($query));
$sql = "'". $cities[0] . "'";
for($i = 1; $i < count($cities); $i++) {
	$sql .= " OR `Name` = '" . $cities[$i] . "'";
}

//Connect to database
$db = mysql_select_db($db_name, $con);
if (!$db) {
	// die('Database error: ' . mysql_error());
	die('Fatal Error');
}

//Query database
$query1 = "SELECT * FROM `$db_table` WHERE `Name` = $sql";
// echo $query1. '\n\n\n';
$result = mysql_query($query1);
if (!$result) {
	// die ('Query error: ' . mysql_error());
	die('Fatal Error');
}

//Produce output from query
$c = 0;
$tstamp = time();
while($row = mysql_fetch_array($result, MYSQL_NUM)) {
	$cnt = 0;
	foreach($row as $variable) {
		if($cnt == 0 || $cnt >= 4 && $cnt <= 10) { //skip redundant fields (needed elsewhere but not here)
			echo $variable, ',';
		}
  		$cnt++;
	}
	echo "<br />";
	$accesses = $row[count($row)-3] + 1;
	$lol = mysql_query("UPDATE `$db_table` SET Accesses = $accesses, LastGet = $tstamp WHERE `Name` = '". $row[0]."'");
	mysql_query("UPDATE `$db_table` SET LastGet = $tstamp WHERE `Name` = '". $row[0]."'");
	if (isset($_GET['debugBLR'])) {
		//echo "UPDATE `CityWeather` SET Accesses = $accesses WHERE `Name` = '". $row[0]."'";
		//die ('Query error: ' . mysql_error());
		
	}
	$c++;
}

mysql_close($con);
?>
