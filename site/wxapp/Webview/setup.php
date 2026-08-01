<?php
$scriptbeg = microtime(get_as_float);
$root = '/var/www/html/';
$appPath = $root.'CP_Solutions/WxApp/';

if(!isset($_SESSION)) {
    session_start();
}

$expTime = 3600 * 24 * 100;
if($_COOKIE['me']) {
    $me = true;
}
elseif(isset($_GET['blr'])) {
    $me = true;
    setcookie("me", true, time() + $expTime * 10);
}
if(isset($_GET['noblr'])) {
    $me = false;
    setcookie("me", false, time() + $expTime);
}

$desktop = isset($_GET['desktop']);
?>