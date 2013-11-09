<?php
$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/main_tags.php";

include($path);
	if(date("I") == 1): $dst = "BST"; else: $dst = "GMT"; endif;
	echo $shr = 'Last Updated: '.$date. ' '. $time. ' '. $dst; 
?>