<?php
namespace nw3\app\helper;

use nw3\data\Albums;

/**
 * Albums/Photos helper
 * @author Ben LR
 */
class Photos {

	static function cover_image($alb_num) {
		$album = Albums::$data[$alb_num];
		$random = true; //j for j
		$img = $random ? mt_rand(1, count($album['photos'])) : $album['cover'];

		return 'photos/'. $album['ref'] . $img .'s.JPG';
	}
}

?>
