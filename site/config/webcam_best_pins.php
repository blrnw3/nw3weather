<?php
/**
 * Hand overrides for the "Best webcam shots" page (webcambest.php, WebcamBest.php). Edits show on the next page load.
 *
 * A pick is a candidate ID from the contact sheets (e.g. 'JAN-03', see config/webcam_best_candidates.php) or a frame
 * 'Y-m-d HH:MM' (UK local, as in /camchive/hik/Y/m/d/HHMMhik.jpg). Give it as a string, or as an array with a
 * caption: array('JAN-03', 'Rainbow over the Heath'). A candidate ID without a caption uses the candidate's caption.
 *
 * months      calendar month (1-12) => pick, or a list of picks shown in that order, e.g.
 *             array('JAN-05', 'JAN-06', array('JAN-08', 'caption')). All-time best for that month; replaces the
 *             automatic pick. Frames must be from that month. Optional third array value: camera ('hik' default).
 *             Months with no picks show the automatic pick.
 * past_month  pick or null                      wins while the frame is within the last 30 days
 * past_year   pick or null                      wins while the frame is within the last 365 days
 * exclude     frames the automatic picks must never use: 'Y-m-d HH:MM', a whole day 'Y-m-d',
 *             or a day range 'Y-m-d..Y-m-d'
 */
return array(
	'months' => array(
		1 => array('JAN-05', 'JAN-06', 'JAN-08'),
		2 => array('FEB-02', 'FEB-04', 'FEB-05', 'FEB-10'),
		3 => array('MAR-01', 'MAR-06', 'MAR-03'),
		4 => array('APR-03', 'APR-05', 'APR-02'),
		5 => array('MAY-06', 'MAY-08', 'MAY-10', 'MAY-03'),
		6 => array('JUN-02', 'JUN-04', 'JUN-10', 'JUN-07'),
		7 => array('JUL-05', 'JUL-10', 'JUL-03', 'JUL-01'),
		8 => array('AUG-10', 'AUG-08', 'AUG-01', 'AUG-02'),
		9 => array('SEP-01', 'SEP-03', 'SEP-10', 'SEP-02'),
		10 => array('OCT-04'),
		11 => array('NOV-02', 'NOV-09'),
		12 => array('DEC-03', 'DEC-04'),
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
