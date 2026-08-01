<?php
$var = new nw3\app\api\Summary();
?>

<h1>Trends, Extremes, and Averages</h1>

<h2>Extremes and Records</h2>

<table>
	<caption>Extremes</caption>
	<?php $this->viewette('datadetail/_period_tbl', $var->extremes()) ?>
</table>

<p>
	<b>Note 1:</b> Records for most variables began in Jan or Feb 2009<br />
	<b>Note 3:</b> Figures in brackets refer to departure from <a href="wxaverages.php" title="Long-term NW3 climate averages">average conditions</a>;
	note well that the month and year figures are adjusted for the current date, i.e. compared to the expected conditions for the month/year to-date, rather than the full period<br />
	<b>Note 3:</b> This station defines the start of the meteorological day to be midnight; this is when daily values are reset. Except Temperature (09-21 Day and 21-09 Night).<br />
</p>

<h2>Means and Totals</h2>

<table>
	<caption>Averages and Totals</caption>
	<?php $this->viewette('datadetail/_period_tbl', $var->aggs()) ?>
</table>


<h2>Trends</h2>

<?php $this->viewette('datadetail/_trend_tbl', [
	'data' => $var->trend_diffs(),
	'caption' => 'Trends - Changes',
	'trend_caption' => 'Change since last'
]); ?>

<?php $this->viewette('datadetail/_trend_tbl', [
	'data' => $var->trend_avgs(),
	'caption' => 'Trends - Averages',
	'trend_caption' => 'Average over past'
]); ?>

<table>
	<caption>Trends - This day a month/year ago</caption>
	<?php $this->viewette('datadetail/_period_tbl', $var->trend_extremes()) ?>
</table>

<table>
	<caption>Trends - This month/year vs same date last month/yr</caption>
	<?php $this->viewette('datadetail/_period_tbl', $var->trend_cumuls()) ?>
</table>
