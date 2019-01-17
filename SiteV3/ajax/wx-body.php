<?php
$siteRoot = '/var/www/html/';
require_once($siteRoot.'unit-select.php');
require_once($siteRoot.'functions.php');
include_once(ROOT.'RainTags.php');
$mainDataCritical = true; //see mainData.php
require_once ROOT.'mainData.php';

$file = 1;
$ajax = true;
?>

<input id="WDtime" type="hidden" value="<?php echo $unix; ?>" />
<input id="Servertime" type="hidden" value="<?php echo time(); ?>" />

<?php
$liveData = array((float)$temp, (int)$humi, (float)$dewp, (int)$pres, (float)$wind, (float)$rain);
//for flash function in js
echo '<input id="newData" type="hidden" value="'. json_encode($liveData) .'" />';

$headings = array('', 'Measure', 'Current<span id="elapsedTime"> - ? s ago</span>', 'Max/Min', 'Rate', '24hr mean');
$widths = array(0,  6, 15, 24, 21, 20, 14);

$images = array('thermom8', 'humidity', 'dewy', 'pressure2', 'windy', 'rain2');
$measures = array('Temperature', 'Relative Humidity', 'Dew Point', 'Pressure', 'Wind', 'Rainfall');
$convs = array(1, 5, 1, 3, 4, 2);

table('table-main', null, 10);
echo '<tr class="table-head" style="padding:0.4em; text-align:center;"> <td colspan="6">
	<span onclick="resume();"> <abbr title="Click to force refresh"> Data recorded at '.
	date('H:i:s', $unix) . ' '. $dst.'</abbr> </span> <span id="info" onclick="resume();"></span> </td></tr>
	';
tr();
foreach ($headings as $value) {
	td( $value, 'tdmain', next($widths) );
}
tr_end();

$var1Names = array("temp", "humi", "dewp", "pres", "wind", "rain");

$vars2a = $var1Names; $vars2a[5] = 'rate';

$vars2b = $vars3a = $vars3b = $vars4 = array();
foreach($var1Names as $var) {
	 $vars2b[] = $NOW['min'][$var];
	 $vars3a[] = $HR24['changeHr'][$var];
	 $vars3b[] = $HR24['changeDay'][$var];
	 $vars4[] = $HR24['mean'][$var];
}
$vars2b[4] = $NOW['max']['gust']; $vars2b[5] = $NOW['max']['rnhr'];
$vars3a[4] = $HR24['misc']['maxhrgst']; $vars3a[5] = $HR24['misc']['rnrate'];
$vars3b[4] = bft($wind); $vars3b[5] = $HR24['trendRn'][0] - $HR24['trendRn']['10m'];

$arrowThreshs = array( array(0.3, 0.8), array(2,8), array(0.4, 0.9), array(1, 2), array(1, 3), array(0.1, 1) );
$arrowTimes = array( array(15, 30), array(15, 45), array(15, 30), array(60, 120), array(60, 120), array(20, 45) );

$extras2 = array('Max Speed', 'Max Gust', 'Max Rate', 'Max Hourly');
$extras3 = array('Max Hr Gust', '<a href="BeaufortScale.php" title="Beaufort Scale Terms">Bft</a>', 'Rate', 'Last 10 mins');
$extras4 = array( degname($HR24['mean']['wdir']) . " ({$HR24['mean']['wdir']}&deg;)", "(total)" );

for($i = 0; $i < count($measures); $i++) {
	$cond = ($i < 4);
	$arrow = ($i !== 4 && !($i === 5 && $rain == 0)) ?
		arrow($var1Names[$i], $arrowTimes[$i][0], $arrowTimes[$i][1], $arrowThreshs[$i][0], $arrowThreshs[$i][1]) : '';
	$t1 = $cond ? ' at '. $NOW['timeMax'][$var1Names[$i]] : '';
	$t2 = $cond ? ' at '. $NOW['timeMin'][$var1Names[$i]] : '';

	$extra1 = ($i === 4) ? "<b>". degname($wdir) ."</b><br />Gusting to <b>". conv($gustRaw, 4, 1) .'</b>' : '';
	$extra2a = !$cond ? "<b>{$extras2[($i - 4)*2]}</b>: " : '';
	$extra2b = !$cond ? "<b>{$extras2[($i - 4)*2+1]}</b>: " : '';
	$extra3a = !$cond ? "<b>{$extras3[($i - 4)*2]}</b>: " : '';
	$extra3b = !$cond ? "<b>{$extras3[($i - 4)*2+1]}</b>: " : '';
	$extra4 = !$cond ? '<br />'. $extras4[$i - 4] : '';
	$ratehr = $cond ? ' /hr' : '';
	$rate24 = $cond ? ' /24hr' : '';

	tr( "row".colcol($i) );

	td( '<img src="/static-images/'. $images[$i] .'_small.png" alt="icon'.$var1Names[$i].'" width="40" height="40" />', 'tdmain' );
	td( '<b>'. $measures[$i] .'</b>', 'tdmain' );
	td( '<b><span id="var'.$i.'" style="">'. conv($liveData[$i], $convs[$i]) ."</span></b>  &nbsp; ". $arrow . $extra1, 'tdmain' );
	td( $extra2a . conv($NOW['max'][$vars2a[$i]], $convs[$i] + (($i === 5) ? 0.1 : 0)) . $t1 .'<br />'. $extra2b . conv($vars2b[$i], $convs[$i]) . $t2, 'tdmain' );
	td( $extra3a . conv($vars3a[$i], $convs[$i] + 0.1, true, $cond) . $ratehr .'<br />'.
		$extra3b . ( ($i !== 4) ? conv($vars3b[$i], $convs[$i] + (($i < 5) ? 0.1 : 0), true, $cond) : $vars3b[$i] ) . $rate24, 'tdmain' );
	td( conv($vars4[$i], $convs[$i]) . $extra4, 'tdmain' );

	tr_end();
}

table_end();

if( $NOW['min']['temp'] - $NOW['min']['feel'] < $NOW['max']['feel'] - $NOW['max']['temp']) {
	$extremeFeel = $NOW['max']['feel'];
	$extremeFeelmm = 'Max';
} else {
	$extremeFeel = $NOW['min']['feel'];
	$extremeFeelmm = 'Min';
}

$mores = array('',    'Day Temp Range', 'Feels Like', '10-min Av. Wind', 'Hour Rain', 'Month Rain', 'Last Rain');
$moreVals = array( conv($NOW['max']['temp'] - $NOW['min']['temp'], 1.1), conv($feel, 1) ." (Daily $extremeFeelmm: ".conv($extremeFeel, 1).")",
	conv($w10m,4,1) .' '. degname($HR24['trend'][0]['wdir']), conv($HR24['trendRn'][0] - $HR24['trendRn'][1], 2), conv($monthrn, 2), $HR24['misc']['rnlast'] );

table('table-mainFoot');
echo '<tr>';
foreach ($moreVals as $value) {
	echo '<td><b>'. next($mores) ."</b>:<br /> $value" . '</td>';
}
echo '</tr>';
table_end();

function arrow($varName, $trendShort, $trendLong, $v1, $v2) {
	global $HR24;
	$vartrS = $HR24['trend'][0][$varName] - $HR24['trend'][$trendShort][$varName];
	$vartrL = $HR24['trend'][0][$varName] - $HR24['trend'][$trendLong][$varName];

	//small short-term trend, or short/long trends opposite, or long trend too small
	if( abs($vartrS) == 0 || ( ($vartrS > 0) != ($vartrL > 0) ) || abs($vartrL) < $v1 ) {
		$type = 'steady.jpg" height="3" alt="steady" title="trend: steady"';
	} else {
		$dir = ($vartrL > 0) ? 'rising' : 'falling';
		$isSmallTrend = abs($vartrL) >= $v1 && abs($vartrL) <= $v2;
		$size = $isSmallTrend ? '' : ' height="10" width="9"';
		$dirFull = $dir. '.gif" alt="'. $dir .'" title="trend: '. $dir . ($isSmallTrend ? '' : ' rapidly') . '"';
		$type = $dirFull . $size;
	}

	return "<img src=\"/static-images/$type />";
}
?>