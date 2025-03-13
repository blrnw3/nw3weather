<?php

require("Page.php");
Page::init([
	"fileNum" => 7,
	"title" => "Photos",
	"description" => 'My Personal Weather Photography from NW3 and around the UK &amp; Europe.'
]);

require ROOT .'photos/albums/albInfo.php';

Page::Start();
?>
<h1>Photo Galleries</h1>

<div class="galleries">
<?php

for ($i = count($mains)-1; $i >= 0; $i--) {
	echo '<div>
			<a href="albgen.php?albnum='. ($i+1) .'&view=Full" title="Click to view full album">
				<img src="/photos/'. $refs[$i] . $mains[$i] .'s.JPG" alt="preview photo" width="200" height="150" border="1" />
			</a>
			<span>'.$titles[$i].'</span>
		</div>';
}
?>
</div>

<h2 id="wx-albums">Weather Station Albums</h2>
<div class="galleries">
<?php

for ($i = count($wx_mains)-1; $i >= 0; $i--) {
	echo '<div>
			<a href="wx_albgen.php?albnum='. ($i+1) .'&view=Full" title="Click to view full album">
				<img src="/photos/'. $wx_refs[$i] .'/'. $wx_mains[$i] .'s.jpg" alt="preview photo" width="200" height="150" border="1" />
			</a>
			<span>'.$wx_titles[$i].'</span>
		</div>';
}
?>
</div>

<p><i>&copy; Ben Masschelein-Rodgers</i></p>

<p><b>About:</b> Photos from after 24th December 2009 were taken with a Panasonic DMC-TZ6;
	before such date, a variety of cameras were used.
</p>

<p><b>NB:</b> All the photos present in these albums are my own, and consequently I hold their copyright.</p>


<?php Page::End(); ?>