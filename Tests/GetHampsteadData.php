<?php

$date = 0; $m = 1;
while( intval($date) < date("ym") ) {
	$date = date("ym",mktime(0,0,0,$m,1,2008));
	$handle = fopen("http://www.climate-uk.com/monthly/".$date.".htm", "r");
	$cnt = 0;
	$here = -99;
	if($handle) {
		while(!feof($handle)) {
			$cnt++;
			$line = fgets($handle). "<br />";
			if(strpos($line, "Hampstead<")) {
				$here = $cnt;
			}
			if($cnt == $here + 2) {
				echo $line;
				$data[$m][1] = $line;
			} elseif($cnt == $here + 6) {
				echo $line;
				$data[$m][0] = $line;
				break;
			}
		}
		// 	echo $cnt;
		fclose($handle);
	} else {
		echo 'Fail!!!!! at ' .$date;
		break;
	}
	echo "<br />------End of ".$date."--------<br />";

	$m++;
}

echo "<table>";
foreach($data as $i) {
	echo "<tr>";
	foreach($i as $j) {
		echo str_replace("<br />", "", $j);
	}
	echo "</tr>";
}
echo "</table>";
// print_r($data);
?>