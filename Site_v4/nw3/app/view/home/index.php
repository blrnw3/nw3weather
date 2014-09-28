<?php
use nw3\app\util\Html;
use nw3\app\util\Date;
use nw3\app\core\Units;

$latest = new nw3\app\api\Latest();
$report = $latest->weather_report();
$live_metas = $latest->live_metadata();
$live_vars = ['temp', 'rain', 'wind', 'humi', 'pres', 'dewp'];
$live = $latest->live(false);
$month_report = $latest->monthly_report(Date::mkdate(D_month-1, 1, D_year));
?>

<h1>Current Weather in Hampstead nw3, London</h1>

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
	<a href="webcam/skycam">
		<img id="cam" name="refresh" border="0" src="/currcam_small.jpg" title="View enlarged" alt="Web cam" width="236" height="177" />
	</a>
	<br />
	<a href="webcam" title="Full webcam image and timelapses">More Webcam</a>
</td>
	<td width="45%" rowspan="3" align="center">
	<?php
		if(Units::$is_us) {
			$img1 = 'graphdayA.php?type1=temp&amp;type2=rain&amp;ts=12&amp;x=400&amp;y=160&amp;nofooter';
			$img2 = 'graphdayA.php?type1=hum&amp;type2=dew&amp;ts=12&amp;x=400&amp;y=160';
			$click = '';
		} else {
			$timeID = date('dmYHi');
			$img1 = '/mainGraph1.png?reqid='. $timeID;
			$img2 = '/mainGraph2.png?reqid='. $timeID;
			$click = Units::$is_eu ? 'title="You can click, but the units will be mph!"' : 'title="Click to change graph variables" ';
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
		<img src="<?php echo ASSET_PATH .'img/wx_symbols/'. $report['forecast']['icon'] ?>_lg.png" style="background-color:#CCCEEC;" title="<?php echo $report['forecast']['text'] ?>" width="83" height="81" alt="London Forecast icon" />
		<br />
		<a href="forecast" title="5-Day Local Forecast and Maps">Full forecast</a>
	</td>
</tr>

<tr class="rowlight">
	<td colspan="3" align="center">
		<div id="nw3_about_summary">
			<b>NW3 Weather</b> is a meteorological observation site located near Hampstead, in North London. <br />
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
	<table id="livewx_main">
		<caption>
			Latest data as of <span id="data_recorded_at"><?php echo D_time ?></span> <span id="autopause_info"></span>
		</caption>
		<thead>
			<tr>
				<td></td>
				<td>Measure</td>
				<td>Current - <span id="elapsed_time">?</span> s ago</td>
				<td>Extremes</td>
				<td>Rate</td>
				<td>24hr Mean</td>
			</tr>
		</thead>
		<tbody>
			<?php foreach($live_vars as $live_id): ?>
			<tr>
				<td><img src="<?php echo ASSET_PATH .'img/wx_icons/'. $live_id ?>.png" alt="<?php echo $live_id ?>" width="40" height="40" /></td>
				<td><?php echo $live_metas[$live_id]['name'] ?></td>
				<td id="<?php echo $live_id ?>_now"><?php echo $live[$live_id]['now'] ?></td>
				<td id="<?php echo $live_id ?>_extreme"></td>
				<td id="<?php echo $live_id ?>_rate"></td>
				<td id="<?php echo $live_id ?>_mean"></td>
			</tr>
			<?php endforeach ?>
		</tbody>
	</table>

	<div id="livewx_extra">
		<div>
			<div>Day Temp Range</div>
			<div id="today_temp_range"></div>
		</div>
		<div>
			<div>Feels Like</div>
			<div id="feels_like"></div>
		</div>
		<div>
			<div>10-min Avg Wind</div>
			<div id="10m_wind"></div>
		</div>
		<div>
			<div>Hour Rain</div>
			<div id="hr_rain"></div>
		</div>
		<div>
			<div>Month Rain</div>
			<div id="month_rn"></div>
		</div>
		<div>
			<div>Last Rain</div>
			<div id="last_rn"></div>
		</div>
	</div>

	<noscript>
		<p>
			<b>Warning:</b> Javascript must be enabled to view full data
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
<div id="last_month_report">
	<?php echo $month_report ?>
</div>

<p style="margin-top: 2em;">This weather station has been recording data for
	<abbr title="Since 1st Feb 2009"><b> <?php echo $report['station_stats']['days_running'] ?></b></abbr> days
	(<abbr title="Since 18th Jul 2010"><?php echo $report['station_stats']['days_running_nw3']; ?></abbr> at NW3)
</p>

<?php $live_plus = [
	'response' => $live,
	'exec_stats' => [
		'now' => microtime(true),
		'data_updated' => \nw3\app\model\Store::g()->updated
	]
] ?>
<div id="init_live" style="display:none"><?php echo json_encode($live_plus) ?></div>

<?php $this->js_script('home') ?>
