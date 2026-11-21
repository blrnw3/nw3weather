<?php if (!defined('NW3_ANNUAL_REPORT')) { http_response_code(404); exit; } ?>
		<h2 id="report-2024">Year 2024 - wet, warm, gloomy</h2>
<p style="margin: 0.5em;">
<p>
	2024 was the 16th year of records for this weather station. <br />
	In May I replaced the rain gauge with a new 'tipping-spoon' version which has shown greatly-improved accuracy.
	I also gave the radiation shield a good clean and new coat of paint, and repaired a broken cable on the wind sensor.<br />
</p>
<p>
	Here are my weather highlights from the year:
	<ul>
		<li>Rainy: wettest year since 2014, with ~30% more than normal. September was the wettest in nw3 since 1974</li>
		<li>Warm again: 2nd warmest of the past 100+ years, after 2022. Overnight temps were an overall record high</li>
		<li>Dull: one of the dullest years in the past 50, with 17% less sun than normal, on par with 2021. 69 days had zero sunshine.</li>
		<li>Snow: barely any. The worst winter since 2014 when there was literally nothing.</li>
		<li>Feb was the warmest in the 100+ year record, by 0.7 C over Feb 1990. The min-temp anomaly (+3.4) was higher than all but Dec 2015 (+5.8)</li>
		<li>June was the best of the summer months, with little rain and lots of sun</li>
		<li>November saw a relentless gloomy spell, the 2nd worst 14-day stretch for Nov in over 100 years</li>
		<li>December was the dullest month since 1969, with just 21 hrs of sun and 15 days with zero sunshine</li>
		<li>May 11th saw the Aurora Borealis come to nw3, lighting up the skies above.</li>
	</ul>
</p>
<p>
NB: due to the rain gauge replacement noted above, I had to adjust the rainfall amounts from late May onwards downwards versus initial reporting,
as a correction I was applying to the old rain gauge no longer applied. Unfortunately I only realised this at the end of the year.
</p>

	<div style="margin-left: 0.1em">
		<h3>Monthly charts</h3>
		<div id="ryc8" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc8","\/histdata.php?type=tmean&mode=monthly&year=2024&summary_type=0");
//]]></script>
		<div id="ryc9" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc9","\/histdata.php?type=rain&mode=monthly&year=2024&summary_type=0");
//]]></script>
		<div id="ryc10" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc10","\/histdata.php?type=tmaxa&mode=monthly&year=2024&summary_type=0");
//]]></script>
		<div id="ryc11" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc11","\/histdata.php?type=sunhr&mode=monthly&year=2024&summary_type=0");
//]]></script>
		<h3>Daily charts</h3>
		<div style="margin:1em"></div>
		<div id="ryc12" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc12","\/histdata.php?type=tmax&mode=daily&year=2024&month=0");
//]]></script>
		<div id="ryc13" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc13","\/histdata.php?type=tmin&mode=daily&year=2024&month=0");
//]]></script>
		<div id="ryc14" class="wxchart ry-chart ry-chart-tall"></div><script>//<![CDATA[
NW3.histChart("ryc14","\/histdata.php?type=rain&mode=daily&year=2024&month=0");
//]]></script>
		<a href="/charts.php">More charts</a>
	</div>

<h3>Key averages and extremes</h3>
<p>
<dl>
	<dt class="temp">Temperature</dt>
	<dd>Mean <b>12.2 C</b> [+0.8 C from the <abbr title="Long-term climate average">LTA</abbr>].
		Absolute Min and Max: <b>-4.3 C</b> (Jan 18), and <b>33.2 C</b> (Aug 12). <br />
		Air frosts: <b>10</b>. Ice days: <b>0</b>. Days above 30C: <b>3</b>.
	</dd>
	<dt class="rain">Rainfall</dt>
	<dd>Total: <b>795 mm</b> [+29%] across <b>171</b> days (47%) of <abbr title="=0.2mm">recordable rain</abbr>. <br />
		Highest daily (starting at midnight) total: <b>27 mm</b> (Sep 23). <br />
		Wettest Month: <b>120 mm</b> [+140%] (Sep). Most rainy: <b>22 days</b> (Feb). <br />
		Driest: <b>10 mm</b> [-80%] (Jun) on just 6 days, with a total of 16 hours of rainfall  <br />
		Most rain in an hour: <b>~17 mm</b>, on the <a href="/wxhistday.php?year=2024&month=09&day=23">23rd of Sep</a>
	</dd>
	<dt class="sun">Sunshine</dt>
	<dd>Total: <b>1326 hrs</b> [-17%] from a possible 4045. (33%) <br />
		Days with more than a minute of sunshine: <b>297</b> days (81%). <br />
		Sunniest month: <b>205 hrs</b> [+5%] (Jun). Dullest: <b>21 hrs</b> [-67%] in Dec.
	</dd>
	<dt class="snow">Winter Events</dt>
	<dd>
		A touch of snow on Jan 8th was all.
	</dd>
</dl>
</p>
<h3>Website stats</h3>
<p>
	Another good year for organic growth. Key data:
	<ul>
		<li>Total sessions: <b>324k</b> (+13% yoy)</li>
		<li>Mean: <b>885</b> per day, Median: <b>737</b></li>
		<li>Max: <b>2.8k</b> on <a href="/wxhistday.php?year=2024&month=9&day=23">23 Sep</a></li>
		<li>Monthly-active users: <b>~9000</b> (+23% yoy)</li>
		<li>Number of tweets: <b>22</b> (-2). Number of Followers: 300 (-2%) (<a href="https://twitter.com/nw3weather">twitter.com/nw3weather</a>)</li>
		<li>New features: added historical data back to ~1910 using nearby Met Office sites.</li>
	</ul>
</p>
<p>
<h3>Weather cam timelapse for 2024</h3>
<video width="640" height="480" controls>
  <source src="/cam/timelapse/skycam_yearly_2024.mp4" type="video/mp4" />
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
	Updated: 02 Jan 2025
</span>
