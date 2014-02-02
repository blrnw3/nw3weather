<?php
use nw3\app\util\Date;
use nw3\app\core\Units;
use nw3\app\model\Climate;
use nw3\app\model\Variable;
?>

<h1>Climate of NW3</h1>

<p>
	Much like the rest of London, this is a function of its proximity to the European continent, positioning close to the North Atlantic
	and the North Sea, and to some extent the Urban Heat Island effect and London's rather northerly latitude.
	The direction of the wind (or more precisely the air mass this brings) is largely responsible for which of these sources most influences the day-to-day weather.
	With the prevailing wind being broadly south-westerly (bringing tropical maritime air), this gives London its climate of consistent rainfall throughout the year,
	relatively low sunshine total and few snow days, as well as a lack of extremes of temperature,	those generally coming when the wind switches away from this direction
	-  to the Arctic north or Polar north-east for cold, and to the Continental south or south east for heat.
</p>
<p>
	Thunderstorms are not frequent, and generally comparatively weak
	compared to those of the near continent. Days of strong winds do occasionally occur, and along with the odd short-lived heat wave or icy cold snap,
	are the only real hazards in NW3, flooding not being a problem to this hilly area of London, home to inner London's highest point, Whitestone Pond, at
	134m (440ft).
</p>

<h1>Long-term Climate Averages</h1>

<p>
	These are estimates for the long-term average weather conditions, i.e. the climate, at NW3.
	<br /> They were derived from data for the period 1981-2010 - the <acronym title="World Meteorological Organisation">WMO</acronym>
	 standard reference period - from nearby official Met Office sites.
	 (Mostly the one at Whitestone Pond (see above), which although less than a couple of miles away, is some 80m (260ft)
	 higher-up in terms of elevation).<br />Some adjustments have therefore been made to reflect the different siting conditions.
</p>

<table>
	<thead>
		<tr>
			<td rowspan="2">&nbsp;</td>
			<td colspan="4" width="30%">Temperature / &deg;<?php echo Units::$temp; ?></td>
			<td colspan="2" width="16%">Rain / <?php echo Units::$rain; ?></td>
			<td rowspan="2" width="8%">Wind<br />Speed<br />/ <?php echo Units::$wind; ?></td>
			<td colspan="2" width="15%">Days Of</td>
			<td colspan="2" width="14%">Days Of Snow</td>
			<td rowspan="2" width="7%">Wet<br />Hours</td>
			<td rowspan="2" width="10%">Sun Hrs<br />(% of max)</td>
		</tr>
		<tr>
			<td width="8%">Min</td>
			<td width="8%">Max</td>
			<td width="7%">Mean</td>
			<td width="7%">Range</td>
			<td width="9%">Rainfall</td>
			<td width="7%">&gt;1mm<br />Days</td>
			<td width="8%">Air<br />Frost</td>
			<td width="7%">Thun<br />der</td>
			<td width="7%">Lying</td>
			<td width="7%">Falling</td>
		</tr>
	</thead>
	<?php $climate = new Climate();
		$data = $climate->summary();
		$maxsun = array_pop($data);
		$sun = array_pop($data);
	?>

	<?php foreach(Date::$months3 as $m): ?>
		<tr<?php if($m === D_monthshort): ?> class="current"<?php endif; ?>>
			<td><?php echo $m ?></td>
			<?php foreach ($data as $lta_name => $ltas): ?>
				<?php $val = Variable::conv($ltas['monthly'][$m], $ltas['id'], false, false, $ltas['dpa']) ?>
				<td class="<?php echo Variable::get_class($lta_name) ?>"><?php echo $val ?></td>
			<?php endforeach; ?>
			<td><?php echo $climate->sun_with_maxsun_comparison('monthly', $m) ?></td>
		</tr>
	<?php endforeach; ?>

	<tr>
		<td colspan="<?php echo (count($data)+2) ?>">&nbsp;</td>
	</tr>

	<?php foreach(Date::$seasons as $season): ?>
		<tr<?php if($season === D_seasonname): ?> class="current"<?php endif; ?>>
			<td><?php echo $season ?></td>
			<?php foreach ($data as $lta_name => $ltas): ?>
				<?php $val = Variable::conv($ltas['seasonal'][$season], $ltas['id'], false, false, $ltas['dpa']) ?>
				<td class="<?php echo Variable::get_class($lta_name) ?>"><?php echo $val ?></td>
			<?php endforeach; ?>
			<td><?php echo $climate->sun_with_maxsun_comparison('seasonal', $season) ?></td>
		</tr>
	<?php endforeach; ?>

	<tr>
		<td colspan="<?php echo (count($data)+2) ?>">&nbsp;</td>
	</tr>

	<tr>
		<td>Sum</td>
		<?php foreach ($data as $lta_name => $ltas): ?>
			<?php $val = (Variable::$daily[$lta_name]['summable']) ? Variable::conv($ltas['annual']['sum'], $ltas['id'], false, false, $ltas['dpa']) : '&nbsp;' ?>
			<td class="<?php echo Variable::get_class($lta_name) ?>"><?php echo $val ?></td>
		<?php endforeach; ?>
		<td><?php echo $climate->sun_with_maxsun_comparison('annual', 'sum') ?></td>
	</tr>
	<tr>
		<td>Annual</td>
		<?php foreach ($data as $lta_name => $ltas): ?>
			<?php $val = Variable::conv($ltas['annual']['mean'], $ltas['id'], false, false, $ltas['dpa']) ?>
			<td class="<?php echo Variable::get_class($lta_name) ?>"><?php echo $val ?></td>
		<?php endforeach; ?>
		<td><?php echo $climate->sun_with_maxsun_comparison('annual', 'mean') ?></td>
	</tr>
</table>

<p>A day-by-day progression of the temperature averages can be found <a href="./daily" title="Daily long-term average temperatures">here</a>.</p>

<img class="climate_graph" src="./graph?types=tmin,tmax" alt="climgraph1" />
<img class="climate_graph" src="./graph?types=tmean,trange" alt="climgraph2" />
<img class="climate_graph" src="./graph?types=rain" alt="climgraph3" />
<img class="climate_graph" src="./graph?types=rdays" alt="climgraph3.5" />
<img class="climate_graph" src="./graph?types=wmean" alt="climgraph4" />
<img class="climate_graph" src="./graph?types=days_snow,days_snowfall,days_storm,days_frost" alt="climgraph5" />
<img class="climate_graph" src="./graph?types=wethr" alt="climgraph6" />
<img class="climate_graph" src="./graph?types=sunhr" alt="climgraph7" />
<img class="climate_graph" src="./graph?types=sunmax" alt="climgraph8" />