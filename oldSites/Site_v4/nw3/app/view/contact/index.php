<?php
use nw3\app\util\Html;

?>

<h1>Contact Information</h1>

If you have any queries regarding anything on this site or concerning my weather station, please don't hesitate to contact me via email.
I also welcome general feedback, bug reports, and feature suggestions for the next site version.
<p>My address is: &nbsp <span id="email_address"> blr[at][name_of_this_website]</span></p>

<p>Alternatively, you may use the form below to send short comments or queries.</p>

<form id="contact_form" action="#htmlform">
	<table>
		<tr>
			<td valign="top"> <label for="name">Name (optional)</label></td>
			<td valign="top"> <input type="text" name="name" maxlength="50" size="30" /> </td>
		</tr>
		<tr>
			<td valign="top"> <label for="email">Email Address (optional)</label> </td>
			<td valign="top"> <input type="text" name="email" maxlength="80" size="30" /> </td>
		</tr>
		<tr>
			<td valign="top"> <label for="comments">Comments (required)</label> </td>
			<td valign="top"> <textarea  name="comments" maxlength="3000" cols="40" rows="7"></textarea> </td>
		</tr>
		<tr style='display: none'>
			<td valign="top"> <label for="spam">Spam prevention<br />(required)</label> </td>
			<td valign="top">What is a common form of liquid precipitation? <input type='text' name="spam" maxlength="4" size="4" /> </td>
		</tr>
		<tr>
			<td style="text-align:right">  <input id='form_submit' type="submit" value="Submit"> </td>
			<td id='form_loading'>&nbsp;</td>
		</tr>
	</table>
</form>

<div id='submit_response_message' class='statusBox'></div>

<script type="text/javascript">
$(document).ready(function() {
	$('#email_address').html(function(){
		return $(this).text()
			.replace('[at]', '@')
			.replace('[name_of_this_website]', 'nw3weather.co.uk');
	});

	$('#form_submit').click(function(e){
		e.preventDefault();
		$('#form_loading').addClass('loading');
		$("#submit_response_message").html('');
		$.ajax({
			url: "contact/submit",
			type: "POST",
			data: $("#contact_form").serialize[],
			dataType: "json",
			cache: false,
			success: function (data) {
				$('#submit_response_message')
					.html(data.message)
					.toggleClass('info', data.success)
					.toggleClass('warning', !data.success);
				$('#form_loading').removeClass('loading');
			}
		});
	});
});
</script>
