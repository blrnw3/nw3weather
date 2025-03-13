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
if (elapsedSeconds > 199 && elapsedSeconds < 3500) { elapsedSeconds = '>200' }
var newdata = '';  if (elapsedSeconds < 5 && elapsedSeconds > -5) { newdata = '  - NEW!'; }
var all = elapsedSeconds + ' s ago' + newdata;
if (elapsedSeconds < -3 || elapsedSeconds > 3500) { all = ''; }
 document.getElementById('elapsedTime').innerHTML = all; t = setTimeout('showElapsedTimea()',1000); 
}
// -->
</script>

<?php if(date("I") == 1): $dst = "BST"; else: $dst = "GMT"; endif; echo $live[29], ':', $live[30], ':', $live[31], ' ', $dst; ?>
<table cellpadding="5">
<tr class="table-top">
<td width="35%"><b>Measure</b></td>
<td width="65%"><b><span id="elapsedTime"><script>
<!--
showElapsedTimea()
//-->
</script> </span></b></td>
</tr><tr>
<td><b>Temp </b></td>
<td><b><?php echo sprintf('%.1f',$live[4]); ?> &deg;C</b> (<?php echo sprintf('%+.1f',$tempchangehour); ?> /hr)</td>
</tr><tr>
<td><b>Humidity</b></td>
<td><b><?php echo $live[5]; ?>%</b> (<?php echo $humchangelasthour; ?> /hr)</td>
</tr><tr>
<td><b>Dew Pt</b></td>
<td><b><?php echo sprintf('%.1f',$dew); ?> &deg;C</b> (<?php echo sprintf('%+.1f',$dewchangelasthour); ?> /hr)</td>
</tr><tr>
<td><b>Pressure</b></td>
<td><b><?php echo round($live[6],0); ?> hPa</b> (<?php echo sprintf('%+.0f',$trend); ?> /hr)</td>
</tr><tr>
<td><b>Wind</b></td>
<td><b><?php echo round($live[1]*1.152,1), ' mph ',$dirlabel, ' </b><br />(Gusting ', round($live[2],1), ')'; ?></td>
</tr><tr>
<td><b>Daily Rain</b></td>
<td><b><?php echo $live[7]; ?> mm</b> (Hour: <?php echo $hourrn; ?>)</td>
</tr><tr>
<td colspan="2"><b>Free Memory: </b><?php if($freememory<0): echo 4000+$freememory; else: echo $freememory; endif; ?> MB</td></tr><tr>
<td colspan="2"><b>10m Wind: </b><?php echo $avtenminutewind; ?> mph - <b>Gust:</b> <?php echo round($live[133]*1.152,1); ?><br />
<b>10-min Rain: </b><?php echo $rainlast10min; ?> mm<?php if($rainlast10min > 0) { echo ' Last:',$timeoflastrainalways; } ?></td>
</tr></table>

</div>
<span style="display:none">
		<a href="http://www.martynhicks.co.uk/weather/topsites/index.php?a=in&amp;u=Timmead">
		<img src="http://www.martynhicks.co.uk/weather/topsites/button.php?u=Timmead" alt="UK - WEATHER STATION TOPSITES - UK" border="0" /></a>
	</span>