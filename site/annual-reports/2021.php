<?php if (!defined('NW3_ANNUAL_REPORT')) { http_response_code(404); exit; } ?>
<h2 id="report-2021">Year 2021 - coolest in eight years, severely lacking in sun, fairly wet</h2>
<p style="margin: 0.5em;">
<p>
	2021 was the 13th year of records for this weather station, and the 11th complete year in its current location in nw3. <br />
	Aside from the wind sensor which is still broken, despite my efforts to fix it, the station is still going strong.
	I moved the weather cam to sit outside the window in direct weather, which has improved the glorious images streaming in.
	I also launched a sister station here in the SF bay area, <a href="https://rwcweather.com" title="Redwood City, CA weather">RWC weather</a>.
</p>
<p>
	As for the weather, here are my highlights from the year:
	<ul>
		<li>Cold, wet, dreary <a href="wxhistmonth.php?year=2021&month=1">January</a>. Coldest since 2017, wettest since 2014, dullest since 2013.</li>
		<li>One freezing week in <a href="wxhistmonth.php?year=2021&month=2">Feb</a> when there was a 64 hour spell of sub-zero weather and 6 days of means below zero.</li>
		<li><a href="wxhistmonth.php?year=2021&month=4">April</a>: cold, sunny, very dry. Coldest in my records and 2.7C below the 30yr normal; 2nd driest in my records with 93% less rain than avg.</li>
		<li><a href="wxhistmonth.php?year=2021&month=5">May</a> continued the cold trend (-1.7 vs avg, a record for me), but the rain returned (+60% vs avg) and sun vanished (-32%)</li>
		<li>The summer mostly saw colder than avg days with limited sun, especially in <a href="wxhistmonth.php?year=2021&month=8">Aug</a> (only 50% of avg and duller than March and Nov!).
		Early June was nice, and mid July which saw the hottest temp of the year at 31.7 (albeit this was the lowest 7 years).</li>
		<li>Record <a href="wxhistmonth.php?year=2021&month=7">July</a> rain (168% above avg) was thanks mostly to two very active thunderstorms on the 12th and 25th
			The first caused local flooding and resulted in <a href="RankDay.php?vartype=rain&month=0&rankLimit=20">record daily rainfall</a>; some areas in Hampstead received over 100mm.</li>
		<li>After the dismal Aug, <a href="wxhistmonth.php?year=2021&month=9">September</a> was the warmest in 5 years.
			<a href="wxhistmonth.php?year=2021&month=10">October</a> was wet for the 3rd year running (+50% vs avg),
			while <a href="wxhistmonth.php?year=2021&month=5">November</a> was the driest in my record books (88% below avg) despite seeing some rain on more than a third of the days.</li>
		<li><a href="wxhistmonth.php?year=2021&month=12">December</a> was abysmally dull, with just 29 hrs of sun, matching the record low of Dec 2010 (-56% vs avg).</li>
		<li>Overall 2021 was the least sunny in my 13 years of records, the coldest since 2013, and wettest since 2014.</li>
	</ul>
</p>

<p>Additionally, at the end of the year I updated the reference period for climate normals from 1981-2010 to 1991-2020, as per standards.
	The latter period was about 0.5C warmer than the prior one, and marginally wetter. Separately I updated the sunshine averages to fix a
	bug in my original translation of the Heathrow averages I based them on.
	Updated values are now on the <a href="wxaverages.php">climate page</a> and used throughout the site.
	</p>

	<div style="margin-left: 0.1em">
		<h3>Monthly charts</h3>
		<div id="ryc29" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc29","\/histdata.php?type=tmax&mode=monthly&year=2021&summary_type=0");
//]]></script>
		<div id="ryc30" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc30","\/histdata.php?type=rain&mode=monthly&year=2021&summary_type=0");
//]]></script>
		<div id="ryc31" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc31","\/histdata.php?type=tmaxa&mode=monthly&year=2021&summary_type=0");
//]]></script>
		<div id="ryc32" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc32","\/histdata.php?type=sunhr&mode=monthly&year=2021&summary_type=0");
//]]></script>
		<h3>Daily charts</h3>
		<div style="margin:1em"></div>
		<div id="ryc33" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc33","\/histdata.php?type=tmax&mode=daily&year=2021&month=0");
//]]></script>
		<div id="ryc34" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc34","\/histdata.php?type=rain&mode=daily&year=2021&month=0");
//]]></script>
		<div id="ryc35" class="wxchart ry-chart ry-chart-tall"></div><script>//<![CDATA[
NW3.histChart("ryc35","\/histdata.php?type=tmaxa&mode=daily&year=2021&month=0");
//]]></script>
		<a href="/charts.php">More charts</a>
	</div>

<h3>Key averages and extremes</h3>
<p>
<dl>
	<dt class="temp">Temperature</dt>
	<dd>Mean <b>11.3 C</b> [-0.1 C from the <abbr title="Long-term climate average">LTA</abbr>].
		Absolute Min and Max: <b>-3.9 C</b> (Feb 13), and <b>31.7 C</b> (Jul 20). <br />
		Air frosts: <b>27</b>. Ice days: <b>2</b>. Days above 30C: <b>3</b>. Max feels-like: <b>39 C</b>.
	</dd>
	<dt class="rain">Rainfall</dt>
	<dd>Total: <b>676 mm</b> [+10%] across <b>159</b> days (44%) of <abbr title="=0.2mm">recordable rain</abbr>. <br />
		Highest daily (starting at midnight) total: <b>42 mm</b> (Jul 12). <br />
		Wettest Month: <b>123 mm</b> [+168%] (Jul). Most rainy: <b>19 days</b> (Jan). <br />
		Driest: <b>3 mm</b> [-93%] (Apr). Fewest days: <b>4 days</b> in Apr. <br />
		Most rain in an hour: <b>~20 mm</b>, from a thunderstorm on the <a href="/wxhistday.php?year=2021&month=07&day=12">12th of July</a>
	</dd>
	<dt class="sun">Sunshine</dt>
	<dd>Total: <b>1323 hrs</b> [-17%] from a possible 4045. (33%) <br />
		Days with more than a minute of sunshine: <b>294</b> days (80%). <br />
		Days with at least 95% of their maximum possible sun: <b>19</b> (5%). <br />
		Sunniest month: <b>200 hrs</b> [+24%] (Apr). Dullest than avg: <b>-56%</b> in Dec.
	</dd>
	<dt class="snow">Winter Events</dt>
	<dd>There were <b>6</b> days with lying snow, and <b>7</b> with falling snow (giving <b>10 cm</b> worth in aggregate),
		as well as <b>249</b> total hrs when the temperature was below zero, the majority in Feb.
		The minimum wind chill was <b>-12C</b> on Feb 13.
	</dd>
	<dt class="wind">Wind</dt>
		<dd>No reliable wind data this year ;(</dd>
	<dt>Other Events</dt>
	<dd>There was <b>1</b> know days of hail, <b>5</b> with thunder (3 Jul, 2 May), and <b>4</b> days with dense fog at 9am.<br />
	</dd>
	</dl>
</p>
<h3>Website stats</h3>
<p>
	nw3weather had no year-on-year growth for the first time in its history... Stats:
	<ul>
		<li>Total visits: <b>184,398</b> (-1% over 2020)</li>
		<li>Mean: <b>503</b> per day, Median: <b>435</b></li>
		<li>Min: 215 on <a href="/wxhistday.php?year=2021&month=09&day=25">25 Sep</a></li>
		<li>Max: <b>1,552</b> on <a href="/wxhistday.php?year=2021&month=6&day=18">18 Jun</a></li>
		<li>Number of tweets: <b>35</b> (+1). Number of Followers: 220 (+21) (<a href="https://twitter.com/nw3weather">twitter.com/nw3weather</a>).</li>
		<li>New features: none this year for nw3weather, check out <a href="http://rwcweather.com">rwcweather</a> for new features!</li>
	</ul>
</p>
<p>
<h3>Weather cam timelapse for 2021</h3>
<video width="640" height="480" controls>
  <source src="/cam/timelapse/skycam_yearly_2021.mp4" type="video/mp4" />
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
	Updated: 5th Jan 2022
</span>
