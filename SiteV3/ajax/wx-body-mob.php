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

<div id="main-copy">


<?php echo date('H:i:s', $unix) . ' '. $dst; ?>
<table cellpadding="5">
<tr class="table-top">
<td width="25%"><b>Measure</b></td>
<td width="35%"><b>Current<span id="elapsedTime"><script>
</script> </span></b></td>
<td width="40%"><b>Max/Min</b></td>
</tr><tr>
<td><b>Temp </b></td>
<td><b><?php echo sprintf('%.1f',$temp); ?> &deg;C</b> (<?php echo sprintf('%+.1f',$NOW['changeHr']['temp']); ?> /hr)</td>
<td><?php echo sprintf('%.1f',$NOW['max']['temp']), ' &deg;C at ', $NOW['timeMax']['temp']; ?><br/>
<?php echo sprintf('%.1f',$NOW['min']['temp']), ' &deg;C at ', $NOW['timeMin']['temp']; ?></td>
</tr><tr>
<td><b>Humidity</b></td>
<td><b><?php echo $humi; ?>%</b> (<?php echo $NOW['changeHr']['humi']; ?> /hr)</td>
<td><?php echo $NOW['max']['humi']; ?>% at <?php echo $NOW['timeMax']['humi']; ?><br/>
<?php echo $NOW['min']['humi']; ?>% at <?php echo $NOW['timeMin']['humi']; ?></td>
</tr><tr>
<td><b>Dew Point</b></td>
<td><b><?php echo sprintf('%.1f',$dewp); ?> &deg;C</b> (<?php echo sprintf('%+.1f',$NOW['changeHr']['dewp']); ?> /hr)</td>
<td><?php echo sprintf('%.1f',$NOW['max']['dewp']), ' &deg;C at ', $NOW['timeMax']['dewp'], '<br/>', sprintf('%.1f',$NOW['min']['dewp']), ' &deg;C at ', $NOW['timeMin']['dewp']; ?></td>
</tr><tr>
<td><b>Pressure</b></td>
<td><b><?php echo round($pres,0); ?> hPa</b> (<?php echo sprintf('%+.0f',$NOW['changeHr']['pres']); ?> /hr)</td>
<td><?php echo round($NOW['max']['pres'],0); ?> hPa at <?php echo $NOW['timeMax']['pres']; ?><br/>
<?php echo round($NOW['min']['pres'],0); ?> hPa at <?php echo $NOW['timeMin']['pres']; ?></td>
</tr><tr>
<td><b>Wind</b></td>
<td><b><?php echo sprintf('%.1f',$wind), ' mph ',degname($wdir), ' </b><br />(Gusting ', sprintf('%.1f',$gustRaw), ' mph)'; ?></td>
<td><b>Max hour gust:</b> <?php echo sprintf('%.1f',$HR24['misc']['maxhrgst']), ' mph'; ?><br/><b>Max day gust:</b> <?php echo $NOW['max']['gust']; ?> mph</td>
</tr><tr>
<td><b>Daily Rain</b></td>
<td><b><?php echo $rain, ' mm</b><br />(10min: ', ($HR24['trendRn'][0] - $HR24['trendRn']['10m']); ?> mm)</td>
<td><b>Last Hour:</b> <?php echo ($HR24['trendRn'][0] - $HR24['trendRn'][1]); ?> mm<br/><b>Month Rain:</b> <?php echo $monthrn; ?> mm</td>
</tr><tr>
<td><b>Other</b></td>
<td><b>Feels like: </b><?php echo sprintf('%.1f',$feel); ?> &deg;C</td>
<td><b>Last Rain: </b><?php echo $HR24['misc']['rnlast']; ?></td>
</tr></table>

</div>