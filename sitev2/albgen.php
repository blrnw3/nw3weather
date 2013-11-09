<?php
if(!isset($_GET['view'])) {
		echo '<p>', $albdescrip, '</p>';
		echo '<p align="center"><b>Overview</b><br /><i>Click on thumbnail to jump to full-size image and show film-strip</i><br /><br />';
		for($i = 1; $i <= $imgnum; $i++) {
			echo '<a href="album', $albnum, 'm.php?view=Strip&img=', $i, '#start"><img src="', $imgref, $i, 's.JPG" width="20%" alt="loading..." title="', $imgdescrip[$i-1], '" /></a><span style="color:white">\n</span>';
			if($i%3 == 0) { echo '<br />'; }
		}
		echo '</p><a href="wx7.php" title="Return to overview of photo galleries">Back to Photos</a><br />';
}

else {
	if($_GET['view'] == 'Full') { // All photos displayed
		echo '<p>', $albdescrip, '</p><p align="center"><b>Overview</b><br /><i>Click on thumbnail to jump to full-size image</i><br /><br />';

		for($i = 1; $i <= $imgnum; $i++) {
			echo '<a href="#pic', $i, '"><img src="', $imgref, $i, 's.JPG" width="20%" alt="loading..." title="', $imgdescrip[$i-1], '" /></a><span style="color:white">\n</span>';
			if($i%3 == 0) { echo '<br />'; }
		}
		
		echo '</p><a href="wx7.php" title="Return to overview of photo galleries">Back to Photos</a><br /><br /><br />';

		for($i = 1; $i <= $imgnum; $i++) {
			echo '<a name="pic', $i, '"> </a><h3>', $imgdescrip[$i-1], '</h3><img src="', $imgref, $i, '.JPG" width="65%" alt="Loading image ', $i, '..." /> <a href="#header">Top</a><br /><br />';
		}
	}
	else { // Individual photo-Viewer
		if(!isset($_GET['img'])) { $num = 1; } else { $num = intval($_GET['img']); } 
		echo '<br />	<a name="start"></a>';
		echo '<h3>', $imgdescrip[$num-1], '</h3>'; // Header

		echo '<table align="center" width="60%"><tr>'; // Navigation bar
		echo '<td width="15%" align="left">'; if($num != 1) { echo '<a href="album', $albnum, 'm.php?view=Strip&img=1#start" title="First Photo">'; } echo '&lt;&lt; START'; if($num != 1) { echo '</a>'; } echo '</td>';
		echo '<td width="15%" align="left">'; if($num != 1) { echo '<a href="album', $albnum, 'm.php?view=Strip&img=', $num-1, '#start" title="Previous Photo">'; } echo '&lt; Previous'; if($num != 1) { echo '</a>'; } echo '</td>';
		echo '<td width="40%" align="center"><a href="album', $albnum, 'm.php#main-copy" title="Return to thumbnails">Album Overview</a></td>';
		echo '<td width="15%" align="right">'; if($num != $imgnum) { echo '<a href="album', $albnum, 'm.php?view=Strip&img=', $num+1, '#start" title="Next Photo">'; } echo 'Next &gt;'; if($num != $imgnum) { echo '</a>'; } echo '</td>';
		echo '<td width="15%" align="right">'; if($num != $imgnum) { echo '<a href="album', $albnum, 'm.php?view=Strip&img=', $imgnum, '#start" title="Last Photo">'; } echo 'END &gt;&gt;'; if($num != $imgnum) { echo '</a>'; } echo '</td>';
		echo '</tr></table>'; // End of bar

		echo '<img src="', $imgref, $num, '.JPG" width="56%" alt="Loading image ', $num, '..." />'; // Main Image

		echo '<table align="center" width="60%"><tr>'; // Thumbnails of other photos
		if($num == 1) {
			echo '<td width="24%"><img width="96%" border="5" style="border-color:#0B610B" src="', $imgref, '1s.JPG" title="', $imgdescrip[0], '" alt="Loading image 1..." /></td>';
			for($i = 2; $i <= 5; $i++) {
				echo '<td width="19%"><a href="album', $albnum, 'm.php?view=Strip&img=', $i, '#start"><img width="96%" src="', $imgref, $i, 's.JPG" title="', $imgdescrip[$i-1], '" alt="Loading image ', $i, '..." /></a></td>';
			}
		}
		elseif($num == 2) {
			echo '<td width="19%"><a href="album', $albnum, 'm.php?view=Strip&img=1#start"><img width="96%" src="', $imgref, '1s.JPG" title="', $imgdescrip[0], '" alt="Loading image 1..." /></a></td>';
			echo '<td width="24%"><img width="96%" border="5" style="border-color:#0B610B" src="', $imgref, '2s.JPG" title="', $imgdescrip[1], '" alt="Loading image 2..." /></td>';
			for($i = 3; $i <= 5; $i++) {
				echo '<td width="19%"><a href="album', $albnum, 'm.php?view=Strip&img=', $i, '#start"><img width="96%" src="', $imgref, $i, 's.JPG" title="', $imgdescrip[$i-1], '" alt="Loading image ', $i, '..." /></a></td>';
			}
		}
		elseif($num == $imgnum-1) {
			for($i = $imgnum-4; $i <= $imgnum-2; $i++) {
				echo '<td width="19%"><a href="album', $albnum, 'm.php?view=Strip&img=', $i, '#start"><img width="96%" src="', $imgref, $i, 's.JPG" title="', $imgdescrip[$i-1], '" alt="Loading image ', $i, '..." /></a></td>';
			}
			echo '<td width="24%"><img width="96%" border="5" style="border-color:#0B610B" src="', $imgref, $imgnum-1, 's.JPG" title="', $imgdescrip[$imgnum-2], '" alt="Loading image', $imgnum-1, '..." /></td>';
			echo '<td width="19%"><a href="album', $albnum, 'm.php?view=Strip&img=', $imgnum, '#start"><img width="96%" src="', $imgref, $imgnum, 's.JPG" title="', $imgdescrip[$imgnum-1], '" alt="Loading image', $imgnum, '..." /><a></td>';
		}
		elseif($num == $imgnum) {
			for($i = $imgnum-4; $i <= $imgnum-1; $i++) {
				echo '<td width="19%"><a href="album', $albnum, 'm.php?view=Strip&img=', $i, '#start"><img width="96%" src="', $imgref, $i, 's.JPG" title="', $imgdescrip[$i-1], '" alt="Loading image ', $i, '..." /></a></td>';
			}
			echo '<td width="24%"><img width="96%" border="5" style="border-color:#0B610B" src="', $imgref, $imgnum, 's.JPG" title="', $imgdescrip[$imgnum-1], '" alt="Loading image', $imgnum, '..." /></td>';
		}
		else {
			for($i = $num-2; $i <= $num+2; $i++) {
				if($num == $i) { $width = 24; } else { $width = 19; }
				echo '<td width="', $width, '%"><a href="album', $albnum, 'm.php?view=Strip&img=', $i, '#start"><img width="96%"';
				if($num == $i) { echo 'border="5" style="border-color:#0B610B"'; }
				echo 'src="', $imgref, $i, 's.JPG" title="', $imgdescrip[$i-1], '" alt="Loading image ', $i, '..." /></a></td>';
			}
		}
		echo '</tr></table>';
	}
}

echo '<br /><span><b>Album Viewer Type</b></span>
	<form method="get" action="album', $albnum, 'm.php"> <input name="view" type="submit" value="Full"'; if($_GET['view'] == 'Full') { echo 'disabled="disabled"'; } echo ' />
	<input name="view" type="submit" value="Strip"'; if($_GET['view'] == 'Strip') { echo 'disabled="disabled"'; } echo ' /></form>';
?>