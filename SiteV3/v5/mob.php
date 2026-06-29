<?php
require("Page.php");
$reload = isset($_GET['reload']) ? max(10, intval($_GET['reload'])) : 30;
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title>NW3 Weather - Mobile</title>
	<meta name="description" content="NW3 weather mobile site. Live data table and daily extremes. Optimised for mobile / handheld browsing." />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta http-equiv="pragma" content="no-cache" />
	<link rel="stylesheet" type="text/css" href="/v5/mainstyle_v5.css" media="screen" />
	<style>
		body { margin: 0; padding: 0.5em; font-family: sans-serif; }
		#mob-body { min-height: 200px; }
		#mob-links ul { padding-left: 1.2em; }
	</style>
	<?php include_once("ggltrack.php"); ?>
</head>
<body>
	<div id="mob-body"><p>Current data loading&hellip; Please wait.</p>
		<noscript><p><b>Warning:</b> JavaScript is required for live updates.</p></noscript>
	</div>

	<div id="mob-links">
		Links:
		<ul>
			<li><a href="/">Main Site</a></li>
			<li><a href="#graph" onclick="document.getElementById('mob-graph').style.display='block';return false;">Latest Graph</a></li>
		</ul>
		<a name="graph"></a>
		<img id="mob-graph" style="display:none;max-width:100%" src="/stitchedmaingraph_small.png" alt="24hr weather graph" title="Latest weather data over the last 24hrs" />
	</div>

	<script type="text/javascript">
	//<![CDATA[
		var reloadMs = <?php echo $reload * 1000; ?>;
		function loadBody() {
			fetch('ajaxwxbody.php?r=' + Math.random())
				.then(function(r) { return r.text(); })
				.then(function(html) { document.getElementById('mob-body').innerHTML = html; })
				.catch(function() {});
		}
		loadBody();
		setInterval(loadBody, reloadMs);
	//]]>
	</script>
</body>
</html>
