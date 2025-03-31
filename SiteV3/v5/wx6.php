<?php

require("Page.php");
Page::init([
	"fileNum" => 6,
	"title" => "Sun and Moon",
	"description" => 'Sun, Moon, and other astronomy, for Hampstead, North London; provided by NW3 weather.'
]);
Page::Start();

require Site::$rareTags;
$zenithTwi = 96;
$twilightAM = date_sunrise(time(), SUNFUNCS_RET_STRING, site::LATITUDE, site::LONGITUDE, $zenithTwi, date('I'));
$twilightPM = date_sunset(time(), SUNFUNCS_RET_STRING, site::LATITUDE, site::LONGITUDE, $zenithTwi, date('I'));
$splitmoon = explode(" ", $moonage);
$moonImg = "moon13.jpg";
if ($splitmoon[2] < 2): $moonImg = 'moon1.jpg';
elseif ($splitmoon[2] >= 2 && $splitmoon[2] < 4): $moonImg = 'moon2.jpg';
elseif ($splitmoon[2] >= 4 && $splitmoon[2] < 6): $moonImg = 'moon3.jpg';
elseif ($splitmoon[2] >= 6 && $splitmoon[2] < 9): $moonImg = 'moon4.jpg';
elseif ($splitmoon[2] >= 9 && $splitmoon[2] < 11): $moonImg = 'moon5.jpg';
elseif ($splitmoon[2] >= 11 && $splitmoon[2] < 13): $moonImg = 'moon6.jpg';
elseif ($splitmoon[2] >= 13 && $splitmoon[2] < 16): $moonImg = 'moon7.jpg';
elseif ($splitmoon[2] >= 16 && $splitmoon[2] < 18): $moonImg = 'moon8.jpg';
elseif ($splitmoon[2] >= 18 && $splitmoon[2] < 21): $moonImg = 'moon9.jpg';
elseif ($splitmoon[2] >= 21 && $splitmoon[2] < 23): $moonImg = 'moon10.jpg';
elseif ($splitmoon[2] >= 23 && $splitmoon[2] < 26): $moonImg = 'moon11.jpg';
elseif ($splitmoon[2] >= 26 && $splitmoon[2] < 28): $moonImg = 'moon12.jpg';
endif;
$moonImg .= "?" . time();
?>

<h1>Basic Astronomy Data for London</h1>

<div id="sun-moon">
	<div>
		<div>
			<h3>Sun</h3>
			<div style="height:90px;"><img border="0" src="/static-images/sunicon.jpg" width="104" height="72" alt="Sun" /></div>
			<div>Sunrise: <?php echo Date::$sunrise; ?></div>
			<div>Sunset: <?php echo  Date::$sunset; ?></div>
			<div>Daylight: <?php echo $hoursofpossibledaylight; ?></div>
		</div>
		<div>
			<h3>Moon</h3>
			<div><img border="0" src="/static-images/<?php echo $moonImg; ?>" width="90" height="90" alt="Moon" /></div>
			<div>Moonrise: <?php echo $moonrise; ?></div>
			<div>Moonset: <?php echo $moonset; ?></div>
			<div>Illumination: <?php echo $moonphase; ?></div>
		</div>
	</div>
	<div>
		<div>
			<div>First Quarter Moon</div>
			<div><img src="/static-images/firstquartermoon.jpg" width="87" height="75" alt="First Quarter Moon" /></div>
			<div><?php echo $firstquarter; ?></div>
		</div>
		<div>
			<div>Full Moon</div>
			<div><img src="/static-images/fullmoon.jpg" width="87" height="75" alt="First Quarter Moon" /></div>
			<div><?php echo $fullmoon; ?></div>
		</div>
		<div>
			<div>Last Quarter Moon</div>
			<div><img src="/static-images/lastquartermoon.jpg" width="87" height="75" alt="First Quarter Moon" /></div>
			<div><?php echo $lastquarter; ?></div>
		</div>
		<div>
			<div>New Moon</div>
			<div><img src="/static-images/newmoon.jpg" width="87" height="75" alt="First Quarter Moon" /></div>
			<div><?php echo $nextnewmoon; ?></div>
		</div>
	</div>

</div>

<div class="center">

<h2>Earth View</h2>
<div id="earthviewer">
	<a href="http://www.fourmilab.ch/earthview/" target="_blank">
		<img src="http://www.fourmilab.ch/cgi-bin/Earth?img=learth.evif&amp;imgsize=500&amp;dynimg=y&amp;opt=-p&amp;lat=&amp;lon=&amp;alt=&amp;tle=&amp;date=0&amp;utc=&amp;jd="
			alt="Earth Daylight &amp; Darkness" />
	</a>
	<br />
	<span>Current location of sunlight/darkness across the globe</span>
</div>

<h2>Sun Detail</h2>
	<div class="kv-table">
		<div class="rowlight">
			<div>Sun Transit</div>
			<div><?php echo substr($suntransit, 0, 5); ?> </div>
		</div>
		<div class="rowdark">
			<div>AM Twilight Starts</div>
			<div><?php echo $twilightAM; ?> </div>
		</div>
		<div class="rowlight">
			<div>PM Twilight Ends</div>
			<div><?php echo $twilightPM; ?> </div>
		</div>
		<div class="rowdark">
			<div>Day Length</div>
			<div><?php echo $hoursofpossibledaylight; ?></div>
		</div>
		<div class="rowlight">
			<div>Change since Yesterday</div>
			<div><?php echo $changeinday; ?> </div>
		</div>
		<div class="rowdark">
			<div>Change since Summer Solstice</div>
			<div><?php echo $changeindayjun; ?></div>
		</div>
		<div class="rowlight">
			<div>Chance since Winter Solstice </div>
			<div><?php echo $changeindaydec; ?> </div>
		</div>
		<div class="rowdark empty">
			<div></div>
			<div></div>
		</div>
		<div class="rowlight">
			<div>Vernal Equinox</div>
			<div><?php echo $marchequinox; ?></div>
		</div>
		<div class="rowdark">
			<div>Summer Solstice</div>
			<div><?php echo $junesolstice; ?> </div>
		</div>
		<div class="rowlight">
			<div>Autumnal Equinox</div>
			<div><?php echo $sepequinox; ?> </div>
		</div>
		<div class="rowdark">
			<div>Winter Solstice</div>
			<div><?php echo $decsolstice; ?> </div>
		</div>
		<div class="rowlight">
			<div>Next Solar Eclipse</div>
			<div><?php echo $suneclipse; ?> </div>
		</div>
	</div>

<h2>Moon Detail</h2>

<div class="kv-table">
	<div class="rowlight">
		<div>Moon Age</div>
		<div><?php $splitmoon2 = explode(",", $moonage);
					 echo $splitmoon[2], ' days, ', $splitmoon2[1], ' (', str_ireplace(' moon', '', $moonphasename);
				?>)
		</div>
	</div>
	<div class="rowdark">
		<div>Moon Transit</div>
		<div><?php echo str_ireplace('local', Date::$dst, $moontransit); ?> </div>
	</div>
	<div class="rowlight">
		<div>Next Lunar Eclipse</div>
		<div><?php echo $mooneclipse; ?></div>
	</div>
	<div class="rowdark">
		<div>Next Moon <abbr title="Nearest approach of Moon to Earth">Perigee</abbr></div>
		<div><?php echo $moonperigee; ?> </div></div>
	<div class="rowlight">
		<div>Next Moon <abbr title="Furthest approach of Moon to Earth">Apogee</abbr></div>
		<div><?php echo $moonapogee; ?> </div>
	</div>
</div>

<p>If not explicit in the value, times are local time (<?php echo Date::$dst; ?>)</p>
</div>


<?php Page::End(); ?>
