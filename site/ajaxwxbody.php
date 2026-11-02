<?php
// Lightweight endpoint that returns only the live current-conditions table,
// for the home page's periodic refresh. No full page chrome is rendered.
require __DIR__ . '/Page.php';
Page::init([
	"fileNum" => 1,
	"title" => "Live data",
	"description" => "Live weather data"
]);
require __DIR__ . '/live-body.php';
nw3_render_cards();
// The home page also refreshes its climate card, which sits further down the page.
if (isset($_GET['clim'])) {
	echo '<div id="clim-next" hidden>';
	nw3_render_clim_card();
	echo '</div>';
}
