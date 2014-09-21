<script type="text/javascript">
	//<![CDATA[
	function loadVid() {
		var link = '<embed name="hourVid" src="/videolasthour.wmv" autostart="true" loop="false" height="350" width="425">\n\
			<noembed>Sorry, your browser does not support the embedding of multimedia.</noembed></embed>';
		if(cntJS === 0) {
			document.getElementById('hourVid').innerHTML = 'loading...';
			document.getElementById('hourVid').innerHTML = link;
		}
		cntJS++;
	}
	function loadVid24() {
		var link = '<object id="flowplayer" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="350" height="425">\n\
				<param name="flashvars" value=\'config={"key":"#@8d339434b223613a374","clip":"http://icons.wunderground.com/webcamcurrent/t/i/Timmead/1/current.mp4"}\' />\n\
				<embed autostart="false" type="application/x-shockwave-flash" width="425" height="350" src="http://www.wunderground.com/swf/flowplayer.commercial-3.2.7.swf"\n\
					   flashvars=\'config={"key":"#@8d339434b223613a374","clip":"http://icons.wunderground.com/webcamcurrent/t/i/Timmead/1/current.mp4"}\'/>\n\
			</object>';
		document.getElementById('24hourVid').innerHTML = link;
	}

	//]]>
</script>

<h1>Webcam</h1>

<?php $this->viewette('skycam') ?>

A <a href="webcam/skycam" title="Contains skycam only">self-contained version</a> is also available.
</p>

<hr />

<h3>Latest Groundcam Image</h3>
<p>The camera is a Microsoft Lifecam VX-2000 and is looking NNE over Hampstead Heath (see <a href="wx8.php#location" title="About page">map</a>).</p>
<img src="/jpggroundcam.jpg" alt="Latest Groundcam" width="512" height="384" />
<br />
<p>The image updates every 5 minutes, 24 hours a day.</p>

<hr />

<h3>Skycam Timelapses</h3>

Two are available: a higher quality, slower video of the last hour, created hourly at 5 minutes past the hour;
<br /> or a sped-up one for the entire day, updated every 10 minutes.

<?php if(isset($this->dayvid)): ?>
<p><b>NB:</b> There is also a higher quality video of the all-day timelapse, created nightly at 22:05 <?php echo D_dst; ?>.
The latest version is available for download
<a href="/<?php echo $this->dayvid; ?>" title="Most recent full-day extended HQ timelapse">here</a>
</p>
<?php endif; ?>

<table border="0" cellpadding="10">
	<tr>
		<td align="center"><b> Last Hour</b> (daylight only) </td>
		<td align="center"><b> Last 24hrs</b> </td>
	</tr>
	<tr>
		<td id="hourVid" align="center" width="425" onclick="loadVid();">Click to load</td>
		<td id="24hourVid" align="center" width="425" onclick="loadVid24();">Click to load</td>
	</tr>
</table>

<br />
<b>If Last Hour video does not show</b> after click: <a href="/videolasthour.wmv" title="Last Hour Timelapse">
<b>Launch file</b></a> in external media player).
<hr />

<h3>Skycam images from the last 24 hours</h3>
<p>A <a href="highreswebcam.php" title="Full-resolution summary"><b>higher resolution version</b></a> is also available.</p>
<img title="Last 24hrs summary" src="/dailywebcam.jpg" alt="Webcam summary, past 24hrs" />
<br />
<a href="wcarchive.php" title="Webcam summary archive"><b>See full archive</b></a> (starting 01/08/10).
<table width="95%"> <tr> <td align="center">NB: Some a.m. images may be blurred due to condensation.</td></tr>
</table>
