<?php
require("Page.php");
Page::init(array(
	"fileNum" => 7,
	"isSubFile" => true,
	"title" => "Photos",
	"description" => "Weather Photography album viewer"
));

$selfPage = 'albgen.php';
$albumFilePrefix = 'alb';
$imgExt = 'JPG';
$imgrefSuffix = '';
$metaPrefix = 'Weather Photography - ';

$albumInfo = ROOT . 'photos/albums/albInfo.php';
if (!is_file($albumInfo)) {
	$albumInfo = ROOT . 'albums/albInfo.php';
}
require $albumInfo;
$albumTitles = $titles;
$albumRefs = $refs;

require("album_viewer.inc.php");
