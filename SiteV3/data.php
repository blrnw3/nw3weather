<?php
$t_start = microtime(get_as_float);

$yestNow = dailyData( date('Ymd', time() - 12 * 3600) ); // works for midnight process, and any backup up to midday

$list = array(
	$yestNow['min']['temp'], $yestNow['max']['temp'], $yestNow['mean']['temp'],
	$yestNow['min']['humi'], $yestNow['max']['humi'], $yestNow['mean']['humi'],
	$yestNow['min']['pres'], $yestNow['max']['pres'], $yestNow['mean']['pres'],
	$yestNow['mean']['wind'], $yestNow['max']['wind'], $yestNow['max']['gust'], $yestNow['mean']['wdir'],
	$yestNow['mean']['rain'], $yestNow['max']['rnhr'], $yestNow['max']['rn10'], $yestNow['max']['rate'],
	$yestNow['min']['dewp'], $yestNow['max']['dewp'], $yestNow['mean']['dewp'],
	$yestNow['min']['night'], $yestNow['max']['day'],
	$yestNow['max']['tchange10'], $yestNow['max']['tchangehr'], $yestNow['max']['hchangehr'],
	$yestNow['min']['tchange10'], $yestNow['min']['tchangehr'], $yestNow['min']['hchangehr'],
	$yestNow['max']['w10m'],
	$yestNow['min']['feel'], $yestNow['max']['feel'], $yestNow['mean']['feel'],
	$yestNow['misc']['frosthrs'],
	' \n'
);

$listt = array(
	$yestNow['timeMin']['temp'], $yestNow['timeMax']['temp'], '',
	$yestNow['timeMin']['humi'], $yestNow['timeMax']['humi'], '',
	$yestNow['timeMin']['pres'], $yestNow['timeMax']['pres'], '',
	'', $yestNow['timeMax']['wind'], $yestNow['timeMax']['gust'], '',
	'', $yestNow['timeMax']['rnhr'], $yestNow['timeMax']['rn10'], $yestNow['timeMax']['rate'],
	$yestNow['timeMin']['dewp'], $yestNow['timeMax']['dewp'], '',
	$yestNow['timeMin']['night'], $yestNow['timeMax']['day'],
	$yestNow['timeMax']['tchange10'], $yestNow['timeMax']['tchangehr'], $yestNow['timeMax']['hchangehr'],
	$yestNow['timeMin']['tchange10'], $yestNow['timeMin']['tchangehr'], $yestNow['timeMin']['hchangehr'],
	$yestNow['timeMax']['w10m'],
	$yestNow['timeMin']['feel'], $yestNow['timeMax']['feel'], '',
	'',
	' \n'
);

// night min - handling of min occuring on previous day (21-00)
$nminData = file_get_contents(ROOT."nmin.txt");
$nminSplit = explode(',', $nminData);
$nminyest = (float)$nminSplit[0];
if($nminyest < $list[20]) {
	$list[20] = $nminyest;
	$listt[20] = $nminSplit[1]; //time
}
//write new night (21-00) min
file_put_contents ( ROOT. 'nmin.txt', $yestNow['min']['nightTomoz'] . ',' . $yestNow['timeMin']['nightTomoz'] );

//write wet hrs for easy retrieval when manual data is written
file_put_contents ( ROOT. 'wethrs.txt', $yestNow['misc']['wethrs'] );

//Do the actual writing of data
$fildat = fopen(ROOT."dat" . $yr_yest . ".csv","a");
fputcsv($fildat, $list);
fclose($fildat);

$fildatt = fopen(ROOT."datt" . $yr_yest . ".csv","a");
fputcsv($fildatt, $listt);
fclose($fildatt);

//Set-up for first day of new year
if(date('n') == 1 && date('j') == 1) {
	$fildat = fopen(ROOT."dat" . date('Y') . ".csv","w");
	fputcsv($fildat, $types_original);
	fclose($fildat);
	$fildatt = fopen(ROOT."datt" . date('Y') . ".csv","w");
	fputcsv($fildatt, $types_original);
	fclose($fildatt);
	$fildatm = fopen(ROOT."datm" . date('Y') . ".csv","w");
	fputcsv($fildatm, $types_m_original);
	fclose($fildatm);
}

//backup
copy(ROOT.'dat' . $yr_yest . '.csv', ROOT.'backup/dat' . $yr_yest.'-'.$day_yest . '.csv');
copy(ROOT.'datt' . $yr_yest . '.csv', ROOT.'backup/datt' . $yr_yest.'-'.$day_yest . '.csv');

//Delete old videos
$vid_dtd = $root . date('Ymd', mkdate(date('n'),date('j')-3)) . 'dayvideo.wmv';
if(file_exists($vid_dtd)) { unlink($vid_dtd); }
//delete dailywebcam.gif
$cam_dtd1 = $root . date('Ymd', mkdate(date('n'),date('j')-1)) . 'dailywebcam.gif';
$cam_dtd2 = $root . date('Ymd', mkdate(date('n'),date('j')-1)) . 'dailywebcam2.gif';
if(file_exists($cam_dtd1)) { unlink($cam_dtd1); }
if(file_exists($cam_dtd2)) { unlink($cam_dtd2); }

$t_end = microtime(get_as_float);
$p_time = $t_end - $t_start;

if(date('j') == 1 && date('n') == 1) { //first day of the year
	mkdir(ROOT. date('Y'), 0755);
}

quick_log('data_crontime.txt', $p_time);
?>