<?php if (!defined('NW3_ANNUAL_REPORT')) { http_response_code(404); exit; } ?>
<h2>Year 2018 - 'beast from the east', a real summer, plentiful sun, new hardware</h2>
<p style="margin-bottom: 0.5em;" id="report-2018">
<p>
	2018 was the tenth year of records for this weather station! (and the eighth complete year in its current location in nw3). <br />
	In June I installed a new high-resolution IP web camera (20x more pixels than the current),
	and added functionality to the site providing daily, monthly and annual timelapses from the footage.
</p>
<p>
	This year also saw the death of my local weather server PC, which mysteriously and catastrophically shut down for good in late September.
	From across the Atlantic, I scrambled to find a replacement and within two weeks a new micro PC was up and running,
	thanks to the heroic efforts of my onsite assistant, who declared setting it up as "the hardest thing I have ever done".
	To be fair, we didn't have a working keyboard, so I had to explain over the phone how to use the on-screen keyboard
	to install remote access software so I could take over and complete the setup.
</p>
<p>
	And the weather itself? Drier and warmer than average, with a stand out summer (hot, dry, sunny) and notably cold late winter spell.
	It was also by far the sunniest year I've recorded. My weather highlights:
	<ul>
		<li>Late Feb into March saw a powerful cold snap bring our coldest day on record (-4.1C) and a spell of sub-zero weather lasting over 100 hours</li>
		<li>Snowiest year since 2013, with 8 days of snow on the ground and 16cm total snowfall</li>
		<li>March had 52% above avg rainfall, with rain on 23 days; both new records for that month</li>
		<li>The 19th of April was the hottest April day at 28.4C, and over 15C above average,
			<a href="/RankDay.php?vartype=tmaxa&month=0&rankLimit=20" title="see rankings">a record anomaly</a></li>
		<li>June was the driest month on record, with only 0.8mm of rain (99% below average), from 4 separate days each with under an hour of rainfall</li>
		<li>July was the warmest month I've recorded, 3.8C above average, with a daily mean high of 27.2C</li>
		<li>July was also the sunniest month I've recorded, 42% above avg, with daily sun averaging 8.5 hrs</li>
		<li>Summer as a whole, especially mid June to early Aug, was hot, dry & sunny; most notable was how long those conditions lasted</li>
		<li>Experienced our oldest October night on record, -0.8 C</li>
		<li>We had ~15% less rain than average (driest since 2011), and no month had more than 70 mm (a first)</li>
		<li>It was the fifth consecutive year with above average temperatures (+1.3C, same as 2017). Only Feb and March were colder than avg</li>
		<li>The year as a whole was by far the sunniest year I've recorded, with 18% more than average.</li>
	</ul>
</p>
	<div style="margin-left: 0.1em">
		<img src="/2018/2018_temp.png" />
		<img src="/2018/2018_rain.png" />
		<img src="/2018/2018_sun.png" />
		<img src="/2018/2018_pres.png" />
		<img src="/2018/2018_wind.png" />
		<img src="/2018/2018_snow.png" />
		<div style="margin:1em"></div>
		<img src="/2018/2018_d_tmax.png" />
		<img src="/2018/2018_d_tmeana.png" />
		<a href="/charts.php">More charts</a>
	</div>
<p>
	As for website traffic, nw3weather again had its busiest ever year since launching in Sep 2010. Stats:
	<ul>
		<li>Total visits: <b>118,259</b> (a 21% increase over 2017; was 2018 more interesting weather-wise?)</li>
		<li>Mean: <b>324</b> per day, Median: <b>282</b> (median lower than mean since busy days - commonly very wet, hot/cold, or snowy days - skew the mean)</li>
		<li>Min: 173 on 22nd Dec (<a href="/wxhistday.php?year=2018&month=12&day=22">a Saturday, the quietest day of the week</a>)</li>
		<li>Max: <b>1256</b> on 28th Feb (<a href="/wxhistday.php?year=2018&month=02&day=28">snowy with record-breaking cold</a>)</li>
		<li>Number of tweets: <b>31</b>. Number of Followers: 128 (<a href="https://twitter.com/nw3weather">twitter.com/nw3weather</a>).</li>
	</ul>
	<img src="/2018/2018_visits.PNG" />
	<p>Additionally, to support the new volume of image and video data from the IP cam (I now store snapshots every 5 minutes, along with the daily timelapses),
		I increased the storage of the web server to 75 GB. I expect to need an extra 25 GB per year.
	</p>
</p>
<h3>Summary of Data</h3>
<p>
<dl>
	<dt class="temp">Temperature</dt>
	<dd>Mean <b>12.1 C</b> [+1.3 C from the <abbr title="Long-term average">LTA</abbr>].
		Absolute Min and Max: <b>-5.4 C</b> (28th Feb), and <b>33.9 C</b> (26th Jul). <br />
		Air frosts: <b>23</b>. Ice days: <b>4</b>. Days above 30C: <b>12</b>
	</dd>
	<dt class="rain">Rainfall</dt>
	<dd>Total: <b>530 mm</b> [-15%] across <b>145</b> days (40%) of <abbr title="=0.2mm">recordable rain</abbr>. <br />
		Highest daily (starting at midnight) total: <b>17 mm</b> (10th Nov). <br />
		Wettest Month: <b>67 mm</b> [+52%] (Mar). Most rainy: <b>23 days</b> (March again). <br />
		Driest: <b>0.8 mm</b> [-99%] (June) across just <b>4 days</b> of rainfall. <br />
		Most rain in an hour: <b>12.2 mm</b>, from a thunderstorm on the <a href="/wxhistday.php?year=2018&month=07&day=13">13th of July</a>
	</dd>
	<dt class="sun">Sunshine</dt>
	<dd>Total: <b>1734 hrs</b> [+18%] from a possible 4045. (42%) <br />
		Days with more than a minute of sunshine: <b>305</b> days (84%). <br />
		Days with at least 95% of their maximum possible sun: <b>39</b> (11%). <br />
		Sunniest month: <b>263 hrs</b> [+42%] (July). Most sunnier than avg: <b>+44%</b> in Oct. Dullest than avg: <b>-41%</b> in Apr.
	</dd>
	<dt class="snow">Winter Events</dt>
	<dd>There were <b>eight</b> day with lying snow (max: <b>6 cm</b> on 1st March), and <b>8</b> with falling snow (giving <b>16 cm</b> worth),
		as well as <b>23</b> air frosts [-3] (<b>218</b> hrs below zero, the majority in Feb &amp; March).
		The minimum wind chill was <b>-15C</b> on Feb 28th.
	</dd>
	<dt class="wind">Wind</dt>
	<dd>The maximum wind gust was <b>50 mph</b> on Jan 3rd, and the maximum 1-min-avg wind was <b>31 mph</b> <br />
		The windiest day was the 3rd of Jan with a mean of <b>12.6 mph</b>. Jan was also the windiest month, [+1.1 mph].
	</dd>
	<dt>Other Events</dt>
	<dd>There were <b>0</b> known days of hail, <b>9</b> with thunder (4 in May), and <b>0</b> known days with fog at 9am. <br />
	</dd>
	</dl>
</p>
<p>
<h3>Weather cam timelapse for 2018</h3>
<video width="640" height="480" controls>
  <source src="/cam/timelapse/skycam_yearly_2018.mp4" type="video/mp4" />
Your browser does not support the video tag.
</video>
<br /><a href="timelapsechive.php" title="Webcam timelapse archive"><b>See full timelapse archive</b></a> (high-res cam available 20th Jun onwards)
</p>
<p>
<a href="/TablesDataMonth.php?vartype=rain" title="Monthly Rain Records">
	These pages are useful for comparing all aspects of the weather across the years
</a>.
</p>
</p>
<span style="color:#555; font-size: 90%">
	Updated: 7th Jan 2019
</span>
