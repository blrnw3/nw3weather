<?php
require('unit-select.php');
$file = 7;

$htmlTITLE = ' - My Photos';
$htmlMETA_NAME = 'My Personal Weather Photography from NW3 and around the UK &amp; Europe.';

require $siteRoot.'Top.php';

require ROOT .'photos/albums/albInfo.php';

echo '<div align="center">';
echo "<h1>Photo Galleries</h1>";

table('', null, 5, true, 5);

$cnt = 0;
for ($i = count($mains)-1; $i >= 0; $i--) {
	if($cnt % 4 === 0) {
		echo '<tr valign="top" align="center">';
	}
	echo '<td align="center">
			<a href="albgen.php?albnum='. ($i+1) .'" title="Click to view full album">
				<img src="/photos/'. $refs[$i] . $mains[$i] .'s.JPG" alt="preview photo" width="200" height="150" border="1" />
			</a>
			<br />
			<b>'.$titles[$i].'</b>
			<br />
		</td>';
	if($cnt % 4 === 3 || $i === 0) {
		tr_end();
	}
	$cnt++;
}

table_end();
?>

<h2 id="wx-albums">Weather Station Albums</h2>
<?php
table('', null, 5, true, 5);

$cnt = 0;
for ($i = count($wx_mains)-1; $i >= 0; $i--) {
	if($cnt % 4 === 0) {
		echo '<tr valign="top" align="center">';
	}
	echo '<td align="center">
			<a href="wx_albgen.php?albnum='. ($i+1) .'" title="Click to view full album">
				<img src="/photos/'. $wx_refs[$i] .'/'. $wx_mains[$i] .'s.jpg" alt="preview photo" width="200" height="150" border="1" />
			</a>
			<br />
			<b>'.$wx_titles[$i].'</b>
			<br />
		</td>';
	if($cnt % 4 === 3 || $i === 0) {
		tr_end();
	}
	$cnt++;
}

table_end();
?>


<p align="center"> <i>&copy; Ben Lee-Rodgers</i></p>

<br />

<p><b>About:</b> Photos from after 24th December 2009 were taken with a Panasonic DMC-TZ6;
	before such date, a variety of cameras were used.
</p>

<p><b>NB:</b> All the photos present in these albums are my own, and consequently I hold their copyright.</p>

</div>

<?php require './Bot.php'; ?>