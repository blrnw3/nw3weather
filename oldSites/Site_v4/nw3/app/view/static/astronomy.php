<?php
use nw3\app\model\Astro;
use nw3\app\util\Html;
use nw3\app\util\Time;

$astro = new Astro();
?>

<h1>Basic Sun and Moon Data for London NW3</h1>

<div class="centred">
	<div id="astro_header">
		<div class="key_detail">
			<h2>Sun</h2>
			<div><?php Html::img('sunicon.jpg', 'sun', null, 'width="104" height="72"'); ?></div>
			<div>Sunrise: <?php echo Time::stamp(D_sunrise); ?></div>
			<div>Sunset: <?php echo Time::stamp(D_sunset); ?></div>
			<div>Daylight: <?php echo Time::pretty_duration($astro->day_length); ?></div>
		</div>
		<div class="key_detail">
			<h2>Moon</h2>
			<div><?php Html::img("moon_phases/{$astro->moon_img_num}.jpg", $astro->moonphasename, null, 'width="90" height="90"'); ?></div>
			<div>Moonrise: <?php echo $astro->moonrise; ?></div>
			<div>Moonset: <?php echo $astro->moonset; ?></div>
			<div>Phase: <?php echo $astro->moonphasename; ?>, <?php echo $astro->illumination ?></div>
		</div>
		<div class="moon_phase_stages">
			<div>
				<div>First Quarter Moon</div>
				<div><?php Html::img('firstquartermoon.jpg', 'First Quarter Moon', 'moon_phase'); ?></div>
				<div><?php echo $astro->moon_phase_dates['first']; ?></div>
			</div>
			<div>
				<div>Full Moon</div>
				<div><?php Html::img('fullmoon.jpg', 'Full Moon', 'moon_phase'); ?></div>
				<div><?php echo $astro->moon_phase_dates['full']; ?></div>
			</div>
			<div>
				<div>Last Quarter Moon</div>
				<div><?php Html::img('lastquartermoon.jpg', 'Last Quarter Moon', 'moon_phase'); ?></div>
				<div><?php echo $astro->moon_phase_dates['last']; ?></div>
			</div>
			<div>
				<div>New Moon</div>
				<div><?php Html::img('newmoon.jpg', 'New Moon', 'moon_phase'); ?></div>
				<div><?php echo $astro->moon_phase_dates['new']; ?></div>
			</div>
		</div>
	</div>

	<h3>Earth View</h3>
	<a href="http://www.fourmilab.ch/earthview/" target="_blank">
		<img src="http://www.fourmilab.ch/cgi-bin/Earth?img=learth.evif&amp;imgsize=320&amp;dynimg=y&amp;opt=-p&amp;lat=&amp;lon=&amp;alt=&amp;tle=&amp;date=0&amp;utc=&amp;jd=" width="640" height="320" border="0" alt="Earth Daylight &amp; Darkness" />
	</a>
	<br />Current location of sunlight/darkness across the globe
	<a href="http://www.fourmilab.ch/earthview/credits.php" title="Credit">(Credit)</a>

	<table>
		<caption>Sun Detail</caption>
		<thead>
			<tr>
				<td>Measure</td>
				<td>Value</td>
			</tr>
		</thead>
		<tr>
			<td>Sun Transit</td>
			<td><?php echo $astro->suntransit; ?></td>
		</tr>
		<tr>
			<td>AM Twilight Starts</td>
			<td><?php echo Time::stamp($astro->twirise); ?></td>
		</tr>
		<tr>
			<td>PM Twilight Ends</td>
			<td><?php echo Time::stamp($astro->twiset); ?></td>
		</tr>
		<tr>
			<td>Day Length</td>
			<td><?php echo Time::pretty_duration($astro->day_length); ?></td>
		</tr>
		<tr>
			<td>Change since Yesterday</td>
			<td><?php echo Time::pretty_duration($astro->day_length_change, true); ?></td>
		</tr>
		<tr>
			<td>Change since Summer Solstice</td>
			<td><?php echo Time::pretty_duration($astro->day_length_change_summer, true); ?></td>
		</tr>
		<tr>
			<td>Chance since Winter Solstice</td>
			<td><?php echo Time::pretty_duration($astro->day_length_change_winter, true); ?></td>
		</tr>
		<tr>
			<td>---</td>
			<td>---</td>
		</tr>
		<tr>
			<td>Vernal Equinox</td>
			<td><?php echo $astro->marchequinox; ?></td>
		</tr>
		<tr>
			<td>Summer Solstice</td>
			<td><?php echo $astro->junesolstice; ?></td>
		</tr>
		<tr>
			<td>Autumnal Equinox</td>
			<td><?php echo $astro->sepequinox; ?></td>
		</tr>
		<tr>
			<td>Winter Solstice</td>
			<td><?php echo $astro->decsolstice; ?></td>
		</tr>
		<tr>
			<td>Next Solar Eclipse</td>
			<td><?php echo $astro->suneclipse; ?></td>
		</tr>
	</table>

	<table>
		<caption>Moon Detail</caption>
		<thead>
			<tr>
				<td>Measure</td>
				<td>Value</td>
			</tr>
		</thead>
		<tr>
			<td>Moon Age</td>
			<td><?php echo Time::pretty_duration($astro->moon_age) ?></td>
		</tr>
		<tr>
			<td>Moon Transit</td>
			<td><?php echo str_ireplace('local', D_dst, $astro->moontransit); ?> </td>
		</tr>
		<tr>
			<td>Next Lunar Eclipse</td>
			<td><?php echo $astro->mooneclipse; ?></td>
		</tr>
		<tr>
			<td>Next Moon <acronym title="Nearest approach of Moon to Earth">Perigee</acronym></td>
			<td><?php echo $astro->moonperigee; ?></td>
		</tr>
		<tr>
			<td>Next Moon <acronym title="Furthest approach of Moon to Earth">Apogee</acronym></td>
			<td><?php echo $astro->moonapogee; ?></td>
		</tr>
	</table>

	<p>If not explicit in the value, times are local time (<?php echo D_dst; ?>)</p>
</div>
