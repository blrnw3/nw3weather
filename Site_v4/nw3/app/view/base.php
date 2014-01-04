<?php
use nw3\app\util\Html;
?>

<!--<!DOCTYPE html>
<html lang="en">-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta charset='utf-8'>
		<meta name="author" content="Ben Lee-Rodgers" />
		<meta name="description" content="Live and historical weather data and reports from a personal automatic weather station located near Hampstead, North London." />

		<title><?php echo $this->title; ?> - nw3weather</title>

		<link rel="shortcut icon" type="image/x-icon" href="<?php echo ASSET_PATH; ?>favicon.ico" />
		<link rel="stylesheet" type="text/css" href="<?php echo ASSET_PATH; ?>css/global.css" media="screen" title="screen" />

		<script src="<?php echo ASSET_PATH; ?>js/lib/jquery.js"></script>
		<script src="<?php echo ASSET_PATH; ?>js/global.js"></script>
		<?php if($include_analytics): ?>
			<script src="<?php echo ASSET_PATH; ?>js/analytics.js"></script>
		<?php endif ?>
	</head>

	<body>
		<div id="background">
			<div id="page">
				<div id="header">
					<table align="center" width="100%" cellpadding="0" cellspacing="0"><tr>
					<td align="left" valign="top">
						<img src="<?php echo ASSET_PATH; ?>img/leftheadN.JPG" alt="lefthead-anemometer" width="114" height="100" />
					</td>
					<td align="center">
						<a href="<?php echo \Config::HTML_ROOT; ?>" title="Browse to homepage">
							<img src="<?php echo ASSET_PATH; ?>img/newmain.jpg" alt="mainimage_nw3weather" width="698" height="100" />
						</a>
					</td>
					<td align="right" valign="top">
						<img src="<?php echo ASSET_PATH; ?>img/rightheadS.JPG" alt="righthead-weather_box" width="175" height="100" />
					</td>
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
							<?php $sidebar->group('main'); ?>

							<?php $sidebar->subheading("Detailed Data", "38610B"); ?>
							<?php $sidebar->group('detail'); ?>

							<?php $sidebar->subheading("Historical", "0B614B"); ?>
							<?php $sidebar->group('historical'); ?>

							<?php $sidebar->subheading("Other", "5B9D4B"); ?>
							<?php $sidebar->group('other'); ?>
						</ul>
						<p class="sideBarTitle">Site Options</p>
					</div>
				</div>

				<input id="constants-time" type="hidden" value="<?php echo D_now ?>" />

				<div id="main">
					<?php require $this->view; ?>
				</div>
				<div id="main_base"></div>
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
						Script executed <abbr title="Session Cnt: <?php echo $session_page_count; ?>">in</abbr> <?php echo $script_load_time; ?>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>
