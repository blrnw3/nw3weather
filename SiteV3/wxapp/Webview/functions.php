<?php
function roundToDp($value, $dp = 1) {
	$dp = '.'.$dp.'f';
	return sprintf("%$dp", $value);
}


function alternateColour($i, $name) {
	$type = ($i % 2 == 0) ? 'light' : 'dark';
	return $name . '-' . $type;
}

function quick_log($txtname, $content) {
	file_put_contents( $GLOBALS['root'].'Logs/'.$txtname, date("H:i:s d/m/Y") . "\t" . $content . "\r\n", FILE_APPEND );
}

function tr( $class = 'table-top' ) {
	$class2 = ( !is_null($class) ) ? ' class="'.$class.'"' : '';
	echo '<tr'.$class2.'>
	';
}
function tr_end() {
	echo '</tr>
		';
}

function td($value, $class = null, $width = false, $colspan = false, $rowspan = false) {
	$class2 = ( !is_null($class) ) ? ' class="'.$class.'"' : '';
	if($width) { $wid = ' width="'.$width.'%"'; }
	if($colspan) { $csp = ' colspan="'.$colspan.'"'; }
	if($rowspan) { $rsp = ' rowspan="'.$rowspan.'"'; }
	echo '<td'.$class2.$wid.$csp.$rsp.'>'.$value.'</td>
            ';
}

function tableHead($text, $colspan = 3) {
	echo '<tr><th colspan="'.$colspan.'">'.$text.'</th></tr>
		';
}

function valcolr($value) {
	$values = array(0,1,5, 10,25,50, 100,250,500, 1000,2500);
	$level_type = 'rain';
	if($value == '') { return 'reportday'; }
	for($i = 0; $i < count($values); $i++){
		if($value <= $values[$i]) { return 'level'.$level_type.'_'.$i;	}
	}
	return 'level'.$level_type.'_'.$i;
}

function secsToReadable($secs) {
	if($secs < 100) {
		return $secs .' s';
	} else {
		$diff = $secs / 60;
	}

	if($diff > 999999) {
		$ago = 'Never';
	} elseif($diff < 100) {
		$ago = round($diff) .' mins';
	} elseif($diff < 3000) {
		$ago = round($diff / 60) .' hours';
	} else {
		$ago = round($diff / 60 / 24) .' days';
	}

	return $ago;
}

function valcolr2($value, $num) {
    $tempvals = array(-5, 0, 5, 10, 15, 20, 25, 30, 35);
    $humivals = array(30, 40, 50, 60, 70, 80, 90, 97);
    $presvals = array(970, 980, 990, 1000, 1010, 1015, 1020, 1030, 1040);
    $windvals = array(1, 2, 4, 7, 10, 15, 20, 30, 40);
    $rainvals = array(0, 0.2, 0.6, 1, 2, 5, 10, 15, 20, 25, 50);
	
    $valcol = array($tempvals, $rainvals, $windvals, $humivals, $presvals);
    $col_descrip = array('temp','rain','wind','humi','pres');
        
    $values = $valcol[$num];
    $level_type = $col_descrip[$num];
	
    if($value == '') {
		return '';
    }
	
    for($i = 0; $i < count($values); $i++) {
		if($value <= $values[$i]) {
			return 'level' . $level_type . '_' . $i;
		}
    }
	
    return 'level' . $level_type . '_' . $i;
}
 ?>