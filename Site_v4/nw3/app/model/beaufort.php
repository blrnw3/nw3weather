<?php
namespace nw3\app\model;

/**
 *
 * @author Ben LR
 */
class Beaufort {

	public $scale = array(0,1,3,7,12,17,24,30,38,46,54,63,73,99);

	public $word = array('Calm', 'Light air', 'Light breeze', 'Gentle breeze', 'Moderate breeze',
					'Fresh breeze', 'Strong breeze', 'Near gale', 'Gale',
					'Severe gale', 'Storm', 'Violent storm', 'Hurricane');

	public $descrip = array(
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

}

?>
