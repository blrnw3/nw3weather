<?php
require "Page.php";
require "Report.php";
Page::init([
	"fileNum" => 87,
	"title" => "Annual Reports",
	"description" => "Historical annual weather charts and summaries for Hampstead, London (NW3).",
	"isSubFile" => true,
]);
Page::Start();

$yproc = isset($_GET['year']) ? intval($_GET['year']) : (int)Date::$yr_yest;
$toofar = ($yproc < 2009 || $yproc > (int)Date::$dyear);
if ($toofar) { $yproc = (int)Date::$dyear; }
?>

<h1>Annual Report for <?php echo $yproc; ?></h1>
<?php
// Year navigation
$cond1 = $yproc > 2009 && $yproc <= ((int)Date::$dyear + 1);
$cond2 = $yproc > 2007 && $yproc < (int)Date::$dyear;
echo '<table width="800"><tr><td align="left">';
if ($cond1) { echo '<a href="/wxhistyear.php?year=' . ($yproc - 1) . '" title="Previous year">&lt;&lt;Previous Year</a>'; } else { echo '&lt;&lt;Previous Year'; }
echo '</td><td align="center"><b>Select Year:</b>&nbsp;<form method="get" action=""><select name="year" onchange="this.form.submit()">';
for ($i = 2009; $i <= (int)Date::$dyear; $i++) {
	echo '<option value="' . $i . '"' . ($yproc == $i ? ' selected="selected"' : '') . '>' . $i . '</option>';
}
echo '</select></form></td><td align="right">';
if ($cond2) { echo '<a href="/wxhistyear.php?year=' . ($yproc + 1) . '" title="Next year">Next Year&gt;&gt;</a>'; } else { echo 'Next Year&gt;&gt;'; }
echo '</td></tr></table>';

echo '<p>Monthly weather charts for every available variable in ' . $yproc . ' for London, NW3. '
	. 'Long-term climate normals are overlaid where available. '
	. '<a href="/wxhistmonth.php?year=' . $yproc . '&month=1" title="Detailed monthly reports">View detailed monthly reports</a>.</p>';

// Grid of monthly charts per variable for the chosen year
echo '<div class="annual-charts">';
foreach (Report::$categories as $cat => $vars) {
	echo '<h2>' . $cat . '</h2>';
	foreach ($vars as $v) {
		if (!isset(Wx::$daily[$v])) { continue; }
		$summable = !empty(Wx::$daily[$v]['summable']) || !empty(Wx::$daily[$v]['count-only']);
		$params = [
			'type' => $v,
			'mode' => 'monthly',
			'year' => $yproc,
			'summary_type' => $summable ? Data::SUMMARY_SUM : Data::SUMMARY_MEAN,
		];
		if (!empty(Wx::$daily[$v]['anomaly'])) { $params['lta'] = 1; }
		Charts::daily($params, ['height' => 300]);
	}
}
echo '</div>';

Page::End();
