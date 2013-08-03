<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php 
	$file = 1;
	$subfile = true;
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather Station - Beaufort Scale</title>

	<meta name="description" content="Beaufort Wind Scale descriptions and categories" />

	<?php require('chead.php'); ?>
	<?php include_once("ggltrack.php") ?>
</head>

<body>


	<!-- ##### Header ##### -->
	<?php require('header.php'); ?>

	<!-- ##### Left Sidebar ##### -->
	<?php require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->
<div id="main">

<h1>Beaufort Scale</h1>
<?php
$bftscale = array(0,1,3,7,12,17,24,30,38,46,54,63,73,99);
$bftword = array('Calm', 'Light air', 'Light breeze', 'Gentle breeze', 'Moderate breeze', 'Fresh breeze', 'Strong breeze', 'Near gale', 'Gale',
				'Severe gale', 'Storm', 'Violent storm', 'Hurricane');
$bftdescrip = array(
	'Calm. Smoke rises vertically.',
	'Smoke drift indicates wind direction and wind vanes cease moving.',
	'Wind felt on exposed skin. Leaves rustle and wind vanes begin to move.',
	'Leaves and small twigs constantly moving, light flags extended.',
	'Dust and loose paper raised. Small branches begin to move.',
	'Branches of a moderate size move. Small trees in leaf begin to sway.',
	'Large branches in motion. Whistling heard in overhead wires. Umbrella use becomes difficult. Empty plastic bins tip over.',
	'Whole trees in motion. Effort needed to walk against the wind.',
	'Some twigs broken from trees. Cars veer on road. Progress on foot is seriously impeded.',
	'Some branches break off trees, and some small trees blow over. Construction/temporary signs and barricades blow over.',
	'Trees are broken off or uprooted, saplings bent and deformed. Poorly attached asphalt shingles and shingles in poor condition peel off roofs.',
	'Widespread damage to vegetation. Many roofing surfaces are damaged; asphalt tiles that have curled up and/or fractured due to age may break away completely.',
	'Very widespread damage to vegetation. Some windows may break; mobile homes and poorly constructed sheds and barns are damaged. Debris may be hurled about.'
 );

echo '<table cellpadding="5" width="98%" class="table1">
	<tr class="table-top">
		<td class="td4" width="15%">Beaufort</td>
		<td class="td4" width="15%">1-min Wind speed</td>
		<td class="td4" width="70%">Effects on land</td>
	</tr>';
for($i = 0; $i < 13; $i++) {
	if($i % 2 == 0) { $style = 'light'; } else { $style = 'dark'; }
	echo '<tr class="row', $style, '">
		<td class="td4">', $i, '<br />', $bftword[$i], '</td>
		<td class="td4">', conv($bftscale[$i],4,0,0,-1), ' - ', conv($bftscale[$i+1],4,1,0,-1), '</td>
		<td class="td4">', $bftdescrip[$i], '</td>
		</tr>
		';
}
echo '</table> <br />';
?>
<h2>Alternative, courtesy of NOAA</h2>
	<table cellpadding="5" width="99%" border="0" align="center"><tr><td>
	<img src="/img33_Beaufort_NOAA.gif" alt="Beaufort scale" />
	</td></tr>
	</table>

</div>

<!-- ##### Footer ##### -->
	<?php require('footer.php'); ?>

</body>
</html>