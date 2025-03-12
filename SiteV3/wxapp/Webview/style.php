<?php
//<?php header("Content-type: text/css");
///* CSS file for page */
$width1 = 505;
$width2 = 500;
$widthTagStart = 'width: ';
$widthTagEnd = 'px;';

if($desktop) {
	$width1 = $widthTagStart . $width1 . $widthTagEnd;
	$width2 = $widthTagStart . $width2 . $widthTagEnd;
} else {
	$width1 = $width2 = "";
}

echo '
#background {
	margin-left:auto;
	margin-right:auto;
	margin-top:1px;
	background-color: #D7E2D8;
}

#page {
	margin: 0px auto;
	color: black;
	background-color: white;
	padding: 0;
	'. $width1 .'
	border: 1px solid #0B6121;
	font-size: 9pt;
}

body {
	background-color: rgb(243,242,235);
	font-size: 99%; /* Enables font size scaling in MSIE (apparently) */
}

/******************Header***************************/
#header {
	font-family: arial;
	color: #EFEFFB;
	'. $width2 .'
	background-color:#D35A21;
	font-size: 120%;
	text-align: center;
	padding: 3px;
}

/******************Main***************************/
#main {
	font-family: "Trebuchet MS", verdana, helvetica, arial, sans-serif;
	font-size: 110%;
	'. $width2 .'
	padding: 2px;
	background-color:#F2FAFD;
	margin-left:auto;
	margin-right:auto;
}

#city {
    width: 40%;
}
#country {
    width: 30%;
}
#wxvarName {
    width: 45%;
}
#wxvarRank {
    width: 15%;
}

#main h2 {
	color: #168843;
	font-size: 140%;
	font-weight: bold;
	margin-left: 0.4em;
}

div.tableSubTitle {
	line-height: 2.2em;
	float:left;
	width:90%;
	text-align:center;
}

div.tableHeadSortArrows {
	font-size:80%;
	float:right;
	width:10%;
}

#leftArrow {
	float: left;
}
#rightArrow {
	float: right;
}
#tableTitle {
	line-height: 2em;
	width:35%;
}
.sideArrow {
	font-size: 1.5em;
	width:30%;
	line-height: 1.5em;
}
.sideArrowText {
	font-size: 0.6em;
}

table {
    border: 2px solid #AFB5AF;
    border-spacing: 0px;
    width: 100%;
}

td {
	text-align: center;
        border: solid 1px #CAB99D;
        padding: 4px;
}

th {
	background-color: #BDCDF7;
	font-weight: bold;
	font-size: 123%;
	padding: 0.2em;
}

.table-top {
	background-color: #DBEBEE;
	font-weight: bold;
	font-size: 110%;
}

.row-dark {
	background-color: rgb(233,232,225);
}

.row-light {
	background-color: white;
}
			
.userCity {
	background-color: #ece;
	font-weight: bold;
}

.nonEuroCity {
	background-color: #eeb;
	font-family: Tahoma;
	font-style: italic;	
}

/******************Footer***************************/
#note {
	background-color: #ddd;
	padding: 0.5em;
}

#footer {
	font-family: arial;
	color: #000000;
	'. $width1 .'
	background-color:#F2F5A9;
	text-align: center;
	padding: 3px 0px 3px 0px;
}

#footer a {
	color: black;
	background-color: transparent;
	text-decoration: underline;
	font-weight: bold;
}

#footer a:hover {
	text-decoration: none;
} 
';
?>