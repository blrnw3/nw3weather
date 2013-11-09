<html>
<?php
$names = array( "Hour", "Minute", "Day", "10-min wind speed / mph", "Max Gust / mph",
		"Wind direction / degrees", "Temperature / C", "Relative humidity / %", "Pressure / hPa", "Dew Point / C",
		"Daily rain total / mm");

$date = 0;
$d = 1;

while( intval($date) < date("Ymd",mktime(0,0,0,9,30,2012)) ) {
	$time = mktime(0,0,0,6,$d,2012);
	$date = date("Ymd",$time);
	if( date("j",$time) == 1 ) {
		$handle = fopen( date("Ym",$time).'hourlog.csv',"w" );
		fwrite($handle, implode(',', $names) . "\r\n");
	}
	$fname = "http://nw3weather.co.uk/logfiles/daily/".$date."log.txt";
	$cnt = 1;
	$num = 0; $isBad = 0;

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
	if($num != 24) {
		$isBad++;
	}

	if( date('j',$time) == date('t',$time) ) { //end of month
		fclose($handle);
		echo "<span style=\"font-weight:120%\">End of " . date('Y m d',$time) . '</span><br />';
	}

	unset($file);
	$d++;
}
echo $isBad, " bad lines";
?>
</html>
