<?php header("Content-type: text/css");
	header('Expires: '. date('r', time()+3600*24*100));
?>

/* CSS file for summary and detail historical reports */

<?
//CSS setup
$tempcols = array('0000cc','035CB5','10AFE4', '00FFC4','07EA57','C8FB11', 'FFDE00','FF9933','F65A17', 'DA103C');
$humicols = array('F3E2A9','F7D358','FFBF00', 'D7DF01','A5DF00','74DF00', '31B404','329511','0B6121');
$presscols = $tempcols;
$windcols = array('D9FDFC','B1FFFF','66FF94', '99FF00','99CC00','CCCC00', 'FFCC00','FF9900','FF6600', 'FF0000');
$degrcols = array('F6CECE','F6E3CE','F5F6CE', 'E3F6CE','CEF6D8','CED8F6', 'CEB3FA','F6CEE3');
$raincols = array('94939A','918AA7','CCFFFF', '99FFCC','9EDFFD','9AACFF', '7980FF','3F48F9','010EFE', '050EAB','050D97','0B0B3B');
$rtmxcols = $raincols;
$tchrcols = $tempcols;
$hchrcols = $humicols;
$prngcols = $tempcols;
$dhrscols = array('888','F5E2A9','F1D787', 'F2C181','F4B462','E9A245', 'F2A86B','EE9348','DC7B2B', 'EB8965','E46B3F','D85A3A');

$temptxts = array('white','white',false, false,false,false, false,false,'white', 'white');
$humitxts = array(false,false,false, false,false,false, false,false, "#C4C9C2");
$presstxts = $temptxts;
$windtxts = array(false,false,false, false,false,false, false,false,false, "#C4C9C2");
$degrtxts = array(false,false,false, false,false,false, false,false);
$raintxts = array(false,false,false, false,false,false, false,'white','white', 'white','white','white');
$rtmxtxts = $raintxts;
$tchrtxts = $temptxts;
$hchrtxts = $humitxts;
$prngtxts = $temptxts;
$dhrstxts = array(false,false,false, false,false,false, false,false,false, false,'white','white');

$valcolcol = array($tempcols,$humicols,$presscols, $windcols,$degrcols,$raincols, $rtmxcols,$tchrcols,$hchrcols, $prngcols,$dhrscols);
$valcoltxt = array($temptxts,$humitxts,$presstxts, $windtxts,$degrtxts,$raintxts, $rtmxtxts,$tchrtxts,$hchrtxts, $prngtxts,$dhrstxts);
$col_descrip = array('temp','humi','press', 'wind','degr','rain', 'rtmax','tchg','hchg', 'prng','dhrs');

for($i = 0; $i < count($valcolcol); $i++) {
	for($j = 0; $j < count($valcolcol[$i]); $j++) {
		echo '.level'.$col_descrip[$i].'_'.$j.' { background-color: #'.$valcolcol[$i][$j]. ';';
		if($valcoltxt[$i][$j]) {
			echo ' color: ' . $valcoltxt[$i][$j]. ';';
		}
		echo ' border: 1px solid #6C8288; text-align: center; }
			';
	}
}

?>

.labels {
	background-color: #666666;
	color: #cccccc;
	border: 1px solid #222222;
	text-align: center;
	padding: 2px;
	font-size: 110%;
}

.test {
	font-size: 120%;
	color: green;
	font-weight: bold;
}

.large {
	font-size: 120%;
}

.rep {
	font-size: 130%;
	color: #8904B1;
	font-weight: bold;
}


.labels2 {
	background-color: #666666;
	color: #cccccc;
	border: 1px solid #222222;
	text-align: center;
	font-size: 110%;
	padding: 2px;
	border-left: 2px solid #2A0A1B;
}

.tableheading {
	background-color: #A587A4;
	color: black;
	border: 1px solid #222222;
	text-align: center;
	padding: 2px;
	font-size: 150%;
	font-weight: bold;
}

.title {
	color: #B43104;
	text-align: center;
	padding: 2px;
	font-size: 170%;
	font-weight: bold;
}

.reportttl {
	color: black;
	background-color: #DBEBEE;
	text-align: center;
	font-weight: bold;
	font-size: 110%;
}

.reportttl2 {
	border: 1px solid #222222;
	border-top: 4px ridge black;
}

.reportday {
	background-color: #ccc;
	border: 1px solid #6C8288;
	text-align: center;
	padding: 2px;
}

.raintr { background-color: #CECEF6; }

.reportday2 {
	border: 1px solid #6C8288;
	text-align: center;
	padding: 2px;
	background-color: #A4A4A4;
	border-right: 2px solid #2A0A1B;
}

.getreportdtbxfloat {
	float: right;
	padding: 0px 5px 0px 5px;
	border: 1px ridge #6C8288;
	background-color: #666666;
	color: #FFFFFF;
}

.getreportdtbx {
	font-size: 100%;
	font-weight: bold;
	padding: 0px;
	text-align: center;
}

.dev {
	padding: 5px 0px 10px 0px;
	font-size: 75%;
}

.separator {
	background-color: #ffffff;
}


.yeartotals {
	border: 1px solid #222222;
	text-align: center;
	background-color: #CCFFbb;
	font-weight: bold;
	font-size: 99%;
}

.noday {
	background-color: #DEDEDE;
	background-repeat: repeat-x;
}

.button {
	font-family: arial, verdana, ms sans serif;
	font-weight: bold;
	font-size: 9px;
	width: 65px;
	height: 38px;
	vertical-align: middle;
	padding: 0px;

}

.infotext {
	background-color: #ccffff;
	font-weight: bold;
	border: 1px solid #666666;
}