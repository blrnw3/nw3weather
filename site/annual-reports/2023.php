<?php if (!defined('NW3_ANNUAL_REPORT')) { http_response_code(404); exit; } ?>
	<h2 id="report-2023">Year 2023 - warm and sunny June & Sept, wet end, minimal snow</h2>
<p style="margin: 0.5em;">
<p>
	2023 was the 15th year of records for this weather station.<br />

</p>
<p>
	Here are my highlights from the year:
	<ul>
		<li>Feb was very dry - just 4 mm, a winter month low</li>
		<li>March followed with its wettest, dullest showing, providing more than double its avg rainfall (83 mm) and less half its avg sunshine (40 hrs)</li>
		<li>We broke the record for longest rain-free spell, with a 27 day stretch ending June 11th</li>
		<li>June was consistently warm and sunny, with the highest avg maximum of any June in nw3weather records (24.5 C), and the most sun (253 hrs)</li>
		<li>September had the highest temps of the year, and was overall warmer than July and August</li>
		<li>Dew points in September were very high: we set a new any-month record and all-time high - 22.2 C</li>
		<li>The barometer dipped to a record low in November - 956 mb - as a deep cyclone crossed southern Britain</li>
		<li>December was the warmest winter month since the record-breaking one in 2015, and was warmer than both March and Nov</li>
		<li>Overall temperatures were 0.5 C above the 15-year average, but the average dew point was a record high, 1.6 C above avg</li>
		<li>Rainfall came in a few percent above average and was well distributed but for the dry spells in Feb and May/June.</li>
	</ul>
</p>

	<div style="margin-left: 0.1em">
		<h3>Monthly charts</h3>
		<div id="ryc15" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc15","\/histdata.php?type=tmax&mode=monthly&year=2023&summary_type=0");
//]]></script>
		<div id="ryc16" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc16","\/histdata.php?type=rain&mode=monthly&year=2023&summary_type=0");
//]]></script>
		<div id="ryc17" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc17","\/histdata.php?type=tmaxa&mode=monthly&year=2023&summary_type=0");
//]]></script>
		<div id="ryc18" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc18","\/histdata.php?type=sunhr&mode=monthly&year=2023&summary_type=0");
//]]></script>
		<h3>Daily charts</h3>
		<div style="margin:1em"></div>
		<div id="ryc19" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc19","\/histdata.php?type=tmax&mode=daily&year=2023&month=0");
//]]></script>
		<div id="ryc20" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc20","\/histdata.php?type=tmin&mode=daily&year=2023&month=0");
//]]></script>
		<div id="ryc21" class="wxchart ry-chart ry-chart-tall"></div><script>//<![CDATA[
NW3.histChart("ryc21","\/histdata.php?type=rain&mode=daily&year=2023&month=0");
//]]></script>
		<a href="/charts.php">More charts</a>
	</div>

<h3>Key averages and extremes</h3>
<p>
<dl>
	<dt class="temp">Temperature</dt>
	<dd>Mean <b>12.1 C</b> [+0.7 C from the <abbr title="Long-term climate average">LTA</abbr>].
		Absolute Min and Max: <b>-4.9 C</b> (Jan 23), and <b>31.7 C</b> (Sep 9). <br />
		Air frosts: <b>29</b>. Ice days: <b>1</b>. Days above 30C: <b>6</b>. Max feels-like: <b>40 C</b>.
	</dd>
	<dt class="rain">Rainfall</dt>
	<dd>Total: <b>647 mm</b> [+5%] across <b>159</b> days (44%) of <abbr title="=0.2mm">recordable rain</abbr>. <br />
		Highest daily (starting at midnight) total: <b>21 mm</b> (Sep 20). <br />
		Wettest Month: <b>94 mm</b> [+45%] (Oct). Most rainy: <b>23 days</b> (Mar). <br />
		Driest: <b>4 mm</b> [-91%] (Feb). Fewest days: <b>4 days</b> in Feb, 8 hrs total. <br />
		Most rain in an hour: <b>~14 mm</b>, from a thunderstorm on the <a href="/wxhistday.php?year=2023&month=06&day=12">12th of Jun</a>
	</dd>
	<dt class="sun">Sunshine</dt>
	<dd>Total: <b>1504 hrs</b> [-6%] from a possible 4045. (37%) <br />
		Days with more than a minute of sunshine: <b>322</b> days (88%). <br />
		Sunniest month: <b>253 hrs</b> [+31%] (Jun). Dullest than avg: <b>-60%</b> in Mar.
	</dd>
	<dt class="snow">Winter Events</dt>
	<dd>
		Minimal. A few cm of snow fell early on Mar 8th and settled. Otherwise a trace of snow on March 6 and 10, and on Dec 1st.
	</dd>
</dl>
</p>
<h3>Website stats</h3>
<p>
	Very strong growth this year, no idea why. Key data:
	<ul>
		<li>Total sessions: <b>287k</b> (+37% yoy)</li>
		<li>Mean: <b>787</b> per day, Median: <b>683</b></li>
		<li>Max: <b>2.6k</b> on <a href="/wxhistday.php?year=2023&month=11&day=2">02 Nov</a></li>
		<li>Monthly-active users: <b>~7000</b></li>
		<li>Number of tweets: <b>20</b> (-18). Number of Followers: 305 (+8%) (<a href="https://twitter.com/nw3weather">twitter.com/nw3weather</a>)</li>
		<li>New features: charts with cumulative rain vs normal, daily temperature vs normal, and monthly variables vs normal.</li>
	</ul>
</p>
<p>
<h3>Weather cam timelapse for 2023</h3>
<video width="640" height="480" controls>
  <source src="/cam/timelapse/skycam_yearly_2023.mp4" type="video/mp4" />
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
	Updated: 01 Jan 2024
</span>
