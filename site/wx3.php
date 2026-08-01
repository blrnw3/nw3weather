<?php
require("Page.php");
Page::init([
	"fileNum" => 3,
	"title" => "Latest Graphs",
	"description" => 'Latest NW3 weather station daily and monthly charts and graphs - temperature, wind speed and direction, rainfall, dew point;
	wind direction plot; tempereature, wind, rain trends; wind rose for Hampstead, London.'
]);
Page::Start();
?>

<h1>Latest Graphs &amp; Charts</h1>

<h2>Live charts - past 24 hours</h2>
<div id="main-graphs">
<?php
Charts::livePanel(['height' => 450, 'multiHeight' => 500]);
?>
<p>To view more timescales and historical data back to 2009, use the <a href="graphviewer.php">Daily Graphs</a> page.</p>
</div>

<h2>31-Day Trends</h2>
<div class="charts">
<?php
Charts::daily(['type' => 'tmean',  'mode' => 'daily', 'length' => 31], ['height' => 260]);
Charts::daily(['type' => 'rain',   'mode' => 'daily', 'length' => 31], ['height' => 260]);
Charts::daily(['type' => 'sunhr',  'mode' => 'daily', 'length' => 31], ['height' => 260]);
Charts::daily(['type' => 'aqmean', 'mode' => 'daily', 'length' => 31], ['height' => 260]);
Charts::dailySelectable(['mode' => 'daily', 'length' => 31], ['height' => 280], null, 'wmean');
?>
</div>

<h2>12-Month Trends</h2>
<div class="charts">
<?php
Charts::daily(['type' => 'tmean', 'mode' => 'monthly', 'length' => 12, 'summary_type' => Data::SUMMARY_MEAN, 'lta' => 1], ['height' => 320, 'tickInterval' => 1]);
Charts::daily(['type' => 'rain',  'mode' => 'monthly', 'length' => 12, 'summary_type' => Data::SUMMARY_SUM,  'lta' => 1], ['height' => 320, 'tickInterval' => 1]);
Charts::dailySelectable(['mode' => 'monthly', 'length' => 12, 'lta' => 1], ['height' => 340, 'tickInterval' => 1], null, 'wmean');
?>
</div>

<h2>Year-to-date</h2>
<div class="charts">
<?php
Charts::cumeSelectable(['height' => 380], 'rain');
?>
</div>
<p>Historical data and even more options can be found on the <a href="charts.php">Charts</a> page.</p>

<div class="charts-with-title">
<div>
	<h2>Last 24 hours wind rose</h2>
	<?php Charts::rose(['en' => '24hrs'], ['legend' => false, 'height' => 460]); ?>
</div>
<div>
	<h2>Monthly wind rose</h2>
	<?php Charts::rose(['en' => 'month'], ['legend' => false, 'height' => 460]); ?>
</div>
</div>

<p><a href="windrose_viewer.php" title="Wind roses for all months and years and days">View historical and all-time wind roses</a></p>

<?php Page::End(); ?>
