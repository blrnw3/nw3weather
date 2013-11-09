<!-- Session count: <?php $_SESSION['count'][$file] = $_SESSION['count'][$file] + 1; echo $_SESSION['count'][$file]; ?> -->

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.0/jquery.min.js"></script>

<?php
if($auto == 'on' && ($file == 4 || $file == 6 || $file == 10 || ($file > 11 && $file < 20))) {
	if($_SESSION['count'][$file] <= 10 || $me == 1) {
		if($baro >= 0 && $file > 3 && $file < 70) {
			if(date("s")<48 && date("i")%5 == 0): $reftime = 48-date("s"); elseif(date("s")<48): $reftime = 60* (5-(date("i")-0)%5) + 48-date("s");
			else: $reftime = 60* (4-(date("i")-0)%5) + 108-date("s"); endif;
			if($reftime < 60) {
				$reftime = 60;
			}
			echo '<meta http-equiv="refresh" content="', $reftime+1, '" />';
		}
	}
}
?>

<meta name="keywords" content="weather, london, nw3, data, records, statistics, weather station" />
<meta http-equiv="content-type" content="application/xhtml+xml; charset=ISO-8859-1" />
<meta http-equiv="content-language" content="en-GB" />	<?php if(isset($_GET['mob'])) { echo '<!--'; } ?> 
<link rel="stylesheet" type="text/css" href="weather-screen2.css" media="screen" title="screen" />
<link rel="stylesheet" type="text/css" href="weather-print.css" media="print" />	<?php if(isset($_GET['mob'])) { echo '-->'; } ?>

<?php if($file == 1.1 || $auto == 'off' || $_SESSION['count'][$file] > 10 || $file == 30 || $me == 1 || 1<2) { echo '<!--'; $skip_test = 1; } ?>
<script type="text/javascript">
$(document).ready(function() {
 	 $("#lolo").load("./ajax/testhead.php");
	var refreshId = setInterval(function() {
	$("#lolo").load('./ajax/testhead.php?randval='+ Math.round(Math.random()*10));
   }, 11000);
     $.ajaxSetup({ cache: false });
});
</script>
<?php if($skip_test == 1) { echo '-->'; } ?>

<?php if($file != 1 || $auto == 'off') { echo '<!--'; $skip_date = 1;} ?>
<script type="text/javascript">
$(document).ready(function() {
 	 $("#datehead").load("./ajax/datehead.php");
	var refreshId = setInterval(function() {
	$("#datehead").load('./ajax/datehead.php?randval='+ Math.round(Math.random()*10));
   }, 60000);
     //$.ajaxSetup({ cache: false });
});
</script>
<?php if($skip_date == 1) { echo '-->'; } ?>