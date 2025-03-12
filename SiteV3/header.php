<div id="background">
<div id="page">
<?php
//$timestampWD = sysWDtimes(false);
$timestampWD = $unix;
if(strlen($timeRare) > 2 && $file > 1) {
	$dateaRare = explode("/", $dateRare);
	$dateGoodRare = date('Y-m-d', mkdate($dateaRare[1],$dateaRare[0],$dateaRare[2]));
	$timestampWDRare = strtotime("$timeRare $dateGoodRare");
	$dateTimeStamp = $timestampWDRare;
} else {
	$dateTimeStamp = $timestampWD;
}
$timestampCrontags = filemtime(ROOT.'RainTags.php');
$timeStampBest = min($timestampCrontags, $dateTimeStamp); //oldest
echo "<!-- WDtimestamp: $dateTimeStamp, CrontagsTimestamp: $timestampCrontags -->";

if($file == 20): $shr = 'Last Updated: Jan 2022'; //climate
elseif($file == 7 && !$subfile): $shr = 'Last Upload: Sep 2017';
elseif($file == 7 && $subfile): $shr = 'Uploaded on ' . $uploadDate;
elseif($file == 8): $shr = 'Last Updated: Mar 2023';
elseif($file == 9): $shr = 'Page Last Updated: Mar 2013';
elseif($file == 0): $shr = ''; //blank for generic e.g. error pages
else: $shr = 'Last Full Update: '. date('d M Y, H:i ', $timeStampBest) . $dst;
endif;
?>

<script type='text/javascript'>
//<!--
function shownewhead() {
	var curr = new Date(); var currms = curr.getTime();
	var currsec = Math.round(currms / 1000); var data = '';
	if(currsec % 16 < 4) { data = "<?php echo 'Temperature: ',conv($temp,1,1); ?>"; }
	else if(currsec % 16 < 8) { data = "<?php echo 'Wind Speed: ', conv($wind,4,1); ?>"; }
	else if(currsec % 16 < 12) { data = "<?php echo 'Daily Rain: ', conv($rain,2,1); ?>"; }
	else { data = "<?php echo 'Pressure: ', conv($pres,3,1); ?>"; }
	//var visitl = currsec-<?php echo date('U'); ?>; if(visitl > 10000) { data = 'Old data! &nbsp; Please refresh'; }
	document.getElementById('currms').innerHTML = data; t = setTimeout('shownewhead()',1000);
}
// -->
</script>

<div id="header">
	<table align="center" width="100%" cellpadding="0" cellspacing="0"><tr>
	<td align="left" valign="top"> <img src="/static-images/leftheadN.JPG" alt="lefthead-anemometer" width="114" height="100" /></td>
	<td align="center"> <a href="/" title="Browse to homepage"> <img src="/static-images/newmain.jpg" alt="mainimage_nw3weather" width="698" height="100" /> </a> </td>
	<td align="right" valign="top"> <img src="/static-images/rightheadS.JPG" alt="righthead-weather_box" width="175" height="100" /></td>
	</tr></table>
	<div id="sub-header">
		<div class="subHeader">
			<span id="currms">
				<script type="text/javascript">
					//<!--
		<?php if($file != 0) { echo 'shownewhead();'; } ?>
		//-->
				</script>
			</span>
		</div>
		<div class="subHeaderR">
			<?php
			if($isBot || $file == 0) {
				echo '<h4 style="display: inline; padding: 0px; margin: 0px 3em 0px 0px; color:#454;">
					nw3 weather, Hampstead London England UK</h4>';
			}
			?>
			<span style="text-align:right"><?php echo $shr; ?></span>
		</div>
	</div>
</div>