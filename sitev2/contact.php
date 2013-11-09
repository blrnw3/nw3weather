<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("main_tags.php");
	$file = 30; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
     <title>NW3 Weather - Old(v2) Station - Contact</title>

    <meta name="description" content="Old v2 - Contact info for NW3 weather - email or form submission for comments, questions..." />

	<? require('chead.php'); ?>
	<?php include_once("ggltrack.php") ?> 
</head>

<body>
	<!-- For non-visual user agents: -->
	<div id="top"><a href="#main-copy" class="doNotDisplay doNotPrint">Skip to main content.</a></div>

	<!-- ##### Header ##### -->
	<? require('header.php'); ?>

	<!-- ##### Left Sidebar ##### -->
	<? require('leftsidebar.php'); ?>


	<!-- ##### Main Copy ##### -->
<div id="main-copy">
	
<h1>Contact Information</h1>

If you have any queries regarding anything on this site or concerning my weather station, please don't hesitate to contact me via email.
<p>My address is: &nbsp <span style="color:blue"> blr@nw3weather.co.uk</span></p>

<br>

<p>Alternatively, you may use the form below to send comments or queries.</p>

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
</form>

 <?php
$saved = $_REQUEST['saved'];
 if ($saved == 1){
$_POST['email'];
	
	$email_to = "blr@nw3weather.co.uk";
	$email_subject = "Website form submission";
	
	function died($error) {
		//  error code
		echo "Sorry, but there were errors found with the form you submitted! ";
		echo "These errors appear below.<br /><br />";
		echo $error."<br /><br />";
		echo "Please go back and fix these errors.<br /><br />";
		die();
	}

	// validation expected data exists
	if(!isset($_POST['comments'])) {
		died('We are sorry, but there appears to be a problem with the form you submitted.');      
	}

	$name = $_POST['name']; // required
	$email_from = $_POST['email'];
	$comments = $_POST['comments'];
	if(!isset($_POST['email'])) { $email_from = 'Unknown website user'; }
	
	$error_message = "";
	$email_exp = '/^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/';
if(!preg_match($email_exp,$email_from) && strlen($email_from) > 3) {
	$error_message .= 'The Email Address you entered does not appear to be valid.<br />';
}

if(strlen($comments) < 2) {
	$error_message .= 'The Comments you entered must be longer.<br />';
}
if(strlen($error_message) > 0) {
	died($error_message);
}
	$email_message = "Form details below.\n\n";

	function clean_string($string) {
	$bad = array("content-type","bcc:","to:","cc:","href");
	return str_replace($bad,"",$string);
	}

	$email_message .= "Name: ".clean_string($name)."\n";
	$email_message .= "Email: ".clean_string($email_from)."\n";
	$email_message .= "Comments: ".clean_string($comments)."\n";

// create email headers
$headers = 'From: '.$email_from."\r\n".
'Reply-To: '.$email_from."\r\n" .
'X-Mailer: PHP/' . phpversion();
@mail($email_to, $email_subject, $email_message, $headers); 

echo 'Success!';
}
?>

</div>

<!-- ##### Footer ##### -->
	<? require('footer.php'); ?>

  </body>
</html>