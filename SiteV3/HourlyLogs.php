<?php
include('/home/nwweathe/public_html/basics.php');
require('unit-select.php');
include($root.'functions.php');
echo "<html>";

$Eday = isset($_GET['Eday']) ? intval($_GET['Eday']) : $day_yest;
$Emonth = isset($_GET['Emonth']) ? intval($_GET['Emonth']) : $mon_yest;
$Eyear = isset($_GET['Eyear']) ? intval($_GET['Eyear']) : $yr_yest;
$Fday = isset($_GET['Fday']) ? intval($_GET['Fday']) : 1;
$Fmonth = isset($_GET['Fmonth']) ? intval($_GET['Fmonth']) : $mon_yest;
$Fyear = isset($_GET['Fyear']) ? intval($_GET['Fyear']) : $yr_yest;

$names = array( "Hour", "Minute", "Day", "10-min wind speed / mph", "Max Gust / mph",
		"Wind direction / degrees", "Temperature / C", "Relative humidity / %", "Pressure / hPa", "Dew Point / C",
		"Daily rain total / mm");

$date = 0;
$d = $Fday;

while( intval($date) < date("Ymd",mktime(0,0,0,$Emonth,$Eday,$Eyear)) ) {
	$time = mktime(0,0,0,$Fmonth,$d,$Fyear);
	$date = date("Ymd",$time);
	if( date("j",$time) == 1 ) {
		$handle = fopen( $root."logfiles/monthly/". date("Ym",$time).'hourlog.txt',"w" );
		fwrite($handle, implode(',', $names) . "\r\n");
	}
	$fname = $root."logfiles/daily/".$date."log.txt";
	$cnt = 1;
	$num = 0; $isBad = 0;
	if( file_exists($fname) ) {
		$file = file($fname);
		$len = count($file);
		$interval = $len / 24;


		foreach($file as $line) {
			if( $cnt == floor($interval * ($num + 1)) ) {
				fwrite($handle, trim($line) . "\r\n");
				$num++;
			}
			$cnt++;
		}

		echo "END OF ". $date . " after <b>" . ($cnt-1) . "</b> lines. This length = <b>". $num ."</b><br />";
		if($num != 24) { $isBad++; }

	} else {
		echo "##############NO FILE ". $date . "###################<br />";
	}

	if( date('j',$time) == date('t',$time) ) { //end of month
		fclose($handle);
		echo "<span style=\"font-weight:120%\">End of " . date('F Y',$time) . '</span><br />';
	}

	unset($file);
	$d++;
}

fclose($handle);
echo $isBad, " bad lines";
?>
</html>