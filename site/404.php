<?php
require 'Page.php';
http_response_code(404);
Page::init([
	'fileNum' => 0, // generic / error pages: no nav highlight, no "Last Full Update"
	'title' => 'Error',
	'description' => 'Page Not Found! Please check the URL or re-navigate',
]);
Page::Start();
?>

<h1>Page Not Found!</h1>
<h2>Please check the URL or re-navigate</h2>
<p><a href="/" title="Browse to homepage">Home</a></p>

<?php
Page::End();
