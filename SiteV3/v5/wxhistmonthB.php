<?php
require "Page.php";
Page::init([
	"fileNum" => 86,
	"title" => "Monthly Breakdown",
	"description" => "Detailed historical monthly breakdown showing summary weather data for each day of the month, Hampstead, London (NW3).",
	"isSubFile" => true,
]);
Page::Start();

// ---- Date validation ----
$mproc = isset($_GET['month']) ? intval($_GET['month']) : (int)Date::$mon_yest;
$yproc = isset($_GET['year']) ? intval($_GET['year']) : (int)Date::$yr_yest;
$toofar = false; $num_adv = 0;
$sproc1 = Date::mkdate($mproc, 1, $yproc);
$dim = (int)date('t', $sproc1);
$sproc = Date::mkdate($mproc, $dim, $yproc);
if ($sproc >= mktime(0, 0, 0)) { $sproc = time() - 86400; $dim = (int)Date::$day_yest; $num_adv = 31 - $dim; }
if ($sproc < Date::mkdate(2, 1, 2009) || $mproc < 1 || $mproc > 12) { $toofar = true; $yproc = (int)Date::$dyear; }

$datFile = ROOT . "dat$yproc.csv";
$datLines = is_file($datFile) ? file($datFile) : [];
$haveData = (count($datLines) > 0 && !$toofar && $sproc1 < Date::mkdate((int)Date::$dmonth, (int)Date::$dday + 1, (int)Date::$dyear) && $sproc > Date::mkdate(2, 0, 2009));

// Columns shown: temperature group + rainfall group
$cols = [0, 1, 2, 30, 13, 14, 15, 16];
$colUnit = [0 => Wx::Temperature, 1 => Wx::Temperature, 2 => Wx::Temperature, 30 => Wx::Temperature,
	13 => Wx::Rain, 14 => Wx::Rain, 15 => Wx::Rain, 16 => Wx::RainRate];
$colClass = [0 => 'td14', 1 => 'td14', 2 => 'td14', 30 => 'td14', 13 => 'td12', 14 => 'td12', 15 => 'td12', 16 => 'td12'];
$tempLabels = ['Min', 'Max', 'Mean', 'Feels Max'];
$rainLabels = ['Total', 'Hr-max', '10-min max', 'Max rate'];
?>

<h1>Monthly Breakdown for <?php echo $toofar ? 'Invalid Month!' : date('F Y', $sproc); ?></h1>
<?php
if ($sproc < Date::mkdate(2, 1, 2009)) { echo '<p><b>First report available is February 2009.</b></p>'; }
if ($num_adv > 0) { echo '<p>Based on the first ' . $dim . ' days available.</p>'; }

// Navigation
$prevs = $sproc - 86400 * $dim; $nexts = $sproc + 86400 * (2 + $num_adv);
echo '<table width="800"><tr><td align="left">';
if ($sproc1 > Date::mkdate(2, 10, 2009) && $sproc < Date::mkdate((int)Date::$dmonth, (int)Date::$dday, (int)Date::$dyear) && !$toofar) {
	echo '<a href="/wxhistmonthB.php?year=' . date('Y', $prevs) . '&amp;month=' . date('n', $prevs) . '" title="Previous month">&lt;&lt;Previous Month</a>';
} else { echo '&lt;&lt;Previous Month'; }
echo '</td><td align="center"><form method="get" action="">';
HTML::dateFormMaker($yproc, $mproc);
echo '<input type="submit" value="View Report" /></form> <a href="/wxhistmonth.php" title="Most recent month">Reset</a></td><td align="right">';
if ($sproc < Date::mkdate((int)Date::$dmonth, (int)Date::$dday - 1, (int)Date::$dyear) && Date::mkdate($mproc, 3, $yproc) > Date::mkdate(1, 1, 2009)) {
	echo '<a href="/wxhistmonthB.php?year=' . date('Y', $nexts) . '&amp;month=' . date('n', $nexts) . '" title="Next month">Next Month&gt;&gt;</a>';
} else { echo 'Next Month&gt;&gt;'; }
echo '</td></tr></table>';

if (!$haveData) {
	echo '<p>Monthly breakdown not available.</p>';
	Page::End();
	return;
}

$blank = function ($v) { return $v === '' || $v === '-' || $v === null || !is_numeric($v); };

echo '<table class="table1" width="99%" cellpadding="2" cellspacing="0">';
echo '<tr class="table-top"><td class="td4" width="8%" rowspan="2">Day</td>'
	. '<td class="td14" colspan="4">Temperature / ' . strip_tags(Wx::getUnits(Wx::Temperature)) . '</td>'
	. '<td class="td12" colspan="4">Rainfall / ' . strip_tags(Wx::getUnits(Wx::Rain)) . '</td></tr>';
echo '<tr class="table-top">';
foreach ($tempLabels as $l) { echo '<td class="td14">' . $l . '</td>'; }
foreach ($rainLabels as $l) { echo '<td class="td12">' . $l . '</td>'; }
echo '</tr>';

for ($d = 1; $d <= $dim; $d++) {
	$zed = (int)date('z', Date::mkdate($mproc, $d, $yproc)) + 1;
	$row = isset($datLines[$zed]) ? explode(',', $datLines[$zed]) : [];
	$style = ($d % 2 == 1) ? 'light' : 'dark';
	echo '<tr class="row' . $style . '">';
	echo '<td class="td4C" style="text-align:center;font-weight:bold">' . $d . '</td>';
	foreach ($cols as $ci) {
		$v = isset($row[$ci]) ? $row[$ci] : '';
		$disp = $blank($v) ? '-' : Wx::conv($v, $colUnit[$ci], false);
		echo '<td class="' . $colClass[$ci] . 'C">' . $disp . '</td>';
	}
	echo '</tr>';
}
echo '</table>';

echo '<p><a href="/wxhistmonth.php?month=' . $mproc . '&amp;year=' . $yproc . '" title="Monthly summary">View monthly summary report</a> &nbsp;|&nbsp; '
	. '<a href="/wxhistday.php?day=1&amp;month=' . $mproc . '&amp;year=' . $yproc . '" title="Daily report">View daily breakdown</a></p>';

// Charts
echo '<h2>Graphs and Charts</h2>';
Charts::daily(['type' => 'tmean', 'mode' => 'daily', 'year' => $yproc, 'month' => $mproc], ['height' => 360]);
$lastStamp = date('Ymd', $sproc);
Charts::intradayPanel(['date' => $lastStamp, 'num' => $dim], null, ['height' => 420]);
echo '<h2>Wind rose</h2>';
Charts::rose(['st' => $yproc . Util::zerolead($mproc) . '01', 'en' => 'month'], ['height' => 460]);

Page::End();
