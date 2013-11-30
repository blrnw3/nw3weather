<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset='utf-8'>
		<meta name="author" content="Ben Lee-Rodgers" />
		<meta name="description" content="Live and historical weather data and reports from a personal automatic weather station located near Hampstead, North London." />

		<title><?php echo $this->title; ?> - nw3weather</title>

		<link rel="shortcut icon" type="image/x-icon" href="static/favicon.ico" />
		<link rel="stylesheet" type="text/css" href="static/css/global.css" media="screen" title="screen" />

		<script src="static/js/lib/jquery.js"></script>
		<script src="static/js/global.js"></script>
		<?php if($include_analytics): ?>
			<script src="static/js/analytics.js"></script>
		<?php endif ?>
	</head>

	<body>
		<div id="background">
			<div id="page">
				<div id="header">
					<table align="center" width="100%" cellpadding="0" cellspacing="0"><tr>
					<td align="left" valign="top"> <img src="static/img/leftheadN.JPG" alt="lefthead-anemometer" width="114" height="100" /></td>
					<td align="center"> <a href="./" title="Browse to homepage"> <img src="static/img/newmain.jpg" alt="mainimage_nw3weather" width="698" height="100" /> </a> </td>
					<td align="right" valign="top"> <img src="static/img/rightheadS.JPG" alt="righthead-weather_box" width="175" height="100" /></td>
					</tr></table>
					<div class="subHeader">
						<span id="currms"></span>
					</div>
					<div class="subHeaderR">
						<?php if($show_sneaky_nw3_header): ?>
							<h4 style="display: inline; padding: 0; margin: 0 10em 0 0; color:#565;">
								nw3 weather, Hampstead London England UK
							</h4>
						<?php endif; ?>
						<span style="text-align:right"><?php echo $nw3_time; ?></span>
					</div>
				</div>

				<div id="side-bar">
					<div class="leftSideBar">
						<p class="sideBarTitle">Navigation</p>
						<ul>

						</ul>
						<p class="sideBarTitle">Site Options</p>
					</div>
				</div>

				<div id="main">
					<?php require $this->view; ?>
				</div>

				<br />
				<div id="footer">
					<div id="footer-links">
						<a href="#header">Top</a> |
						<a href="contact.php" title="E-mail me">Contact</a> |
						<a href="http://nw3weather.co.uk" title="Browse to homepage">Home</a>
					</div>
					<div id="copyright">
						&copy; 2010-<?php echo $current_year; ?>, BLR<span> | Site version 4</span>
					</div>
					<div id="footer-message">
						Caution: All data is recorded from an amateur-run personal weather station; accuracy and reliability may be poor.
					</div>
					<div id="script_details">
						Script executed <abbr title="Session Cnt: <?php echo $session_page_count; ?>">in</abbr> <?php echo $script_load_time; ?> s
					</div>
				</div>
			</div>
		</div>
	</body>
</html>
