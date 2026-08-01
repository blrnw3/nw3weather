<?php $path = $_SERVER['DOCUMENT_ROOT'];
	include($path. '/main_tags.php');
	$file = 0.2;
	$client = file($path. '/clientraw.txt');
	$live = explode(" ", $client[0]);
?>

<div id="main-copy">

<script type="text/javascript">
<!--
function showElapsedTimea() {
var startYear = <?php echo $date_year; ?>; var startMonth = <?php echo $date_month - 1; ?>; var startDay = <?php echo $date_day; ?>; var startHour = <?php echo $live[29]; ?>;
var startMinute = <?php echo $live[30]; ?>; var startSecond = <?php echo $live[31]; ?>; 
var startDate = new Date(); startDate.setUTCFullYear(startYear); startDate.setUTCMonth(startMonth); startDate.setUTCDate(startDay); startDate.setUTCHours(startHour);
startDate.setUTCMinutes(startMinute); startDate.setUTCSeconds(startSecond); var rightNow = new Date(); var d = new Date();
var elapsedTime = rightNow.getTime() - startDate.getTime() + <?php if(date("I") == 1) { echo 60*60000; } else { echo 0; } ?>;
var elapsedSeconds = Math.round(elapsedTime / 1000);
if (elapsedSeconds < 0 && elapsedSeconds > -4) { elapsedSeconds = 0 }
if (elapsedSeconds > 99 && elapsedSeconds < 3500) { elapsedSeconds = '>99' }
var newdata = '';  if (elapsedSeconds < 5 && elapsedSeconds > -5) { newdata = '  - NEW!'; }
var all = ' - ' + elapsedSeconds + ' s ago' + newdata;
if (elapsedSeconds < -3 || elapsedSeconds > 3500) { all = ''; }
 document.getElementById('elapsedTime').innerHTML = all; t = setTimeout('showElapsedTimea()',1000); 
}
// -->
</script>

<?php if(date("I") == 1): $dst = "BST"; else: $dst = "GMT"; endif; echo $live[29], ':', $live[30], ':', $live[31], ' ', $dst; ?>
<table cellpadding="5">
<tr class="table-top">
<td width="25%"><b>Measure</b></td>
<td width="35%"><b>Current<span id="elapsedTime"><script>
<!--
showElapsedTimea()
//-->
</script> </span></b></td>
<td width="40%"><b>Max/Min</b></td>
</tr><tr>
<td><b>Temp </b></td>
<td><b><?php echo sprintf('%.1f',$temp); ?> &deg;C</b> (<?php echo sprintf('%+.1f',$tempchangehour); ?> /hr)</td>
<td><?php echo sprintf('%.1f',$maxtemp), ' &deg;C at ', $maxtempt; ?><br/>
<?php echo sprintf('%.1f',$mintemp), ' &deg;C at ', $mintempt; ?></td>
</tr><tr>
<td><b>Humidity</b></td>
<td><b><?php echo $hum; ?>%</b> (<?php echo $humchangelasthour; ?> /hr)</td>
<td><?php echo $highhum; ?>% at <?php echo $highhumt; ?><br/>
<?php echo $lowhum; ?>% at <?php echo $lowhumt; ?></td>
</tr><tr>
<td><b>Dew Point</b></td>
<td><b><?php echo sprintf('%.1f',$dew); ?> &deg;C</b> (<?php echo sprintf('%+.1f',$dewchangelasthour); ?> /hr)</td>
<td><?php echo sprintf('%.1f',$maxdew), ' &deg;C at ', $maxdewt, '<br/>', sprintf('%.1f',$mindew), ' &deg;C at ', $mindewt; ?></td>
</tr><tr>
<td><b>Pressure</b></td>
<td><b><?php echo round($baro,0); ?> hPa</b> (<?php echo sprintf('%+.0f',$trend); ?> /hr)</td>
<td><?php echo round($highbaro,0); ?> hPa at <?php echo $highbarot; ?><br/>
<?php echo round($lowbaro,0); ?> hPa at <?php echo $lowbarot; ?></td>
</tr><tr>
<td><b>Wind</b></td>
<td><b><?php echo $avgspd, ' ',$dirlabel, ' </b><br />(Gusting ', $gstspd, ')'; ?></td>
<td><b>Max hour gust:</b> <?php echo sprintf('%.1f',$maxgsthr), ' mph'; ?><br/><b>Max day gust:</b> <?php echo $maxgst; ?></td>
</tr><tr>
<td><b>Daily Rain</b></td>
<td><b><?php echo $dayrn, '</b> (10min: ', $rainlast10min; ?>)</td>
<td><b>Last Hour:</b> <?php echo $hourrn; ?> mm<br/><b>Month Rain:</b> <?php echo $monthrn; ?></td>
</tr><tr>
<td><b>Other</b></td>
<td><b>Free Memory: </b><?php if($freememory<0): echo 4000+$freememory; else: echo $freememory; endif; ?> MB</td>
<td><b>Last Rain:</b><?php echo $timeoflastrainalways, ' ', $dateoflastrain; ?></td>
</tr></table>

</div>