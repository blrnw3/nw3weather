<?php if (!defined('NW3_ANNUAL_REPORT')) { http_response_code(404); exit; } ?>
<h2 id="report-2020">Year 2020 - warm, v. sunny spring; record October rain; least wintry since 2014</h2>
<p style="margin-bottom: 0.5em;">
<p>
	2020 was the twelfth year of records for this weather station, and the tenth complete year in its current location in nw3. <br />
	For the first time since I moved to the US in 2015 I was unable to visit nw3 due to lockdowns, and without its regular maintenance the station did have
	a few issues. The rain and wind sensors failed, but thankfully with local help I've been able to keep things running (rain gauge fixed, wind sensor soon).
	More happily, while stuck at home in northern California, I finally set up a sister weather station here, with a website launch coming in June 2021!
</p>
<p>
	As for the weather, here are my highlights from a warm, sunny year:
	<ul>
		<li>The year began with record January warmth, and near-record Feb warmth; both months 2.6C above average</li>
		<li>Unsurprisingly it was the least wintry year since 2014, with just a single day of snowfall, no lying snow, and a mere 8 air frosts</li>
		<li>Feb was the windiest of any month I've recorded as a whole, and set records for highest gust (58 mph) and windiest day (16 mph)</li>
		<li>The warmth continued into the spring - Apr and May's avg maxima were 4.4 and 3.5 C above avg respectively</li>
		<li>May was exceptionally sunny with 306 hrs - 72% above avg and 69% of the maximum possible! Prior record from Jul 18 exceeded by over 40 hrs</li>
		<li>With sunny months in March, April and Sept too, the year as a whole had it's 2nd highest total, just behind 2018, with 16% more than average</li>
		<li>Rare for nw3, August was the best of the summer months with maxima 2C above avg and a notable heatwave - all days 7th-12th were above 30C and
			the overnight low on the 12th was a record high of 21.1C</li>
		<li>October was the wettest month I've recorded with 166 mm. More than half of that came in a three day downpour - 2nd-4th</li>
		<li>For the 7th year running, the overall average temperature was above the long-term average. It was the 2nd warmest, just behind 2014.</li>
	</ul>
</p>

<p>NB: After extensive analysis of the data from official met office sites at Heathrow and Northolt in west London (my closest two),
	and updating my long-term-average (LTA) period from 1971-2000 to 1981-2010,
	 I made some small adjustments to the climate averages used on this website.
	 Key changes: avg temp increased slightly, especially nights; rain decreased slightly; winter sun increased modestly.
	 When the official LTA data comes out for 1991-2020 I will update them again, probably in a year from now
	 (the standard practice is for the LTA reference period to update every 10 years and look back at a fixed 30-year period).
	</p>
	<p>
Separately I checked my data for possible sensor calibration issues: sun and temp data looks good but rain was too low so I adjusted all 2020 rain totals by +15%.
Furthermore I applied an additional +10% ongoing correction to the rain gauge itself, giving a +20% total ongoing correction.</p>

	<div style="margin-left: 0.1em">
		<h3>Monthly charts</h3>
		<div id="ryc36" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc36","\/histdata.php?type=tmean&mode=monthly&year=2020&summary_type=0");
//]]></script>
		<div id="ryc37" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc37","\/histdata.php?type=rain&mode=monthly&year=2020&summary_type=0");
//]]></script>
		<div id="ryc38" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc38","\/histdata.php?type=tmeana&mode=monthly&year=2020&summary_type=0");
//]]></script>
		<div id="ryc39" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc39","\/histdata.php?type=sunhr&mode=monthly&year=2020&summary_type=0");
//]]></script>
		<h3>Daily charts</h3>
		<div style="margin:1em"></div>
		<div id="ryc40" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc40","\/histdata.php?type=tmax&mode=daily&year=2020&month=0");
//]]></script>
		<div id="ryc41" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc41","\/histdata.php?type=rain&mode=daily&year=2020&month=0");
//]]></script>
		<div id="ryc42" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc42","\/histdata.php?type=tmaxa&mode=daily&year=2020&month=0");
//]]></script>
		<div id="ryc43" class="wxchart ry-chart"></div><script>//<![CDATA[
NW3.histChart("ryc43","\/histdata.php?type=wmean&mode=daily&year=2020&month=0");
//]]></script>
		<a href="/charts.php">More charts</a>
	</div>

<h3>Key averages and extremes</h3>
<p>
<dl>
	<dt class="temp">Temperature</dt>
	<dd>Mean <b>12.2 C</b> [+1.3 C from the <abbr title="Long-term average">LTA</abbr>].
		Absolute Min and Max: <b>-1.8 C</b> (31st Dec), and <b>36.2 C</b> (31st Jul). <br />
		Air frosts: <b>8</b>. Ice days: <b>0</b>. Days above 30C: <b>10</b>. Max feels-like: <b>42 C</b>.
	</dd>
	<dt class="rain">Rainfall</dt>
	<dd>Total: <b>640 mm</b> [+6%] across <b>172</b> days (47%) of <abbr title="=0.2mm">recordable rain</abbr>. <br />
		Highest daily (starting at midnight) total: <b>~34 mm</b> (3rd Oct). <br />
		Wettest Month: <b>166 mm</b> [+144%] (Oct). Most rainy: <b>24 days</b> (Oct). <br />
		Driest: <b>5 mm</b> [-89%] (May). Fewest days: <b>2 days</b> in May. <br />
		Most rain in an hour: <b>~10 mm</b>, from a thunderstorm on the <a href="/wxhistday.php?year=2020&month=08&day=16">16th of Aug</a>
	</dd>
	<dt class="sun">Sunshine</dt>
	<dd>Total: <b>1730 hrs</b> [+16%] from a possible 4045. (43%) <br />
		Days with more than a minute of sunshine: <b>312</b> days (85%). <br />
		Days with at least 95% of their maximum possible sun: <b>36</b> (10%). <br />
		Sunniest month: <b>306 hrs</b> [+72%] (May). Dullest than avg: <b>-36%</b> in Oct.
	</dd>
	<dt class="snow">Winter Events</dt>
	<dd>There were <b>zero</b> days with lying snow, and just <b>1</b> with falling snow (giving <b>3 cm</b> worth),
		as well as <b>8</b> air frosts [-18] (we had <b>36</b> total hrs below zero, the majority in Dec).
		The minimum wind chill was <b>-7C</b> on Dec 31st.
	</dd>
	<dt class="wind">Wind</dt>
	<dd>The maximum wind gust was <b>58 mph</b> on Feb 9th, and the maximum 1-min-avg wind was <b>34 mph</b> <br />
		The windiest day was the 9th Feb with a mean of <b>16 mph</b>. Feb was also the windiest month at 8.7 [+3.6 mph].
	</dd>
	<dt>Other Events</dt>
	<dd>There were <b>2</b> known days of hail, <b>11</b> with thunder (5 in Aug), and <b>8</b> days with fog at 9am.<br />
	</dd>
	</dl>
</p>
<h3>Website stats</h3>
<p>
	nw3weather again experienced strong growth, with traffic up another 21%. Stats:
	<ul>
		<li>Total visits: <b>184,398</b> (a 28% increase over 2019)</li>
		<li>Mean: <b>504</b> per day, Median: <b>429</b> (median lower than mean since busy days - commonly very wet, hot/cold, or snowy days - skew the mean)</li>
		<li>Min: 224 on 12 Sep (<a href="/wxhistday.php?year=2020&month=09&day=12">a sunny Saturday, the quietest day of the week</a>)</li>
		<li>Max: <b>1,917</b> on 04 Oct (<a href="/wxhistday.php?year=2020&month=10&day=04">last day of a record-breaking three-day rain storm</a>)</li>
		<li>Number of tweets: <b>34</b> (+2). Number of Followers: 199 (+45) (<a href="https://twitter.com/nw3weather">twitter.com/nw3weather</a>).</li>
		<li>New features: wind rose graphics for any custom period, including automated daily, monthly, annual generation.</li>
	</ul>
	<img src="/2020/sessions2020.jpg" /> <br />
</p>
<p>
<h3>Weather cam timelapse for 2020</h3>
<video width="640" height="480" controls>
  <source src="/cam/timelapse/skycam_yearly_2020.mp4" type="video/mp4" />
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
	Updated: 14th Jan 2021
</span>
