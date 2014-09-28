
<h1>Latest Graphs &amp; Charts</h1>

<h2>Past 24hrs</h2>
<?php
if(false) { # Non-UK... TODO
	echo '<img src="graphday.php?x=850&y=450" alt="graph 1" />
		<img src="graphday2.php?x=850&y=450" alt="graph 2" />
		<img src="graphdayA.php?x=850&y=220&type1=wdir" alt="graph 3" />
		';
//	log_events("nonUKwx3.txt", "Imperial: ". makeBool($imperial) . ', Metric:  '. makeBool($metric));
} else {
	echo '<button onclick="showMiniGraph();"> Toggle Graph Size </button>
		<img id="bigiGraph" src="/stitchedmaingraph.png?id='. round(time()/10).'" alt="Graph of last 24hrs weather data" '. GRAPH_DIMS_LARGE .' />
		<img id="miniGraph" src="/stitchedmaingraph_small.png?id='. round(time()/10).'" alt="Mini-Graph of last 24hrs weather" style="display:none" '. GRAPH_DIMS_SMALL .' />
	';
}
?>
<p>To view different timescales, use the <a href="graphviewer.php">Custom Graphs</a> page.</p>

<h2>31-Day Trends</h2>
<img src="graph/daily/tmean?x=430" alt="31-Day temp Trends" /> <img src="./graph/daily/rain?x=430" alt="31-Day rain Trends" />
<img src="graph/daily/wmean?x=430" alt="31-Day wind Trends" /> <img src="./graph/daily/pmean?x=430" alt="31-Day pressure Trends" />

<h2>12-Month Trends</h2>
<img src="graph/monthly/tmean?x=430" alt="12-Month temp Trends" /> <img src="graph/monthly/rain?x=430" alt="12-Month rain Trends" />
<img src="graph/monthly/wmean?x=430" alt="12-Month wind Trends" /> <img src="graph/monthly/pmean?x=430" alt="12-Month pressure Trends" />
<p>More variables and historical graphs available on the <a href="charts.php">Charts</a> page.</p>

<table cellpadding="26" width="98%">
<tr> <td align="center"><h2>12-hr Wind Direction Plot</h2></td> <td align="center"><h2>Monthly Wind Rose</h2></td> </tr>
<tr> <td align="right"> <img src="/dirplot.gif" alt="12-hr wind direction radar plot" /> </td> <td><img src="/windrose.gif" alt="Latest month windrose" /> </td> </tr>
</table>

<br />
<a href="grapharchive.php" title="Daily graph archive starting Feb 2009">View archive of 24hr daily graphs</a>
