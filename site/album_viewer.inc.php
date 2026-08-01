<?php
/**
 * Shared album viewer body for albgen.php / wx_albgen.php.
 * Caller must set before require: $selfPage, $albumTitles, $albumRefs,
 * $albumFilePrefix, $imgExt, $imgrefSuffix, $metaPrefix.
 */

$albCount = count($albumTitles);
$albnum = null;
if (isset($_GET['albnum']) && ctype_digit((string)$_GET['albnum'])) {
	$albnum = (int)$_GET['albnum'];
} elseif (isset($_SESSION['albnum']) && ctype_digit((string)$_SESSION['albnum'])) {
	$albnum = (int)$_SESSION['albnum'];
}

if ($albnum === null || $albnum < 1 || $albnum > $albCount) {
	http_response_code(400);
	die('Naughty! Album not in range');
}
$_SESSION['albnum'] = $albnum;

// Album data may reference $unitW for distance wording.
$unitW = Wx::getUnitsText(Wx::Wind);
$albumFile = ROOT . 'photos/albums/' . $albumFilePrefix . $albnum . '.php';
if (!is_file($albumFile)) {
	$albumFile = ROOT . 'albums/' . $albumFilePrefix . $albnum . '.php';
}
if (!is_file($albumFile)) {
	http_response_code(404);
	die('Album data not found');
}
require $albumFile;

$imgref = '/photos/' . $albumRefs[$albnum - 1] . $imgrefSuffix;
$title = $albumTitles[$albnum - 1];
$imgnum = count($imgdescrip);

$view = isset($_GET['view']) ? (string)$_GET['view'] : '';
if ($view !== '' && $view !== 'Full' && $view !== 'Strip') {
	$view = '';
}

$num = 1;
if ($view === 'Strip') {
	if (isset($_GET['img']) && ctype_digit((string)$_GET['img'])) {
		$num = (int)$_GET['img'];
	}
	if ($num < 1 || $num > $imgnum) {
		$num = 1;
	}
}

/**
 * Build a self-link that always carries albnum (and optional query bits).
 * @param array $params
 * @param string $hash optional fragment without '#'
 * @return string
 */
$albHref = function ($params = array(), $hash = '') use ($selfPage, $albnum) {
	$params['albnum'] = $albnum;
	$url = $selfPage . '?' . http_build_query($params);
	if ($hash !== '') {
		$url .= '#' . $hash;
	}
	return $url;
};

Page::$title = 'Photos - ' . $title;
Page::$description = $metaPrefix . $title;
Page::Start();

$esc = function ($s) {
	return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
};
?>

<div class="album-viewer">
<h1><?php echo $esc($title); ?></h1>

<?php if ($view === '') { ?>
	<p><?php echo $albdescrip; ?></p>
	<p class="album-overview-note"><b>Overview</b><br />
		<i>Click on thumbnail to jump to full-size image, or <b><a href="<?php echo $esc($albHref(array('view' => 'Full'))); ?>">view full album</a></b></i>
	</p>
	<div class="album-thumbs galleries">
	<?php for ($i = 1; $i <= $imgnum; $i++) {
		$thumb = $imgref . $i . 's.' . $imgExt;
		$href = $albHref(array('view' => 'Strip', 'img' => $i), 'start');
		echo '<div><a href="' . $esc($href) . '"><img src="' . $esc($thumb) . '" alt="photo" title="' . $esc($imgdescrip[$i - 1]) . '" /></a></div>';
	} ?>
	</div>
	<p><a href="wx7.php" title="Return to overview of photo galleries">Back to Photos</a></p>

<?php } elseif ($view === 'Full') { ?>
	<p><?php echo $albdescrip; ?></p>
	<p class="album-overview-note"><b>Overview</b><br />
		<i>Click on thumbnail to jump to full-size image, or simply scroll</i>
	</p>
	<div class="album-thumbs galleries">
	<?php for ($i = 1; $i <= $imgnum; $i++) {
		$thumb = $imgref . $i . 's.' . $imgExt;
		echo '<div><a href="#pic' . $i . '"><img src="' . $esc($thumb) . '" alt="Photo..." title="' . $esc($imgdescrip[$i - 1]) . '" /></a></div>';
	} ?>
	</div>
	<p><a href="wx7.php" title="Return to overview of photo galleries">Back to all albums</a></p>

	<div class="album-full-list">
	<?php for ($i = 1; $i <= $imgnum; $i++) {
		$full = $imgref . $i . '.' . $imgExt;
		echo '<div class="album-full-item" id="pic' . $i . '">';
		echo '<h3>' . $esc($imgdescrip[$i - 1]) . '</h3>';
		echo '<a href="' . $esc($full) . '" title="Full-size"><img class="album-img-full" src="' . $esc($full) . '" alt="Photo ' . $i . '" /></a>';
		echo '<p><a href="#header">Top</a></p>';
		echo '</div>';
	} ?>
	</div>

<?php } else { /* Strip */ ?>
	<a id="start"></a>
	<h3><?php echo $esc($imgdescrip[$num - 1]); ?></h3>

	<nav class="album-nav">
		<span class="album-nav-start">
		<?php if ($num != 1) { echo '<a href="' . $esc($albHref(array('view' => 'Strip', 'img' => 1), 'start')) . '" title="First Photo">'; }
		echo '&lt;&lt; START';
		if ($num != 1) { echo '</a>'; } ?>
		</span>
		<span class="album-nav-prev">
		<?php if ($num != 1) { echo '<a href="' . $esc($albHref(array('view' => 'Strip', 'img' => $num - 1), 'start')) . '" title="Previous Photo">'; }
		echo '&lt; Previous';
		if ($num != 1) { echo '</a>'; } ?>
		</span>
		<span class="album-nav-mid"><a href="<?php echo $esc($albHref(array(), 'main')); ?>" title="Return to thumbnails">Album Overview</a></span>
		<span class="album-nav-next">
		<?php if ($num != $imgnum) { echo '<a href="' . $esc($albHref(array('view' => 'Strip', 'img' => $num + 1), 'start')) . '" title="Next Photo">'; }
		echo 'Next &gt;';
		if ($num != $imgnum) { echo '</a>'; } ?>
		</span>
		<span class="album-nav-end">
		<?php if ($num != $imgnum) { echo '<a href="' . $esc($albHref(array('view' => 'Strip', 'img' => $imgnum), 'start')) . '" title="Last Photo">'; }
		echo 'END &gt;&gt;';
		if ($num != $imgnum) { echo '</a>'; } ?>
		</span>
	</nav>

	<?php
	$full = $imgref . $num . '.' . $imgExt;
	echo '<a class="album-main-link" href="' . $esc($full) . '" title="Full-size">';
	echo '<img class="album-img-full" src="' . $esc($full) . '" alt="Photo ' . $num . '" />';
	echo '</a>';

	// Sliding strip of nearby thumbnails (up to 5).
	if ($imgnum <= 5) {
		$from = 1;
		$to = $imgnum;
	} elseif ($num <= 2) {
		$from = 1;
		$to = 5;
	} elseif ($num >= $imgnum - 1) {
		$from = $imgnum - 4;
		$to = $imgnum;
	} else {
		$from = $num - 2;
		$to = $num + 2;
	}
	?>
	<div class="album-strip-thumbs">
	<?php for ($i = $from; $i <= $to; $i++) {
		$thumb = $imgref . $i . 's.' . $imgExt;
		$cls = ($i === $num) ? ' current' : '';
		if ($i === $num) {
			echo '<div class="album-strip-thumb' . $cls . '"><img src="' . $esc($thumb) . '" title="' . $esc($imgdescrip[$i - 1]) . '" alt="Photo ' . $i . '" /></div>';
		} else {
			$href = $albHref(array('view' => 'Strip', 'img' => $i), 'start');
			echo '<div class="album-strip-thumb' . $cls . '"><a href="' . $esc($href) . '"><img src="' . $esc($thumb) . '" title="' . $esc($imgdescrip[$i - 1]) . '" alt="Photo ' . $i . '" /></a></div>';
		}
	} ?>
	</div>
<?php } ?>

	<div class="album-view-switch">
		<span><b>Album Viewer Type</b></span>
		<form method="get" action="">
			<input type="hidden" name="albnum" value="<?php echo (int)$albnum; ?>" />
			<input name="view" type="submit" value="Full"<?php if ($view === 'Full') { echo ' disabled="disabled"'; } ?> />
			<input name="view" type="submit" value="Strip"<?php if ($view === 'Strip') { echo ' disabled="disabled"'; } ?> />
		</form>
	</div>
</div>

<?php Page::End(); ?>
