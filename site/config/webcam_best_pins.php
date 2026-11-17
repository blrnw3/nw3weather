<?php
/**
 * Hand overrides for the "Best webcam shots" page (webcambest.php, WebcamBest.php). Edits show on the next page load.
 *
 * A pick is a candidate ID from the contact sheets (e.g. 'JAN-03', see config/webcam_best_candidates.php) or a frame
 * 'Y-m-d HH:MM' (UK local, as in /camchive/hik/Y/m/d/HHMMhik.jpg). Give it as a string, or as an array with a
 * caption: array('JAN-03', 'Rainbow over the Heath'). A candidate ID without a caption uses the candidate's caption.
 *
 * months      calendar month (1-12) => pick    all-time best for that month; wins over the automatic pick.
 *             The frame must be from that month. Optional third array value: camera ('hik' default).
 * past_month  pick or null                      wins while the frame is within the last 30 days
 * past_year   pick or null                      wins while the frame is within the last 365 days
 * exclude     frames the automatic picks must never use: 'Y-m-d HH:MM', a whole day 'Y-m-d',
 *             or a day range 'Y-m-d..Y-m-d'
 */
return array(
	'months' => array(
		// e.g. 1 => 'JAN-03',
	),
	'past_month' => null,
	'past_year' => null,
	'exclude' => array(
		'2019-04-10..2019-06-14', // scaffolding in front of the camera
		'2018-12-30',             // reflections / lights in front of the lens
		'2018-12-07..2018-12-10', // obstruction in the corner
		'2020-06-18 20:30',       // smudge on the lens
		'2020-12-20 15:30',       // smudge on the lens
	),
);
