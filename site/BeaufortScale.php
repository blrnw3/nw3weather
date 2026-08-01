<?php
require("Page.php");
Page::init(array(
	"fileNum" => 1,
	"isSubFile" => true,
	"title" => "Beaufort Scale",
	"description" => "Beaufort Wind Scale descriptions and categories"
));
Page::Start();

$bftscale = array(0, 1, 3, 7, 12, 17, 24, 30, 38, 46, 54, 63, 73, 99);
$bftword = array(
	'Calm', 'Light air', 'Light breeze', 'Gentle breeze', 'Moderate breeze', 'Fresh breeze',
	'Strong breeze', 'Near gale', 'Gale', 'Severe gale', 'Storm', 'Violent storm', 'Hurricane'
);
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
?>

<h1>Beaufort Scale</h1>

<div class="table-scroll">
<table cellpadding="5" width="98%" class="table1">
	<tr class="table-top">
		<td class="td4" width="15%">Beaufort</td>
		<td class="td4" width="15%">1-min Wind speed</td>
		<td class="td4" width="70%">Effects on land</td>
	</tr>
<?php
for ($i = 0; $i < 13; $i++) {
	$style = ($i % 2 == 0) ? 'light' : 'dark';
	echo '<tr class="row' . $style . '">';
	echo '<td class="td4">' . $i . '<br />' . htmlspecialchars($bftword[$i], ENT_QUOTES, 'UTF-8') . '</td>';
	echo '<td class="td4">'
		. Wx::conv($bftscale[$i], Wx::Wind, false, false, -1)
		. ' - '
		. Wx::conv($bftscale[$i + 1], Wx::Wind, true, false, -1)
		. '</td>';
	echo '<td class="td4">' . htmlspecialchars($bftdescrip[$i], ENT_QUOTES, 'UTF-8') . '</td>';
	echo '</tr>';
}
?>
</table>
</div>

<h2>Alternative, courtesy of NOAA</h2>
<div class="beaufort-noaa">
	<img src="/img33_Beaufort_NOAA.gif" alt="Beaufort scale" />
</div>

<?php Page::End(); ?>
