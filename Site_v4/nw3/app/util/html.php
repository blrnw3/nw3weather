<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UtilHtml
 *
 * @author Ben LR
 */
class UtilHtml {
	//put your code here
	 static function echoln($str) {
		 echo $str + "<br />";
	 }

	 static function tr() {
		 echo '<tr>';
	 }
	 static function tr_() {
		 echo '</tr>';
	 }

	 static function table() {
		 echo '<table>';
	 }
	 static function table_() {
		 echo '</table>';
	 }

	 static function print_m($var) {
		echo '<pre>';
		var_dump($var);
		echo '</pre>';
	}

	static function out($str) {
		echo $str . '<br />';
	}
}

?>
