<script type="text/javascript">
	//<![CDATA[
	var timePHP = <?php echo time(); ?>;
	var dateJS = new Date(timePHP*1000);
	var cntJS = 0;
	function updateTime() {
		dateJS = new Date(timePHP*1000);
		timePHP++;
		setTimeout("updateTime()", 1000);
	}
	updateTime();
	//]]>
</script>

<script type="text/javascript">
	//<![CDATA[
	/**
	 * Changes tab based on dynamic input
	 * @param n sets the summary_type tab to show
	 * @returns {undefined}
	 * @author &copy; Ben Masschelein-Rodgers, nw3weather, Feb 2024
	 */
	function changeTab(n) {
		$(".rank-tab-button").prop("disabled", false);
		$("#rank-btn-" + n).prop("disabled", "disabled");
		$(".rank-tab").hide();
		$("#rank-" + n).show();
		$("#summary-type-input").val(n);
		$(".arrow").each(function() {
			$(this).attr("href", $(this).attr("href").replace(/(summary_type=)\d+/, 'summary_type=' + n));
		});
		history.pushState(null, null, window.location.href.replace(/(summary_type=)\d+/, 'summary_type=' + n));
	}
	//]]>
</script>

<?php
$camImg = '/currcam';
$camImgNew = '/skycam_small';
$camImgLarge = '/skycam.jpg';
$camImg .= (Page::$fileNum === 1) ? '_small.jpg' : '.jpg';
$camImgNew .= (Page::$fileNum === 1) ? '_small.jpg' : '.jpg';
?>
<script type="text/javascript">
	//<![CDATA[
	var image = "<?php echo $camImg; ?>";
	var imageNew = "<?php echo $camImgNew; ?>";
	var imageLarge = "<?php echo $camImgLarge; ?>";

	function camRefesh() {
		if(dateJS.getSeconds() < 15 && dateJS.getSeconds() >= 10) {
			document.images["refresh"].src = image+"?"+timePHP;
		}
		setTimeout("camRefesh()", 5000);
	}
	function camFreshify(name, img) {
		if(document.images[name]) {
			document.images[name].src = img+"?"+timePHP;
		}
	}
	function refreshAll() {
		if(!document.hidden) {
			camFreshify("refresh-new", imageNew);
			camFreshify("refresh-home", imageNew);
			camFreshify("refresh-lg", imageLarge);
		}
	}
	function camRefresher() {
		refreshAll();
		setTimeout("camRefresher()", 10000);
	}

	document.addEventListener("visibilitychange", refreshAll, false);
	camRefresher();
	//]]>
</script>

<script type='text/javascript'>
	//<![CDATA[
	function shownewhead() {
		var curr = new Date(); var currms = curr.getTime();
		var currsec = Math.round(currms / 1000); var data = '';
		if(currsec % 16 < 4) { data = "<?php echo 'Temperature: ',Wx::conv(Live::$temp,Wx::Temperature,1); ?>"; }
		else if(currsec % 16 < 8) { data = "<?php echo 'Wind Speed: ', Wx::conv(Live::$wind,Wx::Wind,1); ?>"; }
		else if(currsec % 16 < 12) { data = "<?php echo 'Daily Rain: ', Wx::conv(Live::$rain,Wx::Rain,1); ?>"; }
		else { data = "<?php echo 'Pressure: ', Wx::conv(Live::$pres,Wx::Pressure,1); ?>"; }
		document.getElementById('live-wx').innerHTML = data;
		setTimeout('shownewhead()',2000);
	}
	shownewhead();
	//]]>
</script>

<script type="text/javascript">
	//<![CDATA[
	function loadVid(vid, seek, sel, noautoplay) {
		$("#skycam-selector span").removeClass("selected");
		$("#timelapse-" + sel).addClass("selected");

		var src = '/cam/timelapse/' + vid + '.mp4';
		console.log("Loading " + src);
		var vidBox = document.getElementById('timelapse');
		vidBox.innerHTML = '<video id="timelapse-vid" width="864" height="576" controls><source src="' + src + '" type="video/mp4"></video>';

		var vid = document.getElementById('timelapse-vid');
		vid.currentTime = seek;
		if(!noautoplay) {
			vid.play();
		}
	}
	//]]>
</script>