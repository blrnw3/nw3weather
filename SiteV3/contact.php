<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php
	$file = 0; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
     <title>NW3 Weather Station - Contact</title>

    <meta name="description" content="Contact info for NW3 weather - email or form submission for comments, questions..." />

	<?php require('chead.php'); ?>
	<?php include_once("ggltrack.php") ?>
</head>

<body>
	<!-- ##### Header ##### -->
	<?php require('header.php'); ?>

	<!-- ##### Left Sidebar ##### -->
	<?php require('leftsidebar.php'); ?>


	<!-- ##### Main Copy ##### -->
<div id="main">

<h1>Contact Information</h1>

<div style="margin:3em">

<p>
	If you have any queries regarding anything on this site or concerning my weather station, please don't hesitate to contact me via email.
	I also welcome general feedback, bug reports, and feature suggestions for the website.</p>

<p>I can also provide data, on request, for use in non-commercial applications.
	I have a very basic API for current data, as well as CSVs since 2009 for all weather variables at hourly and daily intervals.</p>

<p>My address is: &nbsp <span style="color:blue"> bmr[at]nw3weather.co.uk</span></p>

<p>
	For anything informal, feel free to message me on <a href="https://twitter.com/nw3weather/" title="X">Twitter</a>.
</p>

<br />
</div>

<!--<p>Alternatively, you may use the form below to send short comments or queries.</p>

<form name="htmlform" method="post" action="contact.php?saved=1">
<table width="450px">
<tr>
<td valign="top"> <label for="name">Name (optional)</label></td>
<td valign="top"> <input  type="text" name="name" maxlength="50" size="30" /> </td>
</tr>
<tr>
<td valign="top"> <label for="email">Email Address (optional)</label> </td>
<td valign="top"> <input  type="text" name="email" maxlength="80" size="30" /> </td>
</tr>
<tr>
<td valign="top"> <label for="comments">Comments (required)</label> </td>
<td valign="top"> <textarea  name="comments" maxlength="1000" cols="30" rows="8"></textarea> </td>
</tr>
<tr>
<td colspan="2" style="text-align:center">  <input type="submit" value="Submit"> </td>
</tr>
</table>
</form>-->

<?php
$saved = $_REQUEST['saved'];
if (false && $saved == 1) {

	$email_to = "blr@nw3weather.co.uk";
	$email_subject = "nw3weather form submission";

	$name = $_POST['name']; // required
	$email_from = isset($_POST['email']) ? $_POST['email'] : 'Unknown';
	$comments = $_POST['comments'];

	$error_message = "";
	$email_exp = '/^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/';
	if (!preg_match($email_exp, $email_from) && strlen($email_from) > 3) {
		$error_message .= 'Dodgy email address - rejected.<br />';
	}

	if (strlen($comments) < 2) {
		$error_message .= 'Comments must be at least 2 characters.<br />';
	}
	if (strlen($error_message) > 0) {
		died($error_message);
	}
	$email_message = "Form details below.\n\n
		Name: " . clean_string($name) . "\n
		Email: " . clean_string($email_from) . "\n
		Comments: " . clean_string($comments) . "\n"
	;

	//create email headers
	$headers = 'Reply-To: ' . $email_from . "\r\n" .
		'X-Mailer: PHP/' . phpversion();
	@mail($email_to, $email_subject, $email_message, $headers);

	showStatusDiv('Comments successfully submitted.', false);
}

function clean_string($string) {
	$bad = array("content-type", "bcc:", "to:", "cc:", "href");
	return str_replace($bad, "", $string);
}

function died($error) {
	showStatusDiv($error .'. Go back and try again?');
	gracefulDie();
}

function gracefulDie() {
	echo '</div>';
	require('footer.php');
	echo ' </body>
		</html>';
	die();
}
?>

</div>

<!-- ##### Footer ##### -->
	<?php require('footer.php'); ?>

  </body>
</html>