<?php
use nw3\app\model\Variable;

//Variable-specific
foreach (Variable::$daily as $var_name => $var) {
	echo ".$var_name {color:{$var['colour']}}\n";
}

//Variable-group-specific
foreach (Variable::$_ as $var) {
	if(!key_exists('thresholds_day', $var)) {
		continue;
	}
	//Check validity
	$thresh_count = count($var['thresholds_day']) + 1; //always one more css-class than threshold
	$threshcol_count = count($var['threshold_colours']);
	$threshtxtcol_count = count($var['threshold_txtcolours']);

	if($thresh_count !== $threshcol_count
		|| $thresh_count !== $threshtxtcol_count
		|| $threshcol_count !== $threshtxtcol_count) {

		die("Bad count for {$var['name']}: $thresh_count $threshcol_count $threshtxtcol_count");
	}


	foreach ($var['threshold_colours'] as $i => $bgcolour) {
		$colour = ($var['threshold_txtcolours'][$i] !== false) ? ";color:#{$var['threshold_txtcolours'][$i]}" : '';
		echo ".{$var['name']}_level_$i {background-color:#$bgcolour$colour}\n";
	}
}

?>
