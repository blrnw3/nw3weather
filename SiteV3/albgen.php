<?php
require('unit-select.php');


require ROOT .'photos/albums/albInfo.php';

if(isset($_GET['albnum'])) {
	$_SESSION['albnum'] = (int) $_GET['albnum'];
}
$albnum = $_SESSION['albnum'];
if($albnum <= count($titles) && $albnum > 0) { //input validation
	require ROOT .'photos/albums/alb'.$albnum.'.php';
} else {
	die('Naughty! Album not in range');
}


$imgref = '/photos/'. $refs[$albnum-1];
$title = $titles[$albnum-1];
$imgnum = count($imgdescrip);

$file = 7;
$subfile = true;

$htmlTITLE = ' - Photos - '. $title;
$htmlMETA_NAME = 'Weather Photography - '. $title;

require $siteRoot.'Top.php';

//start album-maker
echo '<div align="center">';
echo "<h1>$title</h1>";

if (!isset($_GET['view'])) {
	echo '<p>', $albdescrip, '</p>';
	echo '<p align="center"><b>Overview</b><br /><i>Click on thumbnail to jump to full-size image, or <b><a href="albgen.php?view=Full">view full album</a></b></i><br /><br />';
	for ($i = 1; $i <= $imgnum; $i++) {
		echo '<a href="albgen.php?view=Strip&img=', $i, '#start"><img src="', $imgref, $i, 's.JPG" width="200" alt="photo" title="', $imgdescrip[$i - 1], '" /></a><span style="color:white">\n</span>';
		if ($i % 3 == 0) {
			echo '<br />';
		}
	}
	echo '</p><a href="wx7.php" title="Return to overview of photo galleries">Back to Photos</a><br />';
} else {
	if ($_GET['view'] == 'Full') { // All photos displayed
		echo '<p>', $albdescrip, '</p><p align="center"><b>Overview</b><br /><i>Click on thumbnail to jump to full-size image, or simply scroll</i><br /><br />';

		for ($i = 1; $i <= $imgnum; $i++) {
			echo '<a href="#pic', $i, '"><img src="', $imgref, $i, 's.JPG" width="220" alt="Photo..." title="', $imgdescrip[$i - 1], '" /></a><span style="color:white">\n</span>';
			if ($i % 3 == 0) {
				echo '<br />';
			}
		}

		echo '</p><a href="wx7.php" title="Return to overview of photo galleries">Back to all albums</a><br /><br /><br />';

		for ($i = 1; $i <= $imgnum; $i++) {
			echo '<a name="pic', $i, '"> </a>
				<h3>',	$imgdescrip[$i - 1], '</h3>
				<a href="',$imgref, $i, '.JPG" title="Full-size">
					<img src="', $imgref, $i, '.JPG" width="800" alt="Photo ', $i, '..." />
				</a>
				<a href="#header">Top</a>
				<br /><br />';
		}
	} else { // Individual photo-Viewer
		if (!isset($_GET['img'])) {
			$num = 1;
		} else {
			$num = intval($_GET['img']);
		}
		echo '<br />	<a name="start"></a>';
		echo '<h3>', $imgdescrip[$num - 1], '</h3>'; // Header

		echo '<table align="center" width="60%"><tr>'; // Navigation bar
		echo '<td width="15%" align="left">';
		if ($num != 1) {
			echo '<a href="albgen.php?view=Strip&img=1#start" title="First Photo">';
		} echo '&lt;&lt; START';
		if ($num != 1) {
			echo '</a>';
		} echo '</td>';
		echo '<td width="15%" align="left">';
		if ($num != 1) {
			echo '<a href="albgen.php?view=Strip&img=', $num - 1, '#start" title="Previous Photo">';
		} echo '&lt; Previous';
		if ($num != 1) {
			echo '</a>';
		} echo '</td>';
		echo '<td width="40%" align="center"><a href="albgen.php#main" title="Return to thumbnails">Album Overview</a></td>';
		echo '<td width="15%" align="right">';
		if ($num != $imgnum) {
			echo '<a href="albgen.php?view=Strip&img=', $num + 1, '#start" title="Next Photo">';
		} echo 'Next &gt;';
		if ($num != $imgnum) {
			echo '</a>';
		} echo '</td>';
		echo '<td width="15%" align="right">';
		if ($num != $imgnum) {
			echo '<a href="albgen.php?view=Strip&img=', $imgnum, '#start" title="Last Photo">';
		} echo 'END &gt;&gt;';
		if ($num != $imgnum) {
			echo '</a>';
		} echo '</td>';
		echo '</tr></table>'; // End of bar

		//Main Image
		echo '<a href="',$imgref, $num, '.JPG" title="Full-size">
					<img src="', $imgref, $num, '.JPG" width="83%" alt="Photo ', $num, '..." />
				</a>';

		echo '<table align="center" width="60%"><tr>'; // Thumbnails of other photos
		if ($num == 1) {
			echo '<td width="24%"><img width="96%" border="5" style="border-color:#0B610B" src="', $imgref, '1s.JPG" title="', $imgdescrip[0], '" alt="Photo 1..." /></td>';
			for ($i = 2; $i <= 5; $i++) {
				echo '<td width="19%"><a href="albgen.php?view=Strip&img=', $i, '#start"><img width="96%" src="', $imgref, $i, 's.JPG" title="', $imgdescrip[$i - 1], '" alt="Photo ', $i, '..." /></a></td>';
			}
		} elseif ($num == 2) {
			echo '<td width="19%"><a href="albgen.php?view=Strip&img=1#start"><img width="96%" src="', $imgref, '1s.JPG" title="', $imgdescrip[0], '" alt="Photo 1..." /></a></td>';
			echo '<td width="24%"><img width="96%" border="5" style="border-color:#0B610B" src="', $imgref, '2s.JPG" title="', $imgdescrip[1], '" alt="Photo 2..." /></td>';
			for ($i = 3; $i <= 5; $i++) {
				echo '<td width="19%"><a href="albgen.php?view=Strip&img=', $i, '#start"><img width="96%" src="', $imgref, $i, 's.JPG" title="', $imgdescrip[$i - 1], '" alt="Photo ', $i, '..." /></a></td>';
			}
		} elseif ($num == $imgnum - 1) {
			for ($i = $imgnum - 4; $i <= $imgnum - 2; $i++) {
				echo '<td width="19%"><a href="albgen.php?view=Strip&img=', $i, '#start"><img width="96%" src="', $imgref, $i, 's.JPG" title="', $imgdescrip[$i - 1], '" alt="Photo ', $i, '..." /></a></td>';
			}
			echo '<td width="24%"><img width="96%" border="5" style="border-color:#0B610B" src="', $imgref, $imgnum - 1, 's.JPG" title="', $imgdescrip[$imgnum - 2], '" alt="Photo', $imgnum - 1, '..." /></td>';
			echo '<td width="19%"><a href="albgen.php?view=Strip&img=', $imgnum, '#start"><img width="96%" src="', $imgref, $imgnum, 's.JPG" title="', $imgdescrip[$imgnum - 1], '" alt="Photo', $imgnum, '..." /><a></td>';
		} elseif ($num == $imgnum) {
			for ($i = $imgnum - 4; $i <= $imgnum - 1; $i++) {
				echo '<td width="19%"><a href="albgen.php?view=Strip&img=', $i, '#start"><img width="96%" src="', $imgref, $i, 's.JPG" title="', $imgdescrip[$i - 1], '" alt="Photo ', $i, '..." /></a></td>';
			}
			echo '<td width="24%"><img width="96%" border="5" style="border-color:#0B610B" src="', $imgref, $imgnum, 's.JPG" title="', $imgdescrip[$imgnum - 1], '" alt="Photo', $imgnum, '..." /></td>';
		} else {
			for ($i = $num - 2; $i <= $num + 2; $i++) {
				if ($num == $i) {
					$width = 24;
				} else {
					$width = 19;
				}
				echo '<td width="', $width, '%"><a href="albgen.php?view=Strip&img=', $i, '#start"><img width="96%"';
				if ($num == $i) {
					echo 'border="5" style="border-color:#0B610B"';
				}
				echo 'src="', $imgref, $i, 's.JPG" title="', $imgdescrip[$i - 1], '" alt="Photo ', $i, '..." /></a></td>';
			}
		}
		echo '</tr></table>';
	}
}

echo '<br /><span><b>Album Viewer Type</b></span>
	<form method="get" action=""> <input name="view" type="submit" value="Full"';
if ($_GET['view'] == 'Full') {
	echo 'disabled="disabled"';
} echo ' />
	<input name="view" type="submit" value="Strip"';
if ($_GET['view'] == 'Strip') {
	echo 'disabled="disabled"';
} echo ' /></form>';

echo '</div>';

require $siteRoot .'Bot.php';
?>