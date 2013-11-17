<?php
namespace data;

/**
 * Read-only store of climate / Long-term average (LTA) data
 * Intended to be updated no more than once per year so some pre-calculations are hard-coded in (e.g. tmean)
 */
class climate {
	public static $LTA = array(

		'tmin' => array( 3.0,2.5, 3.8,5.5,8.9, 11.4,13.9,14.0, 11.7,9.3,5.1, 3.1 ),
		'tmax' => array( 7.3,7.8, 10.5,13.0,16.8, 19.9,21.9,21.7, 18.7,14.6,10.2, 7.9 ),
		'tmean' => array( 5.1,5.1, 7.2,9.2,12.9, 15.6,17.9,17.8, 15.2,11.9,7.6, 5.5 ),
		'trange' => array( 4.3,5.3, 6.7,7.5,7.9, 8.5,8.0,7.6, 7.0,5.3,5.1, 4.8 ),

		'wmean' => array( 5.2,5.1, 5.2,4.9,4.7, 4.4,4.3,4.0, 3.9,4.1,4.6, 5.1 ),

		'rain' => array( 55,40, 44,49,51, 55,42,53, 57,65,56, 56 ),
		'rdays' => array( 11,9, 10,10,9, 9,8,8, 9,11,10, 11 ),

		'sunhr' => array( 55,73, 106,148,178, 179,187,187, 135,107,69, 50 ),
		'wethr' => array( 67,52, 53,46,40, 37,34,38, 41,49,63, 62 ),

		'days_af' => array(7,7,3,1,0.1,0,0,0,0,0.2,2,6),
		'days_ts' => array(0.4,0.3,0.6,1.0,2.0,3.0,2.5,2.5,2.0,1.0,0.4,0.3),
		'days_ls' => array(2.5,2.5,0.4,0.2,0,0,0,0,0,0,0.3,1),
		'days_fs' => array(5,5,4,2,0,0,0,0,0,0,1,3),

		'sunmax' => array( 233,249, 331,376,440, 452,454,410, 342,295,237, 219 )
	);
}
?>
