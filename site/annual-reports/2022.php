<?php if (!defined('NW3_ANNUAL_REPORT')) { http_response_code(404); exit; } ?>
	<h2 id="report-2022">Year 2022 - hot and dry summer,  warmest year to date</h2>
<p style="margin: 0.5em;">
<p>
	2022 was the 14th year of records for this weather station.
	For a change, there were no changes to the station or website this year.<br />

</p>
<p>
	As for the weather, here are my highlights from the year:
	<ul>
		<li>Record high temperatures in July - peaking at 39.1C, more than 2C above the prior record</li>
		<li>Jan-Jul very dry with about 50% of the average, vs rather wet remainder of the year with every month wetter than average</li>
		<li>Year overall was 1.1C above the 30-year average and the warmest recorded at nw3weather so far</li>
		<li>As of Aug 10 we had the driest trailing 365 day period recorded with just 394 mm. Then in Nov, the wettest 31 days with 170 mm</li>
		<li>December was the only month with below average temperatures (-1.1C). Biggest anomaly was +2.4 in Aug.</li>
		<li>Feb 18 saw red warnings for wind in London and we broke some records with a 64 mph gust and 44 mph sustained wind</li>
		<li>Coldest, snowiest December since 2010, and snowiest of any month since Jan 2013</li>
		<li>On Jan 15 the shockwave from the Tonga eruption showed up in the barometer readings.</li>
	</ul>
</p>

	<div style="margin-left: 0.1em">
		<h3>Monthly charts</h3>
		<div id="ryc22" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc22","\/histdata.php?type=tmax&mode=monthly&year=2022&summary_type=0");
//]]></script>
		<div id="ryc23" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc23","\/histdata.php?type=rain&mode=monthly&year=2022&summary_type=0");
//]]></script>
		<div id="ryc24" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc24","\/histdata.php?type=tmaxa&mode=monthly&year=2022&summary_type=0");
//]]></script>
		<div id="ryc25" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc25","\/histdata.php?type=sunhr&mode=monthly&year=2022&summary_type=0");
//]]></script>
		<h3>Daily charts</h3>
		<div style="margin:1em"></div>
		<div id="ryc26" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc26","\/histdata.php?type=tmax&mode=daily&year=2022&month=0");
//]]></script>
		<div id="ryc27" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc27","\/histdata.php?type=rain&mode=daily&year=2022&month=0");
//]]></script>
		<div id="ryc28" class="wxchart ry-chart ry-chart-tall"></div><script>//<![CDATA[
NW3.histChart("ryc28","\/histdata.php?type=tmaxa&mode=daily&year=2022&month=0");
//]]></script>
		<a href="/charts.php">More charts</a>
	</div>

<h3>Key averages and extremes</h3>
<p>
<dl>
	<dt class="temp">Temperature</dt>
	<dd>Mean <b>12.5 C</b> [+1.1 C from the <abbr title="Long-term climate average">LTA</abbr>].
		Absolute Min and Max: <b>-5.1 C</b> (Dec 15), and <b>39.1 C</b> (Jul 19). <br />
		Air frosts: <b>20</b>. Ice days: <b>1</b>. Days above 30C: <b>10</b>. Max feels-like: <b>42 C</b>.
	</dd>
	<dt class="rain">Rainfall</dt>
	<dd>Total: <b>566 mm</b> [-8%] across <b>148</b> days (41%) of <abbr title="=0.2mm">recordable rain</abbr>. <br />
		Highest daily (starting at midnight) total: <b>36 mm</b> (Aug 17). <br />
		Wettest Month: <b>135 mm</b> [+102%] (Nov). Most rainy: <b>20 days</b> (Nov). <br />
		Driest: <b>2 mm</b> [-95%] (Jul). Fewest days: <b>4 days</b> in Aug. <br />
		Most rain in an hour: <b>~23 mm</b>, from a thunderstorm on the <a href="/wxhistday.php?year=2022&month=08&day=17">17th of Aug</a>
	</dd>
	<dt class="sun">Sunshine</dt>
	<dd>Total: <b>1705 hrs</b> [+7%] from a possible 4045. (42%) <br />
		Days with more than a minute of sunshine: <b>312</b> days (86%). <br />
		Sunniest month: <b>234 hrs</b> [+26%] (Aug). Dullest than avg: <b>-29%</b> in Nov.
	</dd>
	<dt class="snow">Winter Events</dt>
	<dd>There were <b>7</b> days with lying snow, and <b>2</b> with falling snow (giving <b>9 cm</b> worth in aggregate),
		as well as <b>222</b> total hrs when the temperature was below zero, the majority in Dec.
		The minimum wind chill was <b>-11C</b> on Dec 15.
	</dd>
	</dl>
</p>
<h3>Website stats</h3>
<p>
	nw3weather returned to growth in 2022. Key stats:
	<ul>
		<li>Total visits: <b>209k</b> (+14% yoy)</li>
		<li>Mean: <b>573</b> per day, Median: <b>453</b></li>
		<li>Max: <b>3.3k</b> on <a href="/wxhistday.php?year=2022&month=7&day=19">19 Jul</a></li>
		<li>Number of tweets: <b>38</b> (+3). Number of Followers: 283 (+22%) (<a href="https://twitter.com/nw3weather">twitter.com/nw3weather</a>).</li>
		<li>New features: none this year for nw3weather!</li>
	</ul>
</p>
<p>
<h3>Weather cam timelapse for 2022</h3>
<video width="640" height="480" controls>
  <source src="/cam/timelapse/skycam_yearly_2022.mp4" type="video/mp4" />
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
	Updated: 27th Mar 2023
</span>
