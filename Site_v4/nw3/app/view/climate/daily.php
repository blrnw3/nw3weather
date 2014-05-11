<?php
use nw3\app\util\Date;
use nw3\app\core\Units;
use nw3\app\model\Climate;
use nw3\app\model\Variable;
?>

<h1>Daily long-term temperature averages / <?php echo Units::$temp; ?> &nbsp; (and Max Sun Hrs)</h1>

<p>
	These figures are based on the monthly values, but the detail of the intra-month progression is derived from analysis of
	<abbr title="Central England Temperature: The CET series is the longest continuous temperature record in the world">CET</abbr>
	figures from the last hundred years. The data has been deliberately smoothed to provide a more realistic basis for anomalies.<br />
	Raw CET data are <a href="http://www.metoffice.gov.uk/hadobs/hadcet/data/download.html" title="Hadobs data download">
		obtainable from the	Met Office</a>.
	<br /> <br />
	<a href="#current_month">Jump to current month</a><br />
	<a href="#graph" title="Click to view graph">Jump to summary graph</a>
</p>

<table>
	<thead>
		<tr>
			<td rowspan="2" colspan="2" width="15%">Date</td>
			<td colspan="4" width="60%">Temperature / &deg;<?php echo Units::$temp; ?></td>
			<td rowspan="2" width="12%">Sun Hrs</td>
			<td rowspan="2" width="13%">Max <br />Sun Hrs</td>
		</tr>
			<tr>
			<td width="15%">Min</td>
			<td width="15%">Max</td>
			<td width="15%">Mean</td>
			<td width="15%">Range</td>
		</tr>
	</thead>
	<tbody>

<?php $climate = new Climate();
	$daily = $climate->daily_ltas();
?>
<?php for($m = 1; $m <= 12; $m++): ?>
	<?php $dim = Date::get_days_in_month($m); ?>

	<tr<?php if($m === (int)D_month): ?> id="current_month"<?php if(D_day == 1): ?> class="current"<?php endif; ?><?php endif; ?>>
		<td rowspan="<?php echo $dim ?>" class="month_multispan<?php if($m % 2 === 0): ?> odd<?php endif?>"><?php echo Date::$months[$m-1] ?></td>

	<?php for($d = 1; $d <= $dim; $d++): ?>
		<?php $doy = Date::get_z($d, $m); ?>

		<?php if($d > 1): ?>
			<tr<?php if($doy === D_doy): ?> class="current"<?php endif; ?>>
		<?php endif; ?>

		<td><?php echo $d ?></td>

		<?php foreach ($daily as $lta_name => $ltas): ?>
			<?php $val = Variable::conv($ltas['values'][$doy], $ltas['id'], false, false, $ltas['dpa']) ?>
			<td class="<?php echo Variable::get_class($lta_name) ?>"><?php echo $val ?></td>
		<?php endforeach; ?>
		</tr>
	<?php endfor; ?>

<?php endfor; ?>
	</tbody>
</table>

<p>
<a name="graph"></a>
<img src="./graphyear?types=tmin,tmax" alt="graph year 1" />
<img src="./graphyear?types=tmean,trange" alt="graph year 2" />
<img src="./graphyear?types=sunmax,sunhr,lol" alt="graph year 3" />
</p>