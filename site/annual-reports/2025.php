<?php if (!defined('NW3_ANNUAL_REPORT')) { http_response_code(404); exit; } ?>
		<h2 id="report-2025">Year 2025 - Warm and very sunny</h2>
<p style="margin: 0.5em;">
<p>
	2025 was the 17th year of records for this weather station. <br />
	In Feb I replaced the temp/hum sensor after it was destroyed by a squirrel that took up residence in the radiation shield.<br />
	In more positive news, I developed a new sun-counting algorithm based on a model trained on thousands of images from the weather cam,
	 which has reduced the manual and unreliable toil of the old method of counting sunshine from the daily webcam timelapse.<br />
</p>
<p>
	Here are my weather highlights from the year:
	<ul>
		<li>Mostly dry spring and summer, led to the fewest rainy days for the year on record. 122 vs an avg of 165</li>
		<li>Very sunny, with a record total and above average Mar through Sep (Mar, Jun, Sep were also monthly records)</li>
		<li>Another warm year, just a hair cooler than the record-breaking 2022</li>
		<li>June was the hottest since area records back to 1910, with a mean of almost 20 C</li>
	</ul>
</p>

	<div style="margin-left: 0.1em">
		<h3>Monthly charts</h3>
		<div id="ryc1" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc1","\/histdata.php?type=tmean&mode=monthly&year=2025&summary_type=0");
//]]></script>
		<div id="ryc2" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc2","\/histdata.php?type=rain&mode=monthly&year=2025&summary_type=0");
//]]></script>
		<div id="ryc3" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc3","\/histdata.php?type=tmaxa&mode=monthly&year=2025&summary_type=0");
//]]></script>
		<div id="ryc4" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc4","\/histdata.php?type=sunhr&mode=monthly&year=2025&summary_type=0");
//]]></script>
		<h3>Daily charts</h3>
		<div style="margin:1em"></div>
		<div id="ryc5" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc5","\/histdata.php?type=tmax&mode=daily&year=2025&month=0");
//]]></script>
		<div id="ryc6" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc6","\/histdata.php?type=tmin&mode=daily&year=2025&month=0");
//]]></script>
		<div id="ryc7" class="wxchart ry-chart ry-chart-tall"></div><script>//<![CDATA[
NW3.histChart("ryc7","\/histdata.php?type=rain&mode=daily&year=2025&month=0");
//]]></script>
		<a href="/charts.php">More charts</a>
	</div>

<h3>Key averages and extremes</h3>
<p>
<dl>
	<dt class="temp">Temperature</dt>
	<dd>Mean <b>12.5 C</b> [+1.1 C from the <abbr title="Long-term climate average">LTA</abbr>].
		Absolute Min and Max: <b>-4.4 C</b> (Jan 11), and <b>34.1 C</b> (Jul 1). <br />
		Air frosts: <b>20</b>. Ice days: <b>0</b>. Days above 30C: <b>11</b>.
	</dd>
	<dt class="rain">Rainfall</dt>
	<dd>Total: <b>578 mm</b> [-6%] across <b>122</b> days (33%) of <abbr title="=0.2mm">recordable rain</abbr>. <br />
		Highest daily (starting at midnight) total: <b>36 mm</b> (Jul 19). <br />
		Wettest Month: <b>89 mm</b> [+50%] (Jan). Most rainy: <b>16 days</b> (Nov). <br />
		Driest: <b>4 mm</b> [-90%] (Mar) on just 2 days, with a total of 5 hours of rainfall  <br />
		Most rain in an hour: <b>~26 mm</b> in a Thunderstorm on <a href="/wxhistday.php?year=2025&month=05&day=12">May 12</a>
	</dd>
	<dt class="sun">Sunshine</dt>
	<dd>Total: <b>1911 hrs</b> [+20] from a possible 4045. (47%) <br />
		Days with more than a minute of sunshine: <b>311</b> days (87%). <br />
		Sunniest month: <b>260 hrs</b> [+34%] (Jun). Dullest: <b>59 hrs</b> [-15%] in Jan.
	</dd>
	<dt class="snow">Winter Events</dt>
	<dd>
		A couple of cm of lying snow on Jan 5th, and a bit of falling snow on Nov 19.
	</dd>
</dl>
</p>
<h3>Website stats</h3>
<p>
	A stable year with no growth. Key data:
	<ul>
		<li>Total sessions: <b>320k</b> (-1% yoy)</li>
		<li>Mean: <b>875</b> per day, Median: <b>727</b></li>
		<li>Max: <b>3.5k</b> on <a href="/wxhistday.php?year=2025&month=07&day=19">19 Jul</a></li>
		<li>Monthly-active users: <b>7-11k</b> </li>
		<li>Number of tweets: <b>12</b> Number of Followers: 290 (-3%) (<a href="https://twitter.com/nw3weather">twitter.com/nw3weather</a>)</li>
		<li>New features: automed sun recording and automated pond temps.</li>
	</ul>
</p>
<p>
<h3>Weather cam timelapse for 2025</h3>
<video width="640" height="480" controls>
  <source src="/cam/timelapse/skycam_yearly_2025.mp4" type="video/mp4" />
Your browser does not support the video tag.
</video>

<br /><a href="timelapsechive.php" title="Webcam timelapse archive"><b>See full timelapse archive</b></a>
</p>
<p>
<a href="/TablesDataMonth.php?vartype=rain" title="Monthly Rain Records">
	These pages are useful for comparing all aspects of the weather across the years
</a>.
</p>
</p>
<span style="color:#555; font-size: 90%">
	Updated: 02 Jan 2026
</span>
