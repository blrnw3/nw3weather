<?php
require("Page.php");
Page::init([
	"fileNum" => 3,
	"title" => "Latest Graphs",
	"description" => 'Latest NW3 weather station daily and monthly charts and graphs - temperature, wind speed and direction, rainfall, dew point;
	wind direction plot; tempereature, wind, rain trends; wind rose.'
]);
Page::Start();
?>

<h1>Latest Graphs &amp; Charts</h1>

<h2>Past 24hrs</h2>
<div id="main-graphs">
<?php
if(Page::$units != UNIT_UK) {
	echo '<img src="/graphday.php?x=850&y=450" alt="graph 1" />
		<img src="/graphday2.php?x=850&y=450" alt="graph 2" />
		<img src="/graphdayA.php?x=850&y=220&type1=wdir" alt="graph 3" />
		';
	Page::log_events("nonUKwx3.txt", "Imperial: ". makeBool($imperial) . ', Metric:  '. makeBool($metric));
} else {
	echo '
		<img id="bigiGraph" src="/stitchedmaingraph.png?id='. round(time()/10).'" alt="Graph of last 24hrs weather data" '. Site::GRAPH_DIMS_LARGE .' />
	';
}
?>
<p>To view different timescales, use the <a href="graphviewer.php">Custom Graphs</a> page.</p>
</div>

<h2>31-Day Trends</h2>
<div class="charts">
	<img src="/graph31.php?type=tmean&amp;x=430" alt="31-Day temp Trends" width="430" height="200" /> 
	<img src="/graph31.php?type=rain&amp;x=430" alt="31-Day rain Trends" width="430" height="200" />
	<img src="/graph31.php?type=wmean&amp;x=430" alt="31-Day wind Trends" width="430" height="200" />
	<img src="/graph31.php?type=pmean&amp;x=430" alt="31-Day pressure Trends" width="430" height="200" />
</div>

<h2>12-Month Trends</h2>
<div class="charts">
	<img src="/graph12.php?type=tmean&amp;x=430" alt="12-Month temp Trends" width="430" height="200" />
	<img src="/graph12.php?type=rain&amp;x=430" alt="12-Month rain Trends" width="430" height="200" />
	<img src="/graph12.php?type=wmean&amp;x=430" alt="12-Month wind Trends" width="430" height="200" /> 
	<img src="/graph12.php?type=pmean&amp;x=430" alt="12-Month pressure Trends" width="430" height="200" />
</div>
<p>More data types available on the <a href="charts.php">Charts</a> page.</p>

<div class="charts-with-title">
<div>
	<h2>Last 24hrs wind rose</h2>
	<img src="/rose_24hrs.png" alt="24-hr wind rose" width="432" height="460" />
</div>
<div>
	<h2>Monthly Wind Rose</h2>
	<img src="/rose_month.png" alt="Latest month wind rose" width="432" height="460" /> </td> </tr>
</div>
</div>

<a href="windrose_viewer.php" title="Wind roses for all months and years and days">View historical and all-time wind roses</a>
<br />
<a href="grapharchive.php" title="Daily graph archive starting Feb 2009">View archive of 24hr daily graphs</a>

<?php Page::End(); ?>
