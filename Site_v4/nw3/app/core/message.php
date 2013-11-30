<?php
namespace nw3\app\core;

use nw3\config\Admin;

/**
 * For showing messages on the UI (flash and site status)
 * @author Ben LR
 */
class Message {

	static function get_status() {
		if(Admin::SHOW_MESSAGE) {
			self::showStatusDiv(Admin::STATUS_MESSAGE, false);
		}

		//Bad browser warning
		if(preg_match("/.*MSIE [5|6|7].*/", $browser)) {
			showStatusDiv('You are using a browser ('.$browser.') that is not compatible with nw3weather. Browse at your peril!
				Also, <a href="http://www.updatebrowser.net/">consider upgrading</a>.');
			log_events("BadBrowser.txt");
		}
		//echo $browser;

		//Start old data warnings
		$message = "A local hardware fault has been detected and is preventing data updates -
			the site administrator has been notified and will investigate ASAP.";
		$message2 = 'Planned system maintenance is taking place - updates will resume shortly';

		$diff = sysWDtimes();
		echo "<!-- WDAge: $diff -->";

		$planned = false; //Change to true if maintenance planned
		if($planned) { showStatusDiv($message2); }

		if(!$planned && $diff > 900) { // 15mins downtime before alert message is triggered
			showStatusDiv( $message .' &nbsp;
			   System time: '. date('d/m/y, H:i T', $timestampWD) .
			   '; &nbsp; Server time: '. date('d/m/y, H:i T') .
			   '<br />Problem Age: '. round($diff/60) .' mins ('. round($diff/3600). ' hours)'
			);
			$mail5 = true;
		}

	}

	/**
	* Echos a full-width padded divison
	* @param String $message message to show
	* @param bool $isWarning [=true] set false to show the info-style instead of the default red warning-style
	*/
   static function showStatusDiv($message, $isWarning = true) {
	   $messageType = $isWarning ? 'warning' : 'info';
	   echo "<div class='statusBox $messageType'>$message</div>";
   }

}

?>
