<?php
use nw3\app\model\Report;
use nw3\app\util\Date;
use nw3\app\util\Html;
use nw3\app\helper\Reports;


$report = new Report($this->varname);
$this->ranknum = $report->sanitise_ranknum($this->ranknum);
$api = new nw3\app\api\Datareport($this->varname);

$ranks = $api->ranks_daily($this->month, $this->ranknum);
$meta = $api->meta();

$all_months = ($this->month === 0);
$mon_pretty = $all_months ? '' : ' for '. Date::mon($this->month);
$mon_st = $all_months ? 'Jan' : Date::short_mon($this->month);
?>

<h1>Daily All-time Rankings<?php echo $mon_pretty ?> - <?php echo $meta['description'] ?> / <?php echo $report->var['unit'] ?></h1>

<div id="data_report_header">
	<form method="get" action="#data_report_header">
		<div>
			<label class="variable">Weather Variable
				<?php Reports::var_dropdown($report->categories, $report->var['id']); ?>
			</label>
			<?php $this->viewette('datareport/_arrow_cycle') ?>
		</div>
		<div>
			<label style="padding-left:25px;padding-right:3px;" class="rep">Month
			<select name="month" onchange="this.form.submit()">
				<option value="0">All</option>
				<?php for($i = 1; $i <= 12; $i++): ?>
				<option value="<?php echo $i ?>" <?php if($i === $this->month) echo Html::select ?>>
					<?php echo Date::short_mon($i) ?>
				</option>
				<?php endfor; ?>
			</select>
			</label>
			<?php $this->viewette('datareport/_arrow_cycle') ?>
			<label style="padding-left:25px" class="rep">Limit
			<select name="ranknum" onchange="this.form.submit()">
				<?php  foreach(Report::$ranknumOptions as $rank_opt): ?>
				<option value="<?php echo $rank_opt ?>" <?php if($rank_opt === $this->ranknum) echo Html::select ?>>
					<?php echo $rank_opt ?>
				</option>
				<?php endforeach; ?>
			</select>
			</label>
		</div>
	</form>
</div>

<?php
$this->viewette('ranks', [
	'ranks' => $ranks['desc'],
	'yest' => $ranks['yest'],
	'rep' => $report,
	'meta' => $meta,
	'caption' => 'Highest',
	'format' => 'd M Y'
]);

if($ranks['yest']) {
	$ranks['yest']['rank'] = $ranks['count_good'] - $ranks['yest']['rank'];
}

$this->viewette('ranks', [
	'ranks' => $ranks['asc'],
	'yest' => $ranks['yest'],
	'rep' => $report,
	'meta' => $meta,
	'caption' => 'Lowest',
	'format' => 'd M Y'
]);

?>

<p>
	Ranked daily data from <?php echo $mon_st ?> 2009 to present.
	For <?php echo $meta['description'] ?>, there are<b> <?php echo $ranks['count_good'] ?> </b>valid
	values from a possible <?php echo $ranks['count_all'] . $mon_pretty ?>.
</p>
<?php $this->js_script('datareport') ?>
