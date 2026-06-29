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

<h2>Past 24 hours</h2>
<div id="main-graphs">
<?php
Charts::intradayPanel(['num' => 1], null, ['height' => 420]);
?>
<p>To view different timescales and past days, use the <a href="graphviewer.php">Custom Graphs</a> page.</p>
</div>

<h2>31-Day Trends</h2>
<div class="charts">
<?php
Charts::daily(['type' => 'tmean', 'mode' => 'daily', 'length' => 31], ['height' => 260]);
Charts::daily(['type' => 'rain',  'mode' => 'daily', 'length' => 31], ['height' => 260]);
Charts::daily(['type' => 'wmean', 'mode' => 'daily', 'length' => 31], ['height' => 260]);
Charts::daily(['type' => 'pmean', 'mode' => 'daily', 'length' => 31], ['height' => 260]);
?>
</div>

<h2>12-Month Trends</h2>
<div class="charts">
<?php
Charts::daily(['type' => 'tmean', 'mode' => 'monthly', 'length' => 12, 'summary_type' => Data::SUMMARY_MEAN, 'lta' => 1], ['height' => 260]);
Charts::daily(['type' => 'rain',  'mode' => 'monthly', 'length' => 12, 'summary_type' => Data::SUMMARY_SUM,  'lta' => 1], ['height' => 260]);
Charts::daily(['type' => 'wmean', 'mode' => 'monthly', 'length' => 12, 'summary_type' => Data::SUMMARY_MEAN, 'lta' => 1], ['height' => 260]);
Charts::daily(['type' => 'pmean', 'mode' => 'monthly', 'length' => 12, 'summary_type' => Data::SUMMARY_MEAN], ['height' => 260]);
?>
</div>
<p>More data types and longer periods on the <a href="charts.php">Charts</a> page.</p>

<div class="charts-with-title">
<div>
	<h2>Last 24 hours wind rose</h2>
	<?php Charts::rose(['en' => '24hrs']); ?>
</div>
<div>
	<h2>Monthly wind rose</h2>
	<?php Charts::rose(['en' => 'month']); ?>
</div>
</div>

<p><a href="windrose_viewer.php" title="Wind roses for all months and years and days">View historical and all-time wind roses</a></p>
<p><a href="grapharchive.php" title="Daily graph archive starting Feb 2009">View archive of 24hr daily graphs</a></p>

<?php Page::End(); ?>
