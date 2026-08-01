<?php header("Content-type: text/css"); ?>

/* CSS file for coloured table data */

<?
//CSS setup
$tempcols = array('000074','0000cc','035CB5','10AFE4', '00FFC4','07EA57','C8FB11', 'FFDE00','FF9933','F65A17', 'DA103C');
$humicols = array('F3E2A9','F7D358','FFBF00', 'D7DF01','A5DF00','74DF00', '31B404','329511','0B6121');
$presscols = $tempcols;
$windcols = array('D9FDFC','B1FFFF','66FF94', '99FF00','99CC00','CCCC00', 'FFCC00','FF9900','FF6600', 'FF0000');
$raincols = array('94939A','918AA7','CCFFFF', '99FFCC','9EDFFD','9AACFF', '7980FF','3F48F9','010EFE', '050EAB','050D97','0B0B3B');
$condcols = array('efe','ded','cdc', 'bcb','aba','9a9', '898','787','676', '565');

$temptxts = array('white','white','white',false, false,false,false, false,false,'white', 'white');
$humitxts = array(false,false,false, false,false,false, false,false, "#C4C9C2");
$presstxts = $temptxts;
$windtxts = array(false,false,false, false,false,false, false,false,false, "#C4C9C2");
$raintxts = array(false,false,false, false,false,false, false,'white','white', 'white','white','white');
$condtxts = array(false,false,false, false,false,'blue', '#a4DcF9','white','white', 'white');

$valcolcol = array($tempcols, $raincols, $windcols, $humicols, $presscols, $condcols);
$valcoltxt = array($temptxts, $raintxts, $windtxts, $humitxts, $presstxts, $condtxts);

$col_descrip = array('temp','rain','wind','humi','pres','cond');

for($i = 0; $i < count($valcolcol); $i++) {
	for($j = 0; $j < count($valcolcol[$i]); $j++) {
		echo '.level'.$col_descrip[$i].'_'.$j.' { background-color: #'.$valcolcol[$i][$j]. ';';
		if($valcoltxt[$i][$j]) {
			echo ' color: ' . $valcoltxt[$i][$j];
		}
		echo ' }
                    ';
	}
}		
?>