<?php
include('../main_tags.php');
if(date("I") == 1): $dst = "BST"; else: $dst = "GMT"; endif;
echo $shr = 'Last Updated: '.$date. ' '. $time. ' '. $dst; 
?>