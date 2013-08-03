<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php $file = 1222;
	
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Weather Display Logfile missed-line finder</title>

	<?php require('chead.php'); ?>
	<?php include('ggltrack.php') ?>
</head>

<body>
	<!-- ##### Header ##### -->
	<?php require('header.php'); ?>

	<!-- ##### Left Sidebar ##### -->
	<?php require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->

	<div id="main">

	<p><b> Upload must be a WD-created CSV logfile for the ouput to be correct. <br />
		Max file size is 4.76 MB</b>

	<form action="" method="post" enctype="multipart/form-data">
	<input type="hidden" name="MAX_FILE_SIZE" value="5000000" />
	<label for="file">CSV logfile:</label>
	<input type="file" size="45" name="file" id="file" />
	<br />
	<input type="submit" name="submit" value="Submit file" />
	</form>
	<br />
	<br />
	<b>Alternative form for specifying URL of csv:</b> (e.g. http://nw3weather.co.uk/102012lgcsv.csv)<br />
	<form action="" method="post">
	<label for="url">CSV URL:</label>
	<input type="text" size="50" name="url" />
	<input type="submit" name="submit" value="Submit URL" />
	</form>
	<br />
	<br />

<?php
ini_set( "upload_max_filesize", 5000000);

if($_FILES["file"]["error"] > 0) {
	echo "Error: " . $_FILES["file"]["error"] . "<br />";
}
elseif( strpos( strtolower($_FILES["file"]["name"]), '.csv' ) > 0 && $_FILES["file"]["size"] < 5000000) {
	logneaten($_FILES["file"]["tmp_name"]);
}
elseif( isset($_POST['url']) ) {
	logneaten($_POST['url']);
}
else {
	echo 'Awaiting valid input file';
}

function logneaten($fileopen) {
	global $root; //Document root
	$cnt = 0;
	// $filelog = fopen($root.'fulllog_test.txt',"w"); //Output file
	$filcust = file($fileopen); //Input logfile
	$len = count($filcust);

	if($len < 200) {
		echo '<b>Faulty link or bad file!</b>';
		exit;
	}

	$min = 4; $hr = 3; //column number of min and hr labels in logfile (starting at col. 0)
	$day = 0; //column number of day label in logfile (starting at col. 0)

	for($i = 0; $i < $len; $i++) {
		$custl[$i] = explode(',', $filcust[$i]);
		$fields = count($custl[0]);
		for($t = 0; $t < $fields; $t++) {
			$custl[$i][$t] = round($custl[$i][$t],1);
		}
	}

	$linewrite[0] = implode(',', $custl[0]);

	for($i = 1; $i < $len; $i++) {
		// print_r($custl[$i]); echo ' on line '.$i.'<br />';
		$diff = ( mktime($custl[$i][$hr], $custl[$i][$min], 0) - mktime($custl[$i-1][$hr], $custl[$i-1][$min], 0) ) / 60;
		if( $diff > 1 && $diff < 100 ) {
			for($j = 1; $j < $diff; $j++) {
				$linewrite[$i+$j-1+$cnt] = 'xxxxxxxxxxxxxxx   !!!!!!!!!!MISSING LINE!!!!!!!!!!    xxxxxxxxxxxxxxx';
				$missedline[$custl[$i][$day]][$i] = date( 'H:i', mktime($custl[$i][$hr], $custl[$i][$min] - 1, 0) );
				$cnt++;
			}
		}
		$linewrite[$i+$cnt] = implode(',', $custl[$i]);
	}

	echo "Total number of missing lines: " . $cnt. " out of a possible ".$len.
		percent($cnt, $len, 2) . "<br />";
	for($i = 1; $i <= $custl[$len-1][$day]; $i++) {
		if(is_array($missedline[$i])) {
			echo "Missing times on day ".$i.": " . implode(', ',$missedline[$i]) . "<br />";
		} else {
			echo "Missing times on day ".$i.": None" . "<br />
				";
		}
	}

	// for($i = 1; $i < count($linewrite); $i++) {
		// fwrite($filelog, $linewrite[$i]."\r\n");
	// }

	// fclose($filelog);
}
?>
</div>

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>
 </body>
</html>