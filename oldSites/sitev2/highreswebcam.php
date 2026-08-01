<?php require('unit-select.php'); ?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xml:lang="en" xmlns="http://www.w3.org/1999/xhtml" lang="en"><head>

<?php include("main_tags.php"); 
		$file = 21; $ex = 1; ?>

	<title>NW3 Weather - Old(v2) - 24hr Webcam Summary</title>

	<meta name="description" content="Old v2 - Web cam images from during the day (past 24hrs) from NW3 weather, overlooking Hampstead Heath">

	<? require('chead.php'); ?>
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
<div id="main-copy">

<h1>Last 24 hours Webcam Image Summary</h1>

<p><b>Note:</b> Hover over an image (or "webcam" text) to view its availabilty</p>

<table align="center" border="0" width="99%">
<tbody>
<?php function checkold($tstamp) { if(date("U") - filemtime($tstamp . 'currcam.jpg') < 24*3600) { echo $tstamp . 'currcam.jpg'; } }
if(date(z) < 133 || date(z) > 211): echo '<!--'; endif; ?>
<tr>
<td align="center">
<img src="" alt="webcam" title="Availablility: Never" height="276" width="368"></td>
<td align="center">
<img src="/currcam/<?php if(date(z) < 187 && date(z) > 150): echo '0430currcam.jpg'; endif; ?>" alt="webcam" title="Availablility: 31/05-05/07" height="276" width="368"></td>
<td align="center">
<img src="/currcam/<?php if(date(z) < 212 && date(z) > 132): echo '0500currcam.jpg'; endif; ?>" alt="webcam" title="Availablility: 13/05-Jul" height="276" width="368"></td>
</tr>
<tr> <td align="center" valign="top"> <b>0400</b> </td> <td align="center"> <b>0430</b> </td> <td align="center"><b>0500</b> </td>
</tr>
<tr> <td color="white" align="center" valign="top"> <span style="color: white;"> - </span> </td></tr>
<?php if(date(z) < 133 || date(z) > 211): echo '-->'; endif; ?>

<?php if(date(z) < 59 || date(z) > 267): echo '<!--'; endif; ?>
<tr>
<td align="center">
<img src="/currcam/<?php if(date(z) < 250 && date(z) > 112): echo '0530currcam.jpg'; endif; ?>" alt="webcam" title="Availablility: 23/04-Aug" height="276" width="368"></td>
<td align="center">
<img src="/currcam/<?php if(date(z) < 249 && date(z) > 72): echo '0600currcam.jpg'; endif; ?>" alt="webcam" title="Availablility: 14/03-DST, 11/04-17/09" height="276" width="368"></td>
<td align="center">
<img src="/currcam/<?php if(date(z) < 268 && date(z) > 58): echo '0630currcam.jpg'; endif; ?>" alt="webcam" title="Availablility: 28/02-DST, 28/03-24/09" height="276" width="368"></td>
</tr>
<tr> <td align="center" valign="top"> <b>0530</b> </td> <td align="center"> <b>0600</b> </td> <td align="center"><b>0630</b> </td>
</tr>
<tr> <td color="white" align="center" valign="top"> <span style="color: white;"> - </span> </td></tr>
<?php if(date(z) < 59 || date(z) > 267): echo '-->'; endif; ?>

<tr>
<td align="center">
<img src="/currcam/<?php if(date(z) < 287 && date(z) > 43): echo '0700currcam.jpg'; endif; ?>" alt="webcam" title="Availablility: 13/02-13/10" height="276" width="368"></td>
<td align="center">
<img src="/currcam/<?php if(date(z) < 342 && date(z) > 24): echo '0730currcam.jpg'; endif; ?>" alt="webcam" title="Availablility: 25/01-07/12" height="276" width="368"></td>
<td align="center">
<img src="/currcam/0800currcam.jpg" alt="webcam" title="Availablility: All year" height="276" width="368"></td>
</tr>
<tr> <td align="center" valign="top"> <b>0700</b> </td> <td align="center"> <b>0730</b> </td> <td align="center"><b>0800</b> </td>
</tr>
<tr> <td color="white" align="center" valign="top"> <span style="color: white;"> - </span> </td></tr>

<tr>
<td align="center">
<img src="/currcam/0830currcam.jpg" alt="webcam" title="Availablility: All year" height="276" width="368"></td>
<td align="center">
<img src="/currcam/0900currcam.jpg" alt="webcam" title="Availablility: All year" height="276" width="368"></td>
<td align="center">
<img src="/currcam/0930currcam.jpg" alt="webcam" title="Availablility: All year" height="276" width="368"></td>
</tr>
<tr> <td align="center"> <b>0830</b> </td> <td align="center"> <b>0900</b> </td> <td align="center"><b>0930</b> </td> 
</tr>
<tr> <td color="white" align="center" valign="top"> <span style="color: white;"> - </span> </td></tr>

<tr>
<td align="center">
<img src="/currcam/1000currcam.jpg" alt="webcam" title="Availablility: All year" height="276" width="368"></td>
<td align="center">
<img src="/currcam/1030currcam.jpg" alt="webcam" title="Availablility: All year" height="276" width="368"></td>
<td align="center">
<img src="/currcam/1100currcam.jpg" alt="webcam" title="Availablility: All year" height="276" width="368"></td>
</tr>
<tr> <td align="center"> <b>1000</b> </td> <td align="center"> <b>1030</b> </td> <td align="center"><b>1100</b> </td>
</tr>
<tr> <td color="white" align="center" valign="top"> <span style="color: white;"> - </span> </td></tr>

<tr> 
<td align="center">
<img src="/currcam/1130currcam.jpg" alt="webcam" title="Availablility: All year" height="276" width="368"></td>
<td align="center">
<img src="/currcam/1200currcam.jpg" alt="webcam" title="Availablility: All year" height="276" width="368"></td>
<td align="center">
<img src="/currcam/1230currcam.jpg" alt="webcam" title="Availablility: All year" height="276" width="368"> </td>
</tr>
<tr> <td align="center"> <b>1130</b> </td> <td align="center"> <b>1200</b> </td> <td align="center"><b>1230</b> </td> 
</tr>
<tr> <td color="white" align="center" valign="top"> <span style="color: white;"> - </span> </td></tr>

<tr> 
<td align="center">
<img src="/currcam/1300currcam.jpg" alt="webcam" title="Availablility: All year" height="276" width="368"></td>
<td align="center">
<img src="/currcam/1330currcam.jpg" alt="webcam" title="Availablility: All year" height="276" width="368"></td>
<td align="center">
<img src="/currcam/1400currcam.jpg" alt="webcam" title="Availablility: All year" height="276" width="368"></td>
</tr>
<tr> <td align="center"> <b>1300</b> </td> <td align="center"> <b>1330</b> </td> <td align="center"><b>1400</b> </td>
</tr>
<tr> <td color="white" align="center" valign="top"> <span style="color: white;"> - </span> </td></tr>

<tr> 
<td align="center">
<img src="/currcam/1430currcam.jpg" alt="webcam" title="Availablility: All year" height="276" width="368"></td>
<td align="center">
<img src="/currcam/1500currcam.jpg" alt="webcam" title="Availablility: All year" height="276" width="368"></td>
<td align="center">
<img src="/currcam/1530currcam.jpg" alt="webcam" title="Availablility: All year" height="276" width="368"></td>
</tr>
<tr> <td align="center"> <b>1430</b> </td> <td align="center"> <b>1500</b> </td> <td align="center"><b>1530</b> </td>
</tr>
<tr> <td color="white" align="center" valign="top"> <span style="color: white;"> - </span> </td></tr>

<tr> 
<td align="center">
<img src="/currcam/<?php if(date(z) < 329 && date(z) > 0): echo '1600currcam.jpg'; endif; ?>" alt="webcam" title="Availablility: 01/01-24/11" height="276" width="368"></td>
<td align="center">
<img src="/currcam/<?php if(date(z) < 321 && date(z) > 9): echo '1630currcam.jpg'; endif; ?>" alt="webcam" title="Availablility: 10/01-16/11" height="276" width="368"></td>
<td align="center">
<img src="/currcam/<?php if(date(z) < 304 && date(z) > 37): echo '1700currcam.jpg'; endif; ?>" alt="webcam" title="Availablility: 07/02-30/10" height="276" width="368"></td>
</tr>
<tr> <td align="center"> <b>1600</b> </td> <td align="center"> <b>1630</b> </td> <td align="center"><b>1700</b> </td>
</tr>
<tr> <td color="white" align="center" valign="top"> <span style="color: white;"> - </span> </td></tr>

<?php if(date(z) < 44 || date(z) > 303): echo '<!--'; endif; ?>
<tr> 
<td align="center">
<img src="/currcam/<?php if(date(z) < 304 && date(z) > 43): echo '1730currcam.jpg'; endif; ?>" alt="webcam" title="Availablility: 13/02-30/10" height="276" width="368"></td>
<td align="center">
<img src="/currcam/<?php if(date(z) < 291 && date(z) > 72): echo '1800currcam.jpg'; endif; ?>" alt="webcam" title="Availablility: 14/03-17/10" height="276" width="368"></td>
<td align="center">
<img src="/currcam/<?php if(date(z) < 287 && date(z) > 80): echo '1830currcam.jpg'; endif; ?>" alt="webcam" title=" Availablility: 22/03-13/10" height="276" width="368"></td>
</tr>
<tr> <td align="center"> <b>1730</b> </td> <td align="center"> <b>1800</b> </td> <td align="center"><b>1830</b> </td>
</tr>
<tr> <td color="white" align="center" valign="top"> <span style="color: white;"> - </span> </td></tr>
<?php if(date(z) < 44 || date(z) > 303): echo '-->'; endif; ?>

<?php if(date(z) < 84 || date(z) > 264): echo '<!--'; endif; ?>
<tr> 
<td align="center">
<img src="/currcam/<?php if(date(z) < 265 && date(z) > 83): echo '1900currcam.jpg'; endif; ?>" alt="webcam" title=" Availablility: DST-21/09" height="276" width="368"></td>
<td align="center">
<img src="/currcam/<?php if(date(z) < 261 && date(z) > 83): echo '1930currcam.jpg'; endif; ?>" alt="webcam" title=" Availablility: DST-17/09" height="276" width="368"></td>
<td align="center">
<img src="/currcam/<?php if(date(z) < 236 && date(z) > 105): echo '2000currcam.jpg'; endif; ?>" alt="webcam" title=" Availablility: 16/04-23/08" height="276" width="368"></td>
</tr>
<tr> <td align="center"> <b>1900</b> </td> <td align="center"> <b>1930</b> </td> <td align="center"><b>2000</b> </td>
</tr>
<tr> <td color="white" align="center" valign="top"> <span style="color: white;"> - </span> </td></tr>
<?php if(date(z) < 84 || date(z) > 264): echo '-->'; endif; ?>

<?php if(date(z) < 113 || date(z) > 233): echo '<!--'; endif; ?>
<tr> 
<td align="center">
<img src="/currcam/<?php if(date(z) < 234 && date(z) > 112): echo '2030currcam.jpg'; endif; ?>" alt="webcam" title=" Availablility: 23/04-21/08" height="276" width="368"></td>
<td align="center">
<img src="/currcam/<?php if(date(z) < 207 && date(z) > 139): echo '2100currcam.jpg'; endif; ?>" alt="webcam" title=" Availablility: 20/05-25/07" height="276" width="368"></td>
<td align="center">
<img src="/currcam/<?php if(date(z) < 199 && date(z) > 153): echo '2130currcam.jpg'; endif; ?>" alt="webcam" title=" Availablility: 03/06-18/07" height="276" width="368"></td>
</tr>
<tr> <td align="center"> <b>2030</b> </td> <td align="center"> <b>2100</b> </td> <td align="center"><b>2130</b> </td>
</tr>
<?php if(date(z) < 113 || date(z) > 233): echo '-->'; endif; ?>

</tbody></table> 

<p>A blank image means the light intensity was too low to record anything useful</p>

	</div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>
 </body>
</html>