<?php 
$ipfull = $_SERVER['REMOTE_ADDR']; $ip = explode('.',$ipfull);
// $ips = array($ip[0], $ip[1], $ip[2], $ip[3]);
$location = 1;
if(isset($_COOKIE['me']) || isset($_GET['notrack'])) { echo '<!--';	$skip = 1; } 
	elseif(strpos($_SERVER['HTTP_USER_AGENT'],'zilla/5.0 (Windows NT 6.0; rv:7.0.1) Gecko/20100101 Firefox/7.0.1') > 0 && $location = 1) {
		if($ip[0] == 86 || $ip[0] == 109) {
			echo '<!--';
			$skip = 1;
		}
	}
if ($_SESSION['count'][$file] > 5 && $skip != 1) { $skip = 1; echo '<!--'; }
?>

<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-22722871-1']);
  _gaq.push(['_trackPageview']);
  _gaq.push(['_trackPageLoadTime']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
<?php 
if($skip == 1) {
	echo '-->';
}
?>