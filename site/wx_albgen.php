<?php
require("Page.php");
Page::init(array(
	"fileNum" => 7,
	"isSubFile" => true,
	"title" => "Photos",
	"description" => "Weather Station Photography album viewer"
));

$selfPage = 'wx_albgen.php';
$albumFilePrefix = 'wx_alb';
$imgExt = 'jpg';
$imgrefSuffix = '/';
$metaPrefix = 'Weather Station Photography - ';

$albumInfo = ROOT . 'photos/albums/albInfo.php';
if (!is_file($albumInfo)) {
	$albumInfo = ROOT . 'albums/albInfo.php';
}
require $albumInfo;
$albumTitles = $wx_titles;
$albumRefs = $wx_refs;

require("album_viewer.inc.php");
