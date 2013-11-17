<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UtilString
 *
 * @author Ben LR
 */
class UtilString {
	//put your code here
	static function contains($search, $find) {
		return strpos($search, $find) !== false;
	}
}

?>
