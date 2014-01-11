<?php
namespace nw3\app\model;

use nw3\config\Admin;
use nw3\app\util\String;

/**
 * Contact form submission
 *
 * @author Ben LR
 */
class Contact {

	static function validate($form) {
		$response = array(
			'success' => true,
			'message' => ''
		);

		$name = $form['name'];
		$comments = $form['comments'];
		$email_from = isset($form['email']) ? $form['email'] : 'Unknown';
		$spam_answer = $form['spam'];

		$error_message = '';
		$email_exp = '/^[A-Za-z0-9._%-\+]+@[A-Za-z0-9.-]+\.[A-Za-z]+$/';
		if(!preg_match($email_exp, $email_from) && String::isNotBlank($email_from)) {
			$error_message .= 'Dodgy email address - try again.<br />';
		}
		if(strlen($comments) < 2) {
			$error_message .= 'Comments must be at least 2 characters.<br />';
		}
		if(strtolower($spam_answer) !== 'rain') {
			$error_message .= 'Spam prevention answer wrong! Hint: it falls from the sky in drops.';
		}

		if(String::isNotBlank($error_message)) {
			$response['success'] = false;
			$response['message'] = $error_message;
		} else {
			$response['message'] = "Comments successfully submitted. Thanks $name";

			$email_message = "Form details below.\n\n
				Name: " . self::clean_string($name) . "\n
				Email: " . self::clean_string($email_from) . "\n
				Comments: " . self::clean_string($comments) . "\n"
			;
			$email_subject = "nw3weather form submission";
			//email headers
			$headers = 'From: ' . $email_from . "\r\n" .
				'Reply-To: ' . $email_from . "\r\n" .
				'X-Mailer: PHP/' . phpversion();
			//send email
			if(!@mail(Admin::EMAIL_CONTACT, $email_subject, $email_message, $headers)) {
				$response['success'] = false;
				$response['message'] = "Unknown server error. Check your email then try again, or send me an email directly.";
			}
		}

		return $response;
	}

	private static function clean_string($string) {
		$bad = array("content-type", "bcc:", "to:", "cc:", "href");
		return str_replace($bad, "", $string);
	}

}

?>
