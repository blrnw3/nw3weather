<?php require('unit-select.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("main_tags.php");
	$file = 20; ?>

<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:x="urn:schemas-microsoft-com:office:excel"
xmlns="http://www.w3.org/TR/REC-html40">

<head>
<title>NW3 Weather - Old(v2) - Climate</title>

	<meta name="description" content="Old v2 - Long-term climate averages for Hampstead, North London NW3.
	30-year period weather averages/means/sums/totals for rain, temperature, air frost, thunder, wind, snow and sun" />

	<? require('chead.php'); ?>
	<link rel="stylesheet" type="text/css" href="excel.css" media="screen" />
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

	<p>The climate of NW3, much like the rest of London, is a function of its proximity to the European continent, positioning close to the North Atlantic
	and the North Sea, and to some extent the Urban Heat Island effect and London's rather northerly latitude.
	The direction of the wind (or more precisely the air mass this brings) is largely responsible for which of these sources most influences the day-to-day weather.
	With the prevailing wind being broadly south-westerly (bringing tropical maritime air), this gives London its climate of consistent rainfall throughout the year,
	relatively low sunshine hour count and few snow days, as well as a lack of extremes of temperature,	those generally coming when the wind switches away from this direction
	-  to the Arctic north or Polar north-east for cold, and to the Continental south or south east for heat. Thunderstorms are not frequent, and generally comparatively weak
	compared to those of the near continent. Days of strong winds do occasionally occur, and along with the odd short-lived heat wave or icy cold snap,
	are the only real hazards in NW3, flooding not being a problem to this hilly area of London, home to inner London's highest point (Whitestone Pond, <?php echo conv(134,7,1); ?>).
	</p>

<h1>Long-term Climate Averages</h1>

<p>These are estimates for the long-term average weather conditions, i.e. the climate, at NW3.
<br /> They were derived from data for the period 1971-2000 - the <acronym title="World Meteorologocal Organisation">WMO</acronym>
 standard referece period - from nearby official Met Office sites.
 (Mostly the one at Whitestone Pond (see above), which although less than a couple of miles away, is some <?php echo conv(80,7,1); ?>
 higher up in terms of elevation.)<br />Some adjustments have therfore been made to reflect the different siting conditions.</p>

<table x:str border=0 cellpadding=0 cellspacing=0 width=1088 style='border-collapse:collapse;table-layout:fixed;width:816pt'>
<col width=19 style='mso-width-source:userset;mso-width-alt:694;width:14pt'>
<col width=99 style='mso-width-source:userset;mso-width-alt:3620;width:74pt'>
<col width=71 span=3 style='mso-width-source:userset;mso-width-alt:2596;
width:53pt'>
<col width=69 style='mso-width-source:userset;mso-width-alt:2523;width:52pt'>
<col width=71 style='mso-width-source:userset;mso-width-alt:2596;width:53pt'>
<col width=73 style='mso-width-source:userset;mso-width-alt:2669;width:55pt'>
<col width=72 style='mso-width-source:userset;mso-width-alt:2633;width:54pt'>
<col width=67 style='mso-width-source:userset;mso-width-alt:2450;width:50pt'>
<col width=76 style='mso-width-source:userset;mso-width-alt:2779;width:57pt'>
<col width=66 style='mso-width-source:userset;mso-width-alt:2413;width:50pt'>
<col width=65 style='mso-width-source:userset;mso-width-alt:2377;width:49pt'>
<col width=62 style='mso-width-source:userset;mso-width-alt:2267;width:47pt'>
<col width=76 style='mso-width-source:userset;mso-width-alt:2779;width:57pt'>
<col width=60 style='mso-width-source:userset;mso-width-alt:2194;width:45pt'>
<tr height=17 style='height:12.75pt'>
<td height=17 width=19 style='height:12.75pt;width:14pt'></td>
<td width=99 style='width:74pt'></td>
<td width=71 style='width:53pt'></td>
<td width=71 style='width:53pt'></td>
<td width=71 style='width:53pt'></td>
<td width=69 style='width:52pt'></td>
<td width=71 style='width:53pt'></td>
<td width=73 style='width:55pt'></td>
<td width=72 style='width:54pt'></td>
<td width=67 style='width:50pt'></td>
<td width=76 style='width:57pt'></td>
<td width=66 style='width:50pt'></td>
<td width=65 style='width:49pt'></td>
<td width=62 style='width:47pt'></td>
<td width=76 style='width:57pt'></td>
<td width=60 style='width:45pt'></td>
</tr>
<tr height=17 style='mso-height-source:userset;height:12.75pt'>
<td height=17 style='height:12.75pt'></td>
<td class=xl24>&nbsp;</td>
<td colspan=4 class=xl135 width=282 style='border-right:.5pt solid black;
border-left:none;width:211pt'>Temperature / &deg;<?php echo $unitT; ?></td>
<td colspan=2 class=xl137 width=144 style='border-right:.5pt solid black;
border-left:none;width:108pt'>Rain / <?php echo $unitR; ?></td>
<td rowspan=2 class=xl139 width=72 style='border-bottom:.5pt solid silver;
width:54pt'>Wind<br>Speed<br />/ <?php echo $unitW; ?></td>
<td colspan=2 class=xl141 width=143 style='border-right:.5pt solid black;
border-left:none;width:107pt'>Days Of</td>
<td colspan=2 class=xl143 width=131 style='border-right:.5pt solid black;
border-left:none;width:99pt'>Days Of Snow</td>
<td rowspan=2 class=xl144 width=62 style='border-bottom:.5pt solid silver;
width:47pt'>Sun Hours</td>
<td rowspan=2 class=xl146 width=76 style='border-bottom:.5pt solid silver;
width:57pt'>Max Possible Sun Hours</td>
<td rowspan=2 class=xl148 width=60 style='border-bottom:.5pt solid silver;
width:45pt'>% of Max</td>
</tr>
<tr height=21 style='mso-height-source:userset;height:15.75pt'>
<td height=21 style='height:15.75pt'></td>
<td class=xl25>&nbsp;</td>
<td class=xl26 width=71 style='width:53pt'>Min</td>
<td class=xl27 width=71 style='width:53pt'>Max</td>
<td class=xl28 width=71 style='width:53pt'>Mean</td>
<td class=xl29 width=69 style='width:52pt'>Range</td>
<td class=xl30>Rainfall</td>
<td class=xl150 width=73 style='width:55pt'>&gt;1mm Days</td>
<td class=xl26 width=67 style='width:50pt'>Air<br>
	<span style='mso-spacerun:yes'> </span>Frost</td>
<td class=xl31>Thunder</td>
<td class=xl32>Lying</td>
<td class=xl31>Falling</td>
</tr>
<tr height=24 style='mso-height-source:userset;height:18.0pt'>
<td height=24 style='height:18.0pt'></td>
<td class=xl33>January</td>
<td class=xl34><?php echo conv(2.4,1,0); ?></td>
<td class=xl35><?php echo conv(7.0,1,0); ?></td>
<td class=xl36><?php echo conv(4.7,1,0); ?></td>
<td class=xl37><?php echo conv4(4.6,1,0); ?></td>
<td class=xl38><?php echo conv3(63,2,0); ?></td>
<td class=xl39>11</td>
<td class=xl40><?php echo conv(5.2,4,0); ?></td>
<td class=xl41>7</td>
<td class=xl42>0.4</td>
<td class=xl43>2.5</td>
<td class=xl42>5</td>
<td class=xl44>46</td>
<td class=xl45>233</td>
<td class=xl46>20%</td>
</tr>
<tr height=24 style='mso-height-source:userset;height:18.0pt'>
<td height=24 style='height:18.0pt'></td>
<td class=xl47>February</td>
<td class=xl48><?php echo conv(2.2,1,0); ?></td>
<td class=xl49><?php echo conv(7.6,1,0); ?></td>
<td class=xl50><?php echo conv(4.9,1,0); ?></td>
<td class=xl51><?php echo conv4(5.4,1,0); ?></td>
<td class=xl52 width=71 style='width:53pt'><?php echo conv3(40,2,0); ?></td>
<td class=xl53>9</td>
<td class=xl54><?php echo conv(5.1,4,0); ?></td>
<td class=xl55>7</td>
<td class=xl56>0.3</td>
<td class=xl57>2.5</td>
<td class=xl56>5</td>
<td class=xl58>75</td>
<td class=xl59>249</td>
<td class=xl60>30%</td>
</tr>
<tr height=24 style='mso-height-source:userset;height:18.0pt'>
<td height=24 style='height:18.0pt'></td>
<td class=xl33>March</td>
<td class=xl34><?php echo conv(3.6,1,0); ?></td>
<td class=xl35><?php echo conv(10.4,1,0); ?></td>
<td class=xl61><?php echo conv(7.0,1,0); ?></td>
<td class=xl37><?php echo conv4(6.8,1,0); ?></td>
<td class=xl62 width=71 style='width:53pt'><?php echo conv3(51,2,0); ?></td>
<td class=xl39>10</td>
<td class=xl40><?php echo conv(5.2,4,0); ?></td>
<td class=xl41>3</td>
<td class=xl42>0.6</td>
<td class=xl43>0.4</td>
<td class=xl42>4</td>
<td class=xl44>105</td>
<td class=xl45>331</td>
<td class=xl46>32%</td>
</tr>
<tr height=24 style='mso-height-source:userset;height:18.0pt'>
<td height=24 style='height:18.0pt'></td>
<td class=xl47>April</td>
<td class=xl48><?php echo conv(5.0,1,0); ?></td>
<td class=xl49><?php echo conv(12.8,1,0); ?></td>
<td class=xl50><?php echo conv(8.9,1,0); ?></td>
<td class=xl51><?php echo conv4(7.8,1,0); ?></td>
<td class=xl52 width=71 style='width:53pt'><?php echo conv3(56,2,0); ?></td>
<td class=xl53>10</td>
<td class=xl54><?php echo conv(4.9,4,0); ?></td>
<td class=xl55>1</td>
<td class=xl56>1</td>
<td class=xl57>0.2</td>
<td class=xl56>2</td>
<td class=xl58>137</td>
<td class=xl59>376</td>
<td class=xl60>36%</td>
</tr>
<tr height=24 style='mso-height-source:userset;height:18.0pt'>
<td height=24 style='height:18.0pt'></td>
<td class=xl33>May</td>
<td class=xl34><?php echo conv(8.3,1,0); ?></td>
<td class=xl35><?php echo conv(16.7,1,0); ?></td>
<td class=xl36><?php echo conv(12.5,1,0); ?></td>
<td class=xl37><?php echo conv4(8.4,1,0); ?></td>
<td class=xl62 width=71 style='width:53pt'><?php echo conv3(59,2,0); ?></td>
<td class=xl39>9</td>
<td class=xl40><?php echo conv(4.7,4,0); ?></td>
<td class=xl41>0.1</td>
<td class=xl42>2</td>
<td class=xl43>0</td>
<td class=xl42>0</td>
<td class=xl44>190</td>
<td class=xl45>440</td>
<td class=xl46>43%</td>
</tr>
<tr height=24 style='mso-height-source:userset;height:18.0pt'>
<td height=24 style='height:18.0pt'></td>
<td class=xl47>June</td>
<td class=xl48><?php echo conv(11.1,1,0); ?></td>
<td class=xl49><?php echo conv(20.2,1,0); ?></td>
<td class=xl63><?php echo conv(15.7,1,0); ?></td>
<td class=xl51><?php echo conv4(9.1,1,0); ?></td>
<td class=xl52 width=71 style='width:53pt'><?php echo conv3(59,2,0); ?></td>
<td class=xl53>9</td>
<td class=xl54><?php echo conv(4.4,4,0); ?></td>
<td class=xl55>0</td>
<td class=xl56>3</td>
<td class=xl57>0</td>
<td class=xl56>0</td>
<td class=xl58>191</td>
<td class=xl59>452</td>
<td class=xl60>42%</td>
</tr>
<tr height=24 style='mso-height-source:userset;height:18.0pt'>
<td height=24 style='height:18.0pt'></td>
<td class=xl33>July</td>
<td class=xl34><?php echo conv(13.4,1,0); ?></td>
<td class=xl35><?php echo conv(22.4,1,0); ?></td>
<td class=xl36><?php echo conv(17.9,1,0); ?></td>
<td class=xl64><?php echo conv4(9.0,1,0); ?></td>
<td class=xl62 width=71 style='width:53pt'><?php echo conv3(47,2,0); ?></td>
<td class=xl39>7</td>
<td class=xl40><?php echo conv(4.3,4,0); ?></td>
<td class=xl41>0</td>
<td class=xl42>2</td>
<td class=xl43>0</td>
<td class=xl42>0</td>
<td class=xl44>185</td>
<td class=xl45>454</td>
<td class=xl46>41%</td>
</tr>
<tr height=24 style='mso-height-source:userset;height:18.0pt'>
<td height=24 style='height:18.0pt'></td>
<td class=xl47>August</td>
<td class=xl48><?php echo conv(13.2,1,0); ?></td>
<td class=xl49><?php echo conv(22.2,1,0); ?></td>
<td class=xl50><?php echo conv(17.7,1,0); ?></td>
<td class=xl65><?php echo conv4(9.0,1,0); ?></td>
<td class=xl52 width=71 style='width:53pt'><?php echo conv3(62,2,0); ?></td>
<td class=xl53>8</td>
<td class=xl54><?php echo conv(4.0,4,0); ?></td>
<td class=xl55>0</td>
<td class=xl56>3</td>
<td class=xl57>0</td>
<td class=xl56>0</td>
<td class=xl58>182</td>
<td class=xl59>410</td>
<td class=xl60>44%</td>
</tr>
<tr height=24 style='mso-height-source:userset;height:18.0pt'>
<td height=24 style='height:18.0pt'></td>
<td class=xl33>September</td>
<td class=xl34><?php echo conv(11.1,1,0); ?></td>
<td class=xl35><?php echo conv(18.7,1,0); ?></td>
<td class=xl36><?php echo conv(14.9,1,0); ?></td>
<td class=xl37><?php echo conv4(7.6,1,0); ?></td>
<td class=xl62 width=71 style='width:53pt'><?php echo conv3(66,2,0); ?></td>
<td class=xl39>9</td>
<td class=xl40><?php echo conv(3.9,4,0); ?></td>
<td class=xl41>0</td>
<td class=xl42>2</td>
<td class=xl43>0</td>
<td class=xl42>0</td>
<td class=xl44>138</td>
<td class=xl45>342</td>
<td class=xl46>40%</td>
</tr>
<tr height=24 style='mso-height-source:userset;height:18.0pt'>
<td height=24 style='height:18.0pt'></td>
<td class=xl47>October</td>
<td class=xl48><?php echo conv(8.8,1,0); ?></td>
<td class=xl49><?php echo conv(14.6,1,0); ?></td>
<td class=xl50><?php echo conv(11.7,1,0); ?></td>
<td class=xl51><?php echo conv4(5.8,1,0); ?></td>
<td class=xl52 width=71 style='width:53pt'><?php echo conv3(69,2,0); ?></td>
<td class=xl53>10</td>
<td class=xl54><?php echo conv(4.1,4,0); ?></td>
<td class=xl55>0.2</td>
<td class=xl56>1</td>
<td class=xl57>0</td>
<td class=xl56>0</td>
<td class=xl58>106</td>
<td class=xl59>295</td>
<td class=xl60>36%</td>
</tr>
<tr height=24 style='mso-height-source:userset;height:18.0pt'>
<td height=24 style='height:18.0pt'></td>
<td class=xl33>November</td>
<td class=xl34><?php echo conv(5.0,1,0); ?></td>
<td class=xl35><?php echo conv(10.2,1,0); ?></td>
<td class=xl36><?php echo conv(7.6,1,0); ?></td>
<td class=xl37><?php echo conv4(5.2,1,0); ?></td>
<td class=xl62 width=71 style='width:53pt'><?php echo conv3(64,2,0); ?></td>
<td class=xl39>10</td>
<td class=xl40><?php echo conv(4.6,4,0); ?></td>
<td class=xl41>2</td>
<td class=xl42>0.4</td>
<td class=xl43>0.3</td>
<td class=xl42>1</td>
<td class=xl44>68</td>
<td class=xl45>237</td>
<td class=xl46>29%</td>
</tr>
<tr height=24 style='mso-height-source:userset;height:18.0pt'>
<td height=24 style='height:18.0pt'></td>
<td class=xl47>December</td>
<td class=xl48><?php echo conv(3.2,1,0); ?></td>
<td class=xl49><?php echo conv(7.8,1,0); ?></td>
<td class=xl50><?php echo conv(5.5,1,0); ?></td>
<td class=xl51><?php echo conv4(4.6,1,0); ?></td>
<td class=xl52 width=71 style='width:53pt'><?php echo conv3(66,2,0); ?></td>
<td class=xl53>11</td>
<td class=xl54><?php echo conv(5.1,4,0); ?></td>
<td class=xl55>6</td>
<td class=xl56>0.3</td>
<td class=xl57>1</td>
<td class=xl56>3</td>
<td class=xl58>48</td>
<td class=xl59>219</td>
<td class=xl60>22%</td>
</tr>
<tr height=21 style='height:15.75pt'>
<td height=21 style='height:15.75pt'></td>
<td class=xl33>&nbsp;</td>
<td class=xl66>&nbsp;</td>
<td class=xl66>&nbsp;</td>
<td class=xl66>&nbsp;</td>
<td class=xl67>&nbsp;</td>
<td class=xl66>&nbsp;</td>
<td class=xl67>&nbsp;</td>
<td class=xl68>&nbsp;</td>
<td class=xl66>&nbsp;</td>
<td class=xl67>&nbsp;</td>
<td class=xl66>&nbsp;</td>
<td class=xl67>&nbsp;</td>
<td class=xl69>&nbsp;</td>
<td class=xl70>&nbsp;</td>
<td class=xl46>&nbsp;</td>
</tr>
<tr height=22 style='mso-height-source:userset;height:16.5pt'>
<td height=22 style='height:16.5pt'></td>
<td class=xl33>Winter</td>
<td class=xl34><?php echo conv(2.6,1,0); ?></td>
<td class=xl35><?php echo conv(7.5,1,0); ?></td>
<td class=xl61><?php echo conv(5.0,1,0); ?></td>
<td class=xl64><?php echo conv4(4.9,1,0); ?></td>
<td class=xl38><?php echo conv3(169,2,0); ?></td>
<td class=xl71>31</td>
<td class=xl40><?php echo conv(5.1,4,0); ?></td>
<td class=xl41>20</td>
<td class=xl42>1</td>
<td class=xl43>6</td>
<td class=xl42>13</td>
<td class=xl44>169</td>
<td class=xl45>701</td>
<td class=xl46>24%</td>
</tr>
<tr height=22 style='mso-height-source:userset;height:16.5pt'>
<td height=22 style='height:16.5pt'></td>
<td class=xl72>Spring</td>
<td class=xl48><?php echo conv(5.6,1,0); ?></td>
<td class=xl49><?php echo conv(13.3,1,0); ?></td>
<td class=xl63><?php echo conv(9.5,1,0); ?></td>
<td class=xl65><?php echo conv4(7.7,1,0); ?></td>
<td class=xl73><?php echo conv3(166,2,0); ?></td>
<td class=xl74>29</td>
<td class=xl54><?php echo conv(4.9,4,0); ?></td>
<td class=xl55>4.1</td>
<td class=xl75>4</td>
<td class=xl57>0.6</td>
<td class=xl56>6</td>
<td class=xl58>432</td>
<td class=xl59>1147</td>
<td class=xl60>38%</td>
</tr>
<tr height=22 style='mso-height-source:userset;height:16.5pt'>
<td height=22 style='height:16.5pt'></td>
<td class=xl76>Summer</td>
<td class=xl34><?php echo conv(12.6,1,0); ?></td>
<td class=xl35><?php echo conv(21.6,1,0); ?></td>
<td class=xl61><?php echo conv(17.1,1,0); ?></td>
<td class=xl64><?php echo conv4(9.0,1,0); ?></td>
<td class=xl38><?php echo conv3(168,2,0); ?></td>
<td class=xl71>24</td>
<td class=xl40><?php echo conv(4.3,4,0); ?></td>
<td class=xl41>0</td>
<td class=xl42>8</td>
<td class=xl43>0</td>
<td class=xl42>0</td>
<td class=xl44>558</td>
<td class=xl45>1316</td>
<td class=xl46>42%</td>
</tr>
<tr height=22 style='mso-height-source:userset;height:16.5pt'>
<td height=22 style='height:16.5pt'></td>
<td class=xl72>Autumn</td>
<td class=xl48><?php echo conv(8.3,1,0); ?></td>
<td class=xl49><?php echo conv(14.5,1,0); ?></td>
<td class=xl63><?php echo conv(11.4,1,0); ?></td>
<td class=xl65><?php echo conv4(6.2,1,0); ?></td>
<td class=xl73><?php echo conv3(199,2,0); ?></td>
<td class=xl74>29</td>
<td class=xl54><?php echo conv(4.2,4,0); ?></td>
<td class=xl55>2.2</td>
<td class=xl75>3</td>
<td class=xl57>0.3</td>
<td class=xl56>1</td>
<td class=xl58>312</td>
<td class=xl59>874</td>
<td class=xl60>36%</td>
</tr>
<tr height=11 style='mso-height-source:userset;height:8.25pt'>
<td height=11 style='height:8.25pt'></td>
<td class=xl77>&nbsp;</td>
<td class=xl78>&nbsp;</td>
<td class=xl79>&nbsp;</td>
<td class=xl80>&nbsp;</td>
<td class=xl81>&nbsp;</td>
<td class=xl82>&nbsp;</td>
<td class=xl83>&nbsp;</td>
<td class=xl84>&nbsp;</td>
<td class=xl85>&nbsp;</td>
<td class=xl86>&nbsp;</td>
<td class=xl87>&nbsp;</td>
<td class=xl86>&nbsp;</td>
<td class=xl88>&nbsp;</td>
<td class=xl89>&nbsp;</td>
<td class=xl90>&nbsp;</td>
</tr>
<tr height=25 style='mso-height-source:userset;height:18.75pt'>
<td height=25 style='height:18.75pt'></td>
<td class=xl91>Sum</td>
<td class=xl92>&nbsp;</td>
<td class=xl93>&nbsp;</td>
<td class=xl94>&nbsp;</td>
<td class=xl95>&nbsp;</td>
<td class=xl96><?php echo conv3(702,2,0); ?></td>
<td class=xl97>113</td>
<td class=xl98>&nbsp;</td>
<td class=xl99>26</td>
<td class=xl100>16</td>
<td class=xl101>7</td>
<td class=xl100>20</td>
<td class=xl102>1471</td>
<td class=xl103>4038</td>
<td class=xl104>36%</td>
</tr>
<tr height=11 style='mso-height-source:userset;height:8.25pt'>
<td height=11 style='height:8.25pt'></td>
<td class=xl77>&nbsp;</td>
<td class=xl105>&nbsp;</td>
<td class=xl105>&nbsp;</td>
<td class=xl105>&nbsp;</td>
<td class=xl106>&nbsp;</td>
<td class=xl105>&nbsp;</td>
<td class=xl106>&nbsp;</td>
<td class=xl107>&nbsp;</td>
<td class=xl105>&nbsp;</td>
<td class=xl106>&nbsp;</td>
<td class=xl105>&nbsp;</td>
<td class=xl106>&nbsp;</td>
<td class=xl108>&nbsp;</td>
<td class=xl109>&nbsp;</td>
<td class=xl90>&nbsp;</td>
</tr>
<tr height=42 style='mso-height-source:userset;height:31.5pt'>
<td height=42 style='height:31.5pt'></td>
<td class=xl110>Annual</td>
<td class=xl111><?php echo conv(7.3,1,0); ?></td>
<td class=xl112><?php echo conv(14.3,1,0); ?></td>
<td class=xl113><?php echo conv(10.8,1,0); ?></td>
<td class=xl114><?php echo conv4(6.9,1,0); ?></td>
<td class=xl115><?php echo conv3(59,2,0); ?></td>
<td class=xl116>9</td>
<td class=xl117><?php echo conv(4.6,4,0); ?></td>
<td class=xl111>2.2</td>
<td class=xl118>1.3</td>
<td class=xl119>1</td>
<td class=xl118>1.6</td>
<td class=xl120>123</td>
<td class=xl120>337</td>
<td class=xl151>36%</td>
</tr>
<tr height=12 style='mso-height-source:userset;height:9.0pt'>
<td height=12 style='height:9.0pt'></td>
<td class=xl76>&nbsp;</td>
<td class=xl66>&nbsp;</td>
<td class=xl66>&nbsp;</td>
<td class=xl66>&nbsp;</td>
<td class=xl67>&nbsp;</td>
<td class=xl66>&nbsp;</td>
<td class=xl67>&nbsp;</td>
<td class=xl68>&nbsp;</td>
<td class=xl66>&nbsp;</td>
<td class=xl67>&nbsp;</td>
<td class=xl66>&nbsp;</td>
<td class=xl67>&nbsp;</td>
<td class=xl69>&nbsp;</td>
<td class=xl69>&nbsp;</td>
<td class=xl46>&nbsp;</td>
</tr>
<tr height=20 style='mso-height-source:userset;height:15.0pt'>
<td height=20 style='height:15.0pt'></td>
<td class=xl121>Range</td>
<td class=xl122><?php echo conv4(11.2,1,0); ?></td>
<td class=xl123><?php echo conv4(15.4,1,0); ?></td>
<td class=xl124><?php echo conv4(13.2,1,0); ?></td>
<td class=xl125><?php echo conv4(4.5,1,0); ?></td>
<td class=xl126><?php echo conv3(29,2,0); ?></td>
<td class=xl127>4</td>
<td class=xl128><?php echo conv(1.4,4,0); ?></td>
<td class=xl129>7</td>
<td class=xl130>3</td>
<td class=xl131>2.5</td>
<td class=xl130>5</td>
<td class=xl132>145</td>
<td class=xl132>235</td>
<td class=xl133>62%</td>
</tr>
</table>

<p>A day-by-day progression of the temperature averages can be found <a href="wxtempltas.php" title="Daily long-term average temperatures">here</a>.</p>

<table cellpadding="15" cellspacing="10" border="0"  width="1000">
<tr> <td align="center" colspan="1">
<img src="/static-images/image006.gif" title="Temperature" alt="temperature climate graph" /></td>
<td align="center" colspan="1">
<img src="/static-images/rainfall2.jpg" title="Rainfall" alt="rainfall climate graph" /></td>
</tr>
<tr> <td align="center" colspan="1">
<img src="/static-images/image003.gif" title="Wind Speed" alt="wind speed climate graph" /></td>
<td align="center" colspan="1">
<img src="/static-images/image005.gif" title="Sunshine" alt="sunshine climate graph" /></td>
</tr>
<tr> <td align="center" colspan="1">
<img src="/static-images/image001.gif" title="Air Frosts and Thunder" alt="AF and thunder climate graph" /></td>
<td align="center" colspan="1">
<img src="/static-images/image002.gif" title="Snow days" alt="snow days climate graph" /></td>
</tr>
</table>

</div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>

</body>
</html>