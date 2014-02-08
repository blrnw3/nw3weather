<?php
$allDataNeeded = true;
include('/home/nwweathe/public_html/basics.php');
require_once ($root.'jpgraph/src/jpgraph.php');
require_once ($root.'jpgraph/src/jpgraph_bar.php');
include($root.'unit-select.php');
include($root.'functions.php');

$dtype = $_GET['type'];
$test_type = $types_all[$dtype];
if($test_type === null) {
	$dtype = 'rain';
}

if(isset($_GET['x'])) { $dimx = ($_GET['x'] > 2000) ? 2000 : (int)$_GET['x']; } else { $dimx = 400; }
if(isset($_GET['y'])) { $dimy = ($_GET['y'] > 1500) ? 1500 : (int)$_GET['y']; } else { $dimy = 200; }
?>
