<div id="side-bar">
	<div class="leftSideBar">
		<p class="sideBarTitle">Navigation</p>
		<ul>
<?php
$lastPost = mkdate(6,22,2018); //MUST KEEP UPDATED - latest blog post
$lastAlbum = mkdate(9,6,2017); //MUST KEEP UPDATED - latest album upload

$newLength = (3 * 3600 * 24);
$blog = 'Blog'. ( ((time() - $lastPost < $newLength)) ?
	' <sup title="Last post: '. date('jS M Y', $lastPost) . '" style="color:#382">new post</sup>' : '' );
$photos = 'Photos'. ( ((time() - $lastAlbum < $newLength)) ?
	' <sup title="Last upload: '. date('jS M Y', $lastAlbum) . '" style="color:#382">new album</sup>' : '' );

$itemsM = array('Home', 'Webcam', 'Graphs', 'Records', 'Forecast', 'Astronomy', $photos, 'About');
$itemsD = array('Rain', 'Temperature', 'Wind', 'Humidity', 'Pressure', 'Climate', 'Custom Graphs');
$itemsH = array('Daily Tables', 'Monthly Tables', 'Daily Rankings', 'Monthly Rankings', 'Daily Reports', 'Monthly Reports', 'Annual Reports', 'Charts');
$itemsO = array($blog, 'System', 'External');

$titleM = array('Return to main page', 'Live Webcam and Timelapses', 'Latest Daily and Monthly Graphs &amp; Charts', 'Records, Extremes, Trends, and Averages', ' Local Forecasts and Latest Maps',
	'Sun and Moon Data', 'My Weather Photography', ' About this Weather Station and Website');
$titleD = array('Detailed Rain Data', 'Detailed Temperature Data', 'Detailed Wind Data', 'Detailed Humidity Data', 'Detailed Pressure Data',
	'Long-term climate averages', 'Short-term weather graphs');
$titleH = array('Tables of daily data by weather variable', 'Tables of monthly data by weather variable', 'Daily ranked data by weather variable',
	'Monthly ranked data by weather variable', 'Daily detail reports', 'Monthly detail reports', 'Annual summary reports', 'In-depth custom historical data charts');
$titleO = array('Website and weather station blog and news', 'System Status and Miscellaneous', 'My Site on the Web and Useful Weather Links');

$nameM = array('index', 'wx2', 'wx3', 'wx4', 'wx5', 'wx6', 'wx7', 'wx8');
$nameD = array('wx12', 'wx14', 'wx13', 'wx10', 'wx16', 'wxaverages', 'graphviewer');
$nameH = array('wxdataday', 'TablesDataMonth', 'RankDay', 'RankMonth', 'wxhistday', 'wxhistmonth', 'repyear', 'charts');
$nameO = array('news', 'wx15', 'wx9');

$numsM = array(1, 2, 3, 4, 5, 6, 7, 8);
$numsD = array(12, 14, 13, 10, 16, 20, 31);
$numsH = array(40, 40.1, 41, 42, 85, 86, 87, 32);
$numsO = array(96, 15, 9);

$cfile = explode('/', $_SERVER['PHP_SELF']);
define(PAGE_NAME, $cfile[count($cfile)-1]);

function sidebarGroup($items, $titles, $names, $nums) {
	global $subfile, $file;

	for ($i = 0; $i < count($items); $i++) {
		$cond = ($file == $nums[$i]);

		$class = $cond ? ' class="curr"' : '';
		echo "<li$class>";

		if (!($cond && !$subfile)) { //need link
			echo '<a href="/', $names[$i], '.php" title="', $titles[$i], '">', $items[$i], '</a>
				';
		} else {
			echo $items[$i];
		}
		echo '</li>';
	}

}

function sidebarSubheading($title, $colour) {
	echo '<li><hr /></li>
		<li><span class="sideBarText" style="color:#'.$colour.'">'.$title.'</span></li>
	';
}

sidebarGroup($itemsM, $titleM, $nameM, $numsM);
sidebarSubheading("Detailed Data", "38610B");
sidebarGroup($itemsD, $titleD, $nameD, $numsD);
sidebarSubheading("Historical", "0B614B");
sidebarGroup($itemsH, $titleH, $nameH, $numsH);
sidebarSubheading("Other", "5B9D4B");
sidebarGroup($itemsO, $titleO, $nameO, $numsO);

//echo '<li><a href="/', $cfile[count($cfile) - 1], '" title="Return to current version of this page">Old site page</a> <br /></li>';
if($file == 0) {
	echo '</ul><table><tr><td>&nbsp;</td></tr>
		<!-- ';
}
?>
</ul>

<p class="sideBarTitle">Site Options</p>
<table align="center">
	<tr><td><b>Units</b></td></tr>
	<tr><td>
			<form method="get" name="SetUnits" action="">
				<?php
				echo '<label><input name="unit" type="radio" value="US" onclick="this.form.submit();"';
				if ($unitT == 'F') {
					echo ' checked="checked"';
				}
				echo ' />Imperial</label> <br />
					<label><input name="unit" type="radio" value="UK" onclick="this.form.submit();"';
				if ($unitT == 'C') {
					echo ' checked="checked"';
				}
				echo ' />UK</label> <br />
					<label><input name="unit" type="radio" value="EU" onclick="this.form.submit();"';
				if ($unitW != 'mph') {
					echo ' checked="checked"';
				}
				echo ' />Metric</label> <br />
					<noscript><input type="submit" value="Go" /></noscript>';
				?>
			</form>
	</td></tr>

	<?php
	if($file == 0)
		echo ' -->';

	if(!$metaRefreshable) {
		echo '<!--';
	}
	?>
	<tr><td align="center">
			<br /><b>Auto-update</b>
			<span style="font-size:80%"><sup>
				<acronym title="Automatic page refresh when new data arrives">[?]</acronym>
			</sup></span>
	</td></tr>
	<tr><td>
		<form method="get" name="SetUpdate" action="">
			<?php
			echo '<label><input name="update" type="radio" value="on" onclick="this.form.submit();"';
			if ($auto) {
				echo ' checked="checked"';
			}
			echo ' />On</label> <br />
				<label><input name="update" type="radio" value="off" onclick="this.form.submit();"';
			if (!$auto) {
				echo ' checked="checked"';
			}
			echo ' />Off</label> <br />
				<noscript><input type="submit" value="Go" /></noscript>';
			?>
		</form>
	</td></tr>
	<?php
	if(!$metaRefreshable) {
		echo '-->';
	}
	?>

</table>
	</div>
</div>