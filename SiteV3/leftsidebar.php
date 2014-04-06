<div id="side-bar">
	<div class="leftSideBar">
		<p class="sideBarTitle">Navigation</p>
		<ul>
<?php
$lastPost = mkdate(3,30,2014); //MUST KEEP UPDATED - latest blog post
$lastAlbum = mkdate(6,4,2014); //MUST KEEP UPDATED - latest album upload

$newLength = (3 * 3600 * 24);
$blog = 'Blog'. ( ((time() - $lastPost < $newLength)) ?
	' <sup title="Last post: '. date('jS M Y', $lastPost) . '" style="color:#382">new post</sup>' : '' );
$photos = 'Photos'. ( ((time() - $lastAlbum < $newLength)) ?
	' <sup title="Last upload: '. date('jS M Y', $lastAlbum) . '" style="color:#382">new album</sup>' : '' );

$itemsM = array('Home', 'Webcam', 'Graphs', 'Data Summary', 'Forecast', 'Astronomy', $photos, 'About');
$itemsD = array('Rain', 'Wind', 'Temperature', 'Humidity', 'Charts', 'Climate');
$itemsH = array('Data Tables', 'Rankings', 'Daily Reports', 'Monthly Reports', 'Custom Graphs');
$itemsO = array($blog, 'System', 'External');

$titleM = array('Return to main page', 'Live Webcam and Timelapses', 'Latest Daily and Monthly Graphs &amp; Charts', ' Extremes and Trends, and Averages', ' Local Forecasts and Latest Maps',
	'Sun and Moon Data', 'My Weather Photography', ' About this Weather Station and Website');
$titleD = array('Detailed Rain Data', 'Detailed Wind Data', 'Detailed Temperature Data', 'Detailed Humidity Data', '31-day and 12-month Data Charts', 'Long-term climate averages');
$titleH = array('Tables of monthly and daily data by type', 'Daily and monthly ranked data by type', 'Weather', 'Weather', 'Customisable multi-variable line graphs');
$titleO = array('Website and weather station blog and news', 'System Status and Miscellaneous', 'My Site on the Web and Useful Weather Links');

$nameM = array('index', 'wx2', 'wx3', 'wx4', 'wx5', 'wx6', 'wx7', 'wx8');
$nameD = array('wx12', 'wx13', 'wx14', 'wx10', 'charts', 'wxaverages', 'wx15');
$nameH = array('wxdataday', 'RankDay', 'wxhistday', 'wxhistmonth', 'graphviewer', 'Historical');
$nameO = array('news', 'wx15', 'wx9');

$numsM = array(1, 2, 3, 4, 5, 6, 7, 8);
$numsD = array(12, 13, 14, 10, 32, 20);
$numsH = array(40, 41, 85, 86, 31, 87);
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