<?php
ini_set( "max_execution_time", 200);
$fullpath = '/home/nwweathe/public_html/';
require($fullpath.'basics.php');
require($fullpath.'functions.php');

if(isset($_GET['date'])) { $procdate = mkdate(substr($_GET['date'],4,2),substr($_GET['date'],6,2),substr($_GET['date'],0,4)); } else { $procdate = mktime()-24*3600; }
if(isset($_GET['stamp'])) { $stamp = $_GET['stamp']; }

while($procdate < mktime()-(date('H')+1)*60) {

	$custom = customlog(date('Ymd',$procdate));
	print_r($custom[23]);

	$avtmint = $custom[21]['temp'];	$avtmaxt = $custom[22]['temp'];	$avhmint = $custom[21]['hum']; $avhmaxt = $custom[22]['hum'];
	 //Parameters to be written
	$list = array($custom[23]['temp'], $custom[24]['temp'], $custom[25]['temp'],
				$custom[23]['hum'], $custom[24]['hum'], $custom[25]['hum'],
				round($custom[23]['baro']), round($custom[24]['baro']), round($custom[25]['baro']),
				$custom[25]['wind'], '', $custom[29], $custom[25]['wdir'],
				$custom[26], $custom[1], $custom[0], $custom[32],
				$custom[23]['dew'], $custom[24]['dew'], $custom[25]['dew'],
				'', $custom[31], $custom[7], $custom[10], $custom[12], $custom[6], $custom[14], $custom[16], $custom[2], $custom[4],
				'', substr(date('Ymd',$procdate),6,2), substr(date('Ymd',$procdate),4,2), substr(date('Ymd',$procdate),0,4), ' \n'
			);

	$listt = array($avtmint, $avtmaxt, '',
				$avhmint, $avhmaxt, '',
				$custom[21]['baro'], $custom[22]['baro'], '',
				'',	'', $custom[30], '',
				'',	$custom[28], $custom[18], $custom[33],
				$custom[21]['dew'], $custom[22]['dew'], '',
				'', $custom[20], $custom[9], $custom[11], $custom[13], $custom[8], $custom[15], $custom[17], $custom[3], $custom[5],
				'', substr(date('Ymd',$procdate),6,2), substr(date('Ymd',$procdate),4,2), substr(date('Ymd',$procdate),0,4), ' \n'
			);

	//Do the actual writing of data
	$fildat = fopen($fullpath."dat_test" . $stamp . ".txt","a");
	fputcsv($fildat, $list);
	fclose($fildat);
	$fildat = fopen($fullpath."datt_test" . $stamp . ".txt","a");
	fputcsv($fildat, $listt);
	fclose($fildat);
	
	echo date('Ymd',$procdate), '<br />';
	$procdate += 24*3600;
}
?>