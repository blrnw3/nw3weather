<?php
require("Page.php");
Page::init([
	"fileNum" => 0,
	"title" => "Contact",
	"description" => "Contact info for NW3 weather - email or social media for comments, questions, feedback, and data requests."
]);
Page::Start();
?>

<h1>Contact Information</h1>

<div style="margin:3em">

<p>
	If you have any queries regarding anything on this site or concerning my weather station, please don't hesitate to contact me via email.
	I also welcome general feedback, bug reports, and feature suggestions for the website.</p>

<p>I can also provide data, on request, for use in non-commercial applications.
	I have a very basic API for current data, as well as CSVs since 2009 for all weather variables at hourly and daily intervals.</p>

<p>My address is: &nbsp; <span style="color:blue">bmr[at]nw3weather.co.uk</span></p>

<p>
	For anything informal, feel free to message me on <a href="https://twitter.com/nw3weather/" title="X">Twitter</a>.
</p>

<br />
</div>

<?php Page::End(); ?>
