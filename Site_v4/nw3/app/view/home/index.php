<?php
use nw3\app\util\Html;

$latest = new nw3\app\api\Latest();
$report = $latest->weather_report();
?>

<h1>Hampstead nw3, London - Current Weather</h1>

<div>
<table class="legacy" width="99%" cellpadding="2" cellspacing="0" align="center" border="0" rules="none">
<tr class="rowdark">
<td width="25%" align="center">
	<b><span style="color:#610B0B">Weather Report</span></b>
	<br /><br />
	<?php echo "<b>{$report['conditions']['weather']}</b>; ". Html::tip("Raw METAR: {$report['conditions']['metar']}", $report['conditions']['cloud']); ?>
</td>

<td width="30%" rowspan="3" align="center">
	<b><span title="Clickable!" onclick="camChange();" style="color:#336666">Weathercam</span></b>
	<br /><br />
	<a href="wx11.php">
		<img id="cam" name="refresh" border="0" src="/currcam_small.jpg" title="View enlarged" alt="Web cam" width="236" height="177" />
	</a>
	<br />
	<a href="wx2.php" title="Full webcam image and timelapses">More Webcam</a>
</td>
	<td width="45%" rowspan="3" align="center">
	<?php
		if($imperial) {
			$img1 = 'graphdayA.php?type1=temp&amp;type2=rain&amp;ts=12&amp;x=400&amp;y=160&amp;nofooter';
			$img2 = 'graphdayA.php?type1=hum&amp;type2=dew&amp;ts=12&amp;x=400&amp;y=160';
			$click = '';
		} else {
			$timeID = date('dmYHi');
			$img1 = '/mainGraph1.png?reqid='. $timeID;
			$img2 = '/mainGraph2.png?reqid='. $timeID;
			$click = $metric ? 'title="You can click, but the units will be mph!"' : 'title="Click to change graph variables" ';
		}
		echo '<img '.$click.'id="graph1" src="'.$img1.'&amp;currid='. time().'" alt="Last 12-hours weather" onclick="imgSwap(1);" width="400" height="160" />
			  <img '.$click.'id="graph2" src="'.$img2.'&amp;currid='. time().'" alt="Last 12-hours weather" onclick="imgSwap(2);" width="400" height="160" />';
	?>
	</td>
</tr>

<tr class="rowdark">
	<td>
		<span style="color:rgb(243,242,235)">&nbsp;</span>
	</td>
</tr>
<tr class="rowdark">
	<td align="center">
		<b><span style="color:#6A4EC6">Local Forecast</span></b>
		<br /><br />
		<img src="/static-images/'.<?php echo $report['forecast']['icon'] ?>.'_lg.png" style="background-color:#CCCEEC;" title="<?php echo $report['forecast']['text'] ?>" width="83" height="81" alt="London Forecast icon" />
		<br />
		<a href="wx5.php" title="5-Day Local Forecast and Maps">Full forecast</a>
	</td>
</tr>

<tr class="rowlight">
	<td colspan="3" align="center">
		<div id="nw3_about_summary">
			<b>NW3 Weather</b> is a meteorological observation site located near Hampstead, in North London, UK. <br />
			The site was established with an automatic, server-linked personal weather station in July 2010 and runs continuously.
			<br />Live data is updated at least once a minute, graphs and other data every 5 minutes.
			More info can be found on the <a href="wx8.php" title="Detailed station and website information">About</a> page.
		</div>
	</td>
</tr>
</table>

<h1>Live Weather</h1>

</div>

<div id="livewx">
	<?php // include('ajax/wx-body.php'); ?>
	<noscript>
		<p>
			<b>Warning:</b> Javascript must be enabled for live updates to function
		</p>
	</noscript>
</div>


<span id="pauser" style="color:#3a5;" onclick="pause();">
	Pause live updates
</span>

<h1>Latest Monthly Weather Report</h1>
<?php
# TODO
//$repStamp = mkdate($dmonth-1, 1);
//$repMonth = date('n', $repStamp);
//$repYear = date('Y', $repStamp);
//displayMonthlyReport($repMonth, $repYear);
?>

<p style="margin-top: 2em;">This weather station has been recording data for
	<abbr title="Since 1st Feb 2009"><b> <?php echo $report['station_stats']['days_running'] ?></b></abbr> days
	(<abbr title="Since 18th Jul 2010"><?php echo $report['station_stats']['days_running_nw3']; ?></abbr> at NW3)
</p>
