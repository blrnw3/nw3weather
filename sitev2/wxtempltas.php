<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("main_tags.php");
	$file = 201; ?>

<html xmlns:v="urn:schemas-microsoft-com:vml"
xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:x="urn:schemas-microsoft-com:office:excel"
xmlns="http://www.w3.org/TR/REC-html40">

<head>

<title>NW3 Weather - Old(v2) - LTA temperature detail</title>

	<meta name="description" content="Old v2 - Long-term climate/weather average/mean temperatures day-by-day for London, NW3" />

	<?php require('chead.php'); ?>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">

<style>
<!--table
	{mso-displayed-decimal-separator:"\.";
	mso-displayed-thousand-separator:"\,";}
@page
	{margin:1.0in .75in 1.0in .75in;
	mso-header-margin:.5in;
	mso-footer-margin:.5in;}
tr
	{mso-height-source:auto;}
col
	{mso-width-source:auto;}
br
	{mso-data-placement:same-cell;}
.style0
	{mso-number-format:General;
	text-align:general;
	vertical-align:bottom;
	white-space:nowrap;
	mso-rotate:0;
	mso-background-source:auto;
	mso-pattern:auto;
	color:windowtext;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Arial;
	mso-generic-font-family:auto;
	mso-font-charset:0;
	border:none;
	mso-protection:locked visible;
	mso-style-name:Normal;
	mso-style-id:0;}
td
	{mso-style-parent:style0;
	padding:0px;
	mso-ignore:padding;
	color:windowtext;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Arial;
	mso-generic-font-family:auto;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:general;
	vertical-align:bottom;
	border:none;
	mso-background-source:auto;
	mso-pattern:auto;
	mso-protection:locked visible;
	white-space:nowrap;
	mso-rotate:0;}
.xl26
	{mso-style-parent:style0;
	text-align:left;
	vertical-align:middle;}
.xl27
	{mso-style-parent:style0;
	text-align:center;
	vertical-align:middle;
	border-top:.5pt solid windowtext;
	border-right:none;
	border-bottom:.5pt solid windowtext;
	border-left:.5pt solid windowtext;
	background:#E6DFCF;
	mso-pattern:auto none;}
.xl28
	{mso-style-parent:style0;
	text-align:center;
	vertical-align:middle;
	border-top:.5pt solid windowtext;
	border-right:.5pt solid windowtext;
	border-bottom:.5pt solid windowtext;
	border-left:none;
	background:#E6DFCF;
	mso-pattern:auto none;}
.xl29
	{mso-style-parent:style0;
	color:blue;
	font-weight:700;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	text-align:center;
	vertical-align:middle;
	border-top:.5pt solid windowtext;
	border-right:.5pt solid silver;
	border-bottom:.5pt solid windowtext;
	border-left:none;
	background:#E6DFCF;
	mso-pattern:auto none;
	white-space:normal;}
.xl30
	{mso-style-parent:style0;
	color:#FF6600;
	font-weight:700;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	text-align:center;
	vertical-align:middle;
	border-top:.5pt solid windowtext;
	border-right:.5pt solid silver;
	border-bottom:.5pt solid windowtext;
	border-left:.5pt solid silver;
	background:#E6DFCF;
	mso-pattern:auto none;
	white-space:normal;}
.xl31
	{mso-style-parent:style0;
	color:purple;
	font-weight:700;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	text-align:center;
	vertical-align:middle;
	border-top:.5pt solid windowtext;
	border-right:.5pt solid silver;
	border-bottom:.5pt solid windowtext;
	border-left:.5pt solid silver;
	background:#E6DFCF;
	mso-pattern:auto none;
	white-space:normal;}
.xl32
	{mso-style-parent:style0;
	color:#339966;
	font-weight:700;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	mso-number-format:"0\.0";
	text-align:center;
	vertical-align:middle;
	border-top:.5pt solid windowtext;
	border-right:.5pt solid windowtext;
	border-bottom:.5pt solid windowtext;
	border-left:.5pt solid silver;
	background:#E6DFCF;
	mso-pattern:auto none;
	white-space:normal;}
.xl33
	{mso-style-parent:style0;
	font-weight:700;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	text-align:center;
	vertical-align:middle;
	border-top:none;
	border-right:.5pt solid silver;
	border-bottom:.5pt solid silver;
	border-left:.5pt solid windowtext;}
.xl34
	{mso-style-parent:style0;
	text-align:center;
	vertical-align:middle;
	border-top:none;
	border-right:.5pt solid windowtext;
	border-bottom:.5pt solid silver;
	border-left:.5pt solid silver;}
.xl35
	{mso-style-parent:style0;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	mso-number-format:"0\.0";
	text-align:center;
	vertical-align:middle;
	border-top:none;
	border-right:.5pt solid silver;
	border-bottom:.5pt solid silver;
	border-left:none;}
.xl36
	{mso-style-parent:style0;
	mso-number-format:"0\.0";
	text-align:center;
	vertical-align:middle;
	border-top:none;
	border-right:.5pt solid silver;
	border-bottom:.5pt solid silver;
	border-left:.5pt solid silver;}
.xl37
	{mso-style-parent:style0;
	mso-number-format:"0\.0";
	text-align:center;
	vertical-align:middle;
	border-top:none;
	border-right:.5pt solid windowtext;
	border-bottom:.5pt solid silver;
	border-left:.5pt solid silver;}
.xl38
	{mso-style-parent:style0;
	font-weight:700;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	text-align:center;
	vertical-align:middle;
	border-top:.5pt solid silver;
	border-right:.5pt solid silver;
	border-bottom:.5pt solid silver;
	border-left:.5pt solid windowtext;}
.xl39
	{mso-style-parent:style0;
	text-align:center;
	vertical-align:middle;
	border-top:.5pt solid silver;
	border-right:.5pt solid windowtext;
	border-bottom:.5pt solid silver;
	border-left:.5pt solid silver;}
.xl40
	{mso-style-parent:style0;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	mso-number-format:"0\.0";
	text-align:center;
	vertical-align:middle;
	border-top:.5pt solid silver;
	border-right:.5pt solid silver;
	border-bottom:.5pt solid silver;
	border-left:none;}
.xl41
	{mso-style-parent:style0;
	mso-number-format:"0\.0";
	text-align:center;
	vertical-align:middle;
	border:.5pt solid silver;}
.xl42
	{mso-style-parent:style0;
	mso-number-format:"0\.0";
	text-align:center;
	vertical-align:middle;
	border-top:.5pt solid silver;
	border-right:.5pt solid windowtext;
	border-bottom:.5pt solid silver;
	border-left:.5pt solid silver;}
.xl43
	{mso-style-parent:style0;
	font-weight:700;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	text-align:center;
	vertical-align:middle;
	border-top:.5pt solid silver;
	border-right:.5pt solid silver;
	border-bottom:.5pt solid windowtext;
	border-left:.5pt solid windowtext;}
.xl44
	{mso-style-parent:style0;
	text-align:center;
	vertical-align:middle;
	border-top:.5pt solid silver;
	border-right:.5pt solid windowtext;
	border-bottom:.5pt solid windowtext;
	border-left:.5pt solid silver;}
.xl45
	{mso-style-parent:style0;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	mso-number-format:"0\.0";
	text-align:center;
	vertical-align:middle;
	border-top:.5pt solid silver;
	border-right:.5pt solid silver;
	border-bottom:.5pt solid windowtext;
	border-left:none;}
.xl46
	{mso-style-parent:style0;
	mso-number-format:"0\.0";
	text-align:center;
	vertical-align:middle;
	border-top:.5pt solid silver;
	border-right:.5pt solid silver;
	border-bottom:.5pt solid windowtext;
	border-left:.5pt solid silver;}
.xl47
	{mso-style-parent:style0;
	mso-number-format:"0\.0";
	text-align:center;
	vertical-align:middle;
	border-top:.5pt solid silver;
	border-right:.5pt solid windowtext;
	border-bottom:.5pt solid windowtext;
	border-left:.5pt solid silver;}
.xl48
	{mso-style-parent:style0;
	font-weight:700;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	text-align:center;
	vertical-align:middle;
	border-top:none;
	border-right:.5pt solid silver;
	border-bottom:.5pt solid silver;
	border-left:.5pt solid windowtext;
	background:#F3F3EB;
	mso-pattern:auto none;}
.xl49
	{mso-style-parent:style0;
	text-align:center;
	vertical-align:middle;
	border-top:none;
	border-right:.5pt solid windowtext;
	border-bottom:.5pt solid silver;
	border-left:.5pt solid silver;
	background:#F3F3EB;
	mso-pattern:auto none;}
.xl50
	{mso-style-parent:style0;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	mso-number-format:"0\.0";
	text-align:center;
	vertical-align:middle;
	border-top:none;
	border-right:.5pt solid silver;
	border-bottom:.5pt solid silver;
	border-left:none;
	background:#F3F3EB;
	mso-pattern:auto none;}
.xl51
	{mso-style-parent:style0;
	mso-number-format:"0\.0";
	text-align:center;
	vertical-align:middle;
	border-top:none;
	border-right:.5pt solid silver;
	border-bottom:.5pt solid silver;
	border-left:.5pt solid silver;
	background:#F3F3EB;
	mso-pattern:auto none;}
.xl52
	{mso-style-parent:style0;
	mso-number-format:"0\.0";
	text-align:center;
	vertical-align:middle;
	border-top:none;
	border-right:.5pt solid windowtext;
	border-bottom:.5pt solid silver;
	border-left:.5pt solid silver;
	background:#F3F3EB;
	mso-pattern:auto none;}
.xl53
	{mso-style-parent:style0;
	font-weight:700;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	text-align:center;
	vertical-align:middle;
	border-top:.5pt solid silver;
	border-right:.5pt solid silver;
	border-bottom:.5pt solid silver;
	border-left:.5pt solid windowtext;
	background:#F3F3EB;
	mso-pattern:auto none;}
.xl54
	{mso-style-parent:style0;
	text-align:center;
	vertical-align:middle;
	border-top:.5pt solid silver;
	border-right:.5pt solid windowtext;
	border-bottom:.5pt solid silver;
	border-left:.5pt solid silver;
	background:#F3F3EB;
	mso-pattern:auto none;}
.xl55
	{mso-style-parent:style0;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	mso-number-format:"0\.0";
	text-align:center;
	vertical-align:middle;
	border-top:.5pt solid silver;
	border-right:.5pt solid silver;
	border-bottom:.5pt solid silver;
	border-left:none;
	background:#F3F3EB;
	mso-pattern:auto none;}
.xl56
	{mso-style-parent:style0;
	mso-number-format:"0\.0";
	text-align:center;
	vertical-align:middle;
	border:.5pt solid silver;
	background:#F3F3EB;
	mso-pattern:auto none;}
.xl57
	{mso-style-parent:style0;
	mso-number-format:"0\.0";
	text-align:center;
	vertical-align:middle;
	border-top:.5pt solid silver;
	border-right:.5pt solid windowtext;
	border-bottom:.5pt solid silver;
	border-left:.5pt solid silver;
	background:#F3F3EB;
	mso-pattern:auto none;}
.xl58
	{mso-style-parent:style0;
	font-weight:700;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	text-align:center;
	vertical-align:middle;
	border-top:.5pt solid silver;
	border-right:.5pt solid silver;
	border-bottom:.5pt solid windowtext;
	border-left:.5pt solid windowtext;
	background:#F3F3EB;
	mso-pattern:auto none;}
.xl59
	{mso-style-parent:style0;
	text-align:center;
	vertical-align:middle;
	border-top:.5pt solid silver;
	border-right:.5pt solid windowtext;
	border-bottom:.5pt solid windowtext;
	border-left:.5pt solid silver;
	background:#F3F3EB;
	mso-pattern:auto none;}
.xl60
	{mso-style-parent:style0;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	mso-number-format:"0\.0";
	text-align:center;
	vertical-align:middle;
	border-top:.5pt solid silver;
	border-right:.5pt solid silver;
	border-bottom:.5pt solid windowtext;
	border-left:none;
	background:#F3F3EB;
	mso-pattern:auto none;}
.xl61
	{mso-style-parent:style0;
	mso-number-format:"0\.0";
	text-align:center;
	vertical-align:middle;
	border-top:.5pt solid silver;
	border-right:.5pt solid silver;
	border-bottom:.5pt solid windowtext;
	border-left:.5pt solid silver;
	background:#F3F3EB;
	mso-pattern:auto none;}
.xl62
	{mso-style-parent:style0;
	mso-number-format:"0\.0";
	text-align:center;
	vertical-align:middle;
	border-top:.5pt solid silver;
	border-right:.5pt solid windowtext;
	border-bottom:.5pt solid windowtext;
	border-left:.5pt solid silver;
	background:#F3F3EB;
	mso-pattern:auto none;}
.xl63
	{mso-style-parent:style0;
	font-weight:700;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	text-align:center;
	vertical-align:middle;}
.xl64
	{mso-style-parent:style0;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	mso-number-format:"0\.0";
	text-align:center;
	vertical-align:middle;}
.xl65
	{mso-style-parent:style0;
	mso-number-format:"0\.0";
	text-align:center;
	vertical-align:middle;}
-->
</style>

	<?php include_once("ggltrack.php") ?>
</head>

<body>

	<!-- For non-visual user agents: -->
	<div id="top"><a href="#main-copy" class="doNotDisplay doNotPrint">Skip to main content.</a></div>

<!-- ##### Header ##### -->
	<? require('header.php'); ?>

	<!-- ##### Left Sidebar ##### -->
	<? require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->

	<div align="left" id="main-copy">
<?php if($unitT == 'F') { echo '<b>Sorry, unit conversion not yet available for this page</b>'; } ?>
<h1>Daily long-term temperature averages / &deg;C</h1>

<p>These figure are based on the monthly values, but the detail of the intra-month progression is derived from analysis of
<acronym title="Central England Temperature: The CET series is the longest continuous temperature record in the world">CET</acronym>
figures from the last hundred years. The data has been deliberatley smoothed to provide a more realistic basis for anomalies.<br />
The raw CET data can be viewed here: <a href="CETanalysis.xls" title="Excel 2003 spreadsheet with raw CET data from 1900-2010">CETanalysis</a> (.xls, 2.70 MB)
<br /><br />
<a href="#graph" title="Click to view graph"> Jump to summary graph</a></p>

<table x:str border="0" cellpadding="0" cellspacing="0" width="1135" style='border-collapse:collapse;table-layout:fixed;width:854pt'>
<tr height="17" style='height:12.75pt'>
<td height="17" class=xl26 style='height:12.75pt'></td>
<td class=xl27>&nbsp;</td>
<td class=xl28>&nbsp;</td>
<td class=xl29 width=64 style='width:48pt'>Min</td>
<td class=xl30 width=64 style='border-left:none;width:48pt'>Max</td>
<td class=xl31 width=64 style='border-left:none;width:48pt'>Mean</td>
<td class=xl32 width=64 style='border-left:none;width:48pt'>Range</td>
<td class=xl26></td>
<td class=xl27>&nbsp;</td>
<td class=xl28>&nbsp;</td>
<td class=xl29 width=64 style='width:48pt'>Min</td>
<td class=xl30 width=64 style='border-left:none;width:48pt'>Max</td>
<td class=xl31 width=64 style='border-left:none;width:48pt'>Mean</td>
<td class=xl32 width=64 style='border-left:none;width:48pt'>Range</td>
<td class=xl26></td>
<td class=xl27>&nbsp;</td>
<td class=xl28>&nbsp;</td>
<td class=xl29 width=64 style='width:48pt'>Min</td>
<td class=xl30 width=64 style='border-left:none;width:48pt'>Max</td>
<td class=xl31 width=64 style='border-left:none;width:48pt'>Mean</td>
<td class=xl32 width=64 style='border-left:none;width:48pt'>Range</td>
</tr><tr><td></td>
<td rowspan=31 class=xl33 style='border-bottom:.5pt solid black'>Jan</td>
<td class=xl34 style='border-left:none' >1</td>
<td class=xl35 >2.6</td>
<td class=xl36 style='border-left:none' >7.0</td>
<td class=xl36 style='border-left:none' >4.8</td>
<td class=xl37 style='border-left:none' >4.4</td>
<td class=xl26></td>
<td rowspan=28 class=xl48 style='border-bottom:.5pt solid black'>Feb</td>
<td class=xl49 style='border-left:none' >1</td>
<td class=xl50 >2.4</td>
<td class=xl51 style='border-left:none' >7.1</td>
<td class=xl51 style='border-left:none' >4.7</td>
<td class=xl52 style='border-left:none' >4.7</td>
<td class=xl26></td>
<td rowspan=31 class=xl33 style='border-bottom:.5pt solid black'>Mar</td>
<td class=xl34 style='border-left:none' >1</td>
<td class=xl35 >2.6</td>
<td class=xl36 style='border-left:none' >8.9</td>
<td class=xl36 style='border-left:none' >5.8</td>
<td class=xl37 style='border-left:none' >6.3</td>
</tr><tr><td></td>
<td class=xl39 >2</td>
<td class=xl40>2.6</td>
<td class=xl41 >7.0</td>
<td class=xl41 >4.8</td>
<td class=xl42 >4.5</td>
<td class=xl26></td>
<td class=xl54 >2</td>
<td class=xl55>2.4</td>
<td class=xl56 >7.1</td>
<td class=xl56 >4.7</td>
<td class=xl57 >4.7</td>
<td class=xl26></td>
<td class=xl39 >2</td>
<td class=xl40>2.7</td>
<td class=xl41 >9.0</td>
<td class=xl41 >5.8</td>
<td class=xl42 >6.3</td>
</tr><tr><td></td>
<td class=xl39 >3</td>
<td class=xl40>2.6</td>
<td class=xl41 >7.0</td>
<td class=xl41 >4.8</td>
<td class=xl42 >4.5</td>
<td class=xl26></td>
<td class=xl54 >3</td>
<td class=xl55>2.4</td>
<td class=xl56 >7.1</td>
<td class=xl56 >4.7</td>
<td class=xl57 >4.8</td>
<td class=xl26></td>
<td class=xl39 >3</td>
<td class=xl40>2.7</td>
<td class=xl41 >9.1</td>
<td class=xl41 >5.9</td>
<td class=xl42 >6.4</td>
</tr><tr><td></td>
<td class=xl39 >4</td>
<td class=xl40>2.5</td>
<td class=xl41 >7.0</td>
<td class=xl41 >4.8</td>
<td class=xl42 >4.5</td>
<td class=xl26></td>
<td class=xl54 >4</td>
<td class=xl55>2.4</td>
<td class=xl56 >7.2</td>
<td class=xl56 >4.8</td>
<td class=xl57 >4.8</td>
<td class=xl26></td>
<td class=xl39 >4</td>
<td class=xl40>2.7</td>
<td class=xl41 >9.2</td>
<td class=xl41 >5.9</td>
<td class=xl42 >6.5</td>
</tr><tr><td></td>
<td class=xl39 >5</td>
<td class=xl40>2.5</td>
<td class=xl41 >7.0</td>
<td class=xl41 >4.8</td>
<td class=xl42 >4.5</td>
<td class=xl26></td>
<td class=xl54 >5</td>
<td class=xl55>2.4</td>
<td class=xl56 >7.3</td>
<td class=xl56 >4.8</td>
<td class=xl57 >5.0</td>
<td class=xl26></td>
<td class=xl39 >5</td>
<td class=xl40>2.8</td>
<td class=xl41 >9.4</td>
<td class=xl41 >6.1</td>
<td class=xl42 >6.6</td>
</tr><tr><td></td>
<td class=xl39 >6</td>
<td class=xl40>2.5</td>
<td class=xl41 >7.0</td>
<td class=xl41 >4.8</td>
<td class=xl42 >4.5</td>
<td class=xl26></td>
<td class=xl54 >6</td>
<td class=xl55>2.4</td>
<td class=xl56 >7.4</td>
<td class=xl56 >4.9</td>
<td class=xl57 >5.0</td>
<td class=xl26></td>
<td class=xl39 >6</td>
<td class=xl40>2.8</td>
<td class=xl41 >9.5</td>
<td class=xl41 >6.1</td>
<td class=xl42 >6.6</td>
</tr><tr><td></td>
<td class=xl39 >7</td>
<td class=xl40>2.5</td>
<td class=xl41 >7.0</td>
<td class=xl41 >4.8</td>
<td class=xl42 >4.6</td>
<td class=xl26></td>
<td class=xl54 >7</td>
<td class=xl55>2.4</td>
<td class=xl56 >7.3</td>
<td class=xl56 >4.8</td>
<td class=xl57 >5.0</td>
<td class=xl26></td>
<td class=xl39 >7</td>
<td class=xl40>2.9</td>
<td class=xl41 >9.6</td>
<td class=xl41 >6.2</td>
<td class=xl42 >6.6</td>
</tr><tr><td></td>
<td class=xl39 >8</td>
<td class=xl40>2.5</td>
<td class=xl41 >7.0</td>
<td class=xl41 >4.7</td>
<td class=xl42 >4.6</td>
<td class=xl26></td>
<td class=xl54 >8</td>
<td class=xl55>2.4</td>
<td class=xl56 >7.2</td>
<td class=xl56 >4.8</td>
<td class=xl57 >4.9</td>
<td class=xl26></td>
<td class=xl39 >8</td>
<td class=xl40>3.0</td>
<td class=xl41 >9.6</td>
<td class=xl41 >6.3</td>
<td class=xl42 >6.6</td>
</tr><tr><td></td>
<td class=xl39 >9</td>
<td class=xl40>2.4</td>
<td class=xl41 >7.0</td>
<td class=xl41 >4.7</td>
<td class=xl42 >4.6</td>
<td class=xl26></td>
<td class=xl54 >9</td>
<td class=xl55>2.3</td>
<td class=xl56 >7.2</td>
<td class=xl56 >4.7</td>
<td class=xl57 >4.9</td>
<td class=xl26></td>
<td class=xl39 >9</td>
<td class=xl40>3.1</td>
<td class=xl41 >9.7</td>
<td class=xl41 >6.4</td>
<td class=xl42 >6.6</td>
</tr><tr><td></td>
<td class=xl39 >10</td>
<td class=xl40>2.4</td>
<td class=xl41 >7.0</td>
<td class=xl41 >4.7</td>
<td class=xl42 >4.6</td>
<td class=xl26></td>
<td class=xl54 >10</td>
<td class=xl55>2.2</td>
<td class=xl56 >7.1</td>
<td class=xl56 >4.6</td>
<td class=xl57 >4.9</td>
<td class=xl26></td>
<td class=xl39 >10</td>
<td class=xl40>3.2</td>
<td class=xl41 >9.8</td>
<td class=xl41 >6.5</td>
<td class=xl42 >6.6</td>
</tr><tr><td></td>
<td class=xl39 >11</td>
<td class=xl40>2.4</td>
<td class=xl41 >7.0</td>
<td class=xl41 >4.7</td>
<td class=xl42 >4.6</td>
<td class=xl26></td>
<td class=xl54 >11</td>
<td class=xl55>2.1</td>
<td class=xl56 >7.1</td>
<td class=xl56 >4.6</td>
<td class=xl57 >5.0</td>
<td class=xl26></td>
<td class=xl39 >11</td>
<td class=xl40>3.3</td>
<td class=xl41 >9.9</td>
<td class=xl41 >6.6</td>
<td class=xl42 >6.7</td>
</tr><tr><td></td>
<td class=xl39 >12</td>
<td class=xl40>2.4</td>
<td class=xl41 >7.0</td>
<td class=xl41 >4.7</td>
<td class=xl42 >4.6</td>
<td class=xl26></td>
<td class=xl54 >12</td>
<td class=xl55>2.0</td>
<td class=xl56 >7.1</td>
<td class=xl56 >4.5</td>
<td class=xl57 >5.1</td>
<td class=xl26></td>
<td class=xl39 >12</td>
<td class=xl40>3.4</td>
<td class=xl41 >10.0</td>
<td class=xl41 >6.7</td>
<td class=xl42 >6.7</td>
</tr><tr><td></td>
<td class=xl39 >13</td>
<td class=xl40>2.4</td>
<td class=xl41 >7.0</td>
<td class=xl41 >4.7</td>
<td class=xl42 >4.6</td>
<td class=xl26></td>
<td class=xl54 >13</td>
<td class=xl55>1.9</td>
<td class=xl56 >7.2</td>
<td class=xl56 >4.5</td>
<td class=xl57 >5.3</td>
<td class=xl26></td>
<td class=xl39 >13</td>
<td class=xl40>3.5</td>
<td class=xl41 >10.1</td>
<td class=xl41 >6.8</td>
<td class=xl42 >6.7</td>
</tr><tr><td></td>
<td class=xl39 >14</td>
<td class=xl40>2.4</td>
<td class=xl41 >7.0</td>
<td class=xl41 >4.7</td>
<td class=xl42 >4.6</td>
<td class=xl26></td>
<td class=xl54 >14</td>
<td class=xl55>1.8</td>
<td class=xl56 >7.2</td>
<td class=xl56 >4.5</td>
<td class=xl57 >5.4</td>
<td class=xl26></td>
<td class=xl39 >14</td>
<td class=xl40>3.5</td>
<td class=xl41 >10.2</td>
<td class=xl41 >6.9</td>
<td class=xl42 >6.7</td>
</tr><tr><td></td>
<td class=xl39 >15</td>
<td class=xl40>2.4</td>
<td class=xl41 >7.0</td>
<td class=xl41 >4.7</td>
<td class=xl42 >4.6</td>
<td class=xl26></td>
<td class=xl54 >15</td>
<td class=xl55>1.8</td>
<td class=xl56 >7.3</td>
<td class=xl56 >4.5</td>
<td class=xl57 >5.5</td>
<td class=xl26></td>
<td class=xl39 >15</td>
<td class=xl40>3.6</td>
<td class=xl41 >10.3</td>
<td class=xl41 >7.0</td>
<td class=xl42 >6.7</td>
</tr><tr><td></td>
<td class=xl39 >16</td>
<td class=xl40>2.4</td>
<td class=xl41 >7.0</td>
<td class=xl41 >4.7</td>
<td class=xl42 >4.6</td>
<td class=xl26></td>
<td class=xl54 >16</td>
<td class=xl55>1.8</td>
<td class=xl56 >7.4</td>
<td class=xl56 >4.6</td>
<td class=xl57 >5.6</td>
<td class=xl26></td>
<td class=xl39 >16</td>
<td class=xl40>3.7</td>
<td class=xl41 >10.4</td>
<td class=xl41 >7.1</td>
<td class=xl42 >6.7</td>
</tr><tr><td></td>
<td class=xl39 >17</td>
<td class=xl40>2.4</td>
<td class=xl41 >7.0</td>
<td class=xl41 >4.7</td>
<td class=xl42 >4.6</td>
<td class=xl26></td>
<td class=xl54 >17</td>
<td class=xl55>1.9</td>
<td class=xl56 >7.4</td>
<td class=xl56 >4.6</td>
<td class=xl57 >5.5</td>
<td class=xl26></td>
<td class=xl39 >17</td>
<td class=xl40>3.8</td>
<td class=xl41 >10.5</td>
<td class=xl41 >7.2</td>
<td class=xl42 >6.7</td>
</tr><tr><td></td>
<td class=xl39 >18</td>
<td class=xl40>2.4</td>
<td class=xl41 >7.0</td>
<td class=xl41 >4.7</td>
<td class=xl42 >4.6</td>
<td class=xl26></td>
<td class=xl54 >18</td>
<td class=xl55>2.0</td>
<td class=xl56 >7.5</td>
<td class=xl56 >4.7</td>
<td class=xl57 >5.5</td>
<td class=xl26></td>
<td class=xl39 >18</td>
<td class=xl40>3.9</td>
<td class=xl41 >10.6</td>
<td class=xl41 >7.3</td>
<td class=xl42 >6.7</td>
</tr><tr><td></td>
<td class=xl39 >19</td>
<td class=xl40>2.3</td>
<td class=xl41 >7.0</td>
<td class=xl41 >4.7</td>
<td class=xl42 >4.7</td>
<td class=xl26></td>
<td class=xl54 >19</td>
<td class=xl55>2.0</td>
<td class=xl56 >7.5</td>
<td class=xl56 >4.8</td>
<td class=xl57 >5.5</td>
<td class=xl26></td>
<td class=xl39 >19</td>
<td class=xl40>3.9</td>
<td class=xl41 >10.7</td>
<td class=xl41 >7.3</td>
<td class=xl42 >6.7</td>
</tr><tr><td></td>
<td class=xl39 >20</td>
<td class=xl40>2.3</td>
<td class=xl41 >7.0</td>
<td class=xl41 >4.7</td>
<td class=xl42 >4.7</td>
<td class=xl26></td>
<td class=xl54 >20</td>
<td class=xl55>2.1</td>
<td class=xl56 >7.6</td>
<td class=xl56 >4.9</td>
<td class=xl57 >5.5</td>
<td class=xl26></td>
<td class=xl39 >20</td>
<td class=xl40>4.0</td>
<td class=xl41 >10.8</td>
<td class=xl41 >7.4</td>
<td class=xl42 >6.8</td>
</tr><tr><td></td>
<td class=xl39 >21</td>
<td class=xl40>2.3</td>
<td class=xl41 >7.0</td>
<td class=xl41 >4.6</td>
<td class=xl42 >4.7</td>
<td class=xl26></td>
<td class=xl54 >21</td>
<td class=xl55>2.2</td>
<td class=xl56 >7.8</td>
<td class=xl56 >5.0</td>
<td class=xl57 >5.6</td>
<td class=xl26></td>
<td class=xl39 >21</td>
<td class=xl40>4.0</td>
<td class=xl41 >10.8</td>
<td class=xl41 >7.4</td>
<td class=xl42 >6.8</td>
</tr><tr><td></td>
<td class=xl39 >22</td>
<td class=xl40>2.3</td>
<td class=xl41 >7.0</td>
<td class=xl41 >4.6</td>
<td class=xl42 >4.7</td>
<td class=xl26></td>
<td class=xl54 >22</td>
<td class=xl55>2.3</td>
<td class=xl56 >7.9</td>
<td class=xl56 >5.1</td>
<td class=xl57 >5.6</td>
<td class=xl26></td>
<td class=xl39 >22</td>
<td class=xl40>4.1</td>
<td class=xl41 >10.9</td>
<td class=xl41 >7.5</td>
<td class=xl42 >6.8</td>
</tr><tr><td></td>
<td class=xl39 >23</td>
<td class=xl40>2.3</td>
<td class=xl41 >7.0</td>
<td class=xl41 >4.6</td>
<td class=xl42 >4.7</td>
<td class=xl26></td>
<td class=xl54 >23</td>
<td class=xl55>2.4</td>
<td class=xl56 >8.1</td>
<td class=xl56 >5.2</td>
<td class=xl57 >5.7</td>
<td class=xl26></td>
<td class=xl39 >23</td>
<td class=xl40>4.1</td>
<td class=xl41 >11.0</td>
<td class=xl41 >7.6</td>
<td class=xl42 >6.9</td>
</tr><tr><td></td>
<td class=xl39 >24</td>
<td class=xl40>2.3</td>
<td class=xl41 >7.0</td>
<td class=xl41 >4.6</td>
<td class=xl42 >4.7</td>
<td class=xl26></td>
<td class=xl54 >24</td>
<td class=xl55>2.4</td>
<td class=xl56 >8.2</td>
<td class=xl56 >5.3</td>
<td class=xl57 >5.8</td>
<td class=xl26></td>
<td class=xl39 >24</td>
<td class=xl40>4.2</td>
<td class=xl41 >11.1</td>
<td class=xl41 >7.6</td>
<td class=xl42 >6.9</td>
</tr><tr><td></td>
<td class=xl39 >25</td>
<td class=xl40>2.3</td>
<td class=xl41 >7.0</td>
<td class=xl41 >4.6</td>
<td class=xl42 >4.7</td>
<td class=xl26></td>
<td class=xl54 >25</td>
<td class=xl55>2.5</td>
<td class=xl56 >8.4</td>
<td class=xl56 >5.4</td>
<td class=xl57 >5.9</td>
<td class=xl26></td>
<td class=xl39 >25</td>
<td class=xl40>4.2</td>
<td class=xl41 >11.2</td>
<td class=xl41 >7.7</td>
<td class=xl42 >6.9</td>
</tr><tr><td></td>
<td class=xl39 >26</td>
<td class=xl40>2.3</td>
<td class=xl41 >7.0</td>
<td class=xl41 >4.6</td>
<td class=xl42 >4.7</td>
<td class=xl26></td>
<td class=xl54 >26</td>
<td class=xl55>2.5</td>
<td class=xl56 >8.5</td>
<td class=xl56 >5.5</td>
<td class=xl57 >6.0</td>
<td class=xl26></td>
<td class=xl39 >26</td>
<td class=xl40>4.3</td>
<td class=xl41 >11.2</td>
<td class=xl41 >7.8</td>
<td class=xl42 >7.0</td>
</tr><tr><td></td>
<td class=xl39 >27</td>
<td class=xl40>2.3</td>
<td class=xl41 >7.0</td>
<td class=xl41 >4.6</td>
<td class=xl42 >4.7</td>
<td class=xl26></td>
<td class=xl54 >27</td>
<td class=xl55>2.6</td>
<td class=xl56 >8.7</td>
<td class=xl56 >5.6</td>
<td class=xl57 >6.1</td>
<td class=xl26></td>
<td class=xl39 >27</td>
<td class=xl40>4.3</td>
<td class=xl41 >11.3</td>
<td class=xl41 >7.8</td>
<td class=xl42 >7.0</td>
</tr><tr><td></td>
<td class=xl39 >28</td>
<td class=xl40>2.3</td>
<td class=xl41 >7.0</td>
<td class=xl41 >4.6</td>
<td class=xl42 >4.7</td>
<td class=xl26></td>
<td class=xl59 >28</td>
<td class=xl60>2.6</td>
<td class=xl61 >8.8</td>
<td class=xl61 >5.7</td>
<td class=xl62 >6.2</td>
<td class=xl26></td>
<td class=xl39 >28</td>
<td class=xl40>4.4</td>
<td class=xl41 >11.4</td>
<td class=xl41 >7.9</td>
<td class=xl42 >7.0</td>
</tr><tr><td></td>
<td class=xl39 >29</td>
<td class=xl40>2.4</td>
<td class=xl41 >7.0</td>
<td class=xl41 >4.7</td>
<td class=xl42 >4.6</td>
<td colspan=8 class=xl26 style='mso-ignore:colspan'></td>
<td class=xl39 >29</td>
<td class=xl40>4.4</td>
<td class=xl41 >11.5</td>
<td class=xl41 >7.9</td>
<td class=xl42 >7.1</td>
</tr><tr><td></td>
<td class=xl39 >30</td>
<td class=xl40>2.4</td>
<td class=xl41 >7.0</td>
<td class=xl41 >4.7</td>
<td class=xl42 >4.6</td>
<td colspan=8 class=xl26 style='mso-ignore:colspan'></td>
<td class=xl39 >30</td>
<td class=xl40>4.5</td>
<td class=xl41 >11.6</td>
<td class=xl41 >8.0</td>
<td class=xl42 >7.1</td>
</tr><tr><td></td>
<td class=xl44 >31</td>
<td class=xl45>2.4</td>
<td class=xl46 >7.0</td>
<td class=xl46 >4.7</td>
<td class=xl47 >4.6</td>
<td colspan=8 class=xl26 style='mso-ignore:colspan'></td>
<td class=xl44 >31</td>
<td class=xl45>4.5</td>
<td class=xl46 >11.7</td>
<td class=xl46 >8.1</td>
<td class=xl47 >7.2</td>
</tr><tr><td height=17 colspan=21 class=xl26 style='height:12.75pt;mso-ignore:colspan'></td>
</tr><tr><td></td>
<td class=xl27>&nbsp;</td>
<td class=xl28>&nbsp;</td>
<td class=xl29 width=64 style='width:48pt'>Min</td>
<td class=xl30 width=64 style='border-left:none;width:48pt'>Max</td>
<td class=xl31 width=64 style='border-left:none;width:48pt'>Mean</td>
<td class=xl32 width=64 style='border-left:none;width:48pt'>Range</td>
<td class=xl26></td>
<td class=xl27>&nbsp;</td>
<td class=xl28>&nbsp;</td>
<td class=xl29 width=64 style='width:48pt'>Min</td>
<td class=xl30 width=64 style='border-left:none;width:48pt'>Max</td>
<td class=xl31 width=64 style='border-left:none;width:48pt'>Mean</td>
<td class=xl32 width=64 style='border-left:none;width:48pt'>Range</td>
<td class=xl26></td>
<td class=xl27>&nbsp;</td>
<td class=xl28>&nbsp;</td>
<td class=xl29 width=64 style='width:48pt'>Min</td>
<td class=xl30 width=64 style='border-left:none;width:48pt'>Max</td>
<td class=xl31 width=64 style='border-left:none;width:48pt'>Mean</td>
<td class=xl32 width=64 style='border-left:none;width:48pt'>Range</td>
</tr><tr><td></td>
<td rowspan=30 class=xl48 style='border-bottom:.5pt solid black'>Apr</td>
<td class=xl49 style='border-left:none' >1</td>
<td class=xl50 >4.5</td>
<td class=xl51 style='border-left:none' >11.7</td>
<td class=xl51 style='border-left:none' >8.1</td>
<td class=xl52 style='border-left:none' >7.2</td>
<td class=xl26></td>
<td rowspan=31 class=xl33 style='border-bottom:.5pt solid black'>May</td>
<td class=xl34 style='border-left:none' >1</td>
<td class=xl35 >6.2</td>
<td class=xl36 style='border-left:none' >14.8</td>
<td class=xl36 style='border-left:none' >10.5</td>
<td class=xl37 style='border-left:none' >8.6</td>
<td class=xl26></td>
<td rowspan=30 class=xl48 style='border-bottom:.5pt solid black'>Jun</td>
<td class=xl49 style='border-left:none' >1</td>
<td class=xl50 >10.2</td>
<td class=xl51 style='border-left:none' >18.5</td>
<td class=xl51 style='border-left:none' >14.4</td>
<td class=xl52 style='border-left:none' >8.3</td>
</tr><tr><td></td>
<td class=xl54 >2</td>
<td class=xl55>4.6</td>
<td class=xl56 >11.7</td>
<td class=xl56 >8.1</td>
<td class=xl57 >7.2</td>
<td class=xl26></td>
<td class=xl39 >2</td>
<td class=xl40>6.4</td>
<td class=xl41 >14.9</td>
<td class=xl41 >10.7</td>
<td class=xl42 >8.6</td>
<td class=xl26></td>
<td class=xl54 >2</td>
<td class=xl55>10.3</td>
<td class=xl56 >18.7</td>
<td class=xl56 >14.5</td>
<td class=xl57 >8.4</td>
</tr><tr><td></td>
<td class=xl54 >3</td>
<td class=xl55>4.6</td>
<td class=xl56 >11.7</td>
<td class=xl56 >8.2</td>
<td class=xl57 >7.2</td>
<td class=xl26></td>
<td class=xl39 >3</td>
<td class=xl40>6.5</td>
<td class=xl41 >15.1</td>
<td class=xl41 >10.8</td>
<td class=xl42 >8.6</td>
<td class=xl26></td>
<td class=xl54 >3</td>
<td class=xl55>10.3</td>
<td class=xl56 >18.8</td>
<td class=xl56 >14.6</td>
<td class=xl57 >8.5</td>
</tr><tr><td></td>
<td class=xl54 >4</td>
<td class=xl55>4.6</td>
<td class=xl56 >11.8</td>
<td class=xl56 >8.2</td>
<td class=xl57 >7.2</td>
<td class=xl26></td>
<td class=xl39 >4</td>
<td class=xl40>6.6</td>
<td class=xl41 >15.2</td>
<td class=xl41 >10.9</td>
<td class=xl42 >8.6</td>
<td class=xl26></td>
<td class=xl54 >4</td>
<td class=xl55>10.4</td>
<td class=xl56 >18.9</td>
<td class=xl56 >14.7</td>
<td class=xl57 >8.5</td>
</tr><tr><td></td>
<td class=xl54 >5</td>
<td class=xl55>4.6</td>
<td class=xl56 >11.8</td>
<td class=xl56 >8.2</td>
<td class=xl57 >7.2</td>
<td class=xl26></td>
<td class=xl39 >5</td>
<td class=xl40>6.8</td>
<td class=xl41 >15.3</td>
<td class=xl41 >11.0</td>
<td class=xl42 >8.5</td>
<td class=xl26></td>
<td class=xl54 >5</td>
<td class=xl55>10.5</td>
<td class=xl56 >19.1</td>
<td class=xl56 >14.8</td>
<td class=xl57 >8.6</td>
</tr><tr><td></td>
<td class=xl54 >6</td>
<td class=xl55>4.7</td>
<td class=xl56 >11.8</td>
<td class=xl56 >8.2</td>
<td class=xl57 >7.2</td>
<td class=xl26></td>
<td class=xl39 >6</td>
<td class=xl40>6.9</td>
<td class=xl41 >15.4</td>
<td class=xl41 >11.2</td>
<td class=xl42 >8.5</td>
<td class=xl26></td>
<td class=xl54 >6</td>
<td class=xl55>10.5</td>
<td class=xl56 >19.2</td>
<td class=xl56 >14.9</td>
<td class=xl57 >8.7</td>
</tr><tr><td></td>
<td class=xl54 >7</td>
<td class=xl55>4.7</td>
<td class=xl56 >11.9</td>
<td class=xl56 >8.3</td>
<td class=xl57 >7.2</td>
<td class=xl26></td>
<td class=xl39 >7</td>
<td class=xl40>7.0</td>
<td class=xl41 >15.6</td>
<td class=xl41 >11.3</td>
<td class=xl42 >8.5</td>
<td class=xl26></td>
<td class=xl54 >7</td>
<td class=xl55>10.6</td>
<td class=xl56 >19.3</td>
<td class=xl56 >15.0</td>
<td class=xl57 >8.7</td>
</tr><tr><td></td>
<td class=xl54 >8</td>
<td class=xl55>4.7</td>
<td class=xl56 >12.0</td>
<td class=xl56 >8.3</td>
<td class=xl57 >7.3</td>
<td class=xl26></td>
<td class=xl39 >8</td>
<td class=xl40>7.2</td>
<td class=xl41 >15.7</td>
<td class=xl41 >11.4</td>
<td class=xl42 >8.5</td>
<td class=xl26></td>
<td class=xl54 >8</td>
<td class=xl55>10.6</td>
<td class=xl56 >19.5</td>
<td class=xl56 >15.1</td>
<td class=xl57 >8.8</td>
</tr><tr><td></td>
<td class=xl54 >9</td>
<td class=xl55>4.7</td>
<td class=xl56 >12.1</td>
<td class=xl56 >8.4</td>
<td class=xl57 >7.4</td>
<td class=xl26></td>
<td class=xl39 >9</td>
<td class=xl40>7.3</td>
<td class=xl41 >15.8</td>
<td class=xl41 >11.6</td>
<td class=xl42 >8.5</td>
<td class=xl26></td>
<td class=xl54 >9</td>
<td class=xl55>10.7</td>
<td class=xl56 >19.6</td>
<td class=xl56 >15.2</td>
<td class=xl57 >8.9</td>
</tr><tr><td></td>
<td class=xl54 >10</td>
<td class=xl55>4.8</td>
<td class=xl56 >12.2</td>
<td class=xl56 >8.5</td>
<td class=xl57 >7.4</td>
<td class=xl26></td>
<td class=xl39 >10</td>
<td class=xl40>7.4</td>
<td class=xl41 >15.9</td>
<td class=xl41 >11.7</td>
<td class=xl42 >8.5</td>
<td class=xl26></td>
<td class=xl54 >10</td>
<td class=xl55>10.8</td>
<td class=xl56 >19.7</td>
<td class=xl56 >15.3</td>
<td class=xl57 >9.0</td>
</tr><tr><td></td>
<td class=xl54 >11</td>
<td class=xl55>4.8</td>
<td class=xl56 >12.3</td>
<td class=xl56 >8.5</td>
<td class=xl57 >7.5</td>
<td class=xl26></td>
<td class=xl39 >11</td>
<td class=xl40>7.6</td>
<td class=xl41 >16.1</td>
<td class=xl41 >11.8</td>
<td class=xl42 >8.5</td>
<td class=xl26></td>
<td class=xl54 >11</td>
<td class=xl55>10.8</td>
<td class=xl56 >19.9</td>
<td class=xl56 >15.4</td>
<td class=xl57 >9.0</td>
</tr><tr><td></td>
<td class=xl54 >12</td>
<td class=xl55>4.8</td>
<td class=xl56 >12.4</td>
<td class=xl56 >8.6</td>
<td class=xl57 >7.6</td>
<td class=xl26></td>
<td class=xl39 >12</td>
<td class=xl40>7.7</td>
<td class=xl41 >16.2</td>
<td class=xl41 >11.9</td>
<td class=xl42 >8.5</td>
<td class=xl26></td>
<td class=xl54 >12</td>
<td class=xl55>10.9</td>
<td class=xl56 >20.0</td>
<td class=xl56 >15.4</td>
<td class=xl57 >9.1</td>
</tr><tr><td></td>
<td class=xl54 >13</td>
<td class=xl55>4.8</td>
<td class=xl56 >12.5</td>
<td class=xl56 >8.6</td>
<td class=xl57 >7.6</td>
<td class=xl26></td>
<td class=xl39 >13</td>
<td class=xl40>7.9</td>
<td class=xl41 >16.3</td>
<td class=xl41 >12.1</td>
<td class=xl42 >8.5</td>
<td class=xl26></td>
<td class=xl54 >13</td>
<td class=xl55>11.0</td>
<td class=xl56 >20.1</td>
<td class=xl56 >15.5</td>
<td class=xl57 >9.2</td>
</tr><tr><td></td>
<td class=xl54 >14</td>
<td class=xl55>4.9</td>
<td class=xl56 >12.5</td>
<td class=xl56 >8.7</td>
<td class=xl57 >7.7</td>
<td class=xl26></td>
<td class=xl39 >14</td>
<td class=xl40>8.0</td>
<td class=xl41 >16.4</td>
<td class=xl41 >12.2</td>
<td class=xl42 >8.4</td>
<td class=xl26></td>
<td class=xl54 >14</td>
<td class=xl55>11.0</td>
<td class=xl56 >20.3</td>
<td class=xl56 >15.6</td>
<td class=xl57 >9.2</td>
</tr><tr><td></td>
<td class=xl54 >15</td>
<td class=xl55>4.9</td>
<td class=xl56 >12.6</td>
<td class=xl56 >8.8</td>
<td class=xl57 >7.8</td>
<td class=xl26></td>
<td class=xl39 >15</td>
<td class=xl40>8.1</td>
<td class=xl41 >16.6</td>
<td class=xl41 >12.3</td>
<td class=xl42 >8.4</td>
<td class=xl26></td>
<td class=xl54 >15</td>
<td class=xl55>11.1</td>
<td class=xl56 >20.3</td>
<td class=xl56 >15.7</td>
<td class=xl57 >9.2</td>
</tr><tr><td></td>
<td class=xl54 >16</td>
<td class=xl55>4.9</td>
<td class=xl56 >12.7</td>
<td class=xl56 >8.8</td>
<td class=xl57 >7.8</td>
<td class=xl26></td>
<td class=xl39 >16</td>
<td class=xl40>8.3</td>
<td class=xl41 >16.7</td>
<td class=xl41 >12.5</td>
<td class=xl42 >8.4</td>
<td class=xl26></td>
<td class=xl54 >16</td>
<td class=xl55>11.2</td>
<td class=xl56 >20.4</td>
<td class=xl56 >15.8</td>
<td class=xl57 >9.3</td>
</tr><tr><td></td>
<td class=xl54 >17</td>
<td class=xl55>4.9</td>
<td class=xl56 >12.8</td>
<td class=xl56 >8.9</td>
<td class=xl57 >7.9</td>
<td class=xl26></td>
<td class=xl39 >17</td>
<td class=xl40>8.4</td>
<td class=xl41 >16.8</td>
<td class=xl41 >12.6</td>
<td class=xl42 >8.4</td>
<td class=xl26></td>
<td class=xl54 >17</td>
<td class=xl55>11.2</td>
<td class=xl56 >20.5</td>
<td class=xl56 >15.9</td>
<td class=xl57 >9.3</td>
</tr><tr><td></td>
<td class=xl54 >18</td>
<td class=xl55>5.0</td>
<td class=xl56 >12.9</td>
<td class=xl56 >8.9</td>
<td class=xl57 >8.0</td>
<td class=xl26></td>
<td class=xl39 >18</td>
<td class=xl40>8.5</td>
<td class=xl41 >16.9</td>
<td class=xl41 >12.7</td>
<td class=xl42 >8.4</td>
<td class=xl26></td>
<td class=xl54 >18</td>
<td class=xl55>11.3</td>
<td class=xl56 >20.6</td>
<td class=xl56 >15.9</td>
<td class=xl57 >9.3</td>
</tr><tr><td></td>
<td class=xl54 >19</td>
<td class=xl55>5.0</td>
<td class=xl56 >13.0</td>
<td class=xl56 >9.0</td>
<td class=xl57 >8.0</td>
<td class=xl26></td>
<td class=xl39 >19</td>
<td class=xl40>8.7</td>
<td class=xl41 >17.0</td>
<td class=xl41 >12.9</td>
<td class=xl42 >8.4</td>
<td class=xl26></td>
<td class=xl54 >19</td>
<td class=xl55>11.3</td>
<td class=xl56 >20.7</td>
<td class=xl56 >16.0</td>
<td class=xl57 >9.3</td>
</tr><tr><td></td>
<td class=xl54 >20</td>
<td class=xl55>5.0</td>
<td class=xl56 >13.1</td>
<td class=xl56 >9.1</td>
<td class=xl57 >8.1</td>
<td class=xl26></td>
<td class=xl39 >20</td>
<td class=xl40>8.8</td>
<td class=xl41 >17.2</td>
<td class=xl41 >13.0</td>
<td class=xl42 >8.4</td>
<td class=xl26></td>
<td class=xl54 >20</td>
<td class=xl55>11.4</td>
<td class=xl56 >20.8</td>
<td class=xl56 >16.1</td>
<td class=xl57 >9.3</td>
</tr><tr><td></td>
<td class=xl54 >21</td>
<td class=xl55>5.1</td>
<td class=xl56 >13.3</td>
<td class=xl56 >9.2</td>
<td class=xl57 >8.2</td>
<td class=xl26></td>
<td class=xl39 >21</td>
<td class=xl40>8.9</td>
<td class=xl41 >17.3</td>
<td class=xl41 >13.1</td>
<td class=xl42 >8.4</td>
<td class=xl26></td>
<td class=xl54 >21</td>
<td class=xl55>11.5</td>
<td class=xl56 >20.8</td>
<td class=xl56 >16.2</td>
<td class=xl57 >9.4</td>
</tr><tr><td></td>
<td class=xl54 >22</td>
<td class=xl55>5.2</td>
<td class=xl56 >13.4</td>
<td class=xl56 >9.3</td>
<td class=xl57 >8.2</td>
<td class=xl26></td>
<td class=xl39 >22</td>
<td class=xl40>9.1</td>
<td class=xl41 >17.4</td>
<td class=xl41 >13.2</td>
<td class=xl42 >8.4</td>
<td class=xl26></td>
<td class=xl54 >22</td>
<td class=xl55>11.5</td>
<td class=xl56 >20.9</td>
<td class=xl56 >16.2</td>
<td class=xl57 >9.4</td>
</tr><tr><td></td>
<td class=xl54 >23</td>
<td class=xl55>5.3</td>
<td class=xl56 >13.6</td>
<td class=xl56 >9.5</td>
<td class=xl57 >8.3</td>
<td class=xl26></td>
<td class=xl39 >23</td>
<td class=xl40>9.2</td>
<td class=xl41 >17.5</td>
<td class=xl41 >13.4</td>
<td class=xl42 >8.3</td>
<td class=xl26></td>
<td class=xl54 >23</td>
<td class=xl55>11.6</td>
<td class=xl56 >21.0</td>
<td class=xl56 >16.3</td>
<td class=xl57 >9.4</td>
</tr><tr><td></td>
<td class=xl54 >24</td>
<td class=xl55>5.4</td>
<td class=xl56 >13.7</td>
<td class=xl56 >9.6</td>
<td class=xl57 >8.3</td>
<td class=xl26></td>
<td class=xl39 >24</td>
<td class=xl40>9.3</td>
<td class=xl41 >17.7</td>
<td class=xl41 >13.5</td>
<td class=xl42 >8.3</td>
<td class=xl26></td>
<td class=xl54 >24</td>
<td class=xl55>11.7</td>
<td class=xl56 >21.1</td>
<td class=xl56 >16.4</td>
<td class=xl57 >9.4</td>
</tr><tr><td></td>
<td class=xl54 >25</td>
<td class=xl55>5.6</td>
<td class=xl56 >13.9</td>
<td class=xl56 >9.7</td>
<td class=xl57 >8.4</td>
<td class=xl26></td>
<td class=xl39 >25</td>
<td class=xl40>9.5</td>
<td class=xl41 >17.8</td>
<td class=xl41 >13.6</td>
<td class=xl42 >8.3</td>
<td class=xl26></td>
<td class=xl54 >25</td>
<td class=xl55>11.7</td>
<td class=xl56 >21.2</td>
<td class=xl56 >16.5</td>
<td class=xl57 >9.4</td>
</tr><tr><td></td>
<td class=xl54 >26</td>
<td class=xl55>5.7</td>
<td class=xl56 >14.1</td>
<td class=xl56 >9.9</td>
<td class=xl57 >8.4</td>
<td class=xl26></td>
<td class=xl39 >26</td>
<td class=xl40>9.6</td>
<td class=xl41 >17.9</td>
<td class=xl41 >13.8</td>
<td class=xl42 >8.3</td>
<td class=xl26></td>
<td class=xl54 >26</td>
<td class=xl55>11.8</td>
<td class=xl56 >21.3</td>
<td class=xl56 >16.5</td>
<td class=xl57 >9.5</td>
</tr><tr><td></td>
<td class=xl54 >27</td>
<td class=xl55>5.8</td>
<td class=xl56 >14.2</td>
<td class=xl56 >10.0</td>
<td class=xl57 >8.5</td>
<td class=xl26></td>
<td class=xl39 >27</td>
<td class=xl40>9.7</td>
<td class=xl41 >18.0</td>
<td class=xl41 >13.9</td>
<td class=xl42 >8.3</td>
<td class=xl26></td>
<td class=xl54 >27</td>
<td class=xl55>11.9</td>
<td class=xl56 >21.3</td>
<td class=xl56 >16.6</td>
<td class=xl57 >9.5</td>
</tr><tr><td></td>
<td class=xl54 >28</td>
<td class=xl55>5.9</td>
<td class=xl56 >14.4</td>
<td class=xl56 >10.1</td>
<td class=xl57 >8.5</td>
<td class=xl26></td>
<td class=xl39 >28</td>
<td class=xl40>9.9</td>
<td class=xl41 >18.2</td>
<td class=xl41 >14.0</td>
<td class=xl42 >8.3</td>
<td class=xl26></td>
<td class=xl54 >28</td>
<td class=xl55>11.9</td>
<td class=xl56 >21.4</td>
<td class=xl56 >16.7</td>
<td class=xl57 >9.5</td>
</tr><tr><td></td>
<td class=xl54 >29</td>
<td class=xl55>6.0</td>
<td class=xl56 >14.5</td>
<td class=xl56 >10.3</td>
<td class=xl57 >8.6</td>
<td class=xl26></td>
<td class=xl39 >29</td>
<td class=xl40>10.0</td>
<td class=xl41 >18.3</td>
<td class=xl41 >14.1</td>
<td class=xl42 >8.3</td>
<td class=xl26></td>
<td class=xl54 >29</td>
<td class=xl55>12.0</td>
<td class=xl56 >21.5</td>
<td class=xl56 >16.8</td>
<td class=xl57 >9.5</td>
</tr><tr><td></td>
<td class=xl59 >30</td>
<td class=xl60>6.1</td>
<td class=xl61 >14.7</td>
<td class=xl61 >10.4</td>
<td class=xl62 >8.6</td>
<td class=xl26></td>
<td class=xl39 >30</td>
<td class=xl40>10.1</td>
<td class=xl41 >18.4</td>
<td class=xl41 >14.3</td>
<td class=xl42 >8.3</td>
<td class=xl26></td>
<td class=xl59 >30</td>
<td class=xl60>12.1</td>
<td class=xl61 >21.6</td>
<td class=xl61 >16.8</td>
<td class=xl62 >9.6</td>
</tr><tr><td height=17 colspan=8 class=xl26 style='height:12.75pt;mso-ignore:colspan'></td>
<td class=xl44 >31</td>
<td class=xl45>10.1</td>
<td class=xl46 >18.4</td>
<td class=xl46 >14.3</td>
<td class=xl47 >8.3</td>
<td colspan=7 class=xl26 style='mso-ignore:colspan'></td>
</tr><tr><td height=17 colspan=8 class=xl26 style='height:12.75pt;mso-ignore:colspan'></td>
<td class=xl63></td>
<td class=xl26></td>
<td class=xl64></td>
<td colspan=3 class=xl65 style='mso-ignore:colspan'></td>
<td colspan=7 class=xl26 style='mso-ignore:colspan'></td>
</tr><tr><td></td>
<td class=xl27>&nbsp;</td>
<td class=xl28>&nbsp;</td>
<td class=xl29 width=64 style='width:48pt'>Min</td>
<td class=xl30 width=64 style='border-left:none;width:48pt'>Max</td>
<td class=xl31 width=64 style='border-left:none;width:48pt'>Mean</td>
<td class=xl32 width=64 style='border-left:none;width:48pt'>Range</td>
<td class=xl26></td>
<td class=xl27>&nbsp;</td>
<td class=xl28>&nbsp;</td>
<td class=xl29 width=64 style='width:48pt'>Min</td>
<td class=xl30 width=64 style='border-left:none;width:48pt'>Max</td>
<td class=xl31 width=64 style='border-left:none;width:48pt'>Mean</td>
<td class=xl32 width=64 style='border-left:none;width:48pt'>Range</td>
<td class=xl26></td>
<td class=xl27>&nbsp;</td>
<td class=xl28>&nbsp;</td>
<td class=xl29 width=64 style='width:48pt'>Min</td>
<td class=xl30 width=64 style='border-left:none;width:48pt'>Max</td>
<td class=xl31 width=64 style='border-left:none;width:48pt'>Mean</td>
<td class=xl32 width=64 style='border-left:none;width:48pt'>Range</td>
</tr><tr><td></td>
<td rowspan=31 class=xl33 style='border-bottom:.5pt solid black'>Jul</td>
<td class=xl34 style='border-left:none' >1</td>
<td class=xl35 >12.2</td>
<td class=xl36 style='border-left:none' >21.7</td>
<td class=xl36 style='border-left:none' >16.9</td>
<td class=xl37 style='border-left:none' >9.5</td>
<td class=xl26></td>
<td rowspan=31 class=xl48 style='border-bottom:.5pt solid black'>Aug</td>
<td class=xl49 style='border-left:none' >1</td>
<td class=xl50 >13.8</td>
<td class=xl51 style='border-left:none' >22.7</td>
<td class=xl51 style='border-left:none' >18.2</td>
<td class=xl52 style='border-left:none' >8.9</td>
<td class=xl26></td>
<td rowspan=30 class=xl33 style='border-bottom:.5pt solid black'>Sep</td>
<td class=xl34 style='border-left:none' >1</td>
<td class=xl35 >12.2</td>
<td class=xl36 style='border-left:none' >21.0</td>
<td class=xl36 style='border-left:none' >16.6</td>
<td class=xl37 style='border-left:none' >8.8</td>
</tr><tr><td></td>
<td class=xl39 >2</td>
<td class=xl40>12.3</td>
<td class=xl41 >21.8</td>
<td class=xl41 >17.0</td>
<td class=xl42 >9.5</td>
<td class=xl26></td>
<td class=xl54 >2</td>
<td class=xl55>13.8</td>
<td class=xl56 >22.7</td>
<td class=xl56 >18.2</td>
<td class=xl57 >8.9</td>
<td class=xl26></td>
<td class=xl39 >2</td>
<td class=xl40>12.1</td>
<td class=xl41 >20.9</td>
<td class=xl41 >16.5</td>
<td class=xl42 >8.7</td>
</tr><tr><td></td>
<td class=xl39 >3</td>
<td class=xl40>12.4</td>
<td class=xl41 >21.8</td>
<td class=xl41 >17.1</td>
<td class=xl42 >9.5</td>
<td class=xl26></td>
<td class=xl54 >3</td>
<td class=xl55>13.7</td>
<td class=xl56 >22.7</td>
<td class=xl56 >18.2</td>
<td class=xl57 >8.9</td>
<td class=xl26></td>
<td class=xl39 >3</td>
<td class=xl40>12.1</td>
<td class=xl41 >20.8</td>
<td class=xl41 >16.4</td>
<td class=xl42 >8.7</td>
</tr><tr><td></td>
<td class=xl39 >4</td>
<td class=xl40>12.5</td>
<td class=xl41 >21.9</td>
<td class=xl41 >17.2</td>
<td class=xl42 >9.5</td>
<td class=xl26></td>
<td class=xl54 >4</td>
<td class=xl55>13.7</td>
<td class=xl56 >22.6</td>
<td class=xl56 >18.2</td>
<td class=xl57 >8.9</td>
<td class=xl26></td>
<td class=xl39 >4</td>
<td class=xl40>12.0</td>
<td class=xl41 >20.7</td>
<td class=xl41 >16.3</td>
<td class=xl42 >8.7</td>
</tr><tr><td></td>
<td class=xl39 >5</td>
<td class=xl40>12.6</td>
<td class=xl41 >22.0</td>
<td class=xl41 >17.3</td>
<td class=xl42 >9.4</td>
<td class=xl26></td>
<td class=xl54 >5</td>
<td class=xl55>13.7</td>
<td class=xl56 >22.6</td>
<td class=xl56 >18.1</td>
<td class=xl57 >9.0</td>
<td class=xl26></td>
<td class=xl39 >5</td>
<td class=xl40>11.9</td>
<td class=xl41 >20.6</td>
<td class=xl41 >16.3</td>
<td class=xl42 >8.7</td>
</tr><tr><td></td>
<td class=xl39 >6</td>
<td class=xl40>12.7</td>
<td class=xl41 >22.1</td>
<td class=xl41 >17.4</td>
<td class=xl42 >9.4</td>
<td class=xl26></td>
<td class=xl54 >6</td>
<td class=xl55>13.6</td>
<td class=xl56 >22.6</td>
<td class=xl56 >18.1</td>
<td class=xl57 >9.0</td>
<td class=xl26></td>
<td class=xl39 >6</td>
<td class=xl40>11.8</td>
<td class=xl41 >20.4</td>
<td class=xl41 >16.1</td>
<td class=xl42 >8.6</td>
</tr><tr><td></td>
<td class=xl39 >7</td>
<td class=xl40>12.8</td>
<td class=xl41 >22.2</td>
<td class=xl41 >17.5</td>
<td class=xl42 >9.4</td>
<td class=xl26></td>
<td class=xl54 >7</td>
<td class=xl55>13.6</td>
<td class=xl56 >22.6</td>
<td class=xl56 >18.1</td>
<td class=xl57 >9.0</td>
<td class=xl26></td>
<td class=xl39 >7</td>
<td class=xl40>11.8</td>
<td class=xl41 >20.2</td>
<td class=xl41 >16.0</td>
<td class=xl42 >8.4</td>
</tr><tr><td></td>
<td class=xl39 >8</td>
<td class=xl40>12.9</td>
<td class=xl41 >22.2</td>
<td class=xl41 >17.6</td>
<td class=xl42 >9.4</td>
<td class=xl26></td>
<td class=xl54 >8</td>
<td class=xl55>13.6</td>
<td class=xl56 >22.6</td>
<td class=xl56 >18.1</td>
<td class=xl57 >9.0</td>
<td class=xl26></td>
<td class=xl39 >8</td>
<td class=xl40>11.7</td>
<td class=xl41 >20.0</td>
<td class=xl41 >15.9</td>
<td class=xl42 >8.3</td>
</tr><tr><td></td>
<td class=xl39 >9</td>
<td class=xl40>13.0</td>
<td class=xl41 >22.3</td>
<td class=xl41 >17.7</td>
<td class=xl42 >9.3</td>
<td class=xl26></td>
<td class=xl54 >9</td>
<td class=xl55>13.5</td>
<td class=xl56 >22.5</td>
<td class=xl56 >18.0</td>
<td class=xl57 >9.0</td>
<td class=xl26></td>
<td class=xl39 >9</td>
<td class=xl40>11.6</td>
<td class=xl41 >19.8</td>
<td class=xl41 >15.7</td>
<td class=xl42 >8.2</td>
</tr><tr><td></td>
<td class=xl39 >10</td>
<td class=xl40>13.1</td>
<td class=xl41 >22.4</td>
<td class=xl41 >17.7</td>
<td class=xl42 >9.3</td>
<td class=xl26></td>
<td class=xl54 >10</td>
<td class=xl55>13.5</td>
<td class=xl56 >22.5</td>
<td class=xl56 >18.0</td>
<td class=xl57 >9.0</td>
<td class=xl26></td>
<td class=xl39 >10</td>
<td class=xl40>11.5</td>
<td class=xl41 >19.6</td>
<td class=xl41 >15.6</td>
<td class=xl42 >8.1</td>
</tr><tr><td></td>
<td class=xl39 >11</td>
<td class=xl40>13.2</td>
<td class=xl41 >22.4</td>
<td class=xl41 >17.8</td>
<td class=xl42 >9.3</td>
<td class=xl26></td>
<td class=xl54 >11</td>
<td class=xl55>13.5</td>
<td class=xl56 >22.5</td>
<td class=xl56 >18.0</td>
<td class=xl57 >9.0</td>
<td class=xl26></td>
<td class=xl39 >11</td>
<td class=xl40>11.5</td>
<td class=xl41 >19.4</td>
<td class=xl41 >15.4</td>
<td class=xl42 >8.0</td>
</tr><tr><td></td>
<td class=xl39 >12</td>
<td class=xl40>13.3</td>
<td class=xl41 >22.5</td>
<td class=xl41 >17.9</td>
<td class=xl42 >9.2</td>
<td class=xl26></td>
<td class=xl54 >12</td>
<td class=xl55>13.5</td>
<td class=xl56 >22.4</td>
<td class=xl56 >17.9</td>
<td class=xl57 >9.0</td>
<td class=xl26></td>
<td class=xl39 >12</td>
<td class=xl40>11.4</td>
<td class=xl41 >19.2</td>
<td class=xl41 >15.3</td>
<td class=xl42 >7.9</td>
</tr><tr><td></td>
<td class=xl39 >13</td>
<td class=xl40>13.4</td>
<td class=xl41 >22.5</td>
<td class=xl41 >17.9</td>
<td class=xl42 >9.1</td>
<td class=xl26></td>
<td class=xl54 >13</td>
<td class=xl55>13.4</td>
<td class=xl56 >22.4</td>
<td class=xl56 >17.9</td>
<td class=xl57 >9.0</td>
<td class=xl26></td>
<td class=xl39 >13</td>
<td class=xl40>11.3</td>
<td class=xl41 >19.1</td>
<td class=xl41 >15.2</td>
<td class=xl42 >7.7</td>
</tr><tr><td></td>
<td class=xl39 >14</td>
<td class=xl40>13.5</td>
<td class=xl41 >22.5</td>
<td class=xl41 >18.0</td>
<td class=xl42 >9.0</td>
<td class=xl26></td>
<td class=xl54 >14</td>
<td class=xl55>13.4</td>
<td class=xl56 >22.4</td>
<td class=xl56 >17.9</td>
<td class=xl57 >9.0</td>
<td class=xl26></td>
<td class=xl39 >14</td>
<td class=xl40>11.2</td>
<td class=xl41 >18.9</td>
<td class=xl41 >15.0</td>
<td class=xl42 >7.6</td>
</tr><tr><td></td>
<td class=xl39 >15</td>
<td class=xl40>13.6</td>
<td class=xl41 >22.5</td>
<td class=xl41 >18.0</td>
<td class=xl42 >9.0</td>
<td class=xl26></td>
<td class=xl54 >15</td>
<td class=xl55>13.3</td>
<td class=xl56 >22.3</td>
<td class=xl56 >17.8</td>
<td class=xl57 >9.0</td>
<td class=xl26></td>
<td class=xl39 >15</td>
<td class=xl40>11.2</td>
<td class=xl41 >18.7</td>
<td class=xl41 >14.9</td>
<td class=xl42 >7.5</td>
</tr><tr><td></td>
<td class=xl39 >16</td>
<td class=xl40>13.6</td>
<td class=xl41 >22.6</td>
<td class=xl41 >18.1</td>
<td class=xl42 >9.0</td>
<td class=xl26></td>
<td class=xl54 >16</td>
<td class=xl55>13.3</td>
<td class=xl56 >22.3</td>
<td class=xl56 >17.8</td>
<td class=xl57 >9.0</td>
<td class=xl26></td>
<td class=xl39 >16</td>
<td class=xl40>11.1</td>
<td class=xl41 >18.5</td>
<td class=xl41 >14.8</td>
<td class=xl42 >7.4</td>
</tr><tr><td></td>
<td class=xl39 >17</td>
<td class=xl40>13.7</td>
<td class=xl41 >22.6</td>
<td class=xl41 >18.1</td>
<td class=xl42 >8.9</td>
<td class=xl26></td>
<td class=xl54 >17</td>
<td class=xl55>13.2</td>
<td class=xl56 >22.3</td>
<td class=xl56 >17.8</td>
<td class=xl57 >9.0</td>
<td class=xl26></td>
<td class=xl39 >17</td>
<td class=xl40>11.0</td>
<td class=xl41 >18.3</td>
<td class=xl41 >14.6</td>
<td class=xl42 >7.3</td>
</tr><tr><td></td>
<td class=xl39 >18</td>
<td class=xl40>13.7</td>
<td class=xl41 >22.6</td>
<td class=xl41 >18.2</td>
<td class=xl42 >8.9</td>
<td class=xl26></td>
<td class=xl54 >18</td>
<td class=xl55>13.2</td>
<td class=xl56 >22.2</td>
<td class=xl56 >17.7</td>
<td class=xl57 >9.1</td>
<td class=xl26></td>
<td class=xl39 >18</td>
<td class=xl40>10.9</td>
<td class=xl41 >18.1</td>
<td class=xl41 >14.5</td>
<td class=xl42 >7.2</td>
</tr><tr><td></td>
<td class=xl39 >19</td>
<td class=xl40>13.8</td>
<td class=xl41 >22.7</td>
<td class=xl41 >18.2</td>
<td class=xl42 >8.9</td>
<td class=xl26></td>
<td class=xl54 >19</td>
<td class=xl55>13.1</td>
<td class=xl56 >22.2</td>
<td class=xl56 >17.7</td>
<td class=xl57 >9.1</td>
<td class=xl26></td>
<td class=xl39 >19</td>
<td class=xl40>10.9</td>
<td class=xl41 >18.0</td>
<td class=xl41 >14.4</td>
<td class=xl42 >7.1</td>
</tr><tr><td></td>
<td class=xl39 >20</td>
<td class=xl40>13.8</td>
<td class=xl41 >22.7</td>
<td class=xl41 >18.2</td>
<td class=xl42 >8.9</td>
<td class=xl26></td>
<td class=xl54 >20</td>
<td class=xl55>13.1</td>
<td class=xl56 >22.2</td>
<td class=xl56 >17.6</td>
<td class=xl57 >9.1</td>
<td class=xl26></td>
<td class=xl39 >20</td>
<td class=xl40>10.8</td>
<td class=xl41 >17.9</td>
<td class=xl41 >14.3</td>
<td class=xl42 >7.1</td>
</tr><tr><td></td>
<td class=xl39 >21</td>
<td class=xl40>13.9</td>
<td class=xl41 >22.7</td>
<td class=xl41 >18.3</td>
<td class=xl42 >8.8</td>
<td class=xl26></td>
<td class=xl54 >21</td>
<td class=xl55>13.0</td>
<td class=xl56 >22.1</td>
<td class=xl56 >17.6</td>
<td class=xl57 >9.1</td>
<td class=xl26></td>
<td class=xl39 >21</td>
<td class=xl40>10.7</td>
<td class=xl41 >17.7</td>
<td class=xl41 >14.2</td>
<td class=xl42 >7.0</td>
</tr><tr><td></td>
<td class=xl39 >22</td>
<td class=xl40>13.9</td>
<td class=xl41 >22.7</td>
<td class=xl41 >18.3</td>
<td class=xl42 >8.8</td>
<td class=xl26></td>
<td class=xl54 >22</td>
<td class=xl55>13.0</td>
<td class=xl56 >22.0</td>
<td class=xl56 >17.5</td>
<td class=xl57 >9.0</td>
<td class=xl26></td>
<td class=xl39 >22</td>
<td class=xl40>10.6</td>
<td class=xl41 >17.6</td>
<td class=xl41 >14.1</td>
<td class=xl42 >7.0</td>
</tr><tr><td></td>
<td class=xl39 >23</td>
<td class=xl40>14.0</td>
<td class=xl41 >22.7</td>
<td class=xl41 >18.3</td>
<td class=xl42 >8.7</td>
<td class=xl26></td>
<td class=xl54 >23</td>
<td class=xl55>12.9</td>
<td class=xl56 >21.9</td>
<td class=xl56 >17.4</td>
<td class=xl57 >9.0</td>
<td class=xl26></td>
<td class=xl39 >23</td>
<td class=xl40>10.6</td>
<td class=xl41 >17.5</td>
<td class=xl41 >14.0</td>
<td class=xl42 >6.9</td>
</tr><tr><td></td>
<td class=xl39 >24</td>
<td class=xl40>14.0</td>
<td class=xl41 >22.7</td>
<td class=xl41 >18.3</td>
<td class=xl42 >8.7</td>
<td class=xl26></td>
<td class=xl54 >24</td>
<td class=xl55>12.8</td>
<td class=xl56 >21.8</td>
<td class=xl56 >17.3</td>
<td class=xl57 >9.0</td>
<td class=xl26></td>
<td class=xl39 >24</td>
<td class=xl40>10.5</td>
<td class=xl41 >17.4</td>
<td class=xl41 >13.9</td>
<td class=xl42 >6.9</td>
</tr><tr><td></td>
<td class=xl39 >25</td>
<td class=xl40>14.0</td>
<td class=xl41 >22.7</td>
<td class=xl41 >18.3</td>
<td class=xl42 >8.7</td>
<td class=xl26></td>
<td class=xl54 >25</td>
<td class=xl55>12.7</td>
<td class=xl56 >21.7</td>
<td class=xl56 >17.2</td>
<td class=xl57 >8.9</td>
<td class=xl26></td>
<td class=xl39 >25</td>
<td class=xl40>10.4</td>
<td class=xl41 >17.3</td>
<td class=xl41 >13.8</td>
<td class=xl42 >6.8</td>
</tr><tr><td></td>
<td class=xl39 >26</td>
<td class=xl40>14.0</td>
<td class=xl41 >22.7</td>
<td class=xl41 >18.3</td>
<td class=xl42 >8.7</td>
<td class=xl26></td>
<td class=xl54 >26</td>
<td class=xl55>12.7</td>
<td class=xl56 >21.6</td>
<td class=xl56 >17.1</td>
<td class=xl57 >8.9</td>
<td class=xl26></td>
<td class=xl39 >26</td>
<td class=xl40>10.4</td>
<td class=xl41 >17.1</td>
<td class=xl41 >13.7</td>
<td class=xl42 >6.8</td>
</tr><tr><td></td>
<td class=xl39 >27</td>
<td class=xl40>14.0</td>
<td class=xl41 >22.7</td>
<td class=xl41 >18.3</td>
<td class=xl42 >8.7</td>
<td class=xl26></td>
<td class=xl54 >27</td>
<td class=xl55>12.6</td>
<td class=xl56 >21.5</td>
<td class=xl56 >17.0</td>
<td class=xl57 >8.9</td>
<td class=xl26></td>
<td class=xl39 >27</td>
<td class=xl40>10.3</td>
<td class=xl41 >17.0</td>
<td class=xl41 >13.7</td>
<td class=xl42 >6.7</td>
</tr><tr><td></td>
<td class=xl39 >28</td>
<td class=xl40>14.0</td>
<td class=xl41 >22.7</td>
<td class=xl41 >18.3</td>
<td class=xl42 >8.7</td>
<td class=xl26></td>
<td class=xl54 >28</td>
<td class=xl55>12.5</td>
<td class=xl56 >21.4</td>
<td class=xl56 >17.0</td>
<td class=xl57 >8.9</td>
<td class=xl26></td>
<td class=xl39 >28</td>
<td class=xl40>10.2</td>
<td class=xl41 >16.9</td>
<td class=xl41 >13.6</td>
<td class=xl42 >6.7</td>
</tr><tr><td></td>
<td class=xl39 >29</td>
<td class=xl40>13.9</td>
<td class=xl41 >22.7</td>
<td class=xl41 >18.3</td>
<td class=xl42 >8.8</td>
<td class=xl26></td>
<td class=xl54 >29</td>
<td class=xl55>12.4</td>
<td class=xl56 >21.3</td>
<td class=xl56 >16.9</td>
<td class=xl57 >8.8</td>
<td class=xl26></td>
<td class=xl39 >29</td>
<td class=xl40>10.1</td>
<td class=xl41 >16.8</td>
<td class=xl41 >13.5</td>
<td class=xl42 >6.6</td>
</tr><tr><td></td>
<td class=xl39 >30</td>
<td class=xl40>13.9</td>
<td class=xl41 >22.7</td>
<td class=xl41 >18.3</td>
<td class=xl42 >8.8</td>
<td class=xl26></td>
<td class=xl54 >30</td>
<td class=xl55>12.4</td>
<td class=xl56 >21.2</td>
<td class=xl56 >16.8</td>
<td class=xl57 >8.8</td>
<td class=xl26></td>
<td class=xl44 >30</td>
<td class=xl45>10.1</td>
<td class=xl46 >16.7</td>
<td class=xl46 >13.4</td>
<td class=xl47 >6.6</td>
</tr><tr><td></td>
<td class=xl44 >31</td>
<td class=xl45>13.8</td>
<td class=xl46 >22.7</td>
<td class=xl46 >18.3</td>
<td class=xl47 >8.8</td>
<td class=xl26></td>
<td class=xl59 >31</td>
<td class=xl60>12.3</td>
<td class=xl61 >21.1</td>
<td class=xl61 >16.7</td>
<td class=xl62 >8.8</td>
<td colspan=7 class=xl26 style='mso-ignore:colspan'></td>
</tr><tr><td height=17 colspan=21 class=xl26 style='height:12.75pt;mso-ignore:colspan'></td>
</tr><tr><td></td>
<td class=xl27>&nbsp;</td>
<td class=xl28>&nbsp;</td>
<td class=xl29 width=64 style='width:48pt'>Min</td>
<td class=xl30 width=64 style='border-left:none;width:48pt'>Max</td>
<td class=xl31 width=64 style='border-left:none;width:48pt'>Mean</td>
<td class=xl32 width=64 style='border-left:none;width:48pt'>Range</td>
<td class=xl26></td>
<td class=xl27>&nbsp;</td>
<td class=xl28>&nbsp;</td>
<td class=xl29 width=64 style='width:48pt'>Min</td>
<td class=xl30 width=64 style='border-left:none;width:48pt'>Max</td>
<td class=xl31 width=64 style='border-left:none;width:48pt'>Mean</td>
<td class=xl32 width=64 style='border-left:none;width:48pt'>Range</td>
<td class=xl26></td>
<td class=xl27>&nbsp;</td>
<td class=xl28>&nbsp;</td>
<td class=xl29 width=64 style='width:48pt'>Min</td>
<td class=xl30 width=64 style='border-left:none;width:48pt'>Max</td>
<td class=xl31 width=64 style='border-left:none;width:48pt'>Mean</td>
<td class=xl32 width=64 style='border-left:none;width:48pt'>Range</td>
</tr><tr><td></td>
<td rowspan=31 class=xl48 style='border-bottom:.5pt solid black'>Oct</td>
<td class=xl49 style='border-left:none' >1</td>
<td class=xl50 >10.0</td>
<td class=xl51 style='border-left:none' >16.5</td>
<td class=xl51 style='border-left:none' >13.3</td>
<td class=xl52 style='border-left:none' >6.5</td>
<td class=xl26></td>
<td rowspan=30 class=xl33 style='border-bottom:.5pt solid black'>Nov</td>
<td class=xl34 style='border-left:none' >1</td>
<td class=xl35 >6.9</td>
<td class=xl36 style='border-left:none' >12.2</td>
<td class=xl36 style='border-left:none' >9.5</td>
<td class=xl37 style='border-left:none' >5.3</td>
<td class=xl26></td>
<td rowspan=31 class=xl48 style='border-bottom:.5pt solid black'>Dec</td>
<td class=xl49 style='border-left:none' >1</td>
<td class=xl50 >3.7</td>
<td class=xl51 style='border-left:none' >8.9</td>
<td class=xl51 style='border-left:none' >6.3</td>
<td class=xl52 style='border-left:none' >5.3</td>
</tr><tr><td></td>
<td class=xl54 >2</td>
<td class=xl55>9.9</td>
<td class=xl56 >16.4</td>
<td class=xl56 >13.2</td>
<td class=xl57 >6.5</td>
<td class=xl26></td>
<td class=xl39 >2</td>
<td class=xl40>6.8</td>
<td class=xl41 >12.0</td>
<td class=xl41 >9.4</td>
<td class=xl42 >5.2</td>
<td class=xl26></td>
<td class=xl54 >2</td>
<td class=xl55>3.6</td>
<td class=xl56 >8.8</td>
<td class=xl56 >6.2</td>
<td class=xl57 >5.2</td>
</tr><tr><td></td>
<td class=xl54 >3</td>
<td class=xl55>9.9</td>
<td class=xl56 >16.3</td>
<td class=xl56 >13.1</td>
<td class=xl57 >6.4</td>
<td class=xl26></td>
<td class=xl39 >3</td>
<td class=xl40>6.6</td>
<td class=xl41 >11.8</td>
<td class=xl41 >9.2</td>
<td class=xl42 >5.2</td>
<td class=xl26></td>
<td class=xl54 >3</td>
<td class=xl55>3.6</td>
<td class=xl56 >8.8</td>
<td class=xl56 >6.2</td>
<td class=xl57 >5.2</td>
</tr><tr><td></td>
<td class=xl54 >4</td>
<td class=xl55>9.8</td>
<td class=xl56 >16.2</td>
<td class=xl56 >13.0</td>
<td class=xl57 >6.4</td>
<td class=xl26></td>
<td class=xl39 >4</td>
<td class=xl40>6.5</td>
<td class=xl41 >11.6</td>
<td class=xl41 >9.0</td>
<td class=xl42 >5.1</td>
<td class=xl26></td>
<td class=xl54 >4</td>
<td class=xl55>3.6</td>
<td class=xl56 >8.7</td>
<td class=xl56 >6.1</td>
<td class=xl57 >5.1</td>
</tr><tr><td></td>
<td class=xl54 >5</td>
<td class=xl55>9.7</td>
<td class=xl56 >16.1</td>
<td class=xl56 >12.9</td>
<td class=xl57 >6.3</td>
<td class=xl26></td>
<td class=xl39 >5</td>
<td class=xl40>6.4</td>
<td class=xl41 >11.4</td>
<td class=xl41 >8.9</td>
<td class=xl42 >5.0</td>
<td class=xl26></td>
<td class=xl54 >5</td>
<td class=xl55>3.5</td>
<td class=xl56 >8.6</td>
<td class=xl56 >6.1</td>
<td class=xl57 >5.1</td>
</tr><tr><td></td>
<td class=xl54 >6</td>
<td class=xl55>9.7</td>
<td class=xl56 >15.9</td>
<td class=xl56 >12.8</td>
<td class=xl57 >6.3</td>
<td class=xl26></td>
<td class=xl39 >6</td>
<td class=xl40>6.2</td>
<td class=xl41 >11.2</td>
<td class=xl41 >8.7</td>
<td class=xl42 >5.0</td>
<td class=xl26></td>
<td class=xl54 >6</td>
<td class=xl55>3.5</td>
<td class=xl56 >8.5</td>
<td class=xl56 >6.0</td>
<td class=xl57 >5.0</td>
</tr><tr><td></td>
<td class=xl54 >7</td>
<td class=xl55>9.6</td>
<td class=xl56 >15.8</td>
<td class=xl56 >12.7</td>
<td class=xl57 >6.2</td>
<td class=xl26></td>
<td class=xl39 >7</td>
<td class=xl40>6.1</td>
<td class=xl41 >11.0</td>
<td class=xl41 >8.5</td>
<td class=xl42 >4.9</td>
<td class=xl26></td>
<td class=xl54 >7</td>
<td class=xl55>3.5</td>
<td class=xl56 >8.4</td>
<td class=xl56 >5.9</td>
<td class=xl57 >5.0</td>
</tr><tr><td></td>
<td class=xl54 >8</td>
<td class=xl55>9.5</td>
<td class=xl56 >15.7</td>
<td class=xl56 >12.6</td>
<td class=xl57 >6.2</td>
<td class=xl26></td>
<td class=xl39 >8</td>
<td class=xl40>5.9</td>
<td class=xl41 >10.8</td>
<td class=xl41 >8.4</td>
<td class=xl42 >4.9</td>
<td class=xl26></td>
<td class=xl54 >8</td>
<td class=xl55>3.4</td>
<td class=xl56 >8.4</td>
<td class=xl56 >5.9</td>
<td class=xl57 >4.9</td>
</tr><tr><td></td>
<td class=xl54 >9</td>
<td class=xl55>9.4</td>
<td class=xl56 >15.6</td>
<td class=xl56 >12.5</td>
<td class=xl57 >6.1</td>
<td class=xl26></td>
<td class=xl39 >9</td>
<td class=xl40>5.8</td>
<td class=xl41 >10.6</td>
<td class=xl41 >8.2</td>
<td class=xl42 >4.8</td>
<td class=xl26></td>
<td class=xl54 >9</td>
<td class=xl55>3.4</td>
<td class=xl56 >8.3</td>
<td class=xl56 >5.8</td>
<td class=xl57 >4.9</td>
</tr><tr><td></td>
<td class=xl54 >10</td>
<td class=xl55>9.4</td>
<td class=xl56 >15.5</td>
<td class=xl56 >12.4</td>
<td class=xl57 >6.1</td>
<td class=xl26></td>
<td class=xl39 >10</td>
<td class=xl40>5.7</td>
<td class=xl41 >10.4</td>
<td class=xl41 >8.0</td>
<td class=xl42 >4.7</td>
<td class=xl26></td>
<td class=xl54 >10</td>
<td class=xl55>3.3</td>
<td class=xl56 >8.2</td>
<td class=xl56 >5.8</td>
<td class=xl57 >4.9</td>
</tr><tr><td></td>
<td class=xl54 >11</td>
<td class=xl55>9.3</td>
<td class=xl56 >15.3</td>
<td class=xl56 >12.3</td>
<td class=xl57 >6.0</td>
<td class=xl26></td>
<td class=xl39 >11</td>
<td class=xl40>5.5</td>
<td class=xl41 >10.2</td>
<td class=xl41 >7.9</td>
<td class=xl42 >4.7</td>
<td class=xl26></td>
<td class=xl54 >11</td>
<td class=xl55>3.3</td>
<td class=xl56 >8.1</td>
<td class=xl56 >5.7</td>
<td class=xl57 >4.8</td>
</tr><tr><td></td>
<td class=xl54 >12</td>
<td class=xl55>9.2</td>
<td class=xl56 >15.2</td>
<td class=xl56 >12.2</td>
<td class=xl57 >6.0</td>
<td class=xl26></td>
<td class=xl39 >12</td>
<td class=xl40>5.4</td>
<td class=xl41 >10.0</td>
<td class=xl41 >7.7</td>
<td class=xl42 >4.6</td>
<td class=xl26></td>
<td class=xl54 >12</td>
<td class=xl55>3.3</td>
<td class=xl56 >8.0</td>
<td class=xl56 >5.7</td>
<td class=xl57 >4.8</td>
</tr><tr><td></td>
<td class=xl54 >13</td>
<td class=xl55>9.2</td>
<td class=xl56 >15.1</td>
<td class=xl56 >12.1</td>
<td class=xl57 >5.9</td>
<td class=xl26></td>
<td class=xl39 >13</td>
<td class=xl40>5.3</td>
<td class=xl41 >10.0</td>
<td class=xl41 >7.6</td>
<td class=xl42 >4.7</td>
<td class=xl26></td>
<td class=xl54 >13</td>
<td class=xl55>3.2</td>
<td class=xl56 >8.0</td>
<td class=xl56 >5.6</td>
<td class=xl57 >4.7</td>
</tr><tr><td></td>
<td class=xl54 >14</td>
<td class=xl55>9.1</td>
<td class=xl56 >15.0</td>
<td class=xl56 >12.0</td>
<td class=xl57 >5.9</td>
<td class=xl26></td>
<td class=xl39 >14</td>
<td class=xl40>5.2</td>
<td class=xl41 >9.9</td>
<td class=xl41 >7.5</td>
<td class=xl42 >4.8</td>
<td class=xl26></td>
<td class=xl54 >14</td>
<td class=xl55>3.2</td>
<td class=xl56 >7.9</td>
<td class=xl56 >5.5</td>
<td class=xl57 >4.7</td>
</tr><tr><td></td>
<td class=xl54 >15</td>
<td class=xl55>9.0</td>
<td class=xl56 >14.9</td>
<td class=xl56 >11.9</td>
<td class=xl57 >5.8</td>
<td class=xl26></td>
<td class=xl39 >15</td>
<td class=xl40>5.0</td>
<td class=xl41 >9.9</td>
<td class=xl41 >7.5</td>
<td class=xl42 >4.9</td>
<td class=xl26></td>
<td class=xl54 >15</td>
<td class=xl55>3.2</td>
<td class=xl56 >7.8</td>
<td class=xl56 >5.5</td>
<td class=xl57 >4.6</td>
</tr><tr><td></td>
<td class=xl54 >16</td>
<td class=xl55>8.9</td>
<td class=xl56 >14.7</td>
<td class=xl56 >11.8</td>
<td class=xl57 >5.8</td>
<td class=xl26></td>
<td class=xl39 >16</td>
<td class=xl40>4.9</td>
<td class=xl41 >9.8</td>
<td class=xl41 >7.4</td>
<td class=xl42 >4.9</td>
<td class=xl26></td>
<td class=xl54 >16</td>
<td class=xl55>3.1</td>
<td class=xl56 >7.7</td>
<td class=xl56 >5.4</td>
<td class=xl57 >4.6</td>
</tr><tr><td></td>
<td class=xl54 >17</td>
<td class=xl55>8.9</td>
<td class=xl56 >14.6</td>
<td class=xl56 >11.7</td>
<td class=xl57 >5.7</td>
<td class=xl26></td>
<td class=xl39 >17</td>
<td class=xl40>4.8</td>
<td class=xl41 >9.8</td>
<td class=xl41 >7.3</td>
<td class=xl42 >5.0</td>
<td class=xl26></td>
<td class=xl54 >17</td>
<td class=xl55>3.1</td>
<td class=xl56 >7.6</td>
<td class=xl56 >5.4</td>
<td class=xl57 >4.5</td>
</tr><tr><td></td>
<td class=xl54 >18</td>
<td class=xl55>8.8</td>
<td class=xl56 >14.5</td>
<td class=xl56 >11.7</td>
<td class=xl57 >5.7</td>
<td class=xl26></td>
<td class=xl39 >18</td>
<td class=xl40>4.7</td>
<td class=xl41 >9.8</td>
<td class=xl41 >7.2</td>
<td class=xl42 >5.1</td>
<td class=xl26></td>
<td class=xl54 >18</td>
<td class=xl55>3.1</td>
<td class=xl56 >7.6</td>
<td class=xl56 >5.3</td>
<td class=xl57 >4.5</td>
</tr><tr><td></td>
<td class=xl54 >19</td>
<td class=xl55>8.7</td>
<td class=xl56 >14.3</td>
<td class=xl56 >11.5</td>
<td class=xl57 >5.7</td>
<td class=xl26></td>
<td class=xl39 >19</td>
<td class=xl40>4.5</td>
<td class=xl41 >9.7</td>
<td class=xl41 >7.1</td>
<td class=xl42 >5.2</td>
<td class=xl26></td>
<td class=xl54 >19</td>
<td class=xl55>3.0</td>
<td class=xl56 >7.5</td>
<td class=xl56 >5.3</td>
<td class=xl57 >4.5</td>
</tr><tr><td></td>
<td class=xl54 >20</td>
<td class=xl55>8.5</td>
<td class=xl56 >14.2</td>
<td class=xl56 >11.4</td>
<td class=xl57 >5.6</td>
<td class=xl26></td>
<td class=xl39 >20</td>
<td class=xl40>4.4</td>
<td class=xl41 >9.7</td>
<td class=xl41 >7.0</td>
<td class=xl42 >5.3</td>
<td class=xl26></td>
<td class=xl54 >20</td>
<td class=xl55>3.0</td>
<td class=xl56 >7.5</td>
<td class=xl56 >5.2</td>
<td class=xl57 >4.5</td>
</tr><tr><td></td>
<td class=xl54 >21</td>
<td class=xl55>8.4</td>
<td class=xl56 >14.0</td>
<td class=xl56 >11.2</td>
<td class=xl57 >5.6</td>
<td class=xl26></td>
<td class=xl39 >21</td>
<td class=xl40>4.3</td>
<td class=xl41 >9.6</td>
<td class=xl41 >7.0</td>
<td class=xl42 >5.4</td>
<td class=xl26></td>
<td class=xl54 >21</td>
<td class=xl55>3.0</td>
<td class=xl56 >7.4</td>
<td class=xl56 >5.2</td>
<td class=xl57 >4.4</td>
</tr><tr><td></td>
<td class=xl54 >22</td>
<td class=xl55>8.3</td>
<td class=xl56 >13.9</td>
<td class=xl56 >11.1</td>
<td class=xl57 >5.6</td>
<td class=xl26></td>
<td class=xl39 >22</td>
<td class=xl40>4.2</td>
<td class=xl41 >9.6</td>
<td class=xl41 >6.9</td>
<td class=xl42 >5.4</td>
<td class=xl26></td>
<td class=xl54 >22</td>
<td class=xl55>3.0</td>
<td class=xl56 >7.4</td>
<td class=xl56 >5.2</td>
<td class=xl57 >4.4</td>
</tr><tr><td></td>
<td class=xl54 >23</td>
<td class=xl55>8.1</td>
<td class=xl56 >13.7</td>
<td class=xl56 >10.9</td>
<td class=xl57 >5.6</td>
<td class=xl26></td>
<td class=xl39 >23</td>
<td class=xl40>4.0</td>
<td class=xl41 >9.5</td>
<td class=xl41 >6.8</td>
<td class=xl42 >5.5</td>
<td class=xl26></td>
<td class=xl54 >23</td>
<td class=xl55>2.9</td>
<td class=xl56 >7.4</td>
<td class=xl56 >5.1</td>
<td class=xl57 >4.4</td>
</tr><tr><td></td>
<td class=xl54 >24</td>
<td class=xl55>8.0</td>
<td class=xl56 >13.5</td>
<td class=xl56 >10.8</td>
<td class=xl57 >5.5</td>
<td class=xl26></td>
<td class=xl39 >24</td>
<td class=xl40>3.9</td>
<td class=xl41 >9.5</td>
<td class=xl41 >6.7</td>
<td class=xl42 >5.6</td>
<td class=xl26></td>
<td class=xl54 >24</td>
<td class=xl55>2.9</td>
<td class=xl56 >7.3</td>
<td class=xl56 >5.1</td>
<td class=xl57 >4.4</td>
</tr><tr><td></td>
<td class=xl54 >25</td>
<td class=xl55>7.8</td>
<td class=xl56 >13.4</td>
<td class=xl56 >10.6</td>
<td class=xl57 >5.5</td>
<td class=xl26></td>
<td class=xl39 >25</td>
<td class=xl40>3.9</td>
<td class=xl41 >9.4</td>
<td class=xl41 >6.6</td>
<td class=xl42 >5.6</td>
<td class=xl26></td>
<td class=xl54 >25</td>
<td class=xl55>2.9</td>
<td class=xl56 >7.3</td>
<td class=xl56 >5.1</td>
<td class=xl57 >4.4</td>
</tr><tr><td></td>
<td class=xl54 >26</td>
<td class=xl55>7.7</td>
<td class=xl56 >13.2</td>
<td class=xl56 >10.5</td>
<td class=xl57 >5.5</td>
<td class=xl26></td>
<td class=xl39 >26</td>
<td class=xl40>3.8</td>
<td class=xl41 >9.3</td>
<td class=xl41 >6.6</td>
<td class=xl42 >5.5</td>
<td class=xl26></td>
<td class=xl54 >26</td>
<td class=xl55>2.8</td>
<td class=xl56 >7.2</td>
<td class=xl56 >5.0</td>
<td class=xl57 >4.4</td>
</tr><tr><td></td>
<td class=xl54 >27</td>
<td class=xl55>7.6</td>
<td class=xl56 >13.0</td>
<td class=xl56 >10.3</td>
<td class=xl57 >5.5</td>
<td class=xl26></td>
<td class=xl39 >27</td>
<td class=xl40>3.8</td>
<td class=xl41 >9.3</td>
<td class=xl41 >6.5</td>
<td class=xl42 >5.5</td>
<td class=xl26></td>
<td class=xl54 >27</td>
<td class=xl55>2.8</td>
<td class=xl56 >7.2</td>
<td class=xl56 >5.0</td>
<td class=xl57 >4.4</td>
</tr><tr><td></td>
<td class=xl54 >28</td>
<td class=xl55>7.4</td>
<td class=xl56 >12.9</td>
<td class=xl56 >10.2</td>
<td class=xl57 >5.4</td>
<td class=xl26></td>
<td class=xl39 >28</td>
<td class=xl40>3.8</td>
<td class=xl41 >9.2</td>
<td class=xl41 >6.5</td>
<td class=xl42 >5.4</td>
<td class=xl26></td>
<td class=xl54 >28</td>
<td class=xl55>2.8</td>
<td class=xl56 >7.2</td>
<td class=xl56 >5.0</td>
<td class=xl57 >4.4</td>
</tr><tr><td></td>
<td class=xl54 >29</td>
<td class=xl55>7.3</td>
<td class=xl56 >12.7</td>
<td class=xl56 >10.0</td>
<td class=xl57 >5.4</td>
<td class=xl26></td>
<td class=xl39 >29</td>
<td class=xl40>3.7</td>
<td class=xl41 >9.1</td>
<td class=xl41 >6.4</td>
<td class=xl42 >5.4</td>
<td class=xl26></td>
<td class=xl54 >29</td>
<td class=xl55>2.8</td>
<td class=xl56 >7.1</td>
<td class=xl56 >4.9</td>
<td class=xl57 >4.4</td>
</tr><tr><td></td>
<td class=xl54 >30</td>
<td class=xl55>7.2</td>
<td class=xl56 >12.6</td>
<td class=xl56 >9.9</td>
<td class=xl57 >5.4</td>
<td class=xl26></td>
<td class=xl44 >30</td>
<td class=xl45>3.7</td>
<td class=xl46 >9.0</td>
<td class=xl46 >6.4</td>
<td class=xl47 >5.3</td>
<td class=xl26></td>
<td class=xl54 >30</td>
<td class=xl55>2.7</td>
<td class=xl56 >7.1</td>
<td class=xl56 >4.9</td>
<td class=xl57 >4.3</td>
</tr><tr><td></td>
<td class=xl59 >31</td>
<td class=xl60>7.0</td>
<td class=xl61 >12.4</td>
<td class=xl61 >9.7</td>
<td class=xl62 >5.4</td>
<td colspan=8 class=xl26 style='mso-ignore:colspan'></td>
<td class=xl59 >31</td>
<td class=xl60>2.7</td>
<td class=xl61 >7.0</td>
<td class=xl61 >4.9</td>
<td class=xl62 >4.3</td>
</tr>

</table>

<br /><br />

<a name="graph" />
<img width="1085" height="512" src="/static-images/image0011.gif" alt="graph year" />

</div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>

</body>
</html>