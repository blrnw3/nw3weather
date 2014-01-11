<h3>Latest weather/sky cam Image</h3>

<p>The camera is a Logitech C300 and is looking NE over Hampstead Heath (<a href="about#location" title="About page">see map</a>).</p>
<img id="skycam" src="<?php echo $this->skycam_url ?>" title="Latest skycam" width="640" height="480" alt="skycam" />

<p>The image is updated automatically every minute, day and night, operating with a delay of about 70s.
<br />
<?php if($this->dark): ?>
	<h3>Latest daylight weathercam image</h3>
	<img src="/sunsetcam.jpg" alt="Latest sunsetcam" width="640" height="480" />
	<br /><br />
<?php endif; ?>

<script type="text/javascript">
$(document).ready(function(){
	setInterval(camRefesh, 5000);
});
function camRefesh() {
	var now = date().getSeconds();
	if(now < 6 && now >= 1) {
		$('#skycam').attr('src', '<?php echo $this->skycam_url ?>' +'?'+ time());
	}
}
</script>