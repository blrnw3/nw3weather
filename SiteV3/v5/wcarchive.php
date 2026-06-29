<?php
require("Page.php");
Page::init([
	"fileNum" => 2,
	"title" => "Webcam image archive",
	"description" => "View daily historical web cam (weather skycam) image summaries from Hampstead Heath, North London."
]);
Page::Start();

$dproc = isset($_GET['day']) ? intval($_GET['day']) : Date::$day_yest;
$mproc = isset($_GET['month']) ? intval($_GET['month']) : Date::$mon_yest;
$yproc = isset($_GET['year']) ? intval($_GET['year']) : Date::$yr_yest;
$sproc = Date::mkdate($mproc, $dproc, $yproc);
$datetag = date('Ymd', $sproc) . 'daily';
$datedescrip = date('jS F Y', $sproc);
$direc = ($yproc < date('Y')) ? $yproc . '/' : '';

$prevs = $sproc - 3600 * 24;
$nexts = $sproc + 3600 * 24;
$prevd = date('j', $prevs); $prevm = date('n', $prevs); $prevy = date('Y', $prevs);
$nextd = date('j', $nexts); $nextm = date('n', $nexts); $nexty = date('Y', $nexts);

$cond1 = $sproc > Date::mkdate(8, 1, 2010);
$cond2 = $sproc < Date::mkdate(Date::$dmonth, Date::$dday, Date::$dyear)
	&& mktime(1, 0, 0, $mproc, $dproc, $yproc) > Date::mkdate(7, 31, 2010);
$cond3 = $sproc < Date::mkdate(6, 27, 2012);
if ($cond3) {
	$endtag = 'gif';
} else {
	$endtag = 'jpg';
	$direc = date('Y', $sproc) . '/';
}
if (!$cond1) { echo '<b>Archive begins on 1st August 2010</b>'; }
if (Date::mkdate() - $sproc < 24 * 3600) { $datetag = 'today'; $direc = ''; }
?>

<h1>Webcam image summary Archive</h1>

<br />
<a name="start"></a>

<table width="612"><tr><td align="left">
<?php if ($cond1) { echo '<a href="wcarchive.php?year=', $prevy, '&amp;month=', $prevm, '&amp;day=', $prevd, '#start" title="View previous day&#39;s images">'; } ?>
&lt;&lt;Previous <?php if ($cond1) { echo '</a>'; } ?></td>
<td align="center">
<form method="get" action="">
<select name="year">
<?php
for ($i = 2010; $i <= Date::$dyear; $i++) {
	echo '<option value="', $i, '"';
	if ($yproc == $i) { echo ' selected="selected"'; }
	echo '>', $i, '</option>';
} ?>
</select>
<select name="month">
<?php
for ($i = 0; $i < 12; $i++) {
	echo '<option value="', sprintf('%1$02d', $i + 1), '"';
	if ($mproc == $i + 1) { echo ' selected="selected"'; }
	echo '>', Date::$months[$i], '</option>';
} ?>
</select>
<select name="day">
<?php
for ($i = 1; $i <= 31; $i++) {
	echo '<option value="', sprintf('%1$02d', $i), '"';
	if ($dproc == $i) { echo ' selected="selected"'; }
	echo '>', $i, '</option>';
} ?>
</select>
<input type="submit" value="View" />
</form> &nbsp; <a href="wcarchive.php" title="Return to latest available image">Reset</a>
</td><td align="right">
<?php if ($cond2) { echo '<a href="wcarchive.php?year=', $nexty, '&amp;month=', $nextm, '&amp;day=', $nextd, '#start" title="View next day&#39;s images">'; } ?>
Next&gt;&gt; <?php if ($cond2) { echo '</a>'; } ?></td>
</tr>

<?php
echo '<tr><td colspan="3">';
if (file_exists(ROOT . $direc . $datetag . 'webcam.' . $endtag)) {
	echo '<img title="Image for ', $datedescrip, '" alt="Summary Image" src="/', $direc, $datetag, 'webcam.', $endtag, '" />';
} else {
	echo 'Image not available for this day';
}
echo '</td></tr>';

if ($cond3) {
	echo '<tr><td colspan="3">';
	if (file_exists(ROOT . $direc . $datetag . 'webcam2.gif')) {
		echo '<img title="Image 2 for ', $datedescrip, '" alt="Summary Image 2" src="/', $direc, $datetag, 'webcam2.gif" />';
	} else {
		echo 'Image 2 not available for this day';
	}
	echo '</td></tr>';
}
?>

</table>

<?php Page::End(); ?>
