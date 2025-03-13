<!doctype html>
<html lang="en">
	<head>
		<title>NW3 Weather - $this->title</title>
		<meta charset="UTF-8" />
		<meta http-equiv="pragma" content="no-cache" />
		<meta name="author" content="Ben Masschelein Rodgers" />
		<meta name="description" content="$this->description" />
		<meta name="viewport" content="width=420, initial-scale=1.0" />
		<!-- Buffered: $buffered -->
		$metaRefresh
		<link rel="stylesheet" type="text/css" href="/$this->styleSheet.css?updated2024mar8" media="screen" title="screen" />
		$colorCss
		$scripts
	</head>
	<body>
		<div id="background">
			<div id="page">
				<div id="header">
					<div id="banner">
						<div id="banner-main" onclick="location.href='/';">
							<div id="banner-nw3">
								<span class="nw3">nw3</span> weather
							</div>
							<div id="banner-location">
								<div>Hampstead</div>
								<div id="banner-location-comma">,</div>
								<div>London</div>
							</div>
						</div>
						<div id="banner-left"></div>
						<div id="banner-right"></div>
					</div>
					<div id="sub-header">
						<div id="live-wx"></div>
						<div id="last-updated">$updatedAt</div>
					</div>
				</div>
				<div id="nav">

				</div>

				<div id="main">
					<div id="status">
						
					</div>
					<div id="content">

					</div>
				</div>
				<div id="footer">
					<div>
						<a href="#header">Top</a> |
						<a href="contact.php" title="E-mail me">Contact</a> |
						<a href="mob.php" title="Very basic mobile browsing">Mobile</a> |
						<a href="http://nw3weather.co.uk" title="Browse to homepage">Home</a>
					</div>
						<div>
						&#9728; Sister station: <a href="https://rwcweather.com" target="_blank" title="Redwood City Weather, CA">RWC Weather</a>
					</div>
					<div>
						&copy; 2010-<?php echo $dyear; ?>, Ben Masschelein-Rodgers<span> | Site version 3</span>
					</div>
					<div>
						<?php echo $message; ?>
					</div>
					<div>
						<span style="font-size:85%">
							<?php
							echo 'PHP executed '. acronym('Session count: '. $_SESSION['count'][$file], 'in ') . $phpload .'s';
							if($me) {
								$mem_usage = round(memory_get_usage() / 1024 / 1024, 1);
								$mem_peak = round(memory_get_peak_usage() / 1024 / 1024, 1);
								echo "&nbsp; Mem: $mem_usage, Peak mem: $mem_peak";
							}	?>
						</span>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>
